<script setup lang="ts">
import { onMounted, ref, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import {
  TrendingUp, TrendingDown, DollarSign,
  Wallet, Coins, BarChart3, ShoppingCart, PieChart, RefreshCw, Users, Eye, Clock,
  Percent, Target, Shield, Zap, ArrowRight,
  FileText, CreditCard
} from 'lucide-vue-next'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Chart, registerables } from 'chart.js'
import { Line, Bar } from 'vue-chartjs'
import { Skeleton } from '@/components/ui/skeleton'
import { Progress } from '@/components/ui/progress'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

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
const activeTab = ref('overview')

// Chart periods
const periods = [
  { value: '7d', label: '7D', color: '#35A7FF' },
  { value: '30d', label: '30D', color: '#01FF19' },
  { value: '90d', label: '90D', color: '#FF5964' },
  { value: '1y', label: '1Y', color: '#38618C' }
]

// Metrics for chart
const metrics = [
  { value: 'value', label: 'Portfolio Value', icon: DollarSign, color: '#35A7FF' },
  { value: 'pnl', label: 'Profit/Loss', icon: TrendingUp, color: '#01FF19' },
  { value: 'performance', label: 'Performance', icon: Percent, color: '#FF5964' }
]

// Quick stats
const quickStats = ref([
  { label: 'Total Users', value: '1,284', change: '+12.5%', icon: Users, color: '#35A7FF' },
  { label: 'Page Views', value: '42.5K', change: '+8.2%', icon: Eye, color: '#01FF19' },
  { label: 'Avg. Session', value: '4m 32s', change: '+2.1%', icon: Clock, color: '#FF5964' },
  { label: 'Conversion', value: '3.42%', change: '+0.8%', icon: Target, color: '#38618C' }
])

// Recent activities
const recentActivities = ref([
  { user: 'Alex Morgan', action: 'Purchased BTC', amount: '€2,500', time: '2 min ago', type: 'buy', icon: ShoppingCart },
  { user: 'Sarah Chen', action: 'Sold ETH', amount: '€1,850', time: '15 min ago', type: 'sell', icon: TrendingDown },
  { user: 'Marcus Lee', action: 'Withdrawal', amount: '€5,000', time: '1 hour ago', type: 'withdraw', icon: CreditCard },
  { user: 'Emma Wilson', action: 'Deposit', amount: '€10,000', time: '3 hours ago', type: 'deposit', icon: Wallet }
])

// Performance goals
const performanceGoals = ref([
  { label: 'Monthly Target', value: 75, target: 100, color: '#35A7FF' },
  { label: 'Risk Level', value: 30, target: 100, color: '#01FF19' },
  { label: 'Diversification', value: 85, target: 100, color: '#FF5964' },
  { label: 'Liquidity', value: 60, target: 100, color: '#38618C' }
])

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0
  }).format(value)
}

function formatCompactNumber(value: number): string {
  if (value >= 1e9) return `€${(value / 1e9).toFixed(1)}B`
  if (value >= 1e6) return `€${(value / 1e6).toFixed(1)}M`
  if (value >= 1e3) return `€${(value / 1e3).toFixed(1)}K`
  return `€${value.toFixed(0)}`
}

function formatPercentage(value: number): string {
  const sign = value >= 0 ? '+' : ''
  return `${sign}${value.toFixed(1)}%`
}

function formatNumber(value: number, decimals = 2): string {
  return value.toFixed(decimals).replace(/\.?0+$/, '')
}

function getCryptoIcon(symbol: string): string {
  const icons: Record<string, string> = {
    'BTC': '₿', 'ETH': 'Ξ', 'BNB': 'β', 'XRP': 'X', 'ADA': 'A',
    'SOL': '◎', 'DOT': '●', 'DOGE': 'Ð', 'AVAX': 'A', 'MATIC': 'M'
  }
  return icons[symbol] || symbol.charAt(0)
}

