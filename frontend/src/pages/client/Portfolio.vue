<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import type { Wallet } from '@/types'

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
    const response = await api.wallet.list() as Wallet[]

    // 🔹 Sécurité
    if (!response || response.length === 0) {
      throw new Error('Aucun wallet trouvé')
    }

    const walletData = response[0]

    // 🔹 Solde
    userBalance.value = Number(walletData.balance_eur ?? 0)

    // 🔹 Assets
    portfolio.value = (walletData.cryptoWalletAssets || [])
      .filter(asset => Number(asset.quantity || 0) > 0)
      .map(asset => {
        const crypto = asset.cryptomoney || {}
        const quantity = Number(asset.quantity || 0)
        const currentPrice = Number(crypto.price_eur || 0)
        const avgBuyPrice = Number(asset.average_buy_price || 0)

        const currentValue = quantity * currentPrice
        const profitAmount = quantity * (currentPrice - avgBuyPrice)
        const profitPercent =
          avgBuyPrice > 0
            ? (profitAmount / (quantity * avgBuyPrice)) * 100
            : 0

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
          profitAmount,
          profitPercent,
          asset,
          crypto,
        }
      })

    // 🔹 Totaux
    totalValue.value = portfolio.value.reduce((s, p) => s + p.currentValue, 0)
    totalProfit.value = portfolio.value.reduce((s, p) => s + p.profitAmount, 0)

  } catch (e: any) {
    error.value = e.message || String(e)
    console.error('Erreur portefeuille:', e)
  } finally {
    loading.value = false
  }
}


onMounted(() => {
  fetchPortfolioData()
})


// Distribution des actifs avec pourcentages
const portfolioDistribution = computed(() => {
  return portfolio.value.map(asset => ({
    ...asset,
    percentageOfPortfolio: totalValue.value > 0 ? (asset.currentValue / totalValue.value) * 100 : 0
  }))
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

function goToTransactionHistory() {
  router.push('/dashboard/history')
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
          :disabled="loading"
          class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white"
          @click="refreshData"
        >
          🔄 Refresh
        </Button>
        <Button 
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
          @click="goToBuy"
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
          <div class="text-sm text-gray-500 mb-1">Invested Amount</div>
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

    <!-- Distribution Details Section -->
    <Card v-if="portfolio.length > 0" class="border-gray-200 bg-gradient-to-br from-indigo-50 to-purple-50">
      <CardContent class="p-6">
        <div class="flex items-center gap-2 mb-6">
          <span class="text-2xl">📊</span>
          <h2 class="text-xl font-bold text-[#38618C]">Portfolio Distribution</h2>
        </div>
        
        <!-- Responsive table -->
        <div class="overflow-x-auto">
          <table class="w-full">
            <thead>
              <tr class="border-b-2 border-indigo-200">
                <th class="text-left py-3 px-4 font-semibold text-[#38618C]">Asset</th>
                <th class="text-right py-3 px-4 font-semibold text-[#38618C]">Quantity</th>
                <th class="text-right py-3 px-4 font-semibold text-[#38618C]">Current Price</th>
                <th class="text-right py-3 px-4 font-semibold text-[#38618C]">Value</th>
                <th class="text-right py-3 px-4 font-semibold text-[#38618C]">Portfolio %</th>
              </tr>
            </thead>
            <tbody>
              <tr 
                v-for="asset in portfolioDistribution" 
                :key="asset.assetId"
                class="border-b border-gray-200 hover:bg-white transition-colors cursor-pointer"
                @click="goDetails(asset)"
              >
                <td class="py-4 px-4">
                  <div class="flex items-center gap-3">
                    <div class="h-8 w-8 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img 
                        v-if="asset.image_url"
                        :src="makeImageUrl(asset.image_url)" 
                        :alt="asset.name"
                        class="h-8 w-8 rounded-full object-cover"
                        @error="(e) => e.target.style.display = 'none'"
                      />
                      <div v-if="!asset.image_url" class="text-sm">💎</div>
                    </div>
                    <div>
                      <div class="font-semibold text-[#38618C]">{{ asset.name }}</div>
                      <div class="text-xs text-gray-500">{{ String(asset.symbol || '').toUpperCase() }}</div>
                    </div>
                  </div>
                </td>
                <td class="text-right py-4 px-4 font-mono text-sm">{{ formatNumber(asset.quantity, 8) }}</td>
                <td class="text-right py-4 px-4 font-semibold text-[#38618C]">{{ formatCurrency(asset.currentPrice) }}</td>
                <td class="text-right py-4 px-4 font-bold text-[#35A7FF]">{{ formatCurrency(asset.currentValue) }}</td>
                <td class="text-right py-4 px-4">
                  <Badge 
                    class="bg-indigo-100 text-indigo-900 font-semibold"
                  >
                    {{ asset.percentageOfPortfolio.toFixed(1) }}%
                  </Badge>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </CardContent>
    </Card>

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
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
          @click="goToBuy"
        >
          🚀 Start Investing
        </Button>
      </CardContent>
    </Card>
    
    <!-- Transaction History Button -->
    <div v-if="portfolio.length > 0" class="flex justify-center">
      <Button 
        class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold px-8 py-3 transition-all hover:shadow-lg hover:scale-105"
        @click="goToTransactionHistory"
      >
        📋 View Complete Transaction History
      </Button>
    </div>

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
                <div class="text-xs text-gray-500">Current Value ss</div>
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