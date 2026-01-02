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
# BitChest — Backend API (Laravel)

Ce document décrit l'installation, la configuration, l'exécution et le déploiement du backend BitChest.

## Vue d'ensemble

Le backend expose une API REST (version v1) pour :
- Authentification (Sanctum)
- Gestion des utilisateurs (clients, admins)
- Portefeuilles et actifs crypto
- Transactions (achat/vente)
- Notifications
- Gestion des cryptomonnaies et synchronisation (CoinGecko)

## Prérequis

- PHP 8.2+
- Composer
- MySQL 5.7+ ou équivalent
- Node.js 18+ (pour assets Vite/Tailwind)
- Git

## Installation (développement)

1. Cloner le dépôt et se placer dans `backend/` :

```powershell
git clone <repo-url>
cd backend
```

2. Installer les dépendances PHP :

```powershell
composer install
```

3. Copier et adapter l'environnement :

```powershell
copy .env.example .env
```

Configurer au minimum : `APP_URL`, `DB_*`, `SANCTUM_STATEFUL_DOMAINS`, `MAIL_*`.

4. Générer la clé d'application :

```powershell
php artisan key:generate
```

5. Installer les dépendances Node (assets) :

```powershell
npm install
```

6. Créer la base de données et exécuter les migrations :

```powershell
php artisan migrate --seed
```

7. Lier le dossier de stockage public :

```powershell
php artisan storage:link
```

8. Lancer le serveur de développement :

```powershell
php artisan serve --host=127.0.0.1 --port=8000
```

## Variables importantes (.env)

- APP_URL=http://localhost:8000
- DB_CONNECTION, DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME, DB_PASSWORD
- SANCTUM_STATEFUL_DOMAINS=localhost:5173,localhost:3000
# BitChest — Backend (présentation)

Table des matières
- Présentation
- Architecture et dossiers
- Fonctionnalités
- Prérequis
- Utilisation (cas d'usage)
- Endpoints API (résumé)

Description
Le backend BitChest est une API développée avec Laravel. Il fournit les services métier essentiels : authentification, gestion des comptes et portefeuilles, traitement des transactions, gestion des cryptomonnaies, stockage des fichiers et envoi de notifications.

Architecture et dossiers (présentation)
- `app/Http/Controllers` : contrôleurs exposant la logique des endpoints.
- `app/Models` : modèles Eloquent représentant les entités (User, Wallet, Transaction, Cryptomoney, Notification).
- `app/Services` : services métier (upload, profil, transaction) qui encapsulent la logique réutilisable.
- `routes/api.php` : définition des routes API versionnées.
- `database/migrations` et `database/seeders` : schéma et données de test.
- `storage/app/public` : stockage des fichiers publics (photos, bannières, images de cryptos).

Fonctionnalités principales
- API RESTful pour toutes les opérations clients et administrateurs.
- Authentification et autorisation avec rôles (client / admin) via Sanctum.
- Création et gestion automatique de portefeuilles utilisateur.
- Traitement sécurisé des transactions (achat/vente), historique et calcul des plus-values.
- Intégration et synchronisation des prix via CoinGecko.
- Upload et gestion sécurisée des images utilisateur et des médias de cryptomonnaies.
- Jobs asynchrones et file d'attente pour opérations longues.
- Tests unitaires et fonctionnels pour assurer la qualité.

Prérequis
- PHP 8.2 ou supérieur et Composer.
- MySQL / MariaDB ou équivalent.
- Node.js et npm (pour la compilation d'assets si besoin).
- Accès à un service SMTP pour l'envoi d'e-mails (optionnel mais recommandé).

Utilisation (grands cas d'usage)
- Préparer l'environnement : copier le fichier ` .env` depuis ` .env.example`, configurer la base de données, les variables Sanctum et la configuration mail.
- Préparer la base : exécuter les migrations et seeders pour initialiser le schéma et les données de test.
- Gérer les fichiers : s'assurer que le dossier `storage` est lié au dossier public pour servir les uploads.
- Surveillance : surveiller les workers de queue et les logs pour assurer le traitement asynchrone des tâches.

Endpoints API (aperçu, une phrase chacun)
- POST /api/v1/login — authentifie un utilisateur et délivre un token.
- POST /api/v1/logout — termine la session de l'utilisateur.
- GET /api/v1/profile — récupère les informations du profil authentifié.
- PUT /api/v1/profile — met à jour les informations du profil.
- GET /api/v1/cryptos — liste publique des cryptomonnaies.
- GET /api/v1/cryptos/{id}/history — historique des prix d'une cryptomonnaie.
- GET /api/v1/wallets — affiche le portefeuille et ses positions pour l'utilisateur.
- POST /api/v1/wallets/transaction — enregistre une transaction d'achat ou de vente.
- GET /api/v1/notifications — liste les notifications utilisateur.
- Routes administrateur (/api/v1/admin/*) — gestion des cryptomonnaies, clients et transactions par des APIs réservées aux administrateurs.

Pour des informations techniques détaillées, importer la collection `postman_collection.json` fournie et consulter les fichiers dans `app/` et `routes/`.

Dernière mise à jour : 2025
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
