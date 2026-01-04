<script setup lang="ts">
import { ref, computed, onMounted, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { 
  Search, Filter, RefreshCw, Download, Calendar, 
  ArrowDownRight, AlertCircle, CheckCircle2, XCircle,
  TrendingUp, TrendingDown, DollarSign, Package,
  MoreVertical, ExternalLink
} from 'lucide-vue-next'
// UI components
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Progress } from '@/components/ui/progress'
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip'

const router = useRouter()
const wallet = ref<any>(null)
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const activeTab = ref('all')
const dateRange = ref('30d')
const filterType = ref<'all' | 'ACHAT' | 'VENTE'>('all')
const searchQuery = ref('')
const showFilters = ref(false)
const showSellDialog = ref(false)
const selectedAsset = ref<any>(null)
const sellQuantity = ref('')
const sellError = ref<string | null>(null)
const sellSuccess = ref<string | null>(null)
const isSelling = ref(false)
const itemsPerPage = ref(10)
const currentPage = ref(1)

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
const formatCurrency = (value: any) => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('en-US', { style: 'currency', currency: 'EUR', minimumFractionDigits: 2 })
}

const formatNumber = (value: any, decimals = 8) => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  const formatted = n.toFixed(decimals)
  return formatted.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '')
}

const formatRelativeDate = (date: string) => {
  const now = new Date()
  const diff = now.getTime() - new Date(date).getTime()
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  if (days > 0) return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  return 'Just now'
}

// ============================================================================
// API FUNCTIONS
// ============================================================================
const fetchWallet = async () => {
  loading.value = true
  try {
    wallet.value = await api.wallet.list()
  } catch (e: any) {
    error.value = e.message || 'Error loading wallet'
  } finally {
    loading.value = false
  }
}

const loadTransactions = async () => {
  loading.value = true
  error.value = null
  try {
    const data = await api.wallet.getTransactionsHistory(filterType.value === 'all' ? undefined : filterType.value)
    const transactionsData = Array.isArray(data) ? data : []
    
    transactions.value = transactionsData.map((tx: any) => ({
      id: tx.id,
      originalType: tx.type,
      type: tx.type === 'ACHAT' ? 'buy' : 'sell',
      typeLabel: tx.type === 'ACHAT' ? 'BUY' : 'SELL',
      quantity: Number(tx.quantity || 0),
      price: Number(tx.price || 0),
      unitPrice: Number(tx.unit_price_eur || tx.price || 0),
      total: Number(tx.total_eur || 0),
      date: tx.date,
      cancelled: !!tx.cancelled_at,
      cancelReason: tx.cancel_reason,
      crypto: {
        id: tx.crypto_id,
        name: tx.crypto_name || 'Unknown Crypto',
        symbol: tx.crypto_symbol || 'N/A',
        image_url: tx.crypto_image_url || '',
        current_price: Number(tx.crypto_current_price || 0)
      }
    })).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
    
  } catch (e: any) {
    error.value = e.message || 'Error loading transactions'
  } finally {
    loading.value = false
  }
}

// ============================================================================
// COMPUTED PROPERTIES
// ============================================================================
const availableQuantities = computed(() => {
  const quantities: Record<string, any> = {}
  
  transactions.value.forEach((tx) => {
    if (tx.cancelled) return
    const symbol = tx.crypto.symbol
    if (!quantities[symbol]) {
      quantities[symbol] = {
        amount: 0,
        name: tx.crypto.name,
        symbol,
        image: tx.crypto.image_url,
        currentPrice: tx.crypto.current_price,
        purchaseValue: 0
      }
    }
    if (tx.originalType === 'ACHAT') {
      quantities[symbol].amount += tx.quantity
      quantities[symbol].purchaseValue += tx.total
    } else if (tx.originalType === 'VENTE') {
      quantities[symbol].amount -= tx.quantity
      quantities[symbol].purchaseValue -= tx.total
    }
  })

  Object.keys(quantities).forEach(symbol => {
    const q = quantities[symbol]
    q.currentValue = q.amount * q.currentPrice
    q.profitLoss = q.currentValue - q.purchaseValue
    q.avgBuyPrice = q.amount > 0 ? q.purchaseValue / q.amount : 0
  })

  return quantities
})

