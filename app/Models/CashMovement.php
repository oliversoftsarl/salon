<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashMovement extends Model
{
    protected $fillable = [
        'date',
        'type',
        'category',
        'amount',
        'description',
        'reference',
        'payment_method',
        'transaction_id',
        'user_id',
        'created_by',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Labels pour les catégories
    public static array $categoryLabels = [
        // Entrées
        'sale' => 'Vente',
        'other_income' => 'Autres revenus',
        // Sorties
        'expense' => 'Dépense générale',
        'bank_deposit' => 'Dépôt banque',
        'salary_advance' => 'Avance sur salaire',
        'internal_expense' => 'Dépense interne',
        'purchase' => 'Acquisition/Achat',
        'supplier_payment' => 'Paiement fournisseur',
    ];

    // Labels pour les méthodes de paiement
    public static array $paymentMethodLabels = [
        'cash' => 'Espèces',
        'card' => 'Carte bancaire',
        'transfer' => 'Virement',
        'mobile_money' => 'Mobile Money',
        'check' => 'Chèque',
    ];

    // Catégories d'entrées
    public static array $entryCategories = ['sale', 'other_income'];

    // Catégories de sorties
    public static array $exitCategories = [
        'expense', 'bank_deposit', 'salary_advance',
        'internal_expense', 'purchase', 'supplier_payment'
    ];

    // Relations
    public function transaction()
    {
        return $this->belongsTo(Transaction::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accesseurs
    public function getCategoryLabelAttribute(): string
    {
        return self::$categoryLabels[$this->category] ?? $this->category;
    }

    public function getPaymentMethodLabelAttribute(): string
    {
        return self::$paymentMethodLabels[$this->payment_method] ?? $this->payment_method;
    }

    public function getSignedAmountAttribute(): float
    {
        return $this->type === 'entry' ? $this->amount : -$this->amount;
    }

    // Scopes
    public function scopeEntries($query)
    {
        return $query->where('type', 'entry');
    }

    public function scopeExits($query)
    {
        return $query->where('type', 'exit');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeBetweenDates($query, $from, $to)
    {
        return $query->whereBetween('date', [$from, $to]);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('date', today());
    }
}

