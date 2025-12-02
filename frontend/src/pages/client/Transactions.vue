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
import { Card, CardContent } from '@/components/ui/card'
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
  if (!isFinite(n) || isNaN(n)) return '$0.00'
  return n.toLocaleString('en-US', { style: 'currency', currency: 'USD' })
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
      throw new Error('Wallet not available')
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
    error.value = e.message || 'Error loading transactions'
    console.error('Error:', e)
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
    sellError.value = 'Please enter a valid quantity'
    return
  }

  if (qty > available) {
    sellError.value = `Insufficient quantity. Available: ${formatNumber(available, 8)} ${selectedAsset.value.crypto.symbol.toUpperCase()}`
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

    sellSuccess.value = 'Sale completed successfully!'
    setTimeout(async () => {
      await loadTransactions()
      closeSellDialog()
    }, 1500)

  } catch (e: any) {
    sellError.value = e?.message || 'Error during sale'
  } finally {
    isSelling.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Transaction History</h1>
        <p class="text-gray-500">Complete record of your trades</p>
      </div>
      <Button
        @click="loadTransactions"
        :disabled="loading"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold w-full sm:w-auto"
      >
        🔄 Refresh
      </Button>
    </div>

    <!-- Quick Stats - Buy/Sell Only -->
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4">
      <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Total Buys</div>
          <div class="text-lg md:text-xl font-bold text-[#01FF19] truncate">
            {{ stats.countBuys }}
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ formatCurrency(stats.totalBuys) }}</div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#FF5964] transition-colors bg-gradient-to-br from-[#FF5964]/10 to-transparent">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Total Sells</div>
          <div class="text-lg md:text-xl font-bold text-[#FF5964] truncate">
            {{ stats.countSells }}
          </div>
          <div class="text-xs text-gray-500 mt-1">{{ formatCurrency(stats.totalSells) }}</div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#38618C] transition-colors">
        <CardContent class="p-4">
          <div class="text-xs text-gray-500 mb-1">Total Transactions</div>
          <div class="text-lg md:text-xl font-bold text-[#38618C]">
            {{ transactions.length }}
          </div>
          <div class="text-xs text-gray-500 mt-1">
            {{ stats.countCancelled > 0 ? `${stats.countCancelled} cancelled` : 'All valid' }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Alert Cancelled Transactions -->
    <Alert v-if="stats.countCancelled > 0" class="border-[#FF5964] bg-[#FF5964]/10">
      <AlertDescription class="text-[#FF5964] text-sm">
        ⚠️ {{ stats.countCancelled }} transaction{{ stats.countCancelled > 1 ? 's' : '' }} cancelled. Refund in 24h.
      </AlertDescription>
    </Alert>

    <!-- Filters -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-3">
          <div class="relative flex-1">
            <Input
              v-model="searchQuery"
              placeholder="🔍 Search by name or symbol..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF] text-sm"
            />
          </div>

          <Select v-model="filterType">
            <SelectTrigger class="w-full sm:w-40 border-[#38618C] text-sm">
              <SelectValue placeholder="Filter" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">All Transactions</SelectItem>
              <SelectItem value="buy">📈 Buys Only</SelectItem>
              <SelectItem value="sell">📉 Sells Only</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div class="text-sm">Loading transactions...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="text-4xl mb-4">❌</div>
        <div class="text-sm text-[#FF5964] mb-4">{{ error }}</div>
        <Button @click="loadTransactions" class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white text-sm">
          Retry
        </Button>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredTransactions.length === 0">
      <CardContent class="p-8 sm:p-12 text-center">
        <div class="text-4xl sm:text-6xl mb-4">📋</div>
        <h3 class="text-lg sm:text-xl font-semibold text-[#38618C] mb-2">
          {{ searchQuery || filterType !== 'all' ? 'No results' : 'No transactions yet' }}
        </h3>
        <p class="text-sm text-gray-500">
          {{ searchQuery || filterType !== 'all'
            ? 'Try adjusting your filters'
            : 'Start buying or selling crypto to see your transactions'
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
                    {{ tx.type === 'buy' ? '📈 BUY' : '📉 SELL' }}
                  </Badge>
                  <Badge v-if="tx.cancelled" class="bg-[#FF5964] text-white text-xs">❌ Cancelled</Badge>
                </div>
                <div class="font-bold text-[#38618C] text-sm">{{ tx.crypto.name }}</div>
                <div class="text-xs text-gray-500 font-mono">{{ String(tx.crypto.symbol).toUpperCase() }}</div>
              </div>

              <div class="text-right">
                <div class="text-sm font-bold text-[#35A7FF]">{{ formatCurrency(tx.total) }}</div>
                <div class="text-xs text-gray-500">
                  {{ new Date(tx.date).toLocaleDateString('en-GB', { month: 'short', day: 'numeric' }) }}
                </div>
              </div>
            </div>

            <div class="bg-gray-50 rounded p-3 space-y-2 text-sm">
              <div class="flex justify-between">
                <span class="text-gray-500">Quantity</span>
                <span class="font-mono font-semibold text-[#38618C]">{{ formatNumber(tx.quantity, 8) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-500">Price</span>
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
                <div class="text-xs text-gray-500 font-mono">{{ String(tx.crypto.symbol).toUpperCase() }}</div>
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
                  day: 'numeric'
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
              </div>
            </CardContent>
          </Card>

          <!-- Available Quantity -->
          <div class="bg-[#01FF19]/10 rounded-lg p-4 border border-[#01FF19]">
            <div class="text-sm text-gray-600 mb-1">Available to Sell</div>
            <div class="text-2xl font-bold text-[#01FF19]">
              {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto.symbol), 8) }}
              <span class="text-sm text-gray-600 ml-2">{{ String(selectedAsset?.crypto.symbol).toUpperCase() }}</span>
            </div>
          </div>

          <!-- Quantity Input -->
          <div class="space-y-2">
            <Label class="text-[#38618C]">Quantity to Sell</Label>
            <Input
              v-model="sellQuantity"
              type="number"
              placeholder="0.00000000"
              step="0.00000001"
              :max="getAvailableQuantity(selectedAsset?.crypto.symbol)"
              class="border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <!-- Error Alert -->
          <Alert v-if="sellError" class="border-[#FF5964] bg-[#FF5964]/10">
            <AlertDescription class="text-[#FF5964] text-sm">
              {{ sellError }}
            </AlertDescription>
          </Alert>

          <!-- Success Alert -->
          <Alert v-if="sellSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
            <AlertDescription class="text-[#01FF19] text-sm">
              ✅ {{ sellSuccess }}
            </AlertDescription>
          </Alert>
        </div>

        <DialogFooter class="gap-2">
          <Button
            @click="closeSellDialog"
            variant="outline"
            class="border-[#38618C] text-[#38618C]"
          >
            Cancel
          </Button>
          <Button
            @click="confirmSell"
            :disabled="isSelling || !sellQuantity"
            class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white"
          >
            {{ isSelling ? '⏳ Selling...' : '💰 Confirm Sale' }}
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