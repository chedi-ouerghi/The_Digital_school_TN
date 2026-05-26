# 🎓 Laravel - Framework Moderne PHP

Bienvenue dans le répertoire des projets **Laravel** ! Ici vous trouverez des applications web professionnelles et scalables développées avec le framework Laravel.

## 📁 Structure du Projet

### 1. **Education Platform** 🎓
Une plateforme complète d'éducation en ligne pour gérer les cours, instructeurs et étudiants.

#### Architecture MVC
```
Education_Plateform/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── CourseController.php
│   │       ├── UserController.php
│   │       └── ...
│   │
│   └── Models/
│       ├── Utilisateur.php
│       ├── Instructor.php
│       ├── Etudiant.php
│       ├── Cours.php
│       ├── Category.php
│       ├── Commentaire.php
│       ├── DemandeRejoindreCours.php
│       ├── FAQ.php
│       └── Feedback.php
│
├── database/
│   ├── migrations/ (Schema)
│   ├── seeders/ (Données initiales)
│   └── factories/ (Test data)
│
├── resources/
│   ├── views/ (Blade templates)
│   ├── css/ (Styles)
│   └── js/ (Assets JavaScript)
│
├── routes/
│   ├── web.php (Routes principales)
│   ├── admin.php (Routes admin)
│   └── student.php (Routes étudiants)
│
├── public/ (Dossier public)
│   ├── css/ (Stylesheets compilés)
│   ├── img/ (Images)
│   └── index.php (Entry point)
│
└── storage/ (Fichiers générés)
    ├── app/ (Uploads)
    └── logs/ (Application logs)
```

#### Fonctionnalités Principales
- ✅ **Authentification** (Login/Register)
- ✅ **Gestion des utilisateurs**:
  - Instructeurs
  - Étudiants
  - Administrateurs
- ✅ **Gestion des cours**:
  - Créer/Éditer/Supprimer des cours
  - Assigner des instructeurs
  - Catégoriser les cours
- ✅ **Inscription aux cours**
- ✅ **Système de commentaires**
- ✅ **Évaluations et feedback**
- ✅ **FAQ**
- ✅ **Demandes de participation**
- ✅ **Dashboard utilisateur**
- ✅ **Système de rôles et permissions**

---

### 2. **Laravel TodoList** ✅
Une application simple et efficace de gestion de tâches avec Laravel.

#### Architecture
```
laravel-todoList/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── TodoController.php
│   │
│   └── Models/
│       └── Todo.php
│
├── database/
│   ├── migrations/ (Schéma DB)
│   └── seeders/ (Données test)
│
├── resources/
│   └── views/
│       ├── layouts/
│       ├── todos/
│       └── dashboard.blade.php
│
├── routes/
│   ├── web.php
│   └── api.php (API REST)
│
└── public/
    ├── css/ (Styles)
    └── js/ (Scripts)
```

#### Fonctionnalités Principales
- ✅ **CRUD complet** (Create, Read, Update, Delete)
- ✅ **Marquer les tâches comme complétées**
- ✅ **Supprimer les tâches**
- ✅ **Filtrer par statut**
- ✅ **Validation des entrées**
- ✅ **API REST**
- ✅ **Interface responsive**

---

## 🛠️ Stack Technologique

### Backend
- **Laravel 11** - Framework PHP moderne
- **Eloquent ORM** - Gestion des modèles
- **Blade** - Moteur de templates
- **Composer** - Gestionnaire de dépendances

### Database
- **MySQL / PostgreSQL** - Base de données relationnelle
- **Migrations** - Contrôle de version du schéma
- **Seeders** - Données initiales

### Frontend
- **Blade Templates** - Moteur de templates natif
- **Tailwind CSS** - Framework CSS utilitaire
- **JavaScript** - Interactivité
- **Vite** - Bundler moderne

### Testing
- **PHPUnit** - Tests unitaires
- **Pest** (optionnel) - Framework de test

---

## 📋 Prérequis

- **PHP 8.0+** minimum
- **Composer** (Gestionnaire PHP)
- **Node.js & npm** (pour Vite)
- **MySQL 5.7+** ou **PostgreSQL 10+**
- **Git** (optionnel)

---

## 🚀 Installation & Démarrage

### Installation Complète

```bash
# 1. Cloner le projet (ou extraire les fichiers)
cd Laravel/Education_Plateform

# 2. Installer les dépendances PHP
composer install

# 3. Copier le fichier .env
cp .env.example .env

# 4. Générer la clé d'application
php artisan key:generate

# 5. Configurer la base de données dans .env
# DB_DATABASE=education_platform
# DB_USERNAME=root
# DB_PASSWORD=

# 6. Exécuter les migrations
php artisan migrate

# 7. (Optionnel) Remplir la base de données
php artisan db:seed

# 8. Installer les dépendances Node
npm install

# 9. Compiler les assets (CSS/JS)
npm run build

# 10. Démarrer le serveur de développement
php artisan serve

# 11. (Nouveau terminal) Démarrer Vite
npm run dev
```

