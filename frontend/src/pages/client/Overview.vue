<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../../services/api'
import { useRouter } from 'vue-router'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line } from 'vue-chartjs'

// Import des composants shadcn-vue
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const router = useRouter()
const wallet = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

const chartData = ref<number[]>([])
const chartLabels = ref<string[]>([])

function formatCurrency(value: any) {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatNumber(value: any, decimals = 8) {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Calcul des statistiques
const stats = computed(() => {
  if (!wallet.value) return {
    totalValue: 0,
    profitAmount: 0,
    profitPercent: 0,
    assetsCount: 0,
    soldeDisponible: 0,
    totalBalance: 0,
    positions: []
  }

  const assets = wallet.value.crypto_wallet_assets || []
  let totalValue = 0
  let invested = 0
  let assetsCount = 0
  const positions: any[] = []

  assets.forEach((asset: any) => {
    const crypto = asset.cryptomoney || {}
    const quantity = Number(asset.quantity || 0)
    const currentPrice = Number(crypto.price_eur || 0)
    const avgBuyPrice = Number(asset.average_buy_price || 0)

    if (quantity > 0) {
      assetsCount++
      const currentValue = quantity * currentPrice
      const investedValue = quantity * avgBuyPrice
      
      totalValue += currentValue
      invested += investedValue

      positions.push({
        id: asset.id,
        cryptoId: crypto.id,
        name: crypto.name,
        symbol: crypto.symbol,
        image_url: crypto.image_url,
        quantity,
        currentPrice,
        avgBuyPrice,
        currentValue,
        pnl: currentValue - investedValue,
        pnlPercent: avgBuyPrice > 0 ? ((currentPrice - avgBuyPrice) / avgBuyPrice) * 100 : 0,
        change24h: Number(crypto.change_24h_pct || 0),
        created_at: asset.created_at
      })
    }
  })

  const profitAmount = totalValue - invested
  const profitPercent = invested > 0 ? (profitAmount / invested) * 100 : 0
  const soldeDisponible = Number(wallet.value.balance_eur || 0)

  return {
    totalValue,
    profitAmount,
    profitPercent,
    assetsCount,
    soldeDisponible,
    totalBalance: soldeDisponible + totalValue,
    positions: positions.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())
  }
})

// Première crypto pour afficher les stats
const selectedCrypto = computed(() => {
  return stats.value.positions.length > 0 ? stats.value.positions[0] : null
})

// Stats de la crypto sélectionnée
const cryptoStats = computed(() => {
  if (!selectedCrypto.value) {
    return {
      change24h: 0,
      high24h: 0,
      low24h: 0,
      volume24h: 0
    }
  }

  const price = selectedCrypto.value.currentPrice
  const changePct = selectedCrypto.value.change24h
  
  return {
    change24h: changePct,
    high24h: price * (1 + Math.abs(changePct) / 100),
    low24h: price * (1 - Math.abs(changePct) / 100),
    volume24h: 0 // À récupérer de l'API si nécessaire
  }
})

// Générer les données du graphique
async function generateChartData() {
  try {
    // Créer des données simulées basées sur les transactions
    const transactions = wallet.value.transactions || []
    
    if (transactions.length === 0) {
      chartLabels.value = []
      chartData.value = []
      return
    }

    // Calculer la valeur cumulée par date
    const valueByDate: { [key: string]: number } = {}
    
    transactions.forEach((tx: any) => {
      const date = new Date(tx.created_at).toLocaleDateString('fr-FR')
      const crypto = tx.cryptomoney || {}
      const amount = Number(tx.total_eur || 0)
      
      if (!valueByDate[date]) {
        valueByDate[date] = 0
      }
      
      if (tx.type === 'ACHAT') {
        valueByDate[date] += amount
      } else {
        valueByDate[date] -= amount
      }
    })

    // Convertir en arrays pour le graphique
    const sortedDates = Object.keys(valueByDate).sort()
    let cumulativeValue = 0
    
    chartLabels.value = sortedDates.map(date => new Date(date).toLocaleDateString('fr-FR', { day: '2-digit', month: '2-digit' }))
    chartData.value = sortedDates.map(date => {
      cumulativeValue += valueByDate[date]
      return cumulativeValue
    })

    // Ajouter le point actuel
    chartLabels.value.push('Aujourd\'hui')
    chartData.value.push(stats.value.totalValue)

  } catch (err) {
    console.error('Erreur lors de la génération du graphique:', err)
  }
}

async function fetchData() {
  loading.value = true
  error.value = null
  try {
    const response = await api.wallet.list()
    wallet.value = response?.wallet || {}

    // Générer les données du graphique
    await generateChartData()

  } catch (e: any) {
    error.value = e.message || 'Erreur lors du chargement des données'
    console.error('Erreur:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

function goBuy() {
  router.push('/dashboard/cryptos')
}

function goToDetails(cryptoId: string) {
  router.push(`/dashboard/portfolio/crypto/${cryptoId}`)
}

// Chart options
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
    label: 'Valeur du wallet',
    data: chartData.value,
    fill: true,
    borderColor: '#35A7FF',
    backgroundColor: 'rgba(53, 167, 255, 0.1)',
    tension: 0.4,
    pointBackgroundColor: '#01FF19',
    pointBorderColor: '#01FF19',
    pointRadius: 5
  }]
}))
</script>

