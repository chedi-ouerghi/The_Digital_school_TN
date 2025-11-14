<script setup lang="ts">
import { ref, onMounted, watch } from 'vue'
import api from '../../services/api'
import { useRouter } from 'vue-router'

// UI Components
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar'
import { Separator } from '@/components/ui/separator'

const router = useRouter()

// Profile state
const profile = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// Active tab
const activeTab = ref('profile')

// Profile form
const name = ref('')
const email = ref('')
const updateLoading = ref(false)
const updateError = ref('')
const updateSuccess = ref('')

// Password form
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passLoading = ref(false)
const passError = ref('')
const passSuccess = ref('')

// Avatar & Banner Management
const avatarFile = ref<File | null>(null)
const bannerFile = ref<File | null>(null)
const avatarPreview = ref('')
const bannerPreview = ref('')
const uploadLoading = ref(false)

// Location Settings
const language = ref('en')
const timezone = ref('Europe/Paris')
const dateFormat = ref('DD/MM/YYYY')
const timeFormat = ref('24h')

// User ID Management (for admin)
const userId = ref('')
const userIdOriginal = ref('')
const userIdLoading = ref(false)

// Available options
const languages = [
  { value: 'en', label: 'English' },
  { value: 'fr', label: 'Français' },
  { value: 'es', label: 'Español' },
  { value: 'de', label: 'Deutsch' }
]

const timezones = [
  'Europe/Paris',
  'Europe/London', 
  'America/New_York',
  'America/Los_Angeles',
  'Asia/Tokyo',
  'Australia/Sydney'
]

const dateFormats = [
  'DD/MM/YYYY',
  'MM/DD/YYYY', 
  'YYYY-MM-DD',
  'DD MMM YYYY'
]

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const resp = await api.auth.profile()
    const user = resp?.user ?? resp
    profile.value = user || null
    name.value = profile.value?.name || ''
    email.value = profile.value?.email || ''
    userId.value = profile.value?.id || profile.value?.user_id || ''
    userIdOriginal.value = userId.value
    
    // Set avatar if exists
    if (profile.value?.avatar_url) {
      avatarPreview.value = profile.value.avatar_url
    }
    if (profile.value?.banner_url) {
      bannerPreview.value = profile.value.banner_url
    }
  } catch (e: any) {
    error.value = e?.message || 'Failed to load profile'
    console.error(e)
  } finally {
    loading.value = false
  }
}

async function updateProfile() {
  updateError.value = ''
  updateSuccess.value = ''
  updateLoading.value = true
  try {
    await api.auth.updateProfile({ name: name.value, email: email.value })
    updateSuccess.value = 'Profile updated successfully'
    await fetchProfile()
  } catch (e: any) {
    updateError.value = e?.message || 'Failed to update profile'
  } finally {
    updateLoading.value = false
  }
}

