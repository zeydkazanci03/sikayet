<?php

namespace App\Services;

use App\Models\Brand;
use App\Models\User;
use App\Events\BrandApproved;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Marka işlemleri servis sınıfı
 * Service class for brand operations
 */
class BrandService
{
    protected FileUploadService $fileUploadService;
    protected NotificationService $notificationService;

    public function __construct(
        FileUploadService $fileUploadService,
        NotificationService $notificationService
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
    }

    /**
     * Yeni marka oluştur
     * Create a new brand
     */
    public function create(array $data): Brand
    {
        try {
            DB::beginTransaction();

            // Logo yükle
            // Upload logo
            if (isset($data['logo'])) {
                $data['logo'] = $this->fileUploadService->upload($data['logo'], 'brands/logos');
            }

            // Banner yükle
            // Upload banner
            if (isset($data['banner'])) {
                $data['banner'] = $this->fileUploadService->upload($data['banner'], 'brands/banners');
            }

            // Marka oluştur
            // Create brand
            $brand = Brand::create($data);

            DB::commit();

            Log::info('Brand created successfully', ['brand_id' => $brand->id]);

            return $brand;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating brand', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Markayı güncelle
     * Update brand
     */
    public function update(Brand $brand, array $data): Brand
    {
        try {
            DB::beginTransaction();

            // Logo güncelle
            // Update logo
            if (isset($data['logo'])) {
                if ($brand->logo) {
                    $this->fileUploadService->delete($brand->logo);
                }
                $data['logo'] = $this->fileUploadService->upload($data['logo'], 'brands/logos');
            }

            // Banner güncelle
            // Update banner
            if (isset($data['banner'])) {
                if ($brand->banner) {
                    $this->fileUploadService->delete($brand->banner);
                }
                $data['banner'] = $this->fileUploadService->upload($data['banner'], 'brands/banners');
            }

            // Marka güncelle
            // Update brand
            $brand->update($data);

            DB::commit();

            Log::info('Brand updated successfully', ['brand_id' => $brand->id]);

            return $brand;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating brand', [
                'error' => $e->getMessage(),
                'brand_id' => $brand->id,
            ]);
            throw $e;
        }
    }

    /**
     * Markayı onayla
     * Approve brand
     */
    public function approve(Brand $brand, User $admin): Brand
    {
        $brand->update([
            'status' => 'approved',
            'is_active' => true,
        ]);

        // Bildirimleri gönder
        // Send notifications
        $this->notificationService->notifyBrandAboutApproval($brand);

        // Event'i tetikle
        // Trigger event
        event(new BrandApproved($brand));

        Log::info('Brand approved', [
            'brand_id' => $brand->id,
            'admin_id' => $admin->id,
        ]);

        return $brand;
    }

    /**
     * Markayı reddet
     * Reject brand
     */
    public function reject(Brand $brand, User $admin, string $reason): Brand
    {
        $brand->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
            'is_active' => false,
        ]);

        // Bildirimleri gönder
        // Send notifications
        $this->notificationService->notifyBrandAboutRejection($brand, $reason);

        Log::info('Brand rejected', [
            'brand_id' => $brand->id,
            'admin_id' => $admin->id,
            'reason' => $reason,
        ]);

        return $brand;
    }

    /**
     * Marka istatistiklerini güncelle
     * Update brand statistics
     */
    public function updateStatistics(Brand $brand): Brand
    {
        $brand->updateStatistics();

        Log::info('Brand statistics updated', [
            'brand_id' => $brand->id,
            'index_score' => $brand->index_score,
        ]);

        return $brand;
    }

    /**
     * Marka endeks skorunu hesapla
     * Calculate brand index score
     */
    public function calculateIndexScore(Brand $brand): float
    {
        // İstatistikleri güncelle
        // Update statistics first
        $this->updateStatistics($brand);

        // Endeks skorunu hesapla
        // Calculate index score
        $score = $brand->calculateIndexScore();

        Log::info('Brand index score calculated', [
            'brand_id' => $brand->id,
            'score' => $score,
        ]);

        return $score;
    }

    /**
     * Marka performans raporunu al
     * Get brand performance report
     */
    public function getPerformanceReport(Brand $brand): array
    {
        $complaints = $brand->complaints()->with(['category'])->get();
        $resolvedComplaints = $brand->resolvedComplaints()->get();

        return [
            'total_complaints' => $complaints->count(),
            'resolved_complaints' => $resolvedComplaints->count(),
            'pending_complaints' => $brand->pendingComplaints()->count(),
            'resolution_rate' => $brand->resolution_rate,
            'avg_resolution_time' => $brand->avg_resolution_time,
            'avg_response_time' => $brand->avg_response_time,
            'customer_satisfaction' => $brand->customer_satisfaction,
            'index_score' => $brand->index_score,
            'complaints_by_category' => $complaints->groupBy('category_id')->map(function ($items) {
                return [
                    'count' => $items->count(),
                    'category' => $items->first()->category->name ?? 'Bilinmiyor',
                ];
            }),
            'monthly_trend' => $this->getMonthlyTrend($brand),
        ];
    }

    /**
     * Aylık trend verilerini al
     * Get monthly trend data
     */
    protected function getMonthlyTrend(Brand $brand): array
    {
        $months = [];
        for ($i = 11; $i >= 0; $i--) {
            $date = now()->subMonths($i);
            $monthStart = $date->copy()->startOfMonth();
            $monthEnd = $date->copy()->endOfMonth();

            $monthComplaints = $brand->complaints()
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->count();

            $monthResolved = $brand->resolvedComplaints()
                ->whereBetween('resolved_at', [$monthStart, $monthEnd])
                ->count();

            $months[] = [
                'month' => $date->format('M Y'),
                'complaints' => $monthComplaints,
                'resolved' => $monthResolved,
            ];
        }

        return $months;
    }

    /**
     * Markayı sil
     * Delete brand
     */
    public function delete(Brand $brand): bool
    {
        try {
            DB::beginTransaction();

            // Logo sil
            // Delete logo
            if ($brand->logo) {
                $this->fileUploadService->delete($brand->logo);
            }

            // Banner sil
            // Delete banner
            if ($brand->banner) {
                $this->fileUploadService->delete($brand->banner);
            }

            // Markayı sil
            // Delete brand
            $deleted = $brand->delete();

            DB::commit();

            Log::info('Brand deleted', ['brand_id' => $brand->id]);

            return $deleted;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting brand', [
                'error' => $e->getMessage(),
                'brand_id' => $brand->id,
            ]);
            throw $e;
        }
    }

    /**
     * En iyi markaları al
     * Get top performing brands
     */
    public function getTopBrands(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Brand::active()
            ->where('complaint_count', '>', 0)
            ->orderBy('index_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * En kötü markaları al
     * Get worst performing brands
     */
    public function getWorstBrands(int $limit = 10): \Illuminate\Database\Eloquent\Collection
    {
        return Brand::active()
            ->where('complaint_count', '>', 0)
            ->orderBy('index_score', 'asc')
            ->limit($limit)
            ->get();
    }
}
