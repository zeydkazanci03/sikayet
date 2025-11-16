<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Brand;
use App\Models\Category;
use App\Models\BlogPost;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display the homepage
     */
    public function index()
    {
        // Featured brands
        $featuredBrands = Brand::where('status', 'approved')
            ->where('is_featured', true)
            ->withCount('complaints')
            ->take(12)
            ->get();

        // Recent complaints
        $recentComplaints = Complaint::with(['user', 'brand', 'category'])
            ->where('status', 'approved')
            ->latest()
            ->take(10)
            ->get();

        // Popular complaints (most viewed)
        $popularComplaints = Complaint::with(['user', 'brand', 'category'])
            ->where('status', 'approved')
            ->orderBy('view_count', 'desc')
            ->take(10)
            ->get();

        // Trending complaints (most comments in last 7 days)
        $trendingComplaints = Complaint::with(['user', 'brand', 'category'])
            ->where('status', 'approved')
            ->where('created_at', '>=', now()->subDays(7))
            ->withCount('comments')
            ->orderBy('comments_count', 'desc')
            ->take(10)
            ->get();

        // Categories with complaint count
        $categories = Category::where('is_active', true)
            ->withCount('complaints')
            ->orderBy('complaints_count', 'desc')
            ->take(12)
            ->get();

        // Statistics
        $stats = [
            'total_complaints' => Complaint::where('status', 'approved')->count(),
            'total_brands' => Brand::where('status', 'approved')->count(),
            'solved_complaints' => Complaint::where('status', 'approved')->where('is_resolved', true)->count(),
            'total_users' => \App\Models\User::count(),
        ];

        // Latest blog posts
        $latestPosts = BlogPost::where('status', 'published')
            ->latest('published_at')
            ->take(3)
            ->get();

        return view('frontend.home', compact(
            'featuredBrands',
            'recentComplaints',
            'popularComplaints',
            'trendingComplaints',
            'categories',
            'stats',
            'latestPosts'
        ));
    }

    /**
     * Search complaints, brands, and categories
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('home')
                ->with('error', 'Lütfen bir arama terimi girin.');
        }

        // Search complaints
        $complaints = Complaint::with(['user', 'brand', 'category'])
            ->where('status', 'approved')
            ->where(function($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                  ->orWhere('content', 'like', "%{$query}%")
                  ->orWhere('complaint_number', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(20, ['*'], 'complaints');

        // Search brands
        $brands = Brand::where('status', 'approved')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->withCount('complaints')
            ->paginate(12, ['*'], 'brands');

        // Search categories
        $categories = Category::where('is_active', true)
            ->where('name', 'like', "%{$query}%")
            ->withCount('complaints')
            ->get();

        return view('frontend.search-results', compact(
            'query',
            'complaints',
            'brands',
            'categories'
        ));
    }
}