const stats = computed(() => {
  const activeTxs = transactions.value.filter(t => !t.cancelled)
  const buys = activeTxs.filter(t => t.originalType === 'ACHAT')
  const sells = activeTxs.filter(t => t.originalType === 'VENTE')
  const cancelled = transactions.value.filter(t => t.cancelled)
  
  const totalBuys = buys.reduce((sum, t) => sum + t.total, 0)
  const totalSells = sells.reduce((sum, t) => sum + t.total, 0)
  const totalValue = Object.values(availableQuantities.value).reduce((sum: number, q: any) => sum + q.currentValue, 0)
  const totalInvestment = Object.values(availableQuantities.value).reduce((sum: number, q: any) => sum + q.purchaseValue, 0)
  const profit = totalValue - totalInvestment
  const profitPercentage = totalInvestment > 0 ? (profit / totalInvestment) * 100 : 0

  return {
    countBuys: buys.length,
    countSells: sells.length,
    countCancelled: cancelled.length,
    profit,
    profitPercentage,
    totalValue,
    totalInvestment
  }
})

const portfolioMetrics = computed(() => {
  if (!wallet.value) return []
  const w = wallet.value
  const totalValue = w.totalValue || 0
  const totalInvestment = w.totalInvestment || 0
  const profit = totalValue - totalInvestment
  const profitPercentage = totalInvestment > 0 ? (profit / totalInvestment) * 100 : 0

  return [
    {
      title: 'Portfolio Value',
      value: formatCurrency(totalValue),
      change: `${profitPercentage.toFixed(2)}%`,
      isPositive: profitPercentage >= 0,
      icon: DollarSign
    },
    {
      title: 'Invested',
      value: formatCurrency(totalInvestment),
      change: `${w.buyCount || 0} buys`,
      icon: TrendingUp
    },
    {
      title: 'Profit/Loss',
      value: formatCurrency(profit),
      change: profit >= 0 ? 'Profit' : 'Loss',
      isPositive: profit >= 0,
      icon: profit >= 0 ? TrendingUp : TrendingDown
    },
    {
      title: 'Assets',
      value: w.assets?.length || 0,
      change: `${w.totalUnits || 0} units`,
      icon: Package
    }
  ]
})

const filteredTransactions = computed(() => {
  let filtered = transactions.value

  if (activeTab.value === 'buy') filtered = filtered.filter(t => t.originalType === 'ACHAT')
  else if (activeTab.value === 'sell') filtered = filtered.filter(t => t.originalType === 'VENTE')
  else if (activeTab.value === 'cancelled') filtered = filtered.filter(t => t.cancelled)

  if (filterType.value !== 'all') filtered = filtered.filter(t => t.originalType === filterType.value)

  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(t =>
      t.crypto.name.toLowerCase().includes(q) ||
      t.crypto.symbol.toLowerCase().includes(q) ||
      t.id.toLowerCase().includes(q)
    )
  }

  const now = new Date()
  const cutoff = new Date()
  if (dateRange.value === '7d') cutoff.setDate(now.getDate() - 7)
  else if (dateRange.value === '30d') cutoff.setDate(now.getDate() - 30)
  else if (dateRange.value === '90d') cutoff.setDate(now.getDate() - 90)

  if (dateRange.value !== 'all') {
    filtered = filtered.filter(t => new Date(t.date) >= cutoff)
  }

  return filtered
})

const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  return filteredTransactions.value.slice(start, start + itemsPerPage.value)
})

const totalPages = computed(() => Math.ceil(filteredTransactions.value.length / itemsPerPage.value))

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
const getAvailableQuantity = (symbol: string) => availableQuantities.value[symbol]?.amount || 0

const canSell = (tx: any) => {
  if (tx.cancelled || tx.originalType !== 'ACHAT') return false
  const available = getAvailableQuantity(tx.crypto.symbol)
  return available > 0 && available >= 0.00000001
}

const openSellDialog = (tx: any) => {
  selectedAsset.value = tx
  sellQuantity.value = getAvailableQuantity(tx.crypto.symbol).toFixed(8)
  sellError.value = null
  sellSuccess.value = null
  showSellDialog.value = true
}

const closeSellDialog = () => {
  showSellDialog.value = false
  selectedAsset.value = null
  sellQuantity.value = ''
  sellError.value = null
  sellSuccess.value = null
}

