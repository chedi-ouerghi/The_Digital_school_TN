<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import api from '@/services/api'
import { Eye, EyeOff } from 'lucide-vue-next'
import { ref } from 'vue'
import type { PasswordForm } from '../types'

const passwordForm = ref<PasswordForm>({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const changingPassword = ref(false)
const message = ref<string | null>(null)
const error = ref<string | null>(null)

const changePassword = async () => {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }
  
  changingPassword.value = true
  message.value = null
  error.value = null
  
  try {
    await api.auth.changePassword(passwordForm.value)
    message.value = 'Password changed successfully!'
    
    // Reset form
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: ''
    }
    
    setTimeout(() => {
      message.value = null
    }, 3000)
  } catch (e: any) {
    error.value = e.message || 'Error changing password'
  } finally {
    changingPassword.value = false
  }
}
</script>

<template>
  <Card class="border-gray-200 shadow-lg">
    <CardHeader>
      <CardTitle class="text-[#38618C]">Change Password</CardTitle>
    </CardHeader>
    <CardContent>
      <form class="space-y-4" @submit.prevent="changePassword">
        <div class="space-y-2">
          <Label for="current_password">Current Password</Label>
          <div class="relative">
            <Input
              id="current_password"
              v-model="passwordForm.current_password"
              :type="showCurrentPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              class="pr-10 transition-colors focus:border-[#35A7FF]"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
              @click="showCurrentPassword = !showCurrentPassword"
            >
              <Eye v-if="showCurrentPassword" class="w-4 h-4" />
              <EyeOff v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="password">New Password</Label>
          <div class="relative">
            <Input
              id="password"
              v-model="passwordForm.password"
              :type="showNewPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              class="pr-10 transition-colors focus:border-[#35A7FF]"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
              @click="showNewPassword = !showNewPassword"
            >
              <Eye v-if="showNewPassword" class="w-4 h-4" />
              <EyeOff v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <div class="space-y-2">
          <Label for="password_confirmation">Confirm New Password</Label>
          <div class="relative">
            <Input
              id="password_confirmation"
              v-model="passwordForm.password_confirmation"
              :type="showConfirmPassword ? 'text' : 'password'"
              placeholder="••••••••"
              required
              class="pr-10 transition-colors focus:border-[#35A7FF]"
            />
            <button
              type="button"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
              @click="showConfirmPassword = !showConfirmPassword"
            >
              <Eye v-if="showConfirmPassword" class="w-4 h-4" />
              <EyeOff v-else class="w-4 h-4" />
            </button>
          </div>
        </div>

        <Button 
          type="submit" 
          :disabled="changingPassword"
          class="w-full bg-[#FF5964] hover:bg-[#E63946] text-white transition-colors"
        >
          {{ changingPassword ? 'Changing...' : 'Change Password' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>