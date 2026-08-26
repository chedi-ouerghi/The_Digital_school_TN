<script setup lang="ts">
import CustomSidebar from '@/components/CustomSidebar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { Drawer, DrawerContent, DrawerDescription, DrawerTitle } from '@/components/ui/drawer';
import { ScrollArea } from '@/components/ui/scroll-area';
import { Lock, Menu, X } from 'lucide-vue-next';
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api';
import auth from '../services/auth';

const router = useRouter()
const user = ref(auth.getUser())
const role = ref(auth.getRole())

// 🔐 SECURITY: Vérifier le statut du mot de passe
const showPasswordWarning = ref(false)
const passwordChecked = ref(false)

// URL de base de l'API
const API_BASE = (import.meta.env as any).VITE_API_URL || 'http://localhost:8000'

// Profil utilisateur transmis au tableau de bord
interface Props {
  profile?: any
}
const props = withDefaults(defineProps<Props>(), {
  profile: () => ({})
})

// Chargement du profil lorsque le composant ne le reçoit pas en propriété
const userProfile = ref<any>(props.profile)

// URL de l'image de profil
const getProfilePictureUrl = computed(() => {
  const profileData = userProfile.value?.user || userProfile.value
  
  if (!profileData?.profile_picture) return ''
  
  // Check if it's already a full URL
  if (profileData.profile_picture.startsWith('http')) {
    return profileData.profile_picture
  }
  
  // Handle relative paths
  const picturePath = profileData.profile_picture.replace(/^\/+/, '')
  
  // Construct proper URL with API base
  return `${API_BASE.replace(/\/+$/, '')}/storage/${picturePath}`
})

// Valeurs de remplacement pour les données utilisateur absentes
const displayUser = computed(() => {
  const profileData = userProfile.value?.user || userProfile.value
  return profileData || user.value || {}
})

// Données wallet et plus-value
const portfolio = ref<any[]>([])
const totalValue = ref(0)
const totalPlusValue = ref(0)
const totalPlusValuePercent = ref(0)
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

// Initiales utilisées pour l'avatar de remplacement
const userInitials = computed(() => {
  const name = displayUser.value?.name || displayUser.value?.email || ''
  if (!name) return 'U'
  
  return name
    .split(' ')
    .map((word: string) => word[0])
    .filter(Boolean)
    .join('')
    .toUpperCase()
    .slice(0, 2)
})

// Déconnexion après la fin de l'appel API et du nettoyage de session
// avant de naviguer, sinon le guard requiresGuest de /signin revalide la
// session encore active et renvoie vers le dashboard.
const isLoggingOut = ref(false)

async function logout() {
  if (isLoggingOut.value) return
  isLoggingOut.value = true
  try {
    await auth.logout()
  } finally {
    isLoggingOut.value = false
    await router.push('/signin')
  }
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
    await api.notifications.markAllAsRead()
    notifications.value.forEach(n => {
      n.is_read = true
      n.read_at = n.read_at || new Date().toISOString()
    })
  } catch (err) {
    console.warn('Cannot mark all notifications as read', err)
  }
}

async function openNotification(n: any) {
  if (!n.is_read) await markNotificationAsRead(n)
  if (typeof n.action_url === 'string' && n.action_url.startsWith('/dashboard/')) {
    await router.push(n.action_url)
    showNotifications.value = false
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
    'success': '✅',
    'welcome': '🎉',
    'account_request': '👤',
    'price_update': '📈',
    'admin_action': '⚡'
  }
  return icons[type] || '📢'
}

