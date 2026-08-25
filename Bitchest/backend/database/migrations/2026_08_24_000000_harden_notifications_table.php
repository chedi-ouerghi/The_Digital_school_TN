<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->string('action_url')->nullable()->after('message');
            $table->string('entity_id')->nullable()->after('action_url');
            $table->string('entity_type')->nullable()->after('entity_id');
            $table->json('metadata')->nullable()->after('entity_type');
            $table->timestamp('read_at')->nullable()->after('is_read');
            $table->string('dedupe_key')->nullable()->unique()->after('read_at');
            $table->index(['user_id', 'is_read', 'created_at']);
        });

        if (DB::getDriverName() === 'mysql') {
            DB::statement("ALTER TABLE notifications MODIFY COLUMN type VARCHAR(80) NOT NULL");
        }
    }

    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropUnique(['dedupe_key']);
            $table->dropIndex(['user_id', 'is_read', 'created_at']);
            $table->dropColumn(['action_url', 'entity_id', 'entity_type', 'metadata', 'read_at', 'dedupe_key']);
        });
    }
};