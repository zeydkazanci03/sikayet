<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\ComplaintComment;
use App\Models\Brand;
use App\Models\User;
use App\Models\Notification;
use App\Events\NotificationSent;
use App\Mail\ComplaintCreated as ComplaintCreatedMail;
use App\Mail\ComplaintResponded;
use App\Mail\BrandApproved as BrandApprovedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

/**
 * Bildirim işlemleri servis sınıfı
 * Service class for notification operations
 */
class NotificationService
{
    protected EmailService $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    /**
     * Kullanıcıya bildirim oluştur
     * Create notification for user
     */
    public function create(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        $notification = Notification::create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'is_read' => false,
        ]);

        event(new NotificationSent($notification));

        Log::info('Notification created', [
            'notification_id' => $notification->id,
            'user_id' => $user->id,
            'type' => $type,
        ]);

        return $notification;
    }

    /**
     * Marka hakkında yeni şikayet bildirimi gönder
     * Notify brand about new complaint
     */
    public function notifyBrandAboutNewComplaint(Complaint $complaint): void
    {
        // Marka kullanıcılarını bul
        // Find brand users
        $brandUsers = User::where('user_type', 'brand')
            ->where('is_active', true)
            ->get();

        foreach ($brandUsers as $user) {
            $this->create(
                $user,
                'new_complaint',
                'Yeni Şikayet',
                "Markanız hakkında yeni bir şikayet oluşturuldu: {$complaint->title}",
                ['complaint_id' => $complaint->id]
            );
        }

        // E-posta gönder
        // Send email
        if ($complaint->brand->email) {
            $this->emailService->send(
                $complaint->brand->email,
                new ComplaintCreatedMail($complaint)
            );
        }
    }

    /**
     * Admin kullanıcılara yeni şikayet bildirimi gönder
     * Notify admins about new complaint
     */
    public function notifyAdminsAboutNewComplaint(Complaint $complaint): void
    {
        $admins = User::where('user_type', 'admin')
            ->where('is_active', true)
            ->get();

        foreach ($admins as $admin) {
            $this->create(
                $admin,
                'new_complaint_admin',
                'Onay Bekleyen Şikayet',
                "Yeni bir şikayet onay bekliyor: {$complaint->title}",
                ['complaint_id' => $complaint->id]
            );
        }
    }

    /**
     * Kullanıcıya şikayet durumu değişikliği bildirimi gönder
     * Notify user about complaint status change
     */
    public function notifyUserAboutComplaintStatusChange(Complaint $complaint, string $oldStatus): void
    {
        $statusMessages = [
            'approved' => 'Şikayetiniz onaylandı ve yayınlandı.',
            'rejected' => 'Şikayetiniz reddedildi.',
            'in_progress' => 'Şikayetiniz inceleniyor.',
            'resolved' => 'Şikayetiniz çözüldü olarak işaretlendi.',
            'closed' => 'Şikayetiniz kapatıldı.',
        ];

        $message = $statusMessages[$complaint->status] ?? 'Şikayet durumunuz güncellendi.';

        $this->create(
            $complaint->user,
            'complaint_status_change',
            'Şikayet Durumu Güncellendi',
            $message,
            [
                'complaint_id' => $complaint->id,
                'old_status' => $oldStatus,
                'new_status' => $complaint->status,
            ]
        );
    }

    /**
     * Kullanıcıya şikayet onayı bildirimi gönder
     * Notify user about complaint approval
     */
    public function notifyUserAboutComplaintApproval(Complaint $complaint): void
    {
        $this->create(
            $complaint->user,
            'complaint_approved',
            'Şikayetiniz Onaylandı',
            "'{$complaint->title}' başlıklı şikayetiniz onaylandı ve yayınlandı.",
            ['complaint_id' => $complaint->id]
        );
    }

    /**
     * Kullanıcıya şikayet reddi bildirimi gönder
     * Notify user about complaint rejection
     */
    public function notifyUserAboutComplaintRejection(Complaint $complaint): void
    {
        $this->create(
            $complaint->user,
            'complaint_rejected',
            'Şikayetiniz Reddedildi',
            "'{$complaint->title}' başlıklı şikayetiniz reddedildi. Sebep: {$complaint->rejection_reason}",
            ['complaint_id' => $complaint->id]
        );
    }

    /**
     * Yeni yorum bildirimi gönder
     * Notify about new comment
     */
    public function notifyAboutNewComment(ComplaintComment $comment): void
    {
        $complaint = $comment->complaint;

        // Şikayet sahibine bildir (kendi yorumu değilse)
        // Notify complaint owner (if not their own comment)
        if ($comment->user_id !== $complaint->user_id) {
            $this->create(
                $complaint->user,
                'new_comment',
                'Yeni Yorum',
                "{$comment->user->name} şikayetinize yorum yaptı.",
                [
                    'complaint_id' => $complaint->id,
                    'comment_id' => $comment->id,
                ]
            );

            // E-posta gönder
            // Send email
            if ($comment->user->isBrand()) {
                $this->emailService->send(
                    $complaint->user->email,
                    new ComplaintResponded($complaint, $comment)
                );
            }
        }

        // Üst yorumun sahibine bildir (varsa)
        // Notify parent comment owner (if exists)
        if ($comment->parent_id) {
            $parentComment = ComplaintComment::find($comment->parent_id);
            if ($parentComment && $parentComment->user_id !== $comment->user_id) {
                $this->create(
                    $parentComment->user,
                    'comment_reply',
                    'Yorumunuza Cevap',
                    "{$comment->user->name} yorumunuza cevap verdi.",
                    [
                        'complaint_id' => $complaint->id,
                        'comment_id' => $comment->id,
                    ]
                );
            }
        }
    }

    /**
     * Marka onayı bildirimi gönder
     * Notify about brand approval
     */
    public function notifyBrandAboutApproval(Brand $brand): void
    {
        // Marka kullanıcılarını bul
        // Find brand users
        $brandUsers = User::where('user_type', 'brand')
            ->where('is_active', true)
            ->get();

        foreach ($brandUsers as $user) {
            $this->create(
                $user,
                'brand_approved',
                'Marka Onaylandı',
                "'{$brand->name}' markası onaylandı ve aktif edildi.",
                ['brand_id' => $brand->id]
            );
        }

        // E-posta gönder
        // Send email
        if ($brand->email) {
            $this->emailService->send(
                $brand->email,
                new BrandApprovedMail($brand)
            );
        }
    }

    /**
     * Marka reddi bildirimi gönder
     * Notify about brand rejection
     */
    public function notifyBrandAboutRejection(Brand $brand, string $reason): void
    {
        // Marka kullanıcılarını bul
        // Find brand users
        $brandUsers = User::where('user_type', 'brand')
            ->where('is_active', true)
            ->get();

        foreach ($brandUsers as $user) {
            $this->create(
                $user,
                'brand_rejected',
                'Marka Reddedildi',
                "'{$brand->name}' markası reddedildi. Sebep: {$reason}",
                ['brand_id' => $brand->id]
            );
        }
    }

    /**
     * Bildirimi okundu olarak işaretle
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): void
    {
        $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Tüm bildirimleri okundu olarak işaretle
     * Mark all notifications as read
     */
    public function markAllAsRead(User $user): void
    {
        Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Bildirimi sil
     * Delete notification
     */
    public function delete(Notification $notification): bool
    {
        return $notification->delete();
    }

    /**
     * Eski bildirimleri temizle
     * Clean old notifications
     */
    public function cleanOldNotifications(int $days = 30): int
    {
        return Notification::where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }
}
