<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../../services/api'
import type { Wallet } from '../../types'

// UI components
import { Dialog, DialogContent, DialogHeader, DialogTitle, DialogDescription, DialogFooter } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

// State
const wallet = ref<Wallet | null>(null)
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)

// Filters
const filterType = ref<'all' | 'buy' | 'sell'>('all')
const searchQuery = ref('')

// Sell dialog
const showSellDialog = ref(false)
const selectedAsset = ref<any | null>(null)
const sellQuantity = ref('')
const sellError = ref<string | null>(null)
const sellSuccess = ref<string | null>(null)
const isSelling = ref(false)

// Format functions
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0,00 €'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  return n.toFixed(decimals)
}

// Load transactions
async function loadTransactions() {
  loading.value = true
  error.value = null
  try {
    const response = await api.wallet.list()
    const data = response?.wallet

    if (!data) {
      throw new Error('Portefeuille non disponible')
    }

    wallet.value = data

    // Map wallet transactions correctly
    const mapped: any[] = []
    const walletTxs = data.transactions || []

    walletTxs.forEach((tx: any) => {
      const crypto = tx.cryptomoney || {}
      
      mapped.push({
        id: tx.id,
        type: tx.type === 'ACHAT' ? 'buy' : 'sell',
        typeLabel: tx.type,
        quantity: Number(tx.quantity || 0),
        price: Number(tx.price || 0),
        total: Number(tx.total_eur || 0),
        date: tx.created_at,
        cancelled: !!tx.cancelled_at,
        cancelReason: tx.cancel_reason,
        crypto: {
          id: crypto.id,
          name: crypto.name,
          symbol: crypto.symbol,
          image_url: crypto.image_url,
        },
      })
    })

    transactions.value = mapped.sort(
      (a, b) => new Date(b.date).getTime() - new Date(a.date).getTime()
    )

  } catch (e: any) {
    error.value = e.message || 'Erreur lors du chargement des transactions'
    console.error('Erreur:', e)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  loadTransactions()
})

// Compute available quantities per crypto
const availableQuantities = computed(() => {
  const quantities: {
    [key: string]: { amount: number; name: string; symbol: string; image: string }
  } = {}

  transactions.value.forEach((tx) => {
    if (tx.cancelled) return

    const symbol = tx.crypto.symbol
    if (!quantities[symbol]) {
      quantities[symbol] = {
        amount: 0,
        name: tx.crypto.name,
        symbol: symbol,
        image: tx.crypto.image_url,
      }
    }

    if (tx.type === 'buy') {
      quantities[symbol].amount += tx.quantity
    } else if (tx.type === 'sell') {
      quantities[symbol].amount -= tx.quantity
    }
  })

  return quantities
})

// Statistics
const stats = computed(() => {
  const activeTxs = transactions.value.filter(t => !t.cancelled)
  const buys = activeTxs.filter(t => t.type === 'buy')
  const sells = activeTxs.filter(t => t.type === 'sell')

  return {
    totalBuys: buys.reduce((sum, t) => sum + t.total, 0),
    totalSells: sells.reduce((sum, t) => sum + t.total, 0),
    countBuys: buys.length,
    countSells: sells.length,
    countCancelled: transactions.value.filter(t => t.cancelled).length,
    profit: sells.reduce((sum, t) => sum + t.total, 0) - buys.reduce((sum, t) => sum + t.total, 0),
  }
})

// Filtered transactions
const filteredTransactions = computed(() => {
  let filtered = transactions.value

  // Type filter
  if (filterType.value !== 'all') {
    filtered = filtered.filter(t => t.type === filterType.value)
  }

  // Search filter
  if (searchQuery.value) {
    const q = searchQuery.value.toLowerCase()
    filtered = filtered.filter(t =>
      t.crypto.name.toLowerCase().includes(q) ||
      t.crypto.symbol.toLowerCase().includes(q) ||
      t.id.includes(q)
    )
  }

  return filtered
})

