// components/TransactionsList.vue
<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent } from '@/components/ui/card'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select'
import { Skeleton } from '@/components/ui/skeleton'
import { Tooltip, TooltipContent, TooltipProvider, TooltipTrigger } from '@/components/ui/tooltip'
import {
  AlertCircle,
  Calendar,
  ChevronLeft,
  ChevronRight,
  Clock,
  Coins,
  DollarSign,
  Eye,
  Hash,
  Receipt,
  TrendingDown,
  TrendingUp
} from 'lucide-vue-next'
import { useRouter } from 'vue-router'

defineProps<{
  loading: boolean
  error: string | null
  transactions: any[]
  showValueInEur: boolean
  currentPage: number
  totalPages: number
  itemsPerPage: number
  filteredCount: number
  formatCurrency: (value: any) => string
  formatNumber: (value: any, decimals?: number) => string
  formatRelativeDate: (date: string) => string
  formatFullDate: (date: string) => string
  getTransactionIcon: (type: string) => any
  getTransactionColor: (type: string) => string
  getTransactionBgColor: (type: string) => string
  canSell: (tx: any) => boolean
}>()

const emit = defineEmits<{
  (e: 'update:itemsPerPage', value: number): void
  (e: 'navigatePage', page: number): void
  (e: 'openSell', tx: any): void
  (e: 'resetFilters'): void
}>()

const router = useRouter()
</script>

