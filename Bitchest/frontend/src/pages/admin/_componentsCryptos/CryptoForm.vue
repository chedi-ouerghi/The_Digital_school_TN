<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import {
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { ref, watch } from 'vue'
import api from '../../../services/api'

interface Props {
  isEditMode: boolean
  editingCryptoId: number | null
  editingCryptoData: any
}

interface Emits {
  (e: 'success'): void
  (e: 'cancel'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// États du formulaire
const formLoading = ref(false)
const formError = ref('')
const formSuccess = ref('')

// État des champs du formulaire de cryptomonnaie
const symbol = ref('')
const name = ref('')
const coingeckoId = ref('')
const category = ref('')
const website = ref('')
const marketCap = ref('')
const volume24h = ref('')
const change24hPct = ref('')

const cryptoImage = ref<File | null>(null)
const imagePreview = ref<string>('')

// Surveiller les changements de props pour mettre à jour le formulaire
watch(() => props.editingCryptoData, (newData) => {
  if (newData && props.isEditMode) {
    symbol.value = newData?.symbol || ''
    name.value = newData?.name || ''
    coingeckoId.value = newData?.coingecko_id || ''
    category.value = newData?.category || ''
    website.value = newData?.website || ''
    marketCap.value = newData?.market_cap || ''
    volume24h.value = newData?.volume_24h || ''
    change24hPct.value = newData?.change_24h_pct || ''
    imagePreview.value = newData?.image_url || newData?.image || ''
    cryptoImage.value = null
  }
}, { immediate: true })

// Gestion de l'upload d'image
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

// Validation du formulaire
function validateForm(): string | null {
  if (!symbol.value.trim()) {
    return 'Le symbole est requis (ex: BTC)'
  }
  if (!name.value.trim()) {
    return 'Le nom est requis (ex: Bitcoin)'
  }
  
  // Valider les nombres
  if (marketCap.value && isNaN(Number(marketCap.value))) {
    return 'Market Cap doit être un nombre'
  }
  if (volume24h.value && isNaN(Number(volume24h.value))) {
    return 'Volume 24h doit être un nombre'
  }
  if (change24hPct.value && isNaN(Number(change24hPct.value))) {
    return 'Changement 24h doit être un pourcentage'
  }
  
  return null
}

// Soumission du formulaire
async function handleSubmit() {
  formError.value = ''
  formSuccess.value = ''
  
  const validationError = validateForm()
  if (validationError) {
    formError.value = validationError
    return
  }
  
  formLoading.value = true
  
  try {
    if (props.isEditMode && props.editingCryptoId) {
      // Mise à jour de la cryptomonnaie avec son image éventuelle
      if (cryptoImage.value) {
        // Créer FormData avec tous les champs
        const formData = new FormData()
        formData.append('symbol', symbol.value.toUpperCase())
        formData.append('name', name.value)
        formData.append('coingecko_id', coingeckoId.value || '')
        formData.append('category', category.value || '')
        formData.append('website', website.value || '')
        formData.append('market_cap', marketCap.value ? Number(marketCap.value).toString() : '')
        formData.append('volume_24h', volume24h.value ? Number(volume24h.value).toString() : '')
        formData.append('change_24h_pct', change24hPct.value ? Number(change24hPct.value).toString() : '')
        formData.append('image', cryptoImage.value)
        
        await api.crypto.updateWithImage(props.editingCryptoId.toString(), formData)
      } else {
        // Sinon utiliser update() sans image
        await api.crypto.update(props.editingCryptoId.toString(), {
          symbol: symbol.value.toUpperCase(),
          name: name.value,
          coingecko_id: coingeckoId.value || undefined,
          category: category.value || undefined,
          website: website.value || undefined,
          market_cap: marketCap.value ? Number(marketCap.value) : undefined,
          volume_24h: volume24h.value ? Number(volume24h.value) : undefined,
          change_24h_pct: change24hPct.value ? Number(change24hPct.value) : undefined,
        })
      }
      formSuccess.value = '✅ Crypto modifiée avec succès!'
    } else {
      // Création d'une cryptomonnaie avec les données du formulaire
      await api.crypto.create({
        symbol: symbol.value.toUpperCase(),
        name: name.value,
        coingecko_id: coingeckoId.value || undefined,
        category: category.value || undefined,
        website: website.value || undefined,
        market_cap: marketCap.value ? Number(marketCap.value) : undefined,
        volume_24h: volume24h.value ? Number(volume24h.value) : undefined,
        change_24h_pct: change24hPct.value ? Number(change24hPct.value) : undefined,
        image: cryptoImage.value || undefined,
      })
      formSuccess.value = '✅ Crypto ajoutée avec succès!'
    }
    
    // Réinitialiser le formulaire après le succès
    symbol.value = ''
    name.value = ''
    coingeckoId.value = ''
    category.value = ''
    website.value = ''
    marketCap.value = ''
    volume24h.value = ''
    change24hPct.value = ''
    cryptoImage.value = null
    imagePreview.value = ''
    
    // Emit success et fermer après un délai
    setTimeout(() => {
      emit('success')
    }, 1000)
  } catch (err: any) {
    formError.value = err.message || 'Erreur lors de l\'opération'
  } finally {
    formLoading.value = false
  }
}

function handleCancel() {
  formError.value = ''
  formSuccess.value = ''
  symbol.value = ''
  name.value = ''
  coingeckoId.value = ''
  category.value = ''
  website.value = ''
  marketCap.value = ''
  volume24h.value = ''
  change24hPct.value = ''
  cryptoImage.value = null
  imagePreview.value = ''
  emit('cancel')
}
</script>

<template>
  <DialogContent class="sm:max-w-2xl border-brand-blue max-h-[90vh] overflow-y-auto">
    <DialogHeader>
      <DialogTitle class="text-brand-dark text-xl">
        {{ isEditMode ? '✏️ Modifier la Crypto' : '➕ Ajouter une Crypto' }}
      </DialogTitle>
      <DialogDescription class="text-gray-600">
        {{ isEditMode ? 'Mettez à jour les informations de cette cryptomonnaie' : '✅ Génération automatique de prix (pas besoin de CoinGecko)' }}
      </DialogDescription>
    </DialogHeader>

    <div class="space-y-4 py-4">
      <Alert v-if="formError" class="border-brand-red bg-brand-red/10">
        <AlertDescription class="text-brand-red">❌ {{ formError }}</AlertDescription>
      </Alert>
      
      <Alert v-if="formSuccess" class="border-brand-green bg-brand-green/10">
        <AlertDescription class="text-brand-green">{{ formSuccess }}</AlertDescription>
      </Alert>

      <!-- Ligne 1: Symbol + Name (Requis) -->
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <Label for="symbol" class="text-brand-dark font-semibold">
            Symbole * 
            <span class="text-xs text-gray-500">(ex: BTC)</span>
          </Label>
          <Input
            id="symbol"
            v-model="symbol"
            placeholder="BTC"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>

        <div class="space-y-2">
          <Label for="name" class="text-brand-dark font-semibold">
            Nom * 
            <span class="text-xs text-gray-500">(ex: Bitcoin)</span>
          </Label>
          <Input
            id="name"
            v-model="name"
            placeholder="Bitcoin"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>
      </div>

      <!-- Ligne 2: CoinGecko ID + Category (Optionnel) -->
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <Label for="coingecko_id" class="text-brand-dark font-semibold">
            CoinGecko ID (optionnel)
            <span class="text-xs text-gray-500">(ex: bitcoin)</span>
          </Label>
          <Input
            id="coingecko_id"
            v-model="coingeckoId"
            placeholder="bitcoin"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>

        <div class="space-y-2">
          <Label for="category" class="text-brand-dark font-semibold">
            Catégorie (optionnel)
            <span class="text-xs text-gray-500">(ex: Layer 1)</span>
          </Label>
          <Input
            id="category"
            v-model="category"
            placeholder="Layer 1"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>
      </div>

      <!-- Ligne 3: Website + Market Cap (Optionnel) -->
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <Label for="website" class="text-brand-dark font-semibold">
            Site web (optionnel)
            <span class="text-xs text-gray-500">(URL)</span>
          </Label>
          <Input
            id="website"
            v-model="website"
            placeholder="https://bitcoin.org"
            type="url"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>

        <div class="space-y-2">
          <Label for="market_cap" class="text-brand-dark font-semibold">
            Market Cap € (optionnel)
            <span class="text-xs text-gray-500">(nombre)</span>
          </Label>
          <Input
            id="market_cap"
            v-model="marketCap"
            placeholder="500000000000"
            type="number"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>
      </div>

      <!-- Ligne 4: Volume 24h + Change 24h (Optionnel) -->
      <div class="grid grid-cols-2 gap-4">
        <div class="space-y-2">
          <Label for="volume_24h" class="text-brand-dark font-semibold">
            Volume 24h € (optionnel)
            <span class="text-xs text-gray-500">(nombre)</span>
          </Label>
          <Input
            id="volume_24h"
            v-model="volume24h"
            placeholder="20000000000"
            type="number"
            class="border-brand-dark focus:border-brand-blue"
            :disabled="formLoading"
          />
        </div>

        <div class="space-y-2">
          <Label for="change_24h_pct" class="text-brand-dark font-semibold">
            Changement 24h % (optionnel)
            <span class="text-xs text-gray-500">(auto-généré)</span>
          </Label>
          <Input
            id="change_24h_pct"
            v-model="change24hPct"
            placeholder="2.5"
            type="number"
            step="0.01"
            class="border-brand-dark focus:border-brand-blue bg-gray-100"
            :disabled="true"
          />
        </div>
      </div>

      <!-- Image (Optionnel) -->
      <div class="space-y-2">
        <Label for="crypto_image" class="text-brand-dark font-semibold">
          Image (optionnel)
        </Label>
        <div class="flex items-center gap-3">
          <input
            id="crypto_image"
            type="file"
            accept="image/*"
            class="hidden"
            :disabled="formLoading"
            @change="handleImageUpload"
          />
          <Button
            type="button"
            variant="outline"
            class="border-brand-dark text-brand-dark hover:bg-brand-dark hover:text-white"
            :disabled="formLoading"
            @click="triggerFileInput"
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
            alt="Preview"
            class="h-16 w-16 rounded-full border-2 border-gray-300 object-cover"
          />
        </div>
        
        <p class="text-xs text-gray-500">
          📸 Format accepté: JPG, PNG, WebP. Max: 2MB
        </p>
      </div>

      <!-- Info sur la génération -->
      <div class="bg-blue-50 border border-blue-200 rounded p-3 text-sm text-blue-800">
        <p class="font-semibold">ℹ️ Génération automatique</p>
        <ul class="list-disc list-inside mt-1 text-xs">
          <li>Prix initial: Généré automatiquement</li>
          <li>Historique: 30 jours de données synthétiques</li>
          <li>Changement 24h: Calculé automatiquement</li>
          <li>Devise: EUR (Euros)</li>
        </ul>
      </div>
    </div>

    <DialogFooter class="flex gap-2 sm:gap-0">
      <Button 
        variant="outline" 
        class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
        :disabled="formLoading"
        @click="handleCancel"
      >
        ✕ Annuler
      </Button>
      <Button 
        class="bg-brand-green hover:bg-brand-green/90 text-brand-dark font-semibold flex-1 sm:flex-none"
        :disabled="formLoading || !symbol.trim() || !name.trim()"
        @click="handleSubmit"
      >
        {{ formLoading ? '⏳ Traitement...' : isEditMode ? '✓ Modifier' : '✓ Ajouter' }}
      </Button>
    </DialogFooter>
  </DialogContent>
</template>

<style scoped>
:deep(.border-brand-dark) {
  border-color: #38618C;
}

:deep(.text-brand-dark) {
  color: #38618C;
}

:deep(.border-brand-blue) {
  border-color: #35A7FF;
}

:deep(.text-brand-blue) {
  color: #35A7FF;
}

:deep(.bg-brand-green) {
  background-color: #01FF19;
}

:deep(.hover\:bg-brand-green\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.focus\:border-brand-blue:focus) {
  border-color: #35A7FF;
}

:deep(.hover\:bg-brand-dark:hover) {
  background-color: #38618C;
}
</style>
