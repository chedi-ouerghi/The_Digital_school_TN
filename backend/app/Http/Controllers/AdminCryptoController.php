<?php

namespace App\Http\Controllers;

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
        if (!$crypto) return response()->json(['error'=>'Crypto non trouvée'],404);
        
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
        if (!$crypto) return response()->json(['error'=>'Crypto non trouvée'],404);
        
        // Supprimer l'image si elle existe
        $crypto->deleteImage();
        
        $crypto->delete();
        return response()->json(['message'=>'Crypto supprimée']);
    }

    /**
     * Synchroniser toutes les cryptos (si coingecko_id present)
     */
    public function sync(): JsonResponse
    {
        $results = ['updated' => 0, 'failed' => 0, 'errors' => []];
        $cryptos = Cryptomoney::whereNotNull('coingecko_id')->get();
        foreach ($cryptos as $c) {
            try {
                $this->cryptoService->addFromCoinGecko($c->coingecko_id);
                $results['updated']++;
            } catch (\Exception $e) {
                $results['failed']++;
                $results['errors'][] = ['id' => $c->id, 'error' => $e->getMessage()];
            }
        }
        return response()->json($results);
    }

    /**
     * Récupérer une crypto pour modification
     */
    public function edit($id): JsonResponse
    {
        $crypto = Cryptomoney::find($id);
        if (!$crypto) {
            return response()->json(['error' => 'Crypto non trouvée'], 404);
        }
        return response()->json($crypto);
    }
}
