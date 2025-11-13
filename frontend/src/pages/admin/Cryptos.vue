<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'

const router = useRouter()
const cryptos = ref<any[]>([])
const loading = ref(false)
const query = ref('')
const sortBy = ref<'name'|'price'|'change'>('price')
const viewMode = ref<'grid'|'list'>('grid')
const currentPage = ref(1)
const totalPages = ref(1)
const itemsPerPage = 10

const addCryptoDialog = ref(false)
const deleteDialog = ref(false)
const cryptoToDelete = ref<any>(null)
const formLoading = ref(false)
const formError = ref('')
const formSuccess = ref('')
// Référence au fichier image
const cryptoImage = ref<File | null>(null)
const imagePreview = ref<string>('')

// État pour la modification
const isEditMode = ref(false)
const editingCryptoId = ref<number | null>(null)

// Variables du formulaire
const newCryptoId = ref('')

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
async function fetchCryptos() {
  loading.value = true
  try {
    const res = await api.crypto.list(currentPage.value)
    cryptos.value = res.data || []
    // Use total_items, total, or fallback to data length
    const totalItems = res.total_items || res.total || cryptos.value.length
    totalPages.value = Math.ceil(totalItems / itemsPerPage)
  } catch (err: any) {
    console.error('Erreur chargement cryptos:', err)
    cryptos.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCryptos()
})

// Statistiques
const stats = computed(() => ({
  totalCryptos: filteredCryptos.value.length,
  totalMarketCap: filteredCryptos.value.reduce((sum, c) => sum + Number(c.market_cap || 0), 0),
  averageChange: filteredCryptos.value.length > 0 
    ? filteredCryptos.value.reduce((sum, c) => sum + Number(c.change_24h_pct || 0), 0) / filteredCryptos.value.length 
    : 0
}))

