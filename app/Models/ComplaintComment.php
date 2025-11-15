<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintComment extends Model
{
    protected $fillable = [
        'complaint_id', 'user_id', 'content', 'user_type',
        'is_official_response', 'is_solution', 'is_approved'
    ];

    protected function casts(): array
    {
        return [
            'is_official_response' => 'boolean',
            'is_solution' => 'boolean',
            'is_approved' => 'boolean',
        ];
    }

    public function complaint()
    {
        return $this->belongsTo(Complaint::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
