# Scénario d'Utilisation - Client

## Vue d'Ensemble
Ce document décrit le scénario complet d'utilisation du système Bitchest du point de vue du client (utilisateur final), étape par étape, depuis l'inscription jusqu'à la gestion avancée du portefeuille.

## Prérequis
- Accès à internet
- Adresse email valide
- Âge minimum requis (18 ans)

---

## Scénario 1: Inscription et Première Connexion

### Étape 1: Accès à la Plateforme
1. **Navigation** : Le client accède à l'URL de Bitchest
2. **Page d'accueil** : Découverte de la plateforme et de ses fonctionnalités
3. **Call-to-action** : Clic sur "S'inscrire" ou "Commencer"

### Étape 2: Formulaire d'Inscription
1. **Informations personnelles** :
   - Prénom et nom
   - Adresse email valide
   - Date de naissance
   - Numéro de téléphone (optionnel)

2. **Création du mot de passe** :
   - Mot de passe sécurisé (8+ caractères, majuscules, chiffres, symboles)
   - Confirmation du mot de passe
   - Acceptation des conditions d'utilisation

3. **Validation** :
   - Vérification de l'unicité de l'email
   - Validation du format des données
   - Captcha anti-spam

4. **Soumission** : Envoi du formulaire

### Étape 3: Activation du Compte
1. **Email de confirmation** : Réception d'un email avec lien d'activation
2. **Activation** : Clic sur le lien d'activation
3. **Confirmation** : Message de succès et redirection vers la connexion

### Étape 4: Première Connexion
1. **Authentification** : Saisie de l'email et du mot de passe
2. **Token généré** : Création d'un token d'authentification sécurisé
3. **Redirection** : Accès au tableau de bord personnel
4. **Bienvenue** : Message de bienvenue et tour guidé (optionnel)

---

## Scénario 2: Découverte du Tableau de Bord

### Étape 1: Vue d'Ensemble
1. **Solde du portefeuille** : Affichage du solde initial (500€)
2. **Cryptomonnaies disponibles** : Liste des cryptos échangeables
3. **Prix en temps réel** : Cours actuels des principales cryptomonnaies
4. **Graphiques** : Évolution des prix (24h, 7j, 30j)

### Étape 2: Personnalisation
1. **Préférences** : Configuration des préférences d'affichage
2. **Devise** : Choix de la devise de référence (EUR, USD)
3. **Notifications** : Configuration des alertes de prix
4. **Langue** : Sélection de la langue

---

## Scénario 3: Premier Achat de Cryptomonnaie

### Étape 1: Recherche et Sélection
1. **Exploration** : Parcours de la liste des cryptomonnaies
2. **Informations** : Consultation des détails (prix, variation, volume)
3. **Historique** : Visualisation de l'évolution du prix
4. **Sélection** : Choix de la cryptomonnaie à acheter

### Étape 2: Configuration de l'Achat
1. **Montant** : Saisie du montant en EUR ou en crypto
2. **Calcul automatique** : Conversion et frais affichés
3. **Prévisualisation** : Résumé de la transaction
4. **Confirmation** : Validation de l'achat

### Étape 3: Traitement de la Transaction
1. **Vérification** : Contrôle du solde disponible
2. **Déduction** : Débit du montant du portefeuille EUR
3. **Crédit** : Ajout des cryptomonnaies au portefeuille
4. **Confirmation** : Message de succès et reçu de transaction
5. **Historique** : Enregistrement dans l'historique des transactions

### Étape 4: Suivi de l'Investissement
1. **Portfolio** : Mise à jour du portfolio avec la nouvelle acquisition
2. **Plus-value** : Calcul automatique de la plus-value
3. **Graphiques** : Évolution de l'investissement
4. **Alertes** : Configuration d'alertes de prix

---

## Scénario 4: Gestion du Portefeuille

### Étape 1: Consultation du Portfolio
1. **Vue globale** : Ensemble des cryptomonnaies détenues
2. **Détails par crypto** : Quantité, valeur actuelle, plus-value
3. **Répartition** : Graphique en camembert de la répartition
4. **Performance** : Performance globale du portfolio

### Étape 2: Analyse des Performances
1. **Plus-values/Moins-values** : Calcul des gains/pertes
2. **Historique** : Évolution temporelle du portfolio
3. **Comparaisons** : Comparaison avec le marché
4. **Export** : Export des données (PDF, CSV)

---

## Scénario 5: Vente de Cryptomonnaie

### Étape 1: Sélection de la Crypto à Vendre
1. **Portfolio** : Accès à la liste des cryptos détenues
2. **Quantité disponible** : Vérification de la quantité détenue
3. **Valeur actuelle** : Affichage de la valeur actuelle
4. **Sélection** : Choix de la crypto et de la quantité à vendre

### Étape 2: Configuration de la Vente
1. **Montant** : Saisie du montant ou de la quantité
2. **Prévisualisation** : Montant en EUR après vente
3. **Frais** : Affichage des frais de transaction
4. **Confirmation** : Validation finale

### Étape 3: Traitement de la Vente
1. **Vérification** : Contrôle de la quantité disponible
2. **Vente** : Crédit du montant en EUR
3. **Déduction** : Débit des cryptomonnaies vendues
4. **Confirmation** : Message de succès et reçu

