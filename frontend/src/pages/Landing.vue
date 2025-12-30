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
  <div class="min-h-screen bg-gradient-to-b from-gray-900 via-gray-900 to-black">
    <Header />
    <main class="animate-in">
      <HeroSection id="hero" />
      <CryptoShowcase id="crypto-tracker" />
      <FeaturesSection id="features" />
      <InvestorFeatures id="for-everyone" />
      <HowItWork id="how-it-works" />
      <Newsletter id="contact" />
    </main>
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

/* Responsive optimizations */
@media (max-width: 640px) {
  .animate-in {
    animation-duration: 0.4s;
    padding-top: 0;
  }
}

/* Reduce motion for accessibility */
@media (prefers-reduced-motion: reduce) {
  .animate-in {
    animation: none;
    opacity: 1;
  }
}

/* Tablet optimizations */
@media (min-width: 641px) and (max-width: 1024px) {
  .animate-in {
    animation-duration: 0.5s;
  }
}

/* Large screen optimizations */
@media (min-width: 1025px) {
  .animate-in {
    animation-duration: 0.6s;
  }
}

/* Smooth scrolling for anchor links */
html {
  scroll-behavior: smooth;
}

/* Better touch targets on mobile */
@media (max-width: 768px) {
  a, button {
    min-height: 44px;
    min-width: 44px;
  }
}

/* Prevent horizontal overflow */
body {
  overflow-x: hidden;
}

/* Optimize font rendering */
* {
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-rendering: optimizeLegibility;
}

/* Improve image loading */
img {
  max-width: 100%;
  height: auto;
}

/* Better focus styles for accessibility */
:focus-visible {
  outline: 2px solid #3b82f6;
  outline-offset: 2px;
}

/* Optimize section spacing for mobile */
@media (max-width: 640px) {
  section {
    padding-top: 3rem !important;
    padding-bottom: 3rem !important;
  }
}
</style>