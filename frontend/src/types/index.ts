// Export all model types
export * from './models';

// Re-export commonly used types for convenience
export type {
  User,
  Wallet,
  Cryptomoney,
  CryptoWalletAsset,
  Transaction,
  CryptoHistory,
  AccountRequest,
  Notification,
  NotificationType,
  ApiResponse,
  PaginatedResponse,
  CreateUserInput,
  UpdateUserInput,
  CreateTransactionInput,
  CreateCryptoInput,
  CreateAccountRequestInput,
  UpdateAccountRequestInput,
  WalletSummary,
  CryptoWithHistory,
} from './models';

// Export utility functions
export {
  NotificationTypes,
  getNotificationIcon,
  getNotificationColor,
  shouldSendEmail,
  isUser,
  isTransaction,
  isCryptomoney,
} from './models';