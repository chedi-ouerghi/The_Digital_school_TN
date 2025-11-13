<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line } from 'vue-chartjs'

// Import des composants shadcn-vue
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const route = useRoute()
const router = useRouter()
const position = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const chartData = ref<number[]>([])
const chartLabels = ref<string[]>([])

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Données calculées
const quantity = computed(() => Number(position.value?.quantity ?? 0))
const currentPrice = computed(() => Number(position.value?.crypto?.price_eur ?? 0))
const currentValue = computed(() => quantity.value * currentPrice.value)

const avgBuyPrice = computed(() => {
  // Calculer depuis les transactions ACHAT
  const txs = transactions.value || []
  let totalCost = 0
  let totalQty = 0
  
  txs.forEach((t: any) => {
    if (t.type === 'ACHAT') {
      totalCost += Number(t.price || 0) * Number(t.quantity || 0)
      totalQty += Number(t.quantity || 0)
    }
  })
  
  return totalQty > 0 ? totalCost / totalQty : 0
})

const investedValue = computed(() => avgBuyPrice.value * quantity.value)
const profitAmount = computed(() => currentValue.value - investedValue.value)
const profitPercent = computed(() => 
  investedValue.value > 0 ? (profitAmount.value / investedValue.value) * 100 : 0
)

const transactions = computed(() => {
  const txs = position.value?.transactions ?? []
  return Array.isArray(txs) 
    ? txs.filter((t: any) => !t.cancelled_at && !t.deleted_at).sort((a, b) => 
        new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      )
    : []
})

const change24h = computed(() => Number(position.value?.crypto?.change_24h_pct ?? 0))
const volume24h = computed(() => Number(position.value?.crypto?.volume_24h ?? 0))

async function fetchPositionData() {
  loading.value = true
  error.value = null
  
  try {
    const rawId = route.params.id
    const cryptoId = Array.isArray(rawId) ? rawId[0] : String(rawId || '')
    
    // Récupérer le wallet complet avec toutes les données
    const portfolioRes = await api.wallet.list()
    const walletData = portfolioRes?.wallet || {}
    
    // Chercher l'asset dans crypto_wallet_assets (pas dans cryptomonnaies)
    const asset = walletData.crypto_wallet_assets?.find((a: any) => a.cryptomoney_id === cryptoId)
    
    if (!asset) {
      throw new Error('Position non trouvée')
    }

    // Chercher les transactions pour cette crypto
    const relatedTransactions = walletData.transactions?.filter(
      (t: any) => t.cryptomoney_id === cryptoId && !t.deleted_at
    ) || []

    position.value = {
      assetId: asset.id,
      cryptoId: asset.cryptomoney_id,
      quantity: Number(asset.quantity || 0),
      average_buy_price: Number(asset.average_buy_price || 0),
      asset,
      crypto: asset.cryptomoney || {},
      transactions: relatedTransactions,
    }

    await generateChartData()

  } catch (e: any) {
    error.value = e.message || String(e)
    console.error('Erreur lors du chargement de la position:', e)
  } finally {
    loading.value = false
  }
}

async function generateChartData() {
  // Générer un graphique basé sur les transactions
  const txs = transactions.value
  
  if (txs.length === 0) {
    chartLabels.value = []
    chartData.value = []
    return
  }

  // Créer des points basés sur les transactions (triées par date)
  const sortedTxs = [...txs].sort((a, b) => 
    new Date(a.created_at).getTime() - new Date(b.created_at).getTime()
  )

  chartLabels.value = sortedTxs.map((t: any) => 
    new Date(t.created_at).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' })
  )
  
  // Valeur cumulée après chaque transaction
  let cumulativeQty = 0
  chartData.value = sortedTxs.map((t: any) => {
    if (t.type === 'ACHAT') {
      cumulativeQty += Number(t.quantity || 0)
    } else {
      cumulativeQty -= Number(t.quantity || 0)
    }
    return cumulativeQty * currentPrice.value
  })

  // Ajouter le point actuel
  chartLabels.value.push('Aujourd\'hui')
  chartData.value.push(currentValue.value)
}

