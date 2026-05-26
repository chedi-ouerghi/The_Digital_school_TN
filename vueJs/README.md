# 💚 Vue.js - Framework Progressif

Bienvenue dans le répertoire des projets **Vue.js** ! Ici vous trouverez des applications web modernes développées avec le framework progressif Vue.js.

## 📁 Structure du Projet

### 1. **Frontend Gallery** 🖼️
Une galerie interactive de films avec authentification et recherche avancée.

#### Architecture Complète
```
Frontend_Gallery/
├── src/
│   ├── App.vue (Composant racine)
│   ├── App2.vue (Alternative App)
│   ├── main.js (Entry point)
│   │
│   ├── components/
│   │   ├── Navbar.vue (Navigation)
│   │   ├── Avatar.vue (Profil utilisateur)
│   │   ├── FooterSection.vue (Pied de page)
│   │   ├── HelloWorld.vue (Accueil)
│   │   │
│   │   ├── inscription/ (Authentification)
│   │   │   ├── inscri.vue (Composant inscription)
│   │   │   ├── index.html
│   │   │   └── style.css
│   │   │
│   │   └── movie/ (Galerie films)
│   │       ├── MovieCard.vue (Carte film)
│   │       └── SearchBar.vue (Recherche)
│   │
│   ├── views/ (Pages principales)
│   │   ├── Home.vue (Accueil)
│   │   ├── HomeView.vue (Vue accueil alternative)
│   │   ├── Gallery.vue (Galerie principale)
│   │   ├── AboutView.vue (À propos)
│   │   ├── Login.vue (Login)
│   │   ├── gallery.css
│   │   └── loginForm.css
│   │
│   ├── router/
│   │   └── index.js (Configuration routage)
│   │
│   └── assets/
│       └── responsive.css (Styles responsive)
│
├── public/
│   └── index.html (HTML principal)
│
├── babel.config.js
├── vue.config.js (Configuration Vue)
├── jsconfig.json
├── package.json
└── README.md
```

#### Fonctionnalités Principales
- ✅ **Authentification**:
  - Login/Inscription
  - Gestion d'utilisateur
  - Profils
- ✅ **Galerie de films**:
  - Affichage en grille
  - Cartes films interactives
  - Détails films
- ✅ **Recherche avancée**:
  - Filtrer par genre
  - Filtrer par titre
  - Recherche en temps réel
- ✅ **Navigation fluide**:
  - Vue Router
  - Transitions animées
  - Navigation par onglets
- ✅ **Design responsive**:
  - Mobile first
  - Adapté tablette/desktop
  - CSS flexible
- ✅ **Interface moderne**:
  - Composants réutilisables
  - Avatar utilisateur
  - Navbar dynamique

---

### 2. **TodoList** ✅
Une application complète de gestion de tâches avec Vue.js et authentification.

#### Architecture
```
TodoLists/
├── src/
│   ├── App.vue (Composant principal)
│   ├── main.js (Entry point)
│   ├── style.css (Styles globaux)
│   │
│   ├── components/
│   │   ├── Login.vue (Authentification)
│   │   ├── loginForm.css (Styles login)
│   │   ├── Todo.vue (Composant todo)
│   │   ├── TodoList.vue (Liste complète)
│   │   └── TodoCounter.vue (Compteur tâches)
│   │
│   ├── router/
│   │   └── index.js (Routes)
│   │
│   └── assets/ (Ressources)
│
├── public/
│   └── index.html
│
├── babel.config.js
├── vue.config.js
├── jsconfig.json
├── package.json
└── README.md
```

#### Fonctionnalités Principales
- ✅ **Authentification**:
  - Login sécurisé
  - Gestion de session
  - Profil utilisateur
- ✅ **Gestion complète des tâches**:
  - Créer tâche
  - Modifier tâche
  - Supprimer tâche
  - Marquer comme complétée
- ✅ **Filtrage**:
  - Afficher toutes les tâches
  - Afficher complétées
  - Afficher non-complétées
