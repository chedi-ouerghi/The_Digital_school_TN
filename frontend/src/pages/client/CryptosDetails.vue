<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import { ArrowLeft, TrendingUp, TrendingDown, RefreshCw, Share2, Download, MoreVertical } from 'lucide-vue-next'

// Composants UI
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Progress } from '@/components/ui/progress'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

// Chart.js
import { Chart, registerables } from 'chart.js'
import { Line } from 'vue-chartjs'
Chart.register(...registerables)

// ============================================================================
// INTERFACES
// ============================================================================
interface HistoryEntry {
  timestamp: number
  date: string
  price: number
  change_24h_pct: number
}

interface CryptoData {
  id: string
  name: string
  symbol: string
  image: string
  image_url: string
  category: string
  website: string
  price_eur: string
  price: string
  market_cap: string | null
  change_24h_pct: string
  change_24h: string
}

interface PositionData {
  id: string
  symbol: string
  name: string
  quantity: number
  avg_buy_price_eur: number
  current_price_eur: number
  invested_eur: number
  current_value_eur: number
  plus_value_eur: number
  plus_value_percent: number
  transactions: Transaction[]
}

interface Transaction {
  id: string
  type: string
  quantity: number
  unit_price_eur: number
  total_eur: number
  date: string
}

interface WalletResponse {
  totalValue: number
  totalInvestment: number
  assets: PositionData[]
  balance_eur: number
}

// ============================================================================
// ÉTATS RÉACTIFS
// ============================================================================
const route = useRoute()
const router = useRouter()
const crypto = ref<CryptoData | null>(null)
const loading = ref(true)
const error = ref<string | null>(null)
const positions = ref<PositionData | null>(null)
const history = ref<HistoryEntry[]>([])
const historyLoading = ref(false)
const userBalance = ref(0)
const timeRange = ref('30d')
const chartType = ref('line')

// ============================================================================
// FONCTIONS UTILITAIRES
// ============================================================================
function makeImageUrl(path: string | undefined | null): string {
  if (!path) return ''
  const p = String(path)
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const cleanPath = p.startsWith('/storage/') ? p : `/storage/${p}`
  return `${baseUrl}${cleanPath}`
}

function formatCurrency(value: any): string {
  const num = parseFloat(value) || 0
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2 
  }).format(num)
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e12) return `${(n / 1e12).toFixed(2)}T`
  if (n >= 1e9) return `${(n / 1e9).toFixed(2)}B`
  if (n >= 1e6) return `${(n / 1e6).toFixed(2)}M`
  if (n >= 1e3) return `${(n / 1e3).toFixed(2)}K`
  return n.toFixed(2)
}

function formatPercentage(value: any): string {
  const num = parseFloat(value) || 0
  const sign = num >= 0 ? '+' : ''
  return `${sign}${num.toFixed(2)}%`
}

function formatNumber(value: any, decimals = 4): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals).replace(/\.?0+$/, '')
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// ============================================================================
// APPELS API
// ============================================================================
async function fetchCryptoDetail() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id
    const response = await api.crypto.show(id)
    crypto.value = response.data || response
    if (!crypto.value) throw new Error('Cryptocurrency not found')
  } catch (e: any) {
    error.value = e.message || 'Error loading cryptocurrency data'
    console.error('Error loading crypto details:', e)
  } finally {
    loading.value = false
  }
}

async function fetchPositions() {
  if (!crypto.value) return
  try {
    const response: WalletResponse = await api.wallet.list()
    userBalance.value = response.balance_eur || 0
    const asset = response.assets?.find(a => a.symbol === crypto.value!.symbol)
    positions.value = asset || null
  } catch (e: any) {
    console.error('Error loading positions:', e)
    positions.value = null
  }
}

async function fetchHistoricalData() {
  if (!crypto.value?.id) return
  historyLoading.value = true
  try {
    const response = await api.crypto.history(crypto.value.id)
    if (response.history && Array.isArray(response.history)) {
      history.value = response.history
    } else if (Array.isArray(response)) {
      history.value = response
    } else if (response.data?.history) {
      history.value = response.data.history
    } else if (response.data && Array.isArray(response.data)) {
      history.value = response.data
    }
  } catch (e: any) {
    console.warn('Historical data not available:', e.message)
    history.value = []
  } finally {
    historyLoading.value = false
  }
}

async function loadAllData() {
  await fetchCryptoDetail()
  if (crypto.value) {
    await Promise.all([fetchPositions(), fetchHistoricalData()])
  }
}

// ============================================================================
// LIFECYCLE & WATCHERS
// ============================================================================
onMounted(loadAllData)
watch(() => route.params.id, loadAllData)

