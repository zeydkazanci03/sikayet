<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\SafeController;
use App\Models\Complaint;
use Illuminate\Http\Request;

class ComplaintController extends SafeController
{
    /**
     * Display a listing of brand's complaints
     */
    public function index(Request $request)
    {
        $brand = auth()->user()->brand;

        if (!$brand) {
            return redirect()->route('brand.dashboard')
                ->with('error', 'Marka bulunamadı.');
        }

        $query = $brand->complaints()->with(['user', 'category']);

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

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by response status
        if ($request->filled('response_status')) {
            if ($request->response_status === 'responded') {
                $query->whereNotNull('responded_at');
            } else {
                $query->whereNull('responded_at');
            }
        }

        // Filter by solved status
        if ($request->filled('is_solved')) {
            $query->where('is_solved', $request->is_solved);
        }

        $complaints = $query->latest()->paginate(20);

        // Stats for filters
        $stats = [
            'total' => $brand->complaints()->count(),
            'pending' => $brand->complaints()->where('status', 'pending')->count(),
            'approved' => $brand->complaints()->where('status', 'approved')->count(),
            'needs_response' => $brand->complaints()->where('status', 'approved')->whereNull('responded_at')->count(),
            'solved' => $brand->complaints()->where('is_solved', true)->count(),
        ];

        return view('brand.complaints.index', compact('complaints', 'stats'));
    }

    /**
     * Display the specified complaint
     */
    public function show($id)
    {
        $brand = auth()->user()->brand;

        $complaint = Complaint::with(['user', 'category', 'comments.user', 'brand'])
            ->where('brand_id', $brand->id)
            ->findOrFail($id);

        // Increment views
        $complaint->increment('views');

        return view('brand.complaints.show', compact('complaint'));
    }

    /**
     * Respond to a complaint
     */
    public function respond(Request $request, $id)
    {
        $brand = auth()->user()->brand;

        $complaint = Complaint::where('brand_id', $brand->id)
            ->where('status', 'approved')
            ->findOrFail($id);

        $validated = $request->validate([
            'response' => 'required|string',
        ]);

        // Create a comment as brand response
        $complaint->comments()->create([
            'user_id' => auth()->id(),
            'comment' => $validated['response'],
            'is_brand_response' => true,
        ]);

        // Update complaint with response timestamp
        $complaint->update([
            'responded_at' => now(),
        ]);

        return back()->with('success', 'Yanıtınız başarıyla gönderildi.');
    }

    /**
     * Mark a complaint as solved
     */
    public function markAsSolved($id)
    {
        $brand = auth()->user()->brand;

        $complaint = Complaint::where('brand_id', $brand->id)
            ->findOrFail($id);

        $complaint->update([
            'is_solved' => true,
            'solved_at' => now(),
        ]);

        return back()->with('success', 'Şikayet çözüldü olarak işaretlendi.');
    }
}
