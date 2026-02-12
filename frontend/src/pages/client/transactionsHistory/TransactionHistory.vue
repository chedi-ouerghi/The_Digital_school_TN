<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import { Skeleton } from '@/components/ui/skeleton'
import { Switch } from '@/components/ui/switch'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import {
    Download, RefreshCw
} from 'lucide-vue-next'
import { onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'

// Import composables and components
import SellDialog from './SellDialog.vue'
import TransactionFilters from './TransactionFilters.vue'
import TransactionSidebar from './TransactionSidebar.vue'
import TransactionsList from './TransactionsList.vue'
import { useTransactionLogic } from './useTransactionLogic'
import { useTransactionStats } from './useTransactionStats'

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

// Use composables
const {
  colors,
  formatCurrency,
  formatCompactCurrency,
  formatNumber,
  formatRelativeDate,
  formatFullDate,
  fetchWallet,
  loadTransactions,
  refreshData,
  validateSellQuantity,
  setSellPercentage,
  calculateSellAmount,
  calculateProfitLoss,
  calculateProfitLossPercentage,
  getAvailableQuantity,
  canSell,
  openSellDialog: openSell,
  closeSellDialog: closeSell,
  confirmSell: executeSell,
  getTransactionIcon,
  getTransactionColor,
  getTransactionBgColor,
  resetFilters,
  exportTransactions,
  navigateToPage
} = useTransactionLogic(
  wallet,
  transactions,
  loading,
  error,
  isRefreshing,
  filterType,
  dateRange,
  activeTab,
  searchQuery,
  currentPage,
  showSellDialog,
  selectedAsset,
  sellQuantity,
  sellError,
  sellSuccess,
  isSelling
)

const {
  portfolioStats,
  statsCards,
  transactionStats,
  filteredTransactions,
  paginatedTransactions,
  totalPages,
  transactionTypeSummary
} = useTransactionStats(
  wallet,
  transactions,
  activeTab,
  filterType,
  searchQuery,
  dateRange,
  currentPage,
  itemsPerPage,
  colors
)

// Lifecycle
onMounted(async () => {
  console.log('🚀 Component mounted, fetching initial data...')
  await refreshData()
})

// Watchers
watch(activeTab, (newTab, oldTab) => {
  console.log(`📑 Tab changed from "${oldTab}" to "${newTab}"`)
  currentPage.value = 1
})

watch([filterType, dateRange], (newValues) => {
  console.log(`🔍 Filters changed:`, { filterType: newValues[0], dateRange: newValues[1] })
  currentPage.value = 1
  loadTransactions()
})

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
              :style="{ background: `linear-gradient(135deg, ${stat.color}20, transparent)` }"
            />
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
          <!-- Filters Component -->
          <TransactionFilters
            v-model:search-query="searchQuery"
            v-model:filter-type="filterType"
            v-model:date-range="dateRange"
            v-model:show-advanced="showAdvancedFilters"
            :filtered-count="filteredTransactions.length"
            @reset="resetFilters"
          />

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

          <!-- Transactions List Component -->
          <TransactionsList
            :loading="loading"
            :error="error"
            :transactions="paginatedTransactions"
            :show-value-in-eur="showValueInEur"
            :current-page="currentPage"
            :total-pages="totalPages"
            :items-per-page="itemsPerPage"
            :filtered-count="filteredTransactions.length"
            :format-currency="formatCurrency"
            :format-number="formatNumber"
            :format-relative-date="formatRelativeDate"
            :format-full-date="formatFullDate"
            :get-transaction-icon="getTransactionIcon"
            :get-transaction-color="getTransactionColor"
            :get-transaction-bg-color="getTransactionBgColor"
            :can-sell="canSell"
            @update:items-per-page="itemsPerPage = $event"
            @navigate-page="navigateToPage"
            @open-sell="openSell"
            @reset-filters="resetFilters"
          />
        </div>

        <!-- Right Column - Stats & Insights -->
        <TransactionSidebar
          :transaction-type-summary="transactionTypeSummary"
          :transaction-stats="transactionStats"
          :format-compact-currency="formatCompactCurrency"
        />
      </div>
    </div>

    <!-- Sell Dialog Component -->
    <SellDialog
      :show="showSellDialog"
      :selected-asset="selectedAsset"
      :sell-quantity="sellQuantity"
      :sell-error="sellError"
      :sell-success="sellSuccess"
      :is-selling="isSelling"
      :format-currency="formatCurrency"
      :format-number="formatNumber"
      :get-available-quantity="getAvailableQuantity"
      :calculate-sell-amount="calculateSellAmount"
      :calculate-profit-loss="calculateProfitLoss"
      :calculate-profit-loss-percentage="calculateProfitLossPercentage"
      @update:quantity="sellQuantity = $event"
      @validate="validateSellQuantity"
      @set-percentage="setSellPercentage"
      @confirm="executeSell"
      @close="closeSell"
    />
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
