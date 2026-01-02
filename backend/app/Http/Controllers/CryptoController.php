<?php

namespace App\Http\Controllers;

use App\Models\Cryptomoney;
use App\Services\CryptoService;
use App\Http\Requests\AddCryptoRequest;
use Illuminate\Http\JsonResponse;
use App\Models\CryptoHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Tag(
 *     name="Crypto",
 *     description="Gestion des cryptomonnaies via CoinGecko"
 * )
 */
class CryptoController extends Controller
{
    use AuthorizesRequests;

    protected $cryptoService;

    public function __construct(CryptoService $cryptoService)
    {
        $this->cryptoService = $cryptoService;
    }

    /**
     * @OA\Post(
     *     path="/api/v1/admin/cryptos",
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
     *     @OA\Response(response=422, description="Erreur de validation ou CoinGecko"),
     *     @OA\Response(response=403, description="Non autorisé")
     * )
     */
    public function store(AddCryptoRequest $request): JsonResponse
    {
        try {
            // ✅ OPTION 1: Vérifier le rôle directement (Plus simple)
            if (auth()->user()->role !== 'ADMIN') {
                return response()->json([
                    'error' => 'Not authorized. Only administrators can add cryptocurrencies.',
                    'code' => 'unauthorized'
                ], 403);
            }

            $name = $request->crypto_id;

            // ✅ Vérifier que CoinGecko est accessible
            $search = Http::withOptions(['verify' => false])
                ->timeout(10)
                ->get('https://api.coingecko.com/api/v3/coins/list?include_platform=false');

            if (!$search->ok()) {
                Log::warning('CoinGecko API unavailable', [
                    'status' => $search->status(),
                    'crypto_searched' => $name
                ]);

                    return response()->json([
                        'error' => 'CoinGecko is currently unavailable. Please try again later.',
                    'code' => 'coingecko_unavailable'
                ], 503);
            }

            $list = $search->json();

            // ✅ Recherche case-insensitive et trim
            $found = collect($list)->first(function ($item) use ($name) {
                return strtolower(trim($item['name'])) === strtolower(trim($name));
            });

            if (!$found) {
                Log::info('Crypto not found on CoinGecko', ['crypto' => $name]);

                return response()->json([
                    'error' => "Cryptocurrency '{$name}' not found on CoinGecko",
                    'code' => 'crypto_not_found'
                ], 404);
            }

            $coingeckoId = $found['id'];

            try {
                $id = $this->cryptoService->addFromCoinGecko($coingeckoId);

                // ✅ Gérer l'image si fournie
                if ($request->hasFile('image')) {
                    try {
                        $crypto = Cryptomoney::find($id);
                        if ($crypto && $this->isValidImageFile($request->file('image'))) {
                            $imagePath = Cryptomoney::storeImage($request->file('image'));
                            $crypto->update(['image' => $imagePath]);
                        }
                    } catch (\Exception $e) {
                        Log::warning('Error uploading image', [
                            'crypto_id' => $id,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                Log::info('Cryptocurrency added successfully', ['crypto_id' => $id]);

                return response()->json([
                    'message' => 'Cryptocurrency added successfully',
                    'id' => $id,
                    'code' => 'crypto_added'
                ], 201);

            } catch (\Exception $e) {
                Log::error('Error saving cryptocurrency', [
                    'coingecko_id' => $coingeckoId,
                    'error' => $e->getMessage()
                ]);

                return response()->json([
                    'error' => 'Unable to register cryptocurrency',
                    'code' => 'save_failed'
                ], 422);
            }

        } catch (\Exception $e) {
            Log::error('Unexpected error during crypto addition', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Internal server error',
                'code' => 'internal_error'
            ], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/v1/cryptos",
     *     summary="Lister les cryptomonnaies (public)",
     *     tags={"Crypto"},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="integer", default=10)
     *     ),
     *     @OA\Response(response=200, description="Liste paginée des cryptos")
     * )
     */
    public function index(): JsonResponse
    {
        try {
            $perPage = request()->get('per_page', 10);
            $perPage = min($perPage, 100);

            $cryptos = Cryptomoney::paginate($perPage);

            return response()->json($cryptos);

        } catch (\Exception $e) {
            Log::error('Error retrieving cryptos', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Unable to retrieve cryptocurrencies',
                'code' => 'list_failed'
            ], 500);
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
            $crypto = Cryptomoney::findOrFail($id);
            return response()->json($crypto);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cryptocurrency not found',
                'code' => 'crypto_not_found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving a cryptocurrency', [
                'crypto_id' => $id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Server error',
                'code' => 'internal_error'
            ], 500);
        }
    }

/**
 * @OA\Get(
 *     path="/api/v1/cryptos/{id}/history",
 *     summary="Récupérer l'historique des prix (30 derniers jours par défaut)",
 *     tags={"Crypto"},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Parameter(
 *         name="days",
 *         in="query",
 *         required=false,
 *         @OA\Schema(type="integer", default=30, minimum=1, maximum=365)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Historique des prix",
 *         @OA\JsonContent(
 *             @OA\Property(property="prices", type="array",
 *                 @OA\Items(type="array", example={1700000000000, 50000.50})
 *             ),
 *             @OA\Property(property="symbol", type="string"),
 *             @OA\Property(property="name", type="string"),
 *             @OA\Property(property="count", type="integer")
 *         )
 *     ),
 *     @OA\Response(response=404, description="Crypto non trouvée"),
 *     @OA\Response(response=422, description="Paramètres invalides")
 * )
 */
public function history($id): JsonResponse
{
    try {
        // ✅ Récupérer la crypto avec validation
        $crypto = Cryptomoney::findOrFail($id);

        // ✅ Valider le paramètre days
        $days = (int) request()->get('days', 30);

        if ($days < 1 || $days > 365) {
            return response()->json([
                'error' => 'The number of days must be between 1 and 365',
                'code' => 'invalid_days'
            ], 422);
        }

        // ✅ ÉTAPE 1: Récupérer depuis la base de données locale
        $history = CryptoHistory::forCrypto($id)
            ->recent($days)
            ->orderedByDate()
            ->get();

        // ✅ Si pas d'historique, tenter CoinGecko
        if ($history->isEmpty() && !empty($crypto->coingecko_id)) {
            return $this->fetchFromCoinGeckoAndSave($crypto, $days, $id);
        }

        // ✅ Formater la réponse
        $prices = $history->map(function ($record) {
            return [
                (int) $record->recorded_at->timestamp * 1000,
                (float) $record->price
            ];
        })->values()->all();

        return response()->json([
            'prices' => $prices,
            'symbol' => $crypto->symbol,
            'name' => $crypto->name,
            'count' => count($prices),
            'from' => $history->first()?->recorded_at?->toDateString(),
            'to' => $history->last()?->recorded_at?->toDateString(),
        ], 200);

    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'error' => 'Cryptocurrency not found',
            'code' => 'crypto_not_found'
        ], 404);

    } catch (\Exception $e) {
        Log::error('Error retrieving cryptocurrency history', [
            'crypto_id' => $id,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'Server error',
            'code' => 'internal_error'
        ], 500);
    }
}

