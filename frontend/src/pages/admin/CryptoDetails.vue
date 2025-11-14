<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js'
import { Line } from 'vue-chartjs'

// Import des composants shadcn-vue
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

const route = useRoute()
const router = useRouter()
const crypto = ref<any>(null)
const history = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e9) return `${(n / 1e9).toFixed(2)}B €`
  if (n >= 1e6) return `${(n / 1e6).toFixed(2)}M €`
  if (n >= 1e3) return `${(n / 1e3).toFixed(2)}K €`
  return formatCurrency(n)
}

function formatPercentage(value: any): string {
  const n = Number(value ?? 0)
  return `${n >= 0 ? '+' : ''}${n.toFixed(2)}`
}

// Récupération des données
async function fetchData() {
  loading.value = true
  error.value = null
  
  try {
    const id = route.params.id
    
    // Récupérer les détails de la crypto
    const cryptoData = await api.crypto.show(id)
    crypto.value = cryptoData
    
    // Récupérer l'historique
    try {
      const historyData = await api.crypto.history(id)
      history.value = historyData.prices || []
    } catch (err) {
      console.warn('Historique non disponible:', err)
      history.value = []
    }
  } catch (err: any) {
    error.value = err.message || 'Erreur lors du chargement des données'
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchData()
})

// Calcul des variations
const get7DayChange = computed(() => {
  if (history.value.length < 7) return 0
  const now = history.value[history.value.length - 1]?.[1] || 0
  const before = history.value[history.value.length - 7]?.[1] || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

const get30DayChange = computed(() => {
  if (history.value.length < 2) return 0
  const now = history.value[history.value.length - 1]?.[1] || 0
  const before = history.value[0]?.[1] || now
  return before !== 0 ? ((now - before) / before) * 100 : 0
})

// Configuration du graphique
const chartData = computed(() => {
  if (!history.value?.length) return null

  const labels = history.value.map(p => 
    new Date(p[0]).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })
  )
  
  return {
    labels,
    datasets: [{
      label: 'Prix (EUR)',
      data: history.value.map(p => p[1]),
      borderColor: '#35A7FF',
      backgroundColor: 'rgba(53, 167, 255, 0.1)',
      borderWidth: 2,
      fill: true,
      tension: 0.4,
      pointBackgroundColor: '#01FF19',
      pointBorderColor: '#FFFFFF',
      pointBorderWidth: 2,
      pointRadius: 4,
      pointHoverRadius: 6,
      pointHitRadius: 10
    }]
  }
})

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    mode: 'nearest' as const,
    axis: 'x' as const,
    intersect: false
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#38618C',
      bodyColor: '#38618C',
      borderColor: '#38618C',
      borderWidth: 1,
      padding: 10,
      displayColors: false,
      callbacks: {
        label: (context: any) => `${formatCurrency(context.parsed.y)}`
      }
    }
  },
  scales: {
    x: {
      grid: { color: '#E5E7EB' },
      ticks: { 
        color: '#38618C',
        maxRotation: 45,
        minRotation: 45
      }
    },
    y: {
      grid: { color: '#E5E7EB' },
      ticks: { 
        color: '#38618C',
        callback: (value: number) => formatCurrency(value)
      }
    }
  }
}

