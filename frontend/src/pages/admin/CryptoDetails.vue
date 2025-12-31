<script setup lang="ts">
import { CategoryScale, Chart as ChartJS, Filler, Legend, LinearScale, LineElement, PointElement, Title, Tooltip } from 'chart.js'
import { computed, onMounted, ref } from 'vue'
import { Line } from 'vue-chartjs'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const route = useRoute()
const router = useRouter()
const crypto = ref<any>(null)
const history = ref<any[]>([])
const historyMetadata = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// Fonctions utilitaires améliorées
function formatCurrency(value: any, decimals: number = 5): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return `0.${'0'.repeat(decimals)} €`
  
  const absValue = Math.abs(n)
  if (absValue > 0 && absValue < 0.01) {
    decimals = Math.max(decimals, 8)
  }
  
  const options = {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
    useGrouping: true
  }
  
  const formatted = n.toLocaleString('en-US', options)
  return `${formatted} €`
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (Math.abs(n) >= 1e12) return `${(n / 1e12).toFixed(2)}T €`
  if (Math.abs(n) >= 1e9) return `${(n / 1e9).toFixed(2)}B €`
  if (Math.abs(n) >= 1e6) return `${(n / 1e6).toFixed(2)}M €`
  if (Math.abs(n) >= 1e3) return `${(n / 1e3).toFixed(2)}K €`
  return formatCurrency(n, 2)
}

function formatPercentage(value: any, decimals: number = 2): string {
  const n = Number(value ?? 0)
  const sign = n >= 0 ? '+' : ''
  return `${sign}${n.toFixed(decimals)}%`
}

function formatDate(dateString: string): string {
  if (!dateString) return 'N/A'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric',
      year: 'numeric'
    })
  } catch {
    return dateString
  }
}

