// components/TransactionFilters.vue
<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Filter, Search } from 'lucide-vue-next'
defineProps<{
  searchQuery: string
  filterType: 'all' | 'ACHAT' | 'VENTE'
  dateRange: string
  showAdvanced: boolean
  filteredCount: number
}>()

const emit = defineEmits<{
  (e: 'update:searchQuery', value: string): void
  (e: 'update:filterType', value: 'all' | 'ACHAT' | 'VENTE'): void
  (e: 'update:dateRange', value: string): void
  (e: 'update:showAdvanced', value: boolean): void
  (e: 'reset'): void
}>()
</script>

<template>
  <Card class="border-slate-200">
    <CardContent class="p-5">
      <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-4">
        <div>
          <h3 class="text-lg font-semibold text-slate-900">Transaction Filters</h3>
          <p class="text-sm text-slate-500 mt-1">
            {{ filteredCount }} transaction{{ filteredCount !== 1 ? 's' : '' }} found
          </p>
        </div>
        
        <div class="flex items-center gap-3">
          <Button
            variant="outline"
            size="sm"
            class="gap-2"
            @click="emit('update:showAdvanced', !showAdvanced)"
          >
            <Filter class="w-4 h-4" />
            {{ showAdvanced ? 'Hide Filters' : 'Show Filters' }}
          </Button>
          
          <Button
            variant="ghost"
            size="sm"
            :disabled="searchQuery === '' && filterType === 'all' && dateRange === 'all'"
            @click="emit('reset')"
          >
            Clear All
          </Button>
        </div>
      </div>

      <!-- Search Bar -->
      <div class="relative mb-4">
        <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-500 w-4 h-4" />
        <Input
          :model-value="searchQuery"
          placeholder="Search by crypto name, symbol, or transaction ID..."
          class="pl-10 border-slate-200 focus:border-brand-blue"
          @update:model-value="emit('update:searchQuery', $event)"
        />
      </div>

      <!-- Advanced Filters -->
      <div v-if="showAdvanced" class="space-y-4 pt-4 border-t border-slate-200">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="space-y-2">
            <Label class="text-sm font-medium text-slate-500">Transaction Type</Label>
            <Select :model-value="filterType" @update:model-value="emit('update:filterType', $event)">
              <SelectTrigger class="border-slate-200 focus:border-brand-blue">
                <SelectValue placeholder="All transaction types" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">All Types</SelectItem>
                <SelectItem value="ACHAT">Purchases Only</SelectItem>
                <SelectItem value="VENTE">Sales Only</SelectItem>
              </SelectContent>
            </Select>
          </div>
          
          <div class="space-y-2">
            <Label class="text-sm font-medium text-slate-500">Date Range</Label>
            <Select :model-value="dateRange" @update:model-value="emit('update:dateRange', $event)">
              <SelectTrigger class="border-slate-200 focus:border-brand-blue">
                <SelectValue placeholder="Select period" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="7d">Last 7 days</SelectItem>
                <SelectItem value="30d">Last 30 days</SelectItem>
                <SelectItem value="90d">Last 90 days</SelectItem>
                <SelectItem value="1y">Last year</SelectItem>
                <SelectItem value="all">All time</SelectItem>
              </SelectContent>
            </Select>
          </div>
        </div>
      </div>
    </CardContent>
  </Card>
</template>