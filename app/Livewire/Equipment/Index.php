<?php

namespace App\Livewire\Equipment;

use App\Models\Equipment;
use App\Models\EquipmentMaintenance;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EquipmentExport;

class Index extends Component
{
    use WithPagination;

    // Filtres
    public string $search = '';
    public string $filterCategory = '';
    public string $filterSubCategory = '';
    public string $filterStatus = '';
    public string $filterCondition = '';

    // Formulaire équipement
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $name = '';
    public string $code = '';
    public string $category = '';
    public string $sub_category = '';
    public string $brand = '';
    public string $model = '';
    public string $serial_number = '';
    public ?string $purchase_date = null;
    public ?float $purchase_price = null;
    public ?int $lifespan_months = null;
    public string $supplier = '';
    public string $status = 'operational';
    public string $condition = 'good';
    public string $location = '';
    public ?string $warranty_expiry = null;
    public ?string $next_maintenance = null;
    public string $description = '';
    public string $notes = '';
    public ?int $assigned_to = null;

    // Formulaire maintenance
    public bool $showMaintenanceForm = false;
    public ?int $maintenanceEquipmentId = null;
    public string $maintenance_date = '';
    public string $maintenance_type = 'preventive';
    public string $performed_by = '';
    public float $maintenance_cost = 0;
    public string $maintenance_description = '';
    public string $parts_replaced = '';
    public ?string $maintenance_next_date = null;

    // Modal détails
    public bool $showDetails = false;
    public ?Equipment $selectedEquipment = null;

    // Modal suppression
    public bool $showDeleteModal = false;
    public ?int $deletingId = null;
    public ?string $deletingInfo = null;

    protected $queryString = ['search', 'filterCategory', 'filterStatus'];

