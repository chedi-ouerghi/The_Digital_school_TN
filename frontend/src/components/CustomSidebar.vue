<script setup lang="ts">
import { ref, computed } from 'vue'
import { useRoute } from 'vue-router'
import { Card, CardContent } from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
  ChevronLeft,
  ChevronRight,
  TrendingUp,
  TrendingDown,
  Wallet,
  Shield
} from 'lucide-vue-next'

// Chart colors
const COLORS = {
  primary: '#38618C',
  success: '#01FF19',
  accent: '#35A7FF',
  danger: '#FF5964'
}

const props = defineProps<{
  menuItems: Array<{ label: string; icon: string; path: string }>
  portfolio: any[]
  totalValue: number
  dayChangePct: number | string
  role: string
}>()

const route = useRoute()
const isCollapsed = ref(false)

const formattedTotalValue = computed(() => {
  const value = typeof props.totalValue === 'number' ? props.totalValue : parseFloat(props.totalValue) || 0
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
})

const isPositiveChange = computed(() => {
  return Number(props.dayChangePct) > 0
})

function toggleSidebar() {
  isCollapsed.value = !isCollapsed.value
}
</script>

<template>
  <div 
    class="h-full absolut top-24 transition-all duration-500 ease-in-out bg-gradient-to-b from-white to-gray-50/50 backdrop-blur-sm border-r border-gray-100 shadow-xl"
    :class="[
      isCollapsed ? 'w-20' : 'w-80',
      'rounded-r-3xl overflow-hidden'
    ]"
  >
    <div class="h-full flex flex-col">
      <!-- Header avec toggle -->
      <div class="p-6 pb-4 border-b border-gray-100/50 relative">
        <div class="flex items-center justify-between">
          <div 
            class="transition-all duration-300 overflow-hidden"
            :class="isCollapsed ? 'w-0 opacity-0' : 'w-auto opacity-100'"
          >
            <h2 class="text-xl font-bold bg-gradient-to-r from-[#38618C] to-[#35A7FF] bg-clip-text text-transparent">
              Navigation
            </h2>
            <p class="text-xs text-gray-500 mt-1">Manage your portfolio</p>
          </div>
          
          <Button
            variant="ghost"
            size="icon"
            @click="toggleSidebar"
            class="w-10 h-10 rounded-xl border border-gray-200 bg-white/80 hover:bg-white shadow-sm hover:shadow-md transition-all duration-300 hover:scale-105"
            :class="{ 'ml-auto': isCollapsed }"
          >
            <ChevronLeft v-if="!isCollapsed" class="w-5 h-5 text-[#38618C]" />
            <ChevronRight v-else class="w-5 h-5 text-[#38618C]" />
          </Button>
        </div>
      </div>

      <!-- Content -->
      <div class="flex-1 p-6 space-y-6 overflow-y-auto">
        
        <!-- Carte Admin -->
        <Card 
          v-if="role === 'ADMIN'"
          class="bg-gradient-to-br from-[#FF5964] to-[#38618C] border-0 shadow-2xl overflow-hidden group hover:shadow-3xl transition-all duration-500 hover:scale-[1.02]"
          :class="isCollapsed ? 'p-2' : 'p-6'"
        >
          <CardContent class="p-0 text-white">
            <div :class="isCollapsed ? 'text-center' : 'space-y-3'">
              <template v-if="!isCollapsed">
                <div class="flex items-center space-x-2">
                  <Shield class="w-5 h-5 text-white/90" />
                  <span class="text-sm font-medium text-white/90">Admin Panel</span>
                </div>
                <div class="text-lg font-bold">System Control</div>
                <Badge variant="secondary" class="bg-white/20 text-white border-0 text-xs">
                  Full Access
                </Badge>
              </template>
              <template v-else>
                <Shield class="w-6 h-6 mx-auto text-white/90" />
                <div class="text-xs font-semibold text-center mt-1">Admin</div>
              </template>
            </div>
          </CardContent>
        </Card>

        <!-- Navigation Menu -->
        <nav class="space-y-2">
          <router-link 
            v-for="item in menuItems" 
            :key="item.label"
            :to="item.path"
            class="flex items-center rounded-2xl transition-all duration-300 group relative overflow-hidden"
            :class="[
              route.path === item.path 
                ? 'bg-gradient-to-r from-[#35A7FF] to-[#38618C] text-white shadow-lg shadow-blue-500/25' 
                : 'text-gray-600 hover:bg-white hover:shadow-lg hover:border hover:border-gray-100',
              isCollapsed ? 'p-3 justify-center' : 'p-4'
            ]"
          >
            <!-- Background hover effect -->
            <div class="absolute inset-0 bg-gradient-to-r from-[#35A7FF] to-[#38618C] opacity-0 group-hover:opacity-5 transition-opacity duration-300"></div>
            
            <!-- Icon -->
            <div 
              class="text-xl transition-transform duration-300 group-hover:scale-110 z-10"
              :class="{ 'mr-0': isCollapsed, 'mr-3': !isCollapsed }"
            >
              {{ item.icon }}
            </div>
            
            <!-- Label -->
            <span 
              class="font-semibold transition-all duration-300 z-10"
              :class="[
                isCollapsed ? 'opacity-0 w-0' : 'opacity-100 w-auto',
                route.path === item.path ? 'text-white' : 'text-gray-700'
              ]"
            >
              {{ item.label }}
            </span>

            <!-- Active Indicator -->
            <div 
              v-if="route.path === item.path && !isCollapsed"
              class="absolute right-4 w-2 h-2 bg-white rounded-full animate-pulse"
            />

            <!-- Tooltip for collapsed state -->
            <div 
              v-if="isCollapsed"
              class="absolute left-full ml-3 px-3 py-2 bg-gray-900 text-white text-sm rounded-xl opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-300 whitespace-nowrap z-50 shadow-xl backdrop-blur-sm"
            >
              {{ item.label }}
              <div class="absolute right-full top-1/2 transform -translate-y-1/2 border-8 border-transparent border-r-gray-900"></div>
            </div>

            <!-- Hover glow effect -->
            <div 
              class="absolute inset-0 rounded-2xl bg-gradient-to-r from-[#35A7FF] to-[#38618C] opacity-0 group-hover:opacity-[0.03] transition-opacity duration-300"
            />
          </router-link>
        </nav>

  
      </div>

      <!-- Footer Mini (mode réduit) -->
      <div 
        v-if="isCollapsed"
        class="p-4 border-t border-gray-100/50 text-center"
      >
        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-[#38618C] to-[#35A7FF] mx-auto flex items-center justify-center text-white text-xs font-bold">
          B
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Custom scrollbar */
.overflow-y-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-y-auto::-webkit-scrollbar-track {
  background: #f1f5f9;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
  background: #cbd5e1;
  border-radius: 10px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
  background: #94a3b8;
}

/* Smooth transitions */
* {
  transition-property: all;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 300ms;
}

/* Hover lift effect */
.hover-lift:hover {
  transform: translateY(-2px);
}
</style>