<script setup lang="ts">
import { computed } from 'vue'
import { useRoute } from 'vue-router'
import { Card, CardContent } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Shield } from 'lucide-vue-next'

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

    <!-- NAVIGATION -->
    <nav class="space-y-2">
      <router-link
        v-for="item in menuItems"
        :key="item.label"
        :to="item.path"
        class="relative flex items-center p-3 sm:p-4 rounded-xl sm:rounded-2xl group"
        :class="[
          route.path === item.path
            ? 'bg-gradient-to-r from-[#35A7FF] to-[#38618C] text-white shadow-lg shadow-blue-500/30'
            : 'text-gray-600 hover:bg-gray-50 hover:shadow-md'
        ]"
      >
        <!-- Icon -->
        <div class="mr-3 text-lg sm:text-xl group-hover:scale-110 transition-transform z-10">
          {{ item.icon }}
        </div>

        <!-- Label -->
        <span 
          class="font-semibold text-sm sm:text-base z-10"
          :class="route.path === item.path ? 'text-white' : 'text-gray-700'"
        >
          {{ item.label }}
        </span>

        <!-- Active dot -->
        <div 
          v-if="route.path === item.path"
          class="absolute right-4 w-2 h-2 bg-white rounded-full animate-pulse"
        />

        <!-- Soft hover glow -->
        <div 
          class="absolute inset-0 bg-gradient-to-r from-[#35A7FF] to-[#38618C] opacity-0 group-hover:opacity-[0.05] transition-opacity rounded-xl sm:rounded-2xl"
        />
      </router-link>
    </nav>

    <!-- PORTFOLIO SUMMARY (CLIENT) -->
    <Card 
      v-if="role !== 'ADMIN' && portfolio.length > 0"
      class="bg-white border border-gray-200 shadow-md hover:shadow-lg transition-all"
    >
      <CardContent class="p-4 sm:p-6">
        <div class="space-y-3">

          <div class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-600">Total Portfolio</span>

            <Badge
              class="text-xs font-bold"
              :class="isPositiveChange
                ? 'bg-green-100 text-green-700 border-green-300'
                : 'bg-red-100 text-red-700 border-red-300'"
            >
              {{ isPositiveChange ? '↑' : '↓' }} {{ Math.abs(Number(dayChangePct)).toFixed(2) }}%
            </Badge>
          </div>

          <div class="text-2xl font-bold bg-gradient-to-r from-[#38618C] to-[#35A7FF] bg-clip-text text-transparent">
            {{ formattedTotalValue }}
          </div>

          <div class="text-xs text-gray-500">
            {{ portfolio.length }} {{ portfolio.length === 1 ? 'asset' : 'assets' }}
          </div>

        </div>
      </CardContent>
    </Card>

  </div>
</template>

<style scoped>
* {
  transition: all 0.25s ease;
}
</style>
