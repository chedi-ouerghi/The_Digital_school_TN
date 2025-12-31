<script setup lang="ts">
import CustomSidebar from '@/components/CustomSidebar.vue';
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Drawer, DrawerContent } from '@/components/ui/drawer';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Bell, ChevronDown, LogOut, Menu, RefreshCw, Settings, TrendingDown, TrendingUp, User, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import auth from '../services/auth';

const router = useRouter()
const user = ref(auth.getUser())
const role = ref(auth.getRole())

// Données wallet
const portfolio = ref<any[]>([])
const totalValue = ref(0)
const dayChangePct = ref(0)
const dayChangeAmount = ref(0)
const loadingWallet = ref(false)

// Notifications
const notifications = ref<any[]>([])
const unreadCount = computed(() => notifications.value.filter(n => !n.is_read).length)
const showNotifications = ref(false)
const loadingNotifications = ref(false)
const notifError = ref<string | null>(null)

// Mobile sidebar state
const showMobileSidebar = ref(false)

function logout() {
  auth.logout()
  router.push('/signin')
}

async function fetchNotifications() {
  loadingNotifications.value = true
  notifError.value = null
  try {
    const res = await api.notifications.list()
    if (Array.isArray(res)) {
      notifications.value = res
    } else if ((res as any)?.data) {
      notifications.value = Array.isArray((res as any).data) ? (res as any).data : []
    } else {
      notifications.value = []
    }
  } catch (err: any) {
    console.error('Error notifications:', err)
    notifError.value = err?.message || String(err)
  } finally {
    loadingNotifications.value = false
  }
}

async function markNotificationAsRead(n: any) {
  try {
    await api.notifications.markAsRead(n.id)
    const idx = notifications.value.findIndex((x: any) => x.id === n.id)
    if (idx !== -1) notifications.value[idx].is_read = true
  } catch (err) {
    console.warn('Cannot mark notification as read', err)
  }
}

async function markAllAsRead() {
  try {
    const unreadNotifications = notifications.value.filter(n => !n.is_read)
    for (const n of unreadNotifications) {
      await markNotificationAsRead(n)
    }
  } catch (err) {
    console.warn('Cannot mark all notifications as read', err)
  }
}

function toggleNotifications() {
  showNotifications.value = !showNotifications.value
  if (showNotifications.value) fetchNotifications()
}

function formatNotificationDate(dateString: string): string {
  const date = new Date(dateString)
  const now = new Date()
  const diffMs = now.getTime() - date.getTime()
  const diffMins = Math.floor(diffMs / (1000 * 60))
  const diffHours = Math.floor(diffMs / (1000 * 60 * 60))
  const diffDays = Math.floor(diffMs / (1000 * 60 * 60 * 24))

  if (diffMins < 1) return 'Just now'
  if (diffMins < 60) return `${diffMins} min ago`
  if (diffHours < 24) return `${diffHours} hours ago`
  if (diffDays === 1) return 'Yesterday'
  if (diffDays < 7) return `${diffDays} days ago`
  
  return date.toLocaleDateString('en-US', { 
    day: 'numeric', 
    month: 'short',
    year: date.getFullYear() !== now.getFullYear() ? 'numeric' : undefined
  })
}

function getNotificationIcon(type: string): string {
  const icons: { [key: string]: string } = {
    'transaction': '💰',
    'security': '🔒',
    'system': '⚙️',
    'alert': '⚠️',
    'info': 'ℹ️',
    'success': '✅'
  }
  return icons[type] || '📢'
}

function formatCurrency(value: number): string {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'EUR',
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  }).format(value)
}

