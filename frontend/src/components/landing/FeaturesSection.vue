<script setup lang="ts">
import { onMounted, onUnmounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { ArrowRight, BarChart3, Shield, Zap } from 'lucide-vue-next';
import * as THREE from 'three'

const features = [
  {
    icon: Shield,
    title: 'Secure Transactions',
    description: 'Bank-grade security for all your crypto investments'
  },
  {
    icon: Zap,
    title: 'Lightning Fast',
    description: 'Execute trades in milliseconds with our advanced platform'
  },
  {
    icon: BarChart3,
    title: 'Advanced Analytics',
    description: 'Real-time insights and powerful trading tools'
  }
];

// Three.js animation
let scene: any;
let camera: any;
let renderer: any;
let particles: any;
let animationFrameId: number;

const threeContainer = ref<HTMLElement>();

const initThree = () => {
  if (!threeContainer.value) return;

  // Create scene
  scene = new THREE.Scene();
  
  // Create camera
  camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
  camera.position.z = 30;
  
  // Create renderer
  renderer = new THREE.WebGLRenderer({ alpha: true, antialias: true });
  renderer.setSize(window.innerWidth, window.innerHeight);
  renderer.setClearColor(0x000000, 0);
  threeContainer.value.appendChild(renderer.domElement);
  
  // Create particles
  createParticles();
  
  // Handle resize
  window.addEventListener('resize', onWindowResize);
  
  // Start animation
  animate();
};

const createParticles = () => {
  const particlesGeometry = new THREE.BufferGeometry();
  const particlesCount = 1500;
  
  const posArray = new Float32Array(particlesCount * 3);
  const colorArray = new Float32Array(particlesCount * 3);
  
  for(let i = 0; i < particlesCount * 3; i++) {
    // Random position
    posArray[i] = (Math.random() - 0.5) * 100;
    
    // Colors in blue/green palette
    if(i % 3 === 0) {
      colorArray[i] = Math.random() * 0.2 + 0.2; // Blue
    } else if(i % 3 === 1) {
      colorArray[i] = Math.random() * 0.3 + 0.5; // Green/blue
    } else {
      colorArray[i] = Math.random() * 0.2 + 0.8; // Green
    }
  }
  
  particlesGeometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3));
  particlesGeometry.setAttribute('color', new THREE.BufferAttribute(colorArray, 3));
  
  const particlesMaterial = new THREE.PointsMaterial({
    size: 0.1,
    vertexColors: true,
    transparent: true,
    opacity: 0.8
  });
  
  particles = new THREE.Points(particlesGeometry, particlesMaterial);
  scene.add(particles);
};

const animate = () => {
  animationFrameId = requestAnimationFrame(() => animate());
  
  // Particle animation
  if(particles) {
    particles.rotation.y += 0.001;
    particles.rotation.x += 0.0005;
    
    // Subtle pulsing animation
    const time = Date.now() * 0.001;
    particles.scale.x = 1 + Math.sin(time) * 0.05;
    particles.scale.y = 1 + Math.cos(time * 0.8) * 0.05;
  }
  
  renderer.render(scene, camera);
};

const onWindowResize = () => {
  if (!camera || !renderer) return;
  
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
};

const cleanupThree = () => {
  if (animationFrameId) {
    cancelAnimationFrame(animationFrameId);
  }
  window.removeEventListener('resize', onWindowResize);
  
  if (renderer && threeContainer.value) {
    threeContainer.value.removeChild(renderer.domElement);
    renderer.dispose();
  }
};

// Stats animation
const animateStats = () => {
  const statValues = document.querySelectorAll('.stat-value');
  
  statValues.forEach(stat => {
    const raw = stat.textContent || '0'
    const hasPercent = raw.includes('%')
    const target = parseInt(raw.replace(/[^0-9]/g, '') || '0');
    let current = 0;
    const increment = Math.max(1, Math.floor(target / 50));
    const timer = setInterval(() => {
      current += increment;
      if(current >= target) {
        current = target;
        clearInterval(timer);
      }
      stat.textContent = hasPercent ? `${Math.floor(current)}%` : `${Math.floor(current)}`
    }, 30);
  });
};

onMounted(() => {
  initThree();
  setTimeout(animateStats, 1500);
});

onUnmounted(() => {
  cleanupThree();
});
</script>

<template>
    <!-- Features Section -->
    <section class="relative py-24 lg:py-32 overflow-hidden bg-gradient-to-br from-[#071B2C] via-[#0B2E4E] to-[#071B2C] text-white font-[Celias]" id="features">
      <!-- Éclat d'arrière-plan -->
      <div class="absolute inset-0">
        <div class="absolute top-20 right-20 w-96 h-96 bg-[#35A7FF]/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 left-20 w-96 h-96 bg-[#01FF19]/15 rounded-full blur-[120px]"></div>
      </div>

      <div class="relative z-10 container mx-auto px-6 lg:px-16">
        <div class="text-center mb-16 animate-fade-in">
          <div class="inline-flex items-center gap-2 px-5 py-2 rounded-full bg-[#35A7FF]/10 border border-[#35A7FF]/30 text-[#35A7FF] font-medium text-sm tracking-wide mb-6">
            <span class="text-lg">✨</span> Powerful Features
          </div>
          <h2 class="text-4xl md:text-5xl xl:text-6xl font-extrabold leading-tight mb-6">
            Why Choose <span class="text-[#01FF19] drop-shadow-[0_0_10px_#01FF19]">BitChest</span>?
          </h2>
          <p class="text-white/80 text-lg max-w-2xl mx-auto">
            Experience the future of crypto trading with cutting-edge technology and unmatched security
          </p>
        </div>

        <div class="grid md:grid-cols-3 gap-8 max-w-6xl mx-auto">
          <div 
            v-for="(feature, index) in features" 
            :key="feature.title"
            class="relative p-8 rounded-2xl bg-white/5 backdrop-blur-sm border border-white/10 hover:border-[#01FF19]/40 hover:bg-white/10 transition-all duration-300 group animate-fade-in"
            :style="{ animationDelay: `${0.2 + index * 0.1}s` }"
          >
            <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-[#01FF19]/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
            <div class="relative z-10">
              <component 
                :is="feature.icon"
                class="w-14 h-14 text-[#01FF19] mb-6 group-hover:scale-110 group-hover:drop-shadow-[0_0_15px_#01FF19] transition-all duration-300"
              />
              <h3 class="text-xl md:text-2xl font-semibold text-white mb-4">
                {{ feature.title }}
              </h3>
              <p class="text-white/70 text-base leading-relaxed">{{ feature.description }}</p>
            </div>
          </div>
        </div>

        <!-- Three.js canvas container -->
        <div ref="threeContainer" class="absolute inset-0 pointer-events-none" aria-hidden="true"></div>
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
</style>