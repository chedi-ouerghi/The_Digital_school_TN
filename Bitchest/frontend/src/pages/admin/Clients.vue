<script setup lang="ts">
import { ref, watch, onMounted } from 'vue'
import ClientList from './_componentsClients/ClientList.vue'
import RequestList from './_componentsClients/RequestList.vue'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Badge } from '@/components/ui/badge'

const activeTab = ref('clients')
const pendingRequestsCount = ref(0)
const loadingCount = ref(false)

// Fonction pour récupérer le nombre de demandes
async function fetchRequestsCount() {
  loadingCount.value = true
  try {
    const res = await api.admin.accountRequests.list()
    const requests = res.data || res.items || res || []
    pendingRequestsCount.value = Array.isArray(requests) ? requests.length : 0
  } catch (err) {
    console.error('Error loading requests count:', err)
    pendingRequestsCount.value = 0
  } finally {
    loadingCount.value = false
  }
}

// Charger le compte au montage
onMounted(() => {
  fetchRequestsCount()
})

// Rafraîchir le compte quand on revient sur l'onglet requests
watch(activeTab, (newTab) => {
  if (newTab === 'requests') {
    fetchRequestsCount()
  }
})

// Écouter les événements de mise à jour depuis RequestList
function handleRequestsUpdated() {
  fetchRequestsCount()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-brand-dark mb-1">Client Management</h1>
        <p class="text-gray-500">Manage platform users and account requests</p>
      </div>
    </div>

    <!-- Tabs -->
    <Card>
      <CardContent class="p-4">
        <Tabs v-model="activeTab" class="w-full">
          <TabsList class="grid w-full grid-cols-2 max-w-md">
            <TabsTrigger value="clients" class="text-sm font-medium">
              Clients
            </TabsTrigger>
            
            <TabsTrigger value="requests" class="text-sm font-medium">
              <span class="flex items-center gap-2">
                Account Requests
                <Badge 
                  v-if="pendingRequestsCount > 0"
                  class="ml-2 bg-brand-red text-white font-semibold px-2 py-1 text-xs"
                  :class="{'animate-pulse': loadingCount}"
                >
                  {{ pendingRequestsCount }}
                </Badge>
              </span>
            </TabsTrigger>
          </TabsList>
        </Tabs>
      </CardContent>
    </Card>

    <!-- Content based on active tab -->
    <ClientList v-if="activeTab === 'clients'" />
    <RequestList 
      v-else-if="activeTab === 'requests'" 
      @requests-updated="handleRequestsUpdated"
    />
  </div>
</template>

<style scoped>
:deep(.text-brand-dark) {
  color: #38618C;
}

:deep(.bg-brand-red) {
  background-color: #FF5964;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>