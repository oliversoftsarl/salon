<?php

namespace App\Livewire\Products;

use App\Models\Product;
use App\Models\StockMovement;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';
    public ?int $editingId = null;

    public string $name = '';
    public ?string $sku = null;
    public string $price = '0.00';
    public int $stock_quantity = 0;
    public bool $is_snack = true;

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sku' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'is_snack' => ['boolean'],
        ];
    }

    protected array $messages = [
        'name.required' => 'Le nom du produit est requis.',
        'name.max' => 'Le nom ne doit pas dépasser 255 caractères.',
        'sku.max' => 'Le SKU ne doit pas dépasser 255 caractères.',
        'price.required' => 'Le prix est requis.',
        'price.numeric' => 'Le prix doit être un nombre.',
        'price.min' => 'Le prix ne peut pas être négatif.',
        'stock_quantity.required' => 'Le stock est requis.',
        'stock_quantity.integer' => 'Le stock doit être un entier.',
        'stock_quantity.min' => 'Le stock ne peut pas être négatif.',
    ];

    public function updated(string $propertyName): void
    {
        $this->validateOnly($propertyName, $this->rules(), $this->messages);
    }

    public function render()
    {
        $products = Product::query()
            ->when($this->search, fn ($q) =>
            $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('sku', 'like', "%{$this->search}%")
            )
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.products.index', [
            'products' => $products,
        ])->layout('layouts.main', ['title' => 'Produits']);
    }

    public function create(): void
    {
        $this->resetForm();
        $this->editingId = 0;
    }

    public function edit(int $id): void
    {
        $p = Product::findOrFail($id);
        $this->editingId = $p->id;
        $this->name = $p->name;
        $this->sku = $p->sku;
        $this->price = (string) $p->price;
        $this->stock_quantity = (int) $p->stock_quantity;
        $this->is_snack = (bool) $p->is_snack;
    }

    public function save(): void
    {
        $data = $this->validate($this->rules(), $this->messages);

        if ($this->editingId && $this->editingId > 0) {
            $p = Product::findOrFail($this->editingId);
            $delta = $data['stock_quantity'] - (int)$p->stock_quantity;

            $p->update($data);

            if ($delta !== 0) {
                $this->recordStockMovement($p->id, $delta, $delta > 0 ? 'in' : 'out', 'Ajustement');
            }

            session()->flash('success', 'Produit mis à jour.');
        } else {
            $p = Product::create($data);
            if ($p->stock_quantity > 0) {
                $this->recordStockMovement($p->id, (int)$p->stock_quantity, 'in', 'Stock initial');
            }
            session()->flash('success', 'Produit créé.');
        }

        $this->resetForm();
        $this->editingId = null;
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        Product::findOrFail($id)->delete();
        session()->flash('success', 'Produit supprimé.');
        $this->resetPage();
    }

    public function addStock(int $id, int $qty): void
    {
        $this->validateOnly('stock_quantity', ['stock_quantity' => 'integer|min:0'], [
            'stock_quantity.integer' => 'La quantité doit être un entier.',
            'stock_quantity.min' => 'La quantité doit être positive.',
        ]);

        $p = Product::findOrFail($id);
        $qty = max(0, (int)$qty);
        if ($qty === 0) {
            $this->addError('stock_quantity', 'La quantité doit être supérieure à 0.');
            return;
        }
        $p->increment('stock_quantity', $qty);
        $this->recordStockMovement($p->id, $qty, 'in', 'Entrée manuelle');
        session()->flash('success', 'Stock ajouté.');
    }

    public function removeStock(int $id, int $qty): void
    {
        $this->validateOnly('stock_quantity', ['stock_quantity' => 'integer|min:0'], [
            'stock_quantity.integer' => 'La quantité doit être un entier.',
            'stock_quantity.min' => 'La quantité doit être positive.',
        ]);

        $p = Product::findOrFail($id);
        $qty = max(0, (int)$qty);
        if ($qty === 0) {
            $this->addError('stock_quantity', 'La quantité doit être supérieure à 0.');
            return;
        }
        $qty = min($qty, max(0, $p->stock_quantity));
        $p->decrement('stock_quantity', $qty);
        $this->recordStockMovement($p->id, $qty, 'out', 'Sortie manuelle');
        session()->flash('success', 'Stock décrémenté.');
    }

    private function recordStockMovement(int $productId, int $quantity, string $direction, string $reason): void
    {
        StockMovement::create([
            'product_id' => $productId,
            'quantity' => $quantity,
            'direction' => $direction,
            'reason' => $reason,
        ]);
    }

    private function resetForm(): void
    {
        $this->name = '';
        $this->sku = null;
        $this->price = '0.00';
        $this->stock_quantity = 0;
        $this->is_snack = true;
    }
}
