# 📱 React Native - Développement Mobile Moderne

Bienvenue dans le répertoire des projets **React Native** ! Ici vous trouverez des applications mobiles modernes développées avec React Native et Expo.

## 📁 Structure du Projet

### **E-commerce Mobile** 🛍️
Une application mobile e-commerce complète avec catalogue de produits, panier et paiement.

#### Architecture Complète
```
e_commerce_mobile/
├── app/ (Routes et écrans - Expo Router)
│   ├── _layout.tsx (Layout principal)
│   ├── index.tsx (Accueil)
│   ├── products.tsx (Liste produits)
│   ├── cart.tsx (Panier)
│   ├── (tabs)/ (Navigation par onglets)
│   │   ├── _layout.tsx
│   │   ├── index.tsx
│   │   ├── products.tsx
│   │   └── cart.tsx
│   └── product/[id].tsx (Détail produit)
│
├── components/ (Composants réutilisables)
│   ├── AnimatedText.tsx (Texte animé)
│   ├── Button.tsx (Boutons personnalisés)
│   ├── HeroSection.tsx (Section héros)
│   ├── Icon.tsx (Icônes)
│   ├── ProductCard.tsx (Carte produit)
│   └── ...
│
├── context/ (Gestion d'état globale)
│   └── CartContext.tsx (État du panier)
│
├── styles/ (Thèmes et styles)
│   ├── commonStyles.ts (Styles communs)
│   └── luxuryStyles.ts (Thème luxe)
│
├── utils/ (Utilitaires)
│   └── errorLogger.ts (Gestion erreurs)
│
├── assets/ (Images, polices)
│   └── images/ (Images de l'app)
│
├── public/ (Fichiers statiques web)
│   ├── index.html
│   └── manifest.json
│
└── Configuration
    ├── app.json (Configuration Expo)
    ├── babel.config.js
    ├── metro.config.js
    ├── package.json
    ├── tsconfig.json
    ├── eas.json (Configuration EAS Build)
    └── .eslintrc.js
```

#### Fonctionnalités Principales
- ✅ **Catalogue de produits**:
  - Liste complète
  - Détail produit
  - Images et descriptions
- ✅ **Système de panier**:
  - Ajouter/Supprimer
  - Modifier quantité
  - Calcul total
- ✅ **Navigation fluide**:
  - Expo Router (routing moderne)
  - Navigation par onglets
  - Stack navigation
- ✅ **Design responsive**:
  - Adapté pour tous les écrans
  - Thème luxe intégré
  - Animations fluides
- ✅ **Gestion d'état**:
  - Context API
  - État du panier persistant
  - Thème global
- ✅ **Authentification** (pré-configurée)
- ✅ **Gestion erreurs robuste**
- ✅ **Performance optimisée**

---

## 🛠️ Stack Technologique

### Frontend Mobile
- **React Native** - Framework mobile multiplateforme
- **Expo** - Plateforme de développement React Native
- **Expo Router** - Routing moderne (Next.js-like)
- **TypeScript** - Typage statique
- **React Context** - Gestion d'état

### Styling
- **React Native StyleSheet** - Styles natifs
- **Tailwind CSS** (optionnel) - Styles utilitaires
- **Thèmes personnalisés** - Luxe et commun

### Build & Deploy
- **Expo CLI** - Outils de développement
- **EAS Build** - Build cloud
- **Metro** - Bundler React Native

### Outils Additionnels
- **ESLint** - Linting de code
- **Babel** - Transpilation JS
- **Node.js** - Runtime

---

## 📋 Prérequis

