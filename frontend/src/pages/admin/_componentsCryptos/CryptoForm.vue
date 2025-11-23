<script setup lang="ts">
import { ref, watch } from 'vue'
import api from '../../../services/api'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  DialogContent,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogDescription,
} from '@/components/ui/dialog'

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
const newCryptoId = ref('')
const cryptoImage = ref<File | null>(null)
const imagePreview = ref<string>('')

// Surveiller les changements de props pour mettre à jour le formulaire
watch(() => props.editingCryptoData, (newData) => {
  if (newData) {
    newCryptoId.value = newData?.coingecko_id || newData?.coingeckoId || ''
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

// Soumission du formulaire
async function handleSubmit() {
  formError.value = ''
  formSuccess.value = ''
  
  if (!newCryptoId.value.trim()) {
    formError.value = 'Veuillez entrer un ID CoinGecko'
    return
  }
  
  formLoading.value = true
  
  try {
    if (props.isEditMode && props.editingCryptoId) {
      // Mode édition
      const formData = new FormData()
      if (cryptoImage.value) {
        formData.append('image', cryptoImage.value)
      }
      
      await api.crypto.updateWithImage(props.editingCryptoId, formData)
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
    
    emit('success')
  } catch (err: any) {
    formError.value = err.message || 'Erreur lors de l\'opération'
  } finally {
    formLoading.value = false
  }
}

function handleCancel() {
  formError.value = ''
  formSuccess.value = ''
  newCryptoId.value = ''
  cryptoImage.value = null
  imagePreview.value = ''
  emit('cancel')
}
</script>

<template>
  <DialogContent class="sm:max-w-md border-[#35A7FF]">
    <DialogHeader>
      <DialogTitle class="text-[#38618C] text-xl">
        {{ isEditMode ? 'Edit Cryptocurrency' : 'Add Cryptocurrency' }}
      </DialogTitle>
      <DialogDescription class="text-gray-600">
        Enter the name of the cryptocurrency to {{ isEditMode ? 'edit' : 'add' }}
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
        <Label for="crypto_id" class="text-[#38618C] font-semibold">Name</Label>
        <Input
          id="crypto_id"
          v-model="newCryptoId"
          placeholder="bitcoin, ethereum, cardano..."
          class="border-[#38618C] focus:border-[#35A7FF]"
          :disabled="formLoading || isEditMode"
        />
        <p class="text-xs text-gray-500">
          💡 Find the name on  
          <a href="https://www.coingecko.com" target="_blank"
             class="text-[#35A7FF] hover:underline">CoinGecko.com</a>
        </p>
      </div>

      <div class="space-y-2">
        <Label for="crypto_image" class="text-[#38618C] font-semibold">
          Cryptocurrency image (optional)
        </Label>
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
            📁 Choose an image
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
          📸 Accepted formats: JPG, PNG, GIF. Max size: 2MB
        </p>
      </div>
    </div>

    <DialogFooter class="flex gap-2 sm:gap-0">
      <Button 
        variant="outline" 
        @click="handleCancel"
        class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
        :disabled="formLoading"
      >
        ✕ Cancel
      </Button>
      <Button 
        @click="handleSubmit"
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold flex-1 sm:flex-none"
        :disabled="formLoading || !newCryptoId.trim()"
      >
        {{ formLoading ? (isEditMode ? '⏳ Updating...' : '⏳ Adding...') : (isEditMode ? '✓ Update' : '✓ Add') }}
      </Button>
    </DialogFooter>
  </DialogContent>
</template>

<style scoped>
:deep(.border-\[#38618C\]) {
  border-color: #38618C;
}

:deep(.text-\[#38618C\]) {
  color: #38618C;
}

:deep(.border-\[#35A7FF\]) {
  border-color: #35A7FF;
}

:deep(.text-\[#35A7FF\]) {
  color: #35A7FF;
}

:deep(.bg-\[#01FF19\]) {
  background-color: #01FF19;
}

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}
</style>