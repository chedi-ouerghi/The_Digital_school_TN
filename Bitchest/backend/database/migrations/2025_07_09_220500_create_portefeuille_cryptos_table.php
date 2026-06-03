<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Renommage logique: wallet_cryptos -> crypto_wallet_assets
        Schema::create('crypto_wallet_assets', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('wallet_id');
            $table->foreign('wallet_id')->references('id')->on('wallets')->onDelete('cascade');
            $table->string('cryptomoney_id');
            $table->foreign('cryptomoney_id')->references('id')->on('cryptomoney')->onDelete('cascade');
            $table->decimal('quantity', 20, 8)->default(0);
            $table->decimal('average_buy_price', 20, 8)->default(0);
            $table->timestamps();
            
            $table->unique(['wallet_id', 'cryptomoney_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('crypto_wallet_assets');
    }
};
