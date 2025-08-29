<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Models\ProductConsumption;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;

class Consumptions extends Component
{
    use WithPagination;

    // Form
    public ?int $product_id = null;
    public int $quantity_used = 1;
    public ?int $staff_id = null;
    public ?string $used_at = null;
    public ?string $notes = null;

    // Filters
    public ?string $date_from = null;
    public ?string $date_to = null;
    public ?int $filter_product_id = null;
    public ?int $filter_staff_id = null;

    protected function rules(): array
    {
        return [
            'product_id' => ['required', 'exists:products,id'],
            'quantity_used' => ['required', 'integer', 'min:1'],
            'staff_id' => ['nullable', 'exists:users,id'],
            'used_at' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->used_at = Carbon::now()->toDateString();
        $this->date_from = Carbon::now()->startOfMonth()->toDateString();
        $this->date_to   = Carbon::now()->toDateString();
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['date_from','date_to','filter_product_id','filter_staff_id'], true)) {
            $this->resetPage();
        }
    }

    public function save(): void
    {
        $data = $this->validate();

        $p = Product::lockForUpdate()->findOrFail($data['product_id']);
        $available = max(0, (int)$p->stock_quantity);
        if ($data['quantity_used'] > $available) {
            $this->addError('quantity_used', 'Stock insuffisant. Disponible: '.$available);
            return;
        }

        DB::transaction(function () use ($data, $p) {
            $cons = ProductConsumption::create($data);

            $p->decrement('stock_quantity', $data['quantity_used']);

            StockMovement::create([
                'product_id'   => $p->id,
                'qty_change'   => -$data['quantity_used'],
                'reason'       => 'Consommation',
                'reference_id' => $cons->id,
            ]);
        });

        $this->product_id = null;
        $this->quantity_used = 1;
        $this->staff_id = null;
        $this->used_at = Carbon::now()->toDateString();
        $this->notes = null;

        session()->flash('success', 'Consommation enregistrée.');
    }

    public function render()
    {
        $products = Product::orderBy('name')->get(['id','name','is_consumable','low_stock_threshold','stock_quantity']);
        $staff = User::orderBy('name')->get(['id','name']);

        $q = ProductConsumption::query()
            ->with(['product:id,name,stock_quantity,low_stock_threshold,is_consumable', 'staff:id,name'])
            ->when($this->date_from, fn($qr) => $qr->whereDate('used_at', '>=', Carbon::parse($this->date_from)))
            ->when($this->date_to, fn($qr) => $qr->whereDate('used_at', '<=', Carbon::parse($this->date_to)))
            ->when($this->filter_product_id, fn($qr) => $qr->where('product_id', $this->filter_product_id))
            ->when($this->filter_staff_id, fn($qr) => $qr->where('staff_id', $this->filter_staff_id))
            ->orderByDesc('used_at')
            ->orderByDesc('id');

        $consumptions = $q->paginate(12);

        return view('livewire.inventory.consumptions', compact('products','staff','consumptions'))
            ->layout('layouts.main', ['title' => 'Consommations']);
    }
}
