<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class StaffWeeklyRevenue extends Model
{
    protected $fillable = [
        'staff_id',
        'year',
        'week_number',
        'week_start',
        'week_end',
        'target_amount',
        'actual_amount',
        'difference',
        'cumulative_shortage',
        'notes',
    ];

    protected $casts = [
        'week_start' => 'date',
        'week_end' => 'date',
        'target_amount' => 'decimal:2',
        'actual_amount' => 'decimal:2',
        'difference' => 'decimal:2',
        'cumulative_shortage' => 'decimal:2',
    ];

    /**
     * Relation avec le staff
     */
    public function staff()
    {
        return $this->belongsTo(User::class, 'staff_id');
    }

    /**
     * Calculer et enregistrer les recettes hebdomadaires pour un staff
     */
    public static function calculateWeeklyRevenue(int $staffId, Carbon $weekStart): self
    {
        $weekEnd = $weekStart->copy()->endOfWeek();
        $year = $weekStart->year;
        $weekNumber = $weekStart->weekOfYear;

        // Récupérer le montant cible
        $targetAmount = Setting::getWeeklyRevenueTarget();

        // Calculer le montant réalisé cette semaine par ce coiffeur
        $actualAmount = TransactionItem::query()
            ->where('stylist_id', $staffId)
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) use ($weekStart, $weekEnd) {
                $q->whereBetween('created_at', [$weekStart->startOfDay(), $weekEnd->endOfDay()]);
            })
            ->sum('line_total');

        // Calculer la différence
        $difference = $actualAmount - $targetAmount;

        // Récupérer le cumul des manquants précédent
        $previousRecord = static::where('staff_id', $staffId)
            ->where(function ($q) use ($year, $weekNumber) {
                $q->where('year', '<', $year)
                    ->orWhere(function ($q2) use ($year, $weekNumber) {
                        $q2->where('year', $year)->where('week_number', '<', $weekNumber);
                    });
            })
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->first();

        $previousCumulativeShortage = $previousRecord ? $previousRecord->cumulative_shortage : 0;

        // Calculer le nouveau cumul
        // Si différence négative (manquant), on ajoute au cumul
        // Si différence positive (surplus), on réduit le cumul (mais pas en dessous de 0)
        $cumulativeShortage = $previousCumulativeShortage;
        if ($difference < 0) {
            $cumulativeShortage += abs($difference);
        } else {
            $cumulativeShortage = max(0, $cumulativeShortage - $difference);
        }

        // Créer ou mettre à jour l'enregistrement
        return static::updateOrCreate(
            [
                'staff_id' => $staffId,
                'year' => $year,
                'week_number' => $weekNumber,
            ],
            [
                'week_start' => $weekStart->toDateString(),
                'week_end' => $weekEnd->toDateString(),
                'target_amount' => $targetAmount,
                'actual_amount' => $actualAmount,
                'difference' => $difference,
                'cumulative_shortage' => $cumulativeShortage,
            ]
        );
    }

    /**
     * Calculer les recettes pour tous les coiffeurs pour une semaine donnée
     */
    public static function calculateWeeklyRevenueForAllStaff(Carbon $weekStart): void
    {
        // Récupérer tous les coiffeurs/barbiers
        $staffIds = User::whereHas('staffProfile', function ($q) {
            $q->where('role_title', 'like', '%Coiffeur%')
                ->orWhere('role_title', 'like', '%Coiffeuse%')
                ->orWhere('role_title', 'like', '%Barbier%');
        })->pluck('id');

        foreach ($staffIds as $staffId) {
            static::calculateWeeklyRevenue($staffId, $weekStart->copy());
        }
    }

    /**
     * Obtenir le cumul total des manquants pour un staff
     */
    public static function getTotalShortage(int $staffId): float
    {
        $latestRecord = static::where('staff_id', $staffId)
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->first();

        return $latestRecord ? $latestRecord->cumulative_shortage : 0;
    }
}
