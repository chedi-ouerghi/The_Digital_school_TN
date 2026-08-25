// components/SellDialog.vue
<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { AlertCircle, ArrowUpRight, CheckCircle2, Loader2, X } from 'lucide-vue-next'

defineProps<{
  show: boolean
  selectedAsset: any
  sellQuantity: string
  sellError: string | null
  sellSuccess: string | null
  isSelling: boolean
  formatCurrency: (value: any) => string
  formatNumber: (value: any, decimals?: number) => string
  getAvailableQuantity: (symbol: string) => number
  calculateSellAmount: () => number
  calculateProfitLoss: () => number
  calculateProfitLossPercentage: () => string
}>()

const emit = defineEmits<{
  (e: 'update:quantity', value: string): void
  (e: 'validate'): void
  (e: 'setPercentage', percent: number): void
  (e: 'confirm'): void
  (e: 'close'): void
}>()
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="max-w-md bg-white border border-brand-red/20 shadow-xl rounded-xl">
      <DialogHeader class="pb-4 border-b border-slate-200">
        <div class="flex items-center justify-between">
          <DialogTitle class="text-xl font-bold text-slate-900 flex items-center gap-2">
            <div class="p-2 rounded-lg bg-gradient-to-r from-brand-red to-brand-red-light">
              <ArrowUpRight class="w-5 h-5 text-white" />
            </div>
            Sell {{ selectedAsset?.crypto?.symbol }}
          </DialogTitle>
          <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="emit('close')">
            <X class="w-4 h-4" />
          </Button>
        </div>
        <DialogDescription class="text-slate-500">
          Sell your {{ selectedAsset?.crypto?.name }} holdings
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <!-- Asset Info -->
        <div class="p-4 rounded-lg border border-slate-200 bg-gradient-to-br from-brand-red/5 to-transparent">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-brand-red to-brand-red-light flex items-center justify-center">
                <span class="font-bold text-white">
                  {{ selectedAsset?.crypto?.symbol?.charAt(0) }}
                </span>
              </div>
              <div>
                <h4 class="font-semibold text-slate-900">{{ selectedAsset?.crypto?.name }}</h4>
                <p class="text-sm text-slate-500">{{ selectedAsset?.crypto?.symbol }}</p>
              </div>
            </div>
            <Badge class="bg-brand-red text-white">
              Available: {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto?.symbol), 8) }}
            </Badge>
          </div>
          
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-slate-500">Current Price</p>
              <p class="font-semibold text-slate-900">
                {{ formatCurrency(selectedAsset?.crypto?.current_price || selectedAsset?.unitPrice || 0) }}
              </p>
            </div>
            <div>
              <p class="text-slate-500">Purchase Price</p>
              <p class="font-semibold text-slate-900">
                {{ formatCurrency(selectedAsset?.unitPrice || 0) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Quantity Input -->
        <div class="space-y-3">
          <Label for="sellQuantity" class="text-sm font-medium text-slate-900">
            Quantity to Sell
            <span class="text-slate-500 font-normal ml-1">
              (Max: {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto?.symbol), 8) }})
            </span>
          </Label>
          
          <div class="relative">
            <Input
              id="sellQuantity"
              :model-value="sellQuantity"
              type="number"
              step="0.00000001"
              :max="getAvailableQuantity(selectedAsset?.crypto?.symbol)"
              min="0.00000001"
              placeholder="0.00000000"
              class="pl-4 pr-24 py-3 text-lg font-medium border-brand-red/30 focus:border-brand-red focus:ring-brand-red/20"
              :disabled="isSelling"
              @update:model-value="emit('update:quantity', $event)"
              @input="emit('validate')"
            />
            <div class="absolute right-2 top-1/2 -translate-y-1/2">
              <Badge class="bg-brand-red text-white">
                {{ selectedAsset?.crypto?.symbol }}
              </Badge>
            </div>
          </div>

          <!-- Quick Percentage Buttons -->
          <div class="grid grid-cols-4 gap-2">
            <Button
              v-for="percent in [25, 50, 75, 100]"
              :key="percent"
              variant="outline"
              size="sm"
              class="text-xs hover:bg-brand-red/10 hover:border-brand-red"
              :disabled="isSelling"
              @click="emit('setPercentage', percent)"
            >
              {{ percent }}%
            </Button>
          </div>
        </div>

        <!-- Sell Summary -->
        <div class="p-4 rounded-lg border border-slate-200 space-y-3">
          <h5 class="font-semibold text-slate-900">Sale Summary</h5>
          
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-sm text-slate-500">Quantity</span>
              <span class="text-sm font-medium text-slate-900">
                {{ sellQuantity || '0.00000000' }} {{ selectedAsset?.crypto?.symbol }}
              </span>
            </div>
            
            <div class="flex justify-between items-center">
              <span class="text-sm text-slate-500">Current Price</span>
              <span class="text-sm font-medium text-slate-900">
                {{ formatCurrency(selectedAsset?.crypto?.current_price || selectedAsset?.unitPrice || 0) }}
              </span>
            </div>
            
            <div class="flex justify-between items-center pt-2 border-t border-slate-200">
              <span class="text-sm font-semibold text-slate-900">Total Amount</span>
              <span class="text-lg font-bold text-brand-red">
                {{ formatCurrency(calculateSellAmount()) }}
              </span>
            </div>

            <!-- Profit/Loss -->
            <div v-if="selectedAsset?.unitPrice && parseFloat(sellQuantity || '0') > 0" class="pt-2 border-t border-slate-200">
              <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500">Profit/Loss</span>
                <span 
                  class="text-sm font-bold"
                  :class="calculateProfitLoss() >= 0 ? 'text-brand-green' : 'text-brand-red'"
                >
                  {{ formatCurrency(calculateProfitLoss()) }}
                  <span class="ml-1">
                    ({{ Number(calculateProfitLossPercentage()) > 0 ? '+' : '' }}{{ calculateProfitLossPercentage() }}%)
                  </span>
                </span>
              </div>
            </div>

            <!-- After Sale -->
            <div v-if="parseFloat(sellQuantity || '0') > 0" class="pt-2 border-t border-slate-200">
              <div class="flex justify-between items-center">
                <span class="text-sm text-slate-500">Remaining</span>
                <span class="text-sm font-medium text-slate-900">
                  {{ formatNumber(Math.max(0, getAvailableQuantity(selectedAsset?.crypto?.symbol) - parseFloat(sellQuantity || '0')), 8) }}
                  {{ selectedAsset?.crypto?.symbol }}
                </span>
              </div>
              
              <Progress 
                :value="((parseFloat(sellQuantity || '0') / getAvailableQuantity(selectedAsset?.crypto?.symbol)) * 100) || 0"
                class="h-2 mt-2 bg-brand-red/20 [&>div]:bg-gradient-to-r [&>div]:from-brand-red [&>div]:to-brand-red-light"
              />
            </div>
          </div>
        </div>

        <!-- Alerts -->
        <Alert v-if="sellError" variant="destructive" class="border-brand-red bg-brand-red/10">
          <AlertCircle class="w-4 h-4 text-brand-red" />
          <AlertDescription class="text-brand-red">
            {{ sellError }}
          </AlertDescription>
        </Alert>

        <Alert v-if="sellSuccess" class="border-brand-green bg-brand-green/10">
          <CheckCircle2 class="w-4 h-4 text-brand-green" />
          <AlertDescription class="text-brand-green">
            {{ sellSuccess }}
          </AlertDescription>
        </Alert>
      </div>

      <DialogFooter class="pt-4 border-t border-slate-200">
        <Button variant="outline" class="flex-1" :disabled="isSelling" @click="emit('close')">
          Cancel
        </Button>
        <Button
          class="flex-1 bg-gradient-to-r from-brand-red to-brand-red-light text-white hover:opacity-90"
          :disabled="isSelling || !sellQuantity || parseFloat(sellQuantity) <= 0 || parseFloat(sellQuantity) > getAvailableQuantity(selectedAsset?.crypto?.symbol)"
          @click="emit('confirm')"
        >
          <Loader2 v-if="isSelling" class="w-4 h-4 mr-2 animate-spin" />
          {{ isSelling ? 'Processing...' : 'Confirm Sale' }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
