<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffBreak extends Model
{
    protected $fillable = ['staff_id','start_at','end_at','recurring'];

    protected $casts = [
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'recurring' => 'boolean',
    ];

    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

}
