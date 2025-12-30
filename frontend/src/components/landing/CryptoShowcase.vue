<script setup lang="ts">
import { onMounted, ref } from 'vue'
import { TrendingUp, TrendingDown, BarChart3 } from 'lucide-vue-next'

interface Crypto {
  id: string
  name: string
  symbol: string
  price: number
  change: number
  color: string
  volume: string
  marketCap: string
}

const cryptos = ref<Crypto[]>([
  {
    id: 'bitcoin',
    name: 'Bitcoin',
    symbol: 'BTC',
    price: 63842.15,
    change: 2.4,
    color: '#F7931A',
    volume: '$32.4B',
    marketCap: '$1.25T'
  },
  {
    id: 'ethereum',
    name: 'Ethereum',
    symbol: 'ETH',
    price: 3456.78,
    change: 1.2,
    color: '#627EEA',
    volume: '$14.2B',
    marketCap: '$415B'
  },
  {
    id: 'solana',
    name: 'Solana',
    symbol: 'SOL',
    price: 142.35,
    change: 5.8,
    color: '#14F195',
    volume: '$3.8B',
    marketCap: '$62B'
  },
  {
    id: 'cardano',
    name: 'Cardano',
    symbol: 'ADA',
    price: 0.52,
    change: -0.3,
    color: '#0033AD',
    volume: '$420M',
    marketCap: '$18.5B'
  },
  {
    id: 'polkadot',
    name: 'Polkadot',
    symbol: 'DOT',
    price: 8.45,
    change: 1.8,
    color: '#E6007A',
    volume: '$380M',
    marketCap: '$10.8B'
  },
  {
    id: 'avalanche',
    name: 'Avalanche',
    symbol: 'AVAX',
    price: 36.92,
    change: 4.2,
    color: '#E84142',
    volume: '$620M',
    marketCap: '$13.9B'
  }
])

const stats = ref([
  { label: 'Market Cap', value: '$1.87T', change: '+2.4%' },
  { label: '24h Volume', value: '$68.4B', change: '+12.8%' },
  { label: 'BTC Dominance', value: '52.8%', change: '-0.2%' },
  { label: 'Fear & Greed', value: '72', subtext: 'Greed' }
])

const timeframes = ['1D', '7D', '1M', '3M', '1Y']
const selectedTimeframe = ref('1D')
</script>

