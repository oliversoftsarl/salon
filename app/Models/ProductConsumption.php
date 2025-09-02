<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProductConsumption extends Model
{
    protected $fillable = ['product_id','quantity_used','staff_id','used_at','notes'];

    protected $casts = [
        'used_at' => 'date',
        'quantity_used' => 'integer',
    ];

    public function product() { return $this->belongsTo(Product::class); }
    public function staff()   { return $this->belongsTo(User::class, 'staff_id'); }
}
