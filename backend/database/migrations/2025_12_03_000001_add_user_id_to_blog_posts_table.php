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
                $table->foreignId('user_id')->nullable()->constrained('users')->cascadeOnDelete()->after('id');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('blog_posts', 'user_id')) {
            Schema::table('blog_posts', function (Blueprint $table) {
                // dropConstrainedForeignId will remove FK and column
                $table->dropConstrainedForeignId('user_id');
            });
        }
    }
};
