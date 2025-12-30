<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Bar, Doughnut } from 'vue-chartjs';

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

<template>
  <!-- Main Content Grid -->
  <div class="grid grid-cols-1 xl:grid-cols-3 gap-6 xl:gap-8">
    <!-- Left Column - 2/3 width -->
    <div class="xl:col-span-2 space-y-6">
      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Bar Chart -->
        <Card class="shadow-md hover:shadow-lg transition-shadow">
          <CardHeader class="pb-4 border-b border-gray-200">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              📊 Top Cryptos by Volume
            </CardTitle>
          </CardHeader>
          <CardContent class="pt-6">
            <div v-if="!stats?.top_cryptos?.length" class="h-[300px] flex items-center justify-center text-gray-500">
              <div class="text-center">
                <div class="text-3xl mb-2">📊</div>
                <p>No volume data available</p>
              </div>
            </div>
            <div v-else class="h-[300px]">
              <Bar :data="barChartData" :options="barChartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Doughnut Chart -->
        <Card class="shadow-md hover:shadow-lg transition-shadow">
          <CardHeader class="pb-4 border-b border-gray-200">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              🥧 Volume Distribution
            </CardTitle>
          </CardHeader>
          <CardContent class="pt-6">
            <div v-if="!stats?.top_cryptos?.length" class="h-[300px] flex items-center justify-center text-gray-500">
              <div class="text-center">
                <div class="text-3xl mb-2">🥧</div>
                <p>No distribution data available</p>
              </div>
            </div>
            <div v-else class="h-[300px]">
              <Doughnut :data="doughnutChartData" :options="doughnutChartOptions" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Recent Transactions -->
      <Card class="shadow-md hover:shadow-lg transition-shadow">
        <CardHeader class="pb-4 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              📋 Last 10 Transactions
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
        <CardContent class="pt-6">
          <div v-if="recentTransactions.length === 0" class="text-center py-8 sm:py-12">
            <div class="text-4xl sm:text-6xl mb-4">📋</div>
            <p class="text-gray-500 text-sm sm:text-base">No recent transactions</p>
          </div>
          <div v-else class="space-y-3">
            <Card 
              v-for="tx in recentTransactions" 
              :key="tx.id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-md"
            >
              <CardContent class="p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-4">
                  <div class="flex items-center gap-3 sm:gap-4 flex-1">
                    <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center flex-shrink-0">
                      <img 
                        v-if="tx.cryptomoney?.image"
                        :src="tx.cryptomoney.image"
                        :alt="tx.cryptomoney?.name"
                        class="w-8 h-8 sm:w-10 sm:h-10 rounded-full object-cover"
                        @error="(e) => {
                          const target = e.target as HTMLImageElement
                          if (target && target.parentNode) {
                            target.style.display = 'none'
                          }
                        }"
                      />
                      <div v-else class="text-sm sm:text-lg">💎</div>
                    </div>
                    <div class="flex-1 min-w-0">
                      <div class="flex flex-col sm:flex-row sm:items-center gap-1 sm:gap-2 mb-1">
                        <Badge 
                          :class="tx.type === 'ACHAT' ? 'bg-[#01FF19]' : 'bg-[#FF5964]'"
                          class="text-white text-xs"
                        >
                          {{ tx.type === 'ACHAT' ? '📈 BUY' : '📉 SELL' }}
                        </Badge>
                        <span class="text-xs text-gray-500">ID: {{ tx.id.slice(0, 8) }}</span>
                      </div>
                      <div class="font-semibold text-[#38618C] text-sm sm:text-base truncate">
                        {{ tx.crypto_name || 'Crypto' }}
                      </div>
                      <div class="text-xs text-gray-500">
                        by {{ tx.user_name || 'Unknown' }}
                      </div>
                    </div>
                  </div>

                  <div class="grid grid-cols-2 gap-4 sm:gap-6 text-right">
                    <div>
                      <div class="text-xs text-gray-500">Quantity</div>
                      <div class="text-sm sm:text-base font-bold text-[#38618C] font-mono">
                        {{ formatNumber(tx.quantity, 4) }}
                      </div>
                    </div>
                    <div>
                      <div class="text-xs text-gray-500">Amount</div>
                      <div class="text-base sm:text-lg font-bold text-[#35A7FF]">
                        {{ formatCurrency(tx.total_eur) }}
                      </div>
                    </div>
                  </div>

                  <div class="text-xs text-gray-500 text-center sm:text-right col-span-full sm:col-span-auto">
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
    <div class="space-y-6">
      <!-- Top 5 Most Traded Cryptos -->
      <Card class="shadow-md hover:shadow-lg transition-shadow h-fit">
        <CardHeader class="pb-4 border-b border-gray-200">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2">
            <CardTitle class="text-base sm:text-lg font-semibold text-[#38618C]">
              🏆 Top 5 Most Traded
            </CardTitle>
            <Button 
              @click="$emit('goToCryptos')"
              variant="outline"
              size="sm"
              class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white text-xs"
            >
              All →
            </Button>
          </div>
        </CardHeader>

        <CardContent class="pt-6 flex flex-col gap-3">
          <!-- No Data -->
          <div v-if="!stats?.top_cryptos?.length" class="text-center py-8">
            <div class="text-3xl mb-3">💎</div>
            <p class="text-gray-500 text-sm">No trading data</p>
          </div>

          <!-- Top Traded Cryptos -->
          <div v-else class="flex flex-col gap-3">
            <Card 
              v-for="(crypto, index) in stats.top_cryptos.slice(0, 5)" 
              :key="crypto.id"
              class="border-gray-200 hover:border-[#35A7FF] transition-all hover:shadow-md bg-gradient-to-br from-gray-50 to-transparent"
            >
              <CardContent class="flex items-center gap-2 sm:gap-3 p-3">
                <!-- Rank Badge -->
                <div class="flex-shrink-0">
                  <div class="w-7 h-7 sm:w-8 sm:h-8 rounded-full flex items-center justify-center text-white font-bold text-sm"
                       :class="getRankColor(index)">
                    {{ index + 1 }}
                  </div>
                </div>

                <!-- Crypto Image -->
                <div class="flex-shrink-0">
                  <div class="h-9 w-9 sm:h-11 sm:w-11 rounded-full border-2 border-gray-300 bg-gray-100 flex items-center justify-center">
                    <img 
                      v-if="crypto.image"
                      :src="crypto.image"
                      :alt="crypto.name"
                      class="w-9 h-9 sm:w-11 sm:h-11 rounded-full object-cover"
                      @error="(e) => e.target.style.display = 'none'"
                    />
                    <div v-else class="text-lg">💎</div>
                  </div>
                </div>

                <!-- Crypto Info -->
                <div class="flex-1 min-w-0">
                  <div class="font-semibold text-[#38618C] text-xs sm:text-sm truncate">
                    {{ crypto.name }}
                  </div>
                  <Badge class="bg-[#35A7FF] text-white text-xs font-mono mt-1">
                    {{ (crypto.symbol || 'N/A').toUpperCase() }}
                  </Badge>
                </div>

                <!-- Trading Stats -->
                <div class="text-right flex-shrink-0">
                  <div class="text-xs text-gray-500 mb-1">Volume</div>
                  <div class="text-xs sm:text-sm font-bold text-[#01FF19]">
                    {{ formatCurrency(crypto.total_volume) }}
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Summary Footer -->
            <div class="mt-2 pt-3 border-t border-gray-200 grid grid-cols-2 gap-3 text-center text-xs">
              <div>
                <div class="text-gray-500 mb-1">Total Volume</div>
                <div class="font-bold text-[#38618C]">
                  {{ formatCurrency(stats.top_cryptos.reduce((sum: number, c: any) => sum + Number(c.total_volume), 0)) }}
                </div>
              </div>
              <div>
                <div class="text-gray-500 mb-1">Cryptos</div>
                <div class="font-bold text-[#35A7FF]">
                  {{ stats.top_cryptos.length }}
                </div>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </div>
</template>
