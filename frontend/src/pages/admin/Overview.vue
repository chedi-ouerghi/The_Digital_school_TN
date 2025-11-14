<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement, PointElement, LineElement } from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'

// Import des composants shadcn-vue
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement, PointElement, LineElement)

const router = useRouter()
const stats = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)
const cryptoDetails = ref<Map<number, any>>(new Map())
const recentTransactions = ref<any[]>([])

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatNumber(value: any, decimals = 2): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

async function fetchCryptoDetails(cryptoIds: number[]) {
  try {
    const promises = cryptoIds.map(id => api.crypto.show(id))
    const details = await Promise.all(promises)
    details.forEach((crypto, index) => {
      if (crypto) {
        cryptoDetails.value.set(cryptoIds[index], crypto)
      }
    })
  } catch (err) {
    console.error('Erreur lors du chargement des détails des cryptos:', err)
  }
}

async function fetchRecentTransactions() {
  try {
    const response = await api.admin.transactions.list({ limit: 5 })
    recentTransactions.value = (response?.data || []).slice(0, 5)
  } catch (err) {
    console.error('Erreur lors du chargement des transactions:', err)
  }
}

async function fetchStats() {
  loading.value = true
  error.value = null
  try {
    const data = await api.admin.stats.global()
    stats.value = data

    // Récupérer les détails des cryptos
    if (data.top_traded?.length) {
      const cryptoIds = data.top_traded.map((c: any) => c.cryptomoney_id)
      await fetchCryptoDetails(cryptoIds)
    }

    // Récupérer les transactions récentes
    await fetchRecentTransactions()
  } catch (err: any) {
    error.value = err.message || String(err)
    console.error('Erreur chargement stats:', err)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchStats()
})

// Statistiques principales
const cardStats = computed(() => ({
  totalClients: stats.value?.total_clients || 0,
  totalVolume: stats.value?.total_transaction_volume_eur || 0,
  totalRevenue: stats.value?.estimated_revenue_eur || 0,
  totalCryptos: stats.value?.top_traded?.length || 0,
}))

// Données pour le graphique en barres (Top cryptos tradées)
const barChartData = computed(() => {
  const topTraded = stats.value?.top_traded || []
  
  return {
    labels: topTraded.map((c: any) => {
      const crypto = cryptoDetails.value.get(c.cryptomoney_id)
      return (crypto?.symbol || c.symbole || 'UNKNOWN').toUpperCase()
    }),
    datasets: [{
      label: 'Quantité tradée',
      data: topTraded.map((c: any) => Number(c.total_quantity || 0)),
      backgroundColor: [
        'rgba(1, 255, 25, 0.8)',
        'rgba(53, 167, 255, 0.8)',
        'rgba(56, 97, 140, 0.8)',
        'rgba(255, 89, 100, 0.8)',
        'rgba(1, 255, 25, 0.5)',
      ],
      borderColor: [
        '#01FF19',
        '#35A7FF',
        '#38618C',
        '#FF5964',
        '#01FF19',
      ],
      borderWidth: 2
    }]
  }
})

const barChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#38618C',
      bodyColor: '#38618C',
      borderColor: '#38618C',
      borderWidth: 1,
    }
  },
  scales: {
    x: { 
      ticks: { color: '#38618C', font: { weight: 'bold' } },
      grid: { display: false }
    },
    y: { 
      ticks: { color: '#38618C' },
      grid: { color: '#E5E7EB' }
    }
  }
}

// Données pour le graphique circulaire (Répartition du volume)
const doughnutChartData = computed(() => {
  const topTraded = stats.value?.top_traded || []
  
  return {
    labels: topTraded.map((c: any) => {
      const crypto = cryptoDetails.value.get(c.cryptomoney_id)
      return (crypto?.symbol || c.symbole || 'UNKNOWN').toUpperCase()
    }),
    datasets: [{
      data: topTraded.map((c: any) => Number(c.total_quantity || 0)),
      backgroundColor: [
        'rgba(1, 255, 25, 0.8)',
        'rgba(53, 167, 255, 0.8)',
        'rgba(56, 97, 140, 0.8)',
        'rgba(255, 89, 100, 0.8)',
        'rgba(1, 255, 25, 0.5)',
      ],
      borderColor: [
        '#01FF19',
        '#35A7FF',
        '#38618C',
        '#FF5964',
        '#01FF19',
      ],
      borderWidth: 2
    }]
  }
})

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'right',
      labels: {
        color: '#38618C',
        font: { size: 12, weight: 'bold' },
        padding: 15,
      }
    },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#38618C',
      bodyColor: '#38618C',
      borderColor: '#38618C',
      borderWidth: 1,
    }
  }
}

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
function getRankColor(index: number): string {
  switch (index) {
    case 0:
      return 'bg-yellow-400'
    case 1:
      return 'bg-gray-400'
    case 2:
      return 'bg-yellow-700'
    default:
      return 'bg-gray-300 text-gray-700'
  }
}

