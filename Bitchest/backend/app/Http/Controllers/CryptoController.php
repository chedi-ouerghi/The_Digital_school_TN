<?php

namespace App\Http\Controllers;

use App\Models\Cryptomoney;
use App\Services\CryptoService;
use App\Http\Requests\AddCryptoRequest;
use Illuminate\Http\JsonResponse;
use App\Models\CryptoHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

/**
 * @OA\Tag(
 *     name="Crypto",
 *     description="Gestion des cryptomonnaies"
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
        $perPage = (int) request()->get('per_page', 10);
        $perPage = min($perPage, 100);
        $page = (int) request()->get('page', 1);
        
        // Cache Redis pour les cryptos - 5 minutes TTL
        $cacheKey = 'cryptos_list:page_' . $page . ':per_page_' . $perPage;
        $ttl = 60 * 5; // 5 minutes
        
        $cryptos = Cache::remember($cacheKey, $ttl, function () use ($perPage) {
            return $this->cryptoService->listCryptos($perPage);
        });
        
        return response()->json($cryptos, 200);

    } catch (\Exception $e) {
        Log::error('Error retrieving cryptos', [
            'error' => $e->getMessage(),
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
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Détail de la crypto"),
     *     @OA\Response(response=404, description="Crypto non trouvée")
     * )
     */
    public function show($id): JsonResponse
    {
        try {
            // Cache Redis pour une crypto spécifique - 10 minutes TTL
            $cacheKey = 'crypto_single:' . $id;
            $ttl = 60 * 10; // 10 minutes
            
            $crypto = Cache::remember($cacheKey, $ttl, function () use ($id) {
                return $this->cryptoService->getCryptoById($id);
            });
            
            return response()->json($crypto, 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'error' => 'Cryptocurrency not found',
                'code' => 'crypto_not_found'
            ], 404);

        } catch (\Exception $e) {
            Log::error('Error retrieving cryptocurrency', [
                'crypto_id' => $id,
                'error' => $e->getMessage(),
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
     *     summary="Récupérer l'historique des prix (30 derniers jours)",
     *     tags={"Crypto"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
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
     *             @OA\Property(property="count", type="integer"),
     *             @OA\Property(property="from", type="string", format="date"),
     *             @OA\Property(property="to", type="string", format="date")
     *         )
     *     ),
     *     @OA\Response(response=404, description="Crypto non trouvée"),
     *     @OA\Response(response=422, description="Paramètres invalides")
     * )
     */
  public function history($id): JsonResponse
{
    try {
        // Cache Redis pour la crypto - 10 minutes TTL
        $cacheKey = 'crypto_single:' . $id;
        $ttl = 60 * 10; // 10 minutes
        
        $crypto = Cache::remember($cacheKey, $ttl, function () use ($id) {
            return $this->cryptoService->getCryptoById($id);
        });

        $days = (int) request()->get('days', 90);
        if ($days < 1 || $days > 365) {
            return response()->json([
                'error' => 'The number of days must be between 1 and 365',
                'code'  => 'invalid_days'
            ], 422);
        }

        // Cache Redis pour l'historique - 15 minutes TTL
        $historyCacheKey = 'crypto_history:' . $id . ':days_' . $days;
        $historyTtl = 60 * 15; // 15 minutes
        
        $prices = Cache::remember($historyCacheKey, $historyTtl, function () use ($id, $days) {
            return $this->cryptoService->getMarketChart($id, $days);
        });

        // Génération de données de remplacement lorsque l'API ne renvoie aucun résultat
        if (empty($prices)) {
            Log::warning('No price data available - generating synthetic data', [
                'crypto_id' => $id,
                'days' => $days,
            ]);
            
            // La méthode getMarketChart doit maintenant TOUJOURS retourner des données
            // Cela signifie qu'il y a un problème de configuration
            return response()->json([
                'error' => 'Unable to fetch price data',
                'code'  => 'no_data'
            ], 500);
        }

        // Transformation des prix et de leur variation sur 24 heures
        $history = collect($prices)->map(function ($row) {
            return [
                'timestamp' => $row[0],
                'date' => Carbon::createFromTimestampMs($row[0])->toDateString(),
                'price' => $row[1],
                'volume' => $row[2] ?? 0.00,
                'change_24h_pct' => $row[3] ?? 0.00,
            ];
        });

        $firstDate = Carbon::createFromTimestampMs($prices[0][0]);
        $lastDate  = Carbon::createFromTimestampMs(end($prices)[0]);

        return response()->json([
            'crypto' => [
                'id'     => $crypto->id,
                'symbol' => $crypto->symbol,
                'name'   => $crypto->name,
            ],
            'meta' => [
                'count' => $history->count(),
                'from'  => $firstDate->toDateString(),
                'to'    => $lastDate->toDateString(),
                'days'  => $days,
            ],
            'history' => $history->values(),
        ], 200);


    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException) {
        return response()->json([
            'error' => 'Cryptocurrency not found',
            'code'  => 'crypto_not_found'
        ], 404);

    } catch (\Exception $e) {
        Log::error('Error retrieving cryptocurrency history', [
            'crypto_id' => $id,
            'error'     => $e->getMessage(),
        ]);

        return response()->json([
            'error' => 'Server error',
            'code'  => 'internal_error'
        ], 500);
    }
}

}