<script setup lang="ts">
import { ref, watch } from 'vue'
import ClientList from './_componentsClients/ClientList.vue'
import RequestList from './_componentsClients/RequestList.vue'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'

const activeTab = ref('clients')

// Charger les données quand l'onglet change
watch(activeTab, (newTab) => {
  // Les composants enfants géreront leur propre chargement
  console.log(`Switched to ${newTab} tab`)
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Client Management</h1>
        <p class="text-gray-500">Manage platform users and account requests</p>
      </div>
    </div>

    <!-- Tabs -->
    <Card>
      <CardContent class="p-4">
        <Tabs v-model="activeTab" class="w-full">
          <TabsList class="grid w-full grid-cols-2 max-w-md">
            <TabsTrigger value="clients">Clients</TabsTrigger>
            <TabsTrigger value="requests">Account Requests</TabsTrigger>
          </TabsList>
        </Tabs>
      </CardContent>
    </Card>

    <!-- Content based on active tab -->
    <ClientList v-if="activeTab === 'clients'" />
    <RequestList v-else-if="activeTab === 'requests'" />
  </div>
</template>