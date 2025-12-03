<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import lottie, { type AnimationItem } from 'lottie-web'
import { ChevronRight, Sparkles, Zap, Shield, TrendingUp } from 'lucide-vue-next'

const lottieContainer = ref<HTMLDivElement | null>(null)
let animationInstance: AnimationItem | null = null

// States for interactive elements
const isHovered = ref(false)
const stats = ref([
  { value: '24/7', label: 'Market Monitoring', icon: Shield, color: 'from-blue-500 to-cyan-500' },
  { value: '0.1s', label: 'Execution Speed', icon: TrendingUp, color: 'from-violet-500 to-purple-500' }
])

// Floating particles
const particles = ref(Array.from({ length: 15 }, (_, i) => ({
  id: i,
  x: Math.random() * 100,
  y: Math.random() * 100,
  size: Math.random() * 3 + 1,
  speed: Math.random() * 0.5 + 0.2,
  color: Math.random() > 0.5 ? '#35A7FF' : '#01FF19'
})))

function initLottie() {
  if (!lottieContainer.value || typeof window === 'undefined') return
  
  const loadAnimation = () => {
    if (!lottieContainer.value) return
    
    animationInstance = lottie.loadAnimation({
      container: lottieContainer.value,
      renderer: 'svg',
      loop: true,
      autoplay: true,
      path: '/heroAnimattion.json',
      rendererSettings: {
        progressiveLoad: true,
        hideOnTransparent: true,
        className: 'lottie-animation'
      }
    })
  }

  if (document.readyState === 'complete') {
    loadAnimation()
  } else {
    window.addEventListener('load', loadAnimation, { once: true })
  }
}

// Animate particles
onMounted(() => {
  initLottie()
  
  const animateParticles = () => {
    particles.value.forEach(particle => {
      particle.y -= particle.speed
      if (particle.y < -10) {
        particle.y = 110
        particle.x = Math.random() * 100
      }
    })
    requestAnimationFrame(animateParticles)
  }
  
  animateParticles()
})

onUnmounted(() => {
  animationInstance?.destroy()
})
</script>