function getNotificationBgColor(type: string): string {
  const colors: { [key: string]: string } = {
    'transaction': 'bg-emerald-100 text-emerald-700 border-emerald-200',
    'security': 'bg-orange-100 text-orange-700 border-orange-200',
    'system': 'bg-slate-100 text-slate-700 border-slate-200',
    'alert': 'bg-red-100 text-red-700 border-red-200',
    'success': 'bg-green-100 text-green-700 border-green-200',
    'info': 'bg-blue-100 text-blue-700 border-blue-200',
    'welcome': 'bg-gradient-to-r from-green-100 to-emerald-100 text-green-700 border-green-200',
    'account_request': 'bg-yellow-100 text-yellow-700 border-yellow-200',
    'price_update': 'bg-blue-100 text-blue-700 border-blue-200',
    'admin_action': 'bg-purple-100 text-purple-700 border-purple-200'
  }
  return colors[type] || 'bg-slate-100 text-slate-700 border-slate-200'
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

// Chargement de la plus-value depuis l'endpoint dédié
async function loadWalletData() {
  if (String(role.value).toUpperCase().includes('ADMIN')) return
  
  loadingWallet.value = true
  try {
    // Charger les données du wallet
    const response = await api.wallet.list()
    
    const walletData = Array.isArray(response) 
      ? (response[0] || {})
      : (response as any) || {}
    
    portfolio.value = walletData.cryptomonnaies || walletData.cryptos || walletData.assets || []
    totalValue.value = Number(walletData.current_value || walletData.total_value || walletData.totalValue || 0)
    
    // Récupération de la plus-value depuis l'endpoint du portefeuille
    try {
      const plusValueResponse = await api.wallet.plusValue()
      
      // Lecture des champs renvoyés par l'API
      totalPlusValue.value = Number(plusValueResponse.total_plus_value_eur || 0)
      totalPlusValuePercent.value = Number(plusValueResponse.total_plus_value_percent || 0)
      totalValue.value = Number(plusValueResponse.total_current_value || totalValue.value)
      
      // Mettre à jour le portfolio avec les assets détaillés
      if (plusValueResponse.assets && Array.isArray(plusValueResponse.assets)) {
        portfolio.value = plusValueResponse.assets
      }
      
    } catch (plusValueError) {
      console.warn('Error loading plus-value data:', plusValueError)
      // Fallback: calcul manuel si l'endpoint échoue
      const totalInvested = Number(walletData.total_invested || walletData.totalInvestment || 0)
      totalPlusValue.value = totalValue.value - totalInvested
      totalPlusValuePercent.value = totalInvested > 0 
        ? (totalPlusValue.value / totalInvested) * 100 
        : 0
    }
    
    // Calcul du changement 24h (basé sur le portefeuille)
    let totalYesterday = 0
    let hasValidData = false
    
    for (const crypto of portfolio.value) {
      const qty = Number(crypto.pivot?.quantity || crypto.quantity || 0)
      const price = Number(crypto.price_eur || crypto.current_price_eur || crypto.current_price || 0)
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
    totalPlusValue.value = 0
    totalPlusValuePercent.value = 0
    dayChangePct.value = 0
    dayChangeAmount.value = 0
  } finally {
    loadingWallet.value = false
  }
}

async function refreshWallet() {
  await loadWalletData()
}

// Define event handler function
const onBalanceUpdated = async (e: any) => {
  try {
    if (!String(role.value).toUpperCase().includes('ADMIN')) {
      if (e?.detail?.balance !== undefined) {
        user.value = { ...(user.value || {}), solde: e.detail.balance }
      } else {
        const profile = await api.auth.profile()
        userProfile.value = profile || user.value
      }
      await loadWalletData()
    }
  } catch (err) {
    console.warn('Error updating balance', err)
  }
}

// Chargement du profil utilisateur
async function loadUserProfile() {
  try {
    if (Object.keys(props.profile).length === 0) {
      const profile = await api.auth.profile()
      userProfile.value = profile
      
      // 🔐 Vérifier le statut du mot de passe lors du chargement du profil
      if (String(role.value).toUpperCase() === 'CLIENT') {
        if (!profile.password_changed_at) {
          showPasswordWarning.value = true
        } else {
          showPasswordWarning.value = false
        }
      }
    } else {
      userProfile.value = props.profile
    }
  } catch (err) {
    console.error('❌ Failed to load user profile:', err)
  }
}

// 🔐 Fonction pour vérifier le statut du mot de passe
async function checkPasswordStatus() {
  if (String(role.value).toUpperCase() !== 'CLIENT') return
  
  try {
    const profile = await api.auth.profile()
    if (!profile.password_changed_at) {
      showPasswordWarning.value = true
    } else {
      showPasswordWarning.value = false
    }
  } catch (err) {
    console.warn('Error checking password status:', err)
  }
}

// 🔐 Écouter l'événement de changement de mot de passe
function handlePasswordChanged() {
  checkPasswordStatus()
}

onMounted(async () => {
  await loadUserProfile()
  
  // 🔐 SECURITY: Vérifier que l'utilisateur a changé son mot de passe (pour les clients uniquement)
  if (String(role.value).toUpperCase() === 'CLIENT' && !passwordChecked.value) {
    await checkPasswordStatus()
    passwordChecked.value = true
  }
  
  // 🔐 Écouter l'événement de changement de mot de passe
  window.addEventListener('password-changed', handlePasswordChanged)
  
  await loadWalletData()
  
  window.addEventListener('balance-updated', onBalanceUpdated)
  fetchNotifications()
})

// Move onUnmounted to top level
onUnmounted(() => {
  window.removeEventListener('balance-updated', onBalanceUpdated)
  window.removeEventListener('password-changed', handlePasswordChanged)
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

const isPositiveChange = computed(() => totalPlusValue.value >= 0)
const displayValue = computed(() => loadingWallet.value ? '...' : formatCurrency(totalPlusValue.value))
const displayPercentage = computed(() => loadingWallet.value ? '...' : formatPercentage(totalPlusValuePercent.value))
</script>

<template>
  <div class="min-h-screen w-full bg-white flex flex-col">

    <!-- 🔐 PASSWORD WARNING ALERT -->
    <div v-if="showPasswordWarning" class="w-full bg-gradient-to-r from-red-50 to-orange-50 border-b-2 border-red-300/50 px-4 sm:px-6 lg:px-8 py-4">
      <div class="flex items-start gap-4 max-w-7xl mx-auto">
        <div class="flex-1">
          <div class="flex items-center gap-2 mb-1">
            <span class="text-lg">🔒</span>
            <p class="font-semibold text-red-900">Security Notice: Change Your Password</p>
          </div>
          <p class="text-sm text-red-800 mb-3">
            For your account security, you must change your temporary password before you can buy or sell cryptocurrencies.
          </p>
          <button
            class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg font-medium transition-colors"
            @click="router.push('/dashboard/Portfolio')"
          >
            <Lock class="w-4 h-4" />
            Go to Profile Settings
          </button>
        </div>
        <button
          class="mt-1 text-red-600 hover:text-red-800 transition-colors p-1"
          @click="showPasswordWarning = false"
        >
          <X class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- MAIN LAYOUT (le header a été supprimé : sidebar + contenu principal uniquement) -->
    <div class="flex flex-1 min-h-0 w-full">

      <!-- DESKTOP SIDEBAR -->
      <aside
        class="hidden lg:flex flex-col w-72 border-r border-slate-200 bg-white shadow-sm sticky left-0 top-0 h-screen overflow-y-auto"
      >
        <div class="p-6">
          <CustomSidebar
            :menu-items="menuItems"
            :portfolio="portfolio"
            :total-value="totalValue"
            :day-change-pct="dayChangePct"
            :role="role"
            :user-name="displayUser?.name || displayUser?.email"
            :user-email="displayUser?.email"
            :user-initials="userInitials"
            :profile-picture-url="getProfilePictureUrl"
            :unread-count="unreadCount"
            :plus-value-display="displayValue"
            :plus-value-percent-display="displayPercentage"
            :plus-positive="isPositiveChange"
            :loading-wallet="loadingWallet"
            @open-notifications="toggleNotifications"
            @logout="logout"
            @refresh="refreshWallet"
          />
        </div>
      </aside>

      <!-- MAIN CONTENT -->
      <main class="flex-1 overflow-y-auto min-h-0 relative">
        <!-- Bouton menu mobile (remplace le bouton hamburger du header supprimé) -->
        <button
          type="button"
          class="lg:hidden fixed top-4 left-4 z-40 p-2.5 bg-white border border-slate-200 shadow-sm text-slate-600 hover:bg-slate-100 rounded-xl transition-all duration-200"
          aria-label="Open navigation"
          @click="showMobileSidebar = true"
        >
          <Menu class="w-5 h-5" />
        </button>

        <div class="px-4 sm:px-8 lg:px-12 py-10 max-w-full">
          <Card class="bg-white border border-slate-200 shadow-sm rounded-3xl w-full min-h-[calc(100vh-8rem)]">
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
        
        <!-- Accessibility components for screen readers -->
        <DrawerTitle class="sr-only">Notifications Panel</DrawerTitle>
        <DrawerDescription class="sr-only">
          View and manage your notifications. You have {{ unreadCount }} unread notifications.
        </DrawerDescription>

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
          <div
            v-for="n in notifications"
            :key="n.id"
            class="p-4 transition-all duration-200 cursor-pointer hover:shadow-md group"
            :class="[
              n.is_read 
                ? 'bg-white hover:bg-slate-50/80' 
                : 'bg-gradient-to-r from-blue-50/60 to-indigo-50/40 border-l-4 border-blue-500',
              n.type === 'welcome' && 'bg-gradient-to-r from-green-50 to-emerald-50/40 border-l-4 border-green-500 shadow-md'
            ]"
            @click="openNotification(n)"
          >
            <!-- Header with icon, type badge and timestamp -->
            <div class="flex gap-4 mb-3">
              <!-- Icon -->
              <div
                class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0 text-xl font-semibold transition-transform group-hover:scale-110 shadow-sm"
                :class="[
                  n.is_read 
                    ? 'bg-slate-100 text-slate-600' 
                    : n.type === 'welcome'
                    ? 'bg-gradient-to-br from-green-200 to-emerald-200 text-green-700 shadow-lg'
                    : 'bg-blue-100 text-blue-600 shadow-md'
                ]"
              >
                {{ getNotificationIcon(n.type) }}
              </div>

              <!-- Type badge and read status -->
              <div class="flex-1 min-w-0 flex items-start justify-between gap-2">
                <div class="flex gap-2 flex-wrap items-center">
                  <!-- Type Badge -->
                  <Badge
                    class="text-xs font-semibold px-2.5 py-1 rounded-full border"
                    :class="getNotificationBgColor(n.type)"
                  >
                    {{ (n.type || 'info')
                      .replace(/_/g, ' ')
                      .split(' ')
                      .map((word: string) => word.charAt(0).toUpperCase() + word.slice(1))
                      .join(' ')
                    }}
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
            <h3 
              class="font-bold mb-2 text-sm leading-snug pr-2 transition-colors"
              :class="n.type === 'welcome' ? 'text-green-900' : 'text-slate-900'"
            >
              {{ n.title }}
            </h3>

            <!-- Message (full text, separated from title) -->
            <p 
              class="text-sm leading-relaxed whitespace-pre-wrap mb-3 p-3 rounded-lg border-l-2 transition-all"
              :class="n.type === 'welcome'
                ? 'bg-gradient-to-br from-green-50 to-emerald-50/50 text-green-800 border-green-300'
                : 'bg-white/40 text-slate-600 border-slate-200'"
            >
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
                class="flex-1 text-xs h-8 rounded-lg transition-all font-medium"
                :class="n.type === 'welcome'
                  ? 'bg-gradient-to-r from-green-500 to-emerald-500 hover:from-green-600 hover:to-emerald-600 text-white shadow-md'
                  : 'bg-blue-500 hover:bg-blue-600 text-white shadow-md'
                "
                @click.stop="markNotificationAsRead(n)"
              >
                ✓ Mark as read
              </Button>
            </div>
            <div v-else class="mt-2">
              <span class="text-xs text-slate-400 italic">✓ Read</span>
            </div>
          </div>
        </ScrollArea>

      </DrawerContent>
    </Drawer>

    <!-- MOBILE SIDEBAR -->
    <Drawer :open="showMobileSidebar" direction="left" @update:open="v => showMobileSidebar = v">
      <DrawerContent class="w-[85vw] max-w-sm h-full border-r border-slate-200 bg-white shadow-xl flex flex-col">
        
        <!-- Accessibility components for screen readers -->
        <DrawerTitle class="sr-only">Navigation Menu</DrawerTitle>
        <DrawerDescription class="sr-only">
          Navigate through different sections of your dashboard.
        </DrawerDescription>

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
            :user-name="displayUser?.name || displayUser?.email"
            :user-email="displayUser?.email"
            :user-initials="userInitials"
            :profile-picture-url="getProfilePictureUrl"
            :unread-count="unreadCount"
            :plus-value-display="displayValue"
            :plus-value-percent-display="displayPercentage"
            :plus-positive="isPositiveChange"
            :loading-wallet="loadingWallet"
            @open-notifications="toggleNotifications"
            @logout="logout"
            @refresh="refreshWallet"
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