<script setup lang="ts">
import { Chart, registerables } from 'chart.js'
import { computed, nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router'
import api, { API_BASE } from '../../services/api'

// Import des composants shadcn-vue
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Skeleton } from '@/components/ui/skeleton'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import {
  AlertCircle,
  CheckCircle2,
  Eye,
  EyeOff,
  Lock,
  RefreshCw,
  TrendingDown,
  TrendingUp,
  User,
  Wallet
} from 'lucide-vue-next'

// Enregistrer Chart.js
Chart.register(...registerables)

// Types
type PortfolioResponse = {
  success: boolean;
  data: {
    stats: {
      total_invested: number;
      current_value: number;
      total_profit: number;
      profit_percentage: number;
      total_transactions: number;
    };
    growth: {
      labels: string[];
      data: number[];
      raw: {
        date: string;
        value: number;
        timestamp: number;
      }[];
    };
    distribution: {
      labels: string[];
      data: number[];
      colors: string[];
      raw: {
        crypto_name: string;
        crypto_symbol: string;
        value: number;
        percentage: number;
        quantity: string;
      }[];
    };
  };
};

type UserProfile = {
  name: string;
  email: string;
  created_at: string;
  email_verified_at: string | null;
};

type PasswordForm = {
  current_password: string;
  password: string;
  password_confirmation: string;
};

const router = useRouter()
const loading = ref(false)
const error = ref<string | null>(null)
const message = ref<string | null>(null)

// Onglets
const activeTab = ref('overview')

// Données du profil
const profile = ref<UserProfile | null>(null)
const profileForm = ref({
  name: '',
  email: ''
})
const updatingProfile = ref(false)

// Changement de mot de passe
const passwordForm = ref<PasswordForm>({
  current_password: '',
  password: '',
  password_confirmation: ''
})
const showCurrentPassword = ref(false)
const showNewPassword = ref(false)
const showConfirmPassword = ref(false)
const changingPassword = ref(false)

// Données du portfolio
const portfolioStats = ref<PortfolioResponse['data'] | null>(null)
const userBalance = ref(0)

// Graphiques
const growthChart = ref<Chart | null>(null)
const distributionChart = ref<Chart | null>(null)

// Fonctions utilitaires
function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('en-US', { style: 'currency', currency: 'EUR' })
}

const formatNumber = (num: any, decimals = 2) => {
  if (num === null || num === undefined) return '0'
  return parseFloat(num).toLocaleString('en-US', {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals
  })
}

// Charger les données du profil
async function fetchProfile() {
  try {
    const res = await api.auth.profile()
    profile.value = res.user || res
    profileForm.value = {
      name: profile.value.name || '',
      email: profile.value.email || ''
    }
    // set previews if available
    if (profile.value?.profile_picture) {
      avatarPreview.value = profile.value.profile_picture.startsWith('http')
        ? profile.value.profile_picture
        : `${apiBaseStorageUrl()}/storage/${profile.value.profile_picture}`
    }
    if (profile.value?.profile_banner) {
      bannerPreview.value = profile.value.profile_banner.startsWith('http')
        ? profile.value.profile_banner
        : `${apiBaseStorageUrl()}/storage/${profile.value.profile_banner}`
    }
  } catch (e: any) {
    console.error('Error loading profile:', e)
  }
}

// helper to build base storage url (assumes backend on same host/port)
function apiBaseStorageUrl() {
  try {
    const u = new URL(API_BASE.replace('/api/v1', ''))
    return u.origin
  } catch {
    return 'http://localhost:8000'
  }
}

// Media (avatar/banner) state
const avatarFile = ref<File | null>(null)
const bannerFile = ref<File | null>(null)
const avatarPreview = ref<string>('')
const bannerPreview = ref<string>('')
const uploadLoading = ref(false)

function handleAvatarUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    avatarFile.value = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => (avatarPreview.value = String(e.target?.result || ''))
    reader.readAsDataURL(avatarFile.value)
  }
}

function handleBannerUpload(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    bannerFile.value = target.files[0]
    const reader = new FileReader()
    reader.onload = (e) => (bannerPreview.value = String(e.target?.result || ''))
    reader.readAsDataURL(bannerFile.value)
  }
}

