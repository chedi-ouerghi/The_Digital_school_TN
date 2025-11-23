# 🚀 BitChest - Plateforme de Trading de Cryptomonnaies

BitChest est une plateforme complète de gestion et trading de cryptomonnaies construite avec **Laravel** (backend) et **Vue 3 + Vite** (frontend). Elle offre une expérience de trading sécurisée et intuitive pour les clients ainsi qu'un tableau de bord complet pour les administrateurs.

## 📋 Table des Matières

- [Caractéristiques](#caractéristiques)
- [Architecture](#architecture)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Utilisation](#utilisation)
- [Structure du Projet](#structure-du-projet)
- [Documentation API](#documentation-api)
- [Dépannage](#dépannage)
- [Licence](#licence)

## ✨ Caractéristiques

### 🎯 Pour les Clients
- ✅ **Authentification sécurisée** - Inscription, connexion, déconnexion avec Laravel Sanctum
- 💰 **Gestion du portefeuille** - Solde initial de 500€, suivi en temps réel
- 📈 **Trading de cryptomonnaies** - Achat et vente avec prix en direct
- 📊 **Historique complet** - Toutes les transactions avec détails
- 📱 **Dashboard personnel** - Vue d'ensemble du portfolio et plus-values
- 🔔 **Système de notifications** - Alertes en temps réel
- 👤 **Profil personnalisable** - Photo de profil, bannière, informations personnelles
- 💾 **Stockage sécurisé** - Images et fichiers via Laravel Storage

### 👨‍💼 Pour les Administrateurs
- 🔧 **Gestion complète des cryptomonnaies** - CRUD complet (Ajouter, Modifier, Supprimer)
- 👥 **Management des utilisateurs** - Création, modification, suppression de comptes clients
- 📊 **Dashboard analytique** - Statistiques globales de la plateforme
- 📋 **Gestion des demandes de compte** - Approuver ou rejeter les demandes
- 🔄 **Synchronisation des cours** - Mise à jour automatique des prix via CoinGecko
- ❌ **Annulation des transactions** - Possibilité d'annuler les transactions avec raison
- 📈 **Rapports détaillés** - Suivi complet des transactions et utilisateurs
- ⚙️ **Paramètres avancés** - Configuration de l'ID administrateur, langue, fuseau horaire

## 🏗️ Architecture

```
Bitchest_project/
├── backend/                    # API Laravel 12.x
│   ├── app/
│   │   ├── Console/Commands/   # Commandes Artisan
│   │   ├── Http/
│   │   │   ├── Controllers/    # Logique métier (Auth, Crypto, Wallet, Admin)
│   │   │   ├── Middleware/     # Authentification, autorisation
│   │   │   └── Requests/       # Validation des requêtes
│   │   ├── Models/             # Modèles Eloquent (User, Wallet, Transaction, etc.)
│   │   ├── Services/           # Services métier (Upload, Profile, Transaction)
│   │   └── Mail/               # Templates email
│   ├── database/
│   │   ├── migrations/         # Schéma DB (users, wallets, transactions, etc.)
│   │   ├── seeders/            # Données de test
│   │   └── factories/          # Factories pour tests
│   ├── routes/
│   │   └── api.php             # Routes API RESTful
│   ├── storage/
│   │   └── app/public/         # Fichiers uploadés (images profil, bannières)
│   └── public/
│       └── storage/            # Symlink vers storage/app/public
│
├── frontend/                   # Vue 3 + Vite
│   ├── src/
│   │   ├── pages/
│   │   │   ├── SignIn.vue      # Page de connexion
│   │   │   ├── Dashboard.vue   # Layout principal
│   │   │   ├── client/         # Pages client (Portfolio, Transactions)
│   │   │   └── admin/          # Pages admin (Overview, Clients, Cryptos)
│   │   ├── components/         # Composants réutilisables
│   │   ├── services/           # Services API et authentification
│   │   ├── router/             # Configuration des routes
│   │   ├── types/              # Types TypeScript
│   │   └── stores/             # État global (si Pinia)
│   ├── .env                    # Configuration (VITE_API_URL)
│   └── package.json            # Dépendances Node
│
└── README.md                   # Ce fichier
```

## 🛠️ Stack Technique

### Backend
- **Framework** : Laravel 12.x
- **Langage** : PHP 8.2+
- **Base de données** : MySQL
- **API** : RESTful avec Laravel Sanctum (authentification par tokens)
- **Uploads** : Laravel Storage (disque `public`)
- **Jobs** : Laravel Queue
- **Email** : Laravel Mail

### Frontend
- **Framework** : Vue 3 (Composition API)
- **Build Tool** : Vite
- **Stylisation** : Tailwind CSS
- **UI Components** : Shadcn-vue (Radix UI)
- **HTTP Client** : Axios
- **State Management** : Pinia (optionnel)
- **Icônes** : Lucide Vue Next

## 📦 Prérequis

### Système
- **PHP** : 8.2 ou supérieur
- **Node.js** : 18.x ou supérieur
- **npm** : 9.x ou supérieur
- **MySQL** : 5.7 ou supérieur (ou MariaDB 10.3+)
- **Composer** : Version récente

### Outils optionnels
- Git
- VS Code (ou éditeur de votre choix)
- Postman (pour tester l'API)

## 🚀 Installation

### Étape 1 : Cloner le projet

```bash
git clone https://github.com/votre-username/Bitchest_project.git
cd Bitchest_project
```

### Étape 2 : Installation du Backend

```bash
cd backend

# Installer les dépendances PHP
composer install

# Copier le fichier d'environnement
cp .env.example .env

# Générer la clé d'application
php artisan key:generate

# Installer les dépendances Node (pour Vite/Tailwind)
npm install
```

### Étape 3 : Configuration de la Base de Données

Éditer le fichier `.env` :

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bitchest
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe

# Configuration email (optionnel)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=votre_username
MAIL_PASSWORD=votre_password
MAIL_FROM_ADDRESS="noreply@bitchest.com"
```

### Étape 4 : Migrations et Seeders

```bash
# Exécuter les migrations
php artisan migrate

# (Optionnel) Remplir la BD avec des données de test
php artisan db:seed
```

### Étape 5 : Configuration du Stockage

```bash
# Créer le symlink pour les fichiers publics
php artisan storage:link

# Ou vérifier/créer le symlink avec la commande helper
php artisan storage:check-symlink
```

### Étape 6 : Installation du Frontend

```bash
cd ../frontend

# Installer les dépendances
npm install

# Créer le fichier .env
echo "VITE_API_URL=http://localhost:8000" > .env
```

## ⚙️ Configuration

### Backend - Variables d'environnement

```env
# Application
APP_NAME=Bitchest
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bitchest
DB_USERNAME=root
DB_PASSWORD=

# Cache & Session
CACHE_DRIVER=file
SESSION_DRIVER=cookie

# Queue
QUEUE_CONNECTION=sync

# Authentification
SANCTUM_STATEFUL_DOMAINS=localhost:3000

# CoinGecko API (optionnel)
COINGECKO_API_URL=https://api.coingecko.com/api/v3
```

### Frontend - Variables d'environnement

```env
# .env
VITE_API_URL=http://localhost:8000
VITE_APP_NAME=BitChest
```

## 📖 Utilisation

### Démarrer le serveur Backend

```bash
cd backend

# Option 1 : Démarrage complet (Vite + PHP + Queue)
npm run dev

# Option 2 : Démarrages séparés
php artisan serve                    # Démarre le serveur Laravel sur :8000
npm run dev                          # Démarre Vite pour les assets
php artisan queue:work               # Démarre le worker de files d'attente
```

Le serveur backend sera disponible sur : **http://localhost:8000**

### Démarrer le serveur Frontend

```bash
cd frontend

# Démarrer le serveur Vite
npm run dev
```

Le frontend sera disponible sur : **http://localhost:5173**

### Créer un Compte Administrateur (Optionnel)

```bash
cd backend
php artisan tinker

# Dans la console Tinker
>>> $user = App\Models\User::create([
    'name' => 'Admin User',
    'email' => 'admin@bitchest.com',
    'password' => bcrypt('password123'),
    'role' => 'ADMIN'
]);
>>> exit
```

## 📚 Structure du Projet Détaillée

### Backend - Modèles de Données

#### User
```php
- id: string (UUID)
- name: string
- email: string (unique)
- password: string
- role: enum (CLIENT, ADMIN)
- profile_picture: string (nullable, path du fichier)
- profile_banner: string (nullable, path du fichier)
- email_verified_at: timestamp (nullable)
- remember_token: string
- created_at, updated_at: timestamp
```

#### Wallet (Portefeuille)
```php
- id: bigint
- user_id: string (foreign key → users)
- balance_eur: decimal(15,2) - Solde en euros
- created_at, updated_at: timestamp
```

#### CryptoWalletAsset (Actif Crypto)
```php
- id: bigint
- wallet_id: bigint
- cryptomoney_id: bigint
- quantity: decimal(20,8)
- average_buy_price: decimal(15,2)
- created_at, updated_at: timestamp
```

#### Transaction
```php
- id: bigint
- wallet_id: bigint
- cryptomoney_id: bigint
- type: enum (ACHAT, VENTE)
- quantity: decimal(20,8)
- price: decimal(15,2)
- total_eur: decimal(15,2)
- cancelled_at: timestamp (nullable)
- cancel_reason: string (nullable)
- created_at, updated_at: timestamp
```

#### Cryptomoney (Cryptomonnaie)
```php
- id: bigint
- name: string (Bitcoin, Ethereum, etc.)
- symbol: string (BTC, ETH, etc.)
- price_eur: decimal(15,2)
- market_cap: decimal(20,2) (nullable)
- volume_24h: decimal(20,2) (nullable)
- change_24h_pct: decimal(5,2) (nullable)
- image_url: string (nullable)
- coingecko_id: string (nullable)
- created_at, updated_at: timestamp
```

#### Notification
```php
- id: bigint
- user_id: string
- title: string
- message: text
- type: enum (transaction, security, system, alert, info, success)
- is_read: boolean
- created_at, updated_at: timestamp
```

#### AccountRequest
```php
- id: bigint
- name: string
- email: string
- status: enum (PENDING, APPROVED, REJECTED)
- rejection_reason: text (nullable)
- processed_by: string (nullable, user_id)
- processed_at: timestamp (nullable)
- created_at, updated_at: timestamp
```

## 🔐 Endpoints API

### Authentification

```
POST   /api/v1/login                    - Connexion
POST   /api/v1/logout                   - Déconnexion (auth required)
GET    /api/v1/profile                  - Récupérer le profil (auth required)
PUT    /api/v1/profile                  - Mettre à jour le profil (auth required)
POST   /api/v1/profile/password         - Changer le mot de passe (auth required)
```

### Profil et Uploads (auth required)

```
POST   /api/v1/profile/picture/upload   - Upload photo de profil (legacy)
PUT    /api/v1/profile/picture          - Upload/mettre à jour photo de profil
DELETE /api/v1/profile/picture          - Supprimer la photo de profil
POST   /api/v1/profile/banner/upload    - Upload bannière (legacy)
PUT    /api/v1/profile/banner           - Upload/mettre à jour bannière
DELETE /api/v1/profile/banner           - Supprimer la bannière
GET    /api/v1/profile/stats            - Statistiques du profil
```

### Cryptomonnaies (public)

```
GET    /api/v1/cryptos                  - Lister toutes les cryptos
GET    /api/v1/cryptos/{id}             - Détails d'une crypto
GET    /api/v1/cryptos/{id}/history     - Historique des prix
```

### Portefeuille (client, auth required)

```
GET    /api/v1/wallets                  - Voir le portefeuille
GET    /api/v1/wallets/{id}             - Détails du portefeuille
GET    /api/v1/wallets/plus-value       - Calculer les plus-values
GET    /api/v1/wallets/history          - Historique des transactions
GET    /api/v1/wallets/{id}/history     - Historique du portefeuille
POST   /api/v1/wallets/transaction      - Effectuer une transaction (achat/vente)
```

### Notifications (client, auth required)

```
GET    /api/v1/notifications            - Lister les notifications
PUT    /api/v1/notifications/{id}/read  - Marquer comme lue
```

### Administration (admin only, auth required)

#### Gestion des Cryptomonnaies
```
POST   /api/v1/admin/cryptos            - Ajouter une crypto
GET    /api/v1/admin/cryptos/{id}/edit  - Éditer une crypto
PUT    /api/v1/admin/cryptos/{id}       - Mettre à jour une crypto
DELETE /api/v1/admin/cryptos/{id}       - Supprimer une crypto
POST   /api/v1/admin/cryptos/sync       - Synchroniser les cours
```

#### Gestion des Clients
```
GET    /api/v1/admin/clients            - Lister tous les clients
POST   /api/v1/admin/clients            - Créer un client
GET    /api/v1/admin/clients/{id}       - Détails du client
PUT    /api/v1/admin/clients/{id}       - Mettre à jour un client
DELETE /api/v1/admin/clients/{id}       - Supprimer un client
GET    /api/v1/admin/clients/{id}/transactions - Transactions du client
GET    /api/v1/admin/clients/{id}/wallet - Portefeuille du client
```

#### Gestion des Transactions
```
GET    /api/v1/admin/transactions       - Lister toutes les transactions
GET    /api/v1/admin/transactions/{id}  - Détails d'une transaction
POST   /api/v1/admin/transactions/{id}/cancel - Annuler une transaction
```

#### Gestion des Demandes de Compte
```
GET    /api/v1/admin/account-requests           - Lister les demandes
POST   /api/v1/admin/account-requests/{id}/approve - Approuver
POST   /api/v1/admin/account-requests/{id}/reject  - Rejeter
```

#### Statistiques et Paramètres
```
GET    /api/v1/admin/stats              - Statistiques générales
POST   /api/v1/admin/change-id          - Changer son ID administrateur
```

## 🎨 Pages Frontend

### Pages Client
- **SignIn** (`/signin`) - Connexion/Inscription
- **Dashboard Overview** (`/dashboard/overview`) - Vue d'ensemble
- **Cryptos** (`/dashboard/cryptos`) - Liste des cryptomonnaies
- **Transactions** (`/dashboard/transactions`) - Historique des transactions
- **Portfolio/Profile** (`/dashboard/portfolio`) - Profil et portefeuille personnel

### Pages Admin
- **Overview** (`/dashboard/admin/overview`) - Tableau de bord
- **Clients** (`/dashboard/admin/clients`) - Gestion des clients
- **Cryptos** (`/dashboard/admin/cryptos`) - Gestion des cryptomonnaies
- **Transactions** (`/dashboard/admin/transactions`) - Gestion des transactions
- **Settings** (`/dashboard/admin/settings`) - Paramètres admin

## 🔄 Services Backend Importants

### UploadService
Gère le téléchargement sécurisé des fichiers utilisateur :
- Upload de photo de profil
- Upload de bannière de profil
- Suppression des fichiers anciens
- Génération d'URLs publiques

**Localisation** : `app/Services/UploadService.php`

### ProfileService
Récupère et organise les données du profil utilisateur avec statistiques et graphiques.

**Localisation** : `app/Services/ProfileService.php`

### TransactionService
Gère les transactions (achat/vente) avec validation et mise à jour du portefeuille.

**Localisation** : `app/Services/TransactionService.php`

## 📊 Fonctionnalités Avancées

### Uploadification des Fichiers
- Stockage sécurisé sur disque `public`
- Chemins relatifs : `profile_pictures/{user_id}/...` et `profile_banners/{user_id}/...`
- URLs publiques via `Storage::disk('public')->url()`
- Suppression automatique des fichiers anciens lors d'un nouvel upload
- Support multipart/form-data

### Système de Notifications
- Notifications en temps réel (si implémentation WebSocket ajoutée)
- Marquage comme lues
- Types : transaction, security, system, alert, info, success

### Gestion des Demandes de Compte
- Formulaire public de demande
- Approbation/Rejet par les administrateurs
- Email de confirmation automatique
- Création automatique du compte avec mot de passe temporaire

### Synchronisation des Cryptomonnaies
- Intégration CoinGecko API
- Mise à jour automatique des prix
- Tâche planifiée (Scheduler Laravel)

## 🧪 Tests

### Backend
```bash
cd backend

# Tous les tests
composer run test
# ou
php artisan test

# Tests spécifiques
php artisan test --filter=AuthControllerTest
php artisan test --filter=CryptoControllerTest
```

### Frontend
```bash
cd frontend

# Avec Vitest (si configuré)
npm run test
```

## 🚨 Dépannage

### Erreur de Symlink (Windows)

**Problème** : `Failed to create symlink: symlink(): Permission denied`

**Solutions** :

1. **Exécuter en tant qu'administrateur**
   ```bash
   # CMD (en tant qu'administrateur)
   php artisan storage:link
   ```

2. **Activer le Developer Mode (Windows 10+)**
   - Paramètres → Système → Pour les développeurs
   - Activer "Mode développeur"

3. **Utiliser la commande helper**
   ```bash
   php artisan storage:check-symlink
   ```

### Les Images Uploadées ne s'Affichent pas

**Vérifier** :
1. Le symlink existe : `public/storage/` → `storage/app/public/`
2. La permission d'écriture sur `storage/app/public/`
3. L'URL returnée par l'API : doit être `{VITE_API_URL}/storage/{path}`

### CORS ou Erreurs de Requête

**Vérifier** :
1. `VITE_API_URL` dans `.env` du frontend
2. `SANCTUM_STATEFUL_DOMAINS` dans `.env` du backend
3. Les headers CORS dans `config/cors.php`

### Base de Données non Trouvée

```bash
# Vérifier les migrations
php artisan migrate:status

# Réinitialiser et migrer
php artisan migrate:refresh --seed
```

## 📞 Support et Ressources

- **Documentation Laravel** : https://laravel.com/docs
- **Documentation Vue 3** : https://vuejs.org
- **Tailwind CSS** : https://tailwindcss.com
- **CoinGecko API** : https://www.coingecko.com/api

## 📝 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](LICENSE) pour plus de détails.

## 👨‍💻 Auteur

**Développé par le Collectif BitChest**

---

**Dernier mise à jour** : 2025  
**Version** : 1.0.0
