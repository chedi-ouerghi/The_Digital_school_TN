<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

const router = useRouter()
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const search = ref('')
const page = ref(1)
const total = ref(0)
const viewMode = ref<'grid' | 'table'>('table')
const statusFilter = ref<'all' | 'completed' | 'cancelled'>('all')
const itemsPerPage = 10

// États pour l'annulation
const confirmDialog = ref(false)
const selectedTransaction = ref<any>(null)
const cancelReason = ref('')

// Utility functions
function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR'
  }).format(value)
}

function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
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

// Computed - Statistics
const stats = computed(() => {
  const totalTransactions = transactions.value.length
  const totalVolume = transactions.value
    .filter(t => !t.cancelled_at)
    .reduce((sum, t) => sum + Number(t.total_eur || 0), 0)
  const completed = transactions.value.filter(t => !t.cancelled_at).length
  const cancelled = transactions.value.filter(t => t.cancelled_at).length

  return {
    totalTransactions,
    totalVolume,
    completed,
    cancelled
  }
})

// Computed - Pagination
const totalPages = computed(() => Math.ceil(filteredTransactions.value.length / itemsPerPage))

const paginatedTransactions = computed(() => {
  const start = (page.value - 1) * itemsPerPage
  const end = start + itemsPerPage
  return filteredTransactions.value.slice(start, end)
})

async function fetchTransactions() {
  loading.value = true
  error.value = null
  try {
    const response = await api.admin.transactions.list({ page: page.value })
    transactions.value = response.data
    total.value = response.total
  } catch (err: any) {
    error.value = err.message || 'Error loading transactions'
    console.error('Error fetching transactions:', err)
  } finally {
    loading.value = false
  }
}

const filteredTransactions = computed(() => {
  let filtered = transactions.value

  // Filter by status
  if (statusFilter.value === 'completed') {
    filtered = filtered.filter(t => !t.cancelled_at)
  } else if (statusFilter.value === 'cancelled') {
    filtered = filtered.filter(t => t.cancelled_at)
  }

  // Filter by search
  if (search.value) {
    const searchLower = search.value.toLowerCase()
    filtered = filtered.filter(t => 
      t.portefeuille?.user?.name?.toLowerCase().includes(searchLower) ||
      t.portefeuille?.cryptomoney?.symbole?.toLowerCase().includes(searchLower) ||
      t.portefeuille?.cryptomoney?.nom?.toLowerCase().includes(searchLower)
    )
  }
  
  return filtered
})

function viewDetails(id: number) {
  router.push(`/dashboard/admin/transactions/${id}`)
}

function openCancelDialog(transaction: any) {
  selectedTransaction.value = transaction
  confirmDialog.value = true
}

async function handleCancel() {
  if (!selectedTransaction.value) return
  
  try {
    await api.admin.transactions.cancel(
      selectedTransaction.value.id,
      cancelReason.value || 'Administrative cancellation'
    )
    await fetchTransactions()
    confirmDialog.value = false
    selectedTransaction.value = null
    cancelReason.value = ''
  } catch (err: any) {
    error.value = err.message
  }
}

function nextPage() {
  if (page.value < totalPages.value) {
    page.value++
  }
}

function prevPage() {
  if (page.value > 1) {
    page.value--
  }
}

function goToPage(pageNum: number) {
  page.value = pageNum
}

