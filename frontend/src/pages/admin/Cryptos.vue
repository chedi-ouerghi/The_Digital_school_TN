<script setup lang="ts">
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
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
      <div>
        <h1 class="text-3xl font-bold text-[#38618C] mb-1">Cryptocurrency Management</h1>
        <p class="text-gray-500">View cryptocurrencies available on the platform</p>
      </div>
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
      @change-page="changePage"
      @refresh="fetchCryptos"
    />
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