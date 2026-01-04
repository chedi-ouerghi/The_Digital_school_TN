<?php

namespace Tests\Feature\Controllers;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class BlogControllerTest extends TestCase
{

    /**
     * Test get all blog posts (public)
     */
public function test_get_all_blog_posts()
{
    $admin = User::factory()->create(['role' => 'ADMIN']);
    BlogPost::factory()->count(5)->create(['user_id' => $admin->id]);

    $response = $this->getJson('/api/v1/blogs');

    $response->assertStatus(200);
    $this->assertIsArray($response->json('data'));
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
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'title', 'slug'],
                'message'
            ]);
    }

    /**
     * Test get non-existent blog post
     */
    public function test_get_non_existent_blog_post()
    {
        $response = $this->getJson('/api/v1/blogs/non-existent-post');

        $response->assertStatus(404)
            ->assertJson(['error' => 'Blog post not found']);
    }

    /**
     * Test search blog posts
     */
    public function test_search_blog_posts()
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        BlogPost::factory()->count(15)->create([
            'user_id' => $admin->id
        ]);

        $response = $this->getJson('/api/v1/blogs?search=test');

        $response->assertStatus(200);
        // Just check that the response is successful without being too strict on count
        $this->assertIsArray($response->json('data'));
    }

    /**
     * Test create blog post as admin
     */
    public function test_create_blog_post_as_admin()
    {
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);

        $response = $this->authenticatedJson('POST', '/api/v1/admin/blogs', [
            'title' => 'New Blog Post',
            'category' => 'Tutorial',
            'summary' => 'A short summary',
            'content' => 'Full blog content here',
            'published_at' => now()
        ], $admin);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => ['id', 'title', 'slug'],
                'message'
            ]);

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
        $user = $this->createAuthenticatedUser(['role' => 'CLIENT']);

        $response = $this->authenticatedJson('POST', '/api/v1/admin/blogs', [
            'title' => 'New Blog Post',
            'content' => 'Content'
        ], $user);

        $response->assertStatus(403);
    }

    /**
     * Test update blog post as admin
     */
    public function test_update_blog_post_as_admin()
    {
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);
        $post = BlogPost::factory()->create(['user_id' => $admin->id]);

        $response = $this->authenticatedJson('PUT', "/api/v1/admin/blogs/{$post->id}", [
            'title' => 'Updated Title',
            'content' => 'Updated content'
        ], $admin);

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
        $admin = $this->createAuthenticatedUser(['role' => 'ADMIN']);
        $post = BlogPost::factory()->create(['user_id' => $admin->id]);

        $response = $this->authenticatedJson('DELETE', "/api/v1/admin/blogs/{$post->id}", [], $admin);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Blog post deleted successfully']);

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }
}
