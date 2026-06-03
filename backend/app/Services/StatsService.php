<?php

namespace App\Services;

use App\Models\User;
use App\Models\Transaction;
use App\Models\Cryptomoney;
use Illuminate\Support\Facades\DB;

class StatsService
{
    /**
     * Retourne statistiques globales simples.
     */
    public function getGlobalStats(): array
    {
        $totalClients = User::where('role', 'CLIENT')->count();
        // volume total transactions (utilise total_eur si disponible)
        $volume = Transaction::sum(DB::raw('COALESCE(total_eur, quantity * price)'));
        // estimation CA (frais) : exemple taux 0.5% (configurable)
        $feeRate = config('app.transaction_fee_rate', 0.005); // default 0.5%
        $revenue = $volume * $feeRate;

        // top cryptos by traded quantity
        $top = Transaction::select('cryptomoney_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('cryptomoney_id')
            ->orderByDesc('total_qty')
            ->limit(5)
            ->get()
            ->map(function ($row) {
                $crypto = Cryptomoney::find($row->cryptomoney_id);
                return [
                    'cryptomoney_id' => $row->cryptomoney_id,
                    'symbole' => $crypto ? $crypto->symbole : null,
                    'total_quantity' => (float)$row->total_qty,
                ];
            });

        return [
            'total_clients' => $totalClients,
            'total_transaction_volume_eur' => (float)$volume,
            'estimated_revenue_eur' => (float)$revenue,
            'top_traded' => $top,
        ];
    }
}
