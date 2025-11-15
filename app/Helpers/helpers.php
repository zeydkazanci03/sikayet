<?php

use App\Models\Setting;
use Illuminate\Support\Str;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        $setting = Setting::where('key', $key)->first();
        return $setting ? $setting->value : $default;
    }
}

if (!function_exists('update_setting')) {
    function update_setting($key, $value)
    {
        return Setting::updateOrCreate(
            ['key' => $key],
            ['value' => $value, 'type' => 'string']
        );
    }
}

if (!function_exists('format_number')) {
    function format_number($number, $decimals = 0)
    {
        return number_format($number, $decimals, ',', '.');
    }
}

if (!function_exists('time_ago')) {
    function time_ago($datetime)
    {
        return \Carbon\Carbon::parse($datetime)->diffForHumans();
    }
}

if (!function_exists('truncate_text')) {
    function truncate_text($text, $length = 100)
    {
        return Str::limit($text, $length, '...');
    }
}

if (!function_exists('sanitize_html')) {
    function sanitize_html($html)
    {
        return strip_tags($html);
    }
}

if (!function_exists('log_activity')) {
    function log_activity($action, $description = null, $model = null, $properties = [])
    {
        return \App\Models\ActivityLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model ? $model->id : null,
            'description' => $description,
            'properties' => json_encode($properties),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }
}

if (!function_exists('badge_status')) {
    function badge_status($status)
    {
        return match($status) {
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
}

if (!function_exists('badge_priority')) {
    function badge_priority($priority)
    {
        return match($priority) {
            'low' => '<span class="badge bg-success">Düşük</span>',
            'normal' => '<span class="badge bg-primary">Normal</span>',
            'high' => '<span class="badge bg-warning">Yüksek</span>',
            'urgent' => '<span class="badge bg-danger">Acil</span>',
            default => '<span class="badge bg-light">Bilinmiyor</span>',
        };
    }
}

if (!function_exists('complaint_number_format')) {
    function complaint_number_format($complaintId)
    {
        return 'C' . str_pad($complaintId, 6, '0', STR_PAD_LEFT);
    }
}
