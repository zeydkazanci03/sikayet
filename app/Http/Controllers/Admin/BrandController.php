<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    /**
     * Display a listing of brands
     */
    public function index(Request $request)
    {
        $query = Brand::with(['category', 'user']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter featured
        if ($request->filled('is_featured')) {
            $query->where('is_featured', true);
        }

        $brands = $query->latest()->paginate(20);
        $categories = Category::orderBy('name')->get();

        return view('admin.brands.index', compact('brands', 'categories'));
    }

    /**
     * Show the form for creating a new brand
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.brands.create', compact('categories', 'users'));
    }

    /**
     * Store a newly created brand in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands',
            'slug' => 'nullable|string|max:255|unique:brands',
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
            'status' => 'required|in:pending,approved,rejected,suspended',
            'is_featured' => 'boolean',
            'is_verified' => 'boolean',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('brands/logos', 'public');
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            $validated['banner'] = $request->file('banner')->store('brands/banners', 'public');
        }

        $brand = Brand::create($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla oluşturuldu.');
    }

    /**
     * Display the specified brand
     */
    public function show(Brand $brand)
    {
        $brand->load(['category', 'user', 'complaints']);

        $stats = [
            'total_complaints' => $brand->complaints()->count(),
            'pending_complaints' => $brand->complaints()->where('status', 'pending')->count(),
            'approved_complaints' => $brand->complaints()->where('status', 'approved')->count(),
            'solved_complaints' => $brand->complaints()->where('is_solved', true)->count(),
            'total_views' => $brand->complaints()->sum('views'),
        ];

        return view('admin.brands.show', compact('brand', 'stats'));
    }

    /**
     * Show the form for editing the specified brand
     */
    public function edit(Brand $brand)
    {
        $categories = Category::orderBy('name')->get();
        $users = User::where('role', 'user')->orderBy('name')->get();

        return view('admin.brands.edit', compact('brand', 'categories', 'users'));
    }

    /**
     * Update the specified brand in storage
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . $brand->id,
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . $brand->id,
            'category_id' => 'required|exists:categories,id',
            'user_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'logo' => 'nullable|image|max:2048',
            'banner' => 'nullable|image|max:4096',
            'status' => 'required|in:pending,approved,rejected,suspended',
            'is_featured' => 'boolean',
            'is_verified' => 'boolean',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        // Handle logo upload
        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($brand->logo) {
                Storage::disk('public')->delete($brand->logo);
            }
            $validated['logo'] = $request->file('logo')->store('brands/logos', 'public');
        }

        // Handle banner upload
        if ($request->hasFile('banner')) {
            // Delete old banner
            if ($brand->banner) {
                Storage::disk('public')->delete($brand->banner);
            }
            $validated['banner'] = $request->file('banner')->store('brands/banners', 'public');
        }

        $brand->update($validated);

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla güncellendi.');
    }

    /**
     * Remove the specified brand from storage
     */
    public function destroy(Brand $brand)
    {
        // Check if brand has complaints
        if ($brand->complaints()->count() > 0) {
            return back()->with('error', 'Bu marka ile ilişkili şikayetler var. Önce şikayetleri silin veya başka bir markaya taşıyın.');
        }

        // Delete logo and banner
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }
        if ($brand->banner) {
            Storage::disk('public')->delete($brand->banner);
        }

        $brand->delete();

        return redirect()->route('admin.brands.index')
            ->with('success', 'Marka başarıyla silindi.');
    }

    /**
     * Approve a brand
     */
    public function approve($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['status' => 'approved']);

        return back()->with('success', 'Marka onaylandı.');
    }

    /**
     * Reject a brand
     */
    public function reject($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['status' => 'rejected']);

        return back()->with('success', 'Marka reddedildi.');
    }

    /**
     * Suspend a brand
     */
    public function suspend($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['status' => 'suspended']);

        return back()->with('success', 'Marka askıya alındı.');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeature($id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update(['is_featured' => !$brand->is_featured]);

        $message = $brand->is_featured ? 'Marka öne çıkarıldı.' : 'Marka öne çıkarılmaktan kaldırıldı.';
        return back()->with('success', $message);
    }
}
