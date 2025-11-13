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
            $table->decimal('price', 20, 10)->nullable();
            $table->decimal('market_cap', 20, 2)->nullable();
            $table->decimal('volume', 20, 2)->nullable();
            $table->timestamp('recorded_at')->nullable()->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_history');
    }
};
