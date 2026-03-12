<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\SafeController;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BlogController extends SafeController
{
    /**
     * Display a listing of blog posts
     */
    public function index(Request $request)
    {
        $query = BlogPost::with('author');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $posts = $query->latest()->paginate(20);

        return view('admin.blog.index', compact('posts'));
    }

    /**
     * Show the form for creating a new blog post
     */
    public function create()
    {
        return view('admin.blog.create');
    }

    /**
     * Store a newly created blog post in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts',
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);
        $validated['author_id'] = auth()->id();

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        // Set published_at if status is published
        if ($validated['status'] === 'published') {
            $validated['published_at'] = now();
        }

        $post = BlogPost::create($validated);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog yazısı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified blog post
     */
    public function show(BlogPost $blog)
    {
        $blog->load('author');

        return view('admin.blog.show', compact('blog'));
    }

    /**
     * Show the form for editing the specified blog post
     */
    public function edit(BlogPost $blog)
    {
        return view('admin.blog.edit', compact('blog'));
    }

    /**
     * Update the specified blog post in storage
     */
    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,' . $blog->id,
            'content' => 'required|string',
            'excerpt' => 'nullable|string',
            'featured_image' => 'nullable|image|max:2048',
            'status' => 'required|in:draft,published',
            'is_featured' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'meta_keywords' => 'nullable|string',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['title']);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('blog', 'public');
        }

        // Set published_at if status is published and not already set
        if ($validated['status'] === 'published' && !$blog->published_at) {
            $validated['published_at'] = now();
        }

        $blog->update($validated);

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog yazısı başarıyla güncellendi.');
    }

    /**
     * Remove the specified blog post from storage
     */
    public function destroy(BlogPost $blog)
    {
        // Delete featured image
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blog.index')
            ->with('success', 'Blog yazısı başarıyla silindi.');
    }
}
