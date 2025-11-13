<script setup lang="ts">
import { ref, onMounted } from 'vue'
import api from '../../services/api'
import { useRouter } from 'vue-router'

// UI
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'

const router = useRouter()

// Profile state
const profile = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// Update profile form
const name = ref('')
const email = ref('')
const updateLoading = ref(false)
const updateError = ref('')
const updateSuccess = ref('')

// Change password form
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const passLoading = ref(false)
const passError = ref('')
const passSuccess = ref('')

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const resp = await api.auth.profile()
    const user = resp?.user ?? resp
    profile.value = user || null
    name.value = profile.value?.name || ''
    email.value = profile.value?.email || ''
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
    // adapt payload to your API shape if needed
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
    // adapt to your API endpoint
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

onMounted(fetchProfile)
</script>

<template>
  <div class="space-y-6">
    <div class="flex items-start justify-between">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C]">Admin Settings</h1>
        <p class="text-sm text-gray-500 mt-1">Manage your admin profile and change password. Actions here apply to your administrator account.</p>
      </div>
      <Badge class="bg-[#FF5964] text-white">ADMIN</Badge>
    </div>

    <Card v-if="loading">
      <CardContent class="p-6 text-center text-gray-600">
        Loading profile...
      </CardContent>
    </Card>

    <Card v-else-if="error" class="border-[#FF5964]">
      <CardContent class="p-6 text-center">
        <div class="text-sm text-[#FF5964]">{{ error }}</div>
        <div class="mt-3">
          <Button @click="fetchProfile" class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white">Retry</Button>
        </div>
      </CardContent>
    </Card>

    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Profile Card -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-[#38618C]">Profile</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <Alert class="border-[#35A7FF] bg-[#35A7FF]/10">
            <AlertDescription class="text-[#38618C] text-sm">
              You are editing an Administrator profile. Make sure changes are intended for admin access.
            </AlertDescription>
          </Alert>

          <div class="space-y-2">
            <Label for="name" class="text-sm text-gray-600">Full name</Label>
            <Input id="name" v-model="name" :disabled="updateLoading" />
          </div>

          <div class="space-y-2">
            <Label for="email" class="text-sm text-gray-600">Email address</Label>
            <Input id="email" v-model="email" type="email" :disabled="updateLoading" />
          </div>

          <div v-if="updateError" class="text-sm text-[#FF5964]">{{ updateError }}</div>
          <div v-if="updateSuccess" class="text-sm text-[#01FF19]">{{ updateSuccess }}</div>

          <div class="flex gap-2">
            <Button @click="updateProfile" :disabled="updateLoading" class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white flex-1">
              {{ updateLoading ? 'Saving...' : 'Save Profile' }}
            </Button>
            <Button variant="outline" @click="fetchProfile" :disabled="updateLoading" class="border-gray-300 text-gray-700">
              Reset
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Change Password Card -->
      <Card>
        <CardHeader>
          <CardTitle class="text-lg font-semibold text-[#38618C]">Change Password</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
          <div class="text-sm text-gray-500">Update your admin password. Use a strong unique password.</div>

          <div class="space-y-2">
            <Label for="current" class="text-sm text-gray-600">Current password</Label>
            <Input id="current" v-model="currentPassword" type="password" :disabled="passLoading" />
          </div>

          <div class="space-y-2">
            <Label for="new" class="text-sm text-gray-600">New password</Label>
            <Input id="new" v-model="newPassword" type="password" :disabled="passLoading" />
          </div>

          <div class="space-y-2">
            <Label for="confirm" class="text-sm text-gray-600">Confirm new password</Label>
            <Input id="confirm" v-model="confirmPassword" type="password" :disabled="passLoading" />
          </div>

          <div v-if="passError" class="text-sm text-[#FF5964]">{{ passError }}</div>
          <div v-if="passSuccess" class="text-sm text-[#01FF19]">{{ passSuccess }}</div>

          <div class="flex gap-2">
            <Button @click="changePassword" :disabled="passLoading" class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] flex-1">
              {{ passLoading ? 'Updating...' : 'Change Password' }}
            </Button>
            <Button variant="outline" @click="() => { currentPassword=''; newPassword=''; confirmPassword='' }" :disabled="passLoading">
              Clear
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<style scoped>
:deep(.text-\[#38618C\]) { color: #38618C; }
:deep(.bg-\[#35A7FF\]) { background-color: #35A7FF; }
</style>
