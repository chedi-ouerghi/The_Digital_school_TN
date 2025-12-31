<script setup lang="ts">
  import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table'
import { computed, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

const router = useRouter()
const transactions = ref<any[]>([])
const loading = ref(false)
const error = ref<string | null>(null)
const search = ref('')
const page = ref(1)
const total = ref(0)
const viewMode = ref<'grid' | 'table'>('table')
const statusFilter = ref<'all' | 'completed' | 'cancelled'>('all')
const confirmDialog = ref(false)
const selectedTransaction = ref<any>(null)
const cancelReason = ref('')
const itemsPerPage = 10

// Formatters
const formatCurrency = (value: number): string => 
  new Intl.NumberFormat('en-US', { style: 'currency', currency: 'EUR' }).format(value)

const formatDate = (date: string): string => 
  new Date(date).toLocaleDateString('en-US', {
    day: '2-digit',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })

const formatNumber = (value: any, decimals = 8): string => {
  const n = Number(value ?? 0)
  return isFinite(n) && !isNaN(n) ? n.toFixed(decimals) : '0'
}

const getProfilePicture = (profilePicture: string | null | undefined): string | undefined => {
  if (!profilePicture) return undefined
  return profilePicture.startsWith('http') 
    ? profilePicture 
    : `http://localhost:8000/storage/${profilePicture}`
}

// Computed properties
const stats = computed(() => ({
  total: transactions.value.length,
  volume: transactions.value
    .filter(t => !t.cancelled_at)
    .reduce((sum, t) => sum + Number(t.total_eur || 0), 0),
  completed: transactions.value.filter(t => !t.cancelled_at).length,
  cancelled: transactions.value.filter(t => t.cancelled_at).length
}))

const filteredTransactions = computed(() => {
  let filtered = transactions.value

  if (statusFilter.value === 'completed') {
    filtered = filtered.filter(t => !t.cancelled_at)
  } else if (statusFilter.value === 'cancelled') {
    filtered = filtered.filter(t => t.cancelled_at)
  }

  if (search.value) {
    const term = search.value.toLowerCase()
    filtered = filtered.filter(t => {
      const user = t.crypto_wallet_asset?.wallet?.user
      const crypto = t.crypto_wallet_asset?.cryptomoney
      return (
        user?.name?.toLowerCase().includes(term) ||
        crypto?.symbol?.toLowerCase().includes(term) ||
        crypto?.name?.toLowerCase().includes(term)
      )
    })
  }
  
  return filtered
})

const totalPages = computed(() => 
  Math.ceil(filteredTransactions.value.length / itemsPerPage)
)

const paginatedTransactions = computed(() => {
  const start = (page.value - 1) * itemsPerPage
  return filteredTransactions.value.slice(start, start + itemsPerPage)
})

const showingRange = computed(() => ({
  from: (page.value - 1) * itemsPerPage + 1,
  to: Math.min(page.value * itemsPerPage, filteredTransactions.value.length)
}))

// Functions
const fetchTransactions = async () => {
  loading.value = true
  error.value = null
  
  try {
    const response = await api.admin.transactions.list({ page: page.value })
    transactions.value = response.data
    total.value = response.total || 0
  } catch (err: any) {
    error.value = err.message || 'Failed to load transactions'
    console.error('Error:', err)
  } finally {
    loading.value = false
  }
}

const viewDetails = (id: number) => {
  router.push(`/dashboard/admin/transactions/${id}`)
}

const openCancelDialog = (transaction: any) => {
  selectedTransaction.value = transaction
  cancelReason.value = ''
  confirmDialog.value = true
}

const handleCancel = async () => {
  if (!selectedTransaction.value || !cancelReason.value.trim()) {
    error.value = 'Please provide a cancellation reason'
    return
  }
  
  try {
    await api.admin.transactions.cancel(
      selectedTransaction.value.id,
      cancelReason.value.trim()
    )
    await fetchTransactions()
    confirmDialog.value = false
    selectedTransaction.value = null
    cancelReason.value = ''
  } catch (err: any) {
    error.value = err.message
  }
}

const changePage = (newPage: number) => {
  if (newPage >= 1 && newPage <= totalPages.value) {
    page.value = newPage
  }
}

const resetFilters = () => {
  search.value = ''
  statusFilter.value = 'all'
  page.value = 1
}

onMounted(fetchTransactions)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Transaction History</h1>
        <p class="text-gray-500">Manage and monitor all platform transactions</p>
      </div>
      <div class="flex gap-2">
        <Button 
          variant="outline"
          class="border-gray-300"
          :disabled="loading"
          @click="resetFilters"
        >
          Reset Filters
        </Button>
        <Button 
          class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
          :disabled="loading"
          @click="fetchTransactions"
        >
          <span v-if="loading">Loading...</span>
          <span v-else>Refresh Data</span>
        </Button>
      </div>
    </div>

    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card
v-for="(stat, key) in [
        { label: 'Total Transactions', value: stats.total, icon: '📊', color: '#35A7FF' },
        { label: 'Total Volume', value: formatCurrency(stats.volume), icon: '💰', color: '#01FF19' },
        { label: 'Completed', value: stats.completed, icon: '✅', color: '#38618C' },
        { label: 'Cancelled', value: stats.cancelled, icon: '❌', color: '#FF5964' }
      ]" :key="key">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">{{ stat.icon }}</div>
          <div class="text-sm text-gray-500 mb-1">{{ stat.label }}</div>
          <div class="text-2xl font-bold" :style="{ color: stat.color }">
            {{ stat.value }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div class="flex flex-col sm:flex-row gap-4 flex-1">
            <div class="relative flex-1 max-w-md">
              <Input
                v-model="search"
                placeholder="Search by client or cryptocurrency..."
                class="pl-3"
              />
            </div>
            <Select v-model="statusFilter">
              <SelectTrigger class="w-full sm:w-40">
                <SelectValue placeholder="Status Filter" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Status</SelectItem>
                <SelectItem value="completed">Completed</SelectItem>
                <SelectItem value="cancelled">Cancelled</SelectItem>
              </SelectContent>
            </Select>
          </div>
          <div class="flex border rounded-md overflow-hidden">
            <Button
              variant="ghost"
              size="sm"
              :class="viewMode === 'table' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
              @click="viewMode = 'table'"
            >
              Table View
            </Button>
            <Button
              variant="ghost"
              size="sm"
              :class="viewMode === 'grid' ? 'bg-blue-100 text-blue-700' : 'text-gray-600'"
              @click="viewMode = 'grid'"
            >
              Grid View
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Results Summary -->
    <div v-if="filteredTransactions.length > 0" class="text-sm text-gray-600">
      Showing {{ showingRange.from }} to {{ showingRange.to }} of {{ filteredTransactions.length }} transactions
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
      <p class="mt-4 text-gray-600">Loading transactions...</p>
    </div>

    <!-- Error State -->
    <Card v-else-if="error" class="border-red-200 bg-red-50">
      <CardContent class="p-6 text-center">
        <div class="text-red-500 font-semibold mb-2">Error</div>
        <p class="text-gray-600 mb-4">{{ error }}</p>
        <Button class="bg-red-500 hover:bg-red-600 text-white" @click="fetchTransactions">
          Retry
        </Button>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredTransactions.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-4xl mb-4">📭</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No transactions found</h3>
        <p class="text-gray-500">
          {{ search || statusFilter !== 'all' ? 'Try adjusting your search filters' : 'No transactions available' }}
        </p>
      </CardContent>
    </Card>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card 
        v-for="t in paginatedTransactions" 
        :key="t.id"
        class="cursor-pointer hover:shadow-lg transition-shadow"
        @click="viewDetails(t.id)"
      >
        <CardContent class="p-4">
          <div class="flex justify-between items-start mb-3">
            <div class="flex items-center gap-2">
              <img
                v-if="t.crypto_wallet_asset?.cryptomoney?.image_url"
                :src="t.crypto_wallet_asset.cryptomoney.image_url"
                :alt="t.crypto_wallet_asset.cryptomoney.symbol"
                class="w-8 h-8 rounded-full"
              />
              <span class="font-semibold">
                {{ t.crypto_wallet_asset?.cryptomoney?.symbol?.toUpperCase() }}
              </span>
            </div>
            <Badge :class="t.type === 'ACHAT' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
              {{ t.type === 'ACHAT' ? 'BUY' : 'SELL' }}
            </Badge>
          </div>
          
          <div class="space-y-2 mb-4">
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-full bg-gray-200 flex items-center justify-center text-xs">
                {{ t.crypto_wallet_asset?.wallet?.user?.name?.charAt(0) }}
              </div>
              <span class="text-sm">{{ t.crypto_wallet_asset?.wallet?.user?.name }}</span>
            </div>
            
            <div class="grid grid-cols-2 gap-2 text-sm">
              <div>
                <div class="text-gray-500">Quantity</div>
                <div class="font-mono">{{ formatNumber(t.quantity) }}</div>
              </div>
              <div>
                <div class="text-gray-500">Price</div>
                <div>{{ formatCurrency(t.price) }}</div>
              </div>
            </div>
            
            <div class="border-t pt-2">
              <div class="text-gray-500 text-sm">Total</div>
              <div class="font-bold text-lg">{{ formatCurrency(t.total_eur) }}</div>
            </div>
          </div>
          
          <div class="flex justify-between items-center">
            <span class="text-sm text-gray-500">{{ formatDate(t.created_at) }}</span>
            <Button 
              v-if="!t.cancelled_at"
              size="sm"
              variant="ghost"
              @click.stop="openCancelDialog(t)"
            >
              Cancel
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Table View -->
    <div v-else class="overflow-x-auto">
      <Table>
        <TableHeader>
          <TableRow>
            <TableHead>Date</TableHead>
            <TableHead>Client</TableHead>
            <TableHead>Type</TableHead>
            <TableHead>Cryptocurrency</TableHead>
            <TableHead class="text-right">Quantity</TableHead>
            <TableHead class="text-right">Price</TableHead>
            <TableHead class="text-right">Total</TableHead>
            <TableHead>Status</TableHead>
            <TableHead class="text-right">Actions</TableHead>
          </TableRow>
        </TableHeader>
        <TableBody>
          <TableRow 
            v-for="t in paginatedTransactions" 
            :key="t.id"
            class="hover:bg-gray-50"
          >
            <TableCell class="whitespace-nowrap">{{ formatDate(t.created_at) }}</TableCell>
            <TableCell>
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-full bg-gray-200 flex items-center justify-center">
                  {{ t.crypto_wallet_asset?.wallet?.user?.name?.charAt(0) }}
                </div>
                <div>
                  <div class="font-medium">{{ t.crypto_wallet_asset?.wallet?.user?.name }}</div>
                  <div class="text-xs text-gray-500">{{ t.crypto_wallet_asset?.wallet?.user?.email }}</div>
                </div>
              </div>
            </TableCell>
            <TableCell>
              <Badge :class="t.type === 'ACHAT' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'">
                {{ t.type === 'ACHAT' ? 'BUY' : 'SELL' }}
              </Badge>
            </TableCell>
            <TableCell>
              <div class="flex items-center gap-2">
                <img
                  v-if="t.crypto_wallet_asset?.cryptomoney?.image_url"
                  :src="t.crypto_wallet_asset.cryptomoney.image_url"
                  :alt="t.crypto_wallet_asset.cryptomoney.symbol"
                  class="w-6 h-6"
                />
                <div>
                  <div>{{ t.crypto_wallet_asset?.cryptomoney?.symbol?.toUpperCase() }}</div>
                  <div class="text-xs text-gray-500">{{ t.crypto_wallet_asset?.cryptomoney?.name }}</div>
                </div>
              </div>
            </TableCell>
            <TableCell class="text-right font-mono">{{ formatNumber(t.quantity) }}</TableCell>
            <TableCell class="text-right">{{ formatCurrency(t.price) }}</TableCell>
            <TableCell class="text-right font-bold">{{ formatCurrency(t.total_eur) }}</TableCell>
            <TableCell>
              <Badge :class="t.cancelled_at ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'">
                {{ t.cancelled_at ? 'Cancelled' : 'Completed' }}
              </Badge>
            </TableCell>
            <TableCell class="text-right">
              <div class="flex gap-2 justify-end">
                <Button size="sm" variant="outline" @click="viewDetails(t.id)">
                  View Details
                </Button>
                <Button 
                  v-if="!t.cancelled_at" 
                  size="sm"
                  variant="destructive"
                  @click="openCancelDialog(t)"
                >
                  Cancel
                </Button>
              </div>
            </TableCell>
          </TableRow>
        </TableBody>
      </Table>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex items-center justify-between">
      <div class="text-sm text-gray-600">
        Page {{ page }} of {{ totalPages }}
      </div>
      <div class="flex gap-2">
        <Button
          variant="outline"
          :disabled="page <= 1"
          @click="changePage(page - 1)"
        >
          Previous
        </Button>
        <div class="flex items-center gap-1">
          <Button
            v-for="p in Math.min(5, totalPages)"
            :key="p"
            :variant="page === p ? 'default' : 'outline'"
            size="sm"
            @click="changePage(p)"
          >
            {{ p }}
          </Button>
        </div>
        <Button
          variant="outline"
          :disabled="page >= totalPages"
          @click="changePage(page + 1)"
        >
          Next
        </Button>
      </div>
    </div>

    <!-- Cancel Dialog -->
    <Dialog v-model:open="confirmDialog">
      <DialogContent>
        <DialogHeader>
          <DialogTitle>Cancel Transaction</DialogTitle>
          <DialogDescription>
            This action cannot be undone. Please provide a reason for cancellation.
          </DialogDescription>
        </DialogHeader>
        
        <div class="space-y-4 py-4">
          <Input
            v-model="cancelReason"
            placeholder="Cancellation reason..."
          />
        </div>
        
        <DialogFooter>
          <Button variant="outline" @click="confirmDialog = false">
            Cancel
          </Button>
          <Button variant="destructive" @click="handleCancel">
            Confirm Cancellation
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
  </div>
</template>