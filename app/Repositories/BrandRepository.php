<?php

namespace App\Repositories;

use App\Models\Brand;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Marka veri erişim katmanı
 * Brand data access layer
 */
class BrandRepository
{
    protected Brand $model;

    public function __construct(Brand $model)
    {
        $this->model = $model;
    }

    /**
     * Tüm markaları al
     * Get all brands
     */
    public function all(): Collection
    {
        return $this->model->with(['category'])->get();
    }

    /**
     * ID'ye göre marka bul
     * Find brand by ID
     */
    public function find(int $id): ?Brand
    {
        return $this->model->with(['category', 'complaints'])->find($id);
    }

    /**
     * Slug'a göre marka bul
     * Find brand by slug
     */
    public function findBySlug(string $slug): ?Brand
    {
        return $this->model->where('slug', $slug)
            ->with(['category', 'complaints'])
            ->first();
    }

    /**
     * Yeni marka oluştur
     * Create new brand
     */
    public function create(array $data): Brand
    {
        return $this->model->create($data);
    }

    /**
     * Markayı güncelle
     * Update brand
     */
    public function update(Brand $brand, array $data): bool
    {
        return $brand->update($data);
    }

    /**
     * Markayı sil
     * Delete brand
     */
    public function delete(Brand $brand): bool
    {
        return $brand->delete();
    }

    /**
     * Sayfalı markaları al
     * Get paginated brands
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->with(['category'])
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Aktif markaları al
     * Get active brands
     */
    public function getActive(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->active()
            ->with(['category'])
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Öne çıkan markaları al
     * Get featured brands
     */
    public function getFeatured(int $limit = 10): Collection
    {
        return $this->model->featured()
            ->active()
            ->with(['category'])
            ->orderBy('index_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Kategoriye göre markaları al
     * Get brands by category
     */
    public function getByCategory(int $categoryId, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->where('category_id', $categoryId)
            ->active()
            ->with(['category'])
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Abonelik tipine göre markaları al
     * Get brands by subscription type
     */
    public function getBySubscription(string $type, int $perPage = 15): LengthAwarePaginator
    {
        return $this->model->bySubscription($type)
            ->with(['category'])
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * En iyi performans gösteren markaları al
     * Get top performing brands
     */
    public function getTopPerforming(int $limit = 10): Collection
    {
        return $this->model->active()
            ->where('complaint_count', '>', 0)
            ->orderBy('index_score', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * En çok şikayet alan markaları al
     * Get most complained brands
     */
    public function getMostComplained(int $limit = 10): Collection
    {
        return $this->model->active()
            ->orderBy('complaint_count', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * En az şikayet alan markaları al
     * Get least complained brands
     */
    public function getLeastComplained(int $limit = 10): Collection
    {
        return $this->model->active()
            ->where('complaint_count', '>', 0)
            ->orderBy('complaint_count', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Yüksek çözüm oranına sahip markaları al
     * Get brands with high resolution rate
     */
    public function getHighResolutionRate(int $limit = 10): Collection
    {
        return $this->model->active()
            ->where('complaint_count', '>', 0)
            ->orderBy('resolution_rate', 'desc')
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
                ->orWhere('description', 'LIKE', "%{$query}%");
        })
            ->with(['category'])
            ->active()
            ->orderBy('name', 'asc')
            ->paginate($perPage);
    }

    /**
     * Toplam marka sayısı
     * Count total brands
     */
    public function count(): int
    {
        return $this->model->count();
    }

    /**
     * Aktif marka sayısı
     * Count active brands
     */
    public function countActive(): int
    {
        return $this->model->active()->count();
    }

    /**
     * Onay bekleyen marka sayısı
     * Count pending brands
     */
    public function countPending(): int
    {
        return $this->model->where('status', 'pending')->count();
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
            'pending' => $this->countPending(),
            'avg_index_score' => $this->getAverageIndexScore(),
            'avg_resolution_rate' => $this->getAverageResolutionRate(),
        ];
    }

    /**
     * Ortalama endeks skorunu al
     * Get average index score
     */
    protected function getAverageIndexScore(): float
    {
        return round($this->model->active()->avg('index_score') ?? 0, 2);
    }

    /**
     * Ortalama çözüm oranını al
     * Get average resolution rate
     */
    protected function getAverageResolutionRate(): float
    {
        return round($this->model->active()->avg('resolution_rate') ?? 0, 2);
    }

    /**
     * Marka istatistiklerini güncelle
     * Update brand statistics
     */
    public function updateStatistics(Brand $brand): void
    {
        $brand->updateStatistics();
    }
}