function formatPercentage(value: number): string {
  return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`
}

async function loadWalletData() {
  if (String(role.value).toUpperCase().includes('ADMIN')) return
  
  loadingWallet.value = true
  try {
    const response = await api.wallet.list()
    
    const walletData = Array.isArray(response) 
      ? (response[0] || {})
      : (response as any) || {}
    
    portfolio.value = walletData.cryptomonnaies || walletData.cryptos || []
    totalValue.value = Number(walletData.current_value || walletData.total_value || 0)
    
    let totalYesterday = 0
    let hasValidData = false
    
    for (const crypto of portfolio.value) {
      const qty = Number(crypto.pivot?.quantity || crypto.quantity || 0)
      const price = Number(crypto.price_eur || crypto.current_price || 0)
      const changePct = Number(crypto.change_24h_pct || crypto.price_change_percentage_24h || 0)
      
      if (qty > 0 && price > 0) {
        const yesterdayValue = qty * price / (1 + changePct / 100)
        totalYesterday += yesterdayValue
        hasValidData = true
      }
    }
    
    if (hasValidData && totalYesterday > 0) {
      dayChangePct.value = ((totalValue.value - totalYesterday) / totalYesterday) * 100
      dayChangeAmount.value = totalValue.value - totalYesterday
    } else {
      const totalChange = portfolio.value.reduce((sum, crypto) => {
        const change = Number(crypto.change_24h_pct || crypto.price_change_percentage_24h || 0)
        return sum + change
      }, 0)
      
      dayChangePct.value = portfolio.value.length > 0 ? totalChange / portfolio.value.length : 0
      dayChangeAmount.value = totalValue.value * (dayChangePct.value / 100)
    }
    
  } catch (err) {
    console.error('Error loading wallet:', err)
    totalValue.value = 0
    dayChangePct.value = 0
    dayChangeAmount.value = 0
  } finally {
    loadingWallet.value = false
  }
}

const userInitials = computed(() => {
  if (!user.value?.name) return 'U'
  return user.value.name
    .split(' ')
    .map((word: string) => word[0])
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

const getProfilePictureUrl = (profilePicture: string | null | undefined): string | undefined => {
  if (!profilePicture) return undefined
  if (profilePicture.startsWith('http')) return profilePicture
  const baseUrl = (import.meta.env as any).VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${profilePicture}`
}

async function refreshWallet() {
  await loadWalletData()
}

onMounted(async () => {
  await loadWalletData()
  
  const onBalanceUpdated = async (e: any) => {
    try {
      if (!String(role.value).toUpperCase().includes('ADMIN')) {
        if (e?.detail?.balance !== undefined) {
          user.value = { ...(user.value || {}), solde: e.detail.balance }
        } else {
          const profile = await api.auth.profile()
          user.value = profile || user.value
        }
        await loadWalletData()
      }
    } catch (err) {
      console.warn('Error updating balance', err)
    }
  }

  window.addEventListener('balance-updated', onBalanceUpdated)

  onUnmounted(() => {
    window.removeEventListener('balance-updated', onBalanceUpdated)
  })

  fetchNotifications()
})

const menuItems = computed(() => {
  const isAdmin = String(role.value).toUpperCase().includes('ADMIN')
  return isAdmin ? [
    { label: 'Overview', icon: '📊', path: '/dashboard/admin/overview' },
    { label: 'Manage clients', icon: '👥', path: '/dashboard/admin/clients' },
    { label: 'Manage cryptos', icon: '💱', path: '/dashboard/admin/cryptos' },
    { label: 'Manage transactions', icon: '📋', path: '/dashboard/admin/transactions' },
    { label: 'Settings', icon: '⚙️', path: '/dashboard/admin/settings' }
  ] : [
    { label: 'Overview', icon: '📈', path: '/dashboard/overview' },
    { label: 'Cryptos', icon: '💱', path: '/dashboard/cryptos' },
    { label: 'History', icon: '📋', path: '/dashboard/transactions' },
    { label: 'Profile', icon: '👤', path: '/dashboard/Portfolio' }
  ]
})

const isPositiveChange = computed(() => dayChangePct.value >= 0)
const displayValue = computed(() => loadingWallet.value ? '...' : formatCurrency(totalValue.value))
const displayPercentage = computed(() => loadingWallet.value ? '...' : formatPercentage(dayChangePct.value))
</script>

