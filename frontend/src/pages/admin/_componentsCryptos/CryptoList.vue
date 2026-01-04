<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { computed, ref } from 'vue'
import api from '../../../services/api'
import {
  Coins,
  Search,
  Filter,
  Grid3x3,
  List,
  RefreshCw,
  TrendingUp,
  TrendingDown,
  BarChart3,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight, ExternalLink,
  Clock,
  AlertCircle
} from 'lucide-vue-next'

// Props interface
interface Props {
  cryptos: any[]
  loading: boolean
  currentPage: number
  totalPages: number
}

interface Emits {
  (e: 'viewDetails', id: number): void
  (e: 'changePage', page: number): void
  (e: 'refresh'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// États locaux
const query = ref('')
const sortBy = ref<'name'|'price'|'change'|'market_cap'>('price')
const viewMode = ref<'grid'|'list'>('grid')
const syncLoading = ref(false)

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return new Intl.NumberFormat('en-US', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(n)
}

function formatLargeNumber(value: any): string {
  const n = Number(value ?? 0)
  if (n >= 1e12) return `$${(n / 1e12).toFixed(2)}T`
  if (n >= 1e9) return `$${(n / 1e9).toFixed(2)}B`
  if (n >= 1e6) return `$${(n / 1e6).toFixed(2)}M`
  if (n >= 1e3) return `$${(n / 1e3).toFixed(2)}K`
  return formatCurrency(n)
}

function formatPercentage(value: any): string {
  const n = Number(value ?? 0)
  return `${n >= 0 ? '+' : ''}${n.toFixed(2)}`
}

function getChange24h(crypto: any): number {
  const raw = crypto.change_24h_pct ?? crypto.change_24h ?? 0
  const n = Number(raw)
  return isFinite(n) ? n : 0
}

function getImageUrl(crypto: any): string {
  if (crypto.image_url) return crypto.image_url
  if (crypto.image && crypto.image.startsWith('http')) return crypto.image
  if (crypto.image) return `http://localhost:8000/storage/${crypto.image}`
  return ''
}

// Filtrage et tri
const filteredCryptos = computed(() => {
  let list = props.cryptos.slice()
  
  // Filtrage par recherche
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(c => 
      (c.name || '').toLowerCase().includes(q) || 
      (c.symbol || '').toLowerCase().includes(q)
    )
  }
  
  // Tri
  list.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return (a.name || '').localeCompare(b.name || '')
      case 'price':
        return Number(b.price_eur || b.price || 0) - Number(a.price_eur || a.price || 0)
      case 'change':
        return getChange24h(b) - getChange24h(a)
      case 'market_cap':
        return Number(b.market_cap || b.market_cap_eur || 0) - Number(a.market_cap || a.market_cap_eur || 0)
      default:
        return 0
    }
  })
  
  return list
})

// Gestion des erreurs d'image
function handleImgError(e: Event) {
  const target = e.target as HTMLImageElement
  if (target) {
    target.style.display = 'none'
  }
}

// Synchronisation
async function handleSyncHistory() {
  if (syncLoading.value) return
  syncLoading.value = true
  try {
    await api.crypto.syncHistory()
    emit('refresh')
  } catch (err: any) {
    const msg = err?.message || 'Error during synchronization'
    // You could use a toast notification here instead
    console.error('Sync error:', msg)
  } finally {
    syncLoading.value = false
  }
}
</script>

