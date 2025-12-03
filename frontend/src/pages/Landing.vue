<script setup lang="ts">
import Header from '@/components/landing/Header.vue'
import HeroSection from '@/components/landing/HeroSection.vue'
import FeaturesSection from '@/components/landing/FeaturesSection.vue'
import InvestorFeatures from '@/components/landing/InvestorFeatures.vue'
import Newsletter from '@/components/landing/Newsletter.vue'
import Footer from '@/components/landing/Footer.vue'
import HowItWork from '../components/landing/HowItWork.vue'
import CryptoShowcase from '../components/landing/CryptoShowcase.vue'
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C]">
    <Header />
    <HeroSection id="hero" />
    <CryptoShowcase id="crypto-tracker" />
    <FeaturesSection id="features" />
    <InvestorFeatures id="for-everyone" />
    <HowItWork id="how-it-works" />
    <Newsletter id="contact" />
    <Footer />
  </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');

* {
  font-family: 'Inter', sans-serif;
}

.animate-in {
  animation: fadeInSlide 0.6s ease-out forwards;
  opacity: 0;
}

@keyframes fadeInSlide {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.slide-in-from-left {
  animation-delay: calc(var(--index) * 0.1s);
}

.slide-in-from-right {
  animation-delay: calc(var(--index) * 0.1s);
}
</style>
