<script setup lang="ts">
import { onMounted, ref, watch } from 'vue'
import {
  Settings as SettingsIcon,
  Shield,
  AlertCircle
} from 'lucide-vue-next'
import api from '../../services/api'

// UI Components
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

// Composants enfants
import ProfileSettings from './_componentsSettings/ProfileSettings.vue'
import MediaSettings from './_componentsSettings/MediaSettings.vue'
import SecuritySettings from './_componentsSettings/SecuritySettings.vue'
import AdvancedSettings from './_componentsSettings/AdvancedSettings.vue'

// Profile state
const profile = ref<any>(null)
const loading = ref(false)
const error = ref<string | null>(null)

// Active tab
const activeTab = ref('profile')

async function fetchProfile() {
  loading.value = true
  error.value = null
  try {
    const resp = await api.auth.profile()
    const user = resp?.user ?? resp
    profile.value = user || null
  } catch (e: any) {
    error.value = e?.message || 'Failed to load profile'
    console.error('Profile fetch error:', e)
  } finally {
    loading.value = false
  }
}

// Reset success messages when changing tabs
watch(activeTab, () => {
  // Reset messages if needed
})

onMounted(fetchProfile)
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-6 space-y-6">
    <!-- Header -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
      <div class="flex items-center gap-3">
        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
          <SettingsIcon class="h-6 w-6 text-white" />
        </div>
        <div>
          <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Admin Settings</h1>
          <p class="text-sm md:text-base text-gray-600">Manage your administrator profile and platform settings</p>
        </div>
      </div>
      <Badge class="bg-gradient-to-r from-red-500 to-red-600 text-white border-0 px-4 py-1.5 text-sm font-medium shadow-sm">
        <Shield class="h-3 w-3 mr-1.5" />
        ADMINISTRATOR
      </Badge>
    </div>

    <!-- Loading State -->
    <Card v-if="loading" class="border border-gray-200">
      <CardContent class="p-12">
        <div class="text-center space-y-4">
          <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
          <div class="space-y-2">
            <p class="text-lg font-medium text-gray-900">Loading Admin Profile</p>
            <p class="text-sm text-gray-500">Fetching your administrator settings...</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Error State -->
    <Alert v-else-if="error" variant="destructive" class="border-red-200 bg-gradient-to-r from-red-50 to-red-100">
      <AlertCircle class="h-5 w-5 text-red-600" />
      <AlertDescription class="text-red-800">
        {{ error }}
      </AlertDescription>
      <Button 
        variant="outline" 
        size="sm"
        class="mt-3 border-red-300 text-red-700 hover:bg-red-100"
        @click="fetchProfile"
      >
        Retry Loading
      </Button>
    </Alert>

    <!-- Main Content -->
    <div v-else>
      <!-- Tabs Navigation -->
      <Tabs v-model="activeTab" class="w-full">
        <TabsList class="grid w-full grid-cols-4 bg-gray-100/50 p-1.5 rounded-xl">
          <TabsTrigger 
            value="profile" 
            class="rounded-lg data-[state=active]:bg-white data-[state=active]:shadow-lg data-[state=active]:border data-[state=active]:border-gray-200 transition-all"
          >
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-md bg-blue-100 flex items-center justify-center">
                <span class="text-blue-600 text-sm">👤</span>
              </div>
              <span class="text-sm font-medium">Profile</span>
            </div>
          </TabsTrigger>
          
          <TabsTrigger 
            value="media" 
            class="rounded-lg data-[state=active]:bg-white data-[state=active]:shadow-lg data-[state=active]:border data-[state=active]:border-gray-200 transition-all"
          >
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-md bg-purple-100 flex items-center justify-center">
                <span class="text-purple-600 text-sm">🖼️</span>
              </div>
              <span class="text-sm font-medium">Media</span>
            </div>
          </TabsTrigger>
          
          <TabsTrigger 
            value="security" 
            class="rounded-lg data-[state=active]:bg-white data-[state=active]:shadow-lg data-[state=active]:border data-[state=active]:border-gray-200 transition-all"
          >
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-md bg-emerald-100 flex items-center justify-center">
                <Shield class="h-3 w-3 text-emerald-600" />
              </div>
              <span class="text-sm font-medium">Security</span>
            </div>
          </TabsTrigger>
          
          <TabsTrigger 
            value="advanced" 
            class="rounded-lg data-[state=active]:bg-white data-[state=active]:shadow-lg data-[state=active]:border data-[state=active]:border-gray-200 transition-all"
          >
            <div class="flex items-center gap-2">
              <div class="w-6 h-6 rounded-md bg-amber-100 flex items-center justify-center">
                <SettingsIcon class="h-3 w-3 text-amber-600" />
              </div>
              <span class="text-sm font-medium">Advanced</span>
            </div>
          </TabsTrigger>
        </TabsList>

        <!-- Profile Tab -->
        <TabsContent value="profile" class="mt-8">
          <ProfileSettings 
            :profile="profile"
            @profile-updated="fetchProfile"
          />
        </TabsContent>

        <!-- Media Tab -->
        <TabsContent value="media" class="mt-8">
          <MediaSettings 
            :profile="profile"
            @media-updated="fetchProfile"
          />
        </TabsContent>

        <!-- Security Tab -->
        <TabsContent value="security" class="mt-8">
          <SecuritySettings />
        </TabsContent>

        <!-- Advanced Tab -->
        <TabsContent value="advanced" class="mt-8">
          <AdvancedSettings 
            :profile="profile"
            @id-changed="fetchProfile"
          />
        </TabsContent>
      </Tabs>
    </div>
  </div>
</template>