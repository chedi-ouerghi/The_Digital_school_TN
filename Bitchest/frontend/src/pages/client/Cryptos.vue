<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { AlertCircle, CheckCircle2, TrendingUp, TrendingDown, Search, Filter, Grid3x3, List, RefreshCw, BarChart3, Coins, Wallet, Star, Zap, Target } from 'lucide-vue-next'

// Import des composants shadcn-vue
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Progress } from '@/components/ui/progress'
import { Skeleton } from '@/components/ui/skeleton'
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'

const router = useRouter()

// États
const cryptos = ref<any[]>([])
const loading = ref(false)
const page = ref(1)
const totalPages = ref(1)
const itemsPerPage = 12
const query = ref('')
const sortBy = ref<'trending'|'price'|'change'|'market_cap'>('trending')
const viewMode = ref<'grid'|'list'>('grid')
const userBalance = ref(0)
const portfolio = ref<any[]>([])
const favorites = ref<string[]>([])
const activeTab = ref('all')

// Modal d'achat
const showBuyModal = ref(false)
const selectedCrypto = ref<any>(null)
const quantity = ref('')
const buyingError = ref('')
const buyingSuccess = ref('')
const isBuying = ref(false)

// Helper function to build proper image URLs
function makeImageUrl(path: string | undefined | null): string | undefined {
  if (!path) return undefined
  const p = String(path)
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const cleanPath = p.startsWith('/storage/') ? p : `/storage/${p}`
  return `${baseUrl}${cleanPath}`
}

// Format functions
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

function formatNumber(value: any, decimals = 4): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toLocaleString('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: decimals
  })
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e12) return `€${(n / 1e12).toFixed(1)}T`
  if (n >= 1e9) return `€${(n / 1e9).toFixed(1)}B`
  if (n >= 1e6) return `€${(n / 1e6).toFixed(1)}M`
  if (n >= 1e3) return `€${(n / 1e3).toFixed(1)}K`
  return formatCurrency(n)
}

function formatPercentage(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0.00%'
  const sign = n >= 0 ? '+' : ''
  return `${sign}${n.toFixed(2)}%`
}

// Data fetching
async function fetchCryptos() {
  loading.value = true
  try {
    const res = await api.crypto.list({ page: page.value })
    const data = res.data || []
    cryptos.value = Array.isArray(data) ? data : []
    totalPages.value = Math.ceil((res.total || cryptos.value.length) / itemsPerPage)
  } catch (err: any) {
    console.error('Error loading cryptos:', err)
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
    portfolio.value = Array.isArray(assets)
      ? assets
          .map((a: any) => {
            const cm = a.cryptomoney || a
            const quantity = Number(a.quantity ?? a.pivot?.quantity ?? 0)
            return {
              ...a,
              cryptomoney: cm,
              quantity
            }
          })
          .filter((p: any) => Number(p.quantity || 0) > 0)
      : []
    
    // Load favorites from localStorage
    const storedFavorites = localStorage.getItem('crypto_favorites')
    favorites.value = storedFavorites ? JSON.parse(storedFavorites) : []
  } catch (err) {
    console.error('Error loading user data:', err)
    userBalance.value = 0
    portfolio.value = []
  }
}

onMounted(async () => {
  await Promise.all([fetchCryptos(), fetchUserData()])
})

// Vérifier si une crypto est dans le portfolio
function isInPortfolio(crypto: any): boolean {
  const symbol = (crypto.symbole || crypto.symbol || '').toString().toLowerCase()
  return portfolio.value.some(p => 
    (p.cryptomoney?.symbole || p.cryptomoney?.symbol || '').toString().toLowerCase() === symbol
  )
}

// Gestion des favoris
function toggleFavorite(crypto: any) {
  const symbol = (crypto.symbole || crypto.symbol || '').toString()
  if (favorites.value.includes(symbol)) {
    favorites.value = favorites.value.filter(s => s !== symbol)
  } else {
    favorites.value = [...favorites.value, symbol]
  }
  localStorage.setItem('crypto_favorites', JSON.stringify(favorites.value))
}

function isFavorite(crypto: any): boolean {
  const symbol = (crypto.symbole || crypto.symbol || '').toString()
  return favorites.value.includes(symbol)
}

