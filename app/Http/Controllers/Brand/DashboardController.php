<?php

namespace App\Http\Controllers\Brand;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Complaint;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends SafeController
{
    /**
     * Display the brand dashboard with statistics
     */
    public function index()
    {
        $brand = auth()->user()->brand;

        if (!$brand) {
            return redirect()->route('brand.profile.edit')
                ->with('error', 'Lütfen önce marka profilinizi tamamlayın.');
        }

        $today = Carbon::today();
        $weekStart = Carbon::now()->startOfWeek();
        $monthStart = Carbon::now()->startOfMonth();

        // Today's stats
        $todayStats = [
            'complaints' => $brand->complaints()->whereDate('created_at', $today)->count(),
            'responses' => $brand->complaints()->whereDate('brand_first_response_at', $today)->count(),
            'solved' => $brand->complaints()->where('is_resolved', true)->whereDate('updated_at', $today)->count(),
        ];

        // Week's stats
        $weekStats = [
            'complaints' => $brand->complaints()->where('created_at', '>=', $weekStart)->count(),
            'responses' => $brand->complaints()->where('brand_first_response_at', '>=', $weekStart)->count(),
            'solved' => $brand->complaints()->where('is_resolved', true)->where('updated_at', '>=', $weekStart)->count(),
        ];

        // Month's stats
        $monthStats = [
            'complaints' => $brand->complaints()->where('created_at', '>=', $monthStart)->count(),
            'responses' => $brand->complaints()->where('brand_first_response_at', '>=', $monthStart)->count(),
            'solved' => $brand->complaints()->where('is_resolved', true)->where('updated_at', '>=', $monthStart)->count(),
        ];

        // Total stats
        $totalStats = [
            'complaints' => $brand->complaints()->count(),
            'pending' => $brand->complaints()->where('status', 'pending')->count(),
            'approved' => $brand->complaints()->where('status', 'approved')->count(),
            'solved' => $brand->complaints()->where('is_resolved', true)->count(),
            'response_rate' => $this->calculateResponseRate($brand),
            'resolution_rate' => $this->calculateResolutionRate($brand),
        ];

        // Recent complaints
        $recentComplaints = $brand->complaints()
            ->with(['user', 'category'])
            ->latest()
            ->take(10)
            ->get();

        // Pending complaints (needs response)
        $pendingComplaints = $brand->complaints()
            ->with(['user', 'category'])
            ->where('status', 'approved')
            ->whereNull('brand_first_response_at')
            ->latest()
            ->take(5)
            ->get();

        // Chart data - Last 30 days
        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $chartData[] = [
                'date' => $date->format('M d'),
                'complaints' => $brand->complaints()->whereDate('created_at', $date)->count(),
                'responses' => $brand->complaints()->whereDate('brand_first_response_at', $date)->count(),
            ];
        }

        // Category breakdown
        $categoryBreakdown = $brand->complaints()
            ->select('category_id', \DB::raw('count(*) as count'))
            ->with('category')
            ->groupBy('category_id')
            ->get();

        return view('brand.dashboard', compact(
            'brand',
            'todayStats',
            'weekStats',
            'monthStats',
            'totalStats',
            'recentComplaints',
            'pendingComplaints',
            'chartData',
            'categoryBreakdown'
        ));
    }

    /**
     * Calculate response rate
     */
    private function calculateResponseRate($brand)
    {
        $total = $brand->complaints()->where('status', 'approved')->count();

        if ($total === 0) {
            return 0;
        }

        $responded = $brand->complaints()
            ->where('status', 'approved')
            ->whereNotNull('brand_first_response_at')
            ->count();

        return round(($responded / $total) * 100, 2);
    }

    /**
     * Calculate resolution rate
     */
    private function calculateResolutionRate($brand)
    {
        $total = $brand->complaints()->where('status', 'approved')->count();

        if ($total === 0) {
            return 0;
        }

        $solved = $brand->complaints()
            ->where('status', 'approved')
            ->where('is_resolved', true)
            ->count();

        return round(($solved / $total) * 100, 2);
    }
}
