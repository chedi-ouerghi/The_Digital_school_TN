<script setup lang="ts">
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Twitter, Linkedin, Github } from 'lucide-vue-next'
</script>

<template>
  <section
    class="relative py-24 lg:py-32 overflow-hidden bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C] text-white font-[Celias]"
  >
    <!-- Éclat d'arrière-plan -->
    <div class="absolute inset-0">
      <div class="absolute top-20 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-[#01FF19]/20 rounded-full blur-[160px]"></div>
      <div class="absolute bottom-20 right-20 w-96 h-96 bg-[#35A7FF]/15 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 container mx-auto px-6 lg:px-16 text-center">
      <!-- Titre principal -->
      <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-[#FF5964]/10 border border-[#FF5964]/30 text-[#FF5964] font-medium text-sm tracking-wide mb-6 animate-fade-in">
        <span class="text-lg">📧</span> Stay Connected
      </div>
      <h2
        class="text-4xl md:text-5xl xl:text-6xl font-extrabold leading-tight mb-6 animate-fade-in delay-100"
      >
        Get Started with <span class="text-[#01FF19] drop-shadow-[0_0_10px_#01FF19]">BitChest</span>
      </h2>

      <!-- Sous-texte -->
      <p
        class="text-white/80 text-lg mb-10 max-w-2xl mx-auto animate-fade-in delay-200"
      >
        Join our newsletter and be the first to receive updates, insights, and
        market opportunities from the BitChest team.
      </p>

      <!-- Formulaire Newsletter -->
      <div
        class="relative flex flex-col sm:flex-row items-center justify-center gap-4 max-w-lg mx-auto bg-white/5 backdrop-blur-sm border border-white/10 p-6 rounded-2xl shadow-[0_0_60px_-10px_rgba(1,255,25,0.3)] hover:border-[#01FF19]/40 transition-all duration-300 animate-fade-in delay-300"
      >
        <Input
          type="email"
          placeholder="Enter your email address"
          class="flex-1 bg-white/90 border-none text-[#071B2C] placeholder-[#071B2C]/60 rounded-xl h-12 focus-visible:ring-[#01FF19] transition"
        />
        <Button
          class="bg-[#01FF19] hover:bg-[#00e617] text-[#071B2C] font-semibold h-12 px-8 rounded-xl transition-all shadow-lg hover:shadow-[#01FF19]/40"
        >
          Subscribe
        </Button>
      </div>

      <!-- Ligne de séparation décorative -->
      <div
        class="mt-14 w-24 h-[2px] mx-auto bg-gradient-to-r from-transparent via-[#01FF19] to-transparent"
      ></div>

      <!-- Icônes Réseaux Sociaux -->
      <div
        class="flex justify-center space-x-8 mt-10 animate-fade-in delay-500"
      >
        <a
          href="#"
          class="group text-white/80 hover:text-[#01FF19] transition-all duration-300"
          aria-label="Twitter"
        >
          <Twitter
            class="w-7 h-7 transform group-hover:scale-110 group-hover:drop-shadow-[0_0_10px_#01FF19] transition-all duration-300"
          />
        </a>
        <a
          href="#"
          class="group text-white/80 hover:text-[#01FF19] transition-all duration-300"
          aria-label="LinkedIn"
        >
          <Linkedin
            class="w-7 h-7 transform group-hover:scale-110 group-hover:drop-shadow-[0_0_10px_#01FF19] transition-all duration-300"
          />
        </a>
        <a
          href="#"
          class="group text-white/80 hover:text-[#01FF19] transition-all duration-300"
          aria-label="GitHub"
        >
          <Github
            class="w-7 h-7 transform group-hover:scale-110 group-hover:drop-shadow-[0_0_10px_#01FF19] transition-all duration-300"
          />
        </a>
      </div>

      <!-- Texte final -->
      <p class="mt-8 text-white/60 text-sm">
        We respect your privacy. Unsubscribe anytime.
      </p>
    </div>
  </section>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(25px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { 
  opacity: 0; 
  animation: fade-in 1s ease forwards; 
}
.delay-100 { animation-delay: 0.1s; }
.delay-200 { animation-delay: 0.2s; }
.delay-300 { animation-delay: 0.3s; }
.delay-500 { animation-delay: 0.5s; }
</style>
