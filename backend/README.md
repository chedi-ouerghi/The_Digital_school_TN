# Bitchest - Backend API de Gestion de Cryptomonnaies

## 📋 Description

Bitchest est une plateforme de gestion de cryptomonnaies développée en Laravel. Elle permet aux utilisateurs d'acheter, vendre et gérer leur portefeuille de cryptomonnaies. Le système comprend deux types d'utilisateurs : les administrateurs et les clients.

## ✨ Fonctionnalités Principales

### Pour les Clients
- 🔐 Authentification (inscription, connexion, déconnexion)
- 💰 Gestion du portefeuille avec solde initial de 500€
- 📈 Achat et vente de cryptomonnaies
- 📊 Visualisation de l'historique des transactions
- 📱 Consultation du portfolio et des plus-values
- 🔔 Système de notifications

### Pour les Administrateurs
- 🔧 Gestion complète des cryptomonnaies (CRUD)
- 👥 Gestion des utilisateurs et leurs portefeuilles
- 📊 Tableau de bord administrateur avec statistiques
- 📋 Gestion des demandes de création de compte
- 🔄 Synchronisation des cours des cryptomonnaies
- ❌ Annulation des transactions

## 🛠️ Stack Technique

- **Backend Framework**: Laravel 12.x
- **Language**: PHP 8.2+
- **Database**: MySQL
- **API**: RESTful avec Laravel Sanctum
- **Documentation API**: Swagger/OpenAPI
- **Tests**: PHPUnit
- **Frontend Assets**: Vite, Tailwind CSS
- **Authentification**: Laravel Sanctum

## 📦 Prérequis

- PHP 8.2 ou supérieur
- Composer
- MySQL
- Node.js et npm

## 🚀 Installation

### 1. Cloner le projet
```bash
git clone [url-du-repo]
cd bk
```

### 2. Installer les dépendances
```bash
composer install
npm install
```

### 3. Configuration de l'environnement
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configuration de la base de données
Modifiez le fichier `.env` avec vos informations MySQL :
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=bitchest
DB_USERNAME=root
DB_PASSWORD=votre_mot_de_passe
```

### 5. Lancer les migrations
```bash
php artisan migrate
php artisan db:seed
```

### 6. Démarrer le serveur
```bash
# Développement complet avec hot reload
composer run dev

# Ou séparément :
php artisan serve
npm run dev
php artisan queue:work
```

## 📚 Documentation API

La documentation Swagger est disponible à l'adresse : `http://localhost:8000/api/documentation`

## 🧪 Tests

### Lancer tous les tests
```bash
composer run test
# ou
php artisan test
```

### Tests spécifiques
```bash
php artisan test --filter=AuthControllerTest
php artisan test --filter=CryptoControllerTest
```

## 📊 Architecture du Projet

### Structure des dossiers
```
app/
├── Console/Commands/     # Commandes Artisan
├── Http/
│   ├── Controllers/      # Contrôleurs API
│   ├── Middleware/       # Middleware personnalisés
│   └── Requests/         # Validation des requêtes
├── Jobs/                 # Files d'attente
├── Mail/                 # Emails
├── Models/               # Modèles Eloquent
├── Services/             # Logique métier
└── Helpers/              # Fonctions utilitaires

database/
├── migrations/           # Migrations de base de données
├── seeders/              # Données de test
└── factories/            # Fabriques de modèles

tests/
├── Feature/              # Tests fonctionnels
└── Unit/                 # Tests unitaires
```

## 🔐 Endpoints API Principaux

### Authentification
- `POST /api/v1/register` - Inscription
- `POST /api/v1/login` - Connexion
- `POST /api/v1/logout` - Déconnexion
- `GET /api/v1/profile` - Profil utilisateur

### Cryptomonnaies (Public)
- `GET /api/v1/cryptos` - Lister les cryptos
- `GET /api/v1/cryptos/{id}` - Détails d'une crypto
- `GET /api/v1/cryptos/{id}/history` - Historique des prix

### Portefeuille (Client)
- `GET /api/v1/portefeuille` - Voir le portefeuille
- `POST /api/v1/portefeuille/acheter` - Acheter des cryptos
- `POST /api/v1/portefeuille/vendre` - Vendre des cryptos
- `GET /api/v1/portefeuille/plus-value` - Calculer les plus-values
- `GET /api/v1/portefeuille/historique` - Historique des transactions

