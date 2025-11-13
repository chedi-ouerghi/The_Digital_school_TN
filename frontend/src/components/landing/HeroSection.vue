<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import lottie, { type AnimationItem } from 'lottie-web'
import { Button } from '@/components/ui/button'
import { ArrowRight } from 'lucide-vue-next'

const lottieContainer = ref<HTMLDivElement | null>(null)
let animationInstance: AnimationItem | null = null

function initLottie() {
  if (!lottieContainer.value) return
  animationInstance = lottie.loadAnimation({
    container: lottieContainer.value,
    renderer: 'svg',
    loop: true,
    autoplay: true,
    path: '/heroAnimattion.json'
  })
}

onMounted(() => initLottie())
onUnmounted(() => animationInstance?.destroy())
</script>

<template>
  <section class="relative overflow-hidden">
    <div class="absolute inset-0 bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C]" aria-hidden="true"></div>

    <div class="relative container mx-auto px-6 py-24 md:py-28">
      <div class="grid md:grid-cols-2 gap-10 items-center">
        <div>
          <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#35A7FF]/10 border border-[#35A7FF]/30 text-[#35A7FF] mb-6">
            <span class="w-2 h-2 bg-[#35A7FF] rounded-full animate-pulse"></span>
            For Everyone
          </div>
          
          <h1 class="font-[Celias] text-4xl md:text-5xl font-bold text-white tracking-tight">
            Trade & Grow your Crypto where simplicity meets speed
          </h1>
          
          <p class="mt-4 text-[#B0CADB] text-lg">
            Buy, sell and manage your digital assets smarter with real-time insights
            and elegant tools built for clarity.
          </p>
          
          <div class="mt-8 flex flex-wrap items-center gap-4">
            <Button class="bg-[#01FF19] hover:bg-[#00e617] text-[#071B2C] font-semibold px-6 py-3 rounded-full shadow-lg hover:shadow-[#01FF19]/40 transition-all duration-300">
              Get Started
              <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
            <Button variant="outline" class="border-[#35A7FF] text-[#35A7FF] hover:bg-[#35A7FF]/10">
              Learn More
            </Button>
          </div>
        </div>

        <div class="relative">
          <div class="absolute -top-10 -right-10 w-64 h-64 bg-[#FF5964]/20 rounded-full blur-3xl"></div>
          <div class="absolute -bottom-10 -left-10 w-64 h-64 bg-[#35A7FF]/20 rounded-full blur-3xl"></div>
          
          <div class="relative rounded-2xl border border-[#01FF19]/30 hover:border-[#01FF19]/60 transition-colors duration-300 shadow-2xl shadow-[#35A7FF]/20 overflow-hidden">
            <div ref="lottieContainer" class="w-full h-[360px] md:h-[420px]"></div>
            <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
              <div class="absolute inset-x-0 bottom-0 h-28 bg-gradient-to-t from-[#071B2C] via-[#071B2C]/40 to-transparent"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
