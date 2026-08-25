import type {
  AccountRequest,
  ApiResponse, CryptoHistory, Cryptomoney,
  Notification,
  PaginatedResponse,
  Transaction,
  UpdateUserInput,
  User,
  Wallet
} from '@/types';
import type { PortfolioResponse } from '../types';

const API_BASE = 'http://localhost:8000/api/v1';

export { API_BASE };

type HttpMethod = 'GET' | 'POST' | 'PUT' | 'DELETE' | 'PATCH';

// Generic API response type
interface ApiError {
  message?: string;
  error?: string | Record<string, unknown>;
}

/**
 * SÉCURITÉ : Enhanced request function utilisant les cookies HttpOnly + Secure
 * 
 * Migration de Bearer Token (localStorage) vers Sanctum SPA Mode:
 * - Suppression du token dans localStorage
 * - Authentification via cookies HttpOnly (gérés par le serveur)
 * - Ajout de credentials: 'include' pour inclure les cookies
 * - CSRF token géré automatiquement par Sanctum
 * 
 * IMPORTANT : Cette fonction envoie automatiquement le header X-XSRF-TOKEN
 *             qui est extrait du cookie XSRF-TOKEN
 */

/**
 * 🔥 Initialiser le token XSRF via Sanctum
 * IMPORTANT: Appeler cette fonction AVANT toute requête POST publique (sans authentification)
 * Notamment pour: /login, /request-account, /verify-email
 */
async function initializeCsrfToken(): Promise<void> {
  try {
    // 🔥 IMPORTANT: Utiliser l'URL absolue du backend, pas une URL relative
    // Le frontend (localhost:5173) ne peut pas servir cette route
    const csrfUrl = 'http://localhost:8000/sanctum/csrf-cookie';

    const response = await fetch(csrfUrl, {
      method: 'GET',
      credentials: 'include',
      headers: {
        'Accept': 'application/json',
      }
    });

    // Attendre un peu pour s'assurer que le cookie est défini
    await new Promise(resolve => setTimeout(resolve, 100));

    if (response.ok) {
      getXsrfToken();
    }
  } catch (error) {
    console.warn('⚠️ Failed to initialize CSRF token:', error);
  }
}

/**
 * 🔥 Extraire le token XSRF du cookie
 * Sanctum stocke le token dans le cookie XSRF-TOKEN
 */
function getXsrfToken(): string | null {
  if (typeof document === 'undefined') return null;

  const cookies = document.cookie.split(';');
  for (const cookie of cookies) {
    const [name, value] = cookie.trim().split('=');
    if (name === 'XSRF-TOKEN') {
      return decodeURIComponent(value || '');
    }
  }
  return null;
}

async function request<T = unknown>(
  path: string,
  method: HttpMethod = 'GET',
  body?: unknown
): Promise<T> {
  try {
    const headers: Record<string, string> = {
      Accept: 'application/json',
    };

    // Only set Content-Type for non-FormData requests
    if (body && !(body instanceof FormData)) {
      headers['Content-Type'] = 'application/json';
    }

    // 🔥 CRITICAL : Ajouter le header X-XSRF-TOKEN pour la protection CSRF
    // Sanctum valide que ce header correspond au cookie XSRF-TOKEN
    const xsrfToken = getXsrfToken();
    if (xsrfToken) {
      headers['X-XSRF-TOKEN'] = xsrfToken;
    }

    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 15000);

    let finalMethod = method;
    let finalBody = body;

    // Laravel does not natively support PUT/PATCH with multipart/form-data
    // We must spoof the method by sending a POST request with _method field
    if ((method === 'PUT' || method === 'PATCH') && body instanceof FormData) {
      finalMethod = 'POST';
      finalBody.append('_method', method);
    }

    const res = await fetch(`${API_BASE}${path}`, {
      method: finalMethod,
      headers,
      // SÉCURITÉ : Inclure les cookies HttpOnly dans les requêtes cross-origin
      credentials: 'include',
      body: finalBody && !(finalBody instanceof FormData)
        ? JSON.stringify(finalBody)
        : (finalBody as unknown as BodyInit) || undefined,
      signal: controller.signal,
    });

    clearTimeout(timeout);

    const text = await res.text();
    let data: T | ApiError;

    try {
      data = text ? JSON.parse(text) : null;
    } catch {
      data = text as unknown as T;
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
  } catch (error) {
    if (error instanceof Error && error.name === 'AbortError') {
      throw new Error('La requête a expiré (timeout).');
    }
    console.error('Erreur API:', error);
    throw error instanceof Error ? error : new Error('Erreur réseau inconnue.');
  }
}

// ------------------
// 🔑 Token helpers (DEPRECATED - kept for backward compatibility)
// ------------------
/**
 * SÉCURITÉ : Ces fonctions sont DÉPRÉCIÉES
 * L'authentification utilise maintenant les cookies HttpOnly gérés par Sanctum
 * Le token ne doit JAMAIS être stocké en localStorage
 */
export function setToken(_token: string): void {
  // DEPRECATED: Ne pas utiliser
  console.warn('[DEPRECATED] setToken() is no longer needed. Use cookie-based auth.');
}

export function getToken(): string | null {
  // DEPRECATED: Ne pas utiliser
  return null;
}

