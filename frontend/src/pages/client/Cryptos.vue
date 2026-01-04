<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { AlertCircle, CheckCircle2 } from 'lucide-vue-next'

// Import des composants shadcn-vue
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const router = useRouter()
const cryptos = ref<any[]>([])
const loading = ref(false)
const page = ref(1)
const totalPages = ref(1)
const itemsPerPage = 10
const query = ref('')
const sortBy = ref<'name'|'price'|'change'>('price')
const viewMode = ref<'grid'|'list'>('grid')
const userBalance = ref(0)

// Modal d'achat
const showBuyModal = ref(false)
const selectedCrypto = ref<any>(null)
const quantity = ref('')
const buyingError = ref('')
const buyingSuccess = ref('')
const isBuying = ref(false)

// Portfolio holdings
const portfolio = ref<any[]>([])
const ownedSymbols = ref<Set<string>>(new Set())

// Helper function to build proper image URLs
function makeImageUrl(path: string | undefined | null): string | undefined {
  if (!path) return undefined
  const p = String(path)
  if (p.startsWith('http://') || p.startsWith('https://')) return p

  // Build complete URL for relative paths
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const cleanPath = p.startsWith('/storage/') ? p : `/storage/${p}`
  return `${baseUrl}${cleanPath}`
}

// Format functions - consolidated
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('en-US', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2 
  })
}

function formatNumber(value: any, decimals = 2): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  
  if (decimals === 0) {
    return n.toLocaleString('en-US')
  }
  
  const formatted = n.toFixed(decimals)
  return formatted.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '')
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e9) return `${(n / 1e9).toFixed(2)}B`
  if (n >= 1e6) return `${(n / 1e6).toFixed(2)}M`
  if (n >= 1e3) return `${(n / 1e3).toFixed(2)}K`
  return n.toFixed(2)
}

function formatPercentage(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0.00%'
  const sign = n >= 0 ? '+' : ''
  return `${sign}${n.toFixed(2)}%`
}

async function fetchList() {
  loading.value = true
  try {
    const res = await api.crypto.list({ page: page.value })
    const data = res.data || []
    cryptos.value = Array.isArray(data) ? data : []
    totalPages.value = Math.ceil((res.total || cryptos.value.length) / itemsPerPage)
  } catch (err: any) {
    console.error(err)
    cryptos.value = []
  } finally {
    loading.value = false
  }
}

async function fetchUserData() {
  try {
    const portfolioResponse = await api.wallet.list()
    userBalance.value = Number(portfolioResponse?.balance_eur ?? 0)
 
     const assets = portfolioResponse?.assets || []

    ownedSymbols.value.clear()
    portfolio.value = Array.isArray(assets)
      ? assets
          .map((a: any) => {
            const cm = a.cryptomoney || a.cryptomoney || a
            const quantity = Number(a.quantity ?? a.pivot?.quantity ?? (a.cryptomoney?.pivot?.quantity) ?? 0)
            const sym = (cm?.symbol || cm?.symbole || a.symbol || a.symbole || '').toString().toLowerCase()
            if (sym) ownedSymbols.value.add(sym)
            return {
              ...a,
              cryptomoney: cm,
              quantity
            }
          })
          .filter((p: any) => Number(p.quantity || 0) > 0)
      : []

  } catch (err) {
    console.error('Error loading wallet data:', err)
    userBalance.value = 0
    portfolio.value = []
    ownedSymbols.value.clear()
  }
}

onMounted(async () => {
  await Promise.all([fetchList(), fetchUserData()])
})

// Vérifier si une crypto est dans le wallet
function isInPortfolio(crypto: any): boolean {
  const symbol = ((crypto.symbole || crypto.symbol || crypto.sym || '').toString()).toLowerCase()
  return ownedSymbols.value.has(symbol)
}

