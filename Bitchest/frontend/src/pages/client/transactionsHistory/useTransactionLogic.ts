// composables/useTransactionLogic.ts
import {
  ArrowDownRight,
  ArrowUpRight
} from 'lucide-vue-next'
import { type Ref } from 'vue'
import api from '../../../services/api'
import type { Cryptomoney, Transaction } from '../../../types/models'

export const colors = {
  primary: '#35A7FF',
  success: '#01FF19',
  danger: '#FF5964',
  dark: '#38618C',
  neutral: '#64748B'
}

export interface WalletEntry {
  symbol?: string
  quantity?: number
  cryptomoney?: Cryptomoney
}

export interface WalletData {
  cryptoWalletAssets?: WalletEntry[]
  assets?: WalletEntry[]
  totalValue?: number
  totalInvestment?: number
  totalPlusValue?: number
  totalPlusValuePercent?: number
  balance_eur?: number
  buyCount?: number
  totalUnits?: number
}

export interface TransactionItem {
  id: string
  crypto_id?: string | number
  originalType: string
  type: string
  typeLabel: string
  quantity: number
  price: number
  unitPrice: number
  total: number
  date: string
  crypto: {
    id?: string | number
    name: string
    symbol: string
    image_url: string
    current_price: number
  }
  average_price?: number
}

type RawTransaction = Transaction & {
  crypto_id?: string
  unit_price_eur?: number
  crypto_name?: string
  crypto_symbol?: string
  crypto_image_url?: string
  date?: string
}

