<script setup lang="ts">
import { Banknote, BarChart3, CreditCard, Globe, Shield, TrendingUp, Zap } from 'lucide-vue-next'
import { ref } from 'vue'

const features = [
  {
    icon: CreditCard,
    title: 'Instant Card Purchases',
    description: 'Buy crypto instantly with Visa/Mastercard',
    color: 'from-blue-500 to-cyan-500'
  },
  {
    icon: Banknote,
    title: 'Bank Transfers',
    description: 'Direct deposits with minimal fees',
    color: 'from-emerald-500 to-green-500'
  },
  {
    icon: BarChart3,
    title: 'Portfolio Management',
    description: 'Advanced analytics & tracking tools',
    color: 'from-purple-500 to-pink-500'
  },
  {
    icon: TrendingUp,
    title: 'Spot Trading',
    description: 'Trade 100+ crypto pairs instantly',
    color: 'from-orange-500 to-red-500'
  },
  {
    icon: Shield,
    title: 'Secure Storage',
    description: 'Institutional-grade security',
    color: 'from-blue-500 to-indigo-500'
  },
  {
    icon: Globe,
    title: 'Global Access',
    description: 'Available in 150+ countries',
    color: 'from-cyan-500 to-blue-500'
  }
]

const timeframe = ref<'1H' | '24H' | '7D' | '1M'>('24H')
const timeframes = [
  { label: '1H', value: '1H' },
  { label: '24H', value: '24H' },
  { label: '7D', value: '7D' },
  { label: '1M', value: '1M' }
]

const chartData = {
  '1H': { 
    points: [63.2, 62.8, 63.5, 62.9, 63.8, 64.2, 63.5, 63.9, 64.5, 65.1, 64.8, 64.3],
    delta: '+0.8%',
    volume: '$1.2B'
  },
  '24H': { 
    points: [61.5, 62.1, 63.8, 62.9, 63.2, 63.9, 63.5, 64.2, 64.8, 65.3, 64.9, 63.8],
    delta: '+2.4%',
    volume: '$32.4B'
  },
  '7D': { 
    points: [58.2, 59.1, 60.3, 61.2, 62.4, 63.1, 63.8, 64.2, 63.9, 64.5, 64.1, 63.8],
    delta: '+5.8%',
    volume: '$185B'
  },
  '1M': { 
    points: [52.1, 54.3, 56.8, 58.2, 59.4, 60.8, 62.1, 63.4, 64.2, 64.8, 64.3, 63.8],
    delta: '+12.2%',
    volume: '$780B'
  }
}

const portfolioStats = [
  { symbol: 'BTC', name: 'Bitcoin', price: 63842, change: 2.4, holding: 0.1785, value: 11392 },
  { symbol: 'ETH', name: 'Ethereum', price: 3456, change: 1.2, holding: 3.25, value: 11232 },
  { symbol: 'SOL', name: 'Solana', price: 142, change: 5.8, holding: 85.2, value: 12101 }
]

const marketStats = [
  { label: '24h Volume', value: '$68.4B', change: '+12.8%' },
  { label: 'Market Cap', value: '$1.87T', change: '+2.4%' },
  { label: 'BTC Dominance', value: '52.8%', change: '-0.2%' },
  { label: 'Fear & Greed', value: '72', subtext: 'Greed' }
]
</script>