// Cryptos filtrées et triées
const filteredCryptos = computed(() => {
  let list = cryptos.value.slice()
  
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(c => 
      (c.nom || c.name || '').toLowerCase().includes(q) || 
      (c.symbole || c.symbol || '').toLowerCase().includes(q)
    )
  }
  
  if (sortBy.value === 'name') {
    list.sort((a, b) => (a.nom || a.name || '').localeCompare(b.nom || b.name || ''))
  } else if (sortBy.value === 'price') {
    list.sort((a, b) => Number(b.price_eur || b.price || 0) - Number(a.price_eur || a.price || 0))
  } else if (sortBy.value === 'change') {
    list.sort((a, b) => Number(b.change_24h_pct || b.change_24h || 0) - Number(a.change_24h_pct || a.change_24h || 0))
  }
  
  return list
})

function openBuyModal(c: any) {
  selectedCrypto.value = c
  quantity.value = ''
  buyingError.value = ''
  buyingSuccess.value = ''
  showBuyModal.value = true
}

function closeBuyModal() {
  showBuyModal.value = false
  selectedCrypto.value = null
  quantity.value = ''
  buyingError.value = ''
  buyingSuccess.value = ''
}

// Buy functions - consolidated
const quickAmounts = [10, 50, 100, 500]

function calculateQuickAmount(amountEur: number): number {
  const price = selectedCrypto.value?.price_eur || selectedCrypto.value?.price || 0
  if (!price) return 0
  return amountEur / price
}

function setQuickAmount(amountEur: number) {
  const cryptoAmount = calculateQuickAmount(amountEur)
  quantity.value = cryptoAmount.toFixed(8)
}

function calculateTotalCost(): number {
  const price = selectedCrypto.value?.price_eur || selectedCrypto.value?.price || 0
  return (parseFloat(quantity.value) || 0) * price
}

function validateQuantity() {
  const val = parseFloat(quantity.value)
  if (isNaN(val) || val < 0) {
    quantity.value = ''
  } else if (val < 0.00000001) {
    quantity.value = '0.00000001'
  }
}

async function handleBuy() {
  if (!selectedCrypto.value) return
  
  buyingError.value = ''
  buyingSuccess.value = ''
  
  const qty = parseFloat(quantity.value)
  if (!qty || qty <= 0) {
    buyingError.value = 'Please enter a valid quantity'
    return
  }

  const totalCost = calculateTotalCost()
  if (totalCost > userBalance.value) {
    buyingError.value = `Insufficient balance. You have ${formatCurrency(userBalance.value)}, you need ${formatCurrency(totalCost)}`
    return
  }

  const symbol = String(selectedCrypto.value.symbol || selectedCrypto.value.symbole || '').toUpperCase()
  if (!symbol) {
    buyingError.value = 'Invalid crypto (missing symbol)'
    return
  }

  isBuying.value = true
  try {
    await api.wallet.transact({
      symbol,
      type: 'ACHAT',
      quantity: qty
    })
    
    buyingSuccess.value = 'Purchase completed successfully!'
    
    setTimeout(() => {
      closeBuyModal()
      fetchUserData()
    }, 1500)
    
  } catch (err: any) {
    buyingError.value = (err?.message || 'Error during purchase')
  } finally {
    isBuying.value = false
  }
}

function goDetails(c: any) {
  const cryptoId = c.id || c._id
  if (cryptoId) {
    router.push(`/dashboard/cryptos/${cryptoId}`)
  }
}

