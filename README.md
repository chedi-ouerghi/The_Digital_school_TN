# BitChest (monorepo) — frontend + backend

This repository contains both the Laravel backend API and the Vue frontend for BitChest.

Folders:
- backend/  — Laravel API (see backend/README.md)
- frontend/ — Vue 3 / Vite app (see frontend/README.md)

## Run locally (minimal)
1. Backend
   - cd backend
   - composer install
   - cp .env.example .env  (configure DB, MAIL, APP_URL)
   - php artisan key:generate
   - php artisan migrate
   - php artisan storage:link
   - php artisan serve --host=127.0.0.1 --port=8000

2. Frontend
   - cd frontend
   - npm install
   - create .env with VITE_API_URL=http://localhost:8000
   - npm run dev

## Important
- Run `php artisan storage:link` (or `php artisan storage:check-symlink`) so uploaded images are served from `/storage/...`.
- Profile image and banner upload endpoints are documented in backend/README.md and used by frontend components.

## Troubleshooting
- If images don't load after upload: verify `public/storage` exists and points to `storage/app/public`, and the backend returned `data.url` for the uploaded asset.

## 📋 Description

Bitchest est une plateforme de trading qui offre une expérience utilisateur fluide pour l'achat et la vente d'actifs numériques. Le projet est composé de deux parties principales : un backend robuste et un frontend réactif.

## 🏗️ Structure du Projet

```
Bitchest_project/
├── backend/          # API et logique serveur
├── frontend/         # Interface utilisateur
└── README.md     
```

## 📚 Documentation

- **[Backend README](./backend/README.md)** - Instructions d'installation et configuration du serveur
- **[Frontend README](./frontend/README.md)** - Instructions d'installation et configuration de l'interface utilisateur

## 🚀 Démarrage Rapide

### Prérequis
- Node.js (version 18 ou supérieure)
- npm 

### Installation

1. Clonez le repository :
```bash
git clone https://github.com/chedi-ouerghi/trading_plateforme.git
cd Bitchest_project
```

2. Installez et démarrez le backend :
```bash
cd backend
npm install
npm start
```

3. Dans un autre terminal, installez et démarrez le frontend :
```bash
cd frontend
npm install
npm start
```

## 🔗 Liens Utiles

- **Repository GitHub** : https://github.com/chedi-ouerghi/trading_plateforme
- **Backend** : Consultez [backend/README.md](./backend/README.md)
- **Frontend** : Consultez [frontend/README.md](./frontend/README.md)

## 📝 Licence

Ce projet est sous licence MIT. Consultez le fichier LICENSE pour plus de détails.

## 👨‍💼 Auteur

**Chedi Ouerghi**  
GitHub : https://github.com/chedi-ouerghi

---

Pour plus d'informations, consultez les README dédiés dans les dossiers `backend` et `frontend`.
