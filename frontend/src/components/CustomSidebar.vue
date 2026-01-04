<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { Shield, User } from 'lucide-vue-next';
import { useRoute } from 'vue-router';

const props = defineProps<{
  menuItems: Array<{ label: string; icon: string; path: string }>
  portfolio: any[]
  totalValue: number
  dayChangePct: number | string
  role: string
}>()

const route = useRoute()

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