function goBack() {
  router.push('/dashboard/admin/cryptos')
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button 
          @click="goBack"
          variant="outline"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
        >
          ← Retour
        </Button>
        <div v-if="crypto">
          <h1 class="text-3xl font-bold text-[#38618C]">{{ crypto.name }}</h1>
        </div>
      </div>
      <Button 
        @click="fetchData"
        :disabled="loading"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
      >
        🔄 Actualiser
      </Button>
    </div>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Chargement des données...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">❌</div>
        <h3 class="text-xl font-semibold text-[#FF5964] mb-2">Loading Error</h3>
        <div class="text-gray-600 mb-4">{{ error }}</div>
        <Button 
          @click="fetchData"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Retry
        </Button>
      </CardContent>
    </Card>

    <!-- Contenu principal -->
    <div v-else-if="crypto" class="space-y-6">
      <!-- Header avec image et infos -->
      <Card class="border-[#35A7FF]">
        <CardContent class="p-6">
          <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
            <div class="h-24 w-24 rounded-full border-4 border-[#35A7FF] bg-gray-100 flex items-center justify-center flex-shrink-0">
              <img 
                :src="crypto.image_url || crypto.image" 
                :alt="crypto.name"
                class="h-24 w-24 rounded-full object-cover"
                @error="(e) => {
                  const target = e.target as HTMLImageElement
                  if (target?.parentNode) {
                    target.style.display = 'none'
                  }
                }"
              />
              <div v-if="!crypto.image_url && !crypto.image" class="text-4xl">💎</div>
            </div>
            <div class="flex-1">
              <div class="flex items-center gap-3 mb-2">
                <h2 class="text-2xl font-bold text-[#38618C]">{{ crypto.name }}</h2>
                <Badge class="bg-[#38618C] text-white text-lg px-3 py-1">
                  {{ String(crypto.symbol || '').toUpperCase() }}
                </Badge>
                <Badge 
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white text-lg px-3 py-1"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}% (24h)
                </Badge>
              </div>
              <div class="text-4xl font-bold text-[#35A7FF]">
                {{ formatCurrency(crypto.price_eur) }}
              </div>
              <div class="text-sm text-gray-500 mt-1">Current Price</div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Statistiques -->
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Current Price</div>
            <div class="text-xl font-bold text-[#35A7FF]">
              {{ formatCurrency(crypto.price_eur) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">Market Cap</div>
            <div class="text-xl font-bold text-[#38618C]">
              {{ formatLargeNumber(crypto.market_cap) }}
            </div>
          </CardContent>
        </Card>

        <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">24h Volume</div>
            <div class="text-xl font-bold text-[#38618C]">
              {{ formatLargeNumber(crypto.volume_24h) }}
            </div>
          </CardContent>
        </Card>

        <Card 
          class="border-gray-200 transition-colors"
          :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'hover:border-[#01FF19]' : 'hover:border-[#FF5964]'"
        >
          <CardContent class="p-4">
            <div class="text-xs text-gray-500 mb-1">24h Change</div>
            <div 
              class="text-xl font-bold"
              :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
            >
              {{ formatPercentage(crypto.change_24h_pct) }}%
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Graphique d'évolution -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📈 Price Evolution (30 days)
            </CardTitle>
            <div class="flex gap-2">
              <Badge class="bg-[#35A7FF] text-white">30J</Badge>
            </div>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="!chartData" class="h-[400px] flex items-center justify-center">
            <div class="text-center text-gray-500">
              <div class="text-6xl mb-4">📊</div>
              <div>No historical data available</div>
            </div>
          </div>
          <div v-else class="h-[400px]">
            <Line :data="chartData" :options="chartOptions" />
          </div>
          
          <!-- Résumé des variations -->
          <div v-if="history.length" class="grid grid-cols-3 gap-4 mt-6">
            <Card class="border-gray-200">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">24h</div>
                <div 
                  class="text-lg font-bold"
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}%
                </div>
              </CardContent>
            </Card>
            
            <Card class="border-gray-200">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">7 jours</div>
                <div 
                  class="text-lg font-bold"
                  :class="get7DayChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(get7DayChange) }}%
                </div>
              </CardContent>
            </Card>
            
            <Card class="border-gray-200">
              <CardContent class="p-4 text-center">
                <div class="text-sm text-gray-500 mb-1">30 jours</div>
                <div 
                  class="text-lg font-bold"
                  :class="get30DayChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatPercentage(get30DayChange) }}%
                </div>
              </CardContent>
            </Card>
          </div>
        </CardContent>
      </Card>

      <!-- Informations détaillées -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Informations générales -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              ℹ️ Informations
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">Nom complet</span>
              <span class="font-semibold text-[#38618C]">{{ crypto.name }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Symbole</span>
              <Badge class="bg-[#35A7FF] text-white font-mono">
                {{ String(crypto.symbol || '').toUpperCase() }}
              </Badge>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Catégorie</span>
              <span class="font-semibold text-[#38618C]">{{ crypto.category || 'N/A' }}</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-gray-500">Site web</span>
              <a 
                v-if="crypto.website"
                :href="crypto.website" 
                target="_blank"
                class="text-[#35A7FF] hover:underline"
              >
                Visiter →
              </a>
              <span v-else class="text-gray-400">N/A</span>
            </div>
        </CardContent>
        </Card>

        <!-- Données de marché -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📊 Données de Marché
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-3">
            <div class="flex justify-between">
              <span class="text-gray-500">Prix actuel</span>
              <span class="font-bold text-[#35A7FF]">{{ formatCurrency(crypto.price_eur) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Market Cap</span>
              <span class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Volume 24h</span>
              <span class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Variation 24h</span>
              <Badge 
                :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                class="text-white"
              >
                {{ formatPercentage(crypto.change_24h_pct) }}%
              </Badge>
            </div>
            <div class="flex justify-between text-xs">
              <span class="text-gray-500">Dernière maj</span>
              <span class="text-gray-600">
                {{ crypto.updated_at ? new Date(crypto.updated_at).toLocaleDateString('fr-FR', {
                  year: 'numeric',
                  month: 'short',
                  day: 'numeric',
                  hour: '2-digit',
                  minute: '2-digit'
                }) : 'N/A' }}
              </span>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Alerte de mise à jour -->
      <!-- <Alert class="border-[#35A7FF] bg-[#35A7FF]/10">
        <AlertDescription class="text-[#38618C]">
          💡 Les données sont synchronisées avec CoinGecko toutes les 5 minutes.
        </AlertDescription>
      </Alert> -->
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

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}
</style>