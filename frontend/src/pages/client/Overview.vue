<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { 
  TrendingUp, TrendingDown, DollarSign, 
  Wallet, Coins, BarChart3, ShoppingCart,
  ArrowUpRight, Activity, ChevronRight,
  PieChart, RefreshCw
} from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Chart, registerables } from 'chart.js'
import { Line, Bar } from 'vue-chartjs'
import { Skeleton } from '@/components/ui/skeleton'

// Setup Chart.js
Chart.register(...registerables)

const router = useRouter()

// State
const loading = ref(true)
const walletData = ref<any>(null)
const chartPeriod = ref('30d')
const selectedMetric = ref('value')
const chartLoading = ref(false)
const refreshLoading = ref(false)

// Chart periods
const periods = [
  { value: '7d', label: '7D' },
  { value: '30d', label: '30D' },
  { value: '90d', label: '90D' },
  { value: '1y', label: '1Y' }
]

// Metrics for chart
const metrics = [
  { value: 'value', label: 'Portfolio Value' },
  { value: 'pnl', label: 'Profit/Loss' },
  { value: 'performance', label: 'Performance' }
]

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

function formatCompactNumber(value: number): string {
  if (value >= 1e9) return `€${(value / 1e9).toFixed(2)}B`
  if (value >= 1e6) return `€${(value / 1e6).toFixed(2)}M`
  if (value >= 1e3) return `€${(value / 1e3).toFixed(1)}K`
  return `€${value.toFixed(2)}`
}

