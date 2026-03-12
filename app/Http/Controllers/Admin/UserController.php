<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\SafeController;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends SafeController
{
    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $query = User::query();

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('is_banned', $request->status === 'banned');
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created user in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:admin,moderator,user',
            'is_verified' => 'boolean',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $validated['email_verified_at'] = $request->is_verified ? now() : null;

        $user = User::create($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla oluşturuldu.');
    }

    /**
     * Display the specified user
     */
    public function show(User $user)
    {
        $user->load(['complaints', 'comments']);

        $stats = [
            'total_complaints' => $user->complaints()->count(),
            'pending_complaints' => $user->complaints()->where('status', 'pending')->count(),
            'approved_complaints' => $user->complaints()->where('status', 'approved')->count(),
            'rejected_complaints' => $user->complaints()->where('status', 'rejected')->count(),
            'total_comments' => $user->comments()->count(),
        ];

        return view('admin.users.show', compact('user', 'stats'));
    }

    /**
     * Show the form for editing the specified user
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified user in storage
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'role' => 'required|in:admin,moderator,user',
            'password' => 'nullable|string|min:8|confirmed',
            'is_verified' => 'boolean',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $validated['email_verified_at'] = $request->is_verified ? ($user->email_verified_at ?? now()) : null;

        $user->update($validated);

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla güncellendi.');
    }

    /**
     * Remove the specified user from storage
     */
    public function destroy(User $user)
    {
        // Prevent self-deletion
        if ($user->id === auth()->id()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Kendi hesabınızı silemezsiniz.');
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Kullanıcı başarıyla silindi.');
    }

    /**
     * Ban a user
     */
    public function ban($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kendi hesabınızı yasaklayamazsınız.');
        }

        $user->update(['is_banned' => true]);

        return back()->with('success', 'Kullanıcı yasaklandı.');
    }

    /**
     * Unban a user
     */
    public function unban($id)
    {
        $user = User::findOrFail($id);
        $user->update(['is_banned' => false]);

        return back()->with('success', 'Kullanıcı yasağı kaldırıldı.');
    }

    /**
     * Perform bulk actions on users
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => 'required|in:ban,unban,delete,verify',
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
        ]);

        $userIds = $request->user_ids;

        // Prevent action on self
        if (in_array(auth()->id(), $userIds)) {
            return back()->with('error', 'Kendi hesabınız üzerinde toplu işlem yapamazsınız.');
        }

        switch ($request->action) {
            case 'ban':
                User::whereIn('id', $userIds)->update(['is_banned' => true]);
                $message = 'Seçili kullanıcılar yasaklandı.';
                break;
            case 'unban':
                User::whereIn('id', $userIds)->update(['is_banned' => false]);
                $message = 'Seçili kullanıcıların yasağı kaldırıldı.';
                break;
            case 'delete':
                User::whereIn('id', $userIds)->delete();
                $message = 'Seçili kullanıcılar silindi.';
                break;
            case 'verify':
                User::whereIn('id', $userIds)->update(['email_verified_at' => now()]);
                $message = 'Seçili kullanıcılar doğrulandı.';
                break;
        }

        return back()->with('success', $message);
    }
}
