<script setup lang="ts">
import { Shield, Zap, BarChart3, Lock, Globe, Cpu } from 'lucide-vue-next'

const features = [
  {
    icon: Shield,
    title: 'Bank-Grade Security',
    description: 'Military-grade encryption and multi-signature wallets',
    color: 'from-blue-500 to-cyan-500'
  },
  {
    icon: Zap,
    title: 'Lightning Fast',
    description: 'Execute trades in milliseconds with low latency',
    color: 'from-emerald-500 to-green-500'
  },
  {
    icon: BarChart3,
    title: 'Smart Analytics',
    description: 'AI-powered insights and real-time market data',
    color: 'from-purple-500 to-pink-500'
  },
  {
    icon: Lock,
    title: 'Secure Storage',
    description: 'Cold storage with insurance protection',
    color: 'from-orange-500 to-red-500'
  },
  {
    icon: Globe,
    title: 'Global Access',
    description: 'Trade 150+ cryptocurrencies worldwide',
    color: 'from-blue-500 to-indigo-500'
  },
  {
    icon: Cpu,
    title: 'Advanced Tools',
    description: 'Professional trading tools and APIs',
    color: 'from-cyan-500 to-blue-500'
  }
]

const stats = [
  { value: '99.9%', label: 'Uptime', suffix: '' },
  { value: '0.1s', label: 'Speed', suffix: '' },
  { value: '500K+', label: 'Users', suffix: '' },
  { value: '$10B+', label: 'Volume', suffix: '' }
]
</script>

