<script setup lang="ts">
import { ref } from 'vue'
import {
    Image as ImageIcon,
    Upload,
    X,
    Trash2,
    Save
} from 'lucide-vue-next'
import api from '../../../services/api'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'

interface Props {
  profile: any
}

interface Emits {
  (e: 'media-updated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Avatar & Banner Management
const avatarFile = ref<File | null>(null)
const bannerFile = ref<File | null>(null)
const avatarPreview = ref(props.profile?.profile_picture ? 
  `http://localhost:8000/storage/${props.profile.profile_picture}` : 
  ''
)
const bannerPreview = ref(props.profile?.profile_banner ? 
  `http://localhost:8000/storage/${props.profile.profile_banner}` : 
  ''
)
const uploadLoading = ref(false)

// Handle file uploads
function handleAvatarUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    avatarFile.value = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(avatarFile.value)
  }
}

function handleBannerUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    bannerFile.value = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => {
      bannerPreview.value = e.target?.result as string
    }
    reader.readAsDataURL(bannerFile.value)
  }
}

// Upload images
async function uploadImages() {
  if (!avatarFile.value && !bannerFile.value) return
  
  uploadLoading.value = true
  try {
    if (avatarFile.value) {
      const formData = new FormData()
      formData.append('profile_picture', avatarFile.value)
      await api.auth.uploadProfilePicture(formData)
    }
    
    if (bannerFile.value) {
      const formData = new FormData()
      formData.append('profile_banner', bannerFile.value)
      await api.auth.uploadProfileBanner(formData)
    }
    
    // Reset files after successful upload
    avatarFile.value = null
    bannerFile.value = null
    emit('media-updated')
  } catch (e: any) {
    console.error('Upload failed:', e)
  } finally {
    uploadLoading.value = false
  }
}

// Remove images
function removeAvatar() {
  avatarPreview.value = ''
  avatarFile.value = null
}

function removeBanner() {
  bannerPreview.value = ''
  bannerFile.value = null
}
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Profile Picture Card -->
    <Card class="border border-gray-200">
      <CardHeader>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center">
            <ImageIcon class="h-5 w-5 text-purple-600" />
          </div>
          <div>
            <CardTitle class="text-lg font-semibold">Profile Picture</CardTitle>
            <CardDescription>
              Upload a new profile picture
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-6">
        <Alert class="border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100">
          <AlertDescription class="text-blue-800 text-sm">
            Recommended: Square image, 256x256px, JPG or PNG format, max 2MB
          </AlertDescription>
        </Alert>

        <div class="flex flex-col items-center space-y-6">
          <!-- Avatar Preview -->
          <div class="relative">
            <Avatar class="h-32 w-32 border-4 border-white shadow-lg">
              <AvatarImage :src="avatarPreview" />
              <AvatarFallback class="bg-gradient-to-br from-blue-500 to-blue-600 text-white text-2xl font-semibold">
                {{ profile?.name?.charAt(0)?.toUpperCase() || 'A' }}
              </AvatarFallback>
            </Avatar>
            <Button
              v-if="avatarPreview"
              variant="destructive"
              size="sm"
              class="absolute -top-2 -right-2 h-8 w-8 rounded-full p-0"
              @click="removeAvatar"
            >
              <X class="h-4 w-4" />
            </Button>
          </div>

          <!-- File Input -->
          <div class="w-full space-y-3">
            <div class="relative">
              <Input 
                type="file" 
                accept="image/*" 
                class="cursor-pointer border-dashed"
                @change="handleAvatarUpload"
              />
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="flex items-center gap-2 text-gray-500">
                  <Upload class="h-4 w-4" />
                  <span class="text-sm">Choose file</span>
                </div>
              </div>
            </div>
            
            <div class="flex gap-2">
              <Button 
                :disabled="!avatarFile || uploadLoading" 
                class="bg-gradient-to-r from-purple-500 to-purple-600 hover:from-purple-600 hover:to-purple-700 text-white gap-2 flex-1"
                @click="uploadImages"
              >
                <Save class="h-4 w-4" />
                {{ uploadLoading ? 'Uploading...' : 'Upload Picture' }}
              </Button>
              
              <Button 
                variant="outline" 
                class="border-gray-300 text-gray-700 hover:bg-gray-50"
                @click="removeAvatar"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Profile Banner Card -->
    <Card class="border border-gray-200">
      <CardHeader>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-pink-50 to-pink-100 flex items-center justify-center">
            <ImageIcon class="h-5 w-5 text-pink-600" />
          </div>
          <div>
            <CardTitle class="text-lg font-semibold">Profile Banner</CardTitle>
            <CardDescription>
              Upload a banner image for your profile
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-6">
        <Alert class="border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100">
          <AlertDescription class="text-blue-800 text-sm">
            Recommended: 1200x300px, JPG or PNG format, max 5MB
          </AlertDescription>
        </Alert>

        <div class="space-y-6">
          <!-- Banner Preview -->
          <div class="relative">
            <div 
              class="w-full h-40 bg-gradient-to-r from-gray-100 to-gray-200 rounded-lg border-2 border-dashed border-gray-300 overflow-hidden"
              :class="{ 'border-blue-300': bannerPreview }"
            >
              <img 
                v-if="bannerPreview" 
                :src="bannerPreview" 
                class="w-full h-full object-cover"
                alt="Banner preview"
              />
              <div v-else class="w-full h-full flex items-center justify-center">
                <div class="text-center text-gray-500">
                  <ImageIcon class="h-8 w-8 mx-auto mb-2" />
                  <p class="text-sm">No banner uploaded</p>
                </div>
              </div>
            </div>
            
            <Button
              v-if="bannerPreview"
              variant="destructive"
              size="sm"
              class="absolute -top-2 -right-2 h-8 w-8 rounded-full p-0"
              @click="removeBanner"
            >
              <X class="h-4 w-4" />
            </Button>
          </div>

          <!-- File Input -->
          <div class="space-y-3">
            <div class="relative">
              <Input 
                type="file" 
                accept="image/*" 
                class="cursor-pointer border-dashed"
                @change="handleBannerUpload"
              />
              <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                <div class="flex items-center gap-2 text-gray-500">
                  <Upload class="h-4 w-4" />
                  <span class="text-sm">Choose file</span>
                </div>
              </div>
            </div>
            
            <div class="flex gap-2">
              <Button 
                :disabled="!bannerFile || uploadLoading" 
                class="bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white gap-2 flex-1"
                @click="uploadImages"
              >
                <Save class="h-4 w-4" />
                {{ uploadLoading ? 'Uploading...' : 'Upload Banner' }}
              </Button>
              
              <Button 
                variant="outline" 
                class="border-gray-300 text-gray-700 hover:bg-gray-50"
                @click="removeBanner"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>