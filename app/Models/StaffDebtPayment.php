<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffDebtPayment extends Model
{
    protected $fillable = [
        'staff_debt_id',
        'amount',
        'payment_date',
        'payment_method',
        'recorded_by',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Labels pour les méthodes de paiement
    public static array $paymentMethodLabels = [
        'cash' => 'Espèces',
        'salary_deduction' => 'Retenue sur salaire',
        'transfer' => 'Virement',
        'mobile_money' => 'Mobile Money',
    ];

    // Relations
    public function debt(): BelongsTo
    {
        return $this->belongsTo(StaffDebt::class, 'staff_debt_id');
    }

    public function recorder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    // Accesseurs
    public function getPaymentMethodNameAttribute(): string
    {
        return self::$paymentMethodLabels[$this->payment_method] ?? $this->payment_method;
    }
}

