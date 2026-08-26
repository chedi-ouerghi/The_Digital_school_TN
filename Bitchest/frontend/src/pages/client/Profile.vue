<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Skeleton } from '@/components/ui/skeleton'
import { authApi } from '@/services/api'
import {
    AlertCircle,
    Calendar,
    CheckCircle2,
    Edit3,
    Eye,
    EyeOff,
    Mail,
    Save,
    Shield,
    User,
    Wallet,
    X
} from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import api from '../../services/api'



const profile = ref<any>(null)
const editing = ref(false)
const loading = ref(false)
const message = ref<string | null>(null)
const error = ref<string | null>(null)
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)

const pwd = ref({
  current_password: '',
  password: '',
  password_confirmation: '',
})

const userBalance = ref(0)
const portfolioValue = ref(0)

onMounted(async () => {
  await loadProfile()
})

async function loadProfile() {
  loading.value = true
  message.value = null
  error.value = null
  try {
    const [profileRes, portfolioRes] = await Promise.all([
      authApi.profile(),
      api.wallet.list()
    ])
    
    profile.value = profileRes.user
    
    const walletData = portfolioRes?.wallet || {}
    userBalance.value = Number(walletData.balance_eur || 0)
    portfolioValue.value = Number(walletData.current_value || 0)
    
  } catch (e: any) {
    error.value = e.message || 'Error loading profile'
  } finally {
    loading.value = false
  }
}

async function saveProfile() {
  if (!profile.value) return
  message.value = null
  error.value = null
  try {
    await authApi.updateProfile({
      name: profile.value.name,
      email: profile.value.email,
    })
    message.value = 'Profile updated successfully ✅'
    editing.value = false
    await loadProfile()
  } catch (err: any) {
    error.value = err.message || 'Unable to update profile'
  }
}

function validatePasswordForm() {
  if (!pwd.value.current_password) return 'Please enter your current password'
  if ((pwd.value.password || '').length < 8)
    return 'Password must be at least 8 characters long'
  if (!/[A-Z]/.test(pwd.value.password) || !/\d/.test(pwd.value.password))
    return 'Password must contain at least one uppercase letter and one number'
  if (pwd.value.password !== pwd.value.password_confirmation)
    return 'Passwords do not match'
  return null
}

async function changePassword() {
  message.value = null
  error.value = null
  const validation = validatePasswordForm()
  if (validation) {
    error.value = validation
    return
  }

  try {
    await authApi.changePassword(pwd.value)
    message.value = 'Password updated successfully 🔒'
    pwd.value = { current_password: '', password: '', password_confirmation: '' }
  } catch (err: any) {
    error.value = err.message || 'Error changing password'
  }
}

function cancelEdit() {
  editing.value = false
  loadProfile() // Rechargement des données originales
}

