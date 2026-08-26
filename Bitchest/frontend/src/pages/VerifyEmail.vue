<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Button } from '@/components/ui/button'
import api from '@/services/api'
import { initializeCsrf } from '@/services/auth'
import { AlertCircle, ArrowRight, CheckCircle2, Home, Loader2, Mail } from 'lucide-vue-next'
import { onMounted, ref } from 'vue'
import { useRoute, useRouter } from 'vue-router'

const router = useRouter()
const route = useRoute()
const loading = ref(true)
const status = ref<'loading' | 'success' | 'error'>('loading')
const message = ref('')
const token = ref('')

onMounted(async () => {
  token.value = (route.query.token as string) || ''
  
  if (!token.value) {
    status.value = 'error'
    message.value = 'Verification token missing. Please use the link from your email.'
    loading.value = false
    return
  }

  try {
    // Initialisation du token CSRF avant la vérification de l'adresse
    await initializeCsrf()
    
    const response = await api.auth.verifyEmail({ token: token.value })
    
    if (response.success || response) {
      status.value = 'success'
      message.value = response.message || 'Your email has been verified successfully!'
    }
  } catch (error: any) {
    status.value = 'error'
    message.value = error.message || 'Verification failed. Please try again.'
  } finally {
    loading.value = false
  }
})

const redirectToSignin = () => {
  router.push('/signin')
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-gray-50 to-white p-6">
    <div class="w-full max-w-md mx-auto">
      <!-- Card Container -->
      <div class="bg-white rounded-2xl shadow-xl p-8 border border-gray-100">
        
        <!-- Success State -->
        <div v-if="!loading && status === 'success'" class="text-center space-y-6">
          <!-- Animated Checkmark -->
          <div class="relative">
            <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-emerald-50 to-transparent flex items-center justify-center">
              <div class="absolute inset-0 animate-ping rounded-full bg-emerald-100"></div>
              <CheckCircle2 class="w-14 h-14 text-emerald-600" />
            </div>
          </div>

          <!-- Success Message -->
          <div class="space-y-3">
            <h1 class="text-3xl font-bold text-gray-900">Email Verified!</h1>
            <p class="text-gray-600">{{ message }}</p>
          </div>

          <!-- Status Info -->
          <Alert class="bg-gray-50 border-gray-200 rounded-xl">
            <Mail class="h-5 w-5 text-gray-600" />
            <AlertDescription class="text-gray-700">
              Your account request has been confirmed. An administrator will review it shortly.
            </AlertDescription>
          </Alert>

          <!-- Actions -->
          <div class="space-y-3 pt-2">
            <Button 
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl transition-all duration-300 hover:scale-[1.02] active:scale-95"
              @click="redirectToSignin"
            >
              <span>Go to Sign In</span>
              <ArrowRight class="ml-2 w-5 h-5" />
            </Button>
          </div>
        </div>

        <!-- Error State -->
        <div v-else-if="!loading && status === 'error'" class="text-center space-y-6">
          <!-- Error Icon -->
          <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-red-50 to-transparent flex items-center justify-center border-4 border-red-100">
            <AlertCircle class="w-14 h-14 text-red-500" />
          </div>

          <!-- Error Message -->
          <div class="space-y-3">
            <h1 class="text-3xl font-bold text-gray-900">Verification Failed</h1>
            <p class="text-gray-600">{{ message }}</p>
          </div>

          <!-- Error Info -->
          <Alert class="bg-red-50 border-red-200 rounded-xl">
            <AlertCircle class="h-5 w-5 text-red-500" />
            <AlertDescription class="text-red-700">
              Please ensure you're using the complete link from your verification email.
            </AlertDescription>
          </Alert>

          <!-- Actions -->
          <div class="space-y-3 pt-2">
            <Button 
              class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-semibold py-3 rounded-xl"
              @click="redirectToSignin"
            >
              Return to Sign In
            </Button>
            <Button 
              variant="outline"
              class="w-full border-gray-300 text-gray-700 hover:bg-gray-50 py-3 rounded-xl"
              @click="router.push('/')"
            >
              <Home class="mr-2 w-5 h-5" />
              Back to Home
            </Button>
          </div>
        </div>

        <!-- Loading State -->
        <div v-else class="text-center space-y-6">
          <!-- Spinner -->
          <div class="relative">
            <div class="w-24 h-24 mx-auto rounded-full bg-gradient-to-br from-gray-100 to-transparent flex items-center justify-center">
              <Loader2 class="w-14 h-14 text-gray-400 animate-spin" />
            </div>
          </div>

          <!-- Loading Text -->
          <div class="space-y-3">
            <h1 class="text-3xl font-bold text-gray-900">Verifying Email</h1>
            <p class="text-gray-600">We're confirming your email address. Please wait...</p>
          </div>

          <!-- Loading Progress -->
          <div class="pt-2">
            <div class="h-1 w-full bg-gray-200 rounded-full overflow-hidden">
              <div class="h-full bg-emerald-500 animate-pulse"></div>
            </div>
          </div>
        </div>

        <!-- Footer -->
        <div class="mt-8 pt-6 border-t border-gray-100 text-center">
          <p class="text-sm text-gray-500">
            Need help? 
            <a href="mailto:support@example.com" class="text-emerald-600 hover:underline font-medium">
              Contact Support
            </a>
          </p>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes pulse {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.5; }
}
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}
.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes ping {
  75%, 100% {
    transform: scale(2);
    opacity: 0;
  }
}
.animate-ping {
  animation: ping 1s cubic-bezier(0, 0, 0.2, 1) infinite;
}
</style>