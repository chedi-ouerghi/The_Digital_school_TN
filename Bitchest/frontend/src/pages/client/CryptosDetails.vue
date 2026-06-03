<script setup lang="ts">
import { ArrowLeft, Download, ExternalLink, RefreshCw, Share2, TrendingDown, TrendingUp } from 'lucide-vue-next'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

// Composants UI
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'

// Chart.js
import type { ChartData, ChartOptions } from 'chart.js'
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
  volume: number
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
  market_cap: string
  change_24h_pct: string
  change_24h: string
  created_at: string
  updated_at: string
}

interface CryptoDetailResponse {
  crypto: {
    id: string
    symbol: string
    name: string
  }
  meta: {
    count: number
    from: string
    to: string
    days: number
  }
  history: HistoryEntry[]
}

interface Transaction {
  id: string
  crypto_id: string
  crypto_symbol: string
  crypto_name: string
  crypto_image: string
  crypto_image_url: string
  type: 'ACHAT' | 'VENTE'
  quantity: number
  price: number
  unit_price_eur: number
  total_eur: number
  date: string
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

interface WalletResponse {
  totalValue: number
  totalInvestment: number
  totalPlusValue: number
  totalPlusValuePercent: number
  assets: PositionData[]
  totalUnits: number
  buyCount: number
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
const walletTransactions = ref<Transaction[]>([])
const historyLoading = ref(false)
const timeRange = ref('30d')
const chartRenderKey = ref(0)

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

function formatDateShort(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric'
  })
}

// ============================================================================
// APPELS API
// ============================================================================
async function fetchCryptoDetail() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id as string
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

async function fetchWalletTransactions() {
  if (!crypto.value) return
  try {
    const response = await api.wallet.getTransactionsHistory()
    // Filter transactions for this specific crypto
    const transactions = response.transactions || []
    walletTransactions.value = transactions.filter((tx: Transaction) => 
      tx.crypto_id === crypto.value?.id
    )
  } catch (e: any) {
    console.error('Error loading wallet transactions:', e)
    walletTransactions.value = []
  }
}

async function fetchWalletHoldings() {
  if (!crypto.value) return
  try {
    const response = await api.wallet.list() as WalletResponse
    // Find this crypto in wallet holdings
    const asset = response.assets?.find(a => a.symbol === crypto.value!.symbol)
    positions.value = asset || null
  } catch (e: any) {
    console.error('Error loading wallet holdings:', e)
    positions.value = null
  }
}

async function fetchHistoricalData() {
  if (!crypto.value?.id) return
  historyLoading.value = true
  try {
    // Map time ranges to days
    const dayMap: Record<string, number> = {
      '1d': 1,
      '7d': 7,
      '30d': 30,
      '60d': 60
    }
    
    const days = dayMap[timeRange.value] || 30
    console.log(`📊 Fetching ${timeRange.value} (${days} days) for ${crypto.value.symbol}...`)
    
    try {
      const response = await api.crypto.history(crypto.value.id, days)
      const data = response as any
      
      if (data.history && Array.isArray(data.history)) {
        history.value = data.history
        console.log(`✅ Loaded ${history.value.length} data points`)
        // Force chart re-render by updating the key
        chartRenderKey.value++
      } else {
        console.warn('Invalid history data structure')
        history.value = []
        chartRenderKey.value++
      }
    } catch (apiError: any) {
      console.debug(`API returned error (expected fallback): ${apiError.message}`)
      // API error is handled - history.value remains as is or becomes empty
      history.value = []
      chartRenderKey.value++
    }
  } catch (e: any) {
    console.debug('Chart data fetch failed:', e instanceof Error ? e.message : 'Unknown error')
    history.value = []
    chartRenderKey.value++
  } finally {
    historyLoading.value = false
  }
}

async function loadAllData() {
  await fetchCryptoDetail()
  if (crypto.value) {
    await Promise.all([
      fetchWalletHoldings(),
      fetchWalletTransactions(),
      fetchHistoricalData()
    ])
  }
}

// ============================================================================
// LIFECYCLE & WATCHERS
// ============================================================================
onMounted(loadAllData)
watch(() => route.params.id as string, loadAllData)

// Watch timeRange changes to fetch new data
watch(timeRange, async () => {
  console.log(`📊 Time range changed to ${timeRange.value}`)
  await fetchHistoricalData()
}, { immediate: false })

// Watch for history changes to trigger chart re-render
watch(history, () => {
  console.log(`📈 History updated: ${history.value.length} points`)
}, { deep: true })

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
// CONFIGURATION DU GRAPHIQUE DYNAMIQUE
// ============================================================================
const filteredHistory = computed(() => {
  // Don't filter - use all the data returned from the API
  // The API already returns data for the requested time range
  return history.value
})

