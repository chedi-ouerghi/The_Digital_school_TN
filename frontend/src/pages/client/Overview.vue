<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import api from '../../services/api'
import { useRouter } from 'vue-router'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler, BarElement } from 'chart.js'
import { Line, Bar } from 'vue-chartjs'
import {
  TrendingUp,
  ShoppingCart,
  ArrowUpRight,
  Wallet,
  PieChart,
  BarChart3,
  ChevronRight,
  TrendingDown,
  DollarSign,
  Coins,
  Activity
} from 'lucide-vue-next'

// Import des composants shadcn-vue
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler, BarElement)

const router = useRouter()
const wallet = ref<any>(null)
const cryptos = ref<any[]>([])
const loading = ref(false)
const chartLoading = ref(false)
const error = ref<string | null>(null)

const chartData = ref<number[]>([])
const chartLabels = ref<string[]>([])
const selectedChartPeriod = ref<string>('7d')
const selectedChartType = ref<string>('portfolio')
const selectedCryptoForChart = ref<string>('')

const timePeriods = [
  { value: '24h', label: '24H' },
  { value: '7d', label: '7D' },
  { value: '30d', label: '30D' },
  { value: '90d', label: '3M' },
  { value: '1y', label: '1Y' },
]

function makeImageUrl(path: string | undefined | null): string | null {
  if (!path) return null
  const p = String(path)
  if (p.startsWith('http://') || p.startsWith('https://')) return p
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const cleanPath = p.startsWith('/storage/') ? p : `/storage/${p}`
  return `${baseUrl}${cleanPath}`
}

function formatCurrency(value: any) {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '$0.00'
  return n.toLocaleString('en-US', { style: 'currency', currency: 'USD', minimumFractionDigits: 2, maximumFractionDigits: 2 })
}

function formatCompactCurrency(value: any) {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '$0'
  
  if (n >= 1000000) return `$${(n / 1000000).toFixed(2)}M`
  if (n >= 1000) return `$${(n / 1000).toFixed(2)}K`
  return `$${n.toFixed(2)}`
}

function formatNumber(value: any, decimals = 4) {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toLocaleString('en-US', { 
    minimumFractionDigits: 0, 
    maximumFractionDigits: decimals 
  })
}

function formatPercent(value: any) {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0.00%'
  return `${n >= 0 ? '+' : ''}${n.toFixed(2)}%`
}

// Calcul des statistiques
const stats = computed(() => {
  if (!wallet.value) return {
    totalValue: 0,
    investedAmount: 0,
    profitAmount: 0,
    profitPercent: 0,
    assetsCount: 0,
    availableBalance: 0,
    totalBalance: 0,
    positions: [],
    top3Positions: [],
    assetDistribution: [],
    dailyChange: 0,
    weeklyChange: 0,
    bestPerformer: null,
    worstPerformer: null,
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
        investedValue,
        pnl: currentValue - investedValue,
        pnlPercent: avgBuyPrice > 0 ? ((currentPrice - avgBuyPrice) / avgBuyPrice) * 100 : 0,
        change24h: Number(crypto.change_24h_pct || 0),
        weight: 0,
        created_at: asset.created_at
      })
    }
  })

  // Calcul des poids pour la distribution
  positions.forEach(pos => {
    pos.weight = totalValue > 0 ? (pos.currentValue / totalValue) * 100 : 0
  })

  const profitAmount = totalValue - invested
  const profitPercent = invested > 0 ? (profitAmount / invested) * 100 : 0
  const availableBalance = Number(wallet.value.balance_eur || 0)
  
  // Get top 3 by value
  const sortedByValue = [...positions].sort((a, b) => b.currentValue - a.currentValue)
  const top3Positions = sortedByValue.slice(0, 3)

  // Best & worst performer
  const sortedByPerformance = [...positions].sort((a, b) => b.pnlPercent - a.pnlPercent)
  const bestPerformer = sortedByPerformance.length > 0 ? sortedByPerformance[0] : null
  const worstPerformer = sortedByPerformance.length > 0 ? sortedByPerformance[sortedByPerformance.length - 1] : null

  return {
    totalValue,
    investedAmount: invested,
    profitAmount,
    profitPercent,
    assetsCount,
    availableBalance,
    totalBalance: availableBalance + totalValue,
    positions: positions.sort((a, b) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime()),
    top3Positions,
    assetDistribution: sortedByValue.map(p => ({
      name: p.symbol,
      value: p.weight
    })),
    dailyChange: 1.5, // À remplacer par des données réelles
    weeklyChange: 5.2, // À remplacer par des données réelles
    bestPerformer,
    worstPerformer,
  }
})

