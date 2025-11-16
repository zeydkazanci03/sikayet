<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Brand;
use App\Models\User;
use App\Models\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 * İstatistik hesaplama servis sınıfı
 * Service class for statistics calculations
 */
class StatisticsService
{
    protected int $cacheTTL = 3600; // 1 saat / 1 hour

    /**
     * Platform genel istatistiklerini al
     * Get platform general statistics
     */
    public function getPlatformStatistics(): array
    {
        return Cache::remember('platform_statistics', $this->cacheTTL, function () {
            return [
                'total_complaints' => Complaint::count(),
                'total_brands' => Brand::count(),
                'total_users' => User::where('user_type', 'customer')->count(),
                'total_categories' => Category::count(),
                'pending_complaints' => Complaint::where('status', 'pending')->count(),
                'resolved_complaints' => Complaint::where('is_resolved', true)->count(),
                'active_brands' => Brand::where('is_active', true)->count(),
                'avg_resolution_time' => $this->getAverageResolutionTime(),
                'avg_response_time' => $this->getAverageResponseTime(),
                'resolution_rate' => $this->getResolutionRate(),
                'customer_satisfaction' => $this->getAverageCustomerSatisfaction(),
            ];
        });
    }

    /**
     * Ortalama çözüm süresini al (saat)
     * Get average resolution time (hours)
     */
    public function getAverageResolutionTime(): float
    {
        $resolvedComplaints = Complaint::whereNotNull('resolved_at')
            ->whereNotNull('created_at')
            ->select('created_at', 'resolved_at')
            ->get();

        if ($resolvedComplaints->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($resolvedComplaints as $complaint) {
            $totalHours += $complaint->created_at->diffInHours($complaint->resolved_at);
        }

        return round($totalHours / $resolvedComplaints->count(), 2);
    }

    /**
     * Ortalama cevap süresini al (saat)
     * Get average response time (hours)
     */
    public function getAverageResponseTime(): float
    {
        $respondedComplaints = Complaint::whereNotNull('brand_first_response_at')
            ->whereNotNull('created_at')
            ->select('created_at', 'brand_first_response_at')
            ->get();

        if ($respondedComplaints->isEmpty()) {
            return 0;
        }

        $totalHours = 0;
        foreach ($respondedComplaints as $complaint) {
            $totalHours += $complaint->created_at->diffInHours($complaint->brand_first_response_at);
        }

        return round($totalHours / $respondedComplaints->count(), 2);
    }

    /**
     * Çözüm oranını al (%)
     * Get resolution rate (%)
     */
    public function getResolutionRate(): float
    {
        $total = Complaint::count();
        if ($total === 0) {
            return 0;
        }

        $resolved = Complaint::where('is_resolved', true)->count();
        return round(($resolved / $total) * 100, 2);
    }

    /**
     * Ortalama müşteri memnuniyetini al (1-5)
     * Get average customer satisfaction (1-5)
     */
    public function getAverageCustomerSatisfaction(): float
    {
        return round(
            Complaint::whereNotNull('customer_satisfaction_rating')
                ->avg('customer_satisfaction_rating') ?? 0,
            2
        );
    }

    /**
     * Kategorilere göre şikayet dağılımını al
     * Get complaint distribution by categories
     */
    public function getComplaintsByCategory(): array
    {
        return Cache::remember('complaints_by_category', $this->cacheTTL, function () {
            return Category::withCount('complaints')
                ->orderBy('complaints_count', 'desc')
                ->get()
                ->map(function ($category) {
                    return [
                        'name' => $category->name,
                        'count' => $category->complaints_count,
                        'percentage' => $this->calculatePercentage($category->complaints_count, Complaint::count()),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Aylık şikayet trendini al
     * Get monthly complaint trend
     */
    public function getMonthlyComplaintTrend(int $months = 12): array
    {
        return Cache::remember("monthly_complaint_trend_{$months}", $this->cacheTTL, function () use ($months) {
            $data = [];

            for ($i = $months - 1; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthStart = $date->copy()->startOfMonth();
                $monthEnd = $date->copy()->endOfMonth();

                $complaints = Complaint::whereBetween('created_at', [$monthStart, $monthEnd])->count();
                $resolved = Complaint::whereBetween('resolved_at', [$monthStart, $monthEnd])->count();

                $data[] = [
                    'month' => $date->format('M Y'),
                    'complaints' => $complaints,
                    'resolved' => $resolved,
                    'resolution_rate' => $complaints > 0 ? round(($resolved / $complaints) * 100, 2) : 0,
                ];
            }

            return $data;
        });
    }

    /**
     * En çok şikayet alan markaları al
     * Get brands with most complaints
     */
    public function getTopComplainedBrands(int $limit = 10): array
    {
        return Cache::remember("top_complained_brands_{$limit}", $this->cacheTTL, function () use ($limit) {
            return Brand::withCount('complaints')
                ->orderBy('complaints_count', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'complaints_count' => $brand->complaints_count,
                        'index_score' => $brand->index_score,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * En iyi performans gösteren markaları al
     * Get best performing brands
     */
    public function getBestPerformingBrands(int $limit = 10): array
    {
        return Cache::remember("best_performing_brands_{$limit}", $this->cacheTTL, function () use ($limit) {
            return Brand::where('complaint_count', '>', 0)
                ->orderBy('index_score', 'desc')
                ->limit($limit)
                ->get()
                ->map(function ($brand) {
                    return [
                        'id' => $brand->id,
                        'name' => $brand->name,
                        'index_score' => $brand->index_score,
                        'resolution_rate' => $brand->resolution_rate,
                        'customer_satisfaction' => $brand->customer_satisfaction,
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Günlük istatistikleri al
     * Get daily statistics
     */
    public function getDailyStatistics(): array
    {
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        return [
            'today' => [
                'new_complaints' => Complaint::where('created_at', '>=', $today)->count(),
                'resolved_complaints' => Complaint::where('resolved_at', '>=', $today)->count(),
                'new_users' => User::where('created_at', '>=', $today)->count(),
            ],
            'yesterday' => [
                'new_complaints' => Complaint::whereBetween('created_at', [$yesterday, $today])->count(),
                'resolved_complaints' => Complaint::whereBetween('resolved_at', [$yesterday, $today])->count(),
                'new_users' => User::whereBetween('created_at', [$yesterday, $today])->count(),
            ],
        ];
    }

    /**
     * Durum bazlı şikayet dağılımını al
     * Get complaint distribution by status
     */
    public function getComplaintsByStatus(): array
    {
        return Cache::remember('complaints_by_status', $this->cacheTTL, function () {
            $total = Complaint::count();

            return DB::table('complaints')
                ->select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->map(function ($item) use ($total) {
                    return [
                        'status' => $item->status,
                        'count' => $item->count,
                        'percentage' => $this->calculatePercentage($item->count, $total),
                    ];
                })
                ->toArray();
        });
    }

    /**
     * Kullanıcı aktivite istatistiklerini al
     * Get user activity statistics
     */
    public function getUserActivityStatistics(): array
    {
        return [
            'active_users' => User::where('is_active', true)->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'users_with_complaints' => User::has('complaints')->count(),
            'avg_complaints_per_user' => round(
                Complaint::count() / max(User::where('user_type', 'customer')->count(), 1),
                2
            ),
        ];
    }

    /**
     * Yüzde hesapla
     * Calculate percentage
     */
    protected function calculatePercentage(int $value, int $total): float
    {
        if ($total === 0) {
            return 0;
        }

        return round(($value / $total) * 100, 2);
    }

    /**
     * Cache'i temizle
     * Clear cache
     */
    public function clearCache(): void
    {
        Cache::forget('platform_statistics');
        Cache::forget('complaints_by_category');
        Cache::forget('complaints_by_status');

        for ($i = 1; $i <= 20; $i++) {
            Cache::forget("monthly_complaint_trend_{$i}");
            Cache::forget("top_complained_brands_{$i}");
            Cache::forget("best_performing_brands_{$i}");
        }
    }
}
