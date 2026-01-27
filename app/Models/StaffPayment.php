<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffPayment extends Model
{
    protected $fillable = [
        'user_id',
        'payment_type',
        'base_salary',
        'bonus',
        'deductions',
        'shortage_deduction',
        'net_amount',
        'period',
        'period_start',
        'period_end',
        'payment_date',
        'payment_method',
        'notes',
        'debt_details',
        'shortage_details',
        'cash_movement_id',
        'created_by',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'bonus' => 'decimal:2',
        'deductions' => 'decimal:2',
        'shortage_deduction' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'payment_date' => 'date',
        'debt_details' => 'array',
        'shortage_details' => 'array',
    ];

    // Labels pour les types de paiement
    public static array $paymentTypeLabels = [
        'weekly' => 'Hebdomadaire',
        'monthly' => 'Mensuel',
    ];

    // Labels pour les méthodes de paiement
    public static array $paymentMethodLabels = [
        'cash' => 'Espèces',
        'transfer' => 'Virement',
        'mobile_money' => 'Mobile Money',
        'check' => 'Chèque',
    ];

    /**
     * Staff associé au paiement
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Mouvement de caisse associé
     */
    public function cashMovement(): BelongsTo
    {
        return $this->belongsTo(CashMovement::class);
    }

    /**
     * Utilisateur qui a créé le paiement
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Accesseurs
     */
    public function getPaymentTypeLabelAttribute(): string
    {
        return self::$paymentTypeLabels[$this->payment_type] ?? $this->payment_type;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::$paymentMethodLabels[$this->payment_method] ?? $this->payment_method;
    }

    public function getPeriodLabelAttribute(): string
    {
        if ($this->payment_type === 'weekly') {
            return 'Semaine du ' . $this->period_start->format('d/m/Y') . ' au ' . $this->period_end->format('d/m/Y');
        }
        return $this->period_start->translatedFormat('F Y');
    }

    public function getTotalDeductionsAttribute(): float
    {
        return $this->deductions + $this->shortage_deduction;
    }

    /**
     * Scopes
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWeekly($query)
    {
        return $query->where('payment_type', 'weekly');
    }

    public function scopeMonthly($query)
    {
        return $query->where('payment_type', 'monthly');
    }

    public function scopeForPeriod($query, string $period)
    {
        return $query->where('period', $period);
    }

    /**
     * Vérifier si un paiement existe déjà pour cette période
     */
    public static function existsForPeriod(int $userId, string $period): bool
    {
        return static::where('user_id', $userId)->where('period', $period)->exists();
    }

    /**
     * Obtenir le dernier paiement d'un staff
     */
    public static function getLastPayment(int $userId): ?self
    {
        return static::where('user_id', $userId)
            ->orderByDesc('payment_date')
            ->first();
    }
}
