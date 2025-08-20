<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = ['name','description','duration_minutes','price','service_type','active'];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

}
