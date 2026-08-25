// composables/useTransactionStats.ts

import { ArrowDownRight, ArrowUpRight, Coins, DollarSign, Package, TrendingUp } from "lucide-vue-next"
import type { Ref } from "vue"
import { computed } from "vue"
import type { TransactionItem, WalletData } from './useTransactionLogic'

type ColorPalette = {
  primary: string
  success: string
  danger: string
  dark: string
  neutral: string
}

export function useTransactionStats(
  wallet: Ref<WalletData>,
  transactions: Ref<TransactionItem[]>,
  activeTab: Ref<string>,
  filterType: Ref<'all' | 'ACHAT' | 'VENTE'>,
  searchQuery: Ref<string>,
  dateRange: Ref<string>,
  currentPage: Ref<number>,
  itemsPerPage: Ref<number>,
  colors: ColorPalette
) {
  const portfolioStats = computed(() => {
    if (!wallet.value) return null

    const totalValue = Number(wallet.value.totalValue || 0)
    const totalInvestment = Number(wallet.value.totalInvestment || 0)
    const totalPlusValue = Number(wallet.value.totalPlusValue || 0)
    const totalPlusValuePercent = Number(wallet.value.totalPlusValuePercent || 0)
    const balance = Number(wallet.value.balance_eur || 0)
    const assets = wallet.value.assets || []
    const buyCount = wallet.value.buyCount || 0

    return {
      totalValue,
      totalInvestment,
      totalPlusValue,
      totalPlusValuePercent,
      balance,
      assets,
      buyCount,
      assetCount: assets.length,
      totalUnits: Number(wallet.value.totalUnits || 0)
    }
  })

  const statsCards = computed(() => {
    if (!portfolioStats.value) return []

    const stats = portfolioStats.value

    return [
      {
        title: 'Portfolio Value',
        value: formatCompactCurrency(stats.totalValue),
        change: `${stats.totalPlusValuePercent.toFixed(2)}%`,
        positive: stats.totalPlusValuePercent >= 0,
        icon: DollarSign,
        color: colors.primary,
        description: 'Total current value'
      },
      {
        title: 'Available Balance',
        value: formatCurrency(stats.balance),
        change: 'Ready to trade',
        positive: true,
        icon: Coins,
        color: colors.success,
        description: 'Free balance'
      },
      {
        title: 'Total Investment',
        value: formatCompactCurrency(stats.totalInvestment),
        change: `${stats.buyCount} transactions`,
        positive: true,
        icon: TrendingUp,
        color: colors.dark,
        description: 'Total invested'
      },
      {
        title: 'Active Assets',
        value: stats.assetCount,
        change: `${stats.totalUnits.toFixed(2)} units`,
        positive: true,
        icon: Package,
        color: colors.neutral,
        description: 'Diversified holdings'
      }
    ]
  })

  const transactionStats = computed(() => {
    const allTransactions = transactions.value
    const buyTransactions = allTransactions.filter(t => t.originalType === 'ACHAT')
    const sellTransactions = allTransactions.filter(t => t.originalType === 'VENTE')

    const totalBuyAmount = buyTransactions.reduce((sum, t) => sum + t.total, 0)
    const totalSellAmount = sellTransactions.reduce((sum, t) => sum + t.total, 0)
    const totalTransactions = allTransactions.length

    const weekAgo = new Date()
    weekAgo.setDate(weekAgo.getDate() - 7)
    const recentTransactions = allTransactions.filter(t => new Date(t.date) >= weekAgo)

    return {
      totalTransactions,
      buyCount: buyTransactions.length,
      sellCount: sellTransactions.length,
      totalBuyAmount,
      totalSellAmount,
      netFlow: totalBuyAmount - totalSellAmount,
      recentActivity: recentTransactions.length,
      avgTransactionSize: totalTransactions > 0 ? (totalBuyAmount + totalSellAmount) / totalTransactions : 0
    }
  })

  const filteredTransactions = computed(() => {
    let filtered = transactions.value

    if (activeTab.value === 'buy') {
      filtered = filtered.filter(t => t.originalType === 'ACHAT')
    } else if (activeTab.value === 'sell') {
      filtered = filtered.filter(t => t.originalType === 'VENTE')
    }

    if (filterType.value !== 'all' && activeTab.value === 'all') {
      filtered = filtered.filter(t => t.originalType === filterType.value)
    }

    if (searchQuery.value) {
      const query = searchQuery.value.toLowerCase()
      filtered = filtered.filter(t =>
        t.crypto.name.toLowerCase().includes(query) ||
        t.crypto.symbol.toLowerCase().includes(query) ||
        t.id.toLowerCase().includes(query)
      )
    }

    if (dateRange.value !== 'all') {
      const now = new Date()
      const cutoff = new Date()

      switch (dateRange.value) {
        case '7d': cutoff.setDate(now.getDate() - 7); break
        case '30d': cutoff.setDate(now.getDate() - 30); break
        case '90d': cutoff.setDate(now.getDate() - 90); break
        case '1y': cutoff.setFullYear(now.getFullYear() - 1); break
      }

      filtered = filtered.filter(t => new Date(t.date) >= cutoff)
    }

    return filtered
  })

  const paginatedTransactions = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage.value
    const end = start + itemsPerPage.value
    return filteredTransactions.value.slice(start, end)
  })

  const totalPages = computed(() => {
    return Math.ceil(filteredTransactions.value.length / itemsPerPage.value)
  })

  const transactionTypeSummary = computed(() => [
    {
      type: 'ACHAT',
      label: 'Purchases',
      count: transactionStats.value.buyCount,
      total: transactionStats.value.totalBuyAmount,
      icon: ArrowDownRight,
      color: colors.success,
      bgColor: `${colors.success}15`
    },
    {
      type: 'VENTE',
      label: 'Sales',
      count: transactionStats.value.sellCount,
      total: transactionStats.value.totalSellAmount,
      icon: ArrowUpRight,
      color: colors.danger,
      bgColor: `${colors.danger}15`
    }
  ])

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

  return {
    portfolioStats,
    statsCards,
    transactionStats,
    filteredTransactions,
    paginatedTransactions,
    totalPages,
    transactionTypeSummary
  }
}