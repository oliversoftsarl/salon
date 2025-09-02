<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    //

    protected $fillable = [
        'name',
        'description',
        'duration_minutes',
        'price',
        'service_type',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'price' => 'decimal:2',
        'duration_minutes' => 'integer',
    ];
}