// Stats cards data
const statCards = computed(() => [
  {
    title: 'Total Value',
    value: formatCurrency(stats.value.totalValue),
    change: formatPercent(stats.value.dailyChange),
    isPositive: stats.value.dailyChange >= 0,
    icon: DollarSign,
    color: 'text-[#38618C]',
    bgColor: 'bg-[#38618C]/10'
  },
  {
    title: 'Available',
    value: formatCurrency(stats.value.availableBalance),
    change: 'Ready to trade',
    isPositive: true,
    icon: Wallet,
    color: 'text-[#35A7FF]',
    bgColor: 'bg-[#35A7FF]/10'
  },
  {
    title: 'Total Invested',
    value: formatCurrency(stats.value.investedAmount),
    change: formatPercent(stats.value.profitPercent),
    isPositive: stats.value.profitPercent >= 0,
    icon: TrendingUp,
    color: 'text-[#01FF19]',
    bgColor: 'bg-[#01FF19]/10'
  },
  {
    title: 'Assets',
    value: stats.value.assetsCount.toString(),
    change: `${stats.value.positions.length} positions`,
    isPositive: true,
    icon: Coins,
    color: 'text-[#FF5964]',
    bgColor: 'bg-[#FF5964]/10'
  }
])

// Generate chart data from actual transactions
async function generateChartData() {
  chartLoading.value = true
  try {
    if (selectedChartType.value === 'portfolio') {
      // Portfolio value chart - calculated from transactions
      const transactions = wallet.value?.transactions || []
      
      if (transactions.length === 0) {
        chartData.value = []
        chartLabels.value = []
        return
      }

      // Calculate portfolio value from transactions
      generatePortfolioChartData(transactions)
    } else {
      // Individual crypto chart - fetch history from API
      if (selectedCryptoForChart.value) {
        try {
          const historyResponse = await api.crypto.history(selectedCryptoForChart.value) as any
          
          // Handle both array and object response formats
          const priceData = Array.isArray(historyResponse) ? historyResponse : historyResponse?.prices
          
          if (priceData && Array.isArray(priceData) && priceData.length > 0) {
            generateCryptoChartData(priceData)
          } else {
            generateDummyChartData()
          }
        } catch (err) {
          console.error('Error fetching crypto history:', err)
          generateDummyChartData()
        }
      }
    }
  } catch (err) {
    console.error('Error generating chart:', err)
    generateDummyChartData()
  } finally {
    chartLoading.value = false
  }
}

function generateCryptoChartData(prices: any[]) {
  if (!Array.isArray(prices) || prices.length === 0) {
    generateDummyChartData()
    return
  }

  // Prices format: [[timestamp_ms, price], ...]
  const periodDays: Record<string, number> = {
    '24h': 1,
    '7d': 7,
    '30d': 30,
    '90d': 90,
    '1y': 365
  }

  const daysToShow = periodDays[selectedChartPeriod.value] || 30
  const now = Date.now()
  const cutoffTime = now - (daysToShow * 24 * 60 * 60 * 1000)

  // Filter prices within the selected period
  const filteredPrices = prices.filter((p: any) => {
    return Array.isArray(p) && typeof p[0] === 'number' && typeof p[1] === 'number' && p[0] >= cutoffTime
  })

  if (filteredPrices.length === 0) {
    generateDummyChartData()
    return
  }

  // Extract prices and format labels
  chartData.value = filteredPrices.map((p: any) => Number(p[1]) || 0)
  chartLabels.value = filteredPrices.map((p: any) => {
    const date = new Date(Number(p[0]))
    if (selectedChartPeriod.value === '24h') {
      return date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' })
    } else if (selectedChartPeriod.value === '7d') {
      return date.toLocaleDateString('en-US', { weekday: 'short' })
    } else {
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }
  })
}

