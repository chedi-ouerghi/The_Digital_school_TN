<script setup lang="ts">
import { Instagram, Linkedin, Mail, Send, Twitter } from 'lucide-vue-next';

const socialLinks = [
  { icon: Twitter, label: 'Twitter', url: '#' },
  { icon: Linkedin, label: 'LinkedIn', url: '#' },
  { icon: Instagram, label: 'Instagram', url: '#' }
]
</script>

<template>
  <section class="py-20 md:py-28 bg-gradient-to-b from-gray-900 to-black">
    <div class="container mx-auto px-4 md:px-6">
      
      <!-- Background Elements -->
      <div class="absolute inset-0 overflow-hidden">
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-blue-500/5 rounded-full blur-3xl"></div>
        <div class="absolute bottom-1/4 right-1/4 w-96 h-96 bg-emerald-500/5 rounded-full blur-3xl"></div>
      </div>

      <div class="relative z-10">
        <!-- Header -->
        <div class="text-center mb-12">
          <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/5 backdrop-blur-sm border border-white/10 mb-6">
            <Mail class="w-4 h-4 text-blue-400" />
            <span class="text-sm font-medium text-white">STAY UPDATED</span>
          </div>
          
          <h2 class="text-4xl md:text-5xl font-bold text-white mb-4">
            Join Our
            <span class="bg-gradient-to-r from-blue-400 to-emerald-400 bg-clip-text text-transparent">
              Community
            </span>
          </h2>
          
          <p class="text-gray-400 max-w-xl mx-auto">
            Get exclusive market insights, platform updates, and trading tips directly to your inbox
          </p>
        </div>

        <!-- Newsletter Form -->
        <div class="max-w-2xl mx-auto mb-16">
          <div class="relative group">
            <!-- Form Card -->
            <div class="p-8 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-white/20 transition-all">
              <form class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                  <div>
                    <label class="block text-sm text-gray-400 mb-2">First Name</label>
                    <input
                      type="text"
                      placeholder="John"
                      class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-blue-400 transition-all"
                    />
                  </div>
                  <div>
                    <label class="block text-sm text-gray-400 mb-2">Email Address</label>
                    <input
                      type="email"
                      placeholder="john@example.com"
                      class="w-full px-4 py-3 rounded-lg bg-white/5 border border-white/10 text-white placeholder-gray-500 focus:outline-none focus:border-blue-400 transition-all"
                    />
                  </div>
                </div>

                <!-- Preferences -->
                <div class="space-y-3">
                  <div class="text-sm text-gray-400 mb-3">What updates interest you?</div>
                  <div class="flex flex-wrap gap-3">
                    <label class="inline-flex items-center">
                      <input type="checkbox" class="hidden peer">
                      <div class="px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-300 peer-checked:bg-blue-500/20 peer-checked:border-blue-400 peer-checked:text-white transition-all cursor-pointer">
                        Market Analysis
                      </div>
                    </label>
                    <label class="inline-flex items-center">
                      <input type="checkbox" class="hidden peer">
                      <div class="px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-300 peer-checked:bg-emerald-500/20 peer-checked:border-emerald-400 peer-checked:text-white transition-all cursor-pointer">
                        Platform Updates
                      </div>
                    </label>
                    <label class="inline-flex items-center">
                      <input type="checkbox" class="hidden peer">
                      <div class="px-4 py-2 rounded-lg bg-white/5 border border-white/10 text-gray-300 peer-checked:bg-purple-500/20 peer-checked:border-purple-400 peer-checked:text-white transition-all cursor-pointer">
                        Trading Tips
                      </div>
                    </label>
                  </div>
                </div>

                <!-- Submit Button -->
                <button
                  type="submit"
                  class="w-full py-4 rounded-xl bg-gradient-to-r from-blue-500 to-emerald-500 text-white font-semibold hover:opacity-90 transition-all flex items-center justify-center gap-3"
                >
                  <Send class="w-5 h-5" />
                  Subscribe to Newsletter
                </button>

                <!-- Privacy Notice -->
                <p class="text-center text-gray-500 text-sm">
                  We respect your privacy. Unsubscribe at any time.
                </p>
              </form>
            </div>

            <!-- Decorative Elements -->
            <div class="absolute -top-4 -right-4 w-24 h-24 bg-blue-500/10 rounded-full blur-xl"></div>
            <div class="absolute -bottom-4 -left-4 w-32 h-32 bg-emerald-500/5 rounded-full blur-xl"></div>
          </div>
        </div>

        <!-- Social Links -->
        <div class="text-center">
          <div class="mb-6">
            <div class="text-gray-400 mb-4">Follow us on social media</div>
            <div class="flex justify-center gap-6">
              <a
                v-for="social in socialLinks"
                :key="social.label"
                :href="social.url"
                class="group p-3 rounded-xl bg-white/5 border border-white/10 hover:border-white/20 hover:bg-white/10 transition-all"
                :aria-label="social.label"
              >
                <component
                  :is="social.icon"
                  class="w-6 h-6 text-gray-400 group-hover:text-white transition-colors"
                />
              </a>
            </div>
          </div>

          <!-- Trust Indicators -->
          <div class="mt-12 pt-12 border-t border-white/10">
            <div class="text-gray-400 text-sm mb-6">Trusted by traders worldwide</div>
            <div class="flex flex-wrap justify-center gap-8 opacity-70">
              <div class="text-center">
                <div class="text-2xl font-bold text-white">4.9/5</div>
                <div class="text-gray-400 text-sm">Trustpilot</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-white">100+</div>
                <div class="text-gray-400 text-sm">Market Pairs</div>
              </div>
              <div class="text-center">
                <div class="text-2xl font-bold text-white">99.9%</div>
                <div class="text-gray-400 text-sm">Uptime</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<style scoped>
@keyframes float {
  0%, 100% {
    transform: translateY(0);
  }
  50% {
    transform: translateY(-10px);
  }
}

.floating-element {
  animation: float 3s ease-in-out infinite;
}

/* Form animations */
input:focus {
  box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
}
</style>