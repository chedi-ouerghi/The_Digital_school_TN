<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import api from '../../services/api'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement } from 'chart.js'
import { Bar, Doughnut } from 'vue-chartjs'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

const route = useRoute()
const router = useRouter()
const transaction = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// Utility functions
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR' 
  }).format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('fr-FR', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Computed - Price evolution data (exemple simulé)
const priceEvolutionData = computed(() => ({
  labels: ['-24h', '-12h', '-6h', '-3h', '-1h', 'Transaction', '+1h', '+3h'],
  datasets: [{
    label: 'Price EUR',
    data: [92000, 93500, 94500, 95000, 95300, 95471.83, 95600, 95800],
    backgroundColor: 'rgba(53, 167, 255, 0.2)',
    borderColor: '#35A7FF',
    borderWidth: 2,
    tension: 0.4,
    fill: true
  }]
}))

const priceEvolutionOptions = {
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
      ticks: { color: '#38618C', font: { weight: 'bold' } },
      grid: { display: false }
    },
    y: { 
      ticks: { 
        color: '#38618C',
        callback: (value: number) => formatCurrency(value)
      },
      grid: { color: '#E5E7EB' }
    }
  }
}

// Computed - Market comparison data
const marketComparisonData = computed(() => ({
  labels: ['Your Price', 'Average Market Price', '24h Min Price', '24h Max Price'],
  datasets: [{
    data: [
      transaction.value?.price || 0,
      transaction.value?.portefeuille?.cryptomoney?.price_eur || 0,
      (transaction.value?.portefeuille?.cryptomoney?.price_eur || 0) * 0.98,
      (transaction.value?.portefeuille?.cryptomoney?.price_eur || 0) * 1.02
    ],
    backgroundColor: [
      'rgba(53, 167, 255, 0.8)',
      'rgba(1, 255, 25, 0.8)',
      'rgba(255, 89, 100, 0.8)',
      'rgba(56, 97, 140, 0.8)'
    ],
    borderColor: ['#35A7FF', '#01FF19', '#FF5964', '#38618C'],
    borderWidth: 2
  }]
}))

const marketComparisonOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'bottom' as const,
      labels: {
        color: '#38618C',
        font: { size: 12, weight: 'bold' },
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
}

