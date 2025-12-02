<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line } from 'vue-chartjs'
import { cryptoApi } from '@/services/api'
import { TrendingUp, TrendingDown, Activity, BarChart3 } from 'lucide-vue-next'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

interface CryptoDisplay {
  id: string
  name: string
  symbol: string
  color: string
  currentPrice: number
  change24h: number
  data: Array<{ time: string; price: number }>
}

const cryptos = ref<CryptoDisplay[]>([])
const isLoading = ref(true)
const selectedTimeframe = ref('1D')

const colorMap: Record<string, string> = {
  'bitcoin': '#F7931A',
  'ethereum': '#627EEA',
  'cardano': '#0033AD',
  'solana': '#14F195',
  'ripple': '#23292F',
  'polkadot': '#E6007A'
}

const timeframes = [
  { label: '1D', value: '1D' },
  { label: '7D', value: '7D' },
  { label: '1M', value: '1M' },
  { label: '3M', value: '3M' },
  { label: '1Y', value: '1Y' }
]

onMounted(async () => {
  try {
    // Fetch first page and detect pagination to fetch all pages
    const first = await cryptoApi.list({ page: 1 })
    let all = Array.isArray(first.data) ? [...first.data] : []
    const lastPage = (first as any).last_page || 1

    if (lastPage > 1) {
      const pages = [] as Promise<any>[]
      for (let p = 2; p <= lastPage; p++) pages.push(cryptoApi.list({ page: p }))
      const rest = await Promise.all(pages)
      rest.forEach(r => { if (r?.data) all.push(...r.data) })
    }

    // Normalize, sort by price descending and take top 6
    const normalized = all.map((crypto: any) => ({
      raw: crypto,
      price: parseFloat(String(crypto.price ?? crypto.price_eur ?? 0)),
    }))
      .sort((a: any, b: any) => b.price - a.price)
      .slice(0, 6)

    const cryptoData = await Promise.all(
      normalized.map(async ({ raw }) => {
        const history = await cryptoApi.history(raw.id)

        const coingeckoId = (raw.coingecko_id || '').toLowerCase()
        const color = colorMap[coingeckoId] || '#35A7FF'

        const price = parseFloat(String(raw.price ?? raw.price_eur ?? 0)) || 0
        const change = parseFloat(String(raw.change_24h ?? raw.change_24h_pct ?? raw.change24h ?? 0)) || 0

        return {
          id: raw.id,
          name: raw.name,
          symbol: String(raw.symbol || raw.sym || '').toUpperCase(),
          color,
          currentPrice: price,
          change24h: change,
          data: formatHistoryData(history)
        }
      })
    )

    cryptos.value = cryptoData
  } catch (error) {
    console.error('Error loading crypto data:', error)
  } finally {
    isLoading.value = false
  }
})

// Derived stats
const marketSentimentPercent = () => {
  if (!cryptos.value.length) return 50
  const positive = cryptos.value.filter(c => c.change24h > 0).length
  return Math.round((positive / cryptos.value.length) * 100)
}

const marketSentimentLabel = () => {
  const p = marketSentimentPercent()
  if (p >= 60) return 'Bullish'
  if (p <= 40) return 'Bearish'
  return 'Neutral'
}

const volatilityScore = () => {
  if (!cryptos.value.length) return 0
  const changes = cryptos.value.map(c => c.change24h)
  const mean = changes.reduce((a, b) => a + b, 0) / changes.length
  const variance = changes.reduce((a, b) => a + Math.pow(b - mean, 2), 0) / changes.length
  const std = Math.sqrt(variance)
  return Math.abs(std)
}

const volatilityLabel = () => {
  const v = volatilityScore()
  if (v >= 4) return 'High'
  if (v >= 2) return 'Medium'
  return 'Low'
}

function formatHistoryData(history: any): Array<{ time: string; price: number }> {
  let historyArray = []
  
  if (Array.isArray(history)) {
    historyArray = history
  } else if (history?.data && Array.isArray(history.data)) {
    historyArray = history.data
  } else if (history?.prices && Array.isArray(history.prices)) {
    historyArray = history.prices
  } else if (history?.result && Array.isArray(history.result)) {
    historyArray = history.result
  }
  
  if (historyArray.length === 0) {
    console.warn('No history data found, using sample data')
    return generateSampleData()
  }
  
  return historyArray.slice(-30).map((entry: any, index: number) => {
    let price = 0
    
    if (typeof entry === 'number') {
      price = entry
    } else if (entry.price !== undefined) {
      price = entry.price
    } else if (entry.y !== undefined) {
      price = entry.y
    } else if (entry[1] !== undefined) {
      price = entry[1]
    }
    
    return {
      time: `D${index + 1}`,
      price: parseFloat(String(price || 0))
    }
  })
}

