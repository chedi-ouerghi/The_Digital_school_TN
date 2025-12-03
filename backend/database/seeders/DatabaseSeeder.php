<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Database\Seeders\BlogSeeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create admin user
        try {
          User::create([
    'id' => strtoupper(Str::random(14)),
    'name' => 'Admin',
    'email' => 'chediouerghi88@gmail.com',
    'password' => bcrypt('Admin123!'),
    'role' => 'ADMIN',
    'email_verified_at' => now(),
]);

            
            echo "Admin user created successfully!\n";
            
        } catch (\Exception $e) {
            echo "Error creating admin user: " . $e->getMessage() . "\n";
        }

        // Seed blog posts
        try {
            $this->call([BlogSeeder::class]);
            echo "Blog posts seeded successfully!\n";
        } catch (\Exception $e) {
            echo "Error seeding blog posts: " . $e->getMessage() . "\n";
        }
    }
}