// Récupération des données
async function fetchData() {
  loading.value = true
  error.value = null
  
  try {
    const id = route.params.id
    
    // Récupérer les détails de la crypto
    const cryptoData = await api.crypto.show(id)
    crypto.value = cryptoData
    
    // Récupérer l'historique
    try {
      const historyData = await api.crypto.history(id)
      
      if (historyData.data?.prices) {
        history.value = historyData.data.prices || []
        historyMetadata.value = {
          symbol: historyData.data.symbol,
          name: historyData.data.name,
          count: historyData.data.count,
          from: historyData.data.from,
          to: historyData.data.to
        }
      } else if (historyData.prices) {
        history.value = historyData.prices || []
        historyMetadata.value = {
          symbol: historyData.symbol,
          name: historyData.name,
          count: historyData.count,
          from: historyData.from,
          to: historyData.to
        }
      } else {
        history.value = []
        historyMetadata.value = null
      }
    } catch (err) {
      console.warn('History not available:', err)
      history.value = []
      historyMetadata.value = null
    }
  } catch (err: any) {
    error.value = err.message || 'Error loading data'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Calcul des variations
const get7DayChange = computed(() => {
  if (history.value.length < 7) return 0
  const now = history.value[history.value.length - 1]?.[1] || 0
  const before = history.value[history.value.length - 7]?.[1] || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

const get30DayChange = computed(() => {
  if (history.value.length < 2) return 0
  const now = history.value[history.value.length - 1]?.[1] || 0
  const before = history.value[0]?.[1] || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

// Configuration du graphique
const chartData = computed(() => {
  if (!history.value?.length) return null

  const labels = history.value.map(p => 
    new Date(p[0]).toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric'
    })
  )
  
  const isPositive = history.value[history.value.length - 1]?.[1] >= history.value[0]?.[1]
  
  return {
    labels,
    datasets: [{
      label: 'Price (EUR)',
      data: history.value.map(p => p[1]),
      borderColor: isPositive ? '#01FF19' : '#FF5964',
      backgroundColor: isPositive ? 'rgba(1, 255, 25, 0.1)' : 'rgba(255, 89, 100, 0.1)',
      borderWidth: 3,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: isPositive ? '#01FF19' : '#FF5964',
      pointBorderColor: '#FFFFFF',
      pointBorderWidth: 2,
      pointRadius: 0,
      pointHoverRadius: 6,
      pointHitRadius: 10
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'nearest' as const,
    axis: 'x' as const,
    intersect: false
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#38618C',
      bodyColor: '#38618C',
      borderColor: '#38618C',
      borderWidth: 1,
      padding: 12,
      displayColors: false,
      callbacks: {
        label: (context: any) => `${formatCurrency(context.parsed.y, 5)}`,
        title: (context: any) => {
          const index = context[0].dataIndex
          if (history.value[index]) {
            return new Date(history.value[index][0]).toLocaleDateString('en-US', {
              weekday: 'short',
              year: 'numeric',
              month: 'short',
              day: 'numeric',
              hour: '2-digit',
              minute: '2-digit'
            })
          }
          return ''
        }
      }
    }
  },
  scales: {
    x: {
      grid: { 
        color: 'rgba(56, 97, 140, 0.1)',
        drawBorder: false
      },
      ticks: { 
        color: '#38618C',
        maxRotation: 0,
        font: {
          size: 11
        }
      }
    },
    y: {
      grid: { 
        color: 'rgba(56, 97, 140, 0.1)',
        drawBorder: false
      },
      ticks: { 
        color: '#38618C',
        callback: (value: number) => formatCurrency(value, 2),
        font: {
          size: 11
        }
      }
    }
  }
}

function goBack() {
  router.push('/dashboard/admin/cryptos')
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button 
          variant="outline"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white transition-all duration-200"
          @click="goBack"
        >
          ← Back
        </Button>
        <div v-if="crypto">
          <h1 class="text-3xl font-bold text-[#38618C]">{{ crypto.name }}</h1>
          <div class="text-sm text-gray-500">Cryptocurrency Analytics</div>
        </div>
      </div>
      <Button 
        :disabled="loading"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold transition-all duration-200"
        @click="fetchData"
      >
        🔄 Refresh
      </Button>
    </div>

    <!-- Loading State -->
    <Card v-if="loading" class="border-[#35A7FF]">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse space-y-4">
          <div class="text-4xl mb-4">⏳</div>
          <div class="h-2 bg-[#35A7FF]/20 rounded w-24 mx-auto"></div>
          <div class="text-gray-600">Loading cryptocurrency data...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-12 text-center">
        <div class="text-4xl mb-4">⚠️</div>
        <h3 class="text-xl font-semibold text-[#FF5964] mb-2">Error Loading Data</h3>
        <div class="text-gray-600 mb-4">{{ error }}</div>
        <Button 
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white transition-all duration-200"
          @click="fetchData"
        >
          Try Again
        </Button>
      </CardContent>
    </Card>

    <!-- Main Content -->
    <div v-else-if="crypto" class="space-y-6">
      <!-- Price Header Card -->
      <Card class="border-[#35A7FF] bg-gradient-to-r from-white to-blue-50">
        <CardContent class="p-6">
          <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <!-- Crypto Image -->
            <div class="h-20 w-20 rounded-full border-4 border-[#35A7FF] bg-white flex items-center justify-center flex-shrink-0 shadow-lg">
              <img 
                :src="crypto.image_url || crypto.image" 
                :alt="crypto.name"
                class="h-16 w-16 rounded-full object-cover"
                @error="(e) => {
                  const target = e.target as HTMLImageElement
                  if (target?.parentNode) {
                    target.style.display = 'none'
                  }
                }"
              />
              <div v-if="!crypto.image_url && !crypto.image" class="text-3xl">💎</div>
            </div>
            
            <!-- Price Info -->
            <div class="flex-1">
              <div class="flex flex-wrap items-center gap-2 mb-3">
                <h2 class="text-2xl font-bold text-[#38618C]">{{ crypto.name }}</h2>
                <Badge class="bg-[#38618C] text-white px-3 py-1 text-sm font-mono">
                  {{ String(crypto.symbol || '').toUpperCase() }}
                </Badge>
                <Badge 
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white px-3 py-1"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}
                </Badge>
              </div>
              
              <!-- Current Price -->
              <div class="flex items-baseline gap-3">
                <div class="text-4xl md:text-5xl font-bold text-[#35A7FF] tracking-tight">
                  {{ formatCurrency(crypto.price_eur, 5) }}
                </div>
                <div class="text-sm text-gray-500">Current Price</div>
              </div>
              
              <!-- Additional Prices -->
              <div v-if="crypto.price_usd || crypto.price_btc" class="flex flex-wrap gap-4 mt-3 text-sm">
                <div v-if="crypto.price_usd" class="flex items-center gap-1 bg-gray-50 px-3 py-1 rounded">
                  <span class="text-gray-600">USD:</span>
                  <span class="font-medium">{{ formatCurrency(crypto.price_usd, 5).replace('€', '$') }}</span>
                </div>
                <div v-if="crypto.price_btc" class="flex items-center gap-1 bg-gray-50 px-3 py-1 rounded">
                  <span class="text-gray-600">BTC:</span>
                  <span class="font-medium">{{ Number(crypto.price_btc).toFixed(8) }}</span>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Key Metrics Grid -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-all duration-200 group">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Market Cap</div>
            <div class="text-xl font-bold text-[#38618C] group-hover:text-[#35A7FF] transition-colors">
              {{ formatLargeNumber(crypto.market_cap) }}
            </div>
            <div v-if="crypto.market_cap_rank" class="text-xs text-gray-500 mt-1">
              Rank <Badge class="bg-gray-100 text-gray-800 ml-1">#{{ crypto.market_cap_rank }}</Badge>
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-all duration-200 group">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">24h Volume</div>
            <div class="text-xl font-bold text-[#38618C] group-hover:text-[#35A7FF] transition-colors">
              {{ formatLargeNumber(crypto.volume_24h) }}
            </div>
            <div v-if="crypto.market_cap" class="text-xs text-gray-500 mt-1">
              {{ (crypto.volume_24h / crypto.market_cap * 100).toFixed(1) }}% of market cap
            </div>
          </CardContent>
        </Card>

        <Card 
          class="border-gray-200 transition-all duration-200 group"
          :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'hover:border-[#01FF19]' : 'hover:border-[#FF5964]'"
        >
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">24h Change</div>
            <div 
              class="text-xl font-bold transition-colors"
              :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'text-[#01FF19] group-hover:text-[#01FF19]/90' : 'text-[#FF5964] group-hover:text-[#FF5964]/90'"
            >
              {{ formatPercentage(crypto.change_24h_pct) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">
              {{ formatCurrency(crypto.low_24h, 2) }} → {{ formatCurrency(crypto.high_24h, 2) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-all duration-200 group">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Current Price</div>
            <div class="text-xl font-bold text-[#35A7FF]">
              {{ formatCurrency(crypto.price_eur, 5) }}
            </div>
            <div class="text-xs text-gray-500 mt-1">Updated in real-time</div>
          </CardContent>
        </Card>
      </div>

      <!-- Chart Section -->
      <Card class="border-[#38618C]/20">
        <CardHeader class="pb-3">
          <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
              <CardTitle class="text-lg font-bold text-[#38618C] flex items-center gap-2">
                <span class="text-[#35A7FF]">📈</span>
                Price History
                <Badge class="ml-2 bg-[#35A7FF] text-white">30D</Badge>
              </CardTitle>
              <div v-if="historyMetadata" class="text-sm text-gray-500 mt-1">
                {{ historyMetadata.count }} data points from {{ formatDate(historyMetadata.from) }} to {{ formatDate(historyMetadata.to) }}
              </div>
            </div>
            <div class="flex items-center gap-2">
              <Badge 
                variant="outline" 
                class="border-[#38618C] text-[#38618C]"
              >
                {{ historyMetadata?.symbol || crypto.symbol }}
              </Badge>
              <Badge 
                :class="get30DayChange >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                class="text-white"
              >
                {{ formatPercentage(get30DayChange) }}
              </Badge>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="!chartData" class="h-[300px] flex flex-col items-center justify-center">
            <div class="text-4xl mb-3">📊</div>
            <div class="text-gray-500">No historical data available</div>
            <div class="text-sm text-gray-400 mt-1">Data will appear when available</div>
          </div>
          <div v-else class="h-[300px]">
            <Line :data="chartData" :options="chartOptions" />
          </div>
          
          <!-- Performance Overview -->
          <div v-if="history.length" class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-6">
            <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">24H</div>
                <div 
                  class="text-xl font-bold"
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}
                </div>
                <div class="text-xs text-gray-500 mt-1">
                  {{ formatCurrency(crypto.high_24h, 2) }} high
                </div>
              </CardContent>
            </Card>
            
            <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">7D</div>
                <div 
                  class="text-xl font-bold"
                  :class="get7DayChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(get7DayChange) }}
                </div>
                <div v-if="history.length >= 7" class="text-xs text-gray-500 mt-1">
                  Week ago: {{ formatCurrency(history[history.length - 7][1], 2) }}
                </div>
              </CardContent>
            </Card>
            
            <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">30D</div>
                <div 
                  class="text-xl font-bold"
                  :class="get30DayChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(get30DayChange) }}
                </div>
                <div v-if="history.length >= 2" class="text-xs text-gray-500 mt-1">
                  Month ago: {{ formatCurrency(history[0][1], 2) }}
                </div>
              </CardContent>
            </Card>
          </div>
        </CardContent>
      </Card>

      <!-- Detailed Information Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Asset Details -->
        <Card class="border-[#38618C]/20">
          <CardHeader>
            <CardTitle class="text-base font-bold text-[#38618C] flex items-center gap-2">
              <span class="text-[#35A7FF]">🔍</span>
              Asset Details
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-2 gap-4">
              <div class="space-y-1">
                <div class="text-xs text-gray-500">Symbol</div>
                <Badge class="bg-[#38618C] text-white font-mono px-3 py-1.5">
                  {{ String(crypto.symbol || '').toUpperCase() }}
                </Badge>
              </div>
              
            </div>
            
            <div class="space-y-1">
              <div class="text-xs text-gray-500">Category</div>
              <div class="font-semibold text-[#38618C]">{{ crypto.category || 'Uncategorized' }}</div>
            </div>
            
            <div class="space-y-1">
              <div class="text-xs text-gray-500">Website</div>
              <div v-if="crypto.website">
                <a 
                  :href="crypto.website" 
                  target="_blank"
                  class="text-[#35A7FF] hover:text-[#35A7FF]/80 hover:underline transition-colors text-sm flex items-center gap-1"
                >
                  Visit website
                  <span class="text-xs">↗</span>
                </a>
              </div>
              <div v-else class="text-gray-400 text-sm">Not available</div>
            </div>
          </CardContent>
        </Card>

        <!-- Supply & Market Data -->
        <Card class="border-[#38618C]/20">
          <CardHeader>
            <CardTitle class="text-base font-bold text-[#38618C] flex items-center gap-2">
              <span class="text-[#35A7FF]">📊</span>
              Supply & Valuation
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Supply Information -->
            <div v-if="crypto.circulating_supply || crypto.total_supply" class="space-y-3">
              <div class="text-sm font-medium text-gray-700">Supply Overview</div>
              <div class="grid grid-cols-2 gap-4">
                <div v-if="crypto.circulating_supply" class="space-y-1">
                  <div class="text-xs text-gray-500">Circulating</div>
                  <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.circulating_supply) }}</div>
                </div>
                <div v-if="crypto.total_supply" class="space-y-1">
                  <div class="text-xs text-gray-500">Total Supply</div>
                  <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.total_supply) }}</div>
                </div>
              </div>
              <div v-if="crypto.max_supply" class="space-y-1">
                <div class="text-xs text-gray-500">Max Supply</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.max_supply) }}</div>
              </div>
            </div>

            <!-- Valuation -->
            <div class="space-y-3 pt-3 border-t border-gray-100">
              <div class="text-sm font-medium text-gray-700">Valuation</div>
              <div class="space-y-2">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Market Cap</span>
                  <span class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</span>
                </div>
                <div v-if="crypto.fully_diluted_valuation" class="flex justify-between items-center">
                  <span class="text-sm text-gray-600">Fully Diluted</span>
                  <span class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.fully_diluted_valuation) }}</span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Historical Data Summary -->
      <Card v-if="historyMetadata" class="border-[#38618C]/20">
        <CardHeader>
          <CardTitle class="text-base font-bold text-[#38618C] flex items-center gap-2">
            <span class="text-[#35A7FF]">📅</span>
            Historical Data Summary
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div class="space-y-2 text-center">
              <div class="text-xs text-gray-500">Data Points</div>
              <div class="text-2xl font-bold text-[#35A7FF]">{{ historyMetadata.count }}</div>
              <div class="text-xs text-gray-500">Price records</div>
            </div>
            
            <div class="space-y-2 text-center">
              <div class="text-xs text-gray-500">Period</div>
              <div class="text-lg font-semibold text-[#38618C]">
                {{
                  Math.round(
                    (new Date(historyMetadata.to).getTime() - new Date(historyMetadata.from).getTime()) 
                    / (1000 * 60 * 60 * 24)
                  )
                }} days
              </div>
              <div class="text-xs text-gray-500">{{ formatDate(historyMetadata.from) }}</div>
            </div>
            
            <div v-if="history.length" class="space-y-2 text-center">
              <div class="text-xs text-gray-500">Starting Price</div>
              <div class="text-lg font-semibold text-gray-700">{{ formatCurrency(history[0][1], 5) }}</div>
              <div class="text-xs text-gray-500">Month ago</div>
            </div>
            
            <div v-if="history.length" class="space-y-2 text-center">
              <div class="text-xs text-gray-500">Current Price</div>
              <div class="text-lg font-semibold text-[#35A7FF]">{{ formatCurrency(history[history.length - 1][1], 5) }}</div>
              <div class="text-xs text-gray-500">Latest</div>
            </div>
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

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

/* Smooth transitions */
:deep(.transition-all) {
  transition-property: all;
}

:deep(.duration-200) {
  transition-duration: 200ms;
}

/* Chart improvements */
:deep(.chartjs-tooltip) {
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
  border-radius: 8px;
  backdrop-filter: blur(10px);
}

:deep(.chartjs-grid) {
  border-color: rgba(56, 97, 140, 0.05);
}
</style>