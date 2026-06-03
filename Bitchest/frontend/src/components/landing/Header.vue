<script setup lang="ts">
import { Menu, X } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import GetStartedDialog from './GetStartedDialog.vue';

// Color palette
const colors = {
  primary: '#01FF19',
  secondary: '#35A7FF',
  accent: '#FF5964',
  textPrimary: '#38618C',
  textSecondary: '#5A6175',
  background: '#FFFFFF',
  surface: '#F8FAFF',
  border: '#E2E8F0',
  borderAccent: '#FF5964'
}

const navItems = [
  { label: 'Home', href: '/' },
  { label: 'Features', href: '/features' },
  { label: 'Pricing', href: '/pricing' },
]

const marketData = ref([
  { symbol: 'BTC', price: '$63,842', change: '+2.4%', isPositive: true },
  { symbol: 'ETH', price: '$3,456', change: '+1.8%', isPositive: true },
  { symbol: 'SOL', price: '$142.56', change: '-0.5%', isPositive: false },
  { symbol: '24h Vol', price: '$68.4B', change: '+5.2%', isPositive: true },
]);

const isMenuOpen = ref(false);
const isScrolled = ref(false);

const handleScroll = () => {
  isScrolled.value = window.scrollY > 20;
};

onMounted(() => {
  window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
  window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
  <header class="fixed w-full z-50 transition-all duration-300"
          :style="{ 
            backgroundColor: isScrolled ? `${colors.background}E6` : colors.background,
            backdropFilter: isScrolled ? 'blur(10px)' : 'none',
            borderBottom: `1.5px solid ${isScrolled ? colors.borderAccent : colors.border}`,
            boxShadow: isScrolled ? `0 4px 20px ${colors.border}20` : 'none'
          }">
    
    <!-- Main Header -->
    <div class="container mx-auto px-4 md:px-6 py-4">
      <div class="flex items-center justify-between">
        
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <div class="w-32 h-8 flex items-center">
            <div class="w-8 h-8 rounded-lg mr-3 flex items-center justify-center"
                 :style="{ background: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})`, color: colors.background }">
              ⚡
            </div>
            <span class="text-xl font-black" :style="{ color: colors.textPrimary }">
              Bit<span :style="{ color: colors.primary }">CHEST</span>
            </span>
          </div>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-1">
          <router-link
            v-for="item in navItems"
            :key="item.label"
            :to="item.href"
            class="nav-link relative px-4 py-2 text-sm font-semibold transition-all duration-300 group"
            :style="{ color: colors.textPrimary }"
            :target="item.external ? '_self' : undefined"
          >
            {{ item.label }}
            <div class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-x-0 group-hover:scale-x-100"
                 :style="{ backgroundColor: colors.primary }"></div>
          </router-link>
        </nav>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
          <!-- Desktop Button -->
          <div class="hidden md:flex items-center gap-3">
            <GetStartedDialog />
          </div>

          <!-- Mobile Menu Button -->
          <button
            class="md:hidden p-2 rounded-lg border transition-all duration-300 hover:scale-110"
            :style="{ 
              borderColor: colors.borderAccent,
              color: colors.textPrimary
            }"
            @click="isMenuOpen = !isMenuOpen"
          >
            <Menu v-if="!isMenuOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Market Data Bar -->
    <div class="border-t py-2" :style="{ borderColor: colors.border, backgroundColor: colors.surface }">
      <div class="container mx-auto px-4 md:px-6">
        <div class="flex items-center justify-between overflow-x-auto scrollbar-hide">
          <!-- Live Status -->
          <div class="flex items-center gap-3 flex-shrink-0 pr-4">
            <div class="flex items-center gap-2 px-3 py-1 rounded-full animate-pulse"
                 :style="{ backgroundColor: `${colors.primary}15`, color: colors.primary }">
              <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: colors.primary }"></div>
              <span class="text-xs font-bold">LIVE</span>
            </div>
            <div class="text-xs font-semibold" :style="{ color: colors.textSecondary }">
              Real-time data
            </div>
          </div>
          
          <!-- Market Data -->
          <div class="flex items-center gap-6 md:gap-8 px-4 flex-1 justify-center">
            <div v-for="data in marketData" :key="data.symbol" 
                 class="flex items-center gap-2 flex-shrink-0 transition-all duration-300 hover:scale-105">
              <span class="text-xs font-bold" :style="{ color: colors.textSecondary }">{{ data.symbol }}:</span>
              <span class="text-xs font-black" :style="{ color: colors.textPrimary }">{{ data.price }}</span>
              <span class="text-xs font-bold px-2 py-1 rounded"
                    :style="{ 
                      backgroundColor: data.isPositive ? `${colors.primary}15` : `${colors.accent}15`,
                      color: data.isPositive ? colors.primary : colors.accent
                    }">
                {{ data.change }}
              </span>
            </div>
          </div>
          
          <!-- Time -->
          <div class="hidden md:block flex-shrink-0 pl-4">
            <div class="text-xs font-semibold" :style="{ color: colors.textSecondary }">
              <span :style="{ color: colors.primary }">•</span> Updated just now
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Mobile Menu -->
    <transition
      enter-active-class="transition-all duration-300 ease-out"
      leave-active-class="transition-all duration-200 ease-in"
      enter-from-class="opacity-0 -translate-y-4"
      leave-to-class="opacity-0 -translate-y-4"
    >
      <div
        v-if="isMenuOpen"
        class="md:hidden absolute top-full left-0 right-0 p-6 border-b animate-fadeInUp"
        :style="{ 
          backgroundColor: colors.background,
          borderColor: colors.borderAccent,
          boxShadow: `0 20px 40px ${colors.border}30`
        }"
      >
        <div class="space-y-1">
          <router-link
            v-for="item in navItems"
            :key="item.label"
            :to="item.href"
            class="block py-3 px-4 rounded-lg text-base font-semibold transition-all duration-300 hover:translate-x-2"
            :style="{ 
              color: colors.textPrimary,
              backgroundColor: `${colors.primary}05`
            }"
            @click="isMenuOpen = false"
          >
            {{ item.label }}
          </router-link>
        </div>
        
        <div class="mt-6 pt-6 border-t" :style="{ borderColor: colors.border }">
          <GetStartedDialog />
        </div>
        
        <!-- Mobile Market Data -->
        <div class="mt-6 grid grid-cols-2 gap-3">
          <div v-for="data in marketData.slice(0, 2)" :key="data.symbol"
               class="p-3 rounded-lg text-center"
               :style="{ 
                 backgroundColor: colors.surface,
                 border: `1.5px solid ${colors.border}`
               }">
            <div class="text-xs font-bold mb-1" :style="{ color: colors.textSecondary }">{{ data.symbol }}</div>
            <div class="text-sm font-black" :style="{ color: colors.textPrimary }">{{ data.price }}</div>
          </div>
        </div>
      </div>
    </transition>
  </header>
</template>

<style scoped>
.scrollbar-hide {
  -ms-overflow-style: none;
  scrollbar-width: none;
}

.scrollbar-hide::-webkit-scrollbar {
  display: none;
}

.nav-link:hover {
  color: v-bind('colors.primary');
}

.nav-link.router-link-active {
  color: v-bind('colors.primary');
}

.nav-link.router-link-active .underline {
  width: 60%;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.3s ease-out;
}
</style>