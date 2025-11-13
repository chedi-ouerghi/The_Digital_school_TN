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

const router = useRouter()
const user = ref(auth.getUser())
const role = ref(auth.getRole())

function logout() {
  auth.logout()
  router.push('/signin')
}

// Données wallet
const portfolio = ref<any[]>([])
const totalValue = ref(0)
const dayChangePct = ref(0)

// Notifications
const notifications = ref<any[]>([])
const unreadCount = computed(() => notifications.value.filter(n => !n.is_read).length)
const showNotifications = ref(false)
const loadingNotifications = ref(false)
const notifError = ref<string | null>(null)

async function fetchNotifications() {
  loadingNotifications.value = true
  notifError.value = null
  try {
    const res = await api.notifications.list()
    // backend retourne liste d'objets Notification (user-specific)
    notifications.value = Array.isArray(res) ? res : (res?.data || res?.notifications || res || [])
  } catch (err: any) {
    console.error('Erreur notifications:', err)
    notifError.value = err?.message || String(err)
  } finally {
    loadingNotifications.value = false
  }
}

async function markNotificationAsRead(n: any) {
  try {
    await api.notifications.markAsRead(n.id)
    // mettre à jour localement
    const idx = notifications.value.findIndex((x: any) => x.id === n.id)
    if (idx !== -1) notifications.value[idx].is_read = true
  } catch (err) {
    console.warn('Impossible de marquer la notification comme lue', err)
  }
}

