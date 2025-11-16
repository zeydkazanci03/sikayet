<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the admin dashboard with statistics and metrics
     */
    public function index()
    {
        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Today's metrics
        $todayStats = [
            'complaints' => Complaint::whereDate('created_at', $today)->count(),
            'users' => User::whereDate('created_at', $today)->count(),
            'brands' => Brand::whereDate('created_at', $today)->count(),
        ];

        // Week's metrics
        $weekStats = [
            'complaints' => Complaint::where('created_at', '>=', $weekStart)->count(),
            'users' => User::where('created_at', '>=', $weekStart)->count(),
            'brands' => Brand::where('created_at', '>=', $weekStart)->count(),
        ];

        // Month's metrics
        $monthStats = [
            'complaints' => Complaint::where('created_at', '>=', $monthStart)->count(),
            'users' => User::where('created_at', '>=', $monthStart)->count(),
            'brands' => Brand::where('created_at', '>=', $monthStart)->count(),
        ];

        // Total metrics
        $totalStats = [
            'complaints' => Complaint::count(),
            'users' => User::count(),
            'brands' => Brand::count(),
            'categories' => Category::count(),
        ];

        // Pending tasks
        $pendingTasks = [
            'complaints' => Complaint::where('status', 'pending')->count(),
            'brands' => Brand::where('status', 'pending')->count(),
            'reported' => Complaint::where('is_reported', true)->count(),
        ];

        // Recent complaints
        $recentComplaints = Complaint::with(['user', 'brand', 'category'])
            ->latest()
            ->take(10)
            ->get();

        // Chart data - Last 7 days
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('M d'),
                'complaints' => Complaint::whereDate('created_at', $date)->count(),
                'users' => User::whereDate('created_at', $date)->count(),
            ];
        }

        return view('admin.dashboard', compact(
            'todayStats',
            'weekStats',
            'monthStats',
            'totalStats',
            'pendingTasks',
            'recentComplaints',
            'chartData'
        ));
    }
}
