<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // If the column exists and is not a string, convert it to VARCHAR and recreate FK
        $table = 'blog_posts';
        $column = 'user_id';

        // Skip for SQLite as it doesn't support the same ALTER TABLE syntax
        if (DB::getDriverName() === 'sqlite') {
            return;
        }

        $row = DB::selectOne("SELECT DATA_TYPE FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?", [$table, $column]);

        if ($row) {
            $dataType = strtolower($row->DATA_TYPE ?? '');
            // If column is numeric (like bigint), change to VARCHAR(64)
            if (in_array($dataType, ['bigint', 'int', 'mediumint', 'smallint'])) {
                try {
                    // drop existing FK if exists (Laravel default name)
                    DB::statement("ALTER TABLE `{$table}` DROP FOREIGN KEY `{$table}_{$column}_foreign`");
                } catch (\Throwable $e) {
                    // ignore if constraint does not exist
                }

                // modify column to VARCHAR(64) NULL
                DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` VARCHAR(64) NULL");

                try {
                    // add FK referencing users(id)
                    DB::statement("ALTER TABLE `{$table}` ADD CONSTRAINT `{$table}_{$column}_foreign` FOREIGN KEY (`{$column}`) REFERENCES `users`(`id`) ON DELETE CASCADE");
                } catch (\Throwable $e) {
                    // ignore if adding FK fails for any reason
                }
            }
        }
    }

    public function down(): void
    {
        // No automated down action - leave column as-is to avoid data loss
    }
};
