<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Bar, Doughnut } from 'vue-chartjs';
import {
  TrendingUp, ArrowRight, ExternalLink
} from 'lucide-vue-next';

// Define proper types
interface Transaction {
  id: string
  type: 'ACHAT' | 'VENTE'
  quantity: string
  total_eur: string
  created_at: string
  crypto_name: string
  crypto_wallet_asset?: {
    cryptomoney?: {
      image_url?: string
    }
  }
}

interface CryptoStats {
  id: string
  name: string
  symbol: string
  image?: string
  total_volume: string | number
}

interface Props {
  stats: {
    top_cryptos?: CryptoStats[]
    total_volume?: string | number
    total_transactions?: number
  }
  cryptoDetails: Map<number, any>
  recentTransactions: Transaction[]
  barChartData: any
  barChartOptions: any
  doughnutChartData: any
  doughnutChartOptions: any
  formatCurrency: (value: any) => string
  formatNumber: (value: any, decimals?: number) => string
  getRankColor: (index: number) => string
}

defineProps<Props>()

defineEmits<{
  goToTransactions: []
  goToCryptos: []
}>()

// Helper function to handle image errors
const handleImageError = (event: Event) => {
  const target = event.target as HTMLImageElement | null
  if (target) {
    target.style.display = 'none'
  }
}
</script>

<template>
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
    <!-- Left Column - 2/3 width -->
    <div class="xl:col-span-2 space-y-6">
      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart -->
        <Card class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
          <CardHeader class="pb-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                  <TrendingUp class="h-4 w-4 text-blue-600" />
                </div>
                <CardTitle class="text-base font-semibold text-gray-900">
                  Top Cryptos by Volume
                </CardTitle>
              </div>
              <Badge variant="outline" class="text-xs">Last 30 Days</Badge>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="!stats?.top_cryptos?.length" class="h-[300px] flex items-center justify-center">
              <div class="text-center text-gray-500">
                <div class="text-4xl mb-3">📊</div>
                <p class="text-sm">No volume data available</p>
              </div>
            </div>
            <div v-else class="h-[300px]">
              <Bar :data="barChartData" :options="barChartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Doughnut Chart -->
        <Card class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
          <CardHeader class="pb-4">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-purple-50 to-purple-100 flex items-center justify-center">
                  <TrendingUp class="h-4 w-4 text-purple-600" />
                </div>
                <CardTitle class="text-base font-semibold text-gray-900">
                  Volume Distribution
                </CardTitle>
              </div>
              <Badge variant="outline" class="text-xs">Market Share</Badge>
            </div>
          </CardHeader>
          <CardContent>
            <div v-if="!stats?.top_cryptos?.length" class="h-[300px] flex items-center justify-center">
              <div class="text-center text-gray-500">
                <div class="text-4xl mb-3">🥧</div>
                <p class="text-sm">No distribution data available</p>
              </div>
            </div>
            <div v-else class="h-[300px]">
              <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Transactions -->
      <Card class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <CardHeader class="pb-4">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center">
                <TrendingUp class="h-4 w-4 text-amber-600" />
              </div>
              <div>
                <CardTitle class="text-base font-semibold text-gray-900">
                  Recent Transactions
                </CardTitle>
                <p class="text-xs text-gray-500 mt-1">Last 10 transactions across platform</p>
              </div>
            </div>
            <Button 
              variant="ghost"
              size="sm"
              class="text-blue-600 hover:text-blue-700 hover:bg-blue-50"
              @click="$emit('goToTransactions')"
            >
              View All
              <ArrowRight class="h-3 w-3 ml-1" />
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="recentTransactions.length === 0" class="text-center py-12">
            <div class="text-4xl mb-3">📋</div>
            <p class="text-gray-500 text-sm">No recent transactions</p>
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="tx in recentTransactions" 
              :key="tx.id"
              class="group flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div class="flex items-center gap-3">
                <div class="relative">
                  <div class="w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 flex items-center justify-center">
                    <img 
                      v-if="tx.crypto_wallet_asset?.cryptomoney?.image_url"
                      :src="tx.crypto_wallet_asset.cryptomoney.image_url"
                      :alt="tx.crypto_name"
                      class="w-8 h-8 rounded-full object-cover"
                      @error="handleImageError"
                    />
                    <div v-else class="text-sm">💎</div>
                  </div>
                  <Badge 
                    :class="[
                      'absolute -top-1 -right-1 text-xs px-1.5',
                      tx.type === 'ACHAT' ? 'bg-green-500' : 'bg-red-500'
                    ]"
                  >
                    {{ tx.type === 'ACHAT' ? 'B' : 'S' }}
                  </Badge>
                </div>
                <div>
                  <div class="font-medium text-sm text-gray-900">{{ tx.crypto_name }}</div>
                  <div class="text-xs text-gray-500">
                    {{ new Date(tx.created_at).toLocaleDateString('en-US', { 
                      month: 'short',
                      day: 'numeric',
                      hour: '2-digit',
                      minute: '2-digit'
                    }) }}
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-4">
                <div class="text-right">
                  <div class="font-medium text-sm text-gray-900">{{ formatNumber(tx.quantity, 4) }}</div>
                  <div class="text-xs text-gray-500">Quantity</div>
                </div>
                <div class="text-right">
                  <div class="font-bold text-blue-600">{{ formatCurrency(tx.total_eur) }}</div>
                  <div class="text-xs text-gray-500">Amount</div>
                </div>
                <div class="text-xs text-gray-400 group-hover:text-gray-600">
                  <ExternalLink class="h-4 w-4" />
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Right Column - 1/3 width -->
    <div class="space-y-6">
      <!-- Top Cryptos -->
      <Card class="border border-gray-200 shadow-sm hover:shadow-md transition-shadow">
        <CardHeader class="pb-4">
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center">
                <TrendingUp class="h-4 w-4 text-emerald-600" />
              </div>
              <CardTitle class="text-base font-semibold text-gray-900">
                Top Traded Cryptos
              </CardTitle>
            </div>
            <Button 
              variant="ghost"
              size="sm"
              class="text-emerald-600 hover:text-emerald-700 hover:bg-emerald-50"
              @click="$emit('goToCryptos')"
            >
              All
              <ArrowRight class="h-3 w-3 ml-1" />
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="!stats?.top_cryptos?.length" class="text-center py-12">
            <div class="text-4xl mb-3">💎</div>
            <p class="text-gray-500 text-sm">No trading data</p>
          </div>
          <div v-else class="space-y-3">
            <div 
              v-for="(crypto, index) in stats.top_cryptos.slice(0, 5)" 
              :key="crypto.id"
              class="group flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div class="flex items-center gap-3">
                <div
