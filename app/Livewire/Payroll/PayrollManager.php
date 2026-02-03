<?php

namespace App\Livewire\Payroll;

use App\Models\CashMovement;
use App\Models\Setting;
use App\Models\StaffDebt;
use App\Models\StaffPayment;
use App\Models\StaffWeeklyRevenue;
use App\Models\User;
use Carbon\Carbon;
use Livewire\Component;
use Livewire\WithPagination;

class PayrollManager extends Component
{
    use WithPagination;

    public string $search = '';
    public string $filterType = ''; // weekly, monthly
    public string $filterPeriod = '';

    // Modal de paiement
    public bool $showPaymentModal = false;
    public $selectedStaffId = null;
    public array $selectedStaff = [];
    public string $paymentType = 'weekly';
    public string $periodStart = '';
    public string $periodEnd = '';
    public float $baseSalary = 0;
    public float $bonus = 0;
    public float $deductDebts = 0;
    public float $deductShortage = 0;
    public float $netAmount = 0;
    public string $paymentMethod = 'cash';
    public string $notes = '';

    // Données du staff sélectionné
    public array $staffDebts = [];
    public array $staffShortages = [];
    public float $totalDebts = 0;
    public float $totalShortage = 0;  // Cumul des manquants précédents
    public float $weeklyTarget = 0;    // Seuil de la semaine en cours
    public float $totalToDeduct = 0;   // Total disponible à déduire (cumul + seuil si applicable)
    public array $selectedDebtsToDeduct = [];

    // Modal de détails
    public bool $showDetailsModal = false;
    public ?StaffPayment $selectedPayment = null;

    protected $queryString = ['search', 'filterType', 'filterPeriod'];

