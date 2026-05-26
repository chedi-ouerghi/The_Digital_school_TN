# 🚀 FullStack - Projets d'Application Complets

Bienvenue dans le répertoire des projets **Full Stack** ! Ce dossier contient des applications complètes avec frontend, backend et bases de données intégrées.

## 📁 Structure du Projet

### 1. **BibloFlow-Hub** 📚
Une plateforme de gestion de bibliothèque complète avec système de gestion des emprunts.

#### Architecture
```
BibloFlow-Hub/
├── Backend (Node.js/Express)
│   ├── Controllers (Logique métier)
│   ├── Models (Base de données)
│   ├── Routes (Endpoints API)
│   ├── Middleware (Authentification JWT, Validation)
│   ├── Tests (Jest - Tests unitaires)
│   └── Utils (Utilitaires)
│
└── Frontend (React)
    ├── Components (Composants réutilisables)
    ├── Pages (Pages principales)
    ├── API (Appels services)
    ├── Tests (Jest)
    └── Styles (CSS/Tailwind)
```

#### Fonctionnalités Clés
- ✅ Gestion complète des livres et auteurs
- ✅ Système d'authentification sécurisé (JWT)
- ✅ Gestion des emprunts et retours
- ✅ Système de notations et commentaires
- ✅ Dashboard administrateur
- ✅ Chatbot intégré

---

### 2. **Bigscreen** 📊
Une application de gestion de sondages avec tableau de bord analytics complet.

#### Architecture
```
Bigscreen/
├── Backend (Laravel/PHP)
│   ├── Controllers (Gestion des requêtes)
│   ├── Models (Modèles Eloquent)
│   ├── Services (Logique métier)
│   ├── Database (Migrations, Seeders)
│   ├── Tests (PHPUnit)
│   └── Config (Configuration JWT, CORS)
│
├── Back Office (React + TypeScript + Vite)
│   ├── Components (Interface administrateur)
│   ├── Pages (Dashboards et gestion)
│   ├── Context (Gestion d'état)
│   ├── Services (API)
│   └── Styles (Tailwind CSS)
│
└── Front Office (React + TypeScript + Vite + Bun)
    ├── Components (Interface utilisateur)
    ├── Pages (Sondages, réponses)
    ├── Services (API)
    └── Styles (Tailwind CSS)
```

#### Fonctionnalités Clés
- ✅ Création et gestion de sondages
- ✅ Collecte des réponses en temps réel
- ✅ Analytics et statistiques complètes
- ✅ Authentification sécurisée
- ✅ Interface administrateur intuitive
- ✅ Responsive design

---

## 🛠️ Stack Technologique

### Backend
- **BibloFlow-Hub**: Node.js + Express + MongoDB/SQL
- **Bigscreen**: Laravel + PHP + PostgreSQL/MySQL

### Frontend
- **React** (composants modernes)
- **TypeScript** (typage statique)
- **Tailwind CSS** (styles utilitaires)
- **Jest** (tests automatisés)
- **Vite** (bundler ultra-rapide)

### Outils
- Swagger/Postman (API documentation)
- JWT (Authentification)
- CORS (Contrôle d'accès)
- ESLint (Code quality)

---

## 📋 Prérequis

- Node.js v16+ (pour React, Express)
- PHP 8.0+ (pour Laravel)
- Base de données (MongoDB, PostgreSQL ou MySQL)
- npm ou yarn ou bun

---

## 🚀 Démarrage Rapide

### BibloFlow-Hub

**Backend**
```bash
cd BibloFlow-Hub/backend
npm install
npm start
```

**Frontend**
```bash
cd BibloFlow-Hub/frontend
npm install
npm start
```

### Bigscreen

**Backend**
```bash
cd Bigscreen/backend
composer install
php artisan serve
```

**Back Office**
```bash
cd Bigscreen/back_office
npm install
npm run dev
```

**Front Office**
```bash
cd Bigscreen/front_office
npm install
npm run dev
```

---

## 📚 Documentation

Chaque projet contient sa propre documentation dans son dossier respectif:
- `README.md` pour les détails spécifiques
- `postman_collection.json` pour tester les APIs

---

## 🧪 Tests

### BibloFlow-Hub
```bash
npm test
```

### Bigscreen
```bash
php artisan test
```

---

## 🔐 Sécurité

- ✅ Authentification JWT sur tous les endpoints sensibles
- ✅ Validation des entrées utilisateur
- ✅ Protection CORS configurée
- ✅ Hachage des mots de passe
- ✅ Tests de sécurité inclus

---

## 📈 Prochaines Étapes

- [ ] Ajouter des tests d'intégration
- [ ] Implémenter le caching
- [ ] Optimiser les performances
- [ ] Déployer sur production
- [ ] Mettre en place le monitoring

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement Full Stack

---

## 📞 Support

Pour toute question ou problème, consultez la documentation spécifique de chaque projet ou contactez votre instructeur.

---

**Dernière mise à jour**: 2026  
**Version**: 1.0.0
