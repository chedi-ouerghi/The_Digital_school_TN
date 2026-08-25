// components/TransactionSidebar.vue
<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { FileText, Shield, TrendingUp, Zap } from 'lucide-vue-next'
import { useRouter } from 'vue-router'

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
    <Card class="border-slate-200">
      <CardHeader>
        <CardTitle class="text-lg font-semibold text-slate-900">
          Transaction Summary
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-4">
        <div
          v-for="summary in transactionTypeSummary"
          :key="summary.type"
          class="flex items-center justify-between p-3 rounded-lg hover:bg-slate-50 transition-colors"
        >
          <div class="flex items-center gap-3">
            <div class="p-2 rounded-lg" :style="{ backgroundColor: summary.bgColor }">
              <component :is="summary.icon" :style="{ color: summary.color }" class="w-4 h-4" />
            </div>
            <div>
              <div class="font-medium text-slate-900">{{ summary.label }}</div>
              <div class="text-xs text-slate-500">{{ summary.count }} transactions</div>
            </div>
          </div>
          <div class="text-right">
            <div class="font-semibold text-slate-900">
              {{ formatCompactCurrency(summary.total) }}
            </div>
            <div class="text-xs text-slate-500">
              {{ summary.type === 'ACHAT' ? 'Invested' : 'Received' }}
            </div>
          </div>
        </div>
        
        <div class="pt-3 border-t border-slate-200">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-slate-900">Net Flow</span>
            <span 
              class="text-sm font-bold"
              :class="transactionStats.netFlow >= 0 ? 'text-brand-green' : 'text-brand-red'"
            >
              {{ transactionStats.netFlow >= 0 ? '+' : '' }}{{ formatCompactCurrency(transactionStats.netFlow) }}
            </span>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Quick Insights -->
    <Card class="border-slate-200">
      <CardHeader>
        <CardTitle class="text-lg font-semibold text-slate-900">
          Quick Insights
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-3">
        <div class="flex items-center justify-between">
          <span class="text-sm text-slate-500">Recent Activity</span>
          <Badge class="bg-brand-blue/10 text-brand-blue border-brand-blue/20">
            {{ transactionStats.recentActivity }} this week
          </Badge>
        </div>
        
        <div class="flex items-center justify-between">
          <span class="text-sm text-slate-500">Avg. Transaction</span>
          <span class="text-sm font-medium text-slate-900">
            {{ formatCompactCurrency(transactionStats.avgTransactionSize) }}
          </span>
        </div>
        
        <div class="flex items-center justify-between">
          <span class="text-sm text-slate-500">Total Volume</span>
          <span class="text-sm font-medium text-slate-900">
            {{ formatCompactCurrency(transactionStats.totalBuyAmount + transactionStats.totalSellAmount) }}
          </span>
        </div>
      </CardContent>
    </Card>

    <!-- Actions -->
    <Card class="border-slate-200">
      <CardHeader>
        <CardTitle class="text-lg font-semibold text-slate-900">
          Quick Actions
        </CardTitle>
      </CardHeader>
      <CardContent class="space-y-3">
        <Button
          class="w-full justify-start h-12 bg-gradient-to-r from-brand-blue to-brand-dark text-white hover:opacity-90"
          @click="router.push('/dashboard/cryptos')"
        >
          <Zap class="w-5 h-5 mr-3" />
          Trade Crypto
        </Button>
        <Button
          variant="outline"
          class="w-full justify-start h-12 border-slate-200 text-slate-900 hover:border-brand-green hover:bg-brand-green/5"
          @click="router.push('/dashboard/portfolio')"
        >
          <TrendingUp class="w-5 h-5 mr-3 text-brand-green" />
          View Portfolio
        </Button>
        <Button
          variant="outline"
          class="w-full justify-start h-12 border-slate-200 text-slate-900 hover:border-brand-red hover:bg-brand-red/5"
          @click="router.push('/dashboard/analytics')"
        >
          <FileText class="w-5 h-5 mr-3 text-brand-red" />
          Analytics
        </Button>
      </CardContent>
    </Card>

    <!-- Security Badge -->
    <Card class="border-slate-200 bg-gradient-to-br from-brand-blue/5 to-transparent">
      <CardContent class="p-5">
        <div class="flex items-center gap-3">
          <div class="p-2 rounded-lg bg-gradient-to-r from-brand-blue to-brand-dark">
            <Shield class="w-5 h-5 text-white" />
          </div>
          <div>
            <h4 class="font-semibold text-slate-900">Secure Transactions</h4>
            <p class="text-xs text-slate-500 mt-1">
              All transactions are encrypted and secured
            </p>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>
