<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Skeleton } from '@/components/ui/skeleton'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const route = useRoute()
const router = useRouter()
const crypto = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const positions = ref<any>(null)
const loadingPositions = ref(false)
const history = ref<any[]>([])

// ============================
// Fonctions utilitaires
// ============================
function formatCurrency(value: any): string {
  const num = parseFloat(value) || 0
  return new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'EUR' }).format(num)
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e9) return `${(n / 1e9).toFixed(2)}B €`
  if (n >= 1e6) return `${(n / 1e6).toFixed(2)}M €`
  if (n >= 1e3) return `${(n / 1e3).toFixed(2)}K €`
  return formatCurrency(n)
}

function formatPercentage(value: any): string {
  const num = parseFloat(value) || 0
  return `${num >= 0 ? '+' : ''}${num.toFixed(2)}%`
}

// ============================
// API
// ============================
async function fetchCryptoDetail() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id
    let response

    if (api.crypto?.show) response = await api.crypto.show(id)
    else if (api.get) response = await api.get(`/cryptos/${id}`)
    else throw new Error('Méthode API non disponible')

    crypto.value = response.data || response.item || response || null
    if (!crypto.value) throw new Error('Cryptomonnaie non trouvée')
  } catch (e: any) {
    error.value = e.message || 'Erreur lors du chargement des données'
    console.error('Erreur détail crypto:', e)
  } finally {
    loading.value = false
  }
}

async function fetchPositions() {
  if (!crypto.value) return
  loadingPositions.value = true
  try {
    const response = await api.wallet.list()
    const walletData = response?.wallet || {}

    // wallet may expose assets under crypto_wallet_assets
    const assets = walletData?.crypto_wallet_assets || []

    // find asset matching current crypto by id (support different keys)
    const asset = assets.find((p: any) =>
      p.cryptomoney?.id === crypto.value.id ||
      p.cryptomoney_id === crypto.value.id ||
      p.cryptomoney?.coingecko_id === crypto.value.coingecko_id
    )

    if (asset) {
      // normalize numeric fields and include related transactions
      const txs = (walletData.transactions || []).filter((t: any) =>
        t.crypto_wallet_asset_id === asset.id || t.cryptomoney_id === asset.cryptomoney_id
      )

      positions.value = {
        ...asset,
        // normalize numbers for consistent usage in template/helpers
        quantity: Number(asset.quantity || asset.pivot?.quantity || 0),
        average_buy_price: Number(asset.average_buy_price || asset.pivot?.average_buy_price || 0),
        transactions: Array.isArray(txs) ? txs : []
      }
    } else {
      positions.value = null
    }
  } catch (e: any) {
    console.error('Error loading positions:', e)
    positions.value = null
  } finally {
    loadingPositions.value = false
  }
}

async function fetchHistoricalData() {
  try {
    if (!crypto.value?.id) return
    let response

    if (api.crypto?.history) response = await api.crypto.history(crypto.value.id)
    else if (api.get) response = await api.get(`/cryptos/${crypto.value.id}/history`)
    else {
      console.warn('Aucune méthode API disponible pour l’historique')
      return
    }

    history.value = response.data || response.prices || response || []
  } catch (e: any) {
    console.warn('Historique non disponible:', e.message)
    history.value = []
  }
}

async function loadAllData() {
  await fetchCryptoDetail()
  if (crypto.value) {
    await Promise.all([fetchPositions(), fetchHistoricalData()])
  }
}

onMounted(loadAllData)