### Administration
- `GET /api/v1/admin/stats` - Statistiques générales
- `POST /api/v1/admin/cryptos` - Ajouter une crypto
- `PUT /api/v1/admin/cryptos/{id}` - Modifier une crypto
- `DELETE /api/v1/admin/cryptos/{id}` - Supprimer une crypto
- `POST /api/v1/admin/cryptos/sync` - Synchroniser les cours

## 📋 Modèles de Données

### User (Utilisateur)
- id, name, email, password, role (ADMIN/CLIENT)
- solde (pour les clients), email_verified_at

### Cryptomoney (Cryptomonnaie)
- id, nom, symbole, prix_actuel, coingecko_id

### Wallet (Portefeuille)
- id, user_id, solde

### CryptoWalletAsset (Actif Crypto)
- id, wallet_id, cryptomoney_id, quantite, prix_achat

### Transaction
- id, wallet_id, cryptomoney_id, type (ACHAT/VENTE)
- quantite, prix_unitaire, montant_total

### Notification
- id, user_id, message, type, read_at

## 🔧 Configuration

### Variables d'environnement importantes
- `APP_URL` - URL de l'application
- `DB_*` - Configuration MySQL
- `MAIL_*` - Configuration email
- `QUEUE_CONNECTION` - Configuration des files d'attente

### Tâches planifiées
Le système inclut une commande pour synchroniser automatiquement les cours des cryptomonnaies :
```bash
php artisan crypto:sync-history
```

## 🚨 Gestion des Erreurs

L'API retourne des réponses JSON structurées avec des codes HTTP appropriés :
- `200` - Succès
- `201` - Créé
- `400` - Mauvaise requête
- `401` - Non authentifié
- `403` - Accès refusé
- `404` - Non trouvé
- `422` - Validation échouée
- `500` - Erreur serveur

## 📞 Support

Pour toute question ou problème, veuillez consulter la documentation ou ouvrir une issue sur le repository.

## 📝 License

Ce projet est sous licence MIT. Voir le fichier LICENSE pour plus de détails.

# What's new / Important notes (updated)
This repository includes a few recent additions and improvements; please read before running.

- Profile picture & banner management
  - Endpoints:
    - POST /api/v1/profile/picture/upload (legacy/backward compatible)
    - PUT  /api/v1/profile/picture        (preferred)
    - DELETE /api/v1/profile/picture
    - POST /api/v1/profile/banner/upload (legacy/backward compatible)
    - PUT  /api/v1/profile/banner
    - DELETE /api/v1/profile/banner
  - Uploads expect multipart/form-data; keys:
    - profile_picture
    - profile_banner
  - Responses include `data.path` (storage relative path) and `data.url` (public URL via storage disk).

- UploadService
  - Centralized service for uploads (app/Services/UploadService.php).
  - Stores files on the `public` disk under:
    - profile_pictures/{user_id}/...
    - profile_banners/{user_id}/...
  - Deletes previous files on new upload, updates `users` table fields `profile_picture` / `profile_banner`.
  - Controller wrappers are in `ProfileController`.

- Migration
  - A migration adds `profile_picture` and `profile_banner` to `users`:
    - database/migrations/2025_11_17_000000_add_profile_picture_and_banner_to_users_table.php

- Storage symlink & helper command
  - You must expose storage to public via:
    - php artisan storage:link
  - A helper artisan command is provided to check/create the symlink:
    - php artisan storage:check-symlink
  - If you encounter Windows permission errors, run the commands as Administrator or follow Windows / Linux instructions below (see Troubleshooting).

- Vite & frontend assets
  - Backend contains a vite config and a package.json for building frontend assets (see vite.config.js).
  - If serving assets from backend, ensure `npm install` and `npm run dev` (or build) as needed.

# Setup quick start (backend)
1. Copy env and configure database / mail:
   - cp .env.example .env
   - php artisan key:generate
2. Install dependencies:
   - composer install
3. Migrate DB:
   - php artisan migrate
4. Make storage accessible:
   - php artisan storage:link
   - or, if issues: php artisan storage:check-symlink
5. Run:
   - php artisan serve

# API highlights
- Auth: /api/v1/login, /api/v1/logout, /api/v1/profile
- Cryptos: /api/v1/cryptos
- Admin (requires ADMIN role + auth:sanctum): /api/v1/admin/...
