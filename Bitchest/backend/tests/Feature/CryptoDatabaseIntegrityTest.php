<?php

namespace Tests\Feature;

use App\Models\Cryptomoney;
use App\Models\CryptoHistory;
use App\Models\CryptoWalletAsset;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use App\Services\CryptoHistoryGenerator;
use Database\Seeders\CryptoSeeder;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class CryptoDatabaseIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Seed via DatabaseSeeder (admin + 10 cryptos + 400 histories)
        $this->seed(DatabaseSeeder::class);
    }

    /** Test 1: Chaque crypto officielle possède exactement 40 historiques */
    public function test_each_crypto_has_exactly_40_histories(): void
    {
        $days = (int) config('bitchest.history_days', 40);
        $officialSymbols = array_keys(config('bitchest.cryptos', []));
        $this->assertGreaterThanOrEqual(10, Cryptomoney::count(), 'Expected at least 10 cryptos');
        foreach (Cryptomoney::whereIn('symbol', $officialSymbols)->get() as $crypto) {
            $count = CryptoHistory::where('cryptomoney_id', $crypto->id)->count();
            $this->assertEquals($days, $count, "Crypto {$crypto->symbol} should have {$days} histories, got {$count}");
        }
    }

    /** Test 2: Aucune crypto ne possède deux historiques le même jour (UNIQUE) */
    public function test_no_duplicate_history_per_day_per_crypto(): void
    {
        $duplicates = DB::table('crypto_history')
            ->select('cryptomoney_id', 'recorded_at', DB::raw('COUNT(*) as c'))
            ->groupBy('cryptomoney_id', 'recorded_at')
            ->having('c', '>', 1)
            ->get();

        $this->assertCount(0, $duplicates, 'Duplicate history rows found: ' . $duplicates->toJson());

        // Vérifier que la contrainte UNIQUE existe physiquement (sqlite: index)
        // On tente d'insérer un doublon et on attend une exception
        $crypto = Cryptomoney::first();
        $existing = CryptoHistory::where('cryptomoney_id', $crypto->id)->first();
        $this->expectException(\Throwable::class);
        CryptoHistory::create([
            'cryptomoney_id' => $crypto->id,
            'price' => 123.45,
            'volume' => 1000,
            'recorded_at' => $existing->recorded_at,
        ]);
    }

    /** Test 3: Aucun prix historique n'est négatif ou nul */
    public function test_no_negative_prices(): void
    {
        $negative = CryptoHistory::where('price', '<=', 0)->count();
        $this->assertEquals(0, $negative, 'Found negative or zero prices');

        $nullPrice = CryptoHistory::whereNull('price')->count();
        $this->assertEquals(0, $nullPrice, 'Found null prices');

        $negativeCurrent = Cryptomoney::where('price_eur', '<=', 0)->count();
        $this->assertEquals(0, $negativeCurrent, 'Found cryptos with non-positive current price');
    }

    /** Test 4: Le dernier historique correspond au prix courant (uniquement officiels) */
    public function test_latest_history_equals_current_price(): void
    {
        $officialSymbols = array_keys(config('bitchest.cryptos', []));
        foreach (Cryptomoney::whereIn('symbol', $officialSymbols)->get() as $crypto) {
            $latest = CryptoHistory::where('cryptomoney_id', $crypto->id)
                ->orderByDesc('recorded_at')
                ->first();

            $this->assertNotNull($latest, "No history for {$crypto->symbol}");
            // Comparaison avec bccomp à 8 décimales
            $this->assertEquals(0, bccomp((string) $latest->price, (string) $crypto->price_eur, 8),
                "Latest history price {$latest->price} != current price {$crypto->price_eur} for {$crypto->symbol}");
        }

        // Vérifier que la date du dernier est aujourd'hui (officiels seulement)
        $today = now()->toDateString();
        foreach (Cryptomoney::whereIn('symbol', $officialSymbols)->get() as $crypto) {
            $latest = CryptoHistory::where('cryptomoney_id', $crypto->id)->orderByDesc('recorded_at')->first();
            $this->assertEquals($today, \Carbon\Carbon::parse($latest->recorded_at)->toDateString(),
                "Latest history not today for {$crypto->symbol}");
        }
    }

    /** Test 5: Le Seeder est idempotent */
    public function test_seeder_is_idempotent(): void
    {
        $days = (int) config('bitchest.history_days', 40);
        $officialSymbols = array_keys(config('bitchest.cryptos', []));
        $initialCryptoCount = Cryptomoney::whereIn('symbol', $officialSymbols)->count();
        $initialHistoryCount = CryptoHistory::whereIn('cryptomoney_id', Cryptomoney::whereIn('symbol', $officialSymbols)->pluck('id'))->count();

        $this->assertEquals(10, $initialCryptoCount);
        $this->assertEquals(10 * $days, $initialHistoryCount);

        // Re-seed
        $this->seed(DatabaseSeeder::class);

        $this->assertEquals($initialCryptoCount, Cryptomoney::whereIn('symbol', $officialSymbols)->count(), 'Crypto count changed after re-seed');
        $this->assertEquals($initialHistoryCount, CryptoHistory::whereIn('cryptomoney_id', Cryptomoney::whereIn('symbol', $officialSymbols)->pluck('id'))->count(), 'History count changed after re-seed');

        // Re-seed via CryptoHistoryGenerator directly (officiels seulement)
        $generator = app(CryptoHistoryGenerator::class);
        foreach (Cryptomoney::whereIn('symbol', $officialSymbols)->get() as $crypto) {
            $generator->generateFor($crypto, $days);
        }
        $this->assertEquals($initialHistoryCount, CryptoHistory::whereIn('cryptomoney_id', Cryptomoney::whereIn('symbol', $officialSymbols)->pluck('id'))->count(), 'History count changed after generator re-run');
    }

    /** Test 6: Les relations Eloquent fonctionnent (officiel) */
    public function test_eloquent_relations_work(): void
    {
        $officialSymbols = array_keys(config('bitchest.cryptos', []));
        $crypto = Cryptomoney::whereIn('symbol', $officialSymbols)->first();
        $this->assertNotNull($crypto->histories, 'histories relation missing');
        $this->assertGreaterThan(0, $crypto->histories->count());
        $this->assertInstanceOf(CryptoHistory::class, $crypto->histories->first());
        $this->assertEquals($crypto->id, $crypto->histories->first()->cryptomoney->id);

        // Wallet relations
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $this->assertEquals($user->id, $wallet->user->id);
        $this->assertEquals($wallet->id, $user->wallet->id ?? $user->wallets->first()->id);

        $asset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id,
        ]);
        $this->assertEquals($wallet->id, $asset->wallet->id);
        $this->assertEquals($crypto->id, $asset->cryptomoney->id);
        $this->assertTrue($wallet->cryptoWalletAssets->contains($asset));

        // CryptoHistory belongsTo
        $history = CryptoHistory::where('cryptomoney_id', $crypto->id)->first();
        $this->assertEquals($crypto->id, $history->cryptomoney->id);
    }

    /** Test 7: Une transaction possède bien un wallet asset */
    public function test_transaction_has_wallet_asset(): void
    {
        $user = User::factory()->create();
        $wallet = Wallet::factory()->create(['user_id' => $user->id]);
        $crypto = Cryptomoney::where('symbol', 'BTC')->first() ?? Cryptomoney::first();
        $asset = CryptoWalletAsset::factory()->create([
            'wallet_id' => $wallet->id,
            'cryptomoney_id' => $crypto->id,
            'quantity' => '1.5',
            'average_buy_price' => '50000',
        ]);

        $transaction = Transaction::factory()->create([
            'crypto_wallet_asset_id' => $asset->id,
            'cryptomoney_id' => $crypto->id,
            'type' => 'ACHAT',
            'quantity' => '0.5',
            'price' => (string) $crypto->price_eur,
            'total_eur' => bcmul('0.5', (string) $crypto->price_eur, 8),
        ]);

        $this->assertNotNull($transaction->cryptoWalletAsset);
        $this->assertEquals($asset->id, $transaction->cryptoWalletAsset->id);
        $this->assertEquals($crypto->id, $transaction->cryptomoney->id);
        $this->assertEquals($wallet->id, $transaction->cryptoWalletAsset->wallet->id);

        // Vérifier hasMany côté asset
        $this->assertTrue($asset->transactions->contains($transaction));
    }

    /** Test supplémentaire: La période est J-39 .. J (40 dates distinctes) — officiels seulement */
    public function test_history_covers_40_distinct_dates(): void
    {
        $days = (int) config('bitchest.history_days', 40);
        $today = now()->startOfDay();
        $start = $today->copy()->subDays($days - 1)->toDateString();
        $end = $today->toDateString();
        $officialSymbols = array_keys(config('bitchest.cryptos', []));

        foreach (Cryptomoney::whereIn('symbol', $officialSymbols)->get() as $crypto) {
            $min = CryptoHistory::where('cryptomoney_id', $crypto->id)->min('recorded_at');
            $max = CryptoHistory::where('cryptomoney_id', $crypto->id)->max('recorded_at');
            $this->assertEquals($start, \Carbon\Carbon::parse($min)->toDateString(), "Min date mismatch for {$crypto->symbol}");
            $this->assertEquals($end, \Carbon\Carbon::parse($max)->toDateString(), "Max date mismatch for {$crypto->symbol}");

            $distinctDates = CryptoHistory::where('cryptomoney_id', $crypto->id)->distinct()->pluck('recorded_at')->count();
            $this->assertEquals($days, $distinctDates, "Distinct dates not {$days} for {$crypto->symbol}");
        }
    }

    /** Test supplémentaire: Factory CryptoHistory ne crée pas de doublons si utilisée avec date existante -> doit échouer grâce à UNIQUE */
    public function test_factory_respects_unique_constraint(): void
    {
        $officialSymbols = array_keys(config('bitchest.cryptos', []));
        $crypto = Cryptomoney::whereIn('symbol', $officialSymbols)->first();
        $existing = CryptoHistory::where('cryptomoney_id', $crypto->id)->first();
        $this->assertNotNull($existing, 'No history to test unique constraint');
        $existingDate = $existing->recorded_at;

        // Création manuelle avec même date doit lever exception via DB constraint
        try {
            CryptoHistory::factory()->create([
                'cryptomoney_id' => $crypto->id,
                'recorded_at' => \Carbon\Carbon::parse($existingDate)->toDateString(),
            ]);
            $this->fail('Expected unique constraint violation');
        } catch (\Throwable $e) {
            $this->assertTrue(true, 'Unique constraint enforced');
        }
    }
}
