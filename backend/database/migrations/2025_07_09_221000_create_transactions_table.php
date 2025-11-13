<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary();
            // Lien direct avec l'asset du wallet
            $table->string('crypto_wallet_asset_id');
            $table->foreign('crypto_wallet_asset_id')->references('id')->on('crypto_wallet_assets')->onDelete('cascade');

            $table->string('cryptomoney_id');
            $table->foreign('cryptomoney_id')->references('id')->on('cryptomoney')->onDelete('cascade');
            $table->enum('type', ['ACHAT', 'VENTE']);
            $table->decimal('quantity', 20, 8);
            $table->decimal('price', 20, 8);
            $table->decimal('total_eur', 20, 8);
            $table->boolean('admin_operation')->default(false);
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancel_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Index pour améliorer les performances des requêtes
            $table->index(['crypto_wallet_asset_id', 'cryptomoney_id']);
            $table->index(['created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};
