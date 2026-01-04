<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js'
import { computed, onMounted, ref } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import {
  ArrowLeft,
  RefreshCw,
  TrendingUp,
  TrendingDown, PieChart, User,
  Coins,
  DollarSign,
  Package,
  Calendar, AlertCircle,
  ExternalLink,
  Clock,
  CheckCircle,
  XCircle,
  Activity,
  Target
} from 'lucide-vue-next'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const route = useRoute()
const router = useRouter()
const transaction = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// Utility functions
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Price evolution data (exemple simulé)
const priceEvolutionData = computed(() => ({
  labels: ['-24h', '-12h', '-6h', '-3h', '-1h', 'Transaction', '+1h', '+3h'],
  datasets: [{
    label: 'Price Evolution',
    data: [92000, 93500, 94500, 95000, 95300, 95471.83, 95600, 95800],
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    borderColor: '#3B82F6',
    borderWidth: 3,
    tension: 0.4,
    fill: true,
    pointBackgroundColor: '#3B82F6',
    pointBorderColor: '#FFFFFF',
    pointBorderWidth: 2,
    pointRadius: 6,
    pointHoverRadius: 8
  }]
}))

const priceEvolutionOptions = {
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
        label: (context: any) => `Price: ${formatCurrency(context.parsed.y)}`
      }
    }
  },
  scales: {
    x: { 
      ticks: { color: '#6B7280', font: { weight: '500' } },
      grid: { display: false }
    },
    y: { 
      ticks: { 
        color: '#6B7280',
        callback: (value: number) => formatCurrency(value)
      },
      grid: { color: '#F3F4F6' }
    }
  }
}

// Market comparison data
const marketComparisonData = computed(() => ({
  labels: ['Your Price', 'Market Avg', '24h Low', '24h High'],
  datasets: [{
    data: [
      transaction.value?.price || 0,
      transaction.value?.portefeuille?.cryptomoney?.price_eur || 0,
      (transaction.value?.portefeuille?.cryptomoney?.price_eur || 0) * 0.98,
      (transaction.value?.portefeuille?.cryptomoney?.price_eur || 0) * 1.02
    ],
    backgroundColor: [
      'rgba(59, 130, 246, 0.8)',
      'rgba(16, 185, 129, 0.8)',
      'rgba(239, 68, 68, 0.8)',
      'rgba(245, 158, 11, 0.8)'
    ],
    borderColor: ['#3B82F6', '#10B981', '#EF4444', '#F59E0B'],
    borderWidth: 2
  }]
}))

const marketComparisonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        color: '#374151',
        font: { size: 12, weight: '500' },
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
}

