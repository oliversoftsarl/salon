<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = ['name','sku','price','stock_quantity','is_snack'];

    protected $casts = [
        'is_snack' => 'boolean',
    ];

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }

}
