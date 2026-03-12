<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\SafeController;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends SafeController
{
    /**
     * Display a listing of categories
     */
    public function index(Request $request)
    {
        $query = Category::withCount(['brands', 'complaints']);

        // Search
        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        // Filter by status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $categories = $query->orderBy('order')->paginate(20);

        return view('admin.categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new category
     */
    public function create()
    {
        $parentCategories = Category::whereNull('parent_id')
            ->orderBy('name')
            ->get();

        return view('admin.categories.create', compact('parentCategories'));
    }

    /**
     * Store a newly created category in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        // Set order to max + 1 if not provided
        if (!isset($validated['order'])) {
            $validated['order'] = Category::max('order') + 1;
        }

        // Handle icon upload
        if ($request->hasFile('icon')) {
            $validated['icon'] = $request->file('icon')->store('categories', 'public');
        }

        $category = Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla oluşturuldu.');
    }

    /**
     * Display the specified category
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children']);
        $category->loadCount(['brands', 'complaints']);

        return view('admin.categories.show', compact('category'));
    }

    /**
     * Show the form for editing the specified category
     */
    public function edit(Category $category)
    {
        $parentCategories = Category::whereNull('parent_id')
            ->where('id', '!=', $category->id)
            ->orderBy('name')
            ->get();

        return view('admin.categories.edit', compact('category', 'parentCategories'));
    }

    /**
     * Update the specified category in storage
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
            'slug' => 'nullable|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'icon' => 'nullable|image|max:1024',
            'is_active' => 'boolean',
            'order' => 'nullable|integer',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);

        // Prevent self-parenting
        if (isset($validated['parent_id']) && $validated['parent_id'] == $category->id) {
            return back()->with('error', 'Kategori kendi üst kategorisi olamaz.');
        }

        // Handle icon upload
        if ($request->hasFile('icon')) {
            // Delete old icon
            if ($category->icon) {
                Storage::disk('public')->delete($category->icon);
            }
            $validated['icon'] = $request->file('icon')->store('categories', 'public');
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla güncellendi.');
    }

    /**
     * Remove the specified category from storage
     */
    public function destroy(Category $category)
    {
        // Check if category has brands or complaints
        if ($category->brands()->count() > 0 || $category->complaints()->count() > 0) {
            return back()->with('error', 'Bu kategori ile ilişkili markalar veya şikayetler var. Önce bunları silin veya başka bir kategoriye taşıyın.');
        }

        // Delete icon
        if ($category->icon) {
            Storage::disk('public')->delete($category->icon);
        }

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Kategori başarıyla silindi.');
    }

    /**
     * Reorder categories
     */
    public function reorder(Request $request)
    {
        $request->validate([
            'orders' => 'required|array',
            'orders.*' => 'required|integer',
        ]);

        foreach ($request->orders as $id => $order) {
            Category::where('id', $id)->update(['order' => $order]);
        }

        return back()->with('success', 'Kategori sıralaması güncellendi.');
    }
}
