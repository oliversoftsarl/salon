<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\ProductSupply;
use App\Models\ProductConsumption;
use App\Models\TransactionItem;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Carbon\Carbon;

class StockSheet extends Component
{
    public ?int $product_id = null;
    public string $date_from = '';
    public string $date_to = '';
    public string $period = 'month';

    // Données calculées
    public array $summary = [];
    public $movements = [];
    public $initialStock = 0;

    public function mount(): void
    {
        $this->date_from = now()->startOfMonth()->toDateString();
        $this->date_to = now()->toDateString();
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
        $this->loadStockSheet();
    }

    public function updatedDateFrom(): void
    {
        $this->period = 'custom';
        $this->loadStockSheet();
    }

    public function updatedDateTo(): void
    {
        $this->period = 'custom';
        $this->loadStockSheet();
    }

    public function updatedProductId(): void
    {
        $this->loadStockSheet();
    }

    public function getProductsProperty()
    {
        return Product::orderBy('name')->get(['id', 'name', 'sku', 'stock_quantity']);
    }

    public function getSelectedProductProperty()
    {
        if (!$this->product_id) {
            return null;
        }
        return Product::find($this->product_id);
    }

    public function loadStockSheet(): void
    {
        if (!$this->product_id) {
            $this->movements = collect();
            $this->summary = [];
            $this->initialStock = 0;
            return;
        }

        $dateFrom = Carbon::parse($this->date_from)->startOfDay();
        $dateTo = Carbon::parse($this->date_to)->endOfDay();

        // Calculer le stock initial (avant la période)
        $this->initialStock = $this->calculateStockBefore($this->product_id, $dateFrom);

        // Récupérer tous les mouvements de la période
        $this->movements = $this->getMovements($this->product_id, $dateFrom, $dateTo);

        // Calculer le résumé
        $this->summary = $this->calculateSummary();
    }

    private function calculateStockBefore(int $productId, Carbon $date): int
    {
        // Entrées avant la date (approvisionnements)
        $suppliesBefore = ProductSupply::where('product_id', $productId)
            ->where('received_at', '<', $date)
            ->sum('quantity_received');

        // Sorties avant la date (consommations)
        $consumptionsBefore = ProductConsumption::where('product_id', $productId)
            ->where('used_at', '<', $date)
            ->sum('quantity_used');

        // Sorties avant la date (ventes)
        $salesBefore = TransactionItem::where('product_id', $productId)
            ->whereHas('transaction', fn($q) => $q->where('created_at', '<', $date))
            ->sum('quantity');

        return (int)($suppliesBefore - $consumptionsBefore - $salesBefore);
    }

    private function getMovements(int $productId, Carbon $dateFrom, Carbon $dateTo)
    {
        $movements = collect();

        // 1. Approvisionnements (entrées)
        $supplies = ProductSupply::where('product_id', $productId)
            ->whereBetween('received_at', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->received_at,
                    'type' => 'entry',
                    'label' => 'Approvisionnement',
                    'description' => $item->supplier ? "Fournisseur: {$item->supplier}" : ($item->notes ?? ''),
                    'entry' => $item->quantity_received,
                    'exit' => 0,
                    'unit_cost' => $item->unit_cost,
                    'reference' => "APPRO-{$item->id}",
                ];
            });
        $movements = $movements->merge($supplies);

        // 2. Consommations (sorties)
        $consumptions = ProductConsumption::with('staff')
            ->where('product_id', $productId)
            ->whereBetween('used_at', [$dateFrom->toDateString(), $dateTo->toDateString()])
            ->get()
            ->map(function ($item) {
                $staffName = $item->staff->name ?? 'N/A';
                return [
                    'date' => $item->used_at,
                    'type' => 'exit',
                    'label' => 'Consommation',
                    'description' => "Staff: {$staffName}" . ($item->notes ? " - {$item->notes}" : ''),
                    'entry' => 0,
                    'exit' => $item->quantity_used,
                    'unit_cost' => null,
                    'reference' => "CONSO-{$item->id}",
                ];
            });
        $movements = $movements->merge($consumptions);

        // 3. Ventes (sorties)
        $sales = TransactionItem::with('transaction')
            ->where('product_id', $productId)
            ->whereHas('transaction', function ($q) use ($dateFrom, $dateTo) {
                $q->whereBetween('created_at', [$dateFrom, $dateTo]);
            })
            ->get()
            ->map(function ($item) {
                return [
                    'date' => $item->transaction->created_at,
                    'type' => 'exit',
                    'label' => 'Vente',
                    'description' => "Réf: {$item->transaction->reference}",
                    'entry' => 0,
                    'exit' => $item->quantity,
                    'unit_cost' => $item->unit_price,
                    'reference' => $item->transaction->reference,
                ];
            });
        $movements = $movements->merge($sales);

        // Trier par date
        return $movements->sortBy('date')->values();
    }

    private function calculateSummary(): array
    {
        $totalEntries = $this->movements->sum('entry');
        $totalExits = $this->movements->sum('exit');
        $finalStock = $this->initialStock + $totalEntries - $totalExits;

        // Valeur des entrées (avec coût unitaire)
        $entryValue = $this->movements
            ->where('type', 'entry')
            ->sum(fn($m) => ($m['entry'] ?? 0) * ($m['unit_cost'] ?? 0));

        // Valeur des sorties ventes
        $salesValue = $this->movements
            ->where('label', 'Vente')
            ->sum(fn($m) => ($m['exit'] ?? 0) * ($m['unit_cost'] ?? 0));

        // Compteurs par type
        $suppliesCount = $this->movements->where('label', 'Approvisionnement')->count();
        $consumptionsCount = $this->movements->where('label', 'Consommation')->count();
        $salesCount = $this->movements->where('label', 'Vente')->count();

        return [
            'initial_stock' => $this->initialStock,
            'total_entries' => $totalEntries,
            'total_exits' => $totalExits,
            'final_stock' => $finalStock,
            'entry_value' => $entryValue,
            'sales_value' => $salesValue,
            'supplies_count' => $suppliesCount,
            'consumptions_count' => $consumptionsCount,
            'sales_count' => $salesCount,
            'total_movements' => $this->movements->count(),
        ];
    }

    public function exportPdf()
    {
        // Redirection vers une route qui génère le PDF
        if (!$this->product_id) {
            session()->flash('error', 'Veuillez sélectionner un produit.');
            return;
        }

        return redirect()->route('inventory.stock-sheet.pdf', [
            'product' => $this->product_id,
            'from' => $this->date_from,
            'to' => $this->date_to,
        ]);
    }

    public function render()
    {
        $this->loadStockSheet();

        return view('livewire.inventory.stock-sheet', [
            'products' => $this->products,
            'selectedProduct' => $this->selectedProduct,
        ])->layout('layouts.main', ['title' => 'Fiche de Stock']);
    }
}

