<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffProfile extends Model
{
    protected $fillable = [
        'user_id',
        'display_name',
        'role_title',
        'hourly_rate',
        'availability',
    ];

    protected $casts = [
        'hourly_rate' => 'decimal:2',
        'availability' => 'array', // cast JSON en array PHP
    ];

    /**
     * Utilisateur associé au profil
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
