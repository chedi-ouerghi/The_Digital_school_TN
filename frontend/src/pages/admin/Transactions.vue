<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import {
  Search,
  RefreshCw,
  Filter,
  Download,
  Eye,
  XCircle,
  Calendar,
  User,
  TrendingUp,
  TrendingDown,
  LayoutGrid,
  List,
  ArrowUpDown,
  ChevronLeft,
  ChevronRight, 
  CheckCircle,
  AlertCircle
} from 'lucide-vue-next'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const search = ref('')
const page = ref(1)
const total = ref(0)
const viewMode = ref<'grid' | 'table'>('table')
const statusFilter = ref<'all' | 'completed' | 'cancelled'>('all')
const typeFilter = ref<'all' | 'ACHAT' | 'VENTE'>('all')
const confirmDialog = ref(false)
const selectedTransaction = ref<any>(null)
const cancelReason = ref('')
const itemsPerPage = 10
const sortBy = ref<'date' | 'amount'>('date')
const sortOrder = ref<'asc' | 'desc'>('desc')

// Formatters améliorés
const formatCurrency = (value: number | string): string => {
  const num = typeof value === 'string' ? parseFloat(value) : value
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(num)
}

const formatDate = (date: string): string => 
  new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })

const formatNumber = (value: any, decimals = 6): string => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  const parts = n.toFixed(decimals).split('.')
  parts[0] = parts[0]?.replace(/\B(?=(\d{3})+(?!\d))/g, ',') ?? '0'
  return parts.join('.')
}

// Computed properties
const stats = computed(() => ({
  total: transactions.value.length,
  volume: transactions.value
    .filter(t => !t.cancelled_at)
    .reduce((sum, t) => sum + Number(t.total_eur || 0), 0),
  completed: transactions.value.filter(t => !t.cancelled_at).length,
  cancelled: transactions.value.filter(t => t.cancelled_at).length,
  buys: transactions.value.filter(t => t.type === 'ACHAT' && !t.cancelled_at).length,
  sells: transactions.value.filter(t => t.type === 'VENTE' && !t.cancelled_at).length
}))

const filteredTransactions = computed(() => {
  let filtered = transactions.value

  if (statusFilter.value === 'completed') {
    filtered = filtered.filter(t => !t.cancelled_at)
  } else if (statusFilter.value === 'cancelled') {
    filtered = filtered.filter(t => t.cancelled_at)
  }

  if (typeFilter.value !== 'all') {
    filtered = filtered.filter(t => t.type === typeFilter.value)
  }

  if (search.value) {
    const term = search.value.toLowerCase()
    filtered = filtered.filter(t => {
      const user = t.crypto_wallet_asset?.wallet?.user
      const crypto = t.crypto_wallet_asset?.cryptomoney
      return (
        user?.name?.toLowerCase().includes(term) ||
        user?.email?.toLowerCase().includes(term) ||
        crypto?.symbol?.toLowerCase().includes(term) ||
        crypto?.name?.toLowerCase().includes(term) ||
        t.id?.toLowerCase().includes(term)
      )
    })
  }
  
  return filtered
})

const sortedTransactions = computed(() => {
  return [...filteredTransactions.value].sort((a, b) => {
    let valueA, valueB
    
    if (sortBy.value === 'date') {
      valueA = new Date(a.created_at).getTime()
      valueB = new Date(b.created_at).getTime()
    } else {
      valueA = parseFloat(a.total_eur)
      valueB = parseFloat(b.total_eur)
    }
    
    return sortOrder.value === 'desc' ? valueB - valueA : valueA - valueB
  })
})

const totalPages = computed(() => 
  Math.ceil(sortedTransactions.value.length / itemsPerPage)
)

const paginatedTransactions = computed(() => {
  const start = (page.value - 1) * itemsPerPage
  return sortedTransactions.value.slice(start, start + itemsPerPage)
})

const showingRange = computed(() => ({
  from: sortedTransactions.value.length ? (page.value - 1) * itemsPerPage + 1 : 0,
  to: Math.min(page.value * itemsPerPage, sortedTransactions.value.length),
  total: sortedTransactions.value.length
}))

