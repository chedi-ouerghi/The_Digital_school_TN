<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'

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

// Fonction utilitaire pour formater les prix
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatNumber(value: any, decimals = 2): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e9) return `${(n / 1e9).toFixed(2)}B €`
  if (n >= 1e6) return `${(n / 1e6).toFixed(2)}M €`
  if (n >= 1e3) return `${(n / 1e3).toFixed(2)}K €`
  return formatCurrency(n)
}

async function fetchList() {
  loading.value = true
  try {
    const res = await api.crypto.list(page.value)
    cryptos.value = res.data || res.items || res || []
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
    // Call the wallet endpoint and handle different payload shapes
    const portfolioResponse = await api.wallet.list()
    const walletData = (portfolioResponse && (portfolioResponse as any).wallet) ? (portfolioResponse as any).wallet : (portfolioResponse || {})

    // Balance may be on wallet or top-level solde_eur etc.
    userBalance.value = Number(walletData?.balance_eur ?? (portfolioResponse as any)?.solde_eur ?? 0)

    // Support both shapes: wallet.crypto_wallet_assets (with cryptomoney) OR wallet.cryptomonnaies (older shape)
    const assets = walletData?.crypto_wallet_assets || walletData?.cryptomonnaies || []

    // Normalize portfolio entries and build ownedSymbols
    ownedSymbols.value.clear()
    portfolio.value = Array.isArray(assets)
      ? assets
          .map((a: any) => {
            // support asset.cryptomoney or asset (cryptomonnaies shape)
            const cm = a.cryptomoney || a.cryptomoney || a
            const quantity = Number(a.quantity ?? a.pivot?.quantity ?? (a.cryptomoney?.pivot?.quantity) ?? 0)
            const sym = (cm?.symbol || cm?.symbole || a.symbol || a.symbole || '').toString().toLowerCase()
            if (sym && quantity > 0) ownedSymbols.value.add(sym)
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
  
  // Filtrage par recherche
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(c => 
      (c.nom || '').toLowerCase().includes(q) || 
      (c.symbole || '').toLowerCase().includes(q)
    )
  }
  
  // Tri
  if (sortBy.value === 'name') {
    list.sort((a, b) => (a.nom || '').localeCompare(b.nom || ''))
  } else if (sortBy.value === 'price') {
    list.sort((a, b) => Number(b.price_eur || 0) - Number(a.price_eur || 0))
  } else if (sortBy.value === 'change') {
    list.sort((a, b) => Number(b.change_24h_pct || 0) - Number(a.change_24h_pct || 0))
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

async function handleBuy() {
  if (!selectedCrypto.value) return
  
  buyingError.value = ''
  buyingSuccess.value = ''
  
  // Validation
  const qty = parseFloat(quantity.value)
  if (!qty || qty <= 0) {
    buyingError.value = 'Veuillez entrer une quantité valide'
    return
  }

  const totalCost = qty * Number(selectedCrypto.value.price_eur || 0)
  if (totalCost > userBalance.value) {
    buyingError.value = `Solde insuffisant. Vous avez ${formatCurrency(userBalance.value)}`
    return
  }

  const symbol = String(selectedCrypto.value.symbol || selectedCrypto.value.symbole || '').toUpperCase()
  if (!symbol) {
    buyingError.value = 'Crypto invalide (symbole manquant)'
    return
  }

  isBuying.value = true
  try {
    await api.wallet.transact({
      symbol,
      type: 'ACHAT',
      quantity: qty
    })
    
    buyingSuccess.value = 'Achat effectué avec succès!'
    
    // Attendre un peu pour que l'utilisateur voie le message
    setTimeout(() => {
      closeBuyModal()
      fetchUserData() // Rafraîchir les données
    }, 1500)
    
  } catch (err: any) {
    buyingError.value = (err?.message || 'Erreur lors de l\'achat')
  } finally {
    isBuying.value = false
  }
}

function goDetails(c: any) {
  router.push(`/dashboard/cryptos/${c.id}`)
}

function changePage(newPage: number) {
  page.value = newPage
  fetchList()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header avec balance et stats -->
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


    <!-- Barre de recherche et filtres -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <!-- Recherche -->
          <div class="relative flex-1 max-w-md">
            <Input
              v-model="query"
              placeholder="🔍 search by name or symbol..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <div class="flex gap-3 w-full sm:w-auto">
            <!-- Tri -->
            <Select v-model="sortBy">
              <SelectTrigger class="w-full sm:w-40 border-[#38618C]">
                <SelectValue placeholder="Trier par" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="price">Price ↓</SelectItem>
                <SelectItem value="change">Variation 24h ↓</SelectItem>
                <SelectItem value="name">Name A-Z</SelectItem>
              </SelectContent>
            </Select>

            <!-- Toggle vue -->
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

            <!-- Actualiser -->
            <Button 
              @click="fetchList" 
              :disabled="loading"
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
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
          <div>Chargement des cryptomonnaies...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredCryptos.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">Aucune cryptomonnaie trouvée</h3>
        <p class="text-gray-500">Essayez de modifier votre recherche</p>
      </CardContent>
    </Card>

    <!-- Vue Grid -->
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
                  v-if="(crypto.image || crypto.image_url || crypto.image_url_full)"
                  :src="crypto.image || crypto.image_url || crypto.image_url_full"
                  :alt="crypto.name || crypto.nom || 'crypto'"
                  class="h-12 w-12 rounded-full border-2 border-gray-300 group-hover:border-[#35A7FF] transition-colors object-cover"
                  @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
                />
                <div v-else class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center">
                  💎
                </div>

                <!-- Owned badge (EN) -->
                <!-- <Badge
                  v-if="isInPortfolio(crypto)"
                  class="absolute -top-2 -left-2 text-xs px-2 py-0.5 rounded bg-[#01FF19] text-[#38618C] font-semibold shadow-sm"
                >
                  Owned
                </Badge> -->

                <!-- existing small check mark (top-right) if you already had it -->
                <Badge v-if="isInPortfolio(crypto)" class="absolute -top-1 -right-1 h-5 w-5 p-0 rounded-full bg-[#01FF19] flex items-center justify-center">
                  ✓
                </Badge>
              </div>
              <div>
                <h3 class="font-bold text-[#38618C] text-lg">{{ crypto.nom || crypto.name }}</h3>
                <div class="text-sm text-gray-500 font-mono">{{ String(crypto.symbole || crypto.symbol || '').toUpperCase() }}</div>
              </div>
            </div>
            <Badge 
              :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
              class="text-white font-semibold"
            >
              {{ Number(crypto.change_24h_pct || 0) >= 0 ? '+' : '' }}{{ formatNumber(crypto.change_24h_pct) }}%
            </Badge>
          </div>

          <div class="space-y-3 mb-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Current Price</div>
              <div class="text-2xl font-bold text-[#35A7FF]">
                {{ formatCurrency(crypto.price_eur) }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-gray-500">Market Cap</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</div>
              </div>
              <div>
                <div class="text-gray-500">24h Volume</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }}</div>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <Button 
              @click.stop="openBuyModal(crypto)"
              class="flex-1 bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
            >
              💰 Buy
            </Button>
            <Button 
              variant="outline"
              @click.stop="goDetails(crypto)"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
            >
              📊 Details
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Vue List -->
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
                  v-if="(crypto.image || crypto.image_url || crypto.image_url_full)"
                  :src="crypto.image || crypto.image_url || crypto.image_url_full"
                  :alt="crypto.name || crypto.nom || 'crypto'"
                  class="h-12 w-12 rounded-full border-2 border-gray-300 object-cover"
                  @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
                />
                <div v-else class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center">💎</div>

                <Badge
                  v-if="isInPortfolio(crypto)"
                  class="absolute -top-2 -left-2 text-xs px-2 py-0.5 rounded bg-[#01FF19] text-[#38618C] font-semibold shadow-sm"
                >
                  Owned
                </Badge>

                <Badge v-if="isInPortfolio(crypto)" class="absolute -top-1 -right-1 h-5 w-5 p-0 rounded-full bg-[#01FF19] flex items-center justify-center text-xs">✓</Badge>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-[#38618C] truncate">{{ crypto.nom || crypto.name }}</h3>
                <div class="text-sm text-gray-500 font-mono">{{ String(crypto.symbole || crypto.symbol || '').toUpperCase() }}</div>
              </div>
            </div>

            <!-- Prix et stats -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">
              <div>
                <div class="text-xs text-gray-500">Price</div>
                <div class="font-bold text-[#35A7FF]">{{ formatCurrency(crypto.price_eur) }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500">24h</div>
                <Badge 
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white"
                >
                  {{ Number(crypto.change_24h_pct || 0) >= 0 ? '+' : '' }}{{ formatNumber(crypto.change_24h_pct) }}%
                </Badge>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs text-gray-500">Market Cap</div>
                <div class="text-sm font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</div>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs text-gray-500">Volume</div>
                <div class="text-sm font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }}</div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex gap-2 w-full sm:w-auto">
              <Button 
                size="sm"
                @click.stop="openBuyModal(crypto)"
                class="flex-1 sm:flex-none bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
              >
                💰 Buy
              </Button>
              <Button 
                size="sm"
                variant="outline"
                @click.stop="goDetails(crypto)"
                class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
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
        @click="changePage(page - 1)"
        class="border-[#38618C] text-[#38618C]"
      >
        ← Précédent
      </Button>
      
      <div class="flex gap-1">
        <Button
          v-for="p in totalPages"
          :key="p"
          :variant="p === page ? 'default' : 'outline'"
          size="sm"
          @click="changePage(p)"
          :class="p === page ? 'bg-[#35A7FF] text-white' : 'border-[#38618C] text-[#38618C]'"
        >
          {{ p }}
        </Button>
      </div>

      <Button
        variant="outline"
        size="sm"
        :disabled="page === totalPages"
        @click="changePage(page + 1)"
        class="border-[#38618C] text-[#38618C]"
      >
        Suivant →
      </Button>
    </div>

    <!-- Modal d'achat -->
    <Dialog :open="showBuyModal" @update:open="closeBuyModal">
      <DialogContent class="sm:max-w-md border-[#35A7FF]">
        <DialogHeader>
          <DialogTitle class="text-[#38618C] text-xl">
            Buy {{ selectedCrypto?.nom || selectedCrypto?.name }}
          </DialogTitle>
          <DialogDescription class="text-gray-600">
            Enter the quantity you want to buy
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <!-- Info crypto -->
          <Card class="border-[#35A7FF] bg-gradient-to-br from-[#35A7FF]/5 to-transparent">
            <CardContent class="p-4">
              <div class="flex items-center gap-4">
                <img 
                  v-if="selectedCrypto?.image || selectedCrypto?.image_url"
                  :src="selectedCrypto?.image || selectedCrypto?.image_url"
                  :alt="selectedCrypto?.name || selectedCrypto?.nom || 'crypto'"
                  class="h-14 w-14 rounded-full border-2 border-[#35A7FF] object-cover"
                  @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
                />
                <div v-else class="h-14 w-14 rounded-full border-2 border-[#35A7FF] bg-gray-100 flex items-center justify-center">💎</div>

                <Badge
                  v-if="selectedCrypto && isInPortfolio(selectedCrypto)"
                  class="absolute -top-2 -left-2 text-xs px-2 py-0.5 rounded bg-[#01FF19] text-[#38618C] font-semibold shadow-sm"
                >
                  Owned
                </Badge>
              </div>
            </CardContent>
          </Card>
          
          <!-- Formulaire -->
          <div class="space-y-4">
            <Alert v-if="buyingError" class="border-[#FF5964] bg-[#FF5964]/10">
              <AlertDescription class="text-[#FF5964]">❌ {{ buyingError }}</AlertDescription>
            </Alert>
            
            <Alert v-if="buyingSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
              <AlertDescription class="text-[#01FF19]">✅ {{ buyingSuccess }}</AlertDescription>
            </Alert>

            <div class="space-y-2">
              <Label for="quantity" class="text-[#38618C] font-semibold">Quantity to buy</Label>
              <Input
                id="quantity"
                v-model="quantity"
                type="number"
                step="0.00000001"
                min="0"
                placeholder="0.00000000"
                class="border-[#38618C] focus:border-[#35A7FF] font-mono text-lg"
                :disabled="isBuying"
              />
            </div>

            <!-- Récapitulatif -->
            <Card class="border-gray-200">
              <CardContent class="p-4 space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-500">Unit Price</span>
                  <span class="font-semibold text-[#38618C]">{{ formatCurrency(selectedCrypto?.price_eur) }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-500">Quantity</span>
                  <span class="font-semibold text-[#38618C] font-mono">{{ quantity || '0' }}</span>
                </div>
                <div class="border-t border-gray-200 pt-2 flex justify-between">
                  <span class="font-semibold text-[#38618C]">Total Cost</span>
                  <span class="font-bold text-[#35A7FF] text-lg">
                    {{ formatCurrency((parseFloat(quantity) || 0) * (selectedCrypto?.price_eur || 0)) }}
                  </span>
                </div>
                <div class="flex justify-between text-xs">
                  <span class="text-gray-500">Available Balance</span>
                  <span 
                    class="font-semibold"
                    :class="(parseFloat(quantity) || 0) * (selectedCrypto?.price_eur || 0) > userBalance ? 'text-[#FF5964]' : 'text-[#01FF19]'"
                  >
                    {{ formatCurrency(userBalance) }}
                  </span>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <DialogFooter class="flex gap-2 sm:gap-0">
          <Button 
            variant="outline" 
            @click="closeBuyModal" 
            class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white flex-1 sm:flex-none"
            :disabled="isBuying"
          >
            ✕ Cancel
          </Button>
          <Button 
            @click="handleBuy"
            class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold flex-1 sm:flex-none"
            :disabled="isBuying || !quantity || parseFloat(quantity) <= 0"
          >
            {{ isBuying ? '⏳ Processing...' : '✓ Confirm Purchase' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
:deep(.border-\[#38618C\]) {
  border-color: #38618C;
}

:deep(.text-\[#38618C\]) {
  color: #38618C;
}

:deep(.bg-\[#01FF19\]) {
  background-color: #01FF19;
}

:deep(.bg-\[#35A7FF\]) {
  background-color: #35A7FF;
}

:deep(.bg-\[#FF5964\]) {
  background-color: #FF5964;
}

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}
</style>