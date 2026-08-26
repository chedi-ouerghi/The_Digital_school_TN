<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Progress } from '@/components/ui/progress'
import { AlertCircle, CheckCircle2, Wallet } from 'lucide-vue-next'

defineProps<{
  show: boolean
  selectedCrypto: any
  quantity: string
  userBalance: number
  buyingError: string
  buyingSuccess: string
  isBuying: boolean
  makeImageUrl: (path: string | undefined | null) => string | undefined
  formatCurrency: (value: any) => string
  formatNumber: (value: any, decimals?: number) => string
  isInPortfolio: (crypto: any) => boolean
  getOwnedQuantity: (crypto: any) => number
  calculateMaxQuantity: () => number
  calculateQuickAmount: (amount: number) => number
  calculateTotalCost: () => number
}>()

const emit = defineEmits<{
  (event: 'update:quantity', value: string): void
  (event: 'validate'): void
  (event: 'set-max'): void
  (event: 'quick-amount', amount: number): void
  (event: 'close'): void
  (event: 'confirm'): void
}>()
</script>

<template>
  <Dialog :open="show" @update:open="emit('close')">
    <DialogContent class="sm:max-w-lg border border-gray-200">
      <DialogHeader>
        <div class="flex items-center gap-3 mb-2">
          <div class="w-10 h-10 rounded-full bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
            <Wallet class="h-5 w-5 text-white" />
          </div>
          <div>
            <DialogTitle class="text-lg font-semibold text-gray-900">Buy Cryptocurrency</DialogTitle>
            <DialogDescription class="text-gray-600">
              Purchase {{ selectedCrypto?.name || selectedCrypto?.nom }}
            </DialogDescription>
          </div>
        </div>
      </DialogHeader>

      <div class="space-y-6 py-4">
        <Alert v-if="buyingError" class="border-red-200 bg-red-50">
          <AlertDescription class="text-red-700 flex items-center gap-2">
            <AlertCircle class="h-4 w-4" />
            {{ buyingError }}
          </AlertDescription>
        </Alert>
        <Alert v-if="buyingSuccess" class="border-emerald-200 bg-emerald-50">
          <AlertDescription class="text-emerald-700 flex items-center gap-2">
            <CheckCircle2 class="h-4 w-4" />
            {{ buyingSuccess }}
          </AlertDescription>
        </Alert>

        <Card class="border border-gray-200">
          <CardContent class="p-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 rounded-full border border-gray-300 overflow-hidden bg-white">
                <img
                  v-if="selectedCrypto && makeImageUrl(selectedCrypto.image || selectedCrypto.image_url)"
                  :src="makeImageUrl(selectedCrypto.image || selectedCrypto.image_url)"
                  :alt="selectedCrypto.name || selectedCrypto.nom"
                  class="w-full h-full object-cover"
                />
                <div v-else class="w-full h-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                  <span class="text-white font-bold text-lg">
                    {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '?').toString().charAt(0).toUpperCase() }}
                  </span>
                </div>
              </div>
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-1">
                  <div class="font-bold text-gray-900">{{ selectedCrypto?.name || selectedCrypto?.nom }}</div>
                  <Badge variant="outline" class="font-mono">
                    {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}
                  </Badge>
                </div>
                <div class="text-xl font-bold text-blue-600">
                  {{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}
                </div>
              </div>
              <Badge :class="Number(selectedCrypto?.change_24h_pct || selectedCrypto?.change_24h || 0) >= 0 ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'">
                {{ Number(selectedCrypto?.change_24h_pct || selectedCrypto?.change_24h || 0).toFixed(2) }}%
              </Badge>
            </div>
          </CardContent>
        </Card>

        <div class="space-y-4">
          <div class="space-y-2">
            <Label class="text-sm font-medium text-gray-700">Quantity to Buy</Label>
            <div v-if="selectedCrypto && isInPortfolio(selectedCrypto)" class="text-sm text-gray-600">
              You own: <strong class="text-gray-900">{{ getOwnedQuantity(selectedCrypto) }} {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}</strong>
            </div>
            <div class="flex-1 relative">
              <Input
                :model-value="quantity"
                type="number"
                step="0.00000001"
                min="0.00000001"
                placeholder="Enter quantity"
                class="pr-28"
                :disabled="isBuying"
                @update:model-value="emit('update:quantity', String($event))"
                @input="emit('validate')"
              />
              <div class="absolute right-2 top-1/2 transform -translate-y-1/2 flex items-center gap-1.5">
                <button type="button" class="h-6 px-2 rounded-md text-[11px] font-bold bg-blue-100 text-blue-700 border border-blue-200 disabled:opacity-50" :disabled="isBuying || calculateMaxQuantity() <= 0" @click="emit('set-max')">MAX</button>
                <Badge class="bg-blue-100 text-blue-700 border-blue-200">{{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}</Badge>
              </div>
            </div>
            <div class="grid grid-cols-4 gap-2">
              <Button v-for="amount in [10, 50, 100, 500]" :key="amount" variant="outline" size="sm" class="text-xs" :disabled="isBuying" @click="emit('quick-amount', amount)">
                {{ formatNumber(calculateQuickAmount(amount), 8) }}
              </Button>
            </div>
          </div>

          <Card class="border border-gray-200">
            <CardContent class="p-4 space-y-3">
              <h4 class="text-sm font-semibold text-gray-900 mb-2">Order Summary</h4>
              <div class="flex justify-between"><span class="text-sm text-gray-600">Unit Price</span><span class="text-sm font-medium text-gray-900">{{ formatCurrency(selectedCrypto?.price_eur || selectedCrypto?.price) }}</span></div>
              <div class="flex justify-between"><span class="text-sm text-gray-600">Quantity</span><span class="text-sm font-medium text-blue-600">{{ formatNumber(quantity || 0, 8) }} {{ (selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase() }}</span></div>
              <div class="pt-2 border-t flex justify-between"><span class="text-sm font-semibold text-gray-900">Total Cost</span><span class="text-lg font-bold text-emerald-600">{{ formatCurrency(calculateTotalCost()) }}</span></div>
            </CardContent>
          </Card>

          <div class="space-y-2">
            <div class="flex justify-between text-sm"><span class="text-gray-600">Available Balance</span><span class="font-medium text-emerald-600">{{ formatCurrency(userBalance) }}</span></div>
            <Progress :value="Math.min((calculateTotalCost() / userBalance) * 100, 100)" class="h-2" :class="calculateTotalCost() <= userBalance ? 'bg-green-500' : 'bg-red-500'" />
            <div class="flex justify-between text-sm"><span class="text-gray-600">Remaining After Purchase</span><span class="font-medium">{{ formatCurrency(userBalance - calculateTotalCost()) }}</span></div>
          </div>
        </div>
      </div>

      <DialogFooter class="flex gap-2">
        <Button variant="outline" :disabled="isBuying" @click="emit('close')">Cancel</Button>
        <Button class="bg-gradient-to-r from-blue-500 to-blue-600 text-white gap-2" :disabled="isBuying || !quantity || parseFloat(quantity) <= 0 || calculateTotalCost() > userBalance" @click="emit('confirm')">
          <Wallet class="h-4 w-4" />
          {{ isBuying ? 'Processing...' : `Buy ${formatNumber(quantity || 0, 4)} ${(selectedCrypto?.symbole || selectedCrypto?.symbol || '').toString().toUpperCase()}` }}
        </Button>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>