function generatePortfolioChartData(transactions: any[]) {
  // Group transactions by date and calculate cumulative portfolio value
  const portfolioByDate: Map<string, number> = new Map()
  
  transactions.forEach((tx: any) => {
    const date = new Date(tx.created_at)
    const dateKey = date.toLocaleDateString('en-US')
    const amount = Number(tx.total_eur || 0)
    
    const current = portfolioByDate.get(dateKey) || 0
    if (tx.type === 'ACHAT') {
      portfolioByDate.set(dateKey, current + amount)
    } else if (tx.type === 'VENTE') {
      portfolioByDate.set(dateKey, current - amount)
    }
  })

  // Sort dates and calculate cumulative values
  const sortedDates = Array.from(portfolioByDate.keys()).sort((a, b) => {
    return new Date(a).getTime() - new Date(b).getTime()
  })

  if (sortedDates.length === 0) {
    chartData.value = [stats.value.totalValue]
    chartLabels.value = ['Today']
    return
  }

  // Determine the actual portfolio age
  const oldestDate = new Date(sortedDates[0] || new Date())
  const today = new Date()
  const portfolioAgeDays = Math.floor((today.getTime() - oldestDate.getTime()) / (1000 * 60 * 60 * 24)) + 1

  // Adjust selected period to not exceed portfolio age
  let actualPeriod = selectedChartPeriod.value
  const periodDays: Record<string, number> = {
    '24h': 1,
    '7d': 7,
    '30d': 30,
    '90d': 90,
    '1y': 365
  }

  if ((periodDays[selectedChartPeriod.value] || 30) > portfolioAgeDays) {
    actualPeriod = 'max' // Show all available data
  }

  // Filter data based on period
  const cutoffDate = new Date(today)
  if (actualPeriod !== 'max') {
    cutoffDate.setDate(cutoffDate.getDate() - (periodDays[selectedChartPeriod.value] || 30))
  } else {
    cutoffDate.setTime(oldestDate.getTime())
  }

  const filteredDates = sortedDates.filter(d => new Date(d) >= cutoffDate)

  // Build chart data with cumulative values
  chartData.value = []
  chartLabels.value = []
  let cumulativeValue = 0

  filteredDates.forEach((dateStr) => {
    cumulativeValue += portfolioByDate.get(dateStr) || 0
    chartData.value.push(Math.max(0, cumulativeValue))

    const date = new Date(dateStr)
    chartLabels.value.push(date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }))
  })

  // If no data points, show at least current value
  if (chartData.value.length === 0) {
    chartData.value = [stats.value.totalValue]
    chartLabels.value = ['Today']
  }
}

function generateDummyChartData() {
  // Fallback dummy data for crypto charts
  const periods: Record<string, number> = {
    '24h': 24,
    '7d': 7,
    '30d': 30,
    '90d': 90,
    '1y': 365
  }
  
  const points = Math.min(periods[selectedChartPeriod.value] || 30, 30)
  chartData.value = []
  chartLabels.value = []
  
  let baseValue = stats.value.totalValue || 10000
  for (let i = 0; i < points; i++) {
    const fluctuation = (Math.random() - 0.5) * 0.1
    baseValue = baseValue * (1 + fluctuation)
    chartData.value.push(baseValue)
    
    if (selectedChartPeriod.value === '24h') {
      chartLabels.value.push(`${i}:00`)
    } else if (selectedChartPeriod.value === '7d') {
      const days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']
      chartLabels.value.push(days[i % 7] || 'Day')
    } else {
      chartLabels.value.push(`Day ${i + 1}`)
    }
  }
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
      borderColor: '#01FF19',
      borderWidth: 2,
      padding: 12,
      displayColors: false,
      callbacks: {
        label: (context: any) => `${formatCurrency(context.parsed.y)}`
      }
    }
  },
  scales: {
    x: {
      ticks: { 
        color: '#6B7280',
        font: { size: 11 }
      },
      grid: { 
        color: 'rgba(229, 231, 235, 0.5)',
        drawBorder: false
      }
    },
    y: {
      ticks: {
        color: '#6B7280',
        font: { size: 11 },
        callback: function(value: any) { return formatCompactCurrency(Number(value)) }
      },
      grid: { 
        color: 'rgba(229, 231, 235, 0.5)',
        drawBorder: false
      }
    }
  },
  interaction: {
    intersect: false,
    mode: 'index' as const
  },
  elements: {
    line: {
      tension: 0.4
    },
    point: {
      radius: 0,
      hoverRadius: 6
    }
  }
}))

