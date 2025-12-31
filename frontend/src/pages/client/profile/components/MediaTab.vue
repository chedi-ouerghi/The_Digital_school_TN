<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Label } from '@/components/ui/label'
import api, { API_BASE } from '@/services/api'
import { onMounted, ref } from 'vue'

// ---------------------------------------------------------------------
// State
// ---------------------------------------------------------------------
const avatarFile = ref<File | null>(null)
const bannerFile = ref<File | null>(null)

const avatarPreview = ref<string>('') // backend + preview
const bannerPreview = ref<string>('')

const uploadLoading = ref(false)
const message = ref<string | null>(null)

const profile = ref<any>(null)

// ---------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------
function apiBaseStorageUrl() {
  try {
    const u = new URL(API_BASE.replace('/api/v1', ''))
    return u.origin
  } catch {
    return 'http://localhost:8000'
  }
}

function storageUrl(path?: string | null) {
  if (!path) return ''
  if (path.startsWith('http')) return path
  return `${apiBaseStorageUrl()}/storage/${path.replace(/^\/+/, '')}`
}

// ---------------------------------------------------------------------
// Load profile (CORRECT METHOD)
// ---------------------------------------------------------------------
async function fetchProfile() {
  try {
    const res = await api.auth.profile()
    profile.value = res.user || res

    if (profile.value?.profile_picture) {
      avatarPreview.value = storageUrl(profile.value.profile_picture)
    }

    if (profile.value?.profile_banner) {
      bannerPreview.value = storageUrl(profile.value.profile_banner)
    }
  } catch (e: any) {
    console.error('Error loading profile:', e)
  }
}

onMounted(fetchProfile)

// ---------------------------------------------------------------------
// Handlers
// ---------------------------------------------------------------------
const handleAvatarUpload = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.[0]) return

  avatarFile.value = input.files[0]
  const reader = new FileReader()
  reader.onload = e => (avatarPreview.value = String(e.target?.result || ''))
  reader.readAsDataURL(avatarFile.value)
}

const handleBannerUpload = (event: Event) => {
  const input = event.target as HTMLInputElement
  if (!input.files?.[0]) return

  bannerFile.value = input.files[0]
  const reader = new FileReader()
  reader.onload = e => (bannerPreview.value = String(e.target?.result || ''))
  reader.readAsDataURL(bannerFile.value)
}

const uploadImages = async () => {
  if (!avatarFile.value && !bannerFile.value) return

  uploadLoading.value = true
  message.value = null

  try {
    if (avatarFile.value) {
      const form = new FormData()
      form.append('profile_picture', avatarFile.value)
      await api.auth.uploadProfilePicture(form)
    }

    if (bannerFile.value) {
      const form = new FormData()
      form.append('profile_banner', bannerFile.value)
      await api.auth.uploadProfileBanner(form)
    }

    message.value = 'Media uploaded successfully'
    avatarFile.value = null
    bannerFile.value = null

    // Reload images from backend
    await fetchProfile()

    setTimeout(() => (message.value = null), 3000)
  } catch (e: any) {
    console.error('Upload failed:', e)
    message.value = e?.message || 'Upload failed'
  } finally {
    uploadLoading.value = false
  }
}

const removeAvatarPreview = () => {
  avatarPreview.value = ''
  avatarFile.value = null
}

const removeBannerPreview = () => {
  bannerPreview.value = ''
  bannerFile.value = null
}
</script>

<template>
  <Card class="border-gray-200 shadow-lg">
    <CardHeader>
      <CardTitle class="text-[#38618C]">Media</CardTitle>
    </CardHeader>

    <CardContent>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Avatar -->
        <div class="space-y-3">
          <Label>Avatar</Label>
          <div class="flex items-center gap-4">
            <div
              class="w-28 h-28 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center"
            >
              <img
                v-if="avatarPreview"
                :src="avatarPreview"
                class="w-full h-full object-cover"
              />
              <div v-else class="text-gray-400">No avatar</div>
            </div>

            <div class="space-y-2">
              <input type="file" accept="image/*" @change="handleAvatarUpload" />
              <Button variant="outline" @click="removeAvatarPreview">
                Remove
              </Button>
            </div>
          </div>
        </div>

        <!-- Banner -->
        <div class="space-y-3">
          <Label>Banner</Label>
          <div
            class="w-full h-40 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center"
          >
            <img
              v-if="bannerPreview"
              :src="bannerPreview"
              class="w-full h-full object-cover"
            />
            <div v-else class="text-gray-400">No banner</div>
          </div>

          <input type="file" accept="image/*" @change="handleBannerUpload" />
          <Button variant="outline" @click="removeBannerPreview">
            Remove
          </Button>
        </div>
      </div>

      <div class="mt-6 flex items-center gap-4">
        <Button
          :disabled="uploadLoading || (!avatarFile && !bannerFile)"
          class="bg-[#35A7FF] hover:bg-[#38618C] text-white"
          @click="uploadImages"
        >
          {{ uploadLoading ? 'Uploading...' : 'Upload Media' }}
        </Button>

        <span v-if="message" class="text-sm text-green-600">
          {{ message }}
        </span>
      </div>
    </CardContent>
  </Card>
</template>
