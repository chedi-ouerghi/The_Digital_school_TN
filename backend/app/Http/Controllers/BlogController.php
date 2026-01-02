<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class BlogController extends Controller
{
    // GET /api/v1/blogs
    public function index(Request $request)
    {
        try {
            $query = BlogPost::query();

            if ($search = $request->query('search')) {
                $query->where('title', 'like', "%{$search}%")
                      ->orWhere('summary', 'like', "%{$search}%");
            }

            if ($category = $request->query('category')) {
                $query->where('category', $category);
            }

            $posts = $query->with('author')->orderBy('published_at', 'desc')->paginate(12);

            return response()->json([
                'success' => true,
                'data' => $posts,
                'message' => 'Blog posts retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving blog posts',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/v1/blogs/{slug}
    public function show($slug)
    {
        try {
            $post = BlogPost::with('author')->where('slug', $slug)->first();
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'error' => 'Blog post not found'
                ], 404);
            }
            
            return response()->json([
                'success' => true,
                'data' => $post,
                'message' => 'Blog post retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error retrieving blog post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Admin: create a new blog post
    public function store(Request $request)
    {
        try {
            // Protected by route middleware 'auth:sanctum' + 'role:ADMIN'
            if (!Auth::check() || Auth::user()->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: Admin access required'
                ], 403);
            }

            $data = $request->validate([
                'title' => 'required|string|max:255',
                'category' => 'nullable|string|max:100',
                'summary' => 'nullable|string',
                'content' => 'nullable|string',
                'tags' => 'nullable|array',
                'image' => 'nullable|string',
                'published_at' => 'nullable|date',
            ]);

            $data['slug'] = Str::slug($data['title']);
            // ensure unique slug
            $base = $data['slug'];
            $i = 1;
            while (BlogPost::where('slug', $data['slug'])->exists()) {
                $data['slug'] = $base . '-' . $i++;
            }

            $data['user_id'] = Auth::id();

            $post = BlogPost::create($data);

            return response()->json([
                'success' => true,
                'data' => $post,
                'message' => 'Blog post created successfully'
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error creating blog post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Admin: update existing blog post (by id or slug)
    public function update(Request $request, $id)
    {
        try {
            $post = BlogPost::where('id', $id)->orWhere('slug', $id)->first();
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'error' => 'Blog post not found'
                ], 404);
            }

            // Protected by route middleware 'auth:sanctum' + 'role:ADMIN'
            if (!Auth::check() || Auth::user()->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: Admin access required'
                ], 403);
            }

            $data = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'category' => 'nullable|string|max:100',
                'summary' => 'nullable|string',
                'content' => 'nullable|string',
                'tags' => 'nullable|array',
                'image' => 'nullable|string',
                'published_at' => 'nullable|date',
            ]);

            if (isset($data['title'])) {
                $data['slug'] = Str::slug($data['title']);
                $base = $data['slug'];
                $i = 1;
                while (BlogPost::where('slug', $data['slug'])->where('id', '!=', $post->id)->exists()) {
                    $data['slug'] = $base . '-' . $i++;
                }
            }

            $post->update($data);

            return response()->json([
                'success' => true,
                'data' => $post,
                'message' => 'Blog post updated successfully'
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'error' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error updating blog post',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // Admin: delete post
    public function destroy($id)
    {
        try {
            $post = BlogPost::where('id', $id)->orWhere('slug', $id)->first();
            
            if (!$post) {
                return response()->json([
                    'success' => false,
                    'error' => 'Blog post not found'
                ], 404);
            }

            // Protected by route middleware 'auth:sanctum' + 'role:ADMIN'
            if (!Auth::check() || Auth::user()->role !== 'ADMIN') {
                return response()->json([
                    'success' => false,
                    'error' => 'Unauthorized: Admin access required'
                ], 403);
            }

            $post->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Blog post deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Error deleting blog post',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}