<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\ProductSupply;
use App\Models\StockMovement;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Supplies extends Component
{
    use WithPagination;

    // Form
    public ?int $product_id = null;
    public int $quantity_received = 1;
    public ?string $received_at = null;
    public ?string $supplier = null;
    public ?string $notes = null;
    public ?string $unit_cost = null; // string pour saisie, casté à decimal par le modèle

    // Filters
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?int $filter_product_id = null;
    public ?string $filter_supplier = null;

    protected function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity_received' => ['required', 'integer', 'min:1'],
            'received_at' => ['required', 'date'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'unit_cost' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->received_at = Carbon::now()->toDateString();
        $this->date_from = Carbon::now()->startOfMonth()->toDateString();
        $this->date_to   = Carbon::now()->toDateString();
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['date_from','date_to','filter_product_id','filter_supplier'], true)) {
            $this->resetPage();
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        DB::transaction(function () use ($data) {
            $supply = ProductSupply::create($data);

            // Incrémente le stock
            $p = Product::lockForUpdate()->findOrFail($data['product_id']);
            $inc = (int) $data['quantity_received'];
            $p->increment('stock_quantity', $inc);

            // Mouvement de stock positif (approvisionnement)
            StockMovement::create([
                'product_id'   => $p->id,
                'qty_change'   => $inc,
                'reason'       => 'Approvisionnement',
                'reference_id' => $supply->id,
            ]);
        });

        // Reset form
        $this->product_id = null;
        $this->quantity_received = 1;
        $this->received_at = Carbon::now()->toDateString();
        $this->supplier = null;
        $this->unit_cost = null;
        $this->notes = null;

        session()->flash('success', 'Approvisionnement enregistré.');
    }

    public function render()
    {
        $products = Product::orderBy('name')->get(['id','name','stock_quantity','low_stock_threshold']);

        $q = ProductSupply::query()
            ->with(['product:id,name,stock_quantity,low_stock_threshold'])
            ->when($this->date_from, fn($qr) => $qr->whereDate('received_at', '>=', Carbon::parse($this->date_from)))
            ->when($this->date_to, fn($qr) => $qr->whereDate('received_at', '<=', Carbon::parse($this->date_to)))
            ->when($this->filter_product_id, fn($qr) => $qr->where('product_id', $this->filter_product_id))
            ->when($this->filter_supplier, fn($qr) => $qr->where('supplier', 'like', '%'.$this->filter_supplier.'%'))
            ->orderByDesc('received_at')
            ->orderByDesc('id');

        $supplies = $q->paginate(12);

        // Somme de la page
        $pageTotalQty = $supplies->getCollection()->sum('quantity_received');

        return view('livewire.inventory.supplies', compact('products','supplies','pageTotalQty'))
            ->layout('layouts.main', ['title' => 'Approvisionnements']);
    }
}
