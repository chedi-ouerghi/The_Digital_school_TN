<script setup lang="ts">
import Header from '@/components/landing/Header.vue';
import { ref, onMounted, computed } from 'vue';
import { colors, shadows, radius } from '@/config/designSystem';
import {
    TrendingUp,
    ShieldCheck,
    Users,
    Clock,
    ArrowRight,
    CheckCircle2,
    BarChart3,
    Layers,
    Lock,
    Zap
} from 'lucide-vue-next';

// Dashboard metrics
const dashboardItems = [
  { 
    title: 'Active Users', 
    value: '2,458', 
    trend: '+12%', 
    icon: Users,
    color: colors.primary[500],
    bgColor: colors.primary[50]
  },
  { 
    title: 'Volume Total', 
    value: '$4.2M', 
    trend: '+8.5%', 
    icon: TrendingUp,
    color: colors.secondary[500],
    bgColor: colors.secondary[50]
  },
  { 
    title: 'Security Score', 
    value: '99.8%', 
    trend: 'A+', 
    icon: ShieldCheck,
    color: colors.text.primary,
    bgColor: colors.slate[100]
  }
];

const features = [
  {
    title: 'Enterprise Security',
    description: 'Bank-grade encryption, multi-signature wallets, and 2FA authentication for maximum protection.',
    icon: ShieldCheck,
    iconBg: colors.primary[100],
    iconColor: colors.primary[600],
    details: [
      { text: 'OWASP-compliant security', icon: CheckCircle2 },
      { text: 'End-to-end encryption', icon: Lock },
      { text: 'Mandatory multi-signature', icon: Zap }
    ]
  },
  {
    title: 'Advanced Trading',
    description: 'Optimized algorithms for buying and selling with automatic profit and loss calculations.',
    icon: BarChart3,
    iconBg: colors.secondary[100],
    iconColor: colors.secondary[600],
    details: [
      { text: 'Buy in three clicks', icon: CheckCircle2 },
      { text: 'Real-time calculations', icon: Clock },
      { text: 'Complete history', icon: Layers }
    ]
  },
  {
    title: 'Analytics & Rapports',
    description: 'Visualize your performance with interactive charts and detailed, exportable reports.',
    icon: TrendingUp,
    iconBg: colors.primary[100],
    iconColor: colors.primary[600],
    details: [
      { text: 'Performance charts', icon: CheckCircle2 },
      { text: 'Exportable PDF reports', icon: Layers },
      { text: 'Real-time key metrics', icon: Zap }
    ]
  }
];

const stats = [
  { value: '99.8%', label: 'Uptime', icon: TrendingUp, color: colors.primary[500] },
  { value: '< 0.1s', label: 'Execution', icon: Zap, color: colors.secondary[500] },
  { value: '100%', label: 'Secure', icon: ShieldCheck, color: colors.primary[500] },
];

const isLoading = ref(true);
const animatedVolume = ref(0);
const animatedUsers = ref(0);

// Sample chart data
const chartData = [65, 72, 68, 75, 82, 78, 85, 80, 88, 92, 87, 95];
const chartMonths = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

onMounted(() => {
  setTimeout(() => {
    isLoading.value = false;
    startAnimations();
  }, 600);
});

const startAnimations = () => {
  const duration = 2000;
  const steps = 60;
  const volumeStep = 4200000 / steps;
  const usersStep = 2458 / steps;
  
  let step = 0;
  const timer = setInterval(() => {
    step++;
    animatedVolume.value = Math.min(volumeStep * step, 4200000);
    animatedUsers.value = Math.min(usersStep * step, 2458);
    
    if (step >= steps) clearInterval(timer);
  }, duration / steps);
};

const formatVolume = (value: number) => {
  return `$${(value / 1000000).toFixed(1)}M`;
};

const formatUsers = (value: number) => {
  return Math.round(value).toLocaleString();
};

// Chart SVG path
const chartPath = computed(() => {
  const max = Math.max(...chartData);
  const min = Math.min(...chartData);
  const range = max - min;
  const height = 100;
  const width = 500;
  const padding = 10;
  
  const points = chartData.map((value, index) => {
    const x = (index / (chartData.length - 1)) * (width - padding * 2) + padding;
    const y = height - padding - ((value - min) / range) * (height - padding * 2);
    return `${x},${y}`;
  });
  
  return `M ${points.join(' L ')}`;
});

