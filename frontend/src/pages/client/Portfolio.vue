<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const router = useRouter()
const portfolio = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const sortBy = ref<'name'|'value'|'profit'|'quantity'>('value')
const filterBy = ref<'all'|'profit'|'loss'>('all')
const searchQuery = ref('')

// Données calculées
const totalValue = ref(0)
const totalProfit = ref(0)
const userBalance = ref(0)

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

const formatNumber = (num, decimals = 2) => {
  if (num === null || num === undefined) return '0'
  return parseFloat(num).toLocaleString('fr-FR', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  })
}

const makeImageUrl = (imagePath) => {
  if (!imagePath) return ''
  
  // If it's already a full URL, return as is
  if (imagePath.startsWith('http')) {
    return imagePath
  }
  
  // If it's a relative path, construct full URL
  return `${import.meta.env.VITE_API_URL}/storage/${imagePath}`
}

async function fetchPortfolioData() {
  loading.value = true
  error.value = null
  try {
    // Récupération des données du portefeuille
    const response = await api.wallet.list()
    const walletData = response?.wallet || {}
    
    // Mettre à jour le solde depuis le portefeuille
    userBalance.value = Number(walletData.balance_eur ?? response?.solde_eur ?? 0)
    
    // Mapper les crypto_wallet_assets correctement
    portfolio.value = (walletData.crypto_wallet_assets || [])
      .filter((asset: any) => Number(asset.quantity || 0) > 0)
      .map((asset: any) => {
        const crypto = asset.cryptomoney || {}
        const quantity = Number(asset.quantity || 0)
        const currentPrice = Number(crypto.price_eur || 0)
        const avgBuyPrice = Number(asset.average_buy_price || 0)
        const currentValue = quantity * currentPrice
        const plusValue = quantity * (currentPrice - avgBuyPrice)
        const plusValuePercent = avgBuyPrice > 0 ? (plusValue / (quantity * avgBuyPrice)) * 100 : 0

        return {
          assetId: asset.id,
          cryptoId: crypto.id,
          name: crypto.name || '',
          symbol: crypto.symbol || '',
          image_url: crypto.image_url || '',
          quantity,
          currentPrice,
          currentValue,
          avgBuyPrice,
          profitAmount: plusValue,
          profitPercent: plusValuePercent,
          // Keep full objects for reference
          asset,
          crypto,
        }
      })

    // Calculer les totaux
    totalValue.value = portfolio.value.reduce((sum, p) => sum + p.currentValue, 0)
    totalProfit.value = portfolio.value.reduce((sum, p) => sum + p.profitAmount, 0)

  } catch (e: any) {
    error.value = e.message || String(e)
    console.error('Erreur lors du chargement du portefeuille:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchPortfolioData()
})

// Profit percent total
const totalProfitPercent = computed(() => {
  const invested = totalValue.value - totalProfit.value
  return invested > 0 ? (totalProfit.value / invested) * 100 : 0
})

// Filtrage et tri
const filteredAndSortedPortfolio = computed(() => {
  let filtered = portfolio.value.filter(p => {
    const search = searchQuery.value.toLowerCase()
    
    const matchesSearch = !search || 
      p.name.toLowerCase().includes(search) || 
      p.symbol.toLowerCase().includes(search)
    
    const matchesFilter = filterBy.value === 'all' ||
      (filterBy.value === 'profit' && p.profitAmount >= 0) ||
      (filterBy.value === 'loss' && p.profitAmount < 0)
    
    return matchesSearch && matchesFilter
  })

  // Tri
  return filtered.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return a.name.localeCompare(b.name)
      case 'quantity':
        return b.quantity - a.quantity
      case 'profit':
        return b.profitAmount - a.profitAmount
      case 'value':
      default:
        return b.currentValue - a.currentValue
    }
  })
})

function goDetails(p: any) {
  if (!p?.cryptoId) return
  router.push(`/dashboard/portfolio/crypto/${p.cryptoId}`)
}
function goToBuy() {
  router.push('/dashboard/cryptos')
}

