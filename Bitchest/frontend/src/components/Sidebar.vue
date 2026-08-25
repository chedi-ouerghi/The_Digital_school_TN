<script setup lang="ts">
import { Sidebar, SidebarContent, SidebarGroup, SidebarGroupContent, SidebarMenu, SidebarMenuItem } from '@/components/ui/sidebar';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';

defineProps<{
  menuItems: Array<{
    label: string
    icon: string
    path: string
  }>
  portfolio: any[]
  totalValue: number
  dayChangePct: number | string
}>()
</script>

<template>
  <Sidebar class="border border-brand-dark rounded-2xl shadow-lg sticky top-6">
    <SidebarContent>
      <SidebarMenu>
        <SidebarGroup>
          <SidebarGroupContent>
            <!-- Carte Solde -->
            <Card class="bg-gradient-to-r from-brand-dark to-brand-blue border-0 mb-6">
              <CardContent class="p-4 text-white">
                <div class="flex items-center justify-between">
                  <div>
                    <div class="text-white/90 text-sm font-medium">balance_eur total</div>
                    <div class="text-2xl font-bold">€{{ Math.round(totalValue) }}</div>
                  </div>
                  <div class="text-right">
                    <div class="text-lg font-bold" :class="dayChangePct >= 0 ? 'text-brand-green' : 'text-brand-red'">
                      {{ dayChangePct }}%
                    </div>
                    <div class="text-white/80 text-xs">24h</div>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Navigation -->
            <div class="space-y-1">
              <SidebarMenuItem v-for="item in menuItems" :key="item.label">
                <router-link 
                  :to="item.path"
                  class="flex items-center px-3 py-2 rounded-md hover:bg-brand-blue hover:text-white transition-colors duration-200"
                  :class="$route.path === item.path ? 'bg-brand-green text-white' : 'text-brand-dark'"
                >
                  <div class="text-lg mr-3">{{ item.icon }}</div>
                  <span class="font-medium">{{ item.label }}</span>
                </router-link>
              </SidebarMenuItem>
            </div>

            <Separator class="my-6 bg-gray-200" />

            <!-- Diversification -->
            <div class="mb-4">
              <h4 class="text-brand-dark text-sm font-semibold flex items-center gap-2">
                <span class="w-2 h-2 bg-brand-green rounded-full"></span>
                Diversification
              </h4>
            </div>
            
            <div v-if="portfolio.length === 0" class="text-center py-6 text-gray-500 text-sm bg-gray-50 rounded-xl border border-gray-200">
              <div class="text-2xl mb-2">💼</div>
              Aucun actif en wallet
            </div>
            
            <div v-else class="space-y-2 max-h-60 overflow-y-auto scrollbar-custom">
              <Card 
                v-for="p in portfolio" 
                :key="p.id" 
                class="p-3 hover:border-brand-blue transition-colors duration-200 border-gray-200"
              >
                <CardContent class="p-0">
                  <div class="flex items-center gap-3">
                    <img 
                      v-if="p.cryptomoney?.image" 
                      :src="p.cryptomoney?.image"
                      class="h-7 w-7 rounded-full border border-gray-300"
                    />
                    <div class="flex-1 min-w-0">
                      <div class="font-medium text-brand-dark text-sm truncate">
                        {{ p.cryptomoney?.nom || p.cryptomoney?.name }}
                      </div>
                      <div class="text-gray-500 text-xs">
                        {{ parseFloat(p.quantity).toFixed(4) }}
                      </div>
                    </div>
                    <div class="text-right">
                      <div class="font-semibold text-brand-dark text-sm">
                        €{{ (parseFloat(p.quantity) * parseFloat(p.cryptomoney?.price_eur || 0)).toFixed(2) }}
                      </div>
                      <div class="text-xs" :class="parseFloat(p.cryptomoney?.change_24h_pct || 0) >= 0 ? 'text-brand-green' : 'text-brand-red'">
                        {{ parseFloat(p.cryptomoney?.change_24h_pct || 0).toFixed(2) }}%
                      </div>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>
          </SidebarGroupContent>
        </SidebarGroup>
      </SidebarMenu>
    </SidebarContent>
  </Sidebar>
</template>