async function uploadImages() {
  if (!avatarFile.value && !bannerFile.value) return
  uploadLoading.value = true
  try {
    if (avatarFile.value) {
      const form = new FormData()
      form.append('profile_picture', avatarFile.value)
      const resp = await api.auth.uploadProfilePicture(form)
      if (resp && resp.data && resp.data.url) {
        avatarPreview.value = resp.data.url
        message.value = 'Avatar uploaded successfully'
      }
    }

    if (bannerFile.value) {
      const form = new FormData()
      form.append('profile_banner', bannerFile.value)
      const resp = await api.auth.uploadProfileBanner(form)
      if (resp && resp.data && resp.data.url) {
        bannerPreview.value = resp.data.url
        message.value = 'Banner uploaded successfully'
      }
    }

    // refresh profile
    await fetchProfile()
    setTimeout(() => (message.value = null), 3000)
  } catch (e: any) {
    console.error('Upload failed:', e)
    message.value = e?.message || 'Upload failed'
  } finally {
    uploadLoading.value = false
    avatarFile.value = null
    bannerFile.value = null
  }
}

function removeAvatarPreview() {
  avatarPreview.value = ''
  avatarFile.value = null
}

function removeBannerPreview() {
  bannerPreview.value = ''
  bannerFile.value = null
}

// Charger les stats du portfolio
async function fetchPortfolioStats() {
  loading.value = true
  error.value = null
  try {
    const response = await api.auth.getProfileStats()
    portfolioStats.value = response.data
    
    // Récupérer le solde disponible
    try {
      const walletResponse = await api.wallet.list()
      const walletData = walletResponse?.wallet || {}
      userBalance.value = Number(walletData.balance_eur ?? walletResponse?.solde_eur ?? 0)
    } catch (walletError) {
      console.error('Error loading wallet balance:', walletError)
    }
    
    // Recréer les graphiques après un court délai pour s'assurer que le DOM est mis à jour
    setTimeout(() => {
      createGrowthChart()
      createDistributionChart()
    }, 100)
  } catch (e: any) {
    error.value = e.message || String(e)
    console.error('Error loading stats:', e)
  } finally {
    loading.value = false
  }
}

// Mettre à jour le profil
async function updateProfile() {
  updatingProfile.value = true
  message.value = null
  error.value = null
  
  try {
    await api.auth.updateProfile(profileForm.value)
    message.value = 'Profile updated successfully!'
    await fetchProfile()
    
    setTimeout(() => {
      message.value = null
    }, 3000)
  } catch (e: any) {
    error.value = e.message || 'Error updating profile'
  } finally {
    updatingProfile.value = false
  }
}

// Changer le mot de passe
async function changePassword() {
  if (passwordForm.value.password !== passwordForm.value.password_confirmation) {
    error.value = 'Passwords do not match'
    return
  }
  
  changingPassword.value = true
  message.value = null
  error.value = null
  
  try {
    await api.auth.changePassword(passwordForm.value)
    message.value = 'Password changed successfully!'
    
    // Réinitialiser le formulaire
    passwordForm.value = {
      current_password: '',
      password: '',
      password_confirmation: ''
    }
    
    setTimeout(() => {
      message.value = null
    }, 3000)
  } catch (e: any) {
    error.value = e.message || 'Error changing password'
  } finally {
    changingPassword.value = false
  }
}

// Détruire les graphiques existants
function destroyCharts() {
  if (growthChart.value) {
    growthChart.value.destroy()
    growthChart.value = null
  }
  if (distributionChart.value) {
    distributionChart.value.destroy()
    distributionChart.value = null
  }
}

// Créer le graphique de croissance
function createGrowthChart() {
  if (!portfolioStats.value?.growth) return
  
  const ctx = document.getElementById('growthChart') as HTMLCanvasElement
  if (!ctx) return
  
  // Détruire le graphique existant
  if (growthChart.value) {
    growthChart.value.destroy()
  }
  
  const growth = portfolioStats.value.growth
  
  growthChart.value = new Chart(ctx, {
    type: 'line',
    data: {
      labels: growth.labels,
      datasets: [{
        label: 'Portfolio Value',
        data: growth.data,
        borderColor: '#35A7FF',
        backgroundColor: 'rgba(53, 167, 255, 0.1)',
        borderWidth: 3,
        fill: true,
        tension: 0.4,
        pointRadius: 4,
        pointHoverRadius: 6,
        pointBackgroundColor: '#35A7FF',
        pointBorderColor: '#fff',
        pointBorderWidth: 2
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          display: false
        },
        tooltip: {
          backgroundColor: 'rgba(56, 97, 140, 0.95)',
          titleColor: '#fff',
          bodyColor: '#fff',
          padding: 12,
          borderColor: '#35A7FF',
          borderWidth: 1,
          displayColors: false,
          callbacks: {
            label: function(context) {
              return formatCurrency(context.parsed.y)
            }
          }
        }
      },
      scales: {
        y: {
          beginAtZero: false,
          grid: {
            color: 'rgba(0, 0, 0, 0.05)'
          },
          ticks: {
            callback: function(value) {
              return formatCurrency(value)
            },
            font: {
              size: 11
            },
            color: '#6b7280'
          }
        },
        x: {
          grid: {
            display: false
          },
          ticks: {
            font: {
              size: 11
            },
            color: '#6b7280'
          }
        }
      }
    }
  })
}