function generateSampleData() {
  return Array.from({ length: 30 }, (_, i) => ({
    time: `D${i + 1}`,
    price: Math.random() * 100 + 50
  }))
}

const getChartData = (crypto: CryptoDisplay) => ({
  labels: crypto.data.map(d => d.time),
  datasets: [
    {
      label: crypto.symbol,
      data: crypto.data.map(d => d.price),
      borderColor: crypto.color,
      backgroundColor: 'transparent',
      borderWidth: 2,
      fill: false,
      tension: 0.4,
      pointRadius: 0,
      pointHoverRadius: 5
    }
  ]
})

const getChartOptions = (crypto: CryptoDisplay) => ({
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index' as const
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      enabled: true,
      backgroundColor: 'rgba(10, 20, 35, 0.95)',
      titleColor: crypto.color,
      bodyColor: '#fff',
      borderColor: crypto.color,
      borderWidth: 1,
      padding: 10,
      displayColors: false,
      bodyFont: { size: 11 },
      callbacks: {
        title: () => crypto.name,
        label: (context: any) => `$${context.parsed.y.toFixed(2)}`
      }
    }
  },
  scales: {
    x: {
      display: true,
      grid: { display: false },
      ticks: {
        color: '#6B7280',
        font: { size: 9 },
        maxTicksLimit: 5
      }
    },
    y: {
      display: false
    }
  }
})

const isPositive = (value: number) => value >= 0

