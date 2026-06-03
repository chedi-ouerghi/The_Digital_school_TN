# Scénario d'Utilisation - Administrateur

## Vue d'Ensemble
Ce document décrit le scénario complet d'utilisation du système Bitchest du point de vue de l'administrateur, étape par étape.

## Prérequis
- Compte administrateur créé et activé
- Accès au panneau d'administration
- Permissions administrateur configurées

---

## Scénario 1: Première Connexion et Configuration

### Étape 1: Authentification
1. **Accès à l'interface** : L'administrateur accède à l'URL du système
2. **Page de connexion** : Il saisit ses identifiants (email/mot de passe)
3. **Validation** : Le système vérifie les identifiants et le rôle administrateur
4. **Token généré** : Un token d'authentification est créé et stocké
5. **Redirection** : Redirection vers le tableau de bord administrateur

### Étape 2: Exploration du Tableau de Bord
1. **Statistiques générales** : Vue d'ensemble des utilisateurs, transactions, cryptos
2. **Graphiques** : Visualisation des tendances et statistiques
3. **Alertes** : Vérification des notifications et alertes système
4. **Menu de navigation** : Exploration des différentes sections disponibles

---

## Scénario 2: Gestion des Cryptomonnaies

### 2.1 Ajout d'une Nouvelle Cryptomonnaie

#### Étape 1: Accès à la Gestion des Cryptos
1. **Navigation** : Clic sur "Gestion des Cryptomonnaies" dans le menu
2. **Vue liste** : Affichage de toutes les cryptomonnaies actuelles
3. **Bouton ajout** : Clic sur "Ajouter une cryptomonnaie"

#### Étape 2: Formulaire d'Ajout
1. **Nom** : Saisie du nom complet (ex: "Bitcoin")
2. **Symbole** : Saisie du symbole (ex: "BTC")
3. **Prix initial** : Saisie du prix actuel
4. **ID CoinGecko** : Saisie de l'identifiant API CoinGecko
5. **Logo** : Upload de l'image du logo
6. **Description** : Rédaction d'une description détaillée
7. **Validation** : Vérification des données saisies
8. **Soumission** : Envoi du formulaire

#### Étape 3: Confirmation et Synchronisation
1. **Création** : Le système crée la cryptomonnaie en base
2. **Synchronisation** : Premier appel à l'API CoinGecko pour les données
3. **Confirmation** : Message de succès affiché
4. **Mise à jour** : La nouvelle crypto apparaît dans la liste

### 2.2 Modification d'une Cryptomonnaie

#### Étape 1: Sélection
1. **Recherche** : Trouver la cryptomonnaie à modifier
2. **Action** : Clic sur le bouton "Modifier"
3. **Chargement** : Formulaire pré-rempli avec les données actuelles

#### Étape 2: Modification
1. **Champs modifiables** : Nom, prix, description, logo
2. **Validation** : Vérification des modifications
3. **Aperçu** : Prévisualisation des changements
4. **Confirmation** : Validation finale

#### Étape 3: Mise à Jour
1. **Enregistrement** : Sauvegarde des modifications
2. **Cache** : Invalidation du cache associé
3. **Notification** : Message de confirmation
4. **Historique** : Enregistrement dans les logs

### 2.3 Suppression d'une Cryptomonnaie

#### Étape 1: Précautions
1. **Vérification** : Vérifier qu'aucun utilisateur ne possède cette crypto
2. **Avertissement** : Message d'avertissement si des actifs existent
3. **Confirmation** : Demande de confirmation explicite

#### Étape 2: Suppression
1. **Suppression logique** : Marquage comme supprimé
2. **Cascade** : Gestion des relations (transactions, actifs)
3. **Nettoyage** : Suppression des données associées si nécessaire
4. **Journalisation** : Enregistrement de l'action

---

## Scénario 3: Gestion des Utilisateurs

### 3.1 Vue d'Ensemble des Utilisateurs

#### Étape 1: Accès à la Liste
1. **Navigation** : Menu "Gestion des Utilisateurs"
2. **Filtres** : Application de filtres (rôle, statut, date)
3. **Recherche** : Recherche par nom, email
4. **Tri** : Tri par différentes colonnes

#### Étape 2: Détails d'un Utilisateur
1. **Sélection** : Clic sur un utilisateur
2. **Informations** : Vue détaillée du profil
3. **Portefeuille** : Aperçu du portefeuille et des actifs
4. **Historique** : Historique des transactions
5. **Activité** : Journal d'activité récent

### 3.2 Gestion des Comptes

#### Étape 1: Activation/Désactivation
1. **Statut actuel** : Vérification du statut du compte
2. **Action** : Basculer entre actif/inactif
3. **Justification** : Optionnelle, pour la traçabilité
4. **Notification** : Message de confirmation

#### Étape 2: Modification des Droits
1. **Changement de rôle** : Promotion/rétrogradation
2. **Permissions** : Ajustement des permissions spécifiques
3. **Validation** : Confirmation des changements
4. **Impact** : Analyse de l'impact sur le système