<template>
  <section class="py-16 md:py-24 bg-gradient-to-b from-gray-900 to-black">
    <div class="container mx-auto px-4 md:px-6">
      
      <!-- Header -->
      <div class="text-center mb-12">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 mb-6">
          <BarChart3 class="w-4 h-4 text-blue-400" />
          <span class="text-sm font-medium text-white">CRYPTO TRACKER</span>
        </div>
        
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Live Crypto
          <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
            Markets
          </span>
        </h2>
        
        <p class="text-gray-400 max-w-2xl mx-auto">
          Real-time tracking of top cryptocurrencies with essential metrics
        </p>
      </div>

      <!-- Market Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
        <div
          v-for="(stat, index) in stats"
          :key="stat.label"
          class="p-6 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors"
          :style="{ animationDelay: `${index * 100}ms` }"
        >
          <div class="text-sm text-gray-400 mb-2">{{ stat.label }}</div>
          <div class="text-2xl font-bold text-white mb-1">{{ stat.value }}</div>
          <div 
            v-if="stat.change" 
            :class="stat.change.startsWith('+') ? 'text-emerald-400' : 'text-red-400'"
            class="text-sm font-medium"
          >
            {{ stat.change }}
          </div>
          <div v-if="stat.subtext" class="text-sm text-emerald-400 font-medium">
            {{ stat.subtext }}
          </div>
        </div>
      </div>

      <!-- Timeframe Filter -->
      <div class="flex justify-center mb-8">
        <div class="inline-flex gap-1 p-1 rounded-xl bg-white/5 backdrop-blur-sm border border-white/10">
          <button
            v-for="tf in timeframes"
            :key="tf"
            @click="selectedTimeframe = tf"
            :class="[
              'px-4 py-2 rounded-lg text-sm font-medium transition-all',
              selectedTimeframe === tf
                ? 'bg-white/10 text-white border border-white/20'
                : 'text-gray-400 hover:text-white'
            ]"
          >
            {{ tf }}
          </button>
        </div>
      </div>

      <!-- Crypto Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        <div
          v-for="crypto in cryptos"
          :key="crypto.id"
          class="group p-6 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 hover:bg-white/10 transition-all hover:scale-[1.02]"
        >
          <!-- Crypto Header -->
          <div class="flex items-start justify-between mb-6">
            <div class="flex items-center gap-4">
              <div 
                class="w-12 h-12 rounded-xl flex items-center justify-center text-white font-bold text-lg"
                :style="{
                  backgroundColor: `${crypto.color}20`,
                  border: `2px solid ${crypto.color}`
                }"
              >
                {{ crypto.symbol.charAt(0) }}
              </div>
              <div>
                <h3 class="text-lg font-bold text-white">{{ crypto.name }}</h3>
                <p class="text-gray-400">{{ crypto.symbol }}</p>
              </div>
            </div>
            
            <!-- Price Change -->
            <div
              class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-sm font-medium"
              :class="crypto.change >= 0 
                ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                : 'bg-red-500/10 text-red-400 border border-red-500/20'
              "
            >
              <component
                :is="crypto.change >= 0 ? TrendingUp : TrendingDown"
                class="w-4 h-4"
              />
              {{ crypto.change >= 0 ? '+' : '' }}{{ crypto.change }}%
            </div>
          </div>

          <!-- Price Display -->
          <div class="mb-6">
            <div class="text-3xl font-bold text-white">
              ${{ crypto.price.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
            </div>
            <div class="text-gray-400 text-sm">Current Price</div>
          </div>

          <!-- Mini Chart -->
          <div class="relative h-20 mb-6">
            <!-- Simplified chart bars -->
            <div class="absolute inset-0 flex items-end gap-1">
              <div
                v-for="i in 15"
                :key="i"
                class="flex-1 rounded-t"
                :style="{
                  height: `${30 + Math.sin(i * 0.5 + (crypto.id.charCodeAt(0) * 0.1)) * 40}%`,
                  backgroundColor: crypto.change >= 0 
                    ? `rgba(16, 185, 129, ${0.2 + i * 0.05})` 
                    : `rgba(239, 68, 68, ${0.2 + i * 0.05})`
                }"
              ></div>
            </div>
            <!-- Chart gradient overlay -->
            <div
              class="absolute inset-0 rounded-lg opacity-10"
              :style="{
                background: crypto.change >= 0
                  ? 'linear-gradient(90deg, #10b981, transparent)'
                  : 'linear-gradient(90deg, #ef4444, transparent)'
              }"
            ></div>
          </div>

          <!-- Additional Metrics -->
          <div class="flex justify-between text-sm">
            <div>
              <div class="text-gray-400">24h Volume</div>
              <div class="font-semibold text-white">{{ crypto.volume }}</div>
            </div>
            <div>
              <div class="text-gray-400">Market Cap</div>
              <div class="font-semibold text-white">{{ crypto.marketCap }}</div>
            </div>
          </div>

          <!-- Action Button -->
          <button
            class="w-full mt-6 py-3 rounded-lg text-white font-medium transition-all"
            :style="{
              backgroundColor: `${crypto.color}20`,
              border: `1px solid ${crypto.color}`
            }"
          >
            Trade {{ crypto.symbol }}
          </button>
        </div>
      </div>

      <!-- Market Summary -->
      <div class="mt-12 grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Market Sentiment -->
        <div class="p-6 rounded-xl bg-white/5 border border-white/10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Market Sentiment</h3>
            <div class="text-2xl font-bold text-emerald-400">Bullish</div>
          </div>
          <div class="h-2 bg-gray-800 rounded-full overflow-hidden mb-3">
            <div class="h-full bg-gradient-to-r from-emerald-500 via-yellow-500 to-red-500" style="width: 72%"></div>
          </div>
          <div class="flex justify-between text-sm text-gray-400">
            <span>Bearish</span>
            <span>72% Bullish</span>
            <span>Bullish</span>
          </div>
        </div>

        <!-- Volatility -->
        <div class="p-6 rounded-xl bg-white/5 border border-white/10">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-bold text-white">Volatility Index</h3>
            <div class="text-2xl font-bold text-yellow-400">Medium</div>
          </div>
          <div class="space-y-3">
            <div
              v-for="c in cryptos.slice(0, 3)"
              :key="c.id"
              class="flex items-center justify-between"
            >
              <span class="text-gray-300">{{ c.symbol }}</span>
              <div class="flex items-center gap-3">
                <div class="w-24 h-1.5 bg-gray-800 rounded-full overflow-hidden">
                  <div
                    class="h-full rounded-full"
                    :style="{
                      width: `${Math.min(100, Math.abs(c.change) * 10)}%`,
                      backgroundColor: c.color
                    }"
                  ></div>
                </div>
                <span class="text-sm text-gray-400">{{ Math.abs(c.change).toFixed(1) }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="text-center mt-12">
        <button
          class="inline-flex items-center gap-3 px-8 py-4 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all"
        >
          View All Markets
          <TrendingUp class="w-5 h-5" />
        </button>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Animations minimales */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.grid > div {
  animation: fadeIn 0.3s ease-out forwards;
  animation-fill-mode: both;
}

/* Délais d'animation pour effet de stagger */
.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }
.grid > div:nth-child(5) { animation-delay: 0.5s; }
.grid > div:nth-child(6) { animation-delay: 0.6s; }

/* Responsive */
@media (max-width: 768px) {
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>