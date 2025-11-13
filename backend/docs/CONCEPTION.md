# Document de Conception - Bitchest

## 1. Vue d'Ensemble du Système

Bitchest est une plateforme de gestion de cryptomonnaies qui permet aux utilisateurs d'acheter, vendre et gérer leur portefeuille de cryptomonnaies. Le système est divisé en deux parties principales : une interface client pour les utilisateurs finaux et une interface d'administration pour la gestion du système.

### Objectifs du Système
- Faciliter l'achat et la vente de cryptomonnaies
- Gérer les portefeuilles d'utilisateurs
- Administrer les cryptomonnaies disponibles
- Assurer la sécurité des transactions
- Fournir des statistiques et analyses

## 2. Architecture Technique

### Stack Technique
- **Backend**: Laravel 12.x (PHP 8.2+)
- **Frontend**: Vue.js 3 (Intégration possible)
- **Base de données**: MySQL
- **API**: RESTful avec Laravel Sanctum
- **Authentification**: Token-based (Sanctum)
- **Documentation API**: Swagger/OpenAPI

### Architecture MVC
Le système suit l'architecture MVC (Model-View-Controller) de Laravel :
- **Models**: Représentation des données et logique métier
- **Controllers**: Gestion des requêtes HTTP et réponses
- **Views**: Retour JSON pour l'API

## 3. Diagramme de Classes

```mermaid
classDiagram
    class User {
        -id: int
        -name: string
        -email: string
        -email_verified_at: timestamp
        -password: string
        -role: enum(ADMIN, CLIENT)
        -remember_token: string
        -created_at: timestamp
        -updated_at: timestamp
        +wallet(): HasOne
        +notifications(): HasMany
        +transactions(): HasManyThrough
        +isAdmin(): bool
        +isClient(): bool
    }

    class Wallet {
        -id: int
        -user_id: int
        -solde: decimal(10,2)
        -created_at: timestamp
        -updated_at: timestamp
        +user(): BelongsTo
        +assets(): HasMany
        +transactions(): HasMany
        +addFunds(amount): void
        +removeFunds(amount): void
    }

    class Cryptomoney {
        -id: int
        -nom: string
        -symbole: string
        -prix_actuel: decimal(10,2)
        -coingecko_id: string
        -logo_url: string
        -description: text
        -created_at: timestamp
        -updated_at: timestamp
        +transactions(): HasMany
        +assets(): HasMany
        +priceHistory(): HasMany
        +updatePrice(newPrice): void
    }

    class CryptoWalletAsset {
        -id: int
        -wallet_id: int
        -cryptomoney_id: int
        -quantite: decimal(20,8)
        -prix_achat: decimal(10,2)
        -created_at: timestamp
        -updated_at: timestamp
        +wallet(): BelongsTo
        +cryptomoney(): BelongsTo
        +addQuantity(amount): void
        +removeQuantity(amount): void
    }

    class Transaction {
        -id: int
        -wallet_id: int
        -cryptomoney_id: int
        -type: enum(ACHAT, VENTE)
        -quantite: decimal(20,8)
        -prix_unitaire: decimal(10,2)
        -montant_total: decimal(10,2)
        -status: enum(PENDING, COMPLETED, CANCELLED)
        -created_at: timestamp
        -updated_at: timestamp
        +wallet(): BelongsTo
        +cryptomoney(): BelongsTo
        +complete(): void
        +cancel(): void
    }

    class PriceHistory {
        -id: int
        -cryptomoney_id: int
        -prix: decimal(10,2)
        -timestamp: timestamp
        -created_at: timestamp
        +cryptomoney(): BelongsTo
    }

    class Notification {
        -id: int
        -user_id: int
        -message: string
        -type: string
        -read_at: timestamp
        -created_at: timestamp
        -updated_at: timestamp
        +user(): BelongsTo
        +markAsRead(): void
    }

    User "1" -- "1" Wallet : has
    Wallet "1" -- "*" CryptoWalletAsset : contains
    Wallet "1" -- "*" Transaction : has
    CryptoWalletAsset "*" -- "1" Cryptomoney : references
    Transaction "*" -- "1" Cryptomoney : references
    Cryptomoney "1" -- "*" PriceHistory : has
    User "1" -- "*" Notification : receives
    Transaction ..> Wallet : affects balance
    CryptoWalletAsset ..> Wallet : affects assets
```

## 4. Diagrammes de Cas d'Utilisation

### 4.1 Cas d'Utilisation - Client

```mermaid
graph TD
    subgraph "Client Use Cases"
        Client[Client]
        
        UC1[S'inscrire]
        UC2[Se connecter]
        UC3[Consulter profil]
        UC4[Modifier profil]
        UC5[Consulter portefeuille]
        UC6[Acheter cryptomonnaie]
        UC7[Vendre cryptomonnaie]
        UC8[Consulter historique transactions]
        UC9[Consulter plus-value]
        UC10[Consulter prix cryptos]
        UC11[Consulter notifications]
        UC12[Se déconnecter]
        
        Client --> UC1
        Client --> UC2
        Client --> UC3
        Client --> UC4
        Client --> UC5
        Client --> UC6
        Client --> UC7
        Client --> UC8
        Client --> UC9
        Client --> UC10
        Client --> UC11
        Client --> UC12
        
        UC2 -.-> |include| UC1
        UC6 -.-> |include| UC5
        UC7 -.-> |include| UC5
        UC8 -.-> |include| UC5
        UC9 -.-> |include| UC5
    end
```

