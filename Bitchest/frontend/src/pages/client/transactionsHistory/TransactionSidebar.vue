// components/TransactionSidebar.vue
<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { FileText, Shield, TrendingUp, Zap } from 'lucide-vue-next'
import { useRouter } from 'vue-router'
import type { formatCompactCurrency } from '.'

defineProps<{
  transactionTypeSummary: any[]
  transactionStats: any
  formatCompactCurrency: (value: any) => string
}>()

const router = useRouter()
</script>

<template>
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
</template>