// ============================================================================
// COMPUTED PROPERTIES
// ============================================================================
const currentPrice = computed(() => parseFloat(crypto.value?.price_eur || '0'))
const marketCap = computed(() => parseFloat(crypto.value?.market_cap || '0'))
const dailyChange = computed(() => parseFloat(crypto.value?.change_24h_pct || '0'))

const get7DayChange = computed(() => {
  if (history.value.length < 7) return 0
  const now = history.value[history.value.length - 1]?.price || 0
  const before = history.value[Math.max(0, history.value.length - 7)]?.price || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

const positionValue = computed(() => positions.value?.current_value_eur || 0)
const investedValue = computed(() => positions.value?.invested_eur || 0)
const profitLoss = computed(() => positionValue.value - investedValue.value)
const profitLossPercentage = computed(() => {
  if (investedValue.value === 0) return 0
  return (profitLoss.value / investedValue.value) * 100
})

// ============================================================================
// CONFIGURATION DU GRAPHIQUE
// ============================================================================
const filteredHistory = computed(() => {
  if (!history.value.length) return []
  const now = new Date().getTime()
  let cutoff = now
  switch (timeRange.value) {
    case '1d': cutoff -= 24 * 60 * 60 * 1000; break
    case '7d': cutoff -= 7 * 24 * 60 * 60 * 1000; break
    case '30d': cutoff -= 30 * 24 * 60 * 60 * 1000; break
    case '90d': cutoff -= 90 * 24 * 60 * 60 * 1000; break
    default: return history.value
  }
  return history.value.filter(entry => entry.timestamp >= cutoff)
})

const chartData = computed(() => {
  if (!filteredHistory.value.length) return null
  
  const isPositive = dailyChange.value >= 0
  const lineColor = isPositive ? '#22c55e' : '#ef4444'
  const fillColor = isPositive ? 'rgba(34, 197, 94, 0.05)' : 'rgba(239, 68, 68, 0.05)'
  
  const labels = filteredHistory.value.map(entry => {
    const date = new Date(entry.timestamp)
    switch (timeRange.value) {
      case '1d': return date.toLocaleTimeString('en-US', { hour: 'numeric' })
      case '7d': return date.toLocaleDateString('en-US', { weekday: 'short' })
      default: return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }
  })
  
  return {
    labels,
    datasets: [{
      label: 'Price',
      data: filteredHistory.value.map(entry => entry.price),
      borderColor: lineColor,
      backgroundColor: fillColor,
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointRadius: 0,
      pointHoverRadius: 4,
      pointBackgroundColor: lineColor,
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { grid: { display: false }, ticks: { color: '#6b7280', font: { size: 11 } } },
    y: {
      position: 'right',
      grid: { color: 'rgba(107, 114, 128, 0.1)' },
      ticks: { color: '#6b7280', font: { size: 11 } }
    }
  },
  interaction: { intersect: false, mode: 'index' }
}

// ============================================================================
// ACTIONS UTILISATEUR
// ============================================================================
function goBack() {
  router.push('/dashboard/cryptos')
}

function goToBuy() {
  if (crypto.value) {
    router.push(`/dashboard/cryptos?buy=${crypto.value.id}`)
  }
}

function refreshData() {
  loadAllData()
}

function shareCrypto() {
  if (navigator.share && crypto.value) {
    navigator.share({
      title: `${crypto.value.name} (${crypto.value.symbol})`,
      text: `Current price: ${formatCurrency(crypto.value.price_eur)}`,
      url: window.location.href
    })
  }
}

function exportData() {
  // Implementation for data export
  console.log('Export data')
}
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-gray-900">
    <!-- Header Navigation -->
    <div class="sticky top-0 z-10 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex items-center justify-between">
          <div class="flex items-center gap-4">
            <Button 
              variant="ghost" 
              size="sm"
              class="text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800"
              @click="goBack"
            >
              <ArrowLeft class="w-4 h-4 mr-1" />
              Back
            </Button>
            <div v-if="crypto" class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-full overflow-hidden">
                <img 
                  :src="makeImageUrl(crypto.image || crypto.image_url)"
                  :alt="crypto.name"
                  class="w-full h-full object-cover"
                  @error="(e) => e.target.style.display = 'none'"
                />
              </div>
              <div>
                <div class="font-semibold text-gray-900 dark:text-white">{{ crypto.name }}</div>
                <div class="text-sm text-gray-500 dark:text-gray-400">{{ crypto.symbol.toUpperCase() }}</div>
              </div>
            </div>
          </div>
          
          <div class="flex items-center gap-2">
            <Button 
              variant="ghost" 
              size="sm"
              class="text-gray-600 dark:text-gray-300"
              @click="refreshData"
              :disabled="loading"
            >
              <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            </Button>
            <Button 
              variant="ghost" 
              size="sm"
              class="text-gray-600 dark:text-gray-300"
              @click="shareCrypto"
              v-if="crypto"
            >
              <Share2 class="w-4 h-4" />
            </Button>
            <Button 
              variant="ghost" 
              size="sm"
              class="text-gray-600 dark:text-gray-300"
              @click="exportData"
            >
              <Download class="w-4 h-4" />
            </Button>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Loading State -->
      <div v-if="loading" class="space-y-6">
        <div class="h-20 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse"></div>
        <div class="h-[400px] bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse"></div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          <div class="h-48 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse"></div>
          <div class="h-48 bg-gray-100 dark:bg-gray-800 rounded-lg animate-pulse"></div>
        </div>
      </div>

      <!-- Error State -->
      <Alert v-else-if="error" variant="destructive" class="mb-6">
        <AlertDescription class="flex items-center justify-between">
          <span>{{ error }}</span>
          <Button size="sm" @click="loadAllData">Retry</Button>
        </AlertDescription>
      </Alert>

      <!-- Main Content -->
      <div v-else-if="crypto" class="space-y-6">
        <!-- Price Header -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Price & Change -->
            <div>
              <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(currentPrice) }}</div>
              <div class="flex items-center gap-2 mt-2">
                <TrendingUp v-if="dailyChange >= 0" class="w-4 h-4 text-green-500" />
                <TrendingDown v-else class="w-4 h-4 text-red-500" />
                <span :class="dailyChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ formatPercentage(dailyChange) }}
                </span>
                <span class="text-gray-500 dark:text-gray-400 text-sm">(24h)</span>
              </div>
            </div>

            <!-- Market Stats -->
            <div class="space-y-2">
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">Market Cap</span>
                <span class="font-medium text-gray-900 dark:text-white">€{{ formatLargeNumber(marketCap) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500 dark:text-gray-400">7D Change</span>
                <span :class="get7DayChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ formatPercentage(get7DayChange) }}
                </span>
              </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex gap-3">
              <Button 
                class="flex-1 bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900"
                @click="goToBuy"
              >
                Buy
              </Button>
              <Button 
                variant="outline"
                class="flex-1 border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300"
                :disabled="!positions || positions.quantity <= 0"
              >
                Sell
              </Button>
            </div>
          </div>
        </div>

        <!-- Chart Section -->
        <Card class="border-gray-200 dark:border-gray-700">
          <CardContent class="p-6">
            <!-- Chart Header -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
              <div>
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Price Chart</h3>
                <div class="text-sm text-gray-500 dark:text-gray-400">
                  {{ crypto.symbol.toUpperCase() }}/EUR
                </div>
              </div>
              <div class="flex gap-2">
                <Button
                  v-for="range in ['1d', '7d', '30d', '90d']"
                  :key="range"
                  size="sm"
                  :variant="timeRange === range ? 'default' : 'outline'"
                  @click="timeRange = range"
                  class="text-xs"
                >
                  {{ range }}
                </Button>
              </div>
            </div>

            <!-- Chart -->
            <div class="h-[350px]">
              <div v-if="historyLoading" class="h-full flex items-center justify-center">
                <div class="text-center">
                  <div class="w-12 h-12 border-2 border-gray-300 border-t-gray-600 dark:border-gray-600 dark:border-t-gray-300 rounded-full animate-spin mx-auto mb-4"></div>
                  <p class="text-gray-500 dark:text-gray-400">Loading chart...</p>
                </div>
              </div>
              <div v-else-if="!chartData" class="h-full flex items-center justify-center">
                <div class="text-center text-gray-500 dark:text-gray-400">
                  <div class="text-4xl mb-4">📊</div>
                  <p>No historical data available</p>
                </div>
              </div>
              <Line v-else :data="chartData" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Portfolio & Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Portfolio Section -->
          <Card class="border-gray-200 dark:border-gray-700">
            <CardContent class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Your Holdings</h3>
              
              <div v-if="!positions || positions.quantity <= 0" class="text-center py-12">
                <div class="w-16 h-16 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center mx-auto mb-4">
                  <span class="text-2xl">📈</span>
                </div>
                <p class="text-gray-600 dark:text-gray-400 mb-6">You don't own {{ crypto.name }} yet</p>
                <Button 
                  class="bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900"
                  @click="goToBuy"
                >
                  Buy {{ crypto.symbol.toUpperCase() }}
                </Button>
              </div>

              <div v-else class="space-y-6">
                <!-- Holdings Summary -->
                <div class="space-y-4">
                  <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400">Quantity</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                      {{ formatNumber(positions.quantity, 8) }} {{ crypto.symbol.toUpperCase() }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400">Current Value</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                      {{ formatCurrency(positionValue) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400">Avg. Buy Price</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                      {{ formatCurrency(positions.avg_buy_price_eur) }}
                    </span>
                  </div>
                  <div class="flex justify-between items-center">
                    <span class="text-gray-500 dark:text-gray-400">Profit/Loss</span>
                    <span :class="profitLoss >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                      {{ formatCurrency(profitLoss) }} ({{ formatPercentage(profitLossPercentage) }})
                    </span>
                  </div>
                </div>

                <!-- Transaction History -->
                <div>
                  <div class="flex items-center justify-between mb-4">
                    <h4 class="font-medium text-gray-900 dark:text-white">Recent Transactions</h4>
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ positions.transactions.length }} total</span>
                  </div>
                  
                  <div class="space-y-3">
                    <div 
                      v-for="(tx, index) in positions.transactions.slice(0, 3)"
                      :key="tx.id || index"
                      class="flex items-center justify-between py-3 border-b border-gray-100 dark:border-gray-800 last:border-0"
                    >
                      <div class="flex items-center gap-3">
                        <div 
                          class="w-8 h-8 rounded-full flex items-center justify-center"
                          :class="tx.type === 'ACHAT' ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20'"
                        >
                          <span class="text-xs font-medium" :class="tx.type === 'ACHAT' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                            {{ tx.type === 'ACHAT' ? 'B' : 'S' }}
                          </span>
                        </div>
                        <div>
                          <div class="font-medium text-gray-900 dark:text-white">
                            {{ tx.type === 'ACHAT' ? 'Buy' : 'Sell' }} {{ crypto.symbol.toUpperCase() }}
                          </div>
                          <div class="text-xs text-gray-500 dark:text-gray-400">
                            {{ formatDate(tx.date) }}
                          </div>
                        </div>
                      </div>
                      <div class="text-right">
                        <div class="font-medium text-gray-900 dark:text-white">
                          {{ formatCurrency(tx.total_eur) }}
                        </div>
                        <div class="text-xs text-gray-500 dark:text-gray-400">
                          @ {{ formatCurrency(tx.unit_price_eur) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Market Info Section -->
          <Card class="border-gray-200 dark:border-gray-700">
            <CardContent class="p-6">
              <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Market Information</h3>
              
              <div class="space-y-4">
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800">
                  <span class="text-gray-500 dark:text-gray-400">Symbol</span>
                  <code class="font-mono text-gray-900 dark:text-white bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded">
                    {{ crypto.symbol.toUpperCase() }}
                  </code>
                </div>
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800">
                  <span class="text-gray-500 dark:text-gray-400">Category</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ crypto.category || 'Crypto' }}</span>
                </div>
                <!-- Safe website display -->
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800">
                  <span class="text-gray-500 dark:text-gray-400">Website</span>
                  <a 
                    v-if="crypto.website && crypto.website.startsWith('http')"
                    :href="crypto.website"
                    target="_blank"
                    class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm"
                  >
                    Visit
                  </a>
                  <span v-else class="text-gray-400 dark:text-gray-500 text-sm">N/A</span>
                </div>
                <div class="flex justify-between items-center py-3">
                  <span class="text-gray-500 dark:text-gray-400">24h Volume</span>
                  <span class="font-medium text-gray-900 dark:text-white">€{{ formatLargeNumber(marketCap * 0.1) }}</span>
                </div>
              </div>

              <!-- Performance Stats -->
              <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Performance</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                      {{ formatPercentage(dailyChange) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">24h</div>
                  </div>
                  <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white">
                      {{ formatPercentage(get7DayChange) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">7D</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Additional Market Data -->
        <Card class="border-gray-200 dark:border-gray-700">
          <CardContent class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Advanced Statistics</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Market Dominance</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">0.5%</div>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Circulating Supply</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">19.5M</div>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Volume/Market Cap</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">0.08</div>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">All Time High</div>
                <div class="text-xl font-bold text-green-600 dark:text-green-400">
                  {{ formatCurrency(Math.max(...(history.map(h => h.price) || [currentPrice]))) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </main>
  </div>
</template>

<style scoped>
/* Custom animations */
@keyframes fadeIn {
  from { opacity: 0; transform: translateY(10px); }
  to { opacity: 1; transform: translateY(0); }
}

.animate-fade-in {
  animation: fadeIn 0.3s ease-out;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 150ms;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #555;
}

.dark ::-webkit-scrollbar-track {
  background: #374151;
}

.dark ::-webkit-scrollbar-thumb {
  background: #6b7280;
}

.dark ::-webkit-scrollbar-thumb:hover {
  background: #9ca3af;
}
</style>