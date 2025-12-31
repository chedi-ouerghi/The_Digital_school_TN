<script setup lang="ts">
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { auth } from '../services/auth'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Loader2, Mail, Lock, AlertCircle, CheckCircle2, Eye, EyeOff } from 'lucide-vue-next'

const router = useRouter()
const email = ref('')
const password = ref('')
const error = ref<string | null>(null)
const success = ref<string | null>(null)
const loading = ref(false)
const showPassword = ref(false)

async function submit(e: Event) {
  e.preventDefault()
  error.value = null
  success.value = null
  loading.value = true
  try {
    await auth.login({ email: email.value, password: password.value })
    success.value = 'Login successful! Redirecting...'
    setTimeout(() => router.push('/dashboard'), 1500)
  } catch (err: any) {
    error.value = err.message || 'Invalid credentials. Please try again.'
  } finally {
    loading.value = false
  }
}

function togglePasswordVisibility() {
  showPassword.value = !showPassword.value
}
</script>

<template>
  <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden relative">
    <!-- DottedSurface amélioré -->
    <div class="absolute inset-0 hidden lg:block">
      <DottedSurface class-name="w-full h-full opacity-100" />
      <!-- Effets de gradient améliorés -->
      <div class="absolute inset-0 bg-gradient-to-br from-gray-900/80 via-gray-900/50 to-gray-900/80" />
    </div>

    <!-- LEFT SECTION - MODIFIÉ POUR LE CENTRAGE COMPLET -->
    <div class="w-full min-h-screen lg:w-1/2 bg-white flex items-center justify-center px-4 sm:px-8 lg:px-16 py-8 lg:py-0 relative z-10 lg:rounded-r-[3rem] shadow-lg">
      <div class="w-full max-w-md mx-auto space-y-8">
        <!-- Logo -->
        <router-link to="/">
          <div class="flex items-center space-x-2">
            <img src="/assets/bitchest_logo.png" alt="BitChest Logo" class="h-14" />
          </div>
        </router-link>
        <!-- Title -->
        <div>
          <h1 class="text-3xl font-bold text-[#0f172a]">Welcome Back 👋</h1>
          <p class="text-sm text-gray-500 mt-1">Sign in to access your crypto dashboard</p>
        </div>
        <!-- Alerts -->
        <div class="space-y-2">
          <Alert v-if="error" variant="destructive" class="border-red-200 bg-red-50 text-red-600 rounded-xl">
            <AlertCircle class="h-4 w-4" />
            <AlertDescription class="text-xs">{{ error }}</AlertDescription>
          </Alert>
          <Alert v-if="success" variant="default" class="border-green-200 bg-green-50 text-green-700 rounded-xl">
            <CheckCircle2 class="h-4 w-4 text-green-500" />
            <AlertDescription class="text-xs">{{ success }}</AlertDescription>
          </Alert>
        </div>
        <!-- Login Form -->
        <form class="space-y-5" @submit="submit">
          <div>
            <Label for="email" class="text-xs font-medium text-gray-600">Email</Label>
            <div class="relative mt-1">
              <Mail class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                id="email"
                v-model="email"
                type="email"
                required
                placeholder="you@bitchest.com"
                class="pl-10 h-10 text-sm border-gray-300 focus:border-[#35A7FF] focus:ring-[#35A7FF]/20 rounded-xl"
              />
            </div>
          </div>
          <div>
            <div class="flex justify-between items-center">
              <Label for="password" class="text-xs font-medium text-gray-600">Password</Label>
            </div>
            <div class="relative mt-1">
              <Lock class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
              <Input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="••••••••"
                class="pl-10 pr-10 h-10 text-sm border-gray-300 focus:border-[#35A7FF] focus:ring-[#35A7FF]/20 rounded-xl"
              />
              <Button
                type="button"
                variant="ghost"
                size="sm"
                class="absolute right-0 top-0 h-full px-3 py-2 hover:bg-transparent"
                @click="togglePasswordVisibility"
              >
                <EyeOff v-if="showPassword" class="h-4 w-4 text-gray-400" />
                <Eye v-else class="h-4 w-4 text-gray-400" />
              </Button>
            </div>
          </div>
          <Button
            :disabled="loading"
            type="submit"
            class="w-full h-10 bg-[#35A7FF] hover:bg-[#2d8ad9] text-white font-semibold rounded-xl text-sm transition-all duration-200 shadow-md"
          >
            <Loader2 v-if="loading" class="mr-2 h-4 w-4 animate-spin" />
            <span v-else>Sign In</span>
          </Button>
        </form>
       
        <p class="text-center text-[10px] text-gray-400 mt-8">© 2025 BitChest Technologies</p>
      </div>
    </div>

    <!-- RIGHT SECTION - Revised -->
    <div class="hidden lg:flex w-1/2 relative overflow-hidden backdrop-blur-sm">
      <!-- Background effects -->
      <div class="absolute inset-0">
        <!-- Luminous circles -->
        <!-- <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-[#6E00FF]/20 rounded-full blur-[100px] animate-pulse" />
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-[#35A7FF]/20 rounded-full blur-[100px] animate-pulse delay-700" />
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-80 h-80 bg-[#00E0FF]/20 rounded-full blur-[100px] animate-pulse delay-1000" /> -->
      </div>

      <!-- Main content -->
      <div class="relative z-10 flex flex-col justify-center items-center w-full h-full px-12">
        <!-- Header -->
        <div class="text-center mb-12">
          <h1 class="text-6xl font-black leading-tight">
            <span class="bg-gradient-to-r from-white via-[#FFE6FA] to-[#E6F7FF] bg-clip-text text-transparent drop-shadow-2xl">
              The Future of<br />
              Crypto Trading
            </span>
          </h1>
          <p class="mt-6 text-lg text-white/80 max-w-xl mx-auto">
            Discover a new way to trade with BitChest, your trusted platform.
          </p>
        </div>

        <!-- Features Grid -->
        <div class="grid grid-cols-2 gap-8 max-w-2xl w-full mb-12">
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
            <Sparkles class="w-6 h-6 text-[#00E0FF] mb-4" />
            <h3 class="text-white font-semibold mb-2">Smart Trading</h3>
            <p class="text-white/70 text-sm">Advanced analysis and personalized suggestions</p>
          </div>
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
            <Sparkles class="w-6 h-6 text-[#00E0FF] mb-4" />
            <h3 class="text-white font-semibold mb-2">Enhanced Security</h3>
            <p class="text-white/70 text-sm">Optimal protection for your assets</p>
          </div>
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
            <Sparkles class="w-6 h-6 text-[#00E0FF] mb-4" />
            <h3 class="text-white font-semibold mb-2">Intuitive Interface</h3>
            <p class="text-white/70 text-sm">Smooth and pleasant navigation</p>
          </div>
          <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/20">
            <Sparkles class="w-6 h-6 text-[#00E0FF] mb-4" />
            <h3 class="text-white font-semibold mb-2">24/7 Support</h3>
            <p class="text-white/70 text-sm">Dedicated assistance at any time</p>
          </div>
        </div>

        <!-- Footer -->
        <div class="absolute bottom-8 w-full text-center">
          <div class="inline-flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-full px-6 py-2">
            <Shield class="w-4 h-4 text-[#00E0FF]" />
            <span class="text-sm text-white/80">Bank-level security</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

* {
  font-family: 'Inter', sans-serif;
  letter-spacing: 0.2px;
}

input::placeholder {
  color: #9ca3af;
  opacity: 0.8;
}

/* Animations personnalisées */
@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.animate-float {
  animation: float 3s ease-in-out infinite;
}

/* Animation améliorée */
@keyframes gradientFlow {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}

.gradient-animate {
  background-size: 200% 200%;
  animation: gradientFlow 8s ease infinite;
}

/* Glassmorphism amélioré */
.glass-effect {
  backdrop-filter: blur(12px);
  background: rgba(255, 255, 255, 0.1);
  border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Smooth scroll & background animation effect */
body {
  overflow-x: hidden;
}
</style>