<template>
  <section class="py-20 md:py-28 bg-gradient-to-b from-black to-gray-900">
    <div class="container mx-auto px-4 md:px-6">
      
      <!-- Header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 mb-6">
          <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></div>
          <span class="text-sm font-medium text-white">PROFESSIONAL TRADING</span>
        </div>
        
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Complete Crypto
          <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
            Platform
          </span>
        </h2>
        
        <p class="text-gray-400 max-w-2xl mx-auto">
          Everything you need to trade, invest, and manage cryptocurrencies in one secure platform
        </p>
      </div>

      <!-- Market Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-12">
        <div
          v-for="stat in marketStats"
          :key="stat.label"
          class="p-5 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors"
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

      <div class="grid lg:grid-cols-2 gap-12">
        
        <!-- Left: Features -->
        <div class="space-y-8">
          <!-- Features Grid -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div
              v-for="feature in features"
              :key="feature.title"
              class="group p-5 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 hover:bg-white/10 transition-all hover:scale-[1.02]"
            >
              <!-- Icon -->
              <div class="mb-4">
                <div class="relative inline-block">
                  <div
                    class="absolute -inset-2 rounded-lg opacity-20 blur"
                    :class="`bg-gradient-to-r ${feature.color}`"
                  ></div>
                  <div
                    class="relative w-12 h-12 rounded-lg flex items-center justify-center backdrop-blur-sm"
                    :class="`bg-gradient-to-br ${feature.color}`"
                  >
                    <component
                      :is="feature.icon"
                      class="w-6 h-6 text-white"
                    />
                  </div>
                </div>
              </div>

              <!-- Content -->
              <h3 class="text-lg font-bold text-white mb-2">
                {{ feature.title }}
              </h3>
              
              <p class="text-gray-400 text-sm">
                {{ feature.description }}
              </p>

              <!-- Hover line -->
              <div class="w-0 group-hover:w-full h-px mt-3 bg-gradient-to-r from-transparent via-current to-transparent transition-all duration-300"></div>
            </div>
          </div>

          <!-- Portfolio Summary -->
          <div class="p-6 rounded-xl bg-white/5 border border-white/10">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-xl font-bold text-white">Your Portfolio</h3>
              <div class="text-emerald-400 text-sm font-medium">+4.8% Today</div>
            </div>

            <!-- Holdings -->
            <div class="space-y-4">
              <div
                v-for="crypto in portfolioStats"
                :key="crypto.symbol"
                class="flex items-center justify-between"
              >
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-white/5 flex items-center justify-center">
                    <div class="text-lg font-bold text-white">{{ crypto.symbol.charAt(0) }}</div>
                  </div>
                  <div>
                    <div class="text-white font-semibold">{{ crypto.name }}</div>
                    <div class="text-gray-400 text-sm">{{ crypto.holding }} {{ crypto.symbol }}</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-white font-semibold">${{ crypto.value.toLocaleString() }}</div>
                  <div 
                    :class="crypto.change >= 0 ? 'text-emerald-400' : 'text-red-400'"
                    class="text-sm font-medium"
                  >
                    {{ crypto.change >= 0 ? '+' : '' }}{{ crypto.change }}%
                  </div>
                </div>
              </div>
            </div>

            <!-- Total Value -->
            <div class="mt-6 pt-6 border-t border-white/10">
              <div class="flex justify-between items-center">
                <div>
                  <div class="text-gray-400 text-sm">Total Value</div>
                  <div class="text-2xl font-bold text-white">$34,725</div>
                </div>
                <button class="px-6 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all">
                  Add Funds
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Right: Live Dashboard -->
        <div>
          <!-- Dashboard Card -->
          <div class="rounded-2xl bg-white/5 border border-white/10 overflow-hidden">
            <!-- Header -->
            <div class="p-6 border-b border-white/10">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 rounded-lg bg-gradient-to-r from-blue-500 to-emerald-500 flex items-center justify-center">
                    <Zap class="w-5 h-5 text-white" />
                  </div>
                  <div>
                    <h3 class="text-xl font-bold text-white">Live Trading</h3>
                    <div class="text-gray-400 text-sm">Real-time market data</div>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                  <span class="text-sm text-emerald-400">LIVE</span>
                </div>
              </div>
            </div>

            <!-- Chart -->
            <div class="p-6">
              <!-- Chart Header -->
              <div class="flex items-center justify-between mb-6">
                <div>
                  <div class="text-2xl font-bold text-white">$63,842.15</div>
                  <div class="text-emerald-400 font-medium">BTC/USDT</div>
                </div>
                <div class="text-right">
                  <div class="text-lg font-bold text-emerald-400">+2.4%</div>
                  <div class="text-gray-400 text-sm">24h change</div>
                </div>
              </div>

              <!-- Timeframe Selector -->
              <div class="flex gap-2 mb-6">
                <button
                  v-for="tf in timeframes"
                  :key="tf.value"
                  class="px-4 py-2 rounded-lg text-sm font-medium transition-all"
                  :class="timeframe === tf.value
                    ? 'bg-white/10 text-white border border-white/20'
                    : 'text-gray-400 hover:text-white hover:bg-white/5'"
                  @click="timeframe = tf.value"
                >
                  {{ tf.label }}
                </button>
              </div>

              <!-- Chart Visualization -->
              <div class="relative h-48 mb-6">
                <!-- Chart lines -->
                <div class="absolute inset-0">
                  <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <!-- Area fill -->
                    <defs>
                      <linearGradient id="area-fill" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.2" />
                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0" />
                      </linearGradient>
                    </defs>
                    
                    <!-- Chart area -->
                    <path
                      :d="`M 0,100 ${chartData[timeframe].points.map((p, i) => `L ${i * (100 / (chartData[timeframe].points.length - 1))},${100 - p}`).join(' ')} L 100,100 Z`"
                      fill="url(#area-fill)"
                    />
                    
                    <!-- Chart line -->
                    <path
                      :d="`M 0,${100 - chartData[timeframe].points[0]} ${chartData[timeframe].points.slice(1).map((p, i) => `L ${(i + 1) * (100 / (chartData[timeframe].points.length - 1))},${100 - p}`).join(' ')}`"
                      fill="none"
                      stroke="#3b82f6"
                      stroke-width="2"
                      stroke-linecap="round"
                      stroke-linejoin="round"
                    />
                  </svg>
                </div>

                <!-- Volume indicator -->
                <div class="absolute bottom-0 left-0 right-0 h-8 opacity-10">
                  <div class="w-full h-full bg-gradient-to-r from-blue-500 to-emerald-500"></div>
                </div>

                <!-- Current price indicator -->
                <div class="absolute top-2 right-2 bg-emerald-500/20 text-emerald-400 px-3 py-1 rounded-full text-sm font-semibold backdrop-blur-sm border border-emerald-500/30">
                  {{ chartData[timeframe].delta }}
                </div>
              </div>

              <!-- Volume & Time -->
              <div class="flex justify-between text-sm text-gray-400">
                <span>9:00 AM</span>
                <span>Volume: {{ chartData[timeframe].volume }}</span>
                <span>9:00 PM</span>
              </div>
            </div>

            <!-- Order Book Preview -->
            <div class="p-6 border-t border-white/10">
              <div class="grid grid-cols-2 gap-6">
                <!-- Buy Orders -->
                <div>
                  <div class="text-emerald-400 font-semibold mb-3">Buy Orders</div>
                  <div class="space-y-2">
                    <div
                      v-for="i in 3"
                      :key="'buy-' + i"
                      class="flex justify-between items-center"
                    >
                      <div class="text-emerald-300">$63,8{{ 4 - i }}.{{ 10 + i * 5 }}</div>
                      <div class="text-gray-300">0.{{ 5 + i }}2 BTC</div>
                    </div>
                  </div>
                </div>

                <!-- Sell Orders -->
                <div>
                  <div class="text-red-400 font-semibold mb-3">Sell Orders</div>
                  <div class="space-y-2">
                    <div
                      v-for="i in 3"
                      :key="'sell-' + i"
                      class="flex justify-between items-center"
                    >
                      <div class="text-red-300">$63,8{{ 4 + i }}.{{ 20 + i * 5 }}</div>
                      <div class="text-gray-300">0.{{ 3 + i }}8 BTC</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Actions -->
            <div class="p-6 border-t border-white/10">
              <div class="flex gap-4">
                <button class="flex-1 py-3 rounded-lg bg-emerald-500/10 text-emerald-400 font-semibold hover:bg-emerald-500/20 transition-all border border-emerald-500/20">
                  Buy BTC
                </button>
                <button class="flex-1 py-3 rounded-lg bg-red-500/10 text-red-400 font-semibold hover:bg-red-500/20 transition-all border border-red-500/20">
                  Sell BTC
                </button>
                <button class="flex-1 py-3 rounded-lg bg-white/5 text-white font-semibold hover:bg-white/10 transition-all border border-white/10">
                  Set Limit
                </button>
              </div>
            </div>
          </div>

          <!-- Platform Features -->
          <div class="grid grid-cols-2 gap-4 mt-6">
            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-center">
              <div class="text-2xl font-bold text-white mb-1">0.1s</div>
              <div class="text-gray-400 text-sm">Execution Speed</div>
            </div>
            <div class="p-4 rounded-xl bg-white/5 border border-white/10 text-center">
              <div class="text-2xl font-bold text-white mb-1">99.9%</div>
              <div class="text-gray-400 text-sm">Platform Uptime</div>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="text-center mt-16">
        <div class="inline-flex flex-col sm:flex-row items-center gap-8 p-8 rounded-2xl bg-gradient-to-r from-white/5 to-white/10 backdrop-blur-sm border border-white/10">
          <div class="text-left">
            <h3 class="text-2xl font-bold text-white mb-2">Ready to trade like a pro?</h3>
            <p class="text-gray-400">Join 500K+ traders worldwide</p>
          </div>
          <div class="flex gap-4">
            <button class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all">
              Get Started Free
            </button>
            <button class="px-8 py-3 rounded-xl bg-white/5 text-white font-semibold hover:bg-white/10 transition-all border border-white/10">
              Schedule Demo
            </button>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Simple fade-in animation */
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.grid > div {
  animation: fadeIn 0.5s ease-out forwards;
  animation-fill-mode: both;
}

/* Stagger animation delays */
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