export function useTransactionLogic(
  wallet: Ref<WalletData>,
  transactions: Ref<TransactionItem[]>,
  loading: Ref<boolean>,
  error: Ref<string | null>,
  isRefreshing: Ref<boolean>,
  filterType: Ref<'all' | 'ACHAT' | 'VENTE'>,
  dateRange: Ref<string>,
  activeTab: Ref<string>,
  searchQuery: Ref<string>,
  currentPage: Ref<number>,
  showSellDialog: Ref<boolean>,
  selectedAsset: Ref<TransactionItem | null>,
  sellQuantity: Ref<string>,
  sellError: Ref<string | null>,
  sellSuccess: Ref<string | null>,
  isSelling: Ref<boolean>
) {
  // ============================================================================
  // UTILITY FUNCTIONS
  // ============================================================================
  const formatCurrency = (value: unknown) => {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '€0.00'
    return n.toLocaleString('en-US', {
      style: 'currency',
      currency: 'EUR',
      minimumFractionDigits: 2,
      maximumFractionDigits: 2
    })
  }

  const formatCompactCurrency = (value: unknown) => {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '€0'

    if (n >= 1000000) return `€${(n / 1000000).toFixed(2)}M`
    if (n >= 1000) return `€${(n / 1000).toFixed(1)}K`
    return formatCurrency(n)
  }

  const formatNumber = (value: unknown, decimals = 8) => {
    const n = Number(value ?? 0)
    if (!isFinite(n) || isNaN(n)) return '0'
    const formatted = n.toFixed(decimals)
    return formatted.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '')
  }

  const formatRelativeDate = (date: string | Date) => {
    if (!date) return 'Unknown'

    try {
      const now = new Date()
      const transactionDate = new Date(date)

      // Handle invalid dates
      if (isNaN(transactionDate.getTime())) return 'Invalid date'

      const diff = now.getTime() - transactionDate.getTime()

      const seconds = Math.floor(diff / 1000)
      const minutes = Math.floor(seconds / 60)
      const hours = Math.floor(minutes / 60)
      const days = Math.floor(hours / 24)
      const months = Math.floor(days / 30)
      const years = Math.floor(months / 12)

      if (years > 0) return `${years} year${years > 1 ? 's' : ''} ago`
      if (months > 0) return `${months} month${months > 1 ? 's' : ''} ago`
      if (days > 0) return `${days}d ago`
      if (hours > 0) return `${hours}h ago`
      if (minutes > 0) return `${minutes}m ago`
      if (seconds > 0) return `${seconds}s ago`
      return 'Just now'
    } catch (e) {
      console.error('Error formatting date:', date, e)
      return 'Invalid date'
    }
  }

  const formatFullDate = (date: string) => {
    return new Date(date).toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'long',
      day: 'numeric',
      hour: '2-digit',
      minute: '2-digit'
    })
  }

  // ============================================================================
  // API FUNCTIONS
  // ============================================================================
  const fetchWallet = async () => {
    try {
      const response = await api.wallet.list()
      wallet.value = response as unknown as WalletData
      if (!wallet.value.cryptoWalletAssets) {
        wallet.value.cryptoWalletAssets = []
      }
    } catch (e) {
      console.error('Error fetching wallet:', e)
      error.value = e instanceof Error ? e.message : 'Error loading wallet data'
    }
  }

  const loadTransactions = async () => {
    loading.value = true
    error.value = null
    try {

      const response = await api.wallet.getTransactionsHistory()
      const transactionsData = response?.transactions || []

      if (!transactionsData || transactionsData.length === 0) {
        console.warn('⚠️ No transactions received from API')
        transactions.value = []
        return
      }

      transactions.value = transactionsData.map((tx: RawTransaction) => ({
        id: tx.id,
        crypto_id: tx.crypto_id,
        originalType: tx.type,
        type: tx.type === 'ACHAT' ? 'buy' : 'sell',
        typeLabel: tx.type === 'ACHAT' ? 'Purchase' : 'Sale',
        quantity: Number(tx.quantity || 0),
        price: Number(tx.price || 0),
        unitPrice: Number(tx.unit_price_eur || tx.price || 0),
        total: Number(tx.total_eur || 0),
        date: tx.created_at || tx.date || '',
        crypto: {
          id: tx.crypto_id,
          name: tx.crypto_name || 'Unknown',
          symbol: tx.crypto_symbol || 'N/A',
          image_url: tx.crypto_image_url || '',
          current_price: 0
        }
      })).sort((a, b) => new Date(b.date).getTime() - new Date(a.date).getTime())

      if (wallet.value?.cryptoWalletAssets) {
        transactions.value.forEach(tx => {
          const asset = wallet.value?.cryptoWalletAssets?.find((a) => a.cryptomoney?.symbol === tx.crypto.symbol)
          if (asset) {
            tx.crypto.current_price = asset.cryptomoney?.price_eur || 0
            tx.crypto.image_url = asset.cryptomoney?.image || tx.crypto.image_url
          }
        })
      }

    } catch (e) {
      console.error('❌ Error loading transactions:', e)
      error.value = e instanceof Error ? e.message : 'Error loading transactions. Please try again.'
    } finally {
      loading.value = false
      isRefreshing.value = false
    }
  }

  const refreshData = async () => {
    isRefreshing.value = true
    try {
      await Promise.all([fetchWallet(), loadTransactions()])
    } finally {
      isRefreshing.value = false
    }
  }

  // ============================================================================
  // SELL DIALOG FUNCTIONS
  // ============================================================================
  const validateSellQuantity = () => {
    if (!selectedAsset.value) return
    const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)
    const quantity = parseFloat(sellQuantity.value || '0')

    if (quantity > available) {
      sellQuantity.value = available.toFixed(8)
    }

    if (quantity < 0.00000001 && quantity > 0) {
      sellQuantity.value = '0.00000001'
    }
  }

  const setSellPercentage = (percent: number) => {
    if (!selectedAsset.value) return
    const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)
    sellQuantity.value = (available * (percent / 100)).toFixed(8)
    validateSellQuantity()
  }

  const calculateSellAmount = () => {
    if (!selectedAsset.value || !sellQuantity.value) return 0
    const quantity = parseFloat(sellQuantity.value)
    const price = selectedAsset.value.crypto.current_price || selectedAsset.value.unitPrice || 0
    return quantity * price
  }

  const calculateProfitLoss = () => {
    if (!selectedAsset.value?.unitPrice || !sellQuantity.value) return 0
    const sellAmount = calculateSellAmount()
    const cost = parseFloat(sellQuantity.value) * selectedAsset.value.unitPrice
    return sellAmount - cost
  }

  const calculateProfitLossPercentage = () => {
    const profitLoss = calculateProfitLoss()
    const cost = parseFloat(sellQuantity.value || '0') * (selectedAsset.value?.unitPrice || 0)
    if (cost === 0) return 0
    return ((profitLoss / cost) * 100).toFixed(2)
  }

  const getAvailableQuantity = (symbol: string) => {
    if (!wallet.value?.assets) return 0
    const asset = wallet.value.assets.find((a) => a.symbol === symbol)
    return asset ? Number(asset.quantity || 0) : 0
  }

  const canSell = (tx: TransactionItem) => {
    if (tx.originalType !== 'ACHAT') return false
    const available = getAvailableQuantity(tx.crypto.symbol)
    return available > 0 && available >= 0.00000001
  }

  const openSellDialog = (tx: TransactionItem) => {
    selectedAsset.value = tx
    selectedAsset.value.average_price = tx.unitPrice
    sellQuantity.value = getAvailableQuantity(tx.crypto.symbol).toFixed(8)
    sellError.value = null
    sellSuccess.value = null
    showSellDialog.value = true
  }

  const closeSellDialog = () => {
    showSellDialog.value = false
    selectedAsset.value = null
    sellQuantity.value = ''
    sellError.value = null
    sellSuccess.value = null
    isSelling.value = false
  }

  const confirmSell = async () => {
    if (!selectedAsset.value) return

    const qty = parseFloat(sellQuantity.value)
    const available = getAvailableQuantity(selectedAsset.value.crypto.symbol)

    try {
      const profile = await api.auth.profile()
      if (!profile.password_changed_at) {
        sellError.value = 'You must change your password before making sales. Please go to your profile settings.'
        return
      }
    } catch (err) {
      console.error('Error checking password status:', err)
      sellError.value = 'Error verifying your account status'
      return
    }

    if (!qty || qty <= 0) {
      sellError.value = 'Please enter a valid quantity'
      return
    }
    if (qty > available) {
      sellError.value = `Insufficient quantity. Available: ${formatNumber(available, 8)} ${selectedAsset.value.crypto.symbol.toUpperCase()}`
      return
    }
    if (qty < 0.00000001) {
      sellError.value = 'Minimum sell amount is 0.00000001'
      return
    }

    isSelling.value = true
    sellError.value = null

    try {

      await api.wallet.transact({
        symbol: selectedAsset.value.crypto.symbol,
        type: 'VENTE',
        quantity: qty,
      })

      sellSuccess.value = '✅ Sale completed successfully! Updating your portfolio...'

      // Wait a bit then refresh data and close dialog
      await new Promise(resolve => setTimeout(resolve, 800))

      // Refresh both wallet and transactions data
      await Promise.all([fetchWallet(), loadTransactions()])

      sellSuccess.value = '✅ Sale completed and portfolio updated!'

      // Close the dialog after final success message
      await new Promise(resolve => setTimeout(resolve, 800))
      closeSellDialog()

    } catch (e) {
      console.error('❌ Error during sale:', e)
      sellError.value = e instanceof Error ? e.message : 'Error during sale. Please try again.'
    } finally {
      isSelling.value = false
    }
  }

  // ============================================================================
  // HELPER FUNCTIONS
  // ============================================================================
  const getTransactionIcon = (type: string) => {
    return type === 'ACHAT' ? ArrowDownRight : ArrowUpRight
  }

  const getTransactionColor = (type: string) => {
    return type === 'ACHAT' ? colors.success : colors.danger
  }

  const getTransactionBgColor = (type: string) => {
    return type === 'ACHAT' ? `${colors.success}15` : `${colors.danger}15`
  }

  const resetFilters = () => {
    searchQuery.value = ''
    filterType.value = 'all'
    dateRange.value = 'all'
    activeTab.value = 'all'
    currentPage.value = 1
  }

  const exportTransactions = () => {
    try {
      // Get filtered transactions for export
      let filtered = transactions.value

      if (activeTab.value === 'buy') {
        filtered = filtered.filter(t => t.originalType === 'ACHAT')
      } else if (activeTab.value === 'sell') {
        filtered = filtered.filter(t => t.originalType === 'VENTE')
      }

      const data = filtered.map(tx => ({
        'Transaction ID': tx.id,
        'Type': tx.typeLabel,
        'Crypto': tx.crypto.name,
        'Symbol': tx.crypto.symbol,
        'Quantity': tx.quantity,
        'Unit Price': formatCurrency(tx.unitPrice),
        'Total Amount': formatCurrency(tx.total),
        'Date': formatFullDate(tx.date),
        'Status': 'Completed'
      }))

      if (data.length === 0) {
        alert('No transactions to export')
        return
      }

      const csv = [
        Object.keys(data[0]).join(','),
        ...data.map(row => Object.values(row).join(','))
      ].join('\n')

      const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      const link = document.createElement('a')
      const url = URL.createObjectURL(blob)

      link.setAttribute('href', url)
      link.setAttribute('download', `transactions_${new Date().toISOString().split('T')[0]}.csv`)
      link.style.visibility = 'hidden'

      document.body.appendChild(link)
      link.click()
      document.body.removeChild(link)

    } catch (error) {
      console.error('Export error:', error)
      alert('Error exporting transactions')
    }
  }

  const navigateToPage = (page: number) => {
    const totalPagesValue = Math.ceil(transactions.value.length / 10)
    if (page >= 1 && page <= totalPagesValue) {
      currentPage.value = page
      const transactionsList = document.querySelector('.transactions-list')
      if (transactionsList) {
        transactionsList.scrollIntoView({ behavior: 'smooth' })
      }
    }
  }

  return {
    colors,
    formatCurrency,
    formatCompactCurrency,
    formatNumber,
    formatRelativeDate,
    formatFullDate,
    fetchWallet,
    loadTransactions,
    refreshData,
    validateSellQuantity,
    setSellPercentage,
    calculateSellAmount,
    calculateProfitLoss,
    calculateProfitLossPercentage,
    getAvailableQuantity,
    canSell,
    openSellDialog,
    closeSellDialog,
    confirmSell,
    getTransactionIcon,
    getTransactionColor,
    getTransactionBgColor,
    resetFilters,
    exportTransactions,
    navigateToPage
  }
}