onMounted(() => {
  fetchPositionData()
})

// Options du graphique
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#38618C',
      bodyColor: '#38618C',
      borderColor: '#38618C',
      borderWidth: 1,
      callbacks: {
        label: (context: any) => `${formatCurrency(context.parsed.y)}`
      }
    }
  },
  scales: {
    x: { 
      ticks: { color: '#38618C' },
      grid: { color: '#E5E7EB' }
    },
    y: { 
      ticks: { 
        color: '#38618C',
        callback: (value: number) => formatCurrency(value)
      },
      grid: { color: '#E5E7EB' }
    }
  }
}))

const chartDataset = computed(() => ({
  labels: chartLabels.value,
  datasets: [{
    label: 'Valeur de la position',
    data: chartData.value,
    fill: true,
    borderColor: profitAmount.value >= 0 ? '#01FF19' : '#FF5964',
    backgroundColor: profitAmount.value >= 0 
      ? 'rgba(1, 255, 25, 0.1)' 
      : 'rgba(255, 89, 100, 0.1)',
    tension: 0.4,
    pointBackgroundColor: profitAmount.value >= 0 ? '#01FF19' : '#FF5964',
    pointBorderColor: profitAmount.value >= 0 ? '#01FF19' : '#FF5964',
    pointRadius: 5
  }]
}))

function goBack() {
  router.push('/dashboard/portfolio')
}

