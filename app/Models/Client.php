<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $table = 'clients';

    protected $fillable = [
        'first_name', 'last_name', 'name', 'names',
        'email',
        'phone', 'phone_number',
        'birthdate', 'gender', 'loyalty_point', 'notes',
        'publish_consent',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    protected $casts = [
        'birthdate' => 'date',
        'loyalty_point' => 'integer',
        'publish_consent' => 'boolean',
    ];

    /**
     * Vérifie si le client a donné son consentement pour la publication
     */
    public function hasPublishConsent(): bool
    {
        return (bool) $this->publish_consent;
    }

    /**
     * Scope pour filtrer les clients avec consentement
     */
    public function scopeWithConsent($query)
    {
        return $query->where('publish_consent', true);
    }

    /**
     * Scope pour filtrer les clients sans consentement
     */
    public function scopeWithoutConsent($query)
    {
        return $query->where('publish_consent', false);
    }
}
