<script setup lang="ts">
import { onMounted, ref } from 'vue'
import api from '../../../services/api'

// Import des composants shadcn-vue
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'

// Émissions d'événements
const emit = defineEmits<{
  'requests-updated': []
}>()

// State
const requests = ref<any[]>([])
const loading = ref(false)

// Utility functions
function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Data fetching
async function fetchAccountRequests() {
  loading.value = true
  try {
    const res = await api.admin.accountRequests.list()
    requests.value = res.data || res.items || res || []
    // Émettre l'événement de mise à jour
    emit('requests-updated')
  } catch (err: any) {
    console.error('Error loading requests:', err)
    requests.value = []
  } finally {
    loading.value = false
  }
}

// Actions
async function approveRequest(id: number | string) {
  try {
    await api.admin.accountRequests.approve(id)
    await fetchAccountRequests()
  } catch (err: any) {
    alert(err.message || 'Error approving request')
  }
}

async function rejectRequest(id: number | string) {
  try {
    const reason = window.prompt('Enter rejection reason (optional):') || undefined
    await api.admin.accountRequests.reject(id, reason)
    await fetchAccountRequests()
  } catch (err: any) {
    alert(err.message || 'Error rejecting request')
  }
}

async function approveAll() {
  if (!confirm(`Approve all ${requests.value.length} requests?`)) return
  
  try {
    for (const req of requests.value) {
      await approveRequest(req.id)
    }
  } catch (err: any) {
    alert(err.message || 'Error approving all requests')
  }
}

// Lifecycle
onMounted(() => {
  fetchAccountRequests()
})
</script>

<template>
  <div>
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <Card class="border-gray-200 hover:border-[#FF5964] transition-colors bg-gradient-to-br from-[#FF5964]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">⏳</div>
          <div class="text-sm text-gray-500 mb-1">Pending Requests</div>
          <div class="text-3xl font-bold text-[#FF5964]">
            {{ requests.length }}
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">📋</div>
          <div class="text-sm text-gray-500 mb-1">This Week</div>
          <div class="text-3xl font-bold text-[#35A7FF]">
            {{ requests.filter(req => {
              const weekAgo = new Date();
              weekAgo.setDate(weekAgo.getDate() - 7);
              return new Date(req.created_at) > weekAgo;
            }).length }}
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">✅</div>
          <div class="text-sm text-gray-500 mb-1">Ready to Process</div>
          <div class="text-3xl font-bold text-[#01FF19]">
            {{ requests.length }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Actions -->
    <Card class="mb-6">
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div>
            <h3 class="text-lg font-semibold text-[#38618C]">Account Requests</h3>
            <p class="text-sm text-gray-500">Review and manage new account applications</p>
          </div>
          
          <div class="flex gap-3">
            <Button 
              :disabled="loading"
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              @click="fetchAccountRequests"
            >
              🔄 Refresh
            </Button>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading">
      <CardContent class="p-12 text-center">
        <div class="animate-pulse text-gray-600">
          <div class="text-4xl mb-4">⏳</div>
          <div>Loading account requests...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Content -->
    <Card v-else>
      <CardContent class="p-0">
        <!-- Empty State -->
        <div v-if="requests.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">📭</div>
          <h3 class="text-xl font-semibold text-[#38618C] mb-2">No pending requests</h3>
          <p class="text-gray-500 mb-6">All account requests have been processed</p>
          <Button 
            class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
            @click="fetchAccountRequests"
          >
            🔄 Check Again
          </Button>
        </div>

        <!-- Requests List -->
        <div v-else class="space-y-3 p-4">
          <Card 
            v-for="request in requests" 
            :key="request.id"
            class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg"
          >
            <CardContent class="p-6">
              <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <!-- User Info -->
                <div class="flex items-center gap-4 flex-1">
                  <div class="h-14 w-14 rounded-full bg-gradient-to-br from-[#FF5964] to-[#38618C] flex items-center justify-center text-white font-bold text-lg">
                    {{ request.name?.charAt(0).toUpperCase() || '?' }}
                  </div>
                  <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                      <h3 class="font-bold text-[#38618C] text-lg">{{ request.name || 'Unknown' }}</h3>
                      <Badge class="bg-[#FF5964] text-white text-xs">
                        NEW
                      </Badge>
                    </div>
                    <p class="text-sm text-gray-500 mb-1">{{ request.email }}</p>
                    <p class="text-xs text-gray-400">
                      Requested on {{ formatDate(request.created_at) }}
                    </p>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex flex-col sm:flex-row gap-3">
                  <Button 
                    class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold px-6 py-2 min-w-[120px]"
                    @click="approveRequest(request.id)"
                  >
                    <span class="mr-2">✓</span>
                    Approve
                  </Button>
                  <Button 
                    class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white font-semibold px-6 py-2 min-w-[120px]"
                    @click="rejectRequest(request.id)"
                  >
                    <span class="mr-2">✗</span>
                    Reject
                  </Button>
                </div>
              </div>

              <!-- Additional Info -->
              <div class="mt-4 pt-4 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                  <span class="text-gray-500">Application ID:</span>
                  <span class="font-mono text-[#38618C] ml-2">{{ request.id }}</span>
                </div>
                <div>
                  <span class="text-gray-500">Status:</span>
                  <Badge class="bg-yellow-100 text-yellow-800 ml-2">Pending Review</Badge>
                </div>
                <div>
                  <span class="text-gray-500">Wait Time:</span>
                  <span class="text-[#38618C] font-medium ml-2">
                    {{ Math.floor((new Date().getTime() - new Date(request.created_at).getTime()) / (1000 * 60 * 60 * 24)) }} days
                  </span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Summary -->
        <div v-if="requests.length > 0" class="p-4 border-t border-gray-200 bg-gray-50 rounded-b-lg">
          <div class="flex flex-col sm:flex-row items-center justify-between text-sm text-gray-600">
            <div>
              Showing <strong>{{ requests.length }}</strong> pending request{{ requests.length !== 1 ? 's' : '' }}
            </div>
            <div class="flex gap-4 mt-2 sm:mt-0">
              <Button 
                variant="outline"
                size="sm"
                class="border-[#01FF19] text-[#01FF19] hover:bg-[#01FF19] hover:text-white"
                :disabled="requests.length === 0"
                @click="approveAll"
              >
                ✓ Approve All
              </Button>
              <Button 
                variant="outline"
                size="sm"
                class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white"
                @click="fetchAccountRequests"
              >
                🔄 Refresh
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>
  </div>
</template>

<style scoped>
:deep(.border-\[#38618C\]) {
  border-color: #38618C;
}

:deep(.text-\[#38618C\]) {
  color: #38618C;
}

:deep(.bg-\[#01FF19\]) {
  background-color: #01FF19;
}

:deep(.bg-\[#35A7FF\]) {
  background-color: #35A7FF;
}

:deep(.bg-\[#FF5964\]) {
  background-color: #FF5964;
}

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:bg-\[#FF5964\]\/90:hover) {
  background-color: rgba(255, 89, 100, 0.9);
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.border-\[#01FF19\]) {
  border-color: #01FF19;
}

:deep(.text-\[#01FF19\]) {
  color: #01FF19;
}

:deep(.hover\:bg-\[#01FF19\]:hover) {
  background-color: #01FF19;
}

:deep(.hover\:text-white:hover) {
  color: white;
}
</style>