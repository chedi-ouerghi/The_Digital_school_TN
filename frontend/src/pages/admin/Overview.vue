<script setup lang="ts">
import { useRouter } from 'vue-router'
import ChartsSection from './_componentsOverview/ChartsSection.vue'
import { useAdminStats } from './_componentsOverview/composables/useAdminStats'
import StatsCards from './_componentsOverview/StatsCards.vue'

// Import des composants shadcn-vue
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'

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
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div class="space-y-1">
        <h1 class="text-2xl sm:text-3xl font-bold text-[#38618C] tracking-tight">Admin Dashboard</h1>
        <p class="text-sm sm:text-base text-gray-500 leading-relaxed">Platform overview and analytics</p>
      </div>
      <div class="flex flex-wrap items-center gap-2 sm:gap-3">
        <Button 
          :disabled="loading"
          variant="outline"
          class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white text-sm sm:text-base px-3 sm:px-4 h-9 sm:h-10"
          @click="refreshData"
        >
          🔄 Refresh
        </Button>
        <Button 
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold text-sm sm:text-base px-4 sm:px-6 h-9 sm:h-10"
          @click="goToClients"
        >
          👥 Clients
        </Button>
        <Button 
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold text-sm sm:text-base px-4 sm:px-6 h-9 sm:h-10"
          @click="goToCryptos"
        >
          💎 Cryptos
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <Card v-for="i in 4" :key="i">
          <CardContent class="p-4 sm:p-6">
            <div class="animate-pulse space-y-3">
              <div class="h-4 bg-gray-200 rounded w-1/2 mb-1"></div>
              <div class="h-7 bg-gray-200 rounded w-3/4"></div>
              <div class="h-3 bg-gray-200 rounded w-full mt-2"></div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-6 text-center">
        <div class="text-5xl mb-4">❌</div>
        <h3 class="text-xl font-semibold text-[#FF5964] mb-2">Loading Error</h3>
        <p class="text-gray-600 mb-6">{{ error }}</p>
        <Button 
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white px-6 h-10"
          @click="fetchStats"
        >
          Try Again
        </Button>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else>
      <!-- Statistics Cards -->
      <StatsCards 
        :card-stats="cardStats"
        :format-currency="formatCurrency"
        @go-to-clients="goToClients"
        @go-to-cryptos="goToCryptos"
      />

      <!-- Charts and Data -->
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
/* Améliorations de l'alignement */
.btn-group {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.card-content {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}

/* Responsive */
@media (max-width: 640px) {
  .header-actions {
    width: 100%;
    justify-content: flex-start;
  }
}
</style>