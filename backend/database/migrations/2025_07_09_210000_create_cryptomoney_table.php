<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cryptomoney', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('symbol')->unique();
            $table->string('coingecko_id')->nullable()->unique();
            $table->string('image')->nullable();
            $table->string('category')->nullable();
            $table->string('website')->nullable();
            $table->decimal('price_eur', 20, 10)->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->decimal('volume_24h', 20, 2)->nullable();
            $table->decimal('change_24h_pct', 5, 2)->nullable();
            $table->dateTime('updated_at_api');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Ensure legacy tables referencing cryptomoney are dropped first to avoid FK errors
        if (Schema::hasTable('crypto_wallet_assets')) {
            Schema::drop('crypto_wallet_assets');
        }
        if (Schema::hasTable('wallet_cryptos')) {
            // Drop foreign key if it exists, then drop the legacy table
            try {
                Schema::table('wallet_cryptos', function (Blueprint $table) {
                    $table->dropForeign(['cryptomoney_id']);
                });
            } catch (\Throwable $e) {
                // Ignore if the foreign key doesn't exist or has a different name
            }
            Schema::drop('wallet_cryptos');
        }
        Schema::dropIfExists('cryptomoney');
    }
};
