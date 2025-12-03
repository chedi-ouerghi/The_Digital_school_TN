import type {
    AccountRequest,
    ApiResponse,
    CryptoHistory,
    Cryptomoney,
    Notification,
    PaginatedResponse,
    Transaction,
    UpdateUserInput,
    User,
    Wallet
} from '@/types';
import type { PortfolioResponse } from '../types';

const API_BASE = 'http://localhost:8000/api/v1';

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';

// Generic API response type
interface ApiError {
  message?: string;
  error?: string | Record<string, any>;
}

// Enhanced request function with proper typing
async function request<T = any>(
  path: string,
  method: HttpMethod = 'GET',
  body?: any,
  token?: string
): Promise<T> {
  try {
    const t = token || localStorage.getItem('api_token') || '';

    const headers: Record<string, string> = {
      Accept: 'application/json',
    };
    
    if (t) headers['Authorization'] = `Bearer ${t}`;
    // Only set Content-Type for non-FormData requests
    if (body && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);
    
    const res = await fetch(`${API_BASE}${path}`, {
      method,
      headers,
      body: body && !(body instanceof FormData) 
        ? JSON.stringify(body) 
        : (body as any) || undefined,
      signal: controller.signal,
    });
    
    clearTimeout(timeout);

    const text = await res.text();
    let data: T | ApiError;
    
    try {
      data = text ? JSON.parse(text) : null;
    } catch {
      data = text as any;
    }

    if (!res.ok) {
      let message = `${res.status} ${res.statusText}`;
      const errorData = data as ApiError & { errors?: Record<string, string[]> };

      if (errorData) {
        if (typeof errorData.message === 'string') {
          message = errorData.message;
        } else if (typeof errorData.error === 'string') {
          message = errorData.error;
        } else if (errorData.errors && typeof errorData.errors === 'object') {
          const first = Object.values(errorData.errors)[0];
          message = Array.isArray(first) ? String(first[0]) : JSON.stringify(errorData.errors);
        } else if (errorData.error && typeof errorData.error === 'object') {
          const first = Object.values(errorData.error)[0];
          message = Array.isArray(first) ? String(first[0]) : JSON.stringify(errorData.error);
        }
      }

      const err = new Error(message);
      Object.assign(err, { status: res.status, data: errorData });
      throw err;
    }

    return data as T;
  } catch (error: any) {
    if (error.name === 'AbortError') {
      throw new Error('La requête a expiré (timeout).');
    }
    console.error('Erreur API:', error);
    throw error instanceof Error ? error : new Error('Erreur réseau inconnue.');
  }
}

// ------------------
// 🔑 Token helpers
// ------------------

export function setToken(token: string): void {
  try {
    localStorage.setItem('api_token', token);
  } catch {
    console.warn('Impossible d\'enregistrer le token.');
  }
}

export function getToken(): string | null {
  try {
    return localStorage.getItem('api_token');
  } catch {
    return null;
  }
}

export function clearToken(): void {
  try {
    localStorage.removeItem('api_token');
  } catch {
    console.warn('Impossible de supprimer le token.');
  }
}

// ------------------
// 🔐 Auth
// ------------------

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  user: User;
}

export interface RegisterRequest {
  name: string;
  email: string;
  password: string;
  password_confirmation: string;
}

export interface ChangePasswordRequest {
  current_password: string;
  password: string;
  password_confirmation: string;
}

export interface ProfileUploadResponse {
  path: string;
  url: string;
  user: User; // added user in response payload
}

