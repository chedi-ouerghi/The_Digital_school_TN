<?php

namespace App\Http\Controllers;

use App\Models\Cryptomoney;
use App\Services\CryptoService;
use App\Http\Requests\AddCryptoRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Models\CryptoHistory;
use Carbon\Carbon;

/**
 * @OA\Tag(
 *     name="Crypto",
 *     description="Gestion des cryptomonnaies via CoinGecko"
 * )
 */
class CryptoController extends Controller
{
    protected $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/cryptos",
     *     summary="Ajouter une cryptomonnaie depuis CoinGecko (ADMIN)",
     *     tags={"Crypto"},
     *     security={{"sanctum":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"crypto_id"},
     *             @OA\Property(property="crypto_id", type="string", description="Nom de la crypto (ex: Bitcoin)")
     *         )
     *     ),
     *     @OA\Response(response=201, description="Crypto ajoutée/maj avec succès"),
     *     @OA\Response(response=422, description="Erreur de validation ou CoinGecko")
     * )
     */
    public function store(AddCryptoRequest $request): JsonResponse
    {
        try {
            $name = $request->crypto_id;
            $search = \Illuminate\Support\Facades\Http::withOptions(['verify' => false])
                ->get('https://api.coingecko.com/api/v3/coins/list?include_platform=false');
            if (!$search->ok()) {
                \Log::error('Erreur CoinGecko lors de la récupération de la liste : ' . ($search->body() ?? 'Aucune réponse'));
                return response()->json([
                    'error' => 'Erreur lors de la recherche CoinGecko.',
                    'details' => $search->json() ?? $search->body()
                ], 500);
            }
            $list = $search->json();
            $found = collect($list)->first(function ($item) use ($name) {
                return strtolower($item['name']) === strtolower($name);
            });
            if (!$found) {
                \Log::warning('Crypto non trouvée sur CoinGecko : ' . $name);
                return response()->json([
                    'error' => 'Crypto non trouvée par nom sur CoinGecko.',
                    'details' => $name
                ], 404);
            }
            $coingeckoId = $found['id'];
            try {
                $id = $this->cryptoService->addFromCoinGecko($coingeckoId);
                
                // Handle image upload if provided
                if ($request->hasFile('image')) {
                    $crypto = Cryptomoney::find($id);
                    if ($crypto) {
                        $imagePath = Cryptomoney::storeImage($request->file('image'));
                        $crypto->update(['image' => $imagePath]);
                    }
                }
                
                return response()->json(['message' => 'Crypto ajoutée/maj avec succès', 'id' => $id], 201);
            } catch (\Exception $e) {
                \Log::error('Erreur lors de l\'enregistrement de la crypto : ' . $e->getMessage());
                return response()->json([
                    'error' => 'Erreur lors de l\'enregistrement de la crypto',
                    'details' => $e->getMessage()
                ], 422);
            }
        } catch (\Exception $e) {
            \Log::error('Erreur interne lors de l\'ajout de crypto : ' . $e->getMessage());
            return response()->json([
                'error' => 'Erreur interne',
                'details' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/cryptos",
     *     summary="Lister les cryptomonnaies (public)",
     *     tags={"Crypto"},
     *     @OA\Response(response=200, description="Liste paginée des cryptos")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $cryptos = Cryptomoney::paginate(10);
            return response()->json($cryptos);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération des cryptos : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/cryptos/{id}",
     *     summary="Afficher une crypto par ID (public)",
     *     tags={"Crypto"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(response=200, description="Détail de la crypto"),
     *     @OA\Response(response=404, description="Crypto non trouvée")
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            $crypto = Cryptomoney::find($id);
            if (!$crypto) {
                \Log::warning('Crypto non trouvée en base : ' . $id);
                return response()->json(['error' => 'Crypto non trouvée'], 404);
            }
            return response()->json($crypto);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération de la crypto : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * @OA\\Get(
     *     path="/api/v1/cryptos/{id}/history",
     *     summary="Récupérer la courbe des 30 derniers jours pour une crypto",
     *     tags={"Crypto"},
     *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
     *     @OA\Response(response=200, description="Liste des prix (timestamp, prix)"),
     *     @OA\Response(response=404, description="Crypto non trouvée")
     * )
     */
    public function history($id): JsonResponse
    {
        try {
            $crypto = Cryptomoney::find($id);
            if (!$crypto) {
                return response()->json(['error' => 'Crypto non trouvée'], 404);
            }

            // D'abord essayer de récupérer depuis notre historique local
            $history = CryptoHistory::where('cryptomoney_id', $id)
                ->orderBy('recorded_at')
                ->get()
                ->map(function ($record) {
                    return [
                        $record->recorded_at->timestamp * 1000,
                        (float)$record->price
                    ];
                });

            // Si pas d'historique local, utiliser CoinGecko
            if ($history->isEmpty() && !empty($crypto->coingecko_id)) {
                $prices = $this->cryptoService->getMarketChart($crypto->coingecko_id, 30);
                
                // Sauvegarder l'historique récupéré
                foreach ($prices as [$timestamp, $price]) {
                    $date = Carbon::createFromTimestampMs($timestamp);
                    CryptoHistory::updateOrCreate(
                        [
                            'cryptomoney_id' => $crypto->id,
                            'recorded_at' => $date,
                        ],
                        [
                            'price' => $price,
                            'market_cap' => $crypto->market_cap,
                            'volume' => $crypto->volume_24h,
                        ]
                    );
                }
                
                return response()->json(['prices' => $prices]);
            }

            return response()->json(['prices' => $history]);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la récupération de l\'historique : ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