const confirmSell = async () => {
  if (!selectedAsset.value) return

  const qty = parseFloat(sellQuantity.value)
  const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)

  if (!qty || qty <= 0) {
    sellError.value = 'Please enter a valid quantity'
    return
  }
  if (qty > available) {
    sellError.value = `Insufficient quantity. Available: ${formatNumber(available, 8)} ${selectedAsset.value.crypto.symbol.toUpperCase()}`
    return
  }
  if (qty < 0.00000001) {
    sellError.value = 'Minimum sell amount is 0.00000001'
    return
  }

  isSelling.value = true
  sellError.value = null

  try {
    await api.wallet.transact({
      symbol: selectedAsset.value.crypto.symbol,
      type: 'VENTE',
      quantity: qty,
    })
    sellSuccess.value = 'Sale completed successfully!'
    setTimeout(async () => {
      await loadTransactions()
      await fetchWallet()
      closeSellDialog()
    }, 1500)
  } catch (e: any) {
    sellError.value = e?.message || 'Error during sale'
  } finally {
    isSelling.value = false
  }
}

const resetFilters = () => {
  searchQuery.value = ''
  filterType.value = 'all'
  dateRange.value = '30d'
  activeTab.value = 'all'
  currentPage.value = 1
}

const exportTransactions = () => {
  const data = filteredTransactions.value.map(tx => ({
    ID: tx.id,
    Type: tx.typeLabel,
    Crypto: tx.crypto.name,
    Symbol: tx.crypto.symbol,
    Quantity: tx.quantity,
    Price: formatCurrency(tx.price),
    Total: formatCurrency(tx.total),
    Date: tx.date,
    Status: tx.cancelled ? 'Cancelled' : 'Completed'
  }))
  
  const csv = Object.keys(data[0] || {}).join(',') + '\n' + data.map(row => Object.values(row).join(',')).join('\n')
  const blob = new Blob([csv], { type: 'text/csv' })
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = `transactions_${new Date().toISOString().split('T')[0]}.csv`
  a.click()
}

// ============================================================================
// LIFECYCLE & WATCHERS
// ============================================================================
onMounted(async () => {
  await Promise.all([fetchWallet(), loadTransactions()])
})

watch([searchQuery, filterType, dateRange, activeTab], () => {
  currentPage.value = 1
  loadTransactions()
})
</script>

