<script setup lang="ts">
import { ref, watch } from 'vue'
import {
    Key,
    AlertCircle,
    CheckCircle,
    XCircle,
    Shield,
    Copy,
    Check
} from 'lucide-vue-next'
import api from '../../../services/api'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
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

interface Props {
  profile: any
}

interface Emits {
  (e: 'id-changed'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()

// Change ID Dialog
const showChangeIdDialog = ref(false)
const newId = ref('')
const confirmation = ref('')
const changeIdLoading = ref(false)
const changeIdError = ref('')
const changeIdSuccess = ref('')
const confirmationValid = ref(false)
const newIdValid = ref(false)
const copied = ref(false)

// Validate new ID format
watch(newId, (value) => {
  newIdValid.value = /^[A-Z0-9]{14}$/.test(value)
})

// Validate confirmation text
watch(confirmation, (value) => {
  confirmationValid.value = value === 'I confirm that I want to change my administrator ID'
})

// Copy current ID
function copyCurrentId() {
  if (props.profile?.id) {
    navigator.clipboard.writeText(props.profile.id)
    copied.value = true
    setTimeout(() => {
      copied.value = false
    }, 2000)
  }
}

// Open change ID dialog
function openChangeIdDialog() {
  showChangeIdDialog.value = true
  newId.value = ''
  confirmation.value = ''
  changeIdError.value = ''
  changeIdSuccess.value = ''
}

// Cancel change ID
function cancelChangeId() {
  showChangeIdDialog.value = false
  newId.value = ''
  confirmation.value = ''
  changeIdError.value = ''
  changeIdSuccess.value = ''
}

// Confirm change ID
async function confirmChangeId() {
  changeIdError.value = ''
  changeIdSuccess.value = ''

  // Validation
  if (!newIdValid.value) {
    changeIdError.value = 'ID must be exactly 14 uppercase alphanumeric characters'
    return
  }
  if (!confirmationValid.value) {
    changeIdError.value = 'You must type the exact confirmation sentence'
    return
  }

  changeIdLoading.value = true
  try {
    await api.auth.changeId({ 
      new_id: newId.value, 
      confirmation: confirmation.value 
    })
    changeIdSuccess.value = 'Administrator ID changed successfully'
    emit('id-changed')
    
    setTimeout(() => {
      showChangeIdDialog.value = false
      newId.value = ''
      confirmation.value = ''
      changeIdSuccess.value = ''
    }, 2000)
  } catch (e: any) {
    changeIdError.value = e?.message || 'Failed to change ID'
  } finally {
    changeIdLoading.value = false
  }
}
</script>

<template>
  <Card class="border border-gray-200 max-w-3xl">
    <CardHeader>
      <div class="flex items-center gap-3">
        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center">
          <Key class="h-5 w-5 text-red-600" />
        </div>
        <div>
          <CardTitle class="text-lg font-semibold">Administrator ID Management</CardTitle>
          <CardDescription>
            Change your administrator identifier. This is a sensitive operation.
          </CardDescription>
        </div>
      </div>
    </CardHeader>
    
    <CardContent class="space-y-6">
      <!-- Security Alert -->
      <Alert class="border-red-200 bg-gradient-to-r from-red-50 to-red-100">
        <AlertCircle class="h-5 w-5 text-red-600" />
        <AlertDescription class="text-red-800 text-sm">
          <strong class="font-semibold">Security Notice:</strong> Changing your administrator ID affects all system references, integrations, and audit logs. This action can only be performed once every 2 days and requires careful consideration.
        </AlertDescription>
      </Alert>

      <!-- Current ID Display -->
      <div class="p-4 rounded-lg border border-gray-200 bg-gradient-to-r from-gray-50 to-gray-100">
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
          <div class="space-y-1">
            <div class="flex items-center gap-2">
              <p class="text-sm font-medium text-gray-700">Current Administrator ID</p>
              <Badge variant="outline" class="text-xs bg-white">
                <Shield class="h-3 w-3 mr-1" />
                ADMIN
              </Badge>
            </div>
            <div class="flex items-center gap-2">
              <p class="font-mono text-lg font-bold text-gray-900">
                {{ profile?.id || 'Loading...' }}
              </p>
              <Button
                variant="ghost"
                size="sm"
                class="h-7 w-7 p-0"
                :title="copied ? 'Copied!' : 'Copy ID'"
                @click="copyCurrentId"
              >
                <component :is="copied ? Check : Copy" class="h-4 w-4" />
              </Button>
            </div>
          </div>
          
          <Button
            class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white gap-2"
            @click="openChangeIdDialog"
          >
            <Key class="h-4 w-4" />
            Change ID
          </Button>
        </div>
      </div>

      <!-- Important Notes -->
      <div class="space-y-3">
        <h3 class="text-sm font-semibold text-gray-900">Important Considerations:</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
          <div class="p-3 rounded-lg bg-blue-50 border border-blue-100">
            <p class="text-xs font-medium text-blue-800 mb-1">System Impact</p>
            <p class="text-xs text-blue-700">All system logs and references will be updated to use the new ID</p>
          </div>
          
          <div class="p-3 rounded-lg bg-amber-50 border border-amber-100">
            <p class="text-xs font-medium text-amber-800 mb-1">Time Restriction</p>
            <p class="text-xs text-amber-700">You can only change your ID once every 48 hours</p>
          </div>
          
          <div class="p-3 rounded-lg bg-purple-50 border border-purple-100">
            <p class="text-xs font-medium text-purple-800 mb-1">Irreversible Action</p>
            <p class="text-xs text-purple-700">This change cannot be undone or reverted</p>
          </div>
          
          <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-100">
            <p class="text-xs font-medium text-emerald-800 mb-1">Confirmation Required</p>
            <p class="text-xs text-emerald-700">You must type the exact confirmation sentence</p>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>

  <!-- Change ID Dialog -->
  <Dialog v-model:open="showChangeIdDialog">
    <DialogContent class="sm:max-w-[500px]">
      <DialogHeader>
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center">
            <Key class="h-5 w-5 text-red-600" />
          </div>
          <div>
            <DialogTitle>Change Administrator ID</DialogTitle>
            <DialogDescription>
              Enter your new ID and confirm this sensitive operation.
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <!-- New ID Input -->
        <div class="space-y-2">
          <Label for="newId" class="text-sm font-medium text-gray-700 flex items-center gap-2">
            <Key class="h-4 w-4 text-gray-500" />
            New Administrator ID
          </Label>
          <Input
            id="newId"
            v-model="newId"
            placeholder="Enter 14-character alphanumeric ID (e.g., RWW60MGY2NDVSF)"
            :disabled="changeIdLoading"
            maxlength="14"
            :class="{
              'border-green-500': newIdValid && newId,
              'border-red-500': !newIdValid && newId,
              'border-gray-300': !newId
            }"
            @input="newId = $event.target.value.toUpperCase()"
          />
          <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500">
              Must be exactly 14 uppercase letters and numbers
            </p>
            <span 
              v-if="newId" 
              class="text-xs px-2 py-1 rounded"
              :class="newIdValid ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'"
            >
              {{ newIdValid ? '✓ Valid format' : '✗ Invalid format' }}
            </span>
          </div>
        </div>

