<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent } from '@/components/ui/card';
import { LogOut, RefreshCw, Shield, TrendingDown, TrendingUp, User } from 'lucide-vue-next';
import { computed } from 'vue';
import { useRoute } from 'vue-router';

const props = defineProps<{
  menuItems: Array<{ label: string; icon: string; path: string }>
  portfolio: unknown[]
  totalValue: number
  dayChangePct: number | string
  role: string
  userName?: string
  userEmail?: string
  userInitials?: string
  profilePictureUrl?: string
  unreadCount?: number
  plusValueDisplay?: string
  plusValuePercentDisplay?: string
  plusPositive?: boolean
  loadingWallet?: boolean
}>()

const emit = defineEmits<{
  (e: 'openNotifications'): void
  (e: 'logout'): void
  (e: 'refresh'): void
}>()

const route = useRoute()

const normalizedRole = computed(() => String(props.role ?? '').trim().toUpperCase())
const isAdmin = computed(() => normalizedRole.value === 'ADMIN')
const roleLabel = computed(() => (isAdmin.value ? 'Administrator' : 'Client'))
const rolePillLabel = computed(() => (isAdmin.value ? 'Admin · Full access' : 'Client'))

const roleCard = computed(() => {
  if (isAdmin.value) {
    return {
      title: 'System Control',
      description: 'Manage users, assets & transactions',
      Icon: Shield,
      iconBg: 'bg-emerald-50',
      iconColor: 'text-emerald-600',
    }
  }
  return {
    title: 'Portfolio Overview',
    description: 'Track your holdings & performance',
    Icon: User,
    iconBg: 'bg-blue-50',
    iconColor: 'text-blue-600',
  }
})
</script>