---

## Scénario 6: Gestion de l'Historique et des Relevés

### Étape 1: Consultation de l'Historique
1. **Filtres** : Filtrage par date, type, crypto
2. **Recherche** : Recherche de transactions spécifiques
3. **Détails** : Vue détaillée de chaque transaction
4. **Statut** : État de chaque transaction (complétée, en cours, annulée)

### Étape 2: Génération de Relevés
1. **Période** : Sélection de la période
2. **Format** : Choix du format (PDF, CSV, Excel)
3. **Génération** : Création du relevé
4. **Téléchargement** : Téléchargement du fichier

---

## Scénario 7: Configuration des Alertes et Notifications

### Étape 1: Configuration des Alertes de Prix
1. **Sélection de la crypto** : Choix de la cryptomonnaie
2. **Seuil** : Définition du prix seuil
3. **Type d'alerte** : Hausse ou baisse du prix
4. **Notification** : Email, SMS, notification push

### Étape 2: Gestion des Notifications
1. **Centre de notifications** : Vue de toutes les notifications
2. **Marquage** : Marquer comme lu/non lu
3. **Suppression** : Suppression des anciennes notifications
4. **Préférences** : Configuration des préférences de réception

---

## Scénario 8: Support et Aide

### Étape 1: Accès au Support
1. **Centre d'aide** : Accès à la FAQ et guides
2. **Contact** : Formulaire de contact
3. **Chat** : Chat en direct (si disponible)
4. **Ticket** : Création d'un ticket de support

### Étape 2: Gestion des Demandes
1. **Suivi** : Suivi du statut des demandes
2. **Historique** : Historique des communications
3. **Évaluation** : Évaluation du support reçu

---

## Scénario 9: Sécurité et Confidentialité

### Étape 1: Configuration de la Sécurité
1. **Mot de passe** : Modification régulière du mot de passe
2. **2FA** : Activation de l'authentification à deux facteurs
3. **Sessions** : Gestion des sessions actives
4. **Historique** : Consultation de l'historique de connexion

### Étape 2: Protection des Données
1. **Confidentialité** : Configuration des préférences de confidentialité
2. **Export des données** : Demande d'export des données personnelles
3. **Suppression** : Demande de suppression du compte

---

## Scénario 10: Fonctionnalités Avancées

### Étape 1: Trading Avancé
1. **Ordres limites** : Configuration d'ordres d'achat/vente à prix limite
2. **Stop-loss** : Configuration d'ordres stop-loss
3. **Planification** : Achat/vente programmés

### Étape 2: Analyses Approfondies
1. **Graphiques techniques** : Indicateurs techniques (RSI, MACD)
2. **Analyse fondamentale** : Informations sur les projets
3. **News** : Fil d'actualités crypto
4. **Prédictions** : Analyses de marché

---

## Points de Vigilance et Bonnes Pratiques

### Sécurité Personnelle
- **Ne jamais partager** ses identifiants
- **Utiliser un mot de passe fort** et unique
- **Activer la 2FA** dès la première connexion
- **Vérifier l'URL** avant de se connecter
- **Se déconnecter** après chaque session

### Gestion des Risques
- **Investir avec prudence** : Ne jamais investir plus que ce que l'on peut se permettre de perdre
- **Diversifier** : Ne pas mettre tous ses œufs dans le même panier
- **Surveiller le marché** : Rester informé des évolutions
- **Fixer des objectifs** : Avoir une stratégie claire

### Utilisation Responsable
- **Comprendre avant d'investir** : Se renseigner sur les cryptomonnaies
- **Suivre l'actualité** : Rester informé du marché crypto
- **Utiliser les outils** : Profiter de toutes les fonctionnalités disponibles
- **Demander de l'aide** : Ne pas hésiter à contacter le support

### Gestion du Compte
- **Vérifier régulièrement** l'activité du compte
- **Mettre à jour** les informations personnelles
- **Consulter** l'historique des transactions
- **Archiver** les relevés importants

---

## Dépannage et Support

### Problèmes Courants
1. **Connexion impossible** : Vérifier identifiants et réinitialiser si nécessaire
2. **Transaction échouée** : Vérifier solde et réessayer
3. **Prix non à jour** : Actualiser la page ou vérifier la connexion
4. **Notification non reçue** : Vérifier les préférences et le spam

### Contact Support
1. **Formulaire de contact** : Utiliser le formulaire dédié
2. **Email** : Envoyer un email détaillé
3. **Chat** : Utiliser le chat si disponible
4. **Téléphone** : Appeler le support si urgent

---

## Conclusion

Ce scénario détaille l'ensemble du parcours client sur la plateforme Bitchest, depuis l'inscription jusqu'à l'utilisation avancée. Il est conçu pour être intuitif et sécurisé, tout en offrant des fonctionnalités complètes pour la gestion de cryptomonnaies.

L'expérience utilisateur est au cœur du design, avec une attention particulière portée à la clarté, la sécurité et la facilité d'utilisation. Chaque étape est pensée pour guider l'utilisateur dans ses décisions d'investissement tout en lui fournissant les outils nécessaires pour gérer efficacement son portefeuille.