<?php

namespace App\Traits;

/**
 * Kullanıcı yetki kontrolü için trait
 * Trait for user permission checks
 */
trait HasPermissions
{
    /**
     * Kullanıcı admin mi?
     * Is user an admin?
     */
    public function isAdmin(): bool
    {
        return $this->user_type === 'admin';
    }

    /**
     * Kullanıcı marka mı?
     * Is user a brand?
     */
    public function isBrand(): bool
    {
        return $this->user_type === 'brand';
    }

    /**
     * Kullanıcı müşteri mi?
     * Is user a customer?
     */
    public function isCustomer(): bool
    {
        return $this->user_type === 'customer';
    }

    /**
     * Kullanıcı aktif mi?
     * Is user active?
     */
    public function isActive(): bool
    {
        return $this->is_active === true;
    }

    /**
     * Kullanıcı yasaklı mı?
     * Is user banned?
     */
    public function isBanned(): bool
    {
        return $this->is_banned === true;
    }

    /**
     * Kullanıcı belirli bir yetkiye sahip mi?
     * Does user have specific permission?
     */
    public function hasPermission(string $permission): bool
    {
        // Admin kullanıcılar tüm yetkilere sahip
        // Admin users have all permissions
        if ($this->isAdmin()) {
            return true;
        }

        // Marka kullanıcıları için yetkiler
        // Permissions for brand users
        if ($this->isBrand()) {
            $brandPermissions = [
                'view_complaints',
                'respond_to_complaints',
                'view_brand_statistics',
                'update_brand_profile',
            ];

            return in_array($permission, $brandPermissions);
        }

        // Müşteri kullanıcıları için yetkiler
        // Permissions for customer users
        if ($this->isCustomer()) {
            $customerPermissions = [
                'create_complaint',
                'view_own_complaints',
                'update_own_complaints',
                'delete_own_complaints',
            ];

            return in_array($permission, $customerPermissions);
        }

        return false;
    }

    /**
     * Kullanıcı birden fazla yetkiden birine sahip mi?
     * Does user have any of the given permissions?
     */
    public function hasAnyPermission(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Kullanıcı tüm verilen yetkilere sahip mi?
     * Does user have all given permissions?
     */
    public function hasAllPermissions(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!$this->hasPermission($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Kullanıcı şikayeti görüntüleyebilir mi?
     * Can user view complaint?
     */
    public function canViewComplaint($complaint): bool
    {
        // Admin tüm şikayetleri görebilir
        // Admin can view all complaints
        if ($this->isAdmin()) {
            return true;
        }

        // Marka kendi şikayetlerini görebilir
        // Brand can view their own complaints
        if ($this->isBrand() && $complaint->brand_id === $this->brand_id) {
            return true;
        }

        // Müşteri kendi şikayetlerini görebilir
        // Customer can view their own complaints
        if ($this->isCustomer() && $complaint->user_id === $this->id) {
            return true;
        }

        // Yayınlanan şikayetler herkes tarafından görülebilir
        // Published complaints can be viewed by everyone
        if ($complaint->is_published) {
            return true;
        }

        return false;
    }

    /**
     * Kullanıcı şikayeti düzenleyebilir mi?
     * Can user edit complaint?
     */
    public function canEditComplaint($complaint): bool
    {
        // Admin tüm şikayetleri düzenleyebilir
        // Admin can edit all complaints
        if ($this->isAdmin()) {
            return true;
        }

        // Kullanıcı sadece kendi şikayetlerini düzenleyebilir
        // User can only edit their own complaints
        if ($this->isCustomer() && $complaint->user_id === $this->id) {
            // Sadece beklemedeki şikayetler düzenlenebilir
            // Only pending complaints can be edited
            return $complaint->status === 'pending';
        }

        return false;
    }

    /**
     * Kullanıcı şikayeti silebilir mi?
     * Can user delete complaint?
     */
    public function canDeleteComplaint($complaint): bool
    {
        // Admin tüm şikayetleri silebilir
        // Admin can delete all complaints
        if ($this->isAdmin()) {
            return true;
        }

        // Kullanıcı sadece kendi beklemedeki şikayetlerini silebilir
        // User can only delete their own pending complaints
        if ($this->isCustomer() && $complaint->user_id === $this->id) {
            return $complaint->status === 'pending';
        }

        return false;
    }

    /**
     * Kullanıcı şikayete cevap verebilir mi?
     * Can user respond to complaint?
     */
    public function canRespondToComplaint($complaint): bool
    {
        // Admin tüm şikayetlere cevap verebilir
        // Admin can respond to all complaints
        if ($this->isAdmin()) {
            return true;
        }

        // Marka kendi şikayetlerine cevap verebilir
        // Brand can respond to their own complaints
        if ($this->isBrand() && $complaint->brand_id === $this->brand_id) {
            return true;
        }

        // Şikayet sahibi kendi şikayetine cevap verebilir
        // Complaint owner can respond to their own complaint
        if ($this->isCustomer() && $complaint->user_id === $this->id) {
            return true;
        }

        return false;
    }

    /**
     * Kullanıcı şikayeti onaylayabilir mi?
     * Can user approve complaint?
     */
    public function canApproveComplaint(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Kullanıcı şikayeti reddedebilir mi?
     * Can user reject complaint?
     */
    public function canRejectComplaint(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Kullanıcı markayı yönetebilir mi?
     * Can user manage brand?
     */
    public function canManageBrand($brand = null): bool
    {
        // Admin tüm markaları yönetebilir
        // Admin can manage all brands
        if ($this->isAdmin()) {
            return true;
        }

        // Marka kullanıcısı kendi markasını yönetebilir
        // Brand user can manage their own brand
        if ($this->isBrand() && $brand && $brand->id === $this->brand_id) {
            return true;
        }

        return false;
    }

    /**
     * Kullanıcı istatistikleri görebilir mi?
     * Can user view statistics?
     */
    public function canViewStatistics(): bool
    {
        return $this->isAdmin() || $this->isBrand();
    }

    /**
     * Kullanıcı tüm şikayetleri görebilir mi?
     * Can user view all complaints?
     */
    public function canViewAllComplaints(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Kullanıcı kullanıcıları yönetebilir mi?
     * Can user manage users?
     */
    public function canManageUsers(): bool
    {
        return $this->isAdmin();
    }

    /**
     * Kullanıcı ayarları yönetebilir mi?
     * Can user manage settings?
     */
    public function canManageSettings(): bool
    {
        return $this->isAdmin();
    }
}