function refreshData() {
  fetchPortfolioData()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header with balance -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">My Portfolio</h1>
        <p class="text-gray-500">Manage your cryptocurrency investments</p>
      </div>
      <div class="flex gap-3">
        <Button 
          variant="outline" 
          @click="refreshData"
          :disabled="loading"
          class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white"
        >
          🔄 Refresh
        </Button>
        <Button 
          @click="goToBuy"
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
        >
          + Buy Cryptos
        </Button>
      </div>
    </div>

    <!-- Main statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <!-- Available balance -->
      <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-6">
          <div class="text-sm text-gray-500 mb-1">Available Balance</div>
          <div class="text-3xl font-bold text-[#01FF19]">
            {{ formatCurrency(userBalance) }}
          </div>
          <div class="text-xs text-gray-500 mt-2">Available for investment</div>
        </CardContent>
      </Card>

      <!-- Total portfolio value -->
      <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
        <CardContent class="p-6">
          <div class="text-sm text-gray-500 mb-1">Portfolio Value</div>
          <div class="text-3xl font-bold text-[#35A7FF]">
            {{ formatCurrency(totalValue) }}
          </div>
          <div class="text-xs text-gray-500 mt-2">{{ portfolio.length }} asset{{ portfolio.length > 1 ? 's' : '' }}</div>
        </CardContent>
      </Card>

      <!-- Total profit/loss -->
      <Card 
        class="border-gray-200 transition-colors"
        :class="totalProfit >= 0 ? 'hover:border-[#01FF19] bg-gradient-to-br from-[#01FF19]/10 to-transparent' : 'hover:border-[#FF5964] bg-gradient-to-br from-[#FF5964]/10 to-transparent'"
      >
        <CardContent class="p-6">
          <div class="text-sm text-gray-500 mb-1">Total Profit/Loss</div>
          <div 
            class="text-3xl font-bold"
            :class="totalProfit >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
          >
            23.578 €
          </div>
          <Badge 
            :class="totalProfit >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
            class="text-white mt-2"
          >
            23.45%
          </Badge>
        </CardContent>
      </Card>
    </div>

    <!-- Filters and search -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
          <!-- Search -->
          <div class="relative flex-1 max-w-md">
            <Input
              v-model="searchQuery"
              placeholder="🔍 Search a crypto..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <!-- Filter -->
          <Select v-model="filterBy">
            <SelectTrigger class="w-full sm:w-40 border-[#38618C]">
              <SelectValue placeholder="Filter by" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="profit">In Profit 📈</SelectItem>
              <SelectItem value="loss">In Loss 📉</SelectItem>
            </SelectContent>
          </Select>

          <!-- Sort -->
          <Select v-model="sortBy">
            <SelectTrigger class="w-full sm:w-40 border-[#38618C]">
              <SelectValue placeholder="Sort by" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="value">Value</SelectItem>
              <SelectItem value="name">Name</SelectItem>
              <SelectItem value="quantity">Quantity</SelectItem>
              <SelectItem value="profit">Performance</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-8 text-center text-gray-600">
        <div class="animate-pulse">⏳ Loading your portfolio...</div>
      </CardContent>
    </Card>
    
    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-8 text-center text-[#FF5964]">
        <div class="text-2xl mb-2">❌</div>
        <div>{{ error }}</div>
      </CardContent>
    </Card>
    
    <!-- Empty State -->
    <Card v-else-if="filteredAndSortedPortfolio.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">💼</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">
          {{ searchQuery || filterBy !== 'all' ? 'No results' : 'Your portfolio is empty' }}
        </h3>
        <p class="text-gray-500 mb-6">
          {{ searchQuery || filterBy !== 'all' 
            ? 'Try changing your search filters' 
            : 'Start investing in cryptocurrencies' 
          }}
        </p>
        <Button 
          v-if="!searchQuery && filterBy === 'all'"
          @click="goToBuy"
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
        >
          🚀 Start Investing
        </Button>
      </CardContent>
    </Card>
    
    <!-- Portfolio list -->
    <div v-else class="grid grid-cols-1 gap-4">
      <Card 
        v-for="p in filteredAndSortedPortfolio" 
        :key="p.assetId"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer"
        @click="goDetails(p)"
      >
        <CardContent class="p-6">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <!-- Crypto info -->
            <div class="flex items-center gap-4 flex-1">
              <div class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img 
                  v-if="p.image_url"
                  :src="makeImageUrl(p.image_url)" 
                  :alt="p.name"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="(e) => e.target.style.display = 'none'"
                />
                <div v-if="!p.image_url" class="text-lg">💎</div>
              </div>
              <div>
                <div class="text-lg font-bold text-[#38618C]">
                  {{ p.name }}
                </div>
                <div class="text-sm text-gray-500 font-mono">
                  {{ String(p.symbol || '').toUpperCase() }} • {{ formatNumber(p.quantity, 8) }}
                </div>
              </div>
            </div>

            <!-- Prices and values -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">
              <div>
                <div class="text-xs text-gray-500">Avg. Buy Price</div>
                <div class="text-sm font-semibold text-[#38618C]">
                  {{ formatCurrency(p.avgBuyPrice) }}
                </div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Current Price</div>
                <div class="text-sm font-semibold text-[#38618C]">
                  {{ formatCurrency(p.currentPrice) }}
                </div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Current Value</div>
                <div class="text-sm font-bold text-[#35A7FF]">
                  {{ formatCurrency(p.currentValue) }}
                </div>
              </div>
              <div>
                <div class="text-xs text-gray-500">Profit/Loss</div>
                <div 
                  class="text-sm font-bold"
                  :class="p.profitAmount >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ p.profitAmount >= 0 ? '+' : '' }}{{ formatCurrency(p.profitAmount) }}
                </div>
                <Badge 
                  :class="p.profitAmount >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white text-xs mt-1"
                >
                  {{ p.profitPercent >= 0 ? '+' : '' }}{{ p.profitPercent.toFixed(2) }}%
                </Badge>
              </div>
            </div>

            <!-- Details button -->
            <Button 
              variant="outline"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
              @click.stop="goDetails(p)"
            >
              View Details →
            </Button>
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

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:bg-\[#35A7FF\]:hover) {
  background-color: #35A7FF;
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}
</style>