export function clearToken(): void {
  // DEPRECATED: Ne pas utiliser
  console.warn('[DEPRECATED] clearToken() is no longer needed. Cookies are cleared server-side.');
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
    await initializeCsrfToken();
    return await request<LoginResponse>('/login', 'POST', payload);
  },

  async requestAccount(payload: RegisterRequest): Promise<ApiResponse<User>> {
    await initializeCsrfToken();
    return await request<ApiResponse<User>>('/request-account', 'POST', payload);
  },

  async verifyEmail(payload: { token: string }): Promise<ApiResponse<{ message: string; user?: User }>> {
    await initializeCsrfToken();
    return await request<ApiResponse<{ message: string; user?: User }>>('/verify-email', 'POST', payload);
  },

  async logout(): Promise<void> {
    return await request<void>('/logout', 'POST');
  },

  async profile(): Promise<User> {
    const response = await request<{ user: User }>('/profile', 'GET');
    return response.user;
  },

  async updateProfile(payload: UpdateUserInput): Promise<User> {
    return await request<User>('/profile', 'PUT', payload);
  },

  async changePassword(payload: ChangePasswordRequest): Promise<void> {
    return await request<void>('/profile/password', 'PUT', payload);
  },

  async getProfileStats(): Promise<PortfolioResponse> {
    return await request<PortfolioResponse>('/profile/stats', 'GET');
  },



  async uploadProfilePicture(formData: FormData): Promise<ApiResponse<ProfileUploadResponse>> {
    // Use PUT method for profile picture upload
    return await request<ApiResponse<ProfileUploadResponse>>('/profile/picture', 'PUT', formData);
  },

  async uploadProfilePicturePost(formData: FormData): Promise<ApiResponse<ProfileUploadResponse>> {
    // POST method for profile picture upload
    return await request<ApiResponse<ProfileUploadResponse>>('/profile/picture/upload', 'POST', formData);
  },

  async uploadProfileBanner(formData: FormData): Promise<ApiResponse<ProfileUploadResponse>> {
    // Use PUT method for profile banner upload
    return await request<ApiResponse<ProfileUploadResponse>>('/profile/banner', 'PUT', formData);
  },

  async uploadProfileBannerPost(formData: FormData): Promise<ApiResponse<ProfileUploadResponse>> {
    // POST method for profile banner upload
    return await request<ApiResponse<ProfileUploadResponse>>('/profile/banner/upload', 'POST', formData);
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

/**
 * ✅ Les 10 cryptos sont FIXES et non modifiables

 * ✅ Seule fonctionnalité: Synchroniser l'historique 30 jours avec api.crypto.syncHistory()
 */

export interface CryptoListParams {
  page?: number;
  search?: string;
  category?: string;
}

export interface CryptoHistoryPayload {
  history: CryptoHistory[];
  name: string;
  symbol: string;
  count: number;
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

  async history(id: string, days: number = 30): Promise<CryptoHistoryPayload> {
    return await request<CryptoHistoryPayload>(`/cryptos/${id}/history?days=${days}`, 'GET');
  },

  // ✅ Synchroniser l'historique de toutes les cryptos (24h, 7j, 30j)
  async syncHistory(): Promise<ApiResponse<{ status: string; message: string; output: string[] }>> {
    return await request<ApiResponse<{ status: string; message: string; output: string[] }>>('/admin/cryptos/sync-history', 'POST');
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
  async list(): Promise<Wallet> {
    return await request<Wallet>('/wallets', 'GET');
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

  async history(days: number = 30): Promise<WalletHistoryResponse[]> {
    return await request<WalletHistoryResponse[]>(`/wallets/history?days=${days}`, 'GET');
  },

  async walletHistory(id: string): Promise<WalletHistoryResponse[]> {
    return await request<WalletHistoryResponse[]>(`/wallets/${id}/history`, 'GET');
  },

  async getTransactionsHistory(type?: 'ACHAT' | 'VENTE'): Promise<{ transactions: Transaction[] }> {
    return await request<{ transactions: Transaction[] }>(`/wallets/transactions/history${type ? `?type=${type}` : ''}`, 'GET');
  },
};

// ------------------
// ‍💼 Admin - Clients
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

export interface ApproveAccountRequestPayload {
  temporary_password: string;
}

export const adminAccountRequestsApi = {
  async list(): Promise<PaginatedResponse<AccountRequest>> {
    return await request<PaginatedResponse<AccountRequest>>('/admin/account-requests', 'GET');
  },

  async approve(id: string, payload?: ApproveAccountRequestPayload): Promise<ApiResponse<AccountRequest>> {
    return await request<ApiResponse<AccountRequest>>(`/admin/account-requests/${id}/approve`, 'POST', payload);
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
    return await request<ApiResponse<Notification>>(`/notifications/${id}/read`, 'PATCH');
  },

  async markAllAsRead(): Promise<ApiResponse<{ updated: number }>> {
    return await request<ApiResponse<{ updated: number }>>('/notifications/read-all', 'PATCH');
  },

  async unreadCount(): Promise<{ unread_count: number }> {
    return await request<{ unread_count: number }>('/notifications/unread-count', 'GET');
  },


};

// ------------------
// 🌍 Export global API
// ------------------

const api = {
  setToken,
  getToken,
  clearToken,
  initializeCsrfToken,
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