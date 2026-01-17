<?php

namespace App\Livewire\Cash;

use App\Models\CashMovement;
use App\Models\StaffDebt;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Livewire\Component;
use Livewire\WithPagination;
use Carbon\Carbon;

class Register extends Component
{
    use WithPagination;

    // Filtres
    public string $date_from = '';
    public string $date_to = '';
    public string $period = 'today';
    public string $filter_type = 'all'; // all, entry, exit
    public string $filter_category = '';
    public string $search = '';

    // Formulaire nouveau mouvement
    public bool $showForm = false;
    public ?int $editingId = null;
    public string $form_type = 'exit';
    public string $form_category = 'expense';
    public string $form_date = '';
    public float $form_amount = 0;
    public string $form_description = '';
    public string $form_reference = '';
    public string $form_payment_method = 'cash';
    public ?int $form_user_id = null;
    public string $form_notes = '';

    protected function rules(): array
    {
        return [
            'form_type' => ['required', 'in:entry,exit'],
            'form_category' => ['required', 'string'],
            'form_date' => ['required', 'date'],
            'form_amount' => ['required', 'numeric', 'min:0.01'],
            'form_description' => ['required', 'string', 'max:255'],
            'form_reference' => ['nullable', 'string', 'max:100'],
            'form_payment_method' => ['required', 'in:cash,card,transfer,mobile_money,check'],
            'form_user_id' => ['nullable', 'exists:users,id'],
            'form_notes' => ['nullable', 'string'],
        ];
    }

    public function mount(): void
    {
        $this->date_from = now()->toDateString();
        $this->date_to = now()->toDateString();
        $this->form_date = now()->toDateString();
    }

