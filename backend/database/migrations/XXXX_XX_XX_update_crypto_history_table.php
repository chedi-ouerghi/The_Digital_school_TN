<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('crypto_history')) {
            Schema::create('crypto_history', function (Blueprint $table) {
                $table->string('id', 14)->primary();

                $table->string('cryptomoney_id', 14)->index();
                $table->foreign('cryptomoney_id')
                    ->references('id')
                    ->on('cryptomoney')
                    ->onDelete('cascade');

                $table->decimal('price', 20, 10);
                $table->decimal('market_cap', 20, 2)->nullable();
                $table->decimal('volume', 20, 2)->nullable();

                $table->timestamp('recorded_at')->index();

                $table->timestamps();

                $table->index(['cryptomoney_id', 'recorded_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('crypto_history');
    }
};