const chartData = computed<ChartData<'line'>>(() => {
  if (!filteredHistory.value.length) {
    return {
      labels: [],
      datasets: []
    }
  }

  // Déterminer la couleur de la ligne basée sur le dernier change_24h_pct
  const lastEntry = filteredHistory.value[filteredHistory.value.length - 1]
  const lineColor = lastEntry.change_24h_pct >= 0 ? '#22c55e' : '#ef4444'
  const fillColor = lastEntry.change_24h_pct >= 0 
    ? 'rgba(34, 197, 94, 0.1)' 
    : 'rgba(239, 68, 68, 0.1)'

  // Créer un dégradé pour le remplissage
  const createGradient = (ctx: CanvasRenderingContext2D) => {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400)
    if (lastEntry.change_24h_pct >= 0) {
      gradient.addColorStop(0, 'rgba(34, 197, 94, 0.3)')
      gradient.addColorStop(1, 'rgba(34, 197, 94, 0.05)')
    } else {
      gradient.addColorStop(0, 'rgba(239, 68, 68, 0.3)')
      gradient.addColorStop(1, 'rgba(239, 68, 68, 0.05)')
    }
    return gradient
  }

  // Format les labels selon le time range - plus intelligemment
  const labels = filteredHistory.value.map((entry, index) => {
    const date = new Date(entry.timestamp)
    const isFirstPoint = index === 0
    const isLastPoint = index === filteredHistory.value.length - 1
    const isEveryNthPoint = index % Math.max(1, Math.floor(filteredHistory.value.length / 10)) === 0
    
    // Afficher les premiers, derniers et points réguliers
    if (isFirstPoint || isLastPoint || isEveryNthPoint || filteredHistory.value.length < 15) {
      switch (timeRange.value) {
        case '1d': 
          return date.toLocaleTimeString('en-US', { 
            hour: 'numeric',
            minute: '2-digit'
          })
        case '7d': 
          return date.toLocaleDateString('en-US', { 
            weekday: 'short',
            month: 'short',
            day: 'numeric'
          })
        default: 
          return date.toLocaleDateString('en-US', { 
            month: 'short',
            day: 'numeric'
          })
      }
    }
    return ''
  })

  return {
    labels,
    datasets: [
      {
        label: `${crypto.value?.symbol || 'Crypto'} Price`,
        data: filteredHistory.value.map(entry => entry.price),
        borderColor: lineColor,
        backgroundColor: (context) => {
          const chart = context.chart
          const { ctx, chartArea } = chart
          if (!chartArea) return fillColor
          return createGradient(ctx)
        },
        borderWidth: 3,
        fill: true,
        tension: 0.2, // Moins courbe pour mieux suivre les données
        pointRadius: 0,
        pointHoverRadius: 8,
        pointBackgroundColor: lineColor,
        pointBorderColor: '#ffffff',
        pointBorderWidth: 2,
        clip: false, // Permettre aux points de déborder
      }
    ]
  }
})

const chartOptions = computed<ChartOptions<'line'>>(() => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'index',
    intersect: false
  },
  plugins: {
    legend: {
      display: false
    },
    tooltip: {
      enabled: true,
      backgroundColor: 'rgba(17, 24, 39, 0.95)',
      titleColor: 'rgb(249, 250, 251)',
      bodyColor: 'rgb(229, 231, 235)',
      borderColor: 'rgba(75, 192, 192, 0.3)',
      borderWidth: 1,
      padding: 12,
      displayColors: false,
      titleFont: {
        size: 12,
        weight: 'bold'
      },
      bodyFont: {
        size: 11
      },
      boxPadding: 6,
      callbacks: {
        label: (context) => {
          const value = context.parsed.y
          return `Price: ${formatCurrency(value)}`
        },
        title: (tooltipItems) => {
          const item = tooltipItems[0]
          const index = item.dataIndex
          const entry = filteredHistory.value[index]
          if (!entry) return ''
          
          const date = new Date(entry.timestamp)
          return date.toLocaleDateString('en-US', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
          })
        },
        afterLabel: (context) => {
          const index = context.dataIndex
          const entry = filteredHistory.value[index]
          if (!entry) return ''
          
          const change = entry.change_24h_pct
          const sign = change >= 0 ? '+' : ''
          return `24h Change: ${sign}${change.toFixed(2)}%`
        }
      }
    }
  },
  scales: {
    x: {
      grid: {
        display: false,
        drawBorder: false
      },
      ticks: {
        color: 'rgb(156, 163, 175)',
        font: {
          size: 10
        },
        maxRotation: 45,
        minRotation: 0,
        maxTicksLimit: timeRange.value === '1d' ? 12 : 8
      }
    },
    y: {
      position: 'right',
      grid: {
        color: 'rgba(75, 192, 192, 0.08)',
        drawBorder: false,
        lineWidth: 0.5
      },
      ticks: {
        color: 'rgb(156, 163, 175)',
        font: {
          size: 10
        },
        callback: (value) => formatCurrency(value),
        padding: 8
      },
      border: {
        display: false
      }
    }
  },
  elements: {
    line: {
      cubicInterpolationMode: 'monotone'
    }
  }
}))

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

