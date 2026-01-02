<script setup lang="ts">
import Header from '@/components/landing/Header.vue';
import { ref } from 'vue';

const dashboardItems = ref([
  {
    title: 'Active Users',
    subtitle: 'Platform engagement',
    value: '2,458',
    trend: '+12% this month',
    icon: '👥',
  },
  {
    title: 'Total Volume',
    subtitle: 'Trading activity',
    value: '$4.2M',
    trend: '+8.5% weekly',
    icon: '📈',
  },
  {
    title: 'Security Score',
    subtitle: 'Platform safety',
    value: '99.8%',
    trend: 'A+ rating',
    icon: '🔒',
  }
])
</script>

<template>
  <div class="min-h-screen" style="background: linear-gradient(135deg, #FFFFFF 0%, #F8FAFF 100%)">
    <!-- Header - NO CHANGES -->
    <Header />

    <!-- Hero Section -->
    <section class="pt-40 pb-24 px-6 container mx-auto relative overflow-hidden">
      <div class="absolute -top-40 -right-40 w-96 h-96 rounded-full opacity-10" style="background: radial-gradient(circle, #01FF19 0%, transparent 70%); filter: blur(60px)"></div>
      <div class="absolute bottom-0 -left-40 w-80 h-80 rounded-full opacity-10" style="background: radial-gradient(circle, #35A7FF 0%, transparent 70%); filter: blur(60px)"></div>
      
      <div class="grid lg:grid-cols-2 gap-16 items-center relative z-10">
        <!-- Left Side -->
        <div class="space-y-8">
          <div class="inline-block px-4 py-2 rounded-full text-xs font-bold tracking-widest" style="background: rgba(1, 255, 25, 0.1); color: #01FF19; border: 1px solid rgba(1, 255, 25, 0.3)">
            🚀 SECURE CRYPTO TRADING
          </div>

          <h1 class="text-6xl lg:text-7xl font-black leading-tight" style="color: #38618C">
            Secure Crypto Trading
            <span class="relative inline-block" style="color: #01FF19">
              Made Simple
              <div class="absolute -inset-2 rounded-lg opacity-20" style="background: linear-gradient(135deg, #01FF19, #35A7FF); filter: blur(12px); z-index: -1"></div>
            </span>
          </h1>
          
          <p class="text-xl font-semibold leading-relaxed max-w-2xl" style="color: #38618C; opacity: 0.8">
            BitChest is a white-label platform prototype for secure crypto buying/selling, designed to integrate cryptocurrency into existing financial ecosystems with enterprise-grade security.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 pt-4">
            <button class="px-8 py-4 font-bold rounded-xl text-lg transition-all duration-300 group relative overflow-hidden" style="background: linear-gradient(135deg, #01FF19, #00E617); color: #38618C; box-shadow: 0 20px 40px rgba(1, 255, 25, 0.2)">
              <div class="absolute inset-0 opacity-0 group-hover:opacity-30 transition-all" style="background: #01FF19; filter: blur(20px)"></div>
              <span class="relative">Explore Demo</span>
            </button>
            <button class="px-8 py-4 font-bold rounded-xl text-lg border-2 transition-all duration-300 group backdrop-blur-sm hover:scale-105" style="border-color: #FF5964; color: #FF5964; background: rgba(255, 89, 100, 0.05)">
              📚 Documentation
            </button>
          </div>
        </div>
        
        <!-- Right Side - Dashboard Card -->
        <div class="relative group">
          <div class="absolute -inset-1 rounded-3xl opacity-20 group-hover:opacity-40 transition-all duration-300" style="background: linear-gradient(135deg, #01FF19, #35A7FF); filter: blur(20px)"></div>
          <div class="relative bg-white/60 backdrop-blur-2xl rounded-3xl border border-white/40 p-8 hover:border-white/60 transition-all">
            <div class="flex justify-between items-center mb-8">
              <h3 class="font-black text-2xl" style="color: #38618C">Platform Dashboard</h3>
              <div class="flex items-center gap-2 px-4 py-2 rounded-full animate-pulse" style="background: rgba(1, 255, 25, 0.2); color: #01FF19">
                <div class="w-2 h-2 rounded-full" style="background: #01FF19"></div>
                <span class="text-sm font-bold">LIVE</span>
              </div>
            </div>
            
            <div class="space-y-4">
              <div v-for="item in dashboardItems" :key="item.title" class="flex items-center justify-between p-4 rounded-xl transition-all" style="background: rgba(1, 255, 25, 0.05); border: 1px solid rgba(53, 167, 255, 0.1)">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" style="background: rgba(1, 255, 25, 0.2)">
                    {{ item.icon }}
                  </div>
                  <div>
                    <h4 class="font-bold" style="color: #38618C">{{ item.title }}</h4>
                    <p class="text-sm" style="color: #38618C; opacity: 0.6">{{ item.subtitle }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-black" style="color: #38618C">{{ item.value }}</div>
                  <div class="text-sm font-bold" style="color: #01FF19">{{ item.trend }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Project Context Section - Minimalist & Attractive -->
    <section class="py-32 px-6 relative overflow-hidden">
      <div class="absolute top-20 right-0 w-96 h-96 rounded-full opacity-5" style="background: radial-gradient(circle, #01FF19 0%, transparent 70%); filter: blur(60px)"></div>
      <div class="absolute bottom-0 left-0 w-80 h-80 rounded-full opacity-5" style="background: radial-gradient(circle, #35A7FF 0%, transparent 70%); filter: blur(60px)"></div>

      <div class="container mx-auto relative z-10">
        <div class="grid lg:grid-cols-2 gap-24 items-center max-w-6xl mx-auto">
          
          <!-- Left - Visual Element -->
          <div class="flex items-center justify-center h-96 relative">
            <div class="relative w-64 h-64 flex items-center justify-center">
              <!-- Animated background -->
              <div class="absolute inset-0 rounded-3xl opacity-20" style="background: linear-gradient(135deg, #01FF19, #35A7FF); filter: blur(40px); animation: pulse 4s ease-in-out infinite"></div>
              
              <!-- Rock-like shape with icon -->
              <div class="relative z-10 text-center">
                <div class="text-9xl mb-4 opacity-60" style="filter: grayscale(50%); animation: float 6s ease-in-out infinite">🪨</div>
              </div>
            </div>
          </div>

          <!-- Right - Content -->
          <div class="space-y-12">
            <div>
              <h2 class="text-5xl font-black mb-8 leading-tight" style="color: #38618C">
                The Future of 
                <span style="background: linear-gradient(135deg, #01FF19, #35A7FF); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text">Crypto Trading</span>
              </h2>

              <p class="text-xl font-semibold leading-relaxed mb-8" style="color: #38618C; opacity: 0.8">
                BitChest represents a paradigm shift in cryptocurrency trading. We've engineered a white-label platform that combines security, simplicity, and scalability into one comprehensive solution.
              </p>

              <div class="space-y-6">
                <div class="flex items-start gap-4 group">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl" style="background: rgba(1, 255, 25, 0.2); color: #01FF19">
                    ✓
                  </div>
                  <div>
                    <h3 class="text-lg font-bold mb-2" style="color: #38618C">Enterprise-Grade Security</h3>
                    <p class="font-semibold" style="color: #38618C; opacity: 0.7">Bank-level encryption and multi-signature wallets protect every transaction</p>
                  </div>
                </div>

                <div class="flex items-start gap-4 group">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl" style="background: rgba(1, 255, 25, 0.2); color: #01FF19">
                    ✓
                  </div>
                  <div>
                    <h3 class="text-lg font-bold mb-2" style="color: #38618C">White-Label Ready</h3>
                    <p class="font-semibold" style="color: #38618C; opacity: 0.7">Seamlessly integrate into your ecosystem with full customization options</p>
                  </div>
                </div>

                <div class="flex items-start gap-4 group">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl" style="background: rgba(1, 255, 25, 0.2); color: #01FF19">
                    ✓
                  </div>
                  <div>
                    <h3 class="text-lg font-bold mb-2" style="color: #38618C">Real-Time Analytics</h3>
                    <p class="font-semibold" style="color: #38618C; opacity: 0.7">Deep insights into market trends and user behavior with instant reporting</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- Stats highlight -->
            <div class="grid grid-cols-3 gap-4 pt-8">
              <div class="group bg-white/50 backdrop-blur-sm rounded-xl p-6 border border-white/40 hover:border-white/60 transition-all" style="border-color: rgba(1, 255, 25, 0.2)">
                <div class="text-3xl font-black mb-2" style="color: #01FF19">99.8%</div>
                <div class="text-sm font-bold" style="color: #38618C; opacity: 0.7">Uptime SLA</div>
              </div>
              <div class="group bg-white/50 backdrop-blur-sm rounded-xl p-6 border border-white/40 hover:border-white/60 transition-all" style="border-color: rgba(35, 167, 255, 0.2)">
                <div class="text-3xl font-black mb-2" style="color: #35A7FF">$4.2M</div>
                <div class="text-sm font-bold" style="color: #38618C; opacity: 0.7">Daily Volume</div>
              </div>
              <div class="group bg-white/50 backdrop-blur-sm rounded-xl p-6 border border-white/40 hover:border-white/60 transition-all" style="border-color: rgba(255, 89, 100, 0.2)">
                <div class="text-3xl font-black mb-2" style="color: #FF5964">2.4K+</div>
                <div class="text-sm font-bold" style="color: #38618C; opacity: 0.7">Active Users</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t py-16 px-6" style="background: rgba(56, 97, 140, 0.95); border-color: rgba(255, 255, 255, 0.1); backdrop-filter: blur(20px)">
      <div class="container mx-auto">
        <!-- Main Footer Content -->
        <div class="grid md:grid-cols-4 gap-12 mb-12 max-w-6xl">
          <!-- Brand -->
          <div>
            <div class="flex items-center gap-3 mb-4">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl" style="background: linear-gradient(135deg, #01FF19, #35A7FF)">
                ⚡
              </div>
              <div>
                <h2 class="text-xl font-black text-white">Bit<span style="color: #01FF19">CHEST</span></h2>
              </div>
            </div>
            <p class="font-semibold" style="color: rgba(255, 255, 255, 0.6)">White-label crypto trading platform for the future</p>
          </div>

          <!-- Product -->
          <div>
            <h3 class="text-sm font-bold tracking-widest mb-6" style="color: #01FF19">PRODUCT</h3>
            <ul class="space-y-3">
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Features</a></li>
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Pricing</a></li>
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Security</a></li>
            </ul>
          </div>

          <!-- Company -->
          <div>
            <h3 class="text-sm font-bold tracking-widest mb-6" style="color: #01FF19">COMPANY</h3>
            <ul class="space-y-3">
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">About</a></li>
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Blog</a></li>
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Careers</a></li>
            </ul>
          </div>

          <!-- Legal -->
          <div>
            <h3 class="text-sm font-bold tracking-widest mb-6" style="color: #01FF19">LEGAL</h3>
            <ul class="space-y-3">
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Privacy</a></li>
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Terms</a></li>
              <li><a href="#" class="font-semibold transition-colors" style="color: #35A7FF; opacity: 0.8" @mouseenter="(e) => (e.target as HTMLElement).style.opacity = '1'" @mouseleave="(e) => (e.target as HTMLElement).style.opacity = '0.8'">Compliance</a></li>
            </ul>
          </div>
        </div>

        <!-- Bottom Section -->
        <div class="border-t pt-8" style="border-color: rgba(255, 255, 255, 0.1)">
          <div class="flex flex-col md:flex-row justify-between items-center gap-8">
            <p class="font-semibold" style="color: rgba(255, 255, 255, 0.6)">© 2024 BitChest. All rights reserved.</p>
            
            <div class="flex gap-8">
              <a href="#" class="text-lg transition-colors hover:scale-125" style="color: #01FF19">𝕏</a>
              <a href="#" class="text-lg transition-colors hover:scale-125" style="color: #35A7FF">f</a>
              <a href="#" class="text-lg transition-colors hover:scale-125" style="color: #FF5964">▶</a>
              <a href="#" class="text-lg transition-colors hover:scale-125" style="color: #01FF19">✈️</a>
            </div>
            
            <p class="text-sm font-semibold" style="color: rgba(255, 255, 255, 0.5)">Prototype for educational purposes</p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
* {
  font-family: 'Celias', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
}

html {
  scroll-behavior: smooth;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-30px); }
}

@keyframes pulse {
  0%, 100% { opacity: 0.2; transform: scale(1); }
  50% { opacity: 0.4; transform: scale(1.05); }
}

.animate-float {
  animation: float 6s ease-in-out infinite;
}

::-webkit-scrollbar {
  width: 8px;
}

::-webkit-scrollbar-track {
  background: transparent;
}

::-webkit-scrollbar-thumb {
  background: #01FF19;
  border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
  background: #35A7FF;
}
</style>