<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('account_requests', function (Blueprint $table) {
            $table->string('id')->primary();

            // Relation 1:1 — un seul utilisateur (client) par demande
            $table->string('user_id')->nullable()->unique();
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->string('name');
            $table->string('email')->unique();

            // Statut de la demande
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');

            // Token unique pour validation par lien
            $table->string('token')->unique();

            // Informations de traitement
            $table->timestamp('processed_at')->nullable();

            // Admin qui a traité la demande (0..N)
            $table->string('processed_by')->nullable();
            $table->foreign('processed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            // Raison du rejet, si applicable
            $table->text('rejection_reason')->nullable();

            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('account_requests');
    }
};