const chartAreaPath = computed(() => {
  const max = Math.max(...chartData);
  const min = Math.min(...chartData);
  const range = max - min;
  const height = 100;
  const width = 500;
  const padding = 10;
  
  const points = chartData.map((value, index) => {
    const x = (index / (chartData.length - 1)) * (width - padding * 2) + padding;
    const y = height - padding - ((value - min) / range) * (height - padding * 2);
    return `${x},${y}`;
  });
  
  return `M ${padding},${height - padding} L ${points.join(' L ')} L ${width - padding},${height - padding} Z`;
});
</script>

<template>
  <div class="min-h-screen bg-background font-sans antialiased">
    <Header />
    
    <!-- Loading -->
    <div v-if="isLoading" class="fixed inset-0 z-[100] flex items-center justify-center bg-background">
      <div class="text-center">
        <div 
          class="w-12 h-12 mx-auto mb-4 border-3 rounded-full animate-spin"
          :style="{
            borderColor: `${colors.primary[500]}30`,
            borderTopColor: colors.primary[500]
          }"
        />
        <p class="text-sm font-medium" :style="{ color: colors.text.secondary }">
          Loading...
        </p>
      </div>
    </div>

    <!-- Hero Section -->
    <section class="relative pt-32 md:pt-40 pb-20 md:pb-28 overflow-hidden">
      <div class="container mx-auto px-4 sm:px-6 max-w-7xl">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
          
          <!-- Left Content -->
          <div class="space-y-8">
            <!-- Badge -->
            <span 
              class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-xs font-bold tracking-wider border"
              :style="{
                backgroundColor: `${colors.primary[500]}10`,
                borderColor: `${colors.primary[500]}30`,
                color: colors.primary[600]
              }"
            >
              <span 
                class="w-1.5 h-1.5 rounded-full animate-pulse"
                :style="{ backgroundColor: colors.primary[500] }"
              />
              PREMIUM CRYPTO TRADING PLATFORM
            </span>
            
            <!-- Hero Title -->
            <h1 
              class="text-5xl md:text-6xl lg:text-7xl font-extrabold leading-tight tracking-tight"
              :style="{ color: colors.text.primary }"
            >
              Trade your 
              <span 
                class="relative inline-block"
                :style="{ color: colors.primary[500] }"
              >
                Cryptos
                <span 
                  class="absolute bottom-0 left-0 w-full h-3 -mb-2 opacity-30 blur-lg"
                  :style="{
                    background: `linear-gradient(90deg, ${colors.primary[400]}, ${colors.primary[200]})`,
                    borderRadius: radius.full
                  }"
                />
              </span>
              <br />
              <span class="relative">
                with Total
                <span class="text-gradient bg-gradient-to-r from-primary-400 to-primary-600">
                  Security
                </span>
              </span>
            </h1>
            
            <!-- Subtitle -->
            <p 
              class="text-lg md:text-xl leading-relaxed max-w-lg"
              :style="{ color: colors.text.secondary }"
            >
              BitChest is a complete cryptocurrency management and trading platform. 
              Buy, sell, and track your investments with professional tools built to the highest SaaS standards.
            </p>
            
            <!-- CTA Buttons -->
            <div class="flex flex-wrap gap-4 pt-4">
              <router-link
                to="/signin"
                class="inline-flex items-center gap-2 px-8 py-4 font-semibold rounded-2xl transition-all duration-200 hover:scale-[1.02] hover:shadow-lg active:scale-[0.98]"
                :style="{
                  background: `linear-gradient(135deg, ${colors.primary[500]}, ${colors.primary[600]})`,
                  color: colors.white,
                  boxShadow: shadows.primary.md
                }"
              >
                Get Started
                <ArrowRight class="w-5 h-5" />
              </router-link>
              
              <a
                href="#features"
                class="inline-flex items-center gap-2 px-8 py-4 font-semibold rounded-2xl border-2 transition-all duration-200 hover:scale-[1.02] hover:bg-surface"
                :style="{
                  borderColor: colors.text.primary,
                  color: colors.text.primary
                }"
              >
                Explore Features
              </a>
            </div>
            
            <!-- Trust Indicators -->
            <div class="flex items-center gap-8 pt-6">
              <div class="flex -space-x-2">
                <div 
                  v-for="i in 5"
                  :key="i"
                  class="w-10 h-10 rounded-full border-2 border-background flex items-center justify-center text-xs font-bold ring-2 ring-background"
                  :style="{
                    backgroundColor: i % 2 === 0 ? colors.primary[100] : colors.secondary[100],
                    color: i % 2 === 0 ? colors.primary[700] : colors.secondary[700]
                  }"
                >
                  {{ ['JD', 'MK', 'AL', 'SF', 'RT'][i-1] }}
                </div>
              </div>
              
              <div class="flex items-center gap-4">
                <div class="flex items-center gap-1.5">
                  <span class="text-lg">✨</span>
                  <span 
                    class="text-sm font-bold"
                    :style="{ color: colors.text.primary }"
                  >
                    +2.4K
                  </span>
                </div>
                <span 
                  class="text-sm"
                  :style="{ color: colors.text.tertiary }"
                >
                  active users
                </span>
              </div>
            </div>
          </div>
          
          <!-- Right - Dashboard Preview -->
          <div class="relative">
            <!-- Background Glow -->
            <div 
              class="absolute inset-0 rounded-3xl opacity-20 blur-3xl"
              :style="{
                background: `linear-gradient(135deg, ${colors.primary[400]}, ${colors.secondary[400]})`
              }"
            />
            
            <!-- Dashboard Card -->
            <div 
              class="relative rounded-3xl p-6 border backdrop-blur-sm"
              :style="{
                backgroundColor: `${colors.background}E6`,
                borderColor: colors.border.light,
                boxShadow: shadows.lg
              }"
            >
              <div class="flex justify-between items-center mb-6">
                <div>
                  <h3 
                    class="text-lg font-bold"
                    :style="{ color: colors.text.primary }"
                  >
                    Portfolio Overview
                  </h3>
                  <p 
                    class="text-xs mt-1"
                    :style="{ color: colors.text.tertiary }"
                  >
                    Real-time data
                  </p>
                </div>
                <span 
                  class="text-xs font-medium px-3 py-1.5 rounded-xl"
                  :style="{
                    backgroundColor: `${colors.primary[500]}15`,
                    color: colors.primary[600]
                  }"
                >
                  Demo
                </span>
              </div>
              
              <!-- Mini Chart -->
              <div class="h-32 mb-6">
                <svg viewBox="0 0 500 100" class="w-full h-full" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="heroGrad" x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" :stop-color="colors.primary[500]" stop-opacity="0.25" />
                      <stop offset="100%" :stop-color="colors.primary[500]" stop-opacity="0.0" />
                    </linearGradient>
                  </defs>
                  <path :d="chartAreaPath" fill="url(#heroGrad)" />
                  <path :d="chartPath" fill="none" :stroke="colors.primary[500]" stroke-width="2.5" stroke-linecap="round" />
                </svg>
              </div>
              
              <!-- Stats -->
              <div class="grid grid-cols-3 gap-3">
                <div 
                  class="text-center p-4 rounded-2xl border"
                  :style="{ borderColor: colors.border.light }"
                >
                  <p 
                    class="text-xs font-medium mb-2"
                    :style="{ color: colors.text.tertiary }"
                  >
                    Volume 24h
                  </p>
                  <p 
                    class="text-xl font-bold"
                    :style="{ color: colors.text.primary }"
                  >
                    {{ formatVolume(animatedVolume) }}
                  </p>
                </div>
                <div 
                  class="text-center p-4 rounded-2xl border"
                  :style="{ borderColor: colors.border.light }"
                >
                  <p 
                    class="text-xs font-medium mb-2"
                    :style="{ color: colors.text.tertiary }"
                  >
                    Users
                  </p>
                  <p 
                    class="text-xl font-bold"
                    :style="{ color: colors.text.primary }"
                  >
                    {{ formatUsers(animatedUsers) }}
                  </p>
                </div>
                <div 
                  class="text-center p-4 rounded-2xl border"
                  :style="{ borderColor: colors.border.light }"
                >
                  <p 
                    class="text-xs font-medium mb-2"
                    :style="{ color: colors.text.tertiary }"
                  >
                    Security
                  </p>
                  <p 
                    class="text-xl font-bold"
                    :style="{ color: colors.primary[600] }"
                  >
                    99.8%
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Features Section -->
    <section 
      id="features"
      class="py-20 md:py-28"
      :style="{ backgroundColor: colors.surface }"
    >
      <div class="container mx-auto px-4 sm:px-6 max-w-7xl">
        <div class="text-center max-w-3xl mx-auto mb-16">
          <span 
            class="inline-block px-5 py-1.5 rounded-full text-xs font-bold tracking-wider mb-6"
            :style="{
              backgroundColor: `${colors.primary[500]}15`,
              color: colors.primary[600]
            }"
          >
            FEATURES
          </span>
          
          <h2 
            class="text-4xl md:text-5xl font-extrabold leading-tight mb-5"
            :style="{ color: colors.text.primary }"
          >
            A Complete 
            <span 
              class="relative inline-block"
              :style="{ color: colors.primary[500] }"
            >
              Platform
              <span 
                class="absolute bottom-0 left-0 w-full h-2 -mb-1 opacity-30 blur-lg"
                :style="{
                  background: `linear-gradient(90deg, ${colors.primary[400]}, ${colors.primary[200]})`,
                  borderRadius: radius.full
                }"
              />
            </span>
          </h2>
          
          <p 
            class="text-lg md:text-xl"
            :style="{ color: colors.text.secondary }"
          >
            Everything you need to manage your cryptocurrency professionally and securely.
          </p>
        </div>
        
        <div class="grid md:grid-cols-3 gap-6">
          <div 
            v-for="feature in features"
            :key="feature.title"
            class="group p-8 rounded-3xl transition-all duration-300 hover:-translate-y-2 hover:shadow-xl bg-background border"
            :style="{
              borderColor: colors.border.light,
              boxShadow: shadows.glass.sm
            }"
          >
            <!-- Feature Icon -->
            <div 
              class="w-14 h-14 rounded-2xl flex items-center justify-center text-2xl mb-6 transition-all duration-300 group-hover:scale-110"
              :style="{
                backgroundColor: feature.iconBg,
                color: feature.iconColor
              }"
            >
              <component :is="feature.icon" class="w-7 h-7" />
            </div>
            
            <!-- Feature Title -->
            <h3 
              class="text-xl font-bold mb-4"
              :style="{ color: colors.text.primary }"
            >
              {{ feature.title }}
            </h3>
            
            <!-- Feature Description -->
            <p 
              class="mb-6 text-sm leading-relaxed"
              :style="{ color: colors.text.secondary }"
            >
              {{ feature.description }}
            </p>
            
            <!-- Feature Details -->
            <ul class="space-y-3">
              <li 
                v-for="detail in feature.details"
                :key="detail.text"
                class="flex items-center gap-3 text-sm font-medium"
                :style="{ color: colors.text.tertiary }"
              >
                <component 
                  :is="detail.icon" 
                  class="w-4 h-4"
                  :style="{ color: colors.primary[500] }"
                />
                <span>{{ detail.text }}</span>
              </li>
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Analytics Section -->
    <section id="analytics" class="py-20 md:py-28 bg-background">
      <div class="container mx-auto px-4 sm:px-6 max-w-7xl">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
          
          <!-- Left - Chart -->
          <div>
            <div 
              class="rounded-3xl p-6 border"
              :style="{
                borderColor: colors.border.light,
                backgroundColor: colors.surface
              }"
            >
              <div class="flex items-center justify-between mb-6">
                <div>
                  <h4 
                    class="font-bold"
                    :style="{ color: colors.text.primary }"
                  >
                    Portfolio Performance
                  </h4>
                  <p 
                    class="text-xs mt-1"
                    :style="{ color: colors.text.tertiary }"
                  >
                    Last 12 months
                  </p>
                </div>
                <span 
                  class="text-xs font-medium px-3 py-1.5 rounded-xl"
                  :style="{
                    backgroundColor: `${colors.secondary[500]}15`,
                    color: colors.secondary[600]
                  }"
                >
                  +24.5% Total
                </span>
              </div>
              
              <!-- Main Chart -->
              <div class="h-48 mb-6">
                <svg viewBox="0 0 500 120" class="w-full h-full" preserveAspectRatio="none">
                  <defs>
                    <linearGradient id="analyticsGrad" x1="0" x2="0" y1="0" y2="1">
                      <stop offset="0%" :stop-color="colors.secondary[500]" stop-opacity="0.2" />
                      <stop offset="100%" :stop-color="colors.secondary[500]" stop-opacity="0.0" />
                    </linearGradient>
                  </defs>
                  <path