<template>
  <section class="relative min-h-screen flex items-center bg-gradient-to-br from-gray-950 via-gray-900 to-black overflow-hidden">
    
    <!-- Animated Background Elements -->
    <div class="absolute inset-0 overflow-hidden">
      <!-- Grid Pattern -->
      <div 
        class="absolute inset-0 opacity-5"
        style="background-image: linear-gradient(rgba(255, 255, 255, 0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255, 255, 255, 0.1) 1px, transparent 1px); background-size: 50px 50px;"
      ></div>
      
      <!-- Floating Particles -->
      <div class="absolute inset-0">
        <div
          v-for="particle in particles"
          :key="particle.id"
          class="absolute rounded-full opacity-20"
          :style="{
            left: `${particle.x}%`,
            top: `${particle.y}%`,
            width: `${particle.size}px`,
            height: `${particle.size}px`,
            backgroundColor: particle.color,
            boxShadow: `0 0 ${particle.size * 2}px ${particle.color}`
          }"
        ></div>
      </div>
      
      <!-- Animated Gradients -->
      <div class="absolute top-1/4 -left-1/4 w-[800px] h-[800px]">
        <div class="absolute inset-0 bg-gradient-to-r from-blue-500/10 via-transparent to-emerald-500/10 rounded-full blur-3xl animate-pulse"></div>
      </div>
      
      <div class="absolute bottom-1/4 -right-1/4 w-[600px] h-[600px]">
        <div class="absolute inset-0 bg-gradient-to-r from-violet-500/5 via-transparent to-cyan-500/5 rounded-full blur-3xl"></div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="relative container mx-auto px-6 py-24 lg:py-32">
      <div class="grid lg:grid-cols-2 gap-16 lg:gap-24 items-center">
        
        <!-- Left Column -->
        <div class="space-y-10 lg:space-y-12">
          
          <!-- Premium Badge -->
          <div 
            @mouseenter="isHovered = true"
            @mouseleave="isHovered = false"
            class="inline-flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-gray-800/50 to-gray-900/50 backdrop-blur-xl border border-gray-700 hover:border-blue-500/50 transition-all duration-300 group cursor-pointer"
          >
            <div class="relative">
              <div class="absolute -inset-1 bg-gradient-to-r from-blue-500 to-emerald-500 rounded-full blur opacity-30 group-hover:opacity-50 transition-opacity"></div>
              <div class="relative bg-gray-800 p-2 rounded-lg">
                <Sparkles class="w-5 h-5 text-blue-400" />
              </div>
            </div>
            <div>
              <span class="text-sm font-semibold text-white">PROFESSIONAL TRADING PLATFORM</span>
              <div class="flex items-center gap-1 mt-1">
                <div class="h-px w-4 bg-gradient-to-r from-blue-500 to-emerald-500"></div>
                <span class="text-xs text-gray-400">Bitchest</span>
              </div>
            </div>
          </div>

          <!-- Main Headline -->
          <div class="space-y-6">
            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-tight">
              <span class="bg-gradient-to-r from-white via-white to-gray-300 bg-clip-text text-transparent">
                BIT
              </span>
              <span class="bg-gradient-to-r from-blue-400 via-cyan-400 to-emerald-400 bg-clip-text text-transparent">
                CHEST
              </span>
            </h1>
            
            <div class="pl-4 border-l-2 border-blue-500/50">
              <p class="text-xl text-gray-300 leading-relaxed max-w-lg">
                Advanced trading platform with institutional-grade tools, 
                real-time analytics, and lightning-fast execution.
              </p>
            </div>
          </div>

          <!-- Stats Cards -->
          <div class="grid grid-cols-3 gap-4 pt-4">
            <div
              v-for="(stat, index) in stats"
              :key="stat.label"
              class="bg-gradient-to-br from-gray-800/40 to-gray-900/40 backdrop-blur-lg border border-gray-700 rounded-xl p-4 hover:border-gray-600 transition-all duration-300"
              :style="{ animationDelay: `${index * 100}ms` }"
            >
              <div class="flex items-center justify-between mb-3">
                <component 
                  :is="stat.icon" 
                  class="w-6 h-6"
                  :class="`text-gradient bg-gradient-to-r ${stat.color} bg-clip-text text-transparent`"
                />
                <div class="text-xs text-gray-500 font-mono">▲</div>
              </div>
              <div class="text-2xl font-bold text-white mb-1">{{ stat.value }}</div>
              <div class="text-xs text-gray-400">{{ stat.label }}</div>
            </div>
          </div>

          <!-- CTA Buttons -->
          <div class="flex flex-col sm:flex-row gap-4 pt-8">
            <button 
              class="group relative px-8 py-4 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:shadow-2xl hover:shadow-blue-500/30 transition-all duration-300 overflow-hidden"
            >
              <div class="absolute inset-0 bg-gradient-to-r from-blue-600 to-emerald-600 opacity-0 group-hover:opacity-100 transition-opacity"></div>
              <div class="relative flex items-center justify-center gap-3">
                <span>Start Trading</span>
                <ChevronRight class="w-5 h-5 group-hover:translate-x-1 transition-transform" />
              </div>
            </button>

          
          </div>

          <!-- Trust Indicators -->
          <div class="pt-8">
            <div class="flex items-center gap-4 text-sm text-gray-500">
              <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
              <span>Trusted by 500K+ traders worldwide</span>
              <div class="h-px flex-1 bg-gradient-to-r from-transparent via-gray-700 to-transparent"></div>
            </div>
          </div>
        </div>

        <!-- Right Column - Visual -->
        <div class="relative">
          
          <!-- Main Visual Container -->
          <div class="relative group">
            <!-- Outer Glow -->
            <div class="absolute -inset-4 bg-gradient-to-r from-blue-500/20 via-cyan-500/10 to-emerald-500/20 rounded-3xl blur-2xl opacity-50 group-hover:opacity-70 transition-opacity duration-500"></div>
            
            <!-- Glass Container -->
            <div class="relative bg-gradient-to-br from-gray-800/30 to-gray-900/30 backdrop-blur-2xl border border-gray-700/50 rounded-2xl overflow-hidden shadow-2xl">
              
              <!-- Animated Border -->
              <div class="absolute inset-0 rounded-2xl p-[1px] bg-gradient-to-r from-transparent via-blue-500/30 to-transparent">
                <div class="absolute inset-0 rounded-2xl bg-gradient-to-r from-blue-500/0 via-blue-500/20 to-blue-500/0 animate-gradient"></div>
              </div>

              <!-- Lottie Container -->
              <div 
                ref="lottieContainer" 
                class="relative w-full aspect-[4/3] bg-gradient-to-br from-gray-900/50 to-black/50"
              >
                <!-- Loading State -->
                <div class="absolute inset-0 flex items-center justify-center">
                  <div class="text-center space-y-4">
                    <div class="relative">
                      <div class="w-16 h-16 border-4 border-gray-800 rounded-full"></div>
                      <div class="absolute top-0 left-0 w-16 h-16 border-4 border-blue-500 border-t-transparent rounded-full animate-spin"></div>
                    </div>
                    <div class="text-sm text-gray-500">Loading trading interface...</div>
                  </div>
                </div>
              </div>

              <!-- Overlay Stats -->
              <div class="absolute bottom-6 left-6 right-6">
                <div class="flex items-center justify-between">
                  <div class="bg-gray-900/80 backdrop-blur-sm rounded-lg px-4 py-2">
                    <div class="text-xs text-gray-400">LIVE</div>
                    <div class="text-sm font-bold text-white">BTC: $63,842.15</div>
                  </div>
                  <div class="flex gap-2">
                    <div class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></div>
                    <div class="text-xs text-emerald-400 font-medium">+2.4%</div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Floating Elements -->
            <div class="absolute -top-4 -right-4 bg-gradient-to-r from-blue-500 to-cyan-500 rounded-lg p-3 shadow-2xl shadow-blue-500/30">
              <Zap class="w-6 h-6 text-white" />
            </div>
          </div>

          <!-- Feature Dots -->
          <div class="grid grid-cols-3 gap-4 mt-8">
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-blue-500/20 to-blue-500/10 border border-blue-500/30 mb-2">
                <div class="w-2 h-2 rounded-full bg-blue-400"></div>
              </div>
              <div class="text-xs text-gray-400">Zero Fees</div>
            </div>
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-emerald-500/20 to-emerald-500/10 border border-emerald-500/30 mb-2">
                <div class="w-2 h-2 rounded-full bg-emerald-400"></div>
              </div>
              <div class="text-xs text-gray-400">API Access</div>
            </div>
            <div class="text-center">
              <div class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-r from-violet-500/20 to-violet-500/10 border border-violet-500/30 mb-2">
                <div class="w-2 h-2 rounded-full bg-violet-400"></div>
              </div>
              <div class="text-xs text-gray-400">24/7 Support</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Scroll Indicator -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
      <div class="flex flex-col items-center">
        <div class="text-xs text-gray-500 mb-2 animate-bounce">EXPLORE</div>
        <div class="w-px h-16 bg-gradient-to-b from-blue-500/50 via-emerald-500/30 to-transparent"></div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
  font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
}