---

## Scénario 4: Surveillance des Transactions

### 4.1 Vue Globale des Transactions

#### Étape 1: Tableau de Bord Transactions
1. **Statistiques** : Nombre total, volume, tendances
2. **Graphiques** : Répartition achat/vente, par crypto
3. **Alertes** : Transactions suspectes ou anormales
4. **Temps réel** : Mise à jour en temps réel (WebSocket)

#### Étape 2: Analyse des Transactions
1. **Filtres avancés** : Date, montant, type, statut
2. **Recherche** : Par utilisateur, par crypto
3. **Export** : Export des données (CSV, PDF)
4. **Impression** : Génération de rapports

### 4.2 Gestion des Transactions Problématiques

#### Étape 1: Détection d'Anomalies
1. **Alertes automatiques** : Système de détection d'anomalies
2. **Revue manuelle** : Examen des transactions signalées
3. **Analyse** : Analyse des patterns suspects

#### Étape 2: Actions Correctives
1. **Suspension** : Suspension temporaire d'une transaction
2. **Annulation** : Annulation définitive avec justification
3. **Investigation** : Enquête approfondie si nécessaire
4. **Communication** : Notification des parties concernées

---

## Scénario 5: Synchronisation et Maintenance

### 5.1 Synchronisation des Prix

#### Étape 1: Configuration
1. **API CoinGecko** : Configuration de l'API
2. **Intervalle** : Définition de la fréquence de synchronisation
3. **Cryptos actives** : Sélection des cryptos à synchroniser

#### Étape 2: Processus de Synchronisation
1. **Lancement manuel** : Via le bouton "Synchroniser maintenant"
2. **Automatique** : Tâche planifiée (cron job)
3. **Progression** : Affichage de la progression
4. **Résultats** : Résumé de la synchronisation
5. **Erreurs** : Gestion et affichage des erreurs

### 5.2 Maintenance Système

#### Étape 1: Sauvegardes
1. **Planification** : Configuration des sauvegardes automatiques
2. **Manuelle** : Lancement d'une sauvegarde immédiate
3. **Vérification** : Test de restauration des sauvegardes
4. **Nettoyage** : Suppression des anciennes sauvegardes

#### Étape 2: Optimisation
1. **Nettoyage** : Nettoyage des données obsolètes
2. **Réindexation** : Réindexation des tables
3. **Cache** : Vidage et reconstruction du cache
4. **Logs** : Rotation et archivage des logs

---

## Scénario 6: Rapports et Analyses

### 6.1 Génération de Rapports

#### Étape 1: Configuration du Rapport
1. **Type de rapport** : Sélection du type (utilisateurs, transactions, finances)
2. **Période** : Définition de la période d'analyse
3. **Filtres** : Application de filtres spécifiques
4. **Format** : Choix du format (PDF, Excel, CSV)

#### Étape 2: Génération et Export
1. **Génération** : Création du rapport
2. **Aperçu** : Visualisation avant téléchargement
3. **Export** : Téléchargement du fichier
4. **Partage** : Option de partage par email

### 6.2 Analyses Avancées

#### Étape 1: Tableau de Bord Analytique
1. **KPIs** : Indicateurs clés de performance
2. **Tendances** : Analyse des tendances historiques
3. **Comparaisons** : Comparaison entre périodes
4. **Prédictions** : Projections basées sur les données

---

## Scénario 7: Support et Communication

### 7.1 Support Utilisateurs

#### Étape 1: Gestion des Tickets
1. **Réception** : Réception des demandes de support
2. **Tri** : Classification et priorisation
3. **Assignation** : Attribution à un membre de l'équipe
4. **Suivi** : Suivi du statut et du temps de résolution

#### Étape 2: Communication
1. **Réponse** : Réponse aux utilisateurs
2. **Escalade** : Escalade si nécessaire
3. **Résolution** : Clôture du ticket après résolution
4. **Feedback** : Collecte du feedback utilisateur

---

## Points de Vigilance et Bonnes Pratiques

### Sécurité
- Toujours vérifier l'identité avant toute action sensible
- Utiliser l'authentification à deux facteurs
- Ne jamais partager ses identifiants
- Surveiller les accès non autorisés

### Intégrité des Données
- Vérifier les données avant modification
- Maintenir un historique des changements
- Effectuer des sauvegardes régulières
- Tester les restaurations de sauvegardes

### Performance
- Surveiller les performances du système
- Optimiser les requêtes lentes
- Nettoyer régulièrement les données obsolètes
- Maintenir les index de base de données

### Communication
- Informer les utilisateurs des changements importants
- Documenter les actions administratives
- Maintenir une communication claire avec l'équipe
- Fournir un support réactif

---

## Conclusion

Ce scénario couvre l'ensemble des fonctionnalités disponibles pour l'administrateur. Il est important de suivre ces étapes méthodiquement pour assurer une gestion efficace et sécurisée du système Bitchest. Chaque action doit être effectuée avec précaution et documentée pour maintenir l'intégrité du système.