<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

/**
 * ✅ ADMIN CRYPTO CONTROLLER
 * 
 * Les 10 cryptos sont FIXES et non modifiables:
 * ❌ Pas de création de cryptos
 * ❌ Pas de modification de cryptos
 * ❌ Pas de suppression de cryptos
 * 
 * ✅ Seule fonctionnalité: Synchroniser l'historique 30 jours
 */
class AdminCryptoController extends Controller
{
    /**
     * Synchroniser l'historique des prix (24h, 7j, 30j)
     * 
     * Générationheure:
     * - Génère automatiquement les données synthétiques
     * - Met à jour change_24h_pct de chaque crypto
     * - Crée l'historique complet (1 par jour, par 4h, par heure)
     * 
     * Réponse: JSON avec statut et logs de synchronisation
     */
    public function syncHistory(): JsonResponse
    {
        try {
            Artisan::call('crypto:sync-history');

            $output = trim(Artisan::output() ?? '');
            $lines = $output === '' ? [] : array_values(array_filter(
                explode("\n", $output),
                fn($l) => trim($l) !== ''
            ));

            return response()->json([
                'status' => 'success',
                'message' => 'Synchronization of history launched',
                'output' => $lines,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Error during execution',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
