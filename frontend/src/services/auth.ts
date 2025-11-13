import api, { setToken as apiSetToken, getToken as apiGetToken, clearToken as apiClearToken } from './api';

type Credentials = { email: string; password: string }

const STORAGE_USER_KEY = 'user'

export const auth = {
  async login(payload: Credentials) {
    try {
      const res = await api.auth.login(payload)

      // Accepter plusieurs structures de réponse courantes
      const token = res?.token || res?.access_token || res?.data?.token || res?.data?.access_token
      const user = res?.user || res?.data?.user || null

      if (token) {
        apiSetToken(token)
      }

      // Si l'API a renvoyé aussi l'utilisateur, on le stocke
      if (user) {
        try { localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(user)) } catch (e) { console.warn('Erreur de stockage local:', e) }
        return { token, user }
      }

      // Si pas d'utilisateur dans la réponse mais token présent, récupérer le profil
      if (token) {
        try {
          const profile = await api.auth.profile()
          const profileUser = profile?.user || profile || null
          if (profileUser) {
            try { localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(profileUser)) } catch (e) { console.warn('Erreur de stockage local:', e) }
            return { token, user: profileUser }
          }
        } catch (e) {
          // ignore profile fetch error, will be handled by isAuthenticated later
        }
      }

      // Si aucune info utile, lancer une erreur
      throw new Error('Format de réponse de login invalide')
    } catch (error) {
      console.error('Erreur de connexion:', error)
      throw error
    }
  },

  async logout() {
    try {
      await api.auth.logout()
    } catch (e) {
      console.warn('Erreur lors de la déconnexion API:', e)
    } finally {
      this.clearLocalAuth()
    }
  },

  async isAuthenticated() {
    const token = apiGetToken()
    if (!token) {
      // No token stored -> not authenticated. Avoid calling profile (prevents unnecessary 401 requests).
      this.clearLocalAuth()
      return false
    }

    // Vérifier l'authentification en récupérant le profil
    try {
      const response = await api.auth.profile()
      const profileUser = response?.user || response || null
      if (profileUser) {
        try { localStorage.setItem(STORAGE_USER_KEY, JSON.stringify(profileUser)) } catch {}
        return true
      }
      this.clearLocalAuth()
      return false
    } catch (e) {
      console.warn('Token invalide ou erreur de profil:', e)
      this.clearLocalAuth()
      return false
    }
  },

  getUser() {
    try {
      const raw = localStorage.getItem(STORAGE_USER_KEY)
      return raw ? JSON.parse(raw) : null
    } catch {
      return null
    }
  },

  getRole() {
    const u = this.getUser()
    return u?.role || 'CLIENT'
  },

  // Nouvelle méthode pour vider les données d'authentification locales
  clearLocalAuth() {
    apiClearToken()
    try {
      localStorage.removeItem(STORAGE_USER_KEY)
    } catch (e) {
      console.warn('Erreur lors du nettoyage du stockage local:', e)
    }
  },

  // Méthode utilitaire pour vérifier si l'utilisateur est admin
  isAdmin(): boolean {
    return this.getRole().toUpperCase() === 'ADMIN'
  },

  // Méthode utilitaire pour vérifier si l'utilisateur est client
  isClient(): boolean {
    return this.getRole().toUpperCase() === 'CLIENT'
  }
}

export default auth