// Functions
const fetchTransactions = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.admin.transactions.list({ page: page.value })
    transactions.value = response.data
    total.value = response.total || 0
  } catch (err: any) {
    error.value = err.message || 'Failed to load transactions'
    console.error('Error:', err)
  } finally {
    loading.value = false
  }
}

const viewDetails = (id: number) => {
  router.push(`/dashboard/admin/transactions/${id}`)
}

const openCancelDialog = (transaction: any) => {
  selectedTransaction.value = transaction
  cancelReason.value = ''
  confirmDialog.value = true
}

const handleCancel = async () => {
  if (!selectedTransaction.value || !cancelReason.value.trim()) {
    error.value = 'Please provide a cancellation reason'
    return
  }
  
  try {
    await api.admin.transactions.cancel(
      selectedTransaction.value.id,
      cancelReason.value.trim()
    )
    await fetchTransactions()
    confirmDialog.value = false
    selectedTransaction.value = null
    cancelReason.value = ''
  } catch (err: any) {
    error.value = err.message
  }
}

const changePage = (newPage: number) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage
    window.scrollTo({ top: 0, behavior: 'smooth' })
  }
}

const resetFilters = () => {
  search.value = ''
  statusFilter.value = 'all'
  typeFilter.value = 'all'
  sortBy.value = 'date'
  sortOrder.value = 'desc'
  page.value = 1
}

const toggleSort = (field: 'date' | 'amount') => {
  if (sortBy.value === field) {
    sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc'
  } else {
    sortBy.value = field
    sortOrder.value = 'desc'
  }
}

const exportTransactions = () => {
  console.log('Exporting transactions...')
}

onMounted(fetchTransactions)
</script>

<template>
  <div class="space-y-6 px-4 sm:px-6 lg:px-0">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Transaction Management</h1>
        <p class="text-sm sm:text-base text-gray-600 mt-1">Monitor and manage all cryptocurrency transactions on the platform</p>
      </div>
      <div class="flex flex-wrap items-center gap-2 w-full lg:w-auto">
        <Button 
          variant="outline"
          size="sm"
          class="border-gray-300 gap-2 hover:bg-gray-50 active:bg-gray-100 transition-colors flex-1 lg:flex-none"
          :disabled="loading"
          @click="resetFilters"
        >
          <Filter class="h-4 w-4" />
          <span class="hidden sm:inline">Reset Filters</span>
          <span class="sm:hidden">Reset</span>
        </Button>
        <Button 
          variant="outline"
          size="sm"
          class="gap-2 hover:bg-gray-50 active:bg-gray-100 transition-colors flex-1 lg:flex-none"
          @click="exportTransactions"
        >
          <Download class="h-4 w-4" />
          <span class="hidden sm:inline">Export</span>
        </Button>
        <Button 
        variant="outline"
          class=" flex-1 lg:flex-none"
          :disabled="loading"
          @click="fetchTransactions"
        >
          <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          <span class="hidden sm:inline">{{ loading ? 'Refreshing...' : 'Refresh' }}</span>
          <span class="sm:hidden">{{ loading ? '...' : 'Refresh' }}</span>
        </Button>
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-6 gap-4">
      <Card class="lg:col-span-2 hover:shadow-md transition-shadow">
        <CardContent class="p-4 sm:p-6">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">Total Volume (24h)</p>
              <p class="text-xl sm:text-2xl font-bold text-gray-900 truncate">{{ formatCurrency(stats.volume) }}</p>
            </div>
            <div class="p-2 sm:p-3 rounded-full bg-blue-50 flex-shrink-0 ml-2">
              <TrendingUp class="h-5 w-5 sm:h-6 sm:w-6 text-blue-600" />
            </div>
          </div>
        </CardContent>
      </Card>
      
      <Card
