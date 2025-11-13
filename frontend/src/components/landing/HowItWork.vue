<script setup lang="ts">
import { ref } from 'vue'
/**
 * Ce composant reproduit fidèlement la section "Trade Crypto on KuCoin Worldwide"
 * - Texte à gauche (titre + paragraphe)
 * - À droite : carte dashboard sombre (maquette HTML/CSS)
 * - En dessous (ou en bas à gauche selon écran) : 4 blocs features avec icônes + texte
 *
 * Couleurs strictement respectées : #38618C, #35A7FF, #01FF19, #FF5964
 */

const features = [
  {
    title: 'Buy Crypto with a Credit or Debit Card',
    text: 'Use your card to buy crypto instantly. Select amount, confirm and pay with Visa or Mastercard.',
    color: '#35A7FF',
    icon: '💳',
  },
  {
    title: 'Buy Crypto via Bank Deposit',
    text: 'Direct bank deposits supported — faster and low-fee funding methods.',
    color: '#01FF19',
    icon: '🏦',
  },
  {
    title: 'Expand Your Crypto Portfolio',
    text: 'Stake, lend, and diversify across many assets with advanced portfolio tools.',
    color: '#FF5964',
    icon: '📈',
  },
  {
    title: 'Buy on the Spot Market',
    text: 'Access the KuCoin Spot Market and trade hundreds of pairs with deep liquidity.',
    color: '#38618C',
    icon: '🪙',
  },
]

// Timeframe state and chart data using the required palette
const timeframe = ref<'1H' | '24H' | '7D' | '1M'>('24H')
const chartPointsMap: Record<string, string> = {
  '1H': '0,36 8,34 16,35 24,33 32,31 40,29 48,28 56,27 64,25 72,24 80,23 88,22 96,21 100,20',
  '24H': '0,35 8,28 16,30 24,22 32,18 40,20 48,15 56,18 64,12 72,10 80,8 88,6 96,4 100,3',
  '7D': '0,37 8,34 16,31 24,28 32,26 40,24 48,20 56,18 64,22 72,19 80,16 88,14 96,12 100,11',
  '1M': '0,38 8,35 16,33 24,30 32,27 40,25 48,22 56,20 64,18 72,16 80,14 88,12 96,10 100,9'
}
const deltaMap: Record<string, string> = {
  '1H': '+0.6%','24H': '+2.4%','7D': '+5.8%','1M': '+12.2%'
}
</script>