        <!-- Confirmation Input -->
        <div class="space-y-2">
          <Label for="confirmation" class="text-sm font-medium text-gray-700">
            Type the exact confirmation sentence:
          </Label>
          <Input
            id="confirmation"
            v-model="confirmation"
            placeholder='Type: "I confirm that I want to change my administrator ID"'
            :disabled="changeIdLoading"
            :class="{
              'border-green-500': confirmationValid && confirmation,
              'border-red-500': !confirmationValid && confirmation,
              'border-gray-300': !confirmation
            }"
          />
          <div class="flex items-center justify-between">
            <p class="text-xs text-gray-500 italic">
              "I confirm that I want to change my administrator ID"
            </p>
            <span 
              v-if="confirmation" 
              class="text-xs px-2 py-1 rounded"
              :class="confirmationValid ? 'text-green-600 bg-green-50' : 'text-red-600 bg-red-50'"
            >
              {{ confirmationValid ? '✓ Correct' : '✗ Incorrect' }}
            </span>
          </div>
        </div>

        <!-- Status Messages -->
        <div v-if="changeIdError" class="p-3 rounded-lg border border-red-200 bg-gradient-to-r from-red-50 to-red-100">
          <div class="flex items-center gap-2 text-red-800">
            <XCircle class="h-4 w-4 flex-shrink-0" />
            <span class="text-sm">{{ changeIdError }}</span>
          </div>
        </div>
        
        <div v-if="changeIdSuccess" class="p-3 rounded-lg border border-green-200 bg-gradient-to-r from-green-50 to-green-100">
          <div class="flex items-center gap-2 text-green-800">
            <CheckCircle class="h-4 w-4 flex-shrink-0" />
            <span class="text-sm">{{ changeIdSuccess }}</span>
          </div>
        </div>
      </div>

      <DialogFooter class="gap-2">
        <Button
          variant="outline"
          :disabled="changeIdLoading"
          @click="cancelChangeId"
        >
          Cancel
        </Button>
        <Button
          :disabled="changeIdLoading || !newIdValid || !confirmationValid"
          class="bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white gap-2"
          @click="confirmChangeId"
        >
          <Key class="h-4 w-4" />
          <span>{{ changeIdLoading ? 'Changing...' : 'Confirm Change' }}</span>
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>