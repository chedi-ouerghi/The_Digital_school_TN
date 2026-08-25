<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Skeleton } from '@/components/ui/skeleton'
import api from '@/services/api'
import type { Wallet as WalletType } from '@/types'
import { Chart, registerables } from 'chart.js'
import { BarChart3, Euro, PieChart, RefreshCw, TrendingDown, TrendingUp, Wallet } from 'lucide-vue-next'
import { computed, onMounted, onUnmounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import type { PortfolioData } from '../types'

Chart.register(...registerables)

const router = useRouter()
const loading = ref(false)
const error = ref<string | null>(null)
const portfolioStats = ref<PortfolioData | null>(null)
const userBalance = ref(0)
const growthChart = ref<Chart | null>(null)
const distributionChart = ref<Chart | null>(null)

// Formatters
const formatCurrency = (value: any): string => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('en-US', { style: 'currency', currency: 'EUR' })
}

const formatNumber = (num: any, decimals = 2) => {
  if (num === null || num === undefined) return '0'
  return parseFloat(num).toLocaleString('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  })
}

// Fetch data with TypeScript fix
const fetchData = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await api.auth.getProfileStats()
    portfolioStats.value = response.data
    
    // Get wallet balance with proper typing
    try {
      const walletResponse = await api.wallet.list()
      // Handle both single wallet object and array response
      if (Array.isArray(walletResponse)) {
        const wallet = walletResponse[0] as WalletType
        userBalance.value = Number(wallet?.balance_eur || 0)
      } else if (walletResponse && typeof walletResponse === 'object') {
        // Try different possible property names
        const walletData = (walletResponse as any).wallet || walletResponse
        userBalance.value = Number(walletData?.balance_eur || walletData?.balance || 0)
      }
    } catch (walletError) {
      console.error('Error loading wallet balance:', walletError)
    }
    
    setTimeout(() => {
      createGrowthChart()
      createDistributionChart()
    }, 100)
  } catch (e: any) {
    error.value = e.message || 'Failed to load data'
  } finally {
    loading.value = false
  }
}

// Chart functions
const destroyCharts = () => {
  [growthChart.value, distributionChart.value].forEach(chart => {
    chart?.destroy()
  })
  growthChart.value = null
  distributionChart.value = null
}

const createGrowthChart = () => {
  if (!portfolioStats.value?.growth) return
  
  const ctx = document.getElementById('growthChart') as HTMLCanvasElement
  if (!ctx) return
  
  destroyCharts()
  
  const growth = portfolioStats.value.growth
  const isPositive = growth.data[0] < growth.data[growth.data.length - 1]
  
  growthChart.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: growth.labels,
      datasets: [{
        label: 'Portfolio Value',
        data: growth.data,
        borderColor: isPositive ? '#10b981' : '#ef4444',
        backgroundColor: isPositive ? 'rgba(16, 185, 129, 0.1)' : 'rgba(239, 68, 68, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 0,
        pointHoverRadius: 6,
        pointBackgroundColor: isPositive ? '#10b981' : '#ef4444',
        pointBorderColor: '#fff',
        pointBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          backgroundColor: 'rgba(17, 24, 39, 0.95)',
          titleColor: '#fff',
          bodyColor: '#fff',
          padding: 12,
          borderColor: isPositive ? '#10b981' : '#ef4444',
          borderWidth: 1,
          displayColors: false,
          callbacks: { label: (context) => formatCurrency(context.parsed.y) }
        }
      },
      scales: {
        y: {
          beginAtZero: false,
          grid: { color: 'rgba(0, 0, 0, 0.05)' },
          ticks: {
            callback: (value) => formatCurrency(value),
            font: { size: 11 },
            color: '#6b7280'
          }
        },
        x: {
          grid: { display: false },
          ticks: {
            font: { size: 11 },
            color: '#6b7280'
          }
        }
      }
    }
  })
}

const createDistributionChart = () => {
  if (!portfolioStats.value?.distribution) return
  
  const ctx = document.getElementById('distributionChart') as HTMLCanvasElement
  if (!ctx) return
  
  const distribution = portfolioStats.value.distribution
  const colors = ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b', '#ef4444', '#ec4899']
  
  distributionChart.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: distribution.labels,
      datasets: [{
        data: distribution.data,
        backgroundColor: colors.slice(0, distribution.data.length),
        borderWidth: 2,
        borderColor: '#fff',
        hoverOffset: 15
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      cutout: '70%',
      plugins: {
        legend: {
          position: 'right',
          labels: {
            padding: 15,
            font: { size: 12 },
            color: '#374151',
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(17, 24, 39, 0.95)',
          titleColor: '#fff',
          bodyColor: '#fff',
          padding: 12,
          borderColor: '#3b82f6',
          borderWidth: 1,
          displayColors: true,
          callbacks: {
            label: (context) => {
              const label = context.label || ''
              const value = context.parsed || 0
              const total = context.dataset.data.reduce((a: number, b: number) => a + b, 0)
              const percentage = ((value / total) * 100).toFixed(1)
              return `${label}: ${formatCurrency(value)} (${percentage}%)`
            }
          }
        }
      }
    }
  })
}

