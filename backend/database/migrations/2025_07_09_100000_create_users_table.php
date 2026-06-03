<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->enum('role', ['ADMIN', 'CLIENT'])->default('CLIENT');
            $table->timestamp('last_id_change_at')->nullable();
            $table->rememberToken();
            $table->string('profile_picture')->nullable();
            $table->string('profile_banner')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Drop legacy tables referencing users to avoid FK errors
        if (Schema::hasTable('wallets')) {
            Schema::drop('wallets');
        }
        if (Schema::hasTable('wallets')) {
            try {
                Schema::table('wallets', function (Blueprint $table) {
                    $table->dropForeign(['user_id']);
                });
            } catch (\Throwable $e) {
                // Ignore if FK doesn't exist or is named differently
            }
            Schema::drop('wallets');
        }
        Schema::dropIfExists('users');
    }
};
