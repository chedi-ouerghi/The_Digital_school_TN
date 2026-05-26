# 💻 JavaScript - Projets Pratiques

Bienvenue dans le répertoire des projets **JavaScript** ! Ici vous trouverez des applications frontend modernes et interactives développées avec HTML5, CSS3 et JavaScript vanilla.

## 📁 Structure du Projet

### 1. **Post** 📝
Une application simple pour créer et afficher des posts.

**Fichier**: `index.html`

**Fonctionnalités**:
- ✅ Interface simple et intuitive
- ✅ Créer de nouveaux posts
- ✅ Affichage dynamique du contenu

---

### 2. **Quiz Application** 🎯
Une application interactive de quiz avec système de scoring.

**Fichier**: `index.html`

**Fonctionnalités**:
- ✅ Questions à choix multiples
- ✅ Système de scoring automatique
- ✅ Feedback immédiat
- ✅ Résultats finaux

---

### 3. **TodoList** ✅
Une application complète de gestion de tâches avec stockage local.

**Fichiers**:
- `index.html` - Structure HTML
- `index.js` - Logique JavaScript
- `index.css` - Styles CSS

**Fonctionnalités**:
- ✅ Ajouter/Supprimer des tâches
- ✅ Marquer les tâches comme complétées
- ✅ Persistance des données (localStorage)
- ✅ Interface utilisateur fluide
- ✅ Filtrage des tâches

---

## 🎯 Objectifs d'Apprentissage

✅ Maîtriser JavaScript vanilla (pas de framework)  
✅ Manipuler le DOM efficacement  
✅ Gérer les événements utilisateur  
✅ Utiliser localStorage pour la persistance  
✅ Créer des interfaces interactives  

---

## 🚀 Comment Utiliser

1. Ouvrez simplement le fichier `index.html` dans votre navigateur
2. Aucune installation requise
3. Aucun serveur web nécessaire

**Exemple**:
```bash
# Sur Windows, double-clic sur le fichier HTML
# Ou via terminal
start index.html

# Sur Mac
open index.html

# Sur Linux
xdg-open index.html
```

---

## 📝 Prérequis

- Un navigateur web moderne (Chrome, Firefox, Safari, Edge)
- Pas de dépendances externes
- Connaissance basique en JavaScript, HTML et CSS

---

## 💡 Conseils

- Ouvrez la console du navigateur (F12) pour déboguer
- Expérimentez avec le code
- Modifiez les styles CSS pour personnaliser
- Ajoutez vos propres fonctionnalités

---

## 📚 Concepts Clés

### Manipulation du DOM
```javascript
document.getElementById()
document.querySelector()
element.addEventListener()
element.innerHTML / textContent
```

### Gestion des Événements
- Click, Submit, Change
- Validation des formulaires
- Feedback utilisateur

### localStorage
```javascript
localStorage.setItem(key, value)
localStorage.getItem(key)
localStorage.removeItem(key)
```

---

## 🔍 Structure Générale

```javascript
// 1. Sélectionner les éléments DOM
const input = document.getElementById('input');

// 2. Créer des fonctions
function addTask() { ... }

// 3. Ajouter des écouteurs d'événements
input.addEventListener('keyup', addTask);

// 4. Mettre à jour le DOM
document.querySelector('ul').innerHTML = ...
```

---

## 🎨 Personnalisation

Chaque projet est facilement personnalisable:
- Modifiez les couleurs dans le CSS
- Ajoutez des animations
- Créez de nouvelles fonctionnalités

---

## 📚 Ressources Utiles

- [MDN Web Docs - JavaScript](https://developer.mozilla.org/fr/docs/Web/JavaScript)
- [MDN Web Docs - DOM](https://developer.mozilla.org/fr/docs/Web/API/DOM)
- [W3Schools - JavaScript](https://www.w3schools.com/js/)

---

## 💼 Auteur

**The Digital School TN** 🎓  
Formations pratiques en développement web

---

**Dernière mise à jour**: 2026  
**Version**: 1.0.0
