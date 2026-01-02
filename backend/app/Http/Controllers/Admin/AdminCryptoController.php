<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\Cryptomoney;
use App\Services\CryptoService;

class AdminCryptoController extends Controller
{
    protected $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    public function update(Request $request, $id): JsonResponse
    {
        $data = $request->validate([
            'name' => ['nullable','string'],
            'symbol' => ['nullable','string'],
            'image' => ['nullable','file','image','max:2048'],
            'price_eur' => ['nullable','numeric'],
            'coingecko_id' => ['nullable','string'],
            'category' => ['nullable','string'],
            'website' => ['nullable','string'],
            'market_cap' => ['nullable','numeric'],
            'volume_24h' => ['nullable','numeric'],
            'change_24h_pct' => ['nullable','numeric'],
        ]);
        $crypto = Cryptomoney::find($id);
        if (!$crypto) return response()->json(['error'=>'Crypto not found'],404);
        
        // Gérer l'upload d'image
        if ($request->hasFile('image')) {
            $crypto->deleteImage();
            $imagePath = Cryptomoney::storeImage($request->file('image'));
            $data['image'] = $imagePath;
        }
        
        $crypto->update($data);
        return response()->json($crypto);
    }

    public function destroy($id): JsonResponse
    {
        $crypto = Cryptomoney::find($id);
        if (!$crypto) return response()->json(['error'=>'Crypto not found'],404);
        
        // Supprimer l'image si elle existe
        $crypto->deleteImage();
        
        $crypto->delete();
        return response()->json(['message'=>'Crypto deleted']);
    }

  


   /**
     * Synchroniser toutes les cryptos (si coingecko_id present)
     */
    
    public function syncHistory(): \Illuminate\Http\JsonResponse
{
    try {
        \Artisan::call('crypto:sync-history');

        $output = trim(\Artisan::output() ?? '');
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
