<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Extension des statuts possibles d'une demande de compte
        Schema::table('account_requests', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'VERIFIED', 'APPROVED', 'REJECTED', 'EXPIRED'])
                ->default('PENDING')
                ->change();
        });

        // Ajout de la date de vérification de l'adresse email
        Schema::table('account_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('account_requests', 'email_verified_at')) {
                $table->timestamp('email_verified_at')->nullable()->after('token');
            }
        });
    }

    public function down()
    {
        Schema::table('account_requests', function (Blueprint $table) {
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])
                ->default('PENDING')
                ->change();
        });

        Schema::table('account_requests', function (Blueprint $table) {
            if (Schema::hasColumn('account_requests', 'email_verified_at')) {
                $table->dropColumn('email_verified_at');
            }
        });
    }
};
