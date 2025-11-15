<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModerationRule extends Model
{
    protected $fillable = ['name', 'type', 'pattern', 'action', 'scope', 'is_active', 'priority'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'priority' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
