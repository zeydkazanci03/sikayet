<?php

namespace App\Listeners;

use App\Events\ComplaintCreated;
use App\Services\NotificationService;
use App\Services\EmailService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Şikayet oluşturulduğunda bildirim gönderen listener
 * Listener that sends notifications when a complaint is created
 */
class NotifyOnComplaintCreated implements ShouldQueue
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
    public function handle(ComplaintCreated $event): void
    {
        $complaint = $event->complaint;

        try {
            // Marka kullanıcılarına bildir
            // Notify brand users
            $this->notificationService->notifyBrandAboutNewComplaint($complaint);

            // Admin kullanıcılara bildir
            // Notify admin users
            $this->notificationService->notifyAdminsAboutNewComplaint($complaint);

            Log::info('Notifications sent for new complaint', [
                'complaint_id' => $complaint->id,
                'complaint_number' => $complaint->complaint_number,
            ]);

        } catch (\Exception $e) {
            Log::error('Error sending notifications for new complaint', [
                'complaint_id' => $complaint->id,
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
    public function failed(ComplaintCreated $event, \Throwable $exception): void
    {
        Log::error('Failed to send notifications for complaint', [
            'complaint_id' => $event->complaint->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
