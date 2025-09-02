<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','sku','price','stock_quantity','is_snack',
        'is_consumable',
        'low_stock_threshold',
    ];

    protected $casts = [
        'is_snack' => 'boolean',
        'is_consumable' => 'boolean',
        'low_stock_threshold' => 'integer',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

}