// Créer le graphique de distribution
function createDistributionChart() {
  if (!portfolioStats.value?.distribution) return
  
  const ctx = document.getElementById('distributionChart') as HTMLCanvasElement
  if (!ctx) return
  
  // Détruire le graphique existant
  if (distributionChart.value) {
    distributionChart.value.destroy()
  }
  
  const distribution = portfolioStats.value.distribution
  
  distributionChart.value = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: distribution.labels,
      datasets: [{
        data: distribution.data,
        backgroundColor: distribution.colors,
        borderWidth: 2,
        borderColor: '#fff'
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: {
          position: 'right',
          labels: {
            padding: 15,
            font: {
              size: 12
            },
            color: '#374151',
            usePointStyle: true,
            pointStyle: 'circle'
          }
        },
        tooltip: {
          backgroundColor: 'rgba(56, 97, 140, 0.95)',
          titleColor: '#fff',
          bodyColor: '#fff',
          padding: 12,
          borderColor: '#35A7FF',
          borderWidth: 1,
          displayColors: true,
          callbacks: {
            label: function(context) {
              const label = context.label || ''
              const value = context.parsed || 0
              return `${label}: ${formatCurrency(value)}`
            }
          }
        }
      }
    }
  })
}

// Recréer les graphiques quand on revient à l'onglet portfolio
watch(activeTab, (newTab) => {
  if (newTab === 'overview' && portfolioStats.value) {
    nextTick(() => {
      setTimeout(() => {
        createGrowthChart()
        createDistributionChart()
      }, 100)
    })
  }
})

onMounted(async () => {
  await fetchProfile()
  await fetchPortfolioStats()
})

// Computed
const stats = computed(() => portfolioStats.value?.stats || null)
const distributionData = computed(() => portfolioStats.value?.distribution.raw || [])

const profitTrend = computed(() => {
  if (!stats.value) return 'neutral'
  return (stats.value.total_profit || 0) >= 0 ? 'up' : 'down'
})
</script>

