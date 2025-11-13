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
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Dashboard Administrateur</h1>
        <p class="text-gray-500">Vue d'ensemble de la plateforme</p>
      </div>
      <div class="flex gap-3">
        <Button 
          @click="refreshData"
          :disabled="loading"
          variant="outline"
          class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white"
        >
          🔄 Actualiser
        </Button>
        <Button 
          @click="goToClients"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
        >
          👥 Clients
        </Button>
        <Button 
          @click="goToCryptos"
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
        >
          💎 Cryptos
        </Button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card v-for="i in 4" :key="i">
          <CardContent class="p-6">
            <div class="animate-pulse">
              <div class="h-4 bg-gray-200 rounded w-1/2 mb-2"></div>
              <div class="h-8 bg-gray-200 rounded w-3/4"></div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">❌</div>
        <h3 class="text-xl font-semibold text-[#FF5964] mb-2">Erreur de chargement</h3>
        <div class="text-gray-600 mb-4">{{ error }}</div>
        <Button 
          @click="fetchStats"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Réessayer
        </Button>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else class="space-y-6">
      <!-- Cartes de statistiques -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer" @click="goToClients">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="text-4xl">👥</div>
              <Badge class="bg-[#35A7FF] text-white">Total</Badge>
            </div>
            <div class="text-sm text-gray-500 mb-1">Clients</div>
            <div class="text-3xl font-bold text-[#38618C]">
              {{ cardStats.totalClients }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#01FF19] transition-all hover:shadow-lg bg-gradient-to-br from-[#01FF19]/10 to-transparent">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="text-4xl">💰</div>
              <Badge class="bg-[#01FF19] text-white">EUR</Badge>
            </div>
            <div class="text-sm text-gray-500 mb-1">Volume Total des transactions</div>
            <div class="text-3xl font-bold text-[#01FF19]">
              {{ formatCurrency(cardStats.totalVolume) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="text-4xl">📈</div>
              <Badge class="bg-[#35A7FF] text-white">Revenue</Badge>
            </div>
            <div class="text-sm text-gray-500 mb-1">Chiffre d'Affaires</div>
            <div class="text-3xl font-bold text-[#35A7FF]">
              {{ formatCurrency(cardStats.totalRevenue) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#38618C] transition-all hover:shadow-lg cursor-pointer" @click="goToCryptos">
          <CardContent class="p-6">
            <div class="flex items-center justify-between mb-4">
              <div class="text-4xl">💎</div>
              <Badge class="bg-[#38618C] text-white">Actives</Badge>
            </div>
            <div class="text-sm text-gray-500 mb-1">Cryptos Tradées</div>
            <div class="text-3xl font-bold text-[#38618C]">
              {{ cardStats.totalCryptos }}
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Graphiques -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Graphique en barres -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📊 Top Cryptos par Volume
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-[300px]">
              <Bar :data="barChartData" :options="barChartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Graphique circulaire -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              🥧 Répartition des Volumes
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-[300px]">
              <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Top 5 des cryptos les plus tradées -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              🏆 Top 5 Cryptos les Plus Tradées
            </CardTitle>
            <Button 
              @click="goToCryptos"
              variant="outline"
              size="sm"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
            >
              Voir tout →
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="!stats?.top_traded?.length" class="text-center py-12">
            <div class="text-6xl mb-4">💎</div>
            <p class="text-gray-500">Aucune donnée de trading disponible</p>
          </div>
          <div v-else class="space-y-3">
            <Card 
              v-for="(crypto, index) in stats.top_traded.slice(0, 5)" 
              :key="crypto.cryptomoney_id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all"
            >
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div class="flex items-center gap-4 flex-1">
                    <div class="text-2xl font-bold text-[#38618C] w-8">
                      #{{ index + 1 }}
                    </div>
                    <div class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img 
                        v-if="cryptoDetails.get(crypto.cryptomoney_id)?.image_url"
                        :src="cryptoDetails.get(crypto.cryptomoney_id).image_url"
                        :alt="cryptoDetails.get(crypto.cryptomoney_id)?.name"
                        class="w-12 h-12 rounded-full object-cover"
                        @error="(e) => e.target.style.display = 'none'"
                      />
                      <div v-if="!cryptoDetails.get(crypto.cryptomoney_id)?.image_url" class="text-lg">💎</div>
                    </div>
                    <div class="flex-1">
                      <div class="font-bold text-[#38618C] text-lg">
                        {{ cryptoDetails.get(crypto.cryptomoney_id)?.name || 'Chargement...' }}
                      </div>
                      <Badge class="bg-[#35A7FF] text-white font-mono">
                        {{ (cryptoDetails.get(crypto.cryptomoney_id)?.symbol || crypto.symbole || 'N/A').toUpperCase() }}
                      </Badge>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-6 text-right">
                    <div>
                      <div class="text-xs text-gray-500">Quantité Tradée</div>
                      <div class="text-xl font-bold text-[#01FF19] font-mono">
                        {{ formatNumber(crypto.total_quantity, 4) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500">Prix Actuel</div>
                      <div class="text-xl font-bold text-[#35A7FF]">
                        {{ formatCurrency(cryptoDetails.get(crypto.cryptomoney_id)?.price_eur || 0) }}
                      </div>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </CardContent>
      </Card>

      <!-- Dernières transactions -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📋 5 Dernières Transactions
            </CardTitle>
            <Button 
              @click="goToTransactions"
              variant="outline"
              size="sm"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
            >
              Voir tout →
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="recentTransactions.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">📋</div>
            <p class="text-gray-500">Aucune transaction récente</p>
          </div>
          <div v-else class="space-y-3">
            <Card 
              v-for="tx in recentTransactions" 
              :key="tx.id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all"
            >
              <CardContent class="p-4">
                <div class="flex items-center justify-between gap-4">
                  <div class="flex items-center gap-4 flex-1">
                    <div class="h-10 w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img 
                        v-if="tx.cryptomoney?.image_url"
                        :src="tx.cryptomoney.image_url"
                        :alt="tx.cryptomoney?.name"
                        class="w-10 h-10 rounded-full object-cover"
                        @error="(e) => {
                          const target = e.target as HTMLImageElement
                          if (target && target.parentNode) {
                            target.style.display = 'none'
                          }
                        }"
                      />
                      <div v-if="!tx.cryptomoney?.image_url" class="text-lg">💎</div>
                    </div>
                    <div>
                      <div class="flex items-center gap-2 mb-1">
                        <Badge 
                          :class="tx.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                          class="text-white"
                        >
                          {{ tx.type === 'ACHAT' ? '📈 ACHAT' : '📉 VENTE' }}
                        </Badge>
                        <span class="text-sm text-gray-500">Transaction #{{ tx.id.slice(0, 8) }}</span>
                      </div>
                      <div class="font-semibold text-[#38618C]">
                        {{ tx.cryptomoney?.name || 'Crypto' }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ String(tx.cryptomoney?.symbol || '').toUpperCase() }}
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-6 text-right">
                    <div>
                      <div class="text-xs text-gray-500">Quantité</div>
                      <div class="font-bold text-[#38618C] font-mono">
                        {{ formatNumber(tx.quantity, 6) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500">Montant</div>
                      <div class="text-lg font-bold text-[#35A7FF]">
                        {{ formatCurrency(tx.total_eur) }}
                      </div>
                    </div>
                  </div>

                  <div class="text-xs text-gray-500">
                    {{ new Date(tx.created_at).toLocaleDateString('fr-FR', { 
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

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}
</style>