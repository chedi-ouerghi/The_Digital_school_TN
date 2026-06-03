# 🔐 SÉCURITÉ - Guide Complet

## 📋 Table des matières
1. [Politique de Sécurité](#politique)
2. [Architecture de sécurité](#architecture)
3. [Authentification SPA](#authentification)
4. [Protection CSRF](#csrf)
5. [Protection XSS](#xss)
6. [Configuration production](#production)
7. [Audit et monitoring](#audit)
8. [Checkliste de déploiement](#checklist)

---

## 🚨 Politique de Sécurité <a name="politique"></a>

Si vous trouvez une faille, merci d'envoyer un email à : **chediouerghi40@gmail.com**  
Ne créez pas d'issue publique.

---

## 🏗️ Architecture de sécurité <a name="architecture"></a>

### Modèle d'authentification : Sanctum SPA avec cookies HttpOnly + Secure

```
┌─────────────────────────────────────────────────────────────────┐
│ Frontend (Vue SPA)                                              │
│ - Pas de localStorage pour tokens                               │
│ - sessionStorage pour données non-sensibles                     │
│ - fetch({ credentials: 'include' })                             │
└──────────────────────────┬──────────────────────────────────────┘
                           │
                    HTTP/HTTPS + Cookies
                           │
┌──────────────────────────▼──────────────────────────────────────┐
│ Backend (Laravel Sanctum)                                       │
│ - Mode SPA (stateful sessions)                                  │
│ - Cookies HttpOnly + Secure + SameSite=Lax                      │
│ - CSRF protection automatique (X-XSRF-TOKEN)                    │
│ - Session chiffrée en base de données                           │
└─────────────────────────────────────────────────────────────────┘
```

### Protections implémentées

| Menace | Prévention | Implémentation |
|--------|-----------|-----------------|
| **XSS** | Cookies HttpOnly | `http_only: true` dans session.php |
| **CSRF** | Token XSRF + SameSite | `same_site: 'lax'` dans session.php |
| **Session hijacking** | Secure flag | `secure: true` en production |
| **Man-in-the-Middle** | HTTPS + Secure | `APP_URL=https://...` en prod |
| **Token theft** | Pas de localStorage | `credentials: 'include'` au lieu de Bearer |
| **Privilege escalation** | Validation côté serveur | RoleMiddleware + auth:sanctum |
| **CORS attacks** | Origines restreintes | `allowed_origins` spécifiques |
| **Session fixation** | Regenerate après login | `Auth::login()` automatique |

---

## 🔐 Authentification SPA <a name="authentification"></a>

### Frontend - Flux de login

```typescript
// ✅ BON : Utiliser credentials: 'include'
const res = await fetch('http://localhost:8000/api/v1/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  credentials: 'include',  // ← IMPORTANT : envoyer les cookies
  body: JSON.stringify({ email, password })
});

// Le serveur retourne UNIQUEMENT les données utilisateur
// Le token est géré par les cookies automatiquement
const user = await res.json(); // { user: { id, email, name, role } }
```

### Backend - Login API

```php
// ✅ BON : Sanctum SPA Mode
public function login(LoginRequest $request): JsonResponse {
    // ... validation ...
    
    Auth::login($user);  // ← Crée automatiquement la session + cookie
    
    // NE PAS retourner le token
    return response()->json([
        'user' => $user,
        'message' => 'Login successful'
    ]);
}
```

### Données utilisateur - Stockage frontend

```typescript
// ✅ BON : sessionStorage pour les données non-sensibles
sessionStorage.setItem('user', JSON.stringify({
  id: user.id,
  email: user.email,
  name: user.name,
  role: user.role,
  // JAMAIS : passwords, tokens, API keys
}));

// ❌ MAUVAIS : localStorage (persiste après fermeture du navigateur)
// ❌ MAUVAIS : stocker des données sensibles

// 💡 RAPPEL : les cookies HttpOnly ne sont jamais accessibles en JS
// Ils sont gérés automatiquement par le navigateur
```

### Routes protégées

```php
// Routes authentifiées (tous les rôles)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'update']);
});

// Routes admin uniquement
Route::middleware(['auth:sanctum', 'role:ADMIN'])->group(function () {
    Route::get('/admin/clients', [AdminClientController::class, 'index']);
    Route::post('/admin/clients', [AdminClientController::class, 'store']);
});
```

---

## 🛡️ Protection CSRF <a name="csrf"></a>

### Frontend - Automatique via Sanctum

```typescript
// Les cookies XSRF-TOKEN et Laravel session sont gérés automatiquement
// Sanctum extrait le token du cookie et le valide dans le header X-XSRF-TOKEN

// ✅ Rien à faire - c'est automatique avec credentials: 'include'

// Le navigateur envoie automatiquement :
// Cookie: XSRF-TOKEN=abcd1234
// Header: X-XSRF-TOKEN=abcd1234
```

### Backend - Configuration

```php
// config/sanctum.php
'middleware' => [
    'validate_csrf_token' => Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
    // ...
],

// Le middleware ValidateCsrfToken gère la vérification automatiquement
```

### Routes sans protection CSRF (si besoin)

```php
// Certaines routes n'ont pas besoin de CSRF (webhooks externes)
Route::withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)->group(function () {
    Route::post('/webhooks/stripe', [StripeWebhookController::class, 'handle']);
});
```

---

## 🚫 Protection XSS <a name="xss"></a>

### Frontend - Bonnes pratiques

```typescript
// ❌ MAUVAIS : innerHTML (vulnérable à XSS)
element.innerHTML = userInput;

// ✅ BON : Vue gère l'escaping automatiquement
<div>{{ userInput }}</div>  // ← Échappé par défaut

// ✅ BON : Si besoin de HTML brut (rare)
<div v-html="sanitized(userInput)"></div>  // ← Avec sanitizer
```

### Backend - Headers de sécurité

```php
// Dans App\Http\Middleware\SecurityHeaders.php (optionnel mais recommandé)
public function handle(Request $request, Closure $next) {
    return $next($request)
        ->header('X-Content-Type-Options', 'nosniff')
        ->header('X-Frame-Options', 'DENY')
        ->header('X-XSS-Protection', '1; mode=block')
        ->header('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->header('Content-Security-Policy', "default-src 'self'");
}
```

---

## 🚀 Configuration Production <a name="production"></a>

### .env pour production

```dotenv
# ============================================================================
# PRODUCTION
# ============================================================================
APP_ENV=production
APP_DEBUG=false
APP_URL=https://api.votredomaine.com
APP_FRONTEND_URL=https://votredomaine.com

# ============================================================================
# SÉCURITÉ : Sessions (TOUJOURS chiffrées)
# ============================================================================
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_ENCRYPT=true
SESSION_SECURE_COOKIE=true        # ← HTTPS ONLY
SESSION_HTTP_ONLY=true             # ← Pas d'accès JavaScript
SESSION_SAME_SITE=lax              # ← Protection CSRF

# ============================================================================
# SÉCURITÉ : CORS (JAMAIS * en production)
# ============================================================================
CORS_ALLOWED_ORIGINS=https://votredomaine.com
CORS_MAX_AGE=3600

# ============================================================================
# SÉCURITÉ : Sanctum
# ============================================================================
SANCTUM_STATEFUL_DOMAINS=votredomaine.com,api.votredomaine.com
SANCTUM_TOKEN_EXPIRATION=null

# ============================================================================
# BASE DE DONNÉES (sur serveur sécurisé)
# ============================================================================
DB_HOST=db.example.com
DB_USERNAME=bitchest_prod
DB_PASSWORD=${SECURE_PASSWORD}  # ← Via secrets manager

# ============================================================================
# LOGS (pour audit)
# ============================================================================
LOG_CHANNEL=stack
LOG_LEVEL=warning
```

### Certificat SSL/TLS (obligatoire en production)

```bash
# Obtenir un certificat gratuit avec Let's Encrypt
certbot certonly --webroot -w /var/www/html -d votredomaine.com

# Ou utiliser un service managed (AWS ACM, Cloudflare, etc.)
```

---

## 📊 Audit et Monitoring <a name="audit"></a>

### Logging de sécurité

```php
// Les accès refusés sont loggés automatiquement avec le prefix SECURITY
Log::warning('SECURITY: Accès refusé à /admin/clients - rôle insuffisant', [
    'user_id' => $user->id,
    'user_role' => $userRole,
    'ip' => $request->ip(),
]);

// Vérifier les logs régulièrement
tail -f storage/logs/laravel.log | grep SECURITY
```

### Vérification des sessions

```php
// Afficher les sessions actives
SELECT * FROM sessions 
WHERE payload LIKE '%user_id%' 
ORDER BY last_activity DESC;

// Supprimer les sessions expirées
php artisan session:prune
```

---

## ✅ Checkliste de déploiement <a name="checklist"></a>

- [ ] **HTTPS activé** : `APP_URL=https://...`
- [ ] **Debug désactivé** : `APP_DEBUG=false`
- [ ] **Sessions chiffrées** : `SESSION_ENCRYPT=true`
- [ ] **Cookies sécurisés** : `SESSION_SECURE_COOKIE=true`
- [ ] **CORS restreint** : `allowed_origins` ≠ `['*']`
- [ ] **Sanctum configuré** : `stateful` includes votre domaine
- [ ] **APP_KEY** : nouveau (pas celui du développement)
- [ ] **Secrets** : dans les variables d'environnement
- [ ] **Logs** : rotation activée
- [ ] **Backups** : automatiques et testées

---

**Dernière mise à jour** : 2 janvier 2026
**Auteur** : GitHub Copilot
**Statut** : Production-Ready ✅