// Retourne la quantité possédée pour une crypto donnée (0 si aucune)
function getOwnedQuantity(crypto: any): number {
  const symbol = (crypto?.symbole || crypto?.symbol || '').toString().toLowerCase()
  const found = portfolio.value.find(p => ((p.cryptomoney?.symbole || p.cryptomoney?.symbol || '')?.toString().toLowerCase()) === symbol)
  return found ? Number(found.quantity || 0) : 0
}

// Cryptos filtrées et triées
const filteredCryptos = computed(() => {
  let list = cryptos.value.slice()
  
  // Filter by search
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(c => 
      (c.nom || c.name || '').toLowerCase().includes(q) || 
      (c.symbole || c.symbol || '').toLowerCase().includes(q)
    )
  }
  
  // Filter by tab
  if (activeTab.value === 'favorites') {
    list = list.filter(c => isFavorite(c))
  } else if (activeTab.value === 'owned') {
    list = list.filter(c => isInPortfolio(c))
  } else if (activeTab.value === 'gainers') {
    list = list.filter(c => Number(c.change_24h_pct || c.change_24h || 0) > 0)
  } else if (activeTab.value === 'losers') {
    list = list.filter(c => Number(c.change_24h_pct || c.change_24h || 0) < 0)
  }
  
  // Sort
  switch (sortBy.value) {
    case 'trending':
      list.sort((a, b) => {
        const aChange = Number(a.change_24h_pct || a.change_24h || 0)
        const bChange = Number(b.change_24h_pct || b.change_24h || 0)
        return Math.abs(bChange) - Math.abs(aChange)
      })
      break
    case 'price':
      list.sort((a, b) => Number(b.price_eur || b.price || 0) - Number(a.price_eur || a.price || 0))
      break
    case 'change':
      list.sort((a, b) => Number(b.change_24h_pct || b.change_24h || 0) - Number(a.change_24h_pct || a.change_24h || 0))
      break
    case 'market_cap':
      list.sort((a, b) => Number(b.market_cap || 0) - Number(a.market_cap || 0))
      break
  }
  
  return list
})

// Statistiques
const stats = computed(() => ({
  totalCryptos: cryptos.value.length,
  topGainer: cryptos.value.reduce((max, c) => {
    const change = Number(c.change_24h_pct || c.change_24h || 0)
    return change > max.change ? { name: c.nom || c.name, change } : max
  }, { name: '', change: -Infinity }),
  avgChange: cryptos.value.reduce((sum, c) => sum + Number(c.change_24h_pct || c.change_24h || 0), 0) / cryptos.value.length || 0
}))

// Buy functions
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

// Quantité maximale achetable avec le solde disponible pour la crypto sélectionnée.
// Arrondi vers le BAS à la précision de l'application (8 décimales) pour garantir
// que le coût total ne dépasse jamais le solde disponible.
function calculateMaxQuantity(): number {
  const price = Number(selectedCrypto.value?.price_eur || selectedCrypto.value?.price || 0)
  if (!price || price <= 0 || !Number.isFinite(userBalance.value) || userBalance.value <= 0) return 0
  return Math.floor((userBalance.value / price) * 1e8) / 1e8
}