</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#38618C] mb-1">Admin Dashboard</h1>
        <p class="text-sm sm:text-base text-gray-500">Platform overview and analytics</p>
      </div>
      <div class="flex flex-wrap gap-2 sm:gap-3">
        <Button 
          @click="refreshData"
          :disabled="loading"
          variant="outline"
          class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white text-sm sm:text-base"
        >
          🔄 Refresh
        </Button>
        <Button 
          @click="goToClients"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold text-sm sm:text-base"
        >
          👥 Clients
        </Button>
        <Button 
          @click="goToCryptos"
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold text-sm sm:text-base"
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
            <div class="animate-pulse">
              <div class="h-3 sm:h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
              <div class="h-6 sm:h-8 bg-gray-200 rounded w-3/4"></div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-6 sm:p-8 lg:p-12 text-center">
        <div class="text-4xl sm:text-6xl mb-4">❌</div>
        <h3 class="text-lg sm:text-xl font-semibold text-[#FF5964] mb-2">Loading Error</h3>
        <div class="text-sm sm:text-base text-gray-600 mb-4">{{ error }}</div>
        <Button 
          @click="fetchStats"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Try Again
        </Button>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer" @click="goToClients">
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

        <Card class="border-gray-200 hover:border-[#38618C] transition-all hover:shadow-lg cursor-pointer" @click="goToCryptos">
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

      <!-- Main Content Grid -->
      <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
        <!-- Left Column - 2/3 width -->
        <div class="xl:col-span-2 space-y-4 sm:space-y-6">
          <!-- Charts -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
            <!-- Bar Chart -->
            <Card>
              <CardHeader class="pb-3">
                <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
                  📊 Top Cryptos by Volume
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px] sm:h-[300px]">
                  <Bar :data="barChartData" :options="barChartOptions" />
                </div>
              </CardContent>
            </Card>

            <!-- Doughnut Chart -->
            <Card>
              <CardHeader class="pb-3">
                <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
                  🥧 Volume Distribution
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px] sm:h-[300px]">
                  <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Recent Transactions -->
          <Card>
            <CardHeader class="pb-3">
              <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
                <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
                  📋 Last 5 Transactions
                </CardTitle>
                <Button 
                  @click="goToTransactions"
                  variant="outline"
                  size="sm"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white text-xs sm:text-sm"
                >
                  View All →
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div v-if="recentTransactions.length === 0" class="text-center py-8 sm:py-12">
                <div class="text-4xl sm:text-6xl mb-4">📋</div>
                <p class="text-gray-500 text-sm sm:text-base">No recent transactions</p>
              </div>
              <div v-else class="space-y-3">
                <Card 
                  v-for="tx in recentTransactions" 
                  :key="tx.id"
                  class="border-gray-200 hover:border-[#35A7FF] transition-all"
                >
                  <CardContent class="p-3 sm:p-4">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                      <div class="flex items-center gap-3 sm:gap-4 flex-1">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                          <img 
                            v-if="tx.cryptomoney?.image_url"
                            :src="tx.cryptomoney.image_url"
                            :alt="tx.cryptomoney?.name"
                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                            @error="(e) => {
                              const target = e.target as HTMLImageElement
                              if (target && target.parentNode) {
                                target.style.display = 'none'
                              }
                            }"
                          />
                          <div v-if="!tx.cryptomoney?.image_url" class="text-sm sm:text-lg">💎</div>
                        </div>
                        <div class="flex-1 min-w-0">
                          <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1">
                            <Badge 
                              :class="tx.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                              class="text-white text-xs"
                            >
                              {{ tx.type === 'ACHAT' ? '📈 BUY' : '📉 SELL' }}
                            </Badge>
                            <span class="text-xs text-gray-500">Transaction #{{ tx.id.slice(0, 8) }}</span>
                          </div>
                          <div class="font-semibold text-[#38618C] text-sm sm:text-base truncate">
                            {{ tx.cryptomoney?.name || 'Crypto' }}
                          </div>
                          <div class="text-xs text-gray-500">
                            {{ String(tx.cryptomoney?.symbol || '').toUpperCase() }}
                          </div>
                        </div>
                      </div>

                      <div class="grid grid-cols-2 gap-4 sm:gap-6 text-right">
                        <div>
                          <div class="text-xs text-gray-500">Quantity</div>
                          <div class="text-sm sm:text-base font-bold text-[#38618C] font-mono">
                            {{ formatNumber(tx.quantity, 6) }}
                          </div>
                        </div>
                        <div>
                          <div class="text-xs text-gray-500">Amount</div>
                          <div class="text-base sm:text-lg font-bold text-[#35A7FF]">
                            {{ formatCurrency(tx.total_eur) }}
                          </div>
                        </div>
                      </div>

                      <div class="text-xs text-gray-500 text-center sm:text-right">
                        {{ new Date(tx.created_at).toLocaleDateString('en-US', { 
                          day: '2-digit', 
                          month: 'short',
                          hour: '2-digit',
                          minute: '2-digit'
                        }) }}
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Right Column - 1/3 width -->
        <div class="space-y-4 sm:space-y-6">
          <!-- Top 5 Most Traded Cryptos -->
          <Card class="xl:sticky xl:top-6">
            <CardHeader class="pb-3">
              <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
                <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
                  🏆 Top 5 Most Traded Cryptos
                </CardTitle>
                <Button 
                  @click="goToCryptos"
                  variant="outline"
                  size="sm"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white text-xs sm:text-sm"
                >
                  View All →
                </Button>
              </div>
            </CardHeader>
            <CardContent>
              <div v-if="!stats?.top_traded?.length" class="text-center py-6 sm:py-8">
                <div class="text-3xl sm:text-4xl mb-3">💎</div>
                <p class="text-gray-500 text-xs sm:text-sm">No trading data available</p>
              </div>
              <div v-else class="space-y-2 sm:space-y-3">
                <Card 
                  v-for="(crypto, index) in stats.top_traded.slice(0, 5)" 
                  :key="crypto.cryptomoney_id"
                  class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-md"
                >
                  <CardContent class="p-2 sm:p-3">
                    <div class="flex items-center gap-2 sm:gap-3">
                      <!-- Rank Badge -->
                      <div class="flex-shrink-0">
                        <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-white font-bold text-xs sm:text-sm"
                             :class="getRankColor(index)">
                          {{ index + 1 }}
                        </div>
                      </div>

                      <!-- Crypto Image -->
                      <div class="flex-shrink-0">
                        <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border border-gray-300 bg-gray-100 flex items-center justify-center">
                          <img 
                            v-if="cryptoDetails.get(crypto.cryptomoney_id)?.image_url"
                            :src="cryptoDetails.get(crypto.cryptomoney_id).image_url"
                            :alt="cryptoDetails.get(crypto.cryptomoney_id)?.name"
                            class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                            @error="(e) => e.target.style.display = 'none'"
                          />
                          <div v-if="!cryptoDetails.get(crypto.cryptomoney_id)?.image_url" class="text-xs sm:text-sm">💎</div>
                        </div>
                      </div>

                      <!-- Crypto Info -->
                      <div class="flex-1 min-w-0">
                        <div class="font-semibold text-[#38618C] text-xs sm:text-sm truncate">
                          {{ cryptoDetails.get(crypto.cryptomoney_id)?.name || 'Loading...' }}
                        </div>
                        <Badge class="bg-[#35A7FF] text-white text-xs font-mono mt-1">
                          {{ (cryptoDetails.get(crypto.cryptomoney_id)?.symbol || crypto.symbole || 'N/A').toUpperCase() }}
                        </Badge>
                      </div>

                      <!-- Trading Stats -->
                      <div class="text-right flex-shrink-0">
                        <div class="text-xs text-gray-500 mb-1">Traded Qty</div>
                        <div class="text-xs sm:text-sm font-bold text-[#01FF19] font-mono">
                          {{ formatNumber(crypto.total_quantity, 2) }}
                        </div>
                        <div class="text-xs text-gray-500 mt-1">
                          {{ formatCurrency(cryptoDetails.get(crypto.cryptomoney_id)?.price_eur || 0) }}
                        </div>
                      </div>
                    </div>
                  </CardContent>
                </Card>
              </div>

              <!-- Quick Stats Summary -->
              <div v-if="stats?.top_traded?.length" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200">
                <div class="grid grid-cols-2 gap-3 sm:gap-4 text-center">
                  <div>
                    <div class="text-xs text-gray-500">Total Traded</div>
                    <div class="text-sm font-bold text-[#38618C]">
                      {{ formatNumber(stats.top_traded.reduce((sum, crypto) => sum + crypto.total_quantity, 0), 2) }}
                    </div>
                  </div>
                  <div>
                    <div class="text-xs text-gray-500">Active Cryptos</div>
                    <div class="text-sm font-bold text-[#35A7FF]">
                      {{ stats.top_traded.length }}
                    </div>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Styles pour le sticky sidebar sur desktop */
@media (min-width: 1280px) {
  .xl\:sticky {
    position: sticky;
    top: 1.5rem;
    max-height: calc(100vh - 3rem);
    overflow-y: auto;
  }
}

/* Custom scrollbar pour le sticky sidebar */
.xl\:sticky::-webkit-scrollbar {
  width: 4px;
}

.xl\:sticky::-webkit-scrollbar-track {
  background: transparent;
}

.xl\:sticky::-webkit-scrollbar-thumb {
  background: rgba(156, 163, 175, 0.3);
  border-radius: 2px;
}

.xl\:sticky::-webkit-scrollbar-thumb:hover {
  background: rgba(156, 163, 175, 0.5);
}
</style>