function goToTrade() {
  router.push(`/dashboard/cryptos?symbol=${position.value?.crypto?.symbol}`)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header with back button -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button 
          variant="outline"
          @click="goBack"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
        >
          ← Back
        </Button>
        <div v-if="position">
          <h1 class="text-3xl font-bold text-[#38618C]">{{ position.crypto?.name }}</h1>
          <p class="text-gray-500">Position details</p>
        </div>
      </div>
      <Button 
        v-if="position"
        @click="goToTrade"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
      >
        💱 Trade
      </Button>
    </div>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-8 text-center text-gray-600">
        <div class="animate-pulse">⏳ Loading position...</div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-8 text-center text-[#FF5964]">
        <div class="text-2xl mb-2">❌</div>
        <div>{{ error }}</div>
        <Button @click="goBack" class="mt-4">← Back to wallet</Button>
      </CardContent>
    </Card>

    <!-- Not Found State -->
    <Card v-else-if="!position">
      <CardContent class="p-8 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">Position not found</h3>
        <Button @click="goBack" class="mt-4">← Back to wallet</Button>
      </CardContent>
    </Card>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Header with image and info -->
      <Card class="border-[#35A7FF]">
        <CardContent class="p-6">
          <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <div class="h-20 w-20 rounded-full border-4 border-[#35A7FF] bg-gray-100 flex items-center justify-center flex-shrink-0">
              <img 
                v-if="position.crypto?.image_url"
                :src="position.crypto.image_url" 
                :alt="position.crypto.name"
                class="h-20 w-20 rounded-full object-cover"
                @error="(e) => e.target.style.display = 'none'"
              />
              <div v-if="!position.crypto?.image_url" class="text-3xl">💎</div>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-[#38618C]">
                  {{ position.crypto?.name }}
                </h2>
                <Badge class="bg-[#38618C] text-white">
                  {{ String(position.crypto?.symbol || '').toUpperCase() }}
                </Badge>
                <Badge 
                  :class="change24h >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white"
                >
                  {{ change24h >= 0 ? '+' : '' }}{{ change24h.toFixed(2) }}% (24h)
                </Badge>
              </div>
              <div class="text-3xl font-bold text-[#35A7FF]">
                {{ formatCurrency(currentPrice) }}
              </div>
              <div class="text-sm text-gray-500 mt-1">Current Price</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Main Statistics -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Quantity Held</div>
            <div class="text-xl font-bold text-[#38618C] font-mono">
              {{ formatNumber(quantity, 8) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">
              {{ String(position.crypto?.symbol || '').toUpperCase() }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Average Buy Price</div>
            <div class="text-xl font-bold text-[#38618C]">
              {{ formatCurrency(avgBuyPrice) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Per unit</div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Invested Value</div>
            <div class="text-xl font-bold text-[#38618C]">
              {{ formatCurrency(investedValue) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Total Invested</div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Current Value</div>
            <div class="text-xl font-bold text-[#35A7FF]">
              {{ formatCurrency(currentValue) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Market Value</div>
          </CardContent>
        </Card>
      </div>

      <!-- Profit/Loss -->
      <Card 
        class="border-2 transition-colors"
        :class="profitAmount >= 0 ? 'border-[#01FF19] bg-gradient-to-br from-[#01FF19]/5 to-transparent' : 'border-[#FF5964] bg-gradient-to-br from-[#FF5964]/5 to-transparent'"
      >
        <CardContent class="p-6">
          <div class="text-center">
            <div class="text-sm text-gray-500 mb-2">Profit / Loss</div>
            <div 
              class="text-4xl font-bold mb-2"
              :class="profitAmount >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
            >
              {{ profitAmount >= 0 ? '+' : '' }}{{ formatCurrency(profitAmount) }}
            </div>
            <Badge 
              :class="profitAmount >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
              class="text-white text-lg px-4 py-2"
            >
              {{ profitAmount >= 0 ? '+' : '' }}{{ profitPercent.toFixed(2) }}%
            </Badge>
          </div>
        </CardContent>
      </Card>

      <!-- Performance Chart -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-[#38618C]">
            Position Evolution
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="chartData.length === 0" class="h-[300px] flex items-center justify-center text-gray-500">
            No historical data yet
          </div>
          <div v-else class="h-[300px]">
            <Line 
              :data="chartDataset"
              :options="chartOptions"
            />
          </div>
        </CardContent>
      </Card>

      <!-- Transaction History -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-[#38618C]">
            Transaction History
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="transactions.length === 0" class="text-center py-8 text-gray-500">
            No transactions available
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="t in transactions" 
              :key="t.id"
              class="flex items-center justify-between p-4 border border-gray-200 rounded-lg hover:border-[#35A7FF] transition-colors"
            >
              <div class="flex items-center gap-4">
                <Badge 
                  :class="t.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white text-sm"
                >
                  {{ t.type === 'ACHAT' ? '📈 BUY' : '📉 SELL' }}
                </Badge>
                <div>
                  <div class="font-semibold text-[#38618C]">
                    {{ formatNumber(t.quantity, 8) }} {{ String(position.crypto?.symbol || '').toUpperCase() }}
                  </div>
                  <div class="text-sm text-gray-500">
                    Unit Price: {{ formatCurrency(Number(t.price)) }}
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-[#38618C]">
                  {{ formatCurrency(Number(t.total_eur)) }}
                </div>
                <div class="text-xs text-gray-500">
                  {{ new Date(t.created_at).toLocaleDateString('en-GB', { 
                    year: 'numeric', 
                    month: 'short', 
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                  }) }}
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Additional Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              Market Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">24h Volume</span>
              <span class="font-semibold text-[#38618C]">{{ formatCurrency(volume24h) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Category</span>
              <span class="font-semibold text-[#38618C]">
                {{ position.crypto?.category || 'N/A' }}
              </span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-500">Website</span>
              <a 
                v-if="position.crypto?.website"
                :href="position.crypto.website" 
                target="_blank"
                class="text-[#35A7FF] hover:underline"
              >
                Visit →
              </a>
              <span v-else class="text-gray-400">N/A</span>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              Position Summary
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">Number of Transactions</span>
              <span class="font-semibold text-[#38618C]">{{ transactions.length }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">First Purchase Date</span>
              <span class="font-semibold text-[#38618C]">
                {{ transactions.length > 0 
                  ? new Date(transactions[transactions.length - 1].created_at).toLocaleDateString('en-GB')
                  : 'N/A' 
                }}
              </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Performance</span>
              <Badge 
                :class="profitAmount >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                class="text-white"
              >
                {{ profitAmount >= 0 ? '📈 In Profit' : '📉 In Loss' }}
              </Badge>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>
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

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}
</style>