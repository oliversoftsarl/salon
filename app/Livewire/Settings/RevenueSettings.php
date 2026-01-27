<?php

namespace App\Livewire\Settings;

use App\Models\Setting;
use App\Models\StaffWeeklyRevenue;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;

class RevenueSettings extends Component
{
    public string $weekly_revenue_target = '';
    public bool $showCalculateModal = false;
    public string $calculate_week_start = '';
    public bool $isCalculating = false;

    public function mount(): void
    {
        $this->weekly_revenue_target = (string) Setting::getWeeklyRevenueTarget();
        $this->calculate_week_start = now()->startOfWeek()->toDateString();
    }

    public function saveTarget(): void
    {
        $this->validate([
            'weekly_revenue_target' => ['required', 'numeric', 'min:0'],
        ]);

        Setting::setValue(
            'weekly_revenue_target',
            $this->weekly_revenue_target,
            'decimal',
            'revenue',
            'Montant cible hebdomadaire pour chaque coiffeur (en FC)'
        );

        session()->flash('success', 'Montant cible hebdomadaire enregistré avec succès.');
    }

    public function openCalculateModal(): void
    {
        $this->calculate_week_start = now()->startOfWeek()->toDateString();
        $this->showCalculateModal = true;
    }

    public function closeCalculateModal(): void
    {
        $this->showCalculateModal = false;
        $this->isCalculating = false;
    }

    public function calculateWeeklyRevenues(): void
    {
        $this->validate([
            'calculate_week_start' => ['required', 'date'],
        ]);

        $this->isCalculating = true;

        try {
            $weekStart = Carbon::parse($this->calculate_week_start)->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            StaffWeeklyRevenue::calculateWeeklyRevenueForAllStaff($weekStart);

            $this->showCalculateModal = false;
            $this->isCalculating = false;

            session()->flash('success', 'Recettes hebdomadaires calculées avec succès pour la semaine du ' .
                $weekStart->format('d/m/Y') . ' au ' . $weekEnd->format('d/m/Y') . '.');
        } catch (\Exception $e) {
            $this->isCalculating = false;
            session()->flash('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    public function calculateCurrentWeek(): void
    {
        $this->isCalculating = true;

        try {
            $weekStart = now()->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            StaffWeeklyRevenue::calculateWeeklyRevenueForAllStaff($weekStart);

            $this->isCalculating = false;

            session()->flash('success', 'Recettes de la semaine en cours calculées avec succès (' .
                $weekStart->format('d/m/Y') . ' au ' . $weekEnd->format('d/m/Y') . ').');
        } catch (\Exception $e) {
            $this->isCalculating = false;
            session()->flash('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    public function calculateLastWeek(): void
    {
        $this->isCalculating = true;

        try {
            $weekStart = now()->subWeek()->startOfWeek();
            $weekEnd = $weekStart->copy()->endOfWeek();

            StaffWeeklyRevenue::calculateWeeklyRevenueForAllStaff($weekStart);

            $this->isCalculating = false;

            session()->flash('success', 'Recettes de la semaine dernière calculées avec succès (' .
                $weekStart->format('d/m/Y') . ' au ' . $weekEnd->format('d/m/Y') . ').');
        } catch (\Exception $e) {
            $this->isCalculating = false;
            session()->flash('error', 'Erreur lors du calcul: ' . $e->getMessage());
        }
    }

    public function getStaffShortagesProperty()
    {
        return User::whereHas('staffProfile', function ($q) {
            $q->where('role_title', 'like', '%Coiffeur%')
                ->orWhere('role_title', 'like', '%Coiffeuse%')
                ->orWhere('role_title', 'like', '%Barbier%');
        })
        ->with('staffProfile')
        ->get()
        ->map(function ($staff) {
            $staff->total_shortage = StaffWeeklyRevenue::getTotalShortage($staff->id);
            return $staff;
        })
        ->sortByDesc('total_shortage');
    }

    public function render()
    {
        return view('livewire.settings.revenue-settings', [
            'staffShortages' => $this->staffShortages,
        ])->layout('layouts.main', ['title' => 'Paramètres des Recettes']);
    }
}
