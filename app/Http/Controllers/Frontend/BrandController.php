<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    /**
     * Display a listing of brands
     */
    public function index(Request $request)
    {
        $query = Brand::where('status', 'approved');

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter featured
        if ($request->filled('featured')) {
            $query->where('is_featured', true);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'name');
        switch ($sort) {
            case 'most_complaints':
                $query->withCount('complaints')->orderBy('complaints_count', 'desc');
                break;
            case 'least_complaints':
                $query->withCount('complaints')->orderBy('complaints_count', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('name');
        }

        $brands = $query->withCount('complaints')->paginate(24);

        // Get categories for filter
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // Featured brands
        $featuredBrands = Brand::where('status', 'approved')
            ->where('is_featured', true)
            ->withCount('complaints')
            ->take(6)
            ->get();

        return view('frontend.brands.index', compact('brands', 'categories', 'featuredBrands'));
    }

    /**
     * Display the specified brand
     */
    public function show($slug)
    {
        $brand = Brand::where('slug', $slug)
            ->where('status', 'approved')
            ->withCount('complaints')
            ->firstOrFail();

        // Get brand's complaints
        $complaintsQuery = $brand->complaints()
            ->with(['user', 'category'])
            ->where('status', 'approved');

        // Get latest complaints
        $latestComplaints = (clone $complaintsQuery)->latest()->take(10)->get();

        // Get popular complaints
        $popularComplaints = (clone $complaintsQuery)->orderBy('view_count', 'desc')->take(10)->get();

        // Statistics
        $stats = [
            'total_complaints' => $brand->complaints()->where('status', 'approved')->count(),
            'pending_complaints' => $brand->complaints()->where('status', 'pending')->count(),
            'solved_complaints' => $brand->complaints()->where('status', 'approved')->where('is_resolved', true)->count(),
            'total_views' => $brand->complaints()->where('status', 'approved')->sum('view_count'),
            'response_rate' => $this->calculateResponseRate($brand),
            'resolution_rate' => $this->calculateResolutionRate($brand),
        ];

        // Category breakdown
        $categoryBreakdown = $brand->complaints()
            ->where('status', 'approved')
            ->select('category_id', \DB::raw('count(*) as count'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        // Related brands (same category)
        $relatedBrands = Brand::where('status', 'approved')
            ->where('category_id', $brand->category_id)
            ->where('id', '!=', $brand->id)
            ->withCount('complaints')
            ->take(6)
            ->get();

        return view('frontend.brands.show', compact(
            'brand',
            'latestComplaints',
            'popularComplaints',
            'stats',
            'categoryBreakdown',
            'relatedBrands'
        ));
    }

    /**
     * Search brands
     */
    public function search(Request $request)
    {
        $query = $request->get('q');

        if (empty($query)) {
            return redirect()->route('frontend.brands.index');
        }

        $brands = Brand::where('status', 'approved')
            ->where(function($q) use ($query) {
                $q->where('name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%");
            })
            ->withCount('complaints')
            ->orderBy('name')
            ->paginate(24);

        return view('frontend.brands.search', compact('brands', 'query'));
    }

    /**
     * Calculate response rate
     */
    private function calculateResponseRate($brand)
    {
        $total = $brand->complaints()->where('status', 'approved')->count();

        if ($total === 0) {
            return 0;
        }

        $responded = $brand->complaints()
            ->where('status', 'approved')
            ->whereNotNull('brand_first_response_at')
            ->count();

        return round(($responded / $total) * 100, 2);
    }

    /**
     * Calculate resolution rate
     */
    private function calculateResolutionRate($brand)
    {
        $total = $brand->complaints()->where('status', 'approved')->count();

        if ($total === 0) {
            return 0;
        }

        $solved = $brand->complaints()
            ->where('status', 'approved')
            ->where('is_resolved', true)
            ->count();

        return round(($solved / $total) * 100, 2);
    }
}
