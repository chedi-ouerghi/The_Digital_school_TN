<script setup lang="ts">
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js'
import { computed, onMounted, ref } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import {
  Users,
  Mail,
  Calendar,
  CreditCard,
  TrendingUp,
  TrendingDown,
  BarChart3,
  PieChart,
  ArrowLeft,
  RefreshCw,
  Wallet,
  Shield,
  CheckCircle,
  Clock,
  ExternalLink,
  Filter,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  Download,
  Eye
} from 'lucide-vue-next'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const route = useRoute()
const router = useRouter()
const client = ref<any>(null)
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | undefined>(undefined)

// Pagination
const currentPage = ref(1)
const itemsPerPage = 8

// Utility functions
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(n)
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Add helper to build a usable image URL when backend returns a relative path
function makeImageUrl(path: string | undefined | null): string | undefined {
  if (!path) return undefined
  const p = String(path)
  if (p.startsWith('http://') || p.startsWith('https://')) return p

  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  const cleanPath = p.startsWith('/storage/') ? p : `/storage/${p}`
  return `${baseUrl}${cleanPath}`
}

// Fetch data
async function fetchClientDetails() {
  const id = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
  if (!id) return

  loading.value = true
  error.value = undefined
  
  try {
    const clientData = await api.admin.clients.show(id)
    client.value = clientData
    
    const positions = (clientData as any).positions || []
    const posMap: Record<string, any> = {}
    positions.forEach((p: any) => {
      const sym = String(p.symbol || p.symbole || '').toLowerCase()
      const imageUrl = makeImageUrl(p.image || p.image_url)
      if (sym) posMap[sym] = {
        ...p,
        image: imageUrl || '',
        image_url: imageUrl || ''
      }
    })

    const rawTxs = (clientData as any).transactions || []
    
    transactions.value = (rawTxs || []).map((tx: any) => {
      const txSym = String(tx.crypto || tx.cryptomoney || tx.cryptomoney?.symbol || '').toLowerCase()
      const pos = posMap[txSym]
      const txCryptoImage = makeImageUrl(tx.cryptomoney?.image || tx.cryptomoney?.image_url)

      return {
        ...tx,
        wallet: {
          cryptomoney: {
            nom: pos?.name || pos?.nom || (tx.cryptomoney?.name || tx.crypto?.toUpperCase() || 'Unknown'),
            symbole: pos?.symbol || pos?.symbole || (tx.cryptomoney?.symbol || tx.crypto || '').toLowerCase(),
            image: pos?.image || txCryptoImage || ''
          }
        },
        cryptomoney: {
          ...(tx.cryptomoney || {}),
          image: txCryptoImage || pos?.image || tx.cryptomoney?.image || tx.cryptomoney?.image_url || ''
        }
      }
    }).sort((a: any, b: any) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())

  } catch (err: any) {
    error.value = err.message || 'Error loading client details'
    console.error('Error fetching client details:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchClientDetails)

// Computed - Statistics
const stats = computed(() => {
  let totalTransactions = 0
  let totalBuy = 0
  let totalSell = 0
  let cancelled = 0
  let activeTransactions = 0

  transactions.value.forEach(t => {
    totalTransactions += 1

    const isCancelled = t.status?.toLowerCase() === 'annulée' || t.cancelled_at
    if (isCancelled) {
      cancelled += 1
      return 
    }

    activeTransactions += 1
    
    if (t.type === 'ACHAT') {
      totalBuy += Number(t.total_eur || 0)
    } else if (t.type === 'VENTE') {
      totalSell += Number(t.total_eur || 0)
    }
  })

  return {
    totalTransactions,
    activeTransactions,
    totalBuy,
    totalSell,
    cancelled,
    netAmount: totalBuy - totalSell,
    avgTransaction: activeTransactions > 0 ? (totalBuy + totalSell) / activeTransactions : 0
  }
})

// Computed - Crypto breakdown
const cryptoBreakdown = computed(() => {
  const breakdown: { [key: string]: { count: number; amount: number; name: string; image: string } } = {}
  
  transactions.value
    .filter(t => !(t.status?.toLowerCase() === 'annulée' || t.cancelled_at))
    .forEach(t => {
      const symbol = t.wallet?.cryptomoney?.symbole || 'Unknown'
      const name = t.wallet?.cryptomoney?.nom || 'Unknown'
      const image = t.wallet?.cryptomoney?.image || ''
      
      if (!breakdown[symbol]) {
        breakdown[symbol] = { count: 0, amount: 0, name, image }
      }
      
      breakdown[symbol].count++
      breakdown[symbol].amount += Number(t.total_eur || 0)
    })
  
  return Object.entries(breakdown)
    .map(([symbol, data]) => ({ symbol, ...data }))
    .sort((a, b) => b.amount - a.amount)
})

// Computed - Pagination
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return transactions.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(transactions.value.length / itemsPerPage))

// Charts data
const barChartData = computed(() => ({
  labels: cryptoBreakdown.value.slice(0, 5).map(c => c.symbol.toUpperCase()),
  datasets: [{
    label: 'Transaction Volume (€)',
    data: cryptoBreakdown.value.slice(0, 5).map(c => c.amount),
    backgroundColor: [
      'rgba(59, 130, 246, 0.8)',
      'rgba(16, 185, 129, 0.8)',
      'rgba(245, 158, 11, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(139, 92, 246, 0.8)',
    ],
    borderColor: ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'],
    borderWidth: 2,
    borderRadius: 6,
    borderSkipped: false
  }]
}))

const barChartOptions: any = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#1F2937',
      titleColor: '#F9FAFB',
      bodyColor: '#F9FAFB',
      borderColor: '#374151',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 6,
      callbacks: {
        label: (context: any) => `${formatCurrency(context.parsed.y)}`
      }
    }
  },
  scales: {
    x: { 
      ticks: { 
        color: '#6B7280',
        font: { weight: '500' as const }
      },
      grid: { display: false }
    },
    y: { 
      ticks: { 
        color: '#6B7280',
        callback: (value: any) => formatCurrency(value)
      },
      grid: { color: '#F3F4F6' }
    }
  }
}