// Computed
const stats = computed(() => portfolioStats.value?.stats || null)
const distributionData = computed(() => portfolioStats.value?.distribution.raw || [])

const profitTrend = computed(() => {
  if (!stats.value) return 'neutral'
  return (stats.value.total_profit || 0) >= 0 ? 'up' : 'down'
})

const totalValue = computed(() => {
  if (!stats.value) return 0
  return stats.value.total_invested + stats.value.total_profit
})

// Lifecycle
onMounted(fetchData)
onUnmounted(destroyCharts)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-brand-dark to-brand-blue bg-clip-text text-transparent">
          Portfolio Overview
        </h1>
        <p class="text-sm text-gray-500 mt-1">Track your investments and performance</p>
      </div>
      <Button 
        variant="outline" 
        :disabled="loading"
        class="border-gray-300 hover:border-brand-blue hover:bg-brand-blue/5 transition-all"
        @click="fetchData"
      >
        <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
        {{ loading ? 'Refreshing...' : 'Refresh Data' }}
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <!-- Total Invested -->
      <Card class="group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 border-gray-200 hover:border-brand-dark transition-all duration-300 hover:shadow-lg">
        <div class="absolute top-4 right-4 w-10 h-10 rounded-lg bg-brand-dark/10 flex items-center justify-center">
          <Euro class="w-5 h-5 text-brand-dark" />
        </div>
        <CardContent class="p-6 pt-8">
          <div class="text-sm font-medium text-gray-500 mb-2">Total Invested</div>
          <div class="text-2xl font-bold text-brand-dark mb-1">
            {{ loading ? '...' : formatCurrency(stats?.total_invested || 0) }}
          </div>
          <div class="text-xs text-gray-400">Capital invested</div>
        </CardContent>
      </Card>

      <!-- Available Balance -->
      <Card class="group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 border-gray-200 hover:border-emerald-500 transition-all duration-300 hover:shadow-lg">
        <div class="absolute top-4 right-4 w-10 h-10 rounded-lg bg-emerald-100 flex items-center justify-center">
          <Wallet class="w-5 h-5 text-emerald-600" />
        </div>
        <CardContent class="p-6 pt-8">
          <div class="text-sm font-medium text-gray-500 mb-2">Available Balance</div>
          <div class="text-2xl font-bold text-emerald-600 mb-1">
            {{ loading ? '...' : formatCurrency(userBalance || 0) }}
          </div>
          <div class="text-xs text-gray-400">Ready to invest</div>
        </CardContent>
      </Card>

      <!-- Total Profit -->
      <Card
:class="[
        'group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 border-gray-200 transition-all duration-300 hover:shadow-lg',
        profitTrend === 'up' ? 'hover:border-emerald-500' : 'hover:border-red-500'
      ]">
        <div
:class="[
          'absolute top-4 right-4 w-10 h-10 rounded-lg flex items-center justify-center',
          profitTrend === 'up' ? 'bg-emerald-100' : 'bg-red-100'
        ]">
          <TrendingUp v-if="profitTrend === 'up'" class="w-5 h-5 text-emerald-600" />
          <TrendingDown v-else class="w-5 h-5 text-red-600" />
        </div>
        <CardContent class="p-6 pt-8">
          <div class="text-sm font-medium text-gray-500 mb-2">Total Profit/Loss</div>
          <div
