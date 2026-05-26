# ⚛️ React - Applications Modernes

Bienvenue dans le répertoire des projets **React** ! Ici vous trouverez des applications web modernes et interactives développées avec React.

## 📁 Structure du Projet

### 1. **Contact Form** 📧
Une application simple de formulaire de contact avec validation et soumission.

#### Architecture
```
contact-form/
├── src/
│   ├── App.js (Composant principal)
│   ├── App.css (Styles App)
│   ├── App.test.js (Tests)
│   ├── contact_form.js (Composant formulaire)
│   ├── ContactForm.css (Styles formulaire)
│   ├── index.js (Entry point)
│   └── setupTests.js (Configuration tests)
│
├── public/
│   ├── index.html
│   └── manifest.json
│
└── package.json
```

#### Fonctionnalités
- ✅ Formulaire avec validation
- ✅ Gestion des états
- ✅ Messages d'erreur
- ✅ Soumission sécurisée
- ✅ Design responsive

---

### 2. **Movie Card** 🎬
Une plateforme complète de gestion de films avec authentification et système d'administration.

#### Architecture Complète
```
movie-card/
├── client/ (Frontend React)
│   ├── src/
│   │   ├── App.js (App principale)
│   │   ├── components/
│   │   │   ├── admin/ (Interface admin)
│   │   │   │   ├── action/ (Créer/Modifier films)
│   │   │   │   └── list/ (Lister films)
│   │   │   ├── client/ (Interface utilisateur)
│   │   │   │   ├── auth/ (Login/Register)
│   │   │   │   ├── movies/ (Affichage films)
│   │   │   │   ├── stars/ (Acteurs)
│   │   │   │   └── feedbacks/ (Avis)
│   │   │   └── layout/ (Navbar, Footer)
│   │   │
│   │   ├── routes/
│   │   │   └── AdminRoutes.jsx (Routes protégées)
│   │   │
│   │   ├── services/
│   │   │   ├── AuthContext.js (Gestion auth)
│   │   │   ├── movieService.jsx (API films)
│   │   │   ├── starService.jsx (API acteurs)
│   │   │   └── ProtectedRoute.js (Routes sécurisées)
│   │   │
│   │   └── styles/ (CSS)
│   │
│   ├── public/
│   └── package.json
│
└── server/ (Backend Node.js/Express)
    ├── config/
    │   ├── config.js (Configuration)
    │   ├── multerConfig.js (Upload fichiers)
    │   └── nodemailerConfig.js (Emails)
    │
    ├── models/ (Modèles MongoDB)
    │   ├── auth.js
    │   ├── movie.js
    │   ├── MovieStar.js
    │   └── Contact.js
    │
    ├── routes/ (API endpoints)
    │   ├── authRouter.js
    │   ├── movieRouter.js
    │   ├── MovieStarRouter.js
    │   └── contactRouter.js
    │
    ├── Middleware/
    │   ├── authenticateToken.js
    │   └── checkRole.js
    │
    ├── uploads/ (Dossier uploads)
    │   ├── movies/
    │   └── stars/
    │
    ├── server.js
    └── package.json
```

#### Fonctionnalités Principales
- ✅ **Authentification complète**:
  - Login/Register
  - JWT tokens
  - Profils utilisateurs
- ✅ **Gestion des films**:
  - CRUD complet
  - Upload d'images
  - Descriptions détaillées
- ✅ **Système d'acteurs**:
  - Lister acteurs
  - Associer aux films
- ✅ **Dashboard Admin**:
  - Gestion des contenus
  - Modération
  - Statistiques
- ✅ **Système de feedback**:
  - Avis utilisateurs
  - Système de notation
  - Commentaires
- ✅ **Contact & Support**:
  - Formulaire de contact
  - Emails automatiques

#### Stack
- Frontend: React, Axios, React Router
- Backend: Express, MongoDB, Mongoose
- Auth: JWT Tokens
- Upload: Multer
- Email: Nodemailer

---

### 3. **Pre-Flight Checklist** ✈️
Une application de gestion de listes de vérification avant vol avec authentification.

#### Architecture
```
pre-flight-checklist/
├── src/
│   ├── App.js (Composant principal)
│   ├── components/
│   │   ├── Auth.js (Authentification)
│   │   ├── login.js (Login)
│   │   ├── signup.js (Inscription)
│   │   ├── Dashboard.js (Tableau de bord)
│   │   ├── Checklists.js (Listes)
│   │   ├── Form.js (Créer checklist)
│   │   └── UpdateForm.js (Modifier)
│   │
│   ├── api/
│   │   └── axiosConfig.js (Configuration API)
│   │
│   └── styles/
│       └── signup-login.css
│
├── public/
│   └── index.html
│
├── tailwind.config.js (Configuration Tailwind)
├── postcss.config.js
└── package.json
```

#### Fonctionnalités
- ✅ Authentification sécurisée
- ✅ Créer des checklists personnalisées
- ✅ Marquer les éléments comme complétés
- ✅ Modifier et supprimer listes
- ✅ Dashboard intuitif
- ✅ Design responsive avec Tailwind

---

### 4. **Spinner** 🌀
Une application simple et élégante avec composants animés et boutons interactifs.

#### Architecture
```
spinner/
├── src/
│   ├── App.js (App principale)
│   ├── App.css (Styles)
│   ├── Spinner.js (Composant spinner)
│   ├── BtArrow.js (Boutons flèches)
│   └── index.js (Entry point)
│
├── public/
│   └── index.html
│
└── package.json
```

