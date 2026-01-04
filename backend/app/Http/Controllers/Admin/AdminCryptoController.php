<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;

class AdminCryptoController extends Controller
{
    public function syncHistory(): JsonResponse
    {
        Artisan::call('crypto:sync-history');

        $output = trim(Artisan::output());

        if (str_contains($output, 'already executed today')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Daily sync already executed today',
            ], 409);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Daily crypto history synced',
            'logs' => explode("\n", $output),
        ]);
    }
}
