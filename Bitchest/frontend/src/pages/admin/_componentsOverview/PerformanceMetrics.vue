<script setup lang="ts">
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Progress } from '@/components/ui/progress'
import { TrendingUp, TrendingDown, Target, Zap } from 'lucide-vue-next'

interface Props {
  stats: any
  formatCurrency: (value: any) => string
}

const props = defineProps<Props>()

const metrics = [
  {
    title: 'Transaction Success Rate',
    value: '98.5%',
    change: '+2.3%',
    trend: 'up',
    icon: Target,
    color: 'text-green-600',
    bgColor: 'bg-green-50',
    progress: 98.5
  },
  {
    title: 'Platform Uptime',
    value: '99.9%',
    change: '+0.1%',
    trend: 'up',
    icon: Zap,
    color: 'text-blue-600',
    bgColor: 'bg-blue-50',
    progress: 99.9
  },
  {
    title: 'Avg. Transaction Value',
    value: props.formatCurrency(Number(props.stats?.total_volume || 0) / (props.stats?.total_transactions || 1)),
    change: '+15%',
    trend: 'up',
    icon: TrendingUp,
    color: 'text-purple-600',
    bgColor: 'bg-purple-50',
    progress: 85
  },
  {
    title: 'New Users This Month',
    value: props.stats?.total_users || 0,
    change: '+12%',
    trend: 'up',
    icon: TrendingUp,
    color: 'text-orange-600',
    bgColor: 'bg-orange-50',
    progress: 75
  }
]
</script>

<template>
  <Card class="border border-gray-200">
    <CardHeader>
      <CardTitle class="text-lg font-semibold text-gray-900">Performance Metrics</CardTitle>
      <p class="text-sm text-gray-500">Key platform performance indicators</p>
    </CardHeader>
    <CardContent>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div 
          v-for="(metric, index) in metrics" 
          :key="index"
          class="space-y-4"
        >
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div :class="['p-2 rounded-lg', metric.bgColor]">
                <component :is="metric.icon" :class="['h-4 w-4', metric.color]" />
              </div>
              <div>
                <h4 class="text-sm font-medium text-gray-700">{{ metric.title }}</h4>
                <div class="flex items-center gap-2 mt-1">
                  <span class="text-lg font-bold text-gray-900">{{ metric.value }}</span>
                  <span 
                    :class="[
                      'text-xs font-medium px-1.5 py-0.5 rounded',
                      metric.trend === 'up' ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'
                    ]"
                  >
                    <component 
                      :is="metric.trend === 'up' ? TrendingUp : TrendingDown" 
                      class="h-3 w-3 inline mr-1" 
                    />
                    {{ metric.change }}
                  </span>
                </div>
              </div>
            </div>
          </div>
          <Progress :model-value="metric.progress" class="h-2" />
        </div>
      </div>
    </CardContent>
  </Card>
</template>