<script setup lang="ts">
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';
import GetStartedDialog from './GetStartedDialog.vue';

const navItems = [
  { label: 'Home', href: '#hero' },
  { label: 'Markets', href: '#crypto-tracker' },
  { label: 'Features', href: '#features' },
  { label: 'How It Works', href: '#how-it-works' },
  { label: 'Blog', href: '/blog', external: true },
  { label: 'Contact', href: '#contact' }
]

const isMenuOpen = ref(false)
</script>

<template>
  <header class="fixed w-full z-50 bg-black/80 backdrop-blur-xl border-b border-white/10">
    <!-- Main Header -->
    <div class="container mx-auto px-4 md:px-6 py-3">
      <div class="flex items-center justify-between">
        
        <!-- Logo -->
        <div class="flex items-center gap-3 flex-shrink-0">
          <div class="w-32 h-8 rounded-lg flex items-center justify-center">
            <img src="/assets/bitchest_logo.png" alt="BitChest" class="h-8 object-contain" />
          </div>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-1">
          <router-link
            v-for="item in navItems"
            :key="item.label"
            :to="item.href"
            class="relative px-3 py-2 text-sm text-gray-300 hover:text-white transition-colors group"
            :target="item.external ? '_self' : undefined"
          >
            {{ item.label }}
            <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-0 h-0.5 bg-gradient-to-r from-blue-500 to-emerald-500 group-hover:w-4/5 transition-all duration-300"></div>
          </router-link>
        </nav>

        <!-- Right Side -->
        <div class="flex items-center gap-3 flex-shrink-0">
          <!-- Desktop Button -->
          <div class="hidden md:flex items-center gap-3">
            <GetStartedDialog />
          </div>

          <!-- Mobile Menu Button -->
          <button
            class="md:hidden p-2 rounded-lg bg-white/5 border border-white/10 text-white hover:bg-white/10 transition-all"
            @click="isMenuOpen = !isMenuOpen"
          >
            <Menu v-if="!isMenuOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Status Bar -->
    <div class="border-t border-white/5 bg-black/90">
      <div class="container mx-auto px-4 md:px-6 py-2">
        <div class="flex items-center justify-between text-xs">
          <div class="flex items-center gap-4 overflow-x-auto">
            <div class="flex items-center gap-2 whitespace-nowrap">
              <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
              <span class="text-emerald-400 font-medium">LIVE</span>
            </div>
            <div class="text-gray-400 whitespace-nowrap">BTC: <span class="text-white font-medium">$63,842</span></div>
            <div class="text-gray-400 whitespace-nowrap">ETH: <span class="text-white font-medium">$3,456</span></div>
            <div class="text-gray-400 whitespace-nowrap">24h Vol: <span class="text-white font-medium">$68.4B</span></div>
          </div>
          <div class="hidden md:block whitespace-nowrap">
            <div class="text-gray-400">
              Market Cap: <span class="text-white font-medium">$1.87T</span>
              <span class="text-emerald-400 ml-2">+2.4%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div
      v-if="isMenuOpen"
      class="md:hidden absolute top-full left-0 right-0 bg-black/95 backdrop-blur-xl border-t border-white/10 p-6 rounded-b-2xl"
    >
      <div class="space-y-3">
        <router-link
          v-for="item in navItems"
          :key="item.label"
          :to="item.href"
          class="block py-2 px-4 rounded-lg text-sm text-gray-300 hover:text-white hover:bg-white/5 transition-all"
          @click="isMenuOpen = false"
        >
          {{ item.label }}
        </router-link>
        <div class="pt-3 border-t border-white/10">
          <GetStartedDialog />
        </div>
      </div>
    </div>
  </header>
</template>

<style scoped>
/* Smooth scroll behavior */
html {
  scroll-behavior: smooth;
}

/* Mobile menu animation */
div[class*="bg-black/95"] {
  animation: slideDown 0.3s ease-out;
}

@keyframes slideDown {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Hide scrollbar but allow scrolling */
.overflow-x-auto {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.overflow-x-auto::-webkit-scrollbar {
  display: none;
}
</style>