import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js'
import { computed, onMounted, ref } from 'vue'
import api from '../../../../services/api'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

interface AdminTopCrypto {
  id: number
  name: string
  symbol: string
  image?: string
  image_url?: string
  total_qty?: number
  total_volume?: number
}

interface AdminRecentTransaction {
  id: string
  quantity?: number
  total_eur?: number
  type?: string
  crypto_name?: string
  crypto_image?: string
  cryptomoney?: {
    name?: string
    image?: string
  }
}

interface AdminStatsData {
  total_users?: number
  total_volume?: number
  top_cryptos?: AdminTopCrypto[]
  recent_transactions?: AdminRecentTransaction[]
}

export function useAdminStats() {
  const stats = ref<AdminStatsData>({})
  const loading = ref(false)
  const error = ref<string | null>(null)
  const cryptoDetails = ref<Map<number, AdminTopCrypto>>(new Map())
  const recentTransactions = ref<AdminRecentTransaction[]>([])

  // Fonctions utilitaires
  function formatCurrency(value: unknown): string {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '0,00 €'
    return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
  }

  function formatNumber(value: unknown, decimals = 2): string {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '0'
    return n.toFixed(decimals)
  }

  async function fetchStats() {
    loading.value = true
    error.value = null
    try {
      const data = await api.admin.stats.global()
      stats.value = (data?.data || {}) as AdminStatsData

      // Mapper les cryptos dans cryptoDetails pour un accès facile
      if (stats.value.top_cryptos?.length) {
        stats.value.top_cryptos.forEach((crypto) => {
          cryptoDetails.value.set(Number(crypto.id), {
            id: Number(crypto.id),
            name: crypto.name,
            symbol: crypto.symbol,
            image_url: getImageUrl(crypto.image || ''),
            total_qty: crypto.total_qty,
            total_volume: crypto.total_volume
          })
        })
      }

      // Mapper et construire les URLs des images pour top_cryptos
      if (stats.value.top_cryptos?.length) {
        stats.value.top_cryptos = stats.value.top_cryptos.map((crypto) => ({
          ...crypto,
          image: getImageUrl(crypto.image || '')
        }))
      }

      // Mapper les transactions récentes avec les images
      if (stats.value.recent_transactions?.length) {
        stats.value.recent_transactions
          .slice(0, 10)
          .forEach((tx) => {
            recentTransactions.value.push({
              ...tx,
              cryptomoney: {
                name: tx.crypto_name,
                image: getImageUrl(tx.crypto_image || '')
              }
            })
          })
      }

    } catch (err) {
      error.value = err instanceof Error ? err.message : String(err)
      console.error('Erreur chargement stats:', err)
    } finally {
      loading.value = false
    }
  }

  function getImageUrl(imagePath: string): string {
    if (!imagePath) return ''
    if (imagePath.startsWith('http')) return imagePath
    // Construire l'URL complète depuis l'API
    const apiBaseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
    return `${apiBaseUrl}/storage/${imagePath}`
  }

  // Statistiques principales
  const cardStats = computed(() => ({
    totalClients: stats.value?.total_users || 0,
    totalVolume: stats.value?.total_volume || 0,
    totalRevenue: stats.value?.total_volume ? (Number(stats.value.total_volume) * 0.02) : 0, // 2% commission
    totalCryptos: stats.value?.top_cryptos?.length || 0,
  }))

  // Données pour le graphique en barres
  const barChartData = computed(() => {
    const topCryptos = stats.value?.top_cryptos || []
    
    return {
      labels: topCryptos.map((c: AdminTopCrypto) => (c.symbol || 'UNKNOWN').toUpperCase()),
      datasets: [{
        label: 'Quantité tradée',
        data: topCryptos.map((c: AdminTopCrypto) => Number(c.total_qty || 0)),
        backgroundColor: [
          'rgba(1, 255, 25, 0.8)',
          'rgba(53, 167, 255, 0.8)',
          'rgba(56, 97, 140, 0.8)',
          'rgba(255, 89, 100, 0.8)',
          'rgba(1, 255, 25, 0.5)',
        ],
        borderColor: [
          '#01FF19',
          '#35A7FF',
          '#38618C',
          '#FF5964',
          '#01FF19',
        ],
        borderWidth: 2
      }]
    }
  })

  const barChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { display: false },
      tooltip: {
        backgroundColor: '#FFFFFF',
        titleColor: '#38618C',
        bodyColor: '#38618C',
        borderColor: '#38618C',
        borderWidth: 1,
      }
    },
    scales: {
      x: { 
        ticks: { color: '#38618C', font: { weight: 'bold' } },
        grid: { display: false }
      },
      y: { 
        ticks: { color: '#38618C' },
        grid: { color: '#E5E7EB' }
      }
    }
  }

  // Données pour le graphique circulaire
  const doughnutChartData = computed(() => {
    const topCryptos = stats.value?.top_cryptos || []
    
    return {
      labels: topCryptos.map((c: AdminTopCrypto) => (c.symbol || 'UNKNOWN').toUpperCase()),
      datasets: [{
        data: topCryptos.map((c: AdminTopCrypto) => Number(c.total_volume || 0)),
        backgroundColor: [
          'rgba(1, 255, 25, 0.8)',
          'rgba(53, 167, 255, 0.8)',
          'rgba(56, 97, 140, 0.8)',
          'rgba(255, 89, 100, 0.8)',
          'rgba(1, 255, 25, 0.5)',
        ],
        borderColor: [
          '#01FF19',
          '#35A7FF',
          '#38618C',
          '#FF5964',
          '#01FF19',
        ],
        borderWidth: 2
      }]
    }
  })

  const doughnutChartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        position: 'right',
        labels: {
          color: '#38618C',
          font: { size: 12, weight: 'bold' },
          padding: 15,
        }
      },
      tooltip: {
        backgroundColor: '#FFFFFF',
        titleColor: '#38618C',
        bodyColor: '#38618C',
        borderColor: '#38618C',
        borderWidth: 1,
      }
    }
  }

  function getRankColor(index: number): string {
    switch (index) {
      case 0:
        return 'bg-yellow-400'
      case 1:
        return 'bg-gray-400'
      case 2:
        return 'bg-yellow-700'
      default:
        return 'bg-gray-300 text-gray-700'
    }
  }

  onMounted(() => {
    fetchStats()
  })

  return {
    stats,
    loading,
    error,
    cryptoDetails,
    recentTransactions,
    cardStats,
    barChartData,
    barChartOptions,
    doughnutChartData,
    doughnutChartOptions,
    formatCurrency,
    formatNumber,
    getRankColor,
    fetchStats
  }
}