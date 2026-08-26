<script setup lang="ts">
import { Menu, X } from 'lucide-vue-next';
import { ref, onMounted, onUnmounted } from 'vue';
import GetStartedDialog from './GetStartedDialog.vue';
import { colors } from '@/config/designSystem';

const navItems = [
  { label: 'Home', href: '#top' },
  { label: 'Features', href: '#features' },
  { label: 'Analytics', href: '#analytics' },
];

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
  <header
    id="top"
    class="fixed w-full z-50 transition-all duration-300"
    :style="{
      backgroundColor: isScrolled ? `${colors.background}F2` : colors.background,
      backdropFilter: isScrolled ? 'blur(12px)' : 'none',
      borderBottom: `1.5px solid ${isScrolled ? colors.border.medium : colors.border.light}`,
      boxShadow: isScrolled ? `0 4px 20px ${colors.hexWithOpacity(colors.slate[900], 0.08)}` : 'none'
    }"
  >
    <!-- Main Header -->
    <div class="container mx-auto px-4 md:px-6 py-4">
      <div class="flex items-center justify-between">
        <!-- Logo -->
        <div class="flex items-center gap-3">
          <router-link to="/" class="w-32 h-8 flex items-center group">
        
            <span class="" :style="{ color: colors.text.primary }">
              <img src="/assets/bitchest_logo.png" alt="BitChest Logo" class="h-14" />
            </span>
          </router-link>
        </div>

        <!-- Desktop Navigation -->
        <nav class="hidden md:flex items-center gap-1">
          <a
            v-for="item in navItems"
            :key="item.label"
            :href="item.href"
            class="nav-link relative px-4 py-2 text-sm font-semibold transition-all duration-300 group"
            :style="{ color: colors.text.primary }"
          >
            {{ item.label }}
            <div
              class="absolute bottom-0 left-4 right-4 h-0.5 rounded-full opacity-0 group-hover:opacity-100 transition-all duration-300 transform scale-x-0 group-hover:scale-x-100"
              :style="{ backgroundColor: colors.primary[500] }"
            />
          </a>
        </nav>

        <!-- Right Side -->
        <div class="flex items-center gap-3">
          <!-- Desktop CTA -->
          <div class="hidden md:flex items-center gap-3">
            <router-link
              to="/signin"
              class="hidden lg:inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl border transition-all duration-300 hover:scale-[1.03]"
              :style="{ borderColor: colors.border.medium, color: colors.text.primary }"
            >
              Sign In
            </router-link>
            <GetStartedDialog />
          </div>

          <!-- Mobile Menu Button -->
          <button
            class="md:hidden p-2 rounded-lg border transition-all duration-300 hover:scale-110"
            :style="{ borderColor: colors.border.medium, color: colors.text.primary }"
            @click="isMenuOpen = !isMenuOpen"
          >
            <Menu v-if="!isMenuOpen" class="w-5 h-5" />
            <X v-else class="w-5 h-5" />
          </button>
        </div>
      </div>
    </div>

    <!-- Market Data Bar -->
    <div class="border-t py-2" :style="{ borderColor: colors.border.light, backgroundColor: colors.surface }">
      <div class="container mx-auto px-4 md:px-6">
        <div class="flex items-center justify-between overflow-x-auto scrollbar-hide">
          <!-- Live Status -->
          <div class="flex items-center gap-3 flex-shrink-0 pr-4">
            <div
              class="flex items-center gap-2 px-3 py-1 rounded-full"
              :style="{ backgroundColor: `${colors.primary[500]}15`, color: colors.primary[600] }"
            >
              <div class="w-2 h-2 rounded-full animate-pulse" :style="{ backgroundColor: colors.primary[500] }"></div>
              <span class="text-xs font-bold">LIVE</span>
            </div>
            <div class="text-xs font-semibold" :style="{ color: colors.text.secondary }">
              Real-time data
            </div>
          </div>

          <!-- Market Data -->
          <div class="flex items-center gap-6 md:gap-8 px-4 flex-1 justify-center">
            <div
              v-for="data in marketData"
              :key="data.symbol"
              class="flex items-center gap-2 flex-shrink-0 transition-all duration-300 hover:scale-105"
            >
              <span class="text-xs font-bold" :style="{ color: colors.text.secondary }">{{ data.symbol }}:</span>
              <span class="text-xs font-black" :style="{ color: colors.text.primary }">{{ data.price }}</span>
              <span
                class="text-xs font-bold px-2 py-1 rounded"
                :style="{
                  backgroundColor: data.isPositive ? `${colors.primary[500]}15` : `${colors.error}15`,
                  color: data.isPositive ? colors.primary[600] : colors.error
                }"
              >
                {{ data.change }}
              </span>
            </div>
          </div>

          <!-- Time -->
          <div class="hidden md:block flex-shrink-0 pl-4">
            <div class="text-xs font-semibold" :style="{ color: colors.text.secondary }">
              <span :style="{ color: colors.primary[500] }">•</span> Updated just now
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
          borderColor: colors.border.medium,
          boxShadow: `0 20px 40px ${colors.hexWithOpacity(colors.slate[900], 0.1)}`
        }"
      >
        <div class="space-y-1">
          <a
            v-for="item in navItems"
            :key="item.label"
            :href="item.href"
            class="block py-3 px-4 rounded-lg text-base font-semibold transition-all duration-300 hover:translate-x-2"
            :style="{ color: colors.text.primary, backgroundColor: `${colors.primary[500]}05` }"
            @click="isMenuOpen = false"
          >
            {{ item.label }}
          </a>
        </div>

        <div class="mt-6 pt-6 border-t" :style="{ borderColor: colors.border.light }">
          <router-link
            to="/signin"
            class="block text-center py-3 px-4 rounded-lg text-base font-semibold border transition-all duration-300"
            :style="{ borderColor: colors.border.medium, color: colors.text.primary, backgroundColor: colors.surface }"
            @click="isMenuOpen = false"
          >
            Se connecter
          </router-link>
          <div class="mt-4 flex justify-center">
            <GetStartedDialog />
          </div>
        </div>

        <!-- Mobile Market Data -->
        <div class="mt-6 grid grid-cols-2 gap-3">
          <div
            v-for="data in marketData.slice(0, 2)"
            :key="data.symbol"
            class="p-3 rounded-lg text-center"
            :style="{
              backgroundColor: colors.surface,
              border: `1.5px solid ${colors.border.light}`
            }"
          >
            <div class="text-xs font-bold mb-1" :style="{ color: colors.text.secondary }">{{ data.symbol }}</div>
            <div class="text-sm font-black" :style="{ color: colors.text.primary }">{{ data.price }}</div>
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
  color: v-bind('colors.primary[500]');
}

.nav-link[aria-current='location'] {
  color: v-bind('colors.primary[600]');
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