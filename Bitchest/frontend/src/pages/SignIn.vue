<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { auth } from '../services/auth';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Alert, AlertDescription } from '@/components/ui/alert';
import DottedSurface from '@/components/DottedSurface.vue';
import {
  Loader2,
  Mail,
  Lock,
  AlertCircle,
  CheckCircle2,
  Eye,
  EyeOff,
  ShieldCheck, ArrowRight
} from 'lucide-vue-next';
import { colors } from '@/config/designSystem';

const router = useRouter();
const email = ref('');
const password = ref('');
const error = ref<string | null>(null);
const success = ref<string | null>(null);
const loading = ref(false);
const showPassword = ref(false);

async function submit(e: Event) {
  e.preventDefault();
  error.value = null;
  success.value = null;
  loading.value = true;
  try {
    await auth.login({ email: email.value, password: password.value });
    success.value = 'Login successful! Redirecting...';
    setTimeout(() => router.push('/dashboard'), 1500);
  } catch (err: any) {
    error.value = err.message || 'Invalid credentials. Please try again.';
  } finally {
    loading.value = false;
  }
}

function togglePasswordVisibility() {
  showPassword.value = !showPassword.value;
}


</script>

<template>
  <div class="min-h-screen flex flex-col lg:flex-row overflow-hidden relative">
    <!-- Background Effects - Desktop Only -->
    <div class="absolute inset-0 hidden lg:block">
      <DottedSurface class-name="w-full h-full opacity-20" />
      <div class="absolute inset-0 bg-gradient-to-br from-primary-50 via-white to-secondary-50" />
    </div>

    <!-- LEFT SECTION - Login Form -->
    <div class="w-full lg:w-1/2 bg-background flex items-center justify-center px-4 sm:px-8 lg:px-12 py-8 lg:py-0 relative z-10">
      <div class="w-full max-w-md mx-auto space-y-8">
        <!-- Logo -->
        <div class="flex justify-center mb-8">
          <router-link to="/">
            <div class="flex items-center space-x-2">
              <img src="/assets/bitchest_logo.png" alt="BitChest Logo" class="h-14" />
            </div>
          </router-link>
        </div>

        <!-- Title -->
        <div class="text-center">
          <h1
            class="text-3xl md:text-4xl font-bold mb-2"
            :style="{ color: colors.text.primary }"
          >
            Welcome Back 👋
          </h1>
          <p
            class="text-sm"
            :style="{ color: colors.text.primary }"
          >
            Sign in to access your crypto dashboard
          </p>
        </div>

        <!-- Alerts -->
        <div class="space-y-3">
          <Alert
            v-if="error"
            variant="destructive"
            class="border-error/20 bg-error/5 text-error rounded-xl"
          >
            <AlertCircle class="h-4 w-4" />
            <AlertDescription class="text-xs">{{ error }}</AlertDescription>
          </Alert>
          <Alert
            v-if="success"
            variant="default"
            class="border-primary-200 bg-primary-50 text-primary-700 rounded-xl"
          >
            <CheckCircle2 class="h-4 w-4 text-primary-500" />
            <AlertDescription class="text-xs">{{ success }}</AlertDescription>
          </Alert>
        </div>

        <!-- Login Form -->
        <form class="space-y-6" @submit="submit">
          <!-- Email Field -->
          <div>
            <Label
              for="email"
              class="text-sm font-medium"
              :style="{ color: colors.text.primary }"
            >
              Email Address
            </Label>
            <div class="relative mt-2">
              <Mail
                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5"
                :style="{ color: colors.text.tertiary }"
              />
              <Input
                id="email"
                v-model="email"
                type="email"
                required
                placeholder="you@bitchest.com"
                class="pl-12 h-12 text-sm border-border-light focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200"
                :style="{
                  backgroundColor: colors.background,
                  color: colors.text.primary
                }"
              />
            </div>
          </div>

          <!-- Password Field -->
          <div>
            <div class="flex justify-between items-center">
              <Label
                for="password"
                class="text-sm font-medium"
                :style="{ color: colors.text.primary }"
              >
                Password
              </Label>
              <router-link
                to="/forgot-password"
                class="text-xs font-medium transition-colors duration-200 hover:text-primary-500"
                :style="{ color: colors.text.primary }"
              >
                Forgot password?
              </router-link>
            </div>
            <div class="relative mt-2">
              <Lock
                class="absolute left-4 top-1/2 -translate-y-1/2 h-5 w-5"
                :style="{ color: colors.text.tertiary }"
              />
              <Input
                id="password"
                v-model="password"
                :type="showPassword ? 'text' : 'password'"
                required
                placeholder="••••••••"
                class="pl-12 pr-12 h-12 text-sm border-border-light focus:border-primary-500 focus:ring-primary-500/20 rounded-xl transition-all duration-200"
                :style="{
                  backgroundColor: colors.background,
                  color: colors.text.primary
                }"
              />
              <Button
                type="button"
                variant="ghost"
                size="sm"
                class="absolute right-2 top-1/2 -translate-y-1/2 h-8 w-8 p-0 hover:bg-transparent"
                @click="togglePasswordVisibility"
              >
                <EyeOff
                  v-if="showPassword"
                  class="h-5 w-5"
                  :style="{ color: colors.text.tertiary }"
                />
                <Eye
                  v-else
                  class="h-5 w-5"
                  :style="{ color: colors.text.tertiary }"
                />
              </Button>
            </div>
          </div>

          <!-- Submit Button -->
          <Button
            :disabled="loading"
            type="submit"
            class="w-full h-12 bg-primary-500 hover:bg-primary-600 disabled:bg-primary-400 text-white font-semibold rounded-xl transition-all duration-200 shadow-md hover:shadow-lg active:scale-[0.98] flex items-center justify-center gap-2"
          >
            <Loader2 v-if="loading" class="h-5 w-5 animate-spin" />
            <span v-else>Sign In</span>
            <ArrowRight v-if="!loading" class="h-4 w-4" />
          </Button>
        </form>



        <!-- Legal Text -->
        <p
          class="text-center text-xs"
          :style="{ color: colors.text.primary }"
        >
          &copy; 2025 BitChest Technologies. All rights reserved.
        </p>
      </div>
    </div>

    <!-- RIGHT SECTION - Feature Highlights (Desktop Only) -->
    <div class="hidden lg:flex w-1/2 relative overflow-hidden">
      <!-- Background -->
      <div class="absolute inset-0 bg-gradient-to-br from-white via-primary-50/60 to-secondary-50" />

      <!-- Floating decorative elements -->
      <div class="absolute top-1/4 left-1/4 w-72 h-72 bg-primary-500/10 rounded-full blur-[120px] animate-pulse" />
      <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-secondary-500/10 rounded-full blur-[120px] animate-pulse delay-700" />

      <!-- Content Container -->
      <div class="relative z-10 flex flex-col justify-center items-center w-full h-full px-12">
        <div class="max-w-md text-center space-y-8">
          <!-- Main Title -->
          <h2
            class="text-4xl md:text-5xl font-extrabold leading-tight"
            :style="{ color: colors.text.primary }"
          >
            The Future of
            <span
              class="relative inline-block"
              :style="{ color: colors.primary[600] }"
            >
              Crypto Trading
              <span class="absolute -bottom-1 left-0 w-full h-0.5 bg-gradient-to-r from-transparent via-primary-400 to-transparent opacity-50" />
            </span>
          </h2>

          <!-- Subtitle -->
          <p
            class="text-lg"
            :style="{ color: colors.text.secondary }"
          >
            Discover a new way to trade with BitChest, your trusted platform.
          </p>

          <!-- Security Badge -->
          <div
            class="inline-flex items-center gap-2 px-4 py-2 rounded-full"
            :style="{
              backgroundColor: `${colors.primary[500]}10`,
              border: `1px solid ${colors.primary[500]}30`
            }"
          >
            <ShieldCheck
              class="w-4 h-4"
              :style="{ color: colors.primary[600] }"
            />
            <span
              class="text-sm font-medium"
              :style="{ color: colors.primary[700] }"
            >
              Bank‑grade Security
            </span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