    protected function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:equipment,code,' . $this->editingId,
            'category' => 'required|string',
            'sub_category' => 'nullable|string',
            'brand' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_number' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_price' => 'nullable|numeric|min:0',
            'lifespan_months' => 'nullable|integer|min:1|max:600',
            'supplier' => 'nullable|string|max:255',
            'status' => 'required|in:operational,maintenance,broken,retired',
            'condition' => 'required|in:new,good,fair,poor',
            'location' => 'nullable|string|max:255',
            'warranty_expiry' => 'nullable|date',
            'next_maintenance' => 'nullable|date',
            'description' => 'nullable|string',
            'notes' => 'nullable|string',
            'assigned_to' => 'nullable|exists:users,id',
        ];
    }

    public function mount(): void
    {
        $this->purchase_date = now()->format('Y-m-d');
        $this->maintenance_date = now()->format('Y-m-d');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function getStatsProperty(): array
    {
        $equipments = Equipment::all();

        return [
            'total' => $equipments->count(),
            'operational' => $equipments->where('status', 'operational')->count(),
            'maintenance' => $equipments->where('status', 'maintenance')->count(),
            'broken' => $equipments->where('status', 'broken')->count(),
            'needs_maintenance' => $equipments->where('next_maintenance', '<=', now())->where('status', 'operational')->count(),
            'needs_renewal' => $equipments->filter(fn($e) => $e->needs_renewal)->count(),
            'total_value' => $equipments->sum('purchase_price'),
        ];
    }

    public function updatedCategory(): void
    {
        // Réinitialiser la sous-catégorie quand la catégorie change
        $this->sub_category = '';
    }

    public function updatedFilterCategory(): void
    {
        // Réinitialiser le filtre de sous-catégorie quand la catégorie change
        $this->filterSubCategory = '';
        $this->resetPage();
    }

    public function getSubCategoriesProperty(): array
    {
        if (empty($this->category)) {
            return [];
        }
        return Equipment::$subCategoryLabels[$this->category] ?? [];
    }

    public function getFilterSubCategoriesProperty(): array
    {
        if (empty($this->filterCategory)) {
            return [];
        }
        return Equipment::$subCategoryLabels[$this->filterCategory] ?? [];
    }

    public function getEquipmentProperty()
    {
        return Equipment::query()
            ->with(['assignedUser'])
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('name', 'like', "%{$this->search}%")
                       ->orWhere('code', 'like', "%{$this->search}%")
                       ->orWhere('brand', 'like', "%{$this->search}%")
                       ->orWhere('serial_number', 'like', "%{$this->search}%");
                });
            })
            ->when($this->filterCategory, fn($q) => $q->where('category', $this->filterCategory))
            ->when($this->filterSubCategory, fn($q) => $q->where('sub_category', $this->filterSubCategory))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterCondition, fn($q) => $q->where('condition', $this->filterCondition))
            ->orderBy('sub_category')
            ->orderBy('category')
            ->orderBy('name')
            ->paginate(12);
    }

    public function getStaffListProperty()
    {
        return User::where('active', true)
            ->whereIn('role', ['staff', 'admin'])
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function openForm(?int $id = null): void
    {
        $this->resetValidation();
        $this->resetForm();

        if ($id) {
            $equipment = Equipment::findOrFail($id);
            $this->editingId = $equipment->id;
            $this->name = $equipment->name;
            $this->code = $equipment->code ?? '';
            $this->category = $equipment->category;
            $this->sub_category = $equipment->sub_category ?? '';
            $this->brand = $equipment->brand ?? '';
            $this->model = $equipment->model ?? '';
            $this->serial_number = $equipment->serial_number ?? '';
            $this->purchase_date = $equipment->purchase_date?->format('Y-m-d');
            $this->purchase_price = $equipment->purchase_price;
            $this->lifespan_months = $equipment->lifespan_months;
            $this->supplier = $equipment->supplier ?? '';
            $this->status = $equipment->status;
            $this->condition = $equipment->condition;
            $this->location = $equipment->location ?? '';
            $this->warranty_expiry = $equipment->warranty_expiry?->format('Y-m-d');
            $this->next_maintenance = $equipment->next_maintenance?->format('Y-m-d');
            $this->description = $equipment->description ?? '';
            $this->notes = $equipment->notes ?? '';
            $this->assigned_to = $equipment->assigned_to;
        }

        $this->showForm = true;
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->code = '';
        $this->category = '';
        $this->sub_category = '';
        $this->brand = '';
        $this->model = '';
        $this->serial_number = '';
        $this->purchase_date = now()->format('Y-m-d');
        $this->purchase_price = null;
        $this->lifespan_months = null;
        $this->supplier = '';
        $this->status = 'operational';
        $this->condition = 'good';
        $this->location = '';
        $this->warranty_expiry = null;
        $this->next_maintenance = null;
        $this->description = '';
        $this->notes = '';
        $this->assigned_to = null;
    }

    public function save(): void
    {
        $this->validate();

        $data = [
            'name' => $this->name,
            'code' => $this->code ?: null,
            'category' => $this->category,
            'sub_category' => $this->sub_category ?: null,
            'brand' => $this->brand ?: null,
            'model' => $this->model ?: null,
            'serial_number' => $this->serial_number ?: null,
            'purchase_date' => $this->purchase_date ?: null,
            'purchase_price' => $this->purchase_price ?: null,
            'lifespan_months' => $this->lifespan_months ?: null,
            'supplier' => $this->supplier ?: null,
            'status' => $this->status,
            'condition' => $this->condition,
            'location' => $this->location ?: null,
            'warranty_expiry' => $this->warranty_expiry ?: null,
            'next_maintenance' => $this->next_maintenance ?: null,
            'description' => $this->description ?: null,
            'notes' => $this->notes ?: null,
            'assigned_to' => $this->assigned_to ?: null,
        ];

        if ($this->editingId) {
            Equipment::findOrFail($this->editingId)->update($data);
            session()->flash('success', 'Équipement modifié avec succès.');
        } else {
            $data['created_by'] = auth()->id();
            Equipment::create($data);
            session()->flash('success', 'Équipement ajouté avec succès.');
        }

        $this->closeForm();
    }

    public function showEquipmentDetails(int $id): void
    {
        $this->selectedEquipment = Equipment::with(['assignedUser', 'maintenances' => function ($q) {
            $q->orderByDesc('maintenance_date')->limit(10);
        }])->findOrFail($id);
        $this->showDetails = true;
    }

    public function closeDetails(): void
    {
        $this->showDetails = false;
        $this->selectedEquipment = null;
    }

    // Maintenance
    public function openMaintenanceForm(int $equipmentId): void
    {
        $this->maintenanceEquipmentId = $equipmentId;
        $this->maintenance_date = now()->format('Y-m-d');
        $this->maintenance_type = 'preventive';
        $this->performed_by = '';
        $this->maintenance_cost = 0;
        $this->maintenance_description = '';
        $this->parts_replaced = '';
        $this->maintenance_next_date = null;
        $this->showMaintenanceForm = true;
    }

    public function closeMaintenanceForm(): void
    {
        $this->showMaintenanceForm = false;
        $this->maintenanceEquipmentId = null;
    }

    public function saveMaintenance(): void
    {
        $this->validate([
            'maintenance_date' => 'required|date',
            'maintenance_type' => 'required|string',
            'performed_by' => 'nullable|string|max:255',
            'maintenance_cost' => 'nullable|numeric|min:0',
            'maintenance_description' => 'nullable|string',
            'parts_replaced' => 'nullable|string',
            'maintenance_next_date' => 'nullable|date|after:maintenance_date',
        ]);

        DB::beginTransaction();
        try {
            EquipmentMaintenance::create([
                'equipment_id' => $this->maintenanceEquipmentId,
                'maintenance_date' => $this->maintenance_date,
                'type' => $this->maintenance_type,
                'performed_by' => $this->performed_by ?: null,
                'cost' => $this->maintenance_cost ?: 0,
                'description' => $this->maintenance_description ?: null,
                'parts_replaced' => $this->parts_replaced ?: null,
                'next_maintenance' => $this->maintenance_next_date ?: null,
                'created_by' => auth()->id(),
            ]);

            // Mettre à jour l'équipement
            $equipment = Equipment::find($this->maintenanceEquipmentId);
            if ($equipment) {
                $equipment->update([
                    'last_maintenance' => $this->maintenance_date,
                    'next_maintenance' => $this->maintenance_next_date ?: null,
                    'status' => 'operational', // Remettre en service après maintenance
                ]);
            }

            DB::commit();
            session()->flash('success', 'Maintenance enregistrée avec succès.');
            $this->closeMaintenanceForm();

        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur: ' . $e->getMessage());
        }
    }

    // Suppression (Admin uniquement)
    public function confirmDelete(int $id): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer un équipement.');
            return;
        }

        $equipment = Equipment::find($id);
        if (!$equipment) {
            session()->flash('error', 'Équipement non trouvé.');
            return;
        }

        $this->deletingId = $equipment->id;
        $this->deletingInfo = $equipment->name . ($equipment->code ? ' (' . $equipment->code . ')' : '');
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingId = null;
        $this->deletingInfo = null;
    }

    public function delete(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer un équipement.');
            return;
        }

        Equipment::findOrFail($this->deletingId)->delete();
        session()->flash('success', 'Équipement supprimé avec succès.');
        $this->closeDeleteModal();
    }

    public function changeStatus(int $id, string $status): void
    {
        $equipment = Equipment::findOrFail($id);
        $equipment->update(['status' => $status]);
        session()->flash('success', 'Statut mis à jour.');
    }

    protected function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }

    public function exportExcel()
    {
        return Excel::download(new EquipmentExport(
            $this->filterCategory,
            $this->filterSubCategory,
            $this->filterStatus,
            $this->filterCondition
        ), 'equipements_' . now()->format('Y-m-d_His') . '.xlsx');
    }

    public function render()
    {
        return view('livewire.equipment.index', [
            'equipmentList' => $this->equipment,
            'stats' => $this->stats,
            'staffList' => $this->staffList,
            'categoryLabels' => Equipment::$categoryLabels,
            'subCategoryLabels' => Equipment::$subCategoryLabels,
            'subCategories' => $this->subCategories,
            'filterSubCategories' => $this->filterSubCategories,
            'statusLabels' => Equipment::$statusLabels,
            'statusColors' => Equipment::$statusColors,
            'conditionLabels' => Equipment::$conditionLabels,
            'maintenanceTypeLabels' => EquipmentMaintenance::$typeLabels,
        ])->layout('layouts.main', ['title' => 'Gestion des Équipements']);
    }
}