function formatPercentage(value: number): string {
  return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`
}

function formatNumber(value: number, decimals = 4): string {
  return value.toFixed(decimals).replace(/\.?0+$/, '')
}

function getCryptoIcon(symbol: string): string {
  const icons: Record<string, string> = {
    'BTC': '₿', 'ETH': 'Ξ', 'BNB': 'β', 'XRP': 'X', 'ADA': 'A',
    'SOL': '◎', 'DOT': '●', 'DOGE': 'Ð', 'AVAX': 'A', 'MATIC': 'M'
  }
  return icons[symbol] || symbol.charAt(0)
}

// ============================================================================
// API FUNCTIONS
// ============================================================================
async function fetchWalletData() {
  try {
    const response = await api.wallet.list()
    walletData.value = response
  } catch (error) {
    console.error('Error fetching wallet data:', error)
  } finally {
    loading.value = false
  }
}

async function refreshData() {
  refreshLoading.value = true
  await fetchWalletData()
  refreshLoading.value = false
}

// ============================================================================
// COMPUTED PROPERTIES
// ============================================================================
// Portfolio stats
const portfolioStats = computed(() => {
  if (!walletData.value) return null

  const totalValue = walletData.value.totalValue || 0
  const totalInvestment = walletData.value.totalInvestment || 0
  const balance = walletData.value.balance_eur || 0
  const assets = walletData.value.assets || []
  const profit = totalValue - totalInvestment
  const profitPercent = totalInvestment > 0 ? (profit / totalInvestment) * 100 : 0
  const todayChange = profitPercent // Simplified for demo

  // Calculate asset distribution
  const assetDistribution = assets.map(asset => ({
    symbol: asset.symbol,
    name: asset.name,
    value: asset.current_value_eur || 0,
    weight: totalValue > 0 ? (asset.current_value_eur / totalValue) * 100 : 0,
    pnlPercent: asset.plus_value_percent || 0,
    pnlAmount: asset.plus_value_eur || 0
  })).sort((a, b) => b.value - a.value)

  // Top performers
  const topPerformers = [...assetDistribution]
    .sort((a, b) => b.pnlPercent - a.pnlPercent)
    .slice(0, 3)

  return {
    totalValue,
    totalInvestment,
    balance,
    profit,
    profitPercent,
    todayChange,
    assets: assets.length,
    topAssets: assetDistribution.slice(0, 5),
    topPerformers,
    assetDistribution
  }
})

// Stat cards
const statCards = computed(() => [
  {
    title: 'Total Portfolio',
    value: portfolioStats.value ? formatCurrency(portfolioStats.value.totalValue) : '€0.00',
    change: portfolioStats.value ? formatPercentage(portfolioStats.value.todayChange) : '+0.00%',
    positive: portfolioStats.value?.todayChange >= 0,
    icon: DollarSign,
    color: 'text-gray-900 dark:text-white'
  },
  {
    title: 'Available',
    value: portfolioStats.value ? formatCurrency(portfolioStats.value.balance) : '€0.00',
    change: 'Ready to trade',
    positive: true,
    icon: Wallet,
    color: 'text-blue-600 dark:text-blue-400'
  },
  {
    title: 'Total Invested',
    value: portfolioStats.value ? formatCurrency(portfolioStats.value.totalInvestment) : '€0.00',
    change: portfolioStats.value ? formatPercentage(portfolioStats.value.profitPercent) : '+0.00%',
    positive: portfolioStats.value?.profitPercent >= 0,
    icon: TrendingUp,
    color: 'text-green-600 dark:text-green-400'
  },
  {
    title: 'Assets',
    value: portfolioStats.value ? portfolioStats.value.assets.toString() : '0',
    change: `${portfolioStats.value?.topAssets.length || 0} holdings`,
    positive: true,
    icon: Coins,
    color: 'text-purple-600 dark:text-purple-400'
  }
])

// Chart data
const chartData = computed(() => {
  if (!portfolioStats.value) return null

  const labels = generateChartLabels()
  const values = generateChartValues()

  return {
    labels,
    datasets: [{
      label: selectedMetric.value === 'value' ? 'Portfolio Value' : 
             selectedMetric.value === 'pnl' ? 'Profit/Loss' : 'Performance',
      data: values,
      borderColor: selectedMetric.value === 'pnl' && values[values.length - 1] < 0 ? '#ef4444' : '#10b981',
      backgroundColor: selectedMetric.value === 'pnl' && values[values.length - 1] < 0 ? 
        'rgba(239, 68, 68, 0.1)' : 'rgba(16, 185, 129, 0.1)',
      fill: true,
      tension: 0.4,
      borderWidth: 2,
      pointRadius: 0,
      pointHoverRadius: 4
    }]
  }
})

function generateChartLabels(): string[] {
  const days = chartPeriod.value === '7d' ? 7 : 
               chartPeriod.value === '30d' ? 30 : 
               chartPeriod.value === '90d' ? 90 : 365

  return Array.from({ length: days }, (_, i) => {
    const date = new Date()
    date.setDate(date.getDate() - (days - i - 1))
    if (days <= 30) {
      return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
    }
    return date.toLocaleDateString('en-US', { month: 'short' })
  })
}

function generateChartValues(): number[] {
  const days = chartPeriod.value === '7d' ? 7 : 
               chartPeriod.value === '30d' ? 30 : 
               chartPeriod.value === '90d' ? 90 : 365

  const baseValue = portfolioStats.value?.totalValue || 10000
  const volatility = chartPeriod.value === '7d' ? 0.03 : 
                     chartPeriod.value === '30d' ? 0.05 : 0.08

  return Array.from({ length: days }, (_, i) => {
    const progress = i / days
    const trend = selectedMetric.value === 'pnl' ? 0 : 0.15
    const noise = (Math.random() - 0.5) * volatility * 2
    const value = baseValue * (1 + trend * progress + noise)
    
    if (selectedMetric.value === 'pnl') {
      return value - baseValue
    } else if (selectedMetric.value === 'performance') {
      return ((value / baseValue) - 1) * 100
    }
    return value
  })
}

// Chart options
const chartOptions = computed(() => ({
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      mode: 'index',
      intersect: false,
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      padding: 12,
      callbacks: {
        label: (context: any) => {
          const value = context.parsed.y
          if (selectedMetric.value === 'performance') {
            return `${value.toFixed(2)}%`
          }
          return formatCurrency(value)
        }
      }
    }
  },
  scales: {
    x: {
      grid: { display: false },
      ticks: { color: '#6b7280', font: { size: 11 } }
    },
    y: {
      position: 'right',
      grid: { color: 'rgba(107, 114, 128, 0.1)' },
      ticks: { 
        color: '#6b7280',
        font: { size: 11 },
        callback: (value: number) => {
          if (selectedMetric.value === 'performance') {
            return `${value.toFixed(0)}%`
          }
          return formatCompactNumber(value)
        }
      }
    }
  },
  interaction: { intersect: false, mode: 'index' }
}))

// Asset distribution chart
const distributionChartData = computed(() => {
  if (!portfolioStats.value) return null

  const topAssets = portfolioStats.value.topAssets.slice(0, 6)
  const colors = [
    '#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#06b6d4'
  ]

  return {
    labels: topAssets.map(a => a.symbol),
    datasets: [{
      data: topAssets.map(a => a.weight),
      backgroundColor: colors,
      borderColor: '#fff',
      borderWidth: 2,
      borderRadius: 6
    }]
  }
})

const distributionChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        color: '#6b7280',
        font: { size: 11 },
        padding: 15,
        usePointStyle: true
      }
    },
    tooltip: {
      callbacks: {
        label: (context: any) => `${context.label}: ${context.parsed.toFixed(1)}%`
      }
    }
  }
}

// ============================================================================
// USER ACTIONS
// ============================================================================
function navigateTo(route: string) {
  router.push(route)
}

function navigateToCrypto(cryptoId: string) {
  router.push(`/dashboard/cryptos/${cryptoId}`)
}

// ============================================================================
// LIFECYCLE
// ============================================================================
onMounted(fetchWalletData)

// Watch for period changes
watch([chartPeriod, selectedMetric], () => {
  chartLoading.value = true
  setTimeout(() => {
    chartLoading.value = false
  }, 300)
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Portfolio Overview</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400">Track your investments and market performance</p>
      </div>
      <Button
        variant="outline"
        size="sm"
        class="gap-2"
        @click="refreshData"
        :disabled="refreshLoading"
      >
        <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': refreshLoading }" />
        {{ refreshLoading ? 'Refreshing...' : 'Refresh' }}
      </Button>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <template v-if="loading">
        <Card v-for="i in 4" :key="i">
          <CardContent class="p-6">
            <Skeleton class="h-8 w-32 mb-2" />
            <Skeleton class="h-4 w-24" />
          </CardContent>
        </Card>
      </template>
      <template v-else>
        <Card
          v-for="(stat, index) in statCards"
          :key="index"
          class="group hover:shadow-lg transition-shadow"
        >
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gray-100 dark:bg-gray-800">
                  <component :is="stat.icon" :class="[stat.color, 'w-5 h-5']" />
                </div>
                <span class="text-sm font-medium text-gray-600 dark:text-gray-400">{{ stat.title }}</span>
              </div>
              <Badge
                v-if="stat.title !== 'Available'"
                :class="stat.positive ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
              >
                {{ stat.change }}
              </Badge>
            </div>
            <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">{{ stat.value }}</div>
            <div v-if="stat.title === 'Available'" class="text-xs text-gray-500 dark:text-gray-400">
              {{ stat.change }}
            </div>
          </CardContent>
        </Card>
      </template>
    </div>

    <!-- Main Content -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Left Column - Charts -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Performance Chart -->
        <Card class="border border-gray-200 dark:border-gray-800">
          <CardHeader class="pb-3">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
              <div>
                <CardTitle class="text-lg font-semibold text-gray-900 dark:text-white">
                  Portfolio Performance
                </CardTitle>
              </div>
              <div class="flex items-center gap-2">
                <Select v-model="selectedMetric">
                  <SelectTrigger class="w-[140px] h-9">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="metric in metrics" 
                      :key="metric.value"
                      :value="metric.value"
                    >
                      {{ metric.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <div class="flex bg-gray-100 dark:bg-gray-800 rounded-lg p-1">
                  <Button
                    v-for="period in periods"
                    :key="period.value"
                    :variant="chartPeriod === period.value ? 'default' : 'ghost'"
                    size="sm"
                    class="h-7 px-3 text-xs"
                    @click="chartPeriod = period.value"
                  >
                    {{ period.label }}
                  </Button>
                </div>
              </div>
            </div>
          </CardHeader>
          <CardContent>
            <div class="h-[320px]">
              <template v-if="chartLoading || loading">
                <div class="h-full flex items-center justify-center">
                  <div class="text-center">
                    <div class="w-12 h-12 border-2 border-gray-300 border-t-gray-600 dark:border-gray-600 dark:border-t-gray-300 rounded-full animate-spin mx-auto mb-4"></div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Loading chart...</p>
                  </div>
                </div>
              </template>
              <template v-else-if="chartData">
                <Line :data="chartData" :options="chartOptions" />
              </template>
              <template v-else>
                <div class="h-full flex items-center justify-center text-gray-500 dark:text-gray-400">
                  <div class="text-center">
                    <BarChart3 class="w-12 h-12 mx-auto mb-3" />
                    <p>No chart data available</p>
                  </div>
                </div>
              </template>
            </div>
          </CardContent>
        </Card>

        <!-- Top Holdings -->
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle class="text-lg font-semibold text-gray-900 dark:text-white">
                Top Holdings
              </CardTitle>
              <Button
                variant="ghost"
                size="sm"
                class="gap-1 text-gray-600 dark:text-gray-400"
                @click="navigateTo('/dashboard/portfolio')"
              >
                View all
                <ChevronRight class="w-4 h-4" />
              </Button>
            </div>
          </CardHeader>
          <CardContent>
            <template v-if="loading">
              <div class="space-y-3">
                <div v-for="i in 3" :key="i" class="flex items-center gap-3 p-3">
                  <Skeleton class="w-10 h-10 rounded-xl" />
                  <div class="space-y-2 flex-1">
                    <Skeleton class="h-4 w-24" />
                    <Skeleton class="h-3 w-32" />
                  </div>
                  <Skeleton class="h-8 w-16" />
                </div>
              </div>
            </template>
            <template v-else-if="portfolioStats?.topAssets.length === 0">
              <div class="text-center py-8">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                  <Coins class="w-8 h-8 text-gray-400" />
                </div>
                <p class="text-gray-500 dark:text-gray-400 mb-4">No holdings yet</p>
                <Button 
                  class="bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900"
                  @click="navigateTo('/dashboard/cryptos')"
                >
                  Start Trading
                </Button>
              </div>
            </template>
            <template v-else>
              <div class="space-y-3">
                <div
                  v-for="asset in portfolioStats.topAssets"
                  :key="asset.symbol"
                  class="group flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600 transition-colors cursor-pointer"
                  @click="navigateToCrypto(asset.symbol)"
                >
                  <div class="flex items-center gap-4">
                    <div class="w-12 h-12 rounded-xl bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                      <span class="text-xl font-bold">{{ getCryptoIcon(asset.symbol) }}</span>
                    </div>
                    <div>
                      <div class="font-semibold text-gray-900 dark:text-white">{{ asset.symbol }}</div>
                      <div class="text-sm text-gray-500 dark:text-gray-400">{{ asset.name }}</div>
                    </div>
                  </div>
                  <div class="text-right">
                    <div class="font-semibold text-gray-900 dark:text-white">
                      {{ formatCurrency(asset.value) }}
                    </div>
                    <div :class="asset.pnlPercent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                      {{ formatPercentage(asset.pnlPercent) }}
                    </div>
                  </div>
                </div>
              </div>
            </template>
          </CardContent>
        </Card>
      </div>

      <!-- Right Column - Stats & Actions -->
      <div class="space-y-6">
        <!-- Quick Actions -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-gray-900 dark:text-white">
              Quick Actions
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <Button
              class="w-full justify-start h-12"
              @click="navigateTo('/dashboard/cryptos')"
            >
              <ShoppingCart class="w-5 h-5 mr-3" />
              Buy Crypto
            </Button>
            <Button
              variant="outline"
              class="w-full justify-start h-12"
              @click="navigateTo('/dashboard/transactions')"
            >
              <Activity class="w-5 h-5 mr-3" />
              View Transactions
            </Button>
            <Button
              variant="outline"
              class="w-full justify-start h-12"
              @click="navigateTo('/dashboard/portfolio')"
            >
              <Wallet class="w-5 h-5 mr-3" />
              Portfolio Details
            </Button>
          </CardContent>
        </Card>

        <!-- Asset Distribution -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-gray-900 dark:text-white">
              Asset Distribution
            </CardTitle>
          </CardHeader>
          <CardContent>
            <template v-if="loading">
              <div class="h-[200px] flex items-center justify-center">
                <Skeleton class="w-full h-full rounded-lg" />
              </div>
            </template>
            <template v-else-if="!distributionChartData">
              <div class="h-[200px] flex items-center justify-center text-gray-500 dark:text-gray-400">
                <div class="text-center">
                  <PieChart class="w-12 h-12 mx-auto mb-3" />
                  <p>No data available</p>
                </div>
              </div>
            </template>
            <template v-else>
              <div class="h-[200px]">
                <Bar :data="distributionChartData" :options="distributionChartOptions" />
              </div>
            </template>
          </CardContent>
        </Card>

        <!-- Top Performers -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-gray-900 dark:text-white">
              Top Performers
            </CardTitle>
          </CardHeader>
          <CardContent>
            <template v-if="loading">
              <div class="space-y-3">
                <div v-for="i in 3" :key="i" class="flex items-center gap-3">
                  <Skeleton class="w-8 h-8 rounded-full" />
                  <Skeleton class="h-4 flex-1" />
                  <Skeleton class="w-12 h-6" />
                </div>
              </div>
            </template>
            <template v-else-if="portfolioStats?.topPerformers.length === 0">
              <p class="text-center text-gray-500 dark:text-gray-400 py-4">No performance data</p>
            </template>
            <template v-else>
              <div class="space-y-4">
                <div
                  v-for="asset in portfolioStats.topPerformers"
                  :key="asset.symbol"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                      <span class="text-sm font-bold">{{ getCryptoIcon(asset.symbol) }}</span>
                    </div>
                    <div>
                      <div class="font-medium text-gray-900 dark:text-white">{{ asset.symbol }}</div>
                      <div class="text-xs text-gray-500 dark:text-gray-400">{{ asset.name }}</div>
                    </div>
                  </div>
                  <Badge
                    :class="asset.pnlPercent >= 0 ? 'bg-green-100 text-green-800 dark:bg-green-900/20 dark:text-green-400' : 'bg-red-100 text-red-800 dark:bg-red-900/20 dark:text-red-400'"
                  >
                    {{ formatPercentage(asset.pnlPercent) }}
                  </Badge>
                </div>
              </div>
            </template>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Recent Activity -->
    <Card>
      <CardHeader>
        <div class="flex items-center justify-between">
          <CardTitle class="text-lg font-semibold text-gray-900 dark:text-white">
            Recent Activity
          </CardTitle>
          <Button
            variant="ghost"
            size="sm"
            class="gap-1 text-gray-600 dark:text-gray-400"
            @click="navigateTo('/dashboard/transactions')"
          >
            View all
            <ChevronRight class="w-4 h-4" />
          </Button>
        </div>
      </CardHeader>
      <CardContent>
        <template v-if="loading">
          <div class="space-y-3">
            <div v-for="i in 3" :key="i" class="flex items-center justify-between p-3">
              <div class="flex items-center gap-3">
                <Skeleton class="w-10 h-10 rounded-lg" />
                <div class="space-y-2">
                  <Skeleton class="h-4 w-32" />
                  <Skeleton class="h-3 w-24" />
                </div>
              </div>
              <Skeleton class="h-8 w-20" />
            </div>
          </div>
        </template>
        <template v-else-if="!walletData?.assets?.length">
          <div class="text-center py-8">
            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
              <Activity class="w-8 h-8 text-gray-400" />
            </div>
            <p class="text-gray-500 dark:text-gray-400 mb-4">No recent activity</p>
            <Button 
              class="bg-gray-900 hover:bg-gray-800 dark:bg-white dark:hover:bg-gray-100 text-white dark:text-gray-900"
              @click="navigateTo('/dashboard/cryptos')"
            >
              Make Your First Trade
            </Button>
          </div>
        </template>
        <template v-else>
          <div class="space-y-3">
            <div
              v-for="asset in walletData.assets.slice(0, 3)"
              :key="asset.id"
              class="flex items-center justify-between p-4 rounded-lg border border-gray-200 dark:border-gray-700"
            >
              <div class="flex items-center gap-3">
                <div 
                  :class="[
                    'w-10 h-10 rounded-lg flex items-center justify-center',
                    asset.plus_value_percent >= 0 ? 'bg-green-100 dark:bg-green-900/20' : 'bg-red-100 dark:bg-red-900/20'
                  ]"
                >
                  <ArrowUpRight 
                    class="w-5 h-5"
                    :class="asset.plus_value_percent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'"
                  />
                </div>
                <div>
                  <div class="font-medium text-gray-900 dark:text-white">{{ asset.name }}</div>
                  <div class="text-sm text-gray-500 dark:text-gray-400">
                    {{ formatNumber(asset.quantity) }} {{ asset.symbol }}
                  </div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-semibold text-gray-900 dark:text-white">
                  {{ formatCurrency(asset.current_value_eur) }}
                </div>
                <div :class="asset.plus_value_percent >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                  {{ formatPercentage(asset.plus_value_percent) }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
  height: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 3px;
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

/* Smooth transitions */
.transition-shadow {
  transition: box-shadow 0.2s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-2px);
  transition: transform 0.2s ease-in-out;
}

/* Gradient border effect */
.border-gradient {
  border-image: linear-gradient(45deg, #3b82f6, #10b981, #8b5cf6) 1;
}
</style>