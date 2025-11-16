<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class SettingController extends SafeController
{
    /**
     * Display a listing of settings
     */
    public function index()
    {
        $settings = Setting::all()->pluck('value', 'key');

        return view('admin.settings.index', compact('settings'));
    }

    /**
     * Store a newly created setting in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'required|string',
            'type' => 'required|in:text,textarea,boolean,number,image,file',
            'group' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        Setting::create($validated);

        // Clear settings cache
        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Ayar başarıyla oluşturuldu.');
    }

    /**
     * Update the specified setting in storage
     */
    public function update(Request $request, Setting $setting)
    {
        $validated = $request->validate([
            'key' => 'required|string|max:255|unique:settings,key,' . $setting->id,
            'value' => 'nullable|string',
            'type' => 'required|in:text,textarea,boolean,number,image,file',
            'group' => 'nullable|string|max:100',
            'description' => 'nullable|string',
        ]);

        // Handle file/image upload
        if ($request->hasFile('file')) {
            // Delete old file
            if ($setting->value && Storage::disk('public')->exists($setting->value)) {
                Storage::disk('public')->delete($setting->value);
            }
            $validated['value'] = $request->file('file')->store('settings', 'public');
        }

        // Handle boolean values
        if ($validated['type'] === 'boolean') {
            $validated['value'] = $request->has('value') ? '1' : '0';
        }

        $setting->update($validated);

        // Clear settings cache
        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Ayar başarıyla güncellendi.');
    }

    /**
     * Remove the specified setting from storage
     */
    public function destroy(Setting $setting)
    {
        // Delete file if exists
        if (in_array($setting->type, ['image', 'file']) && $setting->value) {
            Storage::disk('public')->delete($setting->value);
        }

        $setting->delete();

        // Clear settings cache
        Cache::forget('settings');

        return redirect()->route('admin.settings.index')
            ->with('success', 'Ayar başarıyla silindi.');
    }
}
