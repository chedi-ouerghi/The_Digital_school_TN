<script setup lang="ts">
import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js'
import { computed, onMounted, ref } from 'vue'
import { Bar, Doughnut } from 'vue-chartjs'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const route = useRoute()
const router = useRouter()
const client = ref<any>(null)
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | undefined>(undefined)

// Pagination
const currentPage = ref(1)
const itemsPerPage = 4

// Utility functions
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Add helper to build a usable image URL when backend returns a relative path
function makeImageUrl(path: string | undefined | null): string | undefined {
  if (!path) return undefined
  const p = String(path)
  if (p.startsWith('http://') || p.startsWith('https://')) return p

  // Ton backend sert probablement les fichiers sous /storage/
  const baseUrl = 'http://localhost:8000'
  const cleanPath = p.startsWith('/storage/') ? p : `/storage/${p}`
  return `${baseUrl}${cleanPath}`
}


// Fetch data
async function fetchClientDetails() {
  const id = Array.isArray(route.params.id) ? route.params.id[0] : route.params.id
  if (!id) return

  loading.value = true
  error.value = undefined
  
  try {
    // Fetch single endpoint which contains client + transactions + positions
    const clientData = await api.admin.clients.show(id)

    client.value = clientData
    // Build a quick map of positions by symbol to enrich transactions
    const positions = (clientData as any).positions || []
    const posMap: Record<string, any> = {}
    positions.forEach((p: any) => {
      const sym = String(p.symbol || p.symbole || '').toLowerCase()
      // compute a safe image_url for position (backend may return relative path "cryptos/..")
      const imageUrl = makeImageUrl(p.image || p.image_url)
      if (sym) posMap[sym] = {
        ...p,
        image: imageUrl || '',         // normalized image field
        image_url: imageUrl || ''     // keep both keys for compatibility
      }
    })

    const rawTxs = (clientData as any).transactions || []

    // Normalize transactions to expected shape used in template (wallet.cryptomoney.*)
    transactions.value = (rawTxs || []).map((tx: any) => {
      const txSym = String(tx.crypto || tx.cryptomoney || tx.cryptomoney?.symbol || '').toLowerCase()
      const pos = posMap[txSym]

      // If transaction already has cryptomoney object with image path, normalize it too
      const txCryptoImage = makeImageUrl(tx.cryptomoney?.image || tx.cryptomoney?.image_url)

      return {
        ...tx,
        // attach wallet.cryptomoney to keep existing template logic working
        wallet: {
          cryptomoney: {
            nom: pos?.name || pos?.nom || (tx.cryptomoney?.name || tx.crypto?.toUpperCase() || 'Unknown'),
            symbole: pos?.symbol || pos?.symbole || (tx.cryptomoney?.symbol || tx.crypto || '').toLowerCase(),
            image: pos?.image || txCryptoImage || ''
          }
        },
        // also expose cryptomoney normalized for other template branches
        cryptomoney: {
          ...(tx.cryptomoney || {}),
          image: txCryptoImage || pos?.image || tx.cryptomoney?.image || tx.cryptomoney?.image_url || ''
        }
      }
    }).sort((a: any, b: any) => new Date(b.created_at).getTime() - new Date(a.created_at).getTime())

  } catch (err: any) {
    error.value = err.message || 'Error loading client details'
    console.error('Error fetching client details:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchClientDetails)

// Computed - Statistics
const stats = computed(() => {
  let totalTransactions = 0
  let totalBuy = 0
  let totalSell = 0
  let cancelled = 0

  transactions.value.forEach(t => {
    totalTransactions += 1

    const isCancelled = t.status?.toLowerCase() === 'annulée' || t.cancelled_at
    if (isCancelled) {
      cancelled += 1
      return 
    }

    if (t.type === 'ACHAT') {
      totalBuy += Number(t.total_eur || 0)
    } else if (t.type === 'VENTE') {
      totalSell += Number(t.total_eur || 0)
    }
  })

  return {
    totalTransactions,
    totalBuy,
    totalSell,
    cancelled,
    netAmount: totalBuy - totalSell
  }
})

// Computed - Crypto breakdown
const cryptoBreakdown = computed(() => {
  const breakdown: { [key: string]: { count: number; amount: number; name: string; image: string } } = {}
  
  transactions.value
    .filter(t => !(t.status?.toLowerCase() === 'annulée' || t.cancelled_at))
    .forEach(t => {
      const symbol = t.wallet?.cryptomoney?.symbole || 'Unknown'
      const name = t.wallet?.cryptomoney?.nom || 'Unknown'
      const image = t.wallet?.cryptomoney?.image || ''
      
      if (!breakdown[symbol]) {
        breakdown[symbol] = { count: 0, amount: 0, name, image }
      }
      
      breakdown[symbol].count++
      breakdown[symbol].amount += Number(t.total_eur || 0)
    })
  
  return Object.entries(breakdown)
    .map(([symbol, data]) => ({ symbol, ...data }))
    .sort((a, b) => b.amount - a.amount)
})

// Computed - Pagination
const paginatedTransactions = computed(() => {
  const start = (currentPage.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return transactions.value.slice(start, end)
})

const totalPages = computed(() => Math.ceil(transactions.value.length / itemsPerPage))

// Charts data
const barChartData = computed(() => ({
  labels: cryptoBreakdown.value.slice(0, 5).map(c => c.symbol.toUpperCase()),
  datasets: [{
    label: 'Transaction Volume (€)',
    data: cryptoBreakdown.value.slice(0, 5).map(c => c.amount),
    backgroundColor: [
      'rgba(53, 167, 255, 0.8)',
      'rgba(1, 255, 25, 0.8)',
      'rgba(56, 97, 140, 0.8)',
      'rgba(255, 89, 100, 0.8)',
      'rgba(53, 167, 255, 0.5)',
    ],
    borderColor: ['#35A7FF', '#01FF19', '#38618C', '#FF5964', '#35A7FF'],
    borderWidth: 2
  }]
}))

const barChartOptions: any = {
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
      callbacks: {
        label: (context: any) => `${formatCurrency(context.parsed.y)}`
      }
    }
  },
  scales: {
    x: { 
      ticks: { 
        color: '#38618C',
        font: { weight: 'bold' as const }
      },
      grid: { display: false }
    },
    y: { 
      ticks: { 
        color: '#38618C',
        callback: (value: any) => formatCurrency(value)
      },
      grid: { color: '#E5E7EB' }
    }
  }
}

const doughnutChartData = computed(() => ({
  labels: ['Buy', 'Sell'],
  datasets: [{
    data: [stats.value.totalBuy, stats.value.totalSell],
    backgroundColor: ['rgba(1, 255, 25, 0.8)', 'rgba(255, 89, 100, 0.8)'],
    borderColor: ['#01FF19', '#FF5964'],
    borderWidth: 2
  }]
}))

const doughnutChartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        color: '#38618C',
        font: { size: 12, weight: 'bold' as const },
        padding: 15
      }
    },
    tooltip: {
      backgroundColor: '#FFFFFF',
      titleColor: '#38618C',
      bodyColor: '#38618C',
      borderColor: '#38618C',
      borderWidth: 1,
      callbacks: {
        label: (context: any) => `${context.label}: ${formatCurrency(context.parsed)}`
      }
    }
  }
} as const

