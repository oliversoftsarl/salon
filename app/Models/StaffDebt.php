<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\StaffDebtPayment;

class StaffDebt extends Model
{
    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'paid_amount',
        'description',
        'product_id',
        'quantity',
        'debt_date',
        'due_date',
        'status',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'debt_date' => 'date',
        'due_date' => 'date',
        'amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
    ];

    // Labels pour les types de dette
    public static array $typeLabels = [
        'product_consumption' => 'Consommation produit',
        'loan' => 'Prêt',
        'advance' => 'Avance',
        'other' => 'Autre',
    ];

    // Labels pour les statuts
    public static array $statusLabels = [
        'pending' => 'En attente',
        'partial' => 'Partiellement payé',
        'paid' => 'Remboursé',
        'cancelled' => 'Annulé',
    ];

    // Couleurs pour les statuts
    public static array $statusColors = [
        'pending' => 'warning',
        'partial' => 'info',
        'paid' => 'success',
        'cancelled' => 'secondary',
    ];

    // Relations
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(StaffDebtPayment::class);
    }

    // Accesseurs
    public function getRemainingAmountAttribute(): float
    {
        return $this->amount - $this->paid_amount;
    }

    public function getTypeNameAttribute(): string
    {
        return self::$typeLabels[$this->type] ?? $this->type;
    }

    public function getStatusNameAttribute(): string
    {
        return self::$statusLabels[$this->status] ?? $this->status;
    }

    public function getStatusColorAttribute(): string
    {
        return self::$statusColors[$this->status] ?? 'secondary';
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date && $this->due_date->isPast() && $this->status !== 'paid';
    }

    // Méthodes
    public function addPayment(float $amount, string $method = 'cash', ?int $recordedBy = null, ?string $notes = null): StaffDebtPayment
    {
        $payment = $this->payments()->create([
            'amount' => $amount,
            'payment_date' => now(),
            'payment_method' => $method,
            'recorded_by' => $recordedBy,
            'notes' => $notes,
        ]);

        $this->paid_amount += $amount;

        if ($this->paid_amount >= $this->amount) {
            $this->status = 'paid';
        } else {
            $this->status = 'partial';
        }

        $this->save();

        return $payment;
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'partial']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                     ->whereIn('status', ['pending', 'partial']);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}