- **Node.js 16.0+** et npm/yarn
- **Expo Account** (gratuit sur [expo.dev](https://expo.dev))
- **Expo Go App** (sur iOS/Android, pour développement)
- **VS Code** avec extension Expo
- **TypeScript** knowledge (optionnel mais recommandé)

---

## 🚀 Installation & Démarrage

### Installation Initiale

```bash
# 1. Cloner ou extraire le projet
cd e_commerce_mobile

# 2. Installer les dépendances
npm install
# ou
yarn install

# 3. Installer Expo CLI (si pas fait)
npm install -g expo-cli

# 4. Démarrer le serveur de développement
npm start
# ou
expo start
```

### Lancer sur Appareil/Émulateur

```bash
# Sur le terminal qui affiche le QR code :
# i - Pour iOS
# a - Pour Android
# w - Pour web

# Ou scanner le QR code avec Expo Go (iOS/Android)
```

### Build Production

```bash
# Build pour iOS
eas build --platform ios

# Build pour Android
eas build --platform android

# Build pour web
npm run web
```

---

## 📊 Architecture & Patterns

### Expo Router (Routing)
```
app/ représente les routes:
- app/index.tsx → /
- app/products.tsx → /products
- app/cart.tsx → /cart
- app/product/[id].tsx → /product/:id
```

### Context API (Gestion d'État)
```typescript
// CartContext.tsx
const CartContext = createContext();

// Utilisation dans les composants
const { cart, addToCart } = useContext(CartContext);
```

### Composants TypeScript
```typescript
interface ProductCardProps {
  id: number;
  title: string;
  price: number;
  image: string;
}

const ProductCard: React.FC<ProductCardProps> = (props) => {
  // Composant
};
```

---

## 📱 Commandes Essentielles

```bash
# Développement
npm start              # Démarrer le serveur Expo
expo start --web       # Démarrer pour web

# Production
npm run build          # Build pour production
expo publish          # Publier sur Expo

# Configuration
expo prebuild         # Générer les dossiers natifs
expo eject           # Éjecter de Expo (avancé)

# Debugging
expo doctor          # Vérifier la configuration
npm run lint         # Linter le code
```

---

## 🎨 Customization

### Ajouter une Nouvelle Page

```typescript
// app/mynewpage.tsx
import { View, Text } from 'react-native';

export default function MyNewPage() {
  return (
    <View>
      <Text>Ma nouvelle page</Text>
    </View>
  );
}
```

### Créer un Composant

```typescript
// components/MyComponent.tsx
import React from 'react';
import { View, Text } from 'react-native';

interface MyComponentProps {
  title: string;
}

const MyComponent: React.FC<MyComponentProps> = ({ title }) => {
  return (
    <View>
      <Text>{title}</Text>
    </View>
  );
};

export default MyComponent;
```

### Utiliser le Context

```typescript
// Dans un composant
import { useContext } from 'react';
import { CartContext } from '../context/CartContext';

export default function MyComponent() {
  const { cart, addToCart } = useContext(CartContext);
  
  return (
    // JSX
  );
}
```

---

## 📦 Dépendances Principales

```json
{
  "dependencies": {
    "react": "^18.0",
    "react-native": "^0.73",
    "expo": "^50.0",
    "expo-router": "^2.0",
    "typescript": "^5.0"
  },
  "devDependencies": {
    "@types/react": "^18.0",
    "eslint": "^8.0"
  }
}
```

---

## 🔐 Sécurité

- ✅ Validation des données
- ✅ Gestion sécurisée du contexte
- ✅ Erreur handling robuste
- ✅ HTTPS recommandé
- ✅ Pas de données sensibles en hardcoded
- ✅ Variables d'environnement pour secrets

---

## 📝 Fichiers Importants

### `app.json`
Configuration Expo et métadonnées de l'app
```json
{
  "expo": {
    "name": "E-Commerce",
    "slug": "ecommerce-mobile",
    "version": "1.0.0"
  }
}
```

### `eas.json`
Configuration pour les builds EAS
```json
{
  "build": {
    "production": {
      "ios": { "buildType": "archive" },
      "android": { "buildType": "apk" }
    }
  }
}
```

---

## 🧪 Testing

```bash
# Lancer les tests (si configurés)
npm test

# Avec couverture
npm test -- --coverage
```

---

## 🐛 Débogage

### Expo DevTools
```bash
# Les DevTools s'ouvrent automatiquement
# Ou appuyez sur 'd' dans le terminal
```

### React DevTools
```bash
# Appuyez sur 'm' pour le menu Expo
# Sélectionnez "React DevTools"
```

### Logs
```javascript
// Dans le code
console.log('Debug:', variable);
console.error('Erreur:', err);

// Voir dans le terminal Expo
```

---

## 📱 Responsive Design

### Breakpoints
```typescript
import { useWindowDimensions } from 'react-native';

const { width, height } = useWindowDimensions();

const isSmallScreen = width < 600;
```

### Thèmes
```typescript
// commonStyles.ts - Styles standard
// luxuryStyles.ts - Thème premium

// Appliquer le thème
import { luxuryStyles } from './styles/luxuryStyles';
```

---

## 🚨 Erreurs Courantes

### "Metro Bundler Error"
```bash
# Clear cache
npm start -- --clear

# Ou
expo start -c
```

### "Cannot connect to Expo"
```bash
# Vérifier la connexion réseau
# Redémarrer le serveur
# Vérifier firewall
```

### "Module not found"
```bash
# Réinstaller dépendances
rm -rf node_modules
npm install
```

---

## 📚 Ressources Utiles

- [React Native Docs](https://reactnative.dev/)
- [Expo Documentation](https://docs.expo.dev/)
- [Expo Router Guide](https://docs.expo.dev/routing/introduction/)
- [TypeScript & React Native](https://www.typescriptlang.org/)
- [EAS Build Docs](https://docs.expo.dev/build/introduction/)

---

## 📈 Prochaines Étapes

- [ ] Intégrer backend API
- [ ] Ajouter authentification JWT
- [ ] Implémenter paiement (Stripe, PayPal)
- [ ] Notifications push
- [ ] Animations avancées
- [ ] Offline support
- [ ] Analytics
- [ ] Déployer sur App Store et Play Store

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement Mobile React Native

---

## 📞 Support

Pour questions ou problèmes:
1. Consultez la doc Expo
2. Vérifiez les logs d'erreur
3. Contactez votre instructeur

---

**Dernière mise à jour**: 2026  
**Version**: 1.0.0  
**License**: MIT
