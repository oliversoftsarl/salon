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
        $userRole = $this->role ?? 'staff';
        if (is_array($roles)) {
            return in_array($userRole, $roles, true);
        }
        return $userRole === $roles;
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
     * Vérifie si l'utilisateur peut accéder à une fonctionnalité
     */
    public function canAccess(string $feature): bool
    {
        $permissions = [
            'dashboard' => ['admin', 'staff'],
            'pos' => ['admin', 'cashier'],
            'transactions' => ['admin', 'cashier'],
            'services' => ['admin', 'staff'],
            'products' => ['admin', 'staff'],
            'clients' => ['admin', 'staff'],
            'appointments' => ['admin', 'staff'],
            'staff' => ['admin'],
            'inventory' => ['admin', 'staff'],
            'users' => ['admin'],
        ];

        return isset($permissions[$feature]) && in_array($this->role, $permissions[$feature], true);
    }
}