### Accès à l'Application
```
Frontend: http://localhost:8000
```

---

## 🔑 Configuration Importante

### Fichier `.env`
```env
APP_NAME=Education_Platform
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=education_platform
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=localhost
MAIL_PORT=1025
```

---

## 🗂️ Structure des Dossiers Importants

```
app/
├── Http/Controllers/          # Contrôleurs (logique métier)
├── Models/                    # Modèles Eloquent (base de données)
└── Providers/                 # Service providers

database/
├── migrations/                # Migrations (schéma)
├── seeders/                   # Données initiales
└── factories/                 # Données pour tests

resources/
├── views/                     # Templates Blade
├── css/                       # Feuilles de style
└── js/                        # Scripts JavaScript

routes/
├── web.php                    # Routes web
├── api.php                    # Routes API REST
└── admin.php / student.php    # Routes par rôle

storage/
├── app/                       # Fichiers uploadés
└── logs/                      # Fichiers de log
```

---

## 📚 Commandes Utiles

### Artisan (CLI Laravel)

```bash
# Migrations
php artisan migrate                 # Exécuter migrations
php artisan migrate:rollback        # Annuler dernière migration
php artisan migrate:fresh           # Reset et remigrer
php artisan make:migration          # Créer migration

# Modèles & Controllers
php artisan make:model Todo         # Créer modèle
php artisan make:controller TodoController
php artisan make:model Todo -c      # Modèle + Controller

# Seeders
php artisan make:seeder TodoSeeder
php artisan db:seed                 # Remplir la DB

# Cache & Config
php artisan cache:clear
php artisan config:cache
php artisan view:cache

# Tinker (REPL interactif)
php artisan tinker
```

---

## 🔐 Sécurité

- ✅ **CSRF Protection** (tokens automatiques)
- ✅ **SQL Injection Protection** (Eloquent ORM)
- ✅ **XSS Protection** (Blade escaping)
- ✅ **Hachage sécurisé** des mots de passe
- ✅ **Authentification intégrée**
- ✅ **Authorization gates & policies**

---

## 🧪 Tests

### Exécuter les Tests
```bash
# Tous les tests
php artisan test

# Tests spécifiques
php artisan test --filter=TodoTest

# Avec couverture de code
php artisan test --coverage
```

### Exemple de Test
```php
// tests/Feature/TodoTest.php
public function test_can_create_todo()
{
    $response = $this->post('/todos', [
        'title' => 'Test Todo'
    ]);
    
    $response->assertStatus(201);
    $this->assertDatabaseHas('todos', ['title' => 'Test Todo']);
}
```

---

## 📊 Base de Données

### Principales Migrations
- `users` - Utilisateurs et authentification
- `courses` - Cours et modules
- `instructors` - Instructeurs
- `students` - Étudiants
- `comments` - Commentaires
- `feedback` - Retours utilisateurs
- `faqs` - Foires aux questions

---

## 🎯 Bonnes Pratiques

1. **Utiliser les Migrations** pour toutes les modifications de schéma
2. **Seeders** pour les données initiales
3. **Validation** sur les requêtes
4. **Eloquent** plutôt que SQL pur
5. **Collections** pour manipuler les données
6. **Tinker** pour tester rapidement
7. **Tests automatisés** pour la qualité

---

## 📚 Ressources Utiles

- [Documentation Laravel](https://laravel.com/docs)
- [Eloquent ORM](https://laravel.com/docs/eloquent)
- [Blade Templates](https://laravel.com/docs/blade)
- [Database](https://laravel.com/docs/database)
- [Testing](https://laravel.com/docs/testing)

---

## 🔄 Workflow Typique

### Education Platform

```
1. Admin crée des catégories de cours
2. Instructeur crée un cours
3. Étudiants voient le cours
4. Étudiants s'inscrivent
5. Accès au contenu du cours
6. Commentaires et interactions
7. Feedback final
```

### TodoList

```
1. Utilisateur crée une tâche
2. Liste affichée
3. Marquer complété
4. Supprimer
5. Filtrer par statut
```

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations professionnelles en développement Laravel

---

## 📞 Support & FAQ

**Q: Comment réinitialiser la base de données?**
```bash
php artisan migrate:fresh --seed
```

**Q: Où sont les fichiers uploadés?**
```
storage/app/public/
```

**Q: Comment déboguer?**
- Activez `APP_DEBUG=true` dans `.env`
- Consultez les logs: `storage/logs/`

---

**Dernière mise à jour**: 2026  
**Version**: 11.x  
**License**: MIT
