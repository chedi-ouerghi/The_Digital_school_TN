<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';

const props = defineProps<{ 
  open: boolean
  title?: string
  message?: string 
}>()

const emits = defineEmits<{
  (e: 'close'): void
  (e: 'confirm'): void
}>()
</script>

<template>
  <Dialog :open="open" @update:open="emits('close')">
    <DialogContent class="sm:max-w-md border-[#38618C]">
      <DialogHeader>
        <DialogTitle class="text-[#38618C]">{{ title || 'Confirmation' }}</DialogTitle>
        <DialogDescription class="text-gray-600">
          {{ message || 'Êtes-vous sûr ?' }}
        </DialogDescription>
      </DialogHeader>
      <div class="flex justify-end gap-3 mt-6">
        <Button
          variant="outline"
          @click="emits('close')"
          class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF] hover:text-white"
        >
          Annuler
        </Button>
        <Button
          @click="emits('confirm')"
          class="bg-[#FF5964] hover:bg-[#FF5964]/90 text-white"
        >
          Confirmer
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
:deep(.border-\[#38618C\]) {
  border-color: #38618C;
}
</style>
