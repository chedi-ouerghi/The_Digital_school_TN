<template>
  <div class="min-h-screen flex flex-col items-center justify-center bg-[#071B2C] text-white px-6">
    <!-- Illustration / Chart Placeholder -->
    <div class="w-full max-w-md mb-8">
      <canvas id="notFoundChart" class="w-full h-64"></canvas>
    </div>

    <h1 class="text-6xl font-bold text-[#FF5964] mb-4">404</h1>
    <h2 class="text-2xl md:text-3xl font-semibold mb-2">Page Not Found</h2>
    <p class="text-white/70 text-center mb-6">
      Oops! The page you are looking for doesn't exist or has been moved.
    </p>
    <router-link to="/dashboard" class="bg-[#01FF19] hover:bg-[#00e617] text-[#071B2C] font-semibold px-6 py-3 rounded-lg shadow-lg transition-all duration-300">
      Go Back Home
    </router-link>
  </div>
</template>

<script setup lang="ts">
import { onMounted } from 'vue'
import { Chart, registerables } from 'chart.js'

Chart.register(...registerables)

onMounted(() => {
  const ctx = document.getElementById('notFoundChart') as HTMLCanvasElement
  if (ctx) {
    new Chart(ctx, {
      type: 'bar',
      data: {
        labels: ['BTC', 'ETH', 'XRP', 'ADA', 'SOL'],
        datasets: [{
          label: 'Sample Crypto Chart',
          data: [12, 19, 3, 5, 2],
          backgroundColor: [
            '#FF6384',
            '#36A2EB',
            '#FFCE56',
            '#4BC0C0',
            '#9966FF'
          ],
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { enabled: true }
        },
        scales: {
          x: { grid: { color: 'rgba(255,255,255,0.1)' } },
          y: { grid: { color: 'rgba(255,255,255,0.1)' } }
        }
      }
    })
  }
})
</script>

<style scoped>
/* optional: add subtle animation */
#notFoundChart {
  opacity: 0;
  animation: fadeIn 1s forwards;
}

@keyframes fadeIn {
  to { opacity: 1; }
}
</style>