<template>
  <div class="transactions-list">
    <!-- Loading State -->
    <div v-if="loading" class="space-y-4">
      <Card v-for="i in 3" :key="i" class="border-slate-200">
        <CardContent class="p-5">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <Skeleton class="w-10 h-10 rounded-xl" />
              <div class="space-y-2">
                <Skeleton class="h-4 w-32" />
                <Skeleton class="h-3 w-24" />
              </div>
            </div>
            <Skeleton class="h-8 w-24" />
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Error State -->
    <Alert v-else-if="error" variant="destructive" class="border-brand-red bg-brand-red/10">
      <AlertCircle class="w-4 h-4 text-brand-red" />
      <AlertDescription class="text-brand-red">
        {{ error }}
      </AlertDescription>
    </Alert>

    <!-- Empty State -->
    <div v-else-if="filteredCount === 0" class="text-center py-12">
      <div class="w-20 h-20 mx-auto mb-6 rounded-full bg-gradient-to-br from-brand-blue/10 to-brand-dark/10 flex items-center justify-center">
        <Receipt class="w-10 h-10 text-brand-blue" />
      </div>
      <h3 class="text-lg font-semibold text-slate-900 mb-2">No transactions found</h3>
      <p class="text-slate-500 max-w-md mx-auto mb-6">
        Try adjusting your filters or search query
      </p>
      <Button variant="outline" @click="emit('resetFilters')">
        Clear Filters
      </Button>
    </div>

    <!-- Transactions List -->
    <div v-else class="space-y-3">
      <Card
        v-for="transaction in transactions"
        :key="transaction.id"
        class="group border-slate-200 hover:border-brand-blue/30 hover:shadow-sm transition-all duration-200"
      >
        <CardContent class="p-5">
          <div class="flex items-start justify-between">
            <!-- Left Side: Transaction Info -->
            <div class="flex items-start gap-4">
              <!-- Transaction Icon -->
              <div class="relative">
                <div 
                  class="w-12 h-12 rounded-xl flex items-center justify-center border"
                  :style="{
                    backgroundColor: getTransactionBgColor(transaction.originalType),
                    borderColor: `${getTransactionColor(transaction.originalType)}30`
                  }"
                >
                  <component 
                    :is="getTransactionIcon(transaction.originalType)"
                    class="w-6 h-6"
                    :style="{ color: getTransactionColor(transaction.originalType) }"
                  />
                </div>
                <Badge
                  class="absolute -top-2 -right-2 text-xs font-semibold border-2 border-white"
                  :style="{
                    backgroundColor: getTransactionColor(transaction.originalType),
                    color: 'white'
                  }"
                >
                  {{ transaction.originalType === 'ACHAT' ? 'BUY' : 'SELL' }}
                </Badge>
              </div>

              <!-- Transaction Details -->
              <div class="space-y-2">
                <div class="flex items-center gap-2">
                  <h4 class="font-semibold text-slate-900">
                    {{ transaction.crypto.name }}
                  </h4>
                  <Badge variant="outline" class="text-xs font-mono">
                    {{ transaction.crypto.symbol }}
                  </Badge>
                  <span class="text-xs text-slate-500">•</span>
                  <span class="text-xs text-slate-500 flex items-center gap-1">
                    <Hash class="w-3 h-3" />
                    {{ transaction.id.slice(0, 8) }}...
                  </span>
                </div>

                <div class="flex flex-wrap items-center gap-4 text-sm">
                  <div class="flex items-center gap-1.5">
                    <Coins class="w-3.5 h-3.5 text-slate-500" />
                    <span class="font-medium text-slate-900">
                      {{ formatNumber(transaction.quantity, 8) }}
                    </span>
                    <span class="text-slate-500">
                      {{ transaction.crypto.symbol }}
                    </span>
                  </div>
                  
                  <div class="flex items-center gap-1.5">
                    <DollarSign class="w-3.5 h-3.5 text-slate-500" />
                    <span class="font-medium text-slate-900">
                      {{ showValueInEur ? formatCurrency(transaction.unitPrice) : formatNumber(transaction.unitPrice) }}
                    </span>
                    <span class="text-slate-500">per unit</span>
                  </div>
                  
                  <div class="flex items-center gap-1.5">
                    <Clock class="w-3.5 h-3.5 text-slate-500" />
                    <span class="text-slate-500">
                      {{ formatRelativeDate(transaction.date) }}
                    </span>
                  </div>
                </div>
              </div>
            </div>

            <!-- Right Side: Amount & Actions -->
            <div class="text-right">
              <div class="mb-2">
                <div class="text-2xl font-bold text-slate-900">
                  {{ showValueInEur ? formatCurrency(transaction.total) : formatNumber(transaction.total) }}
                </div>
                <div class="text-xs text-slate-500">
                  Total {{ showValueInEur ? 'EUR' : 'Crypto' }}
                </div>
              </div>
              
              <div class="flex items-center gap-2">
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Button
                        variant="ghost"
                        size="sm"
                        class="gap-1 text-slate-500 hover:text-brand-blue"
                        @click="router.push(`/dashboard/cryptos/${transaction.crypto.id}`)"
                      >
                        <Eye class="w-3.5 h-3.5" />
                        View
                      </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                      <p>View {{ transaction.crypto.name }} details</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
                
                <Button
                  v-if="canSell(transaction)"
                  variant="outline"
                  size="sm"
                  class="gap-1 border-brand-red text-brand-red hover:bg-brand-red/10"
                  @click="emit('openSell', transaction)"
                >
                  Sell
                </Button>
              </div>
            </div>
          </div>

          <!-- Additional Info -->
          <div class="mt-4 pt-4 border-t border-slate-200">
            <div class="flex items-center justify-between text-sm">
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                  <Calendar class="w-3.5 h-3.5 text-slate-500" />
                  <span class="text-slate-500">
                    {{ formatFullDate(transaction.date) }}
                  </span>
                </div>
                
                <div 
                  v-if="transaction.crypto.current_price"
                  class="flex items-center gap-1.5"
                  :class="transaction.crypto.current_price >= transaction.unitPrice ? 'text-brand-green' : 'text-brand-red'"
                >
                  <component 
                    :is="transaction.crypto.current_price >= transaction.unitPrice ? TrendingUp : TrendingDown"
                    class="w-3.5 h-3.5"
                  />
                  <span class="font-medium">
                    {{ formatCurrency(transaction.crypto.current_price) }}
                  </span>
                  <span class="text-slate-500">current price</span>
                </div>
              </div>
              
              <Badge 
                variant="outline" 
                class="text-xs"
                :style="{
                  color: getTransactionColor(transaction.originalType),
                  borderColor: `${getTransactionColor(transaction.originalType)}30`,
                  backgroundColor: `${getTransactionColor(transaction.originalType)}10`
                }"
              >
                {{ transaction.typeLabel.toUpperCase() }}
              </Badge>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Pagination -->
    <div v-if="totalPages > 1 && !loading" class="flex items-center justify-between pt-6 border-t border-slate-200">
      <div class="text-sm text-slate-500">
        Showing {{ Math.min((currentPage - 1) * itemsPerPage + 1, filteredCount) }} 
        to {{ Math.min(currentPage * itemsPerPage, filteredCount) }} 
        of {{ filteredCount }} transactions
      </div>
      
      <div class="flex items-center gap-2">
        <Select :model-value="itemsPerPage" @update:model-value="emit('update:itemsPerPage', $event)">
          <SelectTrigger class="w-28 border-slate-200">
            <SelectValue />
          </SelectTrigger>
          <SelectContent>
            <SelectItem :value="10">10 per page</SelectItem>
            <SelectItem :value="25">25 per page</SelectItem>
            <SelectItem :value="50">50 per page</SelectItem>
          </SelectContent>
        </Select>
        
        <div class="flex items-center gap-1">
          <Button
            variant="outline"
            size="sm"
            class="h-9 w-9 p-0"
            :disabled="currentPage === 1"
            @click="emit('navigatePage', currentPage - 1)"
          >
            <ChevronLeft class="w-4 h-4" />
          </Button>
          
          <div class="flex items-center gap-1">
            <Button
              v-for="page in totalPages"
              v-show="Math.abs(page - currentPage) <= 1 || page === 1 || page === totalPages"
              :key="page"
              variant="outline"
              size="sm"
              class="h-9 w-9"
              :class="page === currentPage ? 'bg-brand-blue text-white border-brand-blue' : 'border-slate-200'"
              @click="emit('navigatePage', page)"
            >
              {{ page }}
            </Button>
          </div>
          
          <Button
            variant="outline"
            size="sm"
            class="h-9 w-9 p-0"
            :disabled="currentPage === totalPages"
            @click="emit('navigatePage', currentPage + 1)"
          >
            <ChevronRight class="w-4 h-4" />
          </Button>
        </div>
      </div>
    </div>
  </div>
</template>