    public function updatedPeriod($value): void
    {
        switch ($value) {
            case 'today':
                $this->date_from = now()->toDateString();
                $this->date_to = now()->toDateString();
                break;
            case 'yesterday':
                $this->date_from = now()->subDay()->toDateString();
                $this->date_to = now()->subDay()->toDateString();
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
        $this->resetPage();
    }

    public function updatedFormType($value): void
    {
        // Réinitialiser la catégorie selon le type
        if ($value === 'entry') {
            $this->form_category = 'sale';
        } else {
            $this->form_category = 'expense';
        }
    }

    public function getStatsProperty(): array
    {
        $query = CashMovement::query()
            ->whereBetween('date', [$this->date_from, $this->date_to]);

        $totalEntries = (clone $query)->where('type', 'entry')->sum('amount');
        $totalExits = (clone $query)->where('type', 'exit')->sum('amount');
        $balance = $totalEntries - $totalExits;

        // Stats par catégorie
        $entriesByCategory = (clone $query)->where('type', 'entry')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        $exitsByCategory = (clone $query)->where('type', 'exit')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->pluck('total', 'category')
            ->toArray();

        // Solde initial (avant la période)
        $initialBalance = CashMovement::where('date', '<', $this->date_from)
            ->selectRaw('SUM(CASE WHEN type = "entry" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        return [
            'initial_balance' => $initialBalance,
            'total_entries' => $totalEntries,
            'total_exits' => $totalExits,
            'balance' => $balance,
            'final_balance' => $initialBalance + $balance,
            'entries_by_category' => $entriesByCategory,
            'exits_by_category' => $exitsByCategory,
        ];
    }

    public function getMovementsProperty()
    {
        return CashMovement::query()
            ->with(['user', 'createdBy', 'transaction'])
            ->whereBetween('date', [$this->date_from, $this->date_to])
            ->when($this->filter_type !== 'all', fn($q) => $q->where('type', $this->filter_type))
            ->when($this->filter_category, fn($q) => $q->where('category', $this->filter_category))
            ->when($this->search, function ($q) {
                $q->where(function ($qq) {
                    $qq->where('description', 'like', "%{$this->search}%")
                       ->orWhere('reference', 'like', "%{$this->search}%");
                });
            })
            ->orderByDesc('date')
            ->orderByDesc('created_at')
            ->paginate(15);
    }

    public function getStaffListProperty()
    {
        return User::whereIn('role', ['staff', 'admin'])
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    public function openForm(string $type = 'exit'): void
    {
        $this->resetForm();
        $this->form_type = $type;
        $this->form_category = $type === 'entry' ? 'other_income' : 'expense';
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $movement = CashMovement::findOrFail($id);

        $this->editingId = $movement->id;
        $this->form_type = $movement->type;
        $this->form_category = $movement->category;
        $this->form_date = $movement->date->toDateString();
        $this->form_amount = $movement->amount;
        $this->form_description = $movement->description ?? '';
        $this->form_reference = $movement->reference ?? '';
        $this->form_payment_method = $movement->payment_method;
        $this->form_user_id = $movement->user_id;
        $this->form_notes = $movement->notes ?? '';

        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        // Validation supplémentaire pour avance sur salaire
        if ($this->form_category === 'salary_advance' && !$this->form_user_id) {
            $this->addError('form_user_id', 'Veuillez sélectionner un employé pour l\'avance sur salaire.');
            return;
        }

        $data = [
            'type' => $this->form_type,
            'category' => $this->form_category,
            'date' => $this->form_date,
            'amount' => $this->form_amount,
            'description' => $this->form_description,
            'reference' => $this->form_reference ?: null,
            'payment_method' => $this->form_payment_method,
            'user_id' => $this->form_category === 'salary_advance' ? $this->form_user_id : null,
            'notes' => $this->form_notes ?: null,
            'created_by' => auth()->id(),
        ];

        DB::beginTransaction();
        try {
            if ($this->editingId) {
                $movement = CashMovement::findOrFail($this->editingId);
                $oldCategory = $movement->category;
                $oldUserId = $movement->user_id;
                $oldAmount = $movement->amount;

                $movement->update($data);

                // Si c'était une avance sur salaire, mettre à jour la dette associée
                if ($oldCategory === 'salary_advance' && $oldUserId) {
                    $debt = StaffDebt::where('user_id', $oldUserId)
                        ->where('type', 'advance')
                        ->where('amount', $oldAmount)
                        ->where('description', 'like', '%Mouvement caisse #' . $this->editingId . '%')
                        ->first();

                    if ($debt) {
                        if ($this->form_category === 'salary_advance') {
                            // Mettre à jour la dette existante
                            $debt->update([
                                'user_id' => $this->form_user_id,
                                'amount' => $this->form_amount,
                                'debt_date' => $this->form_date,
                                'description' => $this->form_description . ' (Mouvement caisse #' . $movement->id . ')',
                            ]);
                        } else {
                            // Ce n'est plus une avance, annuler la dette si pas de paiement
                            if ($debt->paid_amount == 0) {
                                $debt->update(['status' => 'cancelled']);
                            }
                        }
                    }
                }

                session()->flash('success', 'Mouvement modifié avec succès.');
            } else {
                $movement = CashMovement::create($data);

                // Créer automatiquement une dette pour les avances sur salaire
                if ($this->form_category === 'salary_advance' && $this->form_user_id) {
                    StaffDebt::create([
                        'user_id' => $this->form_user_id,
                        'type' => 'advance',
                        'amount' => $this->form_amount,
                        'paid_amount' => 0,
                        'description' => $this->form_description . ' (Mouvement caisse #' . $movement->id . ')',
                        'debt_date' => $this->form_date,
                        'status' => 'pending',
                        'created_by' => auth()->id(),
                        'notes' => 'Créé automatiquement depuis la gestion de caisse',
                    ]);
                }

                session()->flash('success', 'Mouvement enregistré avec succès.');
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            session()->flash('error', 'Erreur lors de l\'enregistrement: ' . $e->getMessage());
            return;
        }

        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $movement = CashMovement::findOrFail($id);

        // Empêcher la suppression des mouvements liés aux ventes
        if ($movement->transaction_id) {
            session()->flash('error', 'Impossible de supprimer un mouvement lié à une vente.');
            return;
        }

        $movement->delete();
        session()->flash('success', 'Mouvement supprimé.');
    }

    public function resetForm(): void
    {
        $this->editingId = null;
        $this->form_type = 'exit';
        $this->form_category = 'expense';
        $this->form_date = now()->toDateString();
        $this->form_amount = 0;
        $this->form_description = '';
        $this->form_reference = '';
        $this->form_payment_method = 'cash';
        $this->form_user_id = null;
        $this->form_notes = '';
    }

    public function closeForm(): void
    {
        $this->showForm = false;
        $this->resetForm();
    }

    public function render()
    {
        return view('livewire.cash.register', [
            'movements' => $this->movements,
            'stats' => $this->stats,
            'staffList' => $this->staffList,
            'categoryLabels' => CashMovement::$categoryLabels,
            'paymentMethodLabels' => CashMovement::$paymentMethodLabels,
            'entryCategories' => CashMovement::$entryCategories,
            'exitCategories' => CashMovement::$exitCategories,
        ])->layout('layouts.main', ['title' => 'Gestion de Caisse']);
    }
}

