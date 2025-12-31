<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../services/api'

// Import des composants shadcn-vue
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'
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
import { Alert, AlertDescription } from '@/components/ui/alert'

const router = useRouter()

// State
const clients = ref<any[]>([])
const loading = ref(false)
const search = ref('')
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

// Image URL helpers (comme dans le dashboard layout)
const getProfilePictureUrl = (profilePicture: string | null | undefined) => {
  if (!profilePicture) return null
  if (profilePicture.startsWith('http')) return profilePicture
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${profilePicture}`
}

const getProfileBannerUrl = (profileBanner: string | null | undefined) => {
  if (!profileBanner) return null
  if (profileBanner.startsWith('http')) return profileBanner
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${profileBanner}`
}

// small image error handler
function handleImgError(e: Event) {
  const t = e.target as HTMLImageElement | null
  if (t) t.style.display = 'none'
}

// Initiales pour l'avatar fallback
const getClientInitials = (name: string | undefined) => {
  if (!name) return 'U'
  return name
    .split(' ')
    .map(word => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
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

// Computed
const stats = computed(() => ({
  totalClients: clients.value.length,
  totalBalance: clients.value.reduce((sum, c) => sum + Number(c.balance_eur || 0), 0),
  verifiedClients: clients.value.filter(c => c.email_verified_at !== null).length,
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
onMounted(() => {
  fetchClients()
})

// Actions
function openCreateDialog() {
  editClient.value = null
  formData.value = {
    name: '',
    email: '',
    role: 'CLIENT',
    balance_eur: 500
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
    balance_eur: client.balance_eur || 0
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

function viewClientDetails(id: number) {
  router.push(`/dashboard/admin/clients/${id}`)
}
</script>

<template>
  <div>
    <!-- Statistics -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
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
            {{ formatCurrency(stats.totalBalance) }}
          </div>
        </CardContent>
      </Card>

      <Card
        :class="stats.verifiedClients > 0 
          ? 'border-gray-200 hover:border-[#38618C] transition-colors bg-gradient-to-br from-[#38618C]/10 to-transparent'
          : 'border-gray-200 hover:border-red-500 transition-colors bg-gradient-to-br from-red-200/20 to-transparent'"
      >
        <CardContent class="p-6 text-center">
          <div class="text-4xl mb-2">
            {{ stats.verifiedClients > 0 ? '✅' : '⏳' }}
          </div>
          <div class="text-sm text-gray-500 mb-1">
            {{ stats.verifiedClients > 0 ? 'Verified' : 'Pending' }}
          </div>
          <div
class="text-3xl font-bold"
              :class="stats.verifiedClients > 0 ? 'text-[#38618C]' : 'text-red-500'">
            {{ stats.verifiedClients }}
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Search and filters -->
    <Card class="mb-6">
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
            <Button 
              class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
              @click="openCreateDialog"
            >
              + New Client
            </Button>

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
              :disabled="loading"
              class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
              @click="fetchClients"
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
          <div>Loading clients...</div>
        </div>
      </CardContent>
    </Card>

    <!-- Clients Content -->
    <div v-else>
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
            class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
            @click="openCreateDialog"
          >
            + Create First Client
          </Button>
        </CardContent>
      </Card>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <Card 
          v-for="client in filteredClients" 
          :key="client.id"
          class="border border-gray-200 rounded-lg hover:border-[#35A7FF] transition-all hover:shadow-xl cursor-pointer group bg-white overflow-hidden"
          @click="viewClientDetails(client.id)"
        >
          <CardContent class="p-0">
            <!-- Banner (grid only) -->
            <div v-if="getProfileBannerUrl(client.profile_banner)" class="relative w-full h-32 bg-gradient-to-br from-[#35A7FF] to-[#38618C] overflow-hidden">
              <img
                :src="getProfileBannerUrl(client.profile_banner)"
                alt="banner"
                class="w-full h-full object-cover"
                @error="handleImgError"
              />
            </div>
            <div v-else class="w-full h-32 bg-gradient-to-br from-[#35A7FF] to-[#38618C']"></div>

            <!-- Content avec padding -->
            <div class="p-6">
              <!-- Header avec avatar + nom + vérification -->
              <div class="flex items-start justify-between mb-5 -mt-10">
                <div class="flex items-center gap-3">
                  <!-- Avatar avec bordure blanche -->
                  <Avatar class="h-16 w-16 border-4 border-white shadow-lg">
                    <template v-if="getProfilePictureUrl(client.profile_picture)">
                      <AvatarImage 
                        :src="getProfilePictureUrl(client.profile_picture)"
                        :alt="client.name || 'User'"
                        class="object-cover"
                        @error="handleImgError"
                      />
                    </template>
                     <AvatarFallback class="bg-gradient-to-br from-[#35A7FF] to-[#38618C] text-white font-bold text-xl">
                       {{ getClientInitials(client.name) }}
                     </AvatarFallback>
                  </Avatar>
                  
                  <div class="mt-10">
                    <h3 class="font-bold text-[#38618C] text-lg">{{ client.name || 'Unknown' }}</h3>
                    <p class="text-sm text-gray-500 truncate">{{ client.email }}</p>
                  </div>
                </div>
                
                <Badge
                  :class="client.email_verified_at === null ? 'bg-[#FF5964]' : 'bg-[#01FF19]'"
                  class="text-white text-xs mt-12"
                >
                  {{ client.email_verified_at === null ? '⏳' : '✓' }}
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
                  <span class="text-sm text-gray-500">Due Date</span>
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
                  size="sm"
                  class="flex-1 bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white text-sm"
                  @click.stop="viewClientDetails(client.id)"
                >
                  📊 Details
                </Button>
                <Button 
                  size="sm"
                  variant="outline"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
                  @click.stop="openEditDialog(client)"
                >
                  ✏️
                </Button>
                <Button 
                  size="sm"
                  variant="outline"
                  class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
                  @click.stop="confirmDelete(client)"
                >
                  🗑️
                </Button>
              </div>
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
                <!-- Avatar uniquement en mode liste -->
                <Avatar class="h-12 w-12 border-2 border-gray-200">
                  <template v-if="getProfilePictureUrl(client.profile_picture)">
                    <AvatarImage 
                      :src="getProfilePictureUrl(client.profile_picture)"
                      :alt="client.name || 'User'"
                      class="object-cover"
                      @error="handleImgError"
                    />
                  </template>
                  <AvatarFallback class="bg-gradient-to-br from-[#35A7FF] to-[#38618C] text-white font-bold">
                    {{ getClientInitials(client.name) }}
                  </AvatarFallback>
                </Avatar>
                
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
                    :class="client.email_verified_at === null ? 'bg-[#FF5964]' : 'bg-[#01FF19]'"
                    class="text-white"
                  >
                    {{ client.email_verified_at === null ? 'Pending' : 'Verified' }}
                  </Badge>
                </div>
                <div class="hidden sm:block">
                  <div class="text-xs text-gray-500">Joined</div>
                  <div class="text-sm text-[#38618C]">{{ formatDate(client.created_at) }}</div>
                </div>
              </div>

              <div class="flex gap-2">
                <Button 
                  size="sm"
                  class="bg-[#35A7FF] hover:bg-[#35A7FF]/90 text-white"
                  @click.stop="viewClientDetails(client.id)"
                >
                  📊
                </Button>
                <Button 
                  size="sm"
                  variant="outline"
                  class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
                  @click.stop="openEditDialog(client)"
                >
                  ✏️
                </Button>
                <Button 
                  size="sm"
                  variant="outline"
                  class="border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white"
                  @click.stop="confirmDelete(client)"
                >
                  🗑️
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>
    </div>

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
              v-model.number="formData.balance_eur"
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
            class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
            :disabled="formLoading"
            @click="formDialog = false"
          >
            ✕ Cancel
          </Button>
          <Button 
            class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold flex-1 sm:flex-none"
            :disabled="formLoading"
            @click="handleFormSubmit"
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
            class="border-gray-300 text-gray-600 hover:bg-gray-50 flex-1 sm:flex-none"
            @click="deleteDialog = false"
          >
            ✕ Cancel
          </Button>
          <Button 
            class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white font-semibold flex-1 sm:flex-none"
            @click="handleDelete"
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

:deep(.bg-\[#35A7FF\]) {
  background-color: #35A7FF;
}

:deep(.bg-\[#01FF19\]) {
  background-color: #01FF19;
}

:deep(.bg-\[#FF5964\]) {
  background-color: #FF5964;
}

:deep(.hover\:bg-\[#35A7FF\]\/90:hover) {
  background-color: rgba(53, 167, 255, 0.9);
}

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.hover\:bg-\[#FF5964\]\/90:hover) {
  background-color: rgba(255, 89, 100, 0.9);
}

:deep(.focus\:border-\[#35A7FF\]:focus) {
  border-color: #35A7FF;
}
</style>