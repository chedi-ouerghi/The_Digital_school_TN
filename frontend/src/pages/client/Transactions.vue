<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Skeleton } from '@/components/ui/skeleton'
import { Switch } from '@/components/ui/switch'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'
import {
  AlertCircle,
  ArrowDownRight,
  ArrowUpRight,
  Calendar,
  CheckCircle2,
  ChevronLeft,
  ChevronRight,
  Clock, Coins,
  DollarSign,
  Download,
  Eye,
  FileText,
  Filter,
  Hash,
  Loader2,
  Package,
  Receipt,
  RefreshCw,
  Search,
  Shield,
  TrendingDown,
  TrendingUp,
  X,
  Zap
} from 'lucide-vue-next'
import { computed, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()

// State
const wallet = ref<any>(null)
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const activeTab = ref('all')
const dateRange = ref('30d')
const filterType = ref<'all' | 'ACHAT' | 'VENTE'>('all')
const searchQuery = ref('')
const showAdvancedFilters = ref(false)
const isRefreshing = ref(false)
const itemsPerPage = ref(10)
const currentPage = ref(1)
const showValueInEur = ref(true)

// Sell Dialog State
const showSellDialog = ref(false)
const selectedAsset = ref<any>(null)
const sellQuantity = ref('')
const sellError = ref<string | null>(null)
const sellSuccess = ref<string | null>(null)
const isSelling = ref(false)

// Define colors
const colors = {
  primary: '#35A7FF',
  success: '#01FF19',
  danger: '#FF5964',
  dark: '#38618C',
  neutral: '#64748B'
}

// ============================================================================
// UTILITY FUNCTIONS
// ============================================================================
const formatCurrency = (value: any) => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('en-US', { 
    style: 'currency', 
    currency: 'EUR', 
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

const formatCompactCurrency = (value: any) => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0'
  
  if (n >= 1000000) return `€${(n / 1000000).toFixed(2)}M`
  if (n >= 1000) return `€${(n / 1000).toFixed(1)}K`
  return formatCurrency(n)
}

const formatNumber = (value: any, decimals = 8) => {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  const formatted = n.toFixed(decimals)
  return formatted.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '')
}

const formatRelativeDate = (date: string) => {
  const now = new Date()
  const transactionDate = new Date(date)
  const diff = now.getTime() - transactionDate.getTime()
  
  const days = Math.floor(diff / (1000 * 60 * 60 * 24))
  const hours = Math.floor(diff / (1000 * 60 * 60))
  const minutes = Math.floor(diff / (1000 * 60))
  
  if (days > 30) {
    return transactionDate.toLocaleDateString('en-US', { 
      month: 'short', 
      day: 'numeric',
      year: 'numeric'
    })
  }
  if (days > 0) return `${days}d ago`
  if (hours > 0) return `${hours}h ago`
  if (minutes > 0) return `${minutes}m ago`
  return 'Just now'
}

const formatFullDate = (date: string) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// ============================================================================
// API FUNCTIONS
// ============================================================================
const fetchWallet = async () => {
  try {
    const response = await api.wallet.list()
    wallet.value = response as any
    // Ensure we have the crypto wallet assets for merging with transaction data
    if (!wallet.value.cryptoWalletAssets) {
      wallet.value.cryptoWalletAssets = []
    }
  } catch (e: any) {
    console.error('Error fetching wallet:', e)
    error.value = e.message || 'Error loading wallet data'
  }
}

const loadTransactions = async () => {
  loading.value = true
  error.value = null
  try {
    console.log('🔄 Loading transactions with filterType:', filterType.value)
    
    // Always fetch ALL transactions first (don't filter at API level)
    // The tabs will handle local filtering
    const response = await api.wallet.getTransactionsHistory()
    const transactionsData = response?.transactions || []
    
    console.log('✅ Fetched transactions:', transactionsData.length)
    
    if (!transactionsData || transactionsData.length === 0) {
      console.warn('⚠️ No transactions received from API')
      transactions.value = []
      return
    }
    
    // Process transactions
    transactions.value = transactionsData.map((tx: any) => ({
      id: tx.id,
      crypto_id: tx.crypto_id,
      originalType: tx.type,
      type: tx.type === 'ACHAT' ? 'buy' : 'sell',
      typeLabel: tx.type === 'ACHAT' ? 'Purchase' : 'Sale',
      quantity: Number(tx.quantity || 0),
      price: Number(tx.price || 0),
      unitPrice: Number(tx.unit_price_eur || tx.price || 0),
      total: Number(tx.total_eur || 0),
      date: tx.created_at || tx.date,
      crypto: {
        id: tx.crypto_id,
        name: tx.crypto_name || 'Unknown',
        symbol: tx.crypto_symbol || 'N/A',
        image_url: tx.crypto_image_url || '',
        current_price: 0 // Will be populated from wallet data
      }
    })).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())
    
    console.log('✅ Processed transactions:', transactions.value.length)
    
    // Merge with wallet data for current prices
    if (wallet.value?.cryptoWalletAssets) {
      transactions.value.forEach(tx => {
        const asset = wallet.value.cryptoWalletAssets.find((a: any) => a.cryptomoney?.symbol === tx.crypto.symbol)
        if (asset) {
          tx.crypto.current_price = asset.cryptomoney?.price_eur || 0
          tx.crypto.image_url = asset.cryptomoney?.image_url || tx.crypto.image_url
        }
      })
    }
    
  } catch (e: any) {
    console.error('❌ Error loading transactions:', e)
    console.error('Error details:', {
      message: e.message,
      status: e.response?.status,
      statusText: e.response?.statusText,
      data: e.response?.data
    })
    error.value = e.message || 'Error loading transactions. Please try again.'
  } finally {
    loading.value = false
    isRefreshing.value = false
  }
}

const refreshData = async () => {
  isRefreshing.value = true
  await Promise.all([fetchWallet(), loadTransactions()])
}