d="M0,90 L 40,85 L 80,70 L 120,75 L 160,55 L 200,60 L 240,45 L 280,50 L 320,35 L 360,40 L 400,25 L 440,30 L 480,20 L 500,15 L 500,120 L 0,120 Z" 
                        fill="url(#analyticsGrad)" />
                  <path
d="M0,90 L 40,85 L 80,70 L 120,75 L 160,55 L 200,60 L 240,45 L 280,50 L 320,35 L 360,40 L 400,25 L 440,30 L 480,20 L 500,15" 
                        fill="none" :stroke="colors.secondary[500]" stroke-width="2.5" stroke-linecap="round" />
                </svg>
              </div>
              
              <!-- Months -->
              <div class="flex justify-between text-xs font-medium mb-6" :style="{ color: colors.text.tertiary }">
                <span v-for="month in chartMonths.slice(0, 6)" :key="month">{{ month }}</span>
              </div>
              
              <!-- Asset Distribution -->
              <div class="space-y-4">
                <h5 
                  class="text-sm font-bold mb-4"
                  :style="{ color: colors.text.primary }"
                >
                  Asset Allocation
                </h5>
                <div 
                  v-for="asset in [
                    { name: 'Bitcoin (BTC)', percent: 45, color: colors.primary[500] },
                    { name: 'Ethereum (ETH)', percent: 30, color: colors.secondary[500] },
                    { name: 'Other', percent: 25, color: colors.slate[600] }
                  ]" 
                  :key="asset.name" 
                  class="flex items-center gap-4"
                >
                  <span 
                    class="text-sm font-medium w-28"
                    :style="{ color: colors.text.secondary }"
                  >
                    {{ asset.name }}
                  </span>
                  <div 
                    class="flex-1 h-2 rounded-full"
                    :style="{ backgroundColor: colors.border.light }"
                  >
                    <div 
                      class="h-2 rounded-full transition-all duration-1000"
                      :style="{
                        width: `${asset.percent}%`,
                        backgroundColor: asset.color
                      }"
                    />
                  </div>
                  <span 
                    class="text-sm font-bold w-12 text-right"
                    :style="{ color: colors.text.primary }"
                  >
                    {{ asset.percent }}%
                  </span>
                </div>
              </div>
            </div>
          </div>
          
          <!-- Right - Content -->
          <div class="space-y-8">
            <span 
              class="inline-block px-5 py-1.5 rounded-full text-xs font-bold tracking-wider"
              :style="{
                backgroundColor: `${colors.secondary[500]}15`,
                color: colors.secondary[600]
              }"
            >
              ADVANCED ANALYTICS
            </span>
            
            <h2 
              class="text-4xl md:text-5xl font-extrabold leading-tight"
              :style="{ color: colors.text.primary }"
            >
              Analytics & 
              <span 
                class="relative inline-block"
                :style="{ color: colors.secondary[500] }"
              >
                Reports
                <span 
                  class="absolute bottom-0 left-0 w-full h-2 -mb-1 opacity-30 blur-lg"
                  :style="{
                    background: `linear-gradient(90deg, ${colors.secondary[400]}, ${colors.secondary[200]})`,
                    borderRadius: radius.full
                  }"
                />
              </span>
            </h2>
            
            <p 
              class="text-lg leading-relaxed"
              :style="{ color: colors.text.secondary }"
            >
              Track your performance with detailed charts. Analyze your investments, 
              view your portfolio growth, and export your reports.
            </p>
            
            <!-- Metrics -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4">
              <div 
                v-for="item in dashboardItems"
                :key="item.title"
                class="p-5 rounded-2xl border"
                :style="{
                  borderColor: colors.border.light,
                  borderLeftWidth: '4px',
                  borderLeftColor: item.color
                }"
              >
                <div class="flex items-center gap-4 mb-3">
                  <div 
                    class="w-10 h-10 rounded-xl flex items-center justify-center"
                    :style="{ backgroundColor: item.bgColor }"
                  >
                    <component :is="item.icon" class="w-5 h-5" :style="{ color: item.color }" />
                  </div>
                  <span 
                    class="text-sm font-medium"
                    :style="{ color: colors.text.secondary }"
                  >
                    {{ item.title }}
                  </span>
                </div>
                <div class="flex items-end justify-between">
                  <span 
                    class="text-2xl font-black"
                    :style="{ color: colors.text.primary }"
                  >
                    {{ item.value }}
                  </span>
                  <span 
                    class="text-sm font-bold"
                    :style="{ color: item.color }"
                  >
                    {{ item.trend }}
                  </span>
                </div>
              </div>
            </div>
            
            <!-- Performance Stats -->
            <div class="grid grid-cols-3 gap-3 pt-4">
              <div 
                v-for="stat in stats" 
                :key="stat.label"
                class="text-center p-4 rounded-2xl border"
                :style="{ borderColor: colors.border.light }"
              >
                <div class="flex justify-center mb-2">
                  <component 
                    :is="stat.icon" 
                    class="w-6 h-6"
                    :style="{ color: stat.color }"
                  />
                </div>
                <div 
                  class="text-xl font-bold"
                  :style="{ color: colors.text.primary }"
                >
                  {{ stat.value }}
                </div>
                <div 
                  class="text-xs font-medium mt-1"
                  :style="{ color: colors.text.tertiary }"
                >
                  {{ stat.label }}
                </div>
              </div>
            </div>
            
            <!-- CTA Button -->
            <router-link
              to="/signin"
              class="inline-flex items-center gap-2 px-6 py-3.5 font-semibold rounded-xl transition-all duration-200 hover:scale-[1.02]"
              :style="{
                border: `2px solid ${colors.border.light}`,
                color: colors.text.primary
              }"
            >
              View Full Reports
              <ArrowRight class="w-4 h-4" />
            </router-link>
          </div>
        </div>
      </div>
    </section>

    <!-- CTA Section -->
    <section 
      id="get-started"
      class="py-20 md:py-28"
      :style="{ backgroundColor: colors.surface }"
    >
      <div class="container mx-auto px-4 sm:px-6 max-w-4xl text-center">
        <span 
          class="inline-block px-5 py-1.5 rounded-full text-xs font-bold tracking-wider mb-8"
          :style="{
            backgroundColor: `${colors.primary[500]}15`,
            color: colors.primary[600]
          }"
        >
          READY TO GET STARTED?
        </span>
        
        <h2 
          class="text-4xl md:text-6xl font-extrabold mb-6 leading-tight"
          :style="{ color: colors.text.primary }"
        >
          Trade with 
          <span 
            class="relative inline-block"
            :style="{ color: colors.primary[500] }"
          >
            Confidence
            <span 
              class="absolute bottom-0 left-0 w-full h-3 -mb-2 opacity-30 blur-lg"
              :style="{
                background: `linear-gradient(90deg, ${colors.primary[400]}, ${colors.primary[200]})`,
                borderRadius: radius.full
              }"
            />
          </span>
        </h2>
        
        <p 
          class="text-lg mb-10 max-w-2xl mx-auto"
          :style="{ color: colors.text.secondary }"
        >
          Join thousands of users who trust BitChest with their crypto investments.
        </p>
        
        <div class="flex flex-wrap gap-4 justify-center">
          <router-link
            to="/signin"
            class="inline-flex items-center gap-2 px-8 py-4 font-semibold rounded-2xl transition-all duration-200 hover:scale-[1.02] hover:shadow-lg active:scale-[0.98]"
            :style="{
              background: `linear-gradient(135deg, ${colors.primary[500]}, ${colors.primary[600]})`,
              color: colors.white,
              boxShadow: shadows.primary.md
            }"
          >
            Create an Account
            <ArrowRight class="w-5 h-5" />
          </router-link>
          
          <router-link
            to="/signin"
            class="inline-flex items-center gap-2 px-8 py-4 font-semibold rounded-2xl border-2 transition-all duration-200 hover:scale-[1.02] hover:bg-surface"
            :style="{
              borderColor: colors.text.primary,
              color: colors.text.primary
            }"
          >
            Sign In
          </router-link>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="py-16 border-t" :style="{ borderColor: colors.border.light }">
      <div class="container mx-auto px-4 sm:px-6 max-w-7xl">
        <div class="grid md:grid-cols-4 gap-12 mb-12">
          <!-- Brand -->
          <div>
            <router-link to="/" class="flex items-center gap-3 mb-6 group">
            
                             <img src="/assets/bitchest_logo.png" alt="BitChest Logo" class="h-14" />
            </router-link>
            
            <p 
              class="text-sm leading-relaxed"
              :style="{ color: colors.text.secondary }"
            >
              Secure crypto trading platform. Simple, fast, and professional. Built for discerning investors.
            </p>
            
            <!-- Social Links -->
            <div class="flex gap-4 mt-6">
              <a 
                href="#"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-200 hover:scale-110 hover:bg-surface"
                :style="{ border: `1px solid ${colors.border.light}` }"
              >
                <span :style="{ color: colors.text.tertiary }">𝕏</span>
              </a>
              <a 
                href="#"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-200 hover:scale-110 hover:bg-surface"
                :style="{ border: `1px solid ${colors.border.light}` }"
              >
                <span :style="{ color: colors.text.tertiary }">f</span>
              </a>
              <a 
                href="#"
                class="w-10 h-10 rounded-xl flex items-center justify-center transition-all duration-200 hover:scale-110 hover:bg-surface"
                :style="{ border: `1px solid ${colors.border.light}` }"
              >
                <span :style="{ color: colors.text.tertiary }">in</span>
              </a>
            </div>
          </div>
          
          <!-- Navigation Links -->
          <div 
            v-for="section in [
              { 
                title: 'Product', 
                links: [
                  { label: 'Features', href: '#features' },
                  { label: 'Security', href: '#security' },
                  { label: 'Pricing', href: '#pricing' },
                  { label: 'Integrations', href: '#integrations' }
                ]
              },
              { 
                title: 'Company', 
                links: [
                  { label: 'About', href: '#about' },
                  { label: 'Blog', href: '#blog' },
                  { label: 'Contact', href: '#contact' },
                  { label: 'Careers', href: '#careers' }
                ]
              },
              { 
                title: 'Legal', 
                links: [
                  { label: 'Privacy', href: '#privacy' },
                  { label: 'Terms', href: '#terms' },
                  { label: 'Compliance', href: '#compliance' },
                  { label: 'Security', href: '#security-policy' }
                ]
              }
            ]" 
            :key="section.title"
          >
            <h4 
              class="text-xs font-bold tracking-wider mb-5"
              :style="{ color: colors.primary[600], letterSpacing: '0.1em' }"
            >
              {{ section.title }}
            </h4>
            <ul class="space-y-3">
              <li v-for="link in section.links" :key="link.label">
                <a
                  :href="link.href"
                  class="text-sm transition-all duration-200 hover:text-primary-500"
                  :style="{ color: colors.text.secondary }"
                >
                  {{ link.label }}
                </a>
              </li>
            </ul>
          </div>
        </div>
        
        <!-- Footer Bottom -->
        <div 
          class="pt-10 border-t flex flex-wrap justify-between items-center gap-6 text-sm"
          :style="{ borderColor: colors.border.light }"
        >
          <span :style="{ color: colors.text.tertiary }">
            © 2025 BitChest. All rights reserved.
          </span>
          
          <div class="flex gap-6">
            <a
              href="#privacy"
              class="transition-all duration-200 hover:text-primary-500"
              :style="{ color: colors.text.tertiary }"
            >
              Privacy Policy
            </a>
            <a
              href="#terms"
              class="transition-all duration-200 hover:text-primary-500"
              :style="{ color: colors.text.tertiary }"
            >
              Terms of Use
            </a>
          </div>
          
          <span :style="{ color: colors.text.tertiary }">
            Prototype v1.0
          </span>
        </div>
      </div>
    </footer>
  </div>
</template>

<style scoped>
.animate-spin {
  animation: spin 0.8s linear infinite;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}

/* Smooth appearance for animated numbers */
.transition-all {
  transition-property: all;
}

/* Gradient text utility */
.bg-gradient-to-r {
  background: linear-gradient(to right, var(--tw-gradient-stops));
}

.from-primary-400 {
  --tw-gradient-from: v-bind('colors.primary[400]');
}

.to-primary-600 {
  --tw-gradient-to: v-bind('colors.primary[600]');
}

.from-secondary-400 {
  --tw-gradient-from: v-bind('colors.secondary[400]');
}

.to-secondary-600 {
  --tw-gradient-to: v-bind('colors.secondary[600]');
}
</style>
