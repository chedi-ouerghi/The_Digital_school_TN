<template>
  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 sm:gap-6">
    <!-- Left Column - 2/3 width -->
    <div class="xl:col-span-2 space-y-4 sm:space-y-6">
      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 sm:gap-6">
        <!-- Bar Chart -->
        <Card>
          <CardHeader class="pb-3">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              📊 Top Cryptos by Volume
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-[250px] sm:h-[300px]">
              <Bar :data="barChartData" :options="barChartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Doughnut Chart -->
        <Card>
          <CardHeader class="pb-3">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              🥧 Volume Distribution
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-[250px] sm:h-[300px]">
              <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Transactions -->
      <Card>
        <CardHeader class="pb-3">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              📋 Last 5 Transactions
            </CardTitle>
            <Button 
              @click="$emit('goToTransactions')"
              variant="outline"
              size="sm"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white text-xs sm:text-sm"
            >
              View All →
            </Button>
          </div>
        </CardHeader>
        <CardContent>
          <div v-if="recentTransactions.length === 0" class="text-center py-8 sm:py-12">
            <div class="text-4xl sm:text-6xl mb-4">📋</div>
            <p class="text-gray-500 text-sm sm:text-base">No recent transactions</p>
          </div>
          <div v-else class="space-y-3">
            <Card 
              v-for="tx in recentTransactions" 
              :key="tx.id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all"
            >
              <CardContent class="p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                  <div class="flex items-center gap-3 sm:gap-4 flex-1">
                    <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img 
                        v-if="tx.cryptomoney?.image_url"
                        :src="tx.cryptomoney.image_url"
                        :alt="tx.cryptomoney?.name"
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                        @error="(e) => {
                          const target = e.target as HTMLImageElement
                          if (target && target.parentNode) {
                            target.style.display = 'none'
                          }
                        }"
                      />
                      <div v-if="!tx.cryptomoney?.image_url" class="text-sm sm:text-lg">💎</div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1">
                        <Badge 
                          :class="tx.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                          class="text-white text-xs"
                        >
                          {{ tx.type === 'ACHAT' ? '📈 BUY' : '📉 SELL' }}
                        </Badge>
                        <span class="text-xs text-gray-500">Transaction #{{ tx.id.slice(0, 8) }}</span>
                      </div>
                      <div class="font-semibold text-[#38618C] text-sm sm:text-base truncate">
                        {{ tx.cryptomoney?.name || 'Crypto' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        {{ String(tx.cryptomoney?.symbol || '').toUpperCase() }}
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4 sm:gap-6 text-right">
                    <div>
                      <div class="text-xs text-gray-500">Quantity</div>
                      <div class="text-sm sm:text-base font-bold text-[#38618C] font-mono">
                        {{ formatNumber(tx.quantity, 6) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500">Amount</div>
                      <div class="text-base sm:text-lg font-bold text-[#35A7FF]">
                        {{ formatCurrency(tx.total_eur) }}
                      </div>
                    </div>
                  </div>

                  <div class="text-xs text-gray-500 text-center sm:text-right">
                    {{ new Date(tx.created_at).toLocaleDateString('en-US', { 
                      day: '2-digit', 
                      month: 'short',
                      hour: '2-digit',
                      minute: '2-digit'
                    }) }}
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </CardContent>
      </Card>
    </div>

    <!-- Right Column - 1/3 width -->
    <div class="space-y-4 sm:space-y-6">
      <!-- Top 5 Most Traded Cryptos -->
      <Card class="xl:top-6">
        <CardHeader class="pb-3">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              🏆 Top 5 Most Traded Cryptos
            </CardTitle>
            <Button 
              @click="$emit('goToCryptos')"
              variant="outline"
              size="sm"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white text-xs sm:text-sm"
            >
              View All →
            </Button>
          </div>
        </CardHeader>

        <CardContent class="flex flex-col gap-2 sm:gap-3">
          <!-- No Data -->
          <div v-if="!stats?.top_traded?.length" class="text-center py-6 sm:py-8">
            <div class="text-3xl sm:text-4xl mb-3">💎</div>
            <p class="text-gray-500 text-xs sm:text-sm">No trading data available</p>
          </div>

          <!-- Top Traded Cryptos -->
          <div v-else class="flex flex-col gap-2 sm:gap-3">
            <Card 
              v-for="(crypto, index) in stats.top_traded.slice(0, 5)" 
              :key="crypto.cryptomoney_id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-md"
            >
              <CardContent class="flex items-center gap-2 sm:gap-3 p-2 sm:p-3">
                <!-- Rank Badge -->
                <div class="flex-shrink-0">
                  <div class="w-6 h-6 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-white font-bold text-xs sm:text-sm"
                       :class="getRankColor(index)">
                    {{ index + 1 }}
                  </div>
                </div>

                <!-- Crypto Image -->
                <div class="flex-shrink-0">
                  <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border border-gray-300 bg-gray-100 flex items-center justify-center">
                    <img 
                      v-if="cryptoDetails.get(crypto.cryptomoney_id)?.image_url"
                      :src="cryptoDetails.get(crypto.cryptomoney_id).image_url"
                      :alt="cryptoDetails.get(crypto.cryptomoney_id)?.name"
                      class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                      @error="(e) => e.target.style.display = 'none'"
                    />
                    <div v-else class="text-xs sm:text-sm">💎</div>
                  </div>
                </div>

                <!-- Crypto Info -->
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-[#38618C] text-xs sm:text-sm truncate">
                    {{ cryptoDetails.get(crypto.cryptomoney_id)?.name || 'Loading...' }}
                  </div>
                  <Badge class="bg-[#35A7FF] text-white text-xs font-mono mt-1">
                    {{ (cryptoDetails.get(crypto.cryptomoney_id)?.symbol || crypto.symbole || 'N/A').toUpperCase() }}
                  </Badge>
                </div>

                <!-- Trading Stats -->
                <div class="text-right flex-shrink-0">
                  <div class="text-xs text-gray-500 mb-1">Traded Qty</div>
                  <div class="text-xs sm:text-sm font-bold text-[#01FF19] font-mono">
                    {{ formatNumber(crypto.total_quantity, 2) }}
                  </div>
                  <div class="text-xs text-gray-500 mt-1">
                    {{ formatCurrency(cryptoDetails.get(crypto.cryptomoney_id)?.price_eur || 0) }}
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Quick Stats Summary -->
          <div v-if="stats?.top_traded?.length" class="mt-3 sm:mt-4 pt-3 sm:pt-4 border-t border-gray-200 grid grid-cols-2 gap-3 sm:gap-4 text-center">
            <div>
              <div class="text-xs text-gray-500">Total Traded</div>
              <div class="text-sm font-bold text-[#38618C]">
                {{ formatNumber(stats.top_traded.reduce((sum, crypto) => sum + crypto.total_quantity, 0), 2) }}
              </div>
            </div>
            <div>
              <div class="text-xs text-gray-500">Active Cryptos</div>
              <div class="text-sm font-bold text-[#35A7FF]">
                {{ stats.top_traded.length }}
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>

<script setup lang="ts">
import { Bar, Doughnut } from 'vue-chartjs'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'

defineProps<{
  stats: any
  cryptoDetails: Map<number, any>
  recentTransactions: any[]
  barChartData: any
  barChartOptions: any
  doughnutChartData: any
  doughnutChartOptions: any
  formatCurrency: (value: any) => string
  formatNumber: (value: any, decimals?: number) => string
  getRankColor: (index: number) => string
}>()

defineEmits<{
  goToTransactions: []
  goToCryptos: []
}>()
</script>