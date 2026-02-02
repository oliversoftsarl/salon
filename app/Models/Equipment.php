<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'code',
        'category',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'supplier',
        'status',
        'condition',
        'location',
        'warranty_expiry',
        'last_maintenance',
        'next_maintenance',
        'description',
        'notes',
        'assigned_to',
        'created_by',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'warranty_expiry' => 'date',
        'last_maintenance' => 'date',
        'next_maintenance' => 'date',
        'purchase_price' => 'decimal:2',
    ];

    // Labels pour les catégories
    public static array $categoryLabels = [
        'dryer' => 'Sèche-cheveux',
        'clipper' => 'Tondeuse',
        'chair' => 'Fauteuil',
        'mirror' => 'Miroir',
        'steamer' => 'Vapeur / Steamer',
        'wash_station' => 'Bac à shampoing',
        'trolley' => 'Chariot / Desserte',
        'hood_dryer' => 'Casque séchoir',
        'straightener' => 'Lisseur',
        'curler' => 'Fer à boucler',
        'massage_table' => 'Table de massage',
        'manicure_table' => 'Table de manucure',
        'pedicure_chair' => 'Fauteuil pédicure',
        'uv_sterilizer' => 'Stérilisateur UV',
        'air_conditioner' => 'Climatiseur',
        'fan' => 'Ventilateur',
        'tv' => 'Télévision',
        'speaker' => 'Enceinte / Haut-parleur',
        'computer' => 'Ordinateur / Caisse',
        'other' => 'Autre',
    ];

    // Labels pour les statuts
    public static array $statusLabels = [
        'operational' => 'Opérationnel',
        'maintenance' => 'En maintenance',
        'broken' => 'En panne',
        'retired' => 'Hors service',
    ];

    // Couleurs pour les statuts
    public static array $statusColors = [
        'operational' => 'success',
        'maintenance' => 'warning',
        'broken' => 'danger',
        'retired' => 'secondary',
    ];

    // Labels pour les conditions
    public static array $conditionLabels = [
        'new' => 'Neuf',
        'good' => 'Bon état',
        'fair' => 'État moyen',
        'poor' => 'Mauvais état',
    ];

    // Relations
    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function maintenances()
    {
        return $this->hasMany(EquipmentMaintenance::class);
    }

    // Accesseurs
    public function getCategoryLabelAttribute(): string
    {
        return self::$categoryLabels[$this->category] ?? $this->category;
    }

    public function getStatusLabelAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? 'secondary';
    }

    public function getConditionLabelAttribute(): string
    {
        return self::$conditionLabels[$this->condition] ?? $this->condition;
    }

    public function getIsUnderWarrantyAttribute(): bool
    {
        return $this->warranty_expiry && $this->warranty_expiry->isFuture();
    }

    public function getNeedsMaintenanceAttribute(): bool
    {
        return $this->next_maintenance && $this->next_maintenance->isPast();
    }

    // Scopes
    public function scopeOperational($query)
    {
        return $query->where('status', 'operational');
    }

    public function scopeNeedsMaintenance($query)
    {
        return $query->where('next_maintenance', '<=', now());
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
