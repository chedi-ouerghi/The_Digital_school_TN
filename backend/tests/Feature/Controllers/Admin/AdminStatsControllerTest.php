<?php

namespace Tests\Feature\Controllers\Admin;

use App\Models\User;
use Tests\TestCase;

class AdminStatsControllerTest extends TestCase
{

    /**
     * Test get admin stats as admin
     */
    public function test_get_admin_stats_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        User::factory()->count(5)->create(['role' => 'CLIENT']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_transactions',
                    'total_volume',
                    'total_users',
                    'top_cryptos',
                    'recent_transactions'
                ]
            ]);
    }

    /**
     * Test get admin stats as non-admin
     */
    public function test_get_admin_stats_as_non_admin()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(403);
    }

    /**
     * Test get admin stats without authentication
     */
    public function test_get_admin_stats_without_authentication()
    {
        $response = $this->getJson('/api/v1/admin/stats');

        $response->assertStatus(401);
    }

    /**
     * Test admin stats includes total users count
     */
    public function test_admin_stats_includes_total_users_count()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        User::factory()->count(3)->create(['role' => 'CLIENT']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'total_users' => 3
                ]
            ]);
    }

    /**
     * Test admin stats includes total volume
     */
    public function test_admin_stats_includes_total_volume()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'total_volume'
                ]
            ]);
    }

    /**
     * Test admin stats includes top cryptos
     */
    public function test_admin_stats_includes_top_cryptos()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'top_cryptos' => [
                        '*' => ['id', 'name', 'symbol']
                    ]
                ]
            ]);
    }

    /**
     * Test admin stats includes recent transactions
     */
    public function test_admin_stats_includes_recent_transactions()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->getJson('/api/v1/admin/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => [
                    'recent_transactions' => [
                        '*' => ['id', 'type', 'quantity']
                    ]
                ]
            ]);
    }
}
