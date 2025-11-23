<script setup lang="ts">
import { computed } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

function fmtCurrency(n: number) {
  if (!isFinite(n) || isNaN(n)) return '0.00 €'
  return n.toLocaleString("en-IE", { style: "currency", currency: "EUR" })
}

interface Props {
  cryptos: any[]
}

const props = defineProps<Props>()

const totalCount = computed(() => props.cryptos?.length || 0)

const totalMarketCap = computed(() =>
  (props.cryptos || []).reduce((sum, c) => sum + Number(c.market_cap ?? c.market_cap_eur ?? 0), 0)
)

const avgPrice = computed(() => {
  const arr = props.cryptos || []
  if (!arr.length) return 0
  const sum = arr.reduce((s, c) => s + Number(c.price_eur ?? 0), 0)
  return sum / arr.length
})
</script>

<template>
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-4 w-full">

    <!-- Total Cryptos -->
    <Card class="border-gray-200 hover:border-[#38618C] transition-all hover:shadow-lg">
      <CardContent class="p-3 sm:p-5 lg:p-6">

        <!-- Header -->
        <div class="flex items-center justify-between mb-2 sm:mb-3 lg:mb-4">
          <div class="text-xl sm:text-3xl lg:text-4xl">💎</div>
          <Badge class="bg-[#38618C] text-white text-[10px] sm:text-xs px-2 sm:px-3">Count</Badge>
        </div>

        <!-- Label -->
        <div class="text-[10px] sm:text-xs lg:text-sm text-gray-500 mb-1">Total Cryptos</div>

        <!-- Value -->
        <div class="text-lg sm:text-2xl lg:text-3xl font-bold text-[#38618C] break-words">
          {{ totalCount }}
        </div>

      </CardContent>
    </Card>

    <!-- Total Market Cap -->
    <Card class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
      <CardContent class="p-3 sm:p-5 lg:p-6">

        <div class="flex items-center justify-between mb-2 sm:mb-3 lg:mb-4">
          <div class="text-xl sm:text-3xl lg:text-4xl">📊</div>
          <Badge class="bg-[#35A7FF] text-white text-[10px] sm:text-xs px-2 sm:px-3">EUR</Badge>
        </div>

        <div class="text-[10px] sm:text-xs lg:text-sm text-gray-500 mb-1">Total Market Cap</div>

        <div class="text-base sm:text-xl lg:text-3xl font-bold text-[#35A7FF] break-words">
          {{ fmtCurrency(totalMarketCap) }}
        </div>

      </CardContent>
    </Card>

    <!-- Average Price -->
    <Card class="border-gray-200 hover:border-[#01FF19] transition-all hover:shadow-lg bg-gradient-to-br from-[#01FF19]/10 to-transparent">
      <CardContent class="p-3 sm:p-5 lg:p-6">

        <div class="flex items-center justify-between mb-2 sm:mb-3 lg:mb-4">
          <div class="text-xl sm:text-3xl lg:text-4xl">💹</div>
          <Badge class="bg-[#01FF19] text-white text-[10px] sm:text-xs px-2 sm:px-3">Avg</Badge>
        </div>

        <div class="text-[10px] sm:text-xs lg:text-sm text-gray-500 mb-1">Average Price</div>

        <div class="text-base sm:text-xl lg:text-3xl font-bold text-[#01FF19] break-words">
          {{ fmtCurrency(avgPrice) }}
        </div>

      </CardContent>
    </Card>

  </div>
</template>

<style scoped>
/* minor responsive tweaks */
:deep(.text-\[#38618C\]) { color: #38618C; }
</style>

