<script setup lang="ts">
import {
  Coins, RefreshCw
} from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import api from '../../services/api'
import CryptoList from './_componentsCryptos/CryptoList.vue'
import CryptoStats from './_componentsCryptos/CryptoStats.vue'

const router = useRouter()
const cryptos = ref<any[]>([])
const loading = ref(false)
const currentPage = ref(1)
const totalPages = ref(1)
const itemsPerPage = 10

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
function viewDetails(id: number) {
  router.push(`/dashboard/admin/cryptos/${id}`)
}

function changePage(page: number) {
  currentPage.value = page
  fetchCryptos()
}

function refreshData() {
  fetchCryptos()
}
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-50 to-gray-100 p-4 md:p-6 space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row items-start lg:items-center justify-between gap-4">
      <div class="space-y-2">
        <div class="flex items-center gap-3">
          <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center shadow-lg">
            <Coins class="h-6 w-6 text-white" />
          </div>
          <div>
            <h1 class="text-2xl md:text-3xl font-bold text-gray-900">Cryptocurrency Management</h1>
            <p class="text-sm md:text-base text-gray-600">Monitor and manage digital assets on the platform</p>
          </div>
        </div>
      </div>
      
      <div class="flex items-center gap-2">
        <Button 
          variant="outline"
          class="border-gray-300 hover:bg-gray-100 gap-2"
          :disabled="loading"
          @click="refreshData"
        >
          <RefreshCw class="h-4 w-4" :class="{ 'animate-spin': loading }" />
          Refresh
        </Button>
      </div>
    </div>

    <!-- Stats Cards -->
    <CryptoStats :cryptos="cryptos" />

    <!-- Crypto List -->
    <CryptoList
      :cryptos="cryptos"
      :loading="loading"
      :current-page="currentPage"
      :total-pages="totalPages"
      @view-details="viewDetails"
      @change-page="changePage"
      @refresh="fetchCryptos"
    />
  </div>
</template>