:class="[
            'text-2xl font-bold mb-1 flex items-center gap-2',
            profitTrend === 'up' ? 'text-emerald-600' : 'text-red-600'
          ]">
            {{ loading ? '...' : formatCurrency(stats?.total_profit || 0) }}
          </div>
          <div class="text-xs text-gray-400">
            {{ loading ? '...' : `${formatNumber(stats?.profit_percentage || 0)}%` }}
          </div>
        </CardContent>
      </Card>

      <!-- Transactions -->
      <Card class="group relative overflow-hidden bg-gradient-to-br from-white to-gray-50 border-gray-200 hover:border-indigo-500 transition-all duration-300 hover:shadow-lg">
        <div class="absolute top-4 right-4 w-10 h-10 rounded-lg bg-indigo-100 flex items-center justify-center">
          <BarChart3 class="w-5 h-5 text-indigo-600" />
        </div>
        <CardContent class="p-6 pt-8">
          <div class="text-sm font-medium text-gray-500 mb-2">Transactions</div>
          <div class="text-2xl font-bold text-indigo-600 mb-1">
            {{ loading ? '...' : stats?.total_transactions || 0 }}
          </div>
          <div class="text-xs text-gray-400">Total operations</div>
        </CardContent>
      </Card>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Growth Chart -->
      <div class="lg:col-span-2">
        <Card class="border-gray-200 hover:shadow-xl transition-all duration-300">
          <CardHeader class="pb-4">
            <div class="flex items-center justify-between">
              <CardTitle class="flex items-center gap-2 text-lg font-semibold">
                <TrendingUp class="w-5 h-5 text-brand-dark" />
                Portfolio Evolution
              </CardTitle>
              <span v-if="stats" class="text-sm font-medium px-3 py-1 rounded-full bg-gray-100">
                Current: {{ formatCurrency(totalValue) }}
              </span>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="loading" class="h-72 flex items-center justify-center">
              <Skeleton class="h-full w-full rounded-lg" />
            </div>
            <div v-else-if="!portfolioStats?.growth" class="h-72 flex flex-col items-center justify-center text-gray-400">
              <BarChart3 class="w-12 h-12 mb-3 opacity-50" />
              <p>No growth data available</p>
            </div>
            <div v-else class="h-72">
              <canvas id="growthChart"></canvas>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Distribution Chart -->
      <div>
        <Card class="border-gray-200 hover:shadow-xl transition-all duration-300">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-2 text-lg font-semibold">
              <PieChart class="w-5 h-5 text-brand-dark" />
              Asset Distribution
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div v-if="loading" class="h-72 flex items-center justify-center">
              <Skeleton class="h-48 w-48 rounded-full" />
            </div>
            <div v-else-if="distributionData.length === 0" class="h-72 flex flex-col items-center justify-center text-gray-400">
              <PieChart class="w-12 h-12 mb-3 opacity-50" />
              <p>No assets yet</p>
            </div>
            <div v-else class="h-72">
              <canvas id="distributionChart"></canvas>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Asset Details Table -->
    <Card v-if="distributionData.length > 0 && !loading" class="border-gray-200 hover:shadow-xl transition-all duration-300 overflow-hidden">
      <CardHeader class="bg-gradient-to-r from-gray-50 to-white border-b">
        <CardTitle class="flex items-center gap-2">
          <span class="text-lg font-semibold">Asset Details</span>
          <Badge variant="outline" class="ml-auto">
            {{ distributionData.length }} assets
          </Badge>
        </CardTitle>
      </CardHeader>
      <CardContent class="p-0">
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b bg-gray-50">
                <th class="text-left py-4 px-6 font-semibold text-gray-700">Asset</th>
                <th class="text-right py-4 px-6 font-semibold text-gray-700">Quantity</th>
                <th class="text-right py-4 px-6 font-semibold text-gray-700">Value</th>
                <th class="text-right py-4 px-6 font-semibold text-gray-700">Allocation</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="item in distributionData" 
                :key="item.crypto_symbol"
                class="border-b hover:bg-gray-50 transition-colors group"
              >
                <td class="py-4 px-6">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-100 to-indigo-100 flex items-center justify-center">
                      <span class="font-bold text-blue-600">{{ item.crypto_symbol.charAt(0) }}</span>
                    </div>
                    <div>
                      <div class="font-semibold text-gray-800">{{ item.crypto_name }}</div>
                      <div class="text-sm text-gray-500">{{ item.crypto_symbol }}</div>
                    </div>
                  </div>
                </td>
                <td class="text-right py-4 px-6">
                  <div class="font-mono text-sm font-medium text-gray-800">
                    {{ formatNumber(parseFloat(item.quantity), 8) }}
                  </div>
                </td>
                <td class="text-right py-4 px-6">
                  <div class="font-bold text-brand-blue">
                    {{ formatCurrency(item.value) }}
                  </div>
                </td>
                <td class="text-right py-4 px-6">
                  <div class="flex items-center justify-end gap-2">
                    <Badge
:class="[
                      'px-3 py-1 rounded-full font-semibold',
                      Number(item.percentage) > 20 ? 'bg-blue-100 text-blue-800' :
                      Number(item.percentage) > 10 ? 'bg-indigo-100 text-indigo-800' :
                      'bg-gray-100 text-gray-800'
                    ]">
                      {{ formatNumber(item.percentage) }}%
                    </Badge>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

    <!-- Action Buttons -->
    <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
      <Button 
        variant="outline"
        class="border-gray-300 hover:border-gray-400 hover:bg-gray-50 px-6"
        @click="router.push('/dashboard/transactions')"
      >
        View Transaction History
      </Button>
      <Button 
        class="bg-gradient-to-r from-brand-dark to-brand-blue hover:from-[#2d4f6f] hover:to-[#2a8ee6] text-white px-8 shadow-md hover:shadow-lg transition-all"
        @click="router.push('/dashboard/cryptos')"
      >
        Explore Cryptocurrencies
      </Button>
    </div>

    <!-- Error Message -->
    <div v-if="error" class="mt-6 p-4 bg-red-50 border border-red-200 rounded-lg">
      <div class="flex items-center text-red-700">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
          </svg>
        </div>
        <div class="ml-3">
          <p class="text-sm">{{ error }}</p>
        </div>
        <div class="ml-auto pl-3">
          <Button variant="ghost" size="sm" class="text-red-700 hover:bg-red-100" @click="fetchData">
            Try Again
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom animations */
@keyframes shimmer {
  0% { background-position: -1000px 0; }
  100% { background-position: 1000px 0; }
}

.animate-shimmer {
  animation: shimmer 2s infinite linear;
  background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
  background-size: 1000px 100%;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Hover effects */
.group:hover .group-hover\:scale-105 {
  transform: scale(1.05);
}

</style>