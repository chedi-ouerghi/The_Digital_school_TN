<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleMiddleware
{
    /**
     * Gère l’accès selon le rôle utilisateur.
     *
     * Usage : middleware('role:ADMIN') ou middleware('role:ADMIN,MANAGER')     *
     * SÉCURITÉ : Middleware de vérification des rôles
     * 
     * Protections :
     * - Authentification requise (401 sinon)
     * - Rôle défini (403 sinon)
     * - Rôle autorisé (403 sinon)
     * - Logging de tous les accès refusés (audit trail)
     * - Pas d'élévation de privilèges via paramètres     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        // SÉCURITÉ : Vérifier que l'utilisateur est authentifié
        if (!$user) {
            Log::warning('SECURITY: Accès refusé à ' . $request->path() . ' - utilisateur non authentifié', [
                'ip' => $request->ip(),
                'method' => $request->method(),
            ]);
            return response()->json(['error' => 'Unauthenticated'], 401);
        }

        // SÉCURITÉ : Vérifier que le rôle est défini
        if (!isset($user->role) || empty($user->role)) {
            Log::warning('SECURITY: Accès refusé à ' . $request->path() . ' - rôle non défini', [
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);
            return response()->json(['error' => 'User role not defined'], 403);
        }

        // SÉCURITÉ : Normaliser les rôles (toujours en UPPERCASE)
        $userRole = strtoupper($user->role);
        $allowedRoles = array_map('strtoupper', $roles);

        // SÉCURITÉ : Vérifier que l'utilisateur a un rôle autorisé
        if (!in_array($userRole, $allowedRoles, true)) {
            Log::warning('SECURITY: Accès refusé à ' . $request->path() . ' - rôle insuffisant', [
                'user_id' => $user->id,
                'user_role' => $userRole,
                'required_roles' => implode(',', $allowedRoles),
                'ip' => $request->ip(),
                'method' => $request->method(),
            ]);
            return response()->json(['error' => 'Unauthorized - insufficient permissions'], 403);
        }

        return $next($request);
    }
}
