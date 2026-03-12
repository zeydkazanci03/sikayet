<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\SafeController;
use App\Models\Complaint;
use App\Models\Brand;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends SafeController
{
    /**
     * Display a listing of complaints
     */
    public function index(Request $request)
    {
        $query = Complaint::with(['user', 'brand', 'category', 'moderator']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('complaint_number', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by priority
        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter reported
        if ($request->filled('is_reported')) {
            $query->where('is_reported', true);
        }

        $complaints = $query->latest()->paginate(20);
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();

        return view('admin.complaints.index', compact('complaints', 'brands', 'categories'));
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {
        $brands = Brand::where('status', 'approved')->orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.complaints.create', compact('brands', 'categories', 'users'));
    }

    /**
     * Store a newly created complaint in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,approved,rejected,spam',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Generate complaint number
        $validated['complaint_number'] = 'SK-' . strtoupper(uniqid());

        // Handle image uploads
        if ($request->hasFile('images')) {
            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('complaints', 'public');
                $images[] = $path;
            }
            $validated['images'] = json_encode($images);
        }

        $complaint = Complaint::create($validated);

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Şikayet başarıyla oluşturuldu.');
    }

    /**
     * Display the specified complaint
     */
    public function show(Complaint $complaint)
    {
        $complaint->load(['user', 'brand', 'category', 'comments.user', 'moderator']);

        return view('admin.complaints.show', compact('complaint'));
    }

    /**
     * Show the form for editing the specified complaint
     */
    public function edit(Complaint $complaint)
    {
        $brands = Brand::orderBy('name')->get();
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('admin.complaints.edit', compact('complaint', 'brands', 'categories', 'users'));
    }

    /**
     * Update the specified complaint in storage
     */
    public function update(Request $request, Complaint $complaint)
    {
        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,approved,rejected,spam',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Handle new image uploads
        if ($request->hasFile('images')) {
            // Delete old images
            if ($complaint->images) {
                $oldImages = json_decode($complaint->images, true);
                foreach ($oldImages as $oldImage) {
                    Storage::disk('public')->delete($oldImage);
                }
            }

            $images = [];
            foreach ($request->file('images') as $image) {
                $path = $image->store('complaints', 'public');
                $images[] = $path;
            }
            $validated['images'] = json_encode($images);
        }

        $complaint->update($validated);

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Şikayet başarıyla güncellendi.');
    }

    /**
     * Remove the specified complaint from storage
     */
    public function destroy(Complaint $complaint)
    {
        // Delete images
        if ($complaint->images) {
            $images = json_decode($complaint->images, true);
            foreach ($images as $image) {
                Storage::disk('public')->delete($image);
            }
        }

        $complaint->delete();

        return redirect()->route('admin.complaints.index')
            ->with('success', 'Şikayet başarıyla silindi.');
    }

    /**
     * Approve a complaint
     */
    public function approve($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Şikayet onaylandı.');
    }

    /**
     * Reject a complaint
     */
    public function reject($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Şikayet reddedildi.');
    }

    /**
     * Mark a complaint as spam
     */
    public function markAsSpam($id)
    {
        $complaint = Complaint::findOrFail($id);
        $complaint->update([
            'status' => 'spam',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Şikayet spam olarak işaretlendi.');
    }

    /**
     * Assign a moderator to complaint
     */
    public function assignModerator(Request $request)
    {
        $request->validate([
            'complaint_id' => 'required|exists:complaints,id',
            'moderator_id' => 'required|exists:users,id',
        ]);

        $complaint = Complaint::findOrFail($request->complaint_id);
        $complaint->update(['moderated_by' => $request->moderator_id]);

        return back()->with('success', 'Moderatör atandı.');
    }

    /**
     * Change complaint priority
     */
    public function changePriority(Request $request)
    {
        $request->validate([
            'complaint_id' => 'required|exists:complaints,id',
            'priority' => 'required|in:low,medium,high,urgent',
        ]);

        $complaint = Complaint::findOrFail($request->complaint_id);
        $complaint->update(['priority' => $request->priority]);

        return back()->with('success', 'Öncelik değiştirildi.');
    }

    /**
     * Change complaint category
     */
    public function changeCategory(Request $request)
    {
        $request->validate([
            'complaint_id' => 'required|exists:complaints,id',
            'category_id' => 'required|exists:categories,id',
        ]);

        $complaint = Complaint::findOrFail($request->complaint_id);
        $complaint->update(['category_id' => $request->category_id]);

        return back()->with('success', 'Kategori değiştirildi.');
    }

    /**
     * Bulk approve complaints
     */
    public function bulkApprove(Request $request)
    {
        $request->validate([
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:complaints,id',
        ]);

        Complaint::whereIn('id', $request->complaint_ids)->update([
            'status' => 'approved',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Seçili şikayetler onaylandı.');
    }

    /**
     * Bulk reject complaints
     */
    public function bulkReject(Request $request)
    {
        $request->validate([
            'complaint_ids' => 'required|array',
            'complaint_ids.*' => 'exists:complaints,id',
        ]);

        Complaint::whereIn('id', $request->complaint_ids)->update([
            'status' => 'rejected',
            'moderated_by' => auth()->id(),
            'moderated_at' => now(),
        ]);

        return back()->with('success', 'Seçili şikayetler reddedildi.');
    }

    /**
     * Export complaints to CSV
     */
    public function export(Request $request)
    {
        $query = Complaint::with(['user', 'brand', 'category']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $complaints = $query->get();

        $filename = 'complaints_' . date('Y-m-d_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($complaints) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Şikayet No', 'Kullanıcı', 'Marka', 'Kategori', 'Başlık', 'Durum', 'Öncelik', 'Tarih']);

            foreach ($complaints as $complaint) {
                fputcsv($file, [
                    $complaint->complaint_number,
                    $complaint->user->name,
                    $complaint->brand->name,
                    $complaint->category->name,
                    $complaint->title,
                    $complaint->status,
                    $complaint->priority ?? 'N/A',
                    $complaint->created_at->format('Y-m-d H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
