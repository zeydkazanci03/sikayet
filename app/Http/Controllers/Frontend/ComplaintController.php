<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\SafeController;
use App\Models\Complaint;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ComplaintController extends SafeController
{
    /**
     * Display a listing of complaints
     */
    public function index(Request $request)
    {
        $query = Complaint::with(['user', 'brand', 'category'])
            ->where('status', 'approved');

        // Filter by brand
        if ($request->filled('brand_id')) {
            $query->where('brand_id', $request->brand_id);
        }

        // Filter by category
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter by solved status
        if ($request->filled('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'popular':
                $query->orderBy('view_count', 'desc');
                break;
            case 'oldest':
                $query->oldest();
                break;
            case 'most_commented':
                $query->withCount('comments')->orderBy('comments_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $complaints = $query->paginate(20);

        // Get brands and categories for filters
        $brands = Brand::where('status', 'approved')->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('frontend.complaints.index', compact('complaints', 'brands', 'categories'));
    }

    /**
     * Display the specified complaint
     */
    public function show($brandSlug, $complaintNumber)
    {
        $complaint = Complaint::with(['user', 'brand', 'category', 'comments.user'])
            ->where('status', 'approved')
            ->where('complaint_number', $complaintNumber)
            ->whereHas('brand', function($q) use ($brandSlug) {
                $q->where('slug', $brandSlug);
            })
            ->firstOrFail();

        // Increment views
        $complaint->incrementViewCount();

        // Get related complaints (same brand or category)
        $relatedComplaints = Complaint::with(['user', 'brand'])
            ->where('status', 'approved')
            ->where('id', '!=', $complaint->id)
            ->where(function($q) use ($complaint) {
                $q->where('brand_id', $complaint->brand_id)
                  ->orWhere('category_id', $complaint->category_id);
            })
            ->latest()
            ->take(5)
            ->get();

        return view('frontend.complaints.show', compact('complaint', 'relatedComplaints'));
    }

    /**
     * Show the form for creating a new complaint
     */
    public function create()
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Şikayet oluşturmak için giriş yapmalısınız.');
        }

        $brands = Brand::where('status', 'approved')->orderBy('name')->get();
        $categories = Category::where('is_active', true)->orderBy('name')->get();

        return view('frontend.complaints.create', compact('brands', 'categories'));
    }

    /**
     * Store a newly created complaint in storage
     */
    public function store(Request $request)
    {
        // Check if user is authenticated
        if (!auth()->check()) {
            return redirect()->route('login')
                ->with('error', 'Şikayet oluşturmak için giriş yapmalısınız.');
        }

        $validated = $request->validate([
            'brand_id' => 'required|exists:brands,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'content' => 'required|string|min:50',
            'images.*' => 'nullable|image|max:2048',
        ]);

        // Generate unique complaint number
        $validated['complaint_number'] = 'SK-' . strtoupper(uniqid());
        $validated['user_id'] = auth()->id();
        $validated['status'] = 'pending'; // Will be moderated

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

        return redirect()->route('frontend.complaints.show', [
            'brand' => $complaint->brand->slug,
            'number' => $complaint->complaint_number
        ])->with('success', 'Şikayetiniz başarıyla oluşturuldu ve moderasyon bekliyor.');
    }

    /**
     * Mark complaint as helpful
     */
    public function helpful($id)
    {
        if (!auth()->check()) {
            return back()->with('error', 'Bu işlem için giriş yapmalısınız.');
        }

        $complaint = Complaint::findOrFail($id);

        // Check if user already marked as helpful
        $existingVote = $complaint->votes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === 'helpful') {
                // Remove vote
                $existingVote->delete();
                $complaint->decrement('helpful_count');
            } else {
                // Change from not helpful to helpful
                $existingVote->update(['vote_type' => 'helpful']);
                $complaint->decrement('not_helpful_count');
                $complaint->increment('helpful_count');
            }
        } else {
            // Create new vote
            $complaint->votes()->create([
                'user_id' => auth()->id(),
                'vote_type' => 'helpful',
            ]);
            $complaint->increment('helpful_count');
        }

        return back()->with('success', 'Oyunuz kaydedildi.');
    }

    /**
     * Mark complaint as not helpful
     */
    public function notHelpful($id)
    {
        if (!auth()->check()) {
            return back()->with('error', 'Bu işlem için giriş yapmalısınız.');
        }

        $complaint = Complaint::findOrFail($id);

        // Check if user already marked as not helpful
        $existingVote = $complaint->votes()
            ->where('user_id', auth()->id())
            ->first();

        if ($existingVote) {
            if ($existingVote->vote_type === 'not_helpful') {
                // Remove vote
                $existingVote->delete();
                $complaint->decrement('not_helpful_count');
            } else {
                // Change from helpful to not helpful
                $existingVote->update(['vote_type' => 'not_helpful']);
                $complaint->decrement('helpful_count');
                $complaint->increment('not_helpful_count');
            }
        } else {
            // Create new vote
            $complaint->votes()->create([
                'user_id' => auth()->id(),
                'vote_type' => 'not_helpful',
            ]);
            $complaint->increment('not_helpful_count');
        }

        return back()->with('success', 'Oyunuz kaydedildi.');
    }
}
