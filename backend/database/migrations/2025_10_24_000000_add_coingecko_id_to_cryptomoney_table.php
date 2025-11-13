<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cryptomoney', function (Blueprint $table) {
            if (!Schema::hasColumn('cryptomoney', 'coingecko_id')) {
                $table->string('coingecko_id')->nullable()->unique()->after('symbol');
            }
        });
    }

    public function down(): void
    {
        Schema::table('cryptomoney', function (Blueprint $table) {
            if (Schema::hasColumn('cryptomoney', 'coingecko_id')) {
                $table->dropColumn('coingecko_id');
            }
        });
    }
};