<template>
  <div class="w-full space-y-5">
    <!-- BRAND + ROLE unique -->
    <div class="flex items-center justify-between gap-3 px-1">
      <img
        src="/assets/bitchest_logo.png"
        class="h-8 w-auto hover:opacity-80 transition-opacity duration-200"
        alt="Bitchest"
      />
      <Badge
        variant="outline"
        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold tracking-wide rounded-full border shadow-sm transition-colors duration-200"
        :class="isAdmin ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-blue-50 text-blue-700 border-blue-200'"
        :aria-label="`Current role: ${roleLabel}`"
      >
        <component
          :is="roleCard.Icon"
          class="w-3.5 h-3.5 shrink-0"
          :class="roleCard.iconColor"
          aria-hidden="true"
        />
        {{ rolePillLabel }}
      </Badge>
    </div>

    <!-- SINGLE ROLE CARD (pro & fluide, plus de duplication) -->
    <Card
      class="bg-white border shadow-sm rounded-2xl overflow-hidden transition-all duration-200"
      :class="isAdmin ? 'border-slate-200' : 'border-slate-200'"
    >
      <CardContent class="p-4 sm:p-5">
        <div class="space-y-3">
          <div class="flex items-center gap-3">
            <div
              class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 transition-colors duration-200"
              :class="roleCard.iconBg"
            >
              <component
                :is="roleCard.Icon"
                class="w-5 h-5"
                :class="roleCard.iconColor"
                aria-hidden="true"
              />
            </div>
            <div class="min-w-0">
              <div class="text-sm font-semibold text-slate-700 leading-none">
                {{ roleCard.title }}
              </div>
              <div class="text-xs text-slate-500 mt-1 truncate">
                {{ roleCard.description }}
              </div>
            </div>
          </div>

          <!-- Contenu spécifique client : plus-value -->
          <div
            v-if="!isAdmin"
            class="pt-3 mt-1 border-t border-slate-100 flex items-start justify-between gap-3"
          >
            <div class="min-w-0">
              <div class="text-xs font-medium text-slate-500 mb-0.5">Plus-value</div>
              <div
                class="text-base font-bold flex items-center gap-1.5"
                :class="plusPositive ? 'text-emerald-600' : 'text-rose-600'"
              >
                <span class="truncate">{{ plusValueDisplay ?? '—' }}</span>
                <TrendingUp
                  v-if="plusPositive"
                  class="w-4 h-4 shrink-0"
                  aria-hidden="true"
                />
                <TrendingDown
                  v-else
                  class="w-4 h-4 shrink-0"
                  aria-hidden="true"
                />
              </div>
              <div
                class="text-xs font-medium mt-0.5"
                :class="plusPositive ? 'text-emerald-600' : 'text-rose-600'"
              >
                {{ plusValuePercentDisplay ?? '' }}
              </div>
            </div>
            <button
              type="button"
              class="p-2 rounded-xl hover:bg-slate-100 active:bg-slate-200 transition-colors disabled:opacity-50 shrink-0"
              :disabled="loadingWallet"
              aria-label="Refresh portfolio"
              @click="emit('refresh')"
            >
              <RefreshCw
                class="w-4 h-4 text-slate-500"
                :class="{ 'animate-spin': loadingWallet }"
                aria-hidden="true"
              />
            </button>
          </div>

          <!-- Contenu spécifique admin : info discrète, sans répéter le mot rôle -->
          <p
            v-else
            class="text-xs text-slate-500 pt-3 mt-1 border-t border-slate-100 leading-relaxed"
          >
            Accès complet au back-office. Les actions sensibles sont journalisées.
          </p>
        </div>
      </CardContent>
    </Card>

    <!-- NAVIGATION -->
    <nav
      class="space-y-1.5"
      aria-label="Primary navigation"
    >
      <router-link
        v-for="item in menuItems"
        :key="item.path"
        :to="item.path"
        class="relative flex items-center p-3 sm:p-3.5 rounded-xl group border transition-all duration-200"
        :class="[
          route.path === item.path
            ? 'bg-blue-50 border-blue-200 shadow-sm'
            : 'bg-white border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-200'
        ]"
        :aria-current="route.path === item.path ? 'page' : undefined"
      >
        <div
          class="mr-3 text-lg sm:text-xl transition-transform duration-200"
          :class="route.path === item.path ? 'scale-110' : 'group-hover:scale-105'"
          aria-hidden="true"
        >
          {{ item.icon }}
        </div>
        <span
          class="font-semibold text-sm transition-colors"
          :class="route.path === item.path ? 'text-blue-700' : 'text-slate-700 group-hover:text-slate-900'"
        >
          {{ item.label }}
        </span>
        <div
          v-if="route.path === item.path"
          class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-7 rounded-r-full bg-blue-500"
          aria-hidden="true"
        />
      </router-link>

      <!-- Notifications -->
      <button
        type="button"
        class="relative flex items-center w-full text-left p-3 sm:p-3.5 rounded-xl group border transition-all duration-200 bg-white border-transparent text-slate-600 hover:bg-slate-50 hover:border-slate-200"
        aria-label="Open notifications"
        @click="emit('openNotifications')"
      >
        <div
          class="mr-3 text-lg sm:text-xl transition-transform duration-200 group-hover:scale-105"
          aria-hidden="true"
        >
          🔔
        </div>
        <span class="font-semibold text-sm text-slate-700 group-hover:text-slate-900">
          Notifications
        </span>
        <span
          v-if="unreadCount && unreadCount > 0"
          class="ml-auto inline-flex items-center justify-center min-w-[22px] h-5 px-1.5 text-xs font-bold text-white bg-gradient-to-r from-rose-500 to-pink-500 rounded-full border-2 border-white shadow-sm"
          :aria-label="`${unreadCount} unread notifications`"
        >
          {{ unreadCount > 9 ? '9+' : unreadCount }}
        </span>
      </button>
    </nav>

    <!-- USER SECTION -->
    <div class="border-t border-slate-200 pt-4 space-y-2">
      <div class="flex items-center gap-3 p-3 rounded-2xl bg-white border border-slate-200 shadow-sm">
        <Avatar class="h-10 w-10 border border-slate-200 shrink-0">
          <AvatarImage
            v-if="profilePictureUrl"
            :src="profilePictureUrl"
            :alt="userName || 'User'"
            class="object-cover"
          />
          <AvatarFallback class="bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 font-semibold text-xs">
            {{ userInitials || 'U' }}
          </AvatarFallback>
        </Avatar>
        <div class="flex-1 min-w-0">
          <div class="text-sm font-semibold text-slate-900 truncate">
            {{ userName || 'User' }}
          </div>
          <div class="text-xs text-slate-500 truncate">
            {{ userEmail }}
          </div>
        </div>
      </div>

      <button
        type="button"
        class="flex items-center w-full text-left p-3 rounded-xl group border transition-all duration-200 bg-white border-transparent text-rose-600 hover:bg-rose-50 hover:border-rose-200"
        @click="emit('logout')"
      >
        <LogOut
          class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-105"
          aria-hidden="true"
        />
        <span class="font-semibold text-sm group-hover:text-rose-700">Logout</span>
      </button>
    </div>
  </div>
</template>
