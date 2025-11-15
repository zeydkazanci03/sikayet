<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Carbon\Carbon;

class Complaint extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'complaint_number', 'user_id', 'brand_id', 'category_id', 'title', 'content',
        'resolution_expectation', 'status', 'rejection_reason', 'admin_notes', 'priority',
        'moderator_id', 'moderated_at', 'brand_notified_at', 'brand_first_response_at',
        'resolved_at', 'view_count', 'comment_count', 'helpful_count', 'not_helpful_count',
        'spam_score', 'sentiment', 'sentiment_score', 'is_resolved',
        'customer_satisfaction_rating', 'customer_satisfaction_comment', 'meta_title',
        'meta_description', 'is_published', 'is_featured', 'published_at'
    ];

    protected function casts(): array
    {
        return [
            'moderated_at' => 'datetime',
            'brand_notified_at' => 'datetime',
            'brand_first_response_at' => 'datetime',
            'resolved_at' => 'datetime',
            'is_resolved' => 'boolean',
            'is_published' => 'boolean',
            'is_featured' => 'boolean',
            'published_at' => 'datetime',
            'sentiment_score' => 'decimal:2',
        ];
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($complaint) {
            if (empty($complaint->complaint_number)) {
                $complaint->complaint_number = self::generateComplaintNumber();
            }
        });

        static::created(function ($complaint) {
            $complaint->user->increment('complaint_count');
            $complaint->brand->increment('complaint_count');
            $complaint->category->increment('complaint_count');
        });

        static::deleting(function ($complaint) {
            $complaint->user->decrement('complaint_count');
            $complaint->brand->decrement('complaint_count');
            $complaint->category->decrement('complaint_count');
        });
    }

    public static function generateComplaintNumber(): string
    {
        $date = Carbon::now()->format('Ymd');
        $lastComplaint = self::whereDate('created_at', Carbon::today())
            ->latest('id')
            ->first();

        $sequence = $lastComplaint
            ? (int)substr($lastComplaint->complaint_number, -4) + 1
            : 1;

        return 'C' . $date . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function brand()
    {
        return $this->belongsTo(Brand::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function attachments()
    {
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function comments()
    {
        return $this->hasMany(ComplaintComment::class)->orderBy('created_at', 'asc');
    }

    // Methods
    public function markAsResolved(): void
    {
        $this->update([
            'is_resolved' => true,
            'status' => 'resolved',
            'resolved_at' => Carbon::now(),
        ]);
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }

    // Accessors
    public function getResolutionTimeAttribute(): ?int
    {
        if ($this->resolved_at) {
            return (int)$this->created_at->diffInHours($this->resolved_at);
        }
        return null;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match($this->status) {
            'pending' => '<span class="badge bg-warning">Beklemede</span>',
            'approved' => '<span class="badge bg-info">Onaylandı</span>',
            'rejected' => '<span class="badge bg-danger">Reddedildi</span>',
            'spam' => '<span class="badge bg-secondary">Spam</span>',
            'in_progress' => '<span class="badge bg-primary">İşlemde</span>',
            'resolved' => '<span class="badge bg-success">Çözüldü</span>',
            'closed' => '<span class="badge bg-dark">Kapalı</span>',
            default => '<span class="badge bg-light">Bilinmiyor</span>',
        };
    }

    public function getPriorityBadgeAttribute(): string
    {
        return match($this->priority) {
            'low' => '<span class="badge bg-success">Düşük</span>',
            'normal' => '<span class="badge bg-primary">Normal</span>',
            'high' => '<span class="badge bg-warning">Yüksek</span>',
            'urgent' => '<span class="badge bg-danger">Acil</span>',
            default => '<span class="badge bg-light">Bilinmiyor</span>',
        };
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopePendingModeration($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeActive($query)
    {
        return $query->where('status', '!=', 'closed')->where('status', '!=', 'spam');
    }

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeSearch($query, $term)
    {
        return $query->whereFullText(['title', 'content'], $term);
    }
}
