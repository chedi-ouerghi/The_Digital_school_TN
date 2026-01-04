<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogTrigger,
} from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
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
const sortBy = ref<'name'|'price'|'change'>('price')
const viewMode = ref<'grid'|'list'>('grid')
const syncLoading = ref(false)

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

function handleImgError(e: Event) {
  const target = e.target as HTMLImageElement | null
  if (target) target.style.display = 'none'
}

async function handleSyncHistory() {
  if (syncLoading.value) return
  syncLoading.value = true
  try {
    await api.crypto.syncHistory()
    emit('refresh')
  } catch (err: any) {
    const msg = err?.message || 'Erreur lors de la synchronisation'
    window.alert(msg)
  } finally {
    syncLoading.value = false
  }
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
                aria-label="grid view"
                @click="viewMode = 'grid'"
              >
                ⊞
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'list' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                aria-label="list view"
                @click="viewMode = 'list'"
              >
                ☰
              </Button>
            </div>

            <Button 
              :disabled="loading" 
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              @click="emit('refresh')"
            >
              🔄
            </Button>

            <!-- Bouton Sync history avec AlertDialog -->
            <AlertDialog>
              <AlertDialogTrigger as-child>
                <Button
                  :disabled="loading || syncLoading"
                  class="relative bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold overflow-hidden group"
                  title="Synchroniser l'historique (admin)"
                >
                  <div class="absolute inset-0 bg-gradient-to-r from-[#01FF19]/20 to-transparent translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000"></div>
                  
                  <span class="relative flex items-center gap-2">
                    <span v-if="syncLoading" class="animate-spin">⏳</span>
                    <span v-else>🔁</span>
                    <span>Sync history</span>
                  </span>
                </Button>
              </AlertDialogTrigger>
              <AlertDialogContent>
                <AlertDialogHeader>
                  <AlertDialogTitle class="text-[#38618C]">Synchronisation de l'historique</AlertDialogTitle>
                  <AlertDialogDescription>
                    Cette action va synchroniser les données historiques pour toutes les cryptomonnaies.
                    <br><br>
                    <strong>Note :</strong> Cette opération peut prendre quelques instants.
                  </AlertDialogDescription>
                </AlertDialogHeader>
                <AlertDialogFooter>
                  <AlertDialogCancel class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white">
                    Annuler
                  </AlertDialogCancel>
                  <AlertDialogAction 
                    :disabled="syncLoading"
                    class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
                    @click="handleSyncHistory"
                  >
                    <span v-if="syncLoading" class="flex items-center gap-2">
                      <span class="animate-spin">⏳</span>
                      Synchronisation...
                    </span>
                    <span v-else>Confirmer la synchronisation</span>
                  </AlertDialogAction>
                </AlertDialogFooter>
              </AlertDialogContent>
            </AlertDialog>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Loading cryptos...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredCryptos.length === 0">
      <CardContent class="p-12 text-center">
        <div class="text-6xl mb-4">🔍</div>
        <h3 class="text-xl font-semibold text-[#38618C] mb-2">Aucune crypto trouvée</h3>
        <p class="text-gray-500 mb-6">
          {{ query ? 'Essayez de modifier votre recherche' : 'Aucune cryptomonnaie disponible' }}
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

          <Button 
            class="w-full bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
            @click.stop="emit('viewDetails', crypto.id)"
          >
            📊 View Details
          </Button>
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

            <Button 
              size="sm"
              class="w-full sm:w-auto bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              @click.stop="emit('viewDetails', crypto.id)"
            >
              📊 View Details
            </Button>
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
        class="border-[#38618C] text-[#38618C]"
        @click="emit('changePage', currentPage - 1)"
      >
        ← Previous
      </Button>
      
      <div class="flex gap-1 flex-wrap">
        <Button
          v-for="page in totalPages"
          :key="page"
          :variant="page === currentPage ? 'default' : 'outline'"
          size="sm"
          :class="page === currentPage ? 'bg-[#35A7FF] text-white' : 'border-[#38618C] text-[#38618C]'"
          @click="emit('changePage', page)"
        >
          {{ page }}
        </Button>
      </div>

      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        class="border-[#38618C] text-[#38618C]"
        @click="emit('changePage', currentPage + 1)"
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

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}

:deep(.hover\:text-white:hover) {
  color: white;
}

@keyframes pulse-glow {
  0%, 100% {
    box-shadow: 0 0 5px #01FF19;
  }
  50% {
    box-shadow: 0 0 20px #01FF19;
  }
}

:deep(.group:hover .bg-\[#01FF19\]) {
  animation: pulse-glow 1.5s ease-in-out infinite;
}
</style>