// ============================================================================
// SELL DIALOG FUNCTIONS
// ============================================================================
const validateSellQuantity = () => {
  if (!selectedAsset.value) return
  const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)
  const quantity = parseFloat(sellQuantity.value || '0')
  
  if (quantity > available) {
    sellQuantity.value = available.toFixed(8)
  }
  
  if (quantity < 0.00000001 && quantity > 0) {
    sellQuantity.value = '0.00000001'
  }
}

const setSellPercentage = (percent: number) => {
  if (!selectedAsset.value) return
  const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)
  sellQuantity.value = (available * (percent / 100)).toFixed(8)
  validateSellQuantity()
}

const calculateSellAmount = () => {
  if (!selectedAsset.value || !sellQuantity.value) return 0
  const quantity = parseFloat(sellQuantity.value)
  const price = selectedAsset.value.crypto.current_price || selectedAsset.value.unitPrice || 0
  return quantity * price
}

const calculateProfitLoss = () => {
  if (!selectedAsset.value?.unitPrice || !sellQuantity.value) return 0
  const sellAmount = calculateSellAmount()
  const cost = parseFloat(sellQuantity.value) * selectedAsset.value.unitPrice
  return sellAmount - cost
}

const calculateProfitLossPercentage = () => {
  const profitLoss = calculateProfitLoss()
  const cost = parseFloat(sellQuantity.value || '0') * (selectedAsset.value?.unitPrice || 0)
  if (cost === 0) return 0
  return ((profitLoss / cost) * 100).toFixed(2)
}

const getAvailableQuantity = (symbol: string) => {
  // Calculate available quantity from wallet assets
  if (!wallet.value?.assets) return 0
  
  const asset = wallet.value.assets.find((a: any) => a.symbol === symbol)
  return asset ? Number(asset.quantity || 0) : 0
}

const canSell = (tx: any) => {
  if (tx.originalType !== 'ACHAT') return false
  const available = getAvailableQuantity(tx.crypto.symbol)
  return available > 0 && available >= 0.00000001
}

const openSellDialog = (tx: any) => {
  selectedAsset.value = tx
  selectedAsset.value.average_price = tx.unitPrice
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
  isSelling.value = false
}

const confirmSell = async () => {
  if (!selectedAsset.value) return

  const qty = parseFloat(sellQuantity.value)
  const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)

  // 🔐 SECURITY: Vérifier que l'utilisateur a changé son mot de passe
  try {
    const profile = await api.auth.profile()
    if (!profile.password_changed_at) {
      sellError.value = 'You must change your password before making sales. Please go to your profile settings.'
      return
    }
  } catch (err) {
    console.error('Error checking password status:', err)
    sellError.value = 'Error verifying your account status'
    return
  }

  // Validation
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
    // Call API to sell
    await api.wallet.transact({
      symbol: selectedAsset.value.crypto.symbol,
      type: 'VENTE',
      quantity: qty,
    })
    
    sellSuccess.value = 'Sale completed successfully!'
    
    // Refresh data after successful sale
    setTimeout(async () => {
      await refreshData()
      closeSellDialog()
    }, 1500)
    
  } catch (e: any) {
    sellError.value = e?.message || 'Error during sale. Please try again.'
  } finally {
    isSelling.value = false
  }
}

// ============================================================================
// COMPUTED PROPERTIES
// ============================================================================
// Portfolio stats from wallet data
const portfolioStats = computed(() => {
  if (!wallet.value) return null
  
  const totalValue = wallet.value.totalValue || 0
  const totalInvestment = wallet.value.totalInvestment || 0
  const totalPlusValue = wallet.value.totalPlusValue || 0
  const totalPlusValuePercent = wallet.value.totalPlusValuePercent || 0
  const balance = wallet.value.balance_eur || 0
  const assets = wallet.value.assets || []
  const buyCount = wallet.value.buyCount || 0
  
  return {
    totalValue,
    totalInvestment,
    totalPlusValue,
    totalPlusValuePercent,
    balance,
    assets,
    buyCount,
    assetCount: assets.length,
    totalUnits: wallet.value.totalUnits || 0
  }
})

// Main statistics cards
const statsCards = computed(() => {
  if (!portfolioStats.value) return []
  
  const stats = portfolioStats.value
  
  return [
    {
      title: 'Portfolio Value',
      value: formatCompactCurrency(stats.totalValue),
      change: `${stats.totalPlusValuePercent.toFixed(2)}%`,
      positive: stats.totalPlusValuePercent >= 0,
      icon: DollarSign,
      color: colors.primary,
      description: 'Total current value'
    },
    {
      title: 'Available Balance',
      value: formatCurrency(stats.balance),
      change: 'Ready to trade',
      positive: true,
      icon: Coins,
      color: colors.success,
      description: 'Free balance'
    },
    {
      title: 'Total Investment',
      value: formatCompactCurrency(stats.totalInvestment),
      change: `${stats.buyCount} transactions`,
      positive: true,
      icon: TrendingUp,
      color: colors.dark,
      description: 'Total invested'
    },
    {
      title: 'Active Assets',
      value: stats.assetCount,
      change: `${stats.totalUnits.toFixed(2)} units`,
      positive: true,
      icon: Package,
      color: colors.neutral,
      description: 'Diversified holdings'
    }
  ]
})

