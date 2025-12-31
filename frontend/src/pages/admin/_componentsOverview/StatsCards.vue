<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 pb-6">
    <Card 
      class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer" 
      @click="$emit('goToClients')"
    >
      <CardContent class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
          <div class="text-2xl sm:text-4xl">👥</div>
          <Badge class="bg-[#35A7FF] text-white text-xs">Total</Badge>
        </div>
        <div class="text-xs sm:text-sm text-gray-500 mb-1">Clients</div>
        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#38618C]">
          {{ cardStats.totalClients }}
        </div>
      </CardContent>
    </Card>

    <Card class="border-gray-200 hover:border-[#01FF19] transition-all hover:shadow-lg bg-gradient-to-br from-[#01FF19]/10 to-transparent">
      <CardContent class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
          <div class="text-2xl sm:text-4xl">💰</div>
          <Badge class="bg-[#01FF19] text-white text-xs">EUR</Badge>
        </div>
        <div class="text-xs sm:text-sm text-gray-500 mb-1">Total Transaction Volume</div>
        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#01FF19]">
          {{ formatCurrency(cardStats.totalVolume) }}
        </div>
      </CardContent>
    </Card>

    <Card class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
      <CardContent class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
          <div class="text-2xl sm:text-4xl">📈</div>
          <Badge class="bg-[#35A7FF] text-white text-xs">Revenue</Badge>
        </div>
        <div class="text-xs sm:text-sm text-gray-500 mb-1">Total Revenue</div>
        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#35A7FF]">
          {{ formatCurrency(cardStats.totalRevenue) }}
        </div>
      </CardContent>
    </Card>

    <Card 
      class="border-gray-200 hover:border-[#38618C] transition-all hover:shadow-lg cursor-pointer" 
      @click="$emit('goToCryptos')"
    >
      <CardContent class="p-4 sm:p-6">
        <div class="flex items-center justify-between mb-3 sm:mb-4">
          <div class="text-2xl sm:text-4xl">💎</div>
          <Badge class="bg-[#38618C] text-white text-xs">Active</Badge>
        </div>
        <div class="text-xs sm:text-sm text-gray-500 mb-1">Traded Cryptos</div>
        <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#38618C]">
          {{ cardStats.totalCryptos }}
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
  cardStats: {
    totalClients: number
    totalVolume: number
    totalRevenue: number
    totalCryptos: number
  }
  formatCurrency: (value: any) => string
}>()

defineEmits<{
  goToClients: []
  goToCryptos: []
}>()
</script>