function goToSell() {
  if (crypto.value && positions.value && positions.value.quantity > 0) {
    router.push(`/dashboard/cryptos?sell=${crypto.value.id}`)
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

function viewAllTransactions() {
  router.push('/dashboard/transactions')
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
                  @error="(e: Event) => {
                    const target = e.target as HTMLImageElement
                    target.style.display = 'none'
                  }"
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
              :disabled="loading"
              @click="refreshData"
            >
              <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" />
            </Button>
            <Button 
              v-if="crypto" 
              variant="ghost"
              size="sm"
              class="text-gray-600 dark:text-gray-300"
              @click="shareCrypto"
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
                @click="goToSell"
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
                  <span v-if="filteredHistory.length > 0">
                    • {{ filteredHistory.length }} data points
                  </span>
                </div>
              </div>
              <div class="flex gap-2 flex-wrap">
                <Button
                  v-for="range in ['1d', '7d', '30d', '60d']"
                  :key="range"
                  size="sm"
                  :variant="timeRange === range ? 'default' : 'outline'"
                  class="text-xs font-medium"
                  :disabled="historyLoading"
                  @click="() => {
                    timeRange = range
                  }"
                >
                  {{ range === '1d' ? '24 Hours' : range === '7d' ? '1 Week' : range === '30d' ? '1 Month' : '2 Months' }}
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
              <div v-else-if="!chartData.datasets.length || !history.length" class="h-full flex items-center justify-center">
                <div class="text-center text-gray-500 dark:text-gray-400">
                  <div class="text-4xl mb-4">📊</div>
                  <p>No historical data available</p>
                </div>
              </div>
              <Line 
                v-else 
                :key="`chart-${chartRenderKey}`"
                :data="chartData"
                :options="chartOptions"
              />
            </div>
          </CardContent>
        </Card>

        <!-- Portfolio & Info Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Portfolio Section - YOUR HOLDINGS -->
          <Card class="border-gray-200 dark:border-gray-700">
            <CardContent class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Your Holdings</h3>
                <Badge 
  v-if="positions && positions.quantity > 0" 
  :variant="profitLoss >= 0 ? 'default' : 'destructive'"
  :class="profitLoss >= 0 
    ? 'bg-green-600 hover:bg-green-600 !text-white' 
    : 'bg-red-600 hover:bg-red-600 !text-white'"
>
  {{ formatPercentage(profitLossPercentage) }}
</Badge>

              </div>
              
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
                <!-- View All Transactions Button -->
                <div class="text-center">
                  <Button 
                    variant="outline"
                    class="w-full border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300"
                    @click="viewAllTransactions"
                  >
                    View All Transactions
                  </Button>
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
                  <span class="text-gray-500 dark:text-gray-400">Name</span>
                  <span class="font-medium text-gray-900 dark:text-white">{{ crypto.name }}</span>
                </div>
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
                <div class="flex justify-between items-center py-3 border-b border-gray-100 dark:border-gray-800">
                  <span class="text-gray-500 dark:text-gray-400">Market Cap</span>
                  <span class="font-medium text-gray-900 dark:text-white">€{{ formatLargeNumber(marketCap) }}</span>
                </div>
                <!-- Safe website display -->
                <div class="flex justify-between items-center py-3">
                  <span class="text-gray-500 dark:text-gray-400">Website</span>
                  <a 
                    v-if="crypto.website && crypto.website.startsWith('http')"
                    :href="crypto.website"
                    target="_blank"
                    class="text-blue-600 dark:text-blue-400 hover:underline font-medium text-sm flex items-center gap-1"
                  >
                    Visit
                    <ExternalLink class="w-3 h-3" />
                  </a>
                  <span v-else class="text-gray-400 dark:text-gray-500 text-sm">N/A</span>
                </div>
              </div>

              <!-- Performance Stats -->
              <div class="mt-8 pt-6 border-t border-gray-100 dark:border-gray-800">
                <h4 class="font-medium text-gray-900 dark:text-white mb-4">Performance</h4>
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div 
                      :class="dailyChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                      class="text-2xl font-bold"
                    >
                      {{ formatPercentage(dailyChange) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">24h Change</div>
                  </div>
                  <div class="text-center p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                    <div 
                      :class="get7DayChange >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" 
                      class="text-2xl font-bold"
                    >
                      {{ formatPercentage(get7DayChange) }}
                    </div>
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">7D Change</div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Additional Statistics -->
        <Card class="border-gray-200 dark:border-gray-700">
          <CardContent class="p-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">Advanced Statistics</h3>
            
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Current Price</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">{{ formatCurrency(currentPrice) }}</div>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Current Volume</div>
                <div class="text-xl font-bold text-gray-900 dark:text-white">
                  {{ history.length > 0 ? `€${formatLargeNumber(history[history.length - 1]?.volume || 0)}` : 'N/A' }}
                </div>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Period High</div>
                <div class="text-xl font-bold text-green-600 dark:text-green-400">
                  {{ history.length > 0 ? formatCurrency(Math.max(...history.map(h => h.price))) : 'N/A' }}
                </div>
              </div>
              <div class="p-4 rounded-lg bg-gray-50 dark:bg-gray-800">
                <div class="text-sm text-gray-500 dark:text-gray-400 mb-2">Period Low</div>
                <div class="text-xl font-bold text-red-600 dark:text-red-400">
                  {{ history.length > 0 ? formatCurrency(Math.min(...history.map(h => h.price))) : 'N/A' }}
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