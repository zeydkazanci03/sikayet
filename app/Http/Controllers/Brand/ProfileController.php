<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Show the form for editing brand profile
     */
    public function edit()
    {
        $brand = auth()->user()->brand;
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        // If brand doesn't exist, create a new one
        if (!$brand) {
            $brand = new Brand();
            $brand->user_id = auth()->id();
        }

        return view('brand.profile.edit', compact('brand', 'categories'));
    }

    /**
     * Update brand profile
     */
    public function update(Request $request)
    {
        $brand = auth()->user()->brand;

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:brands,name,' . ($brand->id ?? 'NULL'),
            'slug' => 'nullable|string|max:255|unique:brands,slug,' . ($brand->id ?? 'NULL'),
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'website' => 'nullable|url',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email',
            'address' => 'nullable|string',
            'facebook' => 'nullable|url',
            'twitter' => 'nullable|url',
            'instagram' => 'nullable|url',
            'linkedin' => 'nullable|url',
        ]);

        // Generate slug if not provided
        $validated['slug'] = $validated['slug'] ?? Str::slug($validated['name']);
        $validated['user_id'] = auth()->id();

        if ($brand) {
            $brand->update($validated);
            $message = 'Profil başarıyla güncellendi.';
        } else {
            // Set status to pending for new brands
            $validated['status'] = 'pending';
            $brand = Brand::create($validated);
            $message = 'Profil başarıyla oluşturuldu. Onay bekliyor.';
        }

        return back()->with('success', $message);
    }

    /**
     * Update brand logo
     */
    public function updateLogo(Request $request)
    {
        $brand = auth()->user()->brand;

        if (!$brand) {
            return back()->with('error', 'Marka bulunamadı.');
        }

        $request->validate([
            'logo' => 'required|image|max:2048|mimes:jpeg,png,jpg,gif,svg',
        ]);

        // Delete old logo
        if ($brand->logo) {
            Storage::disk('public')->delete($brand->logo);
        }

        // Store new logo
        $logoPath = $request->file('logo')->store('brands/logos', 'public');

        $brand->update(['logo' => $logoPath]);

        return back()->with('success', 'Logo başarıyla güncellendi.');
    }

    /**
     * Update brand banner
     */
    public function updateBanner(Request $request)
    {
        $brand = auth()->user()->brand;

        if (!$brand) {
            return back()->with('error', 'Marka bulunamadı.');
        }

        $request->validate([
            'banner' => 'required|image|max:4096|mimes:jpeg,png,jpg,gif',
        ]);

        // Delete old banner
        if ($brand->banner) {
            Storage::disk('public')->delete($brand->banner);
        }

        // Store new banner
        $bannerPath = $request->file('banner')->store('brands/banners', 'public');

        $brand->update(['banner' => $bannerPath]);

        return back()->with('success', 'Banner başarıyla güncellendi.');
    }
}
