# Documentation des Routes API - Bitchest

## Routes Publiques

### Authentication (Authentification)

#### `POST /v1/login`
Permet aux utilisateurs de se connecter avec leurs identifiants (email/identifiant et mot de passe). Retourne un token d'authentification Sanctum pour les appels authentifiés.

#### `POST /v1/request-account`
Permet aux nouveaux utilisateurs de demander la création d'un compte. Un email de confirmation est envoyé à l'administrateur pour approbation.

#### `POST /v1/verify-email`
Vérifie et valide l'email de l'utilisateur lors de la création de compte. Cette route confirme que l'utilisateur a accès à l'adresse email fournie.

---

### Cryptomonnaies (Public)

#### `GET /v1/cryptos`
Récupère la liste de toutes les cryptomonnaies disponibles avec leurs informations actuelles (nom, symbole, prix, etc.). Cette route fournit les données globales pour afficher le catalogue des cryptos.

#### `GET /v1/cryptos/{id}`
Affiche les détails complets d'une cryptomonnaie spécifique. Comprend les informations détaillées comme la description, le logo, la capitalisation boursière et autres métriques.

#### `GET /v1/cryptos/{id}/history`
Retourne l'historique des prix d'une cryptomonnaie sur une période donnée. Ces données permettent de tracer les graphiques et analyser les tendances du marché.

---

### Blogs (Public)

#### `GET /v1/blogs`
Récupère la liste de tous les articles de blog publiés. Affiche les résumés des articles avec les informations essentielles (titre, auteur, date, extrait).

#### `GET /v1/blogs/{slug}`
Affiche l'article de blog complet en utilisant son slug URL. Retourne le contenu détaillé, les commentaires et autres informations de l'article.

---

## Routes Authentifiées

### Authentification et Profil

#### `POST /v1/logout`
Déconnecte l'utilisateur actuel et invalide son token Sanctum. Cette route supprime la session active et termine l'authentification.

#### `GET /v1/profile`
Retourne les informations du profil complet de l'utilisateur connecté. Inclut les données personnelles, les paramètres et les préférences.

#### `PUT /v1/profile`
Met à jour les informations du profil de l'utilisateur (nom, prénom, bio, etc.). Permet à l'utilisateur de modifier ses informations personnelles.

#### `PUT /v1/profile/password`
Permet à l'utilisateur de modifier son mot de passe en fournissant l'ancien et le nouveau. Sécurise la gestion des accès en changeant les identifiants d'authentification.

---

### Gestion du Profil

#### `GET /v1/profile/stats`
Récupère un aperçu des statistiques du profil (nombre de transactions, portefeuille total, historique, etc.). Fournit un résumé des activités et performances de l'utilisateur.

#### `POST /v1/profile/picture/upload`
Upload une nouvelle photo de profil de l'utilisateur. L'image est stockée et remplace la précédente.

#### `PUT /v1/profile/picture`
Met à jour la photo de profil existante avec une nouvelle image. Permet de modifier la photo sans passer par une nouvelle création.

#### `DELETE /v1/profile/picture`
Supprime la photo de profil actuelle. L'utilisateur retrouvera une image par défaut ou vide.

#### `POST /v1/profile/banner/upload`
Upload une nouvelle bannière (image de couverture) pour le profil utilisateur. La bannière s'affiche en haut du profil.

#### `PUT /v1/profile/banner`
Met à jour la bannière existante avec une nouvelle image. Permet de changer l'image de couverture du profil.

#### `DELETE /v1/profile/banner`
Supprime la bannière actuelle du profil. Le profil n'affichera plus d'image de couverture.

---

### Portefeuille et Transactions

#### `GET /v1/wallets`
Récupère la liste de tous les portefeuilles de l'utilisateur avec leurs soldes actuels. Affiche un aperçu complet des actifs cryptographiques possédés.

#### `GET /v1/wallets/plus-value`
Retourne les données de plus-value/moins-value du portefeuille (gains et pertes réalisés). Permet de visualiser la performance financière des investissements.

#### `GET /v1/wallets/history`
Affiche l'historique complet de toutes les transactions du portefeuille. Fournit un journal détaillé des mouvements d'actifs.

#### `GET /v1/wallets/{id}/history`
Retourne l'historique des transactions pour un portefeuille spécifique. Permet de suivre les mouvements d'une cryptomonnaie particulière.

#### `GET /v1/wallets/{id}`
Affiche les détails complets d'un portefeuille spécifique (solde, historique des transactions, etc.). Fournit une vue détaillée d'un actif particulier.

