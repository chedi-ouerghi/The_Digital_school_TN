<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import {
  Coins,
  TrendingUp,
  BarChart3,
  TrendingDown,
  DollarSign
} from 'lucide-vue-next'
import { computed } from 'vue'

// Formatter function
function fmtCurrency(n: number): string {
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(n)
}

// Props interface
interface Props {
  cryptos: any[]
}

const props = defineProps<Props>()

// Computed values
const totalCount = computed(() => props.cryptos?.length || 0)

const totalMarketCap = computed(() => {
  return (props.cryptos || []).reduce((sum: number, c: any) => {
    const marketCap = c.market_cap || c.market_cap_eur || 0
    return sum + Number(marketCap)
  }, 0)
})

const avgPrice = computed(() => {
  const arr = props.cryptos || []
  if (arr.length === 0) return 0
  const sum = arr.reduce((s: number, c: any) => {
    const price = c.price_eur || c.price || 0
    return s + Number(price)
  }, 0)
  return sum / arr.length
})

const avgChange24h = computed(() => {
  const arr = props.cryptos || []
  if (arr.length === 0) return 0
  const sum = arr.reduce((s: number, c: any) => {
    const change = c.change_24h_pct || c.change_24h || 0
    return s + Number(change)
  }, 0)
  return sum / arr.length
})

// Format large numbers
function formatLargeNumber(value: number): string {
  if (value >= 1e12) return `$${(value / 1e12).toFixed(2)}T`
  if (value >= 1e9) return `$${(value / 1e9).toFixed(2)}B`
  if (value >= 1e6) return `$${(value / 1e6).toFixed(2)}M`
  if (value >= 1e3) return `$${(value / 1e3).toFixed(2)}K`
  return fmtCurrency(value)
}
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Cryptos -->
    <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100">
            <Coins class="h-6 w-6 text-blue-600" />
          </div>
          <Badge class="bg-blue-50 text-blue-600 border-blue-200">Total</Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Total Cryptocurrencies</p>
          <h3 class="text-2xl font-bold text-gray-900">{{ totalCount }}</h3>
          <p class="text-xs text-gray-500">Digital assets listed</p>
        </div>
      </CardContent>
    </Card>

    <!-- Total Market Cap -->
    <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100">
            <BarChart3 class="h-6 w-6 text-emerald-600" />
          </div>
          <Badge class="bg-emerald-50 text-emerald-600 border-emerald-200">
            <TrendingUp class="h-3 w-3 mr-1" />
            +8.5%
          </Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Total Market Cap</p>
          <h3 class="text-2xl font-bold text-emerald-600">
            {{ formatLargeNumber(totalMarketCap) }}
          </h3>
          <p class="text-xs text-gray-500">Combined market value</p>
        </div>
      </CardContent>
    </Card>

    <!-- Average Price -->
    <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100">
            <DollarSign class="h-6 w-6 text-purple-600" />
          </div>
          <Badge class="bg-purple-50 text-purple-600 border-purple-200">Average</Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Average Price</p>
          <h3 class="text-2xl font-bold text-purple-600">
            {{ fmtCurrency(avgPrice) }}
          </h3>
          <p class="text-xs text-gray-500">Mean price across assets</p>
        </div>
      </CardContent>
    </Card>

    <!-- 24h Performance -->
    <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100">
            <TrendingUp class="h-6 w-6 text-amber-600" />
          </div>
          <Badge 
            :class="[
              'border',
              avgChange24h >= 0 ? 'bg-green-50 text-green-600 border-green-200' : 'bg-red-50 text-red-600 border-red-200'
            ]"
          >
            <component 
              :is="avgChange24h >= 0 ? TrendingUp : TrendingDown" 
              class="h-3 w-3 mr-1" 
            />
            {{ avgChange24h >= 0 ? '+' : '' }}{{ avgChange24h.toFixed(2) }}%
          </Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">24h Performance</p>
          <h3 class="text-2xl font-bold" :class="avgChange24h >= 0 ? 'text-green-600' : 'text-red-600'">
            {{ avgChange24h >= 0 ? '+' : '' }}{{ avgChange24h.toFixed(2) }}%
          </h3>
          <p class="text-xs text-gray-500">Average daily change</p>
        </div>
      </CardContent>
    </Card>
  </div>
</template>