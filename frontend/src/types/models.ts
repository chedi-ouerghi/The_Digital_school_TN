// Base type for all models with string IDs
export interface BaseModel {
  id: string;
  created_at?: string;
  updated_at?: string;
}

// User model
export interface User extends BaseModel {
  name: string;
  email: string;
  email_verified_at?: string;
  role: 'admin' | 'client';
  remember_token?: string;
  balance?: number;
  wallets?: Wallet[];
  wallet?: Wallet;
  notifications?: Notification[];
  accountRequest?: AccountRequest;
}

// Wallet model
export interface Wallet extends BaseModel {
  user_id: string;
  balance_eur: number;
  user?: User;
  cryptoWalletAssets?: CryptoWalletAsset[];
  transactions?: Transaction[];
}

// Cryptomoney model
export interface Cryptomoney extends BaseModel {
  name: string;
  symbol: string;
  coingecko_id: string;
  image?: string;
  category?: string;
  website?: string;
  price_eur: number;
  market_cap: number;
  volume_24h: number;
  change_24h_pct: number;
  updated_at_api?: string;
  price: number; // alias for price_eur
  change_24h: number; // alias for change_24h_pct
  cryptoWalletAssets?: CryptoWalletAsset[];
  histories?: CryptoHistory[];
}

// CryptoWalletAsset model (Pivot table)
export interface CryptoWalletAsset extends BaseModel {
  wallet_id: string;
  cryptomoney_id: string;
  quantity: number;
  average_buy_price: number;
  wallet?: Wallet;
  cryptomoney?: Cryptomoney;
  transactions?: Transaction[];
}

// Transaction model
export interface Transaction extends BaseModel {
  crypto_wallet_asset_id: string;
  cryptomoney_id: string;
  type: 'buy' | 'sell';
  quantity: number;
  price: number;
  total_eur: number;
  admin_operation: boolean;
  cancelled_at?: string;
  cancel_reason?: string;
  cryptoWalletAsset?: CryptoWalletAsset;
  cryptomoney?: Cryptomoney;
  wallet?: Wallet; // computed property
}

// CryptoHistory model
export interface CryptoHistory extends BaseModel {
  cryptomoney_id: string;
  price: number;
  market_cap: number;
  volume: number;
  recorded_at: string;
  cryptomoney?: Cryptomoney;
}

// AccountRequest model
export interface AccountRequest extends BaseModel {
  name: string;
  email: string;
  status: 'pending' | 'approved' | 'rejected';
  token?: string;
  processed_at?: string;
  processed_by?: string;
  user_id?: string;
  rejection_reason?: string;
  user?: User;
  processedBy?: User;
}

// Notification model
export interface Notification extends BaseModel {
  user_id?: string;
  title: string;
  message: string;
  type: NotificationType;
  is_read: boolean;
  user?: User;
}

// Notification types
export const NotificationTypes = {
  ACCOUNT_REQUEST: 'account_request',
  TRANSACTION: 'transaction',
  PRICE_UPDATE: 'price_update',
  ADMIN_ACTION: 'admin_action',
} as const;

export type NotificationType = typeof NotificationTypes[keyof typeof NotificationTypes];

// Helper functions for notifications
export const getNotificationIcon = (type: NotificationType): string => {
  switch (type) {
    case NotificationTypes.ACCOUNT_REQUEST:
      return 'user-plus';
    case NotificationTypes.TRANSACTION:
      return 'swap-horizontal';
    case NotificationTypes.PRICE_UPDATE:
      return 'trending-up';
    case NotificationTypes.ADMIN_ACTION:
      return 'shield-check';
    default:
      return 'bell';
  }
};

export const getNotificationColor = (type: NotificationType): string => {
  switch (type) {
    case NotificationTypes.ACCOUNT_REQUEST:
      return 'yellow';
    case NotificationTypes.TRANSACTION:
      return 'blue';
    case NotificationTypes.PRICE_UPDATE:
      return 'green';
    case NotificationTypes.ADMIN_ACTION:
      return 'red';
    default:
      return 'gray';
  }
};

export const shouldSendEmail = (type: NotificationType): boolean => {
  return [NotificationTypes.ACCOUNT_REQUEST, NotificationTypes.ADMIN_ACTION].includes(type);
};

// API Response types
export interface ApiResponse<T> {
  data: T;
  message?: string;
  success: boolean;
}

export interface PaginatedResponse<T> {
  data: T[];
  current_page: number;
  total_pages?: number;
  total_items?: number;
  total?: number;
  per_page: number;
  next_page_url?: string | null;
  prev_page_url?: string | null;
  path?: string;
  to?: number;
}

// Form/Input types for frontend forms
export interface CreateUserInput {
  name: string;
  email: string;
  password: string;
  role?: 'admin' | 'client';
}

export interface UpdateUserInput {
  name?: string;
  email?: string;
  role?: 'admin' | 'client';
}

export interface CreateTransactionInput {
  crypto_wallet_asset_id: string;
  cryptomoney_id: string;
  type: 'buy' | 'sell';
  quantity: number;
  price: number;
  total_eur: number;
  admin_operation?: boolean;
}

export interface CreateCryptoInput {
  name: string;
  symbol: string;
  coingecko_id: string;
  image?: string;
  category?: string;
  website?: string;
  price_eur: number;
  market_cap: number;
  volume_24h: number;
  change_24h_pct: number;
}

export interface CreateAccountRequestInput {
  name: string;
  email: string;
}

export interface UpdateAccountRequestInput {
  status: 'pending' | 'approved' | 'rejected';
  processed_by?: string;
  rejection_reason?: string;
}

// Computed/Utility types
export interface WalletSummary {
  totalValue: number;
  totalPlusValue: number;
  balanceEur: number;
  assets: Array<{
    crypto: Cryptomoney;
    asset: CryptoWalletAsset;
    currentValue: number;
    plusValue: number;
    percentage: number;
  }>;
}

export interface CryptoWithHistory extends Cryptomoney {
  priceHistory: CryptoHistory[];
  priceChange24h: number;
  priceChange7d: number;
  priceChange30d: number;
}

// Type guards
export const isUser = (obj: any): obj is User => {
  return obj && typeof obj === 'object' && 'name' in obj && 'email' in obj && 'role' in obj;
};

export const isTransaction = (obj: any): obj is Transaction => {
  return obj && typeof obj === 'object' && 'type' in obj && 'quantity' in obj && 'price' in obj;
};

export const isCryptomoney = (obj: any): obj is Cryptomoney => {
  return obj && typeof obj === 'object' && 'name' in obj && 'symbol' in obj && 'price_eur' in obj;
};