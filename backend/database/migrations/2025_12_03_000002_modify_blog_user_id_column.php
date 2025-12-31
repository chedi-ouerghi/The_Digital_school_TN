<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Cette migration n'est plus nécessaire car la migration précédente
     * crée déjà la colonne user_id avec le bon type (string).
     */
    public function up(): void
    {
        // Rien à faire - la colonne est déjà du bon type
    }

    public function down(): void
    {
        // Rien à faire
    }
};
