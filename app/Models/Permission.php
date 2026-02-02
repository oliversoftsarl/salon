<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = [
        'name',
        'display_name',
        'group',
        'route_name',
        'icon',
        'order',
        'is_menu',
    ];

    protected $casts = [
        'is_menu' => 'boolean',
        'order' => 'integer',
    ];

    // Labels pour les groupes
    public static array $groupLabels = [
        'main' => 'Menu Principal',
        'sales' => 'Ventes',
        'inventory' => 'Inventaire',
        'staff' => 'Staff & RH',
        'finance' => 'Finance',
        'settings' => 'Paramètres',
    ];

    // Relations
    public function roles()
    {
        return $this->belongsToMany(Role::class, 'role_permissions')
            ->withTimestamps();
    }

    // Accesseurs
    public function getGroupLabelAttribute(): string
    {
        return self::$groupLabels[$this->group] ?? $this->group ?? 'Autre';
    }

    // Scopes
    public function scopeMenus($query)
    {
        return $query->where('is_menu', true)->orderBy('order');
    }

    public function scopeByGroup($query, string $group)
    {
        return $query->where('group', $group);
    }
}
