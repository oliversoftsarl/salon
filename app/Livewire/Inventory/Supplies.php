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

    // Modal d'édition (Admin uniquement)
    public bool $showEditModal = false;
    public ?int $editingId = null;
    public ?int $edit_product_id = null;
    public int $edit_quantity_received = 1;
    public ?string $edit_received_at = null;
    public ?string $edit_supplier = null;
    public ?string $edit_unit_cost = null;
    public ?string $edit_notes = null;

    // Modal de suppression (Admin uniquement)
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public ?string $deletingInfo = null;

    public function openEditModal(int $id): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour modifier un approvisionnement.');
            return;
        }

        $supply = ProductSupply::with('product')->find($id);
        if (!$supply) {
            session()->flash('error', 'Approvisionnement non trouvé.');
            return;
        }

        $this->editingId = $supply->id;
        $this->edit_product_id = $supply->product_id;
        $this->edit_quantity_received = $supply->quantity_received;
        $this->edit_received_at = $supply->received_at->toDateString();
        $this->edit_supplier = $supply->supplier;
        $this->edit_unit_cost = $supply->unit_cost;
        $this->edit_notes = $supply->notes;
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingId = null;
        $this->edit_product_id = null;
        $this->edit_quantity_received = 1;
        $this->edit_received_at = null;
        $this->edit_supplier = null;
        $this->edit_unit_cost = null;
        $this->edit_notes = null;
    }

    public function updateSupply(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour modifier un approvisionnement.');
            return;
        }

        $this->validate([
            'edit_product_id' => ['required', 'exists:products,id'],
            'edit_quantity_received' => ['required', 'integer', 'min:1'],
            'edit_received_at' => ['required', 'date'],
            'edit_supplier' => ['nullable', 'string', 'max:255'],
            'edit_unit_cost' => ['nullable', 'numeric', 'min:0'],
            'edit_notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $supply = ProductSupply::findOrFail($this->editingId);
            $oldQuantity = $supply->quantity_received;
            $oldProductId = $supply->product_id;
            $newQuantity = $this->edit_quantity_received;

            // Annuler l'ancien approvisionnement (décrémenter le stock)
            if ($oldProductId) {
                Product::where('id', $oldProductId)->decrement('stock_quantity', $oldQuantity);
            }

            // Mettre à jour l'approvisionnement
            $supply->update([
                'product_id' => $this->edit_product_id,
                'quantity_received' => $newQuantity,
                'received_at' => $this->edit_received_at,
                'supplier' => $this->edit_supplier,
                'unit_cost' => $this->edit_unit_cost,
                'notes' => $this->edit_notes,
            ]);

            // Appliquer le nouveau approvisionnement
            Product::where('id', $this->edit_product_id)->increment('stock_quantity', $newQuantity);

            // Mettre à jour le mouvement de stock
            StockMovement::where('reference_id', $supply->id)
                ->where('reason', 'Approvisionnement')
                ->update([
                    'product_id' => $this->edit_product_id,
                    'qty_change' => $newQuantity,
                ]);

            DB::commit();

            $this->closeEditModal();
            session()->flash('success', 'Approvisionnement modifié avec succès.');

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    public function confirmDelete(int $id): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer un approvisionnement.');
            return;
        }

        $supply = ProductSupply::with('product')->find($id);
        if (!$supply) {
            session()->flash('error', 'Approvisionnement non trouvé.');
            return;
        }

        $this->deletingId = $supply->id;
        $this->deletingInfo = ($supply->product->name ?? 'Produit inconnu') . ' - ' . $supply->quantity_received . ' unité(s) (' . $supply->received_at->format('d/m/Y') . ')';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingInfo = null;
    }

    public function deleteSupply(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer un approvisionnement.');
            return;
        }

        try {
            DB::beginTransaction();

            $supply = ProductSupply::findOrFail($this->deletingId);

            // Annuler le stock ajouté
            Product::where('id', $supply->product_id)->decrement('stock_quantity', $supply->quantity_received);

            // Supprimer le mouvement de stock associé
            StockMovement::where('reference_id', $supply->id)
                ->where('reason', 'Approvisionnement')
                ->delete();

            // Supprimer l'approvisionnement
            $supply->delete();

            DB::commit();

            $this->closeDeleteModal();
            session()->flash('success', 'Approvisionnement supprimé et stock ajusté.');

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
