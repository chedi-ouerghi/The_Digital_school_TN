<script setup lang="ts">
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { useRouter } from 'vue-router';
import auth from '../services/auth';
import api from '../services/api';
import CustomSidebar from '@/components/CustomSidebar.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Drawer, DrawerContent } from '@/components/ui/drawer';
import { Badge } from '@/components/ui/badge';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Bell, LogOut, Menu, X, TrendingUp, TrendingDown, RefreshCw } from 'lucide-vue-next';

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
    notifications.value = Array.isArray(res) ? res : (res?.data || res?.notifications || res || [])
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
    console.log('Wallet API Response:', response) // Debug log
    
    const walletData = response?.wallet || response?.data || response || {}
    
    portfolio.value = walletData.cryptomonnaies || walletData.cryptos || []
    totalValue.value = Number(walletData.current_value || walletData.total_value || 0)
    
    console.log('Portfolio data:', {
      portfolio: portfolio.value,
      totalValue: totalValue.value,
      walletData
    })
    
    // Calcul plus robuste du changement 24h
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
      // Fallback: utiliser les données de change_24h_pct si disponibles
      const totalChange = portfolio.value.reduce((sum, crypto) => {
        const change = Number(crypto.change_24h_pct || crypto.price_change_percentage_24h || 0)
        return sum + change
      }, 0)
      
      dayChangePct.value = portfolio.value.length > 0 ? totalChange / portfolio.value.length : 0
      dayChangeAmount.value = totalValue.value * (dayChangePct.value / 100)
    }
    
  } catch (err) {
    console.error('Error loading wallet:', err)
    // Fallback values for debugging
    totalValue.value = 0
    dayChangePct.value = 0
    dayChangeAmount.value = 0
  } finally {
    loadingWallet.value = false
  }
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
        // Recharger les données du wallet après mise à jour du solde
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
const displayValue = computed(() => loadingWallet ? '...' : formatCurrency(totalValue))
const displayPercentage = computed(() => loadingWallet ? '...' : formatPercentage(dayChangePct.value))
</script>

