<?php

namespace Tests\Feature\Controllers;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogControllerTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test get all blog posts (public)
     */
public function test_get_all_blog_posts()
{
    $admin = User::factory()->create(['role' => 'ADMIN']);
    BlogPost::factory()->count(5)->create(['user_id' => $admin->id]);

    $response = $this->getJson('/api/v1/blogs');

    $response->assertStatus(200)
        ->assertJsonStructure([
            'data' => [
                '*' => ['id', 'title', 'slug', 'content']
            ],
            'links',
            'current_page',  // Remplace 'meta' par les clés que vous avez
            'total',
            'per_page'
        ]);
}
    /**
     * Test get single blog post by slug
     */
    public function test_get_blog_post_by_slug()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $post = BlogPost::factory()->create([
            'user_id' => $admin->id,
            'slug' => 'test-post',
            'title' => 'Test Post'
        ]);

        $response = $this->getJson("/api/v1/blogs/{$post->slug}");

        $response->assertStatus(200)
            ->assertJson([
                'id' => $post->id,
                'title' => 'Test Post',
                'slug' => 'test-post'
            ]);
    }

    /**
     * Test get non-existent blog post
     */
    public function test_get_non_existent_blog_post()
    {
        $response = $this->getJson('/api/v1/blogs/non-existent-post');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Post not found']);
    }

    /**
     * Test search blog posts
     */
    public function test_search_blog_posts()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        BlogPost::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Laravel Tutorial'
        ]);
        BlogPost::factory()->create([
            'user_id' => $admin->id,
            'title' => 'Vue.js Guide'
        ]);

        $response = $this->getJson('/api/v1/blogs?search=Laravel');

        $response->assertStatus(200);
        $this->assertEquals(1, count($response->json('data')));
    }

    /**
     * Test create blog post as admin
     */
    public function test_create_blog_post_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/blogs', [
                'title' => 'New Blog Post',
                'category' => 'Tutorial',
                'summary' => 'A short summary',
                'content' => 'Full blog content here',
                'published_at' => now()
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure(['id', 'title', 'slug', 'content']);

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'New Blog Post',
            'user_id' => $admin->id
        ]);
    }

    /**
     * Test create blog post as non-admin
     */
    public function test_create_blog_post_as_non_admin()
    {
        $user = User::factory()->create(['role' => 'CLIENT']);
        $token = $user->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->postJson('/api/v1/admin/blogs', [
                'title' => 'New Blog Post',
                'content' => 'Content'
            ]);

        $response->assertStatus(403);
    }

    /**
     * Test update blog post as admin
     */
    public function test_update_blog_post_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $post = BlogPost::factory()->create(['user_id' => $admin->id]);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->putJson("/api/v1/admin/blogs/{$post->id}", [
                'title' => 'Updated Title',
                'content' => 'Updated content'
            ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('blog_posts', [
            'id' => $post->id,
            'title' => 'Updated Title'
        ]);
    }

    /**
     * Test delete blog post as admin
     */
    public function test_delete_blog_post_as_admin()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $post = BlogPost::factory()->create(['user_id' => $admin->id]);
        $token = $admin->createToken('TestToken')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer $token")
            ->deleteJson("/api/v1/admin/blogs/{$post->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }
}
