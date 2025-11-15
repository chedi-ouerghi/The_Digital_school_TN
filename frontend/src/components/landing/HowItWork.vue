<script setup lang="ts">
import { ref } from 'vue'

const features = [
  {
    title: 'Buy Crypto with Credit Card',
    text: 'Instant purchases with Visa or Mastercard. Simple, fast, and secure.',
    color: '#35A7FF',
    icon: '💳',
  },
  {
    title: 'Bank Transfer & Deposits',
    text: 'Direct bank deposits with low fees and fast processing times.',
    color: '#01FF19',
    icon: '🏦',
  },
  {
    title: 'Advanced Portfolio Tools',
    text: 'Diversify and manage your assets with professional-grade analytics.',
    color: '#FF5964',
    icon: '📈',
  },
  {
    title: 'Spot Market Trading',
    text: 'Access deep liquidity and trade hundreds of cryptocurrency pairs.',
    color: '#38618C',
    icon: '🪙',
  },
]

// Données du dashboard
const timeframe = ref<'1H' | '24H' | '7D' | '1M'>('24H')
const chartData = {
  '1H': { points: '0,36 8,34 16,35 24,33 32,31 40,29 48,28 56,27 64,25 72,24 80,23 88,22 96,21 100,20', delta: '+0.6%' },
  '24H': { points: '0,35 8,28 16,30 24,22 32,18 40,20 48,15 56,18 64,12 72,10 80,8 88,6 96,4 100,3', delta: '+2.4%' },
  '7D': { points: '0,37 8,34 16,31 24,28 32,26 40,24 48,20 56,18 64,22 72,19 80,16 88,14 96,12 100,11', delta: '+5.8%' },
  '1M': { points: '0,38 8,35 16,33 24,30 32,27 40,25 48,22 56,20 64,18 72,16 80,14 88,12 96,10 100,9', delta: '+12.2%' }
}

const portfolioStats = [
  { symbol: 'BTC', price: '48,927', change: '+2.4%' },
  { symbol: 'ETH', price: '3,120', change: '+1.2%' },
  { symbol: 'SOL', price: '142', change: '+5.8%' }
]
</script>