<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between flex-wrap gap-4">
      <div>
        <h1 class="text-2xl sm:text-3xl font-bold text-brand-dark mb-1">My Profile & Portfolio</h1>
        <p class="text-sm sm:text-base text-gray-500">Manage your account and investments</p>
      </div>
      <Button 
        variant="outline" 
        :disabled="loading"
        class="border-brand-blue text-brand-blue hover:bg-brand-blue hover:text-white transition-colors"
        @click="fetchPortfolioStats"
      >
        <RefreshCw class="w-4 h-4 mr-2" :class="{ 'animate-spin': loading }" />
        {{ loading ? 'Refreshing...' : 'Refresh' }}
      </Button>
    </div>

    <!-- Alertes -->
    <Alert v-if="message" class="border-brand-green bg-brand-green/10 rounded-xl">
      <CheckCircle2 class="h-4 w-4 text-brand-green" />
      <AlertDescription class="text-brand-green font-medium">
        {{ message }}
      </AlertDescription>
    </Alert>

    <Alert v-if="error" class="border-brand-red bg-brand-red/10 rounded-xl">
      <AlertCircle class="h-4 w-4 text-brand-red" />
      <AlertDescription class="text-brand-red font-medium">
        {{ error }}
      </AlertDescription>
    </Alert>

    <!-- Onglets -->
    <Tabs v-model="activeTab" class="w-full">
      <TabsList class="grid w-full grid-cols-4 bg-gray-100 p-1 rounded-lg">
        <TabsTrigger 
          value="overview" 
          class="data-[state=active]:bg-brand-blue data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <Wallet class="w-4 h-4 mr-2" />
          Portfolio
        </TabsTrigger>
        <TabsTrigger 
          value="profile" 
          class="data-[state=active]:bg-brand-blue data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <User class="w-4 h-4 mr-2" />
          Profile
        </TabsTrigger>
        <TabsTrigger 
          value="media" 
          class="data-[state=active]:bg-brand-blue data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <Eye class="w-4 h-4 mr-2" />
          Media
        </TabsTrigger>
        <TabsTrigger 
          value="security" 
          class="data-[state=active]:bg-brand-blue data-[state=active]:text-white data-[state=active]:shadow-sm rounded-md transition-all"
        >
          <Lock class="w-4 h-4 mr-2" />
          Security
        </TabsTrigger>
      </TabsList>

      <!-- Onglet Portfolio -->
      <TabsContent value="overview" class="space-y-6 mt-6">
        <!-- Statistiques principales -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          <Card class="border-gray-200 hover:border-brand-blue transition-all duration-300 bg-gradient-to-br from-brand-blue/5 to-transparent hover-lift">
            <CardContent class="p-4 sm:p-6">
              <div class="text-xs sm:text-sm text-gray-500 mb-1">Total Invested</div>
              <div class="text-xl sm:text-2xl font-bold text-brand-dark">
                {{ loading ? '...' : formatCurrency(stats?.total_invested || 0) }}
              </div>
              <div class="text-xs text-gray-400 mt-1">Amount invested</div>
            </CardContent>
          </Card>

          <Card class="border-gray-200 hover:border-brand-green transition-all duration-300 bg-gradient-to-br from-brand-green/5 to-transparent hover-lift">
            <CardContent class="p-4 sm:p-6">
              <div class="text-xs sm:text-sm text-gray-500 mb-1">Available Balance</div>
              <div class="text-xl sm:text-2xl font-bold text-brand-green">
                {{ loading ? '...' : formatCurrency(userBalance || 0) }}
              </div>
              <div class="text-xs text-gray-400 mt-1">Available for investment</div>
            </CardContent>
          </Card>

          <Card
class="border-gray-200 transition-all duration-300 bg-gradient-to-br hover-lift" 
                :class="profitTrend === 'up' ? 'hover:border-brand-green from-brand-green/5' : 'hover:border-brand-red from-brand-red/5'">
            <CardContent class="p-4 sm:p-6">
              <div class="text-xs sm:text-sm text-gray-500 mb-1">Total Profit</div>
              <div
