<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            // Change tokenable_id to string to support string primary keys
            $table->string('tokenable_id')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Intentionally left empty to avoid invalid cast when existing data are strings.
        // If you need to revert, ensure data are compatible before changing back.
        // Schema::table('personal_access_tokens', function (Blueprint $table) {
        //     $table->unsignedBigInteger('tokenable_id')->change();
        // });
    }
};