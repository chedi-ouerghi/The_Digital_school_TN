<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import lottie, { type AnimationItem } from 'lottie-web'

const lottieContainer = ref<HTMLDivElement | null>(null)
let animationInstance: AnimationItem | null = (null)

// Optimisation du chargement
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

  // Chargement intelligent
  if (document.readyState === 'complete') {
    loadAnimation()
  } else {
    window.addEventListener('load', loadAnimation, { once: true })
  }
}

onMounted(() => initLottie())
onUnmounted(() => {
  animationInstance?.destroy()
})
</script>

<template>
  <section class="relative min-h-screen flex items-center bg-[#0A1423] overflow-hidden">
    
    <!-- Background optimisé -->
    <div class="absolute inset-0 bg-gradient-to-br from-[#0A1423] via-[#0F1E33] to-[#0A1423]">
      <!-- Texture subtile pour la profondeur -->
     <div
  class="absolute inset-0 opacity-5"
  style="background-image: url('data:image/svg+xml,%3Csvg%20width%3D%2220%22%20height%3D%2220%22%20viewBox%3D%220%200%2020%2020%22%20xmlns%3D%22http://www.w3.org/2000/svg%22%3E%3Cg%20fill%3D%22%23ffffff%22%20fill-rule%3D%22evenodd%22%3E%3Ccircle%20cx%3D%2210%22%20cy%3D%2210%22%20r%3D%221%22/%3E%3C/g%3E%3C/svg%3E');">
</div>

    </div>

    <div class="relative container mx-auto px-6 py-24 lg:py-32">
      <div class="grid lg:grid-cols-2 gap-16 lg:gap-20 items-center">
        
        <!-- Content -->
        <div class="space-y-8 lg:space-y-10">
          
          <!-- Badge avec micro-interaction -->
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-white/5 border border-white/10 backdrop-blur-md transition-all hover:border-[#35A7FF]/30">
            <span class="w-2 h-2 bg-[#35A7FF] rounded-full animate-pulse"></span>
            <span class="text-sm text-gray-300 font-medium">Designed for Modern Traders</span>
          </div>

          <!-- Titre avec légère animation au scroll -->
          <h1 class="text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight tracking-tight">
            Trade Crypto  
            <span class="text-[#35A7FF] block">Effortlessly</span>
          </h1>

          <!-- Description avec ligne de mesure parfaite -->
          <p class="text-lg text-gray-400 max-w-md leading-relaxed">
            Une plateforme claire, rapide et intuitive pour acheter, vendre et suivre vos actifs en temps réel.
          </p>

          <!-- CTA avec états hover améliorés -->
          <div class="flex flex-col sm:flex-row gap-3 pt-2">
            <button class="px-8 py-3.5 rounded-lg bg-[#01FF19] text-[#071B2C] font-semibold hover:bg-[#00e617] transition-all duration-200 shadow-lg hover:shadow-[#01FF19]/20">
              Commencer
            </button>

            <button class="px-8 py-3.5 rounded-lg border border-gray-600 text-gray-300 hover:bg-white/5 hover:border-gray-500 hover:text-white transition-all duration-200">
              Découvrir
            </button>
          </div>
        </div>

        <!-- Visual avec optimisation de performance -->
        <div class="relative">
          <!-- Glow background optimisé -->
          <div class="absolute -inset-4 bg-[#35A7FF]/10 blur-xl rounded-2xl opacity-50"></div>
          
          <!-- Container Lottie avec ratio fixe -->
          <div 
            ref="lottieContainer" 
            class="relative w-full aspect-[4/3] bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden"
          >
            <!-- Fallback stylisé -->
            <div class="absolute inset-0 flex items-center justify-center text-gray-500">
              <div class="text-center">
                <div class="w-12 h-12 border-3 border-gray-500 border-t-[#35A7FF] rounded-full animate-spin mx-auto mb-3"></div>
                <div class="text-sm text-gray-400">Chargement de l'interface</div>
              </div>
            </div>
          </div>
        </div>

      </div>
    </div>

    <!-- Scroll indicator minimal -->
    <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2">
      <div class="w-px h-16 bg-gradient-to-b from-gray-600 to-transparent"></div>
    </div>
  </section>
</template>

<style scoped>
/* Optimisations performance */
.lottie-animation {
  transform: translateZ(0);
  backface-visibility: hidden;
}

/* Support reduced motion pour l'accessibilité */
@media (prefers-reduced-motion: reduce) {
  .animate-pulse,
  .animate-spin {
    animation: none;
  }
}

/* Transitions optimisées */
* {
  transition-property: color, background-color, border-color;
  transition-duration: 200ms;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
}
</style>