function changePage(newPage: number) {
  page.value = newPage
  fetchList()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header with balance and stats -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Cryptocurrency Market</h1>
        <p class="text-gray-500">Discover and invest in the top cryptocurrencies</p>
      </div>
      <Card class="border-[#01FF19] bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Your balance</div>
          <div class="text-2xl font-bold text-[#01FF19]">
            {{ formatCurrency(userBalance) }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Search and filters -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <!-- Search -->
          <div class="relative flex-1 max-w-md">
            <Input
              v-model="query"
              placeholder="🔍 Search by name or symbol..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <div class="flex gap-3 w-full sm:w-auto">
            <!-- Sort -->
            <Select v-model="sortBy">
              <SelectTrigger class="w-full sm:w-40 border-[#38618C]">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="price">Price ↓</SelectItem>
                <SelectItem value="change">24h Change ↓</SelectItem>
                <SelectItem value="name">Name A-Z</SelectItem>
              </SelectContent>
            </Select>

            <!-- View toggle -->
            <div class="flex border border-[#38618C] rounded-md overflow-hidden">
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'grid' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'grid'"
              >
                ⊞ Grid
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'list' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'list'"
              >
                ☰ List
              </Button>
            </div>

            <!-- Refresh -->
            <Button 
              :disabled="loading" 
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              @click="fetchList"
            >
              🔄
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Loading cryptocurrencies...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredCryptos.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">No cryptocurrencies found</h3>
        <p class="text-gray-500">Try adjusting your search</p>
      </CardContent>
    </Card>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card 
        v-for="crypto in filteredCryptos" 
        :key="crypto.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-xl cursor-pointer group"
        @click="goDetails(crypto)"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="relative">
                <img
                  v-if="makeImageUrl(crypto.image || crypto.image_url || crypto.image_url_full)"
                  :src="makeImageUrl(crypto.image || crypto.image_url || crypto.image_url_full)"
                  :alt="crypto.name || crypto.nom || 'crypto'"
                  class="h-12 w-12 rounded-full border-2 border-gray-300 group-hover:border-[#35A7FF] transition-colors object-cover"
                  @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
                />
                <div v-else class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center">
                  💎
                </div>

                <Badge 
                  v-if="isInPortfolio(crypto)"
                  class="absolute -top-1 -right-1 h-5 w-5 p-0 rounded-full bg-[#01FF19] flex items-center justify-center"
                >
                  ✓
                </Badge>
              </div>
              <div>
                <h3 class="font-bold text-[#38618C] text-lg">{{ crypto.nom || crypto.name }}</h3>
                <div class="text-sm text-gray-500 font-mono">{{ String(crypto.symbole || crypto.symbol || '').toUpperCase() }}</div>
              </div>
            </div>
            <Badge 
              :class="Number(crypto.change_24h_pct || crypto.change_24h || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
              class="text-white font-semibold"
            >
              {{ formatPercentage(crypto.change_24h_pct || crypto.change_24h) }}
            </Badge>
          </div>

          <div class="space-y-3 mb-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Current Price</div>
              <div class="text-2xl font-bold text-[#35A7FF]">
                {{ formatCurrency(crypto.price_eur || crypto.price) }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-gray-500">Market Cap</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }} €</div>
              </div>
              <div>
                <div class="text-gray-500">24h Volume</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }} €</div>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <Button 
              class="flex-1 bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
              @click.stop="openBuyModal(crypto)"
            >
              💰 Buy
            </Button>
            <Button 
              variant="outline"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
              @click.stop="goDetails(crypto)"
            >
              📊 Details
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- List View -->
    <div v-else class="space-y-3">
      <Card 
        v-for="crypto in filteredCryptos" 
        :key="crypto.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer"
        @click="goDetails(crypto)"
      >
        <CardContent class="p-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <!-- Crypto info -->
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <div class="relative flex-shrink-0">
                <img
                  v-if="makeImageUrl(crypto.image || crypto.image_url || crypto.image_url_full)"
                  :src="makeImageUrl(crypto.image || crypto.image_url || crypto.image_url_full)"
                  :alt="crypto.name || crypto.nom || 'crypto'"
                  class="h-12 w-12 rounded-full border-2 border-gray-300 object-cover"
                  @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
                />
                <div v-else class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center">
                  💎
                </div>

                <Badge 
                  v-if="isInPortfolio(crypto)"
                  class="absolute -top-1 -right-1 h-5 w-5 p-0 rounded-full bg-[#01FF19] flex items-center justify-center text-xs"
                >
                  ✓
                </Badge>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-[#38618C] truncate">{{ crypto.nom || crypto.name }}</h3>
                <div class="text-sm text-gray-500 font-mono">{{ String(crypto.symbole || crypto.symbol || '').toUpperCase() }}</div>
              </div>
            </div>

            <!-- Price and stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">
              <div>
                <div class="text-xs text-gray-500">Price</div>
                <div class="font-bold text-[#35A7FF]">{{ formatCurrency(crypto.price_eur || crypto.price) }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500">24h</div>
                <Badge 
                  :class="Number(crypto.change_24h_pct || crypto.change_24h || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white"
                >
                  {{ formatPercentage(crypto.change_24h_pct || crypto.change_24h) }}
                </Badge>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs text-gray-500">Market Cap</div>
                <div class="text-sm font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }} €</div>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs text-gray-500">Volume</div>
                <div class="text-sm font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }} €</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 w-full sm:w-auto">
              <Button 
                size="sm"
                class="flex-1 sm:flex-none bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
                @click.stop="openBuyModal(crypto)"
              >
                💰 Buy
              </Button>
              <Button 
                size="sm"
                variant="outline"
                class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
                @click.stop="goDetails(crypto)"
              >
                📊
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-6">
      <Button
        variant="outline"
        size="sm"
        :disabled="page === 1"
        class="border-[#38618C] text-[#38618C]"
        @click="changePage(page - 1)"
      >
        ← Previous
      </Button>
      
      <div class="flex gap-1">
        <Button
          v-for="p in totalPages"
          :key="p"
          :variant="p === page ? 'default' : 'outline'"
          size="sm"
          :class="p === page ? 'bg-[#35A7FF] text-white' : 'border-[#38618C] text-[#38618C]'"
          @click="changePage(p)"
        >
          {{ p }}
        </Button>
      </div>

      <Button
        variant="outline"
        size="sm"
        :disabled="page === totalPages"
        class="border-[#38618C] text-[#38618C]"
        @click="changePage(page + 1)"
      >
        Next →
      </Button>
    </div>

    <!-- Buy Modal -->
    <Dialog :open="showBuyModal" @update:open="closeBuyModal">
      <DialogContent class="sm:max-w-lg bg-gradient-to-b from-white to-blue-50 dark:from-gray-900 dark:to-gray-800 border-2 border-blue-500 shadow-2xl">
        <DialogHeader>
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-full bg-gradient-to-r from-blue-500 to-cyan-500">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
            </div>
            <div>
              <DialogTitle class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                Buy Cryptocurrency
              </DialogTitle>
              <DialogDescription class="text-gray-600 dark:text-gray-400 mt-1">
                Purchase {{ selectedCrypto?.nom || selectedCrypto?.name }} instantly
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="space-y-6 py-4">
          <!-- Crypto Info Card -->
          <Card class="border border-blue-200 dark:border-blue-800 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/10 dark:to-cyan-900/10 shadow-lg">
            <CardContent class="p-6">
              <div class="flex items-start gap-6">
                <!-- Crypto Image -->
                <div class="relative">
                  <div class="w-20 h-20 rounded-2xl border-4 border-white dark:border-gray-800 shadow-xl overflow-hidden bg-white dark:bg-gray-800">
                    <img 
                      v-if="makeImageUrl(selectedCrypto?.image || selectedCrypto?.image_url || selectedCrypto?.image_url_full)"
                      :src="makeImageUrl(selectedCrypto?.image || selectedCrypto?.image_url || selectedCrypto?.image_url_full)"
                      :alt="selectedCrypto?.name || selectedCrypto?.nom || 'crypto'"
                      class="w-full h-full object-cover"
                      @error="(e) => { 
                        e.target.style.display = 'none'
                        const fallback = e.target.nextElementSibling
                        if (fallback) fallback.style.display = 'flex'
                      }"
                    />
                    <div class="w-full h-full hidden items-center justify-center bg-gradient-to-br from-blue-500 to-cyan-500">
                      <span class="text-3xl font-bold text-white">
                        {{ String(selectedCrypto?.symbole || selectedCrypto?.symbol || '?').charAt(0) }}
                      </span>
                    </div>
                  </div>
                  <div class="absolute -top-2 -right-2 w-10 h-10 rounded-full bg-gradient-to-r from-green-500 to-emerald-500 flex items-center justify-center shadow-lg">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                  </div>
                </div>

                <!-- Crypto Details -->
                <div class="flex-1">
                  <div class="flex items-center gap-3 mb-2">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white">
                      {{ selectedCrypto?.nom || selectedCrypto?.name }}
                    </h3>
                    <Badge class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold px-3 py-1 rounded-full">
                      {{ String(selectedCrypto?.symbole || selectedCrypto?.symbol || '').toUpperCase() }}
                    </Badge>
                  </div>
                  
                  <div class="grid grid-cols-2 gap-4 mt-4">
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Current Price</p>
                      <p class="text-2xl font-bold text-green-600">
                        {{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}
                      </p>
                      <p class="text-xs text-gray-500 mt-1">
                        24h Change: 
                        <span :class="Number(selectedCrypto?.change_24h_pct || selectedCrypto?.change_24h || 0) >= 0 ? 'text-green-600' : 'text-red-600'">
                          {{ formatPercentage(selectedCrypto?.change_24h_pct || selectedCrypto?.change_24h) }}
                        </span>
                      </p>
                    </div>
                    <div>
                      <p class="text-sm text-gray-500 dark:text-gray-400">Market Cap</p>
                      <p class="text-lg font-semibold text-blue-600">
                        {{ formatCurrency(selectedCrypto?.market_cap) }}
                      </p>
                      <div class="mt-2">
                        <Badge variant="outline" class="text-xs">
                          {{ selectedCrypto?.category || 'Cryptocurrency' }}
                        </Badge>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Alerts -->
          <Alert v-if="buyingError" variant="destructive" class="animate-shake border-red-500 bg-red-50 dark:bg-red-900/20">
            <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400" />
            <AlertDescription class="text-red-700 dark:text-red-300">
              {{ buyingError }}
            </AlertDescription>
          </Alert>

          <Alert v-if="buyingSuccess" class="animate-fade-in border-emerald-500 bg-emerald-50 dark:bg-emerald-900/20">
            <CheckCircle2 class="w-5 h-5 text-emerald-600 dark:text-emerald-400" />
            <AlertDescription class="text-emerald-700 dark:text-emerald-300 font-medium">
              {{ buyingSuccess }}
            </AlertDescription>
          </Alert>

          <!-- Quantity Input -->
          <div class="space-y-4">
            <div class="space-y-3">
              <Label for="quantity" class="text-lg font-semibold text-gray-900 dark:text-white">
                Amount to Purchase
                <span class="text-sm text-gray-500 dark:text-gray-400 font-normal ml-2">
                  (Minimum: 0.00000001)
                </span>
              </Label>
              
              <div class="relative">
                <Input
                  id="quantity"
                  v-model="quantity"
                  type="number"
                  step="0.00000001"
                  min="0.00000001"
                  placeholder="0.00000000"
                  class="pl-4 pr-32 py-6 text-2xl font-mono border-2 border-blue-300 dark:border-blue-700 focus:border-blue-500 dark:focus:border-blue-400 focus:ring-2 focus:ring-blue-200 dark:focus:ring-blue-900 rounded-xl"
                  :disabled="isBuying"
                  @input="validateQuantity"
                />
                <div class="absolute right-3 top-1/2 -translate-y-1/2">
                  <Badge class="bg-gradient-to-r from-blue-600 to-cyan-600 text-white font-bold px-4 py-2 rounded-lg">
                    {{ String(selectedCrypto?.symbole || selectedCrypto?.symbol || '').toUpperCase() }}
                  </Badge>
                </div>
              </div>

              <!-- Quick Amount Buttons -->
              <div class="grid grid-cols-4 gap-2">
                <Button
                  v-for="amount in quickAmounts"
                  :key="amount"
                  variant="outline"
                  size="sm"
                  class="text-xs font-medium hover:bg-blue-50 dark:hover:bg-blue-900/30"
                  :disabled="isBuying"
                  @click="setQuickAmount(amount)"
                >
                  {{ formatNumber(calculateQuickAmount(amount), 8) }}
                </Button>
              </div>
            </div>

            <!-- Summary Card -->
            <Card class="border border-gray-200 dark:border-gray-700 shadow-lg">
              <CardContent class="p-6 space-y-4">
                <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                  <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  Order Summary
                </h4>
                
                <div class="space-y-3">
                  <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-gray-600 dark:text-gray-400">Unit Price</span>
                    <span class="font-semibold text-gray-900 dark:text-white">{{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}</span>
                  </div>
                  
                  <div class="flex justify-between items-center py-2 border-b border-gray-100 dark:border-gray-800">
                    <span class="text-gray-600 dark:text-gray-400">Quantity</span>
                    <span class="font-mono font-bold text-blue-600 dark:text-blue-400">
                      {{ formatNumber(quantity || 0, 8) }}
                      {{ String(selectedCrypto?.symbole || selectedCrypto?.symbol || '').toUpperCase() }}
                    </span>
                  </div>
                  
                  <div class="flex justify-between items-center py-3 bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/10 dark:to-cyan-900/10 rounded-lg px-4">
                    <div>
                      <span class="font-bold text-gray-900 dark:text-white">Total Cost</span>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ formatNumber(quantity || 0, 8) }} × {{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}
                      </p>
                    </div>
                    <div class="text-right">
                      <p class="text-2xl font-bold bg-gradient-to-r from-blue-600 to-cyan-600 bg-clip-text text-transparent">
                        {{ formatCurrency(calculateTotalCost()) }}
                      </p>
                      <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                        {{ formatNumber(quantity || 0, 8) }} {{ String(selectedCrypto?.symbole || selectedCrypto?.symbol || '').toUpperCase() }}
                      </p>
                    </div>
                  </div>
                </div>

                <!-- Balance & Verification -->
                <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                  <div class="flex justify-between items-center mb-4">
                    <span class="text-gray-700 dark:text-gray-300 font-medium">Available Balance</span>
                    <span class="text-xl font-bold text-green-600 dark:text-green-400">
                      {{ formatCurrency(userBalance) }}
                    </span>
                  </div>
                  
                  <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-500 dark:text-gray-400">Required Amount</span>
                      <span class="font-semibold">{{ formatCurrency(calculateTotalCost()) }}</span>
                    </div>
                    
                    <Progress 
                      :value="Math.min((calculateTotalCost() / userBalance) * 100, 100)"
                      class="h-3 rounded-full"
                      :class="{
                        'bg-green-500': calculateTotalCost() <= userBalance,
                        'bg-yellow-500': calculateTotalCost() > userBalance * 0.8 && calculateTotalCost() <= userBalance,
                        'bg-red-500': calculateTotalCost() > userBalance
                      }"
                    />
                    
                    <div class="flex justify-between text-sm">
                      <span class="text-gray-500 dark:text-gray-400">Remaining After Purchase</span>
                      <span 
                        class="font-bold"
                        :class="userBalance - calculateTotalCost() >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                      >
                        {{ formatCurrency(Math.max(0, userBalance - calculateTotalCost())) }}
                      </span>
                    </div>
                    
                    <!-- Balance Status Messages -->
                    <div v-if="quantity && parseFloat(quantity) > 0" class="mt-4">
                      <Alert 
                        v-if="calculateTotalCost() > userBalance" 
                        variant="destructive" 
                        class="border-red-300 dark:border-red-700 bg-red-50 dark:bg-red-900/20"
                      >
                        <AlertCircle class="w-4 h-4" />
                        <AlertDescription>
                          <div class="flex items-center justify-between">
                            <span>Insufficient funds</span>
                            <span class="font-bold text-red-700 dark:text-red-300">
                              -{{ formatCurrency(calculateTotalCost() - userBalance) }}
                            </span>
                          </div>
                        </AlertDescription>
                      </Alert>
                      
                      <Alert 
                        v-else-if="calculateTotalCost() > userBalance * 0.8" 
                        class="border-yellow-300 dark:border-yellow-700 bg-yellow-50 dark:bg-yellow-900/20"
                      >
                        <AlertCircle class="w-4 h-4 text-yellow-600 dark:text-yellow-400" />
                        <AlertDescription class="text-yellow-700 dark:text-yellow-300">
                          Warning: This purchase will use {{ ((calculateTotalCost() / userBalance) * 100).toFixed(1) }}% of your balance
                        </AlertDescription>
                      </Alert>
                      
                      <Alert 
                        v-else 
                        class="border-green-300 dark:border-green-700 bg-green-50 dark:bg-green-900/20"
                      >
                        <CheckCircle2 class="w-4 h-4 text-green-600 dark:text-green-400" />
                        <AlertDescription class="text-green-700 dark:text-green-300 font-medium">
                          ✅ Sufficient funds available for this purchase
                        </AlertDescription>
                      </Alert>
                    </div>
                  </div>
                </div>

                <!-- Estimated Value & Potential Profit -->
                <div v-if="quantity && parseFloat(quantity) > 0" class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-800">
                  <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Potential Value</h5>
                  <div class="grid grid-cols-3 gap-3">
                    <div class="text-center p-3 rounded-lg bg-blue-50 dark:bg-blue-900/10">
                      <p class="text-xs text-gray-500 dark:text-gray-400">At +5%</p>
                      <p class="font-bold text-green-600 dark:text-green-400">
                        {{ formatCurrency(calculateTotalCost() * 1.05) }}
                      </p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-green-50 dark:bg-green-900/10">
                      <p class="text-xs text-gray-500 dark:text-gray-400">At +10%</p>
                      <p class="font-bold text-green-600 dark:text-green-400">
                        {{ formatCurrency(calculateTotalCost() * 1.1) }}
                      </p>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/10">
                      <p class="text-xs text-gray-500 dark:text-gray-400">At +20%</p>
                      <p class="font-bold text-emerald-600 dark:text-emerald-400">
                        {{ formatCurrency(calculateTotalCost() * 1.2) }}
                      </p>
                    </div>
                  </div>
                  <p class="text-xs text-gray-500 dark:text-gray-400 text-center mt-3">
                    Estimated values based on price movements
                  </p>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <DialogFooter class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200 dark:border-gray-800">
          <Button 
            variant="outline" 
            class="flex-1 border-2 border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-800 text-gray-700 dark:text-gray-300 font-semibold py-6 rounded-xl"
            :disabled="isBuying"
            @click="closeBuyModal"
          >
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Cancel
          </Button>
          
          <Button 
            class="flex-1 bg-gradient-to-r from-blue-600 to-cyan-600 hover:from-blue-700 hover:to-cyan-700 text-white font-bold py-6 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="isBuying || !quantity || parseFloat(quantity) <= 0 || calculateTotalCost() > userBalance || parseFloat(quantity) < 0.00000001"
            @click="handleBuy"
          >
            <div v-if="isBuying" class="flex items-center justify-center">
              <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
              </svg>
              Processing Purchase...
            </div>
            <div v-else class="flex items-center justify-center">
              <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
              </svg>
              <span class="text-lg">Confirm Purchase</span>
            </div>
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
@keyframes shake {
  0%, 100% { transform: translateX(0); }
  10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
  20%, 40%, 60%, 80% { transform: translateX(5px); }
}

@keyframes fade-in {
  from { opacity: 0; transform: translateY(-10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-shake {
  animation: shake 0.5s ease-in-out;
}

.animate-fade-in {
  animation: fade-in 0.3s ease-in-out;
}
</style>