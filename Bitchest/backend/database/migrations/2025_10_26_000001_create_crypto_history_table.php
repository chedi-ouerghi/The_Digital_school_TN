<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('crypto_history', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('cryptomoney_id');
            $table->foreign('cryptomoney_id')->references('id')->on('cryptomoney')->onDelete('cascade');
            $table->decimal('price', 20, 10);
            $table->decimal('volume', 20, 2)->nullable();
            $table->date('recorded_at');
            $table->timestamps();

            // Contrainte métier critique : une seule cotation par jour et par crypto
            $table->unique(['cryptomoney_id', 'recorded_at'], 'crypto_history_unique_per_day');
            // Index composite pour recherches fréquentes cryptomoney_id + recorded_at
            $table->index(['cryptomoney_id', 'recorded_at'], 'crypto_history_crypto_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_history');
    }
};
