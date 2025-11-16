<?php

namespace App\Listeners;

use App\Events\ComplaintCreated;
use App\Events\ComplaintUpdated;
use App\Services\BrandService;
use App\Services\StatisticsService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

/**
 * Şikayet olaylarını dinleyen ve istatistikleri güncelleyen listener
 * Listener that updates statistics when complaint events occur
 */
class UpdateComplaintStats implements ShouldQueue
{
    use InteractsWithQueue;

    protected BrandService $brandService;
    protected StatisticsService $statisticsService;

    /**
     * Listener instance oluştur
     * Create the event listener.
     */
    public function __construct(BrandService $brandService, StatisticsService $statisticsService)
    {
        $this->brandService = $brandService;
        $this->statisticsService = $statisticsService;
    }

    /**
     * ComplaintCreated event'ini işle
     * Handle ComplaintCreated event.
     */
    public function handleComplaintCreated(ComplaintCreated $event): void
    {
        try {
            $complaint = $event->complaint;

            // Marka istatistiklerini güncelle
            // Update brand statistics
            $this->brandService->updateStatistics($complaint->brand);

            // Cache'i temizle
            // Clear cache
            $this->clearRelevantCache();

            Log::info('Complaint statistics updated after creation', [
                'complaint_id' => $complaint->id,
                'brand_id' => $complaint->brand_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating complaint statistics after creation', [
                'complaint_id' => $event->complaint->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * ComplaintUpdated event'ini işle
     * Handle ComplaintUpdated event.
     */
    public function handleComplaintUpdated(ComplaintUpdated $event): void
    {
        try {
            $complaint = $event->complaint;
            $oldStatus = $event->oldStatus;

            // Durum değişikliği varsa istatistikleri güncelle
            // Update statistics if status changed
            if ($complaint->status !== $oldStatus) {
                $this->brandService->updateStatistics($complaint->brand);

                // Kullanıcı istatistiklerini güncelle
                // Update user statistics
                if ($complaint->is_resolved) {
                    $complaint->user->increment('resolved_complaint_count');
                }
            }

            // Cache'i temizle
            // Clear cache
            $this->clearRelevantCache();

            Log::info('Complaint statistics updated after update', [
                'complaint_id' => $complaint->id,
                'old_status' => $oldStatus,
                'new_status' => $complaint->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating complaint statistics after update', [
                'complaint_id' => $event->complaint->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * İlgili cache'leri temizle
     * Clear relevant caches
     */
    protected function clearRelevantCache(): void
    {
        // İstatistik cache'lerini temizle
        // Clear statistics caches
        $this->statisticsService->clearCache();

        // Genel cache anahtarlarını temizle
        // Clear general cache keys
        Cache::forget('homepage_stats');
        Cache::forget('dashboard_stats');
    }

    /**
     * Başarısız işlemleri yönet
     * Handle a job failure.
     */
    public function failed($event, \Throwable $exception): void
    {
        $complaintId = property_exists($event, 'complaint') ? $event->complaint->id : 'unknown';

        Log::error('Failed to update complaint statistics', [
            'complaint_id' => $complaintId,
            'event' => get_class($event),
            'error' => $exception->getMessage(),
        ]);
    }
}
