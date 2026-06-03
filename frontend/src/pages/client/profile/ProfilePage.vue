<script setup lang="ts">
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Eye, Lock, User, Wallet } from 'lucide-vue-next'
import { ref } from 'vue'
import MediaTab from './components/MediaTab.vue'
import PortfolioTab from './components/PortfolioTab.vue'
import ProfileTab from './components/ProfileTab.vue'
import SecurityTab from './components/SecurityTab.vue'

const activeTab = ref('overview')
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-[#38618C] mb-1">My Profile & Portfolio</h1>
        <p class="text-sm sm:text-base text-gray-500">Manage your account and investments</p>
      </div>
    </div>

    <!-- Onglets -->
    <Tabs v-model="activeTab" class="w-full">
      <TabsList class="grid w-full grid-cols-4 bg-gray-100 p-1 rounded-lg">
        <TabsTrigger 
          value="overview" 
          class="data-[state=active]:bg-[#35A7FF] data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <Wallet class="w-4 h-4 mr-2" />
          Portfolio
        </TabsTrigger>
        <TabsTrigger 
          value="profile" 
          class="data-[state=active]:bg-[#35A7FF] data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <User class="w-4 h-4 mr-2" />
          Profile
        </TabsTrigger>
        <TabsTrigger 
          value="media" 
          class="data-[state=active]:bg-[#35A7FF] data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <Eye class="w-4 h-4 mr-2" />
          Media
        </TabsTrigger>
        <TabsTrigger 
          value="security" 
          class="data-[state=active]:bg-[#35A7FF] data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <Lock class="w-4 h-4 mr-2" />
          Security
        </TabsTrigger>
      </TabsList>

      <!-- Onglets -->
      <TabsContent value="overview" class="mt-6">
        <PortfolioTab />
      </TabsContent>

      <TabsContent value="profile" class="mt-6">
        <ProfileTab />
      </TabsContent>

      <TabsContent value="media" class="mt-6">
        <MediaTab />
      </TabsContent>

      <TabsContent value="security" class="mt-6">
        <SecurityTab />
      </TabsContent>
    </Tabs>
  </div>
</template>