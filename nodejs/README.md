# 🚀 Node.js - Backend Moderne

Bienvenue dans le répertoire des projets **Node.js** ! Ici vous trouverez des applications backend modernes et scalables développées avec Node.js et Express.

## 📁 Structure du Projet

### 1. **Blog Post** 📝
Une API REST complète pour gérer un blog avec posts, commentaires et utilisateurs.

#### Architecture MVC
```
blogPost/
├── Controllers/
│   └── postController.js (Logique métier des posts)
│
├── Models/
│   └── postModel.js (Modèle de données)
│
├── Routes/
│   └── postRoutes.js (Endpoints API)
│
├── index.js (Entry point - Serveur Express)
└── package.json (Dépendances)
```

#### Fonctionnalités Principales
- ✅ **CRUD complet** (Create, Read, Update, Delete posts)
- ✅ **API REST** bien structurée
- ✅ **Validation des données**
- ✅ **Gestion des erreurs**
- ✅ **Endpoints sécurisés**
- ✅ **Formatage JSON**

#### Endpoints Disponibles
```
GET    /posts           - Récupérer tous les posts
GET    /posts/:id       - Récupérer un post spécifique
POST   /posts           - Créer un nouveau post
PUT    /posts/:id       - Modifier un post
DELETE /posts/:id       - Supprimer un post
```

---

### 2. **Express MongoDB TodoList** ✅
Une application complète de gestion de tâches avec frontend intégré et MongoDB.

#### Architecture
```
express_mongodb/TodoList/
├── controllers/
│   └── task/
│       └── task.js (Logique des tâches)
│
├── models/
│   └── taskModel.js (Schéma MongoDB)
│
├── routers/
│   └── taskRoutes.js (Routes API)
│
├── HTML/
│   ├── index.html (Interface frontend)
│   ├── script.js (Logique client)
│   └── styles.css (Styles)
│
├── server.js (Serveur Express + Configuration)
├── package.json (Dépendances)
└── .gitignore (Fichiers ignorés)
```

#### Fonctionnalités Principales
- ✅ **Gestion complète des tâches**:
  - Créer, lire, modifier, supprimer
  - Marquer comme complétées
  - Filtrer les tâches
- ✅ **Frontend intégré** (HTML + CSS + JS)
- ✅ **Base de données MongoDB**
- ✅ **API REST sécurisée**
- ✅ **Interface utilisateur responsive**
- ✅ **Persistence des données**

#### Endpoints API
```
GET    /tasks           - Récupérer toutes les tâches
GET    /tasks/:id       - Récupérer une tâche
POST   /tasks           - Créer une tâche
PUT    /tasks/:id       - Modifier une tâche
DELETE /tasks/:id       - Supprimer une tâche
PATCH  /tasks/:id/complete - Marquer comme complétée
```

---

## 🛠️ Stack Technologique

### Backend
- **Node.js 14+** - Runtime JavaScript côté serveur
- **Express.js** - Framework web léger et puissant
- **MongoDB** - Base de données NoSQL
- **Mongoose** (optionnel) - ODM pour MongoDB

### Frontend (TodoList)
- **HTML5** - Structure
- **CSS3** - Styles
- **JavaScript Vanilla** - Interactivité
- **Fetch API** - Communications avec le serveur

### Outils
- **npm / yarn** - Gestionnaire de dépendances
- **Nodemon** - Rechargement automatique
- **Postman** - Test d'API

---

## 📋 Prérequis

- **Node.js 14.0+** et npm
- **MongoDB 4.0+** (localement ou Atlas)
- **Postman** (optionnel, pour tester)
- **Navigateur moderne**

---

## 🚀 Installation & Démarrage

### Blog Post

```bash
# 1. Installer les dépendances
npm install

# 2. Configurer les variables d'environnement
# Créer un fichier .env (voir .env.example)

# 3. Démarrer le serveur
npm start
# ou avec nodemon (rechargement automatique)
npm run dev

# 4. Serveur lancé sur http://localhost:3000
```

### Express MongoDB TodoList

```bash
# 1. Naviguer dans le projet
cd express_mongodb/TodoList

# 2. Installer les dépendances
npm install

# 3. Configurer MongoDB
# Éditer server.js avec votre connection string

# 4. Démarrer le serveur
npm start
# ou
npm run dev

# 5. Ouvrir dans le navigateur
# http://localhost:5000 (ou le port configuré)
```

---

## 📊 Configuration MongoDB

