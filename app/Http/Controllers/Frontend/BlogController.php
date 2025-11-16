<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use Illuminate\Http\Request;

class BlogController extends Controller
{
    /**
     * Display a listing of blog posts
     */
    public function index(Request $request)
    {
        $query = Blog::with('author')
            ->where('status', 'published');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest('published_at');
                break;
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            default:
                $query->latest('published_at');
        }

        $posts = $query->paginate(12);

        // Featured posts
        $featuredPosts = Blog::where('status', 'published')
            ->where('is_featured', true)
            ->latest('published_at')
            ->take(3)
            ->get();

        // Recent posts for sidebar
        $recentPosts = Blog::where('status', 'published')
            ->latest('published_at')
            ->take(5)
            ->get();

        // Popular posts for sidebar
        $popularPosts = Blog::where('status', 'published')
            ->orderBy('view_count', 'desc')
            ->take(5)
            ->get();

        return view('frontend.blog.index', compact(
            'posts',
            'featuredPosts',
            'recentPosts',
            'popularPosts'
        ));
    }

    /**
     * Display the specified blog post
     */
    public function show($slug)
    {
        $post = Blog::with('author')
            ->where('slug', $slug)
            ->where('status', 'published')
            ->firstOrFail();

        // Increment views
        $post->increment('view_count');

        // Related posts
        $relatedPosts = Blog::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(3)
            ->get();

        // Recent posts for sidebar
        $recentPosts = Blog::where('status', 'published')
            ->where('id', '!=', $post->id)
            ->latest('published_at')
            ->take(5)
            ->get();

        return view('frontend.blog.show', compact('post', 'relatedPosts', 'recentPosts'));
    }
}