<template>
  <div class="min-h-screen bg-white dark:bg-gray-900 p-4 md:p-6">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-6">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Transactions</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400">Track and manage your trading activity</p>
        </div>
        
        <div class="flex flex-wrap gap-2">
          <Button variant="outline" size="sm" class="gap-2" @click="exportTransactions" :disabled="loading || filteredTransactions.length === 0">
            <Download class="w-4 h-4" /> Export
          </Button>
          <Button variant="outline" size="sm" class="gap-2" @click="showFilters = !showFilters">
            <Filter class="w-4 h-4" /> Filters
          </Button>
          <Button variant="outline" size="sm" @click="resetFilters" :disabled="searchQuery === '' && filterType === 'all'">
            Clear
          </Button>
          <Button size="sm" class="gap-2" @click="loadTransactions" :disabled="loading">
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': loading }" /> Refresh
          </Button>
        </div>
      </div>

      <!-- Metrics -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card v-for="(metric, index) in portfolioMetrics" :key="index" class="border border-gray-200 dark:border-gray-800">
          <CardContent class="p-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ metric.title }}</p>
                <p class="text-lg font-semibold text-gray-900 dark:text-white">{{ metric.value }}</p>
              </div>
              <Badge :variant="metric.isPositive ? 'default' : 'destructive'" class="text-xs">
                {{ metric.change }}
              </Badge>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Filters -->
      <Card v-if="showFilters" class="border border-gray-200 dark:border-gray-800">
        <CardContent class="p-4">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="space-y-2">
              <Label class="text-xs font-medium">Search</Label>
              <div class="relative">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-4 h-4" />
                <Input v-model="searchQuery" placeholder="Search..." class="pl-10 text-sm" />
              </div>
            </div>
            
            <div class="space-y-2">
              <Label class="text-xs font-medium">Period</Label>
              <Select v-model="dateRange">
                <SelectTrigger class="text-sm">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="7d">Last 7 days</SelectItem>
                  <SelectItem value="30d">Last 30 days</SelectItem>
                  <SelectItem value="90d">Last 90 days</SelectItem>
                  <SelectItem value="all">All time</SelectItem>
                </SelectContent>
              </Select>
            </div>
            
            <div class="space-y-2">
              <Label class="text-xs font-medium">Type</Label>
              <Select v-model="filterType">
                <SelectTrigger class="text-sm">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">All</SelectItem>
                  <SelectItem value="ACHAT">Buy</SelectItem>
                  <SelectItem value="VENTE">Sell</SelectItem>
                </SelectContent>
              </Select>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Stats -->
      <Card class="border border-gray-200 dark:border-gray-800">
        <CardContent class="p-4">
          <div class="grid grid-cols-4 gap-4">
            <div class="text-center">
              <p class="text-xs text-gray-500 dark:text-gray-400">Total</p>
              <p class="text-lg font-semibold">{{ transactions.length }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-500 dark:text-gray-400">Buy</p>
              <p class="text-lg font-semibold text-green-600">{{ stats.countBuys }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-500 dark:text-gray-400">Sell</p>
              <p class="text-lg font-semibold text-red-600">{{ stats.countSells }}</p>
            </div>
            <div class="text-center">
              <p class="text-xs text-gray-500 dark:text-gray-400">Cancelled</p>
              <p class="text-lg font-semibold text-yellow-600">{{ stats.countCancelled }}</p>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Tabs -->
      <Tabs v-model="activeTab" class="space-y-4">
        
        <!-- Loading -->
        <Card v-if="loading">
          <CardContent class="p-8 text-center">
            <div class="w-8 h-8 border-2 border-gray-300 border-t-gray-600 dark:border-gray-600 dark:border-t-gray-300 rounded-full animate-spin mx-auto"></div>
          </CardContent>
        </Card>

        <!-- Error -->
        <Alert v-else-if="error" variant="destructive">
          <AlertCircle class="w-4 h-4" />
          <AlertDescription>{{ error }}</AlertDescription>
        </Alert>

        <!-- Empty -->
        <Card v-else-if="filteredTransactions.length === 0">
          <CardContent class="p-8 text-center">
            <p class="text-gray-500 dark:text-gray-400">No transactions found</p>
          </CardContent>
        </Card>

        <!-- Transactions List -->
        <TabsContent v-else value="all" class="space-y-4">
          <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              {{ filteredTransactions.length }} transactions
            </div>
            <Select v-model="itemsPerPage">
              <SelectTrigger class="w-32 text-sm">
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem :value="10">10/page</SelectItem>
                <SelectItem :value="25">25/page</SelectItem>
                <SelectItem :value="50">50/page</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-3">
            <Card v-for="tx in paginatedTransactions" :key="tx.id" class="border border-gray-200 dark:border-gray-700 hover:border-gray-300 dark:hover:border-gray-600">
              <CardContent class="p-4">
                <div class="flex items-start justify-between">
                  <div class="flex items-start gap-4">
                    <div class="relative">
                      <div class="w-10 h-10 rounded-lg bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        <span class="font-bold">{{ tx.crypto.symbol.charAt(0) }}</span>
                      </div>
                      <Badge :class="tx.originalType === 'ACHAT' ? 'bg-green-500' : 'bg-red-500'" class="absolute -top-1 -right-1 w-5 h-5 p-0 text-xs">
                        {{ tx.originalType === 'ACHAT' ? 'B' : 'S' }}
                      </Badge>
                    </div>

                    <div>
                      <div class="flex items-center gap-2 mb-2">
                        <span class="font-medium text-gray-900 dark:text-white">{{ tx.crypto.name }}</span>
                        <Badge variant="outline" class="text-xs">{{ tx.crypto.symbol.toUpperCase() }}</Badge>
                        <Badge :variant="tx.cancelled ? 'destructive' : tx.originalType === 'ACHAT' ? 'default' : 'destructive'" class="text-xs">
                          {{ tx.cancelled ? 'CANCELLED' : tx.typeLabel }}
                        </Badge>
                      </div>
                      
                      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div>
                          <p class="text-xs text-gray-500">Quantity</p>
                          <p class="font-mono">{{ formatNumber(tx.quantity, 8) }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Price</p>
                          <p>{{ formatCurrency(tx.unitPrice) }}</p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Total</p>
                          <p :class="tx.originalType === 'ACHAT' ? 'text-green-600' : 'text-red-600'">
                            {{ formatCurrency(tx.total) }}
                          </p>
                        </div>
                        <div>
                          <p class="text-xs text-gray-500">Date</p>
                          <p>{{ formatRelativeDate(tx.date) }}</p>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="flex flex-col items-end gap-2">
                    <div class="text-right">
                      <p class="text-xs text-gray-500">Available</p>
                      <p class="font-mono text-sm">{{ formatNumber(getAvailableQuantity(tx.crypto.symbol), 8) }}</p>
                    </div>
                    <Button v-if="!tx.cancelled && canSell(tx)" size="sm" variant="destructive" @click="openSellDialog(tx)">
                      Sell
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Pagination -->
          <div v-if="totalPages > 1" class="flex items-center justify-between pt-4">
            <div class="text-sm text-gray-600 dark:text-gray-400">
              Page {{ currentPage }} of {{ totalPages }}
            </div>
            <div class="flex gap-2">
              <Button variant="outline" size="sm" :disabled="currentPage === 1" @click="currentPage--">
                Previous
              </Button>
              <Button variant="outline" size="sm" :disabled="currentPage === totalPages" @click="currentPage++">
                Next
              </Button>
            </div>
          </div>
        </TabsContent>
      </Tabs>

      <!-- Sell Dialog -->
      <Dialog :open="showSellDialog" @update:open="closeSellDialog">
        <DialogContent class="sm:max-w-md">
          <DialogHeader>
            <DialogTitle>Sell {{ selectedAsset?.crypto?.name }}</DialogTitle>
          </DialogHeader>

          <div class="space-y-4 py-4">
            <div class="space-y-2">
              <Label>Sell Quantity</Label>
              <Input
                v-model="sellQuantity"
                type="number"
                step="0.00000001"
                :max="getAvailableQuantity(selectedAsset?.crypto?.symbol)"
                class="font-mono"
                :disabled="isSelling"
              />
              <p class="text-xs text-gray-500">
                Available: {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto?.symbol), 8) }} {{ selectedAsset?.crypto?.symbol?.toUpperCase() }}
              </p>
            </div>

            <div class="grid grid-cols-4 gap-2">
              <Button
                v-for="percent in [25, 50, 75, 100]"
                :key="percent"
                variant="outline"
                size="sm"
                @click="sellQuantity = (getAvailableQuantity(selectedAsset?.crypto?.symbol) * (percent / 100)).toFixed(8)"
                :disabled="isSelling"
              >
                {{ percent }}%
              </Button>
            </div>

            <Card>
              <CardContent class="p-4 space-y-2">
                <div class="flex justify-between">
                  <span class="text-gray-600">Quantity</span>
                  <span class="font-mono">{{ sellQuantity || '0.00000000' }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">Price</span>
                  <span>{{ formatCurrency(selectedAsset?.crypto?.current_price || 0) }}</span>
                </div>
                <div class="flex justify-between pt-2 border-t">
                  <span class="font-semibold">Total</span>
                  <span class="font-bold">
                    {{ formatCurrency((parseFloat(sellQuantity || 0) * (selectedAsset?.crypto?.current_price || 0))) }}
                  </span>
                </div>
              </CardContent>
            </Card>

            <Alert v-if="sellError" variant="destructive">
              <AlertCircle class="w-4 h-4" />
              <AlertDescription>{{ sellError }}</AlertDescription>
            </Alert>

            <Alert v-if="sellSuccess" class="border-green-200 bg-green-50">
              <CheckCircle2 class="w-4 h-4 text-green-600" />
              <AlertDescription class="text-green-600">{{ sellSuccess }}</AlertDescription>
            </Alert>
          </div>

          <DialogFooter class="gap-3">
            <Button variant="outline" :disabled="isSelling" @click="closeSellDialog">Cancel</Button>
            <Button :disabled="isSelling || !sellQuantity || parseFloat(sellQuantity) <= 0" variant="destructive" @click="confirmSell">
              {{ isSelling ? 'Processing...' : 'Confirm Sell' }}
            </Button>
          </DialogFooter>
        </DialogContent>
      </Dialog>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
</style>