<script setup lang="ts">
import { defineEmits, defineProps, ref, watch } from 'vue';

const props = defineProps<{ open: boolean; client: any | null }>()
const emits = defineEmits<{
  (e: 'close'): void
  (e: 'saved', _payload: any): void
}>()

const form = ref({ name: '', email: '', role: 'CLIENT', solde: 0 })
const loading = ref(false)

watch(() => props.client, (c) => {
  if (c) {
    form.value = { name: c.name || '', email: c.email || '', role: c.role || 'CLIENT', solde: c.solde || 0 }
  } else {
    form.value = { name: '', email: '', role: 'CLIENT', solde: 0 }
  }
})

async function save() {
  loading.value = true
  emits('saved', { ...form.value })
  loading.value = false
}
</script>

<template>
  <div v-if="props.open" class="fixed inset-0 z-[75] flex items-center justify-center">
    <div class="absolute inset-0 bg-black/50" @click="emits('close')"></div>
    <div class="bg-white rounded-lg p-6 z-[80] w-11/12 max-w-lg shadow-2xl border border-slate-200">
      <h3 class="text-lg font-semibold text-slate-900 mb-4">{{ props.client ? 'Edit client' : 'Create client' }}</h3>

      <div class="space-y-3">
        <div>
          <label class="block text-sm text-slate-600">Name</label>
          <input v-model="form.name" class="w-full border border-slate-300 text-slate-900 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-slate-600">Email</label>
          <input v-model="form.email" class="w-full border border-slate-300 text-slate-900 rounded px-3 py-2" />
        </div>
        <div>
          <label class="block text-sm text-slate-600">Role</label>
          <select v-model="form.role" class="w-full border border-slate-300 text-slate-900 rounded px-3 py-2">
            <option value="CLIENT">CLIENT</option>
            <option value="ADMIN">ADMIN</option>
          </select>
        </div>
        <div>
          <label class="block text-sm text-slate-600">Balance (€)</label>
          <input v-model.number="form.solde" type="number" class="w-full border border-slate-300 text-slate-900 rounded px-3 py-2" />
        </div>
      </div>

      <div class="mt-4 flex justify-end gap-3">
        <button class="px-4 py-2 rounded bg-white border border-slate-300 text-slate-700 hover:bg-slate-50" @click="emits('close')">Cancel</button>
        <button class="px-4 py-2 rounded bg-emerald-600 text-white hover:bg-emerald-700" @click="save">Save</button>
      </div>
    </div>
  </div>
</template>

<style scoped>
</style>