class="text-xl sm:text-2xl font-bold flex items-center gap-1" 
                   :class="profitTrend === 'up' ? 'text-brand-green' : 'text-brand-red'">
                {{ loading ? '...' : formatCurrency(stats?.total_profit || 0) }}
                <TrendingUp v-if="profitTrend === 'up'" class="w-5 h-5" />
                <TrendingDown v-else class="w-5 h-5" />
              </div>
              <div class="text-xs text-gray-400 mt-1">
                {{ loading ? '...' : formatNumber(stats?.profit_percentage || 0) }}% profit
              </div>
            </CardContent>
          </Card>

          <Card class="border-gray-200 hover:border-brand-dark transition-all duration-300 bg-gradient-to-br from-brand-dark/5 to-transparent hover-lift">
            <CardContent class="p-4 sm:p-6">
              <div class="text-xs sm:text-sm text-gray-500 mb-1">Transactions</div>
              <div class="text-xl sm:text-2xl font-bold text-brand-dark">
                {{ loading ? '...' : stats?.total_transactions || 0 }}
              </div>
              <div class="text-xs text-gray-400 mt-1">Operations performed</div>
            </CardContent>
          </Card>
        </div>

        <!-- Graphiques -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Graphique de croissance -->
          <Card class="border-gray-200 shadow-lg hover-lift transition-all duration-300">
            <CardHeader class="pb-4">
              <CardTitle class="text-brand-dark flex items-center gap-2">
                <TrendingUp class="w-5 h-5" />
                Portfolio Evolution
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="loading" class="flex items-center justify-center h-64">
                <Skeleton class="h-full w-full rounded-lg" />
              </div>
              <div v-else-if="!portfolioStats?.growth" class="flex items-center justify-center h-64 text-gray-500">
                No growth data available
              </div>
              <div v-else class="h-64">
                <canvas id="growthChart"></canvas>
              </div>
            </CardContent>
          </Card>

          <!-- Graphique de distribution -->
          <Card class="border-gray-200 shadow-lg hover-lift transition-all duration-300">
            <CardHeader class="pb-4">
              <CardTitle class="text-brand-dark flex items-center gap-2">
                💰 Portfolio Distribution
              </CardTitle>
            </CardHeader>
            <CardContent>
              <div v-if="loading" class="flex items-center justify-center h-64">
                <Skeleton class="h-full w-full rounded-lg" />
              </div>
              <div v-else-if="distributionData.length === 0" class="flex items-center justify-center h-64 text-gray-500">
                No distribution data available
              </div>
              <div v-else class="h-64">
                <canvas id="distributionChart"></canvas>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Détails de distribution - Tableau moderne -->
        <Card v-if="distributionData.length > 0 && !loading" class="border-gray-200 bg-gradient-to-br from-indigo-50 to-purple-50 shadow-lg hover-lift transition-all duration-300">
          <CardHeader>
            <CardTitle class="text-brand-dark flex items-center gap-2">
              <span>📊</span>
              Portfolio Distribution Details
            </CardTitle>
          </CardHeader>
          <CardContent>
            <div class="overflow-x-auto">
              <table class="w-full">
                <thead>
                  <tr class="border-b-2 border-indigo-200">
                    <th class="text-left py-3 px-4 font-semibold text-brand-dark">Asset</th>
                    <th class="text-right py-3 px-4 font-semibold text-brand-dark">Quantity</th>
                    <th class="text-right py-3 px-4 font-semibold text-brand-dark">Value</th>
                    <th class="text-right py-3 px-4 font-semibold text-brand-dark">Distribution %</th>
                  </tr>
                </thead>
                <tbody>
                  <tr 
                    v-for="item in distributionData" 
                    :key="item.crypto_symbol"
                    class="border-b border-gray-200 hover:bg-white transition-colors"
                  >
                    <td class="py-4 px-4">
                      <div>
                        <div class="font-semibold text-brand-dark">{{ item.crypto_name }}</div>
                        <div class="text-xs text-gray-500">{{ item.crypto_symbol }}</div>
                      </div>
                    </td>
                    <td class="text-right py-4 px-4 font-mono text-sm">{{ formatNumber(parseFloat(item.quantity), 8) }}</td>
                    <td class="text-right py-4 px-4 font-bold text-brand-blue">{{ formatCurrency(item.value) }}</td>
                    <td class="text-right py-4 px-4">
                      <Badge class="bg-indigo-100 text-indigo-900 font-semibold">
                        {{ formatNumber(item.percentage) }}%
                      </Badge>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>

        <!-- Bouton Transaction History -->
        <div v-if="distributionData.length > 0 && !loading" class="flex justify-center">
          <Button 
            class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold px-8 py-3 transition-all hover:shadow-lg hover:scale-105"
            @click="$router.push('/dashboard/transactions')"
          >
            📋 View Complete Transaction History
          </Button>
        </div>
      </TabsContent>

      <!-- Onglet Profil -->
      <TabsContent value="profile" class="space-y-6 mt-6">
        <Card class="border-gray-200 shadow-lg hover-lift transition-all duration-300">
          <CardHeader>
            <CardTitle class="text-brand-dark">Profile Information</CardTitle>
          </CardHeader>
          <CardContent>
            <form class="space-y-4" @submit.prevent="updateProfile">
              <div class="space-y-2">
                <Label for="name">Name</Label>
                <Input
                  id="name"
                  v-model="profileForm.name"
                  type="text"
                  placeholder="Your name"
                  required
                  class="transition-colors focus:border-brand-blue"
                />
              </div>

              <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input
                  id="email"
                  v-model="profileForm.email"
                  type="email"
                  placeholder="your@email.com"
                  required
                  class="transition-colors focus:border-brand-blue"
                />
              </div>

              <div v-if="profile" class="pt-4 border-t space-y-2 text-sm">
                <div class="flex justify-between">
                  <span class="text-gray-600">Member since:</span>
                  <span class="font-medium">{{ new Date(profile.created_at).toLocaleDateString('en-US') }}</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Account status:</span>
                  <Badge :class="profile.email_verified_at ? 'bg-brand-green/20 text-brand-green' : 'bg-brand-red/20 text-brand-red'">
                    {{ profile.email_verified_at ? '✓ Verified' : '⚠ Not verified' }}
                  </Badge>
                </div>
              </div>

              <Button 
                type="submit" 
                :disabled="updatingProfile"
                class="w-full bg-brand-blue hover:bg-brand-dark text-white transition-colors"
              >
                {{ updatingProfile ? 'Updating...' : 'Update Profile' }}
              </Button>
            </form>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Onglet Media -->
      <TabsContent value="media" class="space-y-6 mt-6">
        <Card class="border-gray-200 shadow-lg hover-lift transition-all duration-300">
          <CardHeader>
            <CardTitle class="text-brand-dark">Media</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-3">
                <Label>Avatar</Label>
                <div class="flex items-center gap-4">
                  <div class="w-28 h-28 rounded-full overflow-hidden bg-gray-100 flex items-center justify-center">
                    <img v-if="avatarPreview" :src="avatarPreview" class="object-cover w-full h-full" />
                    <div v-else class="text-gray-400">No avatar</div>
                  </div>
                  <div class="space-y-2">
                    <input type="file" accept="image/*" @change="handleAvatarUpload" />
                    <div class="flex gap-2">
                      <Button variant="outline" @click="removeAvatarPreview">Remove</Button>
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <Label>Banner</Label>
                <div class="space-y-2">
                  <div class="w-full h-40 rounded-lg overflow-hidden bg-gray-100 flex items-center justify-center">
                    <img v-if="bannerPreview" :src="bannerPreview" class="object-cover w-full h-full " />
                    <div v-else class="text-gray-400">No banner</div>
                  </div>
                  <input type="file" accept="image/*" @change="handleBannerUpload" />
                  <div class="flex gap-2">
                    <Button variant="outline" @click="removeBannerPreview">Remove</Button>
                  </div>
                </div>
              </div>
            </div>

            <div class="mt-6">
              <Button :disabled="uploadLoading" @click="uploadImages">
                {{ uploadLoading ? 'Uploading...' : 'Upload Media' }}
              </Button>
            </div>
          </CardContent>
        </Card>
      </TabsContent>

      <!-- Onglet Sécurité -->
      <TabsContent value="security" class="space-y-6 mt-6">
        <Card class="border-gray-200 shadow-lg hover-lift transition-all duration-300">
          <CardHeader>
            <CardTitle class="text-brand-dark">Change Password</CardTitle>
          </CardHeader>
          <CardContent>
            <form class="space-y-4" @submit.prevent="changePassword">
              <div class="space-y-2">
                <Label for="current_password">Current Password</Label>
                <div class="relative">
                  <Input
                    id="current_password"
                    v-model="passwordForm.current_password"
                    :type="showCurrentPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    required
                    class="pr-10 transition-colors focus:border-brand-blue"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                    @click="showCurrentPassword = !showCurrentPassword"
                  >
                    <Eye v-if="showCurrentPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="password">New Password</Label>
                <div class="relative">
                  <Input
                    id="password"
                    v-model="passwordForm.password"
                    :type="showNewPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    required
                    class="pr-10 transition-colors focus:border-brand-blue"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                    @click="showNewPassword = !showNewPassword"
                  >
                    <Eye v-if="showNewPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <div class="space-y-2">
                <Label for="password_confirmation">Confirm New Password</Label>
                <div class="relative">
                  <Input
                    id="password_confirmation"
                    v-model="passwordForm.password_confirmation"
                    :type="showConfirmPassword ? 'text' : 'password'"
                    placeholder="••••••••"
                    required
                    class="pr-10 transition-colors focus:border-brand-blue"
                  />
                  <button
                    type="button"
                    class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-700 transition-colors"
                    @click="showConfirmPassword = !showConfirmPassword"
                  >
                    <Eye v-if="showConfirmPassword" class="w-4 h-4" />
                    <EyeOff v-else class="w-4 h-4" />
                  </button>
                </div>
              </div>

              <Button 
                type="submit" 
                :disabled="changingPassword"
                class="w-full bg-brand-red hover:bg-[#E63946] text-white transition-colors"
              >
                {{ changingPassword ? 'Changing...' : 'Change Password' }}
              </Button>
            </form>
          </CardContent>
        </Card>
      </TabsContent>
    </Tabs>
  </div>
</template>

<style scoped>
.hover-lift {
  transform: translateY(0);
  transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
}

.hover-lift:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
}
</style>