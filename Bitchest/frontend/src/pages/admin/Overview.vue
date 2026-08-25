<script setup lang="ts">
import { useRouter } from 'vue-router'
import {
  RefreshCw, Activity,
  Shield
} from 'lucide-vue-next'
import ChartsSection from './_componentsOverview/ChartsSection.vue'
import { useAdminStats } from './_componentsOverview/composables/useAdminStats'
import StatsCards from './_componentsOverview/StatsCards.vue'
import QuickActions from './_componentsOverview/QuickActions.vue'

// Import des composants shadcn-vue
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'

const router = useRouter()
const {
  stats,
  loading,
  error,
  cryptoDetails,
  recentTransactions,
  cardStats,
  barChartData,
  barChartOptions,
  doughnutChartData,
  doughnutChartOptions,
  formatCurrency,
  formatNumber,
  getRankColor,
  fetchStats,
} = useAdminStats()

function goToCryptos() {
  router.push('/dashboard/admin/cryptos')
}

function goToClients() {
  router.push('/dashboard/admin/clients')
}


function goToTransactions() {
  router.push('/dashboard/admin/transactions')
}

function refreshData() {
  fetchStats()
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
      <div class="space-y-2">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
            <Shield class="h-6 w-6 text-white" />
          </div>
          <div>
<h1 class="text-2xl md:text-3xl font-bold text-gray-900 tracking-tight">Admin Dashboard</h1>
<p class="text-sm md:text-base text-gray-600">Platform analytics & insights</p>
          </div>
        </div>
       
      </div>
      
      <div class="flex flex-wrap items-center gap-2">
        <Button 
          variant="outline"
          class="border-gray-300 hover:bg-gray-100 gap-2"
          :disabled="loading"
          @click="refreshData"
        >
          <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Refresh Data
        </Button>
      </div>
    </div>

  

    <!-- Main Content Area -->
    <div class="space-y-6">
      <!-- Stats Cards Row -->
      <StatsCards 
        :card-stats="cardStats"
        :format-currency="formatCurrency"
        @go-to-clients="goToClients"
        @go-to-cryptos="goToCryptos"
      />

      <!-- Quick Actions -->
      <QuickActions 
        @go-to-clients="goToClients"
        @go-to-cryptos="goToCryptos"
        @go-to-transactions="goToTransactions"
      />

      <!-- Charts Section -->
      <ChartsSection
        :stats="stats"
        :crypto-details="cryptoDetails"
        :recent-transactions="recentTransactions"
        :bar-chart-data="barChartData"
        :bar-chart-options="barChartOptions"
        :doughnut-chart-data="doughnutChartData"
        :doughnut-chart-options="doughnutChartOptions"
        :format-currency="formatCurrency"
        :format-number="formatNumber"
        :get-rank-color="getRankColor"
        @go-to-transactions="goToTransactions"
        @go-to-cryptos="goToCryptos"
      />

    
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

.glow {
  box-shadow: 0 0 20px rgba(59, 130, 246, 0.3);
}
</style>