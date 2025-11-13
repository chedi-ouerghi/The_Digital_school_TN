# SCÉNARIO COMPLET - BITCHEST PLATFORM

## 🎯 OBJECTIF
Ce document présente le parcours utilisateur complet de la plateforme BitChest, couvrant la connexion, le dashboard administrateur et le dashboard client.

---

## 🔐 1. PAGE DE CONNEXION (SignIn)

### 📝 Contexte
- Page d'accueil pour l'authentification des utilisateurs
- Design moderne avec deux sections : formulaire de connexion et présentation de la plateforme

### 🎨 Design Visual
- **Section gauche** : Formulaire sur fond blanc avec logo BitChest
- **Section droite** : Présentation avec effets de gradient et animations
- **Couleurs principales** : Bleu (#38618C), Vert (#01FF19), Blanc

### 🔄 Processus de Connexion

#### Étape 1 : Accès à la page
```
1. L'utilisateur accède à l'URL /signin
2. La page s'affiche avec le formulaire de connexion à gauche
3. Section droite présente les fonctionnalités de la plateforme
```

#### Étape 2 : Saisie des identifiants
```
1. Champ Email : Saisie de l'adresse email
2. Champ Mot de passe : Saisie sécurisée avec icône œil pour afficher/masquer
3. Bouton "Sign In" : Validation du formulaire
```

#### Étape 3 : Authentification
```
1. Validation côté client des formats email/mot de passe
2. Envoi des données au backend
3. Redirection vers le dashboard approprié (admin ou client)
4. Message de succès "Login successful! Redirecting..."
```

#### Étape 4 : Gestion des erreurs
```
- Email invalide : Message d'erreur en rouge
- Mot de passe incorrect : "Invalid credentials. Please try again."
- Compte non vérifié : Message approprié
```

---

## 👨‍💼 2. DASHBOARD ADMINISTRATEUR

### 📊 Vue d'ensemble (Overview)

#### 🎯 Fonctionnalités principales
- **Statistiques globales** : Total clients, volume de transactions, revenus estimés
- **Graphiques interactifs** : Top cryptos tradées, répartition des volumes
- **Tableaux de bord** : Transactions récentes, cryptos populaires

#### 🎨 Interface
- **En-tête** : Logo BitChest + boutons de navigation rapides
- **Cartes de statistiques** : 4 cartes principales avec icônes et chiffres clés
- **Graphiques** : Bar chart et doughnut chart pour la visualisation des données
- **Tableaux** : Listes des cryptos les plus tradées et dernières transactions

#### 🔄 Workflow d'utilisation
```
1. L'administrateur accède au dashboard via /dashboard/admin/overview
2. Visualisation des statistiques globales en temps réel
3. Navigation vers les sections via le menu latéral ou les boutons rapides
4. Accès aux détails via les liens cliquables
```

### 👥 Gestion des Clients (Clients)

#### 🎯 Fonctionnalités
- **Liste des clients** : Vue grille ou liste avec filtres
- **Création de comptes** : Formulaire d'ajout de nouveaux clients
- **Édition** : Modification des informations client
- **Suppression** : Confirmation avant suppression
- **Demandes de compte** : Gestion des demandes en attente

#### 🎨 Interface
- **Onglets** : "Clients" et "Account Requests"
- **Recherche** : Par nom ou email
- **Filtres** : Par statut (vérifié/non vérifié)
- **Actions rapides** : Éditer, supprimer, approuver

#### 🔄 Processus de gestion
```
1. Vue liste des clients avec statistiques (total, solde, vérifiés)
2. Recherche et filtrage des clients
3. Création : Bouton "+ New Client" → Formulaire → Validation
4. Édition : Clic sur client → Modification → Sauvegarde
5. Suppression : Confirmation via dialogue → Suppression
6. Demandes : Liste des demandes → Bouton "Approve"
```

### 💎 Gestion des Cryptomonnaies (Cryptos)

#### 🎯 Fonctionnalités
- **Catalogue des cryptos** : Liste avec prix, variations, market cap
- **Ajout de cryptos** : Intégration via CoinGecko API
- **Modification** : Édition des informations et images
- **Suppression** : Retrait du catalogue
- **Détails** : Vue approfondie avec graphiques

#### 🎨 Interface
- **Vue grille/liste** : Switch entre les deux modes
- **Cartes crypto** : Logo, nom, symbole, prix, variation
- **Formulaire d'ajout** : ID CoinGecko + image optionnelle
- **Graphiques** : Historique des prix sur 30 jours

#### 🔄 Workflow
```
1. Vue catalogue avec tri par prix/nom/variation
2. Ajout : Bouton "+ Ajouter une Crypto" → ID CoinGecko → Validation
3. Édition : Clic sur crypto → Modification → Sauvegarde
4. Détails : Accès à la page détaillée avec historique
5. Suppression : Confirmation → Suppression définitive
```

### 📋 Gestion des Transactions (Transactions)

#### 🎯 Fonctionnalités
- **Historique complet** : Toutes les transactions de la plateforme
- **Filtres avancés** : Par client, crypto, statut, date
- **Vue détaillée** : Informations complètes sur chaque transaction
- **Annulation** : Possibilité d'annuler des transactions
- **Statistiques** : Volume total, transactions complétées/annulées

#### 🎨 Interface
- **Tableau récapitulatif** : 4 cartes de statistiques
- **Table/Liste** : Deux modes d'affichage
- **Filtres** : Recherche, statut (toutes/complétées/annulées)
- **Actions** : Détails, annulation

#### 🔄 Processus
```
1. Vue tableau avec pagination
2. Recherche par client ou crypto
3. Filtrage par statut
4. Détails : Clic sur transaction → Page détaillée
5. Annulation : Bouton "Annuler" → Raison → Confirmation
```

### 🔍 Détails Client (ClientDetails)

#### 🎯 Contenu
- **Informations client** : Avatar, nom, email, solde, statut
- **Statistiques personnelles** : Total transactions, achats, ventes
- **Graphiques** : Répartition par crypto, achats vs ventes
- **Historique transactions** : Liste chronologique

#### 🎨 Présentation
- **En-tête client** : Photo de profil, informations principales
- **Cartes de stats** : 4 métriques clés
- **Graphiques** : Bar chart et doughnut chart
- **Tableau transactions** : 5 dernières transactions avec pagination

---

## 👤 3. DASHBOARD CLIENT

### 📈 Vue d'ensemble (Overview)

#### 🎯 Fonctionnalités
- **Portfolio personnel** : Valeur totale, variation 24h
- **Notifications** : Système de notifications en temps réel
- **Accès rapide** : Navigation vers portfolio, cryptos, transactions

#### 🎨 Interface
- **Sidebar** : Menu de navigation personnalisé
- **En-tête** : Logo, notifications, profil utilisateur
- **Cartes principales** : Aperçu du portfolio
- **Notifications drawer** : Panneau latéral dédié

#### 🔄 Utilisation
```
1. Le client accède à son dashboard via /dashboard/overview
2. Visualisation de son portfolio et ses performances
3. Gestion des notifications (marquer comme lu, tout lire)
4. Navigation vers les différentes sections
```

### 💰 Portfolio Personnel

#### 🎯 Fonctionnalités
- **Vue d'ensemble** : Total des investissements, plus-values/moins-values
- **Détails par crypto** : Quantités détenues, valeurs actuelles
- **Graphiques** : Évolution du portfolio, répartition des actifs
- **Historique** : Transactions passées liées au portfolio

### 💱 Marché des Cryptomonnaies

#### 🎯 Fonctionnalités
- **Catalogue complet** : Toutes les cryptos disponibles
- **Prix en temps réel** : Cours actualisés depuis CoinGecko
- **Graphiques** : Évolution des prix, analyses techniques
- **Achat/Vente** : Interface de trading intuitive

### 📊 Historique des Transactions

#### 🎯 Fonctionnalités
- **Journal complet** : Toutes les opérations effectuées
- **Filtres** : Par date, type (achat/vente), crypto
- **Détails** : Informations détaillées sur chaque transaction
- **Export** : Possibilité d'exporter l'historique

### 👤 Profil Utilisateur

#### 🎯 Fonctionnalités
- **Informations personnelles** : Nom, email, préférences
- **Sécurité** : Changement de mot de passe, 2FA
- **Paramètres** : Notifications, langue, devise
- **Support** : Accès à l'aide et au support client

---

## 🎨 ÉLÉMENTS DE DESIGN UNIFIÉS

### 🌈 Palette de Couleurs
- **Primaire** : #38618C (Bleu profond)
- **Secondaire** : #35A7FF (Bleu clair)
- **Succès** : #01FF19 (Vert vif)
- **Erreur** : #FF5964 (Rouge corail)
- **Neutres** : Gris (#F3F4F6, #E5E7EB, #6B7280)

### 📱 Responsive Design
- **Desktop** : Layout complet avec sidebar
- **Tablette** : Menu hamburger, grilles adaptées
- **Mobile** : Interface optimisée, swipe gestures

### 🔄 Animations et Transitions
- **Chargement** : Spinners et skeletons
- **Transitions** : Fade in/out, slide
- **Hover effects** : Survol des cartes et boutons
- **Micro-interactions** : Feedback visuel immédiat

---

## 📸 RECOMMANDATIONS POUR LES CAPTURES D'ÉCRAN

### 🖼️ Pages Clés à Capturer
1. **Login** : Formulaire avec les deux sections
2. **Admin Overview** : Dashboard complet avec graphiques
3. **Admin Clients** : Liste des clients en mode grille
4. **Admin Cryptos** : Catalogue des cryptomonnaies
5. **Admin Transactions** : Tableau des transactions
6. **Client Overview** : Dashboard client avec notifications
7. **Client Portfolio** : Vue du portfolio personnel
8. **Client Cryptos** : Marché des cryptomonnaies

### 💡 Conseils de Présentation
- **Utiliser des données réalistes** : Montrez des chiffres crédibles
- **Mettre en évidence les interactions** : Survol, clics, transitions
- **Montrer les états** : Chargement, succès, erreurs
- **Présenter la navigation** : Menu, boutons, liens
- **Inclure des graphiques** : Charts et visualisations

---

## ✅ CHECKLIST DE VALIDATION

### 🔐 Authentification
- [ ] Connexion réussie avec bons identifiants
- [ ] Gestion des erreurs d'authentification
- [ ] Redirection appropriée selon le rôle
- [ ] Session sécurisée et persistante

### 👨‍💼 Fonctionnalités Admin
- [ ] Création et gestion des clients
- [ ] Ajout et modification des cryptos
- [ ] Visualisation des transactions
- [ ] Accès aux statistiques détaillées

### 👤 Fonctionnalités Client
- [ ] Consultation du portfolio
- [ ] Achat/vente de cryptomonnaies
- [ ] Historique des transactions
- [ ] Gestion du profil et notifications

### 🎨 Interface Utilisateur
- [ ] Design cohérent sur toutes les pages
- [ ] Navigation intuitive et fluide
- [ ] Feedback visuel pour toutes les actions
- [ ] Accessibilité et responsive design

---

*Ce scénario constitue une base complète pour votre présentation Canva. N'hésitez pas à l'adapter selon vos besoins spécifiques et à ajouter des captures d'écran de votre application fonctionnelle.*