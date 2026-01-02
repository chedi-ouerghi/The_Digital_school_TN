# BitChest — Présentation générale

Table des matières
- Présentation
- Architecture et dossiers
- Fonctionnalités principales
- Prérequis
- Utilisation (exemples d'usage)
- Endpoints API (résumé)

Description générale
BitChest est une plateforme complète de gestion et de trading de cryptomonnaies. Le dépôt contient un backend (API Laravel) et un frontend (application Vue 3 + Vite). Les README spécifiques dans `backend/` et `frontend/` donnent des détails techniques et des guides d'installation.

Architecture et dossiers (présentation)
- `backend/` : API Laravel, contrôleurs, modèles, services métier, migrations et commandes artisan personnalisées.
- `frontend/` : application Vue 3 (pages client/admin, composants UI, services API, routage et styles).
- `docs/` : diagrammes, scénarios, et documentation additionnelle.
- `public/` et `storage/` : assets publics et fichiers uploadés.

Fonctionnalités principales
- API RESTful pour la gestion des utilisateurs, portefeuilles et transactions.
- Authentification et autorisation avec rôles (client/admin) via Laravel Sanctum.
- Gestion complète des cryptomonnaies (CRUD) et synchronisation des cours.
- Upload et gestion d'images (profil, bannière, images cryptos).
- Interface SPA réactive, responsive et accessible (Vue 3, Tailwind, Vite).
- Graphiques et tableaux pour visualisation des performances et transactions.
- Tâches asynchrones et file d'attente pour traitements lourds.
- Tests unitaires et fonctionnels pour backend et frontend.

Prérequis
- PHP 8.2+ et Composer pour le backend.
- Node.js 18+ et npm 9+ pour le frontend.
- Base de données relationnelle (MySQL/MariaDB) ou équivalent.
- Git pour le versioning. Docker est optionnel pour conteneuriser l'environnement.

Utilisation (cas d'usage principaux)
- Développement local : cloner le dépôt, configurer les `.env` pour `backend` et `frontend`, installer les dépendances, lancer les migrations, et démarrer les serveurs backend et frontend.
- Déploiement : préparer les variables d'environnement de production, exécuter les migrations, optimiser l'application Laravel et déployer les fichiers frontend compilés sur un serveur web.
- Exploitation : surveiller les workers de queue, vérifier les symlinks de `storage` et gérer les backups de la base de données.

Endpoints API (aperçu, une phrase chacun)
- POST /api/v1/login — authentifie un utilisateur et retourne un token.
- POST /api/v1/logout — termine la session de l'utilisateur connecté.
- GET /api/v1/profile — renvoie les informations du profil de l'utilisateur connecté.
- GET /api/v1/cryptos — liste publique des cryptomonnaies disponibles.
- GET /api/v1/cryptos/{id}/history — fournit l'historique des prix pour une crypto.
- GET /api/v1/wallets — affiche le portefeuille de l'utilisateur et ses actifs.
- POST /api/v1/wallets/transaction — crée une transaction d'achat ou de vente.
- GET /api/v1/notifications — liste les notifications de l'utilisateur.
- Routes administrateur (/api/v1/admin/*) — gestion des cryptomonnaies, clients et transactions par les administrateurs.

Pour plus de détails techniques, voir `backend/README.md` et `frontend/README.md`.

Dernière mise à jour : 2025

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

Dernière mise à jour : 2025