async function changePassword() {
  passError.value = ''
  passSuccess.value = ''

  if (!currentPassword.value || !newPassword.value) {
    passError.value = 'Please fill all password fields'
    return
  }
  if (newPassword.value !== confirmPassword.value) {
    passError.value = 'New password and confirmation do not match'
    return
  }

  passLoading.value = true
  try {
    await api.auth.changePassword({
      current_password: currentPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value
    })
    passSuccess.value = 'Password changed successfully'
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (e: any) {
    passError.value = e?.message || 'Failed to change password'
  } finally {
    passLoading.value = false
  }
}

// Avatar & Banner Functions
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

async function uploadImages() {
  if (!avatarFile.value && !bannerFile.value) return
  
  uploadLoading.value = true
  try {
    const formData = new FormData()
    if (avatarFile.value) {
      formData.append('avatar', avatarFile.value)
    }
    if (bannerFile.value) {
      formData.append('banner', bannerFile.value)
    }
    
    // Adapt this to your API endpoint
    await api.auth.uploadProfileImages(formData)
    
    // Reset files after successful upload
    avatarFile.value = null
    bannerFile.value = null
    await fetchProfile() // Refresh to get new URLs
  } catch (e: any) {
    console.error('Upload failed:', e)
  } finally {
    uploadLoading.value = false
  }
}

function removeAvatar() {
  avatarPreview.value = ''
  avatarFile.value = null
}

function removeBanner() {
  bannerPreview.value = ''
  bannerFile.value = null
}

// Location Settings Functions
function saveLocationSettings() {
  // Save to backend - implement based on your API
  const settings = {
    language: language.value,
    timezone: timezone.value,
    date_format: dateFormat.value,
    time_format: timeFormat.value
  }
  console.log('Saving location settings:', settings)
  // await api.auth.updateSettings(settings)
}

// User ID Management Functions
async function updateUserId() {
  if (userId.value === userIdOriginal.value) return
  
  userIdLoading.value = true
  try {
    // Adapt to your API
    await api.auth.updateUserId({ user_id: userId.value })
    userIdOriginal.value = userId.value
    // Show success message
  } catch (e: any) {
    console.error('Failed to update user ID:', e)
  } finally {
    userIdLoading.value = false
  }
}

function resetUserId() {
  userId.value = userIdOriginal.value
}

// Reset success messages when changing tabs
watch(activeTab, () => {
  updateSuccess.value = ''
  passSuccess.value = ''
})

onMounted(fetchProfile)
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Admin Settings</h1>
        <p class="text-sm text-gray-600 mt-2">
          Manage your administrator profile, security settings, and platform preferences
        </p>
      </div>
      <Badge class="bg-red-100 text-red-800 border-red-200 px-3 py-1 text-sm font-medium">
        ADMINISTRATOR
      </Badge>
    </div>

    <!-- Loading State -->
    <Card v-if="loading" class="border-gray-200">
      <CardContent class="p-8 text-center">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600 mx-auto"></div>
        <p class="text-gray-600 mt-3">Loading admin profile...</p>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Alert v-else-if="error" class="border-red-200 bg-red-50">
      <AlertDescription class="text-red-800">
        {{ error }}
      </AlertDescription>
      <Button @click="fetchProfile" variant="outline" class="mt-3 border-red-300 text-red-700 hover:bg-red-100">
        Retry Loading
      </Button>
    </Alert>

    <!-- Main Content -->
    <div v-else class="space-y-6">
      <!-- Tabs Navigation -->
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-4 bg-gray-100/50 p-1 rounded-lg">
          <TabsTrigger value="profile" class="rounded-md data-[state=active]:bg-white data-[state=active]:shadow-sm">
            👤 Profile
          </TabsTrigger>
          <TabsTrigger value="media" class="rounded-md data-[state=active]:bg-white data-[state=active]:shadow-sm">
            🖼️ Media
          </TabsTrigger>
          <TabsTrigger value="location" class="rounded-md data-[state=active]:bg-white data-[state=active]:shadow-sm">
            🌎 Location
          </TabsTrigger>
          <TabsTrigger value="advanced" class="rounded-md data-[state=active]:bg-white data-[state=active]:shadow-sm">
            ⚙️ Advanced
          </TabsTrigger>
        </TabsList>

        <!-- Profile Tab -->
        <TabsContent value="profile" class="space-y-6 mt-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Basic Information -->
            <Card class="border-gray-200">
              <CardHeader>
                <CardTitle class="text-lg font-semibold">Basic Information</CardTitle>
                <CardDescription>
                  Update your personal details and contact information
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <Alert class="border-blue-200 bg-blue-50">
                  <AlertDescription class="text-blue-800 text-sm">
                    You are editing an Administrator profile. Changes will affect your admin access.
                  </AlertDescription>
                </Alert>

                <div class="space-y-3">
                  <div class="space-y-2">
                    <Label for="name" class="text-sm font-medium">Full Name</Label>
                    <Input id="name" v-model="name" :disabled="updateLoading" placeholder="Enter your full name" />
                  </div>

                  <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium">Email Address</Label>
                    <Input id="email" v-model="email" type="email" :disabled="updateLoading" placeholder="your.email@example.com" />
                  </div>
                </div>

                <div v-if="updateError" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md">
                  {{ updateError }}
                </div>
                <div v-if="updateSuccess" class="text-sm text-green-600 bg-green-50 px-3 py-2 rounded-md">
                  {{ updateSuccess }}
                </div>

                <div class="flex gap-3 pt-2">
                  <Button 
                    @click="updateProfile" 
                    :disabled="updateLoading"
                    class="bg-blue-600 hover:bg-blue-700 text-white flex-1"
                  >
                    <span v-if="updateLoading" class="flex items-center gap-2">
                      <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                      Saving...
                    </span>
                    <span v-else>Save Changes</span>
                  </Button>
                  <Button 
                    variant="outline" 
                    @click="fetchProfile" 
                    :disabled="updateLoading"
                    class="border-gray-300 text-gray-700"
                  >
                    Reset
                  </Button>
                </div>
              </CardContent>
            </Card>

            <!-- Security Settings -->
            <Card class="border-gray-200">
              <CardHeader>
                <CardTitle class="text-lg font-semibold">Security</CardTitle>
                <CardDescription>
                  Change your password to keep your account secure
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-3">
                  <div class="space-y-2">
                    <Label for="current" class="text-sm font-medium">Current Password</Label>
                    <Input id="current" v-model="currentPassword" type="password" :disabled="passLoading" placeholder="Enter current password" />
                  </div>

                  <div class="space-y-2">
                    <Label for="new" class="text-sm font-medium">New Password</Label>
                    <Input id="new" v-model="newPassword" type="password" :disabled="passLoading" placeholder="Enter new password" />
                  </div>

                  <div class="space-y-2">
                    <Label for="confirm" class="text-sm font-medium">Confirm New Password</Label>
                    <Input id="confirm" v-model="confirmPassword" type="password" :disabled="passLoading" placeholder="Confirm new password" />
                  </div>
                </div>

                <div v-if="passError" class="text-sm text-red-600 bg-red-50 px-3 py-2 rounded-md">
                  {{ passError }}
                </div>
                <div v-if="passSuccess" class="text-sm text-green-600 bg-green-50 px-3 py-2 rounded-md">
                  {{ passSuccess }}
                </div>

                <div class="flex gap-3 pt-2">
                  <Button 
                    @click="changePassword" 
                    :disabled="passLoading"
                    class="bg-green-600 hover:bg-green-700 text-white flex-1"
                  >
                    <span v-if="passLoading" class="flex items-center gap-2">
                      <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                      Updating...
                    </span>
                    <span v-else>Update Password</span>
                  </Button>
                  <Button 
                    variant="outline" 
                    @click="() => { currentPassword=''; newPassword=''; confirmPassword='' }" 
                    :disabled="passLoading"
                    class="border-gray-300 text-gray-700"
                  >
                    Clear
                  </Button>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Media Tab -->
        <TabsContent value="media" class="space-y-6 mt-6">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Avatar Upload -->
            <Card class="border-gray-200">
              <CardHeader>
                <CardTitle class="text-lg font-semibold">Profile Picture</CardTitle>
                <CardDescription>
                  Upload a new profile picture. Recommended: 256x256px, JPG or PNG
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="flex items-center gap-4">
                  <Avatar class="h-20 w-20 border-2 border-gray-200">
                    <AvatarImage :src="avatarPreview" />
                    <AvatarFallback class="bg-blue-100 text-blue-600 text-lg font-semibold">
                      {{ name?.charAt(0)?.toUpperCase() || 'A' }}
                    </AvatarFallback>
                  </Avatar>
                  <div class="flex-1 space-y-3">
                    <Input 
                      type="file" 
                      accept="image/*" 
                      @change="handleAvatarUpload"
                      class="cursor-pointer"
                    />
                    <div class="flex gap-2">
                      <Button 
                        @click="uploadImages" 
                        :disabled="!avatarFile || uploadLoading"
                        size="sm"
                        class="bg-blue-600 hover:bg-blue-700 text-white"
                      >
                        {{ uploadLoading ? 'Uploading...' : 'Save Avatar' }}
                      </Button>
                      <Button 
                        @click="removeAvatar" 
                        variant="outline" 
                        size="sm"
                        class="border-gray-300 text-gray-700"
                      >
                        Remove
                      </Button>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Banner Upload -->
            <Card class="border-gray-200">
              <CardHeader>
                <CardTitle class="text-lg font-semibold">Profile Banner</CardTitle>
                <CardDescription>
                  Upload a banner image for your profile. Recommended: 1200x300px
                </CardDescription>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="space-y-3">
                  <div 
                    class="w-full h-32 bg-gray-100 rounded-lg border-2 border-dashed border-gray-300 flex items-center justify-center overflow-hidden"
                    :class="{ 'border-blue-300 bg-blue-50': bannerPreview }"
                  >
                    <img v-if="bannerPreview" :src="bannerPreview" class="w-full h-full object-cover" />
                    <div v-else class="text-gray-500 text-sm text-center p-4">
                      No banner uploaded
                    </div>
                  </div>
                  
                  <Input 
                    type="file" 
                    accept="image/*" 
                    @change="handleBannerUpload"
                    class="cursor-pointer"
                  />
                  
                  <div class="flex gap-2">
                    <Button 
                      @click="uploadImages" 
                      :disabled="!bannerFile || uploadLoading"
                      size="sm"
                      class="bg-blue-600 hover:bg-blue-700 text-white"
                    >
                      {{ uploadLoading ? 'Uploading...' : 'Save Banner' }}
                    </Button>
                    <Button 
                      @click="removeBanner" 
                      variant="outline" 
                      size="sm"
                      class="border-gray-300 text-gray-700"
                    >
                      Remove
                    </Button>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </TabsContent>

        <!-- Location Tab -->
        <TabsContent value="location" class="space-y-6 mt-6">
          <Card class="border-gray-200 max-w-2xl">
            <CardHeader>
              <CardTitle class="text-lg font-semibold">Regional Settings</CardTitle>
              <CardDescription>
                Customize your dashboard language, timezone, and date formats
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-3">
                  <Label for="language" class="text-sm font-medium">Dashboard Language</Label>
                  <Select v-model="language">
                    <SelectTrigger>
                      <SelectValue placeholder="Select language" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="lang in languages" :key="lang.value" :value="lang.value">
                        {{ lang.label }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-3">
                  <Label for="timezone" class="text-sm font-medium">Timezone</Label>
                  <Select v-model="timezone">
                    <SelectTrigger>
                      <SelectValue placeholder="Select timezone" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="tz in timezones" :key="tz" :value="tz">
                        {{ tz }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-3">
                  <Label for="dateFormat" class="text-sm font-medium">Date Format</Label>
                  <Select v-model="dateFormat">
                    <SelectTrigger>
                      <SelectValue placeholder="Select date format" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem v-for="format in dateFormats" :key="format" :value="format">
                        {{ format }}
                      </SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <div class="space-y-3">
                  <Label for="timeFormat" class="text-sm font-medium">Time Format</Label>
                  <Select v-model="timeFormat">
                    <SelectTrigger>
                      <SelectValue placeholder="Select time format" />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="24h">24-hour format</SelectItem>
                      <SelectItem value="12h">12-hour format</SelectItem>
                    </SelectContent>
                  </Select>
                </div>
              </div>

              <Separator />

              <div class="flex justify-end">
                <Button 
                  @click="saveLocationSettings"
                  class="bg-blue-600 hover:bg-blue-700 text-white"
                >
                  Save Location Settings
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>

        <!-- Advanced Tab -->
        <TabsContent value="advanced" class="space-y-6 mt-6">
          <Card class="border-gray-200 max-w-2xl">
            <CardHeader>
              <CardTitle class="text-lg font-semibold">User ID Management</CardTitle>
              <CardDescription>
                Manage your user identifier for system integration purposes
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <Alert class="border-amber-200 bg-amber-50">
                <AlertDescription class="text-amber-800 text-sm">
                  <strong>Warning:</strong> Changing your User ID may affect system integrations and external references.
                </AlertDescription>
              </Alert>

              <div class="space-y-3">
                <div class="space-y-2">
                  <Label for="userId" class="text-sm font-medium">User ID</Label>
                  <Input 
                    id="userId" 
                    v-model="userId" 
                    :disabled="userIdLoading"
                    placeholder="Enter custom user ID"
                  />
                  <p class="text-xs text-gray-500">
                    This identifier is used for system integration and API references
                  </p>
                </div>
              </div>

              <div class="flex gap-3 pt-2">
                <Button 
                  @click="updateUserId" 
                  :disabled="userId === userIdOriginal || userIdLoading"
                  class="bg-blue-600 hover:bg-blue-700 text-white"
                >
                  <span v-if="userIdLoading" class="flex items-center gap-2">
                    <div class="animate-spin rounded-full h-4 w-4 border-b-2 border-white"></div>
                    Updating...
                  </span>
                  <span v-else>Update User ID</span>
                </Button>
                <Button 
                  variant="outline" 
                  @click="resetUserId" 
                  :disabled="userId === userIdOriginal || userIdLoading"
                  class="border-gray-300 text-gray-700"
                >
                  Reset
                </Button>
              </div>
            </CardContent>
          </Card>
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>

<style scoped>
/* Custom styles for better visual hierarchy */
:deep(.tabs-trigger) {
  transition: all 0.2s ease-in-out;
}

:deep(.tabs-trigger[data-state='active']) {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
</style>