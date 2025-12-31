<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
} from '@/components/ui/dialog'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import CryptoForm from './_componentsCryptos/CryptoForm.vue'
import CryptoList from './_componentsCryptos/CryptoList.vue'
import CryptoStats from './_componentsCryptos/CryptoStats.vue'

const router = useRouter()
const cryptos = ref<any[]>([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const itemsPerPage = 10

// Dialog states
const addCryptoDialog = ref(false)
const deleteDialog = ref(false)
const cryptoToDelete = ref<any>(null)

// Form states
const isEditMode = ref(false)
const editingCryptoId = ref<number | null>(null)
const editingCryptoData = ref<any>(null)

// Récupération des données
async function fetchCryptos() {
  loading.value = true
  try {
    const res = await api.crypto.list({ page: currentPage.value })
    cryptos.value = res.data || []
    const totalItems = res.total_items || res.total || cryptos.value.length
    totalPages.value = Math.ceil(totalItems / itemsPerPage)
  } catch (err: any) {
    console.error('Erreur chargement cryptos:', err)
    cryptos.value = []
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCryptos()
})

// Actions
function openAddDialog() {
  isEditMode.value = false
  editingCryptoId.value = null
  editingCryptoData.value = null
  addCryptoDialog.value = true
}

function closeAddDialog() {
  addCryptoDialog.value = false
  isEditMode.value = false
  editingCryptoId.value = null
  editingCryptoData.value = null
}

async function handleEditCrypto(crypto: any) {
  try {
    const cryptoData = await api.crypto.show(crypto.id)
    editingCryptoData.value = cryptoData
    editingCryptoId.value = cryptoData?.id || crypto.id
    isEditMode.value = true
    addCryptoDialog.value = true
  } catch (err: any) {
    console.error('Error loading data:', err)
  }
}

function confirmDeleteCrypto(crypto: any) {
  cryptoToDelete.value = crypto
  deleteDialog.value = true
}

async function handleDeleteCrypto() {
  if (!cryptoToDelete.value?.id) return
  
  try {
    await api.crypto.delete(cryptoToDelete.value.id)
    deleteDialog.value = false
    cryptoToDelete.value = null
    await fetchCryptos()
  } catch (err: any) {
    alert(err.message || 'Erreur lors de la suppression')
  }
}

function handleFormSuccess() {
  fetchCryptos()
  setTimeout(() => {
    closeAddDialog()
  }, 1500)
}

function viewDetails(id: number) {
  router.push(`/dashboard/admin/cryptos/${id}`)
}

function changePage(page: number) {
  currentPage.value = page
  fetchCryptos()
}
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Cryptocurrency Management</h1>
        <p class="text-gray-500">Manage cryptocurrencies available on the platform</p>
      </div>
      <Button 
        class="bg-[#01FF19] hover:bg-[#01FF19]/90 text-[#38618C] font-semibold"
        @click="openAddDialog"
      >
        + Add Crypto
      </Button>
    </div>

    <!-- Statistiques -->
    <CryptoStats :cryptos="cryptos" />

    <!-- Liste des cryptos -->
    <CryptoList
      :cryptos="cryptos"
      :loading="loading"
      :current-page="currentPage"
      :total-pages="totalPages"
      @view-details="viewDetails"
      @edit-crypto="handleEditCrypto"
      @delete-crypto="confirmDeleteCrypto"
      @change-page="changePage"
      @refresh="fetchCryptos"
    />

    <!-- Form Dialog -->
    <Dialog v-model:open="addCryptoDialog">
      <CryptoForm
        :is-edit-mode="isEditMode"
        :editing-crypto-id="editingCryptoId"
        :editing-crypto-data="editingCryptoData"
        @success="handleFormSuccess"
        @cancel="closeAddDialog"
      />
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <Dialog v-model:open="deleteDialog">
      <DialogContent class="sm:max-w-md border-[#FF5964]">
        <DialogHeader>
          <DialogTitle class="text-[#FF5964] text-xl">⚠️ Confirm Deletion</DialogTitle>
          <DialogDescription class="text-gray-600">
            Are you sure you want to delete <strong>{{ cryptoToDelete?.name }}</strong>?
          </DialogDescription>
        </DialogHeader>

        <Alert class="border-[#FF5964] bg-[#FF5964]/10">
          <AlertDescription class="text-[#FF5964]">
            ⚠️ This action is irreversible and will remove all associated data.
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
            @click="handleDeleteCrypto"
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

:deep(.hover\:bg-\[#01FF19\]\/90:hover) {
  background-color: rgba(1, 255, 25, 0.9);
}

:deep(.bg-\[#FF5964\]) {
  background-color: #FF5964;
}

:deep(.hover\:bg-\[#FF5964\]\/90:hover) {
  background-color: rgba(255, 89, 100, 0.9);
}

:deep(.border-\[#FF5964\]) {
  border-color: #FF5964;
}
</style>