<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'user_type',
        'phone',
        'phone_verified',
        'avatar',
        'birth_date',
        'gender',
        'city',
        'address',
        'is_active',
        'is_banned',
        'complaint_count',
        'resolved_complaint_count',
        'trust_score',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'phone_verified' => 'boolean',
            'is_active' => 'boolean',
            'is_banned' => 'boolean',
            'trust_score' => 'decimal:2',
            'last_login_at' => 'datetime',
        ];
    }

    // Relationships
    public function complaints()
    {
        return $this->hasMany(Complaint::class, 'user_id');
    }

    public function moderatedComplaints()
    {
        return $this->hasMany(Complaint::class, 'moderator_id');
    }

    public function complaintComments()
    {
        return $this->hasMany(ComplaintComment::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    // Methods
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    public function isBrand(): bool
    {
        return $this->user_type === 'brand';
    }

    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    public function getUnreadNotificationsCountAttribute(): int
    {
        return $this->notifications()->unread()->count();
    }

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar) {
            return asset('storage/' . $this->avatar);
        }
        return null;
    }
}
