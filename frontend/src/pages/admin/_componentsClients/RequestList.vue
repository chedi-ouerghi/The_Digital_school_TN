<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../../../services/api'

// Import des composants shadcn-vue
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  AlertDialog,
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
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import {
  AlertCircle,
  Calendar,
  CheckCircle,
  Clock,
  Copy,
  Eye,
  EyeOff,
  Filter,
  Mail,
  RefreshCw,
  Search,
  Shield,
  UserCheck,
  UserX,
  Users
} from 'lucide-vue-next'

// Émissions d'événements
const emit = defineEmits<{
  'requests-updated': []
}>()

// State
const requests = ref<any[]>([])
const loading = ref(false)
const query = ref('')
const sortBy = ref<'date'|'name'|'email'>('date')

// Password dialog state
const approveDialog = ref(false)
const selectedRequest = ref<any>(null)
const temporaryPassword = ref('')
const passwordError = ref('')
const passwordSuccess = ref('')
const isApproving = ref(false)
const showPassword = ref(false)

// Nouveaux états pour l'édition du mot de passe
const isEditingPassword = ref(false)
const originalPassword = ref('')

// Reject dialog state
const rejectDialog = ref(false)
const rejectReason = ref('')
const isRejecting = ref(false)

// Computed
const filteredRequests = computed(() => {
  let list = requests.value.slice()
  
  // Filtrage par recherche
  if (query.value) {
    const q = query.value.toLowerCase()
    list = list.filter(req => 
      (req.name || '').toLowerCase().includes(q) || 
      (req.email || '').toLowerCase().includes(q)
    )
  }
  
  // Tri
  list.sort((a, b) => {
    switch (sortBy.value) {
      case 'name':
        return (a.name || '').localeCompare(b.name || '')
      case 'email':
        return (a.email || '').localeCompare(b.email || '')
      case 'date':
      default:
        return new Date(b.created_at).getTime() - new Date(a.created_at).getTime()
    }
  })
  
  return list
})

const stats = computed(() => ({
  totalRequests: requests.value.length,
  thisWeekRequests: requests.value.filter(req => {
    const weekAgo = new Date()
    weekAgo.setDate(weekAgo.getDate() - 7)
    return new Date(req.created_at) > weekAgo
  }).length,
  oldestRequest: requests.value.length > 0 
    ? Math.min(...requests.value.map(req => 
        Math.floor((new Date().getTime() - new Date(req.created_at).getTime()) / (1000 * 60 * 60 * 24))
      ))
    : 0
}))

// Utility functions
function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatDuration(days: number): string {
  if (days === 0) return 'Today'
  if (days === 1) return '1 day'
  return `${days} days`
}

// Générer un mot de passe temporaire
function generateTemporaryPassword(): string {
  const uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'
  const lowercase = 'abcdefghijklmnopqrstuvwxyz'
  const numbers = '0123456789'
  const special = '!@#$%^&*'
  const all = uppercase + lowercase + numbers + special

  let password = ''
  password += uppercase[Math.floor(Math.random() * uppercase.length)]
  password += lowercase[Math.floor(Math.random() * lowercase.length)]
  password += numbers[Math.floor(Math.random() * numbers.length)]
  password += special[Math.floor(Math.random() * special.length)]

  for (let i = password.length; i < 12; i++) {
    password += all[Math.floor(Math.random() * all.length)]
  }

  return password
    .split('')
    .sort(() => Math.random() - 0.5)
    .join('')
}

// Copy to clipboard
function copyToClipboard() {
  navigator.clipboard.writeText(temporaryPassword.value)
}