v-for="stat in [
        { label: 'Total Transactions', value: stats.total, icon: List, color: 'gray' },
        { label: 'Completed', value: stats.completed, icon: CheckCircle, color: 'green' },
        { label: 'Cancelled', value: stats.cancelled, icon: XCircle, color: 'red' },
        { label: 'Buy/Sell Ratio', value: `${stats.buys}/${stats.sells}`, icon: ArrowUpDown, color: 'purple' }
      ]" :key="stat.label" class="hover:shadow-md transition-shadow">
        <CardContent class="p-4 sm:p-6">
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <p class="text-xs sm:text-sm font-medium text-gray-600 truncate">{{ stat.label }}</p>
              <p
:class="[
                'font-bold truncate',
                stat.label === 'Buy/Sell Ratio' ? 'text-lg sm:text-xl' : 'text-xl sm:text-2xl',
                `text-${stat.color}-600`
              ]">{{ stat.value }}</p>
            </div>
            <div :class="`p-2 sm:p-3 rounded-full bg-${stat.color}-50 flex-shrink-0 ml-2`">
              <component :is="stat.icon" :class="`h-5 w-5 sm:h-6 sm:w-6 text-${stat.color}-600`" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters Card -->
    <Card>
      <CardHeader class="p-4 sm:p-6">
        <CardTitle class="text-base sm:text-lg">Filters</CardTitle>
        <CardDescription class="text-sm">Refine transaction results using the filters below</CardDescription>
      </CardHeader>
      <CardContent class="p-4 sm:p-6 pt-0 sm:pt-0">
        <div class="flex flex-col lg:flex-row gap-3 lg:gap-4">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <Input
                v-model="search"
                placeholder="Search by client, email, crypto..."
                class="pl-10 h-10"
              />
            </div>
          </div>
          
          <!-- Filters Group -->
          <div class="flex flex-col sm:flex-row gap-3 lg:w-auto">
            <!-- Status Filter -->
            <div class="w-full sm:w-40">
              <Select v-model="statusFilter">
                <SelectTrigger class="h-10">
                  <div class="flex items-center gap-2 truncate">
                    <Filter class="h-4 w-4 flex-shrink-0" />
                    <SelectValue placeholder="Status" />
                  </div>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Status</SelectItem>
                  <SelectItem value="completed" class="text-green-600">
                    <div class="flex items-center gap-2">
                      <CheckCircle class="h-4 w-4" />
                      Completed
                    </div>
                  </SelectItem>
                  <SelectItem value="cancelled" class="text-red-600">
                    <div class="flex items-center gap-2">
                      <XCircle class="h-4 w-4" />
                      Cancelled
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <!-- Type Filter -->
            <div class="w-full sm:w-40">
              <Select v-model="typeFilter">
                <SelectTrigger class="h-10">
                  <div class="flex items-center gap-2 truncate">
                    <ArrowUpDown class="h-4 w-4 flex-shrink-0" />
                    <SelectValue placeholder="Type" />
                  </div>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All Types</SelectItem>
                  <SelectItem value="ACHAT" class="text-green-600">
                    <div class="flex items-center gap-2">
                      <TrendingUp class="h-4 w-4" />
                      Buy
                    </div>
                  </SelectItem>
                  <SelectItem value="VENTE" class="text-red-600">
                    <div class="flex items-center gap-2">
                      <TrendingDown class="h-4 w-4" />
                      Sell
                    </div>
                  </SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <!-- View Toggle -->
            <div class="flex border rounded-lg overflow-hidden bg-gray-50 h-10 w-full sm:w-auto">
              <Button
                variant="ghost"
                size="sm"
                :class="[
                  'rounded-none px-3 sm:px-4 h-10 flex-1 sm:flex-initial transition-colors',
                  viewMode === 'table' 
                    ? 'bg-white text-gray-900 shadow-sm border' 
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 active:bg-gray-200'
                ]"
                @click="viewMode = 'table'"
              >
                <List class="h-4 w-4 sm:mr-2" />
                <span class="hidden sm:inline">Table</span>
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="[
                  'rounded-none px-3 sm:px-4 h-10 flex-1 sm:flex-initial transition-colors',
                  viewMode === 'grid' 
                    ? 'bg-white text-gray-900 shadow-sm border' 
                    : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100 active:bg-gray-200'
                ]"
                @click="viewMode = 'grid'"
              >
                <LayoutGrid class="h-4 w-4 sm:mr-2" />
                <span class="hidden sm:inline">Grid</span>
              </Button>
            </div>
          </div>
        </div>
        
        <!-- Active Filters -->
        <div v-if="search || statusFilter !== 'all' || typeFilter !== 'all'" class="mt-4 flex flex-wrap gap-2">
          <Badge 
            v-if="search"
            variant="secondary"
            class="gap-2 px-3 py-1.5 text-sm group hover:bg-gray-200 transition-colors"
          >
            <span class="truncate max-w-[200px]">"{{ search }}"</span>
            <Button 
              size="sm" 
              variant="ghost" 
              class="h-4 w-4 p-0 hover:bg-transparent group-hover:scale-110 transition-transform"
              @click="search = ''"
            >
              <XCircle class="h-3 w-3" />
            </Button>
          </Badge>
          <Badge 
            v-if="statusFilter !== 'all'"
            variant="secondary"
            class="gap-2 px-3 py-1.5 text-sm group hover:bg-gray-200 transition-colors"
          >
            Status: {{ statusFilter === 'completed' ? 'Completed' : 'Cancelled' }}
            <Button 
              size="sm" 
              variant="ghost" 
              class="h-4 w-4 p-0 hover:bg-transparent group-hover:scale-110 transition-transform"
              @click="statusFilter = 'all'"
            >
              <XCircle class="h-3 w-3" />
            </Button>
          </Badge>
          <Badge 
            v-if="typeFilter !== 'all'"
            variant="secondary"
            class="gap-2 px-3 py-1.5 text-sm group hover:bg-gray-200 transition-colors"
          >
            Type: {{ typeFilter === 'ACHAT' ? 'Buy' : 'Sell' }}
            <Button 
              size="sm" 
              variant="ghost" 
              class="h-4 w-4 p-0 hover:bg-transparent group-hover:scale-110 transition-transform"
              @click="typeFilter = 'all'"
            >
              <XCircle class="h-3 w-3" />
            </Button>
          </Badge>
          <Button
            variant="ghost"
            size="sm"
            class="text-xs text-gray-600 hover:text-gray-900 hover:bg-gray-100"
            @click="resetFilters"
          >
            Clear all
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Results Summary -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="text-sm text-gray-600">
        <span class="font-medium">{{ showingRange.from }}-{{ showingRange.to }}</span> of 
        <span class="font-medium">{{ showingRange.total }}</span> transactions
      </div>
      <div v-if="sortedTransactions.length > 0" class="flex flex-col sm:flex-row items-start sm:items-center gap-3 w-full sm:w-auto">
        <Select v-model="sortBy" @update:model-value="sortOrder = 'desc'">
          <SelectTrigger class="w-full sm:w-32 h-9">
            <div class="flex items-center gap-2">
              <ArrowUpDown class="h-4 w-4 flex-shrink-0" />
              <SelectValue />
            </div>
          </SelectTrigger>
          <SelectContent>
            <SelectItem value="date">Date</SelectItem>
            <SelectItem value="amount">Amount</SelectItem>
          </SelectContent>
        </Select>
        <Button
          variant="outline"
          size="sm"
          class="gap-2 w-full sm:w-auto"
          @click="sortOrder = sortOrder === 'asc' ? 'desc' : 'asc'"
        >
          <ArrowUpDown class="h-4 w-4" />
          {{ sortOrder === 'asc' ? 'Ascending' : 'Descending' }}
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <div class="animate-pulse space-y-4">
        <div class="h-12 bg-gray-200 rounded"></div>
        <div class="space-y-3">
          <div v-for="i in 5" :key="i" class="h-20 bg-gray-100 rounded"></div>
        </div>
      </div>
    </div>

    <!-- Error State -->
    <Card v-else-if="error" class="border-red-200 bg-red-50/50">
      <CardContent class="p-6">
        <div class="flex items-start gap-3">
          <AlertCircle class="h-6 w-6 text-red-500 mt-0.5 flex-shrink-0" />
          <div class="flex-1">
            <h3 class="font-semibold text-red-800 mb-1">Failed to Load Transactions</h3>
            <p class="text-red-700 mb-3 text-sm">{{ error }}</p>
            <Button 
              size="sm" 
              variant="outline" 
              class="border-red-300 text-red-700 hover:bg-red-100 active:bg-red-200 transition-colors"
              @click="fetchTransactions"
            >
              <RefreshCw class="h-4 w-4 mr-2" />
              Retry
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="sortedTransactions.length === 0">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="mx-auto w-12 h-12 sm:w-16 sm:h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <Search class="h-6 w-6 sm:h-8 sm:w-8 text-gray-400" />
        </div>
        <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-2">No transactions found</h3>
        <p class="text-sm sm:text-base text-gray-600 mb-6 max-w-md mx-auto">
          {{ search || statusFilter !== 'all' || typeFilter !== 'all' 
            ? 'No transactions match your current filters. Try adjusting your search criteria.' 
            : 'There are no transactions in the system yet.' 
          }}
        </p>
        <Button 
          v-if="search || statusFilter !== 'all' || typeFilter !== 'all'"
          variant="outline"
          class="hover:bg-gray-50 active:bg-gray-100 transition-colors"
          @click="resetFilters"
        >
          Clear All Filters
        </Button>
      </CardContent>
    </Card>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
      <Card 
        v-for="t in paginatedTransactions" 
        :key="t.id"
        class="group relative hover:shadow-lg transition-all duration-200 border hover:border-[#38618C]/30 cursor-pointer"
        :class="{ 'border-red-200 bg-red-50/20': t.cancelled_at }"
        @click="viewDetails(t.id)"
      >
        <CardContent class="p-4 sm:p-5">
          <!-- Header -->
          <div class="flex justify-between items-start mb-4">
            <div class="flex items-center gap-3 min-w-0">
              <div class="relative flex-shrink-0">
                <div v-if="t.crypto_wallet_asset?.cryptomoney?.image_url" class="w-10 h-10 rounded-full ring-2 ring-white overflow-hidden">
                  <img
                    :src="t.crypto_wallet_asset.cryptomoney.image_url"
                    :alt="t.crypto_wallet_asset.cryptomoney.symbol"
                    class="w-full h-full object-cover"
                  />
                </div>
                <div v-else class="w-10 h-10 rounded-full bg-gradient-to-br from-[#38618C]/10 to-[#38618C]/20 flex items-center justify-center">
                  <span class="text-sm font-semibold text-[#38618C]">
                    {{ t.crypto_wallet_asset?.cryptomoney?.symbol?.charAt(0) || '?' }}
                  </span>
                </div>
              </div>
              <div class="min-w-0 flex-1">
                <div class="font-semibold text-gray-900 truncate">
                  {{ t.crypto_wallet_asset?.cryptomoney?.symbol?.toUpperCase() }}
                </div>
                <div class="text-xs text-gray-500 truncate">{{ t.crypto_wallet_asset?.cryptomoney?.name }}</div>
              </div>
            </div>
            <Badge 
              :class="[
                'font-medium px-2.5 py-1 text-xs flex-shrink-0 ml-2',
                t.type === 'ACHAT' 
                  ? 'bg-green-50 text-green-700 border-green-200' 
                  : 'bg-red-50 text-red-700 border-red-200'
              ]"
            >
              {{ t.type === 'ACHAT' ? 'BUY' : 'SELL' }}
            </Badge>
          </div>
          
          <!-- Client Info -->
          <div class="mb-4">
            <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg group-hover:bg-gray-100 transition-colors">
              <div class="w-8 h-8 rounded-full bg-[#38618C]/10 flex items-center justify-center flex-shrink-0">
                <User class="h-4 w-4 text-[#38618C]" />
              </div>
              <div class="flex-1 min-w-0">
                <div class="font-medium text-sm text-gray-900 truncate">
                  {{ t.crypto_wallet_asset?.wallet?.user?.name }}
                </div>
                <div class="text-xs text-gray-500 truncate">
                  {{ t.crypto_wallet_asset?.wallet?.user?.email }}
                </div>
              </div>
            </div>
          </div>
          
          <!-- Transaction Details -->
          <div class="space-y-3 mb-4">
            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-xs text-gray-500 mb-1">Quantity</div>
                <div class="font-mono text-sm font-semibold text-gray-900 truncate">
                  {{ formatNumber(t.quantity, 4) }}
                </div>
              </div>
              <div>
                <div class="text-xs text-gray-500 mb-1">Unit Price</div>
                <div class="text-sm font-semibold text-gray-900 truncate">
                  {{ formatCurrency(t.price) }}
                </div>
              </div>
            </div>
            
            <div class="border-t pt-3">
              <div class="text-xs text-gray-500 mb-1">Total Amount</div>
              <div class="text-lg font-bold text-gray-900 truncate">
                {{ formatCurrency(t.total_eur) }}
              </div>
            </div>
          </div>
          
          <!-- Footer -->
          <div class="flex justify-between items-center pt-4 border-t">
            <div class="flex items-center gap-2 text-xs text-gray-500 min-w-0">
              <Calendar class="h-3 w-3 flex-shrink-0" />
              <span class="truncate">{{ formatDate(t.created_at) }}</span>
            </div>
            <div class="flex gap-1 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
              <Button 
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0 hover:bg-[#38618C]/10 active:bg-[#38618C]/20 transition-colors"
              >
                <Eye class="h-4 w-4 text-[#38618C]" />
              </Button>
              <Button 
                v-if="!t.cancelled_at"
                size="sm"
                variant="ghost"
                class="h-8 w-8 p-0 text-red-600 hover:text-red-700 hover:bg-red-50 active:bg-red-100 transition-colors"
                @click.stop="openCancelDialog(t)"
              >
                <XCircle class="h-4 w-4" />
              </Button>
            </div>
          </div>
          
          <!-- Cancelled Overlay -->
          <div 
            v-if="t.cancelled_at"
            class="absolute inset-0 bg-red-50/90 backdrop-blur-[2px] rounded-lg flex items-center justify-center opacity-0 group-hover:opacity-100 transition-all duration-200"
          >
            <Badge variant="destructive" class="px-3 py-1.5 shadow-lg">
              <XCircle class="h-4 w-4 mr-1.5" />
              Cancelled
            </Badge>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Table View -->
    <div v-else class="bg-white rounded-xl border shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <Table>
          <TableHeader class="bg-gray-50">
            <TableRow>
              <TableHead class="w-12 px-4 py-3"></TableHead>
              <TableHead class="px-4 py-3">
                <Button 
                  variant="ghost" 
                  class="font-semibold text-gray-700 -ml-3 h-8 px-2 hover:bg-gray-200/50 active:bg-gray-200 transition-colors"
                  @click="toggleSort('date')"
                >
                  <div class="flex items-center gap-2">
                    <Calendar class="h-4 w-4" />
                    <span class="hidden sm:inline">Date & Time</span>
                    <span class="sm:hidden">Date</span>
                    <ArrowUpDown 
                      class="h-3 w-3" 
                      :class="{ 'text-[#38618C]': sortBy === 'date' }"
                    />
                  </div>
                </Button>
              </TableHead>
              <TableHead class="px-4 py-3">Client</TableHead>
              <TableHead class="px-4 py-3">Type</TableHead>
              <TableHead class="px-4 py-3">Crypto</TableHead>
              <TableHead class="px-4 py-3 text-right">Quantity</TableHead>
              <TableHead class="px-4 py-3 text-right hidden lg:table-cell">Unit Price</TableHead>
              <TableHead class="px-4 py-3 text-right">
                <Button 
                  variant="ghost" 
                  class="font-semibold text-gray-700 -mr-3 h-8 px-2 hover:bg-gray-200/50 active:bg-gray-200 transition-colors"
                  @click="toggleSort('amount')"
                >
                  <div class="flex items-center gap-2">
                    <span>Total</span>
                    <ArrowUpDown 
                      class="h-3 w-3" 
                      :class="{ 'text-[#38618C]': sortBy === 'amount' }"
                    />
                  </div>
                </Button>
              </TableHead>
              <TableHead class="px-4 py-3">Status</TableHead>
              <TableHead class="px-4 py-3 text-right">Actions</TableHead>
            </TableRow>
          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="t in paginatedTransactions" 
              :key="t.id"
              class="group hover:bg-gray-50/80 border-t cursor-pointer transition-colors"
              :class="{ 'bg-red-50/30 hover:bg-red-50/50': t.cancelled_at }"
              @click="viewDetails(t.id)"
            >
              <TableCell class="px-4 py-3">
                <div
class="w-8 h-8 rounded-full flex items-center justify-center" 
                  :class="t.type === 'ACHAT' ? 'bg-green-100' : 'bg-red-100'">
                  <component 
                    :is="t.type === 'ACHAT' ? TrendingUp : TrendingDown" 
                    class="h-4 w-4" 
                    :class="t.type === 'ACHAT' ? 'text-green-600' : 'text-red-600'"
                  />
                </div>
              </TableCell>
              <TableCell class="px-4 py-3">
                <div class="font-medium text-gray-900 whitespace-nowrap">
                  {{ formatDate(t.created_at) }}
                </div>
                <div class="text-xs text-gray-500 font-mono mt-0.5">{{ t.id.slice(0, 8) }}...</div>
              </TableCell>
              <TableCell class="px-4 py-3">
                <div class="flex items-center gap-3">
                  <div class="w-8 h-8 rounded-full bg-[#38618C]/10 flex items-center justify-center flex-shrink-0">
                    <span class="text-sm font-medium text-[#38618C]">
                      {{ t.crypto_wallet_asset?.wallet?.user?.name?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <div class="min-w-0">
                    <div class="font-medium text-gray-900 truncate max-w-[120px] lg:max-w-[160px]">
                      {{ t.crypto_wallet_asset?.wallet?.user?.name }}
                    </div>
                    <div class="text-sm text-gray-500 truncate max-w-[120px] lg:max-w-[160px]">
                      {{ t.crypto_wallet_asset?.wallet?.user?.email }}
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="px-4 py-3">
                <Badge 
                  :class="[
                    'font-medium px-2.5 py-0.5 whitespace-nowrap',
                    t.type === 'ACHAT' 
                      ? 'bg-green-50 text-green-700 border-green-200' 
                      : 'bg-red-50 text-red-700 border-red-200'
                  ]"
                >
                  {{ t.type === 'ACHAT' ? 'BUY' : 'SELL' }}
                </Badge>
              </TableCell>
              <TableCell class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <div class="w-7 h-7 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center flex-shrink-0">
                    <span class="text-xs font-semibold text-gray-600">
                      {{ t.crypto_wallet_asset?.cryptomoney?.symbol?.charAt(0) || '?' }}
                    </span>
                  </div>
                  <span class="font-medium text-gray-900 truncate max-w-[60px] lg:max-w-[80px]">
                    {{ t.crypto_wallet_asset?.cryptomoney?.symbol?.toUpperCase() }}
                  </span>
                </div>
              </TableCell>
              <TableCell class="px-4 py-3 text-right font-mono text-gray-900 whitespace-nowrap">
                {{ formatNumber(t.quantity, 4) }}
              </TableCell>
              <TableCell class="px-4 py-3 text-right text-gray-900 whitespace-nowrap hidden lg:table-cell">
                {{ formatCurrency(t.price) }}
              </TableCell>
              <TableCell class="px-4 py-3 text-right">
                <div class="font-bold text-gray-900 whitespace-nowrap">{{ formatCurrency(t.total_eur) }}</div>
              </TableCell>
              <TableCell class="px-4 py-3">
                <Badge 
                  :class="[
                    'font-medium px-2.5 py-0.5 whitespace-nowrap',
                    t.cancelled_at 
                      ? 'bg-red-50 text-red-700 border-red-200' 
                      : 'bg-green-50 text-green-700 border-green-200'
                  ]"
                >
                  <component 
                    :is="t.cancelled_at ? XCircle : CheckCircle" 
                    class="h-3 w-3 mr-1.5 inline" 
                  />
                  {{ t.cancelled_at ? 'Cancelled' : 'Completed' }}
                </Badge>
              </TableCell>
              <TableCell class="px-4 py-3 text-right">
                <div class="flex justify-end gap-1 opacity-0 group-hover:opacity-100 transition-opacity" @click.stop>
                  <Button 
                    size="sm" 
                    variant="ghost" 
                    class="h-8 w-8 p-0 hover:bg-[#38618C]/10 active:bg-[#38618C]/20 transition-colors"
                  >
                    <Eye class="h-4 w-4 text-[#38618C]" />
                  </Button>
                  <Button 
                    v-if="!t.cancelled_at"
                    size="sm" 
                    variant="ghost" 
                    class="h-8 w-8 p-0 text-red-600 hover:text-red-700 hover:bg-red-50 active:bg-red-100 transition-colors"
                    @click.stop="openCancelDialog(t)"
                  >
                    <XCircle class="h-4 w-4" />
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t">
      <div class="text-sm text-gray-600 order-2 sm:order-1">
        Page <span class="font-medium">{{ page }}</span> of <span class="font-medium">{{ totalPages }}</span>
      </div>
      <div class="flex items-center gap-2 order-1 sm:order-2 w-full sm:w-auto justify-center">
        <Button
          variant="outline"
          size="sm"
          :disabled="page <= 1"
          class="gap-2 px-3"
          @click="changePage(page - 1)"
        >
          <ChevronLeft class="h-4 w-4" />
          <span class="hidden sm:inline">Previous</span>
        </Button>
        <div class="flex items-center gap-1">
          <Button
            v-for="p in Math.min(5, totalPages)"
            :key="p"
            :variant="page === p ? 'default' : 'outline'"
            size="sm"
            class="w-9 h-9 p-0 transition-colors"
            :class="page === p ? 'bg-[#38618C] hover:bg-[#2c4e6e]' : 'hover:bg-gray-100 active:bg-gray-200'"
            @click="changePage(p)"
          >
            {{ p }}
          </Button>
        </div>
        <Button
          variant="outline"
          size="sm"
          :disabled="page >= totalPages"
          class="gap-2 px-3"
          @click="changePage(page + 1)"
        >
          <span class="hidden sm:inline">Next</span>
          <ChevronRight class="h-4 w-4" />
        </Button>
      </div>
    </div>

    <!-- Cancel Dialog -->
    <Dialog v-model:open="confirmDialog">
      <DialogContent class="max-w-md w-[95vw] sm:w-full mx-auto">
        <DialogHeader>
          <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
            <AlertCircle class="h-6 w-6 text-red-600" />
          </div>
          <DialogTitle class="text-center text-xl">Cancel Transaction</DialogTitle>
          <DialogDescription class="text-center">
            This action cannot be undone. The transaction will be marked as cancelled and all associated funds will be reversed.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4 py-4">
          <div v-if="selectedTransaction" class="bg-gray-50 rounded-lg p-4 space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Transaction ID:</span>
              <span class="font-mono text-sm font-medium">{{ selectedTransaction.id.slice(0, 12) }}...</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Amount:</span>
              <span class="font-bold text-gray-900">{{ formatCurrency(selectedTransaction.total_eur) }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Client:</span>
              <span class="font-medium text-gray-900 truncate max-w-[200px]">
                {{ selectedTransaction.crypto_wallet_asset?.wallet?.user?.name }}
              </span>
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
              Cancellation Reason <span class="text-red-500">*</span>
            </label>
            <Input
              v-model="cancelReason"
              placeholder="Enter reason for cancellation..."
              class="w-full"
            />
            <p class="text-xs text-gray-500 mt-2">
              This reason will be visible to the client and logged in the system.
            </p>
          </div>
        </div>
        
        <DialogFooter class="flex-col sm:flex-row gap-2">
          <Button variant="outline" class="w-full sm:w-auto order-2 sm:order-1" @click="confirmDialog = false">
            Cancel
          </Button>
          <Button 
            variant="destructive" 
            :disabled="!cancelReason.trim()"
            class="w-full sm:w-auto order-1 sm:order-2"
            @click="handleCancel"
          >
            Confirm Cancellation
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>