<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import api from '../services/api'
import ClientFormDialog from './ClientFormDialog.vue'
import ClientDetailsDialog from './ClientDetailsDialog.vue'
import ConfirmDialog from './ConfirmDialog.vue'
import { Input } from '@/components/ui/input'
import { Button } from '@/components/ui/button'

const clients = ref<any[]>([])
const loading = ref(false)
const page = ref(1)
const total = ref(0)
const error = ref<string | null>(null)
const search = ref('')

const dialogOpen = ref(false)
const detailsOpen = ref(false)
const selectedClientId = ref<number | null>(null)
const editClient = ref<any>(null)
const confirmOpen = ref(false)
const toDeleteId = ref<number | null>(null)

async function fetchClients(p = 1) {
  loading.value = true
  try {
    const res = await api.admin.clients.list(p)
    clients.value = res.data || res.items || res || []
    total.value = res.total || res.meta?.total || clients.value.length
  } catch (err: any) {
    error.value = err.message || String(err)
  } finally {
    loading.value = false
  }
}

const filteredClients = computed(() => {
  if (!search.value) return clients.value
  const searchLower = search.value.toLowerCase()
  return clients.value.filter(c => 
    c.name?.toLowerCase().includes(searchLower) ||
    c.email?.toLowerCase().includes(searchLower)
  )
})

onMounted(() => fetchClients())

function openCreate() {
  editClient.value = null
  dialogOpen.value = true
}

function openEdit(c: any) {
  editClient.value = c
  dialogOpen.value = true
}

function openDetails(id: number) {
  selectedClientId.value = id
  detailsOpen.value = true
}

async function handleSave(payload: any) {
  try {
    if (editClient.value?.id) {
      await api.admin.clients.update(editClient.value.id, payload)
      alert('Client updated')
    } else {
      await api.admin.clients.create(payload)
      alert('Client created')
    }
    dialogOpen.value = false
    fetchClients(page.value)
  } catch (err: any) {
    alert(err.message || String(err))
  }
}

function confirmDelete(id: number) {
  toDeleteId.value = id
  confirmOpen.value = true
}

async function doDelete() {
  if (!toDeleteId.value) return
  try {
    await api.admin.clients.delete(toDeleteId.value)
    alert('Client deleted')
    confirmOpen.value = false
    fetchClients(page.value)
  } catch (err: any) {
    alert(err.message || String(err))
  }
}
</script>

<template>
  <div>
    <div class="flex justify-between items-center mb-4">
      <Button variant="default" @click="openCreate">New Client</Button>
      <Input v-model="search" placeholder="Search by name or email..." class="w-64" />
    </div>

    <div v-if="loading">Loading clients...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else class="rounded-lg border">
      <table class="w-full">
        <thead>
          <tr class="border-b bg-muted/50">
            <th class="p-2">Name</th>
            <th class="p-2">Email</th>
            <th class="p-2">Balance (€)</th>
            <th class="p-2">Registration</th>
            <th class="p-2">Actions</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="c in filteredClients" :key="c.id" class="border-b">
            <td class="p-2">{{ c.name }}</td>
            <td class="p-2">{{ c.email }}</td>
            <td class="p-2">{{ c.solde?.toFixed(2) }}</td>
            <td class="p-2">{{ new Date(c.created_at).toLocaleDateString() }}</td>
            <td class="p-2">
              <div class="flex gap-2">
                <Button variant="outline" size="sm" @click="openDetails(c.id)">Details</Button>
                <Button variant="outline" size="sm" @click="openEdit(c)">Edit</Button>
                <Button variant="destructive" size="sm" @click="confirmDelete(c.id)">Delete</Button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <ClientFormDialog 
      :open="dialogOpen" 
      :client="editClient" 
      @close="dialogOpen = false" 
      @saved="handleSave" 
    />
    
    <ClientDetailsDialog
      :open="detailsOpen"
      :client-id="selectedClientId"
      @close="detailsOpen = false"
    />

    <ConfirmDialog 
      :open="confirmOpen"
      title="Delete client"
      message="Are you sure? This action cannot be undone."
      @close="confirmOpen = false"
      @confirm="doDelete"
    />
  </div>
</template>

<style scoped>
</style>
