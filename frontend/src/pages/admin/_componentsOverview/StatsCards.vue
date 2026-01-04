<script setup lang="ts">
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { TrendingUp, Users, Coins, DollarSign, ArrowUpRight } from 'lucide-vue-next'

interface Props {
  cardStats: {
    totalClients: number
    totalVolume: number
    totalRevenue: number
    totalCryptos: number
  }
  formatCurrency: (value: any) => string
}

defineProps<Props>()

defineEmits<{
  goToClients: []
  goToCryptos: []
}>()
</script>

<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- Total Clients Card -->
    <Card 
      class="group cursor-pointer border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-200"
      @click="$emit('goToClients')"
    >
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 group-hover:scale-105 transition-transform">
            <Users class="h-6 w-6 text-blue-600" />
          </div>
          <Badge class="bg-blue-50 text-blue-600 border-blue-200">
            <ArrowUpRight class="h-3 w-3 mr-1" />
            +12%
          </Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Total Clients</p>
          <div class="flex items-end justify-between">
            <h3 class="text-2xl font-bold text-gray-900">{{ cardStats.totalClients }}</h3>
            <span class="text-xs text-gray-400">Active</span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <p class="text-xs text-gray-500">Click to manage clients</p>
        </div>
      </CardContent>
    </Card>

    <!-- Transaction Volume Card -->
    <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-emerald-50 to-emerald-100">
            <DollarSign class="h-6 w-6 text-emerald-600" />
          </div>
          <Badge class="bg-emerald-50 text-emerald-600 border-emerald-200">
            <TrendingUp class="h-3 w-3 mr-1" />
            +24%
          </Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Transaction Volume</p>
          <h3 class="text-2xl font-bold text-emerald-600">
            {{ formatCurrency(cardStats.totalVolume) }}
          </h3>
          <p class="text-xs text-gray-500">Total trading volume across platform</p>
        </div>
      </CardContent>
    </Card>

    <!-- Revenue Card -->
    <Card class="border border-gray-200 hover:shadow-lg transition-all duration-200">
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100">
            <TrendingUp class="h-6 w-6 text-purple-600" />
          </div>
          <Badge class="bg-purple-50 text-purple-600 border-purple-200">
            <ArrowUpRight class="h-3 w-3 mr-1" />
            +18%
          </Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Total Revenue</p>
          <h3 class="text-2xl font-bold text-purple-600">
            {{ formatCurrency(cardStats.totalRevenue) }}
          </h3>
          <p class="text-xs text-gray-500">Platform revenue generated</p>
        </div>
      </CardContent>
    </Card>

    <!-- Cryptocurrencies Card -->
    <Card 
      class="group cursor-pointer border border-gray-200 hover:border-indigo-300 hover:shadow-lg transition-all duration-200"
      @click="$emit('goToCryptos')"
    >
      <CardContent class="p-6">
        <div class="flex items-start justify-between mb-4">
          <div class="p-3 rounded-xl bg-gradient-to-br from-indigo-50 to-indigo-100 group-hover:scale-105 transition-transform">
            <Coins class="h-6 w-6 text-indigo-600" />
          </div>
          <Badge class="bg-indigo-50 text-indigo-600 border-indigo-200">
            Active
          </Badge>
        </div>
        <div class="space-y-1">
          <p class="text-sm text-gray-500 font-medium">Traded Cryptos</p>
          <div class="flex items-end justify-between">
            <h3 class="text-2xl font-bold text-gray-900">{{ cardStats.totalCryptos }}</h3>
            <span class="text-xs text-gray-400">Digital Assets</span>
          </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-100">
          <p class="text-xs text-gray-500">Click to manage cryptocurrencies</p>
        </div>
      </CardContent>
    </Card>
  </div>
</template>