#### Fonctionnalités
- ✅ Animations fluides
- ✅ Composant spinner réutilisable
- ✅ Boutons interactifs
- ✅ Design minimaliste
- ✅ Performance optimisée

---

## 🛠️ Stack Technologique Global

### Frontend Core
- **React 18+** - Librairie UI
- **React Router v6** - Navigation
- **Axios** - HTTP client
- **Context API** - Gestion d'état
- **Hooks** - Fonctionnalités modernes

### Styling
- **CSS3** - Styles natifs
- **Tailwind CSS** (Checklist) - Framework CSS
- **Styled Components** (optionnel) - CSS-in-JS

### Backend (Movie Card)
- **Express.js** - Framework backend
- **MongoDB + Mongoose** - Base de données
- **JWT** - Authentification
- **Multer** - Upload fichiers
- **Nodemailer** - Emails

### Outils
- **Vite / Create React App** - Bundler
- **Jest** - Tests unitaires
- **ESLint** - Linting

---

## 📋 Prérequis

- **Node.js 16+** et npm
- **React knowledge** (composants, hooks)
- **Git** (optionnel)
- **Navigateur moderne**

---

## 🚀 Installation & Démarrage

### Contact Form

```bash
cd React/contact-form
npm install
npm start
# Ouvrir http://localhost:3000
```

### Movie Card

**Frontend**
```bash
cd React/movie-card/client
npm install
npm start
# http://localhost:3000
```

**Backend**
```bash
cd React/movie-card/server
npm install
npm start
# http://localhost:5000
```

### Pre-Flight Checklist

```bash
cd React/pre-flight-checklist
npm install
npm run dev
# http://localhost:5173 (Vite)
```

### Spinner

```bash
cd React/spinner
npm install
npm start
# http://localhost:3000
```

---

## 📚 Concepts Clés React

### Functional Components
```jsx
function MyComponent() {
  return <div>Hello World</div>;
}
```

### Hooks
```jsx
// useState
const [count, setCount] = useState(0);

// useEffect
useEffect(() => {
  // Code côté effet
}, [dependencies]);

// useContext
const value = useContext(MyContext);
```

### Props & State
```jsx
// Props
function Card({ title, description }) {
  return <div>{title}: {description}</div>;
}

// State
const [user, setUser] = useState(null);
```

### Event Handling
```jsx
function Button() {
  const handleClick = () => console.log('Clicked!');
  return <button onClick={handleClick}>Click</button>;
}
```

---

## 🔄 Workflows Typiques

### Movie Card Workflow
```
1. Utilisateur arrive sur le site
2. Non authentifié → Affichage films public
3. Clic Login → Page authentification
4. Login réussi → JWT token sauvegardé
5. Accès au dashboard
6. Admin → Gestion des films/acteurs
7. User → Voir films, laisser avis
```

### Checklist Workflow
```
1. Signup/Login
2. Dashboard affiche checklists
3. Créer nouvelle checklist
4. Ajouter éléments
5. Cocher éléments complétés
6. Sauvegarder
7. Consulter plus tard
```

---

## 📁 Configuration Importante

### Variables d'Environnement (.env)

```env
# Movie Card
REACT_APP_API_URL=http://localhost:5000
REACT_APP_JWT_SECRET=your-secret-key

# Pre-Flight Checklist
VITE_API_BASE_URL=http://localhost:3001
```

---

## 🧪 Tests

```bash
# Exécuter les tests
npm test

# Avec couverture
npm test -- --coverage

# Mode watch
npm test -- --watch
```

---

## 📱 Responsive Design

### Breakpoints Courants
```css
/* Mobile */
@media (max-width: 640px) { }

/* Tablet */
@media (max-width: 1024px) { }

/* Desktop */
@media (min-width: 1025px) { }
```

### Tailwind Prefixes
```jsx
<div className="w-full md:w-1/2 lg:w-1/3">
  Responsive content
</div>
```

---

## 🔐 Sécurité

- ✅ JWT tokens pour authentification
- ✅ Routes protégées
- ✅ Validation des données
- ✅ Protection CSRF
- ✅ XSS prevention
- ✅ HTTPS recommandé

---

## 🐛 Débogage

### React DevTools
```bash
# Chrome Extension
# Inspectez les composants et l'état
# F12 → Components/Profiler
```

### Console Logging
```javascript
console.log('Debug:', variable);
console.error('Erreur:', error);
console.table(dataArray);
```

### Network Tab
- Vérifier les requêtes API
- Voir les réponses
- Vérifier les tokens

---

## 📚 Ressources Utiles

- [React Docs](https://react.dev)
- [React Router](https://reactrouter.com)
- [Axios Documentation](https://axios-http.com)
- [Tailwind CSS](https://tailwindcss.com)
- [Express.js](https://expressjs.com)

---

## 📈 Prochaines Étapes

- [ ] Ajouter tests (Jest, React Testing Library)
- [ ] Améliorer performance (Code splitting, lazy loading)
- [ ] Ajouter PWA support
- [ ] Dark mode
- [ ] Multi-langue (i18n)
- [ ] SEO optimisation
- [ ] Déployer sur Vercel/Netlify

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement React

---

**Dernière mise à jour**: 2026  
**Version**: 1.0.0