    public function mount(): void
    {
        $this->periodStart = now()->startOfWeek()->toDateString();
        $this->periodEnd = now()->endOfWeek()->toDateString();
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedStaffId($value): void
    {
        // Convertir en int ou null
        $this->selectedStaffId = $value ? (int) $value : null;

        if ($this->selectedStaffId) {
            $this->loadStaffData();
        } else {
            $this->resetStaffData();
        }
    }

    public function updatedPaymentType(): void
    {
        $this->updatePeriodDates();
        if ($this->selectedStaffId) {
            $this->loadStaffData();
        }
    }

    public function updatedPeriodStart(): void
    {
        if ($this->paymentType === 'weekly') {
            $start = Carbon::parse($this->periodStart)->startOfWeek();
            $this->periodStart = $start->toDateString();
            $this->periodEnd = $start->copy()->endOfWeek()->toDateString();
        } else {
            $start = Carbon::parse($this->periodStart)->startOfMonth();
            $this->periodStart = $start->toDateString();
            $this->periodEnd = $start->copy()->endOfMonth()->toDateString();
        }

        if ($this->selectedStaffId) {
            $this->loadStaffShortages();
            // Recalculer le chiffre d'affaires si c'est un coiffeur/barbier
            $this->recalculateBaseSalaryIfNeeded();
        }
    }

    public function updatedBaseSalary(): void
    {
        $this->calculateNetAmount();
    }

    public function updatedBonus(): void
    {
        $this->calculateNetAmount();
    }

    public function updatedDeductDebts(): void
    {
        $this->calculateNetAmount();
    }

    public function updatedDeductShortage(): void
    {
        $this->calculateNetAmount();
    }

    protected function updatePeriodDates(): void
    {
        if ($this->paymentType === 'weekly') {
            $this->periodStart = now()->startOfWeek()->toDateString();
            $this->periodEnd = now()->endOfWeek()->toDateString();
        } else {
            $this->periodStart = now()->startOfMonth()->toDateString();
            $this->periodEnd = now()->endOfMonth()->toDateString();
        }
    }

    /**
     * Calcule le chiffre d'affaires généré par le staff pendant la période sélectionnée
     */
    protected function calculateStaffRevenue(): float
    {
        if (!$this->selectedStaffId || !$this->periodStart || !$this->periodEnd) {
            return 0;
        }

        // Calculer le total des services effectués par ce staff (stylist_id)
        $revenue = \App\Models\TransactionItem::where('stylist_id', $this->selectedStaffId)
            ->whereHas('transaction', function ($query) {
                $query->whereDate('created_at', '>=', $this->periodStart)
                      ->whereDate('created_at', '<=', $this->periodEnd);
            })
            ->sum('line_total');

        return (float) $revenue;
    }

    /**
     * Recalcule le salaire brut si nécessaire (pour les coiffeurs/barbiers)
     */
    protected function recalculateBaseSalaryIfNeeded(): void
    {
        if (!$this->selectedStaffId) {
            return;
        }

        $staff = User::with('staffProfile')->find($this->selectedStaffId);

        if (!$staff) {
            return;
        }

        $roleTitle = strtolower($staff->staffProfile?->role_title ?? '');
        $isWeeklyPaid = str_contains($roleTitle, 'coiffeur') ||
                        str_contains($roleTitle, 'coiffeuse') ||
                        str_contains($roleTitle, 'barbier');

        if ($isWeeklyPaid) {
            $this->baseSalary = $this->calculateStaffRevenue();
            $this->calculateNetAmount();
        }
    }

    protected function loadStaffData(): void
    {
        $staff = User::with('staffProfile')->find($this->selectedStaffId);

        if (!$staff) {
            $this->resetStaffData();
            return;
        }

        $this->selectedStaff = [
            'id' => $staff->id,
            'name' => $staff->name,
            'role' => $staff->staffProfile?->role_title ?? 'Non défini',
            'hourly_rate' => $staff->staffProfile?->hourly_rate ?? 0,
        ];

        // Déterminer le type de paiement en fonction du rôle
        $roleTitle = strtolower($staff->staffProfile?->role_title ?? '');
        $isWeeklyPaid = str_contains($roleTitle, 'coiffeur') ||
                        str_contains($roleTitle, 'coiffeuse') ||
                        str_contains($roleTitle, 'barbier');

        $this->paymentType = $isWeeklyPaid ? 'weekly' : 'monthly';
        $this->updatePeriodDates();

        // Charger les dettes
        $this->loadStaffDebts();

        // Charger les manquants (seulement pour les coiffeurs/barbiers)
        $this->loadStaffShortages();

        // Définir le salaire de base
        // Pour les coiffeurs/barbiers, c'est le chiffre d'affaires généré pendant la période
        if ($isWeeklyPaid) {
            $this->baseSalary = $this->calculateStaffRevenue();
        } else {
            // Pour les autres staff, utiliser le taux horaire comme salaire de base
            $this->baseSalary = $staff->staffProfile?->hourly_rate ?? 0;
        }

        $this->calculateNetAmount();
    }

    protected function loadStaffDebts(): void
    {
        // Réinitialiser les données
        $this->staffDebts = [];
        $this->totalDebts = 0;
        $this->selectedDebtsToDeduct = [];
        $this->deductDebts = 0;

        if (!$this->selectedStaffId) {
            return;
        }

        $debts = StaffDebt::where('user_id', (int) $this->selectedStaffId)
            ->whereIn('status', ['pending', 'partial'])
            ->orderBy('debt_date')
            ->get();

        $this->staffDebts = $debts->map(function ($debt) {
            return [
                'id' => $debt->id,
                'type' => $debt->type,
                'type_label' => StaffDebt::$typeLabels[$debt->type] ?? $debt->type,
                'amount' => (float) $debt->amount,
                'paid_amount' => (float) $debt->paid_amount,
                'remaining' => (float) ($debt->amount - $debt->paid_amount),
                'date' => $debt->debt_date->format('d/m/Y'),
                'description' => $debt->description,
            ];
        })->toArray();

        $this->totalDebts = collect($this->staffDebts)->sum('remaining');
    }

    protected function loadStaffShortages(): void
    {
        // Réinitialiser les données
        $this->staffShortages = [];
        $this->totalShortage = 0;
        $this->weeklyTarget = 0;
        $this->totalToDeduct = 0;
        $this->deductShortage = 0;

        if (!$this->selectedStaffId) {
            return;
        }

        $staff = User::with('staffProfile')->find($this->selectedStaffId);

        if (!$staff) {
            return;
        }

        $roleTitle = strtolower($staff->staffProfile?->role_title ?? '');

        // Seulement pour les coiffeurs/barbiers
        if (!str_contains($roleTitle, 'coiffeur') &&
            !str_contains($roleTitle, 'coiffeuse') &&
            !str_contains($roleTitle, 'barbier')) {
            return;
        }

        // Obtenir le cumul des manquants précédents
        $this->totalShortage = StaffWeeklyRevenue::getTotalShortage((int) $this->selectedStaffId);

        // Obtenir le seuil hebdomadaire configuré
        $this->weeklyTarget = Setting::getWeeklyRevenueTarget();

        // Le total à déduire = cumul des manquants + seuil de la semaine
        // (le seuil de la semaine doit être déduit même si le coiffeur a dépassé)
        $this->totalToDeduct = $this->totalShortage + $this->weeklyTarget;
    }

    protected function resetStaffData(): void
    {
        $this->selectedStaff = [];
        $this->staffDebts = [];
        $this->staffShortages = [];
        $this->totalDebts = 0;
        $this->totalShortage = 0;
        $this->weeklyTarget = 0;
        $this->totalToDeduct = 0;
        $this->baseSalary = 0;
        $this->bonus = 0;
        $this->deductDebts = 0;
        $this->deductShortage = 0;
        $this->netAmount = 0;
        $this->selectedDebtsToDeduct = [];
    }

    protected function calculateNetAmount(): void
    {
        $this->netAmount = max(0, $this->baseSalary + $this->bonus - $this->deductDebts - $this->deductShortage);
    }

    public function toggleDebtSelection(int $debtId): void
    {
        if (in_array($debtId, $this->selectedDebtsToDeduct)) {
            $this->selectedDebtsToDeduct = array_diff($this->selectedDebtsToDeduct, [$debtId]);
        } else {
            $this->selectedDebtsToDeduct[] = $debtId;
        }

        // Recalculer le montant des dettes à déduire
        $this->deductDebts = collect($this->staffDebts)
            ->whereIn('id', $this->selectedDebtsToDeduct)
            ->sum('remaining');

        $this->calculateNetAmount();
    }

    public function selectAllDebts(): void
    {
        $this->selectedDebtsToDeduct = collect($this->staffDebts)->pluck('id')->toArray();
        $this->deductDebts = $this->totalDebts;
        $this->calculateNetAmount();
    }

    public function deselectAllDebts(): void
    {
        $this->selectedDebtsToDeduct = [];
        $this->deductDebts = 0;
        $this->calculateNetAmount();
    }

    public function applyFullShortageDeduction(): void
    {
        // Déduire le maximum possible (total disponible ou ce qui reste après les autres déductions)
        $this->deductShortage = min($this->totalToDeduct, $this->baseSalary + $this->bonus - $this->deductDebts);
        $this->calculateNetAmount();
    }

    public function openPaymentModal(): void
    {
        $this->resetStaffData();
        $this->selectedStaffId = null;
        $this->paymentType = 'weekly';
        $this->updatePeriodDates();
        $this->notes = '';
        $this->paymentMethod = 'cash';
        $this->showPaymentModal = true;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->resetStaffData();
    }

    public function processPayment(): void
    {
        $this->validate([
            'selectedStaffId' => 'required|exists:users,id',
            'baseSalary' => 'required|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'deductDebts' => 'nullable|numeric|min:0',
            'deductShortage' => 'nullable|numeric|min:0',
            'paymentMethod' => 'required|in:cash,transfer,mobile_money,check',
            'periodStart' => 'required|date',
            'periodEnd' => 'required|date|after_or_equal:periodStart',
        ]);

        // S'assurer que les valeurs numériques sont définies
        $this->bonus = $this->bonus ?: 0;
        $this->deductDebts = $this->deductDebts ?: 0;
        $this->deductShortage = $this->deductShortage ?: 0;
        $this->calculateNetAmount();

        // Générer l'identifiant de période
        $period = $this->paymentType === 'weekly'
            ? Carbon::parse($this->periodStart)->format('Y-\WW')
            : Carbon::parse($this->periodStart)->format('Y-m');

        // Vérifier si un paiement existe déjà pour cette période
        if (StaffPayment::existsForPeriod((int) $this->selectedStaffId, $period)) {
            session()->flash('error', 'Un paiement existe déjà pour cette période.');
            return;
        }

        // Vérifier le solde de la caisse
        $currentBalance = CashMovement::selectRaw('SUM(CASE WHEN type = "entry" THEN amount ELSE -amount END) as balance')
            ->value('balance') ?? 0;

        if ($this->netAmount > $currentBalance) {
            session()->flash('error', 'Solde de caisse insuffisant. Solde actuel: ' . number_format($currentBalance, 0, ',', ' ') . ' FC');
            return;
        }

        try {
            \DB::beginTransaction();

            $staff = User::find($this->selectedStaffId);

            if (!$staff) {
                throw new \Exception('Staff non trouvé');
            }

            // Créer le mouvement de caisse
            $cashMovement = CashMovement::create([
                'date' => now()->toDateString(),
                'type' => 'exit',
                'category' => 'salary_payment',
                'amount' => $this->netAmount,
                'description' => 'Paiement salaire ' . ($this->paymentType === 'weekly' ? 'hebdomadaire' : 'mensuel') . ' - ' . $staff->name,
                'reference' => 'SAL-' . now()->format('YmdHis'),
                'payment_method' => $this->paymentMethod,
                'user_id' => $this->selectedStaffId,
                'created_by' => auth()->id(),
                'notes' => $this->notes ?: null,
            ]);

            // Préparer les détails des dettes déduites
            $debtDetails = [];
            if (!empty($this->selectedDebtsToDeduct)) {
                foreach ($this->selectedDebtsToDeduct as $debtId) {
                    $debt = StaffDebt::find($debtId);
                    if ($debt) {
                        $remaining = $debt->amount - $debt->paid_amount;
                        $debtDetails[] = [
                            'debt_id' => $debt->id,
                            'type' => $debt->type,
                            'amount_deducted' => $remaining,
                            'description' => $debt->description,
                        ];

                        // Mettre à jour la dette
                        $debt->paid_amount = $debt->amount;
                        $debt->status = 'paid';
                        $debt->save();
                    }
                }
            }

            // Préparer les détails des manquants déduits
            $shortageDetails = null;
            if ($this->deductShortage > 0) {
                $shortageDetails = [
                    'amount_deducted' => $this->deductShortage,
                    'total_shortage_before' => $this->totalShortage,
                    'weekly_target' => $this->weeklyTarget,
                    'total_to_deduct' => $this->totalToDeduct,
                ];

                // Réduire le cumul des manquants dans la base de données
                // On ne réduit que le montant qui dépasse le seuil hebdomadaire (car le seuil de cette semaine est "payé")
                // Si deductShortage <= weeklyTarget : rien à réduire du cumul (on paie juste le seuil de la semaine)
                // Si deductShortage > weeklyTarget : on réduit le cumul de (deductShortage - weeklyTarget)
                $amountToReduceFromCumul = max(0, $this->deductShortage - $this->weeklyTarget);
                if ($amountToReduceFromCumul > 0 && $this->totalShortage > 0) {
                    StaffWeeklyRevenue::reduceShortage((int) $this->selectedStaffId, min($amountToReduceFromCumul, $this->totalShortage));
                }
            }

            // Créer le paiement
            StaffPayment::create([
                'user_id' => (int) $this->selectedStaffId,
                'payment_type' => $this->paymentType,
                'base_salary' => (float) $this->baseSalary,
                'bonus' => (float) $this->bonus,
                'deductions' => (float) $this->deductDebts,
                'shortage_deduction' => (float) $this->deductShortage,
                'net_amount' => (float) $this->netAmount,
                'period' => $period,
                'period_start' => $this->periodStart,
                'period_end' => $this->periodEnd,
                'payment_date' => now()->toDateString(),
                'payment_method' => $this->paymentMethod,
                'notes' => $this->notes ?: null,
                'debt_details' => !empty($debtDetails) ? $debtDetails : null,
                'shortage_details' => $shortageDetails,
                'cash_movement_id' => $cashMovement->id,
                'created_by' => auth()->id(),
            ]);

            \DB::commit();

            $this->closePaymentModal();
            session()->flash('success', 'Paiement de ' . number_format($this->netAmount, 0, ',', ' ') . ' FC effectué avec succès pour ' . $staff->name);

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Erreur lors du paiement: ' . $e->getMessage());
        }
    }

    public function showPaymentDetails(int $paymentId): void
    {
        $this->selectedPayment = StaffPayment::with(['user', 'user.staffProfile', 'cashMovement', 'creator'])->find($paymentId);
        $this->showDetailsModal = true;
    }

    public function closeDetailsModal(): void
    {
        $this->showDetailsModal = false;
        $this->selectedPayment = null;
    }

    // Modal d'édition
    public bool $showEditModal = false;
    public ?int $editingPaymentId = null;
    public float $editBaseSalary = 0;
    public float $editBonus = 0;
    public float $editDeductions = 0;
    public float $editShortageDeduction = 0;
    public string $editPaymentMethod = 'cash';
    public string $editNotes = '';

    // Modal de confirmation de suppression
    public bool $showDeleteModal = false;
    public ?int $deletingPaymentId = null;
    public ?string $deletingPaymentInfo = null;

    /**
     * Ouvre le modal d'édition d'un paiement (admin uniquement)
     */
    public function openEditModal(int $paymentId): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour modifier un paiement.');
            return;
        }

