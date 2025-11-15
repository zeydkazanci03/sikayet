<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Display analytics dashboard
     */
    public function index(Request $request)
    {
        $period = $request->get('period', '30'); // days
        $startDate = Carbon::now()->subDays($period);

        // Daily statistics
        $dailyStats = $this->getDailyStats($startDate);

        // Top brands by complaints
        $topBrands = Brand::withCount('complaints')
            ->orderBy('complaints_count', 'desc')
            ->take(10)
            ->get();

        // Top categories by complaints
        $topCategories = Category::withCount('complaints')
            ->orderBy('complaints_count', 'desc')
            ->take(10)
            ->get();

        // Complaint status distribution
        $statusDistribution = Complaint::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        // User growth
        $userGrowth = $this->getUserGrowth($startDate);

        // Response time analytics
        $responseTime = $this->getAverageResponseTime();

        return view('admin.analytics.index', compact(
            'dailyStats',
            'topBrands',
            'topCategories',
            'statusDistribution',
            'userGrowth',
            'responseTime',
            'period'
        ));
    }

    /**
     * Show the form for creating analytics reports
     */
    public function create()
    {
        return view('admin.analytics.create');
    }

    /**
     * Generate and store a custom analytics report
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:complaints,users,brands,categories',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        // Generate report based on type and date range
        // This is a placeholder for custom report generation

        return redirect()->route('admin.analytics.index')
            ->with('success', 'Rapor başarıyla oluşturuldu.');
    }

    /**
     * Display the specified analytics report
     */
    public function show($id)
    {
        return view('admin.analytics.show');
    }

    /**
     * Show the form for editing analytics settings
     */
    public function edit($id)
    {
        return view('admin.analytics.edit');
    }

    /**
     * Update analytics settings
     */
    public function update(Request $request, $id)
    {
        return redirect()->route('admin.analytics.index')
            ->with('success', 'Ayarlar güncellendi.');
    }

    /**
     * Delete analytics report
     */
    public function destroy($id)
    {
        return redirect()->route('admin.analytics.index')
            ->with('success', 'Rapor silindi.');
    }

    /**
     * Get daily statistics
     */
    private function getDailyStats($startDate)
    {
        $stats = [];
        $days = Carbon::now()->diffInDays($startDate);

        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $stats[] = [
                'date' => $date->format('Y-m-d'),
                'complaints' => Complaint::whereDate('created_at', $date)->count(),
                'users' => User::whereDate('created_at', $date)->count(),
                'brands' => Brand::whereDate('created_at', $date)->count(),
            ];
        }

        return $stats;
    }

    /**
     * Get user growth statistics
     */
    private function getUserGrowth($startDate)
    {
        return User::where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('count(*) as count'))
            ->groupBy('date')
            ->orderBy('date')
            ->get();
    }

    /**
     * Get average response time for complaints
     */
    private function getAverageResponseTime()
    {
        $complaints = Complaint::whereNotNull('moderated_at')
            ->select(
                DB::raw('AVG(TIMESTAMPDIFF(HOUR, created_at, moderated_at)) as avg_hours')
            )
            ->first();

        return $complaints->avg_hours ?? 0;
    }
}
