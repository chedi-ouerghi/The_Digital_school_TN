<script setup lang="ts">
import { Check, Sparkles, Zap, BarChart3 } from 'lucide-vue-next'

const features = [
  {
    icon: Sparkles,
    title: 'For Beginners',
    description: 'Simple, intuitive interface for your first crypto trades',
    items: [
      'One-click purchases',
      'Guided tutorials',
      'Portfolio tracking',
      'Educational content'
    ],
    color: 'from-blue-500 to-cyan-500'
  },
  {
    icon: BarChart3,
    title: 'For Traders',
    description: 'Advanced tools for technical analysis and active trading',
    items: [
      'Advanced charts & indicators',
      'Real-time market data',
      'Custom trading bots',
      'API access'
    ],
    color: 'from-emerald-500 to-green-500'
  },
  {
    icon: Zap,
    title: 'For Institutions',
    description: 'Enterprise-grade solutions for large volume trading',
    items: [
      'OTC trading desk',
      'White-glove support',
      'Regulatory compliance',
      'Advanced security'
    ],
    color: 'from-purple-500 to-pink-500'
  }
]
</script>

<template>
  <section class="py-20 md:py-28 bg-gradient-to-b from-gray-900 to-black">
    <div class="container mx-auto px-4 md:px-6">
      
      <!-- Header -->
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 mb-6">
          <div class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></div>
          <span class="text-sm font-medium text-white">FOR EVERYONE</span>
        </div>
        
        <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
          Built for
          <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
            All Traders
          </span>
        </h2>
        
        <p class="text-gray-400 max-w-2xl mx-auto">
          Whether you're just starting out or trading professionally, we have the right tools for you
        </p>
      </div>

      <!-- Features Grid -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div
          v-for="(feature, index) in features"
          :key="feature.title"
          class="group relative p-8 rounded-2xl bg-white/5 border border-white/10 hover:border-white/20 hover:bg-white/10 transition-all hover:scale-[1.02]"
        >
          <!-- Gradient Background -->
          <div class="absolute inset-0 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
            <div class="absolute inset-0 rounded-2xl" :class="`bg-gradient-to-br ${feature.color}`" style="opacity: 0.1;"></div>
          </div>

          <div class="relative z-10">
            <!-- Icon -->
            <div class="mb-6">
              <div class="relative inline-block">
                <div
                  class="absolute -inset-3 rounded-xl opacity-20 blur"
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
            <h3 class="text-2xl font-bold text-white mb-3">
              {{ feature.title }}
            </h3>
            
            <p class="text-gray-400 mb-6">
              {{ feature.description }}
            </p>

            <!-- Features List -->
            <ul class="space-y-3">
              <li
                v-for="item in feature.items"
                :key="item"
                class="flex items-center gap-3 text-gray-300"
              >
                <div class="w-6 h-6 rounded-full flex items-center justify-center" :class="`bg-gradient-to-r ${feature.color}`" style="opacity: 0.2;">
                  <Check class="w-3 h-3 text-white" />
                </div>
                <span class="text-sm">{{ item }}</span>
              </li>
            </ul>

            <!-- CTA -->
            <button
              class="w-full mt-8 py-3 rounded-lg text-white font-medium transition-all border"
              :class="`border-white/20 hover:border-white/40 bg-white/5 hover:bg-white/10`"
            >
              Learn More
            </button>
          </div>
        </div>
      </div>

      <!-- Stats Bar -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-16">
        <div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center">
          <div class="text-3xl font-bold text-white mb-2">500K+</div>
          <div class="text-gray-400 text-sm">Active Traders</div>
        </div>
        <div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center">
          <div class="text-3xl font-bold text-white mb-2">150+</div>
          <div class="text-gray-400 text-sm">Countries</div>
        </div>
        <div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center">
          <div class="text-3xl font-bold text-white mb-2">$10B+</div>
          <div class="text-gray-400 text-sm">Monthly Volume</div>
        </div>
        <div class="p-6 rounded-xl bg-white/5 border border-white/10 text-center">
          <div class="text-3xl font-bold text-white mb-2">24/7</div>
          <div class="text-gray-400 text-sm">Support</div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
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

.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
</style>