const chartDataset = computed(() => ({
  labels: chartLabels.value,
  datasets: [{
    label: selectedChartType.value === 'portfolio' ? 'Portfolio Value' : 'Price',
    data: chartData.value,
    fill: true,
    borderColor: '#01FF19',
    backgroundColor: selectedChartType.value === 'portfolio'
      ? 'rgba(1, 255, 25, 0.15)'
      : 'rgba(1, 255, 25, 0.15)',
    tension: 0.4,
    borderWidth: 2,
    pointBackgroundColor: '#01FF19',
    pointBorderColor: '#FFFFFF',
    pointBorderWidth: 2,
    pointHoverBackgroundColor: '#FFFFFF',
    pointHoverBorderColor: '#01FF19',
    pointHoverBorderWidth: 2
  }]
}))

// Distribution chart
const distributionChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right' as const,
      labels: {
        color: '#6B7280',
        font: { size: 11 },
        padding: 15,
        usePointStyle: true
      }
    },
    tooltip: {
      callbacks: {
        label: (context: any) => `${context.label}: ${context.parsed}%`
      }
    }
  }
}

const distributionChartData = computed(() => ({
  labels: stats.value.assetDistribution.map(d => d.name),
  datasets: [{
    data: stats.value.assetDistribution.map(d => d.value),
    backgroundColor: [
      '#35A7FF',
      '#01FF19',
      '#FF5964',
      '#38618C',
      '#FFB347',
      '#9D4EDD'
    ],
    borderColor: '#FFFFFF',
    borderWidth: 2,
    borderRadius: 6,
    spacing: 2
  }]
}))

async function fetchData() {
  loading.value = true
  error.value = null
  try {
    // Fetch wallet data
    const walletResponse = await api.wallet.list() as any
    wallet.value = walletResponse?.wallet || walletResponse || {}

    // Fetch available cryptos for chart selection
    const cryptoResponse = await api.crypto.list({ page: 1 }) as any
    cryptos.value = cryptoResponse?.data || []

    // Set default crypto for chart if available
    if (cryptos.value.length > 0 && !selectedCryptoForChart.value) {
      selectedCryptoForChart.value = cryptos.value[0].id
    }

    // Generate initial chart data
    await generateChartData()

  } catch (e: any) {
    error.value = e.message || 'Error loading data'
    console.error('Error:', e)
  } finally {
    loading.value = false
  }
}

// Watch for period changes
watch([selectedChartPeriod, selectedChartType, selectedCryptoForChart], () => {
  generateChartData()
})

onMounted(() => {
  fetchData()
})

function goBuy() {
  router.push('/dashboard/cryptos')
}

function goSell() {
  router.push('/dashboard/transactions')
}

function goPortfolio() {
  router.push('/dashboard/portfolio')
}

function goToDetails(cryptoId: string) {
  router.push(`/dashboard/portfolio/crypto/${cryptoId}`)
}

