<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Modification de l'enum via SQL natif pour respecter le type MySQL
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('account_request', 'transaction', 'price_update', 'admin_action', 'welcome')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }
        // Restauration des valeurs initiales de l'enum lors de l'annulation de la migration
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('account_request', 'transaction', 'price_update', 'admin_action')");
    }
};
