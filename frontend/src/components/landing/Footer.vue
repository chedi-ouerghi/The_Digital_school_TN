<script setup lang="ts">
</script>

<template>
  <footer class="relative bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C] text-white py-16 border-t border-white/10 font-[Celias]">
    <!-- Éclat d'arrière-plan -->
    <div class="absolute inset-0">
      <div class="absolute bottom-0 left-0 w-96 h-96 bg-[#35A7FF]/10 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 container mx-auto px-6 lg:px-16">
      <div class="grid md:grid-cols-4 gap-8 mb-12">
        <div class="animate-fade-in">
          <img src="/assets/bitchest_logo.png" alt="BitChest" class="h-8 mb-4" />
          <p class="text-sm text-white/70 max-w-xs">
            Your trusted crypto investment platform. Trade smarter, invest better.
          </p>
        </div>
        
        <div class="animate-fade-in delay-100">
          <h3 class="font-semibold mb-4 text-white">Quick Links</h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-white/70 hover:text-[#01FF19] transition-colors duration-300">About</a></li>
            <li><a href="#" class="text-white/70 hover:text-[#01FF19] transition-colors duration-300">Careers</a></li>
            <li><a href="#" class="text-white/70 hover:text-[#01FF19] transition-colors duration-300">Blog</a></li>
          </ul>
        </div>
        
        <div class="animate-fade-in delay-200">
          <h3 class="font-semibold mb-4 text-white">Legal</h3>
          <ul class="space-y-3">
            <li><a href="#" class="text-white/70 hover:text-[#01FF19] transition-colors duration-300">Privacy Policy</a></li>
            <li><a href="#" class="text-white/70 hover:text-[#01FF19] transition-colors duration-300">Terms of Service</a></li>
            <li><a href="#" class="text-white/70 hover:text-[#01FF19] transition-colors duration-300">Cookie Policy</a></li>
          </ul>
        </div>
        
        <div class="animate-fade-in delay-300">
          <h3 class="font-semibold mb-4 text-white">Contact</h3>
          <ul class="space-y-3">
            <li class="text-white/70">support@bitchest.com</li>
            <li class="text-white/70">+1 (555) 123-4567</li>
          </ul>
        </div>
      </div>
      
      <div class="border-t border-white/10 pt-8 text-center">
        <p class="text-sm text-white/60">
          © 2024 BitChest. All rights reserved.
        </p>
      </div>
    </div>
  </footer>
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
</style>
