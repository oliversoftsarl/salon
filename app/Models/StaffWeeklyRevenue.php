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
     *
     * Note: La semaine de paie va du vendredi au jeudi suivant.
     * Le target_amount (seuil) est le montant minimum que le coiffeur doit réaliser.
     * Si le coiffeur n'atteint pas ce seuil, la différence s'ajoute au cumul (dette).
     * Si le coiffeur dépasse le seuil, le cumul reste inchangé (la réduction se fait lors du paiement).
     */
    public static function calculateWeeklyRevenue(int $staffId, Carbon $weekStart): self
    {
        // La semaine va du vendredi au jeudi suivant
        $weekEnd = $weekStart->copy()->addDays(6); // vendredi + 6 jours = jeudi
        $year = $weekStart->year;
        $weekNumber = $weekStart->weekOfYear;

        // Récupérer le montant du seuil hebdomadaire
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
        // Positif = le coiffeur a dépassé le seuil
        // Négatif = le coiffeur n'a pas atteint le seuil (manquant)
        $difference = $actualAmount - $targetAmount;

        // Récupérer le cumul précédent
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
        // Si différence négative (manquant), on ajoute le manquant au cumul
        // Si différence positive ou nulle (atteint ou dépassé), le cumul reste inchangé
        // La réduction du cumul se fait uniquement lors du paiement via reduceShortage()
        $cumulativeShortage = $previousCumulativeShortage;
        if ($difference < 0) {
            $cumulativeShortage += abs($difference);
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
     * Obtenir le début de la semaine de paie (vendredi) pour une date donnée
     * La semaine de paie va du vendredi au jeudi suivant
     */
    public static function getPayWeekStart(?Carbon $date = null): Carbon
    {
        $date = $date ? $date->copy() : Carbon::now();

        $dayOfWeek = $date->dayOfWeek; // 0 = dimanche, 5 = vendredi

        if ($dayOfWeek === Carbon::FRIDAY) {
            // C'est vendredi, on retourne cette date
            return $date->startOfDay();
        } elseif ($dayOfWeek === Carbon::SATURDAY) {
            // Samedi : retourner au vendredi d'hier
            return $date->subDay()->startOfDay();
        } else {
            // Dimanche (0), Lundi (1), Mardi (2), Mercredi (3), Jeudi (4)
            // Retourner au vendredi précédent
            return $date->previous(Carbon::FRIDAY)->startOfDay();
        }
    }

    /**
     * Obtenir la fin de la semaine de paie (jeudi) pour une date donnée
     */
    public static function getPayWeekEnd(?Carbon $date = null): Carbon
    {
        return static::getPayWeekStart($date)->copy()->addDays(6)->endOfDay();
    }

    /**
     * Obtenir le cumul total à déduire pour un staff (seuils non encore déduits)
     */
    public static function getTotalShortage(int $staffId): float
    {
        $latestRecord = static::where('staff_id', $staffId)
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->first();

        return $latestRecord ? max(0, $latestRecord->cumulative_shortage) : 0;
    }

    /**
     * Réduire le cumul après une déduction sur salaire
     */
    public static function reduceShortage(int $staffId, float $amountDeducted): void
    {
        if ($amountDeducted <= 0) {
            return;
        }

        $latestRecord = static::where('staff_id', $staffId)
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->first();

        if (!$latestRecord) {
            return;
        }

        // Réduire le cumul (pas en dessous de 0)
        $newCumulative = max(0, $latestRecord->cumulative_shortage - $amountDeducted);
        $latestRecord->cumulative_shortage = $newCumulative;
        $latestRecord->notes = ($latestRecord->notes ? $latestRecord->notes . "\n" : '') .
            '[' . now()->format('d/m/Y H:i') . '] Déduction sur salaire: ' . number_format($amountDeducted, 0, ',', ' ') . ' FC';
        $latestRecord->save();
    }
}
