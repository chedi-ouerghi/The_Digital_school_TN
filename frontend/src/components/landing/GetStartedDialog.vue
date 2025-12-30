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

    console.log('API Response:', response);

    if (response && response.message) {
      notification.value = {
        type: 'success',
        message: 'Your request has been successfully submitted. Our team will contact you soon.'
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
    console.error('API Error:', error);
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
       <button 
      @click="isOpen = true"
      class="px-6 py-2.5 rounded-lg bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all flex items-center gap-2"
    >
      Get Started
      <ArrowRight class="w-4 h-4" />
    </button>
    </DialogTrigger>
    
    <DialogContent class="sm:max-w-md bg-[#071B2C] border border-white/20 text-white">
      <DialogHeader>
        <DialogTitle class="text-2xl font-bold text-center text-blue-500 ">
          {{ currentStep === 'initial' ? 'Welcome to BitChest' : 'Create an Account' }}
        </DialogTitle>
        <DialogDescription class="text-center text-white/80 mt-2">
        <template v-if="currentStep === 'initial'">
          Join the most intuitive cryptocurrency trading platform. 
          Create an account or log in to start your journey in the world of crypto.
        </template>
        <template v-else>
          Fill out the form below to create your account. 
          Our team will contact you shortly to complete your registration.
        </template>
      </DialogDescription>

      <!-- Notifications -->
      <div v-if="notification" class="px-6 pb-4">
        <Alert :class="notification.type === 'success' ? 'border-[#01FF19] bg-[#01FF19]/10' : 'border-[#FF5964] bg-[#FF5964]/10'">
          <AlertDescription :class="notification.type === 'success' ? 'text-[#01FF19]' : 'text-[#FF5964]'" class="flex items-center gap-2">
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
            @click="handleSignIn"
            class="w-full bg-blue-500 hover:bg-blue-900 text-[#071B2C] font-semibold py-3 rounded-lg transition-all duration-300 flex items-center justify-center gap-2"
          >
            <LogIn class="h-4 w-4" />
            Log In
          </Button>
          
          <Button 
            @click="currentStep = 'signup'"
            class="w-full bg-white/10 hover:bg-white/20 text-white font-semibold py-3 rounded-lg border border-white/30 transition-all duration-300 flex items-center justify-center gap-2"
          >
            <UserPlus class="h-4 w-4" />
            Create Account
          </Button>
        </div>

        <!-- Signup step -->
        <div v-else class="space-y-4">
          <div class="space-y-2">
            <Label for="name" class="text-white/90">Full Name</Label>
            <Input
              id="name"
              v-model="formData.name"
              placeholder="Enter your full name"
              class="bg-white/5 border-white/20 text-white placeholder:text-white/50 focus:border-[#01FF19] focus:ring-[#01FF19]/20"
              :class="{ 'border-red-500': errors.name }"
            />
            <p v-if="errors.name" class="text-red-400 text-sm">{{ errors.name }}</p>
          </div>

          <div class="space-y-2">
            <Label for="email" class="text-white/90">Email</Label>
            <Input
              id="email"
              v-model="formData.email"
              type="email"
              placeholder="Enter your email address"
              class="bg-white/5 border-white/20 text-white placeholder:text-white/50 focus:border-[#01FF19] focus:ring-[#01FF19]/20"
              :class="{ 'border-red-500': errors.email }"
            />
            <p v-if="errors.email" class="text-red-400 text-sm">{{ errors.email }}</p>
          </div>

          <div class="flex gap-3 pt-4">
            <Button
              @click="handleCancel"
              variant="outline"
              class="flex-1 bg-transparent border-white/30 text-white hover:bg-white/10"
              :disabled="isLoading"
            >
              <X class="h-4 w-4 mr-2" />
              Cancel
            </Button>
            <Button
              @click="handleSignUp"
              class="flex-1 bg-blue-500 hover:bg-blue-900  text-[#071B2C] font-semibold"
              :disabled="isLoading"
            >
              <ArrowRight class="h-4 w-4 mr-2" />
              {{ isLoading ? 'Sending...' : 'Submit' }}
            </Button>
          </div>
        </div>
      </div>

      <DialogFooter class="justify-center">
        <p class="text-xs text-white/60 text-center">
          By creating an account, you agree to our Terms of Use and Privacy Policy.
        </p>
      </DialogFooter>
    </DialogContent>
  </Dialog>
</template>