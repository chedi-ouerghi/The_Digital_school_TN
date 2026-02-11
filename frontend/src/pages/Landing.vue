<script setup lang="ts">
import Header from '@/components/landing/Header.vue';
import { ref, onMounted, computed } from 'vue';

// Color palette from requirements
const colors = {
  primary: '#01FF19',    // Green accent
  secondary: '#35A7FF',  // Blue accent  
  accent: '#FF5964',     // Red accent (for borders)
  textPrimary: '#38618C', // Dark blue text
  textSecondary: '#5A6175', // Gray text
  textTertiary: '#8C94A8', // Light gray text
  background: '#FFFFFF',  // White background
  surface: '#F8FAFF',    // Light blue background
  border: '#E2E8F0',     // Light border
  borderAccent: '#FF5964' // Red border accent
}

const dashboardItems = ref([
  {
    title: 'Active Users',
    subtitle: 'Platform engagement',
    value: '2,458',
    trend: '+12% this month',
    icon: '👥',
    color: colors.primary,
  },
  {
    title: 'Total Volume',
    subtitle: 'Trading activity',
    value: '$4.2M',
    trend: '+8.5% weekly',
    icon: '📈',
    color: colors.secondary,
  },
  {
    title: 'Security Score',
    subtitle: 'Platform safety',
    value: '99.8%',
    trend: 'A+ rating',
    icon: '🔒',
    color: colors.accent,
  }
]);

const features = ref([
  {
    title: 'Enterprise-Grade Security',
    description: 'Bank-level encryption and multi-signature wallets protect every transaction',
    icon: '🔐'
  },
  {
    title: 'White-Label Ready',
    description: 'Seamlessly integrate into your ecosystem with full customization options',
    icon: '🏷️'
  },
  {
    title: 'Real-Time Analytics',
    description: 'Deep insights into market trends and user behavior with instant reporting',
    icon: '📊'
  }
]);

const stats = ref([
  { value: '99.8%', label: 'Uptime SLA', color: colors.primary },
  { value: '$4.2M', label: 'Daily Volume', color: colors.secondary },
  { value: '2.4K+', label: 'Active Users', color: colors.accent },
]);

const isLoading = ref(true);
const activeStat = ref(0);

onMounted(() => {
  // Simulate data loading
  setTimeout(() => {
    isLoading.value = false;
  }, 800);
  
  // Auto rotate stats
  setInterval(() => {
    activeStat.value = (activeStat.value + 1) % stats.value.length;
  }, 3000);
});
</script>