- ✅ **Compteur de tâches**:
  - Total tâches
  - Tâches complétées
  - Tâches restantes
- ✅ **Persistance des données**:
  - localStorage
  - Synchronisation
- ✅ **Interface intuitive**:
  - Design simple
  - Responsive
  - Feedback utilisateur

---

## 🛠️ Stack Technologique

### Core Vue.js
- **Vue.js 3** - Framework progressif
- **Vue Router** - Routage côté client
- **Vue CLI** - Outils de développement
- **Composition API** (optionnel) - Logique réutilisable

### Styling
- **CSS3** - Styles natifs
- **Responsive Design** - Mobile first
- **Vue Scoped Styles** - Encapsulation CSS

### Build & Development
- **Webpack** (Vue CLI) - Bundler
- **Babel** - Transpilation JS
- **npm/yarn** - Gestionnaire dépendances

### Outils
- **Vue DevTools** - Debugging
- **ESLint** - Linting de code

---

## 📋 Prérequis

- **Node.js 14+** et npm
- **Vue.js 3 knowledge** (composants, directives)
- **JavaScript ES6+**
- **Navigateur moderne**
- **Git** (optionnel)

---

## 🚀 Installation & Démarrage

### Frontend Gallery

```bash
# 1. Naviguer dans le projet
cd vueJs/Frontend_Gallery

# 2. Installer les dépendances
npm install

# 3. Démarrer le serveur de développement
npm run serve

# 4. Ouvrir dans le navigateur
# http://localhost:8080
```

### TodoList

```bash
# 1. Naviguer dans le projet
cd vueJs/TodoLists

# 2. Installer les dépendances
npm install

# 3. Démarrer le serveur de développement
npm run serve

# 4. Ouvrir dans le navigateur
# http://localhost:8080
```

---

## 📚 Concepts Clés Vue.js

### Single File Components (.vue)

```vue
<template>
  <div>
    <h1>{{ message }}</h1>
    <button @click="count++">{{ count }}</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      message: 'Hello Vue',
      count: 0
    }
  }
}
</script>

<style scoped>
h1 {
  color: blue;
}
</style>
```

### Directives Principales

```vue
<!-- Interpolation -->
{{ message }}

<!-- Binding -->
<img :src="imageSrc" />

<!-- Events -->
<button @click="handleClick">Click</button>

<!-- Conditions -->
<div v-if="show">Visible</div>

<!-- Boucles -->
<div v-for="item in items" :key="item.id">
  {{ item.name }}
</div>

<!-- Deux-way binding -->
<input v-model="message" />
```

### Props et Emit

```vue
<!-- Parent -->
<ChildComponent :title="parentTitle" @child-event="handleEvent" />

<!-- Child Component -->
<script>
export default {
  props: ['title'],
  emits: ['child-event'],
  methods: {
    notifyParent() {
      this.$emit('child-event', data);
    }
  }
}
</script>
```

### Vue Router

```javascript
// router/index.js
import { createRouter, createWebHistory } from 'vue-router'
import Home from '../views/Home.vue'

const routes = [
  {
    path: '/',
    component: Home
  },
  {
    path: '/gallery',
    component: () => import('../views/Gallery.vue')
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

export default router
```

---

## 🎨 Structure d'une Application Vue.js

### Hiérarchie Composants
```
App.vue (Racine)
├── Navbar.vue
├── Router-View
│   ├── Home.vue
│   ├── Gallery.vue
│   └── Login.vue
└── FooterSection.vue
```

### Flux de Données
```
Données → Composant → Template → DOM
  ↑                              ↓
  └──────── @click Event ────────
```

---

## 📱 Responsive Design

### CSS Responsive

```css
/* Mobile */
@media (max-width: 640px) {
  .gallery {
    grid-template-columns: 1fr;
  }
}

/* Tablet */
@media (max-width: 1024px) {
  .gallery {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Desktop */
@media (min-width: 1025px) {
  .gallery {
    grid-template-columns: repeat(3, 1fr);
  }
}
```

### Vue Computed Properties

