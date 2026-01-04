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
        // For MySQL, we need to use raw SQL to modify the enum
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('account_request', 'transaction', 'price_update', 'admin_action', 'welcome')");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert the enum back to original values
        DB::statement("ALTER TABLE notifications MODIFY COLUMN type ENUM('account_request', 'transaction', 'price_update', 'admin_action')");
    }
};