function formatDate(dateString: string) {
  if (!dateString) return '-'
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function formatCurrency(amount: number) {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount || 0)
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 py-8 px-4 sm:px-6 lg:px-8">
    <div class="max-w-4xl mx-auto space-y-8">
      
      <!-- Header Section -->
      <div class="text-center space-y-4">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gradient-to-br from-brand-dark to-brand-blue shadow-lg">
          <User class="w-10 h-10 text-white" />
        </div>
        <div>
          <h1 class="text-4xl font-bold bg-gradient-to-r from-brand-dark to-brand-blue bg-clip-text text-transparent">
            My Profile cd00
                    </h1>
          <p class="text-gray-600 mt-2 text-lg">Manage your account settings and preferences</p>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="space-y-6">
        <Card class="border-gray-200 shadow-xl rounded-2xl overflow-hidden">
          <CardContent class="p-8">
            <div class="flex items-center space-x-4">
              <Skeleton class="h-16 w-16 rounded-full" />
              <div class="space-y-2 flex-1">
                <Skeleton class="h-6 w-48" />
                <Skeleton class="h-4 w-32" />
              </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-8">
              <Skeleton v-for="i in 4" :key="i" class="h-20 rounded-xl" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Main Content -->
      <template v-else>
        <!-- Alerts -->
        <div class="space-y-4">
          <Alert v-if="message" class="border-brand-green bg-brand-green/10 rounded-xl">
            <CheckCircle2 class="h-4 w-4 text-brand-green" />
            <AlertDescription class="text-brand-green font-medium">
              {{ message }}
            </AlertDescription>
          </Alert>
          
          <Alert v-if="error" class="border-brand-red bg-brand-red/10 rounded-xl">
            <AlertCircle class="h-4 w-4 text-brand-red" />
            <AlertDescription class="text-brand-red font-medium">
              {{ error }}
            </AlertDescription>
          </Alert>
        </div>

        <!-- Profile Overview Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <!-- Account Status -->
          <Card class="border-gray-200 bg-gradient-to-br from-white to-gray-50 shadow-lg rounded-2xl hover:shadow-xl transition-all duration-300 hover-lift">
            <CardContent class="p-6 text-center">
              <div class="w-12 h-12 rounded-full bg-brand-green/20 flex items-center justify-center mx-auto mb-4">
                <CheckCircle2 class="w-6 h-6 text-brand-green" />
              </div>
              <Badge class="bg-brand-green text-white px-3 py-1 text-sm mb-2">Active</Badge>
              <p class="text-gray-600 text-sm">Account Status</p>
            </CardContent>
          </Card>

          <!-- Member Since -->
          <Card class="border-gray-200 bg-gradient-to-br from-white to-gray-50 shadow-lg rounded-2xl hover:shadow-xl transition-all duration-300 hover-lift">
            <CardContent class="p-6 text-center">
              <div class="w-12 h-12 rounded-full bg-brand-blue/20 flex items-center justify-center mx-auto mb-4">
                <Calendar class="w-6 h-6 text-brand-blue" />
              </div>
              <div class="text-lg font-semibold text-brand-dark mb-2">
                {{ formatDate(profile?.created_at) }}
              </div>
              <p class="text-gray-600 text-sm">Member Since</p>
            </CardContent>
          </Card>

          <!-- Balance -->
          <Card class="border-gray-200 bg-gradient-to-br from-white to-gray-50 shadow-lg rounded-2xl hover:shadow-xl transition-all duration-300 hover-lift">
            <CardContent class="p-6 text-center">
              <div class="w-12 h-12 rounded-full bg-brand-dark/20 flex items-center justify-center mx-auto mb-4">
                <Wallet class="w-6 h-6 text-brand-dark" />
              </div>
              <div class="text-2xl font-bold text-brand-dark mb-2">
                {{ formatCurrency(userBalance) }}
              </div>
              <p class="text-gray-600 text-sm">Available Balance</p>
            </CardContent>
          </Card>
        </div>

        <!-- Profile Information -->
        <Card class="border-gray-200 shadow-xl rounded-2xl overflow-hidden">
          <CardHeader class="bg-gradient-to-r from-brand-dark to-brand-blue text-white p-8">
            <div class="flex items-center justify-between">
              <div class="flex items-center space-x-3">
                <User class="w-6 h-6" />
                <CardTitle class="text-2xl font-bold">Personal Information</CardTitle>
              </div>
              <Button
                v-if="!editing"
                class="bg-white/20 hover:bg-white/30 text-white border-white/30 backdrop-blur-sm"
                @click="editing = true"
              >
                <Edit3 class="w-4 h-4 mr-2" />
                Edit Profile
              </Button>
            </div>
          </CardHeader>
          
          <CardContent class="p-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <!-- Display Mode -->
              <template v-if="!editing">
                <div class="space-y-6">
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <User class="w-5 h-5 text-brand-dark" />
                    <div>
                      <p class="text-sm text-gray-500">Full Name</p>
                      <p class="font-semibold text-brand-dark">{{ profile?.name }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <Mail class="w-5 h-5 text-brand-dark" />
                    <div>
                      <p class="text-sm text-gray-500">Email Address</p>
                      <p class="font-semibold text-brand-dark">{{ profile?.email }}</p>
                    </div>
                  </div>
                </div>
                
                <div class="space-y-6">
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <Calendar class="w-5 h-5 text-brand-dark" />
                    <div>
                      <p class="text-sm text-gray-500">Account Created</p>
                      <p class="font-semibold text-brand-dark">{{ formatDate(profile?.created_at) }}</p>
                    </div>
                  </div>
                  
                  <div class="flex items-center space-x-4 p-4 bg-gray-50 rounded-xl">
                    <Wallet class="w-5 h-5 text-brand-dark" />
                    <div>
                      <p class="text-sm text-gray-500">Current Balance</p>
                      <p class="font-semibold text-brand-dark">{{ formatCurrency(userBalance) }}</p>
                    </div>
                  </div>
                </div>
              </template>

              <!-- Edit Mode -->
              <template v-else>
                <div class="space-y-6">
                  <div class="space-y-2">
                    <Label for="name" class="text-sm font-medium text-gray-700">Full Name</Label>
                    <Input 
                      id="name" 
                      v-model="profile.name" 
                      placeholder="Enter your full name"
                      class="h-12 border-gray-300 focus:border-brand-blue focus:ring-brand-blue/20 rounded-xl transition-all duration-200"
                    />
                  </div>
                  
                  <div class="space-y-2">
                    <Label for="email" class="text-sm font-medium text-gray-700">Email Address</Label>
                    <Input 
                      id="email" 
                      v-model="profile.email" 
                      type="email"
                      placeholder="Enter your email address"
                      class="h-12 border-gray-300 focus:border-brand-blue focus:ring-brand-blue/20 rounded-xl transition-all duration-200"
                    />
                  </div>
                </div>
                
                <div class="flex items-end space-x-4">
                  <div class="flex-1 space-y-2">
                    <Label class="text-sm font-medium text-gray-700">Account Security</Label>
                    <div class="flex items-center space-x-2 text-sm text-gray-600">
                      <Shield class="w-4 h-4 text-brand-green" />
                      <span>Your account is secure and protected</span>
                    </div>
                  </div>
                  
                  <div class="flex space-x-3">
                    <Button 
                      class="bg-brand-green hover:bg-brand-green/90 text-white font-semibold px-6 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
                      @click="saveProfile"
                    >
                      <Save class="w-4 h-4 mr-2" />
                      Save
                    </Button>
                    <Button 
                      variant="outline"
                      class="border-brand-red text-brand-red hover:bg-brand-red hover:text-white font-semibold px-6 rounded-xl transition-all duration-200"
                      @click="cancelEdit"
                    >
                      <X class="w-4 h-4 mr-2" />
                      Cancel
                    </Button>
                  </div>
                </div>
              </template>
            </div>
          </CardContent>
        </Card>

        <!-- Password Change Section -->
        <Card class="border-gray-200 shadow-xl rounded-2xl overflow-hidden">
          <CardHeader class="bg-gradient-to-r from-brand-dark to-brand-blue text-white p-8">
            <div class="flex items-center space-x-3">
              <Shield class="w-6 h-6" />
              <CardTitle class="text-2xl font-bold">Security Settings</CardTitle>
            </div>
          </CardHeader>
          
          <CardContent class="p-8">
            <div class="max-w-2xl space-y-6">
              <div class="space-y-2">
                <Label for="current_password" class="text-sm font-medium text-gray-700 flex items-center space-x-2">
                  <Shield class="w-4 h-4 text-brand-dark" />
                  <span>Current Password</span>
                </Label>
                <div class="relative">
                  <Input 
                    id="current_password" 
                    v-model="pwd.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    placeholder="Enter your current password"
                    class="h-12 border-gray-300 focus:border-brand-blue focus:ring-brand-blue/20 rounded-xl pr-12 transition-all duration-200"
                  />
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <EyeOff v-if="showCurrentPassword" class="h-4 w-4 text-gray-400" />
                    <Eye v-else class="h-4 w-4 text-gray-400" />
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label for="password" class="text-sm font-medium text-gray-700">New Password</Label>
                  <div class="relative">
                    <Input 
                      id="password" 
                      v-model="pwd.password"
                      :type="showNewPassword ? 'text' : 'password'"
                      placeholder="Enter new password"
                      class="h-12 border-gray-300 focus:border-brand-blue focus:ring-brand-blue/20 rounded-xl pr-12 transition-all duration-200"
                    />
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      class="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                      @click="showNewPassword = !showNewPassword"
                    >
                      <EyeOff v-if="showNewPassword" class="h-4 w-4 text-gray-400" />
                      <Eye v-else class="h-4 w-4 text-gray-400" />
                    </Button>
                  </div>
                </div>

                <div class="space-y-2">
                  <Label for="password_confirmation" class="text-sm font-medium text-gray-700">Confirm Password</Label>
                  <div class="relative">
                    <Input 
                      id="password_confirmation" 
                      v-model="pwd.password_confirmation"
                      :type="showConfirmPassword ? 'text' : 'password'"
                      placeholder="Confirm new password"
                      class="h-12 border-gray-300 focus:border-brand-blue focus:ring-brand-blue/20 rounded-xl pr-12 transition-all duration-200"
                    />
                    <Button
                      type="button"
                      variant="ghost"
                      size="sm"
                      class="absolute right-0 top-0 h-full px-3 hover:bg-transparent"
                      @click="showConfirmPassword = !showConfirmPassword"
                    >
                      <EyeOff v-if="showConfirmPassword" class="h-4 w-4 text-gray-400" />
                      <Eye v-else class="h-4 w-4 text-gray-400" />
                    </Button>
                  </div>
                </div>
              </div>

              <div class="pt-4">
                <Button 
                  class="bg-brand-green hover:bg-brand-green/90 text-white font-semibold px-8 py-3 rounded-xl transition-all duration-200 shadow-md hover:shadow-lg"
                  @click="changePassword"
                >
                  <Shield class="w-4 h-4 mr-2" />
                  Update Password
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </template>
    </div>
  </div>
</template>

<style scoped>
.hover-lift {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.hover-lift:hover {
  transform: translateY(-4px);
}

/* Smooth gradient animations */
.gradient-bg {
  background: linear-gradient(-45deg, #38618C, #35A7FF, #01FF19, #FF5964);
  background-size: 400% 400%;
  animation: gradient 15s ease infinite;
}

@keyframes gradient {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: #f1f1f1;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb {
  background: #38618C;
  border-radius: 10px;
}

::-webkit-scrollbar-thumb:hover {
  background: #35A7FF;
}
</style>