<template>
  <div class="min-h-screen w-full bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/20 flex flex-col">
    
    <!-- HEADER - Full width -->
    <header class="sticky top-0 z-50 w-full border-b border-slate-200/60 bg-white/90 backdrop-blur-xl shadow-sm">
      <div class="w-full px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">

          <!-- Left: Logo + Mobile Toggle -->
          <div class="flex items-center gap-4 flex-1">
            <!-- Logo -->
            <div class="flex items-center gap-4">
              <img 
                src="/assets/bitchest_logo.png" 
                alt="BitChest" 
                class="h-8 lg:h-10 w-auto hover:opacity-80 transition-opacity"
              />
              <div class="hidden sm:block h-6 w-px bg-slate-300"></div>
            </div>

            <!-- Mobile Toggle -->
            <button 
              @click="showMobileSidebar = true"
              class="lg:hidden p-2 text-slate-600 hover:text-[#38618C] hover:bg-slate-100 rounded-xl transition-all duration-200"
            >
              <Menu class="w-5 h-5" />
            </button>

            <!-- Portfolio Value (Desktop) -->
            <div v-if="!String(role).toUpperCase().includes('ADMIN')" class="hidden lg:flex items-center gap-6">
              <div class="flex items-center gap-4 bg-white/80 rounded-2xl px-4 py-2 border border-slate-200/60 shadow-xs">
                <div class="text-right">
                  <div class="text-sm font-medium text-slate-600">Portfolio Value</div>
                  <div class="text-xl font-bold text-slate-900 tracking-tight">{{ displayValue }}</div>
                </div>
                
                <Separator orientation="vertical" class="h-8 bg-slate-300" />
                
                <div class="text-right">
                  <div class="flex items-center gap-2 text-sm font-semibold" 
                       :class="isPositiveChange ? 'text-emerald-600' : 'text-rose-600'">
                    <TrendingUp v-if="isPositiveChange" class="w-4 h-4" />
                    <TrendingDown v-else class="w-4 h-4" />
                    {{ displayPercentage }}
                  </div>
                  <div class="text-xs text-slate-500 mt-0.5">24h change</div>
                </div>

                <Button 
                  @click="refreshWallet" 
                  variant="ghost" 
                  size="sm"
                  class="h-8 w-8 p-0 hover:bg-slate-100 transition-colors"
                  :disabled="loadingWallet"
                >
                  <RefreshCw class="w-3.5 h-3.5" :class="{ 'animate-spin': loadingWallet }" />
                </Button>
              </div>
            </div>
          </div>

          <!-- Right: Notifications + User -->
          <div class="flex items-center gap-3 flex-shrink-0">

            <!-- Portfolio Value (Mobile) -->
            <div v-if="!String(role).toUpperCase().includes('ADMIN')" class="lg:hidden">
              <div class="text-right bg-white/80 rounded-xl px-3 py-2 border border-slate-200/60">
                <div class="text-xs font-medium text-slate-600">Portfolio</div>
                <div class="text-sm font-bold text-slate-900">{{ displayValue }}</div>
              </div>
            </div>

            <!-- Notifications -->
            <div class="relative">
              <button 
                @click="toggleNotifications"
                class="relative p-2 text-slate-600 hover:text-[#38618C] hover:bg-slate-100 rounded-xl transition-all duration-200 group"
                :class="{ 'text-[#38618C] bg-slate-100': showNotifications }"
              >
                <Bell class="w-5 h-5" />
                <span 
                  v-if="unreadCount > 0" 
                  class="absolute -top-1 -right-1 inline-flex items-center justify-center min-w-5 h-5 text-xs font-bold text-white bg-rose-500 rounded-full shadow-sm border-2 border-white"
                >
                  {{ unreadCount > 9 ? '9+' : unreadCount }}
                </span>
              </button>
            </div>

            <Separator orientation="vertical" class="h-6 bg-slate-300 hidden sm:block" />

            <!-- User Info -->
            <div class="flex items-center gap-3">
              <div class="hidden sm:block text-right">
                <div class="text-sm font-semibold text-slate-900 leading-tight">
                  {{ user?.name || user?.email }}
                </div>
                <div class="text-xs text-slate-500 leading-tight">
                  {{ String(role).includes('ADMIN') ? 'Administrator' : 'Client' }}
                </div>
              </div>

              <Button 
                @click="logout"
                class="bg-gradient-to-r from-rose-500 to-pink-600 hover:from-rose-600 hover:to-pink-700 text-white font-semibold shadow-sm hover:shadow-md flex items-center gap-2 px-4 h-10 transition-all duration-200 rounded-xl"
              >
                <LogOut class="w-4 h-4" />
                <span class="hidden sm:inline">Logout</span>
              </Button>
            </div>
          </div>
        </div>
      </div>
    </header>

    <!-- MAIN LAYOUT - Full width -->
    <div class="flex flex-1 w-full overflow-hidden">

      <!-- DESKTOP SIDEBAR -->
      <aside 
        class="hidden lg:block flex-shrink-0 w-80 border-r border-slate-200 bg-white/80 backdrop-blur-sm sticky-sidebar"
      >
        <div class="p-6">
          <CustomSidebar 
            :menuItems="menuItems"
            :portfolio="portfolio"
            :totalValue="totalValue"
            :dayChangePct="dayChangePct"
            :role="role"
          />
        </div>
      </aside>

      <!-- MAIN CONTENT AREA - Full width -->
      <main class="flex-1 overflow-y-auto w-full bg-transparent">
        <div class="w-full h-full px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
          <!-- Content Container with full width -->
          <div class="w-full h-full">
            <Card class="border-slate-200/60 bg-white/70 backdrop-blur-sm shadow-xs hover:shadow-sm transition-all duration-300 w-full h-full min-h-[calc(100vh-12rem)]">
              <CardContent class="p-6 sm:p-8 lg:p-10 w-full h-full">
                <router-view />
              </CardContent>
            </Card>
          </div>
        </div>
      </main>
    </div>

    <!-- DRAWER NOTIFICATIONS -->
    <Drawer :open="showNotifications" @openChange="val => showNotifications = val" direction="right">
      <DrawerContent class="w-full sm:w-96 lg:w-[420px] h-full ml-auto rounded-l-2xl border-l border-slate-200 bg-white/95 backdrop-blur-xl flex flex-col shadow-2xl">
        
        <!-- Header Panel -->
        <div class="flex-none border-b border-slate-200 bg-gradient-to-r from-[#38618C] to-[#35A7FF] text-white px-6 py-5">
          <div class="flex items-center justify-between">
            <div>
              <h2 class="text-xl font-bold">Notifications</h2>
              <p class="text-sm text-blue-100/90 mt-1">
                {{ unreadCount }} unread of {{ notifications.length }}
              </p>
            </div>
            <button 
              @click="showNotifications = false" 
              class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200"
            >
              <X class="w-5 h-5" />
            </button>
          </div>
        </div>

        <!-- Scrollable Content -->
        <ScrollArea class="flex-1">
          <div class="p-6">
            <div v-if="loadingNotifications" class="flex items-center justify-center py-16">
              <div class="text-center">
                <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-[#35A7FF] mx-auto mb-4"></div>
                <p class="text-slate-600 font-medium">Loading notifications...</p>
              </div>
            </div>

            <div v-else-if="notifications.length === 0" class="text-center py-20">
              <div class="text-6xl mb-5 opacity-40">📭</div>
              <p class="text-slate-700 font-semibold text-lg">No notifications</p>
              <p class="text-slate-500 text-sm mt-2">We'll notify you when something arrives</p>
            </div>

            <div v-else class="space-y-4">
              <div 
                v-for="n in notifications" 
                :key="n.id"
                @click="!n.is_read && markNotificationAsRead(n)"
                class="group p-5 rounded-2xl border-2 transition-all duration-300 cursor-pointer hover:shadow-md hover:scale-[1.02]"
                :class="n.is_read 
                  ? 'bg-white border-slate-200/80 hover:border-slate-300' 
                  : 'bg-blue-50/80 border-blue-200 hover:border-blue-300'"
              >
                <div class="flex gap-4">
                  <div 
                    class="flex-shrink-0 w-12 h-12 rounded-xl flex items-center justify-center text-xl shadow-sm"
                    :class="n.is_read 
                      ? 'bg-slate-100 text-slate-600' 
                      : 'bg-blue-100 text-blue-600 shadow-blue-200/50'"
                  >
                    {{ getNotificationIcon(n.type) }}
                  </div>

                  <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-start gap-3 mb-2">
                      <h3 class="font-semibold text-slate-900 text-base leading-tight group-hover:text-[#38618C] transition-colors">
                        {{ n.title }}
                      </h3>
                      <span class="text-xs text-slate-500 whitespace-nowrap flex-shrink-0 bg-slate-100 px-2 py-1 rounded-full">
                        {{ formatNotificationDate(n.created_at) }}
                      </span>
                    </div>
                    <p class="text-slate-600 text-sm leading-relaxed line-clamp-3">
                      {{ n.message }}
                    </p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </ScrollArea>

        <!-- Footer Actions -->
        <div v-if="notifications.length > 0 && !loadingNotifications" class="flex-none border-t border-slate-200 p-6">
          <Button 
            @click="markAllAsRead" 
            variant="outline" 
            class="w-full border-slate-300 text-slate-700 hover:bg-slate-50 hover:border-slate-400 rounded-xl h-12 font-medium transition-all duration-200"
            :disabled="unreadCount === 0"
          >
            Mark all as read
          </Button>
        </div>
      </DrawerContent>
    </Drawer>

    <!-- MOBILE SIDEBAR -->
    <Drawer :open="showMobileSidebar" @openChange="v => showMobileSidebar = v" direction="left">
      <DrawerContent class="w-[85vw] max-w-sm h-full rounded-r-2xl border-r border-slate-200 bg-white/95 backdrop-blur-xl flex flex-col shadow-2xl">
        <div class="flex-none p-6 bg-gradient-to-r from-[#38618C] to-[#35A7FF] text-white flex justify-between items-center">
          <h2 class="text-xl font-bold">Navigation</h2>
          <button 
            @click="showMobileSidebar = false" 
            class="p-2 hover:bg-white/20 rounded-xl transition-colors duration-200"
          >
            <X class="w-5 h-5" />
          </button>
        </div>

        <ScrollArea class="flex-1">
          <div class="p-6">
            <CustomSidebar 
              :menuItems="menuItems"
              :portfolio="portfolio"
              :totalValue="totalValue"
              :dayChangePct="dayChangePct"
              :role="role"
            />
          </div>
        </ScrollArea>
      </DrawerContent>
    </Drawer>
  </div>
</template>

<style scoped>
.sticky-sidebar {
  position: sticky;
  top: 5rem;
  height: calc(100vh - 5rem);
  overflow-y: auto;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-3 {
  display: -webkit-box;
  -webkit-line-clamp: 3;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Custom scrollbar */
.sticky-sidebar::-webkit-scrollbar {
  width: 6px;
}

.sticky-sidebar::-webkit-scrollbar-track {
  background: transparent;
}

.sticky-sidebar::-webkit-scrollbar-thumb {
  background: rgba(148, 163, 184, 0.3);
  border-radius: 3px;
}

.sticky-sidebar::-webkit-scrollbar-thumb:hover {
  background: rgba(148, 163, 184, 0.5);
}

/* Smooth transitions */
* {
  transition-property: color, background-color, border-color, text-decoration-color, fill, stroke, opacity, box-shadow, transform, filter, backdrop-filter;
  transition-timing-function: cubic-bezier(0.4, 0, 0.2, 1);
  transition-duration: 200ms;
}
</style>