### 4.2 Cas d'Utilisation - Administrateur

```mermaid
graph TD
    subgraph "Admin Use Cases"
        Admin[Administrateur]
        
        UC1[Se connecter]
        UC2[Gérer cryptomonnaies]
        UC3[Ajouter cryptomonnaie]
        UC4[Modifier cryptomonnaie]
        UC5[Supprimer cryptomonnaie]
        UC6[Consulter statistiques]
        UC7[Gérer utilisateurs]
        UC8[Activer/Désactiver compte]
        UC9[Consulter transactions]
        UC10[Annuler transaction]
        UC11[Synchroniser prix]
        UC12[Gérer notifications système]
        UC13[Exporter données]
        UC14[Se déconnecter]
        
        Admin --> UC1
        Admin --> UC2
        Admin --> UC3
        Admin --> UC4
        Admin --> UC5
        Admin --> UC6
        Admin --> UC7
        Admin --> UC8
        Admin --> UC9
        Admin --> UC10
        Admin --> UC11
        Admin --> UC12
        Admin --> UC13
        Admin --> UC14
        
        UC2 -.-> |include| UC3
        UC2 -.-> |include| UC4
        UC2 -.-> |include| UC5
        UC7 -.-> |include| UC8
        UC9 -.-> |include| UC10
    end
```

## 5. Diagrammes de Séquence

### 5.1 Authentification

```mermaid
sequenceDiagram
    participant U as Utilisateur
    participant API as API
    participant Auth as AuthController
    participant DB as Base de données
    participant Token as Sanctum

    U->>API: POST /api/v1/login
    API->>Auth: Valider données
    Auth->>DB: Vérifier email
    DB-->>Auth: Utilisateur trouvé
    Auth->>DB: Vérifier mot de passe
    DB-->>Auth: Mot de passe correct
    Auth->>Token: Créer token
    Token-->>Auth: Token généré
    Auth->>DB: Enregistrer token
    Auth-->>API: Response JSON avec token
    API-->>U: 200 OK + Token
    
    Note over U,Token: Token stocké côté client pour authentification future
```

### 5.2 Achat de Cryptomonnaie

```mermaid
sequenceDiagram
    participant C as Client
    participant API as API
    participant Auth as Middleware
    participant Trans as TransactionController
    participant Wallet as WalletService
    participant DB as Base de données
    participant Crypto as CryptoService

    C->>API: POST /api/v1/portefeuille/acheter
    Note over C,API: Headers: Authorization: Bearer {token}
    API->>Auth: Vérifier token
    Auth-->>API: Token valide
    API->>Trans: Valider données
    Trans->>Wallet: Vérifier solde
    Wallet->>DB: SELECT solde FROM wallets
    DB-->>Wallet: Solde actuel
    Wallet-->>Trans: Solde suffisant
    Trans->>Crypto: Vérifier crypto existe
    Crypto->>DB: SELECT * FROM cryptomoneys
    DB-->>Crypto: Crypto trouvée
    Crypto-->>Trans: Crypto valide
    Trans->>Wallet: Créer transaction
    Wallet->>DB: BEGIN TRANSACTION
    Wallet->>DB: INSERT INTO transactions
    Wallet->>DB: UPDATE wallets SET solde = solde - montant
    Wallet->>DB: UPDATE crypto_wallet_assets SET quantite = quantite + quantite_achetee
    Wallet->>DB: COMMIT
    DB-->>Wallet: Transaction réussie
    Wallet-->>Trans: Succès
    Trans-->>API: Response JSON
    API-->>C: 201 Created + Détails transaction
```

### 5.3 Vente de Cryptomonnaie

```mermaid
sequenceDiagram
    participant C as Client
    participant API as API
    participant Auth as Middleware
    participant Trans as TransactionController
    participant Wallet as WalletService
    participant DB as Base de données
    participant Crypto as CryptoService

    C->>API: POST /api/v1/portefeuille/vendre
    Note over C,API: Headers: Authorization: Bearer {token}
    API->>Auth: Vérifier token
    Auth-->>API: Token valide
    API->>Trans: Valider données
    Trans->>Wallet: Vérifier actifs
    Wallet->>DB: SELECT quantite FROM crypto_wallet_assets
    DB-->>Wallet: Quantité disponible
    Wallet-->>Trans: Quantité suffisante
    Trans->>Crypto: Vérifier crypto existe
    Crypto->>DB: SELECT * FROM cryptomoneys
    DB-->>Crypto: Crypto trouvée
    Crypto-->>Trans: Crypto valide
    Trans->>Wallet: Créer transaction de vente
    Wallet->>DB: BEGIN TRANSACTION
    Wallet->>DB: INSERT INTO transactions (type: VENTE)
    Wallet->>DB: UPDATE wallets SET solde = solde + montant
    Wallet->>DB: UPDATE crypto_wallet_assets SET quantite = quantite - quantite_vendue
    Wallet->>DB: COMMIT
    DB-->>Wallet: Transaction réussie
    Wallet-->>Trans: Succès
    Trans-->>API: Response JSON
    API-->>C: 201 Created + Détails transaction
```

