<?php

namespace App\Repositories;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Hash;

/**
 * Kullanıcı veri erişim katmanı
 * User data access layer
 */
class UserRepository
{
    protected User $model;

    public function __construct(User $model)
    {
        $this->model = $model;
    }

    /**
     * Tüm kullanıcıları al
     * Get all users
     */
    public function all(): Collection
    {
        return $this->model->get();
    }

    /**
     * ID'ye göre kullanıcı bul
     * Find user by ID
     */
    public function find(int $id): ?User
    {
        return $this->model->with(['complaints', 'notifications'])->find($id);
    }

    /**
     * E-posta adresine göre kullanıcı bul
     * Find user by email
     */
    public function findByEmail(string $email): ?User
    {
        return $this->model->where('email', $email)->first();
    }

    /**
     * Yeni kullanıcı oluştur
     * Create new user
     */
    public function create(array $data): User
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->model->create($data);
    }

    /**
     * Kullanıcıyı güncelle
     * Update user
     */
    public function update(User $user, array $data): bool
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $user->update($data);
    }

    /**
     * Kullanıcıyı sil
     * Delete user
     */
    public function delete(User $user): bool
    {
        return $user->delete();
    }

    /**
     * Sayfalı kullanıcıları al
     * Get paginated users
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->orderBy('created_at', 'desc')->paginate($perPage);
    }

    /**
     * Kullanıcı tipine göre al
     * Get users by type
     */
    public function getByType(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_type', $type)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Admin kullanıcıları al
     * Get admin users
     */
    public function getAdmins(): Collection
    {
        return $this->model->where('user_type', 'admin')->get();
    }

    /**
     * Müşteri kullanıcıları al
     * Get customer users
     */
    public function getCustomers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_type', 'customer')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Marka kullanıcıları al
     * Get brand users
     */
    public function getBrandUsers(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_type', 'brand')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Aktif kullanıcıları al
     * Get active users
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_active', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Yasaklı kullanıcıları al
     * Get banned users
     */
    public function getBanned(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('is_banned', true)
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * En aktif kullanıcıları al (en çok şikayet oluşturan)
     * Get most active users (most complaints created)
     */
    public function getMostActive(int $limit = 10): Collection
    {
        return $this->model->where('user_type', 'customer')
            ->orderBy('complaint_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Yüksek güvenilirlik skoruna sahip kullanıcıları al
     * Get users with high trust score
     */
    public function getHighTrustScore(int $limit = 10): Collection
    {
        return $this->model->where('user_type', 'customer')
            ->orderBy('trust_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Ara
     * Search
     */
    public function search(string $query, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%");
        })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Kullanıcıyı yasakla
     * Ban user
     */
    public function ban(User $user, string $reason = null): bool
    {
        return $user->update([
            'is_banned' => true,
            'is_active' => false,
        ]);
    }

    /**
     * Kullanıcının yasağını kaldır
     * Unban user
     */
    public function unban(User $user): bool
    {
        return $user->update([
            'is_banned' => false,
            'is_active' => true,
        ]);
    }

    /**
     * Kullanıcıyı aktif et
     * Activate user
     */
    public function activate(User $user): bool
    {
        return $user->update(['is_active' => true]);
    }

    /**
     * Kullanıcıyı deaktif et
     * Deactivate user
     */
    public function deactivate(User $user): bool
    {
        return $user->update(['is_active' => false]);
    }

    /**
     * Şifre değiştir
     * Change password
     */
    public function changePassword(User $user, string $newPassword): bool
    {
        return $user->update(['password' => Hash::make($newPassword)]);
    }

    /**
     * Toplam kullanıcı sayısı
     * Count total users
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Aktif kullanıcı sayısı
     * Count active users
     */
    public function countActive(): int
    {
        return $this->model->where('is_active', true)->count();
    }

    /**
     * Yasaklı kullanıcı sayısı
     * Count banned users
     */
    public function countBanned(): int
    {
        return $this->model->where('is_banned', true)->count();
    }

    /**
     * Bugün kayıt olan kullanıcı sayısı
     * Count users registered today
     */
    public function countToday(): int
    {
        return $this->model->whereDate('created_at', today())->count();
    }

    /**
     * Bu ay kayıt olan kullanıcı sayısı
     * Count users registered this month
     */
    public function countThisMonth(): int
    {
        return $this->model->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
    }

    /**
     * İstatistikleri al
     * Get statistics
     */
    public function getStatistics(): array
    {
        return [
            'total' => $this->count(),
            'active' => $this->countActive(),
            'banned' => $this->countBanned(),
            'today' => $this->countToday(),
            'this_month' => $this->countThisMonth(),
            'by_type' => $this->countByType(),
        ];
    }

    /**
     * Tipe göre kullanıcı sayısı
     * Count users by type
     */
    protected function countByType(): array
    {
        return [
            'customer' => $this->model->where('user_type', 'customer')->count(),
            'brand' => $this->model->where('user_type', 'brand')->count(),
            'admin' => $this->model->where('user_type', 'admin')->count(),
        ];
    }
}
