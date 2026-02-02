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

    // Modal d'édition (Admin uniquement)
    public bool $showEditModal = false;
    public ?int $editingId = null;
    public ?int $edit_product_id = null;
    public int $edit_quantity_used = 1;
    public ?int $edit_staff_id = null;
    public ?string $edit_used_at = null;
    public ?string $edit_notes = null;

    // Modal de suppression (Admin uniquement)
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public ?string $deletingInfo = null;

    public function openEditModal(int $id): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour modifier une consommation.');
            return;
        }

        $consumption = ProductConsumption::with('product')->find($id);
        if (!$consumption) {
            session()->flash('error', 'Consommation non trouvée.');
            return;
        }

        $this->editingId = $consumption->id;
        $this->edit_product_id = $consumption->product_id;
        $this->edit_quantity_used = $consumption->quantity_used;
        $this->edit_staff_id = $consumption->staff_id;
        $this->edit_used_at = $consumption->used_at->toDateString();
        $this->edit_notes = $consumption->notes;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingId = null;
        $this->edit_product_id = null;
        $this->edit_quantity_used = 1;
        $this->edit_staff_id = null;
        $this->edit_used_at = null;
        $this->edit_notes = null;
    }

    public function updateConsumption(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour modifier une consommation.');
            return;
        }

        $this->validate([
            'edit_product_id' => ['required', 'exists:products,id'],
            'edit_quantity_used' => ['required', 'integer', 'min:1'],
            'edit_staff_id' => ['nullable', 'exists:users,id'],
            'edit_used_at' => ['required', 'date'],
            'edit_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $consumption = ProductConsumption::findOrFail($this->editingId);
            $oldQuantity = $consumption->quantity_used;
            $oldProductId = $consumption->product_id;
            $newQuantity = $this->edit_quantity_used;

            // Restaurer le stock de l'ancien produit
            if ($oldProductId) {
                Product::where('id', $oldProductId)->increment('stock_quantity', $oldQuantity);
            }

            // Vérifier le stock disponible du nouveau produit
            $newProduct = Product::lockForUpdate()->findOrFail($this->edit_product_id);
            $available = max(0, (int)$newProduct->stock_quantity);
            if ($newQuantity > $available) {
                DB::rollBack();
                $this->addError('edit_quantity_used', 'Stock insuffisant. Disponible: '.$available);
                return;
            }

            // Mettre à jour la consommation
            $consumption->update([
                'product_id' => $this->edit_product_id,
                'quantity_used' => $newQuantity,
                'staff_id' => $this->edit_staff_id,
                'used_at' => $this->edit_used_at,
                'notes' => $this->edit_notes,
            ]);

            // Décrémenter le stock du nouveau produit
            $newProduct->decrement('stock_quantity', $newQuantity);

            // Mettre à jour le mouvement de stock
            StockMovement::where('reference_id', $consumption->id)
                ->where('reason', 'Consommation')
                ->update([
                    'product_id' => $this->edit_product_id,
                    'qty_change' => -$newQuantity,
                ]);

            DB::commit();

            $this->closeEditModal();
            session()->flash('success', 'Consommation modifiée avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer une consommation.');
            return;
        }

        $consumption = ProductConsumption::with('product')->find($id);
        if (!$consumption) {
            session()->flash('error', 'Consommation non trouvée.');
            return;
        }

        $this->deletingId = $consumption->id;
        $this->deletingInfo = ($consumption->product->name ?? 'Produit inconnu') . ' - ' . $consumption->quantity_used . ' unité(s) (' . $consumption->used_at->format('d/m/Y') . ')';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingInfo = null;
    }

    public function deleteConsumption(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer une consommation.');
            return;
        }

        try {
            DB::beginTransaction();

            $consumption = ProductConsumption::findOrFail($this->deletingId);

            // Restaurer le stock
            Product::where('id', $consumption->product_id)->increment('stock_quantity', $consumption->quantity_used);

            // Supprimer le mouvement de stock associé
            StockMovement::where('reference_id', $consumption->id)
                ->where('reason', 'Consommation')
                ->delete();

            // Supprimer la consommation
            $consumption->delete();

            DB::commit();

            $this->closeDeleteModal();
            session()->flash('success', 'Consommation supprimée et stock restauré.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    protected function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }

    public function render()
    {
        // Ne montrer que les produits qui peuvent être consommés (consumption ou both)
        $products = Product::whereIn('category', ['consumption', 'both'])
            ->orderBy('name')
            ->get(['id','name','is_consumable','low_stock_threshold','stock_quantity','category']);
        $staff = User::orderBy('name')->get(['id','name']);

        $q = ProductConsumption::query()
            ->with(['product:id,name,stock_quantity,low_stock_threshold,is_consumable,category', 'staff:id,name'])
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
