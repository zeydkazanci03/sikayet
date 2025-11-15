<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Brand extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'description',
        'logo',
        'banner',
        'category_id',
        'website',
        'phone',
        'email',
        'address',
        'city',
        'tax_number',
        'trade_registry_number',
        'founded_year',
        'facebook',
        'twitter',
        'instagram',
        'linkedin',
        'subscription_type',
        'subscription_start',
        'subscription_end',
        'auto_renew',
        'status',
        'rejection_reason',
        'complaint_count',
        'resolved_complaint_count',
        'resolution_rate',
        'avg_resolution_time',
        'avg_response_time',
        'customer_satisfaction',
        'index_score',
        'is_featured',
        'is_active',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($brand) {
            if (empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });

        static::updating(function ($brand) {
            if ($brand->isDirty('name') && empty($brand->slug)) {
                $brand->slug = Str::slug($brand->name);
            }
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function complaints()
    {
        return $this->hasMany(Complaint::class);
    }

    public function resolvedComplaints()
    {
        return $this->hasMany(Complaint::class)->where('is_resolved', true);
    }

    public function pendingComplaints()
    {
        return $this->hasMany(Complaint::class)->where('status', 'pending');
    }

    // Methods
    public function updateStatistics()
    {
        $this->complaint_count = $this->complaints()->count();
        $this->resolved_complaint_count = $this->resolvedComplaints()->count();

        if ($this->complaint_count > 0) {
            $this->resolution_rate = ($this->resolved_complaint_count / $this->complaint_count) * 100;
        } else {
            $this->resolution_rate = 0;
        }

        // Calculate average resolution time
        $resolvedComplaints = $this->resolvedComplaints()->whereNotNull('resolved_at')->get();
        if ($resolvedComplaints->count() > 0) {
            $totalResolutionTime = 0;
            foreach ($resolvedComplaints as $complaint) {
                if ($complaint->created_at && $complaint->resolved_at) {
                    $totalResolutionTime += $complaint->created_at->diffInHours($complaint->resolved_at);
                }
            }
            $this->avg_resolution_time = $totalResolutionTime / $resolvedComplaints->count();
        } else {
            $this->avg_resolution_time = 0;
        }

        // Calculate average response time
        $respondedComplaints = $this->complaints()->whereNotNull('brand_first_response_at')->get();
        if ($respondedComplaints->count() > 0) {
            $totalResponseTime = 0;
            foreach ($respondedComplaints as $complaint) {
                if ($complaint->created_at && $complaint->brand_first_response_at) {
                    $totalResponseTime += $complaint->created_at->diffInHours($complaint->brand_first_response_at);
                }
            }
            $this->avg_response_time = $totalResponseTime / $respondedComplaints->count();
        } else {
            $this->avg_response_time = 0;
        }

        // Calculate customer satisfaction
        $ratedComplaints = $this->resolvedComplaints()->whereNotNull('customer_satisfaction_rating')->get();
        if ($ratedComplaints->count() > 0) {
            $this->customer_satisfaction = $ratedComplaints->avg('customer_satisfaction_rating');
        } else {
            $this->customer_satisfaction = 0;
        }

        $this->index_score = $this->calculateIndexScore();
        $this->save();
    }

    public function calculateIndexScore(): float
    {
        // Weighted scoring: resolution_rate 30%, resolution_time 25%, satisfaction 25%, response_time 15%, repeat_complaints 5%
        $score = 0;

        // Resolution rate (30%) - Higher is better
        $resolutionScore = ($this->resolution_rate / 100) * 30;
        $score += $resolutionScore;

        // Resolution time (25%) - Lower is better, normalize to 0-100 scale (assuming 168 hours/7 days max)
        $maxResolutionTime = 168;
        $resolutionTimeScore = 0;
        if ($this->avg_resolution_time > 0) {
            $resolutionTimeScore = (1 - min($this->avg_resolution_time / $maxResolutionTime, 1)) * 25;
        }
        $score += $resolutionTimeScore;

        // Customer satisfaction (25%) - 0-5 scale normalized to 0-100
        $satisfactionScore = ($this->customer_satisfaction / 5) * 25;
        $score += $satisfactionScore;

        // Response time (15%) - Lower is better, normalize to 0-100 scale (assuming 48 hours max)
        $maxResponseTime = 48;
        $responseTimeScore = 0;
        if ($this->avg_response_time > 0) {
            $responseTimeScore = (1 - min($this->avg_response_time / $maxResponseTime, 1)) * 15;
        }
        $score += $responseTimeScore;

        // Repeat complaints (5%) - Lower is better
        // For simplicity, assume if repeat complaint rate is low, score is higher
        // This is a simplified calculation; you may want to implement actual repeat complaint tracking
        $repeatComplaintScore = 5; // Default max score if no repeat complaints logic
        $score += $repeatComplaintScore;

        return round($score, 2);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeBySubscription($query, $type)
    {
        return $query->where('subscription_type', $type);
    }

    // Accessors
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo) {
            return asset('storage/' . $this->logo);
        }
        return null;
    }

    public function getBannerUrlAttribute(): ?string
    {
        if ($this->banner) {
            return asset('storage/' . $this->banner);
        }
        return null;
    }
}