async function fetchTransaction() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id
    const res = await api.admin.transactions.show(id)
    const tx = res || {}

    // Normalize structure
    const user =
      tx.user ||
      tx.crypto_wallet_asset?.wallet?.user ||
      tx.portefeuille?.user ||
      tx.portefeuille?.owner ||
      tx.customer ||
      null

    const cryptomoney =
      tx.cryptomoney ||
      tx.crypto_wallet_asset?.cryptomoney ||
      tx.portefeuille?.cryptomoney ||
      tx.asset ||
      null

    transaction.value = {
      ...tx,
      user,
      cryptomoney,
      crypto_wallet_asset: tx.crypto_wallet_asset || tx.portefeuille?.crypto_wallet_asset || null,
      cancelled_at: tx.cancelled_at ?? tx.cancelAt ?? tx.cancelled?.at ?? null,
      cancel_reason: tx.cancel_reason ?? tx.cancelReason ?? tx.cancelled_reason ?? null,
      total_eur: tx.total_eur ?? tx.total ?? tx.amount ?? 0,
      price: tx.price ?? tx.unit_price ?? 0,
      quantity: tx.quantity ?? tx.qty ?? 0,
      created_at: tx.created_at ?? tx.createdAt ?? tx.date ?? null,
    }
  } catch (err: any) {
    error.value = err.message || 'Error loading transaction details'
    console.error('Error fetching transaction:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchTransaction)

// Computed values
const transactionStatus = computed(() => {
  if (transaction.value?.cancelled_at) {
    return {
      label: 'Cancelled',
      color: 'bg-red-100 text-red-700 border-red-200',
      icon: XCircle,
      iconColor: 'text-red-600'
    }
  }
  if (transaction.value?.completed_at) {
    return {
      label: 'Completed',
      color: 'bg-green-100 text-green-700 border-green-200',
      icon: CheckCircle,
      iconColor: 'text-green-600'
    }
  }
  return {
    label: 'Pending',
    color: 'bg-amber-100 text-amber-700 border-amber-200',
    icon: Clock,
    iconColor: 'text-amber-600'
  }
})

const priceDifference = computed(() => {
  const marketPrice = transaction.value?.portefeuille?.cryptomoney?.price_eur || 
                     transaction.value?.cryptomoney?.price_eur || 0
  const transactionPrice = transaction.value?.price || 0
  
  if (!marketPrice || !transactionPrice) return { value: 0, percentage: 0, isPositive: true }
  
  const difference = ((transactionPrice - marketPrice) / marketPrice) * 100
  return {
    value: transactionPrice - marketPrice,
    percentage: Math.abs(difference),
    isPositive: difference >= 0
  }
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <Button 
          variant="outline"
          class="border-gray-300 text-gray-700 hover:bg-gray-50 gap-2"
          @click="router.back()"
        >
          <ArrowLeft class="h-4 w-4" />
          Back to Transactions
        </Button>
        <div v-if="transaction">
          <h1 class="text-2xl font-bold text-gray-900">Transaction #{{ transaction.id }}</h1>
          <p class="text-sm text-gray-500">Detailed transaction analysis</p>
        </div>
      </div>
      <div class="flex gap-2">
        <Button 
          variant="outline"
          class="border-gray-300 text-gray-700 hover:bg-gray-50 gap-2"
          :disabled="loading"
          @click="fetchTransaction"
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
            <p class="text-lg font-medium text-gray-900">Loading Transaction</p>
            <p class="text-sm text-gray-500">Fetching transaction details...</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border border-red-200">
      <CardContent class="p-12 text-center">
        <div class="mx-auto w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mb-4">
          <AlertCircle class="h-8 w-8 text-red-600" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">Error Loading Transaction</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          {{ error }}
        </p>
        <div class="flex gap-3 justify-center">
          <Button 
            variant="outline"
            @click="router.back()"
          >
            Go Back
          </Button>
          <Button 
            class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white"
            @click="fetchTransaction"
          >
            <RefreshCw class="h-4 w-4 mr-2" />
            Try Again
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else-if="transaction" class="space-y-6">
      <!-- Transaction Overview -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Transaction Details -->
        <Card class="border border-gray-200">
          <CardContent class="p-6">
            <!-- Crypto Header -->
            <div class="flex flex-col items-center text-center mb-6">
              <div class="relative mb-4">
                <div class="w-20 h-20 rounded-full border-4 border-white shadow-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                  <div v-if="transaction.cryptomoney?.image_url || transaction.cryptomoney?.image">
                    <img 
                      :src="transaction.cryptomoney.image_url || transaction.cryptomoney.image"
                      :alt="transaction.cryptomoney.nom"
                      class="h-16 w-16 rounded-full object-cover"
                    />
                  </div>
                  <Coins v-else class="h-8 w-8 text-white" />
                </div>
                <Badge 
                  :class="[
                    'absolute -top-2 -right-2 font-bold',
                    transaction.type === 'ACHAT' 
                      ? 'bg-emerald-100 text-emerald-700 border-emerald-200' 
                      : 'bg-red-100 text-red-700 border-red-200'
                  ]"
                >
                  <component 
                    :is="transaction.type === 'ACHAT' ? TrendingUp : TrendingDown" 
                    class="h-3 w-3" 
                  />
                </Badge>
              </div>
              
              <h2 class="text-xl font-bold text-gray-900 mb-2">
                {{ transaction.cryptomoney?.name || 'Unknown Crypto' }}
              </h2>
              <div class="flex items-center gap-2">
                <Badge class="bg-gray-100 text-gray-700 border-gray-200 font-mono">
                  {{ String(transaction.cryptomoney?.symbol || transaction.cryptomoney?.symbole || 'N/A').toUpperCase() }}
                </Badge>
                <Badge :class="transactionStatus.color" class="font-medium">
                  <component :is="transactionStatus.icon" class="h-3 w-3 mr-1.5" />
                  {{ transactionStatus.label }}
                </Badge>
              </div>
            </div>

            <!-- Transaction Info -->
            <div class="space-y-4">
              <div>
                <div class="text-xs font-medium text-gray-500 mb-1">Transaction ID</div>
                <div class="font-mono text-sm text-gray-900 bg-gray-50 px-3 py-2 rounded border border-gray-200">
                  #{{ transaction.id }}
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-xs font-medium text-gray-500 mb-1">Date & Time</div>
                  <div class="text-sm text-gray-900 flex items-center gap-1">
                    <Calendar class="h-3 w-3 text-gray-400" />
                    {{ formatDate(transaction.created_at) }}
                  </div>
                </div>
                <div>
                  <div class="text-xs font-medium text-gray-500 mb-1">Type</div>
                  <Badge 
                    :class="[
                      'font-medium',
                      transaction.type === 'ACHAT' 
                        ? 'bg-emerald-100 text-emerald-700 border-emerald-200' 
                        : 'bg-red-100 text-red-700 border-red-200'
                    ]"
                  >
                    <component 
                      :is="transaction.type === 'ACHAT' ? TrendingUp : TrendingDown" 
                      class="h-3 w-3 mr-1.5" 
                    />
                    {{ transaction.type === 'ACHAT' ? 'BUY' : 'SELL' }}
                  </Badge>
                </div>
              </div>

              <!-- Price Details -->
              <div class="pt-4 border-t border-gray-100">
                <div class="text-xs font-medium text-gray-500 mb-1">Price Analysis</div>
                <div class="space-y-3">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Transaction Price</span>
                    <span class="font-bold text-blue-600">{{ formatCurrency(transaction.price) }}</span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Market Price</span>
                    <span class="font-medium text-gray-900">
                      {{ formatCurrency(transaction.cryptomoney?.price_eur || transaction.portefeuille?.cryptomoney?.price_eur || 0) }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">Price Difference</span>
                    <span 
                      :class="[
                        'font-bold',
                        priceDifference.isPositive ? 'text-emerald-600' : 'text-red-600'
                      ]"
                    >
                      {{ priceDifference.isPositive ? '+' : '-' }}{{ formatCurrency(Math.abs(priceDifference.value)) }}
                      ({{ priceDifference.percentage.toFixed(2) }}%)
                    </span>
                  </div>
                </div>
              </div>

              <!-- Amounts -->
              <div class="pt-4 border-t border-gray-100">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Quantity</div>
                    <div class="font-bold text-gray-900 flex items-center gap-1">
                      <Package class="h-4 w-4 text-gray-400" />
                      {{ formatNumber(transaction.quantity) }}
                      <span class="text-sm font-normal text-gray-500">
                        {{ String(transaction.cryptomoney?.symbol || '').toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div>
                    <div class="text-xs font-medium text-gray-500 mb-1">Total Amount</div>
                    <div class="text-2xl font-bold text-emerald-600">
                      {{ formatCurrency(Number(transaction.total_eur || 0)) }}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Right: Analytics -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Quick Stats -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Card class="border border-gray-200 hover:border-blue-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Unit Price</p>
                    <p class="text-xl font-bold text-gray-900 mt-1">{{ formatCurrency(transaction.price) }}</p>
                  </div>
                  <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                    <DollarSign class="h-5 w-5 text-blue-600" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="border border-gray-200 hover:border-emerald-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Quantity</p>
                    <p class="text-xl font-bold text-gray-900 mt-1 font-mono">
                      {{ formatNumber(transaction.quantity) }}
                    </p>
                  </div>
                  <div class="w-10 h-10 bg-emerald-100 rounded-full flex items-center justify-center">
                    <Package class="h-5 w-5 text-emerald-600" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="border border-gray-200 hover:border-amber-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">24h Change</p>
                    <p 
                      :class="[
                        'text-xl font-bold mt-1',
                        Number(transaction.cryptomoney?.change_24h_pct || 0) >= 0 
                          ? 'text-emerald-600' 
                          : 'text-red-600'
                      ]"
                    >
                      {{ Number(transaction.cryptomoney?.change_24h_pct || 0).toFixed(2) }}%
                    </p>
                  </div>
                  <div class="w-10 h-10 bg-amber-100 rounded-full flex items-center justify-center">
                    <Activity class="h-5 w-5 text-amber-600" />
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card class="border border-gray-200 hover:border-purple-300 transition-colors">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-xs font-medium text-gray-500">Market Cap</p>
                    <p class="text-sm font-bold text-gray-900 mt-1">
                      {{ formatCurrency(Number(transaction.cryptomoney?.market_cap || 0)) }}
                    </p>
                  </div>
                  <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center">
                    <Target class="h-5 w-5 text-purple-600" />
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Price Evolution Chart -->
            <Card class="border border-gray-200">
              <CardHeader class="pb-3">
                <CardTitle class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                  <TrendingUp class="h-4 w-4" />
                  Price Evolution Timeline
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Bar :data="priceEvolutionData" :options="priceEvolutionOptions" />
                </div>
              </CardContent>
            </Card>

            <!-- Market Comparison Chart -->
            <Card class="border border-gray-200">
              <CardHeader class="pb-3">
                <CardTitle class="text-sm font-semibold text-gray-900 flex items-center gap-2">
                  <PieChart class="h-4 w-4" />
                  Market Comparison
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Doughnut :data="marketComparisonData" :options="marketComparisonOptions" />
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Detailed Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Client Information -->
        <Card class="border border-gray-200">
          <CardHeader>
            <CardTitle class="text-sm font-semibold text-gray-900 flex items-center gap-2">
              <User class="h-4 w-4" />
              Client Information
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                  <span class="text-white font-bold text-lg">
                    {{ (transaction.user?.name || transaction.crypto_wallet_asset?.wallet?.user?.name || 'U').charAt(0).toUpperCase() }}
                  </span>
                </div>
                <div>
                  <div class="font-medium text-gray-900">
                    {{ transaction.user?.name || transaction.crypto_wallet_asset?.wallet?.user?.name || 'Unknown User' }}
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ transaction.user?.email || transaction.crypto_wallet_asset?.wallet?.user?.email || 'No email' }}
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                  <div class="text-xs font-medium text-gray-500 mb-1">Client Role</div>
                  <Badge 
                    :class="[
                      'font-medium',
                      (transaction.user?.role || transaction.crypto_wallet_asset?.wallet?.user?.role || '').toString().toLowerCase() === 'admin'
                        ? 'bg-purple-100 text-purple-700 border-purple-200'
                        : 'bg-blue-100 text-blue-700 border-blue-200'
                    ]"
                  >
                    {{ (transaction.user?.role || transaction.crypto_wallet_asset?.wallet?.user?.role || '').toString().toLowerCase() === 'admin' ? 'ADMIN' : 'CLIENT' }}
                  </Badge>
                </div>

                <div>
                  <div class="text-xs font-medium text-gray-500 mb-1">Member Since</div>
                  <div class="text-sm text-gray-900">
                    {{ formatDate(transaction.user?.created_at || transaction.crypto_wallet_asset?.wallet?.user?.created_at || '') }}
                  </div>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100">
                <div class="space-y-2">
                  <div class="text-xs font-medium text-gray-500">Wallet Information</div>
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <div class="text-xs text-gray-500">Wallet ID</div>
                      <div class="text-sm font-mono text-gray-900">
                        {{ transaction.crypto_wallet_asset?.wallet?.id?.slice(-8) || 'N/A' }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500">Current Balance</div>
                      <div class="text-sm font-medium text-emerald-600">
                        {{ formatCurrency(Number(transaction.crypto_wallet_asset?.wallet?.balance_eur || 0)) }}
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Crypto Information -->
        <Card class="border border-gray-200">
          <CardHeader>
            <CardTitle class="text-sm font-semibold text-gray-900 flex items-center gap-2">
              <Coins class="h-4 w-4" />
              Cryptocurrency Details
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-full border-2 border-gray-300 bg-gray-50 flex items-center justify-center">
                  <img
                    v-if="transaction.cryptomoney?.image_url || transaction.cryptomoney?.image"
                    :src="transaction.cryptomoney?.image_url || transaction.cryptomoney?.image"
                    :alt="transaction.cryptomoney?.name || 'crypto'"
                    class="h-10 w-10 rounded-full object-cover"
                  />
                  <Coins v-else class="h-6 w-6 text-gray-400" />
                </div>
                <div>
                  <div class="font-medium text-gray-900">{{ transaction.cryptomoney?.name || 'Unknown Cryptocurrency' }}</div>
                  <Badge class="bg-gray-100 text-gray-700 border-gray-200 font-mono">
                    {{ String(transaction.cryptomoney?.symbol || transaction.cryptomoney?.symbole || '').toUpperCase() }}
                  </Badge>
                </div>
              </div>

              <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
                <div>
                  <div class="text-xs font-medium text-gray-500">Current Price</div>
                  <div class="font-medium text-blue-600">
                    {{ formatCurrency(Number(transaction.cryptomoney?.price_eur || transaction.cryptomoney?.price || 0)) }}
                  </div>
                </div>


                <div>
                  <div class="text-xs font-medium text-gray-500">Avg. Buy Price</div>
                  <div class="font-medium text-gray-900">
                    {{ formatCurrency(Number(transaction.crypto_wallet_asset?.average_buy_price || 0)) }}
                  </div>
                </div>

                <div>
                  <div class="text-xs font-medium text-gray-500">Held Quantity</div>
                  <div class="font-mono text-sm text-gray-900">
                    {{ formatNumber(transaction.crypto_wallet_asset?.quantity || transaction.quantity || 0, 8) }}
                  </div>
                </div>
              </div>

              <div class="pt-4 border-t border-gray-100">
                <div class="text-xs font-medium text-gray-500 mb-2">Performance Metrics</div>
                <div class="space-y-2">
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">24h High</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ formatCurrency((transaction.cryptomoney?.price_eur || 0) * 1.02) }}
                    </span>
                  </div>
                  <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-600">24h Low</span>
                    <span class="text-sm font-medium text-gray-900">
                      {{ formatCurrency((transaction.cryptomoney?.price_eur || 0) * 0.98) }}
                    </span>
                  </div>
                </div>
              </div>

              <div v-if="transaction.cryptomoney?.website" class="pt-4 border-t border-gray-100">
                <a 
                  :href="transaction.cryptomoney.website" 
                  target="_blank"
                  class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 hover:underline text-sm"
                >
                  <ExternalLink class="h-4 w-4" />
                  View Official Website
                </a>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Cancellation Information -->
      <Card v-if="transaction.cancelled_at" class="border border-red-200">
        <CardHeader>
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
              <XCircle class="h-5 w-5 text-red-600" />
            </div>
            <div>
              <CardTitle class="text-sm font-semibold text-red-700">Cancellation Details</CardTitle>
              <p class="text-xs text-gray-500">This transaction was cancelled</p>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="text-xs font-medium text-gray-500 mb-1">Cancellation Date</div>
              <div class="font-medium text-red-700 flex items-center gap-2">
                <Calendar class="h-4 w-4" />
                {{ formatDate(transaction.cancelled_at) }}
              </div>
            </div>
            <div>
              <div class="text-xs font-medium text-gray-500 mb-1">Cancellation Reason</div>
              <div class="font-medium text-red-700">
                {{ transaction.cancel_reason || 'No reason provided' }}
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