<template>
  <section class="py-20 md:py-28 bg-gradient-to-b from-black to-gray-900">
    <div class="container mx-auto px-4 md:px-6">
      
      <!-- Header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 mb-6">
          <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
          <span class="text-sm font-medium text-white">WHY CHOOSE US</span>
        </div>
        
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Platform
          <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
            Features
          </span>
        </h2>
        
        <p class="text-gray-400 max-w-2xl mx-auto">
          Built for professional traders with cutting-edge technology
        </p>
      </div>

      <!-- Stats -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-16">
        <div
          v-for="(stat, index) in stats"
          :key="stat.label"
          class="p-6 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 transition-colors"
          :style="{ animationDelay: `${index * 100}ms` }"
        >
          <div class="text-3xl font-bold text-white mb-2">
            {{ stat.value }}<span class="text-lg">{{ stat.suffix }}</span>
          </div>
          <div class="text-gray-400 text-sm">{{ stat.label }}</div>
        </div>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <div
          v-for="(feature, index) in features"
          :key="feature.title"
          class="group p-6 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 hover:bg-white/10 transition-all hover:scale-[1.02]"
          :style="{ animationDelay: `${index * 50}ms` }"
        >
          <!-- Icon -->
          <div class="mb-6">
            <div class="relative inline-block">
              <div
                class="absolute -inset-2 rounded-lg opacity-20 blur"
                :class="`bg-gradient-to-r ${feature.color}`"
              ></div>
              <div
                class="relative w-14 h-14 rounded-xl flex items-center justify-center backdrop-blur-sm"
                :class="`bg-gradient-to-r ${feature.color}`"
              >
                <component
                  :is="feature.icon"
                  class="w-7 h-7 text-white"
                />
              </div>
            </div>
          </div>

          <!-- Content -->
          <h3 class="text-xl font-bold text-white mb-3">
            {{ feature.title }}
          </h3>
          
          <p class="text-gray-400 mb-6">
            {{ feature.description }}
          </p>

          <!-- Hover Indicator -->
          <div class="flex items-center text-sm text-blue-400 opacity-0 group-hover:opacity-100 transition-opacity">
            <span>Learn more</span>
            <div class="w-0 group-hover:w-16 h-px bg-gradient-to-r from-blue-400 to-transparent ml-2 transition-all"></div>
          </div>
        </div>
      </div>

      <!-- Demo Visual -->
      <div class="mt-20 relative">
        <!-- Background Elements -->
        <div class="absolute inset-0">
          <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
          <div class="absolute top-1/2 right-1/4 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl"></div>
        </div>

        <!-- Demo Card -->
        <div class="relative backdrop-blur-sm bg-white/5 border border-white/10 rounded-2xl overflow-hidden">
          <!-- Header -->
          <div class="p-6 border-b border-white/10">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-4">
                <div class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></div>
                <div class="text-white font-semibold">Live Trading Dashboard</div>
              </div>
              <div class="text-sm text-gray-400">Demo Preview</div>
            </div>
          </div>

          <!-- Content -->
          <div class="p-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
              <!-- Trading Chart -->
              <div class="lg:col-span-2">
                <div class="h-64 relative rounded-lg overflow-hidden bg-black/50">
                  <!-- Simplified chart -->
                  <div class="absolute inset-0">
                    <svg class="w-full h-full" viewBox="0 0 400 200">
                      <path
                        d="M0,150 Q50,120 100,140 T200,100 T300,80 T400,60"
                        fill="none"
                        stroke="url(#chart-gradient)"
                        stroke-width="2"
                      />
                      <defs>
                        <linearGradient id="chart-gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                          <stop offset="0%" stop-color="#3b82f6" />
                          <stop offset="100%" stop-color="#10b981" />
                        </linearGradient>
                      </defs>
                    </svg>
                  </div>
                  
                  <!-- Data points -->
                  <div class="absolute bottom-4 left-4 right-4 flex justify-between text-sm">
                    <div class="text-gray-400">BTC/USDT</div>
                    <div class="text-white font-bold">$63,842</div>
                    <div class="text-emerald-400">+2.4%</div>
                  </div>
                </div>
              </div>

              <!-- Order Book -->
              <div class="space-y-4">
                <div class="text-white font-semibold mb-4">Order Book</div>
                
                <!-- Buy Orders -->
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <div class="text-emerald-400">Bid</div>
                    <div class="text-gray-400">Size</div>
                  </div>
                  <div
                    v-for="i in 3"
                    :key="'buy-' + i"
                    class="flex justify-between text-sm"
                  >
                    <div class="text-emerald-300">$63,8{{ 4 - i }}.{{ 10 + i * 5 }}</div>
                    <div class="text-gray-300">0.{{ 5 + i }}2</div>
                  </div>
                </div>

                <!-- Current Price -->
                <div class="py-4 border-y border-white/10 text-center">
                  <div class="text-2xl font-bold text-white mb-1">$63,842.15</div>
                  <div class="text-sm text-emerald-400">Current Price</div>
                </div>

                <!-- Sell Orders -->
                <div class="space-y-2">
                  <div class="flex justify-between text-sm">
                    <div class="text-red-400">Ask</div>
                    <div class="text-gray-400">Size</div>
                  </div>
                  <div
                    v-for="i in 3"
                    :key="'sell-' + i"
                    class="flex justify-between text-sm"
                  >
                    <div class="text-red-300">$63,8{{ 4 + i }}.{{ 20 + i * 5 }}</div>
                    <div class="text-gray-300">0.{{ 3 + i }}8</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Footer -->
          <div class="p-6 border-t border-white/10">
            <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
              <div class="text-sm text-gray-400">
                Experience the full platform with real trading tools
              </div>
              <button class="px-6 py-3 rounded-lg bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all">
                Start Free Trial
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- CTA -->
      <div class="text-center mt-16">
        <div class="inline-flex flex-col sm:flex-row items-center gap-6 p-8 rounded-2xl bg-gradient-to-r from-white/5 to-white/10 backdrop-blur-sm border border-white/10">
          <div class="text-left">
            <h3 class="text-2xl font-bold text-white mb-2">Ready to start trading?</h3>
            <p class="text-gray-400">Join thousands of successful traders today</p>
          </div>
          <div class="flex gap-4">
            <button class="px-8 py-3 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all">
              Get Started
            </button>
            <button class="px-8 py-3 rounded-xl bg-white/5 text-white font-semibold hover:bg-white/10 transition-all border border-white/10">
              Contact Sales
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

.grid > div,
.stats > div {
  animation: fadeIn 0.5s ease-out forwards;
  opacity: 0;
}

/* Stagger animation delays */
.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }
.grid > div:nth-child(5) { animation-delay: 0.5s; }
.grid > div:nth-child(6) { animation-delay: 0.6s; }

.stats > div:nth-child(1) { animation-delay: 0.1s; }
.stats > div:nth-child(2) { animation-delay: 0.2s; }
.stats > div:nth-child(3) { animation-delay: 0.3s; }
.stats > div:nth-child(4) { animation-delay: 0.4s; }

/* Responsive */
@media (max-width: 768px) {
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>