<template>
  <div class="min-h-screen w-full bg-white flex flex-col">

    <!-- HEADER -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-200/80 bg-white/90 backdrop-blur-xl supports-[backdrop-filter]:bg-white/80 shadow-sm">
      <div class="px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16">

          <!-- LEFT SIDE - Logo & Mobile Menu -->
          <div class="flex items-center gap-3 flex-1">
            <!-- Mobile Menu Button -->
            <button
              class="lg:hidden p-2.5 text-slate-600 hover:bg-slate-100/80 rounded-xl transition-all duration-200 hover:scale-105"
              @click="showMobileSidebar = true"
            >
              <Menu class="w-5 h-5" />
            </button>

            <!-- Logo -->
            <div class="flex items-center gap-3">
              <img 
                src="/assets/bitchest_logo.png"
                class="h-8 w-auto hover:opacity-80 transition-all duration-300 hover:scale-105"
                alt="Bitchest"
              />
              <div class="hidden sm:block h-6 w-px bg-slate-200/60"></div>
            </div>

            <!-- Role Badge - Desktop -->
            <div class="hidden sm:flex items-center">
              <Badge 
                class="px-3 py-1.5 text-xs font-medium bg-gradient-to-r from-blue-50 to-indigo-50 text-slate-700 border-slate-200/60"
                variant="outline"
              >
                {{ String(role).toLowerCase() }}
              </Badge>
            </div>
          </div>

          <!-- RIGHT SIDE - Actions & User -->
          <div class="flex items-center gap-2">
            
            <!-- Wallet Info - Desktop -->
            <div v-if="!String(role).toUpperCase().includes('ADMIN')" class="hidden lg:flex items-center gap-4 mr-4">
              <div class="text-right">
                <div class="text-sm font-semibold text-slate-900 flex items-center gap-2">
                  <span>{{ displayValue }}</span>
                  <TrendingUp v-if="isPositiveChange" class="w-4 h-4 text-green-500" />
                  <TrendingDown v-else class="w-4 h-4 text-rose-500" />
                </div>
                <div 
                  class="text-xs font-medium"
                  :class="isPositiveChange ? 'text-green-600' : 'text-rose-600'"
                >
                  {{ displayPercentage }}
                </div>
              </div>
              <Button
                variant="ghost"
                size="sm"
                class="h-9 w-9 p-0 hover:bg-slate-100/80 rounded-xl"
                :disabled="loadingWallet"
                @click="refreshWallet"
              >
                <RefreshCw class="w-4 h-4" :class="{'animate-spin': loadingWallet}" />
              </Button>
            </div>

            <!-- Notifications -->
            <button
              class="relative p-2.5 text-slate-600 hover:bg-slate-100/80 rounded-xl transition-all duration-200 hover:scale-105"
              aria-label="Open notifications"
              @click="toggleNotifications"
            >
              <Bell class="w-5 h-5" />
              <span
                v-if="unreadCount > 0"
                class="absolute -top-1 -right-1 inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-gradient-to-r from-rose-500 to-pink-500 rounded-full border-2 border-white shadow-sm"
              >
                {{ unreadCount > 9 ? '9+' : unreadCount }}
              </span>
            </button>

            <!-- Separator -->
            <div class="h-8 w-px bg-slate-200/60 mx-1 hidden sm:block"></div>

         <!-- User Menu -->
<DropdownMenu>
  <DropdownMenuTrigger as-child>
    <button class="flex items-center gap-3 p-1.5 rounded-xl hover:bg-slate-100/80 transition-all duration-200 group">
      <!-- Avatar avec effet de hover -->
      <div class="relative">
        <Avatar class="h-9 w-9 border-2 border-slate-200/60 group-hover:border-slate-300 transition-all duration-300 group-hover:scale-105">
          <AvatarImage 
            :src="getProfilePictureUrl(user?.profile_picture) || ''" 
            :alt="user?.name || 'User'"
            class="object-cover"
          />
          <AvatarFallback class="bg-gradient-to-br from-slate-100 to-slate-200 text-slate-700 font-semibold text-sm">
            {{ userInitials }}
          </AvatarFallback>
        </Avatar>
        <div class="absolute -bottom-1 -right-1 w-3 h-3 bg-green-400 border-2 border-white rounded-full"></div>
      </div>

      <!-- User Info - Desktop -->
      <div class="hidden lg:block text-left">
        <div class="text-sm font-semibold text-slate-900 leading-tight">
          {{ user?.name || user?.email }}
        </div>
        <div class="text-xs text-slate-500 leading-tight">
          {{ user?.email }}
        </div>
      </div>

      <!-- Chevron -->
      <ChevronDown class="w-4 h-4 text-slate-400 hidden lg:block transition-transform group-hover:scale-110" />
    </button>
  </DropdownMenuTrigger>

  <DropdownMenuContent class="w-56 mr-2 mt-2" align="end">
    <DropdownMenuLabel class="p-4">
      <div class="flex items-center gap-3">
        <Avatar class="h-10 w-10 border border-slate-200">
          <AvatarImage 
            :src="getProfilePictureUrl(user?.profile_picture) || ''" 
            :alt="user?.name || 'User'"
          />
          <AvatarFallback class="bg-slate-100 text-slate-700">
            {{ userInitials }}
          </AvatarFallback>
        </Avatar>
        <div class="flex-1 min-w-0">
          <div class="font-semibold text-slate-900 truncate">
            {{ user?.name }}
          </div>
          <div class="text-sm text-slate-500 truncate">
            {{ user?.email }}
          </div>
        </div>
      </div>
    </DropdownMenuLabel>

    <DropdownMenuSeparator />

    <!-- SEULEMENT UN ITEM SELON LE RÔLE -->
    <DropdownMenuItem
      v-if="String(role).toUpperCase().includes('ADMIN')"
      class="cursor-pointer p-3"
      @click="router.push('/dashboard/admin/settings')"
    >
      <Settings class="w-4 h-4 mr-3" />
      <span>Settings</span>
    </DropdownMenuItem>

    <DropdownMenuItem
      v-else
      class="cursor-pointer p-3"
      @click="router.push('/dashboard/portfolio')"
    >
      <User class="w-4 h-4 mr-3" />
      <span>Profile</span>
    </DropdownMenuItem>

    <DropdownMenuSeparator />

    <DropdownMenuItem 
      class="cursor-pointer p-3 text-rose-600 focus:text-rose-600 focus:bg-rose-50/50"
      @click="logout"
    >
      <LogOut class="w-4 h-4 mr-3" />
      <span>Logout</span>
    </DropdownMenuItem>
  </DropdownMenuContent>
