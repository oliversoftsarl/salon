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
        $this->showCalculateModal = true;
    }

    public function closeCalculateModal(): void
    {
        $this->showCalculateModal = false;
    }

    public function calculateWeeklyRevenues(): void
    {
        $this->validate([
            'calculate_week_start' => ['required', 'date'],
        ]);

        $weekStart = Carbon::parse($this->calculate_week_start)->startOfWeek();

        StaffWeeklyRevenue::calculateWeeklyRevenueForAllStaff($weekStart);

        $this->showCalculateModal = false;
        session()->flash('success', 'Recettes hebdomadaires calculées pour la semaine du ' . $weekStart->format('d/m/Y') . '.');
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
