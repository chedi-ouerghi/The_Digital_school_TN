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
     * Usage : middleware('role:ADMIN') ou middleware('role:ADMIN,MANAGER')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = $request->user();

        if (! $user) {
            Log::warning('Accès refusé : utilisateur non authentifié.');
            return response()->json(['error' => 'Non authentifié.'], 401);
        }

        if (! isset($user->role)) {
            Log::warning("Accès refusé : rôle non défini pour l'utilisateur {$user->id}");
            return response()->json(['error' => 'Rôle utilisateur non défini.'], 403);
        }

        if (! in_array(strtoupper($user->role), array_map('strtoupper', $roles))) {
            Log::warning("Accès refusé : rôle requis (" . implode(',', $roles) . "), rôle utilisateur {$user->role} (ID {$user->id})");
            return response()->json(['error' => 'Accès non autorisé.'], 403);
        }

        return $next($request);
    }
}
