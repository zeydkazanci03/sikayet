<?php

namespace App\Repositories;

use App\Models\Complaint;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Şikayet veri erişim katmanı
 * Complaint data access layer
 */
class ComplaintRepository
{
    protected Complaint $model;

    public function __construct(Complaint $model)
    {
        $this->model = $model;
    }

    /**
     * Tüm şikayetleri al
     * Get all complaints
     */
    public function all(): Collection
    {
        return $this->model->with(['user', 'brand', 'category'])->get();
    }

    /**
     * ID'ye göre şikayet bul
     * Find complaint by ID
     */
    public function find(int $id): ?Complaint
    {
        return $this->model->with(['user', 'brand', 'category', 'attachments', 'comments'])->find($id);
    }

    /**
     * Şikayet numarasına göre bul
     * Find by complaint number
     */
    public function findByComplaintNumber(string $complaintNumber): ?Complaint
    {
        return $this->model->where('complaint_number', $complaintNumber)
            ->with(['user', 'brand', 'category', 'attachments', 'comments'])
            ->first();
    }

    /**
     * Yeni şikayet oluştur
     * Create new complaint
     */
    public function create(array $data): Complaint
    {
        return $this->model->create($data);
    }

    /**
     * Şikayeti güncelle
     * Update complaint
     */
    public function update(Complaint $complaint, array $data): bool
    {
        return $complaint->update($data);
    }

    /**
     * Şikayeti sil
     * Delete complaint
     */
    public function delete(Complaint $complaint): bool
    {
        return $complaint->delete();
    }

    /**
     * Sayfalı şikayetleri al
     * Get paginated complaints
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['user', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Yayınlanan şikayetleri al
     * Get published complaints
     */
    public function getPublished(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->published()
            ->with(['user', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Onay bekleyen şikayetleri al
     * Get pending complaints
     */
    public function getPending(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->pendingModeration()
            ->with(['user', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Kullanıcıya ait şikayetleri al
     * Get complaints by user
     */
    public function getByUser(int $userId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('user_id', $userId)
            ->with(['brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Markaya ait şikayetleri al
     * Get complaints by brand
     */
    public function getByBrand(int $brandId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('brand_id', $brandId)
            ->published()
            ->with(['user', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Kategoriye ait şikayetleri al
     * Get complaints by category
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('category_id', $categoryId)
            ->published()
            ->with(['user', 'brand'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Duruma göre şikayetleri al
     * Get complaints by status
     */
    public function getByStatus(string $status, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('status', $status)
            ->with(['user', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Çözülen şikayetleri al
     * Get resolved complaints
     */
    public function getResolved(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->resolved()
            ->with(['user', 'brand', 'category'])
            ->orderBy('resolved_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Öne çıkan şikayetleri al
     * Get featured complaints
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->model->where('is_featured', true)
            ->published()
            ->with(['user', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Popüler şikayetleri al (en çok görüntülenen)
     * Get popular complaints (most viewed)
     */
    public function getPopular(int $limit = 10): Collection
    {
        return $this->model->published()
            ->with(['user', 'brand', 'category'])
            ->orderBy('view_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Son şikayetleri al
     * Get recent complaints
     */
    public function getRecent(int $limit = 10): Collection
    {
        return $this->model->published()
            ->with(['user', 'brand', 'category'])
            ->orderBy('created_at', 'desc')
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
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->orWhere('complaint_number', 'LIKE', "%{$query}%");
        })
            ->with(['user', 'brand', 'category'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    /**
     * Toplam şikayet sayısı
     * Count total complaints
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Onay bekleyen şikayet sayısı
     * Count pending complaints
     */
    public function countPending(): int
    {
        return $this->model->pendingModeration()->count();
    }

    /**
     * Çözülen şikayet sayısı
     * Count resolved complaints
     */
    public function countResolved(): int
    {
        return $this->model->resolved()->count();
    }

    /**
     * Bugünkü şikayet sayısı
     * Count today's complaints
     */
    public function countToday(): int
    {
        return $this->model->whereDate('created_at', today())->count();
    }

    /**
     * Bu ayki şikayet sayısı
     * Count this month's complaints
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
            'pending' => $this->countPending(),
            'resolved' => $this->countResolved(),
            'today' => $this->countToday(),
            'this_month' => $this->countThisMonth(),
            'resolution_rate' => $this->calculateResolutionRate(),
        ];
    }

    /**
     * Çözüm oranını hesapla
     * Calculate resolution rate
     */
    protected function calculateResolutionRate(): float
    {
        $total = $this->count();
        if ($total === 0) {
            return 0;
        }

        $resolved = $this->countResolved();
        return round(($resolved / $total) * 100, 2);
    }
}
