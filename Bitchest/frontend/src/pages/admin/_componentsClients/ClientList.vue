<script setup lang="ts">
import {
  AlertDialog,
  AlertDialogTrigger,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { computed, ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../../services/api'
import {
  Search,
  Filter,
  Grid3x3,
  List,
  RefreshCw,
  TrendingUp,
  TrendingDown,
  BarChart3,
  ChevronLeft,
  ChevronRight,
  ChevronsLeft,
  ChevronsRight,
  ExternalLink,
  Users,
  Wallet,
  CheckCircle,
  Clock,
  UserPlus,
  Mail,
  Calendar,
  CreditCard,
  AlertCircle,
  User,
  Edit,
  Trash2
} from 'lucide-vue-next'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Avatar, AvatarImage, AvatarFallback } from '@/components/ui/avatar'

const router = useRouter()

// États
const clients = ref<any[]>([])
const loading = ref(false)
const query = ref('')
const sortBy = ref<'name'|'email'|'balance'|'date'>('date')
const viewMode = ref<'grid'|'list'>('grid')
const formLoading = ref(false)
const formDialog = ref(false)
const deleteDialog = ref(false)
const editClient = ref<any>(null)
const clientToDelete = ref<any>(null)
const formError = ref('')
const formSuccess = ref('')

// Pagination
const currentPage = ref(1)
const totalPages = ref(1)
const totalClients = ref(0)
const perPage = ref(20)

// Form data
const formData = ref({
  name: '',
  email: '',
  role: 'CLIENT',
  balance_eur: 500
})

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return new Intl.NumberFormat('fr-FR', { 
    style: 'currency', 
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(n)
}

function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

function formatPercentage(value: any): string {
  const n = Number(value ?? 0)
  return `${n >= 0 ? '+' : ''}${n.toFixed(2)}`
}

// Image URL helpers
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

// Gestion des erreurs d'image
function handleImgError(e: Event) {
  const target = e.target as HTMLImageElement
  if (target) {
    target.style.display = 'none'
  }
}

// Data fetching
async function fetchClients(page: number = 1) {
  loading.value = true
  try {
    const res = await api.admin.clients.list(page)
    
    // Gestion de la réponse paginée
    clients.value = res.data || []
    totalClients.value = res.total || 0
    totalPages.value = res.last_page || 1
    currentPage.value = res.current_page || page
    perPage.value = res.per_page || 20
  } catch (err: any) {
    console.error('Error loading clients:', err)
    clients.value = []
  } finally {
    loading.value = false
  }
}

// Computed
const stats = computed(() => ({
  totalClients: totalClients.value,
  totalBalance: clients.value.reduce((sum, c) => sum + Number(c.balance_eur || 0), 0),
  verifiedClients: clients.value.filter(c => c.email_verified_at !== null).length,
  pendingClients: clients.value.filter(c => c.email_verified_at === null).length,
  averageBalance: totalClients.value > 0 
    ? clients.value.reduce((sum, c) => sum + Number(c.balance_eur || 0), 0) / totalClients.value 
    : 0
}))

const filteredClients = computed(() => {
  let list = clients.value.slice()
  
  // Filtrage par recherche
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(c => 
      (c.name || '').toLowerCase().includes(q) || 
      (c.email || '').toLowerCase().includes(q)
    )
  }
  
  // Tri
  list.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return (a.name || '').localeCompare(b.name || '')
      case 'email':
        return (a.email || '').localeCompare(b.email || '')
      case 'balance':
        return Number(b.balance_eur || 0) - Number(a.balance_eur || 0)
      case 'date':
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
      default:
        return 0
    }
  })
  
  return list
})

// Lifecycle
onMounted(() => {
  fetchClients(1)
})

// Pagination functions
function changePage(page: number) {
  if (page >= 1 && page <= totalPages.value) {
    fetchClients(page)
  }
}

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
      fetchClients(currentPage.value)
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
    await fetchClients(currentPage.value)
  } catch (err: any) {
    formError.value = err.message || 'Error deleting client'
  }
}

