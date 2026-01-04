<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import api from '@/services/api'
import {
  Activity,
  AlertCircle,
  ArrowLeft,
  BarChart3,
  Calendar,
  CheckCircle,
  ChevronRight,
  Clock,
  DollarSign,
  Download,
  ExternalLink,
  Globe,
  RefreshCw,
  Share2,
  Shield,
  Star,
  TrendingDown,
  TrendingUp
} from 'lucide-vue-next'
import { computed, onMounted, ref, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
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
  category: string
  website: string
  price_eur: string
  market_cap: string
  change_24h_pct: string
  created_at: string
  updated_at: string
  image_url: string
  price: string
  change_24h: string
}

// State
const route = useRoute()
const router = useRouter()
const crypto = ref<CryptoData | null>(null)
const history = ref<HistoryEntry[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const selectedPeriod = ref<'24h' | '7d' | '30d'>('30d')
const hoveredData = ref<HistoryEntry | null>(null)
const activeTab = ref('overview')
const currentPage = ref(1)
const itemsPerPage = 7

// Computed Properties
const isPositiveTrend = computed(() => {
  if (!history.value.length) return true
  const first = history.value[0].price
  const last = history.value[history.value.length - 1].price
  return last >= first
})

const currentPrice = computed(() => {
  return crypto.value?.price_eur ? parseFloat(crypto.value.price_eur) : 0
})

const change24h = computed(() => {
  return crypto.value?.change_24h_pct ? parseFloat(crypto.value.change_24h_pct) : 0
})

const marketCap = computed(() => {
  return crypto.value?.market_cap ? parseFloat(crypto.value.market_cap) : 0
})

const filteredHistory = computed(() => {
  if (!history.value.length) return []
  
  const days = selectedPeriod.value === '24h' ? 1 : selectedPeriod.value === '7d' ? 7 : 30
  const cutoffDate = new Date()
  cutoffDate.setDate(cutoffDate.getDate() - days)
  
  return history.value.filter(h => new Date(h.date) >= cutoffDate)
})

const chartData = computed(() => {
  const data = filteredHistory.value
  if (data.length < 2) return null
  
  const prices = data.map(d => d.price)
  const volumes = data.map(d => d.volume)
  const minPrice = Math.min(...prices)
  const maxPrice = Math.max(...prices)
  const maxVolume = Math.max(...volumes)
  const priceRange = maxPrice - minPrice || 1
  
  return {
    data,
    minPrice,
    maxPrice,
    maxVolume,
    priceRange,
    points: data.map((d, i) => ({
      ...d,
      x: (i / (data.length - 1)) * 100,
      y: 100 - ((d.price - minPrice) / priceRange) * 100,
      volumeHeight: (d.volume / maxVolume) * 40,
      index: i
    }))
  }
})

const chartMetrics = computed(() => {
  if (!chartData.value) return null
  const data = chartData.value.data
  const high = Math.max(...data.map(d => d.price))
  const low = Math.min(...data.map(d => d.price))
  const change = ((data[data.length - 1].price - data[0].price) / data[0].price) * 100
  const avgVolume = data.reduce((sum, d) => sum + d.volume, 0) / data.length
  
  return { high, low, change, avgVolume }
})

// Pagination
const paginatedHistory = computed(() => {
  const sorted = filteredHistory.value.slice().reverse()
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return sorted.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredHistory.value.length / itemsPerPage)
})

// Format functions
const formatCurrency = (v: number, decimals = 2) => 
  `€${v.toLocaleString('en-US', { minimumFractionDigits: decimals, maximumFractionDigits: decimals })}`

const formatLargeNumber = (v: number) => {
  if (v >= 1e12) return `€${(v / 1e12).toFixed(2)}T`
  if (v >= 1e9) return `€${(v / 1e9).toFixed(2)}B`
  if (v >= 1e6) return `€${(v / 1e6).toFixed(2)}M`
  if (v >= 1e3) return `€${(v / 1e3).toFixed(2)}K`
  return formatCurrency(v)
}

const formatPercent = (v: number) => {
  const sign = v >= 0 ? '+' : ''
  return `${sign}${v.toFixed(2)}%`
}