// Validation du mot de passe
const isPasswordValid = computed(() => {
  if (!temporaryPassword.value) return false
  
  const hasUppercase = /[A-Z]/.test(temporaryPassword.value)
  const hasLowercase = /[a-z]/.test(temporaryPassword.value)
  const hasNumber = /[0-9]/.test(temporaryPassword.value)
  const hasSpecial = /[!@#$%^&*]/.test(temporaryPassword.value)
  const hasMinLength = temporaryPassword.value.length >= 8
  
  return hasUppercase && hasLowercase && hasNumber && hasSpecial && hasMinLength
})

// Fonction pour vérifier chaque exigence
function checkRequirement(type: string): boolean {
  switch (type) {
    case 'length':
      return temporaryPassword.value.length >= 8
    case 'uppercase':
      return /[A-Z]/.test(temporaryPassword.value)
    case 'lowercase':
      return /[a-z]/.test(temporaryPassword.value)
    case 'number':
      return /[0-9]/.test(temporaryPassword.value)
    case 'special':
      return /[!@#$%^&*]/.test(temporaryPassword.value)
    default:
      return false
  }
}

// Data fetching
async function fetchAccountRequests() {
  loading.value = true
  try {
    const res = await api.admin.accountRequests.list()
    requests.value = (res as any).data || (res as any).items || (res as any) || []
    // Émettre l'événement de mise à jour
    emit('requests-updated')
  } catch (err: any) {
    console.error('Error loading requests:', err)
    requests.value = []
  } finally {
    loading.value = false
  }
}

// Actions Dialog
function openApproveDialog(request: any) {
  selectedRequest.value = request
  temporaryPassword.value = generateTemporaryPassword()
  passwordError.value = ''
  passwordSuccess.value = ''
  showPassword.value = false
  isEditingPassword.value = false
  originalPassword.value = ''
  approveDialog.value = true
}

function regeneratePassword() {
  temporaryPassword.value = generateTemporaryPassword()
  passwordError.value = ''
  isEditingPassword.value = false
}

function togglePasswordVisibility() {
  showPassword.value = !showPassword.value
}

// Fonctions d'édition du mot de passe
function toggleEditPassword() {
  if (!isEditingPassword.value) {
    // Commencer l'édition
    originalPassword.value = temporaryPassword.value
    isEditingPassword.value = true
  } else {
    // Sauvegarder l'édition
    saveEditedPassword()
  }
}

function saveEditedPassword() {
  if (!isPasswordValid.value) {
    passwordError.value = 'Password does not meet all requirements'
    return
  }
  isEditingPassword.value = false
  passwordError.value = ''
}

function cancelEditPassword() {
  temporaryPassword.value = originalPassword.value
  isEditingPassword.value = false
  passwordError.value = ''
}

async function confirmApprove() {
  if (!isPasswordValid.value) {
    passwordError.value = 'Password does not meet all requirements'
    return
  }

  isApproving.value = true
  passwordError.value = ''
  passwordSuccess.value = ''

  try {
    await api.admin.accountRequests.approve(selectedRequest.value.id, {
      temporary_password: temporaryPassword.value
    })
    passwordSuccess.value = 'Account approved! Email sent to the client with temporary password.'
    
    setTimeout(() => {
      approveDialog.value = false
      fetchAccountRequests()
    }, 2000)
  } catch (err: any) {
    passwordError.value = err.message || 'Error approving request'
    console.error('Error:', err)
  } finally {
    isApproving.value = false
  }
}

// Reject functions
function openRejectDialog(request: any) {
  selectedRequest.value = request
  rejectReason.value = ''
  rejectDialog.value = true
}

async function confirmReject() {
  if (!selectedRequest.value) return
  
  isRejecting.value = true
  try {
    await api.admin.accountRequests.reject(String(selectedRequest.value.id), rejectReason.value || undefined)
    rejectDialog.value = false
    await fetchAccountRequests()
  } catch (err: any) {
    console.error('Error rejecting request:', err)
  } finally {
    isRejecting.value = false
  }
}

// Lifecycle
onMounted(() => {
  fetchAccountRequests()
})
</script>

<template>
  <div class="space-y-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <!-- Pending Requests -->
      <Card class="border border-gray-200 hover:border-amber-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Pending Requests</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.totalRequests }}</p>
              <p class="text-xs text-gray-500 mt-1">Awaiting review</p>
            </div>
            <div class="w-12 h-12 bg-amber-100 rounded-full flex items-center justify-center">
              <Clock class="h-6 w-6 text-amber-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- This Week Requests -->
      <Card class="border border-gray-200 hover:border-blue-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">This Week</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ stats.thisWeekRequests }}</p>
              <p class="text-xs text-gray-500 mt-1">New applications</p>
            </div>
            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
              <Calendar class="h-6 w-6 text-blue-600" />
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Oldest Request -->
      <Card class="border border-gray-200 hover:border-red-300 transition-colors">
        <CardContent class="p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-600">Oldest Request</p>
              <p class="text-3xl font-bold text-gray-900 mt-2">{{ formatDuration(stats.oldestRequest) }}</p>
              <p class="text-xs text-gray-500 mt-1">Pending for</p>
            </div>
            <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
              <AlertCircle class="h-6 w-6 text-red-600" />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Filters and Search -->
    <Card class="border border-gray-200">
      <CardHeader class="pb-4">
        <CardTitle class="text-lg font-semibold text-gray-900">Filters & Search</CardTitle>
        <p class="text-sm text-gray-500">Manage account requests</p>
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
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
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
                <SelectItem value="email">Email A-Z</SelectItem>
              </SelectContent>
            </Select>
            
            <!-- Actions -->
            <div class="flex gap-2">
              <Button 
                variant="outline"
                size="sm"
                class="gap-2"
                :disabled="loading"
                @click="fetchAccountRequests"
              >
                <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
                Refresh
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
            <p class="text-lg font-medium text-gray-900">Loading Requests</p>
            <p class="text-sm text-gray-500">Fetching account requests...</p>
          </div>
        </div>
      </CardContent>
    </Card>

    <!-- Empty State -->
    <Card v-else-if="filteredRequests.length === 0" class="border border-gray-200">
      <CardContent class="p-12 text-center">
        <div class="mx-auto w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
          <Users class="h-8 w-8 text-gray-400" />
        </div>
        <h3 class="text-lg font-semibold text-gray-900 mb-2">No pending requests</h3>
        <p class="text-gray-600 mb-6 max-w-md mx-auto">
          {{ query ? 'No requests match your search criteria. Try adjusting your search.' : 'All account requests have been processed.' }}
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
            variant="outline"
            class="gap-2"
            @click="fetchAccountRequests"
          >
            <RefreshCw class="h-4 w-4" />
            Check Again
          </Button>
        </div>
      </CardContent>
    </Card>

    <!-- Requests List -->
    <div v-else class="space-y-4">
      <Card 
        v-for="request in filteredRequests" 
        :key="request.id"
        class="border border-gray-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300"
      >
        <CardContent class="p-6">
          <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <!-- User Info -->
            <div class="flex items-start gap-4 flex-1">
              <div class="w-14 h-14 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                <span class="text-white font-bold text-xl">
                  {{ request.name?.charAt(0).toUpperCase() || '?' }}
                </span>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-3 mb-2">
                  <h3 class="font-bold text-gray-900 text-lg">{{ request.name || 'Unknown User' }}</h3>
                  <Badge class="bg-amber-100 text-amber-700 border-amber-200">
                    NEW
                  </Badge>
                </div>
                <div class="flex items-center gap-2 text-gray-600 mb-1">
                  <Mail class="h-4 w-4 text-gray-400" />
                  <span class="text-sm">{{ request.email }}</span>
                </div>
                <div class="flex items-center gap-2 text-gray-500 text-xs">
                  <Calendar class="h-3 w-3" />
                  <span>Requested {{ formatDate(request.created_at) }}</span>
                  <span class="text-gray-300">•</span>
                  <span class="font-medium text-amber-600">
                    {{ formatDuration(Math.floor((new Date().getTime() - new Date(request.created_at).getTime()) / (1000 * 60 * 60 * 24))) }}
                  </span>
                </div>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex flex-col sm:flex-row gap-3">
              <Button 
                class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white gap-2 min-w-[140px]"
                @click="openApproveDialog(request)"
              >
                <UserCheck class="h-4 w-4" />
                Approve
              </Button>
              <Button 
                class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white gap-2 min-w-[140px]"
                @click="openRejectDialog(request)"
              >
                <UserX class="h-4 w-4" />
                Reject
              </Button>
            </div>
          </div>

          <!-- Additional Info -->
          <div class="mt-6 pt-6 border-t border-gray-100 grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <div class="text-xs text-gray-500 mb-1">Application ID</div>
              <div class="font-mono text-sm text-gray-900 bg-gray-50 px-3 py-1 rounded border border-gray-200 inline-block">
                {{ request.id }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500 mb-1">Status</div>
              <Badge class="bg-amber-50 text-amber-700 border-amber-200 font-medium">
                <Clock class="h-3 w-3 mr-1.5" />
                Pending Review
              </Badge>
            </div>
            <div>
              <div class="text-xs text-gray-500 mb-1">Wait Time</div>
              <div class="text-sm font-medium text-amber-600">
                {{ Math.floor((new Date().getTime() - new Date(request.created_at).getTime()) / (1000 * 60 * 60 * 24)) }} days
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Summary -->
    <div v-if="filteredRequests.length > 0" class="flex flex-col sm:flex-row items-center justify-between gap-4 pt-4 border-t border-gray-200">
      <div class="text-sm text-gray-600">
        Showing <strong>{{ filteredRequests.length }}</strong> of {{ requests.length }} request{{ requests.length !== 1 ? 's' : '' }}
      </div>
      <div class="flex items-center gap-2">
        <Button
          variant="outline"
          size="sm"
          class="gap-2"
          :disabled="loading"
          @click="fetchAccountRequests"
        >
          <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </Button>
      </div>
    </div>

    <!-- Approve Dialog -->
    <Dialog :open="approveDialog" @update:open="approveDialog = false">
      <DialogContent class="sm:max-w-lg border border-gray-200">
        <DialogHeader>
          <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
              <UserCheck class="h-5 w-5 text-emerald-600" />
            </div>
            <div>
              <DialogTitle class="text-lg font-semibold text-gray-900">Approve Account Request</DialogTitle>
              <DialogDescription class="text-gray-600">
                Set temporary password for {{ selectedRequest?.name }}
              </DialogDescription>
            </div>
          </div>
        </DialogHeader>

        <div class="space-y-6 py-4">
          <!-- Error Alert -->
          <Alert v-if="passwordError" class="border-red-200 bg-red-50">
            <AlertDescription class="text-red-700 flex items-center gap-2">
              <AlertCircle class="h-4 w-4" />
              {{ passwordError }}
            </AlertDescription>
          </Alert>

          <!-- Success Alert -->
          <Alert v-if="passwordSuccess" class="border-emerald-200 bg-emerald-50">
            <AlertDescription class="text-emerald-700 flex items-center gap-2">
              <CheckCircle class="h-4 w-4" />
              {{ passwordSuccess }}
            </AlertDescription>
          </Alert>

          <!-- User Info -->
          <Card class="border border-gray-200">
            <CardContent class="p-4">
              <div class="grid grid-cols-2 gap-4">
                <div>
                  <div class="text-xs text-gray-500 mb-1">Name</div>
                  <div class="text-sm font-medium text-gray-900">{{ selectedRequest?.name }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500 mb-1">Email</div>
                  <div class="text-sm font-medium text-gray-900">{{ selectedRequest?.email }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500 mb-1">Request Date</div>
                  <div class="text-sm text-gray-900">{{ formatDate(selectedRequest?.created_at) }}</div>
                </div>
                <div>
                  <div class="text-xs text-gray-500 mb-1">Wait Time</div>
                  <div class="text-sm font-medium text-amber-600">
                    {{ Math.floor((new Date().getTime() - new Date(selectedRequest?.created_at).getTime()) / (1000 * 60 * 60 * 24)) }} days
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Password Section -->
          <div class="space-y-4">
            <div class="flex items-center justify-between">
              <Label class="text-sm font-medium text-gray-700">Temporary Password</Label>
              <div class="flex items-center gap-2">
                <button
                  type="button"
                  class="text-xs text-blue-600 hover:text-blue-700 flex items-center gap-1"
                  @click="toggleEditPassword"
                >
                  <span v-if="isEditingPassword">Save Edit</span>
                  <span v-else class="flex items-center gap-1">
                    <Edit class="h-3 w-3" />
                    Edit
                  </span>
                </button>
              </div>
            </div>
            
            <div class="space-y-2">
              <div class="flex gap-2">
                <div class="flex-1 relative">
                  <Input
                    v-model="temporaryPassword"
                    :type="showPassword ? 'text' : 'password'"
                    :readonly="!isEditingPassword"
                    :class="[
                      'pr-24',
                      isEditingPassword 
                        ? 'border-blue-300 bg-white ring-1 ring-blue-300' 
                        : 'border-gray-300 bg-gray-50'
                    ]"
                    placeholder="Password will be generated automatically"
                  />
                  <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center gap-2">
                    <button
                      type="button"
                      class="text-gray-500 hover:text-gray-700"
                      title="Toggle visibility"
                      @click="togglePasswordVisibility"
                    >
                      <component :is="showPassword ? EyeOff : Eye" class="h-4 w-4" />
                    </button>
                    <button
                      type="button"
                      class="text-gray-500 hover:text-gray-700"
                      title="Copy to clipboard"
                      @click="copyToClipboard"
                    >
                      <Copy class="h-4 w-4" />
                    </button>
                    <button
                      v-if="isEditingPassword"
                      type="button"
                      class="text-xs text-emerald-600 hover:text-emerald-700 font-medium"
                      title="Save edited password"
                      @click="saveEditedPassword"
                    >
                      Save
                    </button>
                    <button
                      v-if="isEditingPassword"
                      type="button"
                      class="text-xs text-red-600 hover:text-red-700 font-medium"
                      title="Cancel editing"
                      @click="cancelEditPassword"
                    >
                      Cancel
                    </button>
                  </div>
                </div>
                <Button
                  variant="outline"
                  size="sm"
                  class="border-blue-300 text-blue-600 hover:bg-blue-50 hover:text-blue-700"
                  :disabled="isApproving"
                  @click="regeneratePassword"
                >
                  <RefreshCw class="h-4 w-4" />
                </Button>
              </div>
              
              <!-- Password Requirements -->
              <div class="space-y-2">
                <div class="text-xs text-gray-500 font-medium">Password Requirements</div>
                <div class="grid grid-cols-2 sm:grid-cols-5 gap-2">
                  <div class="text-xs flex items-center gap-1">
                    <div 
                      class="w-2 h-2 rounded-full" 
                      :class="checkRequirement('length') ? 'bg-emerald-500' : 'bg-red-500'"
                    />
                    <span :class="checkRequirement('length') ? 'text-emerald-700' : 'text-red-700'">8+ chars</span>
                  </div>
                  <div class="text-xs flex items-center gap-1">
                    <div 
                      class="w-2 h-2 rounded-full" 
                      :class="checkRequirement('uppercase') ? 'bg-emerald-500' : 'bg-red-500'"
                    />
                    <span :class="checkRequirement('uppercase') ? 'text-emerald-700' : 'text-red-700'">A-Z</span>
                  </div>
                  <div class="text-xs flex items-center gap-1">
                    <div 
                      class="w-2 h-2 rounded-full" 
                      :class="checkRequirement('lowercase') ? 'bg-emerald-500' : 'bg-red-500'"
                    />
                    <span :class="checkRequirement('lowercase') ? 'text-emerald-700' : 'text-red-700'">a-z</span>
                  </div>
                  <div class="text-xs flex items-center gap-1">
                    <div 
                      class="w-2 h-2 rounded-full" 
                      :class="checkRequirement('number') ? 'bg-emerald-500' : 'bg-red-500'"
                    />
                    <span :class="checkRequirement('number') ? 'text-emerald-700' : 'text-red-700'">0-9</span>
                  </div>
                  <div class="text-xs flex items-center gap-1">
                    <div 
                      class="w-2 h-2 rounded-full" 
                      :class="checkRequirement('special') ? 'bg-emerald-500' : 'bg-red-500'"
                    />
                    <span :class="checkRequirement('special') ? 'text-emerald-700' : 'text-red-700'">!@#$%^&*</span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Note -->
            <div class="text-xs text-gray-500 p-3 bg-blue-50 rounded-lg border border-blue-100">
              <div class="flex items-start gap-2">
                <Shield class="h-4 w-4 text-blue-500 mt-0.5" />
                <div>
                  <span class="font-medium text-blue-700">Note:</span> The client will receive this temporary password via email and will be required to change it upon first login.
                </div>
              </div>
            </div>
          </div>
        </div>

        <DialogFooter class="flex gap-2">
          <Button 
            variant="outline" 
            class="border-gray-300 text-gray-700 hover:bg-gray-50"
            :disabled="isApproving"
            @click="approveDialog = false"
          >
            Cancel
          </Button>
          <Button 
            class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white gap-2"
            :disabled="isApproving || !isPasswordValid"
            @click="confirmApprove"
          >
            <UserCheck class="h-4 w-4" />
            {{ isApproving ? 'Processing...' : 'Approve Account' }}
          </Button>
        </DialogFooter>
      </DialogContent>
    </Dialog>

    <!-- Reject Dialog -->
    <AlertDialog :open="rejectDialog" @update:open="rejectDialog = false">
      <AlertDialogContent class="border border-red-200">
        <AlertDialogHeader>
          <div class="mx-auto w-12 h-12 bg-red-100 rounded-full flex items-center justify-center mb-4">
            <UserX class="h-6 w-6 text-red-600" />
          </div>
          <AlertDialogTitle class="text-center text-gray-900">Reject Account Request</AlertDialogTitle>
          <AlertDialogDescription class="text-center text-gray-600">
            Are you sure you want to reject the request from <strong>{{ selectedRequest?.name }}</strong>?
            <br><br>
            <div class="space-y-2">
              <Label class="text-sm font-medium text-gray-700">Rejection Reason (Optional)</Label>
              <Input
                v-model="rejectReason"
                placeholder="Optional reason for rejection"
                class="border-gray-300"
              />
            </div>
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel class="border-gray-300">Cancel</AlertDialogCancel>
          <AlertDialogAction
            class="bg-red-600 hover:bg-red-700 text-white"
            :disabled="isRejecting"
            @click="confirmReject"
          >
            {{ isRejecting ? 'Processing...' : 'Reject Request' }}
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