<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EquipmentMaintenance extends Model
{
    protected $fillable = [
        'equipment_id',
        'maintenance_date',
        'type',
        'performed_by',
        'cost',
        'description',
        'parts_replaced',
        'next_maintenance',
        'created_by',
    ];

    protected $casts = [
        'maintenance_date' => 'date',
        'next_maintenance' => 'date',
        'cost' => 'decimal:2',
    ];

    // Labels pour les types de maintenance
    public static array $typeLabels = [
        'preventive' => 'Maintenance préventive',
        'corrective' => 'Réparation',
        'cleaning' => 'Nettoyage / Entretien',
        'inspection' => 'Inspection',
        'replacement' => 'Remplacement pièce',
    ];

    // Relations
    public function equipment()
    {
        return $this->belongsTo(Equipment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accesseurs
    public function getTypeLabelAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? $this->type;
    }
}