async function markAllAsRead() {
  try {
    const unreadNotifications = notifications.value.filter(n => !n.is_read)
    for (const n of unreadNotifications) {
      await markNotificationAsRead(n)
    }
  } catch (err) {
    console.warn('Impossible de marquer toutes les notifications comme lues', err)
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

  if (diffMins < 1) return 'À l\'instant'
  if (diffMins < 60) return `Il y a ${diffMins} min`
  if (diffHours < 24) return `Il y a ${diffHours} h`
  if (diffDays === 1) return 'Hier'
  if (diffDays < 7) return `Il y a ${diffDays} j`
  
  return date.toLocaleDateString('fr-FR', { 
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

onMounted(async () => {
  try {
    // Ne charger le wallet que pour les clients
    if (!String(role.value).toUpperCase().includes('ADMIN')) {
      const response = await api.wallet.list()
      const walletData = response?.wallet || {}
      
      portfolio.value = walletData.cryptomonnaies || []
      totalValue.value = Number(walletData.current_value || 0)
      
      // Calcul variation 24h
      let totalYesterday = 0
      for (const crypto of portfolio.value) {
        const qty = Number(crypto.pivot?.quantity || 0)
        const price = Number(crypto.price_eur || 0)
        const changePct = Number(crypto.change_24h_pct || 0)
        totalYesterday += qty * price / (1 + changePct / 100)
      }
      
      dayChangePct.value = totalYesterday > 0 
        ? ((totalValue.value - totalYesterday) / totalYesterday * 100)
        : 0
    }
      
  } catch (err) {
    console.error('Erreur chargement wallet:', err)
  }
  
  // Listener pour mises à jour du solde
  const onBalanceUpdated = async (e: any) => {
    try {
      // Ne mettre à jour le solde que pour les clients
      if (!String(role.value).toUpperCase().includes('ADMIN')) {
        if (e?.detail?.balance !== undefined) {
          user.value = { ...(user.value || {}), solde: e.detail.balance }
        } else {
          const profile = await api.auth.profile()
          user.value = profile || user.value
        }
      }
    } catch (err) {
      console.warn('Erreur lors de la mise à jour du solde', err)
    }
  }

  window.addEventListener('balance-updated', onBalanceUpdated)

  // cleanup
  onUnmounted(() => {
    window.removeEventListener('balance-updated', onBalanceUpdated)
  })

  // fetch initial notifications (light)
  fetchNotifications()
})

// Menu items selon le rôle
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
    { label: 'Portfolio', icon: '💰', path: '/dashboard/portfolio' },
    { label: 'Cryptos ', icon: '💱', path: '/dashboard/cryptos' },
    { label: 'History', icon: '📋', path: '/dashboard/transactions' },
    { label: 'Profile', icon: '👤', path: '/dashboard/profile' }
  ]
})


</script>

<template>
  <div class="min-h-screen w-full bg-gray-50">
    <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-6">
      <!-- Header -->
      <header class="relative flex items-center justify-between mb-8">
        <div class="flex items-center gap-6">
          <img 
            src="/assets/bitchest_logo.png" 
            alt="BitChest" 
            class="h-12 w-auto"
          />
        </div>

        <div class="flex items-center gap-6">
          <!-- Notifications: trigger + Drawer -->
          <div class="relative">
            <!-- trigger button -->
            <button 
              @click="toggleNotifications"
              class="relative p-3 text-[#38618C] hover:bg-gray-50 rounded-xl transition-all duration-200 border border-transparent hover:border-gray-200 group"
              aria-label="Notifications"
            >
              <div class="relative">
                <span class="text-xl transition-transform group-hover:scale-110">🔔</span>
                <span 
                  v-if="unreadCount > 0" 
                  class="absolute -top-2 -right-2 bg-[#FF5964] text-white text-xs rounded-full min-w-[20px] h-5 flex items-center justify-center px-1 font-semibold shadow-lg animate-pulse"
                >
                  {{ unreadCount > 99 ? '99+' : unreadCount }}
                </span>
              </div>
            </button>

            <!-- Drawer component -->
            <Drawer :open="showNotifications" @openChange="(val) => { showNotifications = val }" direction="right">
              <!-- Drawer: full height column so middle area can scroll -->
              <DrawerContent class="w-full sm:w-[420px] h-screen ml-auto rounded-none border-l drawer-slide-in flex flex-col">
                <!-- HEADER (fixé) -->
                <div class="flex-none flex items-center justify-between p-6 border-b bg-gradient-to-r from-[#38618C] to-[#35A7FF] text-white">
                  <div class="flex items-center gap-3">
                    <div class="text-xl font-bold">Notifications</div>
                    <Badge v-if="unreadCount > 0" class="bg-white text-[#38618C] font-semibold">
                      {{ unreadCount }} non lu{{ unreadCount > 1 ? 's' : '' }}
                    </Badge>
                  </div>
                  <div class="flex items-center gap-2">
                    <button 
                      v-if="unreadCount > 0"
                      @click="markAllAsRead"
                      class="text-sm bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg transition-colors"
                    >
                      Tout marquer lu
                    </button>
                    <button 
                      @click="fetchNotifications"
                      class="p-2 hover:bg-white/20 rounded-lg transition-colors"
                      :class="{ 'animate-spin': loadingNotifications }"
                    >
                      🔄
                    </button>
                  </div>
                </div>

                <!-- BODY (flexible, défilement vertical) -->
                <div class="flex-1 flex flex-col p-0 overflow-hidden">
                  <ScrollArea class="flex-1 overflow-y-auto scrollbar-custom">
                    <div class="p-4">
                      <!-- Loading State -->
                      <div v-if="loadingNotifications" class="flex flex-col items-center justify-center py-12 text-gray-500">
                        <div class="animate-spin text-2xl mb-4">⏳</div>
                        <div>Chargement des notifications...</div>
                      </div>

                      <!-- Error State -->
                      <div v-else-if="notifError" class="text-center py-12 text-red-600">
                        <div class="text-4xl mb-4">❌</div>
                        <div class="font-semibold mb-2">Erreur de chargement</div>
                        <div class="text-sm text-gray-600 mb-4">{{ notifError }}</div>
                        <Button @click="fetchNotifications" class="bg-[#35A7FF] hover:bg-[#38618C] text-white">
                          Réessayer
                        </Button>
                      </div>

                      <!-- Empty State -->
                      <div v-else-if="notifications.length === 0" class="text-center py-16 text-gray-500">
                        <div class="text-6xl mb-4">📭</div>
                        <div class="font-semibold text-lg mb-2">Aucune notification</div>
                        <div class="text-sm">Vous serez notifié des activités importantes ici</div>
                      </div>

                      <!-- Notifications List -->
                      <div v-else class="space-y-3">
                        <div 
                          v-for="n in notifications" 
                          :key="n.id" 
                          class="group p-4 rounded-xl border transition-all duration-200 hover:shadow-md"
                          :class="n.is_read ? 'bg-white border-gray-200' : 'bg-blue-50 border-[#35A7FF]'"
                        >
                          <div class="flex gap-4">
                            <!-- Notification Icon -->
                            <div class="flex-shrink-0">
                              <div 
                                class="w-12 h-12 rounded-xl flex items-center justify-center text-xl"
                                :class="n.is_read ? 'bg-gray-100 text-gray-600' : 'bg-[#35A7FF] text-white'"
                              >
                                {{ getNotificationIcon(n.type) }}
                              </div>
                            </div>

                            <!-- Notification Content -->
                            <div class="flex-1 min-w-0">
                              <div class="flex items-start justify-between gap-2 mb-2">
                                <h3 
                                  class="font-semibold text-sm leading-tight"
                                  :class="n.is_read ? 'text-gray-800' : 'text-[#38618C]'"
                                >
                                  {{ n.title }}
                                </h3>
                                <div class="flex items-center gap-2 flex-shrink-0">
                                  <span class="text-xs text-gray-500 whitespace-nowrap">
                                    {{ formatNotificationDate(n.created_at) }}
                                  </span>
                                </div>
                              </div>
                              
                              <p class="text-sm text-gray-600 mb-3 leading-relaxed">
                                {{ n.message }}
                              </p>

                              <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                  <Badge 
                                    v-if="n.type"
                                    variant="outline"
                                    class="text-xs border-[#38618C] text-[#38618C]"
                                  >
                                    {{ n.type }}
                                  </Badge>
                                </div>
                                
                                <button 
                                  v-if="!n.is_read"
                                  @click="() => markNotificationAsRead(n)"
                                  class="text-xs font-medium text-[#01FF19] hover:text-[#00cc00] transition-colors px-3 py-1 rounded-lg bg-[#01FF19]/10 hover:bg-[#01FF19]/20"
                                >
                                  Marquer lu
                                </button>
                                <span 
                                  v-else 
                                  class="text-xs text-gray-400 px-3 py-1 rounded-lg bg-gray-100"
                                >
                                  ✓ Lu
                                </span>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                  </ScrollArea>
                </div>

                <!-- FOOTER (fixé) -->
                <div class="flex-none p-4 border-t bg-gray-50">
                  <div class="flex items-center justify-between w-full">
                    <span class="text-sm text-gray-600">
                      {{ notifications.length }} notification{{ notifications.length > 1 ? 's' : '' }}
                    </span>
                    <Button 
                      @click="showNotifications = false" 
                      variant="outline"
                      class="border-[#38618C] text-[#38618C] hover:bg-[#38618C] hover:text-white"
                    >
                      Fermer
                    </Button>
                  </div>
                </div>
              </DrawerContent>
            </Drawer>
            <!-- end Drawer -->
          </div>

          <!-- User Menu -->
          <div class="flex items-center gap-4">
            <div class="text-right hidden sm:block">
              <div class="font-semibold text-[#38618C]">
                {{ user?.name || user?.email || 'Utilisateur' }}
              </div>
              
            </div>
            
            <Separator orientation="vertical" class="h-8 bg-gray-200 hidden sm:block" />
            
            <div class="flex items-center gap-3">
             
              <Button 
                @click="logout" 
                class="bg-white border-2 border-[#FF5964] text-[#FF5964] hover:bg-[#FF5964] hover:text-white transition-colors"
              >
                Déconnexion
              </Button>
            </div>
          </div>
        </div>
      </header>

      <div class="max-w-[1920px] mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex gap-6">
          <!-- Sidebar -->
          <aside class="hidden lg:block flex-shrink-0">
            <CustomSidebar 
              :menuItems="menuItems"
              :portfolio="portfolio"
              :totalValue="totalValue"
              :dayChangePct="dayChangePct"
              :role="role"
            />
          </aside>

          <!-- Main content -->
          <main class="flex-1">
            <Card class="border-gray-200 shadow-sm min-h-[calc(100vh-8rem)]">
              <CardContent class="p-6">
                <router-view />
              </CardContent>
            </Card>
          </main>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.scrollbar-custom {
  scrollbar-width: thin;
  scrollbar-color: #38618C transparent;
}

.scrollbar-custom::-webkit-scrollbar {
  width: 4px;
}

.scrollbar-custom::-webkit-scrollbar-track {
  background: transparent;
  border-radius: 2px;
}

.scrollbar-custom::-webkit-scrollbar-thumb {
  background-color: #38618C;
  border-radius: 2px;
}

.scrollbar-custom::-webkit-scrollbar-thumb:hover {
  background-color: #35A7FF;
}

/* Animation pour les transitions de route */
.router-view-enter-active,
.router-view-leave-active {
  transition: opacity 0.3s ease;
}

.router-view-enter-from,
.router-view-leave-to {
  opacity: 0;
}

.font-celias {
  font-family: 'Celias', system-ui, -apple-system, sans-serif;
}

/* Animation pour les notifications */
@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

.drawer-slide-in {
  animation: slideInRight 0.3s ease-out;
}
</style>