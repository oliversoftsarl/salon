<?php

namespace App\Console\Commands;

use App\Models\StaffWeeklyRevenue;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CalculateWeeklyRevenue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'revenue:calculate-weekly
                            {--week= : La semaine spécifique à calculer (format: YYYY-MM-DD, utilise la date du début de semaine)}
                            {--all : Recalculer toutes les semaines depuis le début}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calcule les recettes hebdomadaires pour tous les coiffeurs et barbiers';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧮 Calcul des recettes hebdomadaires...');

        if ($this->option('all')) {
            return $this->calculateAllWeeks();
        }

        if ($weekDate = $this->option('week')) {
            $weekStart = Carbon::parse($weekDate)->startOfWeek();
        } else {
            // Par défaut, calculer la semaine qui vient de se terminer (samedi = fin de semaine de travail)
            $weekStart = Carbon::now()->subWeek()->startOfWeek();
        }

        $this->calculateForWeek($weekStart);

        return Command::SUCCESS;
    }

    /**
     * Calculer pour une semaine spécifique
     */
    protected function calculateForWeek(Carbon $weekStart): void
    {
        $weekEnd = $weekStart->copy()->endOfWeek();

        $this->info("📅 Semaine du {$weekStart->format('d/m/Y')} au {$weekEnd->format('d/m/Y')}");

        StaffWeeklyRevenue::calculateWeeklyRevenueForAllStaff($weekStart);

        $this->info('✅ Calcul terminé avec succès!');

        // Afficher un résumé
        $this->displaySummary($weekStart);
    }

    /**
     * Calculer toutes les semaines depuis la première transaction
     */
    protected function calculateAllWeeks(): int
    {
        $this->info('🔄 Recalcul de toutes les semaines...');

        // Trouver la première transaction
        $firstTransaction = \App\Models\Transaction::orderBy('created_at')->first();

        if (!$firstTransaction) {
            $this->warn('Aucune transaction trouvée.');
            return Command::SUCCESS;
        }

        $startWeek = Carbon::parse($firstTransaction->created_at)->startOfWeek();
        $currentWeek = Carbon::now()->startOfWeek();

        $weeks = [];
        $week = $startWeek->copy();

        while ($week->lte($currentWeek)) {
            $weeks[] = $week->copy();
            $week->addWeek();
        }

        $this->withProgressBar($weeks, function ($weekStart) {
            StaffWeeklyRevenue::calculateWeeklyRevenueForAllStaff($weekStart);
        });

        $this->newLine(2);
        $this->info('✅ Recalcul de toutes les semaines terminé!');

        return Command::SUCCESS;
    }

    /**
     * Afficher un résumé des calculs
     */
    protected function displaySummary(Carbon $weekStart): void
    {
        $records = StaffWeeklyRevenue::with('staff')
            ->where('week_start', $weekStart->toDateString())
            ->get();

        if ($records->isEmpty()) {
            $this->warn('Aucun coiffeur/barbier trouvé pour cette semaine.');
            return;
        }

        $this->newLine();
        $this->info('📊 Résumé de la semaine:');

        $headers = ['Staff', 'Objectif', 'Réalisé', 'Différence', 'Cumul Manquant'];
        $rows = [];

        foreach ($records as $record) {
            $diffColor = $record->difference >= 0 ? 'green' : 'red';
            $diffSign = $record->difference >= 0 ? '+' : '';

            $rows[] = [
                $record->staff->name ?? 'N/A',
                number_format($record->target_amount, 0, ',', ' ') . ' FC',
                number_format($record->actual_amount, 0, ',', ' ') . ' FC',
                $diffSign . number_format($record->difference, 0, ',', ' ') . ' FC',
                number_format($record->cumulative_shortage, 0, ',', ' ') . ' FC',
            ];
        }

        $this->table($headers, $rows);
    }
}
