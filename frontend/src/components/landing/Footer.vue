<template>
  <footer class="relative bg-gradient-to-b from-gray-900 to-black pt-12 pb-8 border-t border-white/10">
    
    <!-- Background Effect -->
    <div class="absolute inset-0">
      <div class="absolute bottom-10 left-10 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl"></div>
    </div>

    <div class="relative container mx-auto px-4 md:px-6">
      
      <!-- Main Content -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-8 mb-8">
        
        <!-- Logo & Description -->
        <div>
          <div class="flex items-center gap-3 mb-4">
            <div class="w-36 h-14 rounded-lg  flex items-center justify-center">
          <img src="/assets/bitchest_logo.png" alt="BitChest" class="h-20" />
            </div>
            
          </div>
          <p class="text-gray-400 text-sm max-w-xs">
            Advanced cryptocurrency trading platform for modern investors.
          </p>
        </div>
        
        <!-- Quick Links -->
        <div>
          <h3 class="text-white font-semibold mb-4">Trading</h3>
          <ul class="space-y-2">
            <li><a href="#markets" class="text-gray-400 hover:text-white transition-colors text-sm">Markets</a></li>
            <li><a href="#features" class="text-gray-400 hover:text-white transition-colors text-sm">Features</a></li>
            <li><a href="#how-it-works" class="text-gray-400 hover:text-white transition-colors text-sm">How It Works</a></li>
          </ul>
        </div>
        
        <!-- Legal -->
        <div>
          <h3 class="text-white font-semibold mb-4">Legal</h3>
          <ul class="space-y-2">
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Privacy Policy</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Terms</a></li>
            <li><a href="#" class="text-gray-400 hover:text-white transition-colors text-sm">Cookie Policy</a></li>
          </ul>
        </div>
        
        <!-- Contact -->
        <div>
          <h3 class="text-white font-semibold mb-4">Contact</h3>
          <ul class="space-y-2">
            <li class="text-gray-400 text-sm">support@bitchest.com</li>
            <li class="text-gray-400 text-sm">+1 (555) 123-4567</li>
          </ul>
        </div>
      </div>
      
      <!-- Bottom Bar -->
      <div class="border-t border-white/10 pt-6 text-center">
        <p class="text-gray-500 text-sm">
          © 2026 BitChest. All rights reserved.
        </p>
        <p class="text-gray-600 text-xs mt-2">
          Cryptocurrency trading involves risk.
        </p>
      </div>
    </div>
  </footer>
</template>

<style scoped>
/* Simple fade-in animation for grid items */
.grid > div {
  animation: fadeIn 0.5s ease-out forwards;
  animation-fill-mode: both;
  opacity: 0;
}

.grid > div:nth-child(1) { animation-delay: 0.1s; }
.grid > div:nth-child(2) { animation-delay: 0.2s; }
.grid > div:nth-child(3) { animation-delay: 0.3s; }
.grid > div:nth-child(4) { animation-delay: 0.4s; }

@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>