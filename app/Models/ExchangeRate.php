<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ExchangeRate extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
        'is_active',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'rate' => 'decimal:4',
        'effective_date' => 'date',
        'is_active' => 'boolean',
    ];

    // Devises supportées
    public const CURRENCY_USD = 'USD';
    public const CURRENCY_CDF = 'CDF';

    public const CURRENCIES = [
        self::CURRENCY_USD => 'Dollar américain ($)',
        self::CURRENCY_CDF => 'Franc congolais (FC)',
    ];

    // Symboles des devises
    public const CURRENCY_SYMBOLS = [
        self::CURRENCY_USD => '$',
        self::CURRENCY_CDF => 'FC',
    ];

    /**
     * Relation avec l'utilisateur qui a créé le taux
     */
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Récupérer le taux de change actif actuel (USD -> CDF)
     */
    public static function getCurrentRate(): ?self
    {
        return Cache::remember('current_exchange_rate', 3600, function () {
            return self::where('from_currency', self::CURRENCY_USD)
                ->where('to_currency', self::CURRENCY_CDF)
                ->where('is_active', true)
                ->where('effective_date', '<=', now()->toDateString())
                ->orderByDesc('effective_date')
                ->first();
        });
    }

    /**
     * Récupérer la valeur du taux actuel
     */
    public static function getCurrentRateValue(): float
    {
        $rate = self::getCurrentRate();
        return $rate ? (float) $rate->rate : 2800.0; // Valeur par défaut
    }

    /**
     * Convertir CDF en USD
     */
    public static function convertToUsd(float $amountCdf): float
    {
        $rate = self::getCurrentRateValue();
        return $rate > 0 ? round($amountCdf / $rate, 2) : 0;
    }

    /**
     * Convertir USD en CDF
     */
    public static function convertToCdf(float $amountUsd): float
    {
        $rate = self::getCurrentRateValue();
        return round($amountUsd * $rate, 2);
    }

    /**
     * Formater un montant en CDF
     */
    public static function formatCdf(float $amount): string
    {
        return number_format($amount, 0, ',', ' ') . ' FC';
    }

    /**
     * Formater un montant en USD
     */
    public static function formatUsd(float $amount): string
    {
        return '$ ' . number_format($amount, 2, ',', ' ');
    }

    /**
     * Formater un montant CDF avec équivalent USD
     */
    public static function formatWithUsd(float $amountCdf): string
    {
        $usd = self::convertToUsd($amountCdf);
        return self::formatCdf($amountCdf) . ' (' . self::formatUsd($usd) . ')';
    }

    /**
     * Vider le cache du taux de change
     */
    public static function clearCache(): void
    {
        Cache::forget('current_exchange_rate');
    }

    /**
     * Scope pour les taux actifs
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope pour USD -> CDF
     */
    public function scopeUsdToCdf($query)
    {
        return $query->where('from_currency', self::CURRENCY_USD)
            ->where('to_currency', self::CURRENCY_CDF);
    }

    /**
     * Boot du modèle pour vider le cache automatiquement
     */
    protected static function booted(): void
    {
        static::saved(function () {
            self::clearCache();
        });

        static::deleted(function () {
            self::clearCache();
        });
    }
}

