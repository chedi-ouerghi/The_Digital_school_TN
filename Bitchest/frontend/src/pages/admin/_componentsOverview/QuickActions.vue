<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import {
  Users,
  Coins, CreditCard
} from 'lucide-vue-next';

defineEmits<{
  goToClients: []
  goToCryptos: []
  goToTransactions: []
}>()
</script>

<template>
  <Card class="border border-gray-200">
    <CardHeader class="pb-3">
      <CardTitle class="text-lg font-semibold text-gray-900">Quick Actions</CardTitle>
      <p class="text-sm text-gray-500">Manage platform sections with one click</p>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
        <Button 
          @click="$emit('goToClients')"
          class="h-auto py-4 px-4 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-blue-50 to-blue-100 hover:from-blue-100 hover:to-blue-200 border border-blue-200 text-blue-700 hover:text-blue-800"
        >
          <Users class="h-5 w-5" />
          <span class="text-sm font-medium">Manage Clients</span>
        </Button>

        <Button 
          @click="$emit('goToCryptos')"
          class="h-auto py-4 px-4 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-emerald-50 to-emerald-100 hover:from-emerald-100 hover:to-emerald-200 border border-emerald-200 text-emerald-700 hover:text-emerald-800"
        >
          <Coins class="h-5 w-5" />
          <span class="text-sm font-medium">Cryptocurrencies</span>
        </Button>

        

        <Button 
          @click="$emit('goToTransactions')"
          class="h-auto py-4 px-4 flex flex-col items-center justify-center gap-2 bg-gradient-to-br from-amber-50 to-amber-100 hover:from-amber-100 hover:to-amber-200 border border-amber-200 text-amber-700 hover:text-amber-800"
        >
          <CreditCard class="h-5 w-5" />
          <span class="text-sm font-medium">Transactions</span>
        </Button>
      </div>
    </CardContent>
  </Card>
</template>