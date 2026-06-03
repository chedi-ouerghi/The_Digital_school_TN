<script setup lang="ts">
import { ref } from 'vue'
import { User, Mail, Save, RefreshCw } from 'lucide-vue-next'
import api from '../../../services/api'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'

interface Props {
  profile: any
}

interface Emits {
  (e: 'profile-updated'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Profile form
const name = ref(props.profile?.name || '')
const email = ref(props.profile?.email || '')
const updateLoading = ref(false)
const updateError = ref('')
const updateSuccess = ref('')

async function updateProfile() {
  updateError.value = ''
  updateSuccess.value = ''
  
  if (!name.value.trim()) {
    updateError.value = 'Name is required'
    return
  }
  
  updateLoading.value = true
  try {
    await api.auth.updateProfile({ 
      name: name.value.trim(), 
      email: email.value.trim() 
    })
    updateSuccess.value = 'Profile updated successfully'
    emit('profile-updated')
  } catch (e: any) {
    updateError.value = e?.message || 'Failed to update profile'
  } finally {
    updateLoading.value = false
  }
}

function resetForm() {
  name.value = props.profile?.name || ''
  email.value = props.profile?.email || ''
  updateError.value = ''
  updateSuccess.value = ''
}
</script>

<template>
  <Card class="border border-gray-200">
    <CardHeader>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
          <User class="h-5 w-5 text-blue-600" />
        </div>
        <div>
          <CardTitle class="text-lg font-semibold">Profile Information</CardTitle>
          <CardDescription>
            Update your personal details and contact information
          </CardDescription>
        </div>
      </div>
    </CardHeader>
    
    <CardContent class="space-y-6">
      <Alert class="border-blue-200 bg-gradient-to-r from-blue-50 to-blue-100">
        <AlertDescription class="text-blue-800 text-sm">
          You are editing an Administrator profile. Changes will affect your admin access across the platform.
        </AlertDescription>
      </Alert>

      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="space-y-2">
          <Label for="name" class="text-sm font-medium text-gray-700 flex items-center gap-2">
            <User class="h-4 w-4 text-gray-500" />
            Full Name
          </Label>
          <Input 
            id="name" 
            v-model="name" 
            :disabled="updateLoading" 
            placeholder="Enter your full name"
            class="w-full"
          />
          <p class="text-xs text-gray-500">Your name as it appears across the platform</p>
        </div>

        <div class="space-y-2">
          <Label for="email" class="text-sm font-medium text-gray-700 flex items-center gap-2">
            <Mail class="h-4 w-4 text-gray-500" />
            Email Address
          </Label>
          <Input 
            id="email" 
            v-model="email" 
            type="email" 
            :disabled="updateLoading" 
            placeholder="your.email@example.com"
            class="w-full"
          />
          <p class="text-xs text-gray-500">Used for notifications and account recovery</p>
        </div>
      </div>

      <!-- Status Messages -->
      <div v-if="updateError" class="p-3 rounded-lg border border-red-200 bg-gradient-to-r from-red-50 to-red-100">
        <div class="flex items-center gap-2 text-red-800">
          <XCircle class="h-4 w-4 flex-shrink-0" />
          <span class="text-sm">{{ updateError }}</span>
        </div>
      </div>
      
      <div v-if="updateSuccess" class="p-3 rounded-lg border border-green-200 bg-gradient-to-r from-green-50 to-green-100">
        <div class="flex items-center gap-2 text-green-800">
          <CheckCircle class="h-4 w-4 flex-shrink-0" />
          <span class="text-sm">{{ updateSuccess }}</span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-100">
        <Button 
          :disabled="updateLoading" 
          class="bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white gap-2 flex-1 sm:flex-none"
          @click="updateProfile"
        >
          <Save class="h-4 w-4" />
          <span>{{ updateLoading ? 'Saving...' : 'Save Changes' }}</span>
        </Button>
        
        <Button 
          variant="outline" 
          :disabled="updateLoading" 
          class="border-gray-300 text-gray-700 hover:bg-gray-50 gap-2"
          @click="resetForm"
        >
          <RefreshCw class="h-4 w-4" />
          Reset to Original
        </Button>
      </div>
    </CardContent>
  </Card>
</template>