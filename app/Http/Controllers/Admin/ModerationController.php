<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Comment;
use App\Models\Brand;
use App\Models\User;
use Illuminate\Http\Request;

class ModerationController extends Controller
{
    /**
     * Display the moderation dashboard
     */
    public function index()
    {
        // Pending complaints
        $pendingComplaints = Complaint::with(['user', 'brand'])
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        // Reported complaints
        $reportedComplaints = Complaint::with(['user', 'brand'])
            ->where('is_reported', true)
            ->latest()
            ->take(10)
            ->get();

        // Pending brands
        $pendingBrands = Brand::with('user')
            ->where('status', 'pending')
            ->latest()
            ->take(10)
            ->get();

        // Reported comments
        $reportedComments = Comment::with(['user', 'complaint'])
            ->where('is_reported', true)
            ->latest()
            ->take(10)
            ->get();

        // Moderation stats
        $stats = [
            'pending_complaints' => Complaint::where('status', 'pending')->count(),
            'reported_complaints' => Complaint::where('is_reported', true)->count(),
            'pending_brands' => Brand::where('status', 'pending')->count(),
            'reported_comments' => Comment::where('is_reported', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
        ];

        return view('admin.moderation.index', compact(
            'pendingComplaints',
            'reportedComplaints',
            'pendingBrands',
            'reportedComments',
            'stats'
        ));
    }

    /**
     * Show the form for creating a new moderation action
     */
    public function create()
    {
        return view('admin.moderation.create');
    }

    /**
     * Store a newly created moderation action in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:complaint,comment,brand,user',
            'action' => 'required|in:approve,reject,spam,ban,delete',
            'item_id' => 'required|integer',
            'reason' => 'nullable|string',
        ]);

        // Process moderation action based on type
        switch ($validated['type']) {
            case 'complaint':
                $this->moderateComplaint($validated);
                break;
            case 'comment':
                $this->moderateComment($validated);
                break;
            case 'brand':
                $this->moderateBrand($validated);
                break;
            case 'user':
                $this->moderateUser($validated);
                break;
        }

        return redirect()->route('admin.moderation.index')
            ->with('success', 'Moderasyon işlemi başarıyla tamamlandı.');
    }

    /**
     * Display the specified moderation item
     */
    public function show($id)
    {
        // This method can be used to show detailed moderation history
        return view('admin.moderation.show');
    }

    /**
     * Show the form for editing the specified moderation action
     */
    public function edit($id)
    {
        return view('admin.moderation.edit');
    }

    /**
     * Update the specified moderation action in storage
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('admin.moderation.index')
            ->with('success', 'Moderasyon işlemi güncellendi.');
    }

    /**
     * Remove the specified moderation action from storage
     */
    public function destroy($id)
    {
        return redirect()->route('admin.moderation.index')
            ->with('success', 'Moderasyon işlemi silindi.');
    }

    /**
     * Moderate a complaint
     */
    private function moderateComplaint($data)
    {
        $complaint = Complaint::findOrFail($data['item_id']);

        switch ($data['action']) {
            case 'approve':
                $complaint->update([
                    'status' => 'approved',
                    'moderated_by' => auth()->id(),
                    'moderated_at' => now(),
                ]);
                break;
            case 'reject':
                $complaint->update([
                    'status' => 'rejected',
                    'moderated_by' => auth()->id(),
                    'moderated_at' => now(),
                ]);
                break;
            case 'spam':
                $complaint->update([
                    'status' => 'spam',
                    'moderated_by' => auth()->id(),
                    'moderated_at' => now(),
                ]);
                break;
            case 'delete':
                $complaint->delete();
                break;
        }
    }

    /**
     * Moderate a comment
     */
    private function moderateComment($data)
    {
        $comment = Comment::findOrFail($data['item_id']);

        switch ($data['action']) {
            case 'approve':
                $comment->update(['is_reported' => false]);
                break;
            case 'delete':
                $comment->delete();
                break;
        }
    }

    /**
     * Moderate a brand
     */
    private function moderateBrand($data)
    {
        $brand = Brand::findOrFail($data['item_id']);

        switch ($data['action']) {
            case 'approve':
                $brand->update(['status' => 'approved']);
                break;
            case 'reject':
                $brand->update(['status' => 'rejected']);
                break;
            case 'delete':
                $brand->delete();
                break;
        }
    }

    /**
     * Moderate a user
     */
    private function moderateUser($data)
    {
        $user = User::findOrFail($data['item_id']);

        switch ($data['action']) {
            case 'ban':
                $user->update(['is_banned' => true]);
                break;
            case 'delete':
                $user->delete();
                break;
        }
    }
}
