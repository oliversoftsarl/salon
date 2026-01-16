<?php

namespace App\Livewire\Staff;

use App\Models\Product;
use App\Models\StaffDebt;
use App\Models\StaffDebtPayment;
use App\Models\User;
use Livewire\Component;
use Livewire\WithPagination;

class Debts extends Component
{
    use WithPagination;

    // Filtres
    public string $search = '';
    public string $filterStatus = '';
    public string $filterType = '';
    public ?int $filterStaff = null;
    public string $dateFrom = '';
    public string $dateTo = '';

    // Formulaire dette
    public bool $showDebtForm = false;
    public ?int $editingDebtId = null;
    public ?int $user_id = null;
    public string $type = 'loan';
    public float $amount = 0;
    public string $description = '';
    public ?int $product_id = null;
    public ?int $quantity = null;
    public string $debt_date = '';
    public ?string $due_date = null;
    public string $notes = '';

    // Formulaire paiement
    public bool $showPaymentForm = false;
    public ?int $payingDebtId = null;
    public float $paymentAmount = 0;
    public string $paymentMethod = 'cash';
    public string $paymentNotes = '';

    // Détail dette
    public bool $showDebtDetail = false;
    public ?StaffDebt $selectedDebt = null;

    protected $queryString = ['search', 'filterStatus', 'filterType', 'filterStaff'];

    protected function rules(): array
    {
        return [
            'user_id' => 'required|exists:users,id',
            'type' => 'required|in:product_consumption,loan,advance,other',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
            'product_id' => 'nullable|exists:products,id',
            'quantity' => 'nullable|integer|min:1',
            'debt_date' => 'required|date',
            'due_date' => 'nullable|date|after_or_equal:debt_date',
            'notes' => 'nullable|string',
        ];
    }

    public function mount(): void
    {
        $this->debt_date = now()->format('Y-m-d');
    }

    public function updatedType($value): void
    {
        if ($value !== 'product_consumption') {
            $this->product_id = null;
            $this->quantity = null;
        }
    }

    public function updatedProductId($value): void
    {
        if ($value && $this->type === 'product_consumption') {
            $product = Product::find($value);
            if ($product) {
                $this->amount = $product->price * ($this->quantity ?? 1);
            }
        }
    }

    public function updatedQuantity($value): void
    {
        if ($this->product_id && $this->type === 'product_consumption') {
            $product = Product::find($this->product_id);
            if ($product) {
                $this->amount = $product->price * ($value ?? 1);
            }
        }
    }

    public function openDebtForm(?int $id = null): void
    {
        $this->resetValidation();

        if ($id) {
            $debt = StaffDebt::findOrFail($id);
            $this->editingDebtId = $debt->id;
            $this->user_id = $debt->user_id;
            $this->type = $debt->type;
            $this->amount = $debt->amount;
            $this->description = $debt->description ?? '';
            $this->product_id = $debt->product_id;
            $this->quantity = $debt->quantity;
            $this->debt_date = $debt->debt_date->format('Y-m-d');
            $this->due_date = $debt->due_date?->format('Y-m-d');
            $this->notes = $debt->notes ?? '';
        } else {
            $this->editingDebtId = null;
            $this->user_id = null;
            $this->type = 'loan';
            $this->amount = 0;
            $this->description = '';
            $this->product_id = null;
            $this->quantity = null;
            $this->debt_date = now()->format('Y-m-d');
            $this->due_date = null;
            $this->notes = '';
        }

        $this->showDebtForm = true;
    }

    public function closeDebtForm(): void
    {
        $this->showDebtForm = false;
        $this->editingDebtId = null;
    }

    public function saveDebt(): void
    {
        $this->validate();

        $data = [
            'user_id' => $this->user_id,
            'type' => $this->type,
            'amount' => $this->amount,
            'description' => $this->description ?: null,
            'product_id' => $this->type === 'product_consumption' ? $this->product_id : null,
            'quantity' => $this->type === 'product_consumption' ? $this->quantity : null,
            'debt_date' => $this->debt_date,
            'due_date' => $this->due_date ?: null,
            'notes' => $this->notes ?: null,
        ];

        if ($this->editingDebtId) {
            $debt = StaffDebt::findOrFail($this->editingDebtId);
            $debt->update($data);
            session()->flash('success', 'Dette modifiée avec succès.');
        } else {
            $data['created_by'] = auth()->id();
            $data['status'] = 'pending';
            StaffDebt::create($data);
            session()->flash('success', 'Dette enregistrée avec succès.');
        }

        $this->closeDebtForm();
    }