<template>
  <section class="relative py-24 lg:py-32 overflow-hidden bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C] text-white font-[Celias]">
    <!-- Éclat d'arrière-plan -->
    <div class="absolute inset-0">
      <div class="absolute top-20 left-20 w-96 h-96 bg-[#35A7FF]/20 rounded-full blur-[120px]"></div>
      <div class="absolute bottom-20 right-20 w-96 h-96 bg-[#01FF19]/15 rounded-full blur-[120px]"></div>
    </div>

    <div class="relative z-10 container mx-auto px-6 lg:px-16">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        <!-- LEFT: Texte principal -->
        <div class="lg:col-span-5 animate-fade-in">
          <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-[#35A7FF]/10 border border-[#35A7FF]/30 text-[#35A7FF] font-medium text-sm tracking-wide mb-6">
            <span class="text-lg">🌍</span> GLOBAL CRYPTO TRADING HUB
          </div>

          <h2 class="text-4xl md:text-5xl xl:text-6xl font-extrabold leading-tight mb-6">
            Trade Crypto on <span class="text-[#01FF19] drop-shadow-[0_0_10px_#01FF19]">BitChest</span> Worldwide
          </h2>

          <p class="text-lg text-white/80 max-w-xl mb-8">
            Trade crypto on BitChest, your gateway to global markets.
            With a wide range of digital assets and advanced trading features,
            BitChest offers a seamless and secure platform for traders worldwide to buy, sell, and manage their crypto assets.
          </p>

          <!-- feature icons row (mobile/desktop) -->
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div
              v-for="(f, i) in features"
              :key="i"
              class="relative flex gap-4 p-5 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-[#01FF19]/40 hover:bg-white/10 transition-all duration-300 group"
              :style="{ animationDelay: `${0.2 + i * 0.1}s` }"
            >
              <div
                class="w-12 h-12 flex items-center justify-center rounded-lg text-white text-xl font-bold shrink-0"
                :style="{ background: `${f.color}20`, border: `1px solid ${f.color}40` }"
              >
                <span>{{ f.icon }}</span>
              </div>
              <div>
                <h3 class="text-sm font-semibold text-white mb-1">{{ f.title }}</h3>
                <p class="text-sm text-white/70">{{ f.text }}</p>
              </div>
            </div>
          </div>
        </div>

        <!-- RIGHT: Dashboard card -->
        <div class="lg:col-span-7 flex justify-center lg:justify-end animate-fade-in delay-400">
          <div
            class="relative w-full max-w-2xl rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 shadow-[0_0_60px_-10px_rgba(1,255,25,0.2)] overflow-hidden hover:border-[#01FF19]/40 transition-all duration-300"
            aria-hidden="true"
          >
            <!-- Top dark nav -->
            <div class="flex items-center justify-between px-6 py-4 bg-gradient-to-r from-[#071826]/80 to-[#081427]/80 border-b border-white/10">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-lg bg-[#35A7FF]/20 border border-[#35A7FF]/40 flex items-center justify-center text-[#35A7FF] font-semibold">BC</div>
                <div>
                  <div class="text-sm text-white/90">Funding Account</div>
                  <div class="text-xs text-white/60">USD Wallet</div>
                </div>
              </div>
              <div class="text-right">
                <div class="text-sm text-white font-semibold">0.17854247 BTC</div>
                <div class="text-xs text-white/60">≈ $4,927.12</div>
              </div>
            </div>

            <!-- Main content grid -->
            <div class="p-6 bg-gradient-to-b from-[#071826]/50 to-[#071826]/30">
              <div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-6">
                <!-- left column: small stats -->
                <div class="col-span-1 flex flex-col gap-3">
                  <div class="p-4 rounded-lg bg-white/5 border border-white/10">
                    <div class="flex justify-between items-center">
                      <div class="text-xs text-white/70">Available</div>
                      <div class="text-sm font-semibold text-[#35A7FF]">0.087 BTC</div>
                    </div>
                    <div class="text-xs text-white/60 mt-2">Use for trading and withdrawal</div>
                  </div>

                  <div class="p-4 rounded-lg bg-white/5 border border-white/10">
                    <div class="flex justify-between items-center">
                      <div class="text-xs text-white/70">In Orders</div>
                      <div class="text-sm font-semibold text-[#01FF19]">0.054 BTC</div>
                    </div>
                    <div class="text-xs text-white/60 mt-2">Open buy/sell orders</div>
                  </div>
                </div>

                <!-- center column: large chart area -->
                <div class="col-span-1 lg:col-span-2">
                  <div class="chart-area rounded-lg overflow-hidden border border-white/10 bg-gradient-to-r from-[#071826]/40 to-[#071826]/20 p-4">
                    <!-- simulated chart header -->
                    <div class="flex justify-between items-center mb-3">
                      <div class="text-sm font-medium text-white">BTC / USD</div>
                      <div class="flex items-center gap-2">
                        <button
                          v-for="tf in ['1H','24H','7D','1M']"
                          :key="tf"
                          @click="timeframe = tf as any"
                          class="px-2.5 py-1 rounded-full text-xs font-semibold border transition-all"
                          :class="timeframe === tf ? 'bg-[#01FF19] text-[#071B2C] border-[#01FF19]' : 'bg-white/5 text-white/70 border-white/10 hover:border-[#35A7FF]/40'"
                        >
                          {{ tf }}
                        </button>
                      </div>
                    </div>

                    <!-- mock chart lines using svg for fidelity -->
                    <div class="relative h-44 w-full bg-[linear-gradient(180deg,#04202b,transparent)] rounded-md overflow-hidden">
                      <svg viewBox="0 0 100 40" preserveAspectRatio="none" class="absolute inset-0 w-full h-full">
                        <defs>
                          <linearGradient id="g1" x1="0" x2="1">
                            <stop offset="0%" stop-color="#35A7FF" stop-opacity="0.6"/>
                            <stop offset="100%" stop-color="#01FF19" stop-opacity="0.2"/>
                          </linearGradient>
                        </defs>
                        <polyline fill="none" stroke="url(#g1)" stroke-width="0.8"
                          :points="chartPointsMap[timeframe]" />
                      </svg>

                      <!-- small price tag -->
                      <div class="absolute -top-3 right-5 bg-[#01FF19] text-[#071B2C] font-semibold text-xs px-3 py-1 rounded-full shadow-lg">{{ deltaMap[timeframe] }}</div>
                    </div>

                    <!-- small table rows -->
                    <div class="mt-4 grid grid-cols-3 gap-3 text-xs">
                      <div class="p-3 rounded-lg bg-white/5 border border-white/10">
                        <div class="font-medium text-white">BTC</div>
                        <div class="text-white/60 text-xs mt-1">48,927</div>
                      </div>
                      <div class="p-3 rounded-lg bg-white/5 border border-white/10">
                        <div class="font-medium text-white">ETH</div>
                        <div class="text-white/60 text-xs mt-1">3,120</div>
                      </div>
                      <div class="p-3 rounded-lg bg-white/5 border border-white/10">
                        <div class="font-medium text-white">USDT</div>
                        <div class="text-white/60 text-xs mt-1">1.00</div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <!-- bottom tiny stats -->
              <div class="flex gap-4 mt-4">
                <div class="flex-1 rounded-lg p-4 bg-white/5 border border-white/10 text-xs">
                  <div class="text-white/70">24H Volume</div>
                  <div class="font-semibold text-white mt-1">1,234 BTC</div>
                </div>
                <div class="flex-1 rounded-lg p-4 bg-white/5 border border-white/10 text-xs">
                  <div class="text-white/70">Active Orders</div>
                  <div class="font-semibold text-white mt-1">12</div>
                </div>
                <div class="flex-1 rounded-lg p-4 bg-white/5 border border-white/10 text-xs">
                  <div class="text-white/70">Trusted</div>
                  <div class="font-semibold text-white mt-1">Trusted by millions</div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes fade-in {
  from { opacity: 0; transform: translateY(25px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in { 
  opacity: 0; 
  animation: fade-in 1s ease forwards; 
}
.delay-400 { animation-delay: 0.4s; }
</style>
