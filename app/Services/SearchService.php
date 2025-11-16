<?php

namespace App\Services;

use App\Models\Complaint;
use App\Models\Brand;
use App\Models\Category;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Arama işlemleri servis sınıfı
 * Service class for search operations
 */
class SearchService
{
    /**
     * Global arama yap
     * Perform global search
     */
    public function globalSearch(string $query, int $limit = 10): array
    {
        return [
            'complaints' => $this->searchComplaints($query, $limit),
            'brands' => $this->searchBrands($query, $limit),
            'categories' => $this->searchCategories($query, $limit),
            'blog_posts' => $this->searchBlogPosts($query, $limit),
        ];
    }

    /**
     * Şikayetlerde ara
     * Search complaints
     */
    public function searchComplaints(string $query, int $limit = 20): Collection
    {
        return Complaint::where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->orWhere('complaint_number', 'LIKE', "%{$query}%");
        })
            ->with(['user', 'brand', 'category'])
            ->published()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Markalarda ara
     * Search brands
     */
    public function searchBrands(string $query, int $limit = 20): Collection
    {
        return Brand::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%");
        })
            ->active()
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Kategorilerde ara
     * Search categories
     */
    public function searchCategories(string $query, int $limit = 20): Collection
    {
        return Category::where(function ($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")
                ->orWhere('description', 'LIKE', "%{$query}%");
        })
            ->active()
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get();
    }

    /**
     * Blog yazılarında ara
     * Search blog posts
     */
    public function searchBlogPosts(string $query, int $limit = 20): Collection
    {
        return BlogPost::where(function ($q) use ($query) {
            $q->where('title', 'LIKE', "%{$query}%")
                ->orWhere('content', 'LIKE', "%{$query}%")
                ->orWhere('excerpt', 'LIKE', "%{$query}%");
        })
            ->where('is_published', true)
            ->orderBy('published_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Gelişmiş şikayet araması
     * Advanced complaint search
     */
    public function advancedComplaintSearch(array $filters): Collection
    {
        $query = Complaint::query()->with(['user', 'brand', 'category']);

        // Metin araması
        // Text search
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('content', 'LIKE', "%{$filters['search']}%")
                    ->orWhere('complaint_number', 'LIKE', "%{$filters['search']}%");
            });
        }

        // Marka filtresi
        // Brand filter
        if (!empty($filters['brand_id'])) {
            $query->where('brand_id', $filters['brand_id']);
        }

        // Kategori filtresi
        // Category filter
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        // Durum filtresi
        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Tarih aralığı
        // Date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        // Öncelik filtresi
        // Priority filter
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Çözüm durumu
        // Resolution status
        if (isset($filters['is_resolved'])) {
            $query->where('is_resolved', $filters['is_resolved']);
        }

        // Sıralama
        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortOrder = $filters['sort_order'] ?? 'desc';
        $query->orderBy($sortBy, $sortOrder);

        // Sayfalama
        // Pagination
        $perPage = $filters['per_page'] ?? 20;

        return $query->paginate($perPage);
    }

    /**
     * Marka bazlı arama
     * Brand-based search
     */
    public function searchByBrand(int $brandId, array $filters = []): Collection
    {
        $query = Complaint::where('brand_id', $brandId)
            ->with(['user', 'category']);

        // Durum filtresi
        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Tarih aralığı
        // Date range
        if (!empty($filters['date_from'])) {
            $query->where('created_at', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $query->where('created_at', '<=', $filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Kategori bazlı arama
     * Category-based search
     */
    public function searchByCategory(int $categoryId, array $filters = []): Collection
    {
        $query = Complaint::where('category_id', $categoryId)
            ->with(['user', 'brand']);

        // Durum filtresi
        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Kullanıcı bazlı arama
     * User-based search
     */
    public function searchByUser(int $userId, array $filters = []): Collection
    {
        $query = Complaint::where('user_id', $userId)
            ->with(['brand', 'category']);

        // Durum filtresi
        // Status filter
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Popüler aramaları al
     * Get popular searches
     */
    public function getPopularSearches(int $limit = 10): array
    {
        // Bu örnekte en çok şikayet alan markaları popüler arama olarak döndürüyoruz
        // In this example, we return most complained brands as popular searches
        return Brand::withCount('complaints')
            ->orderBy('complaints_count', 'desc')
            ->limit($limit)
            ->pluck('name')
            ->toArray();
    }

    /**
     * Benzer şikayetleri bul
     * Find similar complaints
     */
    public function findSimilarComplaints(Complaint $complaint, int $limit = 5): Collection
    {
        return Complaint::where('id', '!=', $complaint->id)
            ->where(function ($q) use ($complaint) {
                $q->where('brand_id', $complaint->brand_id)
                    ->orWhere('category_id', $complaint->category_id);
            })
            ->published()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Otomatik tamamlama önerileri
     * Autocomplete suggestions
     */
    public function autocomplete(string $query, string $type = 'all', int $limit = 10): array
    {
        $results = [];

        if (in_array($type, ['all', 'brands'])) {
            $results['brands'] = Brand::where('name', 'LIKE', "%{$query}%")
                ->active()
                ->limit($limit)
                ->pluck('name')
                ->toArray();
        }

        if (in_array($type, ['all', 'categories'])) {
            $results['categories'] = Category::where('name', 'LIKE', "%{$query}%")
                ->active()
                ->limit($limit)
                ->pluck('name')
                ->toArray();
        }

        if (in_array($type, ['all', 'complaints'])) {
            $results['complaints'] = Complaint::where('title', 'LIKE', "%{$query}%")
                ->published()
                ->limit($limit)
                ->pluck('title')
                ->toArray();
        }

        return $results;
    }

    /**
     * Full-text search (MySQL FULLTEXT kullanarak)
     * Full-text search (using MySQL FULLTEXT)
     */
    public function fullTextSearch(string $query, int $limit = 20): Collection
    {
        return Complaint::whereRaw(
            "MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE)",
            [$query]
        )
            ->with(['user', 'brand', 'category'])
            ->published()
            ->orderByRaw("MATCH(title, content) AGAINST(? IN NATURAL LANGUAGE MODE) DESC", [$query])
            ->limit($limit)
            ->get();
    }
}
