<?php

namespace App\Livewire\Staff;

use App\Models\User;
use App\Models\TransactionItem;
use App\Models\Service;
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
        $query = TransactionItem::query()
            ->whereNotNull('stylist_id')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereDate('created_at', '>=', $this->date_from)
                  ->whereDate('created_at', '<=', $this->date_to);
            });

        if ($this->selected_staff_id) {
            $query->where('stylist_id', $this->selected_staff_id);
        }

        $this->globalStats = [
            'total_prestations' => (clone $query)->count(),
            'total_revenue' => (clone $query)->sum('line_total'),
            'avg_per_prestation' => (clone $query)->avg('line_total') ?? 0,
            'unique_clients' => (clone $query)->whereHas('transaction', fn($q) => $q->whereNotNull('client_id'))
                ->join('transactions', 'transaction_items.transaction_id', '=', 'transactions.id')
                ->distinct('transactions.client_id')
                ->count('transactions.client_id'),
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
            $item->staff = User::find($item->stylist_id);
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

    public function render()
    {
        return view('livewire.staff.performance', [
            'staffList' => $this->staffList,
            'staffPerformance' => $this->staffPerformance,
            'servicesByStaff' => $this->servicesByStaff,
            'recentPrestations' => $this->recentPrestations,
            'dailyStats' => $this->dailyStats,
        ])->layout('layouts.main', ['title' => 'Performance des Prestataires']);
    }
}