function viewClientDetails(id: string) {
  router.push(`/dashboard/admin/clients/${id}`)
}
</script>

<template>
  <div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <!-- Total Clients -->
      <Card class="border border-gray-200 hover:border-blue-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Clients</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalClients }}</p>
              <p class="text-xs text-gray-500 mt-1">All registered users</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <Users class="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Total Balance -->
      <Card class="border border-gray-200 hover:border-emerald-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Total Balance</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ formatCurrency(stats.totalBalance) }}</p>
              <p class="text-xs text-gray-500 mt-1">Combined client funds</p>
            </div>
            <div class="w-12 h-12 bg-emerald-100 rounded-full flex items-center justify-center">
              <Wallet class="h-6 w-6 text-emerald-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Verified Clients -->
      <Card class="border border-gray-200 hover:border-green-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Verified Clients</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.verifiedClients }}</p>
              <p class="text-xs text-gray-500 mt-1">Email verified accounts</p>
            </div>
            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
              <CheckCircle class="h-6 w-6 text-green-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pending Clients -->
      <Card class="border border-gray-200 hover:border-amber-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Pending Clients</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.pendingClients }}</p>
              <p class="text-xs text-gray-500 mt-1">Awaiting verification</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
              <Clock class="h-6 w-6 text-amber-600" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters and Search -->
    <Card class="border border-gray-200">
      <CardHeader class="pb-4">
        <CardTitle class="text-lg font-semibold text-gray-900">Filters & Search</CardTitle>
        <p class="text-sm text-gray-500">Refine client results</p>
      </CardHeader>
      <CardContent>
        <div class="flex flex-col lg:flex-row gap-4">
          <!-- Search -->
          <div class="flex-1">
            <div class="relative">
              <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 h-4 w-4" />
              <Input
                v-model="query"
                placeholder="Search by name or email..."
                class="pl-10"
              />
            </div>
          </div>
          
          <!-- Filters -->
          <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <Select v-model="sortBy">
              <SelectTrigger>
                <div class="flex items-center gap-2">
                  <Filter class="h-4 w-4" />
                  <SelectValue placeholder="Sort by" />
                </div>
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="date">Newest First</SelectItem>
                <SelectItem value="name">Name A-Z</SelectItem>
                <SelectItem value="balance">Balance (High to Low)</SelectItem>
                <SelectItem value="email">Email A-Z</SelectItem>
              </SelectContent>
            </Select>
            
            <!-- View Toggle -->
            <div class="flex border rounded-lg overflow-hidden bg-gray-50">
              <Button
                variant="ghost"
                size="sm"
                :class="[
                  'rounded-none px-4',
                  viewMode === 'grid' 
                    ? 'bg-white text-gray-900 shadow-sm border' 
                    : 'text-gray-600 hover:text-gray-900'
                ]"
                @click="viewMode = 'grid'"
              >
                <Grid3x3 class="h-4 w-4 mr-2" />
                Grid
              </Button>
              <Button
                variant="ghost"
                size="sm"
                :class="[
                  'rounded-none px-4',
                  viewMode === 'list' 
                    ? 'bg-white text-gray-900 shadow-sm border' 
                    : 'text-gray-600 hover:text-gray-900'
                ]"
                @click="viewMode = 'list'"
              >
                <List class="h-4 w-4 mr-2" />
                List
              </Button>
            </div>
            
            <!-- Actions -->
            <div class="flex gap-2">
              <Button 
                variant="outline"
                size="sm"
                class="gap-2"
                :disabled="loading"
                @click="fetchClients(currentPage)"
              >
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                Refresh
              </Button>
              <Button 
                class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white gap-2"
                @click="openCreateDialog"
              >
                <UserPlus class="h-4 w-4" />
                New Client
              </Button>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Loading State -->
    <Card v-if="loading" class="border border-gray-200">
      <CardContent class="p-12">
        <div class="text-center space-y-4">
          <div class="w-12 h-12 border-4 border-blue-500/20 border-t-blue-500 rounded-full animate-spin mx-auto"></div>
          <div class="space-y-2">
            <p class="text-lg font-medium text-gray-900">Loading Clients</p>
            <p class="text-sm text-gray-500">Fetching client data...</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredClients.length === 0" class="border border-gray-200">
      <CardContent class="p-12 text-center">
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <Search class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No clients found</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          {{ query ? 'No clients match your search criteria. Try adjusting your search.' : 'No clients available on the platform yet.' }}
        </p>
        <div class="flex gap-3 justify-center">
          <Button 
            v-if="query"
            variant="outline"
            @click="query = ''"
          >
            Clear Search
          </Button>
          <Button 
            class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white gap-2"
            @click="openCreateDialog"
          >
            <UserPlus class="h-4 w-4" />
            Add First Client
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Grid View -->
    <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <Card 
        v-for="client in filteredClients" 
        :key="client.id"
        class="group border border-gray-200 hover:border-blue-300 hover:shadow-xl transition-all duration-300 cursor-pointer"
        @click="viewClientDetails(client.id)"
      >
        <CardContent class="p-6">
          <!-- Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="relative">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border-2 border-gray-300 group-hover:border-blue-300 transition-colors flex items-center justify-center">
                  <Avatar class="h-10 w-10">
                    <template v-if="getProfilePictureUrl(client.profile_picture)">
                      <AvatarImage 
                        :src="getProfilePictureUrl(client.profile_picture)"
                        :alt="client.name"
                        class="object-cover"
                        @error="handleImgError"
                      />
                    </template>
                    <AvatarFallback class="bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold">
                      {{ getClientInitials(client.name) }}
                    </AvatarFallback>
                  </Avatar>
                </div>
              </div>
              <div>
                <h3 class="font-bold text-gray-900 text-lg">{{ client.name || 'Unknown User' }}</h3>
                <div class="flex items-center gap-2 mt-1">
                  <Badge 
                    :class="[
                      'text-xs',
                      client.role === 'ADMIN' 
                        ? 'bg-purple-100 text-purple-700 border-purple-200' 
                        : 'bg-blue-100 text-blue-700 border-blue-200'
                    ]"
                  >
                    {{ client.role || 'CLIENT' }}
                  </Badge>
                  <Badge 
                    :class="[
                      'text-xs',
                      client.email_verified_at 
                        ? 'bg-green-100 text-green-700 border-green-200' 
                        : 'bg-amber-100 text-amber-700 border-amber-200'
                    ]"
                  >
                    {{ client.email_verified_at ? 'Verified' : 'Pending' }}
                  </Badge>
                </div>
              </div>
            </div>
            <Button
              size="sm"
              variant="ghost"
              class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity"
              title="Edit Client"
              @click.stop="openEditDialog(client)"
            >
              <Edit class="h-4 w-4" />
            </Button>
          </div>

          <!-- Client Info -->
          <div class="space-y-4 mb-6">
            <div class="flex items-center gap-2 text-gray-600">
              <Mail class="h-4 w-4" />
              <span class="text-sm truncate">{{ client.email }}</span>
            </div>
            
            <div>
              <div class="text-sm text-gray-500 mb-1">Account Balance</div>
              <div class="text-2xl font-bold text-emerald-600">
                {{ formatCurrency(client.balance_eur) }}
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <div class="text-xs text-gray-500 mb-1">Member Since</div>
                <div class="text-sm font-semibold text-gray-900">
                  {{ formatDate(client.created_at) }}
                </div>
              </div>
              <div>
                <div class="text-xs text-gray-500 mb-1">Status</div>
                <div class="text-sm font-semibold text-gray-900 flex items-center gap-1">
                  <CheckCircle v-if="client.email_verified_at" class="h-3 w-3 text-green-500" />
                  <Clock v-else class="h-3 w-3 text-amber-500" />
                  {{ client.email_verified_at ? 'Active' : 'Pending' }}
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex gap-2">
            <Button 
              class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white gap-2"
              @click.stop="viewClientDetails(client.id)"
            >
              <BarChart3 class="h-4 w-4" />
              View Details
            </Button>
            <Button 
              size="sm"
              variant="outline"
              class="border-red-300 text-red-600 hover:bg-red-50 hover:text-red-700"
              title="Delete Client"
              @click.stop="confirmDelete(client)"
            >
              <Trash2 class="h-4 w-4" />
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- List View -->
    <div v-else class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50 border-b">
            <tr>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Client</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Email</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Balance</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Status</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Joined</th>
              <th class="text-left py-3 px-6 text-sm font-medium text-gray-700">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr 
              v-for="client in filteredClients" 
              :key="client.id"
              class="hover:bg-gray-50 transition-colors group cursor-pointer"
              @click="viewClientDetails(client.id)"
            >
              <td class="py-4 px-6">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 border border-gray-300 flex items-center justify-center">
                    <Avatar class="h-8 w-8">
                      <template v-if="getProfilePictureUrl(client.profile_picture)">
                        <AvatarImage 
                          :src="getProfilePictureUrl(client.profile_picture)"
                          :alt="client.name"
                          class="object-cover"
                          @error="handleImgError"
                        />
                      </template>
                      <AvatarFallback class="bg-gradient-to-br from-blue-500 to-blue-600 text-white font-bold text-xs">
                        {{ getClientInitials(client.name) }}
                      </AvatarFallback>
                    </Avatar>
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ client.name || 'Unknown User' }}</div>
                    <Badge 
                      :class="[
                        'text-xs mt-1',
                        client.role === 'ADMIN' 
                          ? 'bg-purple-100 text-purple-700 border-purple-200' 
                          : 'bg-blue-100 text-blue-700 border-blue-200'
                      ]"
                    >
                      {{ client.role || 'CLIENT' }}
                    </Badge>
                  </div>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex items-center gap-2 text-gray-700">
                  <Mail class="h-4 w-4 text-gray-400" />
                  <span class="text-sm">{{ client.email }}</span>
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="font-bold text-emerald-600">
                  {{ formatCurrency(client.balance_eur) }}
                </div>
              </td>
              <td class="py-4 px-6">
                <Badge 
                  :class="[
                    'font-medium',
                    client.email_verified_at 
                      ? 'bg-green-50 text-green-700 border-green-200' 
                      : 'bg-amber-50 text-amber-700 border-amber-200'
                  ]"
                >
                  <component 
                    :is="client.email_verified_at ? CheckCircle : Clock" 
                    class="h-3 w-3 mr-1.5" 
                  />
                  {{ client.email_verified_at ? 'Verified' : 'Pending' }}
                </Badge>
              </td>
              <td class="py-4 px-6">
                <div class="text-sm text-gray-900">
                  {{ formatDate(client.created_at) }}
                </div>
              </td>
              <td class="py-4 px-6">
                <div class="flex gap-2">
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-8 w-8 p-0"
                    title="View Details"
                    @click.stop="viewClientDetails(client.id)"
                  >
                    <ExternalLink class="h-4 w-4" />
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-8 w-8 p-0"
                    title="Edit Client"
                    @click.stop="openEditDialog(client)"
                  >
                    <Edit class="h-4 w-4" />
                  </Button>
                  <Button
                    size="sm"
                    variant="ghost"
                    class="h-8 w-8 p-0 text-red-600 hover:text-red-700 hover:bg-red-50"
                    title="Delete Client"
                    @click.stop="confirmDelete(client)"
                  >
                    <Trash2 class="h-4 w-4" />
                  </Button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
      <div class="text-sm text-gray-600">
        Page {{ currentPage }} of {{ totalPages }} • {{ totalClients }} total clients
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="currentPage === 1"
          @click="changePage(1)"
        >
          <ChevronsLeft class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="currentPage === 1"
          @click="changePage(currentPage - 1)"
        >
          <ChevronLeft class="h-4 w-4" />
        </Button>
        
        <div class="flex items-center gap-1">
          <Button
            v-for="page in Math.min(5, totalPages)"
            :key="page"
            :variant="currentPage === page ? 'default' : 'outline'"
            size="sm"
            class="h-8 w-8 p-0"
            @click="changePage(page)"
          >
            {{ page }}
          </Button>
        </div>
        
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="currentPage === totalPages"
          @click="changePage(currentPage + 1)"
        >
          <ChevronRight class="h-4 w-4" />
        </Button>
        <Button
          variant="outline"
          size="sm"
          class="h-8 w-8 p-0"
          :disabled="currentPage === totalPages"
          @click="changePage(totalPages)"
        >
          <ChevronsRight class="h-4 w-4" />
        </Button>
      </div>
    </div>

    <!-- Create/Edit Client Dialog -->
    <Dialog :open="formDialog" @update:open="formDialog = false">
      <DialogContent class="sm:max-w-md border border-gray-200">
        <DialogHeader>
          <DialogTitle class="text-lg font-semibold text-gray-900">
            {{ editClient ? 'Edit Client' : 'New Client' }}
          </DialogTitle>
          <DialogDescription class="text-gray-600">
            {{ editClient ? 'Update client information' : 'Create a new client account' }}
          </DialogDescription>
        </DialogHeader>

        <div class="space-y-4 py-4">
          <Alert v-if="formError" class="border-red-200 bg-red-50">
            <AlertDescription class="text-red-700 flex items-center gap-2">
              <AlertCircle class="h-4 w-4" />
              {{ formError }}
            </AlertDescription>
          </Alert>
          
          <Alert v-if="formSuccess" class="border-emerald-200 bg-emerald-50">
            <AlertDescription class="text-emerald-700 flex items-center gap-2">
              <CheckCircle class="h-4 w-4" />
              {{ formSuccess }}
            </AlertDescription>
          </Alert>

          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">Full Name</Label>
            <Input
              v-model="formData.name"
              placeholder="John Doe"
              class="border-gray-300 focus:border-blue-500"
              :disabled="formLoading"
            />
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">Email Address</Label>
            <Input
              v-model="formData.email"
              type="email"
              placeholder="john@example.com"
              class="border-gray-300 focus:border-blue-500"
              :disabled="formLoading"
            />
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">Role</Label>
            <Select v-model="formData.role" :disabled="formLoading">
              <SelectTrigger class="border-gray-300 focus:border-blue-500">
                <SelectValue placeholder="Select a role" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="CLIENT">CLIENT</SelectItem>
                <SelectItem value="ADMIN">ADMIN</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">Initial Balance (€)</Label>
            <Input
              v-model.number="formData.balance_eur"
              type="number"
              min="0"
              step="0.01"
              placeholder="500.00"
              class="border-gray-300 focus:border-blue-500"
              :disabled="formLoading"
            />
          </div>
        </div>

        <DialogFooter class="flex gap-2">
          <Button 
            variant="outline" 
            class="border-gray-300 text-gray-700 hover:bg-gray-50"
            :disabled="formLoading"
            @click="formDialog = false"
          >
            Cancel
          </Button>
          <Button 
            class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white"
            :disabled="formLoading"
            @click="handleFormSubmit"
          >
            {{ formLoading ? 'Saving...' : (editClient ? 'Update Client' : 'Create Client') }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Delete Dialog -->
    <AlertDialog :open="deleteDialog" @update:open="deleteDialog = false">
      <AlertDialogContent class="border border-red-200">
        <AlertDialogHeader>
          <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
            <AlertCircle class="h-6 w-6 text-red-600" />
          </div>
          <AlertDialogTitle class="text-center text-red-700">Delete Client</AlertDialogTitle>
          <AlertDialogDescription class="text-center text-gray-600">
            Are you sure you want to delete <strong class="text-gray-900">{{ clientToDelete?.name }}</strong>?
            <br><br>
            This action cannot be undone and will permanently delete all associated data.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="border-gray-300">Cancel</AlertDialogCancel>
          <AlertDialogAction
            class="bg-red-600 hover:bg-red-700 text-white"
            @click="handleDelete"
          >
            Delete Client
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>