function goToTransactions() {
  router.push('/dashboard/transactions')
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Dashboard Overview</h1>
        <p class="text-gray-500 text-sm">Welcome back! Here's your portfolio summary</p>
      </div>
    </div>

    <!-- Quick Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <Card 
        v-for="(stat, index) in statCards" 
        :key="index"
        class="hover:shadow-lg transition-shadow duration-200"
      >
        <CardContent class="p-5">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <div :class="[stat.bgColor, 'p-2 rounded-lg']">
                <component :is="stat.icon" :class="[stat.color, 'w-5 h-5']" />
              </div>
              <span class="text-sm font-medium text-gray-600">{{ stat.title }}</span>
            </div>
            <Badge 
              v-if="stat.title !== 'Available'"
              :class="stat.isPositive ? 'bg-[#01FF19]/20 text-[#01FF19]' : 'bg-[#FF5964]/20 text-[#FF5964]'"
              class="font-medium"
            >
              {{ stat.change }}
            </Badge>
          </div>
          <div class="text-2xl font-bold text-gray-900 mb-1">{{ stat.value }}</div>
          <div v-if="stat.title === 'Available'" class="text-xs text-gray-500">
            {{ stat.change }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column: Chart -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Chart Card -->
        <Card class="shadow-sm">
          <CardHeader class="pb-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <CardTitle class="text-lg font-semibold text-gray-900">
                  {{ selectedChartType === 'portfolio' ? 'Portfolio Performance' : 'Crypto Performance' }}
                </CardTitle>
                <CardDescription class="text-sm">
                  {{ selectedChartPeriod === '24h' ? 'Last 24 hours' : 
                     selectedChartPeriod === '7d' ? 'Last 7 days' :
                     selectedChartPeriod === '30d' ? 'Last 30 days' :
                     selectedChartPeriod === '90d' ? 'Last 3 months' : 'Last year' }}
                </CardDescription>
              </div>
              
              <div class="flex items-center gap-2">
                <Select v-model="selectedChartType">
                  <SelectTrigger class="w-[140px] h-9">
                    <SelectValue placeholder="Chart Type" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="portfolio">Portfolio</SelectItem>
                    <SelectItem value="crypto">Crypto</SelectItem>
                  </SelectContent>
                </Select>

                <Select v-if="selectedChartType === 'crypto'" v-model="selectedCryptoForChart">
                  <SelectTrigger class="w-[160px] h-9">
                    <SelectValue placeholder="Select Crypto" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="crypto in cryptos.slice(0, 10)" 
                      :key="crypto.id" 
                      :value="crypto.id"
                    >
                      {{ crypto.symbol.toUpperCase() }}
                    </SelectItem>
                  </SelectContent>
                </Select>

                <div class="flex bg-gray-100 rounded-lg p-1">
                  <Button
                    v-for="period in timePeriods"
                    :key="period.value"
                    @click="selectedChartPeriod = period.value"
                    :variant="selectedChartPeriod === period.value ? 'default' : 'ghost'"
                    :class="[
                      'h-7 px-3 text-xs rounded-md',
                      selectedChartPeriod === period.value 
                        ? 'bg-[#38618C] text-white' 
                        : 'text-gray-600 hover:text-[#38618C]'
                    ]"
                  >
                    {{ period.label }}
                  </Button>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="chartLoading" class="h-[320px] flex items-center justify-center">
              <div class="text-center">
                <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-[#35A7FF] mx-auto mb-2"></div>
                <p class="text-sm text-gray-500">Loading chart data...</p>
              </div>
            </div>
            <div v-else class="h-[320px]">
              <Line 
                v-if="chartData.length > 0"
                :data="chartDataset" 
                :options="chartOptions" 
              />
              <div v-else class="h-full flex items-center justify-center text-gray-500">
                <div class="text-center">
                  <BarChart3 class="w-12 h-12 mx-auto mb-3 text-gray-300" />
                  <p>No chart data available</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Top Holdings -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle class="text-lg font-semibold text-gray-900">Top Holdings</CardTitle>
              <Button 
                @click="goPortfolio" 
                variant="ghost" 
                size="sm" 
                class="text-[#35A7FF] hover:text-[#35A7FF]/80"
              >
                View All
                <ChevronRight class="w-4 h-4 ml-1" />
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="stats.top3Positions.length === 0" class="text-center py-8">
              <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
                <Coins class="w-8 h-8 text-gray-400" />
              </div>
              <p class="text-gray-500 mb-3">No holdings yet</p>
              <Button @click="goBuy" class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C]">
                Start Trading
              </Button>
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="position in stats.top3Positions"
                :key="position.id"
                class="group flex items-center justify-between p-4 rounded-xl border border-gray-200 hover:border-[#35A7FF] hover:shadow-md transition-all duration-200 cursor-pointer"
                @click="goToDetails(position.cryptoId)"
              >
                <div class="flex items-center gap-4 flex-1">
                  <div class="relative">
                    <div class="w-12 h-12 rounded-xl border-2 border-gray-100 bg-white flex items-center justify-center shadow-sm">
                      <img
                        v-if="makeImageUrl(position.image_url)"
                        :src="makeImageUrl(position.image_url) || ''"
                        :alt="position.name"
                        class="w-10 h-10 rounded-lg object-cover"
                        @error="(e: any) => {
                          const target = e.target as HTMLImageElement
                          if (target) target.style.display = 'none'
                        }"
                      />
                      <div v-else class="text-xl">💎</div>
                    </div>
                    <div class="absolute -bottom-1 -right-1 w-5 h-5 rounded-full bg-white border-2 border-gray-100 flex items-center justify-center">
                      <div class="w-2 h-2 rounded-full bg-[#01FF19]"></div>
                    </div>
                  </div>
                  
                  <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                      <span class="font-semibold text-gray-900">{{ position.symbol.toUpperCase() }}</span>
                      <span class="text-xs text-gray-500">•</span>
                      <span class="text-sm text-gray-500 truncate">{{ position.name }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                      <span class="text-sm font-medium text-gray-900">
                        {{ formatNumber(position.quantity, 4) }}
                      </span>
                      <span class="text-xs text-gray-500">
                        ≈ {{ formatCurrency(position.currentValue) }}
                      </span>
                    </div>
                  </div>
                </div>
                
                <div class="text-right ml-4">
                  <div class="flex items-center justify-end gap-2 mb-1">
                    <Badge
                      :class="position.pnl >= 0 ? 'bg-[#01FF19]/20 text-[#01FF19]' : 'bg-[#FF5964]/20 text-[#FF5964]'"
                      class="font-medium px-2 py-0.5"
                    >
                      {{ formatPercent(position.pnlPercent) }}
                    </Badge>
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ formatCurrency(position.pnl) }} P&L
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Right Column: Stats & Actions -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-gray-900">Quick Actions</CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button
              @click="goBuy"
              class="w-full h-12 bg-gradient-to-r from-[#01FF19] to-[#01FF19]/80 hover:from-[#01FF19]/90 hover:to-[#01FF19]/70 text-[#38618C] font-semibold justify-start"
            >
              <ShoppingCart class="w-5 h-5 mr-3" />
              Buy Crypto
            </Button>
            
            <Button
              @click="goSell"
              variant="outline"
              class="w-full h-12 border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white justify-start"
            >
              <ArrowUpRight class="w-5 h-5 mr-3" />
              Sell Assets
            </Button>
            
            <Button
              @click="goToTransactions"
              variant="outline"
              class="w-full h-12 border-gray-300 text-gray-700 hover:bg-gray-50 justify-start"
            >
              <Activity class="w-5 h-5 mr-3" />
              View Transactions
            </Button>
          </CardContent>
        </Card>

        <!-- Asset Distribution -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-gray-900">Asset Distribution</CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="stats.assetDistribution.length === 0" class="text-center py-8">
              <PieChart class="w-12 h-12 mx-auto mb-3 text-gray-300" />
              <p class="text-gray-500">No assets to display</p>
            </div>
            <div v-else class="space-y-4">
              <div class="h-[200px]">
                <Bar :data="distributionChartData" :options="distributionChartOptions" />
              </div>
              
              <div class="space-y-2">
                <div 
                  v-for="(asset, index) in stats.assetDistribution.slice(0, 4)"
                  :key="index"
                  class="flex items-center justify-between text-sm"
                >
                  <div class="flex items-center gap-2">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :style="{ 
                        backgroundColor: distributionChartData.datasets?.[0]?.backgroundColor?.[index] as string || '#e5e7eb'
                      }"
                    ></div>
                    <span class="font-medium text-gray-700">{{ asset.name }}</span>
                  </div>
                  <span class="text-gray-900 font-semibold">{{ asset.value.toFixed(1) }}%</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Performance Highlights -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-gray-900">Performance Highlights</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-if="stats.bestPerformer" class="bg-[#01FF19]/5 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <TrendingUp class="w-4 h-4 text-[#01FF19]" />
                  <span class="text-sm font-medium text-gray-700">Best Performer</span>
                </div>
                <Badge class="bg-[#01FF19]/20 text-[#01FF19]">
                  {{ formatPercent(stats.bestPerformer.pnlPercent) }}
                </Badge>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white border flex items-center justify-center">
                  <span class="font-bold text-xs">{{ stats.bestPerformer.symbol.toUpperCase() }}</span>
                </div>
                <div>
                  <div class="font-semibold text-gray-900">{{ stats.bestPerformer.name }}</div>
                  <div class="text-xs text-gray-500">
                    {{ formatCurrency(stats.bestPerformer.pnl) }} profit
                  </div>
                </div>
              </div>
            </div>

            <div v-if="stats.worstPerformer" class="bg-[#FF5964]/5 rounded-xl p-4">
              <div class="flex items-center justify-between mb-2">
                <div class="flex items-center gap-2">
                  <TrendingDown class="w-4 h-4 text-[#FF5964]" />
                  <span class="text-sm font-medium text-gray-700">Worst Performer</span>
                </div>
                <Badge class="bg-[#FF5964]/20 text-[#FF5964]">
                  {{ formatPercent(stats.worstPerformer.pnlPercent) }}
                </Badge>
              </div>
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-white border flex items-center justify-center">
                  <span class="font-bold text-xs">{{ stats.worstPerformer.symbol.toUpperCase() }}</span>
                </div>
                <div>
                  <div class="font-semibold text-gray-900">{{ stats.worstPerformer.name }}</div>
                  <div class="text-xs text-gray-500">
                    {{ formatCurrency(stats.worstPerformer.pnl) }} loss
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Recent Activity -->
    <Card>
      <CardHeader>
        <div class="flex items-center justify-between">
          <CardTitle class="text-lg font-semibold text-gray-900">Recent Activity</CardTitle>
          <Button 
            @click="goToTransactions" 
            variant="ghost" 
            size="sm" 
            class="text-[#35A7FF] hover:text-[#35A7FF]/80"
          >
            View All Activity
            <ChevronRight class="w-4 h-4 ml-1" />
          </Button>
        </div>
      </CardHeader>
      <CardContent>
        <div v-if="wallet?.transactions?.length === 0" class="text-center py-8">
          <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 flex items-center justify-center">
            <Activity class="w-8 h-8 text-gray-400" />
          </div>
          <p class="text-gray-500 mb-3">No recent activity</p>
          <Button @click="goBuy" class="bg-[#38618C] hover:bg-[#38618C]/90 text-white">
            Make Your First Trade
          </Button>
        </div>
        
        <div v-else class="space-y-3">
          <div
            v-for="transaction in wallet?.transactions?.slice(0, 5) || []"
            :key="transaction.id"
            class="flex items-center justify-between p-4 rounded-lg border border-gray-100 hover:bg-gray-50 transition-colors"
          >
            <div class="flex items-center gap-3">
              <div 
                :class="[
                  'w-10 h-10 rounded-lg flex items-center justify-center',
                  transaction.type === 'ACHAT' ? 'bg-[#01FF19]/10' : 'bg-[#FF5964]/10'
                ]"
              >
                <ShoppingCart 
                  v-if="transaction.type === 'ACHAT'" 
                  class="w-5 h-5 text-[#01FF19]" 
                />
                <ArrowUpRight 
                  v-else 
                  class="w-5 h-5 text-[#FF5964]" 
                />
              </div>
              <div>
                <div class="font-medium text-gray-900">
                  {{ transaction.type === 'ACHAT' ? 'Buy' : 'Sell' }} {{ transaction.crypto?.symbol?.toUpperCase() }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ new Date(transaction.created_at).toLocaleDateString() }}
                </div>
              </div>
            </div>
            
            <div class="text-right">
              <div class="font-semibold text-gray-900">
                {{ formatCurrency(transaction.total_eur) }}
              </div>
              <div class="text-sm text-gray-500">
                {{ formatNumber(transaction.quantity) }} {{ transaction.crypto?.symbol?.toUpperCase() }}
              </div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
/* Custom gradient for chart */
:deep(.chart-gradient) {
  background: linear-gradient(180deg, rgba(53, 167, 255, 0.2) 0%, rgba(53, 167, 255, 0) 100%);
}

/* Smooth transitions */
:deep(.hover-lift) {
  transition: transform 0.2s ease, box-shadow 0.2s ease;
}

:deep(.hover-lift:hover) {
  transform: translateY(-2px);
  box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
}

/* Chart point animation */
:deep(.chart-point) {
  transition: r 0.3s ease;
}

:deep(.chart-point:hover) {
  r: 6;
}

/* Custom scrollbar for select */
:deep(select) {
  scrollbar-width: thin;
  scrollbar-color: #38618C #f1f1f1;
}

:deep(select::-webkit-scrollbar) {
  width: 6px;
}

:deep(select::-webkit-scrollbar-track) {
  background: #f1f1f1;
  border-radius: 3px;
}

:deep(select::-webkit-scrollbar-thumb) {
  background: #38618C;
  border-radius: 3px;
}
</style>