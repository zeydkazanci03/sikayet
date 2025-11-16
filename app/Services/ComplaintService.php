<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\User;
use App\Models\Brand;
use App\Events\ComplaintCreated;
use App\Events\ComplaintUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Şikayet işlemleri servis sınıfı
 * Service class for complaint operations
 */
class ComplaintService
{
    protected FileUploadService $fileUploadService;
    protected NotificationService $notificationService;
    protected ModerationService $moderationService;

    public function __construct(
        FileUploadService $fileUploadService,
        NotificationService $notificationService,
        ModerationService $moderationService
    ) {
        $this->fileUploadService = $fileUploadService;
        $this->notificationService = $notificationService;
        $this->moderationService = $moderationService;
    }

    /**
     * Yeni şikayet oluştur
     * Create a new complaint
     */
    public function create(array $data, User $user): Complaint
    {
        try {
            DB::beginTransaction();

            // Şikayet oluştur
            // Create complaint
            $complaint = Complaint::create([
                'user_id' => $user->id,
                'brand_id' => $data['brand_id'],
                'category_id' => $data['category_id'],
                'title' => $data['title'],
                'content' => $data['content'],
                'resolution_expectation' => $data['resolution_expectation'] ?? null,
                'status' => 'pending',
                'priority' => 'normal',
            ]);

            // Ekleri yükle
            // Upload attachments
            if (!empty($data['attachments'])) {
                foreach ($data['attachments'] as $file) {
                    $path = $this->fileUploadService->upload($file, 'complaints');
                    $complaint->attachments()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Moderasyon kontrolü yap
            // Apply moderation
            $this->moderationService->checkComplaint($complaint);

            // Bildirimleri gönder
            // Send notifications
            $this->notificationService->notifyBrandAboutNewComplaint($complaint);
            $this->notificationService->notifyAdminsAboutNewComplaint($complaint);

            // Event'i tetikle
            // Trigger event
            event(new ComplaintCreated($complaint));

            DB::commit();

            Log::info('Complaint created successfully', ['complaint_id' => $complaint->id]);

            return $complaint->fresh(['user', 'brand', 'category', 'attachments']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating complaint', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
            ]);
            throw $e;
        }
    }

    /**
     * Şikayeti güncelle
     * Update complaint
     */
    public function update(Complaint $complaint, array $data): Complaint
    {
        try {
            DB::beginTransaction();

            $oldStatus = $complaint->status;

            // Şikayeti güncelle
            // Update complaint
            $complaint->update($data);

            // Durum değişikliğinde bildirim gönder
            // Send notification on status change
            if (isset($data['status']) && $oldStatus !== $data['status']) {
                $this->notificationService->notifyUserAboutComplaintStatusChange($complaint, $oldStatus);

                // Çözüldü olarak işaretlendiyse
                // If marked as resolved
                if ($data['status'] === 'resolved') {
                    $complaint->markAsResolved();
                }
            }

            // Event'i tetikle
            // Trigger event
            event(new ComplaintUpdated($complaint, $oldStatus));

            DB::commit();

            Log::info('Complaint updated successfully', ['complaint_id' => $complaint->id]);

            return $complaint->fresh(['user', 'brand', 'category']);

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating complaint', [
                'error' => $e->getMessage(),
                'complaint_id' => $complaint->id,
            ]);
            throw $e;
        }
    }

    /**
     * Şikayeti onayla
     * Approve complaint
     */
    public function approve(Complaint $complaint, User $moderator, ?string $notes = null): Complaint
    {
        $complaint->update([
            'status' => 'approved',
            'moderator_id' => $moderator->id,
            'moderated_at' => now(),
            'admin_notes' => $notes,
            'is_published' => true,
            'published_at' => now(),
        ]);

        // Kullanıcıya bildirim gönder
        // Notify user
        $this->notificationService->notifyUserAboutComplaintApproval($complaint);

        // Markayı bilgilendir
        // Notify brand
        $this->notificationService->notifyBrandAboutNewComplaint($complaint);

        Log::info('Complaint approved', [
            'complaint_id' => $complaint->id,
            'moderator_id' => $moderator->id,
        ]);

        return $complaint;
    }

    /**
     * Şikayeti reddet
     * Reject complaint
     */
    public function reject(Complaint $complaint, User $moderator, string $reason): Complaint
    {
        $complaint->update([
            'status' => 'rejected',
            'moderator_id' => $moderator->id,
            'moderated_at' => now(),
            'rejection_reason' => $reason,
            'is_published' => false,
        ]);

        // Kullanıcıya bildirim gönder
        // Notify user
        $this->notificationService->notifyUserAboutComplaintRejection($complaint);

        Log::info('Complaint rejected', [
            'complaint_id' => $complaint->id,
            'moderator_id' => $moderator->id,
            'reason' => $reason,
        ]);

        return $complaint;
    }

    /**
     * Şikayeti spam olarak işaretle
     * Mark complaint as spam
     */
    public function markAsSpam(Complaint $complaint, User $moderator): Complaint
    {
        $complaint->update([
            'status' => 'spam',
            'moderator_id' => $moderator->id,
            'moderated_at' => now(),
            'is_published' => false,
        ]);

        // Kullanıcının spam skorunu artır
        // Increase user's spam score
        $complaint->user->increment('spam_score', 10);

        Log::warning('Complaint marked as spam', [
            'complaint_id' => $complaint->id,
            'user_id' => $complaint->user_id,
        ]);

        return $complaint;
    }

    /**
     * Şikayete yorum ekle
     * Add comment to complaint
     */
    public function addComment(Complaint $complaint, User $user, string $content, ?int $parentId = null): void
    {
        $comment = $complaint->comments()->create([
            'user_id' => $user->id,
            'parent_id' => $parentId,
            'content' => $content,
        ]);

        // Yorum sayısını artır
        // Increment comment count
        $complaint->increment('comment_count');

        // Marka ilk kez cevap verdiyse
        // If brand responds for the first time
        if ($user->isBrand() && !$complaint->brand_first_response_at) {
            $complaint->update([
                'brand_first_response_at' => now(),
            ]);
        }

        // Bildirimleri gönder
        // Send notifications
        $this->notificationService->notifyAboutNewComment($comment);

        Log::info('Comment added to complaint', [
            'complaint_id' => $complaint->id,
            'comment_id' => $comment->id,
        ]);
    }

    /**
     * Müşteri memnuniyeti puanı ekle
     * Add customer satisfaction rating
     */
    public function addSatisfactionRating(Complaint $complaint, int $rating, ?string $comment = null): Complaint
    {
        $complaint->update([
            'customer_satisfaction_rating' => $rating,
            'customer_satisfaction_comment' => $comment,
        ]);

        // Marka istatistiklerini güncelle
        // Update brand statistics
        $complaint->brand->updateStatistics();

        Log::info('Satisfaction rating added', [
            'complaint_id' => $complaint->id,
            'rating' => $rating,
        ]);

        return $complaint;
    }

    /**
     * Şikayet görüntüleme sayısını artır
     * Increment complaint view count
     */
    public function incrementViewCount(Complaint $complaint): void
    {
        $complaint->incrementViewCount();
    }

    /**
     * Şikayeti sil
     * Delete complaint
     */
    public function delete(Complaint $complaint): bool
    {
        try {
            DB::beginTransaction();

            // Ekleri sil
            // Delete attachments
            foreach ($complaint->attachments as $attachment) {
                $this->fileUploadService->delete($attachment->file_path);
                $attachment->delete();
            }

            // Yorumları sil
            // Delete comments
            $complaint->comments()->delete();

            // Şikayeti sil
            // Delete complaint
            $deleted = $complaint->delete();

            DB::commit();

            Log::info('Complaint deleted', ['complaint_id' => $complaint->id]);

            return $deleted;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error deleting complaint', [
                'error' => $e->getMessage(),
                'complaint_id' => $complaint->id,
            ]);
            throw $e;
        }
    }
}