</DropdownMenu>
          </div>

        </div>
      </div>
    </header>
  
    <!-- MAIN LAYOUT -->
    <div class="flex flex-1 min-h-0 w-full">

      <!-- DESKTOP SIDEBAR -->
      <aside
        class="hidden lg:flex flex-col w-72 border-r border-slate-200 bg-white shadow-sm sticky left-0 top-[4.5rem] h-[calc(100vh-4.5rem)] overflow-y-auto"
      >
        <div class="p-6">
          <CustomSidebar
            :menu-items="menuItems"
            :portfolio="portfolio"
            :total-value="totalValue"
            :day-change-pct="dayChangePct"
            :role="role"
          />
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="flex-1 overflow-y-auto min-h-0">
        <div class="px-4 sm:px-8 lg:px-12 py-10 max-w-full">
          <Card class="bg-white border border-slate-200 shadow-sm rounded-3xl w-full min-h-[calc(100vh-12rem)]">
            <CardContent class="p-6 sm:p-10">
              <router-view />
            </CardContent>
          </Card>
        </div>
      </main>

    </div>

    <!-- NOTIFICATIONS PANEL -->
    <Drawer :open="showNotifications" direction="right" @update:open="v => showNotifications = v">
      <DrawerContent class="w-full sm:w-96 lg:w-[420px] h-full ml-auto border-l border-slate-200 bg-white shadow-xl flex flex-col">

        <div class="p-6 border-b border-slate-200 bg-gradient-to-r from-slate-50 to-white">
          <div class="flex items-center justify-between mb-4">
            <div>
              <h2 class="text-lg font-bold text-slate-900">Notifications</h2>
              <p class="text-sm text-slate-500 mt-1">
                <span class="font-semibold text-blue-600">{{ unreadCount }}</span> unread • 
                <span class="font-semibold text-slate-700">{{ notifications.length }}</span> total
              </p>
            </div>
            <button class="p-2 hover:bg-white rounded-xl transition-colors" @click="showNotifications = false">
              <X class="w-5 h-5 text-slate-700" />
            </button>
          </div>

          <!-- Mark all as read button -->
          <Button
            v-if="unreadCount > 0"
            size="sm"
            class="w-full bg-blue-50 hover:bg-blue-100 text-blue-700 border border-blue-200 rounded-lg h-9"
            @click="markAllAsRead"
          >
            ✓ Mark as read
          </Button>
        </div>

        <ScrollArea class="flex-1">
          <!-- Loading -->
          <div v-if="loadingNotifications" class="text-center py-16 px-6">
            <div class="animate-spin h-10 w-10 border-b-2 border-blue-500 rounded-full mx-auto"></div>
            <p class="text-slate-600 mt-4 text-sm">Loading notifications...</p>
          </div>

          <!-- Empty -->
          <div v-else-if="notifications.length === 0" class="text-center py-20 px-6">
            <div class="text-6xl opacity-40 mb-3">📭</div>
            <p class="text-lg font-semibold text-slate-700">No notifications yet</p>
            <p class="text-sm text-slate-500 mt-2">You're all caught up!</p>
          </div>

          <!-- NOTIFICATIONS LIST -->
          <div v-else class="divide-y divide-slate-200">
            <div
              v-for="n in notifications"
              :key="n.id"
              class="p-4 transition-all duration-200 cursor-pointer hover:bg-slate-50/60 group"
              :class="n.is_read ? 'bg-white' : 'bg-blue-50/40 border-l-4 border-blue-500'"
              @click="!n.is_read && markNotificationAsRead(n)"
            >
              <!-- Header with icon, type badge and timestamp -->
              <div class="flex gap-4 mb-3">
                <!-- Icon -->
                <div
                  class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-semibold transition-transform group-hover:scale-110"
                  :class="n.is_read 
                    ? 'bg-slate-100 text-slate-600' 
                    : 'bg-blue-100 text-blue-600 shadow-md'"
                >
                  {{ getNotificationIcon(n.type) }}
                </div>

                <!-- Type badge and read status -->
                <div class="flex-1 min-w-0 flex items-start justify-between gap-2">
                  <div class="flex gap-2 flex-wrap items-center">
                    <!-- Type Badge -->
                    <Badge
                      class="text-xs font-semibold px-2.5 py-1 rounded-full"
                      :class="n.type === 'transaction' 
                        ? 'bg-emerald-100 text-emerald-700 border-emerald-200' 
                        : n.type === 'security'
                        ? 'bg-orange-100 text-orange-700 border-orange-200'
                        : n.type === 'alert'
                        ? 'bg-red-100 text-red-700 border-red-200'
                        : n.type === 'success'
                        ? 'bg-green-100 text-green-700 border-green-200'
                        : 'bg-slate-100 text-slate-700 border-slate-200'"
                    >
                      {{ n.type?.toUpperCase() || 'INFO' }}
                    </Badge>

                    <!-- Read status indicator -->
                    <div v-if="!n.is_read" class="flex items-center gap-1">
                      <div class="w-2 h-2 bg-blue-500 rounded-full animate-pulse"></div>
                      <span class="text-xs font-medium text-blue-600">New</span>
                    </div>
                  </div>

                  <!-- Timestamp -->
                  <span class="text-xs text-slate-500 whitespace-nowrap flex-shrink-0 bg-white/60 px-2 py-1 rounded-full">
                    {{ formatNotificationDate(n.created_at) }}
                  </span>
                </div>
              </div>

              <!-- Title (always bold and visible) -->
              <h3 class="font-bold text-slate-900 mb-2 text-sm leading-snug pr-2">
                {{ n.title }}
              </h3>

              <!-- Message (full text, separated from title) -->
              <p class="text-slate-600 text-sm leading-relaxed whitespace-pre-wrap mb-3 bg-white/40 p-3 rounded-lg border-l-2 border-slate-200">
                {{ n.message }}
              </p>

              <!-- Additional Info Row (if needed) -->
              <div v-if="n.metadata || n.related_id" class="flex items-center justify-between pt-2 border-t border-slate-200/50 mt-2">
                <span v-if="n.metadata" class="text-xs text-slate-500 font-mono">
                  ID: {{ n.metadata }}
                </span>
                <span v-else-if="n.related_id" class="text-xs text-slate-500 font-mono">
                  Ref: {{ n.related_id }}
                </span>
              </div>

              <!-- Action button for unread -->
              <div v-if="!n.is_read" class="mt-3 flex gap-2">
                <Button
                  size="sm"
                  class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-xs h-8 rounded-lg"
                  @click.stop="markNotificationAsRead(n)"
                >
                  Mark as read
                </Button>
              </div>
              <div v-else class="mt-2">
                <span class="text-xs text-slate-400 italic">✓ Read</span>
              </div>
            </div>
          </div>

        </ScrollArea>

      </DrawerContent>
    </Drawer>

    <!-- MOBILE SIDEBAR -->
    <Drawer :open="showMobileSidebar" direction="left" @update:open="v => showMobileSidebar = v">
      <DrawerContent class="w-[85vw] max-w-sm h-full border-r border-slate-200 bg-white shadow-xl flex flex-col">

        <div class="p-6 border-b border-slate-200 flex justify-between items-center">
          <h2 class="text-lg font-bold text-slate-900">Navigation</h2>
          <button class="p-2 hover:bg-slate-100 rounded-xl" @click="showMobileSidebar = false">
            <X class="w-5 h-5" />
          </button>
        </div>

        <ScrollArea class="flex-1 p-8">
          <CustomSidebar
            :menu-items="menuItems"
            :portfolio="portfolio"
            :total-value="totalValue"
            :day-change-pct="dayChangePct"
            :role="role"
          />
        </ScrollArea>

      </DrawerContent>
    </Drawer>
  </div>
</template>

<style scoped>
.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>