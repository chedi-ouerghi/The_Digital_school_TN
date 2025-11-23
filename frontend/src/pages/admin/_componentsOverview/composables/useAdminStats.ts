import { ArcElement, BarElement, CategoryScale, Chart as ChartJS, Legend, LinearScale, Title, Tooltip } from 'chart.js'
import { computed, onMounted, ref } from 'vue'
import api from '../../../../services/api'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend, ArcElement)

export function useAdminStats() {
  const stats = ref<any>(null)
  const loading = ref(false)
  const error = ref<string | null>(null)
  const cryptoDetails = ref<Map<number, any>>(new Map())
  const recentTransactions = ref<any[]>([])

  // Fonctions utilitaires
  function formatCurrency(value: any): string {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '0,00 €'
    return n.toLocaleString('fr-FR', { style: 'currency', currency: 'EUR' })
  }

  function formatNumber(value: any, decimals = 2): string {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '0'
    return n.toFixed(decimals)
  }

  async function fetchCryptoDetails(cryptoIds: number[]) {
    try {
      const promises = cryptoIds.map(id => api.crypto.show(id))
      const details = await Promise.all(promises)
      details.forEach((crypto, index) => {
        if (crypto) {
          cryptoDetails.value.set(cryptoIds[index], crypto)
        }
      })
    } catch (err) {
      console.error('Erreur lors du chargement des détails des cryptos:', err)
    }
  }

  async function fetchRecentTransactions() {
    try {
      const response = await api.admin.transactions.list({ limit: 5 })
      recentTransactions.value = (response?.data || []).slice(0, 5)
    } catch (err) {
      console.error('Erreur lors du chargement des transactions:', err)
    }
  }

  async function fetchStats() {
    loading.value = true
    error.value = null
    try {
      const data = await api.admin.stats.global()
      stats.value = data

      // Récupérer les détails des cryptos
      if (data.top_traded?.length) {
        const cryptoIds = data.top_traded.map((c: any) => c.cryptomoney_id)
        await fetchCryptoDetails(cryptoIds)
      }

      // Récupérer les transactions récentes
      await fetchRecentTransactions()
    } catch (err: any) {
      error.value = err.message || String(err)
      console.error('Erreur chargement stats:', err)
    } finally {
      loading.value = false
    }
  }

  // Statistiques principales
  const cardStats = computed(() => ({
    totalClients: stats.value?.total_clients || 0,
    totalVolume: stats.value?.total_transaction_volume_eur || 0,
    totalRevenue: stats.value?.estimated_revenue_eur || 0,
    totalCryptos: stats.value?.top_traded?.length || 0,
  }))

  // Données pour le graphique en barres
  const barChartData = computed(() => {
    const topTraded = stats.value?.top_traded || []
    
    return {
      labels: topTraded.map((c: any) => {
        const crypto = cryptoDetails.value.get(c.cryptomoney_id)
        return (crypto?.symbol || c.symbole || 'UNKNOWN').toUpperCase()
      }),
      datasets: [{
        label: 'Quantité tradée',
        data: topTraded.map((c: any) => Number(c.total_quantity || 0)),
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
    const topTraded = stats.value?.top_traded || []
    
    return {
      labels: topTraded.map((c: any) => {
        const crypto = cryptoDetails.value.get(c.cryptomoney_id)
        return (crypto?.symbol || c.symbole || 'UNKNOWN').toUpperCase()
      }),
      datasets: [{
        data: topTraded.map((c: any) => Number(c.total_quantity || 0)),
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
    fetchStats,
    fetchRecentTransactions
  }
}