#### `POST /v1/wallets/transaction`
Crée une nouvelle transaction (achat ou vente) de cryptomonnaie. Valide les fonds disponibles et enregistre le mouvement d'actifs.

---

### Notifications

#### `GET /v1/notifications`
Récupère la liste de toutes les notifications de l'utilisateur (lues et non lues). Affiche les mises à jour, alertes et messages importants.

#### `PUT /v1/notifications/{id}/read`
Marque une notification comme lue. Met à jour le statut de lecture d'une notification spécifique.

---

## Routes Admin

### Admin - Blogs

#### `POST /v1/admin/blogs`
Crée un nouvel article de blog. L'administrateur peut ajouter du contenu avec titre, description et contenu complet.

#### `PUT /v1/admin/blogs/{id}`
Met à jour un article de blog existant. Permet de modifier le titre, le contenu, les tags et autres propriétés.

#### `DELETE /v1/admin/blogs/{id}`
Supprime un article de blog de la plateforme. L'article n'est plus accessible aux utilisateurs.

---

### Admin - Cryptomonnaies

#### `POST /v1/admin/cryptos`
Ajoute une nouvelle cryptomonnaie à la plateforme. Enregistre les informations de base et les paramètres de configuration.

#### `PUT /v1/admin/cryptos/{id}`
Met à jour les informations d'une cryptomonnaie existante. Permet de modifier les détails, les icônes, les descriptions et les paramètres.

#### `DELETE /v1/admin/cryptos/{id}`
Supprime une cryptomonnaie de la plateforme. Elle ne sera plus disponible pour les transactions utilisateurs.

#### `POST /v1/admin/cryptos/sync-history`
Synchronise l'historique des prix de toutes les cryptomonnaies avec les sources externes. Met à jour les données historiques et les prix actuels.

---

### Admin - Clients

#### `GET /v1/admin/clients`
Récupère la liste de tous les clients/utilisateurs du système. Affiche un tableau avec les informations essentielles de chaque utilisateur.

#### `POST /v1/admin/clients`
Crée manuellement un nouveau compte utilisateur par l'administrateur. Contourne le processus de demande standard pour créer directement un compte.

#### `GET /v1/admin/clients/{id}`
Affiche les informations détaillées d'un client spécifique. Inclut le profil complet, les statistiques et l'historique.

#### `PUT /v1/admin/clients/{id}`
Met à jour les informations d'un client (données personnelles, statut, permissions, etc.). Permet la gestion et la correction des profils utilisateurs.

#### `DELETE /v1/admin/clients/{id}`
Supprime un compte client de la plateforme. L'utilisateur ne pourra plus se connecter et ses données seront archivées.

#### `GET /v1/admin/clients/{id}/transactions`
Récupère toutes les transactions effectuées par un client spécifique. Permet de vérifier l'activité et l'historique d'un utilisateur.

---

### Admin - Demandes de Compte

#### `GET /v1/admin/account-requests`
Récupère la liste de toutes les demandes de création de compte en attente d'approbation. Affiche les demandes soumises par les nouveaux utilisateurs.

#### `POST /v1/admin/account-requests/{id}/approve`
Approuve une demande de compte et active le compte utilisateur. L'utilisateur reçoit une notification et peut se connecter.

#### `POST /v1/admin/account-requests/{id}/reject`
Rejette une demande de compte et notifie l'utilisateur. La demande est archivée et l'utilisateur peut réessayer plus tard.

---

### Admin - Transactions

#### `GET /v1/admin/transactions`
Récupère la liste de toutes les transactions du système (tous les utilisateurs). Affiche un journal centralisé de l'activité financière.

#### `GET /v1/admin/transactions/{id}`
Affiche les détails complets d'une transaction spécifique. Inclut les informations complètes sur la transaction et ses modifications.

#### `POST /v1/admin/transactions/{id}/cancel`
Annule une transaction existante et revient les montants aux comptes appropriés. Permet de corriger les erreurs ou les transactions frauduleuses.

---

### Admin - Statistiques

#### `GET /v1/admin/stats`
Récupère les statistiques globales de la plateforme (nombre d'utilisateurs, volume de transactions, revenue, etc.). Fournit un tableau de bord avec les métriques principales.

---

### Admin - Authentification Spéciale

#### `PUT /v1/change-id`
Permet à l'administrateur de changer l'identifiant/login d'un compte utilisateur. Fonction de gestion administrative pour modifier les credentials.

---

### Admin - Accès au Portefeuille Client

#### `GET /v1/admin/clients/{id}/wallet`
Affiche le portefeuille complet d'un client spécifique du point de vue administrateur. Permet de vérifier les actifs et l'activité d'un utilisateur.