    public function openPaymentForm(int $debtId): void
    {
        $this->payingDebtId = $debtId;
        $debt = StaffDebt::findOrFail($debtId);
        $this->paymentAmount = $debt->remaining_amount;
        $this->paymentMethod = 'cash';
        $this->paymentNotes = '';
        $this->showPaymentForm = true;
    }

    public function closePaymentForm(): void
    {
        $this->showPaymentForm = false;
        $this->payingDebtId = null;
    }

    public function savePayment(): void
    {
        $this->validate([
            'paymentAmount' => 'required|numeric|min:0.01',
            'paymentMethod' => 'required|string',
        ]);

        $debt = StaffDebt::findOrFail($this->payingDebtId);

        if ($this->paymentAmount > $debt->remaining_amount) {
            $this->addError('paymentAmount', 'Le montant ne peut pas dépasser le reste à payer.');
            return;
        }

        $debt->addPayment(
            $this->paymentAmount,
            $this->paymentMethod,
            auth()->id(),
            $this->paymentNotes ?: null
        );

        session()->flash('success', 'Paiement enregistré avec succès.');
        $this->closePaymentForm();
    }

    public function showDetail(int $debtId): void
    {
        $this->selectedDebt = StaffDebt::with(['user', 'product', 'creator', 'payments.recorder'])->findOrFail($debtId);
        $this->showDebtDetail = true;
    }

    public function closeDetail(): void
    {
        $this->showDebtDetail = false;
        $this->selectedDebt = null;
    }

    public function cancelDebt(int $debtId): void
    {
        $debt = StaffDebt::findOrFail($debtId);

        if ($debt->paid_amount > 0) {
            session()->flash('error', 'Impossible d\'annuler une dette ayant déjà des paiements.');
            return;
        }

        $debt->update(['status' => 'cancelled']);
        session()->flash('success', 'Dette annulée.');
    }

    public function deleteDebt(int $debtId): void
    {
        $debt = StaffDebt::findOrFail($debtId);

        if ($debt->payments()->count() > 0) {
            session()->flash('error', 'Impossible de supprimer une dette ayant des paiements.');
            return;
        }

        $debt->delete();
        session()->flash('success', 'Dette supprimée.');
    }

    public function getStaffListProperty()
    {
        return User::where('active', true)
            ->whereIn('role', ['staff', 'cashier', 'admin'])
            ->orderBy('name')
            ->get();
    }

    public function getProductsProperty()
    {
        return Product::orderBy('name')->get();
    }

    public function getStatsProperty(): array
    {
        $query = StaffDebt::query();

        if ($this->filterStaff) {
            $query->where('user_id', $this->filterStaff);
        }

        return [
            'total_pending' => (clone $query)->pending()->sum(\DB::raw('amount - paid_amount')),
            'total_debts' => (clone $query)->pending()->count(),
            'overdue_count' => (clone $query)->overdue()->count(),
            'overdue_amount' => (clone $query)->overdue()->sum(\DB::raw('amount - paid_amount')),
        ];
    }

    public function render()
    {
        $query = StaffDebt::with(['user', 'product'])
            ->when($this->search, fn($q) => $q->whereHas('user', fn($u) => $u->where('name', 'like', "%{$this->search}%"))
                ->orWhere('description', 'like', "%{$this->search}%"))
            ->when($this->filterStatus, fn($q) => $q->where('status', $this->filterStatus))
            ->when($this->filterType, fn($q) => $q->where('type', $this->filterType))
            ->when($this->filterStaff, fn($q) => $q->where('user_id', $this->filterStaff))
            ->when($this->dateFrom, fn($q) => $q->whereDate('debt_date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($q) => $q->whereDate('debt_date', '<=', $this->dateTo))
            ->orderByDesc('debt_date')
            ->orderByDesc('created_at');

        return view('livewire.staff.debts', [
            'debts' => $query->paginate(15),
            'typeLabels' => StaffDebt::$typeLabels,
            'statusLabels' => StaffDebt::$statusLabels,
            'paymentMethodLabels' => StaffDebtPayment::$paymentMethodLabels,
        ])->layout('layouts.main', ['title' => 'Dettes Staff']);
    }
}

