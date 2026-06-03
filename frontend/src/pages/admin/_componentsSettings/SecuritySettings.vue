<script setup lang="ts">
import { ref } from 'vue'
import { Lock, Key, Eye, EyeOff, Shield, CheckCircle, XCircle } from 'lucide-vue-next'
import api from '../../../services/api'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

// Password form
const currentPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')
const showCurrent = ref(false)
const showNew = ref(false)
const showConfirm = ref(false)
const passLoading = ref(false)
const passError = ref('')
const passSuccess = ref('')

async function changePassword() {
  passError.value = ''
  passSuccess.value = ''

  // Validation
  if (!currentPassword.value) {
    passError.value = 'Current password is required'
    return
  }
  if (!newPassword.value) {
    passError.value = 'New password is required'
    return
  }
  if (newPassword.value.length < 8) {
    passError.value = 'New password must be at least 8 characters'
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
    
    // Clear form
    currentPassword.value = ''
    newPassword.value = ''
    confirmPassword.value = ''
  } catch (e: any) {
    passError.value = e?.message || 'Failed to change password'
  } finally {
    passLoading.value = false
  }
}

function clearForm() {
  currentPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
  passError.value = ''
  passSuccess.value = ''
}
</script>

<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    <!-- Password Change Card -->
    <Card class="border border-gray-200">
      <CardHeader>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center">
            <Key class="h-5 w-5 text-emerald-600" />
          </div>
          <div>
            <CardTitle class="text-lg font-semibold">Change Password</CardTitle>
            <CardDescription>
              Update your account password for enhanced security
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-6">
        <Alert class="border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100">
          <AlertDescription class="text-blue-800 text-sm">
            For security, your new password must be at least 8 characters long and contain a mix of letters, numbers, and symbols.
          </AlertDescription>
        </Alert>

        <div class="space-y-4">
          <!-- Current Password -->
          <div class="space-y-2">
            <Label for="current" class="text-sm font-medium text-gray-700 flex items-center gap-2">
              <Lock class="h-4 w-4 text-gray-500" />
              Current Password
            </Label>
            <div class="relative">
              <Input 
                id="current" 
                v-model="currentPassword" 
                :type="showCurrent ? 'text' : 'password'" 
                :disabled="passLoading" 
                placeholder="Enter your current password"
                class="w-full pr-10"
              />
              <Button
                type="button"
                variant="ghost"
                size="sm"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 h-7 w-7 p-0"
                @click="showCurrent = !showCurrent"
              >
                <component :is="showCurrent ? EyeOff : Eye" class="h-4 w-4 text-gray-500" />
              </Button>
            </div>
          </div>

          <!-- New Password -->
          <div class="space-y-2">
            <Label for="new" class="text-sm font-medium text-gray-700">New Password</Label>
            <div class="relative">
              <Input 
                id="new" 
                v-model="newPassword" 
                :type="showNew ? 'text' : 'password'" 
                :disabled="passLoading" 
                placeholder="Enter your new password"
                class="w-full pr-10"
              />
              <Button
                type="button"
                variant="ghost"
                size="sm"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 h-7 w-7 p-0"
                @click="showNew = !showNew"
              >
                <component :is="showNew ? EyeOff : Eye" class="h-4 w-4 text-gray-500" />
              </Button>
            </div>
            <div v-if="newPassword" class="flex items-center gap-2 mt-1">
              <div 
                :class="[
                  'h-1 flex-1 rounded',
                  newPassword.length >= 8 ? 'bg-green-500' : 'bg-gray-200'
                ]"
              />
              <div 
                :class="[
                  'h-1 flex-1 rounded',
                  /[A-Z]/.test(newPassword) ? 'bg-green-500' : 'bg-gray-200'
                ]"
              />
              <div 
                :class="[
                  'h-1 flex-1 rounded',
                  /[0-9]/.test(newPassword) ? 'bg-green-500' : 'bg-gray-200'
                ]"
              />
              <div 
                :class="[
                  'h-1 flex-1 rounded',
                  /[^A-Za-z0-9]/.test(newPassword) ? 'bg-green-500' : 'bg-gray-200'
                ]"
              />
            </div>
          </div>

          <!-- Confirm Password -->
          <div class="space-y-2">
            <Label for="confirm" class="text-sm font-medium text-gray-700">Confirm New Password</Label>
            <div class="relative">
              <Input 
                id="confirm" 
                v-model="confirmPassword" 
                :type="showConfirm ? 'text' : 'password'" 
                :disabled="passLoading" 
                placeholder="Confirm your new password"
                class="w-full pr-10"
              />
              <Button
                type="button"
                variant="ghost"
                size="sm"
                class="absolute right-2 top-1/2 transform -translate-y-1/2 h-7 w-7 p-0"
                @click="showConfirm = !showConfirm"
              >
                <component :is="showConfirm ? EyeOff : Eye" class="h-4 w-4 text-gray-500" />
              </Button>
            </div>
            <p 
              v-if="confirmPassword && newPassword !== confirmPassword" 
              class="text-xs text-red-600"
            >
              Passwords do not match
            </p>
          </div>
        </div>

        <!-- Status Messages -->
        <div v-if="passError" class="p-3 rounded-lg border border-red-200 bg-gradient-to-r from-red-50 to-red-100">
          <div class="flex items-center gap-2 text-red-800">
            <XCircle class="h-4 w-4 flex-shrink-0" />
            <span class="text-sm">{{ passError }}</span>
          </div>
        </div>
        
        <div v-if="passSuccess" class="p-3 rounded-lg border border-green-200 bg-gradient-to-r from-green-50 to-green-100">
          <div class="flex items-center gap-2 text-green-800">
            <CheckCircle class="h-4 w-4 flex-shrink-0" />
            <span class="text-sm">{{ passSuccess }}</span>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
          <Button 
            :disabled="passLoading" 
            class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white gap-2 flex-1 sm:flex-none"
            @click="changePassword"
          >
            <Shield class="h-4 w-4" />
            <span>{{ passLoading ? 'Updating...' : 'Update Password' }}</span>
          </Button>
          
          <Button 
            variant="outline" 
            :disabled="passLoading" 
            class="border-gray-300 text-gray-700 hover:bg-gray-50"
            @click="clearForm"
          >
            Clear Form
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Security Tips Card -->
    <Card class="border border-gray-200">
      <CardHeader>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center">
            <Shield class="h-5 w-5 text-amber-600" />
          </div>
          <div>
            <CardTitle class="text-lg font-semibold">Security Tips</CardTitle>
            <CardDescription>
              Best practices for account security
            </CardDescription>
          </div>
        </div>
      </CardHeader>
      
      <CardContent class="space-y-4">
        <div class="space-y-3">
          <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
              <span class="text-blue-600 text-xs font-bold">1</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Use a Strong Password</p>
              <p class="text-xs text-gray-600 mt-0.5">
                Include uppercase, lowercase, numbers, and special characters
              </p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
              <span class="text-blue-600 text-xs font-bold">2</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Never Share Credentials</p>
              <p class="text-xs text-gray-600 mt-0.5">
                Your admin password should never be shared with anyone
              </p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
              <span class="text-blue-600 text-xs font-bold">3</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Regular Updates</p>
              <p class="text-xs text-gray-600 mt-0.5">
                Update your password every 60-90 days
              </p>
            </div>
          </div>

          <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50">
            <div class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center flex-shrink-0">
              <span class="text-blue-600 text-xs font-bold">4</span>
            </div>
            <div>
              <p class="text-sm font-medium text-gray-900">Monitor Account Activity</p>
              <p class="text-xs text-gray-600 mt-0.5">
                Regularly check your account for suspicious activity
              </p>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>