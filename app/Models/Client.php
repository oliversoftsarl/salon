<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        // ... existing code ...
        // Ajoute les champs selon ton schéma
        'first_name', 'last_name', 'name', 'names',
        'email',
        'phone', 'phone_number',
        'birthdate', 'gender', 'loyalty_point', 'notes',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }
    protected $casts = [
        'birthdate' => 'date',
        'loyalty_point' => 'integer',
    ];

}
