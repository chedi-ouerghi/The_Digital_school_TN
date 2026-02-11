<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class AdminCryptoController extends Controller
{
    public function syncHistory(): JsonResponse
    {
        try {
            // Lancer la commande de synchronisation
            Artisan::call('crypto:sync-history');
            
            $output = trim(Artisan::output());
            $logs = array_values(array_filter(explode("\n", $output)));
            
            // Vérifier si certaines cryptos étaient déjà synchronisées
            $alreadySyncedCount = 0;
            $successCount = 0;
            $errorCount = 0;
            
            foreach ($logs as $log) {
                if (str_contains($log, '⏭️')) $alreadySyncedCount++;
                if (str_contains($log, '✅')) $successCount++;
                if (str_contains($log, '💥')) $errorCount++;
            }
            
            $message = 'Sync completed';
            if ($alreadySyncedCount > 0) {
                $message .= " ($alreadySyncedCount already up to date)";
            }
            if ($successCount > 0) {
                $message .= " ($successCount synchronized)";
            }
            if ($errorCount > 0) {
                $message .= " ($errorCount failed)";
            }
            
            return response()->json([
                'status' => $errorCount === 0 ? 'success' : 'partial',
                'message' => $message,
                'stats' => [
                    'already_synced' => $alreadySyncedCount,
                    'successful' => $successCount,
                    'failed' => $errorCount,
                ],
                'logs' => $logs,
                'executed_at' => now()->toDateTimeString(),
            ], $errorCount === 0 ? 200 : 207);
            
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Sync command failed to execute',
                'error' => $e->getMessage(),
                'executed_at' => now()->toDateTimeString(),
            ], 500);
        }
    }
}