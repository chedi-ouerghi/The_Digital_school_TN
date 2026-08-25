import api, { API_BASE } from './api';

/**
 * SÉCURITÉ : Authentification basée sur les cookies HttpOnly + Secure
 * 
 * Modèle :
 * 1. Appeler /sanctum/csrf-cookie pour obtenir le token XSRF
 * 2. Login : le serveur retourne un cookie HttpOnly (XSRF-TOKEN + Laravel session)
 * 3. Stockage user : UNIQUEMENT en mémoire ou sessionStorage (jamais localStorage)
 * 4. Logout : le serveur efface les cookies
 * 
 * Ce modèle prévient :
 * - CSRF : protection Sanctum via X-XSRF-TOKEN header (automatique)
 * - XSS : les cookies HttpOnly ne sont pas accessibles via JS
 * - Token hijacking : pas de token à voler
 */

type Credentials = { email: string; password: string }

const STORAGE_USER_KEY = 'user'

/**
 * 🔥 CRITIQUE : Initialiser le CSRF token
 * 
 * Cet appel DOIT être fait avant le premier login/logout
 * Sanctum définira le cookie XSRF-TOKEN que nous enverrons dans X-XSRF-TOKEN header
 * 
 * IMPORTANT : La route est /sanctum/csrf-cookie (PAS /api/v1/sanctum/csrf-cookie)
 */
export async function initializeCsrf() {
  try {
    const apiUrl = API_BASE.replace('/api/v1', ''); // Retirer v1 du prefix
    await fetch(`${apiUrl}/sanctum/csrf-cookie`, {
      method: 'GET',
      credentials: 'include',  // ← Inclure les cookies
      headers: {
        'Accept': 'application/json',
      },
    });
  } catch (error) {
    console.error('❌ Failed to initialize CSRF token:', error);
  }
}

export const auth = {
  /**
   * Login avec cookies HttpOnly + CSRF protection
   * 
   * IMPORTANT : Appeler initializeCsrf() d'abord
   */
  async login(payload: Credentials) {
    try {
      // Ensure CSRF token is initialized
      await initializeCsrf();

      const res = await api.auth.login(payload)

      // Parser les réponses possibles
      const user = res?.user || res?.data?.user || null

      if (!user) {
        throw new Error('Format de réponse de login invalide')
      }

      // SÉCURITÉ : Stocker UNIQUEMENT les infos non-sensibles en sessionStorage
      const safeUser = {
        id: user.id,
        email: user.email,
        name: user.name,
        role: user.role,
        password_changed_at: user.password_changed_at,
      };

      try {
        if (typeof window !== 'undefined' && window.sessionStorage) {
          window.sessionStorage.setItem(STORAGE_USER_KEY, JSON.stringify(safeUser))
        }
      } catch (e) {
        console.warn('Impossible de stocker l\'utilisateur :', e)
      }

      // Le cookie est automatiquement géré par le navigateur et Sanctum
      return { user: safeUser }
    } catch (error) {
      console.error('Erreur de connexion:', error)
      throw error
    }
  },

  /**
   * Logout : le serveur efface les cookies
   * 🔥 FIX double-clic : réinitialiser le CSRF avant le POST /logout,
   * sinon le premier appel peut échouer en 419 et la session reste active.
   */
  async logout() {
    try {
      await initializeCsrf();
      await api.auth.logout()
    } catch (e) {
      console.warn('Erreur lors de la déconnexion API:', e)
    } finally {
      this.clearLocalAuth()
    }
  },

  /**
   * Vérifier si l'utilisateur est authentifié
   * Le serveur valide la session via les cookies HttpOnly
   */
  async isAuthenticated() {
    try {
      const response = await api.auth.profile()
      const profileUser = response?.user || response || null

      if (profileUser) {
        const safeUser = {
          id: profileUser.id,
          email: profileUser.email,
          name: profileUser.name,
          role: profileUser.role,
          password_changed_at: profileUser.password_changed_at,
        };
        try {
          if (typeof window !== 'undefined' && window.sessionStorage) {
            window.sessionStorage.setItem(STORAGE_USER_KEY, JSON.stringify(safeUser))
          }
        } catch {
          // sessionStorage peut etre indisponible (navigation privee)
        }
        return true
      }
      this.clearLocalAuth()
      return false
    } catch (e) {
      console.warn('Session invalide ou erreur de profil:', e)
      this.clearLocalAuth()
      return false
    }
  },

  /**
   * Récupérer l'utilisateur depuis sessionStorage
   * Les données sensibles ne sont JAMAIS accessibles au frontend
   */
  getUser() {
    try {
      const raw = typeof window !== 'undefined' && window.sessionStorage
        ? window.sessionStorage.getItem(STORAGE_USER_KEY)
        : null
      return raw ? JSON.parse(raw) : null
    } catch {
      return null
    }
  },

  getRole() {
    const u = this.getUser()
    return u?.role || 'CLIENT'
  },

  /**
   * Effacer les données d'authentification locales
   * Les cookies sont effacés par le serveur lors du logout
   */
  clearLocalAuth() {
    try {
      if (typeof window !== 'undefined' && window.sessionStorage) {
        window.sessionStorage.removeItem(STORAGE_USER_KEY)
      }
    } catch (e) {
      console.warn('Erreur lors du nettoyage du stockage:', e)
    }
  },

  isAdmin(): boolean {
    return this.getRole().toUpperCase() === 'ADMIN'
  },

  isClient(): boolean {
    return this.getRole().toUpperCase() === 'CLIENT'
  }
}

export default auth