function getColorClass(color: string, type: 'bg' | 'text' | 'border' = 'bg'): string {
  const base = `${type}-[${color}]`
  if (type === 'bg') {
    return `${base} hover:${type}-[${color}]/90`
  }
  return base
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
  const todayChange = profitPercent

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

// Main stat cards
const mainStatCards = computed(() => [
  {
    title: 'Portfolio Value',
    value: portfolioStats.value ? formatCompactNumber(portfolioStats.value.totalValue) : '€0',
    change: portfolioStats.value ? formatPercentage(portfolioStats.value.todayChange) : '+0.0%',
    positive: portfolioStats.value?.todayChange >= 0,
    icon: DollarSign,
    color: '#35A7FF',
    gradient: 'from-[#35A7FF] to-[#38618C]'
  },
  {
    title: 'Available Balance',
    value: portfolioStats.value ? formatCurrency(portfolioStats.value.balance) : '€0',
    change: 'Ready to invest',
    positive: true,
    icon: Wallet,
    color: '#01FF19',
    gradient: 'from-[#01FF19] to-[#35A7FF]'
  },
  {
    title: 'Total Profit',
    value: portfolioStats.value ? formatCurrency(portfolioStats.value.profit) : '€0',
    change: portfolioStats.value ? formatPercentage(portfolioStats.value.profitPercent) : '+0.0%',
    positive: portfolioStats.value?.profitPercent >= 0,
    icon: TrendingUp,
    color: '#FF5964',
    gradient: 'from-[#FF5964] to-[#38618C]'
  },
  {
    title: 'Active Assets',
    value: portfolioStats.value ? portfolioStats.value.assets.toString() : '0',
    change: `${portfolioStats.value?.topAssets.length || 0} holdings`,
    positive: true,
    icon: Coins,
    color: '#38618C',
    gradient: 'from-[#38618C] to-[#35A7FF]'
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
      borderColor: '#35A7FF',
      backgroundColor: 'rgba(53, 167, 255, 0.1)',
      fill: true,
      tension: 0.4,
      borderWidth: 3,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointBackgroundColor: '#FF5964',
      pointBorderColor: '#FFFFFF',
      pointBorderWidth: 2
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
  const volatility = chartPeriod.value === '7d' ? 0.02 : 
                     chartPeriod.value === '30d' ? 0.04 : 0.06

  return Array.from({ length: days }, (_, i) => {
    const progress = i / days
    const trend = selectedMetric.value === 'pnl' ? 0 : 0.12
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
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      titleColor: '#FFFFFF',
      bodyColor: '#E2E8F0',
      borderColor: '#35A7FF',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (context: any) => {
          const value = context.parsed.y
          if (selectedMetric.value === 'performance') {
            return ` ${value.toFixed(1)}%`
          }
          return ` ${formatCurrency(value)}`
        }
      }
    }
  },
  scales: {
    x: {
      grid: { 
        display: true,
        color: 'rgba(226, 232, 240, 0.1)'
      },
      ticks: { 
        color: '#64748B',
        font: { size: 11, family: "'Inter', sans-serif" }
      }
    },
    y: {
      position: 'right',
      grid: { 
        color: 'rgba(226, 232, 240, 0.1)'
      },
      ticks: { 
        color: '#64748B',
        font: { size: 11, family: "'Inter', sans-serif" },
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

// Distribution chart data
const distributionChartData = computed(() => {
  if (!portfolioStats.value) return null

  const topAssets = portfolioStats.value.topAssets.slice(0, 6)
  const colors = ['#35A7FF', '#01FF19', '#FF5964', '#38618C', '#8B5CF6', '#F59E0B']

  return {
    labels: topAssets.map(a => a.symbol),
    datasets: [{
      data: topAssets.map(a => a.weight),
      backgroundColor: colors,
      borderColor: '#FFFFFF',
      borderWidth: 2,
      borderRadius: 8,
      spacing: 2
    }]
  }
})

const distributionChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  cutout: '65%',
  plugins: {
    legend: {
      position: 'right' as const,
      labels: {
        color: '#64748B',
        font: { 
          size: 12,
          family: "'Inter', sans-serif"
        },
        padding: 16,
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.95)',
      titleColor: '#FFFFFF',
      bodyColor: '#E2E8F0',
      borderColor: '#35A7FF',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 8,
      callbacks: {
        label: (context: any) => ` ${context.label}: ${context.parsed.toFixed(1)}%`
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
  <div class="space-y-8 p-6 bg-gradient-to-b from-slate-50 to-white min-h-screen">
    
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6 mb-2">
      <div>
        <h1 class="text-3xl font-bold text-[#0F172A] tracking-tight">
          Dashboard Overview
        </h1>
        <p class="text-[#64748B] mt-2 max-w-2xl">
          Welcome back! Track your portfolio performance, market insights, and trading activities in real-time.
        </p>
      </div>
      <div class="flex items-center gap-3">
        <Button
          variant="outline"
          class="gap-2 border-[#E2E8F0] text-[#64748B] hover:bg-[#35A7FF]/5 hover:text-[#35A7FF]"
          @click="refreshData"
          :disabled="refreshLoading"
        >
          <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': refreshLoading }" />
          {{ refreshLoading ? 'Syncing...' : 'Refresh Data' }}
        </Button>
        <Button class="gap-2 bg-[#FF5964] hover:bg-[#FF5964]/90 text-white">
          <Zap class="w-4 h-4" />
          Quick Trade
        </Button>
      </div>
    </div>

    <!-- Main Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
      <template v-if="loading">
        <Card v-for="i in 4" :key="i" class="border-[#E2E8F0]">
          <CardContent class="p-6">
            <Skeleton class="h-8 w-32 mb-3" />
            <Skeleton class="h-4 w-24" />
          </CardContent>
        </Card>
      </template>
      <template v-else>
        <Card
          v-for="(stat, index) in mainStatCards"
          :key="index"
          class="group relative overflow-hidden border-[#E2E8F0] hover:border-[#35A7FF]/30 hover:shadow-lg transition-all duration-300"
        >
          <div class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-5 transition-opacity duration-300" 
               :class="stat.gradient"></div>
          <CardContent class="p-6 relative">
            <div class="flex items-center justify-between mb-4">
              <div class="flex items-center gap-3">
                <div class="p-2.5 rounded-xl bg-white border border-[#E2E8F0] shadow-sm">
                  <component :is="stat.icon" :style="{ color: stat.color }" class="w-5 h-5" />
                </div>
                <span class="text-sm font-medium text-[#64748B]">{{ stat.title }}</span>
              </div>
              <Badge
                v-if="stat.title !== 'Available Balance'"
                class="text-xs font-medium px-2 py-0.5 border"
                :style="{
                  backgroundColor: `${stat.color}15`,
                  color: stat.color,
                  borderColor: `${stat.color}30`
                }"
              >
                {{ stat.change }}
              </Badge>
            </div>
            <div class="text-2xl font-bold text-[#0F172A] mb-1">{{ stat.value }}</div>
            <div v-if="stat.title === 'Available Balance'" class="text-sm text-[#64748B]">
              {{ stat.change }}
            </div>
          </CardContent>
        </Card>
      </template>
    </div>

    <!-- Tabs Navigation -->
    <Tabs v-model="activeTab" class="space-y-6">
      <TabsList class="bg-white border border-[#E2E8F0] p-1 rounded-xl">
        <TabsTrigger 
          value="overview" 
          class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#35A7FF] data-[state=active]:to-[#38618C] data-[state=active]:text-white rounded-lg px-6"
        >
          Overview
        </TabsTrigger>
        <TabsTrigger 
          value="portfolio" 
          class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#35A7FF] data-[state=active]:to-[#38618C] data-[state=active]:text-white rounded-lg px-6"
        >
          Portfolio
        </TabsTrigger>
        <TabsTrigger 
          value="analytics" 
          class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#35A7FF] data-[state=active]:to-[#38618C] data-[state=active]:text-white rounded-lg px-6"
        >
          Analytics
        </TabsTrigger>
        <TabsTrigger 
          value="insights" 
          class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#35A7FF] data-[state=active]:to-[#38618C] data-[state=active]:text-white rounded-lg px-6"
        >
          Insights
        </TabsTrigger>
      </TabsList>

      <TabsContent value="overview" class="space-y-6">
        <!-- Performance Chart & Quick Stats -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <!-- Performance Chart -->
          <Card class="lg:col-span-2 border-[#E2E8F0]">
            <CardHeader class="pb-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                  <CardTitle class="text-xl font-semibold text-[#0F172A]">
                    Performance Analytics
                  </CardTitle>
                  <CardDescription class="text-[#64748B] mt-1">
                    Track your portfolio growth over time
                  </CardDescription>
                </div>
                <div class="flex items-center gap-3">
                  <Select v-model="selectedMetric">
                    <SelectTrigger class="w-[160px] h-10 border-[#E2E8F0] focus:border-[#35A7FF]">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem 
                        v-for="metric in metrics" 
                        :key="metric.value"
                        :value="metric.value"
                        class="flex items-center gap-2"
                      >
                        <component :is="metric.icon" :style="{ color: metric.color }" class="w-4 h-4" />
                        {{ metric.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>
            </CardHeader>
            <CardContent>
              <!-- Period Selector -->
              <div class="flex gap-2 mb-6">
                <Button
                  v-for="period in periods"
                  :key="period.value"
                  :variant="chartPeriod === period.value ? 'default' : 'outline'"
                  size="sm"
                  class="h-8 px-4 text-sm font-medium transition-all"
                  :style="chartPeriod === period.value ? {
                    backgroundColor: period.color,
                    borderColor: period.color
                  } : {}"
                  @click="chartPeriod = period.value"
                >
                  {{ period.label }}
                </Button>
              </div>

              <!-- Chart -->
              <div class="h-[320px] relative">
                <template v-if="chartLoading || loading">
                  <div class="absolute inset-0 flex items-center justify-center bg-white/50 backdrop-blur-sm rounded-lg">
                    <div class="text-center">
                      <div class="w-10 h-10 border-3 border-[#35A7FF]/20 border-t-[#35A7FF] rounded-full animate-spin mx-auto mb-3"></div>
                      <p class="text-sm text-[#64748B]">Loading chart data...</p>
                    </div>
                  </div>
                </template>
                <template v-else-if="chartData">
                  <Line :data="chartData" :options="chartOptions" />
                </template>
                <template v-else>
                  <div class="h-full flex items-center justify-center text-[#64748B]">
                    <div class="text-center">
                      <BarChart3 class="w-12 h-12 mx-auto mb-3 text-[#35A7FF]" />
                      <p>No chart data available</p>
                    </div>
                  </div>
                </template>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Stats & Goals -->
          <div class="space-y-6">
            <!-- Quick Stats -->
            <Card class="border-[#E2E8F0]">
              <CardHeader>
                <CardTitle class="text-lg font-semibold text-[#0F172A]">
                  Quick Stats
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div
                  v-for="stat in quickStats"
                  :key="stat.label"
                  class="flex items-center justify-between p-3 rounded-lg hover:bg-[#F8FAFC] transition-colors"
                >
                  <div class="flex items-center gap-3">
                    <div class="p-2 rounded-lg" :style="{ backgroundColor: `${stat.color}15` }">
                      <component :is="stat.icon" :style="{ color: stat.color }" class="w-4 h-4" />
                    </div>
                    <div>
                      <div class="text-sm font-medium text-[#64748B]">{{ stat.label }}</div>
                      <div class="text-xl font-bold text-[#0F172A]">{{ stat.value }}</div>
                    </div>
                  </div>
                  <Badge
                    class="text-xs font-medium px-2 py-0.5"
                    :style="{
                      backgroundColor: `${stat.color}15`,
                      color: stat.color
                    }"
                  >
                    {{ stat.change }}
                  </Badge>
                </div>
              </CardContent>
            </Card>

            <!-- Performance Goals -->
            <Card class="border-[#E2E8F0]">
              <CardHeader>
                <CardTitle class="text-lg font-semibold text-[#0F172A]">
                  Performance Goals
                </CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div
                  v-for="goal in performanceGoals"
                  :key="goal.label"
                  class="space-y-2"
                >
                  <div class="flex justify-between text-sm">
                    <span class="text-[#64748B]">{{ goal.label }}</span>
                    <span class="font-medium text-[#0F172A]">{{ goal.value }}%</span>
                  </div>
                  <Progress 
                    :model-value="goal.value" 
                    class="h-2"
                    :style="{
                      '--progress-background': goal.color
                    }"
                  />
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        <!-- Top Holdings & Asset Distribution -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Holdings -->
          <Card class="border-[#E2E8F0]">
            <CardHeader>
              <div class="flex items-center justify-between">
                <CardTitle class="text-lg font-semibold text-[#0F172A]">
                  Top Holdings
                </CardTitle>
                <Button
                  variant="ghost"
                  size="sm"
                  class="gap-1 text-[#35A7FF] hover:text-[#38618C] hover:bg-[#35A7FF]/10"
                  @click="navigateTo('/dashboard/portfolio')"
                >
                  View All
                  <ArrowRight class="w-4 h-4" />
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <template v-if="loading">
                <div class="space-y-4">
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
                  <div class="w-16 h-16 mx-auto mb-4 rounded-xl bg-gradient-to-br from-[#35A7FF]/10 to-[#38618C]/10 flex items-center justify-center">
                    <Coins class="w-8 h-8 text-[#35A7FF]" />
                  </div>
                  <p class="text-[#64748B] mb-4">No holdings yet</p>
                  <Button 
                    class="bg-gradient-to-r from-[#35A7FF] to-[#38618C] text-white hover:opacity-90"
                    @click="navigateTo('/dashboard/cryptos')"
                  >
                    Start Investing
                  </Button>
                </div>
              </template>
              <template v-else>
                <div class="space-y-3">
                  <div
                    v-for="(asset, index) in portfolioStats.topAssets"
                    :key="asset.symbol"
                    class="group flex items-center justify-between p-4 rounded-xl border border-[#E2E8F0] hover:border-[#35A7FF]/30 hover:shadow-sm transition-all duration-200 cursor-pointer"
                    @click="navigateToCrypto(asset.symbol)"
                  >
                    <div class="flex items-center gap-4">
                      <div class="relative">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-[#35A7FF]/10 to-[#38618C]/10 flex items-center justify-center">
                          <span class="text-xl font-bold text-[#0F172A]">{{ getCryptoIcon(asset.symbol) }}</span>
                        </div>
                        <div class="absolute -top-1 -right-1 w-5 h-5 bg-white border border-[#E2E8F0] rounded-full flex items-center justify-center text-xs font-bold text-[#35A7FF]">
                          {{ index + 1 }}
                        </div>
                      </div>
                      <div>
                        <div class="font-semibold text-[#0F172A]">{{ asset.symbol }}</div>
                        <div class="text-sm text-[#64748B]">{{ asset.name }}</div>
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="font-semibold text-[#0F172A]">
                        {{ formatCompactNumber(asset.value) }}
                      </div>
                      <div :class="asset.pnlPercent >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'" class="text-sm font-medium">
                        {{ formatPercentage(asset.pnlPercent) }}
                      </div>
                    </div>
                  </div>
                </div>
              </template>
            </CardContent>
          </Card>

          <!-- Asset Distribution -->
          <Card class="border-[#E2E8F0]">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-[#0F172A]">
                Asset Distribution
              </CardTitle>
            </CardHeader>
            <CardContent>
              <template v-if="loading">
                <div class="h-[300px] flex items-center justify-center">
                  <Skeleton class="w-full h-full rounded-lg" />
                </div>
              </template>
              <template v-else-if="!distributionChartData">
                <div class="h-[300px] flex items-center justify-center text-[#64748B]">
                  <div class="text-center">
                    <PieChart class="w-12 h-12 mx-auto mb-3 text-[#35A7FF]" />
                    <p>No distribution data</p>
                  </div>
                </div>
              </template>
              <template v-else>
                <div class="h-[300px]">
                  <Bar :data="distributionChartData" :options="distributionChartOptions" />
                </div>
              </template>
            </CardContent>
          </Card>
        </div>

      
      </TabsContent>

      <!-- Other tabs content would go here -->
      <TabsContent value="portfolio">
        <div class="text-center py-12 text-[#64748B]">
          <FileText class="w-12 h-12 mx-auto mb-4 text-[#35A7FF]" />
          <p>Portfolio analysis coming soon...</p>
        </div>
      </TabsContent>
      <TabsContent value="analytics">
        <div class="text-center py-12 text-[#64748B]">
          <BarChart3 class="w-12 h-12 mx-auto mb-4 text-[#01FF19]" />
          <p>Advanced analytics coming soon...</p>
        </div>
      </TabsContent>
      <TabsContent value="insights">
        <div class="text-center py-12 text-[#64748B]">
          <Shield class="w-12 h-12 mx-auto mb-4 text-[#FF5964]" />
          <p>Market insights coming soon...</p>
        </div>
      </TabsContent>
    </Tabs>

    <!-- Footer Stats -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-[#E2E8F0]">
      <div class="text-center">
        <div class="text-2xl font-bold text-[#35A7FF]">24/7</div>
        <div class="text-sm text-[#64748B]">Live Support</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-[#01FF19]">99.9%</div>
        <div class="text-sm text-[#64748B]">Uptime</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-[#FF5964]">256-bit</div>
        <div class="text-sm text-[#64748B]">Security</div>
      </div>
      <div class="text-center">
        <div class="text-2xl font-bold text-[#38618C]">10</div>
        <div class="text-sm text-[#64748B]">Assets</div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

::-webkit-scrollbar-track {
  background: #F1F5F9;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: #CBD5E1;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94A3B8;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Gradient text */
.gradient-text {
  background: linear-gradient(135deg, #35A7FF, #38618C);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
}

/* Custom progress bar */
:deep(.progress) {
  --progress-background: #35A7FF;
}

:deep(.progress-bar) {
  background-color: var(--progress-background) !important;
}

/* Chart tooltip customizations */
:deep(.chartjs-tooltip) {
  box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15) !important;
  backdrop-filter: blur(8px) !important;
}

/* Smooth hover effects */
.hover-lift {
  transition: transform 0.2s ease;
}

.hover-lift:hover {
  transform: translateY(-2px);
}

/* Glass effect */
.glass-effect {
  background: rgba(255, 255, 255, 0.7);
  backdrop-filter: blur(10px);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Animation for stats */
@keyframes pulse-glow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.7; }
}

.pulse-glow {
  animation: pulse-glow 2s ease-in-out infinite;
}
</style>