// Transaction summary statistics
const transactionStats = computed(() => {
  const allTransactions = transactions.value
  const buyTransactions = allTransactions.filter(t => t.originalType === 'ACHAT')
  const sellTransactions = allTransactions.filter(t => t.originalType === 'VENTE')
  
  const totalBuyAmount = buyTransactions.reduce((sum, t) => sum + t.total, 0)
  const totalSellAmount = sellTransactions.reduce((sum, t) => sum + t.total, 0)
  const totalTransactions = allTransactions.length
  
  // Calculate recent activity (last 7 days)
  const weekAgo = new Date()
  weekAgo.setDate(weekAgo.getDate() - 7)
  const recentTransactions = allTransactions.filter(t => new Date(t.date) >= weekAgo)
  
  return {
    totalTransactions,
    buyCount: buyTransactions.length,
    sellCount: sellTransactions.length,
    totalBuyAmount,
    totalSellAmount,
    netFlow: totalBuyAmount - totalSellAmount,
    recentActivity: recentTransactions.length,
    avgTransactionSize: totalTransactions > 0 ? (totalBuyAmount + totalSellAmount) / totalTransactions : 0
  }
})

// Filtered transactions
const filteredTransactions = computed(() => {
  let filtered = transactions.value

  // Filter by active tab (all, buy, sell)
  if (activeTab.value === 'buy') {
    filtered = filtered.filter(t => t.originalType === 'ACHAT')
  } else if (activeTab.value === 'sell') {
    filtered = filtered.filter(t => t.originalType === 'VENTE')
  }
  
  // Also apply filterType if it's set differently from tab
  // This allows advanced filter dropdown to work independently
  if (filterType.value !== 'all' && activeTab.value === 'all') {
    filtered = filtered.filter(t => t.originalType === filterType.value)
  }

  // Filter by search query
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase()
    filtered = filtered.filter(t =>
      t.crypto.name.toLowerCase().includes(query) ||
      t.crypto.symbol.toLowerCase().includes(query) ||
      t.id.toLowerCase().includes(query)
    )
  }

  // Filter by date range
  if (dateRange.value !== 'all') {
    const now = new Date()
    const cutoff = new Date()
    
    switch (dateRange.value) {
      case '7d': cutoff.setDate(now.getDate() - 7); break
      case '30d': cutoff.setDate(now.getDate() - 30); break
      case '90d': cutoff.setDate(now.getDate() - 90); break
      case '1y': cutoff.setFullYear(now.getFullYear() - 1); break
    }
    
    filtered = filtered.filter(t => new Date(t.date) >= cutoff)
  }

  console.log(`📊 Filtered transactions: ${filtered.length} (tab: ${activeTab.value}, type: ${filterType.value}, search: ${searchQuery.value})`)
  
  return filtered
})

// Paginated transactions
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage.value
  const end = start + itemsPerPage.value
  return filteredTransactions.value.slice(start, end)
})

const totalPages = computed(() => {
  return Math.ceil(filteredTransactions.value.length / itemsPerPage.value)
})

// Transaction type summary
const transactionTypeSummary = computed(() => [
  {
    type: 'ACHAT',
    label: 'Purchases',
    count: transactionStats.value.buyCount,
    total: transactionStats.value.totalBuyAmount,
    icon: ArrowDownRight,
    color: colors.success,
    bgColor: `${colors.success}15`
  },
  {
    type: 'VENTE',
    label: 'Sales',
    count: transactionStats.value.sellCount,
    total: transactionStats.value.totalSellAmount,
    icon: ArrowUpRight,
    color: colors.danger,
    bgColor: `${colors.danger}15`
  }
])

// ============================================================================
// HELPER FUNCTIONS
// ============================================================================
const getTransactionIcon = (type: string) => {
  return type === 'ACHAT' ? ArrowDownRight : ArrowUpRight
}

const getTransactionColor = (type: string) => {
  return type === 'ACHAT' ? colors.success : colors.danger
}

const getTransactionBgColor = (type: string) => {
  return type === 'ACHAT' ? `${colors.success}15` : `${colors.danger}15`
}

const resetFilters = () => {
  searchQuery.value = ''
  filterType.value = 'all'
  dateRange.value = '30d'
  activeTab.value = 'all'
  currentPage.value = 1
  showAdvancedFilters.value = false
}

const exportTransactions = () => {
  try {
    const data = filteredTransactions.value.map(tx => ({
      'Transaction ID': tx.id,
      'Type': tx.typeLabel,
      'Crypto': tx.crypto.name,
      'Symbol': tx.crypto.symbol,
      'Quantity': tx.quantity,
      'Unit Price': formatCurrency(tx.unitPrice),
      'Total Amount': formatCurrency(tx.total),
      'Date': formatFullDate(tx.date),
      'Status': 'Completed'
    }))
    
    if (data.length === 0) {
      alert('No transactions to export')
      return
    }
    
    const csv = [
      Object.keys(data[0]).join(','),
      ...data.map(row => Object.values(row).join(','))
    ].join('\n')
    
    const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
    const link = document.createElement('a')
    const url = URL.createObjectURL(blob)
    
    link.setAttribute('href', url)
    link.setAttribute('download', `transactions_${new Date().toISOString().split('T')[0]}.csv`)
    link.style.visibility = 'hidden'
    
    document.body.appendChild(link)
    link.click()
    document.body.removeChild(link)
    
  } catch (error) {
    console.error('Export error:', error)
    alert('Error exporting transactions')
  }
}

const navigateToPage = (page: number) => {
  if (page >= 1 && page <= totalPages.value) {
    currentPage.value = page
    // Scroll to top of transactions list
    const transactionsList = document.querySelector('.transactions-list')
    if (transactionsList) {
      transactionsList.scrollIntoView({ behavior: 'smooth' })
    }
  }
}

// ============================================================================
// LIFECYCLE & WATCHERS
// ============================================================================
onMounted(async () => {
  console.log('🚀 Component mounted, fetching initial data...')
  await refreshData()
})

// Watcher for active tab - reset pagination and reload
watch(activeTab, (newTab, oldTab) => {
  console.log(`📑 Tab changed from "${oldTab}" to "${newTab}"`)
  currentPage.value = 1
  // Don't reload from API, just filter locally
  // The filteredTransactions computed property will handle the filtering
})

// Watcher for filter type and date range - reload transactions
watch([filterType, dateRange], (newValues, oldValues) => {
  console.log(`🔍 Filters changed:`, { filterType: newValues[0], dateRange: newValues[1] })
  currentPage.value = 1
  loadTransactions()
})