// Filtrage et tri
const filteredCryptos = computed(() => {
  let list = cryptos.value.slice()
  
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

// Actions admin
function openAddDialog() {
  newCryptoId.value = ''
  cryptoImage.value = null
  imagePreview.value = ''
  isEditMode.value = false
  editingCryptoId.value = null
  addCryptoDialog.value = true
  formError.value = ''
  formSuccess.value = ''
}

// Fermer la modal d'ajout
function closeAddDialog() {
  addCryptoDialog.value = false
  formError.value = ''
  formSuccess.value = ''
  newCryptoId.value = ''
  cryptoImage.value = null
  imagePreview.value = ''
  isEditMode.value = false
  editingCryptoId.value = null
}

async function handleAddCrypto() {
  formError.value = ''
  formSuccess.value = ''
  
  if (!newCryptoId.value.trim()) {
    formError.value = 'Veuillez entrer un ID CoinGecko'
    return
  }
  
  formLoading.value = true
  
  try {
    if (isEditMode.value && editingCryptoId.value) {
      // Mode édition - utilisez la méthode updateWithImage
      const formData = new FormData()
      if (cryptoImage.value) {
        formData.append('image', cryptoImage.value)
      }
      
      await api.crypto.updateWithImage(editingCryptoId.value, formData)
      formSuccess.value = 'Crypto modifiée avec succès!'
    } else {
      // Mode ajout
      const formData = new FormData()
      formData.append('crypto_id', newCryptoId.value)
      if (cryptoImage.value) {
        formData.append('image', cryptoImage.value)
      }
      
      await api.crypto.createFromCoinGecko(formData)
      formSuccess.value = 'Crypto ajoutée avec succès!'
    }
    
    await fetchCryptos()
    
    // Réinitialiser le formulaire
    newCryptoId.value = ''
    cryptoImage.value = null
    imagePreview.value = ''
    isEditMode.value = false
    editingCryptoId.value = null
    
    setTimeout(() => {
      addCryptoDialog.value = false
      formSuccess.value = ''
    }, 1500)
  } catch (err: any) {
    formError.value = err.message || 'Erreur lors de l\'opération'
  } finally {
    formLoading.value = false
  }
}

function handleImageUpload(event: Event) {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    cryptoImage.value = file
    const reader = new FileReader()
    reader.onload = (e) => {
      imagePreview.value = e.target?.result as string
    }
    reader.readAsDataURL(file)
  }
}

function triggerFileInput() {
  const fileInput = document.getElementById('crypto_image') as HTMLInputElement
  if (fileInput) {
    fileInput.click()
  }
}

// Éditer une crypto existante
async function editCrypto(crypto: any) {
  try {
    // Use the SDK/api wrapper to fetch crypto details (safe and consistent)
    const cryptoData = await api.crypto.show(crypto.id)

    // Fill the form fields with fallback keys if necessary
    newCryptoId.value = cryptoData?.coingecko_id || cryptoData?.coingeckoId || ''
    cryptoImage.value = null
    imagePreview.value = cryptoData?.image_url || cryptoData?.image || ''
    editingCryptoId.value = cryptoData?.id || crypto.id
    isEditMode.value = true
    addCryptoDialog.value = true

    formError.value = ''
    formSuccess.value = ''
  } catch (err: any) {
    console.error('Erreur lors du chargement des données:', err)
    formError.value = 'Erreur lors du chargement des données de la crypto'
  }
}

function confirmDeleteCrypto(crypto: any) {
  cryptoToDelete.value = crypto
  deleteDialog.value = true
}

async function handleDeleteCrypto() {
  if (!cryptoToDelete.value?.id) return
  
  try {
    await api.crypto.delete(cryptoToDelete.value.id)
    deleteDialog.value = false
    cryptoToDelete.value = null
    await fetchCryptos()
  } catch (err: any) {
    alert(err.message || 'Erreur lors de la suppression')
  }
}

function viewDetails(id: number) {
  router.push(`/dashboard/admin/cryptos/${id}`)
}

function changePage(page: number) {
  currentPage.value = page
  fetchCryptos()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Gestion des Cryptomonnaies</h1>
        <p class="text-gray-500">Administration des cryptos disponibles sur la plateforme</p>
      </div>
      <Button 
        @click="openAddDialog"
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
      >
        + Ajouter une Crypto
      </Button>
    </div>

    <!-- Statistiques -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
      <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">💎</div>
          <div class="text-sm text-gray-500 mb-1">Total Cryptos</div>
          <div class="text-3xl font-bold text-[#35A7FF]">
            {{ stats.totalCryptos }}
          </div>
        </CardContent>
      </Card>
      
      <Card class="border-gray-200 hover:border-[#38618C] transition-colors bg-gradient-to-br from-[#38618C]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">📊</div>
          <div class="text-sm text-gray-500 mb-1">Market Cap Total</div>
          <div class="text-2xl font-bold text-[#38618C]">
            {{ formatLargeNumber(stats.totalMarketCap) }}
          </div>
        </CardContent>
      </Card>
      
      <Card 
        class="border-gray-200 transition-colors"
        :class="stats.averageChange >= 0 ? 'hover:border-[#01FF19] bg-gradient-to-br from-[#01FF19]/10' : 'hover:border-[#FF5964] bg-gradient-to-br from-[#FF5964]/10'"
      >
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">{{ stats.averageChange >= 0 ? '📈' : '📉' }}</div>
          <div class="text-sm text-gray-500 mb-1">Variation Moy. 24h</div>
          <div 
            class="text-3xl font-bold"
            :class="stats.averageChange >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
          >
            {{ formatPercentage(stats.averageChange) }}%
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filtres et recherche -->
    <Card>
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div class="relative flex-1 max-w-md">
            <Input
              v-model="query"
              placeholder="🔍 Rechercher par nom ou symbole..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <div class="flex gap-3 w-full sm:w-auto">
            <Select v-model="sortBy">
              <SelectTrigger class="w-full sm:w-40 border-[#38618C]">
                <SelectValue placeholder="Trier par" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="price">Prix ↓</SelectItem>
                <SelectItem value="change">Variation 24h ↓</SelectItem>
                <SelectItem value="name">Nom A-Z</SelectItem>
              </SelectContent>
            </Select>

            <div class="flex border border-[#38618C] rounded-md overflow-hidden">
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'grid' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'grid'"
              >
                ⊞
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'list' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'list'"
              >
                ☰
              </Button>
            </div>

            <Button 
              @click="fetchCryptos" 
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
        <Button 
          v-if="!query"
          @click="openAddDialog"
          class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
        >
          + Ajouter une Crypto
        </Button>
      </CardContent>
    </Card>

    <!-- Vue Grid -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
      <Card 
        v-for="crypto in filteredCryptos" 
        :key="crypto.id"
        class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-xl cursor-pointer group"
        @click="viewDetails(crypto.id)"
      >
        <CardContent class="p-6">
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="h-12 w-12 rounded-full border-2 border-gray-300 group-hover:border-[#35A7FF] transition-colors bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img 
                  :src="crypto.image_url || crypto.image" 
                  :alt="crypto.name"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="(e) => {
                    const target = e.target as HTMLImageElement
                    if (target?.parentNode) {
                      target.style.display = 'none'
                    }
                  }"
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
              <div class="text-sm text-gray-500 mb-1">Prix actuel</div>
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
              @click.stop="viewDetails(crypto.id)"
              class="flex-1 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white font-semibold"
            >
              📊 Détails
            </Button>
            <Button 
              variant="outline"
              @click.stop="editCrypto(crypto)"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
            >
              ✏️
            </Button>
            <Button 
              variant="outline"
              @click.stop="confirmDeleteCrypto(crypto)"
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
        @click="viewDetails(crypto.id)"
      >
        <CardContent class="p-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex items-center gap-4 flex-1 min-w-0">
              <div class="h-12 w-12 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                <img 
                  :src="crypto.image_url || crypto.image" 
                  :alt="crypto.name"
                  class="h-12 w-12 rounded-full object-cover"
                  @error="(e) => {
                    const target = e.target as HTMLImageElement
                    if (target?.parentNode) {
                      target.style.display = 'none'
                    }
                  }"
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
                <div class="text-xs text-gray-500">Prix</div>
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
                @click.stop="viewDetails(crypto.id)"
                class="flex-1 sm:flex-none bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              >
                📊 Détails
              </Button>
              <Button 
                size="sm"
                variant="outline"
                @click.stop="editCrypto(crypto)"
                class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
              >
                ✏️
              </Button>
              <Button 
                size="sm"
                variant="outline"
                @click.stop="confirmDeleteCrypto(crypto)"
                class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
              >
                🗑️
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Modal d'ajout -->
<!-- Modal d'ajout -->
<Dialog :open="addCryptoDialog" @update:open="closeAddDialog">
  <DialogContent class="sm:max-w-md border-[#35A7FF]">
    <DialogHeader>
      <DialogTitle class="text-[#38618C] text-xl">{{ isEditMode ? 'Modifier la Cryptomonnaie' : 'Ajouter une Cryptomonnaie' }}</DialogTitle>
      <DialogDescription class="text-gray-600">
        Entrez l'ID CoinGecko de la crypto à {{ isEditMode ? 'modifier' : 'ajouter' }}
      </DialogDescription>
    </DialogHeader>

    <div class="space-y-4 py-4">
      <Alert v-if="formError" class="border-[#FF5964] bg-[#FF5964]/10">
        <AlertDescription class="text-[#FF5964]">❌ {{ formError }}</AlertDescription>
      </Alert>
      
      <Alert v-if="formSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
        <AlertDescription class="text-[#01FF19]">✅ {{ formSuccess }}</AlertDescription>
      </Alert>

      <div class="space-y-2">
        <Label for="crypto_id" class="text-[#38618C] font-semibold">ID CoinGecko</Label>
        <Input
          id="crypto_id"
          v-model="newCryptoId"
          placeholder="bitcoin, ethereum, cardano..."
          class="border-[#38618C] focus:border-[#35A7FF]"
          :disabled="formLoading"
        />
        <p class="text-xs text-gray-500">
          💡 Trouvez l'ID sur <a href="https://www.coingecko.com" target="_blank" class="text-[#35A7FF] hover:underline">CoinGecko.com</a>
        </p>
      </div>

      <div class="space-y-2">
        <Label for="crypto_image" class="text-[#38618C] font-semibold">Image de la crypto (optionnel)</Label>
        <div class="flex items-center gap-3">
          <input
            id="crypto_image"
            type="file"
            accept="image/*"
            @change="handleImageUpload"
            class="hidden"
            :disabled="formLoading"
          />
          <Button
            type="button"
            @click="triggerFileInput"
            variant="outline"
            class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
            :disabled="formLoading"
          >
            📁 Choisir une image
          </Button>
          <span v-if="cryptoImage" class="text-sm text-gray-600">
            {{ cryptoImage.name }}
          </span>
        </div>
        
        <div v-if="imagePreview" class="mt-2">
          <img 
            :src="imagePreview" 
            alt="Aperçu"
            class="h-16 w-16 rounded-full border-2 border-gray-300 object-cover"
          />
        </div>
        
        <p class="text-xs text-gray-500">
          📸 Formats acceptés : JPG, PNG, GIF. Taille max : 2MB
        </p>
      </div>
    </div>

    <DialogFooter class="flex gap-2 sm:gap-0">
      <Button 
        variant="outline" 
        @click="closeAddDialog"
        class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
        :disabled="formLoading"
      >
        ✕ Annuler
      </Button>
      <Button 
        @click="handleAddCrypto"
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold flex-1 sm:flex-none"
        :disabled="formLoading || !newCryptoId.trim()"
      >
        {{ formLoading ? (isEditMode ? '⏳ Modification en cours...' : '⏳ Ajout en cours...') : (isEditMode ? '✓ Modifier' : '✓ Ajouter') }}
      </Button>
    </DialogFooter>
  </DialogContent>
