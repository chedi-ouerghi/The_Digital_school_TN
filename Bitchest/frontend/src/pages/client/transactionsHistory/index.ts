// Re-export all utilities from useTransactionLogic
export { colors, useTransactionLogic } from './useTransactionLogic'
export { useTransactionStats } from './useTransactionStats'

// Helper functions for formatting and styling
export function formatCurrency(value: any): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '€0.00'
  return n.toLocaleString('en-US', { 
    style: 'currency', 
    currency: 'EUR', 
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  })
}

export function formatNumber(value: any, decimals = 8): string {
  const n = Number(value ?? 0)
  if (!isFinite(n) || isNaN(n)) return '0'
  const formatted = n.toFixed(decimals)
  return formatted.replace(/(\.\d*?)0+$/, '$1').replace(/\.$/, '')
}

export function formatRelativeDate(date: string | Date): string {
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

export function formatFullDate(date: string): string {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

export function getTransactionColor(type: string): string {
  return type === 'ACHAT' ? '#01FF19' : '#FF5964'
}

export function getTransactionBgColor(type: string): string {
  return type === 'ACHAT' ? '#01FF1915' : '#FF596415'
}