.text-gradient {
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
}

.lottie-animation {
  transform: translateZ(0);
  backface-visibility: hidden;
  image-rendering: -webkit-optimize-contrast;
  image-rendering: crisp-edges;
}

@keyframes gradient {
  0%, 100% {
    background-position: 0% 50%;
  }
  50% {
    background-position: 100% 50%;
  }
}

.animate-gradient {
  background: linear-gradient(
    90deg,
    transparent,
    rgba(59, 130, 246, 0.2),
    rgba(16, 185, 129, 0.2),
    transparent
  );
  background-size: 200% 100%;
  animation: gradient 3s ease infinite;
}

/* Entrance animations */
[class*="bg-gradient"] {
  animation: fadeInUp 0.6s ease-out forwards;
  animation-fill-mode: both;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Delay animations */
.grid-cols-3 > *:nth-child(1) { animation-delay: 0.1s; }
.grid-cols-3 > *:nth-child(2) { animation-delay: 0.2s; }
.grid-cols-3 > *:nth-child(3) { animation-delay: 0.3s; }

/* Reduced motion support */
@media (prefers-reduced-motion: reduce) {
  .animate-gradient,
  .animate-spin,
  .animate-pulse,
  .animate-bounce,
  [class*="bg-gradient"] {
    animation: none;
  }
  
  .lottie-animation {
    animation: none !important;
  }
}

/* Optimized transitions */
* {
  transition-property: color, background-color, border-color, transform, opacity;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}

/* Glass effect enhancement */
.backdrop-blur-2xl {
  backdrop-filter: blur(20px);
  -webkit-backdrop-filter: blur(20px);
}

/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}
</style>