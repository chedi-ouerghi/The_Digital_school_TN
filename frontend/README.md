# 🎨 BitChest Frontend

Interface utilisateur moderne et réactive pour la plateforme BitChest, construite avec **Vue 3** et **Vite**.

## 📋 Table des Matières

- [Vue d'ensemble](#vue-densemble)
- [Caractéristiques](#caractéristiques)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Structure du Projet](#structure-du-projet)
- [Pages et Composants](#pages-et-composants)
- [Services API](#services-api)
- [Authentification](#authentification)
- [Uploads de Fichiers](#uploads-de-fichiers)
- [Gestion d'État](#gestion-détat)
- [Styles et Théming](#styles-et-théming)
- [Build et Déploiement](#build-et-déploiement)
- [Dépannage](#dépannage)

## 🎯 Vue d'ensemble

BitChest Frontend est une interface complète et professionnelle pour accéder à la plateforme BitChest. Elle offre deux expériences distinctes : une pour les clients et une pour les administrateurs.

**Technos Utilisées** :
- Vue 3 (Composition API) - Framework réactif moderne
- Vite - Build tool ultra-rapide et léger
- Tailwind CSS - Stylisation utilitaire complète
- Shadcn-vue - Composants UI professionnels basés sur Radix UI
- Vue Router - Routage SPA avec lazy loading
- Axios - Client HTTP configuré pour Sanctum
- Lucide Vue Next - Icônes SVG optimisées
- TypeScript - Types statiques optionnels

## ✨ Caractéristiques

### Interface Client
- ✅ **Authentification Sécurisée** - Connexion/Inscription avec validation
- 💰 **Dashboard Personnel** - Vue d'ensemble dynamique du portefeuille
- 🪙 **Trading en Temps Réel** - Achat/Vente de cryptomonnaies avec prix actuels
- 📊 **Analyse Visuelle** - Graphiques Chart.js interactifs et responsifs
- 📈 **Historique Complet** - Traçabilité de toutes les transactions
- 👤 **Profil Personnalisable** - Gestion photo de profil, bannière, paramètres
- 🔔 **Notifications** - Panneau de notifications en temps réel avec marquage
- 📱 **Design Responsive** - Parfait sur desktop, tablet et mobile

### Interface Administrateur
- 📊 **Dashboard Avancé** - Statistiques globales, KPIs et tendances
- 👥 **Gestion Clients** - Vue grid/liste, création, modification, suppression
- 🪙 **Gestion Cryptos** - CRUD complet avec upload d'images
- 📋 **Transactions** - Vue table avec pagination, annulation avec raison
- 📝 **Demandes de Compte** - Approuver/Rejeter les demandes avec notifications
- ⚙️ **Paramètres Avancés** - Configuration ID admin, langue, fuseau horaire
- 📈 **Rapports Détaillés** - Graphiques et analyses complètes
- 🔍 **Recherche & Filtrage** - Recherche temps réel et filtres multiples

### Général
- 🎨 **Design Cohérent** - Palette de couleurs harmonieuse (#38618C, #35A7FF, #01FF19, #FF5964)
- 🌓 **Responsive Design** - Breakpoints Tailwind optimisés
- ⚡ **Performance** - Lazy loading, code splitting, optimisation assets
- 🔒 **Sécurité** - Tokens Sanctum, validation, HTTPS ready
- ♿ **Accessibilité** - ARIA labels, navigation au clavier, contraste WCAG
- 🌍 **Internationalisation Prête** - Structure pour supports multilingues

## 📦 Prérequis

- Node.js 18.x ou supérieur
- npm 9.x ou supérieur
- Un backend BitChest en cours d'exécution (http://localhost:8000)
- Navigateur moderne (Chrome, Firefox, Safari, Edge)

## 🚀 Installation

### 1. Cloner le Repository

```bash
cd Bitchest_project/frontend
```

### 2. Installer les Dépendances

```bash
npm install
```

Cela installe :
- Vue 3 + Vue Router + Composition API
- Vite comme bundler
- Tailwind CSS pour la stylisation
- Shadcn-vue pour les composants UI
- Axios pour les requêtes HTTP
- Chart.js pour les graphiques
- Lucide Vue Next pour les icônes
- TypeScript pour la sécurité des types

### 3. Configurer les Variables d'Environnement

Créer un fichier `.env` à la racine du frontend :

```env
# .env
VITE_API_URL=http://localhost:8000
VITE_APP_NAME=BitChest
VITE_APP_VERSION=1.0.0
VITE_DEBUG=false
```

### 4. Démarrer le Serveur de Développement

```bash
npm run dev
```

L'application sera disponible sur : **http://localhost:5173**

Le serveur Vite offre :
- Hot Module Replacement (HMR) pour les mises à jour instantanées
- Reconstruction rapide des fichiers modifiés
- Affichage des erreurs dans le navigateur

### 5. Compiler les Assets (Production)

```bash
npm run build
```

Les fichiers compilés seront dans `dist/`

## ⚙️ Configuration

### Variables d'Environnement

Créer `.env` à la racine du frontend :

```env
# URL de l'API backend - IMPORTANT : doit correspondre à APP_URL du backend
VITE_API_URL=http://localhost:8000

# Informations d'application
VITE_APP_NAME=BitChest
VITE_APP_VERSION=1.0.0

# Mode debug (affiche logs supplémentaires)
VITE_DEBUG=false

# Optionnel : timeout des requêtes (ms)
VITE_REQUEST_TIMEOUT=30000
```

**Important** :
- `VITE_API_URL` doit correspondre à `APP_URL` du backend
- En production, utiliser les URLs finales (domaine complet, HTTPS)
- Ne jamais committer `.env` avec des secrets
- Les variables VITE_* sont disponibles dans `import.meta.env`

### Fichier vite.config.js

Configuration Vite optimisée :

```javascript
import vue from '@vitejs/plugin-vue'
import path from 'path'

export default {
  plugins: [vue()],
  resolve: {
    alias: {
      '@': path.resolve(__dirname, './src'),
    },
  },
  server: {
    port: 5173,
    host: 'localhost',
    cors: true,
  },
  build: {
    outDir: 'dist',
    sourcemap: false,
    minify: 'terser',
  },
}
```

### Fichier tailwind.config.js

Configuration Tailwind avec couleurs personnalisées :

```javascript
export default {
  content: ['./index.html', './src/**/*.{vue,js,ts,jsx,tsx}'],
  theme: {
    extend: {
      colors: {
        'bitchest-blue': '#35A7FF',
        'bitchest-dark': '#38618C',
        'bitchest-green': '#01FF19',
        'bitchest-red': '#FF5964',
      },
    },
  },
  plugins: [],
}
```

## 🏗️ Structure du Projet

```
frontend/
├── src/
│   ├── pages/
│   │   ├── SignIn.vue                      # Authentification
│   │   ├── Dashboard.vue                   # Layout principal
│   │   │
│   │   ├── client/
│   │   │   ├── ProfilePortfolio.vue       # Profil + portefeuille
│   │   │   ├── Transactions.vue           # Historique transactions
│   │   │   └── Cryptos.vue                # Liste cryptos (client)
│   │   │
│   │   └── admin/
│   │       ├── Overview.vue               # Dashboard admin
│   │       ├── Cryptos.vue                # Gestion cryptos
│   │       ├── Clients.vue                # Gestion clients
│   │       ├── ClientDetails.vue          # Détails client
│   │       ├── Transactions.vue           # Gestion transactions
│   │       ├── TransactionDetails.vue     # Détails transaction
│   │       ├── Settings.vue               # Paramètres admin
│   │       └── _components*/
│   │           ├── _componentsOverview/
│   │           │   ├── StatsCards.vue
│   │           │   ├── ChartsSection.vue
│   │           │   └── composables/
│   │           │       └── useAdminStats.ts
│   │           ├── _componentsCryptos/
│   │           │   ├── CryptoList.vue
│   │           │   ├── CryptoForm.vue
│   │           │   └── CryptoStats.vue
│   │           ├── _componentsClients/
│   │           │   ├── ClientList.vue
│   │           │   └── RequestList.vue
│   │           └── ...
│   │
│   ├── components/
│   │   ├── CustomSidebar.vue              # Sidebar personnalisée
│   │   ├── ConfirmDialog.vue              # Dialog de confirmation
│   │   ├── DottedSurface.vue              # Fond animé
│   │   ├── ui/                            # Composants Shadcn-vue
│   │   │   ├── button/
│   │   │   ├── card/
│   │   │   ├── input/
│   │   │   ├── dialog/
│   │   │   ├── dropdown-menu/
│   │   │   ├── avatar/
│   │   │   ├── badge/
│   │   │   ├── tabs/
│   │   │   ├── table/
│   │   │   ├── alert/
│   │   │   ├── drawer/
│   │   │   ├── scroll-area/
│   │   │   ├── separator/
│   │   │   └── ...
│   │   └── ...
│   │
│   ├── services/
│   │   ├── api.ts                         # Client API centralisé Axios
│   │   ├── auth.ts                        # Logique authentification + localStorage
│   │   ├── crypto.ts                      # Service cryptomonnaies
│   │   ├── wallet.ts                      # Service portefeuille
│   │   ├── notification.ts                # Service notifications
│   │   └── ...
│   │
│   ├── router/
│   │   └── index.ts                       # Configuration routes + guards
│   │
│   ├── types/
│   │   └── models.ts                      # Interfaces TypeScript
│   │
│   ├── stores/                            # État global (optionnel Pinia)
│   │   ├── auth.ts
│   │   ├── wallet.ts
│   │   └── ...
│   │
│   ├── lib/
│   │   └── utils.ts                       # Utilitaires (format devises, etc.)
│   │
│   ├── App.vue                            # Composant racine
│   ├── main.ts                            # Point d'entrée
│   └── style.css                          # Styles globaux
│
├── public/
│   ├── assets/
│   │   ├── bitchest_logo.png             # Logo application
│   │   └── ...
│   └── ...
│
├── .env                                    # Variables d'environnement
├── .env.example                           # Template d'env
├── .gitignore
├── package.json                           # Dépendances npm
├── package-lock.json
├── vite.config.js                         # Configuration Vite
├── tailwind.config.js                     # Configuration Tailwind
├── postcss.config.cjs                     # Configuration PostCSS
├── tsconfig.json                          # Configuration TypeScript
├── index.html                             # HTML d'entrée
└── README.md
```

## 📄 Pages et Composants

### 🔐 SignIn.vue (pages/SignIn.vue)

Page d'authentification avec :
- Formulaire de connexion/inscription
- Validation en temps réel
- Gestion des erreurs
- Design glassmorphisme avec gradient
- Section features côté droit (desktop)
- Affichage/masquage du mot de passe

**Flux** :
1. Utilisateur saisit email et mot de passe
2. Frontend envoie `POST /api/v1/login`
3. Backend retourne token + user
4. Token stocké dans localStorage
5. Redirection vers /dashboard

### 📊 Dashboard.vue (pages/Dashboard.vue)

Layout principal avec :
- **Header Sticky** - Logo, rôle badge, wallet info, notifications, menu user
- **Sidebar Desktop** - Navigation persistante, portfolio summary
- **Drawer Mobile** - Menu navigation coulissant
- **Main Content** - Affiche le composant de la route actuelle
- **Notifications Panel** - Drawer notifications côté droit

**Composants Enfants** :
- CustomSidebar - Navigation dynamique selon le rôle
- Notifications avec marquage comme lues

### 👤 ProfilePortfolio.vue (pages/client/ProfilePortfolio.vue)

Gestion du profil utilisateur :
- Info de base (nom, email)
- Upload/modification photo de profil
- Upload/modification bannière
- Paramètres régionaux
- Changement de mot de passe

**Features** :
- Aperçu image avant upload
- Validation et gestion d'erreurs
- Mise à jour en temps réel
- Interface intuitive avec onglets

### 📋 Transactions.vue (pages/client/Transactions.vue)

Historique des transactions :
- Tableau des transactions avec pagination
- Filtrage par statut et date
- Recherche par crypto
- Vue détaillée de chaque transaction
- Export possible (optionnel)

### 📊 Admin Overview.vue (pages/admin/Overview.vue)

Dashboard administrateur :
- Cartes statistiques (utilisateurs, volume, etc.)
- Graphiques Chart.js (barres, donuts)
- Transactions récentes
- Top cryptomonnaies
- Boutons d'accès rapide

### 👥 Clients.vue (pages/admin/Clients.vue)

Gestion des clients :
- Onglets : Clients / Demandes de compte
- Vue grid ou liste selon préférence
- Recherche et filtrage en temps réel
- Boutons actions (voir détails, éditer, supprimer)
- Dialogs confirmation pour destructives

### 💱 Cryptos.vue (pages/admin/Cryptos.vue)

Gestion des cryptomonnaies :
- Liste paginée avec recherche
- Ajout de nouvelles cryptos
- Édition avec upload image
- Suppression avec confirmation
- Statistiques d'utilisation

### ⚙️ Settings.vue (pages/admin/Settings.vue)

Paramètres administrateur :
- **Profil** - Infos perso, mot de passe
- **Media** - Upload photo/bannière
- **Location** - Langue, timezone, formats
- **Advanced** - Changement ID admin (sécurisé)

## 🔧 Services API

### api.ts - Client HTTP Centralisé

Configuration Axios avec intercepteurs :

```typescript
// src/services/api.ts
const api = axios.create({
  baseURL: import.meta.env.VITE_API_URL,
  timeout: 30000,
  withCredentials: true,
})

// Intercepteur : ajouter token
api.interceptors.request.use((config) => {
  const token = localStorage.getItem('token')
  if (token) {
    config.headers.Authorization = `Bearer ${token}`
  }
  return config
})

// Intercepteur : gérer les erreurs 401
api.interceptors.response.use(
  (response) => response.data,
  (error) => {
    if (error.response?.status === 401) {
      // Rediriger vers login
      window.location.href = '/signin'
    }
    return Promise.reject(error)
  }
)
```

**Exports** :
```typescript
export default {
  auth: {
    login(credentials): Promise<...>
    logout(): Promise<void>
    profile(): Promise<User>
    updateProfile(data): Promise<User>
    changePassword(data): Promise<void>
    uploadProfilePicture(formData): Promise<...>
    uploadProfileBanner(formData): Promise<...>
    deleteProfilePicture(): Promise<void>
    deleteProfileBanner(): Promise<void>
    changeId(data): Promise<...>
  },
  crypto: {
    list(page?): Promise<Crypto[]>
    show(id): Promise<Crypto>
    history(id): Promise<...>
  },
  wallet: {
    list(): Promise<...>
    show(id): Promise<...>
    transaction(data): Promise<...>
  },
  admin: {
    clients: { list, show, create, update, delete }
    cryptos: { list, show, create, update, delete }
    transactions: { list, show, cancel }
    accountRequests: { list, approve, reject }
    stats(): Promise<AdminStats>
  },
  notifications: {
    list(): Promise<Notification[]>
    markAsRead(id): Promise<void>
  }
}
```

### auth.ts - Gestion Authentification

Logique centralisée d'authentification :

```typescript
export default {
  async login(email: string, password: string): Promise<void>
  async logout(): Promise<void>
  getToken(): string | null
  getUser(): User | null
  getRole(): string | null
  isAuthenticated(): boolean
  isAdmin(): boolean
}
```

Stockage dans localStorage :
- `token` - JWT Sanctum
- `user` - Infos utilisateur
- `role` - Rôle (CLIENT/ADMIN)

## 🔐 Authentification

### Flux d'Authentification

1. **Login** :
   ```typescript
   // SignIn.vue
   await auth.login(email, password)
   // Stocke token + user
   router.push('/dashboard')
   ```

2. **Token dans les Requêtes** :
   ```typescript
   // Intercepteur api.ts ajoute le header
   Authorization: Bearer {token}
   ```

3. **Revalidation** :
   ```typescript
   // Dashboard.vue mounted
   const user = await api.auth.profile()
   // Valide que le token est toujours valide
   ```

4. **Logout** :
   ```typescript
   await auth.logout()
   localStorage.clear()
   router.push('/signin')
   ```

### Route Guards

Protéger les pages avec guards :

```typescript
// router/index.ts
const router = createRouter({
  routes: [
    {
      path: '/dashboard',
      component: Dashboard,
      meta: { requiresAuth: true },
      beforeEnter: (to, from, next) => {
        if (auth.isAuthenticated()) {
          next()
        } else {
          next('/signin')
        }
      }
    },
    {
      path: '/admin',
      meta: { requiresAuth: true, requiresAdmin: true },
      beforeEnter: (to, from, next) => {
        if (auth.isAdmin()) {
          next()
        } else {
          next('/dashboard')
        }
      }
    }
  ]
})
```

## 📸 Uploads de Fichiers

### Upload Profile Picture

```typescript
// src/services/api.ts
uploadProfilePicture(formData: FormData): Promise<{
  path: string
  url: string
  user: User
}>
```

**Utilisation** :
```vue
<script setup>
const handleUpload = async (event: Event) => {
  const file = (event.target as HTMLInputElement).files?.[0]
  if (!file) return

  const formData = new FormData()
  formData.append('profile_picture', file)

  try {
    const response = await api.auth.uploadProfilePicture(formData)
    // response.data.url est l'URL publique
    userAvatar.value = response.data.url
  } catch (err) {
    console.error('Upload failed', err)
  }
}
</script>

<template>
  <input type="file" accept="image/*" @change="handleUpload" />
</template>
```

### Construction des URLs d'Images

```typescript
// Fonction helper dans Dashboard.vue
const getProfilePictureUrl = (profilePicture: string | null | undefined) => {
  if (!profilePicture) return null
  if (profilePicture.startsWith('http')) return profilePicture
  
  // Backend retourne chemin relatif → construire URL complète
  const baseUrl = import.meta.env.VITE_API_URL || 'http://localhost:8000'
  return `${baseUrl}/storage/${profilePicture}`
}
```

**Important** :
- Backend doit avoir `php artisan storage:link` exécuté
- Images servies depuis `/storage/` route publique
- URLs complètes retournées par l'API pour faciliter le frontend

## 🎨 Styles et Théming

### Couleurs BitChest

Palette définie dans `tailwind.config.js` :

```css
--color-bitchest-dark: #38618C    /* Bleu foncé - textes principaux */
--color-bitchest-blue: #35A7FF    /* Bleu clair - accents, boutons */
--color-bitchest-green: #01FF19   /* Vert vibrant - succès, actions positives */
--color-bitchest-red: #FF5964     /* Rouge - erreurs, annulation */
```

**Utilisation dans les classes** :
```vue
<button class="bg-[#35A7FF] text-white hover:bg-[#35A7FF]/90">
  Action
</button>

<div class="border-[#38618C] text-[#38618C]">
  Contenu
</div>
```

### Composants Shadcn-vue

Tous les composants UI sont dans `src/components/ui/` :

```vue
<script setup>
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
</script>

<template>
  <Card>
    <CardHeader>
      <CardTitle>Mon Titre</CardTitle>
    </CardHeader>
    <CardContent>
      <Input placeholder="Saisir..." />
      <Button>Envoyer</Button>
    </CardContent>
  </Card>
</template>
```

### Responsive Design

Breakpoints Tailwind :
- `sm`: 640px
- `md`: 768px
- `lg`: 1024px
- `xl`: 1280px

```vue
<template>
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
    <!-- 1 colonne mobile, 2 tablette, 4 desktop -->
  </div>
</template>
```

## 🚀 Build et Déploiement

### Build pour Production

```bash
npm run build
```

Cela génère :
- `dist/index.html` - HTML minifié
- `dist/assets/` - JS/CSS bundlés et minifiés
- Source maps (optionnel)

### Servir les Fichiers

**Avec Node.js (Express)** :
```javascript
import express from 'express'
const app = express()
app.use(express.static('dist'))
app.get('*', (req, res) => {
  res.sendFile('dist/index.html')
})
app.listen(3000)
```

**Avec Nginx** :
```nginx
server {
  listen 80;
  server_name bitchest.com;
  root /var/www/bitchest/frontend/dist;
  index index.html;

  location / {
    try_files $uri $uri/ /index.html;
  }

  location /api {
    proxy_pass http://localhost:8000;
  }
}
```

### Variables d'Environnement Production

Avant build :
```env
VITE_API_URL=https://api.bitchest.com
VITE_APP_NAME=BitChest
VITE_DEBUG=false
```

### Checklist Déploiement

- [ ] Configurer `VITE_API_URL` pour le domaine production
- [ ] Exécuter `npm run build`
- [ ] Vérifier `dist/` contient les fichiers compilés
- [ ] Copier `dist/` vers le serveur web
- [ ] Configurer le serveur pour les routes SPA (try_files)
- [ ] Configurer les CORS au backend
- [ ] Tester l'API depuis le domaine production
- [ ] Vérifier les uploads d'images
- [ ] Tester sur mobile et navigateurs

## 🐛 Dépannage

### Les Images Uploadées ne s'Affichent pas

**Cause 1** : Backend n'a pas le symlink storage
```bash
# Backend
php artisan storage:link
```

**Cause 2** : VITE_API_URL incorrect
```bash
# Frontend .env
VITE_API_URL=http://localhost:8000  # Doit correspondre à APP_URL backend
```

**Cause 3** : URLs malformées
```typescript
// Vérifier que l'API retourne des chemins cohérents
// et que le frontend construit les URLs correctement
const url = `${VITE_API_URL}/storage/${response.path}`
```

### Erreur CORS

**Symptôme** : "Access to XMLHttpRequest blocked by CORS policy"

**Solution** : Configurer CORS au backend
```php
// backend/config/cors.php
'allowed_origins' => ['http://localhost:5173', 'https://bitchest.com'],
```

### Token Expiré

**Symptôme** : Erreur 401 "Unauthenticated"

**Solution** : L'intercepteur redirige vers `/signin`
```typescript
// api.ts
if (error.response?.status === 401) {
  localStorage.removeItem('token')
  window.location.href = '/signin'
}
```

### Notifications non Mises à Jour

**Cause** : WebSocket/Polling non configuré

**Solution temporaire** :
```typescript
// Dashboard.vue
setInterval(() => {
  fetchNotifications()
}, 5000)  // Recharger toutes les 5 secondes
```

### Build Trop Volumineux

**Vérifier la taille** :
```bash
npm run build -- --report
```

**Optimisations** :
- Lazy load les routes
- Compresser les images
- Utiliser les CDN pour les librairies lourdes
- Tree-shaking inutilisé

## 📚 Ressources et Documentation

- **Vue 3** : https://vuejs.org
- **Vue Router** : https://router.vuejs.org
- **Vite** : https://vitejs.dev
- **Tailwind CSS** : https://tailwindcss.com
- **Shadcn-vue** : https://www.shadcn-vue.com
- **Axios** : https://axios-http.com
- **Chart.js** : https://www.chartjs.org
- **TypeScript** : https://www.typescriptlang.org

## 📝 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](../../LICENSE) pour plus de détails.

---

**Dernière mise à jour** : 2025  
**Version** : 1.0.0