const formatDate = (date: string) => {
  const d = new Date(date)
  return d.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: selectedPeriod.value === '30d' ? 'numeric' : undefined
  })
}

const formatDateTime = (date: string) => {
  const d = new Date(date)
  return d.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Fetch data
async function fetchData() {
  loading.value = true
  error.value = null
  try {
    const cryptoId = route.params.id as string
    crypto.value = await api.crypto.show(cryptoId)
    const historyData = await api.crypto.history(cryptoId)
    history.value = historyData.history || []
    currentPage.value = 1
  } catch (e: any) {
    error.value = e?.message || 'Failed to load cryptocurrency data'
    console.error('Error fetching data:', e)
  } finally {
    loading.value = false
  }
}

// Chart interaction
function handleChartHover(index: number) {
  hoveredData.value = filteredHistory.value[index]
}

function handleChartLeave() {
  hoveredData.value = null
}

// Navigation
function goBack() {
  router.back()
}

function openWebsite() {
  if (crypto.value?.website) {
    window.open(crypto.value.website, '_blank')
  }
}

// Lifecycle
onMounted(() => fetchData())
watch(selectedPeriod, () => {
  hoveredData.value = null
  currentPage.value = 1
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-6">
    <!-- Header -->
    <div class="mb-6">
      <Button 
        variant="ghost" 
        class="gap-2 text-gray-600 hover:text-gray-900 hover:bg-gray-100"
        @click="goBack"
      >
        <ArrowLeft class="h-4 w-4" />
        Back to Cryptocurrencies
      </Button>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="w-12 h-12 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-4"></div>
      <p class="text-gray-600">Loading cryptocurrency data...</p>
    </div>

    <!-- Error State -->
    <Alert v-else-if="error" variant="destructive" class="mb-6">
      <AlertCircle class="h-5 w-5" />
      <AlertDescription>{{ error }}</AlertDescription>
      <Button variant="outline" class="mt-3" @click="fetchData">
        <RefreshCw class="h-4 w-4 mr-2" />
        Retry
      </Button>
    </Alert>

    <!-- Main Content -->
    <div v-else-if="crypto" class="space-y-6">
      <!-- Crypto Header -->
      <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
        <div class="flex items-start gap-4">
          <div class="relative">
            <div class="w-16 h-16 md:w-20 md:h-20 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 p-3 shadow-lg">
              <img 
                :src="crypto.image_url" 
                :alt="crypto.name" 
                class="w-full h-full object-contain"
                @error="(e) => e.target.style.display = 'none'"
              />
              <div v-if="!crypto.image_url" class="w-full h-full flex items-center justify-center">
                <span class="text-2xl text-blue-600">💎</span>
              </div>
            </div>
            <Badge 
              :class="[
                'absolute -top-2 -right-2',
                change24h >= 0 ? 'bg-green-500' : 'bg-red-500'
              ]"
            >
              <component :is="change24h >= 0 ? TrendingUp : TrendingDown" class="h-3 w-3" />
            </Badge>
          </div>
          
          <div>
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-3xl md:text-4xl font-bold text-gray-900">{{ crypto.name }}</h1>
              <Badge variant="outline" class="text-sm font-mono bg-white shadow-sm">
                {{ crypto.symbol.toUpperCase() }}
              </Badge>
            </div>
            
            <div class="flex flex-wrap items-center gap-3 mb-3">
              <Badge variant="secondary" class="gap-1.5">
                <Shield class="h-3 w-3" />
                {{ crypto.category }}
              </Badge>
              
              <Button 
                variant="ghost" 
                size="sm" 
                class="gap-1.5 text-blue-600 hover:text-blue-700 hover:bg-blue-50"
                @click="openWebsite"
              >
                <Globe class="h-4 w-4" />
                Website
              </Button>
              
              <div class="text-sm text-gray-500">
                Added {{ new Date(crypto.created_at).toLocaleDateString('en-US', { month: 'long', year: 'numeric' }) }}
              </div>
            </div>
            
            <div class="flex items-baseline gap-4">
              <span class="text-3xl md:text-4xl font-bold text-blue-600">
                {{ formatCurrency(currentPrice) }}
              </span>
              <Badge 
                :class="[
                  'text-base font-semibold px-3 py-1',
                  change24h >= 0 
                    ? 'bg-green-50 text-green-700 border-green-200' 
                    : 'bg-red-50 text-red-700 border-red-200'
                ]"
              >
                <component :is="change24h >= 0 ? TrendingUp : TrendingDown" class="h-4 w-4 mr-1.5" />
                {{ formatPercent(change24h) }}
              </Badge>
            </div>
          </div>
        </div>
        
        <div class="flex gap-2">
          <Button variant="outline" class="gap-2">
            <Star class="h-4 w-4" />
            Watchlist
          </Button>
          <Button variant="outline" class="gap-2">
            <Share2 class="h-4 w-4" />
            Share
          </Button>
          <Button variant="outline" class="gap-2">
            <Download class="h-4 w-4" />
            Export
          </Button>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-2 rounded-lg bg-blue-50">
                <DollarSign class="h-5 w-5 text-blue-600" />
              </div>
              <Badge variant="outline" class="text-xs">Live</Badge>
            </div>
            <div class="text-sm text-gray-500 font-medium mb-1">Market Cap</div>
            <div class="text-2xl font-bold text-gray-900">{{ formatLargeNumber(marketCap) }}</div>
            <div class="text-xs text-gray-500 mt-2">Total market valuation</div>
          </CardContent>
        </Card>

        <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-2 rounded-lg bg-green-50">
                <Activity class="h-5 w-5 text-green-600" />
              </div>
              <Badge variant="outline" class="text-xs">24h</Badge>
            </div>
            <div class="text-sm text-gray-500 font-medium mb-1">Price Change</div>
            <div class="text-2xl font-bold" :class="change24h >= 0 ? 'text-green-600' : 'text-red-600'">
              {{ formatPercent(change24h) }}
            </div>
            <div class="text-xs text-gray-500 mt-2">Last 24 hours</div>
          </CardContent>
        </Card>

        <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-2 rounded-lg bg-purple-50">
                <BarChart3 class="h-5 w-5 text-purple-600" />
              </div>
              <Badge variant="outline" class="text-xs">Volume</Badge>
            </div>
            <div class="text-sm text-gray-500 font-medium mb-1">24h Volume</div>
            <div v-if="history.length > 0" class="text-2xl font-bold text-purple-600">
              {{ formatLargeNumber(history[history.length - 1]?.volume || 0) }}
            </div>
            <div class="text-xs text-gray-500 mt-2">Trading volume</div>
          </CardContent>
        </Card>

        <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="p-2 rounded-lg bg-amber-50">
                <Calendar class="h-5 w-5 text-amber-600" />
              </div>
              <Badge variant="outline" class="text-xs">Data</Badge>
            </div>
            <div class="text-sm text-gray-500 font-medium mb-1">Historical Points</div>
            <div class="text-2xl font-bold text-amber-600">{{ filteredHistory.length }}</div>
            <div class="text-xs text-gray-500 mt-2">Price entries</div>
          </CardContent>
        </Card>
      </div>

      <!-- Chart Section -->
      <Card class="border border-gray-200">
        <CardHeader>
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <CardTitle class="text-lg font-semibold">Price History</CardTitle>
              <CardDescription>Historical price data and trends</CardDescription>
            </div>
            
            <div class="flex gap-2">
              <Button
                v-for="period in ['24h', '7d', '30d'] as const"
                :key="period"
                :variant="selectedPeriod === period ? 'default' : 'outline'"
                size="sm"
                class="gap-2"
                @click="selectedPeriod = period"
              >
                <Clock class="h-3 w-3" />
                {{ period }}
              </Button>
            </div>
          </div>
        </CardHeader>
        
        <CardContent>
          <div v-if="chartData" class="space-y-6">
            <!-- Chart Metrics -->
            <div v-if="chartMetrics" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
              <div class="text-center p-4 rounded-lg bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200">
                <div class="text-sm text-blue-700 font-medium mb-1">Period High</div>
                <div class="text-xl font-bold text-blue-800">{{ formatCurrency(chartMetrics.high) }}</div>
              </div>
              
              <div class="text-center p-4 rounded-lg bg-gradient-to-r from-green-50 to-green-100 border border-green-200">
                <div class="text-sm text-green-700 font-medium mb-1">Period Low</div>
                <div class="text-xl font-bold text-green-800">{{ formatCurrency(chartMetrics.low) }}</div>
              </div>
              
              <div class="text-center p-4 rounded-lg bg-gradient-to-r from-purple-50 to-purple-100 border border-purple-200">
                <div class="text-sm text-purple-700 font-medium mb-1">Period Change</div>
                <div 
                  class="text-xl font-bold"
                  :class="chartMetrics.change >= 0 ? 'text-green-700' : 'text-red-700'"
                >
                  {{ formatPercent(chartMetrics.change) }}
                </div>
              </div>
            </div>

            <!-- Chart Container -->
            <div class="relative">
              <!-- SVG Chart -->
              <svg 
                class="w-full h-80"
                viewBox="0 0 1200 300"
                preserveAspectRatio="xMidYMid meet"
                @mouseleave="handleChartLeave"
              >
                <!-- Gradient Definitions -->
                <defs>
                  <linearGradient id="chart-gradient" x1="0%" y1="0%" x2="0%" y2="100%">
                    <stop 
                      offset="0%" 
                      :stop-color="isPositiveTrend ? '#10b981' : '#ef4444'"
                      stop-opacity="0.2"
                    />
                    <stop 
                      offset="100%" 
                      :stop-color="isPositiveTrend ? '#10b981' : '#ef4444'"
                      stop-opacity="0"
                    />
                  </linearGradient>
                </defs>

                <!-- Grid -->
                <g class="opacity-30">
                  <line x1="80" y1="40" x2="80" y2="340" stroke="#d1d5db" stroke-width="1.5" />
                  <line x1="80" y1="340" x2="1120" y2="340" stroke="#d1d5db" stroke-width="1.5" />
                  
                  <template v-for="i in 5" :key="`grid-${i}`">
                    <line 
                      x1="80" 
                      :y1="60 + (i * 70)" 
                      x2="1120" 
                      :y2="60 + (i * 70)" 
                      stroke="#e5e7eb" 
                      stroke-width="1" 
                      stroke-dasharray="4,4"
                    />
                  </template>
                </g>

                <!-- Area Path -->
                <path 
                  :d="`M ${chartData.points[0].x * 10.4 + 80} ${chartData.points[0].y * 2.8 + 40} 
                    ${chartData.points.slice(1).map(p => `L ${p.x * 10.4 + 80} ${p.y * 2.8 + 40}`).join(' ')} 
                    L ${chartData.points[chartData.points.length - 1].x * 10.4 + 80} 340
                    L 80 340 Z`"
                  fill="url(#chart-gradient)"
                />

                <!-- Line Path -->
                <polyline 
                  :points="chartData.points.map(p => `${p.x * 10.4 + 80},${p.y * 2.8 + 40}`).join(' ')"
                  fill="none"
                  :stroke="isPositiveTrend ? '#10b981' : '#ef4444'"
                  stroke-width="3"
                  stroke-linecap="round"
                  stroke-linejoin="round"
                />

                <!-- Data Points -->
                <g class="cursor-crosshair">
                  <circle 
                    v-for="(point, i) in chartData.points.filter((_, idx) => idx % Math.ceil(chartData.points.length / 15) === 0 || idx === chartData.points.length - 1)"
                    :key="i"
                    :cx="point.x * 10.4 + 80"
                    :cy="point.y * 2.8 + 40"
                    r="5"
                    :fill="hoveredData?.index === point.index ? 'white' : isPositiveTrend ? '#10b981' : '#ef4444'"
                    :stroke="isPositiveTrend ? '#10b981' : '#ef4444'"
                    stroke-width="2"
                    class="transition-all duration-200 hover:r-6 hover:stroke-gray-900"
                    @mouseenter="handleChartHover(point.index)"
                  />
                </g>

                <!-- Hover Line -->
                <line 
                  v-if="hoveredData"
                  :x1="hoveredData.x * 10.4 + 80"
                  y1="40"
                  :x2="hoveredData.x * 10.4 + 80"
                  y2="340"
                  stroke="#9ca3af"
                  stroke-width="1.5"
                  stroke-dasharray="5,5"
                />
              </svg>

              <!-- Hover Tooltip -->
              <div 
                v-if="hoveredData"
                class="absolute z-50 transform -translate-x-1/2"
                :style="{ left: `${hoveredData.x}%` }"
              >
                <div class="bg-gray-900 text-white rounded-lg shadow-xl p-4 min-w-[200px] transform -translate-y-full -mt-4">
                  <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-300">
                      {{ formatDate(hoveredData.date) }}
                    </span>
                    <Badge 
                      :class="[
                        'text-xs',
                        hoveredData.change_24h_pct >= 0 ? 'bg-green-500' : 'bg-red-500'
                      ]"
                    >
                      {{ formatPercent(hoveredData.change_24h_pct) }}
                    </Badge>
                  </div>
                  <div class="text-xl font-bold text-blue-400 mb-1">
                    {{ formatCurrency(hoveredData.price, 2) }}
                  </div>
                  <div class="text-sm text-gray-400">
                    Volume: {{ formatLargeNumber(hoveredData.volume) }}
                  </div>
                </div>
              </div>
            </div>
          </div>
          
          <div v-else class="h-80 flex items-center justify-center text-gray-500">
            <div class="text-center">
              <BarChart3 class="h-12 w-12 mx-auto mb-3 text-gray-400" />
              <p>No chart data available</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Additional Information -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Crypto Details -->
        <Card class="border border-gray-200 lg:col-span-2">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Cryptocurrency Details</CardTitle>
            <CardDescription>Technical information and specifications</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-4">
                <div>
                  <div class="text-sm text-gray-500 font-medium mb-1">Cryptocurrency ID</div>
                  <div class="font-mono text-gray-900">{{ crypto.id }}</div>
                </div>
                
                <div>
                  <div class="text-sm text-gray-500 font-medium mb-1">Category</div>
                  <Badge variant="outline" class="text-sm">{{ crypto.category }}</Badge>
                </div>
                
                <div>
                  <div class="text-sm text-gray-500 font-medium mb-1">Last Updated</div>
                  <div class="text-gray-900">{{ formatDateTime(crypto.updated_at) }}</div>
                </div>
              </div>
              
              <div class="space-y-4">
                <div>
                  <div class="text-sm text-gray-500 font-medium mb-1">Official Website</div>
                  <Button 
                    variant="link" 
                    class="p-0 h-auto text-blue-600 hover:text-blue-700"
                    @click="openWebsite"
                  >
                    <ExternalLink class="h-4 w-4 mr-2" />
                    {{ crypto.website.replace('https://', '') }}
                  </Button>
                </div>
                
                <div>
                  <div class="text-sm text-gray-500 font-medium mb-1">Created Date</div>
                  <div class="text-gray-900">{{ formatDate(crypto.created_at) }}</div>
                </div>
                
                <div>
                  <div class="text-sm text-gray-500 font-medium mb-1">Symbol</div>
                  <div class="font-bold text-gray-900">{{ crypto.symbol.toUpperCase() }}</div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Performance Summary -->
        <Card class="border border-gray-200">
          <CardHeader>
            <CardTitle class="text-lg font-semibold">Performance Summary</CardTitle>
            <CardDescription>Historical performance metrics</CardDescription>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm text-gray-700">30-Day Performance</span>
                  <span 
                    v-if="chartMetrics"
                    :class="[
                      'text-sm font-medium',
                      chartMetrics.change >= 0 ? 'text-green-600' : 'text-red-600'
                    ]"
                  >
                    {{ formatPercent(chartMetrics.change) }}
                  </span>
                </div>
                <Progress 
                  :model-value="Math.min(Math.abs(chartMetrics?.change || 0), 100)"
                  :class="chartMetrics?.change >= 0 ? 'bg-green-100' : 'bg-red-100'"
                />
              </div>
              
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm text-gray-700">Price Stability</span>
                  <span class="text-sm font-medium text-blue-600">High</span>
                </div>
                <Progress model-value="85" class="bg-blue-100" />
              </div>
              
              <div>
                <div class="flex items-center justify-between mb-2">
                  <span class="text-sm text-gray-700">Market Adoption</span>
                  <span class="text-sm font-medium text-purple-600">Established</span>
                </div>
                <Progress model-value="92" class="bg-purple-100" />
              </div>
              
              <Alert v-if="change24h < -5" class="border-amber-200 bg-amber-50">
                <AlertCircle class="h-5 w-5 text-amber-600" />
                <AlertDescription class="text-amber-800 text-sm">
                  Significant price decline detected in the last 24 hours.
                </AlertDescription>
              </Alert>
              
              <Alert v-else-if="change24h > 5" class="border-green-200 bg-green-50">
                <CheckCircle class="h-5 w-5 text-green-600" />
                <AlertDescription class="text-green-800 text-sm">
                  Strong positive momentum in the last 24 hours.
                </AlertDescription>
              </Alert>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Price History Table -->
      <Card v-if="filteredHistory.length" class="border border-gray-200">
        <CardHeader>
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <CardTitle class="text-lg font-semibold">Price History</CardTitle>
              <CardDescription>Detailed historical price data</CardDescription>
            </div>
            <div class="text-sm text-gray-500">
              Showing {{ filteredHistory.length }} data points
            </div>
          </div>
        </CardHeader>
        
        <CardContent>
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-gray-200">
                  <th class="px-6 py-3 text-left text-sm font-semibold text-gray-900">Date</th>
                  <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Price</th>
                  <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">24h Change</th>
                  <th class="px-6 py-3 text-right text-sm font-semibold text-gray-900">Volume</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200">
                <tr 
                  v-for="(entry, i) in paginatedHistory" 
                  :key="i"
                  class="hover:bg-gray-50 transition-colors duration-150"
                >
                  <td class="px-6 py-4">
                    <div class="flex items-center gap-2">
                      <Calendar class="h-4 w-4 text-gray-400" />
                      <span class="text-sm text-gray-900">{{ formatDate(entry.date) }}</span>
                    </div>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <span class="font-semibold text-blue-600">{{ formatCurrency(entry.price, 2) }}</span>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <Badge 
                      :class="[
                        'text-xs font-medium',
                        entry.change_24h_pct >= 0 
                          ? 'bg-green-50 text-green-700 border-green-200' 
                          : 'bg-red-50 text-red-700 border-red-200'
                      ]"
                    >
                      <component 
                        :is="entry.change_24h_pct >= 0 ? TrendingUp : TrendingDown" 
                        class="h-3 w-3 mr-1" 
                      />
                      {{ formatPercent(entry.change_24h_pct) }}
                    </Badge>
                  </td>
                  <td class="px-6 py-4 text-right">
                    <span class="text-sm text-gray-600">{{ formatLargeNumber(entry.volume) }}</span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination -->
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-6 border-t border-gray-200">
            <div class="text-sm text-gray-600">
              Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to 
              {{ Math.min(currentPage * itemsPerPage, filteredHistory.length) }} of 
              {{ filteredHistory.length }} entries
            </div>
            
            <div class="flex items-center gap-2">
              <Button
                variant="outline"
                size="sm"
                class="h-8 w-8 p-0"
                :disabled="currentPage === 1"
                @click="currentPage = Math.max(1, currentPage - 1)"
              >
                <ChevronRight class="h-4 w-4 rotate-180" />
              </Button>
              
              <div class="flex items-center gap-1">
                <Button
                  v-for="page in Math.min(5, totalPages)"
                  :key="page"
                  :variant="currentPage === page ? 'default' : 'outline'"
                  size="sm"
                  class="h-8 w-8 p-0 text-sm"
                  @click="currentPage = page"
                >
                  {{ page }}
                </Button>
              </div>
              
              <Button
                variant="outline"
                size="sm"
                class="h-8 w-8 p-0"
                :disabled="currentPage === totalPages"
                @click="currentPage = Math.min(totalPages, currentPage + 1)"
              >
                <ChevronRight class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>