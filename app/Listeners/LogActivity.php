<?php

namespace App\Listeners;

use App\Events\ComplaintCreated;
use App\Events\ComplaintUpdated;
use App\Events\BrandApproved;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Request;

/**
 * Tüm önemli olayları loglayan listener
 * Listener that logs all major events
 */
class LogActivity
{
    /**
     * Listener instance oluştur
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * ComplaintCreated event'ini işle
     * Handle ComplaintCreated event.
     */
    public function handleComplaintCreated(ComplaintCreated $event): void
    {
        try {
            $complaint = $event->complaint;

            ActivityLog::create([
                'user_id' => $complaint->user_id,
                'action' => 'complaint_created',
                'model_type' => 'App\Models\Complaint',
                'model_id' => $complaint->id,
                'description' => "Yeni şikayet oluşturuldu: {$complaint->title}",
                'properties' => json_encode([
                    'complaint_number' => $complaint->complaint_number,
                    'brand_id' => $complaint->brand_id,
                    'brand_name' => $complaint->brand->name,
                    'category_id' => $complaint->category_id,
                    'category_name' => $complaint->category->name,
                ]),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            Log::info('Activity logged: complaint created', [
                'complaint_id' => $complaint->id,
                'user_id' => $complaint->user_id,
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging complaint creation activity', [
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

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'complaint_updated',
                'model_type' => 'App\Models\Complaint',
                'model_id' => $complaint->id,
                'description' => "Şikayet güncellendi: {$complaint->title}",
                'properties' => json_encode([
                    'complaint_number' => $complaint->complaint_number,
                    'old_status' => $oldStatus,
                    'new_status' => $complaint->status,
                    'is_resolved' => $complaint->is_resolved,
                ]),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            Log::info('Activity logged: complaint updated', [
                'complaint_id' => $complaint->id,
                'old_status' => $oldStatus,
                'new_status' => $complaint->status,
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging complaint update activity', [
                'complaint_id' => $event->complaint->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * BrandApproved event'ini işle
     * Handle BrandApproved event.
     */
    public function handleBrandApproved(BrandApproved $event): void
    {
        try {
            $brand = $event->brand;

            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => 'brand_approved',
                'model_type' => 'App\Models\Brand',
                'model_id' => $brand->id,
                'description' => "Marka onaylandı: {$brand->name}",
                'properties' => json_encode([
                    'brand_name' => $brand->name,
                    'brand_slug' => $brand->slug,
                    'category_id' => $brand->category_id,
                ]),
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            Log::info('Activity logged: brand approved', [
                'brand_id' => $brand->id,
                'brand_name' => $brand->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging brand approval activity', [
                'brand_id' => $event->brand->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Genel aktivite logu oluştur
     * Create general activity log
     */
    public function logGeneralActivity(
        string $action,
        string $description,
        ?string $modelType = null,
        ?int $modelId = null,
        ?array $properties = null
    ): void {
        try {
            ActivityLog::create([
                'user_id' => auth()->id(),
                'action' => $action,
                'model_type' => $modelType,
                'model_id' => $modelId,
                'description' => $description,
                'properties' => $properties ? json_encode($properties) : null,
                'ip_address' => Request::ip(),
                'user_agent' => Request::userAgent(),
            ]);

            Log::info('Activity logged: ' . $action, [
                'description' => $description,
            ]);

        } catch (\Exception $e) {
            Log::error('Error logging general activity', [
                'action' => $action,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
