<script setup lang="ts">
import { ref, computed, toRefs } from 'vue'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'

interface Props {
  cryptos: any[]
  loading: boolean
  currentPage: number
  totalPages: number
}

interface Emits {
  (e: 'viewDetails', id: number): void
  (e: 'editCrypto', crypto: any): void
  (e: 'deleteCrypto', crypto: any): void
  (e: 'changePage', page: number): void
  (e: 'refresh'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// States locaux
const query = ref('')
const sortBy = ref<'name'|'price'|'change'>('price')
const viewMode = ref<'grid'|'list'>('grid')

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
  if (sortBy.value === 'name') {
    list.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
  } else if (sortBy.value === 'price') {
    list.sort((a, b) => Number(b.price_eur || 0) - Number(a.price_eur || 0))
  } else if (sortBy.value === 'change') {
    list.sort((a, b) => Number(b.change_24h_pct || 0) - Number(a.change_24h_pct || 0))
  }
  
  return list
})

// add image error handler
function handleImgError(e: Event) {
  const target = e.target as HTMLImageElement | null
  if (target) target.style.display = 'none'
}
</script>

<template>
  <div class="space-y-4">
    <!-- Filtres et recherche -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col md:flex-row gap-4 items-start md:items-center justify-between flex-wrap">
          <div class="relative flex-1 max-w-md">
            <Input
              v-model="query"
              placeholder="🔍 Search by name or symbol..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <div class="flex flex-wrap gap-3 w-full md:w-auto items-center">
            <Select v-model="sortBy">
              <SelectTrigger class="w-full md:w-40 border-[#38618C]">
                <SelectValue placeholder="Sort by" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="price">Price ↓</SelectItem>
                <SelectItem value="change">24h Change ↓</SelectItem>
                <SelectItem value="name">Name A–Z</SelectItem>
              </SelectContent>
            </Select>

            <div class="flex border border-[#38618C] rounded-md overflow-hidden items-center">
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'grid' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'grid'"
                aria-label="grid view"
              >
                ⊞
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'list' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'list'"
                aria-label="list view"
              >
                ☰
              </Button>
            </div>

            <Button 
              @click="emit('refresh')" 
              :disabled="loading"
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
            >
              🔄
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Chargement des cryptomonnaies...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredCryptos.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">Aucune crypto trouvée</h3>
        <p class="text-gray-500 mb-6">
          {{ query ? 'Essayez de modifier votre recherche' : 'Ajoutez votre première cryptomonnaie' }}
        </p>
      </CardContent>
    </Card>

    <!-- Vue Grid -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card 
        v-for="crypto in filteredCryptos" 
        :key="crypto.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-xl cursor-pointer group"
        @click="emit('viewDetails', crypto.id)"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full border-2 border-gray-300 group-hover:border-[#35A7FF] transition-colors bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img 
                  :src="crypto.image_url || crypto.image" 
                  :alt="crypto.name"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="handleImgError"
                />
                <div v-if="!crypto.image_url && !crypto.image" class="text-xl">💎</div>
              </div>
              <div>
                <h3 class="font-bold text-[#38618C] text-lg">{{ crypto.name }}</h3>
                <Badge class="bg-[#35A7FF] text-white font-mono">
                  {{ String(crypto.symbol || '').toUpperCase() }}
                </Badge>
              </div>
            </div>
            <Badge 
              :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
              class="text-white font-semibold"
            >
              {{ formatPercentage(crypto.change_24h_pct) }}%
            </Badge>
          </div>

          <div class="space-y-3 mb-4">
            <div>
              <div class="text-sm text-gray-500 mb-1">Current price</div>
              <div class="text-2xl font-bold text-[#35A7FF]">
                {{ formatCurrency(crypto.price_eur) }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-3 text-sm">
              <div>
                <div class="text-gray-500">Market Cap</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</div>
              </div>
              <div>
                <div class="text-gray-500">Volume 24h</div>
                <div class="font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }}</div>
              </div>
            </div>
          </div>

          <div class="flex gap-2">
            <Button 
              @click.stop="emit('viewDetails', crypto.id)"
              class="flex-1 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
            >
              📊 details
            </Button>
            <Button 
              variant="outline"
              @click.stop="emit('editCrypto', crypto)"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
            >
              ✏️
            </Button>
            <Button 
              variant="outline"
              @click.stop="emit('deleteCrypto', crypto)"
              class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
            >
              🗑️
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Vue List -->
    <div v-else class="space-y-3">
      <Card 
        v-for="crypto in filteredCryptos" 
        :key="crypto.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer"
        @click="emit('viewDetails', crypto.id)"
      >
        <CardContent class="p-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <div class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img 
                  :src="crypto.image_url || crypto.image" 
                  :alt="crypto.name"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="handleImgError"
                />
                <div v-if="!crypto.image_url && !crypto.image" class="text-lg">💎</div>
              </div>
              <div class="min-w-0 flex-1">
                <h3 class="font-bold text-[#38618C] truncate">{{ crypto.name }}</h3>
                <Badge class="bg-[#35A7FF] text-white font-mono">
                  {{ String(crypto.symbol || '').toUpperCase() }}
                </Badge>
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 flex-1">
              <div>
                <div class="text-xs text-gray-500">Price</div>
                <div class="font-bold text-[#35A7FF]">{{ formatCurrency(crypto.price_eur) }}</div>
              </div>
              <div>
                <div class="text-xs text-gray-500">24h</div>
                <Badge 
                  :class="Number(crypto.change_24h_pct || 0) >= 0 ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                  class="text-white"
                >
                  {{ formatPercentage(crypto.change_24h_pct) }}%
                </Badge>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs text-gray-500">Market Cap</div>
                <div class="text-sm font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.market_cap) }}</div>
              </div>
              <div class="hidden sm:block">
                <div class="text-xs text-gray-500">Volume</div>
                <div class="text-sm font-semibold text-[#38618C]">{{ formatLargeNumber(crypto.volume_24h) }}</div>
              </div>
            </div>

            <div class="flex gap-2 w-full sm:w-auto">
              <Button 
                size="sm"
                @click.stop="emit('viewDetails', crypto.id)"
                class="flex-1 sm:flex-none bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              >
                📊 details
              </Button>
              <Button 
                size="sm"
                variant="outline"
                @click.stop="emit('editCrypto', crypto)"
                class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
              >
                ✏️
              </Button>
              <Button 
                size="sm"
                variant="outline"
                @click.stop="emit('deleteCrypto', crypto)"
                class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
              >
                🗑️
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages && totalPages > 1" class="flex justify-center gap-2 mt-6 flex-wrap">
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        @click="emit('changePage', currentPage - 1)"
        class="border-[#38618C] text-[#38618C]"
      >
        ← Previous
      </Button>
      
      <div class="flex gap-1 flex-wrap">
        <Button
          v-for="page in totalPages"
          :key="page"
          :variant="page === currentPage ? 'default' : 'outline'"
          size="sm"
          @click="emit('changePage', page)"
          :class="page === currentPage ? 'bg-[#35A7FF] text-white' : 'border-[#38618C] text-[#38618C]'"
        >
          {{ page }}
        </Button>
      </div>

      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        @click="emit('changePage', currentPage + 1)"
        class="border-[#38618C] text-[#38618C]"
      >
        Next →
      </Button>
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

:deep(.bg-\[#35A7FF\]) {
  background-color: #35A7FF;
}

:deep(.text-\[#35A7FF\]) {
  color: #35A7FF;
}

:deep(.bg-\[#01FF19\]) {
  background-color: #01FF19;
}

:deep(.bg-\[#FF5964\]) {
  background-color: #FF5964;
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:bg-\[#FF5964\]:hover) {
  background-color: #FF5964;
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}

:deep(.border-\[#FF5964\]) {
  border-color: #FF5964;
}

:deep(.hover\:text-white:hover) {
  color: white;
}
</style>