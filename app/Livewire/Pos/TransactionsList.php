<?php

namespace App\Livewire\Pos;

use App\Models\TransactionItem;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsList extends Component
{
    use WithPagination;

    public string $filter_type = 'all'; // all|products|services
    public string $tx_type = 'all';     // all|sale|refund
    public ?string $date_from = null;
    public ?string $date_to = null;
    public string $search = '';
    public ?int $stylist_id = null;     // Filtre par prestataire

    protected $queryString = [
        'filter_type' => ['except' => 'all'],
        'tx_type'     => ['except' => 'all'],
        'date_from'   => ['except' => null],
        'date_to'     => ['except' => null],
        'search'      => ['except' => ''],
        'stylist_id'  => ['except' => null],
        'page'        => ['except' => 1],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: Carbon::now()->subDays(7)->toDateString();
        $this->date_to   = $this->date_to   ?: Carbon::now()->toDateString();
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['filter_type','tx_type','date_from','date_to','search','stylist_id'], true)) {
            $this->resetPage();
        }
    }

    public function getStaffListProperty()
    {
        return User::whereIn('role', ['staff', 'admin'])
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function render()
    {
        $query = $this->buildQuery()->orderByDesc('id');

        $items = $query
            ->with(['transaction', 'product:id,name', 'service:id,name', 'stylist:id,name'])
            ->paginate(12);

        $pageTotal  = $items->getCollection()->sum('line_total');
        $grandTotal = $this->buildQuery()->sum('line_total');

        return view('livewire.pos.transactions-list', [
            'items'      => $items,
            'pageTotal'  => $pageTotal,
            'grandTotal' => $grandTotal,
        ])->layout('layouts.main', ['title' => 'Ventes']);
    }

    public function setPreset(string $preset): void
    {
        $today         = Carbon::now()->toDateString();
        $weekStart     = Carbon::now()->startOfWeek()->toDateString();
        $monthStart    = Carbon::now()->startOfMonth()->toDateString();

        match ($preset) {
            'today' => [$this->date_from, $this->date_to] = [$today, $today],
            'week'  => [$this->date_from, $this->date_to] = [$weekStart, $today],
            'month' => [$this->date_from, $this->date_to] = [$monthStart, $today],
            default => null,
        };
    }

    public function exportCsv()
    {
        $fileName = 'transactions_'.Carbon::now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            fputcsv($out, ['Date','Type txn','Référence','Article','Type article','PU','Qté','Total ligne'], ';');

            $this->buildQuery()
                ->with(['transaction:id,type,reference,created_at', 'product:id,name', 'service:id,name'])
                ->orderByDesc('id')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $it) {
                        $tx     = $it->transaction;
                        $isProd = !is_null($it->product_id);
                        $label  = $isProd
                            ? ($it->product->name ?? 'Produit #'.$it->product_id)
                            : ($it->service->name ?? 'Service #'.$it->service_id);

                        fputcsv($out, [
                            optional($tx)->created_at?->format('Y-m-d H:i:s'),
                            optional($tx)->type ?? '',
                            optional($tx)->reference ?? '',
                            $label,
                            $isProd ? 'Produit' : 'Service',
                            number_format((float)$it->unit_price, 2, ',', ' '),
                            (int)$it->quantity,
                            number_format((float)$it->line_total, 2, ',', ' '),
                        ], ';');
                    }
                });

            fclose($out);
        }, $fileName, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function buildQuery(): Builder
    {
        $from = $this->date_from ? Carbon::parse($this->date_from)->startOfDay() : null;
        $to   = $this->date_to   ? Carbon::parse($this->date_to)->endOfDay()   : null;

        return TransactionItem::query()
            // Filtre période et type de transaction sur la relation 'transaction'
            ->whereHas('transaction', function (Builder $t) use ($from, $to) {
                if ($from && $to) {
                    $t->whereBetween('created_at', [$from, $to]);
                } elseif ($from) {
                    $t->where('created_at', '>=', $from);
                } elseif ($to) {
                    $t->where('created_at', '<=', $to);
                }

                if ($this->tx_type !== 'all') {
                    // valeurs attendues: 'sale' ou 'refund'
                    $t->where('type', $this->tx_type);
                }
            })
            // Type d'article
            ->when($this->filter_type === 'products', fn($q) => $q->whereNotNull('product_id'))
            ->when($this->filter_type === 'services', fn($q) => $q->whereNotNull('service_id'))
            // Filtre par prestataire
            ->when($this->stylist_id, fn($q) => $q->where('stylist_id', $this->stylist_id))
            // Recherche
            ->when(trim($this->search) !== '', function ($q) {
                $term = "%{$this->search}%";
                $q->where(function ($qq) use ($term) {
                    $qq->whereHas('transaction', fn($tx) => $tx->where('reference', 'like', $term))
                       ->orWhereHas('product', fn($p) => $p->where('name', 'like', $term))
                       ->orWhereHas('service', fn($s) => $s->where('name', 'like', $term));
                });
            });
    }
}
