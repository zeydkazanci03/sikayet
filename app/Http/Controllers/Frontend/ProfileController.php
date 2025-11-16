<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    /**
     * Display the user's profile
     */
    public function show()
    {
        $user = auth()->user();

        // User statistics
        $stats = [
            'total_complaints' => $user->complaints()->count(),
            'pending_complaints' => $user->complaints()->where('status', 'pending')->count(),
            'approved_complaints' => $user->complaints()->where('status', 'approved')->count(),
            'solved_complaints' => $user->complaints()->where('is_resolved', true)->count(),
            'total_comments' => $user->complaintComments()->count(),
        ];

        // Recent activity
        $recentComplaints = $user->complaints()
            ->with(['brand', 'category'])
            ->latest()
            ->take(5)
            ->get();

        return view('frontend.profile.show', compact('user', 'stats', 'recentComplaints'));
    }

    /**
     * Show the form for editing the user's profile
     */
    public function edit()
    {
        $user = auth()->user();

        return view('frontend.profile.edit', compact('user'));
    }

    /**
     * Update the user's profile
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'bio' => 'nullable|string|max:500',
            'avatar' => 'nullable|image|max:2048',
            'password' => 'nullable|string|min:8|confirmed',
        ]);

        // Handle avatar upload
        if ($request->hasFile('avatar')) {
            // Delete old avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $validated['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        // Handle password update
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        return back()->with('success', 'Profiliniz başarıyla güncellendi.');
    }

    /**
     * Display user's complaints
     */
    public function myComplaints(Request $request)
    {
        $user = auth()->user();

        $query = $user->complaints()->with(['brand', 'category']);

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by solved
        if ($request->filled('is_resolved')) {
            $query->where('is_resolved', $request->is_resolved);
        }

        // Sort
        $sort = $request->get('sort', 'latest');
        switch ($sort) {
            case 'oldest':
                $query->oldest();
                break;
            case 'most_viewed':
                $query->orderBy('view_count', 'desc');
                break;
            case 'most_commented':
                $query->withCount('comments')->orderBy('comments_count', 'desc');
                break;
            default:
                $query->latest();
        }

        $complaints = $query->paginate(20);

        // Statistics
        $stats = [
            'total' => $user->complaints()->count(),
            'pending' => $user->complaints()->where('status', 'pending')->count(),
            'approved' => $user->complaints()->where('status', 'approved')->count(),
            'rejected' => $user->complaints()->where('status', 'rejected')->count(),
            'solved' => $user->complaints()->where('is_resolved', true)->count(),
        ];

        return view('frontend.profile.complaints', compact('complaints', 'stats'));
    }

    /**
     * Display user's notifications
     */
    public function notifications()
    {
        $user = auth()->user();

        // Get notifications (assuming you have a notifications system)
        $notifications = $user->notifications()
            ->latest()
            ->paginate(20);

        // Mark all as read
        $user->unreadNotifications->markAsRead();

        return view('frontend.profile.notifications', compact('notifications'));
    }
}
