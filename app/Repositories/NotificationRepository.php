<?php

namespace App\Repositories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Bildirim veri erişim katmanı
 * Notification data access layer
 */
class NotificationRepository
{
    protected Notification $model;

    public function __construct(Notification $model)
    {
        $this->model = $model;
    }

    /**
     * Tüm bildirimleri al
     * Get all notifications
     */
    public function all(): Collection
    {
        return $this->model->with(['user'])->get();
    }

    /**
     * ID'ye göre bildirim bul
     * Find notification by ID
     */
    public function find(int $id): ?Notification
    {
        return $this->model->with(['user'])->find($id);
    }

    /**
     * Yeni bildirim oluştur
     * Create new notification
     */
    public function create(array $data): Notification
    {
        return $this->model->create($data);
    }

    /**
     * Bildirimi güncelle
     * Update notification
     */
    public function update(Notification $notification, array $data): bool
    {
        return $notification->update($data);
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
     * Sayfalı bildirimleri al
     * Get paginated notifications
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Kullanıcıya ait bildirimleri al
     * Get notifications by user
     */
    public function getByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Kullanıcıya ait okunmamış bildirimleri al
     * Get unread notifications by user
     */
    public function getUnreadByUser(User $user): Collection
    {
        return $this->model->where('user_id', $user->id)
            ->unread()
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Kullanıcıya ait okunmuş bildirimleri al
     * Get read notifications by user
     */
    public function getReadByUser(User $user, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_id', $user->id)
            ->where('is_read', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bildirim tipine göre al
     * Get notifications by type
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('type', $type)
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Bildirimi okundu olarak işaretle
     * Mark notification as read
     */
    public function markAsRead(Notification $notification): bool
    {
        return $notification->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Bildirimi okunmadı olarak işaretle
     * Mark notification as unread
     */
    public function markAsUnread(Notification $notification): bool
    {
        return $notification->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Kullanıcının tüm bildirimlerini okundu olarak işaretle
     * Mark all user notifications as read
     */
    public function markAllAsReadByUser(User $user): int
    {
        return $this->model->where('user_id', $user->id)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }

    /**
     * Kullanıcının okunmamış bildirim sayısı
     * Count unread notifications for user
     */
    public function countUnreadByUser(User $user): int
    {
        return $this->model->where('user_id', $user->id)
            ->unread()
            ->count();
    }

    /**
     * Kullanıcının belirli tipteki okunmamış bildirim sayısı
     * Count unread notifications by type for user
     */
    public function countUnreadByTypeAndUser(User $user, string $type): int
    {
        return $this->model->where('user_id', $user->id)
            ->where('type', $type)
            ->unread()
            ->count();
    }

    /**
     * Eski bildirimleri sil
     * Delete old notifications
     */
    public function deleteOld(int $days = 30): int
    {
        return $this->model->where('created_at', '<', now()->subDays($days))
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Kullanıcının tüm bildirimlerini sil
     * Delete all user notifications
     */
    public function deleteAllByUser(User $user): int
    {
        return $this->model->where('user_id', $user->id)->delete();
    }

    /**
     * Kullanıcının okunmuş bildirimlerini sil
     * Delete read notifications for user
     */
    public function deleteReadByUser(User $user): int
    {
        return $this->model->where('user_id', $user->id)
            ->where('is_read', true)
            ->delete();
    }

    /**
     * Toplu bildirim oluştur
     * Create bulk notifications
     */
    public function createBulk(array $notifications): bool
    {
        return $this->model->insert($notifications);
    }

    /**
     * Kullanıcıya bildirim gönder
     * Send notification to user
     */
    public function sendToUser(User $user, string $type, string $title, string $message, ?array $data = null): Notification
    {
        return $this->create([
            'user_id' => $user->id,
            'type' => $type,
            'title' => $title,
            'message' => $message,
            'data' => $data ? json_encode($data) : null,
            'is_read' => false,
        ]);
    }

    /**
     * Birden fazla kullanıcıya bildirim gönder
     * Send notification to multiple users
     */
    public function sendToMultipleUsers(array $userIds, string $type, string $title, string $message, ?array $data = null): int
    {
        $notifications = [];
        $now = now();

        foreach ($userIds as $userId) {
            $notifications[] = [
                'user_id' => $userId,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'data' => $data ? json_encode($data) : null,
                'is_read' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        $this->model->insert($notifications);

        return count($notifications);
    }

    /**
     * Tüm admin kullanıcılara bildirim gönder
     * Send notification to all admins
     */
    public function sendToAllAdmins(string $type, string $title, string $message, ?array $data = null): int
    {
        $adminIds = User::where('user_type', 'admin')
            ->where('is_active', true)
            ->pluck('id')
            ->toArray();

        return $this->sendToMultipleUsers($adminIds, $type, $title, $message, $data);
    }

    /**
     * Toplam bildirim sayısı
     * Count total notifications
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Okunmamış bildirim sayısı
     * Count unread notifications
     */
    public function countUnread(): int
    {
        return $this->model->unread()->count();
    }

    /**
     * Bugünkü bildirim sayısı
     * Count today's notifications
     */
    public function countToday(): int
    {
        return $this->model->whereDate('created_at', today())->count();
    }

    /**
     * İstatistikleri al
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->count(),
            'unread' => $this->countUnread(),
            'today' => $this->countToday(),
            'by_type' => $this->countByType(),
        ];
    }

    /**
     * Tipe göre bildirim sayısı
     * Count notifications by type
     */
    protected function countByType(): array
    {
        return $this->model->select('type')
            ->selectRaw('count(*) as count')
            ->groupBy('type')
            ->pluck('count', 'type')
            ->toArray();
    }
}
