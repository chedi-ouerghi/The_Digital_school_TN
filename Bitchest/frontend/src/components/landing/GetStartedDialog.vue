<script setup lang="ts">
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import {
  Dialog,
  DialogContent,
  DialogDescription,
  DialogFooter,
  DialogHeader,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { ArrowRight, UserPlus, LogIn, X, CheckCircle2, AlertCircle } from 'lucide-vue-next';
import { Alert, AlertDescription } from '@/components/ui/alert';
import api from '@/services/api';
import { colors } from '@/config/designSystem';

const router = useRouter();
const isOpen = ref(false);
const currentStep = ref<'initial' | 'signup'>('initial');
const isLoading = ref(false);
const notification = ref<{ type: 'success' | 'error', message: string } | null>(null);

// Form data
const formData = ref({
  name: '',
  email: ''
});

// Form errors
const errors = ref({
  name: '',
  email: ''
});

const resetForm = () => {
  formData.value = { name: '', email: '' };
  errors.value = { name: '', email: '' };
  currentStep.value = 'initial';
  isLoading.value = false;
  notification.value = null;
};

const handleSignIn = () => {
  isOpen.value = false;
  router.push('/signin');
};

const validateForm = () => {
  let isValid = true;
  errors.value = { name: '', email: '' };

  if (!formData.value.name.trim()) {
    errors.value.name = 'Full name is required';
    isValid = false;
  }

  if (!formData.value.email.trim()) {
    errors.value.email = 'Email is required';
    isValid = false;
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.value.email)) {
    errors.value.email = 'Invalid email format';
    isValid = false;
  }

  return isValid;
};

const handleSignUp = async () => {
  if (!validateForm()) return;

  isLoading.value = true;
  notification.value = null;

  try {
    const response = await api.auth.requestAccount({
      name: formData.value.name,
      email: formData.value.email
    });

    if (response && response.message) {
      notification.value = {
        type: 'success',
        message: 'Your request was sent successfully. Our team will contact you soon.'
      };

      await new Promise(resolve => setTimeout(resolve, 3000));
      resetForm();
      isOpen.value = false;
    } else {
      notification.value = {
        type: 'error',
        message: 'An error occurred while creating your request.'
      };
    }
  } catch (error: any) {
    const errorMessage = error.message || 'An unexpected error occurred.';
    notification.value = {
      type: 'error',
      message: errorMessage
    };
  } finally {
    isLoading.value = false;
  }
};

const handleCancel = () => {
  resetForm();
  isOpen.value = false;
};
</script>

<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <Button
        class="px-6 py-2.5 rounded-xl bg-gradient-to-r from-primary-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all hover:scale-[1.02] active:scale-[0.98]"
      >
        Get Started
        <ArrowRight class="w-4 h-4 ml-1" />
      </Button>
    </DialogTrigger>

    <DialogContent
      class="sm:max-w-md bg-white border shadow-2xl"
    >
      <DialogHeader>
        <DialogTitle
          class="text-2xl font-bold text-center"
          :style="{ color: colors.text.primary }"
        >
          {{ currentStep === 'initial' ? 'Welcome to BitChest' : 'Create an Account' }}
        </DialogTitle>
        <DialogDescription
          class="text-center mt-2"
          :style="{ color: colors.text.secondary }"
        >
          <template v-if="currentStep === 'initial'">
            Join the most intuitive crypto trading platform.
            Create an account or sign in to get started.
          </template>
          <template v-else>
            Fill in the form below to request your account.
            Our team will contact you to complete your registration.
          </template>
        </DialogDescription>

        <!-- Notifications -->
        <div v-if="notification" class="px-6 pb-4">
          <Alert :class="notification.type === 'success' ? 'border-primary-500 bg-primary-50' : 'border-error bg-red-50'">
            <AlertDescription
              :class="notification.type === 'success' ? 'text-primary-700' : 'text-error'"
              class="flex items-center gap-2 text-sm"
            >
              <CheckCircle2 v-if="notification.type === 'success'" class="h-4 w-4" />
              <AlertCircle v-else class="h-4 w-4" />
              {{ notification.message }}
            </AlertDescription>
          </Alert>
        </div>
      </DialogHeader>

      <div class="py-6">
        <!-- Initial step -->
        <div v-if="currentStep === 'initial'" class="space-y-4">
          <Button
            class="w-full bg-white border-2 border-slate-200 text-slate-800 hover:bg-slate-50 font-semibold py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2"
            @click="handleSignIn"
          >
            <LogIn class="h-4 w-4" />
            Sign In
          </Button>

          <Button
            class="w-full py-3 rounded-xl transition-all duration-200 flex items-center justify-center gap-2 font-semibold text-white shadow-lg hover:brightness-110"
            :style="{
              backgroundColor: colors.primary[500],
              color: colors.white,
              border: `1px solid ${colors.primary[600]}`
            }"
            @click="currentStep = 'signup'"
          >
            <UserPlus class="h-4 w-4" />
            Create an Account
          </Button>
        </div>

        <!-- Signup step -->
        <div v-else class="space-y-4">
          <div class="space-y-2">
            <Label for="name" :style="{ color: colors.text.primary }">Full Name</Label>
            <Input
              id="name"
              v-model="formData.name"
              placeholder="Enter your full name"
              class="bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-primary-500 focus:ring-primary-500/20"
              :class="{ 'border-error': errors.name }"
            />
            <p v-if="errors.name" class="text-red-500 text-sm">{{ errors.name }}</p>
          </div>

          <div class="space-y-2">
            <Label for="email" :style="{ color: colors.text.primary }">Email</Label>
            <Input
              id="email"
              v-model="formData.email"
              type="email"
              placeholder="Enter your email address"
              class="bg-white border-slate-300 text-slate-900 placeholder:text-slate-400 focus:border-primary-500 focus:ring-primary-500/20"
              :class="{ 'border-error': errors.email }"
            />
            <p v-if="errors.email" class="text-red-500 text-sm">{{ errors.email }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <Button
              variant="outline"
              class="flex-1 bg-white border-slate-300 text-slate-700 hover:bg-slate-50 hover:text-slate-900"
              :disabled="isLoading"
              @click="handleCancel"
            >
              <X class="h-4 w-4 mr-2" />
              Cancel
            </Button>
            <Button
              class="flex-1 font-semibold text-white shadow-lg transition-all hover:brightness-110"
              :style="{
                backgroundColor: colors.primary[500],
                color: colors.white,
                border: `1px solid ${colors.primary[600]}`
              }"
              :disabled="isLoading"
              @click="handleSignUp"
            >
              <ArrowRight class="h-4 w-4 mr-2" />
              {{ isLoading ? 'Sending...' : 'Send Request' }}
            </Button>
          </div>
        </div>
      </div>

      <DialogFooter class="justify-center">
        <p
          class="text-xs text-center"
          :style="{ color: colors.text.tertiary }"
        >
          By creating an account, you agree to our Terms of Service and Privacy Policy.
        </p>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>

<style scoped>
/* Smooth transitions */
.transition-all {
  transition-property: all;
}

/* Button base styles */
button {
  transition: all 0.2s ease-in-out;
}

button:hover:not(:disabled) {
  transform: translateY(-1px);
}

button:active:not(:disabled) {
  transform: translateY(0);
}
</style>