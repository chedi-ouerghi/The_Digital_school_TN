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
        $query = BlogPost::query();

        if ($search = $request->query('search')) {
            $query->where('title', 'like', "%{$search}%")
                  ->orWhere('summary', 'like', "%{$search}%");
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        $posts = $query->with('author')->orderBy('published_at', 'desc')->paginate(12);

        return response()->json($posts);
    }

    // GET /api/v1/blogs/{slug}
    public function show($slug)
    {
        $post = BlogPost::with('author')->where('slug', $slug)->first();
        if (! $post) {
            return response()->json(['message' => 'Post not found'], 404);
        }
        return response()->json($post);
    }

    // Admin: create a new blog post
    public function store(Request $request)
    {
        // Protected by route middleware 'auth:sanctum' + 'role:ADMIN'

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

        return response()->json($post, 201);
    }

    // Admin: update existing blog post (by id or slug)
    public function update(Request $request, $id)
    {
        $post = BlogPost::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        // Protected by route middleware 'auth:sanctum' + 'role:ADMIN'

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

        return response()->json($post);
    }

    // Admin: delete post
    public function destroy($id)
    {
        $post = BlogPost::where('id', $id)->orWhere('slug', $id)->firstOrFail();
        // Protected by route middleware 'auth:sanctum' + 'role:ADMIN'
        $post->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
