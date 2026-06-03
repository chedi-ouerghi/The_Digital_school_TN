<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import api from '@/services/api'
import { onMounted, ref } from 'vue'
import type { UserProfile } from '../types'

const profile = ref<UserProfile | null>(null)
const profileForm = ref({
  name: '',
  email: ''
})
const updatingProfile = ref(false)
const message = ref<string | null>(null)
const error = ref<string | null>(null)

// Fetch profile
const fetchProfile = async () => {
  try {
    const res = await api.auth.profile()
    profile.value = res.user || res
    profileForm.value = {
      name: profile.value.name || '',
      email: profile.value.email || ''
    }
  } catch (e: any) {
    console.error('Error loading profile:', e)
    error.value = 'Failed to load profile'
  }
}

// Update profile
const updateProfile = async () => {
  updatingProfile.value = true
  message.value = null
  error.value = null
  
  try {
    await api.auth.updateProfile(profileForm.value)
    message.value = 'Profile updated successfully!'
    await fetchProfile()
    
    setTimeout(() => {
      message.value = null
    }, 3000)
  } catch (e: any) {
    error.value = e.message || 'Error updating profile'
  } finally {
    updatingProfile.value = false
  }
}

onMounted(() => {
  fetchProfile()
})
</script>

<template>
  <Card class="border-gray-200 shadow-lg">
    <CardHeader>
      <CardTitle class="text-[#38618C]">Profile Information</CardTitle>
    </CardHeader>
    <CardContent>
      <form class="space-y-4" @submit.prevent="updateProfile">
        <div class="space-y-2">
          <Label for="name">Name</Label>
          <Input
            id="name"
            v-model="profileForm.name"
            type="text"
            placeholder="Your name"
            required
            class="transition-colors focus:border-[#35A7FF]"
          />
        </div>

        <div class="space-y-2">
          <Label for="email">Email</Label>
          <Input
            id="email"
            v-model="profileForm.email"
            type="email"
            placeholder="your@email.com"
            required
            class="transition-colors focus:border-[#35A7FF]"
          />
        </div>

        <div v-if="profile" class="pt-4 border-t space-y-2 text-sm">
          <div class="flex justify-between">
            <span class="text-gray-600">Member since:</span>
            <span class="font-medium">{{ new Date(profile.created_at).toLocaleDateString('en-US') }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-gray-600">Account status:</span>
            <Badge :class="profile.email_verified_at ? 'bg-[#01FF19]/20 text-[#01FF19]' : 'bg-[#FF5964]/20 text-[#FF5964]'">
              {{ profile.email_verified_at ? '✓ Verified' : '⚠ Not verified' }}
            </Badge>
          </div>
        </div>

        <Button 
          type="submit" 
          :disabled="updatingProfile"
          class="w-full bg-[#35A7FF] hover:bg-[#38618C] text-white transition-colors"
        >
          {{ updatingProfile ? 'Updating...' : 'Update Profile' }}
        </Button>
      </form>
    </CardContent>
  </Card>
</template>