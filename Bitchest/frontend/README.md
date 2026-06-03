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

# BitChest — Frontend (présentation)

Table des matières
- Présentation
- Architecture et dossiers
- Fonctionnalités
- Prérequis
- Utilisation (cas d'usage)
- Endpoints API (résumé)

Description
Le frontend BitChest est une application SPA construite avec Vue 3 et Vite. Elle propose une interface utilisateur pour les clients (gestion de portefeuille, transactions, profil) et pour les administrateurs (gestion des clients, cryptomonnaies, transactions).

Architecture et dossiers (présentation)
- `src/pages` : pages par route (SignIn, Dashboard, pages client et admin).
- `src/components` : composants réutilisables et bibliothèques UI (Shadcn-vue).
- `src/services/api.ts` : client HTTP centralisé qui gère les appels vers l'API backend.
- `src/router` : configuration des routes et guards d'accès.
- `public/` : assets statiques et illustrations.

Fonctionnalités principales
- Authentification et gestion de session côté client.
- Tableau de bord interactif avec cartes et graphiques.
- Pages de profil et gestion des uploads (photo, bannière).
- Liste et détails des cryptomonnaies, affichage d'historique.
- Historique des transactions et actions d'achat/vente depuis l'interface.
- Interface d'administration : gestion des clients, cryptomonnaies et transactions.
- Design responsive, accessible et optimisé pour la performance (HMR, tree-shaking).

Prérequis
- Node.js 18+ et npm 9+.
- Un backend BitChest opérationnel (par défaut `http://localhost:8000`).

Utilisation (grands cas d'usage)
- Développement local : installer les dépendances, configurer ` .env` avec `VITE_API_URL`, puis démarrer le serveur Vite pour développement.
- Build production : créer le build optimisé et déployer le dossier de sortie sur un serveur web ou CDN.
- Intégration continue : configurer un pipeline pour builder le frontend et déployer automatiquement lors de merges en production.

Endpoints API (aperçu, une phrase chacun)
- POST /api/v1/login — endpoint d'authentification utilisé par la page de connexion.
- POST /api/v1/logout — termine la session côté serveur et invalide le token.
- GET /api/v1/profile — récupère les données du profil pour alimenter l'UI du dashboard.
- GET /api/v1/cryptos — liste les cryptomonnaies pour affichage et sélection.
- GET /api/v1/cryptos/{id}/history — récupère l'historique de prix pour les graphiques.
- POST /api/v1/wallets/transaction — envoie une requête d'achat ou de vente depuis l'interface.
- GET /api/v1/notifications — liste les notifications à afficher dans l'interface.

Pour des détails techniques et les fichiers sources, consulter `src/` et `src/services/api.ts`.

Dernière mise à jour : 2025
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