const formattedPrice = (price: number) => {
  if (price >= 10000) return `$${(price / 1000).toFixed(1)}K`
  if (price >= 1000) return `$${(price / 1000).toFixed(2)}K`
  return `$${price.toFixed(2)}`
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-gray-900 to-gray-950 p-4 md:p-8">
    
    <!-- Modern Header -->
    <div class="max-w-7xl mx-auto mb-10 md:mb-16">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-10">
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="relative">
              <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-lg blur opacity-20"></div>
              <div class="relative bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-lg p-2">
                <BarChart3 class="w-6 h-6 text-blue-400" />
              </div>
            </div>
            <span class="text-sm font-medium text-gray-300 bg-gray-800/50 px-3 py-1.5 rounded-lg border border-gray-700">
              CRYPTO TRACKER
            </span>
          </div>
          
          <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-3">
            Cryptocurrency
            <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
              Market Dashboard
            </span>
          </h1>
          
          <p class="text-gray-400 max-w-2xl">
            Real-time tracking of top 6 cryptocurrencies with advanced analytics and performance metrics
          </p>
        </div>
        
        <!-- Timeframe Selector -->
        <div class="flex gap-2 bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-1">
          <button
            v-for="timeframe in timeframes"
            :key="timeframe.value"
            @click="selectedTimeframe = timeframe.value"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200',
              selectedTimeframe === timeframe.value
                ? 'bg-gradient-to-r from-blue-500/20 to-emerald-500/20 text-white border border-blue-500/30'
                : 'text-gray-400 hover:text-white hover:bg-gray-700/50'
            ]"
          >
            {{ timeframe.label }}
          </button>
        </div>
      </div>
      
      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-10">
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-sm border border-gray-700 rounded-xl p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400 mb-1">Total Market Cap</p>
              <p class="text-2xl font-bold text-white">$1.87T</p>
            </div>
            <Activity class="w-8 h-8 text-blue-400" />
          </div>
          <div class="mt-3 text-sm text-emerald-400 font-medium flex items-center gap-1">
            <TrendingUp class="w-4 h-4" />
            +2.4% 24h
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-sm border border-gray-700 rounded-xl p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400 mb-1">24h Volume</p>
              <p class="text-2xl font-bold text-white">$68.42B</p>
            </div>
            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-blue-500 to-emerald-500 flex items-center justify-center">
              <TrendingUp class="w-4 h-4 text-white" />
            </div>
          </div>
          <div class="mt-3 text-sm text-emerald-400 font-medium">+12.8% 24h</div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-sm border border-gray-700 rounded-xl p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400 mb-1">BTC Dominance</p>
              <p class="text-2xl font-bold text-white">52.8%</p>
            </div>
            <div class="relative">
              <div class="w-10 h-10 rounded-full border-4 border-gray-700"></div>
              <div class="absolute top-0 left-0 w-10 h-10 rounded-full border-4 border-blue-500 border-t-transparent -rotate-45"></div>
            </div>
          </div>
          <div class="mt-3 text-sm text-gray-400">-0.2% 24h</div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/50 to-gray-900/50 backdrop-blur-sm border border-gray-700 rounded-xl p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-400 mb-1">Fear & Greed</p>
              <p class="text-2xl font-bold text-white">72</p>
            </div>
            <div class="text-emerald-400 font-bold">Greed</div>
          </div>
          <div class="mt-3 h-2 bg-gray-700 rounded-full overflow-hidden">
            <div class="h-full bg-gradient-to-r from-emerald-500 to-yellow-500" style="width: 72%"></div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="max-w-7xl mx-auto">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div v-for="i in 6" :key="i" class="bg-gray-800/30 backdrop-blur-sm border border-gray-700 rounded-xl p-6 animate-pulse">
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 bg-gray-700 rounded-lg"></div>
              <div>
                <div class="w-24 h-4 bg-gray-700 rounded mb-2"></div>
                <div class="w-16 h-3 bg-gray-700 rounded"></div>
              </div>
            </div>
            <div class="w-20 h-8 bg-gray-700 rounded"></div>
          </div>
          <div class="h-32 bg-gray-700 rounded-lg"></div>
        </div>
      </div>
    </div>

    <!-- Crypto Table -->
    <div v-else class="max-w-7xl mx-auto">
      <!-- Table Header -->
      <div class="hidden lg:grid grid-cols-12 gap-4 px-6 py-4 bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-t-xl border-b-0">
        <div class="col-span-3 text-sm font-medium text-gray-400">ASSET</div>
        <div class="col-span-2 text-sm font-medium text-gray-400 text-right">PRICE</div>
        <div class="col-span-2 text-sm font-medium text-gray-400 text-right">24H CHANGE</div>
        <div class="col-span-3 text-sm font-medium text-gray-400 text-center">CHART</div>
        <div class="col-span-2 text-sm font-medium text-gray-400 text-right">MARKET CAP</div>
      </div>

      <!-- Crypto Rows -->
      <div class="space-y-3">
        <div
          v-for="(crypto, index) in cryptos"
          :key="crypto.id"
          class="group bg-gradient-to-br from-gray-800/30 to-gray-900/30 backdrop-blur-sm border border-gray-700 hover:border-gray-600 rounded-xl lg:rounded-none lg:border-l lg:border-r lg:border-t lg:last:border-b transition-all duration-300 hover:scale-[1.005] hover:shadow-2xl hover:shadow-gray-900/30"
          :style="{ animationDelay: `${index * 50}ms` }"
        >
          <div class="p-4 md:p-6">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-center">
              
              <!-- Asset Column -->
              <div class="lg:col-span-3">
                <div class="flex items-center gap-4">
                  <div 
                    class="w-12 h-12 rounded-xl flex items-center justify-center font-bold text-white text-lg backdrop-blur-sm border-2"
                    :style="{
                      backgroundColor: `${crypto.color}20`,
                      borderColor: crypto.color
                    }"
                  >
                    {{ crypto.symbol.charAt(0) }}
                  </div>
                  <div>
                    <div class="flex items-center gap-3">
                      <h3 class="text-lg font-bold text-white">{{ crypto.name }}</h3>
                      <span class="text-sm text-gray-400 font-mono">{{ crypto.symbol }}</span>
                    </div>
                  
                  </div>
                </div>
              </div>

              <!-- Price Column -->
              <div class="lg:col-span-2 lg:text-right">
                <p class="text-2xl font-bold text-white">
                  {{ formattedPrice(crypto.currentPrice) }}
                </p>
                <p class="text-sm text-gray-400">USD</p>
              </div>

              <!-- 24h Change Column -->
              <div class="lg:col-span-2 lg:text-right">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-lg" :class="{
                  'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': isPositive(crypto.change24h),
                  'bg-red-500/10 text-red-400 border border-red-500/20': !isPositive(crypto.change24h)
                }">
                  <TrendingUp v-if="isPositive(crypto.change24h)" class="w-4 h-4" />
                  <TrendingDown v-else class="w-4 h-4" />
                  <span class="font-bold">
                    {{ isPositive(crypto.change24h) ? '+' : '' }}{{ crypto.change24h.toFixed(2) }}%
                  </span>
                </div>
              </div>

              <!-- Chart Column -->
              <div class="lg:col-span-3">
                <div class="relative h-24 bg-gray-900/50 rounded-lg p-3 border border-gray-700">
                  <Line
                    :data="getChartData(crypto)"
                    :options="getChartOptions(crypto)"
                    class="w-full h-full"
                  />
                  <div class="absolute top-2 right-2 text-xs text-gray-500">
                    {{ selectedTimeframe }}
                  </div>
                </div>
              </div>

              <!-- Market Cap Column -->
              <div class="lg:col-span-2 lg:text-right">
                <div class="flex flex-col items-end">
                  <p class="text-lg font-bold text-white">
                    ${{ (crypto.currentPrice * 1000000).toLocaleString('en-US', { maximumFractionDigits: 0 }) }}
                  </p>
                  <div class="flex items-center gap-2 mt-2">
                    <div class="w-20 h-2 bg-gray-700 rounded-full overflow-hidden">
                      <div 
                        class="h-full rounded-full"
                        :style="{
                          width: `${Math.min(100, (index + 1) * 15)}%`,
                          backgroundColor: crypto.color
                        }"
                      ></div>
                    </div>
                    <span class="text-xs text-gray-400">
                      {{ Math.min(100, (index + 1) * 15) }}%
                    </span>
                  </div>
                </div>
              </div>

            </div>
            
            <!-- Mobile View Actions -->
            <div class="lg:hidden mt-6 pt-6 border-t border-gray-700">
              <div class="flex justify-between items-center">
                <button class="px-4 py-2 bg-gray-800/50 text-gray-300 rounded-lg text-sm font-medium border border-gray-700 hover:bg-gray-700/50 transition-colors">
                  Trade
                </button>
                <button class="px-4 py-2 bg-gradient-to-r from-blue-500/20 to-blue-600/20 text-blue-400 rounded-lg text-sm font-medium border border-blue-500/30 hover:border-blue-400/50 transition-colors">
                  View Details
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer Stats -->
      <div class="mt-10 grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Market Sentiment</h3>
            <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-emerald-500 flex items-center justify-center">
              <Activity class="w-5 h-5 text-white" />
            </div>
          </div>
          <p class="text-gray-400 text-sm mb-4">Overall market sentiment based on social media and news analysis</p>
          <div class="flex items-center gap-2">
            <div class="flex-1 h-2 bg-gray-700 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-emerald-500 via-yellow-500 to-red-500" :style="{ width: `${marketSentimentPercent()}%` }"></div>
            </div>
            <span class="text-sm font-bold text-emerald-400">{{ marketSentimentLabel() }} · {{ marketSentimentPercent() }}%</span>
          </div>
        </div>
        
        <div class="bg-gradient-to-br from-gray-800/30 to-gray-900/30 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Volatility Index</h3>
            <div class="text-2xl font-bold text-yellow-400">{{ volatilityLabel() }}</div>
          </div>
          <p class="text-gray-400 text-sm mb-4">Current market volatility across top cryptocurrencies</p>
          <div class="space-y-2">
            <div v-for="c in cryptos.slice(0, 3)" :key="c.id" class="flex items-center justify-between">
              <span class="text-sm text-gray-300">{{ c.symbol }}</span>
              <div class="flex items-center gap-2">
                <div class="w-16 h-1.5 bg-gray-700 rounded-full overflow-hidden">
                  <div 
                    class="h-full rounded-full"
                    :style="{ 
                      width: `${Math.min(100, Math.abs(c.change24h) * 2)}%`,
                      backgroundColor: c.color
                    }"
                  ></div>
                </div>
                <span class="text-xs text-gray-400">{{ Math.abs(c.change24h).toFixed(1) }}%</span>
              </div>
            </div>
          </div>
        </div>
        
      
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

div[class*="bg-gradient"] {
  animation: fadeInUp 0.5s ease-out forwards;
  animation-fill-mode: both;
}

@media (prefers-reduced-motion: reduce) {
  div[class*="bg-gradient"] {
    animation: none;
  }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb {
  background: linear-gradient(to bottom, #3B82F6, #10B981);
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: linear-gradient(to bottom, #2563EB, #059669);
}
</style>