<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

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
    'password' => bcrypt('Admin123!'), // ← très important !
    'role' => 'ADMIN',
    'email_verified_at' => now(),
]);

            
            echo "Admin user created successfully!\n";
            
        } catch (\Exception $e) {
            echo "Error creating admin user: " . $e->getMessage() . "\n";
        }
    }
}