function goBack() {
  router.push('/dashboard/admin/clients')
}

function nextPage() {
  if (currentPage.value < totalPages.value) {
    currentPage.value++
  }
}

function prevPage() {
  if (currentPage.value > 1) {
    currentPage.value--
  }
}

// add small image error handler
function handleImgError(e: Event) {
  const t = e.target as HTMLImageElement | null
  if (t) t.style.display = 'none'
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button 
          variant="outline"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
          @click="goBack"
        >
          ← Back
        </Button>
        <div v-if="client">
          <h1 class="text-3xl font-bold text-[#38618C]">{{ client.name }}</h1>
          <p class="text-gray-500">Client Details</p>
        </div>
      </div>
      <Button 
        :disabled="loading"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
        @click="fetchClientDetails"
      >
        🔄 Refresh
      </Button>
    </div>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Loading client details...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">❌</div>
        <h3 class="text-xl font-semibold text-[#FF5964] mb-2">Error Loading Data</h3>
        <div class="text-gray-600 mb-4">{{ error }}</div>
        <Button 
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
          @click="fetchClientDetails"
        >
          Try Again
        </Button>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else-if="client" class="space-y-6">
      <!-- Main Grid: Info (Left) + Charts (Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Client Information -->
        <Card class="lg:col-span-1 border-[#35A7FF]">
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              👤 Client Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Avatar -->
            <!-- Banner + Profile Image -->
            <div class="flex justify-center">
              <div class="relative w-full max-w-md">
                <div v-if="makeImageUrl(client.profile_banner)" class="w-full rounded-lg overflow-hidden">
                  <img
                    :src="makeImageUrl(client.profile_banner)"
                    alt="banner"
                    class="w-full h-28 object-cover rounded-lg"
                    @error="handleImgError"
                  />
                </div>

                <div class="mx-auto w-fit -mt-12">
                  <div class="h-24 w-24 rounded-full overflow-hidden border-4 border-white shadow-lg bg-gray-100">
                    <img
                      v-if="makeImageUrl(client.profile_picture)"
                      :src="makeImageUrl(client.profile_picture)"
                      alt="avatar"
                      class="h-full w-full object-cover"
                      @error="handleImgError"
                    />
                    <div v-else class="h-full w-full flex items-center justify-center bg-gradient-to-br from-[#35A7FF] to-[#38618C] text-white font-bold text-4xl">
                      {{ client.name?.charAt(0).toUpperCase() || 'U' }}
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Details -->
            <div class="space-y-4">
              <div>
                <div class="text-xs text-gray-500 mb-1">Full Name</div>
                <div class="font-semibold text-[#38618C]">{{ client.name }}</div>
              </div>

              <div>
                <div class="text-xs text-gray-500 mb-1">Email Address</div>
                <div class="font-semibold text-[#38618C] break-all">{{ client.email }}</div>
              </div>

           

               <div>
                <div class="text-xs text-gray-500 mb-1">balance </div>
                <Badge class="bg-[#38618C] text-white">{{ formatCurrency(client.balance_eur)}}</Badge>
              </div>
              <div>
                <div class="text-xs text-gray-500 mb-1">Account Value</div>
                <div class="text-2xl font-bold text-[#01FF19]">{{ formatCurrency(client.account_balance) }}</div>
              </div>



             
            </div>
          </CardContent>
        </Card>

        <!-- Right: Charts -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Statistics Cards -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-3xl mb-2">📊</div>
                <div class="text-xs text-gray-500 mb-1">Total Transactions</div>
                <div class="text-2xl font-bold text-[#38618C]">{{ stats.totalTransactions }}</div>
              </CardContent>
            </Card>

            <Card class="border-gray-200 hover:border-[#01FF19] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-3xl mb-2">📈</div>
                <div class="text-xs text-gray-500 mb-1">Total Buy</div>
                <div class="text-xl font-bold text-[#01FF19]">{{ formatCurrency(stats.totalBuy) }}</div>
              </CardContent>
            </Card>

            <Card class="border-gray-200 hover:border-[#FF5964] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-3xl mb-2">📉</div>
                <div class="text-xs text-gray-500 mb-1">Total Sell</div>
                <div class="text-xl font-bold text-[#FF5964]">{{ formatCurrency(stats.totalSell) }}</div>
              </CardContent>
            </Card>

            <Card class="border-gray-200 hover:border-[#38618C] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-3xl mb-2">❌</div>
                <div class="text-xs text-gray-500 mb-1">Cancelled</div>
                <div class="text-2xl font-bold text-[#38618C]">{{ stats.cancelled }}</div>
              </CardContent>
            </Card>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Bar Chart -->
            <Card>
              <CardHeader>
                <CardTitle class="text-sm font-semibold text-[#38618C]">
                  📊 Top Cryptos by Volume
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Bar v-if="cryptoBreakdown.length > 0" :data="barChartData" :options="barChartOptions" />
                  <div v-else class="h-full flex items-center justify-center text-gray-500">
                    No data available
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Doughnut Chart -->
            <Card>
              <CardHeader>
                <CardTitle class="text-sm font-semibold text-[#38618C]">
                  🥧 Buy vs Sell
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Doughnut v-if="stats.totalBuy > 0 || stats.totalSell > 0" :data="doughnutChartData" :options="doughnutChartOptions" />
                  <div v-else class="h-full flex items-center justify-center text-gray-500">
                    No data available
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Transactions Table -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📋 Recent Transactions
            </CardTitle>
            <Badge class="bg-[#35A7FF] text-white">
              {{ transactions.length }} Total
            </Badge>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="transactions.length === 0" class="text-center py-12">
            <div class="text-6xl mb-4">📭</div>
            <h3 class="text-xl font-semibold text-[#38618C] mb-2">No Transactions</h3>
            <p class="text-gray-500">This client hasn't made any transactions yet</p>
          </div>
          <div v-else class="space-y-3">
            <Card 
              v-for="tx in paginatedTransactions" 
              :key="tx.id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all"
              :class="(tx.status?.toLowerCase() === 'annulée' || tx.cancelled_at) ? 'opacity-60' : ''"
            >
              <CardContent class="p-4">
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                  <!-- Crypto Info -->
                  <div class="flex items-center gap-3 flex-1">
                    <!-- old img usage replaced with robust fallback -->
                    <img
                      v-if="(
                        tx.wallet?.cryptomoney?.image ||
                        tx.wallet?.cryptomoney?.image_url ||
                        tx.cryptomoney?.image ||
                        tx.cryptomoney?.image_url
                      )"
                      :src="tx.wallet?.cryptomoney?.image || tx.wallet?.cryptomoney?.image_url || tx.cryptomoney?.image || tx.cryptomoney?.image_url"
                      :alt="tx.wallet?.cryptomoney?.nom || tx.cryptomoney?.name || 'crypto'"
                      class="h-10 w-10 rounded-full border-2 border-gray-300 object-cover"
                      @error="(e) => { const t = e.target as HTMLImageElement; t.style.display = 'none' }"
                    />
                    <div v-else class="h-10 w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center">
                      💎
                    </div>
                    <div>
                      <div class="flex items-center gap-2 mb-1">
                        <Badge 
                          :class="tx.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                          class="text-white font-semibold"
                        >
                          {{ tx.type === 'ACHAT' ? '📈 BUY' : '📉 SELL' }}
                        </Badge>
                        <Badge 
                          v-if="tx.status?.toLowerCase() === 'annulée' || tx.cancelled_at"
                          class="bg-[#FF5964] text-white"
                          :title="tx.cancel_reason || 'Cancelled transaction'"
                        >
                          ❌ Cancelled
                        </Badge>
                      </div>
                      <div class="font-semibold text-[#38618C]">
                        {{ tx.wallet?.cryptomoney?.nom || 'Unknown' }}
                      </div>
                      <div class="text-sm text-gray-500">
                        {{ tx.wallet?.cryptomoney?.symbole?.toUpperCase() || 'N/A' }} • TX #{{ tx.id }}
                      </div>
                    </div>
                  </div>

                  <!-- Stats -->
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <div class="text-xs text-gray-500">Quantity</div>
                      <div class="font-semibold text-[#38618C] font-mono">
                        {{ formatNumber(tx.quantity, 8) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500">Price</div>
                      <div class="font-semibold text-[#35A7FF]">
                        {{ formatCurrency(tx.price) }}
                      </div>
                    </div>
                  </div>

                  <!-- Total and Date -->
                  <div class="text-right">
                    <div class="text-xs text-gray-500">Total Amount</div>
                    <div class="text-lg font-bold text-[#38618C]">
                      {{ formatCurrency(tx.total_eur) }}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                      {{ formatDate(tx.created_at) }}
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Pagination -->
            <div class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
              <div class="text-sm text-gray-600">
                Showing {{ Math.min((currentPage - 1) * itemsPerPage + 1, transactions.length) }} 
                to {{ Math.min(currentPage * itemsPerPage, transactions.length) }} 
                of {{ transactions.length }} transactions
              </div>
              <div class="flex gap-2">
                <Button
                  variant="outline"
                  size="sm"
                  :disabled="currentPage <= 1"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white disabled:opacity-50"
                  @click="prevPage"
                >
                  ← Previous
                </Button>
                <div class="flex items-center gap-2 px-3">
                  <span class="text-sm font-semibold text-[#38618C]">{{ currentPage }}</span>
                  <span class="text-sm text-gray-500">of</span>
                  <span class="text-sm font-semibold text-[#38618C]">{{ totalPages }}</span>
                </div>
                <Button
                  variant="outline"
                  size="sm"
                  :disabled="currentPage >= totalPages"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white disabled:opacity-50"
                  @click="nextPage"
                >
                  Next →
                </Button>
              </div>
            </div>
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