</Dialog>

    <!-- Modal de suppression -->
    <Dialog :open="deleteDialog" @update:open="deleteDialog = false">
      <DialogContent class="sm:max-w-md border-[#FF5964]">
        <DialogHeader>
          <DialogTitle class="text-[#FF5964] text-xl">⚠️ Confirmer la suppression</DialogTitle>
          <DialogDescription class="text-gray-600">
            Êtes-vous sûr de vouloir supprimer <strong>{{ cryptoToDelete?.name }}</strong> ?
          </DialogDescription>
        </DialogHeader>

        <Alert class="border-[#FF5964] bg-[#FF5964]/10">
          <AlertDescription class="text-[#FF5964]">
            ⚠️ Cette action est irréversible et supprimera toutes les données associées.
          </AlertDescription>
        </Alert>

        <DialogFooter class="flex gap-2 sm:gap-0">
          <Button 
            variant="outline" 
            @click="deleteDialog = false"
            class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
          >
            ✕ Annuler
          </Button>
          <Button 
            @click="handleDeleteCrypto"
            class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white font-semibold flex-1 sm:flex-none"
          >
            🗑️ Supprimer
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex justify-center gap-2 mt-6">
      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === 1"
        @click="changePage(currentPage - 1)"
        class="border-[#38618C] text-[#38618C]"
      >
        ← Précédent
      </Button>
      
      <div class="flex gap-1">
        <Button
          v-for="page in totalPages"
          :key="page"
          :variant="page === currentPage ? 'default' : 'outline'"
          size="sm"
          @click="changePage(page)"
          :class="page === currentPage ? 'bg-[#35A7FF] text-white' : 'border-[#38618C] text-[#38618C]'"
        >
          {{ page }}
        </Button>
      </div>

      <Button
        variant="outline"
        size="sm"
        :disabled="currentPage === totalPages"
        @click="changePage(currentPage + 1)"
        class="border-[#38618C] text-[#38618C]"
      >
        Suivant →
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