function setMaxQuantity() {
  const maxQty = calculateMaxQuantity()
  quantity.value = maxQty > 0 ? maxQty.toFixed(8) : ''
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

// Modal functions
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

async function handleBuy() {
  if (!selectedCrypto.value) return
  
  buyingError.value = ''
  buyingSuccess.value = ''
  
  // 🔐 SECURITY: Vérifier que l'utilisateur a changé son mot de passe
  try {
    const profile = await api.auth.profile()
    if (!profile.password_changed_at) {
      buyingError.value = 'You must change your password before making purchases. Please go to your profile settings.'
      return
    }
  } catch (err) {
    console.error('Error checking password status:', err)
    buyingError.value = 'Error verifying your account status'
    return
  }
  
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
    buyingError.value = err?.message || 'Error during purchase'
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
  fetchCryptos()
}

// Image error handler
function handleImgError(e: Event) {
  const target = e.target as HTMLImageElement
  if (target) {
    target.style.display = 'none'
    const parent = target.parentElement
    if (parent) {
      const fallback = parent.querySelector('.img-fallback')
      if (fallback) fallback.classList.remove('hidden')
    }
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Hero Section -->
<div
class="relative overflow-hidden rounded-xl 
            bg-gradient-to-br from-white via-blue-50 to-blue-200 
            p-6 text-gray-900">

  <div class="absolute inset-0 bg-white/40"></div>

  <div class="relative z-10">
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-6">

      <div class="max-w-2xl">
        <h1 class="text-3xl md:text-4xl font-bold mb-2">
          Explore Cryptocurrencies
        </h1>
        <p class="text-gray-600 mb-4">
          Discover, invest, and track the most promising digital assets
        </p>

        <div class="flex flex-wrap gap-3">
          <Badge class="bg-blue-100 text-blue-900 border-blue-200">
            <TrendingUp class="h-3 w-3 mr-1" />
            Price History
          </Badge>
          <Badge class="bg-blue-100 text-blue-900 border-blue-200">
            <Zap class="h-3 w-3 mr-1" />
            Paper Trading
          </Badge>
          <Badge class="bg-blue-100 text-blue-900 border-blue-200">
            <Target class="h-3 w-3 mr-1" />
            Data Visualization
          </Badge>
        </div>
      </div>

      <div class="bg-white/70 backdrop-blur-sm rounded-lg p-4 min-w-[200px] shadow-sm">
        <div class="flex items-center gap-2 mb-2 text-gray-600">
          <Wallet class="h-4 w-4" />
          <span class="text-sm">Your Balance</span>
        </div>
        <div class="text-2xl font-bold text-gray-900">
          {{ formatCurrency(userBalance) }}
        </div>
        <div class="text-xs text-gray-500 mt-1">
          Available for trading
        </div>
      </div>

    </div>
  </div>
</div>


    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card class="border border-gray-200 hover:border-blue-300 transition-colors">
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Total Cryptos</p>
              <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.totalCryptos }}</p>
            </div>
            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
              <Coins class="h-5 w-5 text-blue-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border border-gray-200 hover:border-green-300 transition-colors">
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Top Gainer</p>
              <p class="text-lg font-bold text-green-600 mt-1">{{ stats.topGainer.change?.toFixed(2) }}%</p>
              <p class="text-xs text-gray-500 truncate">{{ stats.topGainer.name }}</p>
            </div>
            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
              <TrendingUp class="h-5 w-5 text-green-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border border-gray-200 hover:border-purple-300 transition-colors">
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Avg 24h Change</p>
              <p
:class="[
                'text-lg font-bold mt-1',
                stats.avgChange >= 0 ? 'text-green-600' : 'text-red-600'
              ]">
                {{ stats.avgChange.toFixed(2) }}%
              </p>
            </div>
            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
              <BarChart3 class="h-5 w-5 text-purple-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <Card class="border border-gray-200 hover:border-amber-300 transition-colors">
        <CardContent class="p-4">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Your Portfolio</p>
              <p class="text-lg font-bold text-gray-900 mt-1">{{ portfolio.length }} assets</p>
            </div>
            <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
              <Wallet class="h-5 w-5 text-amber-600" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Main Content Area -->
    <div class="space-y-4">
      <!-- Filters and Controls -->
      <Card class="border border-gray-200">
        <CardContent class="p-4">
          <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
                <Input
                  v-model="query"
                  placeholder="Search cryptocurrencies..."
                  class="pl-10"
                />
              </div>
            </div>

            <!-- Tabs -->
            <Tabs v-model="activeTab" class="w-full lg:w-auto">
              <TabsList class="grid grid-cols-5">
                <TabsTrigger value="all">All</TabsTrigger>
                <TabsTrigger value="favorites" class="flex items-center gap-1">
                  <Star class="h-3 w-3" />
                  Fav
                </TabsTrigger>
                <TabsTrigger value="owned" class="flex items-center gap-1">
                  <Wallet class="h-3 w-3" />
                  Owned
                </TabsTrigger>
                <TabsTrigger value="gainers" class="flex items-center gap-1">
                  <TrendingUp class="h-3 w-3" />
                  Gainers
                </TabsTrigger>
                <TabsTrigger value="losers" class="flex items-center gap-1">
                  <TrendingDown class="h-3 w-3" />
                  Losers
                </TabsTrigger>
              </TabsList>
            </Tabs>

            <!-- Sort and View -->
            <div class="flex gap-2">
              <Select v-model="sortBy">
                <SelectTrigger class="w-[140px]">
                  <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4" />
                    <SelectValue placeholder="Sort by" />
                  </div>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="trending">Trending</SelectItem>
                  <SelectItem value="price">Price</SelectItem>
                  <SelectItem value="change">24h Change</SelectItem>
                  <SelectItem value="market_cap">Market Cap</SelectItem>
                </SelectContent>
              </Select>

              <div class="flex border rounded-lg overflow-hidden bg-gray-50">
                <Button
                  variant="ghost"
                  size="sm"
                  :class="[
                    'rounded-none px-3',
                    viewMode === 'grid' 
                      ? 'bg-white text-gray-900 shadow-sm border' 
                      : 'text-gray-600 hover:text-gray-900'
                  ]"
                  @click="viewMode = 'grid'"
                >
                  <Grid3x3 class="h-4 w-4" />
                </Button>
                <Button
                  variant="ghost"
                  size="sm"
                  :class="[
                    'rounded-none px-3',
                    viewMode === 'list' 
                      ? 'bg-white text-gray-900 shadow-sm border' 
                      : 'text-gray-600 hover:text-gray-900'
                  ]"
                  @click="viewMode = 'list'"
                >
                  <List class="h-4 w-4" />
                </Button>
              </div>

              <Button 
                variant="outline"
                size="sm"
                class="gap-2"
                :disabled="loading"
                @click="fetchCryptos"
              >
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-4">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
          <Card v-for="i in 6" :key="i" class="border border-gray-200">
            <CardContent class="p-4">
              <div class="space-y-3">
                <Skeleton class="h-4 w-1/2" />
                <Skeleton class="h-8 w-full" />
                <Skeleton class="h-4 w-3/4" />
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Empty State -->
      <Card v-else-if="filteredCryptos.length === 0" class="border border-gray-200">
        <CardContent class="p-12 text-center">
          <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
            <Search class="h-8 w-8 text-gray-400" />
          </div>
          <h3 class="text-lg font-semibold text-gray-900 mb-2">No cryptocurrencies found</h3>
          <p class="text-gray-600 mb-6 max-w-md mx-auto">
            {{ query || activeTab !== 'all' ? 'Try adjusting your filters' : 'No cryptocurrencies available at the moment' }}
          </p>
          <div class="flex gap-3 justify-center">
            <Button 
              v-if="query || activeTab !== 'all'"
              variant="outline"
              @click="query = ''; activeTab = 'all'"
            >
              Clear Filters
            </Button>
            <Button 
              variant="outline"
              class="gap-2"
              @click="fetchCryptos"
            >
              <RefreshCw class="h-4 w-4" />
              Refresh
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        <Card 
          v-for="crypto in filteredCryptos" 
          :key="crypto.id"
          class="group border border-gray-200 hover:border-blue-300 hover:shadow-xl transition-all duration-300"
        >
          <CardContent class="p-4">
            <!-- Crypto Header -->
            <div class="flex items-start justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="relative">
                  <div class="w-12 h-12 rounded-full border-2 border-gray-300 group-hover:border-blue-300 transition-colors overflow-hidden bg-white">
                    <img
                      v-if="makeImageUrl(crypto.image || crypto.image_url)"
                      :src="makeImageUrl(crypto.image || crypto.image_url)"
                      :alt="crypto.name || crypto.nom"
                      class="w-full h-full object-cover"
                      @error="handleImgError"
                    />
                    <div v-else class="img-fallback hidden w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                      <span class="text-white font-bold text-lg">
                        {{ (crypto.symbole || crypto.symbol || '?').toString().charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="absolute -top-1 -right-1">
                    <Button
                      size="icon"
                      variant="ghost"
                      class="h-6 w-6 p-0 bg-white border border-gray-200 shadow-sm hover:bg-red-50"
                      @click="toggleFavorite(crypto)"
                    >
                      <Star 
                        class="h-3 w-3" 
                        :class="isFavorite(crypto) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-400'"
                      />
                    </Button>
                  </div>
                </div>
                <div>
                  <h3 class="font-bold text-gray-900 text-sm">{{ crypto.name || crypto.nom }}</h3>
                  <Badge variant="outline" class="text-xs font-mono mt-1">
                    {{ (crypto.symbole || crypto.symbol || '').toString().toUpperCase() }}
                  </Badge>
                </div>
              </div>
              <Badge 
                :class="[
                  'font-medium',
                  Number(crypto.change_24h_pct || crypto.change_24h || 0) >= 0 
                    ? 'bg-green-50 text-green-700 border-green-200' 
                    : 'bg-red-50 text-red-700 border-red-200'
                ]"
              >
                <component 
                  :is="Number(crypto.change_24h_pct || crypto.change_24h || 0) >= 0 ? TrendingUp : TrendingDown" 
                  class="h-3 w-3 mr-1.5" 
                />
                {{ formatPercentage(crypto.change_24h_pct || crypto.change_24h) }}
              </Badge>
            </div>

            <!-- Price Info -->
            <div class="space-y-3 mb-4">
              <div>
                <div class="text-xs text-gray-500 mb-1">Current Price</div>
                <div class="text-xl font-bold text-blue-600">
                  {{ formatCurrency(crypto.price_eur || crypto.price) }}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                  <div class="text-gray-500">Market Cap</div>
                  <div class="font-medium text-gray-900">
                    {{ formatLargeNumber(crypto.market_cap) }}
                  </div>
                </div>
                  <div>
                  <div class="text-gray-500">Category</div>
                  <div class="font-medium text-gray-900">
                    {{(crypto.category) }}
                  </div>
                </div>
                
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-2">
              <Button 
                class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white gap-2"
                size="sm"
                @click="openBuyModal(crypto)"
              >
                <Wallet class="h-4 w-4" />
                {{ isInPortfolio(crypto) ? 'Owned — Buy Again' : 'Buy' }}
              </Button>
              <Button
                variant="outline"
                size="sm"
                class="border-blue-300 text-blue-600 hover:bg-blue-50 hover:text-blue-700"
                @click="goDetails(crypto)"
              >
                Details
              </Button>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- List View -->
      <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead class="bg-gray-50 border-b">
              <tr>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-700">Cryptocurrency</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-700">Price</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-700">24h Change</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-700">Market Cap</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-700">Category</th>
                <th class="text-left py-3 px-4 text-xs font-medium text-gray-700">Actions</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
              <tr 
                v-for="crypto in filteredCryptos" 
                :key="crypto.id"
                class="hover:bg-gray-50 transition-colors"
              >
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full border border-gray-300 overflow-hidden bg-white">
                      <img
                        v-if="makeImageUrl(crypto.image || crypto.image_url)"
                        :src="makeImageUrl(crypto.image || crypto.image_url)"
                        :alt="crypto.name || crypto.nom"
                        class="w-full h-full object-cover"
                        @error="handleImgError"
                      />
                      <div v-else class="img-fallback hidden w-full h-full bg-gradient-to-br from-blue-500 to-purple-500 flex items-center justify-center">
                        <span class="text-white font-bold text-sm">
                          {{ (crypto.symbole || crypto.symbol || '?').toString().charAt(0).toUpperCase() }}
                        </span>
                      </div>
                    </div>
                    <div>
                      <div class="font-medium text-gray-900">{{ crypto.name || crypto.nom }}</div>
                      <div class="text-xs text-gray-500 font-mono">
                        {{ (crypto.symbole || crypto.symbol || '').toString().toUpperCase() }}
                      </div>
                    </div>
                    <Button
                      size="icon"
                      variant="ghost"
                      class="h-6 w-6 p-0 ml-2"
                      @click="toggleFavorite(crypto)"
                    >
                      <Star 
                        class="h-3 w-3" 
                        :class="isFavorite(crypto) ? 'fill-yellow-400 text-yellow-400' : 'text-gray-400'"
                      />
                    </Button>
                  </div>
                </td>
                <td class="py-4 px-4">
                  <div class="font-bold text-blue-600">
                    {{ formatCurrency(crypto.price_eur || crypto.price) }}
                  </div>
                </td>
                <td class="py-4 px-4">
                  <Badge 
                    :class="[
                      'font-medium',
                      Number(crypto.change_24h_pct || crypto.change_24h || 0) >= 0 
                        ? 'bg-green-50 text-green-700 border-green-200' 
                        : 'bg-red-50 text-red-700 border-red-200'
                    ]"
                  >
                    <component 
                      :is="Number(crypto.change_24h_pct || crypto.change_24h || 0) >= 0 ? TrendingUp : TrendingDown" 
                      class="h-3 w-3 mr-1.5" 
                    />
                    {{ formatPercentage(crypto.change_24h_pct || crypto.change_24h) }}
                  </Badge>
                </td>
                <td class="py-4 px-4">
                  <div class="text-sm text-gray-900">
                    {{ formatLargeNumber(crypto.market_cap) }}
                  </div>
                </td>
                <td class="py-4 px-4">
                  <div class="text-sm text-gray-900">
                    {{ (crypto.category) }}
                  </div>
                </td>
                <td class="py-4 px-4">
                  <div class="flex gap-2">
                    <Button 
                      size="sm"
                      class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white gap-2"
                      @click="openBuyModal(crypto)"
                    >
                      <Wallet class="h-3 w-3" />
                      {{ isInPortfolio(crypto) ? 'Owned — Buy Again' : 'Buy' }}
                    </Button>
                    <Button
                      size="sm"
                      variant="outline"
                      class="border-blue-300 text-blue-600 hover:bg-blue-50 hover:text-blue-700"
                      @click="goDetails(crypto)"
                    >
                      View
                    </Button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Pagination -->
      <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
        <div class="text-sm text-gray-600">
          Page {{ page }} of {{ totalPages }} • {{ filteredCryptos.length }} cryptocurrencies
        </div>
        <div class="flex items-center gap-2">
          <Button
            variant="outline"
            size="sm"
            :disabled="page === 1"
            @click="changePage(page - 1)"
          >
            Previous
          </Button>
          <div class="flex items-center gap-1">
            <Button
              v-for="p in Math.min(5, totalPages)"
              :key="p"
              :variant="page === p ? 'default' : 'outline'"
              size="sm"
              class="min-w-[40px]"
              @click="changePage(p)"
            >
              {{ p }}
            </Button>
          </div>
          <Button
            variant="outline"
            size="sm"
            :disabled="page === totalPages"
            @click="changePage(page + 1)"
          >
            Next
          </Button>
        </div>
      </div>
    </div>

    <!-- Buy Dialog -->
    <Dialog :open="showBuyModal" @update:open="closeBuyModal">
      <DialogContent class="sm:max-w-lg border border-gray-200">
        <DialogHeader>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
              <Wallet class="h-5 w-5 text-white" />
            </div>
            <div>
              <DialogTitle class="text-lg font-semibold text-gray-900">Buy Cryptocurrency</DialogTitle>
              <DialogDescription class="text-gray-600">
                Purchase {{ selectedCrypto?.name || selectedCrypto?.nom }}
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="space-y-6 py-4">
          <!-- Alerts -->
          <Alert v-if="buyingError" class="border-red-200 bg-red-50">
            <AlertDescription class="text-red-700 flex items-center gap-2">
              <AlertCircle class="h-4 w-4" />
              {{ buyingError }}
            </AlertDescription>
          </Alert>

          <Alert v-if="buyingSuccess" class="border-emerald-200 bg-emerald-50">
            <AlertDescription class="text-emerald-700 flex items-center gap-2">
              <CheckCircle2 class="h-4 w-4" />
              {{ buyingSuccess }}
            </AlertDescription>
          </Alert>

          <!-- Crypto Info -->
          <Card class="border border-gray-200">
            <CardContent class="p-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full border border-gray-300 overflow-hidden bg-white">
                  <img
                    v-if="selectedCrypto && makeImageUrl(selectedCrypto.image || selectedCrypto.image_url)"
                    :src="makeImageUrl(selectedCrypto.image || selectedCrypto.image_url)"
                    :alt="selectedCrypto.name || selectedCrypto.nom"
                    class="w-full h-full object-cover"
                  />
                  <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <span class="text-white font-bold text-lg">
                      {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '?').toString().charAt(0).toUpperCase() }}
                    </span>
                  </div>
                </div>
                <div class="flex-1">
                  <div class="flex items-center gap-2 mb-1">
                    <div class="font-bold text-gray-900">{{ selectedCrypto?.name || selectedCrypto?.nom }}</div>
                    <Badge variant="outline" class="font-mono">
                      {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}
                    </Badge>
                  </div>
                  <div class="text-xl font-bold text-blue-600">
                    {{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}
                  </div>
                </div>
                <Badge 
                  :class="[
                    'font-medium',
                    Number(selectedCrypto?.change_24h_pct || selectedCrypto?.change_24h || 0) >= 0 
                      ? 'bg-green-50 text-green-700 border-green-200' 
                      : 'bg-red-50 text-red-700 border-red-200'
                  ]"
                >
                  {{ formatPercentage(selectedCrypto?.change_24h_pct || selectedCrypto?.change_24h) }}
                </Badge>
              </div>
            </CardContent>
          </Card>

          <!-- Quantity Input -->
          <div class="space-y-4">
            <div class="space-y-2">
              <Label class="text-sm font-medium text-gray-700">Quantity to Buy</Label>
                <div v-if="selectedCrypto && isInPortfolio(selectedCrypto)" class="text-sm text-gray-600">
                  You own: <strong class="text-gray-900">{{ getOwnedQuantity(selectedCrypto) }} {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}</strong>
                </div>
              <div class="flex gap-2">
                <div class="flex-1 relative">
                  <Input
                    v-model="quantity"
                    type="number"
                    step="0.00000001"
                    min="0.00000001"
                    placeholder="Enter quantity"
                    class="pr-28"
                    :disabled="isBuying"
                    @input="validateQuantity"
                  />
                  <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center gap-1.5">
                    <button
                      type="button"
                      class="h-6 px-2 rounded-md text-[11px] font-bold tracking-wide bg-blue-100 text-blue-700 border border-blue-200 hover:bg-blue-200 active:scale-95 transition-all focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 disabled:opacity-50 disabled:cursor-not-allowed"
                      :disabled="isBuying || calculateMaxQuantity() <= 0"
                      aria-label="Set maximum purchasable quantity with available balance"
                      title="Buy the maximum quantity allowed by your available balance"
                      @click="setMaxQuantity"
                    >
                      MAX
                    </button>
                    <Badge class="bg-blue-100 text-blue-700 border-blue-200">
                      {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}
                    </Badge>
                  </div>
                </div>
              </div>
              
              <!-- Quick Amounts -->
              <div class="grid grid-cols-4 gap-2">
                <Button
                  v-for="amount in quickAmounts"
                  :key="amount"
                  variant="outline"
                  size="sm"
                  class="text-xs"
                  :disabled="isBuying"
                  @click="setQuickAmount(amount)"
                >
                  {{ formatNumber(calculateQuickAmount(amount), 8) }}
                </Button>
              </div>
            </div>

            <!-- Order Summary -->
            <Card class="border border-gray-200">
              <CardContent class="p-4 space-y-3">
                <h4 class="text-sm font-semibold text-gray-900 mb-2">Order Summary</h4>
                <div class="space-y-2">
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Unit Price</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-sm text-gray-600">Quantity</span>
                    <span class="text-sm font-medium text-blue-600">
                      {{ formatNumber(quantity || 0, 8) }} {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}
                    </span>
                  </div>
                  <div class="pt-2 border-t">
                    <div class="flex justify-between items-center">
                      <span class="text-sm font-semibold text-gray-900">Total Cost</span>
                      <span class="text-lg font-bold text-emerald-600">
                        {{ formatCurrency(calculateTotalCost()) }}
                      </span>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Balance Info -->
            <div class="space-y-2">
              <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Available Balance</span>
                <span class="font-medium text-emerald-600">{{ formatCurrency(userBalance) }}</span>
              </div>
              <Progress 
                :value="Math.min((calculateTotalCost() / userBalance) * 100, 100)"
                class="h-2"
                :class="calculateTotalCost() <= userBalance ? 'bg-green-500' : 'bg-red-500'"
              />
<div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Remaining After Purchase</span>
                <span
                  :class="[
                    'font-medium',
                    userBalance - calculateTotalCost() >= 0 ? 'text-green-600' : 'text-red-600'
                  ]">
                  {{ formatCurrency(userBalance - calculateTotalCost()) }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="flex gap-2">
          <Button 
            variant="outline" 
            class="border-gray-300 text-gray-700 hover:bg-gray-50"
            :disabled="isBuying"
            @click="closeBuyModal"
          >
            Cancel
          </Button>
          <Button 
            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white gap-2"
            :disabled="isBuying || !quantity || parseFloat(quantity) <= 0 || calculateTotalCost() > userBalance"
            @click="handleBuy"
          >
            <Wallet class="h-4 w-4" />
            {{ isBuying ? 'Processing...' : `Buy ${formatNumber(quantity || 0, 4)} ${(selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase()}` }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>