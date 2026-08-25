<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Ordre respectant les dépendances :
     * Users -> Cryptomoney -> CryptoHistory -> Wallets -> Assets -> Transactions
     * Pour le seed de base, seuls Users + Cryptos + History sont requis.
     * Les wallets/transactions sont créés via factories/tests ou API.
     */
    public function run(): void
    {
        // 1. Admin user (idempotent)
        try {
            User::updateOrCreate(
                ['email' => 'chediouerghi88@gmail.com'],
                [
                    'id' => (string) Str::uuid(),
                    'name' => 'Admin',
                    'password' => bcrypt('Admin123!'),
                    'role' => 'ADMIN',
                    'email_verified_at' => now(),
                    'password_changed_at' => now(),
                ]
            );
            if ($this->command) {
                $this->command->info('✓ Admin user ensured');
            }
        } catch (\Throwable $e) {
            $this->command?->error('Error creating admin user: ' . $e->getMessage());
        }

        // 2. Cryptos + History (idempotent, 40 jours, 400 lignes)
        try {
            $this->call([CryptoSeeder::class]);
            // CryptoSeeder délègue déjà la génération d'historique via CryptoService -> CryptoHistoryGenerator
            // CryptoHistorySeeder peut être appelé séparément mais n'est pas nécessaire ici (évite double génération)
            // $this->call([CryptoHistorySeeder::class]);

            if ($this->command) {
                $this->command->info('✓ Cryptos seeded');
            }
        } catch (\Throwable $e) {
            $this->command?->error('Error seeding cryptos: ' . $e->getMessage());
            throw $e;
        }

        // 3. Wallets / Assets / Transactions :
        // Non seedés par défaut (créés à la demande via API/factories).
        // Si besoin d'un jeu de démo, décommenter :
        // $this->call([WalletSeeder::class]);
    }
}