<template>
  <div class="min-h-screen overflow-hidden" :style="{ backgroundColor: colors.background }">
    <!-- Header -->
    <Header />
    
    <!-- Loading State -->
    <div v-if="isLoading" class="fixed inset-0 z-50 flex items-center justify-center" :style="{ backgroundColor: colors.background }">
      <div class="flex flex-col items-center gap-4">
        <div class="relative">
          <div class="w-16 h-16 rounded-full border-4" :style="{ borderColor: colors.border }"></div>
          <div class="absolute inset-0 w-16 h-16 rounded-full border-4 border-t-transparent animate-spin" :style="{ borderColor: colors.primary }"></div>
        </div>
        <p class="text-sm font-medium" :style="{ color: colors.textSecondary }">Loading Platform...</p>
      </div>
    </div>

    <!-- Hero Section -->
    <section class="pt-32 md:pt-40 pb-20 md:pb-24 px-4 sm:px-6 container mx-auto relative">
      <!-- Background accents -->
      <div class="absolute top-20 -right-20 w-72 h-72 md:w-96 md:h-96 rounded-full opacity-5" 
           :style="{ background: `radial-gradient(circle, ${colors.primary} 0%, transparent 70%)` }"></div>
      <div class="absolute bottom-0 -left-20 w-64 h-64 md:w-80 md:h-80 rounded-full opacity-5" 
           :style="{ background: `radial-gradient(circle, ${colors.secondary} 0%, transparent 70%)` }"></div>
      
      <div class="grid lg:grid-cols-2 gap-8 lg:gap-12 items-center relative z-10 max-w-7xl mx-auto">
        <!-- Left Content -->
        <div class="space-y-6 md:space-y-8">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full border transition-all duration-300 hover:scale-105"
               :style="{ 
                 backgroundColor: `${colors.primary}10`, 
                 borderColor: `${colors.primary}30`,
                 color: colors.primary,
                 borderWidth: '1.5px'
               }">
            <span class="w-2 h-2 rounded-full animate-pulse" :style="{ backgroundColor: colors.primary }"></span>
            <span class="text-xs font-bold tracking-wider">🚀 SECURE CRYPTO TRADING</span>
          </div>

          <h1 class="text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-black leading-tight" 
              :style="{ color: colors.textPrimary }">
            Secure Crypto Trading
            <br>
            <span class="relative inline-block">
              <span :style="{ color: colors.primary }">Made Simple</span>
              <div class="absolute -inset-2 rounded-lg opacity-20 blur-lg z-[-1]" 
                   :style="{ background: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})` }"></div>
            </span>
          </h1>
          
          <p class="text-lg md:text-xl leading-relaxed max-w-2xl font-semibold opacity-80" 
             :style="{ color: colors.textPrimary }">
            BitChest is a white-label platform prototype for secure crypto buying/selling, designed to integrate cryptocurrency into existing financial ecosystems with enterprise-grade security.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-3 md:gap-4 pt-4">
            <button class="group relative px-6 md:px-8 py-3 md:py-4 font-bold rounded-xl text-sm md:text-base transition-all duration-300 overflow-hidden hover:scale-105"
                    :style="{ 
                      background: `linear-gradient(135deg, ${colors.primary}, #00E617)`,
                      color: colors.textPrimary,
                      boxShadow: `0 10px 30px ${colors.primary}30`
                    }">
              <div class="absolute inset-0 opacity-0 group-hover:opacity-30 transition-all duration-300" 
                   :style="{ backgroundColor: colors.primary, filter: 'blur(20px)' }"></div>
              <span class="relative">Explore Demo</span>
            </button>
            
            <button class="px-6 md:px-8 py-3 md:py-4 font-bold rounded-xl text-sm md:text-base border-2 transition-all duration-300 group hover:scale-105"
                    :style="{ 
                      borderColor: colors.accent,
                      color: colors.accent,
                      backgroundColor: `${colors.accent}05`
                    }">
              <span class="relative">📚 Documentation</span>
            </button>
          </div>
        </div>
        
        <!-- Dashboard Card -->
        <div class="relative group">
          <div class="absolute -inset-1 rounded-3xl opacity-20 group-hover:opacity-30 transition-all duration-300 blur-lg"
               :style="{ background: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})` }"></div>
          
          <div class="relative rounded-3xl p-6 md:p-8 transition-all duration-300 border hover:border-opacity-50"
               :style="{ 
                 backgroundColor: `${colors.background}80`,
                 backdropFilter: 'blur(10px)',
                 border: `1.5px solid ${colors.borderAccent}40`,
                 color: colors.textPrimary
               }">
            
            <div class="flex justify-between items-center mb-6 md:mb-8">
              <h3 class="text-xl md:text-2xl font-black" :style="{ color: colors.textPrimary }">Platform Dashboard</h3>
              <div class="flex items-center gap-2 px-4 py-2 rounded-full animate-pulse"
                   :style="{ backgroundColor: `${colors.primary}20`, color: colors.primary }">
                <div class="w-2 h-2 rounded-full" :style="{ backgroundColor: colors.primary }"></div>
                <span class="text-sm font-bold">LIVE</span>
              </div>
            </div>
            
            <div class="space-y-4">
              <div v-for="item in dashboardItems" :key="item.title" 
                   class="flex items-center justify-between p-4 rounded-xl transition-all duration-300 hover:scale-102 hover:shadow-lg"
                   :style="{ 
                     backgroundColor: `${item.color}08`,
                     border: `1.5px solid ${colors.border}`,
                     borderLeft: `4px solid ${item.color}`
                   }">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-xl flex items-center justify-center text-xl"
                       :style="{ backgroundColor: `${item.color}20`, color: item.color }">
                    {{ item.icon }}
                  </div>
                  <div>
                    <h4 class="text-base font-bold" :style="{ color: colors.textPrimary }">{{ item.title }}</h4>
                    <p class="text-sm" :style="{ color: colors.textSecondary }">{{ item.subtitle }}</p>
                  </div>
                </div>
                <div class="text-right">
                  <div class="text-2xl font-black" :style="{ color: colors.textPrimary }">{{ item.value }}</div>
                  <div class="text-sm font-bold" :style="{ color: item.color }">{{ item.trend }}</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section class="py-16 md:py-32 px-4 sm:px-6 relative" :style="{ backgroundColor: colors.surface }">
      <div class="container mx-auto max-w-7xl">
        <div class="text-center mb-16">
          <h2 class="text-4xl md:text-5xl font-black mb-6" :style="{ color: colors.textPrimary }">
            Why Choose <span :style="{ color: colors.primary }">BitChest</span>
          </h2>
          <p class="text-xl max-w-3xl mx-auto opacity-80" :style="{ color: colors.textPrimary }">
            Engineered for performance, built for scale, trusted by thousands
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 mb-20">
          <div v-for="(feature, index) in features" :key="index"
               class="group p-8 rounded-2xl transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer"
               :style="{ 
                 backgroundColor: colors.background,
                 border: `2px solid ${colors.border}`,
                 boxShadow: `0 20px 40px ${colors.border}20`
               }">
            <div class="w-16 h-16 rounded-xl flex items-center justify-center text-2xl mb-6 transition-all duration-300 group-hover:scale-110"
                 :style="{ backgroundColor: `${colors.primary}15`, color: colors.primary }">
              {{ feature.icon }}
            </div>
            <h3 class="text-xl font-bold mb-4" :style="{ color: colors.textPrimary }">{{ feature.title }}</h3>
            <p class="opacity-80" :style="{ color: colors.textSecondary }">{{ feature.description }}</p>
          </div>
        </div>

        <!-- Stats Section -->
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          <!-- Left - Visual -->
          <div class="relative">
            <div class="relative w-full max-w-lg mx-auto">
              <!-- Trading Interface Mockup -->
              <div class="aspect-video rounded-2xl p-6 relative overflow-hidden"
                   :style="{ 
                     backgroundColor: colors.background,
                     border: `2px solid ${colors.borderAccent}`,
                     boxShadow: `0 25px 50px ${colors.borderAccent}15`
                   }">
                <!-- Chart -->
                <div class="h-32 mb-6 rounded-lg" 
                     :style="{ background: `linear-gradient(90deg, ${colors.primary}20, ${colors.secondary}20)` }"></div>
                
                <!-- Stats Indicators -->
                <div class="grid grid-cols-3 gap-4">
                  <div v-for="stat in stats" :key="stat.label"
                       class="text-center p-4 rounded-lg transition-all duration-300"
                       :class="{ 'scale-110': stat.value === stats[activeStat]?.value }"
                       :style="{ 
                         backgroundColor: stat.color === colors.accent ? `${stat.color}10` : `${stat.color}08`,
                         border: `1.5px solid ${stat.color === colors.accent ? colors.borderAccent : colors.border}`
                       }">
                    <div class="text-2xl font-bold mb-1" :style="{ color: stat.color }">{{ stat.value }}</div>
                    <div class="text-sm font-medium" :style="{ color: colors.textSecondary }">{{ stat.label }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Right - Content -->
          <div class="space-y-8">
            <h2 class="text-4xl md:text-5xl font-black leading-tight" :style="{ color: colors.textPrimary }">
              Real-Time 
              <span class="bg-gradient-to-r bg-clip-text text-transparent"
                    :style="{ backgroundImage: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})` }">
                Analytics
              </span>
              Dashboard
            </h2>

            <p class="text-lg md:text-xl leading-relaxed opacity-80" :style="{ color: colors.textPrimary }">
              Monitor your trading performance with precision. Our advanced analytics dashboard provides real-time insights into market trends, portfolio performance, and user behavior.
            </p>

            <div class="space-y-6">
              <div v-for="stat in stats" :key="stat.label" 
                   class="flex items-center justify-between p-4 rounded-xl transition-all duration-300 hover:bg-white"
                   :style="{ 
                     backgroundColor: `${colors.background}80`,
                     border: `1.5px solid ${colors.border}`,
                     borderLeft: `4px solid ${stat.color}`
                   }">
                <div class="flex items-center gap-4">
                  <div class="w-12 h-12 rounded-lg flex items-center justify-center text-lg font-bold"
                       :style="{ backgroundColor: `${stat.color}15`, color: stat.color }">
                    {{ stat.label === 'Uptime SLA' ? '⚡' : stat.label === 'Daily Volume' ? '💰' : '👥' }}
                  </div>
                  <div>
                    <h4 class="font-bold" :style="{ color: colors.textPrimary }">{{ stat.label }}</h4>
                    <p class="text-sm" :style="{ color: colors.textSecondary }">Live tracking and monitoring</p>
                  </div>
                </div>
                <div class="text-2xl font-black" :style="{ color: stat.color }">{{ stat.value }}</div>
              </div>
            </div>

            <button class="group px-8 py-4 font-bold rounded-xl text-lg border-2 transition-all duration-300 hover:scale-105"
                    :style="{ 
                      borderColor: colors.borderAccent,
                      color: colors.borderAccent,
                      backgroundColor: `${colors.borderAccent}05`
                    }">
              <span class="relative">View All Metrics →</span>
            </button>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 px-4 sm:px-6 relative overflow-hidden">
      <div class="absolute inset-0 opacity-5"
           :style="{ background: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})` }"></div>
      
      <div class="container mx-auto max-w-4xl text-center relative z-10">
        <div class="inline-block px-6 py-3 rounded-full mb-8" 
             :style="{ backgroundColor: `${colors.primary}15`, color: colors.primary }">
          <span class="font-bold">🚀 READY TO START</span>
        </div>
        
        <h2 class="text-4xl md:text-5xl font-black mb-6" :style="{ color: colors.textPrimary }">
          Start Trading with 
          <span :style="{ color: colors.primary }">Confidence</span>
        </h2>
        
        <p class="text-xl mb-10 max-w-2xl mx-auto opacity-80" :style="{ color: colors.textPrimary }">
          Join thousands of traders who trust BitChest for secure, reliable, and professional crypto trading.
        </p>
        
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
          <button class="px-8 py-4 font-bold rounded-xl text-lg transition-all duration-300 hover:scale-105"
                  :style="{ 
                    background: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})`,
                    color: colors.background,
                    boxShadow: `0 20px 40px ${colors.primary}30`
                  }">
            Get Started Free
          </button>
          
          <button class="px-8 py-4 font-bold rounded-xl text-lg border-2 transition-all duration-300 hover:scale-105"
                  :style="{ 
                    borderColor: colors.textPrimary,
                    color: colors.textPrimary,
                    backgroundColor: `${colors.textPrimary}05`
                  }">
            Schedule a Demo
          </button>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-12 md:py-16 px-4 sm:px-6 border-t" :style="{ borderColor: colors.borderAccent }">
      <div class="container mx-auto max-w-7xl">
        <!-- Main Footer Content -->
        <div class="grid md:grid-cols-2 lg:grid-cols-5 gap-8 md:gap-12 mb-12">
          <!-- Brand -->
          <div class="lg:col-span-2">
            <div class="flex items-center gap-3 mb-6">
              <div class="w-12 h-12 rounded-xl flex items-center justify-center text-2xl"
                   :style="{ background: `linear-gradient(135deg, ${colors.primary}, ${colors.secondary})`, color: colors.background }">
                ⚡
              </div>
              <div>
                <h2 class="text-2xl font-black" :style="{ color: colors.textPrimary }">
                  Bit<span :style="{ color: colors.primary }">CHEST</span>
                </h2>
                <p class="text-sm font-semibold opacity-70" :style="{ color: colors.textSecondary }">
                  White-label crypto trading platform
                </p>
              </div>
            </div>
            <p class="max-w-xs opacity-80" :style="{ color: colors.textSecondary }">
              Secure, scalable, and professional crypto trading solutions for the modern financial ecosystem.
            </p>
          </div>

          <!-- Links -->
          <div v-for="section in [
            { title: 'PRODUCT', links: ['Features', 'Pricing', 'Security', 'API Docs'] },
            { title: 'COMPANY', links: ['About', 'Careers', 'Contact'] },
            { title: 'LEGAL', links: ['Privacy', 'Terms', 'Compliance', 'Cookies'] }
          ]" :key="section.title">
            <h3 class="text-xs font-bold tracking-widest mb-6 opacity-70" :style="{ color: colors.primary }">
              {{ section.title }}
            </h3>
            <ul class="space-y-3">
              <li v-for="link in section.links" :key="link">
                <a href="#" class="text-sm font-medium transition-all duration-300 hover:translate-x-2 inline-block"
                   :style="{ color: colors.textPrimary }">
                  {{ link }}
                </a>
              </li>
            </ul>
          </div>
        </div>

        <!-- Bottom Section -->
        <div class="pt-8 border-t" :style="{ borderColor: `${colors.border}80` }">
          <div class="flex flex-col md:flex-row justify-between items-center gap-6">
            <p class="text-sm font-medium opacity-70" :style="{ color: colors.textSecondary }">
              © 2024 BitChest. All rights reserved.
            </p>
            
            <div class="flex gap-6">
              <a v-for="(social, index) in [
                { icon: '𝕏', color: colors.primary },
                { icon: 'f', color: colors.secondary },
                { icon: '▶', color: colors.accent },
                { icon: '✈️', color: colors.textPrimary }
              ]" :key="index" href="#" 
                 class="text-lg transition-all duration-300 hover:scale-125 hover:rotate-12"
                 :style="{ color: social.color }">
                {{ social.icon }}
              </a>
            </div>
            
            <p class="text-xs font-medium opacity-50" :style="{ color: colors.textSecondary }">
              Prototype for educational purposes
            </p>
          </div>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
* {
  font-family: -apple-system, BlinkMacSystemFont, 'Inter', 'Segoe UI', Roboto, sans-serif;
}

html {
  scroll-behavior: smooth;
}

.hover\:scale-102:hover {
  transform: scale(1.02);
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.animate-fadeInUp {
  animation: fadeInUp 0.6s ease-out;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 10px;
  height: 10px;
}

::-webkit-scrollbar-track {
  background: #F8FAFF;
  border-radius: 5px;
}

::-webkit-scrollbar-thumb {
  background: #E2E8F0;
  border-radius: 5px;
  border: 2px solid #F8FAFF;
}

::-webkit-scrollbar-thumb:hover {
  background: #01FF19;
}

/* Performance optimizations */
img {
  content-visibility: auto;
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Responsive utilities */
@media (max-width: 768px) {
  .container {
    padding-left: 1rem;
    padding-right: 1rem;
  }
  
  h1 {
    font-size: 2.5rem;
  }
  
  h2 {
    font-size: 2rem;
  }
}
</style>