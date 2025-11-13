<?php

namespace App\Http\Controllers;

use App\Services\StatsService;
use Illuminate\Http\JsonResponse;

class AdminStatsController extends Controller
{
    protected $stats;

    public function __construct(StatsService $stats)
    {
        $this->stats = $stats;
    }

    public function index(): JsonResponse
    {
        try {
            $data = $this->stats->getGlobalStats();
            return response()->json($data);
        } catch (\Exception $e) {
            \Log::error('Erreur stats admin: '.$e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }
}