```javascript
computed: {
  filteredMovies() {
    return this.movies.filter(movie =>
      movie.title.toLowerCase().includes(this.searchQuery)
    )
  }
}
```

---

## 🔄 Workflows Typiques

### Frontend Gallery Workflow
```
1. Utilisateur arrive sur l'accueil
2. Navigation vers Galerie
3. Voir tous les films
4. Clic recherche/filtre
5. Résultats filtrés affichés
6. Clic sur film → Détails
7. Option login/profil
```

### TodoList Workflow
```
1. Login page
2. Créer nouvelle tâche (input + bouton)
3. Ajouter à la liste
4. Voir tâches affichées
5. Cocher comme complétée
6. Voir compteur mis à jour
7. Filtrer par statut
8. Supprimer tâche
```

---

## 💾 Gestion de l'État

### Data et Computed

```javascript
export default {
  data() {
    return {
      todos: [],
      filter: 'all'
    }
  },
  computed: {
    completedTodos() {
      return this.todos.filter(t => t.completed)
    },
    activeTodos() {
      return this.todos.filter(t => !t.completed)
    }
  }
}
```

### Watchers

```javascript
watch: {
  searchQuery(newVal) {
    this.filteredResults = this.search(newVal)
  }
}
```

---

## 📚 Commandes Essentielles

```bash
# Développement
npm run serve              # Démarrer serveur dev

# Production
npm run build             # Compiler pour production

# Linting
npm run lint              # Vérifier le code

# Création de composants (avec Vue CLI)
vue create project-name   # Créer nouveau projet
vue add router            # Ajouter Vue Router
```

---

## 🧪 Tests

```bash
# Exécuter les tests (si configurés)
npm run test:unit

# Avec couverture
npm run test:unit -- --coverage
```

---

## 🔐 Sécurité

- ✅ Authentification via tokens
- ✅ Gestion sécurisée des sessions
- ✅ Validation des entrées
- ✅ XSS Protection (Vue auto-escape)
- ✅ CSRF tokens
- ✅ HTTPS recommandé

---

## 📁 Variables d'Environnement

Créer `.env.local`:
```env
VUE_APP_API_URL=http://localhost:3000
VUE_APP_API_KEY=your-api-key
```

Accès dans le code:
```javascript
const apiUrl = process.env.VUE_APP_API_URL
```

---

## 🐛 Débogage

### Vue DevTools
```bash
# Chrome Extension
# F12 → Vue tab
# Inspecter composants et état
```

### Console Logging
```javascript
console.log('Debug:', this.movies)
this.$forceUpdate() // Force re-render
```

---

## 📊 Performance

### Code Splitting avec Router

```javascript
// Lazy loading des routes
component: () => import('../views/Gallery.vue')
```

### Optimisation Images

```vue
<img :src="imageSrc" loading="lazy" alt="Film" />
```

---

## 📚 Ressources Utiles

- [Vue.js Official Docs](https://vuejs.org/)
- [Vue Router Guide](https://router.vuejs.org/)
- [Vue CLI Documentation](https://cli.vuejs.org/)
- [Vue Best Practices](https://vuejs.org/guide/best-practices/)
- [Vue Awesome](https://github.com/vuejs/awesome-vue)

---

## 📈 Prochaines Étapes

- [ ] Ajouter Vuex/Pinia (gestion d'état centralisée)
- [ ] Intégrer backend API
- [ ] Ajouter authentification JWT
- [ ] Tests automatisés
- [ ] PWA support
- [ ] Dark mode
- [ ] i18n (multi-langue)
- [ ] Déployer sur Vercel/Netlify

---

## ⚙️ Configuration Recommandée

### ESLint + Prettier

```bash
npm install --save-dev @vue/eslint-config-prettier
```

### Tailwind CSS (optionnel)

```bash
npm install -D tailwindcss postcss autoprefixer
npx tailwindcss init -p
```

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement Vue.js

---

**Dernière mise à jour**: 2026  
**Version**: 3.0.0
