<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CheckStorageSymlink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:check-symlink';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and verify storage symlink';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $link = public_path('storage');
        $target = storage_path('app/public');

        if (is_link($link)) {
            $this->info('Storage symlink already exists.');
            $this->line('Link: ' . $link);
            $this->line('Target: ' . readlink($link));
        } else {
            $this->warn('Storage symlink not found. Creating...');
            try {
                symlink($target, $link);
                $this->info('Storage symlink created successfully!');
                $this->line('Link: ' . $link);
                $this->line('Target: ' . $target);
            } catch (\Exception $e) {
                $this->error('Failed to create symlink: ' . $e->getMessage());
                return 1;
            }
        }

        // Also verify the storage directory exists
        if (!File::isDirectory($target)) {
            $this->warn('Storage directory does not exist. Creating...');
            File::makeDirectory($target, 0755, true);
            $this->info('Storage directory created!');
        }

        return 0;
    }
}
