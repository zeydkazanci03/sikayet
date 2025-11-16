<?php

namespace App\Traits;

use Carbon\Carbon;

/**
 * Zaman damgası formatlama için trait
 * Trait for timestamp formatting
 */
trait HasTimestamps
{
    /**
     * Oluşturulma tarihini formatla
     * Format created at date
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        return $this->created_at ? $this->created_at->format('d.m.Y H:i') : '-';
    }

    /**
     * Güncellenme tarihini formatla
     * Format updated at date
     */
    public function getFormattedUpdatedAtAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->format('d.m.Y H:i') : '-';
    }

    /**
     * Silinme tarihini formatla
     * Format deleted at date
     */
    public function getFormattedDeletedAtAttribute(): string
    {
        if (!property_exists($this, 'deleted_at')) {
            return '-';
        }

        return $this->deleted_at ? $this->deleted_at->format('d.m.Y H:i') : '-';
    }

    /**
     * İnsan dostu oluşturulma tarihi
     * Human readable created at
     */
    public function getCreatedAtHumanAttribute(): string
    {
        return $this->created_at ? $this->created_at->diffForHumans() : '-';
    }

    /**
     * İnsan dostu güncellenme tarihi
     * Human readable updated at
     */
    public function getUpdatedAtHumanAttribute(): string
    {
        return $this->updated_at ? $this->updated_at->diffForHumans() : '-';
    }

    /**
     * İnsan dostu silinme tarihi
     * Human readable deleted at
     */
    public function getDeletedAtHumanAttribute(): string
    {
        if (!property_exists($this, 'deleted_at')) {
            return '-';
        }

        return $this->deleted_at ? $this->deleted_at->diffForHumans() : '-';
    }

    /**
     * Oluşturulma tarihini Türkçe formatla
     * Format created at in Turkish
     */
    public function getCreatedAtTurkishAttribute(): string
    {
        if (!$this->created_at) {
            return '-';
        }

        Carbon::setLocale('tr');
        return $this->created_at->translatedFormat('d F Y, H:i');
    }

    /**
     * Güncellenme tarihini Türkçe formatla
     * Format updated at in Turkish
     */
    public function getUpdatedAtTurkishAttribute(): string
    {
        if (!$this->updated_at) {
            return '-';
        }

        Carbon::setLocale('tr');
        return $this->updated_at->translatedFormat('d F Y, H:i');
    }

    /**
     * Tarih aralığını hesapla
     * Calculate date range
     */
    public function getAgeInDaysAttribute(): int
    {
        return $this->created_at ? $this->created_at->diffInDays(now()) : 0;
    }

    /**
     * Yaş saat cinsinden
     * Age in hours
     */
    public function getAgeInHoursAttribute(): int
    {
        return $this->created_at ? $this->created_at->diffInHours(now()) : 0;
    }

    /**
     * Yaş dakika cinsinden
     * Age in minutes
     */
    public function getAgeInMinutesAttribute(): int
    {
        return $this->created_at ? $this->created_at->diffInMinutes(now()) : 0;
    }

    /**
     * Bugün oluşturuldu mu?
     * Was it created today?
     */
    public function isCreatedToday(): bool
    {
        return $this->created_at ? $this->created_at->isToday() : false;
    }

    /**
     * Dün oluşturuldu mu?
     * Was it created yesterday?
     */
    public function isCreatedYesterday(): bool
    {
        return $this->created_at ? $this->created_at->isYesterday() : false;
    }

    /**
     * Bu hafta oluşturuldu mu?
     * Was it created this week?
     */
    public function isCreatedThisWeek(): bool
    {
        return $this->created_at ? $this->created_at->isCurrentWeek() : false;
    }

    /**
     * Bu ay oluşturuldu mu?
     * Was it created this month?
     */
    public function isCreatedThisMonth(): bool
    {
        return $this->created_at ? $this->created_at->isCurrentMonth() : false;
    }

    /**
     * Bu yıl oluşturuldu mu?
     * Was it created this year?
     */
    public function isCreatedThisYear(): bool
    {
        return $this->created_at ? $this->created_at->isCurrentYear() : false;
    }

    /**
     * Bugün güncellendi mi?
     * Was it updated today?
     */
    public function isUpdatedToday(): bool
    {
        return $this->updated_at ? $this->updated_at->isToday() : false;
    }

    /**
     * Son güncelleme ne kadar zaman önce?
     * How long ago was it updated?
     */
    public function getTimeSinceUpdateAttribute(): string
    {
        if (!$this->updated_at) {
            return '-';
        }

        $diff = $this->updated_at->diffInMinutes(now());

        if ($diff < 60) {
            return $diff . ' dakika önce';
        } elseif ($diff < 1440) {
            return floor($diff / 60) . ' saat önce';
        } elseif ($diff < 10080) {
            return floor($diff / 1440) . ' gün önce';
        } else {
            return $this->updated_at->format('d.m.Y');
        }
    }

    /**
     * Özel tarih formatla
     * Format custom date field
     */
    public function formatDate(?string $field, string $format = 'd.m.Y H:i'): string
    {
        if (!$field || !$this->$field) {
            return '-';
        }

        return $this->$field instanceof Carbon
            ? $this->$field->format($format)
            : Carbon::parse($this->$field)->format($format);
    }

    /**
     * İki tarih arasındaki farkı hesapla
     * Calculate difference between two dates
     */
    public function dateDifference(?string $field1, ?string $field2, string $unit = 'hours'): int
    {
        if (!$field1 || !$field2 || !$this->$field1 || !$this->$field2) {
            return 0;
        }

        $date1 = $this->$field1 instanceof Carbon ? $this->$field1 : Carbon::parse($this->$field1);
        $date2 = $this->$field2 instanceof Carbon ? $this->$field2 : Carbon::parse($this->$field2);

        return match ($unit) {
            'seconds' => $date1->diffInSeconds($date2),
            'minutes' => $date1->diffInMinutes($date2),
            'hours' => $date1->diffInHours($date2),
            'days' => $date1->diffInDays($date2),
            'weeks' => $date1->diffInWeeks($date2),
            'months' => $date1->diffInMonths($date2),
            'years' => $date1->diffInYears($date2),
            default => $date1->diffInHours($date2),
        };
    }
}