export const authApi = {
  async login(payload: LoginRequest): Promise<LoginResponse> {
    return await request<LoginResponse>('/login', 'POST', payload);
  },
  
  async requestAccount(payload: RegisterRequest): Promise<ApiResponse<User>> {
    return await request<ApiResponse<User>>('/request-account', 'POST', payload);
  },
  
  async logout(): Promise<void> {
    return await request<void>('/logout', 'POST');
  },
  
  async profile(): Promise<User> {
    return await request<User>('/profile', 'GET');
  },
  
  async updateProfile(payload: UpdateUserInput): Promise<User> {
    return await request<User>('/profile', 'PUT', payload);
  },
  
  async changePassword(payload: ChangePasswordRequest): Promise<void> {
    return await request<void>('/profile/password', 'POST', payload);
  },

    async getProfileStats(): Promise<PortfolioResponse> {
    return await request<PortfolioResponse>('/profile/stats', 'GET');
  },

  async changeId(payload: { new_id: string; confirmation: string }): Promise<{ message: string }> {
    return await request<{ message: string }>('/admin/change-id', 'POST', payload);
  },

  async uploadProfilePicture(formData: FormData): Promise<ApiResponse<ProfileUploadResponse>> {
    // Preferred: PUT to update the resource
    try {
      return await request<ApiResponse<ProfileUploadResponse>>('/profile/picture', 'PUT', formData);
    } catch (err) {
      // Fallback to legacy POST upload endpoint (method spoofing if needed)
      return await request<ApiResponse<ProfileUploadResponse>>('/profile/picture/upload', 'POST', formData);
    }
  },

  async uploadProfileBanner(formData: FormData): Promise<ApiResponse<ProfileUploadResponse>> {
    try {
      return await request<ApiResponse<ProfileUploadResponse>>('/profile/banner', 'PUT', formData);
    } catch (err) {
      return await request<ApiResponse<ProfileUploadResponse>>('/profile/banner/upload', 'POST', formData);
    }
  },

  async deleteProfilePicture(): Promise<ApiResponse<void>> {
    return await request<ApiResponse<void>>('/profile/picture', 'DELETE');
  },

  async deleteProfileBanner(): Promise<ApiResponse<void>> {
    return await request<ApiResponse<void>>('/profile/banner', 'DELETE');
  },
};

// ------------------
// 💰 Cryptos (public + admin)
// ------------------

export interface CryptoListParams {
  page?: number;
  search?: string;
  category?: string;
}

export interface CreateCryptoFromCoinGeckoRequest {
  crypto_id: string;
}

export interface UpdateCryptoRequest {
  name?: string;
  symbol?: string;
  image?: string;
  price_eur?: number;
  coingecko_id?: string;
  category?: string;
  website?: string;
  market_cap?: number;
  volume_24h?: number;
  change_24h_pct?: number;
}

export const cryptoApi = {
  async list(params: CryptoListParams = {}): Promise<PaginatedResponse<Cryptomoney>> {
    const queryParams = new URLSearchParams();
    if (params.page) queryParams.append('page', params.page.toString());
    if (params.search) queryParams.append('search', params.search);
    if (params.category) queryParams.append('category', params.category);
    
    const queryString = queryParams.toString();
    return await request<PaginatedResponse<Cryptomoney>>(`/cryptos${queryString ? `?${queryString}` : ''}`, 'GET');
  },
  
  async show(id: string): Promise<Cryptomoney> {
    return await request<Cryptomoney>(`/cryptos/${id}`, 'GET');
  },
  
  async history(id: string): Promise<CryptoHistory[]> {
    return await request<CryptoHistory[]>(`/cryptos/${id}/history`, 'GET');
  },
  
  // Admin methods
  async createFromCoinGecko(payload: CreateCryptoFromCoinGeckoRequest): Promise<ApiResponse<Cryptomoney>> {
    return await request<ApiResponse<Cryptomoney>>('/cryptos', 'POST', payload);
  },
  
  async update(id: string, payload: UpdateCryptoRequest): Promise<ApiResponse<Cryptomoney>> {
    return await request<ApiResponse<Cryptomoney>>(`/admin/cryptos/${id}`, 'PUT', payload);
  },

  async updateWithImage(id: string, formData: FormData): Promise<ApiResponse<Cryptomoney>> {
    return await request<ApiResponse<Cryptomoney>>(`/admin/cryptos/${id}`, 'PUT', formData);
  },
  
  async delete(id: string): Promise<void> {
    return await request<void>(`/admin/cryptos/${id}`, 'DELETE');
  },


  //  lance la synchronisation de l'historique 
  async syncHistory(): Promise<ApiResponse<any>> {
    return await request<ApiResponse<any>>('/admin/cryptos/sync-history', 'POST');
  },
};

