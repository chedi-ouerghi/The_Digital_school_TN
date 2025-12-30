<script setup lang="ts">
import { ref } from 'vue';
import { ChevronRight, Sparkles, TrendingUp, Users, Shield, Cpu } from 'lucide-vue-next';

// Données simplifiées
const stats = ref([
  { value: '24/7', label: 'Monitoring', icon: Shield, color: 'text-blue-400' },
  { value: '500K+', label: 'Traders', icon: Users, color: 'text-emerald-400' },
  { value: '0.1s', label: 'Speed', icon: TrendingUp, color: 'text-purple-400' },
  { value: '99.9%', label: 'Uptime', icon: Cpu, color: 'text-orange-400' }
])

const cryptoPrices = ref([
  { symbol: 'BTC', price: '$63,842', change: '+2.4%' },
  { symbol: 'ETH', price: '$3,456', change: '+1.2%' },
  { symbol: 'SOL', price: '$142', change: '+5.8%' }
])
</script>

<template>
  <section class="relative min-h-screen flex items-center bg-black overflow-hidden pt-32 md:pt-20">
    
    <!-- Fond minimaliste -->
    <div class="absolute inset-0">
      <!-- Gradient subtil -->
      <div class="absolute inset-0 bg-gradient-to-br from-gray-900 via-black to-gray-900"></div>
      
      <!-- Grid discret -->
      <div 
        class="absolute inset-0 opacity-5"
        style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 60px 60px;"
      ></div>
      
      <!-- Points lumineux -->
      <div class="absolute top-1/3 left-1/4 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
      <div class="absolute bottom-1/3 right-1/4 w-64 h-64 bg-emerald-500/5 rounded-full blur-3xl"></div>
    </div>

    <!-- Contenu principal -->
    <div class="relative container mx-auto px-6 py-16 lg:py-24">
      <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
        
        <!-- Colonne gauche -->
        <div class="space-y-8">
          
          <!-- Badge simplifié -->
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 backdrop-blur-sm border border-white/10">
            <Sparkles class="w-4 h-4 text-blue-400" />
            <span class="text-sm font-medium text-white">PRO TRADING PLATFORM</span>
          </div>

          <!-- Titre principal -->
          <div class="space-y-4">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-tight">
              <span class="text-white">BIT</span>
              <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">CHEST</span>
            </h1>
            
            <p class="text-xl text-gray-300 leading-relaxed max-w-lg">
              Advanced crypto trading with institutional tools, real-time analytics, and lightning execution.
            </p>
          </div>

          <!-- Prix crypto -->
          <div class="flex flex-wrap gap-4 pt-2">
            <div
              v-for="crypto in cryptoPrices"
              :key="crypto.symbol"
              class="px-4 py-3 rounded-lg bg-white/5 border border-white/10 hover:bg-white/10 transition-colors"
            >
              <div class="flex items-center gap-3">
                <div class="text-sm font-semibold text-white">{{ crypto.symbol }}</div>
                <div class="text-sm font-bold text-white">{{ crypto.price }}</div>
                <div class="text-xs px-2 py-1 rounded bg-emerald-500/20 text-emerald-400">
                  {{ crypto.change }}
                </div>
              </div>
            </div>
          </div>

          <!-- Statistiques -->
          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-4">
            <div
              v-for="stat in stats"
              :key="stat.label"
              class="p-4 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 transition-all"
            >
              <div class="flex items-center gap-3 mb-2">
                <component :is="stat.icon" class="w-5 h-5" :class="stat.color" />
                <div class="text-xs text-gray-400">{{ stat.label }}</div>
              </div>
              <div class="text-2xl font-bold text-white">{{ stat.value }}</div>
            </div>
          </div>

          <!-- Boutons CTA -->
          <div class="flex flex-col sm:flex-row gap-4 pt-8">
            <button 
              class="px-8 py-4 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-3"
            >
              Start Trading
              <ChevronRight class="w-5 h-5" />
            </button>

            <button 
              class="px-8 py-4 rounded-xl bg-white/5 text-white font-semibold hover:bg-white/10 transition-all border border-white/10"
            >
              Learn More
            </button>
          </div>
        </div>

        <!-- Colonne droite - Visuel -->
        <div class="relative">
          
          <!-- Container principal -->
          <div class="relative">
            <!-- Cadre avec effet glass -->
            <div class="relative rounded-2xl bg-gradient-to-br from-white/10 to-white/5 backdrop-blur-xl border border-white/20 overflow-hidden">
              
              <!-- Fond animé -->
              <div class="absolute inset-0">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 via-transparent to-emerald-500/10"></div>
                <!-- Lignes de graphique -->
                <svg class="absolute inset-0 w-full h-full" viewBox="0 0 400 300">
                  <path 
                    d="M0,150 Q100,100 200,180 T400,120" 
                    fill="none" 
                    stroke="url(#gradient)" 
                    stroke-width="2"
                    class="animate-pulse"
                  />
                  <defs>
                    <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="0%">
                      <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.5" />
                      <stop offset="100%" stop-color="#10b981" stop-opacity="0.5" />
                    </linearGradient>
                  </defs>
                </svg>
              </div>

              <!-- Contenu visuel -->
              <div class="relative p-8">
                <div class="space-y-6">
                  <!-- En-tête graphique -->
                  <div class="flex items-center justify-between">
                    <div class="text-lg font-bold text-white">Live Trading</div>
                    <div class="flex items-center gap-2">
                      <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                      <span class="text-sm text-emerald-400">LIVE</span>
                    </div>
                  </div>

                  <!-- Graphique simplifié -->
                  <div class="h-48 flex items-end gap-1">
                    <div 
                      v-for="i in 20" 
                      :key="i"
                      class="flex-1 bg-gradient-to-t from-blue-500/30 to-emerald-500/30 rounded-t"
                      :style="{ height: `${30 + Math.sin(i * 0.5) * 40}%` }"
                    ></div>
                  </div>

                  <!-- Données trading -->
                  <div class="pt-6 border-t border-white/10">
                    <div class="grid grid-cols-2 gap-4">
                      <div>
                        <div class="text-sm text-gray-400">BTC/USDT</div>
                        <div class="text-xl font-bold text-white">$63,842</div>
                      </div>
                      <div class="text-right">
                        <div class="text-sm text-gray-400">24h Change</div>
                        <div class="text-xl font-bold text-emerald-400">+2.4%</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Points de features -->
            <div class="flex justify-center gap-6 mt-6">
              <div class="text-center">
                <div class="w-10 h-10 mx-auto rounded-full bg-blue-500/20 flex items-center justify-center mb-2">
                  <div class="w-2 h-2 rounded-full bg-blue-400"></div>
                </div>
                <div class="text-xs text-gray-400">Zero Fees</div>
              </div>
              <div class="text-center">
                <div class="w-10 h-10 mx-auto rounded-full bg-emerald-500/20 flex items-center justify-center mb-2">
                  <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
                </div>
                <div class="text-xs text-gray-400">Secure</div>
              </div>
              <div class="text-center">
                <div class="w-10 h-10 mx-auto rounded-full bg-purple-500/20 flex items-center justify-center mb-2">
                  <div class="w-2 h-2 rounded-full bg-purple-400"></div>
                </div>
                <div class="text-xs text-gray-400">Fast</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Indicateur de scroll minimaliste -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
      <div class="animate-bounce">
        <div class="w-6 h-10 border-2 border-white/30 rounded-full flex justify-center">
          <div class="w-1 h-2 bg-white/50 rounded-full mt-2 animate-pulse"></div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
/* Styles minimaux */
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

/* Animation simplifiée */
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}

.animate-pulse {
  animation: pulse 2s ease-in-out infinite;
}

/* Optimisations */
.backdrop-blur-xl {
  backdrop-filter: blur(12px);
  -webkit-backdrop-filter: blur(12px);
}

/* Responsive */
@media (max-width: 768px) {
  .grid-cols-4 {
    grid-template-columns: repeat(2, 1fr);
  }
}
</style>