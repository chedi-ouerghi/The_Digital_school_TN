import ProfilePage from '@/pages/client/profile/ProfilePage.vue'
import { createRouter, createWebHistory } from 'vue-router'
import Blog from '../pages/Blog.vue'
import BlogPost from '../pages/BlogPost.vue'
import Dashboard from '../pages/Dashboard.vue'
import Landing from '../pages/Landing.vue'
import SignIn from '../pages/SignIn.vue'
import VerifyEmail from '../pages/VerifyEmail.vue'
import AdminClients from '../pages/admin/Clients.vue'
import CryptoDetails from '../pages/admin/CryptoDetails.vue'
import Cryptos from '../pages/admin/Cryptos.vue'
import AdminOverview from '../pages/admin/Overview.vue'
import Settings from '../pages/admin/Settings.vue'
import AdminTransactionDetails from '../pages/admin/TransactionDetails.vue'
import AdminTransactions from '../pages/admin/Transactions.vue'
import ClientCryptosDetails from '../pages/client/CryptosDetails.vue'
import ClientOverview from '../pages/client/Overview.vue'
import ClientPortfolioDetails from '../pages/client/PortfolioDetails.vue'
import ClientTransactions from '../pages/client/Transactions.vue'
import auth from '../services/auth'

const routes = [
  { 
    path: '/', 
    name: '/Bitchest',
    component: Landing,
  },
  {
    path: '/blog',
    name: 'Blog',
    component: Blog,
  },
  {
    path: '/blog/:slug',
    name: 'BlogPost',
    component: BlogPost,
  },
  { 
    path: '/signin', 
    name: 'SignIn', 
    component: SignIn,
    meta: { requiresGuest: true }
  },
  {
    path: '/verify-email',
    name: 'VerifyEmail',
    component: VerifyEmail,
    meta: { requiresGuest: false }
  },
  
  {
  path: '/:pathMatch(.*)*',
  name: 'NotFound',
  component: () => import('../pages/NotFound.vue')
},

  // Dashboard principal
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard,
    meta: { requiresAuth: true },
    redirect: () => {
      const role = auth.getRole()?.toUpperCase()
      return role === 'ADMIN' ? '/dashboard/admin/overview' : '/dashboard/overview'
    },
    children: [
      // === ADMIN ROUTES ===
      {
        path: 'admin/overview',
        name: 'AdminOverview',
        component: AdminOverview,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      {
        path: 'admin/clients',
        name: 'AdminClients',
        component: AdminClients,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      {
        path: 'admin/clients/:id',
        name: 'AdminClientDetails',
        component: () => import('../pages/admin/ClientDetails.vue'),
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      {
        path: 'admin/cryptos',
        name: 'AdminCryptos',
        component: Cryptos,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      {
        path: 'admin/cryptos/:id',
        name: 'AdminCryptosDetails',
        component: CryptoDetails,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      {
        path: 'admin/transactions',
        name: 'AdminTransactions',
        component: AdminTransactions,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      {
        path: 'admin/transactions/:id',
        name: 'AdminTransactionDetails',
        component: AdminTransactionDetails,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
{
        path: 'admin/settings',
        name: 'AdminSettings',
        component: Settings,
        meta: { requiresAuth: true, roles: ['ADMIN'] }
      },
      

      // === CLIENT ROUTES ===
      {
        path: 'overview',
        name: 'ClientOverview',
        component: ClientOverview,
        meta: { requiresAuth: true, roles: ['CLIENT'] }
      },

      {
        path: 'cryptos',
        name: 'ClientCryptos',
        component: () => import('../pages/client/Cryptos.vue'),
        meta: { requiresAuth: true, roles: ['CLIENT'] }
      },
      {
        path: 'cryptos/:id',
        name: 'ClientCryptosDetails',
        component: ClientCryptosDetails,
        meta: { requiresAuth: true, roles: ['CLIENT'] }
      },
      {
        path: 'portfolio',
        name: 'ProfilePortfolio',
        component: ProfilePage,
        meta: { requiresAuth: true, roles: ['CLIENT'] }
      },
      {
        path: 'portfolio/crypto/:id',
        name: 'ClientPortfolioDetails',
        component: ClientPortfolioDetails,
        meta: { requiresAuth: true, roles: ['CLIENT'] }
      },
      {
        path: 'transactions',
        name: 'ClientTransactions',
        component: ClientTransactions,
        meta: { requiresAuth: true, roles: ['CLIENT'] }
      }
    ]
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

// === GUARDES DE NAVIGATION ===
router.beforeEach(async (to, from) => {
  const isAuthenticated = await auth.isAuthenticated()
  const userRole = auth.getRole()?.toUpperCase()
  
  if (to.meta.requiresAuth && !isAuthenticated) {
    return { path: '/signin', query: { redirect: to.fullPath } }
  }

  if (to.meta.requiresGuest && isAuthenticated) {
    const redirectPath = from.query.redirect as string
    if (redirectPath) return { path: redirectPath }
    return userRole === 'ADMIN' 
      ? { path: '/dashboard/admin/overview' }
      : { path: '/dashboard/overview' }
  }

  if (to.meta.requiresAuth && to.meta.roles) {
    const requiredRoles = (to.meta.roles as string[]).map(role => role.toUpperCase())
    if (!requiredRoles.includes(userRole)) {
      return userRole === 'ADMIN' 
        ? { path: '/dashboard/admin/overview' }
        : { path: '/dashboard/overview' }
    }
  }
})

export default router