async function fetchTransaction() {
  loading.value = true
  error.value = null
  try {
    const id = route.params.id
    const res = await api.admin.transactions.show(id)
    const tx = res || {}

    // Normalize structure: support many payload shapes including crypto_wallet_asset.wallet.user
    const user =
      tx.user ||
      tx.crypto_wallet_asset?.wallet?.user ||
      tx.portefeuille?.user ||
      tx.portefeuille?.owner ||
      tx.customer ||
      null

    const cryptomoney =
      tx.cryptomoney ||
      tx.crypto_wallet_asset?.cryptomoney ||
      tx.portefeuille?.cryptomoney ||
      tx.asset ||
      null

    transaction.value = {
      ...tx,
      user,
      cryptomoney,
      crypto_wallet_asset: tx.crypto_wallet_asset || tx.portefeuille?.crypto_wallet_asset || null,
      cancelled_at: tx.cancelled_at ?? tx.cancelAt ?? tx.cancelled?.at ?? null,
      cancel_reason: tx.cancel_reason ?? tx.cancelReason ?? tx.cancelled_reason ?? null,
      total_eur: tx.total_eur ?? tx.total ?? tx.amount ?? 0,
      price: tx.price ?? tx.unit_price ?? 0,
      quantity: tx.quantity ?? tx.qty ?? 0,
      created_at: tx.created_at ?? tx.createdAt ?? tx.date ?? null,
    }
  } catch (err: any) {
    error.value = err.message || 'Error loading transaction'
    console.error('Error fetching transaction:', err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchTransaction)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div class="flex items-center gap-4">
        <Button 
          @click="router.back()"
          variant="outline"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
        >
          ← Retour
        </Button>
        <div v-if="transaction">
          <h1 class="text-3xl font-bold text-[#38618C]">Transaction #{{ transaction.id }}</h1>
          <p class="text-gray-500">Détails complets de l'opération</p>
        </div>
      </div>
      <Button 
        @click="fetchTransaction"
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
            <div>Loading transaction details...</div>
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
          @click="fetchTransaction"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Réessayer
        </Button>
      </CardContent>
    </Card>

    <!-- Content -->
    <div v-else-if="transaction" class="space-y-6">
      <!-- Main Grid: Info (Left) + Charts (Right) -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left: Transaction Information -->
        <Card class="lg:col-span-1 border-[#35A7FF]">
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">
              📊 Transaction Information
            </CardTitle>
          </CardHeader>
          <CardContent class="space-y-6">
            <!-- Crypto Avatar -->
            <div class="flex justify-center">
              <div class="relative">
                <img 
                  v-if="transaction.cryptomoney?.image_url || transaction.cryptomoney?.image"
                  :src="transaction.cryptomoney.image_url || transaction.cryptomoney.image"
                  :alt="transaction.cryptomoney.nom"
                  class="h-20 w-20 rounded-full border-4 border-[#35A7FF]"
                />
                <div 
                  v-else
                  class="h-20 w-20 rounded-full bg-gradient-to-br from-[#35A7FF] to-[#38618C] flex items-center justify-center text-white font-bold text-2xl"
                >
                  {{ transaction.cryptomoney?.symbole?.charAt(0)?.toUpperCase() || 'C' }}
                </div>
                <Badge 
                  :class="transaction.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="absolute -top-2 -right-2 text-white font-bold"
                >
                  {{ transaction.type === 'ACHAT' ? '📈' : '📉' }}
                </Badge>
              </div>
            </div>

            <!-- Details améliorés et responsive -->
            <div class="space-y-4">
              <div>
                <div class="text-xs text-gray-500 mb-1">ID Transaction</div>
                <div class="font-semibold text-[#38618C] font-mono">#{{ transaction.id }}</div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                  <div class="text-xs text-gray-500 mb-1">Date & Time</div>
                  <div class="font-semibold text-[#38618C]">{{ formatDate(transaction.created_at) }}</div>
                </div>

                <div>
                  <div class="text-xs text-gray-500 mb-1">Type</div>
                  <Badge :class="transaction.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'" class="text-white font-semibold">
                    {{ transaction.type === 'ACHAT' ? 'ACHAT' : 'VENTE' }}
                  </Badge>
                </div>
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4 border-t border-gray-200">
                <div>
                  <div class="text-xs text-gray-500">Quantity</div>
                  <div class="font-semibold text-[#38618C] font-mono">
                    {{ formatNumber(transaction.quantity ?? transaction.crypto_wallet_asset?.quantity, 8) }}
                    {{ String(transaction.cryptomoney?.symbol || '').toUpperCase() }}
                  </div>
                </div>

                <div>
                  <div class="text-xs text-gray-500">Average Buy Price</div>
                  <div class="font-semibold text-[#38618C]">
                    {{ formatCurrency(Number(transaction.crypto_wallet_asset?.average_buy_price || transaction.price || 0)) }}
                  </div>
                </div>

                <div>
                  <div class="text-xs text-gray-500">Wallet Balance</div>
                  <div class="font-semibold text-[#01FF19]">
                    {{ formatCurrency(Number(transaction.crypto_wallet_asset?.wallet?.balance_eur || 0)) }}
                  </div>
                </div>

                <div>
                  <div class="text-xs text-gray-500">Total Amount</div>
                  <div class="text-lg font-bold text-[#01FF19]">
                    {{ formatCurrency(Number(transaction.total_eur || 0)) }}
                  </div>
                </div>
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
                <div class="text-2xl mb-2">💰</div>
                <div class="text-xs text-gray-500 mb-1">Unit Price</div>
                <div class="text-lg font-bold text-[#35A7FF]">{{ formatCurrency(transaction.price) }}</div>
              </CardContent>
            </Card>

            <Card class="border-gray-200 hover:border-[#01FF19] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-2xl mb-2">⚖️</div>
                <div class="text-xs text-gray-500 mb-1">Quantity</div>
                <div class="text-lg font-bold text-[#01FF19] font-mono">
                  {{ formatNumber(transaction.quantity) }}
                </div>
              </CardContent>
            </Card>

            <Card class="border-gray-200 hover:border-[#38618C] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-2xl mb-2">📈</div>
                <div class="text-xs text-gray-500 mb-1">Variation 24h</div>
                <div 
                  :class="Number(transaction.cryptomoney?.change_24h_pct || 0) >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                  class="text-lg font-bold"
                >
                  {{ Number(transaction.cryptomoney?.change_24h_pct || 0).toFixed(2) }}%
                </div>
              </CardContent>
            </Card>

            <Card class="border-gray-200 hover:border-[#FF5964] transition-colors">
              <CardContent class="p-4 text-center">
                <div class="text-2xl mb-2">🏷️</div>
                <div class="text-xs text-gray-500 mb-1">Market Cap</div>
                <div class="text-sm font-bold text-[#FF5964]">
                  {{ formatCurrency(Number(transaction.cryptomoney?.market_cap || 0)) }}
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Charts -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Price Evolution Chart -->
            <Card>
              <CardHeader>
                <CardTitle class="text-sm font-semibold text-[#38618C]">
                  📈 Price Evolution
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Bar :data="priceEvolutionData" :options="priceEvolutionOptions" />
                </div>
              </CardContent>
            </Card>

            <!-- Market Comparison Chart -->
            <Card>
              <CardHeader>
                <CardTitle class="text-sm font-semibold text-[#38618C]">
                  🏪 Market Comparison
                </CardTitle>
              </CardHeader>
              <CardContent>
                <div class="h-[250px]">
                  <Doughnut :data="marketComparisonData" :options="marketComparisonOptions" />
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Additional Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-4">
        <!-- Client Information -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">👤 Client Information</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#35A7FF] to-[#38618C] flex items-center justify-center text-white font-bold">
                {{ (transaction.user?.name || transaction.crypto_wallet_asset?.wallet?.user?.name || 'U').charAt(0).toUpperCase() }}
              </div>
              <div>
                <div class="font-semibold text-[#38618C]">
                  {{ transaction.user?.name || transaction.crypto_wallet_asset?.wallet?.user?.name || 'Utilisateur' }}
                </div>
                <div class="text-sm text-gray-500">
                  {{ transaction.user?.email || transaction.crypto_wallet_asset?.wallet?.user?.email || '—' }}
                </div>
              </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-4 border-t border-gray-200">
              <div>
                <div class="text-xs text-gray-500">Role</div>
                <Badge class="bg-[#38618C] text-white">
                  {{ ( (transaction.user?.role || transaction.crypto_wallet_asset?.wallet?.user?.role || '').toString().toLowerCase() === 'admin') ? 'Admin' : 'Client' }}
                </Badge>
              </div>

              <div>
                <div class="text-xs text-gray-500">Member Since</div>
                <div class="font-semibold text-[#38618C]">
                  {{ formatDate(transaction.user?.created_at || transaction.crypto_wallet_asset?.wallet?.user?.created_at || '') }}
                </div>
              </div>

              <div>
                <div class="text-xs text-gray-500">Wallet ID</div>
                <div class="font-mono text-sm text-[#38618C]">
                  {{ transaction.crypto_wallet_asset?.wallet?.id || '—' }}
                </div>
              </div>

              <div>
                <div class="text-xs text-gray-500">Balance wallet</div>
                <div class="font-semibold text-[#01FF19]">
                  {{ formatCurrency(Number(transaction.crypto_wallet_asset?.wallet?.balance_eur || 0)) }}
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Crypto Information -->
        <Card>
          <CardHeader>
            <CardTitle class="text-lg font-semibold text-[#38618C]">🪙 Crypto Information</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full border-2 border-[#35A7FF] bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img
                  v-if="(transaction.cryptomoney?.image_url || transaction.cryptomoney?.image)"
                  :src="transaction.cryptomoney?.image_url || transaction.cryptomoney?.image"
                  :alt="transaction.cryptomoney?.name || 'crypto'"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="(e) => { const target = e.target as HTMLImageElement; if (target?.parentNode) target.style.display = 'none' }"
                />
                <div v-if="!(transaction.cryptomoney?.image_url || transaction.cryptomoney?.image)" class="text-lg">💎</div>
              </div>
              <div>
                <div class="font-semibold text-[#38618C]">{{ transaction.cryptomoney?.name || '—' }}</div>
                <Badge class="bg-[#35A7FF] text-white">
                  {{ String(transaction.cryptomoney?.symbol || transaction.cryptomoney?.symbole || '').toUpperCase() || '—' }}
                </Badge>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
              <div>
                <div class="text-xs text-gray-500">Current Price</div>
                <div class="font-semibold text-[#35A7FF]">
                  {{ formatCurrency(Number(transaction.cryptomoney?.price_eur || transaction.cryptomoney?.price || 0)) }}
                </div>
              </div>

              <div>
                <div class="text-xs text-gray-500">Volume 24h</div>
                <div class="font-semibold text-[#38618C]">
                  {{ formatCurrency(Number(transaction.cryptomoney?.volume_24h || 0)) }}
                </div>
              </div>

              <div>
                <div class="text-xs text-gray-500">Avg. Buy (asset)</div>
                <div class="font-semibold text-[#38618C]">
                  {{ formatCurrency(Number(transaction.crypto_wallet_asset?.average_buy_price || 0)) }}
                </div>
              </div>

              <div>
                <div class="text-xs text-gray-500">Quantity Held (asset)</div>
                <div class="font-mono text-sm text-[#38618C]">
                  {{ formatNumber(transaction.crypto_wallet_asset?.quantity || transaction.quantity || 0, 8) }}
                </div>
              </div>
            </div>

            <div class="pt-3">
              <a v-if="transaction.cryptomoney?.website" :href="transaction.cryptomoney.website" target="_blank" class="text-[#35A7FF] hover:underline">
                ↗ View Official Website
              </a>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Cancellation Information -->
      <Card v-if="transaction.cancelled_at" class="border-[#FF5964]">
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-[#FF5964]">
            ❌ Cancellation Information
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <div class="text-sm text-gray-500 mb-1">Cancellation Date</div>
              <div class="font-semibold text-[#FF5964]">{{ formatDate(transaction.cancelled_at) }}</div>
            </div>
            <div>
              <div class="text-sm text-gray-500 mb-1">Cancellation Reason</div>
              <div class="font-semibold text-[#FF5964]">{{ transaction.cancel_reason || 'Not specified' }}</div>
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