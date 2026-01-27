<?php

namespace App\Livewire\Staff;

use App\Models\User;
use App\Models\TransactionItem;
use App\Models\Service;
use App\Models\StaffWeeklyRevenue;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Performance extends Component
{
    use WithPagination;

    // Filtres
    public ?int $selected_staff_id = null;
    public string $date_from = '';
    public string $date_to = '';
    public string $period = 'month'; // today, week, month, year, custom

    // Stats globales
    public array $globalStats = [];

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();
        $this->loadGlobalStats();
    }

    public function updatedPeriod($value): void
    {
        switch ($value) {
            case 'today':
                $this->date_from = now()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'week':
                $this->date_from = now()->startOfWeek()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'month':
                $this->date_from = now()->startOfMonth()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'year':
                $this->date_from = now()->startOfYear()->toDateString();
                $this->date_to = now()->toDateString();
                break;
        }
        $this->loadGlobalStats();
    }

    public function updatedDateFrom(): void
    {
        $this->period = 'custom';
        $this->loadGlobalStats();
    }

    public function updatedDateTo(): void
    {
        $this->period = 'custom';
        $this->loadGlobalStats();
    }

    public function updatedSelectedStaffId(): void
    {
        $this->loadGlobalStats();
    }

    public function loadGlobalStats(): void
    {
        // Stats pour les prestataires (coiffeurs)
        $queryStylists = TransactionItem::query()
            ->whereNotNull('stylist_id')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            });

        // Stats pour les masseurs
        $queryMasseurs = TransactionItem::query()
            ->whereNotNull('masseur_id')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            });

        if ($this->selected_staff_id) {
            $queryStylists->where('stylist_id', $this->selected_staff_id);
            $queryMasseurs->where('masseur_id', $this->selected_staff_id);
        }

        $this->globalStats = [
            'total_prestations' => (clone $queryStylists)->count(),
            'total_revenue' => (clone $queryStylists)->sum('line_total'),
            'avg_per_prestation' => (clone $queryStylists)->avg('line_total') ?? 0,
            'unique_clients' => (clone $queryStylists)->whereHas('transaction', fn($q) => $q->whereNotNull('client_id'))
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->distinct('transactions.client_id')
                ->count('transactions.client_id'),
            // Stats masseurs
            'total_massages' => (clone $queryMasseurs)->count(),
            'total_revenue_masseurs' => (clone $queryMasseurs)->sum('line_total'),
        ];
    }

    public function getStaffListProperty()
    {
        return User::whereIn('role', ['staff', 'admin'])
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getStaffPerformanceProperty()
    {
        $query = TransactionItem::query()
            ->select(
                'stylist_id',
                DB::raw('COUNT(*) as total_prestations'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('AVG(line_total) as avg_revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as total_transactions')
            )
            ->whereNotNull('stylist_id')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            })
            ->groupBy('stylist_id')
            ->orderByDesc('total_revenue');

        if ($this->selected_staff_id) {
            $query->where('stylist_id', $this->selected_staff_id);
        }

        return $query->get()->map(function ($item) {
            $item->staff = User::with('staffProfile')->find($item->stylist_id);
            return $item;
        });
    }

    public function getMasseurPerformanceProperty()
    {
        $query = TransactionItem::query()
            ->select(
                'masseur_id',
                DB::raw('COUNT(*) as total_prestations'),
                DB::raw('SUM(line_total) as total_revenue'),
                DB::raw('AVG(line_total) as avg_revenue'),
                DB::raw('COUNT(DISTINCT transaction_id) as total_transactions')
            )
            ->whereNotNull('masseur_id')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            })
            ->groupBy('masseur_id')
            ->orderByDesc('total_revenue');

        if ($this->selected_staff_id) {
            $query->where('masseur_id', $this->selected_staff_id);
        }

        return $query->get()->map(function ($item) {
            $item->staff = User::with('staffProfile')->find($item->masseur_id);
            return $item;
        });
    }

    public function getServicesByStaffProperty()
    {
        if (!$this->selected_staff_id) {
            return collect();
        }

        return TransactionItem::query()
            ->select(
                'service_id',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(line_total) as revenue')
            )
            ->where('stylist_id', $this->selected_staff_id)
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            })
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->get()
            ->map(function ($item) {
                $item->service = Service::find($item->service_id);
                return $item;
            });
    }

    public function getRecentPrestationsProperty()
    {
        $query = TransactionItem::query()
            ->with(['service', 'stylist', 'transaction.client'])
            ->whereNotNull('stylist_id')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            })
            ->orderByDesc('created_at');

        if ($this->selected_staff_id) {
            $query->where('stylist_id', $this->selected_staff_id);
        }

        return $query->paginate(15);
    }

    public function getDailyStatsProperty()
    {
        $query = TransactionItem::query()
            ->select(
                DB::raw('DATE(transactions.created_at) as date'),
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(transaction_items.line_total) as revenue')
            )
            ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
            ->whereNotNull('transaction_items.stylist_id')
            ->whereNotNull('transaction_items.service_id')
            ->whereDate('transactions.created_at', '>=', $this->date_from)
            ->whereDate('transactions.created_at', '<=', $this->date_to)
            ->groupBy('date')
            ->orderBy('date');

        if ($this->selected_staff_id) {
            $query->where('transaction_items.stylist_id', $this->selected_staff_id);
        }

        return $query->get();
    }

    /**
     * Obtenir l'historique des recettes hebdomadaires pour le staff sélectionné
     */
    public function getWeeklyRevenueHistoryProperty()
    {
        if (!$this->selected_staff_id) {
            return collect();
        }

        return StaffWeeklyRevenue::where('staff_id', $this->selected_staff_id)
            ->orderByDesc('year')
            ->orderByDesc('week_number')
            ->limit(12)
            ->get();
    }

    /**
     * Obtenir le cumul des manquants pour le staff sélectionné
     */
    public function getTotalShortageProperty(): float
    {
        if (!$this->selected_staff_id) {
            return 0;
        }

        return StaffWeeklyRevenue::getTotalShortage($this->selected_staff_id);
    }

    /**
     * Obtenir le montant cible hebdomadaire
     */
    public function getWeeklyTargetProperty(): float
    {
        return Setting::getWeeklyRevenueTarget();
    }

    public function render()
    {
        return view('livewire.staff.performance', [
            'staffList' => $this->staffList,
            'staffPerformance' => $this->staffPerformance,
            'masseurPerformance' => $this->masseurPerformance,
            'servicesByStaff' => $this->servicesByStaff,
            'recentPrestations' => $this->recentPrestations,
            'dailyStats' => $this->dailyStats,
            'weeklyRevenueHistory' => $this->weeklyRevenueHistory,
            'totalShortage' => $this->totalShortage,
            'weeklyTarget' => $this->weeklyTarget,
        ])->layout('layouts.main', ['title' => 'Performance des Prestataires']);
    }
}

