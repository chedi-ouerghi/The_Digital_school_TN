<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Only add the column if it does not already exist (avoid duplicate column errors)
        if (! Schema::hasColumn('blog_posts', 'user_id')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                // Utiliser string() au lieu de foreignId() car users.id est une string
                $table->string('user_id', 64)->nullable()->after('id');
                $table->foreign('user_id')
                    ->references('id')
                    ->on('users')
                    ->cascadeOnDelete();
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('blog_posts', 'user_id')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};
