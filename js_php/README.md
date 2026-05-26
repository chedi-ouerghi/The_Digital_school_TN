# 🌐 JavaScript + PHP - Projets Hybrides

Bienvenue dans le répertoire des projets **JavaScript + PHP** ! Ici vous trouverez des applications web complètes combinant JavaScript côté client et PHP côté serveur.

## 📁 Structure du Projet

### 1. **Job-Board-CH** 💼
Une plateforme complète de gestion d'emplois (Job Board) pour les entreprises et les candidats.

#### Architecture
```
Job-Board-CH/
├── Frontend
│   ├── registerforme.html (Page d'enregistrement)
│   ├── Auth/ (Authentification)
│   │   ├── login.html / login.php
│   │   ├── inscription (Entreprise & Utilisateur)
│   │   └── Profils (Gestion des comptes)
│   │
│   ├── dashboard/ (Tableaux de bord)
│   │   ├── dashboard.php
│   │   ├── index.css
│   │   └── script.js
│   │
│   └── Home/ (Accueil)
│       ├── index.html
│       ├── contact.html / Contact.php
│       └── styles.css
│
└── Backend (PHP)
    ├── Modèles (Utilisateur.php, Entreprise.php)
    ├── Contrôleurs (Gestion métier)
    ├── Filtrage (FiltreEmploi.php)
    └── Base de données (SQL)
```

#### Fonctionnalités Principales
- ✅ **Authentification** (Login/Register)
- ✅ **Deux types de comptes**:
  - Utilisateurs (Candidats)
  - Entreprises (Recruteurs)
- ✅ **Gestion des emplois**:
  - Création d'offres d'emploi
  - Modification et suppression
  - Filtrage avancé
- ✅ **Postulation** aux offres
- ✅ **Profils détaillés**:
  - Profil candidat
  - Profil entreprise
- ✅ **Dashboard administrateur**
- ✅ **Contact & Communication**

---

### 2. **Sondage App** 📊
Une application de création et gestion de sondages avec système de réponses et statistiques.

#### Architecture
```
sondage_app/
├── Frontend
│   ├── Sondage.html (Interface de création)
│   ├── statistiques.html (Affichage des résultats)
│   └── controle.js (Logique JavaScript)
│
└── Backend
    ├── Sondage.php (Logique métier)
    ├── statistiques.php (Calcul des statistiques)
    └── projetsondage.sql (Base de données)
```

#### Fonctionnalités Principales
- ✅ **Créer des sondages** avec plusieurs questions
- ✅ **Répondre aux sondages**
- ✅ **Collecter les réponses**
- ✅ **Afficher les statistiques**:
  - Graphiques et tableaux
  - Pourcentages
  - Nombre de réponses
- ✅ **Exporter les données** (SQL)
- ✅ **Interface interactive**

---

## 🛠️ Stack Technologique

### Frontend
- **HTML5** - Structure
- **CSS3** - Styles et mise en page
- **JavaScript** - Interactivité
- **Bootstrap/Tailwind** (optionnel) - Framework CSS

### Backend
- **PHP** - Logique serveur
- **MySQL/PostgreSQL** - Base de données
- **PDO/MySQLi** - Requêtes sécurisées
- **PHPMailer** - Envoi d'emails (dans Home)

### Fonctionnalités Avancées
- ✅ Session management (PHP sessions)
- ✅ Input validation
- ✅ Security (prepared statements)
- ✅ Contact form with email

---

## 📋 Prérequis

- **Serveur Web**: Apache, Nginx ou WAMP/XAMPP
- **PHP 7.4+** minimum
- **Base de données**: MySQL 5.7+ ou PostgreSQL 10+
- **Navigateur moderne**

---

## 🚀 Installation & Démarrage

### Option 1: Avec XAMPP (Windows/Mac/Linux)

```bash
# 1. Télécharger et installer XAMPP
# 2. Mettre les fichiers dans C:\xampp\htdocs\

# 3. Démarrer Apache et MySQL
# 4. Accéder à http://localhost/phpmyadmin

# 5. Créer les bases de données
# - Importer projetsondage.sql
# - Créer la base pour Job-Board

# 6. Accéder aux applications
# http://localhost/Job-Board-Ch/
# http://localhost/sondage_app/
```

### Option 2: Avec PHP Built-in Server

```bash
# Pour PHP 5.4+
cd sondage_app
php -S localhost:8000

# Puis accéder à http://localhost:8000
```

---

## 🔐 Sécurité

- ✅ Validations côté client et serveur
- ✅ Prepared statements (Protection contre SQL Injection)
- ✅ Hachage des mots de passe
- ✅ Gestion des sessions sécurisées
- ✅ Protection CSRF (à implémenter)

---

## 📊 Base de Données

### Job-Board-CH
Tables principales:
- `utilisateurs` - Comptes des candidats
- `entreprises` - Comptes des recruteurs
- `emplois` - Offres d'emploi
- `postulations` - Candidatures

### Sondage App
Tables principales:
- `sondages` - Sondages créés
- `questions` - Questions du sondage
- `reponses` - Réponses des utilisateurs

---

## 📁 Configuration Recommandée

```
htdocs/
├── Job-Board-Ch/
│   ├── config.php (Credentials DB)
│   ├── .htaccess (URL rewriting)
│   └── ...
│
├── sondage_app/
│   ├── config.php
│   ├── db.php
│   └── ...
│
└── Home/
    ├── config.php
    └── vendor/ (PHPMailer)
```

---

## 🧪 Test et Débogage

### Utiliser var_dump() et print_r()
```php
var_dump($_POST);
print_r($_SESSION);
```

### Vérifier les logs PHP
```bash
tail -f /var/log/apache2/error.log
```

### Console Navigateur (F12)
- Vérifiez les requêtes AJAX
- Inspectez les éléments HTML
- Consultez la console pour les erreurs JS

---

## 📚 Ressources Utiles

- [PHP Documentation](https://www.php.net/manual/en/)
- [MySQL Tutorial](https://www.w3schools.com/sql/)
- [JavaScript MDN](https://developer.mozilla.org/fr/docs/Web/JavaScript)

---

## 🔄 Flux d'Utilisation Typique

### Job-Board
```
1. Visiteur → Home
2. Register (Entreprise ou Utilisateur)
3. Login
4. Dashboard personnalisé
5. Entreprise: Publier des emplois
6. Utilisateur: Postuler aux emplois
7. Suivi des applications
```

### Sondage
```
1. Créer un sondage
2. Ajouter des questions
3. Partager le lien
4. Utilisateurs répondent
5. Voir les statistiques
```

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement web Full Stack

---

## 📞 Support

Pour toute question ou problème:
1. Vérifiez la configuration PHP/MySQL
2. Consultez les logs d'erreur
3. Contactez votre instructeur

---

**Dernière mise à jour**: 2026  
**Version**: 1.0.0
