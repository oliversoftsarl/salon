<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'role_id',
        'active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'active' => 'boolean',
    ];

    public function hasRole(string|array $roles): bool
    {
        // Utiliser le nouveau système de rôles si disponible
        if ($this->roleModel) {
            $userRoleName = $this->roleModel->name;
        } else {
            $userRoleName = $this->role ?? 'staff';
        }

        if (is_array($roles)) {
            return in_array($userRoleName, $roles, true);
        }
        return $userRoleName === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    public function isCashier(): bool
    {
        return $this->hasRole('cashier');
    }

    public function isStaff(): bool
    {
        return $this->hasRole('staff');
    }

    /**
     * Relation avec le rôle
     */
    public function roleModel()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    /**
     * Obtenir le nom du rôle
     */
    public function getRoleNameAttribute(): string
    {
        return $this->roleModel?->name ?? $this->role ?? 'staff';
    }

    /**
     * Obtenir le nom d'affichage du rôle
     */
    public function getRoleDisplayNameAttribute(): string
    {
        return $this->roleModel?->display_name ?? ucfirst($this->role ?? 'Staff');
    }

    /**
     * Vérifier si l'utilisateur a une permission spécifique
     */
    public function hasPermission(string $permissionName): bool
    {
        // Admin a toujours toutes les permissions
        if ($this->role_name === 'admin') {
            return true;
        }

        if (!$this->roleModel) {
            return false;
        }

        return $this->roleModel->hasPermission($permissionName);
    }

    /**
     * Obtenir toutes les permissions de l'utilisateur
     */
    public function getPermissionsAttribute()
    {
        if (!$this->roleModel) {
            return collect([]);
        }
        return $this->roleModel->permissions;
    }

    /**
     * Obtenir les menus accessibles par l'utilisateur
     */
    public function getAccessibleMenusAttribute()
    {
        if ($this->role_name === 'admin') {
            return Permission::menus()->get();
        }

        if (!$this->roleModel) {
            return collect([]);
        }

        return $this->roleModel->permissions()->where('is_menu', true)->orderBy('order')->get();
    }

    /**
     * Profil staff associé
     */
    public function staffProfile()
    {
        return $this->hasOne(StaffProfile::class);
    }

    /**
     * Dettes du staff
     */
    public function debts()
    {
        return $this->hasMany(StaffDebt::class);
    }

    /**
     * Dettes en cours (non remboursées)
     */
    public function pendingDebts()
    {
        return $this->hasMany(StaffDebt::class)->pending();
    }

    /**
     * Total des dettes en cours
     */
    public function getTotalDebtAttribute(): float
    {
        return $this->debts()->pending()->sum(\DB::raw('amount - paid_amount'));
    }

    /**
     * Prestations effectuées par ce prestataire
     */
    public function prestations()
    {
        return $this->hasMany(TransactionItem::class, 'stylist_id')
            ->whereNotNull('service_id');
    }

    /**
     * Vérifie si l'utilisateur peut accéder à une fonctionnalité
     */
    public function canAccess(string $feature): bool
    {
        // Admin a toujours accès à tout
        if ($this->role_name === 'admin') {
            return true;
        }

        // Utiliser le nouveau système de permissions
        return $this->hasPermission($feature);
    }
}