// ------------------
// 💼 Wallet / Transactions
// ------------------

export interface TransactionRequest {
  symbol: string;
  type: 'ACHAT' | 'VENTE';
  quantity: number;
}

export interface WalletHistoryResponse {
  date: string;
  value: number;
  change: number;
}

export interface PlusValueResponse {
  total_value: number;
  total_invested: number;
  total_plus_value: number;
  plus_value_percentage: number;
}

export const walletApi = {
  async list(): Promise<Wallet[]> {
    return await request<Wallet[]>('/wallets', 'GET');
  },
  
  async show(id: string): Promise<Wallet> {
    return await request<Wallet>(`/wallets/${id}`, 'GET');
  },
  
  async transact(payload: TransactionRequest): Promise<ApiResponse<Transaction>> {
    return await request<ApiResponse<Transaction>>('/wallets/transaction', 'POST', payload);
  },
  
  async plusValue(): Promise<PlusValueResponse> {
    return await request<PlusValueResponse>('/wallets/plus-value', 'GET');
  },
  
  async history(): Promise<WalletHistoryResponse[]> {
    return await request<WalletHistoryResponse[]>('/wallets/history', 'GET');
  },
  
  async walletHistory(id: string): Promise<WalletHistoryResponse[]> {
    return await request<WalletHistoryResponse[]>(`/wallets/${id}/history`, 'GET');
  },
};

// ------------------
// 📰 Blog posts (public)
// ------------------
export interface BlogListParams {
  page?: number;
  search?: string;
  category?: string;
}

export const blogApi = {
  async list(params: BlogListParams = {}) {
    const queryParams = new URLSearchParams();
    if (params.page) queryParams.append('page', params.page.toString());
    if (params.search) queryParams.append('search', params.search);
    if (params.category) queryParams.append('category', params.category);

    const queryString = queryParams.toString();
    return await request<any>(`/blogs${queryString ? `?${queryString}` : ''}`, 'GET');
  },

  async show(slug: string) {
    return await request<any>(`/blogs/${slug}`, 'GET');
  },
  async create(payload: any) {
    return await request<any>('/admin/blogs', 'POST', payload);
  },
  async update(id: string | number, payload: any) {
    return await request<any>(`/admin/blogs/${id}`, 'PUT', payload);
  },
  async delete(id: string | number) {
    return await request<any>(`/admin/blogs/${id}`, 'DELETE');
  },
};

// ------------------
// 👨‍💼 Admin - Clients
// ------------------

export interface CreateClientRequest {
  name: string;
  email: string;
  role?: 'CLIENT' | 'ADMIN';
  solde?: number;
}

export interface UpdateClientRequest {
  name?: string;
  email?: string;
  role?: string;
  solde?: number;
}

export interface ClientListParams {
  page?: number;
  search?: string;
  role?: string;
}

export const adminClientsApi = {
  async list(params: ClientListParams = {}): Promise<PaginatedResponse<User>> {
    const queryParams = new URLSearchParams();
    if (params.page) queryParams.append('page', params.page.toString());
    if (params.search) queryParams.append('search', params.search);
    if (params.role) queryParams.append('role', params.role);
    
    const queryString = queryParams.toString();
    return await request<PaginatedResponse<User>>(`/admin/clients${queryString ? `?${queryString}` : ''}`, 'GET');
  },
  
  async create(payload: CreateClientRequest): Promise<ApiResponse<User>> {
    return await request<ApiResponse<User>>('/admin/clients', 'POST', payload);
  },
  
  async show(id: string): Promise<User> {
    return await request<User>(`/admin/clients/${id}`, 'GET');
  },
  
  async update(id: string, payload: UpdateClientRequest): Promise<ApiResponse<User>> {
    return await request<ApiResponse<User>>(`/admin/clients/${id}`, 'PUT', payload);
  },
  
  async delete(id: string): Promise<void> {
    return await request<void>(`/admin/clients/${id}`, 'DELETE');
  },
  
  async transactions(id: string): Promise<PaginatedResponse<Transaction>> {
    return await request<PaginatedResponse<Transaction>>(`/admin/clients/${id}/transactions`, 'GET');
  },
  
  async getPortfolio(id: string): Promise<Wallet> {
    return await request<Wallet>(`/admin/clients/${id}/wallet`, 'GET');
  },
};