// Watcher for search query - reset pagination
watch(searchQuery, () => {
  console.log(`🔎 Search query changed: "${searchQuery.value}"`)
  currentPage.value = 1
})
</script>

<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white p-4 md:p-6">
    <div class="max-w-7xl mx-auto space-y-8">
      <!-- Header Section -->
      <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-2">
        <div>
          <h1 class="text-3xl font-bold text-[#0F172A] tracking-tight">
            Transaction History
          </h1>
          <p class="text-[#64748B] mt-2">
            Track, analyze, and manage all your trading activities
          </p>
        </div>
        
        <div class="flex flex-wrap items-center gap-3">
          <TooltipProvider>
            <Tooltip>
              <TooltipTrigger as-child>
                <div class="flex items-center gap-2 px-3 py-1.5 bg-white border border-[#E2E8F0] rounded-lg">
                  <Switch
                    v-model:checked="showValueInEur"
                    class="data-[state=checked]:bg-[#35A7FF]"
                  />
                  <Label class="text-sm text-[#64748B] cursor-pointer">
                    {{ showValueInEur ? 'Show EUR' : 'Show Crypto' }}
                  </Label>
                </div>
              </TooltipTrigger>
              <TooltipContent>
                <p>Toggle between EUR and crypto values</p>
              </TooltipContent>
            </Tooltip>
          </TooltipProvider>
          
          <Button
            variant="outline"
            class="gap-2 border-[#E2E8F0] text-[#64748B] hover:bg-[#35A7FF]/5 hover:text-[#35A7FF]"
            :disabled="isRefreshing"
            @click="refreshData"
          >
            <RefreshCw class="w-4 h-4" :class="{ 'animate-spin': isRefreshing }" />
            {{ isRefreshing ? 'Refreshing...' : 'Refresh' }}
          </Button>
          
          <Button 
            class="gap-2 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
            :disabled="filteredTransactions.length === 0"
            @click="exportTransactions"
          >
            <Download class="w-4 h-4" />
            Export CSV
          </Button>
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <template v-if="loading && !wallet">
          <Card v-for="i in 4" :key="i" class="border-[#E2E8F0]">
            <CardContent class="p-5">
              <Skeleton class="h-8 w-32 mb-3" />
              <Skeleton class="h-4 w-24" />
            </CardContent>
          </Card>
        </template>
        
        <template v-else>
          <Card
            v-for="(stat, index) in statsCards"
            :key="index"
            class="group relative overflow-hidden border-[#E2E8F0] hover:border-[#35A7FF]/30 hover:shadow-lg transition-all duration-300"
          >
            <div