<template>
  <section class="relative py-20 lg:py-28 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 overflow-hidden">
    
    <!-- Background Elements -->
    <div class="absolute inset-0">
      <div class="absolute top-20 left-10 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl"></div>
      <div class="absolute bottom-20 right-10 w-96 h-96 bg-green-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative container mx-auto px-6">
      <div class="grid lg:grid-cols-2 gap-16 lg:gap-20 items-start">
        
        <!-- Left Content -->
        <div class="space-y-10">
          <!-- Header Section -->
          <div class="space-y-6">
            <div class="inline-flex items-center gap-3 px-4 py-2 rounded-full bg-blue-500/10 border border-blue-500/20">
              <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
              <span class="text-sm font-medium text-blue-400">PROFESSIONAL TRADING PLATFORM</span>
            </div>

            <h2 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight">
              Advanced Crypto Trading
              <span class="text-blue-500 block">Made Simple</span>
            </h2>

            <p class="text-lg text-gray-300 leading-relaxed max-w-xl">
              Experience institutional-grade trading tools with an intuitive interface. 
              Buy, sell, and manage your digital assets with confidence on our secure platform.
            </p>
          </div>

          <!-- Features Grid -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="(feature, index) in features"
              :key="index"
              class="group p-5 rounded-xl bg-white/5 backdrop-blur-sm border border-gray-700 hover:border-blue-500/40 transition-all duration-300 hover:transform hover:-translate-y-1"
            >
              <div class="flex items-start gap-4">
                <div 
                  class="w-12 h-12 rounded-lg flex items-center justify-center text-xl transition-transform group-hover:scale-110"
                  :style="{ 
                    backgroundColor: `${feature.color}15`,
                    border: `1px solid ${feature.color}30`
                  }"
                >
                  {{ feature.icon }}
                </div>
                <div class="flex-1">
                  <h3 class="text-white font-semibold mb-2 leading-tight">
                    {{ feature.title }}
                  </h3>
                  <p class="text-gray-400 text-sm leading-relaxed">
                    {{ feature.text }}
                  </p>
                </div>
              </div>
            </div>
          </div>

          <!-- CTA Section -->
          <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button class="px-8 py-4 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition-all duration-200 shadow-lg hover:shadow-blue-500/25">
              Start Trading Now
            </button>
            <button class="px-8 py-4 border border-gray-600 hover:border-gray-500 text-gray-300 hover:text-white font-semibold rounded-lg transition-all duration-200">
              View Live Demo
            </button>
          </div>
        </div>

        <!-- Right: Dashboard Demo -->
        <div class="relative">
          <!-- Main Dashboard Card -->
          <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-2xl shadow-2xl overflow-hidden">
            
            <!-- Dashboard Header -->
            <div class="px-6 py-4 bg-gray-900/80 border-b border-gray-700">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center border border-blue-500/30">
                    <span class="text-blue-400 font-bold text-lg">BC</span>
                  </div>
                  <div>
                    <div class="text-white font-semibold">Trading Account</div>
                    <div class="text-gray-400 text-sm">Live Portfolio</div>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-white font-bold text-lg">0.1785 BTC</div>
                  <div class="text-green-400 text-sm font-medium">+2.4% Today</div>
                </div>
              </div>
            </div>

            <!-- Dashboard Content -->
            <div class="p-6 space-y-6">
              
              <!-- Portfolio Summary -->
              <div class="grid grid-cols-2 gap-4">
                <div class="p-4 rounded-xl bg-gray-700/30 border border-gray-600">
                  <div class="text-gray-400 text-sm mb-1">Available Balance</div>
                  <div class="text-white font-semibold">0.0875 BTC</div>
                </div>
                <div class="p-4 rounded-xl bg-gray-700/30 border border-gray-600">
                  <div class="text-gray-400 text-sm mb-1">In Orders</div>
                  <div class="text-green-400 font-semibold">0.0542 BTC</div>
                </div>
              </div>

              <!-- Chart Section -->
              <div class="bg-gray-900/50 rounded-xl border border-gray-700 p-4">
                <div class="flex items-center justify-between mb-4">
                  <div class="text-white font-semibold">BTC/USD</div>
                  <div class="flex gap-2">
                    <button
                      v-for="tf in ['1H', '24H', '7D', '1M']"
                      :key="tf"
                      @click="timeframe = tf as any"
                      class="px-3 py-1 rounded-full text-sm font-medium transition-all duration-200"
                      :class="timeframe === tf 
                        ? 'bg-blue-600 text-white shadow-lg' 
                        : 'bg-gray-700 text-gray-300 hover:bg-gray-600'"
                    >
                      {{ tf }}
                    </button>
                  </div>
                </div>

                <!-- Chart Container -->
                <div class="relative h-40 bg-gradient-to-b from-gray-800 to-gray-900 rounded-lg border border-gray-700 overflow-hidden">
                  <!-- Chart SVG -->
                  <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full">
                    <defs>
                      <linearGradient id="chartGradient" x1="0%" y1="0%" x2="0%" y2="100%">
                        <stop offset="0%" stop-color="#3B82F6" stop-opacity="0.3" />
                        <stop offset="100%" stop-color="#3B82F6" stop-opacity="0.1" />
                      </linearGradient>
                    </defs>
                    <path 
                      :d="`M ${chartData[timeframe].points}`" 
                      fill="url(#chartGradient)"
                      stroke="#3B82F6"
                      stroke-width="0.8"
                      stroke-linejoin="round"
                    />
                  </svg>
                  
                  <!-- Performance Badge -->
                  <div class="absolute top-3 right-3 bg-green-500 text-white px-3 py-1 rounded-full text-sm font-semibold shadow-lg">
                    {{ chartData[timeframe].delta }}
                  </div>
                </div>

                <!-- Market Stats -->
                <div class="grid grid-cols-3 gap-3 mt-4">
                  <div
                    v-for="crypto in portfolioStats"
                    :key="crypto.symbol"
                    class="p-3 rounded-lg bg-gray-700/30 border border-gray-600 text-center"
                  >
                    <div class="text-white font-semibold text-sm">{{ crypto.symbol }}</div>
                    <div class="text-gray-300 text-xs mt-1">${{ crypto.price }}</div>
                    <div 
                      class="text-xs font-medium mt-1"
                      :class="crypto.change.startsWith('+') ? 'text-green-400' : 'text-red-400'"
                    >
                      {{ crypto.change }}
                    </div>
                  </div>
                </div>
              </div>

              <!-- Platform Stats -->
              <div class="grid grid-cols-3 gap-4 text-center">
                <div class="p-3 rounded-xl bg-gray-700/30 border border-gray-600">
                  <div class="text-gray-400 text-sm">24h Volume</div>
                  <div class="text-white font-semibold text-lg">1.2M BTC</div>
                </div>
                <div class="p-3 rounded-xl bg-gray-700/30 border border-gray-600">
                  <div class="text-gray-400 text-sm">Active Users</div>
                  <div class="text-white font-semibold text-lg">50K+</div>
                </div>
                <div class="p-3 rounded-xl bg-gray-700/30 border border-gray-600">
                  <div class="text-gray-400 text-sm">Uptime</div>
                  <div class="text-green-400 font-semibold text-lg">99.99%</div>
                </div>
              </div>
            </div>
          </div>

          <!-- Floating Elements -->
          <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl"></div>
          <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-green-500/5 rounded-full blur-xl"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Smooth animations */
* {
  transition-property: color, background-color, border-color, transform, box-shadow;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Custom scrollbar for webkit */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: rgba(255, 255, 255, 0.05);
}

::-webkit-scrollbar-thumb {
  background: rgba(255, 255, 255, 0.1);
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: rgba(255, 255, 255, 0.2);
}
</style>