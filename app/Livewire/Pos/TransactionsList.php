<?php

namespace App\Livewire\Pos;

use App\Models\TransactionItem;
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

    protected $queryString = [
        'filter_type' => ['except' => 'all'],
        'tx_type'     => ['except' => 'all'],
        'date_from'   => ['except' => 'null'],
        'date_to'     => ['except' => 'null'],
        'search'      => ['except' => ''],
        'page'        => ['except' => '1'],
    ];

    public function mount(): void
    {
        $this->date_from = $this->date_from ?: Carbon::now()->subDays(7)->toDateString();
        $this->date_to   = $this->date_to   ?: Carbon::now()->toDateString();
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['filter_type','tx_type','date_from','date_to','search'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $query = $this->buildQuery()->orderByDesc('id');

        $items = $query->with(['transaction', 'product:id,name', 'service:id,name'])
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
        $today        = Carbon::now()->toDateString();
        $thisWeekFrom = Carbon::now()->startOfWeek()->toDateString();
        $thisMonthFrom= Carbon::now()->startOfMonth()->toDateString();

        switch ($preset) {
            case 'today':
                $this->date_from = $today;
                $this->date_to   = $today;
                break;
            case 'week':
                $this->date_from = $thisWeekFrom;
                $this->date_to   = $today;
                break;
            case 'month':
                $this->date_from = $thisMonthFrom;
                $this->date_to   = $today;
                break;
        }
    }

    public function exportCsv()
    {
        $fileName = 'transactions_'.Carbon::now()->format('Ymd_His').'.csv';

        return response()->streamDownload(function () {
            $out = fopen('php://output', 'w');

            // En-têtes CSV
            fputcsv($out, [
                'Date',
                'Type txn',
                'Référence',
                'Article',
                'Type article',
                'PU',
                'Qté',
                'Total ligne',
            ], ';');

            // On stream par chunks pour éviter la charge mémoire
            $this->buildQuery()
                ->with(['transaction:id,type,reference,created_at', 'product:id,name', 'service:id,name'])
                ->orderByDesc('id')
                ->chunk(500, function ($chunk) use ($out) {
                    foreach ($chunk as $it) {
                        $tx       = $it->transaction;
                        $isProd   = !is_null($it->product_id);
                        $label    = $isProd ? ($it->product->name ?? 'Produit #'.$it->product_id)
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
        }, $fileName, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    private function buildQuery(): Builder
    {
        return TransactionItem::query()
            ->whereHas('transaction', function ($t) {
                if ($this->date_from) {
                    $t->whereDate('created_at', '>=', Carbon::parse($this->date_from));
                }
                if ($this->date_to) {
                    $t->whereDate('created_at', '<=', Carbon::parse($this->date_to));
                }
                if ($this->tx_type !== 'all') {
                    $t->where('type', $this->tx_type); // 'sale' ou 'refund'
                }
            })
            ->when($this->filter_type === 'products', fn($q) => $q->whereNotNull('product_id'))
            ->when($this->filter_type === 'services', fn($q) => $q->whereNotNull('service_id'))
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