<template>
  <div class="space-y-6">
    <!-- Filters and Search -->
    <Card class="border border-gray-200">
      <CardHeader class="pb-4">
        <CardTitle class="text-lg font-semibold text-gray-900">Filters & Search</CardTitle>
        <p class="text-sm text-gray-500">Refine cryptocurrency results</p>
      </CardHeader>
      <CardContent>
        <div class="flex flex-col lg:flex-row gap-4">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <Input
                v-model="query"
                placeholder="Search by name or symbol..."
                class="pl-10"
              />
            </div>
          </div>
          
          <!-- Filters -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <Select v-model="sortBy">
              <SelectTrigger>
                <div class="flex items-center gap-2">
                  <Filter class="h-4 w-4" />
                  <SelectValue placeholder="Sort by" />
                </div>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="price">Price (High to Low)</SelectItem>
                <SelectItem value="market_cap">Market Cap</SelectItem>
                <SelectItem value="change">24h Change</SelectItem>
                <SelectItem value="name">Name A-Z</SelectItem>
              </SelectContent>
            </Select>
            
            <!-- View Toggle -->
            <div class="flex border rounded-lg overflow-hidden bg-gray-50">
              <Button
                variant="ghost"
                size="sm"
                :class="[
                  'rounded-none px-4',
                  viewMode === 'grid' 
                    ? 'bg-white text-gray-900 shadow-sm border' 
                    : 'text-gray-600 hover:text-gray-900'
                ]"
                @click="viewMode = 'grid'"
              >
                <Grid3x3 class="h-4 w-4 mr-2" />
                Grid
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="[
                  'rounded-none px-4',
                  viewMode === 'list' 
                    ? 'bg-white text-gray-900 shadow-sm border' 
                    : 'text-gray-600 hover:text-gray-900'
                ]"
                @click="viewMode = 'list'"
              >
                <List class="h-4 w-4 mr-2" />
                List
              </Button>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-2">
              <Button 
                variant="outline"
                size="sm"
                class="gap-2"
                @click="emit('refresh')"
                :disabled="props.loading"
              >
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': props.loading }" />
                Refresh
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Sync History Dialog -->
    <AlertDialog>
      <AlertDialogTrigger as-child>
        <Button
          :disabled="syncLoading"
          class="w-full md:w-auto bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white gap-2"
        >
          <Clock class="h-4 w-4" />
          Sync Historical Data
        </Button>
      </AlertDialogTrigger>
      <AlertDialogContent>
        <AlertDialogHeader>
          <div class="mx-auto w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center mb-4">
            <AlertCircle class="h-6 w-6 text-emerald-600" />
          </div>
          <AlertDialogTitle class="text-center">Sync Historical Data</AlertDialogTitle>
          <AlertDialogDescription class="text-center">
            This will synchronize historical price data for all cryptocurrencies.
            <br><br>
            <strong>Note:</strong> This operation may take a few moments to complete.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel>Cancel</AlertDialogCancel>
          <AlertDialogAction
            :disabled="syncLoading"
            class="bg-emerald-600 hover:bg-emerald-700"
            @click="handleSyncHistory"
          >
            <Clock v-if="syncLoading" class="h-4 w-4 mr-2 animate-spin" />
            {{ syncLoading ? 'Syncing...' : 'Confirm Sync' }}
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>

    <!-- Loading State -->
    <Card v-if="props.loading" class="border border-gray-200">
      <CardContent class="p-12">
        <div class="text-center space-y-4">
          <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
          <div class="space-y-2">
            <p class="text-lg font-medium text-gray-900">Loading Cryptocurrencies</p>
            <p class="text-sm text-gray-500">Fetching latest market data...</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredCryptos.length === 0" class="border border-gray-200">
      <CardContent class="p-12 text-center">
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <Search class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No cryptocurrencies found</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          {{ query ? 'No cryptocurrencies match your search criteria. Try adjusting your search.' : 'No cryptocurrencies available on the platform yet.' }}
        </p>
        <Button 
          v-if="query"
          variant="outline"
          @click="query = ''"
        >
          Clear Search
        </Button>
      </CardContent>
    </Card>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card 
        v-for="crypto in filteredCryptos" 
        :key="crypto.id"
        class="group border border-gray-200 hover:border-blue-300 hover:shadow-xl transition-all duration-300 cursor-pointer"
        @click="emit('viewDetails', crypto.id)"
      >
        <CardContent class="p-6">
          <!-- Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="relative">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-gray-300 group-hover:border-blue-300 transition-colors flex items-center justify-center">
                  <img 
                    v-if="getImageUrl(crypto)"
                    :src="getImageUrl(crypto)"
                    :alt="crypto.name"
                    class="w-10 h-10 rounded-full object-cover"
                    @error="handleImgError"
                  />
                  <div v-else class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <Coins class="h-5 w-5 text-blue-600" />
                  </div>
                </div>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 text-lg">{{ crypto.name }}</h3>
                <Badge variant="outline" class="text-xs font-mono mt-1">
                  {{ String(crypto.symbol || '').toUpperCase() }}
                </Badge>
              </div>
            </div>
            <Badge 
              :class="[
                'font-medium px-2.5 py-0.5',
                getChange24h(crypto) >= 0 
                  ? 'bg-green-50 text-green-700 border-green-200' 
                  : 'bg-red-50 text-red-700 border-red-200'
              ]"
            >
              <component 
                :is="getChange24h(crypto) >= 0 ? TrendingUp : TrendingDown" 
                class="h-3 w-3 mr-1.5" 
              />
              {{ formatPercentage(getChange24h(crypto)) }}%
            </Badge>
          </div>

          <!-- Stats -->
          <div class="space-y-4 mb-6">
            <div>
              <div class="text-sm text-gray-500 mb-1">Current Price</div>
              <div class="text-2xl font-bold text-blue-600">
                {{ formatCurrency(crypto.price_eur || crypto.price) }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-xs text-gray-500 mb-1">Market Cap</div>
                <div class="text-sm font-semibold text-gray-900">
                  {{ formatLargeNumber(crypto.market_cap || crypto.market_cap_eur) }}
                </div>
              </div>
              <div>
                <div class="text-xs text-gray-500 mb-1">Volume (24h)</div>
                <div class="text-sm font-semibold text-gray-900">
                  {{ formatLargeNumber(crypto.volume_24h) }}
                </div>
              </div>
            </div>
          </div>

          <!-- Action Button -->
          <Button 
            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white gap-2"
            @click.stop="emit('viewDetails', crypto.id)"
          >
            <BarChart3 class="h-4 w-4" />
            View Details
          </Button>
        </CardContent>
      </Card>
    </div>

    <!-- List View -->
    <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Cryptocurrency</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Price</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">24h Change</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Market Cap</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Volume (24h)</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr 
              v-for="crypto in filteredCryptos" 
              :key="crypto.id"
              class="hover:bg-gray-50 transition-colors group cursor-pointer"
              @click="emit('viewDetails', crypto.id)"
            >
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-300 flex items-center justify-center">
                    <img 
                      v-if="getImageUrl(crypto)"
                      :src="getImageUrl(crypto)"
                      :alt="crypto.name"
                      class="w-8 h-8 rounded-full object-cover"
                      @error="handleImgError"
                    />
                    <div v-else class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center">
                      <Coins class="h-4 w-4 text-blue-600" />
                    </div>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ crypto.name }}</div>
                    <Badge variant="outline" class="text-xs font-mono">
                      {{ String(crypto.symbol || '').toUpperCase() }}
                    </Badge>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="font-bold text-blue-600">
                  {{ formatCurrency(crypto.price_eur || crypto.price) }}
                </div>
              </td>
              <td class="py-4 px-6">
                <Badge 
                  :class="[
                    'font-medium',
                    getChange24h(crypto) >= 0 
                      ? 'bg-green-50 text-green-700 border-green-200' 
                      : 'bg-red-50 text-red-700 border-red-200'
                  ]"
                >
                  <component 
                    :is="getChange24h(crypto) >= 0 ? TrendingUp : TrendingDown" 
                    class="h-3 w-3 mr-1.5" 
                  />
                  {{ formatPercentage(getChange24h(crypto)) }}%
                </Badge>
              </td>
              <td class="py-4 px-6">
                <div class="text-sm text-gray-900">
                  {{ formatLargeNumber(crypto.market_cap || crypto.market_cap_eur) }}
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="text-sm text-gray-900">
                  {{ formatLargeNumber(crypto.volume_24h) }}
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-8 w-8 p-0"
                    @click.stop="emit('viewDetails', crypto.id)"
                    title="View Details"
                  >
                    <ExternalLink class="h-4 w-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="props.totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
      <div class="text-sm text-gray-600">
        Page {{ props.currentPage }} of {{ props.totalPages }}
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="props.currentPage === 1"
          @click="emit('changePage', 1)"
        >
          <ChevronsLeft class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="props.currentPage === 1"
          @click="emit('changePage', props.currentPage - 1)"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>
        
        <div class="flex items-center gap-1">
          <Button
            v-for="page in Math.min(5, props.totalPages)"
            :key="page"
            :variant="props.currentPage === page ? 'default' : 'outline'"
            size="sm"
            class="h-8 w-8 p-0"
            @click="emit('changePage', page)"
          >
            {{ page }}
          </Button>
        </div>
        
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="props.currentPage === props.totalPages"
          @click="emit('changePage', props.currentPage + 1)"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="props.currentPage === props.totalPages"
          @click="emit('changePage', props.totalPages)"
        >
          <ChevronsRight class="h-4 w-4" />
        </Button>
      </div>
    </div>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>