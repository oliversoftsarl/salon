<?php

namespace App\Livewire\Pos;

use App\Models\TransactionItem;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsList extends Component
{
    use WithPagination;

    public string $filter_type = 'all'; // all|products|services
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $search = '';

    protected $queryString = [
        'filter_type' => ['except' => 'all'],
        'date_from' => ['except' => null],
        'date_to' => ['except' => null],
        'search' => ['except' => ''],
        'page' => ['except' => 1],
    ];

    public function mount(): void
    {
        // Défaut: 7 derniers jours
        $this->date_from = $this->date_from ?: Carbon::now()->subDays(7)->toDateString();
        $this->date_to = $this->date_to ?: Carbon::now()->toDateString();
    }

    public function updating($name, $value): void
    {
        // Reset pagination à chaque changement de filtre/recherche
        if (in_array($name, ['filter_type','date_from','date_to','search'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $q = TransactionItem::query()
            ->with(['transaction', 'product:id,name', 'service:id,name'])
            ->whereHas('transaction', function ($t) {
                if ($this->date_from) {
                    $t->whereDate('created_at', '>=', Carbon::parse($this->date_from));
                }
                if ($this->date_to) {
                    $t->whereDate('created_at', '<=', Carbon::parse($this->date_to));
                }
            });

        if ($this->filter_type === 'products') {
            $q->whereNotNull('product_id');
        } elseif ($this->filter_type === 'services') {
            $q->whereNotNull('service_id');
        }

        if (trim($this->search) !== '') {
            $term = "%{$this->search}%";
            $q->where(function ($qq) use ($term) {
                $qq->whereHas('transaction', fn($tx) => $tx->where('reference', 'like', $term))
                   ->orWhereHas('product', fn($p) => $p->where('name', 'like', $term))
                   ->orWhereHas('service', fn($s) => $s->where('name', 'like', $term));
            });
        }

        $q->orderByDesc('id');

        $items = $q->paginate(12);

        // Résumé du total des lignes filtrées (page courante)
        $pageTotal = $items->getCollection()->sum('line_total');

        return view('livewire.pos.transactions-list', [
            'items' => $items,
            'pageTotal' => $pageTotal,
        ])->layout('layouts.main', ['title' => 'Ventes']);
    }

    public function setPreset(string $preset): void
    {
        // Raccourcis de période
        $today = Carbon::now()->toDateString();
        $thisWeekStart = Carbon::now()->startOfWeek()->toDateString();
        $thisMonthStart = Carbon::now()->startOfMonth()->toDateString();

        switch ($preset) {
            case 'today':
                $this->date_from = $today;
                $this->date_to = $today;
                break;
            case 'week':
                $this->date_from = $thisWeekStart;
                $this->date_to = $today;
                break;
            case 'month':
                $this->date_from = $thisMonthStart;
                $this->date_to = $today;
                break;
        }
    }
}
