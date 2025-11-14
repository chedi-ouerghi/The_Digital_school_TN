<script setup lang="ts">
import GetStartedDialog from './GetStartedDialog.vue';

const navItems = [
  { label: 'Home', href: '#' },
  { label: 'Features', href: '#features' },
  { label: 'Pricing', href: '#pricing' },
  { label: 'About', href: '#about' },
  { label: 'Contact', href: '#contact' }
]
</script>

<template>
  <header class="fixed w-full z-50 bg-[#071B2C]/90 backdrop-blur-md border-b border-white/10">
    <div class="container mx-auto px-6 py-4">
      <div class="flex items-center justify-between">
        <img src="/assets/bitchest_logo.png" alt="BitChest" class="h-8" />
        
        <nav class="hidden md:flex items-center space-x-8">
          <a v-for="item in navItems" 
             :key="item.label"
             :href="item.href"
             class="nav-link text-white/90 hover:text-[#01FF19] transition-all duration-300 font-medium">
            {{ item.label }}
          </a>
        </nav>

        <GetStartedDialog />
      </div>
    </div>
  </header>
</template>

<style scoped>
.nav-link { position: relative; }
.nav-link::after {
  content: "";
  position: absolute;
  left: 0;
  bottom: -6px;
  height: 2px;
  width: 0;
  background: #01FF19;
  transition: width .3s ease;
}
.nav-link:hover::after { width: 100%; }
</style>
