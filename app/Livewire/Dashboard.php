<?php

namespace App\Livewire;

use App\Models\Client;
use App\Models\Product;
use App\Models\Service;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\CashMovement;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class Dashboard extends Component
{
    public string $period = 'month';
    public string $date_from = '';
    public string $date_to = '';

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();
    }

    public function updatedPeriod($value): void
    {
        switch ($value) {
            case 'week':
                $this->date_from = now()->startOfWeek()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'month':
                $this->date_from = now()->startOfMonth()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'quarter':
                $this->date_from = now()->startOfQuarter()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'year':
                $this->date_from = now()->startOfYear()->toDateString();
                $this->date_to = now()->toDateString();
                break;
        }
    }

    // Statistiques générales
    public function getGeneralStatsProperty(): array
    {
        return [
            'services_count' => Service::where('active', true)->count(),
            'products_count' => Product::count(),
            'clients_count' => Client::count(),
            'today_sales' => Transaction::whereDate('created_at', today())->sum('total'),
            'today_transactions' => Transaction::whereDate('created_at', today())->count(),
        ];
    }

    // Statistiques de revenus sur la période
    public function getRevenueStatsProperty(): array
    {
        $currentPeriod = Transaction::whereBetween('created_at', [
            Carbon::parse($this->date_from)->startOfDay(),
            Carbon::parse($this->date_to)->endOfDay()
        ]);

        // Période précédente pour comparaison
        $daysDiff = Carbon::parse($this->date_from)->diffInDays(Carbon::parse($this->date_to)) + 1;
        $previousFrom = Carbon::parse($this->date_from)->subDays($daysDiff);
        $previousTo = Carbon::parse($this->date_from)->subDay();

        $previousPeriod = Transaction::whereBetween('created_at', [
            $previousFrom->startOfDay(),
            $previousTo->endOfDay()
        ]);

        $currentTotal = (clone $currentPeriod)->sum('total');
        $previousTotal = (clone $previousPeriod)->sum('total');

        $percentChange = $previousTotal > 0
            ? round((($currentTotal - $previousTotal) / $previousTotal) * 100, 1)
            : ($currentTotal > 0 ? 100 : 0);

        return [
            'current_total' => $currentTotal,
            'previous_total' => $previousTotal,
            'percent_change' => $percentChange,
            'transactions_count' => (clone $currentPeriod)->count(),
            'average_ticket' => (clone $currentPeriod)->count() > 0
                ? $currentTotal / (clone $currentPeriod)->count()
                : 0,
        ];
    }

    // Données pour le graphique des revenus journaliers
    public function getDailyRevenueChartProperty(): array
    {
        $data = Transaction::selectRaw('DATE(created_at) as date, SUM(total) as total, COUNT(*) as count')
            ->whereBetween('created_at', [
                Carbon::parse($this->date_from)->startOfDay(),
                Carbon::parse($this->date_to)->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $revenues = [];
        $transactions = [];

        // Remplir tous les jours de la période
        $current = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);

        while ($current <= $end) {
            $dateStr = $current->toDateString();
            $dayData = $data->firstWhere('date', $dateStr);

            $labels[] = $current->format('d/m');
            $revenues[] = $dayData ? (float)$dayData->total : 0;
            $transactions[] = $dayData ? (int)$dayData->count : 0;

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'revenues' => $revenues,
            'transactions' => $transactions,
        ];
    }

    // Statistiques de fréquentation clients
    public function getClientStatsProperty(): array
    {
        $dateFrom = Carbon::parse($this->date_from)->startOfDay();
        $dateTo = Carbon::parse($this->date_to)->endOfDay();

        // Clients uniques sur la période
        $uniqueClients = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('client_id')
            ->distinct('client_id')
            ->count('client_id');

        // Nouveaux clients (créés sur la période)
        $newClients = Client::whereBetween('created_at', [$dateFrom, $dateTo])->count();

        // Clients fidèles (plus de 2 visites sur la période)
        $loyalClients = DB::table('transactions')
            ->select('client_id')
            ->whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('client_id')
            ->groupBy('client_id')
            ->havingRaw('COUNT(*) >= 2')
            ->get()
            ->count();

        // Visites totales
        $totalVisits = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNotNull('client_id')
            ->count();

        // Clients de passage (sans compte)
        $walkInClients = Transaction::whereBetween('created_at', [$dateFrom, $dateTo])
            ->whereNull('client_id')
            ->count();

        return [
            'unique_clients' => $uniqueClients,
            'new_clients' => $newClients,
            'loyal_clients' => $loyalClients,
            'total_visits' => $totalVisits,
            'walk_in_clients' => $walkInClients,
            'average_visits' => $uniqueClients > 0 ? round($totalVisits / $uniqueClients, 1) : 0,
        ];
    }

    // Données pour le graphique de fréquentation
    public function getClientFrequencyChartProperty(): array
    {
        $data = Transaction::selectRaw('DATE(created_at) as date, COUNT(DISTINCT client_id) as unique_clients, COUNT(*) as total_visits')
            ->whereBetween('created_at', [
                Carbon::parse($this->date_from)->startOfDay(),
                Carbon::parse($this->date_to)->endOfDay()
            ])
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = [];
        $uniqueClients = [];
        $totalVisits = [];

        $current = Carbon::parse($this->date_from);
        $end = Carbon::parse($this->date_to);

        while ($current <= $end) {
            $dateStr = $current->toDateString();
            $dayData = $data->firstWhere('date', $dateStr);

            $labels[] = $current->format('d/m');
            $uniqueClients[] = $dayData ? (int)$dayData->unique_clients : 0;
            $totalVisits[] = $dayData ? (int)$dayData->total_visits : 0;

            $current->addDay();
        }

        return [
            'labels' => $labels,
            'unique_clients' => $uniqueClients,
            'total_visits' => $totalVisits,
        ];
    }

    // Top services
    public function getTopServicesProperty()
    {
        return TransactionItem::selectRaw('service_id, COUNT(*) as count, SUM(line_total) as revenue')
            ->whereNotNull('service_id')
            ->whereHas('transaction', function ($q) {
                $q->whereBetween('created_at', [
                    Carbon::parse($this->date_from)->startOfDay(),
                    Carbon::parse($this->date_to)->endOfDay()
                ]);
            })
            ->groupBy('service_id')
            ->orderByDesc('count')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->service = Service::find($item->service_id);
                return $item;
            });
    }

    // Top produits
    public function getTopProductsProperty()
    {
        return TransactionItem::selectRaw('product_id, SUM(quantity) as qty, SUM(line_total) as revenue')
            ->whereNotNull('product_id')
            ->whereHas('transaction', function ($q) {
                $q->whereBetween('created_at', [
                    Carbon::parse($this->date_from)->startOfDay(),
                    Carbon::parse($this->date_to)->endOfDay()
                ]);
            })
            ->groupBy('product_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->get()
            ->map(function ($item) {
                $item->product = Product::find($item->product_id);
                return $item;
            });
    }

    // Solde de caisse
    public function getCashBalanceProperty(): float
    {
        $entries = CashMovement::where('type', 'entry')->sum('amount');
        $exits = CashMovement::where('type', 'exit')->sum('amount');
        return $entries - $exits;
    }

    // Transactions récentes
    public function getRecentTransactionsProperty()
    {
        return Transaction::with('client')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.dashboard', [
            'generalStats' => $this->generalStats,
            'revenueStats' => $this->revenueStats,
            'clientStats' => $this->clientStats,
            'dailyRevenueChart' => $this->dailyRevenueChart,
            'clientFrequencyChart' => $this->clientFrequencyChart,
            'topServices' => $this->topServices,
            'topProducts' => $this->topProducts,
            'cashBalance' => $this->cashBalance,
            'recentTransactions' => $this->recentTransactions,
        ])->layout('layouts.main', ['title' => 'Dashboard']);
    }
}

