<?php

namespace App\Listeners;

use App\Events\BrandApproved;
use App\Services\NotificationService;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Marka onaylandığında bildirim gönderen listener
 * Listener that sends notifications when a brand is approved
 */
class NotifyOnBrandApproved implements ShouldQueue
{
    use InteractsWithQueue;

    protected NotificationService $notificationService;
    protected EmailService $emailService;

    /**
     * Listener instance oluştur
     * Create the event listener.
     */
    public function __construct(NotificationService $notificationService, EmailService $emailService)
    {
        $this->notificationService = $notificationService;
        $this->emailService = $emailService;
    }

    /**
     * Event'i işle
     * Handle the event.
     */
    public function handle(BrandApproved $event): void
    {
        $brand = $event->brand;

        try {
            // Marka kullanıcılarına bildir
            // Notify brand users
            $this->notificationService->notifyBrandAboutApproval($brand);

            Log::info('Brand approval notifications sent', [
                'brand_id' => $brand->id,
                'brand_name' => $brand->name,
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending brand approval notifications', [
                'brand_id' => $brand->id,
                'error' => $e->getMessage(),
            ]);

            // Hata durumunda listener'ı yeniden kuyruğa ekle
            // Re-queue the listener in case of error
            $this->release(60); // 60 saniye sonra tekrar dene
        }
    }

    /**
     * Başarısız işlemleri yönet
     * Handle a job failure.
     */
    public function failed(BrandApproved $event, \Throwable $exception): void
    {
        Log::error('Failed to send brand approval notifications', [
            'brand_id' => $event->brand->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