onMounted(fetchTransactions)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Transaction Management</h1>
        <p class="text-gray-500">Monitor and manage all platform transactions</p>
      </div>
      <Button 
        @click="fetchTransactions"
        :disabled="loading"
        class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
      >
        🔄 Refresh
      </Button>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">📊</div>
          <div class="text-sm text-gray-500 mb-1">Total Transactions</div>
          <div class="text-3xl font-bold text-[#35A7FF]">
            {{ stats.totalTransactions }}
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">💰</div>
          <div class="text-sm text-gray-500 mb-1">Total Volume</div>
          <div class="text-2xl font-bold text-[#01FF19]">
            {{ formatCurrency(stats.totalVolume) }}
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-gray-200 hover:border-[#38618C] transition-colors bg-gradient-to-br from-[#38618C]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">✅</div>
          <div class="text-sm text-gray-500 mb-1">Completed</div>
          <div class="text-3xl font-bold text-[#38618C]">
            {{ stats.completed }}
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-gray-200 hover:border-[#FF5964] transition-colors bg-gradient-to-br from-[#FF5964]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">❌</div>
          <div class="text-sm text-gray-500 mb-1">Cancelled</div>
          <div class="text-3xl font-bold text-[#FF5964]">
            {{ stats.cancelled }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters and Search -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div class="flex flex-col sm:flex-row gap-4 flex-1">
            <div class="relative flex-1 max-w-md">
              <Input
                v-model="search"
                placeholder="🔍 Search by client, crypto symbol or name..."
                class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
              />
            </div>

            <Select v-model="statusFilter">
              <SelectTrigger class="w-full sm:w-40 border-[#38618C]">
                <SelectValue placeholder="Filter by status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="completed">✅ Completed</SelectItem>
                <SelectItem value="cancelled">❌ Cancelled</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="flex gap-3 w-full sm:w-auto">
            <div class="flex border border-[#38618C] rounded-md overflow-hidden">
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'table' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'table'"
              >
                ☰ Table
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'grid' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'grid'"
              >
                ⊞ Grid
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Loading transactions...</div>
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
          @click="fetchTransactions"
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
        >
          Try Again
        </Button>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredTransactions.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">📭</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">No Transactions Found</h3>
        <p class="text-gray-500">
          {{ search || statusFilter !== 'all' ? 'Try adjusting your search criteria' : 'No transactions available yet' }}
        </p>
      </CardContent>
    </Card>

    <!-- Results Count -->
    <div v-else class="flex items-center justify-between">
      <div class="text-sm text-gray-600">
        Showing {{ Math.min((page - 1) * itemsPerPage + 1, filteredTransactions.length) }} 
        to {{ Math.min(page * itemsPerPage, filteredTransactions.length) }} 
        of {{ filteredTransactions.length }} transactions
      </div>
    </div>

    <!-- Grid View -->
    <div v-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card 
        v-for="t in paginatedTransactions" 
        :key="t.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-xl cursor-pointer group"
        :class="{'opacity-75': t.cancelled_at}"
        @click="viewDetails(t.id)"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img
                  v-if="t.cryptomoney?.image_url"
                  :src="t.cryptomoney.image_url"
                  :alt="t.cryptomoney?.name"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="(e) => {
                    const target = e.target as HTMLImageElement
                    if (target && target.parentNode) {
                      target.style.display = 'none'
                    }
                  }"
                />
                <div v-if="!t.cryptomoney?.image_url" class="text-lg">💎</div>
              </div>
              <div>
                <h3 class="font-bold text-[#38618C]">{{ t.cryptomoney?.name }}</h3>
                <Badge class="bg-[#35A7FF] text-white font-mono">
                  {{ String(t.cryptomoney?.symbol || '').toUpperCase() }}
                </Badge>
              </div>
            </div>
            <Badge 
              :class="t.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
              class="text-white font-semibold"
            >
              {{ t.type === 'ACHAT' ? 'ACHAT' : 'VENTE' }}
            </Badge>
          </div>

          <div class="space-y-3 mb-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Client</div>
              <div class="font-semibold text-[#38618C]">{{ t.user?.name || 'USER' }}</div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-gray-500">Quantity</div>
                <div class="font-semibold text-[#38618C] font-mono">
                  {{ formatNumber(t.quantity) }}
                </div>
              </div>
              <div>
                <div class="text-gray-500">Unit Price</div>
                <div class="font-semibold text-[#35A7FF]">{{ formatCurrency(t.price) }}</div>
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Total Amount</div>
              <div class="text-2xl font-bold text-[#01FF19]">
                {{ formatCurrency(t.total_eur) }}
              </div>
            </div>

            <div>
              <div class="text-sm text-gray-500 mb-1">Date & Time</div>
              <div class="font-semibold text-[#38618C]">{{ formatDate(t.created_at) }}</div>
            </div>
          </div>

          <div class="flex gap-2 pt-4 border-t border-gray-200">
            <Button 
              @click.stop="viewDetails(t.id)"
              class="flex-1 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
            >
              📊 details
            </Button>
            <Button 
              v-if="!t.cancelled_at"
              variant="outline"
              @click.stop="openCancelDialog(t)"
              class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
            >
              🗑️
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Table View -->
    <Card v-else>
      <CardContent class="p-0">
        <Table>
          <TableHeader>
           <TableRow class="bg-[#38618C]/10">
  <TableHead class="text-[#38618C] font-semibold">Date & Time</TableHead>
  <TableHead class="text-[#38618C] font-semibold">Client</TableHead>
  <TableHead class="text-[#38618C] font-semibold">Type</TableHead>
  <TableHead class="text-[#38618C] font-semibold">Crypto</TableHead>
  <TableHead class="text-[#38618C] font-semibold text-right">Quantity</TableHead>
  <TableHead class="text-[#38618C] font-semibold text-right">Price</TableHead>
  <TableHead class="text-[#38618C] font-semibold text-right">Total</TableHead>
  <TableHead class="text-[#38618C] font-semibold">Status</TableHead>
  <TableHead class="text-[#38618C] font-semibold text-right">Actions</TableHead>
</TableRow>

          </TableHeader>
          <TableBody>
            <TableRow 
              v-for="t in paginatedTransactions" 
              :key="t.id"
              :class="{'opacity-75 bg-[#FF5964]/5': t.cancelled_at, 'hover:bg-[#35A7FF]/5': !t.cancelled_at}"
              class="transition-colors"
            >
              <TableCell class="font-medium text-[#38618C]">
                {{ formatDate(t.created_at) }}
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 rounded-full bg-gradient-to-br from-[#35A7FF] to-[#38618C] flex items-center justify-center text-white text-xs font-bold">
                    {{ t.user?.name?.charAt(0)?.toUpperCase() || 'U' }}
                  </div>
                  <div>
                    <div class="font-semibold text-[#38618C]">{{ t.user?.name }}</div>
                    <div class="text-xs text-gray-500">{{ t.user?.email }}</div>
                  </div>
                </div>
              </TableCell>
              <TableCell>
                <Badge 
                  :class="t.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white font-semibold"
                >
                  {{ t.type === 'ACHAT' ? '📈 ACHAT' : '📉 VENTE' }}
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex items-center gap-2">
                  <div class="h-8 w-8 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                    <img 
                      v-if="t.cryptomoney?.image_url"
                      :src="t.cryptomoney.image_url"
                      :alt="t.cryptomoney?.symbol"
                      class="w-8 h-8 rounded-full object-cover"
                      @error="(e) => e.target.style.display = 'none'"
                    />
                    <div v-if="!t.cryptomoney?.image_url" class="text-xs">💎</div>
                  </div>
                  <div>
                    <div class="font-semibold text-[#38618C]">
                      {{ String(t.cryptomoney?.symbol || '').toUpperCase() }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ t.cryptomoney?.name }}
                    </div>
                  </div>
                </div>
              </TableCell>
              <TableCell class="text-right font-mono text-[#38618C] font-semibold">
                {{ formatNumber(t.quantity) }}
              </TableCell>
              <TableCell class="text-right font-mono text-[#35A7FF] font-semibold">
                {{ formatCurrency(t.price) }}
              </TableCell>
              <TableCell class="text-right font-mono font-bold text-[#01FF19] text-lg">
                {{ formatCurrency(t.total_eur) }}
              </TableCell>
              <TableCell>
                <Badge 
                  v-if="t.cancelled_at"
                  class="bg-[#FF5964] text-white font-semibold"
                  :title="t.cancel_reason"
                >
                  ❌ Cancel
                </Badge>
                <Badge 
                  v-else
                  class="bg-[#01FF19] text-white font-semibold"
                >
                  ✅ Completed
                </Badge>
              </TableCell>
              <TableCell>
                <div class="flex justify-end gap-2">
                  <Button
                    variant="outline"
                    size="sm"
                    @click="viewDetails(t.id)"
                    class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white font-semibold"
                  >
                    📊 details
                  </Button>
                  <Button
                    v-if="!t.cancelled_at"
                    variant="outline"
                    size="sm"
                    @click="openCancelDialog(t)"
                    class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white font-semibold"
                  >
                    🗑️ Cancel
                  </Button>
                </div>
              </TableCell>
            </TableRow>
          </TableBody>
        </Table>
      </CardContent>
    </Card>

    <!-- Pagination -->
    <div v-if="filteredTransactions.length > 0 && totalPages > 1" class="flex items-center justify-between mt-6 pt-4 border-t border-gray-200">
      <div class="text-sm text-gray-600">
        Page {{ page }} of {{ totalPages }} • 
        {{ filteredTransactions.length }} total transactions
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          :disabled="page <= 1"
          @click="prevPage"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white disabled:opacity-50"
        >
          ← Previous
        </Button>
        
        <div class="flex items-center gap-1">
          <Button
            v-for="pageNum in Math.min(5, totalPages)"
            :key="pageNum"
            :variant="page === pageNum ? 'default' : 'outline'"
            size="sm"
            @click="goToPage(pageNum)"
            :class="page === pageNum ? 'bg-[#35A7FF] text-white' : 'border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white'"
          >
            {{ pageNum }}
          </Button>
          <span v-if="totalPages > 5" class="text-sm text-gray-500 px-2">...</span>
        </div>

        <Button
          variant="outline"
          size="sm"
          :disabled="page >= totalPages"
          @click="nextPage"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white disabled:opacity-50"
        >
          Next →
        </Button>
      </div>
    </div>

    <!-- Cancel Confirmation Dialog -->
    <ConfirmDialog
      :open="confirmDialog"
      title="⚠️ Cancel Transaction"
      :message="`Are you sure you want to cancel transaction #${selectedTransaction?.id}? This action cannot be undone.`"
      @close="confirmDialog = false"
      @confirm="handleCancel"
    />
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