// Get available quantity
function getAvailableQuantity(symbol: string): number {
  return availableQuantities.value[symbol]?.amount || 0
}

// Can sell transaction
function canSell(tx: any): boolean {
  if (tx.cancelled || tx.type !== 'buy') return false
  return getAvailableQuantity(tx.crypto.symbol) > 0
}

// Sell dialog functions
function openSellDialog(tx: any) {
  selectedAsset.value = tx
  sellQuantity.value = ''
  sellError.value = null
  sellSuccess.value = null
  showSellDialog.value = true
}

function closeSellDialog() {
  showSellDialog.value = false
  selectedAsset.value = null
  sellQuantity.value = ''
  sellError.value = null
  sellSuccess.value = null
}

async function confirmSell() {
  if (!selectedAsset.value) return

  const qty = parseFloat(sellQuantity.value)
  const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)

  if (!qty || qty <= 0) {
    sellError.value = 'Veuillez entrer une quantité valide'
    return
  }

  if (qty > available) {
    sellError.value = `Quantité insuffisante. Disponible: ${formatNumber(available, 8)} ${selectedAsset.value.crypto.symbol.toUpperCase()}`
    return
  }

  isSelling.value = true
  sellError.value = null

  try {
    await api.wallet.transact({
      symbol: selectedAsset.value.crypto.symbol,
      type: 'VENTE',
      quantity: qty,
    })

    sellSuccess.value = 'Vente effectuée avec succès!'
    setTimeout(async () => {
      await loadTransactions()
      closeSellDialog()
    }, 1500)

  } catch (e: any) {
    sellError.value = e?.message || 'Erreur lors de la vente'
  } finally {
    isSelling.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
  <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">My Transactions</h1>
        <p class="text-gray-500">Complete history of your buys and sells</p>
      </div>
      <Button
        @click="loadTransactions"
        :disabled="loading"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold w-full sm:w-auto"
      >
        🔄 Refresh
      </Button>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
      <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Total Buys</div>
          <div class="text-lg md:text-xl font-bold text-[#01FF19] truncate">
            {{ formatCurrency(stats.totalBuys) }}
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ stats.countBuys }} tx</div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#FF5964] transition-colors bg-gradient-to-br from-[#FF5964]/10 to-transparent">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Total Sells</div>
          <div class="text-lg md:text-xl font-bold text-[#FF5964] truncate">
            {{ formatCurrency(stats.totalSells) }}
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ stats.countSells }} tx</div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Profit/Loss</div>
          <div
            class="text-lg md:text-xl font-bold truncate"
            :class="stats.profit >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
          >
            {{ stats.profit >= 0 ? '+' : '' }}{{ formatCurrency(stats.profit) }}
          </div>
          <Badge
            :class="stats.profit >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
            class="text-white text-xs mt-1"
          >
            {{ stats.profit >= 0 ? '📈' : '📉' }}
          </Badge>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#38618C] transition-colors">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Total</div>
          <div class="text-lg md:text-xl font-bold text-[#38618C]">
            {{ transactions.length }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            {{ stats.countCancelled > 0 ? `${stats.countCancelled} cancelled` : 'Valid' }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Alert cancelled -->
    <Alert v-if="stats.countCancelled > 0" class="border-[#FF5964] bg-[#FF5964]/10">
      <AlertDescription class="text-[#FF5964] text-sm">
        ⚠️ {{ stats.countCancelled }} transaction{{ stats.countCancelled > 1 ? 's cancelled' : ' cancelled' }} by admin. Refund in 24h.
      </AlertDescription>
    </Alert>

    <!-- Available quantities -->
    <div v-if="Object.keys(availableQuantities).length > 0">
      <Card>
        <CardHeader>
          <CardTitle class="text-base md:text-lg font-semibold text-[#38618C]">
            💼 Available Quantities
          </CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3">
            <div
              v-for="(data, symbol) in availableQuantities"
              :key="symbol"
              class="border border-gray-200 rounded-lg p-3 hover:border-[#35A7FF] transition-colors text-center"
            >
              <div class="h-10 w-10 rounded-full border-2 border-gray-300 bg-gray-100 mx-auto mb-2 flex items-center justify-center flex-shrink-0">
                <img
                  v-if="data.image"
                  :src="data.image"
                  :alt="symbol"
                  class="h-10 w-10 rounded-full object-cover"
                  @error="(e) => e.target.style.display = 'none'"
                />
                <div v-if="!data.image" class="text-lg">💎</div>
              </div>
              <div class="text-sm font-semibold text-[#38618C] truncate">{{ data.name }}</div>
              <div class="text-xs text-gray-500 font-mono mb-2">{{ String(symbol).toUpperCase() }}</div>
              <Badge
                :class="data.amount > 0 ? 'bg-[#01FF19]' : 'bg-gray-400'"
                class="text-white font-mono text-xs"
              >
                {{ formatNumber(data.amount, 6) }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <Input
              v-model="searchQuery"
              placeholder="🔍 Search..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF] text-sm"
            />
          </div>

          <Select v-model="filterType">
            <SelectTrigger class="w-full sm:w-40 border-[#38618C] text-sm">
              <SelectValue placeholder="Type" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All</SelectItem>
              <SelectItem value="buy">📈 Buys</SelectItem>
              <SelectItem value="sell">📉 Sells</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardContent>
    </Card>

    <!-- Loading -->
    <Card v-if="loading">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div class="text-sm">Loading transactions...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Error -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="text-4xl mb-4">❌</div>
        <div class="text-sm text-[#FF5964] mb-4">{{ error }}</div>
        <Button @click="loadTransactions" class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white text-sm">
          Retry
        </Button>
      </CardContent>
    </Card>

    <!-- Empty -->
    <Card v-else-if="filteredTransactions.length === 0">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="text-4xl sm:text-6xl mb-4">📋</div>
        <h3 class="text-lg sm:text-xl font-semibold text-[#38618C] mb-2">
          {{ searchQuery || filterType !== 'all' ? 'No results' : 'No transactions' }}
        </h3>
        <p class="text-sm text-gray-500">
          {{ searchQuery || filterType !== 'all'
            ? 'Try adjusting your filters'
            : 'Start investing to see your transactions'
          }}
        </p>
      </CardContent>
    </Card>

    <!-- Transactions List -->
    <div v-else class="space-y-3">
      <Card
        v-for="tx in filteredTransactions"
        :key="tx.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg"
        :class="tx.cancelled ? 'opacity-60' : ''"
      >
        <CardContent class="p-4">
          <!-- Mobile Layout -->
          <div class="block md:hidden space-y-3">
            <div class="flex items-start gap-3">
              <div class="h-10 w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img
                  v-if="tx.crypto.image_url"
                  :src="tx.crypto.image_url"
                  :alt="tx.crypto.name"
                  class="h-10 w-10 rounded-full object-cover"
                  @error="(e) => e.target.style.display = 'none'"
                />
                <div v-if="!tx.crypto.image_url" class="text-lg">💎</div>
              </div>

              <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                  <Badge
                    :class="tx.type === 'buy' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                    class="text-white text-xs font-semibold"
                  >
                    {{ tx.type === 'buy' ? '📈 ACHAT' : '📉 VENTE' }}
                  </Badge>
                  <Badge v-if="tx.cancelled" class="bg-[#FF5964] text-white text-xs">❌ Annulée</Badge>
                  <Badge v-else-if="canSell(tx)" class="bg-[#01FF19] text-white text-xs">✓ Dispo</Badge>
                </div>
                <div class="font-bold text-[#38618C] text-sm">{{ tx.crypto.name }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ String(tx.crypto.symbol).toUpperCase() }}</div>
              </div>

              <div class="text-right">
                <div class="text-sm font-bold text-[#35A7FF]">{{ formatCurrency(tx.total) }}</div>
                <div class="text-xs text-gray-500">
                  {{ new Date(tx.date).toLocaleDateString('fr-FR', { month: 'short', day: 'numeric' }) }}
                </div>
              </div>
            </div>

            <div class="bg-gray-50 rounded p-3 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Quantité</span>
                <span class="font-mono font-semibold text-[#38618C]">{{ formatNumber(tx.quantity, 8) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Prix unitaire</span>
                <span class="font-semibold text-[#35A7FF]">{{ formatCurrency(tx.price) }}</span>
              </div>
            </div>

            <div v-if="!tx.cancelled && canSell(tx)">
              <Button
                size="sm"
                @click="openSellDialog(tx)"
                class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white w-full text-sm"
              >
                💰 Sell
              </Button>
            </div>
          </div>

      <!-- Desktop Layout -->
<div class="hidden md:grid md:grid-cols-12 md:items-center md:gap-4">
  <!-- Crypto Info -->
  <div class="md:col-span-3 flex items-center gap-3">
    <div class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
      <img
        v-if="tx.crypto.image_url"
        :src="tx.crypto.image_url"
        :alt="tx.crypto.name"
        class="h-12 w-12 rounded-full object-cover"
        @error="(e) => e.target.style.display = 'none'"
      />
      <div v-if="!tx.crypto.image_url" class="text-xl">💎</div>
    </div>

    <div class="min-w-0">
      <div class="flex items-center gap-2 mb-1">
        <Badge
          :class="tx.type === 'buy' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
          class="text-white text-xs font-semibold"
        >
          {{ tx.type === 'buy' ? '📈 BUY' : '📉 SELL' }}
        </Badge>
        <Badge v-if="tx.cancelled" class="bg-[#FF5964] text-white text-xs">❌ Cancelled</Badge>
      </div>
      <div class="font-bold text-[#38618C]">{{ tx.crypto.name }}</div>
      <div class="text-xs text-gray-500 font-mono">{{ String(tx.crypto.symbol).toUpperCase() }} • TX #{{ tx.id.slice(0, 8) }}</div>
    </div>
  </div>

  <!-- Stats -->
  <div class="md:col-span-4 grid grid-cols-3 gap-4 text-center">
    <div>
      <div class="text-xs text-gray-500 mb-1">Quantity</div>
      <div class="font-mono font-bold text-[#38618C] text-sm">{{ formatNumber(tx.quantity, 8) }}</div>
    </div>
    <div>
      <div class="text-xs text-gray-500 mb-1">Unit Price</div>
      <div class="font-bold text-[#35A7FF] text-sm">{{ formatCurrency(tx.price) }}</div>
    </div>
    <div>
      <div class="text-xs text-gray-500 mb-1">Total</div>
      <div class="font-bold text-[#38618C] text-sm">{{ formatCurrency(tx.total) }}</div>
    </div>
  </div>

  <!-- Date -->
  <div class="md:col-span-2 text-center">
    <div class="text-xs text-gray-500 mb-1">Date</div>
    <div class="text-sm font-semibold text-[#38618C]">
      {{ new Date(tx.date).toLocaleDateString('en-GB', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
      }) }}
    </div>
  </div>

  <!-- Action -->
  <div class="md:col-span-3 flex gap-2">
    <Button
      v-if="!tx.cancelled && canSell(tx)"
      size="sm"
      @click="openSellDialog(tx)"
      class="flex-1 bg-[#FF5964] hover:bg-[#FF5964]/90 text-white text-sm"
    >
      💰 Sell
    </Button>
    <span v-else class="text-xs text-gray-400">Not available</span>
  </div>
</div>

        </CardContent>
      </Card>
    </div>

<!-- Sell Dialog -->
<Dialog :open="showSellDialog" @update:open="closeSellDialog">
  <DialogContent class="sm:max-w-md border-[#35A7FF]">
    <DialogHeader>
      <DialogTitle class="text-[#38618C] text-lg">
        Sell {{ selectedAsset?.crypto.name }}
      </DialogTitle>
      <DialogDescription class="text-gray-600 text-sm">
        Transaction #{{ selectedAsset?.id?.slice(0, 8) }}
      </DialogDescription>
    </DialogHeader>

    <div class="space-y-4 py-4">
      <!-- Crypto Info -->
      <Card class="border-[#35A7FF] bg-gradient-to-br from-[#35A7FF]/5 to-transparent">
        <CardContent class="p-4">
          <div class="flex items-center gap-3">
            <div class="h-12 w-12 rounded-full border-2 border-[#35A7FF] bg-gray-100 flex items-center justify-center flex-shrink-0">
              <img
                v-if="selectedAsset?.crypto.image_url"
                :src="selectedAsset.crypto.image_url"
                :alt="selectedAsset.crypto.name"
                class="h-12 w-12 rounded-full object-cover"
                @error="(e) => e.target.style.display = 'none'"
              />
              <div v-if="!selectedAsset?.crypto.image_url" class="text-xl">💎</div>
            </div>

            <div class="flex-1">
              <div class="font-bold text-[#38618C]">{{ selectedAsset?.crypto.name }}</div>
              <div class="text-sm text-gray-500 font-mono">{{ String(selectedAsset?.crypto.symbol).toUpperCase() }}</div>
            </div>

            <div class="text-right">
              <div class="text-xs text-gray-500">Available</div>
              <div class="font-bold text-[#01FF19] font-mono text-sm">
                {{ formatNumber(selectedAsset ? getAvailableQuantity(selectedAsset.crypto.symbol) : 0, 8) }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Form -->
      <div class="space-y-4">
        <Alert v-if="sellError" class="border-[#FF5964] bg-[#FF5964]/10">
          <AlertDescription class="text-[#FF5964] text-sm">❌ {{ sellError }}</AlertDescription>
        </Alert>

        <Alert v-if="sellSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
          <AlertDescription class="text-[#01FF19] text-sm">✅ {{ sellSuccess }}</AlertDescription>
        </Alert>

        <div class="space-y-2">
          <Label for="sell_qty" class="text-[#38618C] font-semibold text-sm">Quantity to Sell</Label>
          <Input
            id="sell_qty"
            v-model="sellQuantity"
            type="number"
            :min="0"
            :max="selectedAsset ? getAvailableQuantity(selectedAsset.crypto.symbol) : 0"
            step="0.00000001"
            placeholder="0.00000000"
            class="border-[#38618C] focus:border-[#35A7FF] font-mono text-sm"
            :disabled="isSelling"
          />
          <div class="text-xs text-gray-500">
            Max: {{ selectedAsset ? formatNumber(getAvailableQuantity(selectedAsset.crypto.symbol), 8) : '0' }} {{ selectedAsset?.crypto.symbol?.toUpperCase() }}
          </div>
        </div>

        <!-- Summary -->
        <Card class="border-gray-200 bg-gray-50">
          <CardContent class="p-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-500">Estimated Price</span>
              <span class="font-semibold text-[#38618C]">{{ formatCurrency(selectedAsset?.price || 0) }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-500">Quantity</span>
              <span class="font-mono font-semibold text-[#38618C]">{{ sellQuantity || '0' }}</span>
            </div>
            <div class="border-t border-gray-200 pt-2 flex justify-between">
              <span class="font-semibold text-[#38618C]">Estimated Total</span>
              <span class="font-bold text-[#35A7FF]">
                {{ formatCurrency((parseFloat(sellQuantity) || 0) * (selectedAsset?.price || 0)) }}
              </span>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <DialogFooter class="flex gap-2">
      <Button
        variant="outline"
        @click="closeSellDialog"
        class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 text-sm"
        :disabled="isSelling"
      >
        ✕ Cancel
      </Button>
      <Button
        @click="confirmSell"
        class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white font-semibold flex-1 text-sm"
        :disabled="isSelling || !sellQuantity || parseFloat(sellQuantity) <= 0"
      >
        {{ isSelling ? '⏳ Selling...' : '✓ Confirm' }}
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>

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

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:bg-\[#FF5964\]\/90:hover) {
  background-color: rgba(255, 89, 100, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}
</style>