* {
  font-family: 'Inter', system-ui, -apple-system, sans-serif;
  letter-spacing: 0.2px;
}

/* Custom scrollbar */
::-webkit-scrollbar {
  width: 6px;
}

::-webkit-scrollbar-track {
  background: v-bind('colors.background');
}

::-webkit-scrollbar-thumb {
  background: v-bind('colors.border.light');
  border-radius: 3px;
}

::-webkit-scrollbar-thumb:hover {
  background: v-bind('colors.border.medium');
}

/* Input placeholder styling */
input::placeholder {
  color: v-bind('colors.text.tertiary');
  opacity: 0.8;
}

/* Focus styles */
input:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
}

/* Smooth transitions */
.transition-all {
  transition-property: all;
}

/* Ensure no overflow on any screen size */
html, body {
  overflow-x: hidden;
}

/* Responsive adjustments */
@media (max-width: 1024px) {
  .min-h-screen.flex.flex-col.lg\:flex-row {
    flex-direction: column !important;
  }

  .w-full.lg\:w-1\/2:first-child {
    width: 100% !important;
    min-height: 100vh;
  }

  .hidden.lg\:flex {
    display: none !important;
  }
}

@media (max-width: 768px) {
  .max-w-md {
    width: 100%;
    padding: 0 1rem;
  }

  .px-4.sm\:px-8.lg\:px-12 {
    padding-left: 1rem;
    padding-right: 1rem;
  }

  h1 {
    font-size: 1.75rem !important;
  }
}

/* Animation for focus states */
@keyframes pulse {
  0%, 100% {
    box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4);
  }
  50% {
    box-shadow: 0 0 0 10px rgba(16, 185, 129, 0);
  }
}

/* Button hover effect */
button:not(:disabled):hover {
  transform: translateY(-1px);
}

button:not(:disabled):active {
  transform: translateY(0);
}

/* Decorative glow for the right panel */
.animate-pulse {
  animation: pulseGlow 4s ease-in-out infinite;
}

@keyframes pulseGlow {
  0%, 100% {
    opacity: 0.3;
  }
  50% {
    opacity: 0.6;
  }
}
</style>