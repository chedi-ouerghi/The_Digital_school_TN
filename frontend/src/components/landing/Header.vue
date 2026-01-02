<script setup lang="ts">
import { Menu, X } from 'lucide-vue-next';
import { ref } from 'vue';
import GetStartedDialog from './GetStartedDialog.vue';

const navItems = [
  { label: 'Home', href: '/' },
  { label: 'Blog', href: '/blog', external: true },
]

const isMenuOpen = ref(false)
</script>

<template>
  <header class="fixed w-full z-50 backdrop-blur-xl" style="background: transparent; border-bottom: 1px solid rgba(255,255,255,0.06);">
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
        <nav class="hidden md:flex items-center gap-1 nav">
          <router-link
            v-for="item in navItems"
            :key="item.label"
            :to="item.href"
            class="nav-link relative px-3 py-2 text-sm transition-colors group"
            :target="item.external ? '_self' : undefined"
          >
            {{ item.label }}
            <div class="underline"></div>
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
    <div class="status-bar">
      <div class="container mx-auto px-4 md:px-6 py-2">
        <div class="flex items-center justify-between text-xs">
          <div class="flex items-center gap-4 overflow-x-auto">
            <div class="flex items-center gap-2 whitespace-nowrap">
              <div class="w-2 h-2 rounded-full live-dot"></div>
              <span class="live-text">LIVE</span>
            </div>
            <div class="stat-text">BTC: <span class="stat-value">$63,842</span></div>
            <div class="stat-text">ETH: <span class="stat-value">$3,456</span></div>
            <div class="stat-text">24h Vol: <span class="stat-value">$68.4B</span></div>
          </div>
          <div class="hidden md:block whitespace-nowrap">
            <div class="stat-text">Market Cap: <span class="stat-value">$1.87T</span>
              <span class="market-change">+2.4%</span>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div
      v-if="isMenuOpen"
      class="md:hidden absolute top-full left-0 right-0 mobile-menu p-6 rounded-b-2xl"
    >
      <div class="space-y-3">
        <router-link
          v-for="item in navItems"
          :key="item.label"
          :to="item.href"
          class="block py-2 px-4 rounded-lg mobile-link"
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

/* New header styles to match landing palette */
.nav-link { color: #38618C; font-weight: 600; }
.nav-link:hover { color: #01FF19; }
.nav-link.router-link-active { color: #FF5964; }
.underline { position: absolute; bottom: 6px; left: 50%; transform: translateX(-50%); width: 0; height: 3px; border-radius: 6px; background: linear-gradient(90deg, #35A7FF, #01FF19); transition: width .28s; }
.nav-link:hover .underline { width: 60%; }

.status-bar { border-top: 1px solid rgba(255,255,255,0.03); background: transparent; }
.live-dot { background: #01FF19; box-shadow: 0 0 8px rgba(1,255,25,0.3); }
.live-text { color: #01FF19; font-weight: 700; }
.stat-text { color: #38618C; opacity: 0.9 }
.stat-value { color: #FFFFFF; background: rgba(255,255,255,0.06); padding: 2px 6px; border-radius: 6px; margin-left: 6px }
.market-change { color: #01FF19; margin-left: 6px }

.mobile-menu { background: #FFFFFF; color: #38618C; border-top: 1px solid rgba(56,97,140,0.06); }
.mobile-link { color: #38618C; }
.mobile-link:hover { color: #01FF19; background: rgba(1,255,25,0.03); }
</style>