### 5.4 Gestion des Cryptomonnaies (Admin - POST/PUT)

```mermaid
sequenceDiagram
    participant A as Admin
    participant API as API
    participant Auth as Middleware
    participant Admin as AdminController
    participant Val as Validation
    participant DB as Base de données
    participant Cache as Cache

    A->>API: POST/PUT /api/v1/admin/cryptos
    Note over A,API: Headers: Authorization: Bearer {token}
    API->>Auth: Vérifier token + rôle admin
    Auth-->>API: Token valide et rôle admin
    API->>Admin: Valider données
    Admin->>Val: Règles de validation
    Val-->>Admin: Données valides
    
    alt POST (Création)
        Admin->>DB: INSERT INTO cryptomoneys
        DB-->>Admin: Crypto créée
        Admin->>Cache: Invalid cache
        Admin-->>API: Response JSON
        API-->>A: 201 Created + Détails crypto
    else PUT (Modification)
        Admin->>DB: UPDATE cryptomoneys SET ...
        DB-->>Admin: Crypto mise à jour
        Admin->>Cache: Invalid cache
        Admin-->>API: Response JSON
        API-->>A: 200 OK + Détails crypto
    end
```

## 6. Services et Composants

### 6.1 Services Principaux

#### WalletService
- Gestion du solde du portefeuille
- Vérification des fonds disponibles
- Calcul des plus-values
- Historique des transactions

#### CryptoService
- Synchronisation des prix via CoinGecko API
- Gestion des cryptomonnaies
- Calcul des valeurs actuelles
- Historique des prix

#### TransactionService
- Traitement des achats/ventes
- Validation des transactions
- Gestion des états des transactions
- Notifications de transaction

#### AuthService
- Authentification des utilisateurs
- Gestion des rôles et permissions
- Génération et validation des tokens
- Réinitialisation de mot de passe

### 6.2 Middleware

#### AdminMiddleware
- Vérifie que l'utilisateur est administrateur
- Bloque l'accès aux routes admin pour les clients

#### AuthMiddleware
- Vérifie la validité du token d'authentification
- Ajoute l'utilisateur authentifié à la requête

### 6.3 Jobs et Files d'Attente

#### SyncCryptoPricesJob
- Synchronise les prix des cryptomonnaies
- Exécuté toutes les heures via cron
- Utilise l'API CoinGecko

#### SendNotificationJob
- Envoie les notifications par email
- Gère les notifications en file d'attente

## 7. Sécurité

### 7.1 Authentification
- Tokens JWT via Laravel Sanctum
- Expiration des tokens configurée
- Refresh tokens possibles

### 7.2 Validation des Données
- Validation côté serveur pour toutes les entrées
- Protection contre les injections SQL
- Sanitization des données

### 7.3 Chiffrement
- Mots de passe hashés avec bcrypt
- Communications HTTPS recommandées
- Données sensibles chiffrées

### 7.4 Limitation de Requêtes
- Rate limiting sur les endpoints API
- Protection contre les attaques par force brute
- Limitation par IP et par utilisateur

## 8. Performance

### 8.1 Cache
- Cache Redis pour les prix des cryptos
- Cache des requêtes fréquentes
- Invalidation intelligente du cache

### 8.2 Optimisation Base de Données
- Index sur les colonnes fréquemment recherchées
- Relations eager loading
- Pagination des résultats

### 8.3 Optimisation des Requêtes
- Requêtes optimisées avec Eloquent
- Évitement des requêtes N+1
- Utilisation de collections Laravel

## 9. Tests

### 9.1 Tests Unitaires
- Tests des modèles
- Tests des services
- Tests des helpers

### 9.2 Tests d'Intégration
- Tests des contrôleurs
- Tests des endpoints API
- Tests des middlewares

### 9.3 Tests de Sécurité
- Tests d'authentification
- Tests d'autorisation
- Tests de validation

## 10. Déploiement

### 10.1 Configuration Production
- Variables d'environnement sécurisées
- Base de données optimisée
- Serveur web configuré
- SSL activé

### 10.2 Monitoring
- Logs d'erreurs
- Monitoring des performances
- Alertes en cas de problème
- Backup automatique

## 11. Maintenance

### 11.1 Mises à Jour
- Mise à jour régulière des dépendances
- Patchs de sécurité
- Mise à jour des prix des cryptos

### 11.2 Backup
- Backup quotidien de la base de données
- Backup des fichiers importants
- Tests de restauration réguliers

Ce document de conception est évolutif et doit être mis à jour en fonction des évolutions du système.