# 🔧 BitChest Backend API

Backend Laravel 12.x pour la plateforme BitChest. Fournit une API RESTful complète pour gérer les utilisateurs, les cryptomonnaies, les portefeuilles et les transactions.

## 📋 Table des Matières

- [Vue d'ensemble](#vue-densemble)
- [Caractéristiques](#caractéristiques)
- [Prérequis](#prérequis)
- [Installation](#installation)
- [Configuration](#configuration)
- [Base de Données](#base-de-données)
- [Structure du Projet](#structure-du-projet)
- [API Endpoints](#api-endpoints)
- [Services et Logique Métier](#services-et-logique-métier)
- [Authentification](#authentification)
- [Stockage des Fichiers](#stockage-des-fichiers)
- [Commandes Artisan](#commandes-artisan)
- [Tests](#tests)
- [Déploiement](#déploiement)
- [Dépannage](#dépannage)

## 🎯 Vue d'ensemble

BitChest Backend est une API RESTful robuste construite avec Laravel qui gère :
- ✅ Authentification et autorisation (Laravel Sanctum)
- 💰 Gestion des portefeuilles et transactions
- 🪙 Gestion des cryptomonnaies (CRUD)
- 👥 Gestion des utilisateurs (admin et clients)
- 📸 Stockage et gestion des fichiers uploadés
- 🔔 Système de notifications
- 📊 Statistiques et rapports

## ✨ Caractéristiques

### Authentification et Autorisation
- Authentification par token avec Laravel Sanctum
- Support des rôles (CLIENT, ADMIN)
- Middleware d'authentification et d'autorisation
- Gestion sécurisée des mots de passe (Argon2)

### Gestion des Utilisateurs
- Création de comptes avec validation email
- Profils utilisateur complèts
- Upload de photo de profil et bannière
- Gestion des demandes de création de compte

### Portefeuille et Trading
- Création automatique du portefeuille avec solde initial (500€)
- Achat et vente de cryptomonnaies
- Historique complet des transactions
- Calcul des plus-values en temps réel
- Validation des transactions

### Gestion des Cryptomonnaies
- CRUD complet pour les administrateurs
- Intégration CoinGecko pour les prix
- Historique des prix
- Support des images et métadonnées

### Stockage des Fichiers
- Upload de photos de profil
- Upload de bannières
- Stockage sécurisé dans `storage/app/public/`
- URLs publiques générées automatiquement
- Suppression automatique des fichiers anciens

### Notifications
- Système de notifications pour les utilisateurs
- Marquage comme lues
- Types de notifications variés

## 📦 Prérequis

- PHP 8.2 ou supérieur
- Composer (dernière version)
- MySQL 5.7+ ou MariaDB 10.3+
- Node.js 18+ et npm 9+
- Git

## 🚀 Installation

### 1. Cloner le Repository

```bash
git clone https://github.com/votre-username/Bitchest_project.git
cd Bitchest_project/backend
```

### 2. Installer les Dépendances PHP

```bash
composer install
```

### 3. Configurer le Fichier d'Environnement

```bash
cp .env.example .env
```

Puis éditer `.env` et configurer :
```env
APP_NAME=BitChest
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bitchest
DB_USERNAME=root
DB_PASSWORD=

SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
```

### 4. Générer la Clé d'Application

```bash
php artisan key:generate
```

### 5. Installer les Dépendances Node

```bash
npm install
```

### 6. Créer la Base de Données

```bash
# MySQL
mysql -u root -p
CREATE DATABASE bitchest;
```

### 7. Exécuter les Migrations

```bash
php artisan migrate
```

### 8. Remplir la BD avec des Données de Test (Optionnel)

```bash
php artisan db:seed
```

### 9. Configurer le Stockage

```bash
# Créer le symlink pour les fichiers publics
php artisan storage:link

# Ou vérifier/créer avec la commande helper
php artisan storage:check-symlink
```

## ⚙️ Configuration

### Variables d'Environnement Essentielles

```env
# Application
APP_NAME=BitChest
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:... (généré automatiquement)
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bitchest
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file
CACHE_TTL=3600

# Queue
QUEUE_CONNECTION=sync

# Session
SESSION_DRIVER=cookie

# Authentification Sanctum
SANCTUM_STATEFUL_DOMAINS=localhost:3000,localhost:5173
SANCTUM_GUARD=web

# Email (Mailtrap, Gmail, etc.)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS="noreply@bitchest.com"
MAIL_FROM_NAME="BitChest"

# CoinGecko API
COINGECKO_API_URL=https://api.coingecko.com/api/v3
```

### Configuration des Fichiers

**config/sanctum.php** - Authentification par token

**config/filesystems.php** - Disques de stockage
```php
'disks' => [
    'public' => [
        'driver' => 'local',
        'path' => 'storage/app/public',
        'url' => '/storage',
        'visibility' => 'public',
    ],
    // ...
],
```

**config/cors.php** - CORS pour le frontend
```php
'allowed_origins' => ['localhost:3000', 'localhost:5173'],
```

## 📊 Base de Données

### Migrations

Les migrations créent les tables suivantes :

1. **users** - Utilisateurs (clients et administrateurs)
2. **wallets** - Portefeuilles des clients
3. **cryptomonies** - Liste des cryptomonnaies disponibles
4. **crypto_wallet_assets** - Actifs cryptos des utilisateurs
5. **transactions** - Historique des transactions
6. **notifications** - Notifications utilisateur
7. **account_requests** - Demandes de création de compte

### Modèles Eloquent

```php
// app/Models/User.php
- belongsTo(Wallet)
- hasMany(Notification)
- hasOne(AccountRequest)

// app/Models/Wallet.php
- belongsTo(User)
- hasMany(CryptoWalletAsset)
- hasMany(Transaction)

// app/Models/Cryptomoney.php
- hasMany(CryptoWalletAsset)
- hasMany(Transaction)

// app/Models/Transaction.php
- belongsTo(Wallet)
- belongsTo(Cryptomoney)

// app/Models/Notification.php
- belongsTo(User)

// app/Models/AccountRequest.php
- belongsTo(User)
```

## 🏗️ Structure du Projet

```
app/
├── Console/
│   ├── Commands/
│   │   └── CheckStorageSymlink.php    # Commande pour vérifier le symlink
│   └── Kernel.php
├── Http/
│   ├── Controllers/
│   │   ├── AuthController.php         # Authentification
│   │   ├── CryptoController.php       # Gestion des cryptos
│   │   ├── PortefeuilleController.php # Gestion des portefeuilles
│   │   ├── ProfileController.php      # Profil et uploads
│   │   ├── NotificationController.php # Notifications
│   │   ├── AdminUserController.php    # Gestion des clients
│   │   ├── AdminCryptoController.php  # Admin cryptos
│   │   └── AdminTransactionController.php # Admin transactions
│   ├── Middleware/
│   │   ├── Authenticate.php
│   │   ├── CheckRole.php
│   │   └── ...
│   └── Requests/
│       ├── UploadProfilePictureRequest.php
│       ├── UploadProfileBannerRequest.php
│       └── ...
├── Models/
│   ├── User.php
│   ├── Wallet.php
│   ├── Cryptomoney.php
│   ├── CryptoWalletAsset.php
│   ├── Transaction.php
│   ├── Notification.php
│   └── AccountRequest.php
├── Services/
│   ├── UploadService.php              # Gestion des uploads
│   ├── ProfileService.php             # Logique du profil
│   ├── TransactionService.php         # Logique des transactions
│   └── ...
└── Mail/
    ├── TempPasswordMail.php
    ├── NewAccountRequestMail.php
    └── ...

database/
├── migrations/
│   ├── 2024_01_01_000001_create_users_table.php
│   ├── 2024_01_01_000002_create_wallets_table.php
│   ├── 2025_11_17_000000_add_profile_picture_and_banner_to_users_table.php
│   └── ...
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── CryptoSeeder.php
│   └── ...
└── factories/
    ├── UserFactory.php
    └── ...

routes/
└── api.php                            # Toutes les routes API

storage/
└── app/
    └── public/
        ├── profile_pictures/          # Photos de profil uploadées
        └── profile_banners/           # Bannières uploadées

public/
└── storage/ → symlink vers storage/app/public/

tests/
├── Feature/
│   ├── AuthTest.php
│   ├── CryptoTest.php
│   └── ...
└── Unit/
    └── ...

config/
├── app.php
├── database.php
├── filesystems.php
├── mail.php
├── queue.php
├── sanctum.php
└── ...
```

## 🔐 API Endpoints

### Authentification

```
POST   /api/v1/login
    Body: { "email": "user@example.com", "password": "password123" }
    Response: { "user": {...}, "token": "..." }

POST   /api/v1/logout
    Headers: Authorization: Bearer {token}
    Response: { "message": "Logged out successfully" }

POST   /api/v1/request-account
    Body: { "name": "John Doe", "email": "john@example.com" }
    Response: { "message": "Request submitted" }
```

### Profil et Uploads

```
GET    /api/v1/profile
    Headers: Authorization: Bearer {token}
    Response: { "user": {...} }

PUT    /api/v1/profile
    Headers: Authorization: Bearer {token}
    Body: { "name": "New Name", "email": "new@example.com" }

POST   /api/v1/profile/password
    Headers: Authorization: Bearer {token}
    Body: {
        "current_password": "old_password",
        "password": "new_password",
        "password_confirmation": "new_password"
    }

PUT    /api/v1/profile/picture
    Headers: Authorization: Bearer {token}
    Body: FormData { "profile_picture": File }
    Response: { "path": "profile_pictures/...", "url": "..." }

DELETE /api/v1/profile/picture
    Headers: Authorization: Bearer {token}

PUT    /api/v1/profile/banner
    Headers: Authorization: Bearer {token}
    Body: FormData { "profile_banner": File }

DELETE /api/v1/profile/banner
    Headers: Authorization: Bearer {token}

GET    /api/v1/profile/stats
    Headers: Authorization: Bearer {token}
    Response: { "portfolio_data": {...}, "charts": {...} }
```

### Cryptomonnaies (Public)

```
GET    /api/v1/cryptos
    Response: [ { "id": 1, "name": "Bitcoin", "symbol": "BTC", "price_eur": 45000, ... } ]

GET    /api/v1/cryptos/{id}
    Response: { "id": 1, "name": "Bitcoin", ... }

GET    /api/v1/cryptos/{id}/history
    Response: { "prices": [...], "dates": [...] }
```

### Portefeuille (Client, auth required)

```
GET    /api/v1/wallets
    Response: {
        "wallet": { "id": 1, "balance_eur": 500, ... },
        "cryptomonnaies": [ { "name": "Bitcoin", "quantity": 0.5, ... } ]
    }

GET    /api/v1/wallets/{id}
    Response: { "wallet": {...}, "assets": [...] }

GET    /api/v1/wallets/plus-value
    Response: { "total_invested": 5000, "current_value": 5500, "plus_value": 500, "pct": 10 }

GET    /api/v1/wallets/history
    Response: [ { "type": "ACHAT", "crypto": "Bitcoin", "quantity": 0.5, "price": 45000, "date": "2025-01-15" } ]

POST   /api/v1/wallets/transaction
    Body: {
        "type": "ACHAT",
        "cryptomoney_id": 1,
        "quantity": 0.5,
        "price": 45000
    }
    Response: { "transaction": {...}, "wallet": {...} }
```

### Notifications (Client, auth required)

```
GET    /api/v1/notifications
    Response: [ { "id": 1, "title": "Transaction", "message": "...", "is_read": false } ]

PUT    /api/v1/notifications/{id}/read
    Response: { "message": "Marked as read" }
```

### Administration (Admin only, auth required)

#### Clients
```
GET    /api/v1/admin/clients
    Response: [ { "id": "uuid", "name": "John", "email": "john@example.com", "balance_eur": 500, ... } ]

POST   /api/v1/admin/clients
    Body: { "name": "Jane Doe", "email": "jane@example.com", "role": "CLIENT", "balance_eur": 500 }

GET    /api/v1/admin/clients/{id}
    Response: { "user": {...}, "wallet": {...}, "transactions": [...], "positions": [...] }

PUT    /api/v1/admin/clients/{id}
    Body: { "name": "Updated Name", ... }

DELETE /api/v1/admin/clients/{id}

GET    /api/v1/admin/clients/{id}/transactions
    Response: [ { "type": "ACHAT", "quantity": 0.5, ... } ]

GET    /api/v1/admin/clients/{id}/wallet
    Response: { "portfolio": [...] }
```

#### Cryptomonnaies
```
POST   /api/v1/admin/cryptos
    Body: { "crypto_id": "bitcoin", "image": File }
    Response: { "crypto": {...} }

GET    /api/v1/admin/cryptos/{id}/edit
    Response: { "crypto": {...} }

PUT    /api/v1/admin/cryptos/{id}
    Body: FormData { "image": File, ... }

DELETE /api/v1/admin/cryptos/{id}

POST   /api/v1/admin/cryptos/sync
    Response: { "message": "Synced", "count": 10 }
```

#### Transactions
```
GET    /api/v1/admin/transactions
    Response: [ { "id": 1, "type": "ACHAT", "user": {...}, "crypto": {...}, ... } ]

GET    /api/v1/admin/transactions/{id}
    Response: { "transaction": {...}, "user": {...}, "details": {...} }

POST   /api/v1/admin/transactions/{id}/cancel
    Body: { "reason": "Client request" }
    Response: { "message": "Transaction cancelled" }
```

#### Demandes de Compte
```
GET    /api/v1/admin/account-requests
    Response: [ { "id": 1, "name": "John", "email": "john@example.com", "status": "PENDING" } ]

POST   /api/v1/admin/account-requests/{id}/approve
    Response: { "user": {...}, "temp_password": "..." }

POST   /api/v1/admin/account-requests/{id}/reject
    Body: { "reason": "Reason for rejection" }
    Response: { "message": "Request rejected" }
```

#### Statistiques
```
GET    /api/v1/admin/stats
    Response: {
        "total_users": 100,
        "total_transactions": 500,
        "total_volume": 500000,
        "crypto_count": 50,
        "recent_transactions": [...],
        "top_cryptos": [...],
        "charts": {...}
    }

POST   /api/v1/admin/change-id
    Body: { "new_id": "NEWADMINID123456", "confirmation": "I confirm..." }
    Response: { "message": "ID changed" }
```

## 🔧 Services et Logique Métier

### UploadService

Gère le stockage sécurisé des fichiers uploadés.

```php
// app/Services/UploadService.php

public function uploadProfilePicture(User $user, UploadedFile $file): string
public function uploadProfileBanner(User $user, UploadedFile $file): string
public function deleteProfilePicture(User $user): bool
public function deleteProfileBanner(User $user): bool
public function getProfilePictureUrl(User $user): ?string
public function getProfileBannerUrl(User $user): ?string
```

**Caractéristiques** :
- Stockage sur disque `public`
- Suppression automatique des fichiers anciens
- Génération d'URLs publiques
- Validation MIME type et taille
- Logging détaillé

### ProfileService

Récupère les données complètes du profil avec statistiques.

```php
public function getFullProfileOverview(string $userId): array
```

**Retourne** : Statistiques, graphiques, composition du portefeuille, historique

### TransactionService

Gère les transactions d'achat/vente avec validation.

```php
public function processTransaction(Wallet $wallet, $cryptoId, $quantity, $type): Transaction
public function creditInitialBalance(User $user, float $amount): void
```

**Validations** :
- Solde suffisant pour les ventes
- Quantité valide
- Prix actuel récupéré depuis la BD

## 🔐 Authentification

### Laravel Sanctum

Authentification par token API (stateless, idéale pour les SPA).

**Flux de connexion** :
1. Client envoie `email` et `password` à POST `/api/v1/login`
2. Backend valide et retourne un token Sanctum
3. Client stocke le token dans localStorage
4. Pour chaque requête, client ajoute header : `Authorization: Bearer {token}`
5. Middleware `auth:sanctum` valide le token

**Middleware de Rôle** :
```php
middleware(['auth:sanctum', 'role:ADMIN'])
```

## 📸 Stockage des Fichiers

### Structure du Stockage

```
storage/app/public/
├── profile_pictures/
│   ├── user_id_1/
│   │   ├── abc123_1234567890.jpg
│   │   └── def456_1234567891.jpg
│   └── user_id_2/
│       └── ...
└── profile_banners/
    ├── user_id_1/
    │   └── xyz789_1234567892.png
    └── ...
```

### Upload de Fichiers

**Format** : multipart/form-data

**Exemple Frontend** :
```javascript
const formData = new FormData();
formData.append('profile_picture', fileInput.files[0]);

fetch('/api/v1/profile/picture', {
    method: 'PUT',
    headers: {
        'Authorization': `Bearer ${token}`
    },
    body: formData
});
```

**Réponse** :
```json
{
    "success": true,
    "message": "Profile picture uploaded successfully",
    "data": {
        "path": "profile_pictures/user_id/abc123_1234567890.jpg",
        "url": "http://localhost:8000/storage/profile_pictures/user_id/abc123_1234567890.jpg",
        "user": {...}
    }
}
```

### Symlink Storage

Le symlink connecte `public/storage/` à `storage/app/public/` pour servir les fichiers publiquement.

**Créer le symlink** :
```bash
php artisan storage:link
```

**Vérifier/Réparer** :
```bash
php artisan storage:check-symlink
```

## 📝 Commandes Artisan Personnalisées

### CheckStorageSymlink

Vérifier et créer le symlink de stockage.

```bash
php artisan storage:check-symlink

# Sortie
# Storage symlink already exists.
# Link: /var/www/bitchest/public/storage
# Target: /var/www/bitchest/storage/app/public
```

### Commandes Standard Laravel Utiles

```bash
# Migration
php artisan migrate                 # Exécuter les migrations
php artisan migrate:rollback       # Annuler la dernière migration
php artisan migrate:refresh --seed # Réinitialiser la BD

# Seeding
php artisan db:seed                # Exécuter les seeders
php artisan db:seed --class=CryptoSeeder

# Tinker (REPL Laravel)
php artisan tinker

# Queue
php artisan queue:work             # Démarrer le worker des jobs
php artisan queue:failed           # Voir les jobs échoués

# Cache
php artisan cache:clear            # Vider le cache
php artisan config:cache           # Cacher la configuration
```

## 🧪 Tests

### Lancer les Tests

```bash
# Tous les tests
composer run test
# ou
php artisan test

# Avec output détaillé
php artisan test --verbose

# Tests spécifiques
php artisan test --filter=AuthControllerTest
php artisan test tests/Feature/AuthTest.php

# Tests avec couverture
php artisan test --coverage
```

### Écrire des Tests

```php
// tests/Feature/AuthTest.php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;

class AuthTest extends TestCase
{
    public function test_user_can_login()
    {
        $user = User::factory()->create([
            'password' => bcrypt('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertOk();
        $response->assertHas('token');
    }
}
```

## 🚀 Déploiement

### Production Checklist

```bash
# 1. Optimiser l'autoloader Composer
composer install --optimize-autoloader --no-dev

# 2. Mettre en cache la configuration
php artisan config:cache

# 3. Mettre en cache les routes
php artisan route:cache

# 4. Mettre en cache les vues
php artisan view:cache

# 5. Optimiser l'application
php artisan optimize

# 6. Générer la clé d'application
php artisan key:generate

# 7. Exécuter les migrations
php artisan migrate --force

# 8. Créer le symlink de stockage
php artisan storage:link

# 9. Définir APP_ENV=production dans .env
APP_ENV=production
APP_DEBUG=false
```

### Fichier .env pour Production

```env
APP_NAME=BitChest
APP_ENV=production
APP_DEBUG=false
APP_URL=https://bitchest.com

# Database - RDS, Cloud SQL, etc.
DB_CONNECTION=mysql
DB_HOST=db-prod.example.com
DB_DATABASE=bitchest_prod
DB_USERNAME=produser
DB_PASSWORD=...

# Cache distribué (Redis)
CACHE_DRIVER=redis
REDIS_HOST=redis-prod.example.com

# Queue
QUEUE_CONNECTION=redis

# Mail - Service mail en production
MAIL_MAILER=smtp
MAIL_HOST=...
MAIL_USERNAME=...
MAIL_PASSWORD=...
```

## 🐛 Dépannage

### Erreur de Permission de Symlink

**Problème** :
```
Failed to create symlink: symlink(): Permission denied
```

**Solutions** :

**Windows** :
```bash
# Exécuter en tant qu'administrateur
php artisan storage:link
# ou
php artisan storage:check-symlink
```

**Linux/Mac** :
```bash
# Utiliser sudo
sudo php artisan storage:link

# Ou fixer les permissions
sudo chown -R www-data:www-data storage/
sudo chmod -R 755 storage/
```

### Les Fichiers Uploadés ne s'Affichent pas

**Vérifier** :
1. Le symlink existe : `ls -la public/storage`
2. Le disque `public` est configuré correctement
3. L'URL returnée inclut `/storage/`
4. VITE_API_URL au frontend est correct

**Fix** :
```bash
# Supprimer et recréer le symlink
rm public/storage
php artisan storage:link

# Ou
php artisan storage:check-symlink
```

### Erreur "CSRF Token Mismatch"

**Cause** : Middleware CSRF ne s'applique pas à l'API avec Sanctum

**Fix** : Vérifier que les routes API sont correctement configurées dans `routes/api.php`

### Erreur de Base de Données

```bash
# Vérifier la connexion
php artisan tinker
>>> DB::connection()->getPdo()

# Voir l'état des migrations
php artisan migrate:status

# Réinitialiser la BD
php artisan migrate:refresh --seed
```

### Queue Jobs ne sont pas Exécutés

```bash
# Vérifier que le queue worker tourne
php artisan queue:work

# En production, utiliser Supervisor ou systemd
# ou configurer QUEUE_CONNECTION=sync pour debug
```

### Erreurs d'Email

```bash
# Tester la configuration mail
php artisan tinker
>>> Mail::raw('Test message', function ($message) {
    $message->to('test@example.com');
});
```

## 📞 Support et Ressources

- **Documentation Laravel** : https://laravel.com/docs
- **Laravel Sanctum** : https://laravel.com/docs/sanctum
- **Laravel Storage** : https://laravel.com/docs/filesystem
- **CoinGecko API** : https://www.coingecko.com/api
- **Issues du Projet** : https://github.com/votre-username/Bitchest_project/issues

## 📝 Licence

Ce projet est sous licence **MIT**. Voir le fichier [LICENSE](../../LICENSE) pour plus de détails.

---

**Dernière mise à jour** : 2025  
**Version** : 1.0.0
