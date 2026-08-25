<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogHeader,
  DialogTitle
} from '@/components/ui/dialog';
import { defineProps } from 'vue';

const props = defineProps<{ 
  open: boolean
  title?: string
  message?: string 
}>()

</script>

<template>
  <Dialog :open="open" @update:open="emits('close')">
    <DialogContent class="sm:max-w-md border-brand-dark bg-white shadow-lg">
      <DialogHeader>
        <DialogTitle class="text-brand-dark">{{ title || 'Confirmation' }}</DialogTitle>
        <DialogDescription class="text-gray-600">
          {{ message || 'Êtes-vous sûr ?' }}
        </DialogDescription>
      </DialogHeader>
      <div class="flex justify-end gap-3 mt-6">
        <Button
          variant="outline"
          class="border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white"
          @click="emits('close')"
        >
          Annuler
        </Button>
        <Button
          class="bg-brand-red hover:bg-brand-red/90 text-white"
          @click="emits('confirm')"
        >
          Confirmer
        </Button>
      </div>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
:deep(.border-brand-dark) {
  border-color: #38618C;
}
</style>