// ============================
// Calculs et graphiques
// ============================
const get7DayChange = computed(() => {
  if (history.value.length < 7) return 0
  const now = history.value.at(-1)?.[1] || 0
  const before = history.value.at(-7)?.[1] || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

const get30DayChange = computed(() => {
  if (history.value.length < 2) return 0
  const now = history.value.at(-1)?.[1] || 0
  const before = history.value[0]?.[1] || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

const chartData = computed(() => {
  if (!history.value?.length) return null
  const labels = history.value.map(p =>
    new Date(p[0]).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
  )
  return {
    labels,
    datasets: [{
      label: 'Prix (EUR)',
      data: history.value.map(p => p[1]),
      borderColor: '#35A7FF',
      backgroundColor: 'rgba(53, 167, 255, 0.1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: { x: { ticks: { color: '#38618C' } }, y: { ticks: { color: '#38618C' } } }
}

// ============================
// Fonctions valeur / plus-value
// ============================
function getPositionValue(position: any): number {
  if (!position || !crypto.value) return 0
  const qty = Number(position.pivot?.quantity || position.quantity || 0)
  const price = Number(crypto.value.price_eur || crypto.value.price || 0)
  return qty * price
}

function getInvestedValue(position: any): number {
  if (!position) return 0
  const qty = Number(position.pivot?.quantity || position.quantity || 0)
  const avgPrice = Number(position.pivot?.average_buy_price || position.average_buy_price || 0)
  return qty * avgPrice
}

function getProfitLoss(position: any): number {
  return getPositionValue(position) - getInvestedValue(position)
}

function goBack() {
  router.push('/dashboard/cryptos')
}

function buyCrypto() {
  if (crypto.value) router.push(`/dashboard/cryptos?buy=${crypto.value.id}`)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <Card class="border-[#38618C]">
      <CardHeader>
        <div class="flex items-center justify-between">
          <CardTitle class="text-2xl font-bold text-[#38618C]">
            Cryptocurrency details
          </CardTitle>
          <Button 
            variant="outline" 
            @click="goBack"
            class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
          >
            ← back
          </Button>
        </div>
      </CardHeader>
    </Card>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <Card>
        <CardContent class="p-6">
          <div class="flex items-center gap-4">
            <Skeleton class="h-12 w-12 rounded-full" />
            <div class="space-y-2">
              <Skeleton class="h-6 w-32" />
              <Skeleton class="h-4 w-24" />
            </div>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
            <Skeleton v-for="i in 4" :key="i" class="h-20 rounded-lg" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-6 text-center">
        <div class="text-4xl mb-4">❌</div>
        <div class="text-[#FF5964] text-lg font-semibold mb-2">Erreur</div>
        <div class="text-gray-600 mb-4">{{ error }}</div>
        <Button 
          @click="loadAllData"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Réessayer
        </Button>
      </CardContent>
    </Card>

    <!-- Not Found State -->
    <Card v-else-if="!crypto" class="border-[#FF5964]">
      <CardContent class="p-6 text-center">
        <div class="text-4xl mb-4">🔍</div>
        <div class="text-[#FF5964] text-lg font-semibold mb-2">Cryptomonnaie non trouvée</div>
        <div class="text-gray-600 mb-4">La cryptomonnaie demandée n'existe pas ou n'est plus disponible.</div>
        <Button 
          @click="goBack"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Retour à la liste
        </Button>
      </CardContent>
    </Card>

    <!-- Crypto Details -->
    <div v-else class="space-y-6">
      <!-- Header avec image et infos -->
      <Card class="border-[#35A7FF]">
        <CardContent class="p-6">
          <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <!-- Header image -->
            <div class="h-24 w-24 rounded-full border-4 border-[#35A7FF] bg-gray-100 flex items-center justify-center flex-shrink-0 relative">
              <img
                v-if="(crypto.image || crypto.image_url || crypto.image_url_full)"
                :src="crypto.image || crypto.image_url || crypto.image_url_full"
                :alt="crypto.name || crypto.nom || 'crypto'"
                class="h-24 w-24 rounded-full object-cover"
                @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
              />
              <div v-else class="text-4xl">💎</div>

              <!-- Owned badge top-left when user holds this crypto -->
              <Badge
                v-if="positions && Number(positions.quantity || 0) > 0"
                class="absolute -top-2 -left-2 text-xs px-2 py-0.5 rounded bg-[#01FF19] text-[#38618C] font-semibold shadow-sm"
              >
                Owned
              </Badge>
            </div>
            
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-[#38618C]">{{ crypto.nom }}</h2>
                <Badge class="bg-[#38618C] text-white text-lg px-3 py-1">
                  {{ String(crypto.symbole || crypto.symbol || '').toUpperCase() }}
                </Badge>
                <Badge 
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white text-lg px-3 py-1"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}% (24h)
                </Badge>
              </div>
              <div class="text-4xl font-bold text-[#35A7FF]">
                {{ formatCurrency(crypto.price_eur) }}
              </div>
              <div class="text-sm text-gray-500 mt-1">Prix actuel</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Statistiques -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Prix actuel</div>
            <div class="text-xl font-bold text-[#35A7FF]">
              {{ formatCurrency(crypto.price_eur) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Market Cap</div>
            <div class="text-xl font-bold text-[#38618C]">
              {{ formatLargeNumber(crypto.market_cap) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Volume 24h</div>
            <div class="text-xl font-bold text-[#38618C]">
              {{ formatLargeNumber(crypto.volume_24h) }}
            </div>
          </CardContent>
        </Card>

        <Card 
          class="border-gray-200 transition-colors"
          :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'hover:border-[#01FF19]' : 'hover:border-[#FF5964]'"
        >
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Variation 24h</div>
            <div 
              class="text-xl font-bold"
              :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
            >
              {{ formatPercentage(crypto.change_24h_pct) }}%
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Graphique d'évolution -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📈 Évolution du Prix (30 jours)
            </CardTitle>
            <div class="flex gap-2">
              <Badge class="bg-[#35A7FF] text-white">30J</Badge>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="!chartData" class="h-[400px] flex items-center justify-center">
            <div class="text-center text-gray-500">
              <div class="text-6xl mb-4">📊</div>
              <div>Aucune donnée historique disponible</div>
              <Button 
                @click="fetchHistoricalData" 
                class="mt-4 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              >
                Charger l'historique
              </Button>
            </div>
          </div>
          <div v-else class="h-[400px]">
            <Line :data="chartData" :options="chartOptions" />
          </div>
          
          <!-- Résumé des variations -->
          <div v-if="history.length" class="grid grid-cols-3 gap-4 mt-6">
            <Card class="border-gray-200">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">24h</div>
                <div 
                  class="text-lg font-bold"
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}%
                </div>
              </CardContent>
            </Card>
            
            <Card class="border-gray-200">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">7 jours</div>
                <div 
                  class="text-lg font-bold"
                  :class="get7DayChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(get7DayChange) }}%
                </div>
              </CardContent>
            </Card>
            
            <Card class="border-gray-200">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">30 jours</div>
                <div 
                  class="text-lg font-bold"
                  :class="get30DayChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(get30DayChange) }}%
                </div>
              </CardContent>
            </Card>
          </div>
        </CardContent>
      </Card>

      <!-- Informations détaillées -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              ℹ️ Informations
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">Nom complet</span>
              <span class="font-semibold text-[#38618C]">{{ crypto.nom }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Symbole</span>
              <Badge class="bg-[#35A7FF] text-white font-mono">
                {{ String(crypto.symbole || crypto.symbol || '').toUpperCase() }}
              </Badge>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Catégorie</span>
              <span class="font-semibold text-[#38618C]">{{ crypto.categorie || 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-500">Site web</span>
              <a 
                v-if="crypto.site_web"
                :href="crypto.site_web" 
                target="_blank"
                class="text-[#35A7FF] hover:underline"
              >
                Visiter →
              </a>
              <span v-else class="text-gray-400">N/A</span>
            </div>
          </CardContent>
        </Card>

        <!-- Données de marché -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📊 Données de Marché
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">Prix actuel</span>
              <span class="font-bold text-[#35A7FF]">{{ formatCurrency(crypto.price_eur) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Market Cap</span>
              <span class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Volume 24h</span>
              <span class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Variation 24h</span>
              <Badge 
                :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                class="text-white"
              >
                {{ formatPercentage(crypto.change_24h_pct) }}%
              </Badge>
            </div>
            <div class="flex justify-between text-xs">
              <span class="text-gray-500">Dernière maj</span>
              <span class="text-gray-600">
                {{ crypto.updated_at ? new Date(crypto.updated_at).toLocaleDateString('fr-FR', {
                  year: 'numeric',
                  month: 'short',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                }) : 'N/A' }}
              </span>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Mes positions -->
      <Card>
        <CardHeader>
          <CardTitle class="text-[#38618C]">My Positions</CardTitle>
        </CardHeader>
        <CardContent>
          <!-- Loading state -->
          <div v-if="loadingPositions" class="h-32 flex items-center justify-center">
            <div class="animate-pulse text-gray-500">
              Loading positions...
            </div>
          </div>
          
          <!-- No position state -->
          <div 
            v-else-if="!positions || Number(positions.quantity || 0) <= 0" 
            class="h-32 bg-gray-50 rounded-lg flex items-center justify-center border border-gray-200"
          >
            <div class="text-center text-gray-500">
              <div class="text-3xl mb-2">💡</div>
              <div>You don't own this crypto yet</div>
              <Button 
                @click="buyCrypto" 
                class="mt-4 bg-[#01FF19] hover:bg-[#01FF19]/90 text-white"
              >
                Buy now
              </Button>
            </div>
          </div>
          
          <!-- Position details -->
          <div v-else class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <Card class="border-gray-200">
                <CardContent class="p-4">
                  <div class="text-sm text-gray-500">Quantity Held</div>
                  <div class="text-xl font-bold text-[#38618C] mt-1">
                    {{ Number(positions.quantity).toFixed(8) }}
                    <span class="text-sm font-normal">
                      {{ crypto.symbol || crypto.symbole || '' }}
                    </span>
                  </div>
                </CardContent>
              </Card>
              
              <Card class="border-gray-200">
                <CardContent class="p-4">
                  <div class="text-sm text-gray-500">Current Value</div>
                  <div class="text-xl font-bold text-[#38618C] mt-1">
                    {{ formatCurrency(getPositionValue(positions)) }}
                  </div>
                </CardContent>
              </Card>
              
              <Card class="border-gray-200">
                <CardContent class="p-4">
                  <div class="text-sm text-gray-500">Profit / Loss</div>
                  <div
                    class="text-xl font-bold mt-1"
                    :class="getProfitLoss(positions) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                  >
                    {{ formatCurrency(getProfitLoss(positions)) }}
                  </div>
                </CardContent>
              </Card>
            </div>
            
            <!-- Transactions history -->
            <div v-if="positions.transactions && positions.transactions.length" class="mt-6">
              <div class="text-sm font-medium text-gray-500 mb-3">Transaction History</div>
              <div class="space-y-2">
                <div 
                  v-for="(transaction, index) in positions.transactions" 
                  :key="transaction.id || index"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <div class="flex items-center gap-3">
                    <Badge 
                      :class="(transaction.type || '').toUpperCase() === 'ACHAT' || (transaction.type || '').toUpperCase() === 'BUY' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                      class="text-white"
                    >
                      {{ (transaction.type || '').toUpperCase() === 'ACHAT' || (transaction.type || '').toUpperCase() === 'BUY' ? 'BUY' : 'SELL' }}
                    </Badge>
                    <span class="font-mono">
                      {{ Number(transaction.quantity || transaction.qty || 0).toFixed(8) }}
                      {{ crypto.symbol || crypto.symbole || '' }}
                    </span>
                  </div>
                  <div class="text-right">
                    <div class="font-medium text-[#38618C]">
                      {{ formatCurrency(Number(transaction.price || transaction.unit_price || 0)) }}
                    </div>
                    <div class="text-sm text-gray-500">
                      {{ new Date(transaction.created_at).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<style scoped>
/* Styles personnalisés pour la charte graphique */
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

:deep(.hover\:bg-\[#35A7FF\]:hover) {
  background-color: #35A7FF;
}

:deep(.hover\:text-\[#35A7FF\]\/80:hover) {
  color: rgba(53, 167, 255, 0.8);
}
</style>