<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductSupply extends Model
{
    protected $fillable = [
        'product_id',
        'quantity_received',
        'unit_cost',
        'supplier',
        'received_at',
        'notes',
    ];

    protected $casts = [
        'quantity_received' => 'integer',
        'unit_cost' => 'decimal:2',
        'received_at' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
