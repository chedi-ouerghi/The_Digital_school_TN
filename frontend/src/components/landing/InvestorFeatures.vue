<script setup lang="ts">
import { Check } from 'lucide-vue-next'
</script>

<template>
  <section class="relative overflow-hidden py-24">
    <div class="absolute inset-0 bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C]" aria-hidden="true"></div>
    
    <div class="relative container mx-auto px-6">
      <div class="text-center mb-16">
        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-[#35A7FF]/10 border border-[#35A7FF]/30 text-[#35A7FF]">
          <span class="w-2 h-2 bg-[#35A7FF] rounded-full animate-pulse"></span>
          For Everyone
        </div>
        <h2 class="mt-4 font-[Celias] text-3xl md:text-4xl font-bold text-white">Built for Newcomers & Pros</h2>
        <p class="mt-2 text-[#B0CADB]">Clear tools for beginners, powerful features for seasoned traders.</p>
      </div>

      <div class="grid md:grid-cols-3 gap-6">
        <!-- Card 1 -->
        <div class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 backdrop-blur-md">
          <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
            <div class="absolute -top-10 -right-10 w-40 h-40 bg-[#35A7FF]/20 rounded-full blur-2xl"></div>
          </div>
          <div class="p-6">
            <h3 class="font-[Celias] text-xl font-semibold text-white group-hover:text-[#35A7FF] transition-colors">Simple & Fast</h3>
            <p class="text-[#B0CADB] mt-2">Quick buy/sell flow with instant feedback and clear confirmations.</p>
            <ul class="mt-4 space-y-2">
              <li class="flex items-center gap-2 text-[#B0CADB]"><Check class="h-4 w-4 text-[#01FF19]"/> One-tap trades</li>
              <li class="flex items-center gap-2 text-[#B0CADB]"><Check class="h-4 w-4 text-[#01FF19]"/> Guided flows</li>
            </ul>
          </div>
        </div>

        <!-- Card 2 -->
        <div class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 backdrop-blur-md">
          <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
            <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-[#FF5964]/20 rounded-full blur-2xl"></div>
          </div>
          <div class="p-6">
            <h3 class="font-[Celias] text-xl font-semibold text-white group-hover:text-[#FF5964] transition-colors">Pro Tools</h3>
            <p class="text-[#B0CADB] mt-2">Granular control, multi-timeframe analysis, and precise order handling.</p>
            <ul class="mt-4 space-y-2">
              <li class="flex items-center gap-2 text-[#B0CADB]"><Check class="h-4 w-4 text-[#01FF19]"/> Advanced charts</li>
              <li class="flex items-center gap-2 text-[#B0CADB]"><Check class="h-4 w-4 text-[#01FF19]"/> Custom alerts</li>
            </ul>
          </div>
        </div>

        <!-- Card 3 -->
        <div class="group relative overflow-hidden rounded-xl border border-white/10 bg-white/5 backdrop-blur-md">
          <div class="absolute inset-0 opacity-0 group-hover:opacity-100 transition-opacity duration-500">
            <div class="absolute -top-10 -left-6 w-40 h-40 bg-[#01FF19]/15 rounded-full blur-2xl"></div>
          </div>
          <div class="p-6">
            <h3 class="font-[Celias] text-xl font-semibold text-white group-hover:text-[#01FF19] transition-colors">Secure & Transparent</h3>
            <p class="text-[#B0CADB] mt-2">Clear fees, strong protections, and meaningful status updates.</p>
            <ul class="mt-4 space-y-2">
              <li class="flex items-center gap-2 text-[#B0CADB]"><Check class="h-4 w-4 text-[#01FF19]"/> Full audit trail</li>
              <li class="flex items-center gap-2 text-[#B0CADB]"><Check class="h-4 w-4 text-[#01FF19]"/> Bank-grade security</li>
            </ul>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>
