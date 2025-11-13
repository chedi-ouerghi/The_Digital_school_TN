<script setup lang="ts">
import { ref, onMounted, computed, watch } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle,
  DialogFooter,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Tabs, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Alert, AlertDescription } from '@/components/ui/alert'

const router = useRouter()

// State
const clients = ref<any[]>([])
const requests = ref<any[]>([])
const loading = ref(false)
const search = ref('')
const activeTab = ref('clients') // 'clients' | 'requests'
const viewMode = ref<'grid'|'list'>('grid')

// Form dialogs
const formDialog = ref(false)
const deleteDialog = ref(false)
const editClient = ref<any>(null)
const clientToDelete = ref<any>(null)
const formLoading = ref(false)
const formError = ref('')
const formSuccess = ref('')

const formData = ref({
  name: '',
  email: '',
  role: 'CLIENT',
  balance_eur: 500
})

// Utility functions
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Data fetching
async function fetchClients() {
  loading.value = true
  try {
    const listFn = (api.admin as any).clients?.list
    if (!listFn) throw new Error('Client list method not found')
    
    const res = await listFn(1)
    clients.value = res.data || res.items || res || []
  } catch (err: any) {
    console.error('Error loading clients:', err)
    clients.value = []
  } finally {
    loading.value = false
  }
}

async function fetchAccountRequests() {
  loading.value = true
  try {
    const res = await api.admin.accountRequests.list()
    requests.value = res.data || res.items || res || []
  } catch (err: any) {
    console.error('Error loading requests:', err)
    requests.value = []
  } finally {
    loading.value = false
  }
}

// Computed
const stats = computed(() => ({
  totalClients: clients.value.length,
  totalSolde: clients.value.reduce((sum, c) => sum + Number( c.balance_eur || 0), 0),
  verifiedClients: clients.value.filter(c => c.email_verified_at).length,
  pendingRequests: requests.value.length
}))

const filteredClients = computed(() => {
  if (!search.value) return clients.value
  const searchLower = search.value.toLowerCase()
  return clients.value.filter(c => 
    c.name?.toLowerCase().includes(searchLower) ||
    c.email?.toLowerCase().includes(searchLower)
  )
})

// Lifecycle
onMounted(() => loadData())

watch(activeTab, () => loadData())

function loadData() {
  if (activeTab.value === 'clients') {
    fetchClients()
  } else {
    fetchAccountRequests()
  }
}

// Actions
function openCreateDialog() {
  editClient.value = null
  formData.value = {
    name: '',
    email: '',
    role: 'CLIENT',
    solde: 500
  }
  formError.value = ''
  formSuccess.value = ''
  formDialog.value = true
}

function openEditDialog(client: any) {
  editClient.value = client
  formData.value = {
    name: client.name,
    email: client.email,
    role: client.role || 'CLIENT',
    solde: client.solde || 0
  }
  formError.value = ''
  formSuccess.value = ''
  formDialog.value = true
}

async function handleFormSubmit() {
  formLoading.value = true
  formError.value = ''
  formSuccess.value = ''
  
  try {
    if (editClient.value) {
      await api.admin.clients.update(editClient.value.id, formData.value)
      formSuccess.value = 'Client updated successfully!'
    } else {
      await api.admin.clients.create(formData.value)
      formSuccess.value = 'Client created successfully!'
    }
    
    setTimeout(() => {
      formDialog.value = false
      fetchClients()
    }, 1500)
  } catch (err: any) {
    formError.value = err.message || 'An error occurred'
  } finally {
    formLoading.value = false
  }
}

function confirmDelete(client: any) {
  clientToDelete.value = client
  deleteDialog.value = true
}

async function handleDelete() {
  if (!clientToDelete.value?.id) return
  
  try {
    await api.admin.clients.delete(clientToDelete.value.id)
    deleteDialog.value = false
    clientToDelete.value = null
    await fetchClients()
  } catch (err: any) {
    alert(err.message || 'Error deleting client')
  }
}

async function approveRequest(id: number | string) {
  try {
    await api.admin.accountRequests.approve(id)
    await fetchAccountRequests()
  } catch (err: any) {
    alert(err.message || 'Error approving request')
  }
}

// Add rejectRequest: prompt optional reason, call backend, refresh list
async function rejectRequest(id: number | string) {
  try {
    const reason = window.prompt('Enter rejection reason (optional):') || undefined
    await api.admin.accountRequests.reject(id, reason)
    await fetchAccountRequests()
  } catch (err: any) {
    alert(err.message || 'Error rejecting request')
  }
}

