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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'profile_picture')) {
                $table->string('profile_picture')->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'profile_banner')) {
                // after can fail if profile_picture not yet exists, so use conditional
                if (Schema::hasColumn('users', 'profile_picture')) {
                    $table->string('profile_banner')->nullable()->after('profile_picture');
                } else {
                    $table->string('profile_banner')->nullable();
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['profile_picture', 'profile_banner']);
        });
    }
};