### Connexion Locale
```javascript
// Dans server.js
mongoose.connect('mongodb://localhost:27017/todolist');
```

### Connexion Atlas (Cloud)
```javascript
// Dans server.js
mongoose.connect('mongodb+srv://username:password@cluster.mongodb.net/dbname');
```

---

## 🔐 Sécurité

- ✅ Validation des entrées utilisateur
- ✅ Gestion des erreurs robuste
- ✅ CORS configuré
- ✅ Connexions sécurisées (HTTPS recommandé)
- ✅ Sanitization des données

---

## 📝 Structure des Modèles

### Post Model
```javascript
{
  id: ObjectId,
  title: String,
  content: String,
  author: String,
  createdAt: Date,
  updatedAt: Date
}
```

### Task Model
```javascript
{
  id: ObjectId,
  title: String,
  description: String,
  completed: Boolean,
  priority: String,
  dueDate: Date,
  createdAt: Date
}
```

---

## 📚 Commandes Utiles

```bash
# Installer les dépendances
npm install

# Démarrer en développement
npm run dev

# Démarrer en production
npm start

# Tests (si configurés)
npm test

# Vérifier les dépendances
npm list

# Mettre à jour les dépendances
npm update

# Nettoyer les caches
npm cache clean --force
```

---

## 🧪 Test des APIs

### Avec cURL
```bash
# GET
curl http://localhost:3000/posts

# POST
curl -X POST http://localhost:3000/posts \
  -H "Content-Type: application/json" \
  -d '{"title":"Mon post","content":"Contenu"}'

# PUT
curl -X PUT http://localhost:3000/posts/123 \
  -H "Content-Type: application/json" \
  -d '{"title":"Titre modifié"}'

# DELETE
curl -X DELETE http://localhost:3000/posts/123
```

### Avec Postman
1. Importer les endpoints dans Postman
2. Configurer les variables d'environnement
3. Tester chaque endpoint

---

## 🔄 Flux d'Utilisation Typique

### Blog Post
```
1. Client envoie requête POST /posts
2. Controller valide les données
3. Model sauvegarde en DB
4. Réponse JSON retournée
5. Client reçoit l'ID du post créé
```

### TodoList
```
1. Frontend charge la page
2. JavaScript récupère toutes les tâches (GET)
3. Affichage dans le DOM
4. Utilisateur crée une tâche
5. Envoi via Fetch API
6. Backend valide et sauvegarde
7. Réponse affichée
8. DOM mis à jour
```

---

## 📂 Variables d'Environnement

Créer un fichier `.env`:
```env
# Blog Post
PORT=3000
MONGODB_URI=mongodb://localhost:27017/blogdb
NODE_ENV=development

# TodoList
MONGODB_URL=mongodb://localhost:27017/todolist
API_PORT=5000
```

---

## 🐛 Débogage

### Activer les logs
```javascript
// Dans server.js
if (process.env.NODE_ENV === 'development') {
  app.use(logger('dev'));
}
```

### Utiliser console.log
```javascript
console.log('Debug:', variableADebugger);
console.error('Erreur:', err);
```

### Avec Nodemon
```bash
nodemon --inspect server.js
# Puis ouvrir chrome://inspect
```

---

## 📚 Ressources Utiles

- [Node.js Documentation](https://nodejs.org/docs/)
- [Express.js Guide](https://expressjs.com/)
- [MongoDB Manual](https://docs.mongodb.com/manual/)
- [Mongoose Documentation](https://mongoosejs.com/)
- [RESTful API Best Practices](https://restfulapi.net/)

---

## 🚨 Erreurs Courantes

### Port déjà utilisé
```bash
# Libérer le port
netstat -ano | findstr :3000
taskkill /PID <PID> /F

# Ou utiliser un autre port
PORT=3001 npm start
```

### MongoDB Connection Error
```javascript
// Vérifier la connection string
// Vérifier que MongoDB est lancé
// Vérifier les credentials
```

### CORS Error
```javascript
// Configurer CORS dans Express
const cors = require('cors');
app.use(cors());
```

---

## 📈 Prochaines Étapes

- [ ] Ajouter l'authentification JWT
- [ ] Implémenter les permissions/rôles
- [ ] Ajouter des tests unitaires
- [ ] Configurer le logging avancé
- [ ] Déployer sur Heroku/AWS
- [ ] Ajouter la pagination
- [ ] Implémenter le caching

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement Node.js

---

**Dernière mise à jour**: 2026  
**Version**: 1.0.0