/**
 * Récupère l'historique depuis CoinGecko et le sauvegarde
 * 
 * @param Cryptomoney $crypto
 * @param int $days
 * @param string $cryptoId
 * @return JsonResponse
 */
private function fetchFromCoinGeckoAndSave(Cryptomoney $crypto, int $days, string $cryptoId): JsonResponse
{
    try {
        Log::info("CoinGecko historical data recovery for {$crypto->symbol}", [
            'coingecko_id' => $crypto->coingecko_id,
            'days' => $days
        ]);

        // ✅ Appeler CoinGecko
        $prices = $this->cryptoService->getMarketChart($crypto->coingecko_id, $days);

        if (empty($prices)) {
            throw new \Exception('No data received from CoinGecko');
        }

        $saved = 0;
        $failed = 0;

        // ✅ Sauvegarder chaque point dans la base de données
        foreach ($prices as [$timestamp, $price]) {
            try {
                // Valider le timestamp
                if (!$this->isValidTimestamp($timestamp)) {
                    Log::warning('Invalid timestamp ignored', [
                        'timestamp' => $timestamp,
                        'crypto_id' => $cryptoId
                    ]);
                    $failed++;
                    continue;
                }

                // Valider le prix
                $price = (float) $price;
                if ($price < 0.01) {
                    $price = 0.01;
                }

                // Créer la date
                $date = Carbon::createFromTimestampMs($timestamp);

                // ✅ Sauvegarder (ou update si existe)
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

                $saved++;

            } catch (\Exception $e) {
                Log::warning('Error saving historical point', [
                    'crypto_id' => $cryptoId,
                    'timestamp' => $timestamp,
                    'error' => $e->getMessage()
                ]);
                $failed++;
            }
        }

        Log::info("CoinGecko history saved", [
            'crypto_id' => $cryptoId,
            'saved' => $saved,
            'failed' => $failed
        ]);

        // ✅ Retourner les prix
        return response()->json([
            'prices' => $prices,
            'symbol' => $crypto->symbol,
            'name' => $crypto->name,
            'count' => count($prices),
            'source' => 'coingecko',
            'saved_count' => $saved,
        ], 200);

    } catch (\Exception $e) {
        Log::error('Error retrieving CoinGecko data', [
            'crypto_id' => $cryptoId,
            'error' => $e->getMessage()
        ]);

        return response()->json([
            'error' => 'History unavailable',
            'code' => 'history_unavailable',
            'details' => $e->getMessage()
        ], 503);
    }
}

/**
 * Valide qu'un timestamp est valide
 * Entre Jan 2000 et maintenant + 1 an
 */
private function isValidTimestamp($timestamp): bool
{
    if (!is_numeric($timestamp)) {
        return false;
    }

    $minTs = Carbon::parse('2000-01-01')->timestamp * 1000;
    $maxTs = Carbon::now()->addYear()->timestamp * 1000;

    return $timestamp >= $minTs && $timestamp <= $maxTs;
}

/**
 * Valide qu'un fichier d'image est correct
 */
private function isValidImageFile($file): bool
{
    $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2048 * 1024; // 2MB

    return in_array($file->getMimeType(), $allowedMimes) && $file->getSize() <= $maxSize;
}


}