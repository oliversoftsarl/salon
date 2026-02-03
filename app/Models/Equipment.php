<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Equipment extends Model
{
    protected $table = 'equipment';

    protected $fillable = [
        'name',
        'code',
        'category',
        'sub_category',
        'brand',
        'model',
        'serial_number',
        'purchase_date',
        'purchase_price',
        'lifespan_months',
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
        'lifespan_months' => 'integer',
    ];

    // Labels pour les catégories
    public static array $categoryLabels = [
        'hair_equipment' => 'Équipements Coiffure',
        'beauty_equipment' => 'Équipements Beauté/Esthétique',
        'massage_equipment' => 'Équipements Massage',
        'furniture' => 'Mobilier',
        'electronics' => 'Électronique/Informatique',
        'climate' => 'Climatisation/Ventilation',
        'other' => 'Autre',
    ];

    // Labels pour les sous-catégories par catégorie
    public static array $subCategoryLabels = [
        'hair_equipment' => [
            'dryer' => 'Sèche-cheveux',
            'clipper' => 'Tondeuse',
            'straightener' => 'Lisseur',
            'curler' => 'Fer à boucler',
            'hood_dryer' => 'Casque séchoir',
            'steamer' => 'Vapeur / Steamer',
            'wash_station' => 'Bac à shampoing',
        ],
        'beauty_equipment' => [
            'manicure_table' => 'Table de manucure',
            'pedicure_chair' => 'Fauteuil pédicure',
            'uv_sterilizer' => 'Stérilisateur UV',
            'uv_lamp' => 'Lampe UV/LED',
            'wax_heater' => 'Chauffe-cire',
        ],
        'massage_equipment' => [
            'massage_table' => 'Table de massage',
            'massage_chair' => 'Fauteuil de massage',
            'hot_stones' => 'Pierres chaudes',
        ],
        'furniture' => [
            'chair' => 'Fauteuil coiffure',
            'mirror' => 'Miroir',
            'trolley' => 'Chariot / Desserte',
            'reception_desk' => 'Bureau réception',
            'waiting_chair' => 'Chaise d\'attente',
            'storage' => 'Rangement/Étagère',
        ],
        'electronics' => [
            'computer' => 'Ordinateur / Caisse',
            'tv' => 'Télévision',
            'speaker' => 'Enceinte / Haut-parleur',
            'printer' => 'Imprimante',
            'phone' => 'Téléphone',
        ],
        'climate' => [
            'air_conditioner' => 'Climatiseur',
            'fan' => 'Ventilateur',
            'heater' => 'Chauffage',
        ],
        'other' => [
            'other' => 'Autre',
        ],
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

    public function getSubCategoryLabelAttribute(): string
    {
        $subCategories = self::$subCategoryLabels[$this->category] ?? [];
        return $subCategories[$this->sub_category] ?? $this->sub_category ?? '-';
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

    /**
     * Calcule la date de fin de vie (amortissement)
     */
    public function getEndOfLifeDateAttribute(): ?Carbon
    {
        if (!$this->purchase_date || !$this->lifespan_months) {
            return null;
        }
        return $this->purchase_date->copy()->addMonths($this->lifespan_months);
    }

    /**
     * Vérifie si l'équipement doit être renouvelé (fin de vie atteinte ou proche)
     */
    public function getNeedsRenewalAttribute(): bool
    {
        $endOfLife = $this->end_of_life_date;
        if (!$endOfLife) {
            return false;
        }
        // Alerte si dans les 3 prochains mois ou dépassé
        return $endOfLife->lte(now()->addMonths(3));
    }

    /**
     * Vérifie si l'équipement est amorti (fin de vie dépassée)
     */
    public function getIsAmortizedAttribute(): bool
    {
        $endOfLife = $this->end_of_life_date;
        if (!$endOfLife) {
            return false;
        }
        return $endOfLife->isPast();
    }

    /**
     * Retourne le pourcentage d'amortissement
     */
    public function getAmortizationPercentAttribute(): float
    {
        if (!$this->purchase_date || !$this->lifespan_months) {
            return 0;
        }

        $monthsUsed = $this->purchase_date->diffInMonths(now());
        $percent = ($monthsUsed / $this->lifespan_months) * 100;

        return min(100, round($percent, 1));
    }

    /**
     * Retourne les mois restants avant fin de vie
     */
    public function getRemainingMonthsAttribute(): ?int
    {
        $endOfLife = $this->end_of_life_date;
        if (!$endOfLife) {
            return null;
        }

        $months = now()->diffInMonths($endOfLife, false);
        return max(0, $months);
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
