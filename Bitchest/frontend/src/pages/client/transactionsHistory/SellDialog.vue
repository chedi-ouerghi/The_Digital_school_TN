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
import type { formatCurrency, formatNumber } from '.'

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
    <DialogContent class="max-w-md bg-white border border-[#FF5964]/20 shadow-xl rounded-xl">
      <DialogHeader class="pb-4 border-b border-[#E2E8F0]">
        <div class="flex items-center justify-between">
          <DialogTitle class="text-xl font-bold text-[#0F172A] flex items-center gap-2">
            <div class="p-2 rounded-lg bg-gradient-to-r from-[#FF5964] to-[#FF8B94]">
              <ArrowUpRight class="w-5 h-5 text-white" />
            </div>
            Sell {{ selectedAsset?.crypto?.symbol }}
          </DialogTitle>
          <Button variant="ghost" size="sm" class="h-8 w-8 p-0" @click="emit('close')">
            <X class="w-4 h-4" />
          </Button>
        </div>
        <DialogDescription class="text-[#64748B]">
          Sell your {{ selectedAsset?.crypto?.name }} holdings
        </DialogDescription>
      </DialogHeader>

      <div class="space-y-4 py-4">
        <!-- Asset Info -->
        <div class="p-4 rounded-lg border border-[#E2E8F0] bg-gradient-to-br from-[#FF5964]/5 to-transparent">
          <div class="flex items-center justify-between mb-3">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-[#FF5964] to-[#FF8B94] flex items-center justify-center">
                <span class="font-bold text-white">
                  {{ selectedAsset?.crypto?.symbol?.charAt(0) }}
                </span>
              </div>
              <div>
                <h4 class="font-semibold text-[#0F172A]">{{ selectedAsset?.crypto?.name }}</h4>
                <p class="text-sm text-[#64748B]">{{ selectedAsset?.crypto?.symbol }}</p>
              </div>
            </div>
            <Badge class="bg-[#FF5964] text-white">
              Available: {{ formatNumber(getAvailableQuantity(selectedAsset?.crypto?.symbol), 8) }}
            </Badge>
          </div>
          
          <div class="grid grid-cols-2 gap-4 text-sm">
            <div>
              <p class="text-[#64748B]">Current Price</p>
              <p class="font-semibold text-[#0F172A]">
                {{ formatCurrency(selectedAsset?.crypto?.current_price || selectedAsset?.unitPrice || 0) }}
              </p>
            </div>
            <div>
              <p class="text-[#64748B]">Purchase Price</p>
              <p class="font-semibold text-[#0F172A]">
                {{ formatCurrency(selectedAsset?.unitPrice || 0) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Quantity Input -->
        <div class="space-y-3">
          <Label for="sellQuantity" class="text-sm font-medium text-[#0F172A]">
            Quantity to Sell
            <span class="text-[#64748B] font-normal ml-1">
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
              class="pl-4 pr-24 py-3 text-lg font-medium border-[#FF5964]/30 focus:border-[#FF5964] focus:ring-[#FF5964]/20"
              :disabled="isSelling"
              @update:model-value="emit('update:quantity', $event)"
              @input="emit('validate')"
            />
            <div class="absolute right-2 top-1/2 -translate-y-1/2">
              <Badge class="bg-[#FF5964] text-white">
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
              class="text-xs hover:bg-[#FF5964]/10 hover:border-[#FF5964]"
              :disabled="isSelling"
              @click="emit('setPercentage', percent)"
            >
              {{ percent }}%
            </Button>
          </div>
        </div>

        <!-- Sell Summary -->
        <div class="p-4 rounded-lg border border-[#E2E8F0] space-y-3">
          <h5 class="font-semibold text-[#0F172A]">Sale Summary</h5>
          
          <div class="space-y-2">
            <div class="flex justify-between items-center">
              <span class="text-sm text-[#64748B]">Quantity</span>
              <span class="text-sm font-medium text-[#0F172A]">
                {{ sellQuantity || '0.00000000' }} {{ selectedAsset?.crypto?.symbol }}
              </span>
            </div>
            
            <div class="flex justify-between items-center">
              <span class="text-sm text-[#64748B]">Current Price</span>
              <span class="text-sm font-medium text-[#0F172A]">
                {{ formatCurrency(selectedAsset?.crypto?.current_price || selectedAsset?.unitPrice || 0) }}
              </span>
            </div>
            
            <div class="flex justify-between items-center pt-2 border-t border-[#E2E8F0]">
              <span class="text-sm font-semibold text-[#0F172A]">Total Amount</span>
              <span class="text-lg font-bold text-[#FF5964]">
                {{ formatCurrency(calculateSellAmount()) }}
              </span>
            </div>

            <!-- Profit/Loss -->
            <div v-if="selectedAsset?.unitPrice && parseFloat(sellQuantity || '0') > 0" class="pt-2 border-t border-[#E2E8F0]">
              <div class="flex justify-between items-center">
                <span class="text-sm text-[#64748B]">Profit/Loss</span>
                <span 
                  class="text-sm font-bold"
                  :class="calculateProfitLoss() >= 0 ? 'text-[#01FF19]' : 'text-[#FF5964]'"
                >
                  {{ formatCurrency(calculateProfitLoss()) }}
                  <span class="ml-1">
                    ({{ Number(calculateProfitLossPercentage()) > 0 ? '+' : '' }}{{ calculateProfitLossPercentage() }}%)
                  </span>
                </span>
              </div>
            </div>

            <!-- After Sale -->
            <div v-if="parseFloat(sellQuantity || '0') > 0" class="pt-2 border-t border-[#E2E8F0]">
              <div class="flex justify-between items-center">
                <span class="text-sm text-[#64748B]">Remaining</span>
                <span class="text-sm font-medium text-[#0F172A]">
                  {{ formatNumber(Math.max(0, getAvailableQuantity(selectedAsset?.crypto?.symbol) - parseFloat(sellQuantity || '0')), 8) }}
                  {{ selectedAsset?.crypto?.symbol }}
                </span>
              </div>
              
              <Progress 
                :value="((parseFloat(sellQuantity || '0') / getAvailableQuantity(selectedAsset?.crypto?.symbol)) * 100) || 0"
                class="h-2 mt-2 bg-[#FF5964]/20 [&>div]:bg-gradient-to-r [&>div]:from-[#FF5964] [&>div]:to-[#FF8B94]"
              />
            </div>
          </div>
        </div>

        <!-- Alerts -->
        <Alert v-if="sellError" variant="destructive" class="border-[#FF5964] bg-[#FF5964]/10">
          <AlertCircle class="w-4 h-4 text-[#FF5964]" />
          <AlertDescription class="text-[#FF5964]">
            {{ sellError }}
          </AlertDescription>
        </Alert>

        <Alert v-if="sellSuccess" class="border-[#01FF19] bg-[#01FF19]/10">
          <CheckCircle2 class="w-4 h-4 text-[#01FF19]" />
          <AlertDescription class="text-[#01FF19]">
            {{ sellSuccess }}
          </AlertDescription>
        </Alert>
      </div>

      <DialogFooter class="pt-4 border-t border-[#E2E8F0]">
        <Button variant="outline" class="flex-1" :disabled="isSelling" @click="emit('close')">
          Cancel
        </Button>
        <Button
          class="flex-1 bg-gradient-to-r from-[#FF5964] to-[#FF8B94] text-white hover:opacity-90"
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