<template>
  <div class="space-y-6">
    <!-- Header with Total Balance -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <div class="text-sm text-gray-500 mb-1">Total Balance</div>
        <div class="text-3xl sm:text-4xl font-bold text-[#38618C]">
          {{ formatCurrency(stats.totalBalance) }}
          <span class="text-sm font-normal text-gray-500 ml-2">EUR</span>
        </div>
      </div>
      <Button
        @click="goBuy"
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold w-full sm:w-auto"
      >
        + Buy Cryptos
      </Button>
    </div>

    <!-- Loading / Error -->
    <Card v-if="loading">
      <CardContent class="p-8 text-center text-gray-600">
        <div class="animate-pulse">⏳ Loading...</div>
      </CardContent>
    </Card>

    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-8 text-center text-[#FF5964]">
        <div class="text-2xl mb-2">❌</div>
        <div>{{ error }}</div>
      </CardContent>
    </Card>

    <div v-else class="space-y-6">
      <!-- Wallet Stats -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Total Wallet Value -->
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Wallet Value</div>
            <div class="text-2xl font-bold text-[#35A7FF]">
              {{ formatCurrency(stats.totalValue) }}
            </div>
            <div class="text-xs mt-2 flex items-center gap-2">
              <Badge
                :class="stats.profitPercent >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                class="text-white"
              >
                {{ stats.profitPercent >= 0 ? '+' : '' }}{{ stats.profitPercent.toFixed(2) }}%
              </Badge>
              <span class="text-gray-500">
                {{ stats.profitPercent >= 0 ? '+' : '' }}{{ formatCurrency(stats.profitAmount) }}
              </span>
            </div>
          </CardContent>
        </Card>

        <!-- Number of Cryptos -->
        <Card class="border-gray-200 hover:border-[#38618C] transition-colors bg-gradient-to-br from-[#38618C]/10 to-transparent">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Cryptos Held</div>
            <div class="text-2xl font-bold text-[#38618C]">{{ stats.assetsCount }}</div>
            <div class="text-xs text-gray-500 mt-2">{{ stats.positions.length }} position{{ stats.positions.length !== 1 ? 's' : '' }}</div>
          </CardContent>
        </Card>

        <!-- Available Balance -->
        <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Available Balance</div>
            <div class="text-2xl font-bold text-[#01FF19]">
              {{ formatCurrency(stats.soldeDisponible) }}
            </div>
            <div class="text-xs text-gray-500 mt-2">To Invest</div>
          </CardContent>
        </Card>
      </div>

      <!-- Main Section: Chart + Summary Panel -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <Card class="lg:col-span-2">
          <CardHeader class="pb-3">
            <div class="space-y-3">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div v-if="selectedCrypto" class="flex items-center gap-2">
                    <div class="h-10 w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img
                        v-if="selectedCrypto.image_url"
                        :src="selectedCrypto.image_url"
                        :alt="selectedCrypto.name"
                        class="h-10 w-10 rounded-full object-cover"
                        @error="(e) => e.target.style.display = 'none'"
                      />
                      <div v-if="!selectedCrypto.image_url" class="text-lg">💎</div>
                    </div>
                    <div>
                      <div class="text-lg font-semibold text-[#38618C]">
                        {{ selectedCrypto.name }} / EUR
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ String(selectedCrypto.symbol).toUpperCase() }}
                      </div>
                    </div>
                  </div>
                  <div v-else class="text-lg font-semibold text-[#38618C]">
                    Portfolio / EUR
                  </div>
                  <Badge
                    :class="stats.profitPercent >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                    class="text-white font-semibold"
                  >
                    {{ stats.profitPercent >= 0 ? '+' : '' }}{{ stats.profitPercent.toFixed(2) }}%
                  </Badge>
                </div>
                <Button
                  size="sm"
                  variant="outline"
                  @click="fetchData"
                  class="border-[#35A7FF] text-[#35A7FF]"
                >
                  🔄
                </Button>
              </div>

              <!-- Stats Grid -->
              <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                <div>
                  <div class="text-gray-500 mb-1">24h Change</div>
                  <div
                    class="font-semibold"
                    :class="cryptoStats.change24h >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                  >
                    {{ cryptoStats.change24h >= 0 ? '+' : '' }}{{ cryptoStats.change24h.toFixed(2) }}%
                  </div>
                </div>
                <div>
                  <div class="text-gray-500 mb-1">24h High</div>
                  <div class="font-semibold text-[#38618C]">
                    {{ formatCurrency(cryptoStats.high24h) }}
                  </div>
                </div>
                <div>
                  <div class="text-gray-500 mb-1">24h Low</div>
                  <div class="font-semibold text-[#38618C]">
                    {{ formatCurrency(cryptoStats.low24h) }}
                  </div>
                </div>
                <div>
                  <div class="text-gray-500 mb-1">24h Volume</div>
                  <div class="font-semibold text-[#38618C]">
                    {{ formatCurrency(cryptoStats.volume24h) }}
                  </div>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="chartData.length === 0" class="h-[350px] flex items-center justify-center text-gray-500">
              <div class="text-center">
                <div class="text-4xl mb-2">📊</div>
                <div>No historical data available</div>
              </div>
            </div>
            <div v-else class="h-[350px]">
              <Line :data="chartDataset" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Summary Panel (1/3) -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">Wallet Summary</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3 pb-4 border-b border-gray-200">
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Total Value</span>
                <span class="font-semibold text-[#38618C]">{{ formatCurrency(stats.totalValue) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-sm text-gray-500">Invested Amount</span>
                <span class="font-semibold text-[#38618C]">
                  {{ formatCurrency(stats.totalValue - stats.profitAmount) }}
                </span>
              </div>
              <div class="flex justify-between items-center pt-2 border-t border-gray-100">
                <span class="text-sm text-gray-500">Profit/Loss</span>
                <div class="text-right">
                  <div
                    class="font-bold text-lg"
                    :class="stats.profitAmount >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                  >
                    {{ formatCurrency(stats.profitAmount) }}
                  </div>
                  <Badge
                    :class="stats.profitPercent >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                    class="text-white text-xs"
                  >
                    {{ stats.profitPercent >= 0 ? '+' : '' }}{{ stats.profitPercent.toFixed(2) }}%
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Held Assets -->
            <div class="space-y-3">
              <div class="text-sm font-semibold text-[#38618C]">Held Assets</div>
              <div v-if="stats.positions.length === 0" class="text-sm text-gray-500 text-center py-6">
                No assets in your wallet
              </div>
              <div v-else class="space-y-2 max-h-[250px] overflow-y-auto">
                <div
                  v-for="position in stats.positions"
                  :key="position.id"
                  class="flex items-center justify-between p-3 rounded-lg border border-gray-200 hover:border-[#35A7FF] hover:bg-gray-50 transition-colors cursor-pointer"
                  @click="goToDetails(position.cryptoId)"
                >
                  <div class="flex items-center gap-2 flex-1 min-w-0">
                    <div class="h-8 w-8 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img
                        v-if="position.image_url"
                        :src="position.image_url"
                        :alt="position.name"
                        class="h-8 w-8 rounded-full object-cover"
                        @error="(e) => e.target.style.display = 'none'"
                      />
                      <div v-if="!position.image_url" class="text-sm">💎</div>
                    </div>
                    <div class="min-w-0">
                      <div class="text-sm font-semibold text-[#38618C] truncate">
                        {{ position.name }}
                      </div>
                      <div class="text-xs text-gray-500 font-mono">
                        {{ formatNumber(position.quantity, 8) }} {{ String(position.symbol).toUpperCase() }}
                      </div>
                    </div>
                  </div>
                  <div class="text-right ml-2">
                    <div class="text-sm font-semibold text-[#38618C]">
                      {{ formatCurrency(position.currentValue) }}
                    </div>
                    <Badge
                      :class="position.change24h >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                      class="text-white text-xs"
                    >
                      {{ position.change24h >= 0 ? '+' : '' }}{{ position.change24h.toFixed(2) }}%
                    </Badge>
                  </div>
                </div>
              </div>
            </div>

            <Button
              class="w-full bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
              @click="goBuy"
            >
              + Buy Cryptos
            </Button>
          </CardContent>
        </Card>
      </div>

      <!-- Positions Table -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-[#38618C]">My Positions</CardTitle>
        </CardHeader>
        <CardContent>
          <div v-if="stats.positions.length === 0" class="text-center py-8 text-gray-500">
            <div class="text-4xl mb-2">💼</div>
            <div>You have no open positions</div>
          </div>
          <div v-else class="overflow-x-auto">
            <table class="w-full text-sm">
              <thead class="border-b border-gray-200">
                <tr class="text-gray-500">
                  <th class="text-left py-3 font-semibold">Crypto</th>
                  <th class="text-right py-3 font-semibold">Quantity</th>
                  <th class="text-right py-3 font-semibold">Buy Price</th>
                  <th class="text-right py-3 font-semibold">Current Price</th>
                  <th class="text-right py-3 font-semibold">Value</th>
                  <th class="text-right py-3 font-semibold">P&L</th>
                  <th class="text-center py-3 font-semibold">Action</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="position in stats.positions"
                  :key="position.id"
                  class="border-b border-gray-100 hover:bg-gray-50"
                >
                  <td class="py-3">
                    <div class="flex items-center gap-2">
                      <div class="h-8 w-8 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                        <img
                          v-if="position.image_url"
                          :src="position.image_url"
                          :alt="position.name"
                          class="h-8 w-8 rounded-full object-cover"
                          @error="(e) => e.target.style.display = 'none'"
                        />
                        <div v-if="!position.image_url" class="text-xs">💎</div>
                      </div>
                      <div>
                        <div class="font-semibold text-[#38618C]">{{ position.name }}</div>
                        <div class="text-xs text-gray-500">{{ String(position.symbol).toUpperCase() }}</div>
                      </div>
                    </div>
                  </td>
                  <td class="py-3 text-right text-[#38618C] font-mono">
                    {{ formatNumber(position.quantity, 8) }}
                  </td>
                  <td class="py-3 text-right text-[#38618C]">
                    {{ formatCurrency(position.avgBuyPrice) }}
                  </td>
                  <td class="py-3 text-right text-[#38618C]">
                    {{ formatCurrency(position.currentPrice) }}
                  </td>
                  <td class="py-3 text-right font-semibold text-[#35A7FF]">
                    {{ formatCurrency(position.currentValue) }}
                  </td>
                  <td class="py-3 text-right">
                    <div
                      class="font-bold"
                      :class="position.pnl >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                    >
                      {{ position.pnl >= 0 ? '+' : '' }}{{ formatCurrency(position.pnl) }}
                    </div>
                    <Badge
                      :class="position.pnlPercent >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                      class="text-white text-xs"
                    >
                      {{ position.pnlPercent >= 0 ? '+' : '' }}{{ position.pnlPercent.toFixed(2) }}%
                    </Badge>
                  </td>
                  <td class="py-3 text-center">
                    <Button
                      size="sm"
                      variant="outline"
                      class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white text-xs"
                      @click="goToDetails(position.cryptoId)"
                    >
                      Details
                    </Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </CardContent>
      </Card>
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

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}
</style>