:class="[
                  'w-8 h-8 rounded-lg flex items-center justify-center text-white font-bold text-sm',
                  getRankColor(index)
                ]">
                  {{ index + 1 }}
                </div>
                <div class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center">
                  <img 
                    v-if="crypto.image"
                    :src="crypto.image.startsWith('http') ? crypto.image : `http://localhost:8000/storage/${crypto.image}`"
                    :alt="crypto.name"
                    class="w-6 h-6 rounded-full object-cover"
                    @error="handleImageError"
                  />
                  <div v-else class="text-xs">💎</div>
                </div>
                <div>
                  <div class="font-medium text-sm text-gray-900">{{ crypto.name }}</div>
                  <Badge variant="outline" class="text-xs mt-1">
                    {{ crypto.symbol?.toUpperCase() || 'N/A' }}
                  </Badge>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-emerald-600">{{ formatCurrency(crypto.total_volume) }}</div>
                <div class="text-xs text-gray-500">Volume</div>
              </div>
            </div>
            
            <!-- Summary -->
            <div class="pt-4 mt-4 border-t border-gray-200">
              <div class="grid grid-cols-2 gap-4">
                <div class="text-center">
                  <div class="text-xs text-gray-500 mb-1">Total Volume</div>
                  <div class="font-bold text-gray-900">
                    {{
                      formatCurrency(
                        stats.top_cryptos.reduce(
                          (sum: number, c: CryptoStats) => sum + Number(c.total_volume || 0), 
                          0
                        )
                      )
                    }}
                  </div>
                </div>
                <div class="text-center">
                  <div class="text-xs text-gray-500 mb-1">Avg. per Crypto</div>
                  <div class="font-bold text-blue-600">
                    {{
                      formatCurrency(
                        stats.top_cryptos.length > 0 
                          ? stats.top_cryptos.reduce(
                              (sum: number, c: CryptoStats) => sum + Number(c.total_volume || 0), 
                              0
                            ) / stats.top_cryptos.length 
                          : 0
                      )
                    }}
                  </div>
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>