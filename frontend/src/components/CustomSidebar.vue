<script setup lang="ts">
import { Badge } from '@/components/ui/badge'
import { Card, CardContent } from '@/components/ui/card'
import { Shield, TrendingDown, TrendingUp, User, Wallet } from 'lucide-vue-next'
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const props = defineProps<{
  menuItems: Array<{ label: string; icon: string; path: string }>
  portfolio: any[]
  totalValue: number
  dayChangePct: number | string
  role: string
}>()

const route = useRoute()

// Format portfolio total
const formattedTotalValue = computed(() => {
  const value = Number(props.totalValue) || 0
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2
  }).format(value)
})

const isPositiveChange = computed(() => Number(props.dayChangePct) > 0)
</script>


<template>
  <div class="w-full space-y-4 sm:space-y-6">

    <!-- ADMIN CARD -->
    <Card 
      v-if="role === 'ADMIN'"
      class="bg-gradient-to-br from-[#FF5964] to-[#38618C] border-0 shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all"
    >
      <CardContent class="p-4 sm:p-6 text-white">
        <div class="space-y-3">
          <div class="flex items-center gap-2">
            <Shield class="w-5 h-5 text-white/90" />
            <span class="text-sm font-medium text-white/90">Admin Panel</span>
          </div>

          <div class="text-lg font-bold">System Control</div>

          <Badge variant="secondary" class="bg-white/20 text-white border-0 text-xs">
            Full Access
          </Badge>
        </div>
      </CardContent>
    </Card>

    <!-- CLIENT CARD -->
    <Card 
      v-if="role !== 'ADMIN'"
      class="bg-gradient-to-br from-[#35A7FF] to-[#38618C] border-0 shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all"
    >
      <CardContent class="p-4 sm:p-6 text-white">
        <div class="space-y-3">
          <div class="flex items-center gap-2">
            <User class="w-5 h-5 text-white/90" />
            <span class="text-sm font-medium text-white/90">Client Panel</span>
          </div>

          <div class="text-lg font-bold">Portfolio Overview</div>

        
        </div>
      </CardContent>
    </Card>

    <!-- NAVIGATION - Design amélioré -->
    <nav class="space-y-2">
      <router-link
        v-for="item in menuItems"
        :key="item.label"
        :to="item.path"
        class="relative flex items-center p-3 sm:p-4 rounded-xl sm:rounded-2xl group overflow-hidden"
        :class="[
          route.path === item.path
            ? 'bg-gradient-to-r from-[#35A7FF] to-[#38618C] text-white shadow-lg shadow-blue-500/30'
            : 'text-gray-600 hover:bg-gradient-to-r hover:from-blue-50 hover:to-gray-50 hover:shadow-lg'
        ]"
      >
        <!-- Icon avec effet -->
        <div 
          class="mr-3 text-lg sm:text-xl transition-transform duration-300 z-10"
          :class="route.path === item.path ? 'scale-110 text-white' : 'group-hover:scale-110 group-hover:text-blue-600'"
        >
          {{ item.icon }}
        </div>

        <!-- Label -->
        <span 
          class="font-semibold text-sm sm:text-base z-10 transition-colors"
          :class="route.path === item.path ? 'text-white' : 'text-gray-700 group-hover:text-gray-900'"
        >
          {{ item.label }}
        </span>

        <!-- Active indicator amélioré -->
        <div 
          v-if="route.path === item.path"
          class="absolute right-4 w-2 h-8 bg-white rounded-full animate-pulse shadow-lg shadow-white/50"
        />

        <!-- Hover glow effect -->
        <div 
          v-if="route.path !== item.path"
          class="absolute inset-0 bg-gradient-to-r from-[#35A7FF] to-[#38618C] opacity-0 group-hover:opacity-5 transition-opacity rounded-xl sm:rounded-2xl"
        />
        
        <!-- Active glow effect -->
        <div 
          v-if="route.path === item.path"
          class="absolute inset-0 bg-gradient-to-r from-blue-400/20 to-blue-600/10"
        />
      </router-link>
    </nav>

    <!-- PORTFOLIO SUMMARY (CLIENT) - Design amélioré -->
    <Card 
      v-if="role !== 'ADMIN' && portfolio.length > 0"
      class="bg-gradient-to-br from-white to-gray-50 border-0 shadow-lg hover:shadow-xl transition-all group"
    >
      <CardContent class="p-4 sm:p-6">
        <div class="space-y-4">
          <!-- Header -->
          <div class="flex items-center gap-2">
            <Wallet class="w-4 h-4 text-blue-600" />
            <span class="text-sm font-medium text-gray-700">Portfolio Value</span>
          </div>

          <!-- Value avec icône dynamique -->
          <div class="flex items-center justify-between">
            <div class="text-2xl sm:text-3xl font-bold bg-gradient-to-r from-[#38618C] to-[#35A7FF] bg-clip-text text-transparent">
              {{ formattedTotalValue }}
            </div>
            
            <div class="flex items-center gap-1">
              <TrendingUp v-if="isPositiveChange" class="w-5 h-5 text-emerald-500 animate-pulse" />
              <TrendingDown v-else class="w-5 h-5 text-red-500 animate-pulse" />
              <Badge
                class="text-xs font-bold px-3 py-1 shadow-sm"
                :class="isPositiveChange
                  ? 'bg-emerald-100 text-emerald-800 border-emerald-200'
                  : 'bg-red-100 text-red-800 border-red-200'"
              >
                {{ isPositiveChange ? '↑' : '↓' }} {{ Math.abs(Number(dayChangePct)).toFixed(2) }}%
              </Badge>
            </div>
          </div>

          <!-- Stats -->
          <div class="grid grid-cols-2 gap-3 pt-3 border-t border-gray-100">
            <div class="text-center p-2 bg-blue-50 rounded-lg group-hover:bg-blue-100 transition-colors">
              <div class="text-xs text-gray-600">Assets</div>
              <div class="text-sm font-bold text-blue-700">{{ portfolio.length }}</div>
            </div>
            <div class="text-center p-2 bg-emerald-50 rounded-lg group-hover:bg-emerald-100 transition-colors">
              <div class="text-xs text-gray-600">24h Change</div>
              <div 
                class="text-sm font-bold"
                :class="isPositiveChange ? 'text-emerald-700' : 'text-red-700'"
              >
                {{ isPositiveChange ? '+' : '-' }}{{ Math.abs(Number(dayChangePct)).toFixed(2) }}%
              </div>
            </div>
          </div>

          <!-- Progress bar simplifiée -->
          <div class="pt-2">
            <div class="flex justify-between text-xs text-gray-500 mb-1">
              <span>Portfolio Performance</span>
              <span>{{ isPositiveChange ? 'Growing' : 'Declining' }}</span>
            </div>
            <div class="h-1.5 bg-gray-200 rounded-full overflow-hidden">
              <div 
                class="h-full rounded-full transition-all duration-1000"
                :class="isPositiveChange ? 'bg-emerald-500' : 'bg-red-500'"
                :style="{ width: `${Math.min(100, Math.abs(Number(dayChangePct)) * 10)}%` }"
              ></div>
            </div>
          </div>
        </div>
      </CardContent>
    </Card>



  </div>
</template>

<style scoped>
* {
  transition: all 0.3s ease;
}

/* Effet de surbrillance pour les cartes */
.card-hover {
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.card-hover:hover {
  transform: translateY(-2px);
}

/* Animation pour le badge de changement */
@keyframes pulse-subtle {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.8;
  }
}

.animate-pulse {
  animation: pulse-subtle 2s ease-in-out infinite;
}
</style>