const doughnutChartData = computed(() => ({
  labels: ['Buy', 'Sell'],
  datasets: [{
    data: [stats.value.totalBuy, stats.value.totalSell],
    backgroundColor: ['rgba(16, 185, 129, 0.8)', 'rgba(239, 68, 68, 0.8)'],
    borderColor: ['#10B981', '#EF4444'],
    borderWidth: 2
  }]
}))

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        color: '#374151',
        font: { size: 12, weight: '500' as const },
        padding: 15,
        usePointStyle: true,
        pointStyle: 'circle'
      }
    },
    tooltip: {
      backgroundColor: '#1F2937',
      titleColor: '#F9FAFB',
      bodyColor: '#F9FAFB',
      borderColor: '#374151',
      borderWidth: 1,
      padding: 12,
      cornerRadius: 6,
      callbacks: {
        label: (context: any) => `${context.label}: ${formatCurrency(context.parsed)}`
      }
    }
  },
  cutout: '65%'
} as const

// Functions
function goBack() {
  router.push('/dashboard/admin/clients')
}

function changePage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
  }
}

function handleImgError(e: Event) {
  const t = e.target as HTMLImageElement | null
  if (t) t.style.display = 'none'
}

function getClientInitials(name: string | undefined) {
  if (!name) return 'U'
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
}

// Get transaction status
function getTransactionStatus(tx: any) {
  if (tx.status?.toLowerCase() === 'annulée' || tx.cancelled_at) {
    return { label: 'Cancelled', color: 'bg-red-100 text-red-700 border-red-200' }
  }
  // Default to completed (no pending status)
  return { label: 'Completed', color: 'bg-green-100 text-green-700 border-green-200' }
}