// ------------------
// 📋 Admin - Account Requests
// ------------------

export const adminAccountRequestsApi = {
  async list(): Promise<PaginatedResponse<AccountRequest>> {
    return await request<PaginatedResponse<AccountRequest>>('/admin/account-requests', 'GET');
  },
  
  async approve(id: string): Promise<ApiResponse<AccountRequest>> {
    return await request<ApiResponse<AccountRequest>>(`/admin/account-requests/${id}/approve`, 'POST');
  },
  
  async reject(id: string, reason?: string): Promise<ApiResponse<AccountRequest>> {
    return await request<ApiResponse<AccountRequest>>(`/admin/account-requests/${id}/reject`, 'POST', { reason });
  },
};

// ------------------
// 📊 Admin - Stats
// ------------------

export interface AdminStats {
  total_users: number;
  total_cryptos: number;
  total_transactions: number;
  total_volume: number;
  recent_transactions: Transaction[];
  top_cryptos: Cryptomoney[];
  pending_requests: number;
}

export const adminStatsApi = {
  async global(): Promise<AdminStats> {
    return await request<AdminStats>('/admin/stats', 'GET');
  },
};

// ------------------
// 📋 Admin - Transactions
// ------------------

export interface TransactionListParams {
  page?: number;
  user_id?: string;
  cryptomoney_id?: string;
  type?: 'buy' | 'sell';
  status?: 'active' | 'cancelled';
  date_from?: string;
  date_to?: string;
}

export interface CancelTransactionRequest {
  reason?: string;
}

export const adminTransactionsApi = {
  async list(params: TransactionListParams = {}): Promise<PaginatedResponse<Transaction>> {
    const queryParams = new URLSearchParams();
    
    Object.entries(params).forEach(([key, value]) => {
      if (value !== undefined && value !== null) {
        queryParams.append(key, value.toString());
      }
    });
    
    const queryString = queryParams.toString();
    return await request<PaginatedResponse<Transaction>>(`/admin/transactions${queryString ? `?${queryString}` : ''}`, 'GET');
  },
  
  async show(id: string): Promise<Transaction> {
    return await request<Transaction>(`/admin/transactions/${id}`, 'GET');
  },
  
  async cancel(id: string, reason?: string): Promise<ApiResponse<Transaction>> {
    return await request<ApiResponse<Transaction>>(`/admin/transactions/${id}/cancel`, 'POST', { reason });
  },
};

// ------------------
// 🔔 Notifications
// ------------------

export interface NotificationListParams {
  page?: number;
  type?: string;
  is_read?: boolean;
}

export const notificationsApi = {
  async list(params: NotificationListParams = {}): Promise<PaginatedResponse<Notification>> {
    const queryParams = new URLSearchParams();
    if (params.page) queryParams.append('page', params.page.toString());
    if (params.type) queryParams.append('type', params.type);
    if (params.is_read !== undefined) queryParams.append('is_read', params.is_read.toString());
    
    const queryString = queryParams.toString();
    return await request<PaginatedResponse<Notification>>(`/notifications${queryString ? `?${queryString}` : ''}`, 'GET');
  },
  
  async markAsRead(id: string): Promise<ApiResponse<Notification>> {
    return await request<ApiResponse<Notification>>(`/notifications/${id}/read`, 'PUT');
  },
  
  async markAllAsRead(): Promise<ApiResponse<void>> {
    return await request<ApiResponse<void>>('/notifications/read-all', 'PUT');
  },
};

// ------------------
// 🌍 Export global API
// ------------------

const api = {
  setToken,
  getToken,
  clearToken,
  auth: authApi,
  crypto: cryptoApi,
  wallet: walletApi,
  notifications: notificationsApi,
  admin: {
    clients: adminClientsApi,
    cryptos: cryptoApi,
    stats: adminStatsApi,
    transactions: adminTransactionsApi,
    accountRequests: adminAccountRequestsApi,
  },
};

export default api;