function viewClientDetails(id: number) {
  router.push(`/dashboard/admin/clients/${id}`)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Client Management</h1>
        <p class="text-gray-500">Manage platform users and account requests</p>
      </div>
      <Button 
        v-if="activeTab === 'clients'"
        @click="openCreateDialog"
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
      >
        + New Client
      </Button>
    </div>

    <!-- Statistics (only for clients tab) -->
    <div v-if="activeTab === 'clients'" class="grid grid-cols-1 md:grid-cols-4 gap-4">
      <Card class="border-gray-200 hover:border-[#35A7FF] transition-colors bg-gradient-to-br from-[#35A7FF]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">👥</div>
          <div class="text-sm text-gray-500 mb-1">Total Clients</div>
          <div class="text-3xl font-bold text-[#35A7FF]">
            {{ stats.totalClients }}
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#01FF19] transition-colors bg-gradient-to-br from-[#01FF19]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">💰</div>
          <div class="text-sm text-gray-500 mb-1">Total Balance</div>
          <div class="text-2xl font-bold text-[#01FF19]">
            {{ formatCurrency(stats.totalSolde) }}
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#38618C] transition-colors bg-gradient-to-br from-[#38618C]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">✅</div>
          <div class="text-sm text-gray-500 mb-1">Verified</div>
          <div class="text-3xl font-bold text-[#38618C]">
            {{ stats.verifiedClients }}
          </div>
        </CardContent>
      </Card>

      <Card class="border-gray-200 hover:border-[#FF5964] transition-colors bg-gradient-to-br from-[#FF5964]/10 to-transparent">
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">⏳</div>
          <div class="text-sm text-gray-500 mb-1">Pending Requests</div>
          <div class="text-3xl font-bold text-[#FF5964]">
            {{ stats.pendingRequests }}
          </div>
        </CardContent>
      </Card>
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

    <!-- Search and filters (only for clients) -->
    <Card v-if="activeTab === 'clients'">
      <CardContent class="p-4">
        <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
          <div class="relative flex-1 max-w-md">
            <Input
              v-model="search"
              placeholder="🔍 Search by name or email..."
              class="pl-3 border-[#38618C] focus:border-[#35A7FF]"
            />
          </div>

          <div class="flex gap-3">
            <div class="flex border border-[#38618C] rounded-md overflow-hidden">
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'grid' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'grid'"
              >
                ⊞ Grid
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="viewMode === 'list' ? 'bg-[#35A7FF] text-white' : 'text-[#38618C]'"
                @click="viewMode = 'list'"
              >
                ☰ List
              </Button>
            </div>

            <Button 
              @click="loadData"
              :disabled="loading"
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
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
          <div>Loading {{ activeTab === 'clients' ? 'clients' : 'requests' }}...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Clients Content -->
    <div v-else-if="activeTab === 'clients'">
      <!-- Empty State -->
      <Card v-if="filteredClients.length === 0">
        <CardContent class="p-12 text-center">
          <div class="text-6xl mb-4">👥</div>
          <h3 class="text-xl font-semibold text-[#38618C] mb-2">No clients found</h3>
          <p class="text-gray-500 mb-6">
            {{ search ? 'Try adjusting your search' : 'Create your first client to get started' }}
          </p>
          <Button 
            v-if="!search"
            @click="openCreateDialog"
            class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
          >
            + Create First Client
          </Button>
        </CardContent>
      </Card>

  <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
  <Card 
    v-for="client in filteredClients" 
    :key="client.id"
    class="border border-gray-200 rounded-lg hover:border-[#35A7FF] transition-all hover:shadow-xl cursor-pointer group bg-white"
    @click="viewClientDetails(client.id)"
  >
    <CardContent class="p-6">
      <!-- Header avec nom et statut de vérification -->
      <div class="flex items-start justify-between mb-5">
        <div class="flex items-center gap-3">
          <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#35A7FF] to-[#38618C] flex items-center justify-center text-white font-bold text-lg">
            {{ client.name?.charAt(0).toUpperCase() || 'U' }}
          </div>
          <div class="max-w-[70%]">
            <h3 class="font-bold text-[#38618C] truncate">{{ client.name || 'Unknown' }}</h3>
            <p class="text-sm text-gray-500 truncate">{{ client.email }}</p>
          </div>
        </div>
        <Badge 
          :class="client.email_verified_at ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
          class="text-white text-xs"
        >
          {{ client.email_verified_at ? '✓' : '⏳ ' }}
        </Badge>
      </div>

      <!-- Informations principales -->
      <div class="space-y-4 mb-5">
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Balance</span>
          <span class="font-bold text-lg text-[#01FF19]">{{ formatCurrency(client.balance_eur) }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Role</span>
          <Badge class="bg-[#38618C] text-white">{{ client.role || 'CLIENT' }}</Badge>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Date d'échéance</span>
          <span class="text-sm text-[#38618C] font-medium">{{ formatDate(client.due_date) }}</span>
        </div>
        <div class="flex justify-between items-center">
          <span class="text-sm text-gray-500">Joined</span>
          <span class="text-sm text-[#38618C]">{{ formatDate(client.created_at) }}</span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex gap-2 pt-3 border-t border-gray-100">
        <Button 
          @click.stop="viewClientDetails(client.id)"
          size="sm"
          class="flex-1 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white text-sm"
        >
          📊 Details
        </Button>
        <Button 
          @click.stop="openEditDialog(client)"
          size="sm"
          variant="outline"
          class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
        >
          ✏️
        </Button>
        <Button 
          @click.stop="confirmDelete(client)"
          size="sm"
          variant="outline"
          class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
        >
          🗑️
        </Button>
      </div>
    </CardContent>
  </Card>
</div>

      <!-- List View -->
      <div v-else class="space-y-3">
        <Card 
          v-for="client in filteredClients" 
          :key="client.id"
          class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-lg cursor-pointer"
          @click="viewClientDetails(client.id)"
        >
          <CardContent class="p-4">
            <div class="flex items-center justify-between gap-4">
              <div class="flex items-center gap-4 flex-1">
                <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#35A7FF] to-[#38618C] flex items-center justify-center text-white font-bold flex-shrink-0">
                  {{ client.name?.charAt(0).toUpperCase() || 'U' }}
                </div>
                <div class="flex-1 min-w-0">
                  <h3 class="font-bold text-[#38618C] truncate">{{ client.name || 'Unknown' }}</h3>
                  <p class="text-sm text-gray-500 truncate">{{ client.email }}</p>
                </div>
              </div>

              <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                  <div class="text-xs text-gray-500">Balance</div>
                  <div class="font-bold text-[#01FF19]">{{ formatCurrency(client.balance_eur) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500">Role</div>
                  <Badge class="bg-[#38618C] text-white">{{ client.role || 'CLIENT' }}</Badge>
                </div>
                <div class="hidden sm:block">
                  <div class="text-xs text-gray-500">Status</div>
                  <Badge 
                    :class="client.email_verified_at ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                    class="text-white"
                  >
                    {{ client.email_verified_at ? 'Verified' : 'Pending' }}
                  </Badge>
                </div>
                <div class="hidden sm:block">
                  <div class="text-xs text-gray-500">Joined</div>
                  <div class="text-sm text-[#38618C]">{{ formatDate(client.created_at) }}</div>
                </div>
              </div>

              <div class="flex gap-2">
                <Button 
                  @click.stop="viewClientDetails(client.id)"
                  size="sm"
                  class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
                >
                  📊
                </Button>
                <Button 
                  @click.stop="openEditDialog(client)"
                  size="sm"
                  variant="outline"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
                >
                  ✏️
                </Button>
                <Button 
                  @click.stop="confirmDelete(client)"
                  size="sm"
                  variant="outline"
                  class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
                >
                  🗑️
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

    <!-- Account Requests Content -->
    <Card v-else-if="activeTab === 'requests'">
      <CardContent class="p-0">
        <div v-if="requests.length === 0" class="p-12 text-center">
          <div class="text-6xl mb-4">📭</div>
          <h3 class="text-xl font-semibold text-[#38618C] mb-2">No pending requests</h3>
          <p class="text-gray-500">All account requests have been processed</p>
        </div>
        <div v-else class="space-y-3 p-4">
          <Card 
            v-for="request in requests" 
            :key="request.id"
            class="border-gray-200 hover:border-[#35A7FF] transition-all"
          >
            <CardContent class="p-4">
              <div class="flex items-center justify-between gap-4">
                <div class="flex items-center gap-4 flex-1">
                  <div class="h-12 w-12 rounded-full bg-gradient-to-br from-[#FF5964] to-[#38618C] flex items-center justify-center text-white font-bold">
                    {{ request.name?.charAt(0).toUpperCase() || '?' }}
                  </div>
                  <div>
                    <h3 class="font-bold text-[#38618C]">{{ request.name || 'Unknown' }}</h3>
                    <p class="text-sm text-gray-500">{{ request.email }}</p>
                  </div>
                </div>

                <div class="flex items-center gap-4">
                  <div class="text-right">
                    <div class="text-xs text-gray-500">Requested on</div>
                    <div class="text-sm text-[#38618C]">{{ formatDate(request.created_at) }}</div>
                  </div>
                  <Button 
                    @click="approveRequest(request.id)"
                    class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
                  >
                    ✓ Approve
                  </Button>

                  <!-- Replaced duplicate approve button with reject button wired to rejectRequest -->
                  <Button 
                    @click="rejectRequest(request.id)"
                    class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white font-semibold"
                  >
                    ✗ Reject
                  </Button>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </CardContent>
    </Card>

    <!-- Form Dialog -->
    <Dialog :open="formDialog" @update:open="formDialog = false">
      <DialogContent class="sm:max-w-md border-[#35A7FF]">
        <DialogHeader>
          <DialogTitle class="text-[#38618C] text-xl">
            {{ editClient ? 'Edit Client' : 'New Client' }}
          </DialogTitle>
          <DialogDescription class="text-gray-600">
            {{ editClient ? 'Update client information' : 'Create a new client account' }}
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <Alert v-if="formError" class="border-[#FF5964] bg-[#FF5964]/10">
            <AlertDescription class="text-[#FF5964]">❌ {{ formError }}</AlertDescription>
          </Alert>
          
          <Alert v-if="formSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
            <AlertDescription class="text-[#01FF19]">✅ {{ formSuccess }}</AlertDescription>
          </Alert>

          <div class="space-y-2">
            <Label class="text-[#38618C] font-semibold">Full Name</Label>
            <Input
              v-model="formData.name"
              placeholder="John Doe"
              class="border-[#38618C] focus:border-[#35A7FF]"
              :disabled="formLoading"
            />
          </div>

          <div class="space-y-2">
            <Label class="text-[#38618C] font-semibold">Email Address</Label>
            <Input
              v-model="formData.email"
              type="email"
              placeholder="john@example.com"
              class="border-[#38618C] focus:border-[#35A7FF]"
              :disabled="formLoading"
            />
          </div>

          <div class="space-y-2">
            <Label class="text-[#38618C] font-semibold">Role</Label>
            <Select v-model="formData.role" :disabled="formLoading">
              <SelectTrigger class="border-[#38618C] focus:border-[#35A7FF]">
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="CLIENT">CLIENT</SelectItem>
                <SelectItem value="ADMIN">ADMIN</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-[#38618C] font-semibold">Initial Balance (€)</Label>
            <Input
              v-model.number="formData.solde"
              type="number"
              min="0"
              step="0.01"
              class="border-[#38618C] focus:border-[#35A7FF]"
              :disabled="formLoading"
            />
          </div>
        </div>

        <DialogFooter class="flex gap-2 sm:gap-0">
          <Button 
            variant="outline" 
            @click="formDialog = false"
            class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
            :disabled="formLoading"
          >
            ✕ Cancel
          </Button>
          <Button 
            @click="handleFormSubmit"
            class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold flex-1 sm:flex-none"
            :disabled="formLoading"
          >
            {{ formLoading ? '⏳ Saving...' : (editClient ? '✓ Update' : '✓ Create') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <Dialog :open="deleteDialog" @update:open="deleteDialog = false">
      <DialogContent class="sm:max-w-md border-[#FF5964]">
        <DialogHeader>
          <DialogTitle class="text-[#FF5964] text-xl">⚠️ Confirm Deletion</DialogTitle>
          <DialogDescription class="text-gray-600">
            Are you sure you want to delete <strong>{{ clientToDelete?.name }}</strong>?
          </DialogDescription>
        </DialogHeader>

        <Alert class="border-[#FF5964] bg-[#FF5964]/10">
          <AlertDescription class="text-[#FF5964]">
            ⚠️ This action is irreversible and will delete all associated data.
          </AlertDescription>
        </Alert>

        <DialogFooter class="flex gap-2 sm:gap-0">
          <Button 
            variant="outline" 
            @click="deleteDialog = false"
            class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
          >
            ✕ Cancel
          </Button>
          <Button 
            @click="handleDelete"
            class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white font-semibold flex-1 sm:flex-none"
          >
            🗑️ Delete
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>
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

:deep(.hover\:bg-\[#38618C\]:hover) {
  background-color: #38618C;
}

:deep(.hover\:border-\[#35A7FF\]:hover) {
  border-color: #35A7FF;
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}
</style>