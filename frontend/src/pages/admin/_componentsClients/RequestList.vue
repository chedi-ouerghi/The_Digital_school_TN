<script setup lang="ts">
import { computed, onMounted, ref } from 'vue'
import api from '../../../services/api'

// Import des composants shadcn-vue
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
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

// Émissions d'événements
const emit = defineEmits<{
  'requests-updated': []
}>()

// State
const requests = ref<any[]>([])
const loading = ref(false)

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

// Utility functions
function formatDate(dateString: string): string {
  return new Date(dateString).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
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

// Indicateur de force du mot de passe
const passwordStrength = computed(() => {
  const length = temporaryPassword.value.length
  const hasUppercase = /[A-Z]/.test(temporaryPassword.value)
  const hasLowercase = /[a-z]/.test(temporaryPassword.value)
  const hasNumber = /[0-9]/.test(temporaryPassword.value)
  const hasSpecial = /[!@#$%^&*]/.test(temporaryPassword.value)
  
  let score = 0
  if (length >= 8) score += 25
  if (length >= 12) score += 15
  if (hasUppercase) score += 20
  if (hasLowercase) score += 20
  if (hasNumber) score += 10
  if (hasSpecial) score += 10
  
  if (score >= 90) {
    return {
      text: 'Strong',
      color: 'text-[#01FF19]',
      barColor: 'bg-[#01FF19]',
      percentage: 100
    }
  } else if (score >= 70) {
    return {
      text: 'Good',
      color: 'text-[#35A7FF]',
      barColor: 'bg-[#35A7FF]',
      percentage: 75
    }
  } else if (score >= 50) {
    return {
      text: 'Fair',
      color: 'text-yellow-500',
      barColor: 'bg-yellow-500',
      percentage: 50
    }
  } else {
    return {
      text: 'Weak',
      color: 'text-[#FF5964]',
      barColor: 'bg-[#FF5964]',
      percentage: 25
    }
  }
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

async function rejectRequest(id: number | string) {
  try {
    const reason = window.prompt('Enter rejection reason (optional):') || undefined
    await api.admin.accountRequests.reject(String(id), reason)
    await fetchAccountRequests()
  } catch (err: any) {
    alert(err.message || 'Error rejecting request')
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
                    @click="openApproveDialog(request)"
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

<!-- Approve Dialog -->
<Dialog :open="approveDialog" @update:open="approveDialog = false">
  <DialogContent class="sm:max-w-md border-[#01FF19]">
    <DialogHeader>
      <DialogTitle class="text-[#38618C]">
        Approve Account Request
      </DialogTitle>
      <DialogDescription>
        Set a temporary password for <strong>{{ selectedRequest?.name }}</strong>
      </DialogDescription>
    </DialogHeader>

    <div class="space-y-4 py-4">
      <!-- Error Alert -->
      <Alert v-if="passwordError" class="border-[#FF5964] bg-[#FF5964]/10">
        <AlertDescription class="text-[#FF5964]">
          {{ passwordError }}
        </AlertDescription>
      </Alert>

      <!-- Success Alert -->
      <Alert v-if="passwordSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
        <AlertDescription class="text-[#01FF19]">
          {{ passwordSuccess }}
        </AlertDescription>
      </Alert>

      <!-- User Info -->
      <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
        <div class="text-sm text-gray-600 mb-1">Request Details</div>
        <div class="space-y-1">
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-[#38618C]">Name:</span>
            <span class="text-sm text-gray-700">{{ selectedRequest?.name }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-[#38618C]">Email:</span>
            <span class="text-sm text-gray-700">{{ selectedRequest?.email }}</span>
          </div>
          <div class="flex justify-between items-center">
            <span class="text-sm font-medium text-[#38618C]">Requested:</span>
            <span class="text-sm text-gray-700">{{ formatDate(selectedRequest?.created_at) }}</span>
          </div>
        </div>
      </div>

      <!-- Temporary Password Field -->
      <div class="space-y-2">
        <div class="flex items-center justify-between">
          <Label for="temp-password" class="text-[#38618C] font-semibold">
            Temporary Password
          </Label>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="text-xs text-[#35A7FF] hover:text-[#35A7FF]/80 flex items-center gap-1"
              @click="toggleEditPassword"
            >
              <span v-if="isEditingPassword">✓ Save Edit</span>
              <span v-else>✏️ Edit</span>
            </button>
            <button
              type="button"
              class="text-xs text-gray-500 hover:text-[#38618C]"
              @click="togglePasswordVisibility"
            >
              {{ showPassword ? '👁️' : '👁️‍🗨️' }}
            </button>
          </div>
        </div>
        
        <div class="flex gap-2">
          <div class="flex-1 relative">
            <Input
              id="temp-password"
              v-model="temporaryPassword"
              :type="showPassword ? 'text' : 'password'"
              :readonly="!isEditingPassword"
              :class="[
                'pr-20 border-gray-300 focus:border-[#01FF19] transition-all',
                isEditingPassword 
                  ? 'bg-white border-[#35A7FF] ring-1 ring-[#35A7FF]' 
                  : 'bg-gray-50'
              ]"
              placeholder="Password will be generated automatically"
              @keydown.enter="isEditingPassword ? saveEditedPassword() : null"
            />
            <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center gap-1">
              <button
                v-if="isEditingPassword"
                type="button"
                class="text-xs text-[#01FF19] hover:text-[#01FF19]/80 font-medium"
                title="Save edited password"
                @click="saveEditedPassword"
              >
                Save
              </button>
              <button
                v-if="isEditingPassword"
                type="button"
                class="text-xs text-[#FF5964] hover:text-[#FF5964]/80 font-medium"
                title="Cancel editing"
                @click="cancelEditPassword"
              >
                Cancel
              </button>
              <button
                v-else
                type="button"
                class="text-gray-500 hover:text-[#38618C]"
                title="Copy password"
                @click="copyToClipboard"
              >
                📋
              </button>
            </div>
          </div>
        </div>
        
        <!-- Password Strength Indicator -->
        <div v-if="isEditingPassword" class="space-y-1">
          <div class="flex justify-between items-center">
            <span class="text-xs text-gray-500">Password strength:</span>
            <span class="text-xs font-medium" :class="passwordStrength.color">
              {{ passwordStrength.text }}
            </span>
          </div>
          <div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
            <div 
              class="h-full transition-all duration-300" 
              :class="passwordStrength.barColor"
              :style="{ width: passwordStrength.percentage + '%' }"
            />
          </div>
          <div class="text-xs text-gray-500">
            {{ temporaryPassword.length }} characters
          </div>
        </div>
      </div>

      <!-- Password Actions -->
      <div class="flex gap-2">
        <Button
          variant="outline"
          size="sm"
          class="flex-1 border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white"
          :disabled="isApproving || isEditingPassword"
          @click="regeneratePassword"
        >
          🔄 Regenerate
        </Button>
        <Button
          v-if="!isEditingPassword"
          variant="outline"
          size="sm"
          class="border-[#01FF19] text-[#01FF19] hover:bg-[#01FF19] hover:text-white"
          :disabled="isApproving"
          @click="toggleEditPassword"
        >
          ✏️ Edit
        </Button>
      </div>

      <!-- Password Requirements -->
      <div class="text-xs text-gray-500 p-3 bg-blue-50 rounded-lg border border-blue-100">
        <div class="font-semibold text-[#38618C] mb-1">Password Requirements:</div>
        <ul class="space-y-1 list-disc list-inside">
          <li :class="checkRequirement('length') ? 'text-[#01FF19]' : 'text-[#FF5964]'">
            Minimum 8 characters
          </li>
          <li :class="checkRequirement('uppercase') ? 'text-[#01FF19]' : 'text-[#FF5964]'">
            At least 1 uppercase letter
          </li>
          <li :class="checkRequirement('lowercase') ? 'text-[#01FF19]' : 'text-[#FF5964]'">
            At least 1 lowercase letter
          </li>
          <li :class="checkRequirement('number') ? 'text-[#01FF19]' : 'text-[#FF5964]'">
            At least 1 number
          </li>
          <li :class="checkRequirement('special') ? 'text-[#01FF19]' : 'text-[#FF5964]'">
            At least 1 special character (!@#$%^&*)
          </li>
          <li class="text-[#35A7FF]">
            Client will be asked to change it on first login
          </li>
        </ul>
        <div v-if="isEditingPassword" class="mt-2 text-xs text-[#38618C] font-medium">
          ⚠️ Edit carefully - this password will be sent to the client
        </div>
      </div>
    </div>

    <DialogFooter class="flex gap-2 sm:gap-0">
      <Button
        variant="outline"
        class="border-gray-300 text-gray-700"
        :disabled="isApproving"
        @click="approveDialog = false"
      >
        Cancel
      </Button>
      <Button
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
        :disabled="isApproving || !isPasswordValid"
        @click="confirmApprove"
      >
        {{ isApproving ? '⏳ Processing...' : '✓ Confirm & Send' }}
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

:deep(.focus\:border-\[#01FF19\]:focus) {
  border-color: #01FF19;
}

:deep(.border-\[#FF5964\]) {
  border-color: #FF5964;
}

:deep(.text-\[#FF5964\]) {
  color: #FF5964;
}

:deep(.bg-\[#FF5964\]\/10) {
  background-color: rgba(255, 89, 100, 0.1);
}

:deep(.bg-\[#01FF19\]\/10) {
  background-color: rgba(1, 255, 25, 0.1);
}
</style>