        $payment = StaffPayment::with('user')->find($paymentId);

        if (!$payment) {
            session()->flash('error', 'Paiement non trouvé.');
            return;
        }

        $this->editingPaymentId = $payment->id;
        $this->editBaseSalary = (float) $payment->base_salary;
        $this->editBonus = (float) $payment->bonus;
        $this->editDeductions = (float) $payment->deductions;
        $this->editShortageDeduction = (float) $payment->shortage_deduction;
        $this->editPaymentMethod = $payment->payment_method;
        $this->editNotes = $payment->notes ?? '';
        $this->showEditModal = true;
    }

    public function closeEditModal(): void
    {
        $this->showEditModal = false;
        $this->editingPaymentId = null;
        $this->editBaseSalary = 0;
        $this->editBonus = 0;
        $this->editDeductions = 0;
        $this->editShortageDeduction = 0;
        $this->editPaymentMethod = 'cash';
        $this->editNotes = '';
    }

    /**
     * Met à jour un paiement (admin uniquement)
     */
    public function updatePayment(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour modifier un paiement.');
            return;
        }

        $this->validate([
            'editBaseSalary' => 'required|numeric|min:0',
            'editBonus' => 'nullable|numeric|min:0',
            'editDeductions' => 'nullable|numeric|min:0',
            'editShortageDeduction' => 'nullable|numeric|min:0',
            'editPaymentMethod' => 'required|in:cash,transfer,mobile_money,check',
        ]);

        try {
            \DB::beginTransaction();

            $payment = StaffPayment::find($this->editingPaymentId);

            if (!$payment) {
                throw new \Exception('Paiement non trouvé');
            }

            $oldNetAmount = $payment->net_amount;
            $newNetAmount = max(0, $this->editBaseSalary + $this->editBonus - $this->editDeductions - $this->editShortageDeduction);
            $difference = $newNetAmount - $oldNetAmount;

            // Mettre à jour le paiement
            $payment->update([
                'base_salary' => $this->editBaseSalary,
                'bonus' => $this->editBonus ?: 0,
                'deductions' => $this->editDeductions ?: 0,
                'shortage_deduction' => $this->editShortageDeduction ?: 0,
                'net_amount' => $newNetAmount,
                'payment_method' => $this->editPaymentMethod,
                'notes' => $this->editNotes ?: null,
            ]);

            // Mettre à jour le mouvement de caisse associé si le montant a changé
            if ($payment->cash_movement_id && $difference != 0) {
                $cashMovement = CashMovement::find($payment->cash_movement_id);
                if ($cashMovement) {
                    $cashMovement->update([
                        'amount' => $newNetAmount,
                        'payment_method' => $this->editPaymentMethod,
                        'notes' => ($this->editNotes ? $this->editNotes . ' ' : '') . '(Modifié le ' . now()->format('d/m/Y H:i') . ')',
                    ]);
                }
            }

            \DB::commit();

            $this->closeEditModal();
            session()->flash('success', 'Paiement modifié avec succès.');

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    /**
     * Ouvre le modal de confirmation de suppression (admin uniquement)
     */
    public function confirmDelete(int $paymentId): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer un paiement.');
            return;
        }

        $payment = StaffPayment::with('user')->find($paymentId);

        if (!$payment) {
            session()->flash('error', 'Paiement non trouvé.');
            return;
        }

        $this->deletingPaymentId = $payment->id;
        $this->deletingPaymentInfo = $payment->user->name . ' - ' . number_format($payment->net_amount, 0, ',', ' ') . ' FC (' . $payment->payment_date->format('d/m/Y') . ')';
        $this->showDeleteModal = true;
    }

    public function closeDeleteModal(): void
    {
        $this->showDeleteModal = false;
        $this->deletingPaymentId = null;
        $this->deletingPaymentInfo = null;
    }

    /**
     * Supprime un paiement (admin uniquement)
     */
    public function deletePayment(): void
    {
        if (!$this->isAdmin()) {
            session()->flash('error', 'Vous n\'avez pas les droits pour supprimer un paiement.');
            return;
        }

        try {
            \DB::beginTransaction();

            $payment = StaffPayment::find($this->deletingPaymentId);

            if (!$payment) {
                throw new \Exception('Paiement non trouvé');
            }

            // Supprimer le mouvement de caisse associé
            if ($payment->cash_movement_id) {
                CashMovement::where('id', $payment->cash_movement_id)->delete();
            }

            // Restaurer les dettes si elles avaient été marquées comme payées
            if ($payment->debt_details) {
                $debtDetails = is_array($payment->debt_details) ? $payment->debt_details : json_decode($payment->debt_details, true);
                foreach ($debtDetails as $debtDetail) {
                    $debt = StaffDebt::find($debtDetail['debt_id'] ?? null);
                    if ($debt) {
                        $debt->paid_amount = max(0, $debt->paid_amount - ($debtDetail['amount_deducted'] ?? 0));
                        $debt->status = $debt->paid_amount >= $debt->amount ? 'paid' : ($debt->paid_amount > 0 ? 'partial' : 'pending');
                        $debt->save();
                    }
                }
            }

            // Supprimer le paiement
            $payment->delete();

            \DB::commit();

            $this->closeDeleteModal();
            session()->flash('success', 'Paiement supprimé avec succès. Le mouvement de caisse associé a également été supprimé.');

        } catch (\Exception $e) {
            \DB::rollBack();
            session()->flash('error', 'Erreur lors de la suppression: ' . $e->getMessage());
        }
    }

    /**
     * Vérifie si l'utilisateur actuel est un administrateur
     */
    protected function isAdmin(): bool
    {
        $user = auth()->user();
        return $user && $user->role === 'admin';
    }

    public function getStaffListProperty()
    {
        return User::whereHas('staffProfile')
            ->with('staffProfile')
            ->where('active', true)
            ->orderBy('name')
            ->get();
    }

    public function getPaymentsProperty()
    {
        return StaffPayment::with(['user', 'user.staffProfile', 'creator'])
            ->when($this->search, function ($query) {
                $query->whereHas('user', function ($q) {
                    $q->where('name', 'like', '%' . $this->search . '%');
                });
            })
            ->when($this->filterType, function ($query) {
                $query->where('payment_type', $this->filterType);
            })
            ->when($this->filterPeriod, function ($query) {
                $query->where('period', 'like', '%' . $this->filterPeriod . '%');
            })
            ->orderByDesc('payment_date')
            ->paginate(15);
    }

    public function getTotalPaidThisMonthProperty(): float
    {
        return StaffPayment::whereMonth('payment_date', now()->month)
            ->whereYear('payment_date', now()->year)
            ->sum('net_amount');
    }

    public function render()
    {
        return view('livewire.payroll.payroll-manager', [
            'staffList' => $this->staffList,
            'payments' => $this->payments,
            'totalPaidThisMonth' => $this->totalPaidThisMonth,
        ])->layout('layouts.main', ['title' => 'Gestion de la Paie']);
    }
}