</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <Button 
          variant="outline"
          class="border-gray-300 text-gray-700 hover:bg-gray-50 gap-2"
          @click="goBack"
        >
          <ArrowLeft class="h-4 w-4" />
          Back to Clients
        </Button>
        <div v-if="client">
          <h1 class="text-2xl font-bold text-gray-900">{{ client.name }}</h1>
          <p class="text-sm text-gray-500">Client Profile & Analytics</p>
        </div>
      </div>
      <div class="flex gap-2">
        <Button 
          variant="outline"
          class="border-gray-300 text-gray-700 hover:bg-gray-50 gap-2"
          :disabled="loading"
          @click="fetchClientDetails"
        >
          <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <Card v-if="loading" class="border border-gray-200">
      <CardContent class="p-12">
        <div class="text-center space-y-4">
          <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
          <div class="space-y-2">
            <p class="text-lg font-medium text-gray-900">Loading Client Details</p>
            <p class="text-sm text-gray-500">Fetching client data and analytics...</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border border-red-200">
      <CardContent class="p-12 text-center">
        <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
          <Shield class="h-8 w-8 text-red-600" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading Data</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          {{ error }}
        </p>
        <div class="flex gap-3 justify-center">
          <Button 
            variant="outline"
            @click="goBack"
          >
            Back to Clients
          </Button>
          <Button 
            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white"
            @click="fetchClientDetails"
          >
            <RefreshCw class="h-4 w-4 mr-2" />
            Try Again
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else-if="client" class="space-y-6">
      <!-- Client Overview Section -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Client Profile Card -->
        <Card class="border border-gray-200">
          <CardContent class="p-6">
            <!-- Profile Header -->
            <div class="flex flex-col items-center text-center mb-6">
              <!-- Banner -->
              <div v-if="makeImageUrl(client.profile_banner)" 
                   class="w-full h-32 mb-8 rounded-lg overflow-hidden bg-gradient-to-r from-blue-500 to-blue-600">
                <img
                  :src="makeImageUrl(client.profile_banner)"
                  alt="banner"
                  class="w-full h-full object-cover"
                  @error="handleImgError"
                />
              </div>
              <div v-else class="w-full h-32 mb-8 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600"></div>
              
              <!-- Avatar -->
              <div class="relative -mt-16 mb-4">
                <div class="w-24 h-24 rounded-full border-4 border-white shadow-lg bg-white">
                  <img
                    v-if="makeImageUrl(client.profile_picture)"
                    :src="makeImageUrl(client.profile_picture)"
                    alt="avatar"
                    class="h-full w-full rounded-full object-cover"
                    @error="handleImgError"
                  />
                  <div v-else class="h-full w-full rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                    <span class="text-white font-bold text-2xl">
                      {{ getClientInitials(client.name) }}
                    </span>
                  </div>
                </div>
              </div>
              
              <!-- Name and Badges -->
              <div class="space-y-2">
                <h2 class="text-xl font-bold text-gray-900">{{ client.name }}</h2>
                <div class="flex flex-wrap gap-2 justify-center">
                  <Badge 
                    :class="[
                      'font-medium',
                      client.email_verified_at 
                        ? 'bg-green-100 text-green-700 border-green-200' 
                        : 'bg-amber-100 text-amber-700 border-amber-200'
                    ]"
                  >
                    <component 
                      :is="client.email_verified_at ? CheckCircle : Clock" 
                      class="h-3 w-3 mr-1.5" 
                    />
                    {{ client.email_verified_at ? 'Verified' : 'Pending' }}
                  </Badge>
                  <Badge 
                    :class="[
                      'font-medium',
                      client.role === 'ADMIN' 
                        ? 'bg-purple-100 text-purple-700 border-purple-200' 
                        : 'bg-blue-100 text-blue-700 border-blue-200'
                    ]"
                  >
                    {{ client.role || 'CLIENT' }}
                  </Badge>
                </div>
              </div>
            </div>

            <!-- Profile Details -->
            <div class="space-y-4">
              <div>
                <div class="text-xs font-medium text-gray-500 mb-1">Contact Information</div>
                <div class="space-y-3">
                  <div class="flex items-center gap-2 text-gray-700">
                    <Mail class="h-4 w-4 text-gray-400" />
                    <span class="text-sm truncate">{{ client.email }}</span>
                  </div>
                  <div class="flex items-center gap-2 text-gray-700">
                    <Calendar class="h-4 w-4 text-gray-400" />
                    <span class="text-sm">Joined {{ formatDate(client.created_at) }}</span>
                  </div>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100">
                <div class="text-xs font-medium text-gray-500 mb-1">Account Balance</div>
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-2">
                    <Wallet class="h-5 w-5 text-emerald-500" />
                    <div>
                      <div class="text-2xl font-bold text-gray-900">
                        {{ formatCurrency(client.balance_eur || 0) }}
                      </div>
                      <div class="text-xs text-gray-500">Available funds</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Right: Analytics Cards -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Card class="border border-gray-200 hover:border-blue-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Total Transactions</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ stats.totalTransactions }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ stats.activeTransactions }} active</p>
                  </div>
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <BarChart3 class="h-5 w-5 text-blue-600" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="border border-gray-200 hover:border-emerald-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Total Buy</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.totalBuy) }}</p>
                    <p class="text-xs text-emerald-600 mt-1">
                      <TrendingUp class="h-3 w-3 inline mr-1" />
                      {{ cryptoBreakdown.length }} cryptos
                    </p>
                  </div>
                  <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                    <TrendingUp class="h-5 w-5 text-emerald-600" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="border border-gray-200 hover:border-red-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Total Sell</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.totalSell) }}</p>
                    <p class="text-xs text-red-600 mt-1">
                      <TrendingDown class="h-3 w-3 inline mr-1" />
                      Net: {{ formatCurrency(stats.netAmount) }}
                    </p>
                  </div>
                  <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <TrendingDown class="h-5 w-5 text-red-600" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="border border-gray-200 hover:border-amber-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Avg Transaction</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(stats.avgTransaction) }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ stats.cancelled }} cancelled</p>
                  </div>
                  <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                    <CreditCard class="h-5 w-5 text-amber-600" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bar Chart -->
            <Card class="border border-gray-200">
              <CardHeader class="pb-3">
                <CardTitle class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                  <BarChart3 class="h-4 w-4" />
                  Top Cryptos by Volume
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Bar v-if="cryptoBreakdown.length > 0" :data="barChartData" :options="barChartOptions" />
                  <div v-else class="h-full flex flex-col items-center justify-center text-gray-500">
                    <BarChart3 class="h-12 w-12 text-gray-300 mb-3" />
                    <p class="text-sm">No transaction data available</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Doughnut Chart -->
            <Card class="border border-gray-200">
              <CardHeader class="pb-3">
                <CardTitle class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                  <PieChart class="h-4 w-4" />
                  Buy vs Sell Distribution
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Doughnut v-if="stats.totalBuy > 0 || stats.totalSell > 0" :data="doughnutChartData" :options="doughnutChartOptions" />
                  <div v-else class="h-full flex flex-col items-center justify-center text-gray-500">
                    <PieChart class="h-12 w-12 text-gray-300 mb-3" />
                    <p class="text-sm">No buy/sell data available</p>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <Card class="border border-gray-200">
        <CardHeader>
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
            <div>
              <CardTitle class="text-lg font-semibold text-gray-900">Transaction History</CardTitle>
              <p class="text-sm text-gray-500">All client transactions</p>
            </div>
            <div class="flex items-center gap-2">
              <Badge class="bg-blue-100 text-blue-700 border-blue-200">
                {{ transactions.length }} Total
              </Badge>
              <Button
                variant="outline"
                size="sm"
                class="border-gray-300 text-gray-700 hover:bg-gray-50"
                @click="fetchClientDetails"
              >
                <RefreshCw class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <!-- Empty State -->
          <div v-if="transactions.length === 0" class="p-12 text-center">
            <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
              <CreditCard class="h-8 w-8 text-gray-400" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Transactions</h3>
            <p class="text-gray-600 mb-6 max-w-md mx-auto">
              This client hasn't made any transactions yet
            </p>
            <Button 
              variant="outline"
              @click="fetchClientDetails"
            >
              <RefreshCw class="h-4 w-4 mr-2" />
              Refresh
            </Button>
          </div>

          <!-- Transactions List -->
          <div v-else class="space-y-3">
            <!-- Table Header (Desktop) -->
            <div class="hidden md:grid grid-cols-12 gap-4 px-4 py-3 bg-gray-50 rounded-lg border border-gray-200">
              <div class="col-span-3 text-xs font-medium text-gray-500">CRYPTO / TRANSACTION</div>
              <div class="col-span-2 text-xs font-medium text-gray-500">TYPE</div>
              <div class="col-span-2 text-xs font-medium text-gray-500">QUANTITY / PRICE</div>
              <div class="col-span-3 text-xs font-medium text-gray-500">STATUS / DATE</div>
              <div class="col-span-2 text-xs font-medium text-gray-500">TOTAL</div>
            </div>

            <!-- Transactions -->
            <div class="space-y-3">
              <Card 
                v-for="tx in paginatedTransactions" 
                :key="tx.id"
                class="group border border-gray-200 hover:border-blue-300 hover:shadow-md transition-all duration-200"
              >
                <CardContent class="p-4">
                  <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-center">
                    <!-- Crypto Info -->
                    <div class="md:col-span-3">
                      <div class="flex items-center gap-3">
                        <div class="relative">
                          <div class="w-10 h-10 rounded-full border-2 border-gray-300 group-hover:border-blue-300 transition-colors flex items-center justify-center bg-gray-50">
                            <img
                              v-if="(
                                tx.wallet?.cryptomoney?.image ||
                                tx.wallet?.cryptomoney?.image_url ||
                                tx.cryptomoney?.image ||
                                tx.cryptomoney?.image_url
                              )"
                              :src="tx.wallet?.cryptomoney?.image || tx.wallet?.cryptomoney?.image_url || tx.cryptomoney?.image || tx.cryptomoney?.image_url"
                              :alt="tx.wallet?.cryptomoney?.nom || tx.cryptomoney?.name || 'crypto'"
                              class="h-8 w-8 rounded-full object-cover"
                              @error="handleImgError"
                            />
                            <div v-else class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                              <span class="text-white font-bold text-xs">?</span>
                            </div>
                          </div>
                        </div>
                        <div>
                          <div class="font-medium text-gray-900">
                            {{ tx.wallet?.cryptomoney?.nom || 'Unknown Crypto' }}
                          </div>
                          <div class="text-xs text-gray-500 flex items-center gap-1">
                            <span class="font-mono">{{ tx.wallet?.cryptomoney?.symbole?.toUpperCase() || 'N/A' }}</span>
                            <span class="text-gray-300">•</span>
                            <span>TX #{{ tx.id?.slice(-8) }}</span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Type -->
                    <div class="md:col-span-2">
                      <Badge 
                        :class="[
                          'font-medium w-fit',
                          tx.type === 'ACHAT' 
                            ? 'bg-emerald-100 text-emerald-700 border-emerald-200' 
                            : 'bg-red-100 text-red-700 border-red-200'
                        ]"
                      >
                        <component 
                          :is="tx.type === 'ACHAT' ? TrendingUp : TrendingDown" 
                          class="h-3 w-3 mr-1.5" 
                        />
                        {{ tx.type === 'ACHAT' ? 'BUY' : 'SELL' }}
                      </Badge>
                    </div>

                    <!-- Quantity & Price -->
                    <div class="md:col-span-2">
                      <div class="space-y-1">
                        <div>
                          <div class="text-xs text-gray-500">Quantity</div>
                          <div class="font-mono text-sm font-medium text-gray-900">
                            {{ formatNumber(tx.quantity, 8) }}
                          </div>
                        </div>
                        <div>
                          <div class="text-xs text-gray-500">Price</div>
                          <div class="text-sm font-medium text-blue-600">
                            {{ formatCurrency(tx.price) }}
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Status & Date -->
                    <div class="md:col-span-3">
                      <div class="space-y-2">
                        <div>
                          <Badge :class="getTransactionStatus(tx).color" class="font-medium">
                            {{ getTransactionStatus(tx).label }}
                          </Badge>
                        </div>
                        <div class="text-xs text-gray-500">
                          {{ formatDate(tx.created_at) }}
                        </div>
                      </div>
                    </div>

                    <!-- Total -->
                    <div class="md:col-span-2">
                      <div class="text-right">
                        <div class="text-xs text-gray-500">Total Amount</div>
                        <div class="text-lg font-bold text-gray-900">
                          {{ formatCurrency(tx.total_eur) }}
                        </div>
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
              <div class="text-sm text-gray-600">
                Showing {{ Math.min((currentPage - 1) * itemsPerPage + 1, transactions.length) }} 
                to {{ Math.min(currentPage * itemsPerPage, transactions.length) }} 
                of {{ transactions.length }} transactions
              </div>
              <div class="flex items-center gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="currentPage === 1"
                  @click="changePage(1)"
                >
                  <ChevronsLeft class="h-4 w-4" />
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="currentPage === 1"
                  @click="changePage(currentPage - 1)"
                >
                  <ChevronLeft class="h-4 w-4" />
                </Button>
                
                <div class="flex items-center gap-1">
                  <Button
                    v-for="page in Math.min(5, totalPages)"
                    :key="page"
                    :variant="currentPage === page ? 'default' : 'outline'"
                    size="sm"
                    class="h-8 w-8 p-0"
                    @click="changePage(page)"
                  >
                    {{ page }}
                  </Button>
                </div>
                
                <Button
                  variant="outline"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="currentPage === totalPages"
                  @click="changePage(currentPage + 1)"
                >
                  <ChevronRight class="h-4 w-4" />
                </Button>
                <Button
                  variant="outline"
                  size="sm"
                  class="h-8 w-8 p-0"
                  :disabled="currentPage === totalPages"
                  @click="changePage(totalPages)"
                >
                  <ChevronsRight class="h-4 w-4" />
                </Button>
              </div>
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