class="absolute inset-0 bg-gradient-to-br opacity-0 group-hover:opacity-5 transition-opacity duration-300" 
                 :style="{ background: `linear-gradient(135deg, ${stat.color}20, transparent)` }"></div>
            <CardContent class="p-5 relative">
              <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                  <div class="p-2.5 rounded-xl border border-[#E2E8F0]" :style="{ backgroundColor: `${stat.color}10` }">
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
              <div class="text-sm text-[#64748B]">{{ stat.description }}</div>
            </CardContent>
          </Card>
        </template>
      </div>

      <!-- Main Content Area -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column - Transactions List -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Filters Card -->
          <Card class="border-[#E2E8F0]">
            <CardContent class="p-5">
              <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
                <div>
                  <h3 class="text-lg font-semibold text-[#0F172A]">Transaction Filters</h3>
                  <p class="text-sm text-[#64748B] mt-1">
                    {{ filteredTransactions.length }} transaction{{ filteredTransactions.length !== 1 ? 's' : '' }} found
                  </p>
                </div>
                
                <div class="flex items-center gap-3">
                  <Button
                    variant="outline"
                    size="sm"
                    class="gap-2"
                    @click="showAdvancedFilters = !showAdvancedFilters"
                  >
                    <Filter class="w-4 h-4" />
                    {{ showAdvancedFilters ? 'Hide Filters' : 'Show Filters' }}
                  </Button>
                  
                  <Button
                    variant="ghost"
                    size="sm"
                    :disabled="searchQuery === '' && filterType === 'all' && dateRange === '30d'"
                    @click="resetFilters"
                  >
                    Clear All
                  </Button>
                </div>
              </div>

              <!-- Search Bar -->
              <div class="relative mb-4">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-[#64748B] w-4 h-4" />
                <Input
                  v-model="searchQuery"
                  placeholder="Search by crypto name, symbol, or transaction ID..."
                  class="pl-10 border-[#E2E8F0] focus:border-[#35A7FF]"
                />
              </div>

              <!-- Advanced Filters -->
              <div v-if="showAdvancedFilters" class="space-y-4 pt-4 border-t border-[#E2E8F0]">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div class="space-y-2">
                    <Label class="text-sm font-medium text-[#64748B]">Transaction Type</Label>
                    <Select v-model="filterType">
                      <SelectTrigger class="border-[#E2E8F0] focus:border-[#35A7FF]">
                        <SelectValue placeholder="All transaction types" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="all">All Types</SelectItem>
                        <SelectItem value="ACHAT">Purchases Only</SelectItem>
                        <SelectItem value="VENTE">Sales Only</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                  
                  <div class="space-y-2">
                    <Label class="text-sm font-medium text-[#64748B]">Date Range</Label>
                    <Select v-model="dateRange">
                      <SelectTrigger class="border-[#E2E8F0] focus:border-[#35A7FF]">
                        <SelectValue placeholder="Select period" />
                      </SelectTrigger>
                      <SelectContent>
                        <SelectItem value="7d">Last 7 days</SelectItem>
                        <SelectItem value="30d">Last 30 days</SelectItem>
                        <SelectItem value="90d">Last 90 days</SelectItem>
                        <SelectItem value="1y">Last year</SelectItem>
                        <SelectItem value="all">All time</SelectItem>
                      </SelectContent>
                    </Select>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Tabs Navigation -->
          <Tabs v-model="activeTab" class="w-full">
            <TabsList class="grid grid-cols-3 w-full max-w-md bg-white border border-[#E2E8F0] p-1 rounded-xl">
              <TabsTrigger 
                value="all"
                class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#35A7FF] data-[state=active]:to-[#38618C] data-[state=active]:text-white rounded-lg"
              >
                All Transactions
              </TabsTrigger>
              <TabsTrigger 
                value="buy"
                class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#01FF19] data-[state=active]:to-[#35A7FF] data-[state=active]:text-white rounded-lg"
              >
                Purchases
              </TabsTrigger>
              <TabsTrigger 
                value="sell"
                class="data-[state=active]:bg-gradient-to-r data-[state=active]:from-[#FF5964] data-[state=active]:to-[#FF8B94] data-[state=active]:text-white rounded-lg"
              >
                Sales
              </TabsTrigger>
            </TabsList>
          </Tabs>

          <!-- Transactions List -->
          <div class="transactions-list">
            <!-- Loading State -->
            <div v-if="loading" class="space-y-4">
              <Card v-for="i in 3" :key="i" class="border-[#E2E8F0]">
                <CardContent class="p-5">
                  <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                      <Skeleton class="w-10 h-10 rounded-xl" />
                      <div class="space-y-2">
                        <Skeleton class="h-4 w-32" />
                        <Skeleton class="h-3 w-24" />
                      </div>
                    </div>
                    <Skeleton class="h-8 w-24" />
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Error State -->
            <Alert v-else-if="error" variant="destructive" class="border-[#FF5964] bg-[#FF5964]/10">
              <AlertCircle class="w-4 h-4 text-[#FF5964]" />
              <AlertDescription class="text-[#FF5964]">
                {{ error }}
              </AlertDescription>
            </Alert>

            <!-- Empty State -->
            <div v-else-if="filteredTransactions.length === 0" class="text-center py-12">
              <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-[#35A7FF]/10 to-[#38618C]/10 flex items-center justify-center">
                <Receipt class="w-10 h-10 text-[#35A7FF]" />
              </div>
              <h3 class="text-lg font-semibold text-[#0F172A] mb-2">No transactions found</h3>
              <p class="text-[#64748B] max-w-md mx-auto mb-6">
                {{ searchQuery || filterType !== 'all' || dateRange !== '30d' 
                  ? 'Try adjusting your filters or search query'
                  : 'You haven\'t made any transactions yet' }}
              </p>
              <Button 
                v-if="!searchQuery && filterType === 'all' && dateRange === '30d'"
                class="bg-gradient-to-r from-[#35A7FF] to-[#38618C] text-white hover:opacity-90"
                @click="router.push('/dashboard/cryptos')"
              >
                Start Trading
              </Button>
              <Button 
                v-else
                variant="outline"
                @click="resetFilters"
              >
                Clear Filters
              </Button>
            </div>

            <!-- Transactions List -->
            <div v-else class="space-y-3">
              <Card
                v-for="transaction in paginatedTransactions"
                :key="transaction.id"
                class="group border-[#E2E8F0] hover:border-[#35A7FF]/30 hover:shadow-sm transition-all duration-200"
              >
                <CardContent class="p-5">
                  <div class="flex items-start justify-between">
                    <!-- Left Side: Transaction Info -->
                    <div class="flex items-start gap-4">
                      <!-- Transaction Icon -->
                      <div class="relative">
                        <div 
                          class="w-12 h-12 rounded-xl flex items-center justify-center border"
                          :style="{
                            backgroundColor: getTransactionBgColor(transaction.originalType),
                            borderColor: `${getTransactionColor(transaction.originalType)}30`
                          }"
                        >
                          <component 
                            :is="getTransactionIcon(transaction.originalType)"
                            class="w-6 h-6"
                            :style="{ color: getTransactionColor(transaction.originalType) }"
                          />
                        </div>
                        <Badge
                          class="absolute -top-2 -right-2 text-xs font-semibold border-2 border-white"
                          :style="{
                            backgroundColor: getTransactionColor(transaction.originalType),
                            color: 'white'
                          }"
                        >
                          {{ transaction.originalType === 'ACHAT' ? 'BUY' : 'SELL' }}
                        </Badge>
                      </div>

                      <!-- Transaction Details -->
                      <div class="space-y-2">
                        <div class="flex items-center gap-2">
                          <h4 class="font-semibold text-[#0F172A]">
                            {{ transaction.crypto.name }}
                          </h4>
                          <Badge variant="outline" class="text-xs font-mono">
                            {{ transaction.crypto.symbol }}
                          </Badge>
                          <span class="text-xs text-[#64748B]">•</span>
                          <span class="text-xs text-[#64748B] flex items-center gap-1">
                            <Hash class="w-3 h-3" />
                            {{ transaction.id.slice(0, 8) }}...
                          </span>
                        </div>

                        <div class="flex flex-wrap items-center gap-4 text-sm">
                          <div class="flex items-center gap-1.5">
                            <Coins class="w-3.5 h-3.5 text-[#64748B]" />
                            <span class="font-medium text-[#0F172A]">
                              {{ formatNumber(transaction.quantity, 8) }}
                            </span>
                            <span class="text-[#64748B]">
                              {{ transaction.crypto.symbol }}
                            </span>
                          </div>
                          
                          <div class="flex items-center gap-1.5">
                            <DollarSign class="w-3.5 h-3.5 text-[#64748B]" />
                            <span class="font-medium text-[#0F172A]">
                              {{ showValueInEur ? formatCurrency(transaction.unitPrice) : formatNumber(transaction.unitPrice) }}
                            </span>
                            <span class="text-[#64748B]">per unit</span>
                          </div>
                          
                          <div class="flex items-center gap-1.5">
                            <Clock class="w-3.5 h-3.5 text-[#64748B]" />
                            <span class="text-[#64748B]">
                              {{ formatRelativeDate(transaction.date) }}
                            </span>
                          </div>
                        </div>
                      </div>
                    </div>

                    <!-- Right Side: Amount & Actions -->
                    <div class="text-right">
                      <div class="mb-2">
                        <div class="text-2xl font-bold text-[#0F172A]">
                          {{ showValueInEur ? formatCurrency(transaction.total) : formatNumber(transaction.total) }}
                        </div>
                        <div class="text-xs text-[#64748B]">
                          Total {{ showValueInEur ? 'EUR' : 'Crypto' }}
                        </div>
                      </div>
                      
                      <div class="flex items-center gap-2">
                        <TooltipProvider>
                          <Tooltip>
                            <TooltipTrigger as-child>
                              <Button
                                variant="ghost"
                                size="sm"
                                class="gap-1 text-[#64748B] hover:text-[#35A7FF]"
                                @click="router.push(`/dashboard/cryptos/${transaction.crypto.id}`)"
                              >
                                <Eye class="w-3.5 h-3.5" />
                                View
                              </Button>
                            </TooltipTrigger>
                            <TooltipContent>
                              <p>View {{ transaction.crypto.name }} details</p>
                            </TooltipContent>
                          </Tooltip>
                        </TooltipProvider>
                        
                        <Button
                          v-if="canSell(transaction)"
                          variant="outline"
                          size="sm"
                          class="gap-1 border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964]/10"
                          @click="openSellDialog(transaction)"
                        >
                          Sell
                        </Button>
                      </div>
                    </div>
                  </div>

                  <!-- Additional Info -->
                  <div class="mt-4 pt-4 border-t border-[#E2E8F0]">
                    <div class="flex items-center justify-between text-sm">
                      <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1.5">
                          <Calendar class="w-3.5 h-3.5 text-[#64748B]" />
                          <span class="text-[#64748B]">
                            {{ formatFullDate(transaction.date) }}
                          </span>
                        </div>
                        
                        <div 
                          v-if="transaction.crypto.current_price"
                          class="flex items-center gap-1.5"
                          :class="transaction.crypto.current_price >= transaction.unitPrice ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                        >
                          <component 
                            :is="transaction.crypto.current_price >= transaction.unitPrice ? TrendingUp : TrendingDown"
                            class="w-3.5 h-3.5"
                          />
                          <span class="font-medium">
                            {{ formatCurrency(transaction.crypto.current_price) }}
                          </span>
                          <span class="text-[#64748B]">current price</span>
                        </div>
                      </div>
                      
                      <Badge 
                        variant="outline" 
                        class="text-xs"
                        :style="{
                          color: getTransactionColor(transaction.originalType),
                          borderColor: `${getTransactionColor(transaction.originalType)}30`,
                          backgroundColor: `${getTransactionColor(transaction.originalType)}10`
                        }"
                      >
                        {{ transaction.typeLabel.toUpperCase() }}
                      </Badge>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            <!-- Pagination -->
            <div v-if="totalPages > 1 && !loading" class="flex items-center justify-between pt-6 border-t border-[#E2E8F0]">
              <div class="text-sm text-[#64748B]">
                Showing {{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredTransactions.length) }} 
                to {{ Math.min(currentPage * itemsPerPage, filteredTransactions.length) }} 
                of {{ filteredTransactions.length }} transactions
              </div>
              
              <div class="flex items-center gap-2">
                <Select v-model="itemsPerPage">
                  <SelectTrigger class="w-28 border-[#E2E8F0]">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="10">10 per page</SelectItem>
                    <SelectItem :value="25">25 per page</SelectItem>
                    <SelectItem :value="50">50 per page</SelectItem>
                  </SelectContent>
                </Select>
                
                <div class="flex items-center gap-1">
                  <Button
                    variant="outline"
                    size="sm"
                    class="h-9 w-9 p-0"
                    :disabled="currentPage === 1"
                    @click="navigateToPage(currentPage - 1)"
                  >
                    <ChevronLeft class="w-4 h-4" />
                  </Button>
                  
                  <div class="flex items-center gap-1">
                    <Button
                      v-for="page in totalPages"
                      v-show="Math.abs(page - currentPage) <= 1 || page === 1 || page === totalPages"
                      :key="page"
                      variant="outline"
                      size="sm"
                      class="h-9 w-9"
                      :class="page === currentPage ? 'bg-[#35A7FF] text-white border-[#35A7FF]' : 'border-[#E2E8F0]'"
                      @click="navigateToPage(page)"
                    >
                      {{ page }}
                    </Button>
                  </div>
                  
                  <Button
                    variant="outline"
                    size="sm"
                    class="h-9 w-9 p-0"
                    :disabled="currentPage === totalPages"
                    @click="navigateToPage(currentPage + 1)"
                  >
                    <ChevronRight class="w-4 h-4" />
                  </Button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column - Stats & Insights -->
        <div class="space-y-6">
          <!-- Transaction Summary -->
          <Card class="border-[#E2E8F0]">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-[#0F172A]">
                Transaction Summary
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-4">
              <div
                v-for="summary in transactionTypeSummary"
                :key="summary.type"
                class="flex items-center justify-between p-3 rounded-lg hover:bg-[#F8FAFC] transition-colors"
              >
                <div class="flex items-center gap-3">
                  <div class="p-2 rounded-lg" :style="{ backgroundColor: summary.bgColor }">
                    <component :is="summary.icon" :style="{ color: summary.color }" class="w-4 h-4" />
                  </div>
                  <div>
                    <div class="font-medium text-[#0F172A]">{{ summary.label }}</div>
                    <div class="text-xs text-[#64748B]">{{ summary.count }} transactions</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="font-semibold text-[#0F172A]">
                    {{ formatCompactCurrency(summary.total) }}
                  </div>
                  <div class="text-xs text-[#64748B]">
                    {{ summary.type === 'ACHAT' ? 'Invested' : 'Received' }}
                  </div>
                </div>
              </div>
              
              <div class="pt-3 border-t border-[#E2E8F0]">
                <div class="flex justify-between items-center">
                  <span class="text-sm font-medium text-[#0F172A]">Net Flow</span>
                  <span 
                    class="text-sm font-bold"
                    :class="transactionStats.netFlow >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                  >
                    {{ transactionStats.netFlow >= 0 ? '+' : '' }}{{ formatCompactCurrency(transactionStats.netFlow) }}
                  </span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Quick Insights -->
          <Card class="border-[#E2E8F0]">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-[#0F172A]">
                Quick Insights
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <div class="flex items-center justify-between">
                <span class="text-sm text-[#64748B]">Recent Activity</span>
                <Badge class="bg-[#35A7FF]/10 text-[#35A7FF] border-[#35A7FF]/20">
                  {{ transactionStats.recentActivity }} this week
                </Badge>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-[#64748B]">Avg. Transaction</span>
                <span class="text-sm font-medium text-[#0F172A]">
                  {{ formatCompactCurrency(transactionStats.avgTransactionSize) }}
                </span>
              </div>
              
              <div class="flex items-center justify-between">
                <span class="text-sm text-[#64748B]">Total Volume</span>
                <span class="text-sm font-medium text-[#0F172A]">
                  {{ formatCompactCurrency(transactionStats.totalBuyAmount + transactionStats.totalSellAmount) }}
                </span>
              </div>
            </CardContent>
          </Card>

          <!-- Actions -->
          <Card class="border-[#E2E8F0]">
            <CardHeader>
              <CardTitle class="text-lg font-semibold text-[#0F172A]">
                Quick Actions
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <Button
                class="w-full justify-start h-12 bg-gradient-to-r from-[#35A7FF] to-[#38618C] text-white hover:opacity-90"
                @click="router.push('/dashboard/cryptos')"
              >
                <Zap class="w-5 h-5 mr-3" />
                Trade Crypto
              </Button>
              <Button
                variant="outline"
                class="w-full justify-start h-12 border-[#E2E8F0] text-[#0F172A] hover:border-[#01FF19] hover:bg-[#01FF19]/5"
                @click="router.push('/dashboard/portfolio')"
              >
                <TrendingUp class="w-5 h-5 mr-3 text-[#01FF19]" />
                View Portfolio
              </Button>
              <Button
                variant="outline"
                class="w-full justify-start h-12 border-[#E2E8F0] text-[#0F172A] hover:border-[#FF5964] hover:bg-[#FF5964]/5"
                @click="router.push('/dashboard/analytics')"
              >
                <FileText class="w-5 h-5 mr-3 text-[#FF5964]" />
                Analytics
              </Button>
            </CardContent>
          </Card>

          <!-- Security Badge -->
          <Card class="border-[#E2E8F0] bg-gradient-to-br from-[#35A7FF]/5 to-transparent">
            <CardContent class="p-5">
              <div class="flex items-center gap-3">
                <div class="p-2 rounded-lg bg-gradient-to-r from-[#35A7FF] to-[#38618C]">
                  <Shield class="w-5 h-5 text-white" />
                </div>
                <div>
                  <h4 class="font-semibold text-[#0F172A]">Secure Transactions</h4>
                  <p class="text-xs text-[#64748B] mt-1">
                    All transactions are encrypted and secured
                  </p>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>

    <!-- Sell Dialog -->
    <Dialog :open="showSellDialog" @update:open="closeSellDialog">
      <DialogContent class="max-w-md bg-white border border-[#FF5964]/20 shadow-xl rounded-xl">
        <DialogHeader class="pb-4 border-b border-[#E2E8F0]">
          <div class="flex items-center justify-between">
            <DialogTitle class="text-xl font-bold text-[#0F172A] flex items-center gap-2">
              <div class="p-2 rounded-lg bg-gradient-to-r from-[#FF5964] to-[#FF8B94]">
                <ArrowUpRight class="w-5 h-5 text-white" />
              </div>
              Sell {{ selectedAsset?.crypto?.symbol }}
            </DialogTitle>
            <Button
              variant="ghost"
              size="sm"
              class="h-8 w-8 p-0"
              @click="closeSellDialog"
            >
              <X class="w-4 h-4" />
            </Button>
          </div>
          <DialogDescription class="text-[#64748B]">
            Sell your {{ selectedAsset?.crypto?.name }} holdings
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <!-- Asset Info -->
          <div class="p-4 rounded-lg border border-[#E2E8F0] bg-gradient-to-br from-[#FF5964]/5 to-transparent">
            <div class="flex items-center justify-between mb-3">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-[#FF5964] to-[#FF8B94] flex items-center justify-center">
                  <span class="font-bold text-white">
                    {{ selectedAsset?.crypto?.symbol?.charAt(0) }}
                  </span>
                </div>
                <div>
                  <h4 class="font-semibold text-[#0F172A]">{{ selectedAsset?.crypto?.name }}</h4>
                  <p class="text-sm text-[#64748B]">{{ selectedAsset?.crypto?.symbol }}</p>
                </div>
              </div>
              <Badge class="bg-[#FF5964] text-white">
                Available: {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto?.symbol), 8) }}
              </Badge>
            </div>
            
            <div class="grid grid-cols-2 gap-4 text-sm">
              <div>
                <p class="text-[#64748B]">Current Price</p>
                <p class="font-semibold text-[#0F172A]">
                  {{ formatCurrency(selectedAsset?.crypto?.current_price || selectedAsset?.unitPrice || 0) }}
                </p>
              </div>
              <div>
                <p class="text-[#64748B]">Purchase Price</p>
                <p class="font-semibold text-[#0F172A]">
                  {{ formatCurrency(selectedAsset?.unitPrice || 0) }}
                </p>
              </div>
            </div>
          </div>

          <!-- Quantity Input -->
          <div class="space-y-3">
            <Label for="sellQuantity" class="text-sm font-medium text-[#0F172A]">
              Quantity to Sell
              <span class="text-[#64748B] font-normal ml-1">
                (Max: {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto?.symbol), 8) }})
              </span>
            </Label>
            
            <div class="relative">
              <Input
                id="sellQuantity"
                v-model="sellQuantity"
                type="number"
                step="0.00000001"
                :max="getAvailableQuantity(selectedAsset?.crypto?.symbol)"
                min="0.00000001"
                placeholder="0.00000000"
                class="pl-4 pr-24 py-3 text-lg font-medium border-[#FF5964]/30 focus:border-[#FF5964] focus:ring-[#FF5964]/20"
                :disabled="isSelling"
                @input="validateSellQuantity"
              />
              <div class="absolute right-2 top-1/2 -translate-y-1/2">
                <Badge class="bg-[#FF5964] text-white">
                  {{ selectedAsset?.crypto?.symbol }}
                </Badge>
              </div>
            </div>

            <!-- Quick Percentage Buttons -->
            <div class="grid grid-cols-4 gap-2">
              <Button
                v-for="percent in [25, 50, 75, 100]"
                :key="percent"
                variant="outline"
                size="sm"
                class="text-xs hover:bg-[#FF5964]/10 hover:border-[#FF5964]"
                :disabled="isSelling"
                @click="setSellPercentage(percent)"
              >
                {{ percent }}%
              </Button>
            </div>
          </div>

          <!-- Sell Summary -->
          <div class="p-4 rounded-lg border border-[#E2E8F0] space-y-3">
            <h5 class="font-semibold text-[#0F172A]">Sale Summary</h5>
            
            <div class="space-y-2">
              <div class="flex justify-between items-center">
                <span class="text-sm text-[#64748B]">Quantity</span>
                <span class="text-sm font-medium text-[#0F172A]">
                  {{ sellQuantity || '0.00000000' }} {{ selectedAsset?.crypto?.symbol }}
                </span>
              </div>
              
              <div class="flex justify-between items-center">
                <span class="text-sm text-[#64748B]">Current Price</span>
                <span class="text-sm font-medium text-[#0F172A]">
                  {{ formatCurrency(selectedAsset?.crypto?.current_price || selectedAsset?.unitPrice || 0) }}
                </span>
              </div>
              
              <div class="flex justify-between items-center pt-2 border-t border-[#E2E8F0]">
                <span class="text-sm font-semibold text-[#0F172A]">Total Amount</span>
                <span class="text-lg font-bold text-[#FF5964]">
                  {{ formatCurrency(calculateSellAmount()) }}
                </span>
              </div>

              <!-- Profit/Loss -->
              <div v-if="selectedAsset?.unitPrice && parseFloat(sellQuantity || 0) > 0" class="pt-2 border-t border-[#E2E8F0]">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-[#64748B]">Profit/Loss</span>
                  <span 
                    class="text-sm font-bold"
                    :class="calculateProfitLoss() >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                  >
                    {{ formatCurrency(calculateProfitLoss()) }}
                    <span class="ml-1">
                      ({{ calculateProfitLossPercentage() > 0 ? '+' : '' }}{{ calculateProfitLossPercentage() }}%)
                    </span>
                  </span>
                </div>
              </div>

              <!-- After Sale -->
              <div v-if="parseFloat(sellQuantity || 0) > 0" class="pt-2 border-t border-[#E2E8F0]">
                <div class="flex justify-between items-center">
                  <span class="text-sm text-[#64748B]">Remaining</span>
                  <span class="text-sm font-medium text-[#0F172A]">
                    {{ formatNumber(Math.max(0, getAvailableQuantity(selectedAsset?.crypto?.symbol) - parseFloat(sellQuantity || 0)), 8) }}
                    {{ selectedAsset?.crypto?.symbol }}
                  </span>
                </div>
                
                <Progress 
                  :value="((parseFloat(sellQuantity || 0) / getAvailableQuantity(selectedAsset?.crypto?.symbol)) * 100) || 0"
                  class="h-2 mt-2 bg-[#FF5964]/20 [&>div]:bg-gradient-to-r [&>div]:from-[#FF5964] [&>div]:to-[#FF8B94]"
                />
              </div>
            </div>
          </div>

          <!-- Alerts -->
          <Alert v-if="sellError" variant="destructive" class="border-[#FF5964] bg-[#FF5964]/10">
            <AlertCircle class="w-4 h-4 text-[#FF5964]" />
            <AlertDescription class="text-[#FF5964]">
              {{ sellError }}
            </AlertDescription>
          </Alert>

          <Alert v-if="sellSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
            <CheckCircle2 class="w-4 h-4 text-[#01FF19]" />
            <AlertDescription class="text-[#01FF19]">
              {{ sellSuccess }}
            </AlertDescription>
          </Alert>
        </div>

        <DialogFooter class="pt-4 border-t border-[#E2E8F0]">
          <Button
            variant="outline"
            class="flex-1"
            :disabled="isSelling"
            @click="closeSellDialog"
          >
            Cancel
          </Button>
          <Button
            class="flex-1 bg-gradient-to-r from-[#FF5964] to-[#FF8B94] text-white hover:opacity-90"
            :disabled="isSelling || !sellQuantity || parseFloat(sellQuantity) <= 0 || parseFloat(sellQuantity) > getAvailableQuantity(selectedAsset?.crypto?.symbol)"
            @click="confirmSell"
          >
            <Loader2 v-if="isSelling" class="w-4 h-4 mr-2 animate-spin" />
            {{ isSelling ? 'Processing...' : 'Confirm Sale' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>

<style scoped>
/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}

/* Animation for loading */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>