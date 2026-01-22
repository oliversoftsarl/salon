<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'sku',
        'price',
        'stock_quantity',
        'category',
        'is_snack',
        'is_consumable',
        'low_stock_threshold',
    ];

    protected $casts = [
        'is_snack' => 'boolean',
        'is_consumable' => 'boolean',
        'low_stock_threshold' => 'integer',
    ];

    /**
     * Catégories disponibles pour les produits
     */
    public const CATEGORIES = [
        'sale' => 'Vente uniquement',
        'consumption' => 'Consommation interne',
        'both' => 'Vente et Consommation',
    ];

    /**
     * Vérifie si le produit peut être vendu
     */
    public function canBeSold(): bool
    {
        return in_array($this->category, ['sale', 'both']);
    }

    /**
     * Vérifie si le produit peut être consommé en interne
     */
    public function canBeConsumed(): bool
    {
        return in_array($this->category, ['consumption', 'both']);
    }

    /**
     * Retourne le libellé de la catégorie
     */
    public function getCategoryLabelAttribute(): string
    {
        return self::CATEGORIES[$this->category] ?? $this->category;
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

}
