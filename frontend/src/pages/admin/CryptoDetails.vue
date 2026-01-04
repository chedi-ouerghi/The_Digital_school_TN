<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '@/services/api'

interface HistoryEntry {
  timestamp: number
  date: string
  price: number
  change_24h_pct: number
}

interface HistoryResponse {
  crypto: { id: string; symbol: string; name: string }
  meta: { count: number; from: string; to: string; days: number }
  history: HistoryEntry[]
}

interface CryptoData {
  id: string
  name: string
  symbol: string
  category: string
  website: string
  price_eur: string
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
const selectedPeriod = ref('30d')
const hoveredData = ref<HistoryEntry | null>(null)
const currentPage = ref(1)
const itemsPerPage = 7

// Computed
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
  const minPrice = Math.min(...prices)
  const maxPrice = Math.max(...prices)
  const priceRange = maxPrice - minPrice || 1
  
  return {
    data,
    minPrice,
    maxPrice,
    priceRange,
    points: data.map((d, i) => ({
      ...d,
      x: (i / (data.length - 1)) * 100,
      y: 100 - ((d.price - minPrice) / priceRange) * 100,
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
  
  return { high, low, change }
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

const formatPercent = (v: number) => {
  const sign = v >= 0 ? '+' : ''
  return `${sign}${v.toFixed(2)}%`
}

const formatDate = (date: string) => {
  const d = new Date(date)
  return d.toLocaleDateString('en-US', { 
    month: 'short', 
    day: 'numeric',
    year: 'numeric'
  })
}

// Fetch data
async function fetchData() {
  loading.value = true
  try {
    const cryptoId = route.params.id as string
    crypto.value = await api.crypto.show(cryptoId)
    const historyData: HistoryResponse = await api.crypto.history(cryptoId)
    history.value = historyData.history || []
    currentPage.value = 1
  } catch (e) {
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

// Lifecycle
onMounted(() => fetchData())
watch(selectedPeriod, () => {
  hoveredData.value = null
  currentPage.value = 1
})

function goBack() {
  router.back()
}
</script>

<template>
  <div class="min-h-screen bg-white">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 p-6">
      <div class="max-w-7xl mx-auto">
        <button 
          @click="goBack"
          class="mb-6 px-4 py-2 rounded-lg text-gray-600 hover:text-gray-900 hover:bg-gray-100 transition-all duration-200 flex items-center gap-2 text-sm font-medium"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
          </svg>
          Back
        </button>

        <!-- Crypto Header -->
        <div v-if="crypto" class="flex items-center gap-5">
          <div v-if="crypto.image_url" class="w-20 h-20 rounded-2xl bg-gray-100 p-2">
            <img :src="crypto.image_url" :alt="crypto.name" class="w-full h-full object-contain" />
          </div>
          
          <div>
            <div class="flex items-center gap-3 mb-2">
              <h1 class="text-4xl font-bold text-gray-900">{{ crypto.name }}</h1>
              <span class="px-3 py-1 rounded-full bg-gray-100 text-gray-600 text-sm font-medium">
                {{ crypto.symbol.toUpperCase() }}
              </span>
            </div>
            
            <div class="flex items-baseline gap-4">
              <span class="text-3xl font-bold" style="color: #35A7FF;">
                {{ formatCurrency(currentPrice) }}
              </span>
              <span 
                :style="{ color: change24h >= 0 ? '#01FF19' : '#FF5964' }"
                class="text-xl font-semibold"
              >
                {{ formatPercent(change24h) }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="w-12 h-12 border-4 border-gray-200 border-t-blue-500 rounded-full animate-spin mb-4"></div>
      <p class="text-gray-600">Loading data...</p>
    </div>

    <!-- Main Content -->
    <div v-else-if="crypto" class="max-w-7xl mx-auto p-6 space-y-8">
      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Current Price -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
          <div class="text-gray-600 text-sm font-medium mb-3">Current Price</div>
          <div style="color: #35A7FF;" class="text-2xl font-bold mb-2">{{ formatCurrency(currentPrice) }}</div>
          <div class="text-gray-500 text-xs">EUR</div>
        </div>

        <!-- 24h Change -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
          <div class="text-gray-600 text-sm font-medium mb-3">24h Change</div>
          <div 
            :style="{ color: change24h >= 0 ? '#01FF19' : '#FF5964' }"
            class="text-2xl font-bold mb-2"
          >
            {{ formatPercent(change24h) }}
          </div>
          <div class="text-gray-500 text-xs">Percentage</div>
        </div>

        <!-- Category -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
          <div class="text-gray-600 text-sm font-medium mb-3">Category</div>
          <div style="color: #35A7FF;" class="text-2xl font-bold mb-2">{{ crypto.category }}</div>
          <div class="text-gray-500 text-xs">Classification</div>
        </div>

        <!-- Data Points -->
        <div class="bg-white border border-gray-200 rounded-xl p-6 hover:shadow-lg transition-shadow duration-300">
          <div class="text-gray-600 text-sm font-medium mb-3">Data Points</div>
          <div style="color: #35A7FF;" class="text-2xl font-bold mb-2">{{ filteredHistory.length }}</div>
          <div class="text-gray-500 text-xs">Price entries</div>
        </div>
      </div>

      <!-- Period Selector -->
      <div class="flex gap-3 justify-center">
        <button 
          v-for="period in ['24h', '7d', '30d']" 
          :key="period"
          @click="selectedPeriod = period"
          :style="selectedPeriod === period 
            ? { backgroundColor: '#35A7FF', color: 'white' }
            : { backgroundColor: '#f3f4f6', color: '#374151', border: '1px solid #d1d5db' }"
          class="px-6 py-2.5 rounded-lg font-medium transition-all duration-300 min-w-[100px]"
        >
          {{ period }}
        </button>
      </div>

      <!-- Chart Section -->
      <div v-if="chartData" class="space-y-6">
        <!-- Chart Container -->
        <div class="bg-white border border-gray-200 rounded-xl p-8 hover:shadow-lg transition-shadow duration-300">
          <!-- Chart Metrics -->
          <div v-if="chartMetrics" class="flex justify-between items-center mb-8 pb-6 border-b border-gray-200">
            <div class="text-center">
              <div class="text-gray-600 text-sm mb-1">Period High</div>
              <div style="color: #01FF19;" class="text-lg font-bold">{{ formatCurrency(chartMetrics.high) }}</div>
            </div>
            <div class="text-center">
              <div class="text-gray-600 text-sm mb-1">Period Low</div>
              <div style="color: #FF5964;" class="text-lg font-bold">{{ formatCurrency(chartMetrics.low) }}</div>
            </div>
            <div class="text-center">
              <div class="text-gray-600 text-sm mb-1">Period Change</div>
              <div 
                :style="{ color: chartMetrics.change >= 0 ? '#01FF19' : '#FF5964' }"
                class="text-lg font-bold"
              >
                {{ formatPercent(chartMetrics.change) }}
              </div>
            </div>
          </div>

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
                  :stop-color="isPositiveTrend ? '#01FF19' : '#FF5964'"
                  stop-opacity="0.15"
                />
                <stop 
                  offset="100%" 
                  :stop-color="isPositiveTrend ? '#01FF19' : '#FF5964'"
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

            <!-- Y-Axis Labels -->
            <g class="text-sm fill-gray-600" font-family="system-ui" font-weight="500">
              <template v-for="i in 6" :key="`y-label-${i}`">
                <text 
                  x="60" 
                  :y="50 + (i * 70)" 
                  text-anchor="end"
                  dominant-baseline="middle"
                  class="select-none"
                >
                  {{ formatCurrency(chartData.maxPrice - (chartData.priceRange / 5) * (i - 1)) }}
                </text>
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
              :stroke="isPositiveTrend ? '#01FF19' : '#FF5964'"
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
                :fill="hoveredData?.index === point.index ? 'white' : isPositiveTrend ? '#01FF19' : '#FF5964'"
                :stroke="isPositiveTrend ? '#01FF19' : '#FF5964'"
                stroke-width="2"
                @mouseenter="handleChartHover(point.index)"
                @mouseleave="handleChartLeave"
                class="transition-all duration-200 hover:r-6 hover:stroke-gray-900"
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

            <!-- X-Axis Labels -->
            <g class="text-sm fill-gray-600" font-family="system-ui" font-weight="500">
              <text 
                v-for="(point, i) in chartData.points.filter((_, idx) => idx % Math.ceil(chartData.points.length / 8) === 0 || idx === chartData.points.length - 1)"
                :key="`x-label-${i}`"
                :x="point.x * 10.4 + 80"
                y="365"
                text-anchor="middle"
                class="select-none"
              >
                {{ formatDate(point.date) }}
              </text>
            </g>
          </svg>

          <!-- Hover Tooltip -->
          <div 
            v-if="hoveredData"
            class="absolute z-50 transform -translate-x-1/2"
            :style="{ left: `${hoveredData.x}%` }"
          >
            <div class="bg-gray-900 text-white rounded-lg shadow-lg p-4 min-w-[180px] text-center" style="margin-top: -360px;">
              <div class="text-gray-300 text-sm mb-1">
                {{ formatDate(hoveredData.date) }}
              </div>
              <div style="color: #35A7FF;" class="text-xl font-bold mb-1">
                {{ formatCurrency(hoveredData.price, 2) }}
              </div>
              <div 
                :style="{ color: hoveredData.change_24h_pct >= 0 ? '#01FF19' : '#FF5964' }"
                class="text-sm font-semibold"
              >
                {{ formatPercent(hoveredData.change_24h_pct) }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- History Table -->
      <div v-if="filteredHistory.length" class="bg-white border border-gray-200 rounded-xl overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div class="p-6 border-b border-gray-200">
          <h3 class="text-lg font-bold text-gray-900">Price History</h3>
        </div>
        
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="bg-gray-50 border-b border-gray-200">
                <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Date</th>
                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">Price</th>
                <th class="px-6 py-4 text-right text-sm font-semibold text-gray-900">24h Change</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
              <tr 
                v-for="(entry, i) in paginatedHistory" 
                :key="i"
                class="hover:bg-gray-50 transition-colors duration-150"
              >
                <td class="px-6 py-4 text-sm text-gray-900">{{ formatDate(entry.date) }}</td>
                <td class="px-6 py-4 text-right">
                  <span style="color: #35A7FF;" class="font-semibold">{{ formatCurrency(entry.price, 2) }}</span>
                </td>
                <td class="px-6 py-4 text-right">
                  <span 
                    :style="{ color: entry.change_24h_pct >= 0 ? '#01FF19' : '#FF5964' }"
                    class="font-semibold"
                  >
                    {{ formatPercent(entry.change_24h_pct) }}
                  </span>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
          <div class="text-sm text-gray-600">
            Showing {{ (currentPage - 1) * itemsPerPage + 1 }} to {{ Math.min(currentPage * itemsPerPage, filteredHistory.length) }} of {{ filteredHistory.length }} entries
          </div>
          
          <div class="flex gap-2">
            <button 
              @click="currentPage = Math.max(1, currentPage - 1)"
              :disabled="currentPage === 1"
              class="px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200"
              :style="currentPage === 1 
                ? { backgroundColor: '#f3f4f6', color: '#9ca3af', cursor: 'not-allowed' }
                : { backgroundColor: '#f3f4f6', color: '#374151' }"
            >
              Previous
            </button>
            
            <div class="flex gap-1">
              <button 
                v-for="page in totalPages" 
                :key="page"
                @click="currentPage = page"
                class="px-2.5 py-1 rounded-lg text-sm font-medium transition-all duration-200"
                :style="currentPage === page 
                  ? { backgroundColor: '#35A7FF', color: 'white' }
                  : { backgroundColor: '#f3f4f6', color: '#374151' }"
              >
                {{ page }}
              </button>
            </div>
            
            <button 
              @click="currentPage = Math.min(totalPages, currentPage + 1)"
              :disabled="currentPage === totalPages"
              class="px-3 py-1 rounded-lg text-sm font-medium transition-all duration-200"
              :style="currentPage === totalPages 
                ? { backgroundColor: '#f3f4f6', color: '#9ca3af', cursor: 'not-allowed' }
                : { backgroundColor: '#f3f4f6', color: '#374151' }"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
button:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>