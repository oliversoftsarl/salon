<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Cartes statistiques --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Dettes</p>
                                <h5 class="font-weight-bolder mb-0 text-danger">
                                    {{ number_format($this->stats['total_pending'], 0, ',', ' ') }} FC
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Dettes en cours</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ $this->stats['total_debts'] }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-single-copy-04 text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">En retard</p>
                                <h5 class="font-weight-bolder mb-0 text-warning">
                                    {{ $this->stats['overdue_count'] }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-secondary shadow text-center border-radius-md">
                                <i class="ni ni-bell-55 text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Montant en retard</p>
                                <h5 class="font-weight-bolder mb-0 text-warning">
                                    {{ number_format($this->stats['overdue_amount'], 0, ',', ' ') }} FC
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-calendar-grid-58 text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres et bouton ajouter --}}
    <div class="card mb-4">
        <div class="card-header pb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <div class="flex-grow-1">
                    <h6 class="mb-0"><i class="ni ni-credit-card me-2"></i>Gestion des Dettes Staff</h6>
                </div>
                <div class="flex-shrink-0">
                    <button class="btn btn-primary mb-0 w-100 w-md-auto" wire:click="openDebtForm">
                        <i class="ni ni-fat-add me-1"></i> Nouvelle dette
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Rechercher</label>
                    <input type="text" class="form-control" placeholder="Nom, description..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Staff</label>
                    <select class="form-select" wire:model.live="filterStaff">
                        <option value="">— Tous —</option>
                        @foreach($this->staffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" wire:model.live="filterType">
                        <option value="">— Tous —</option>
                        @foreach($typeLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Statut</label>
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">— Tous —</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <div class="flex-fill">
                        <label class="form-label">Du</label>
                        <input type="date" class="form-control" wire:model.live="dateFrom">
                    </div>
                    <div class="flex-fill">
                        <label class="form-label">Au</label>
                        <input type="date" class="form-control" wire:model.live="dateTo">
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des dettes --}}
    <div class="card">
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Staff</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Montant</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Reste</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Date</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Échéance</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Statut</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($debts as $debt)
                        <tr class="{{ $debt->is_overdue ? 'table-warning' : '' }}">
                            <td class="ps-3">
                                <div class="d-flex flex-column">
                                    <span class="text-sm font-weight-bold">{{ $debt->user->name ?? '—' }}</span>
                                    <span class="text-xs text-secondary">{{ $debt->user->role ?? '' }}</span>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-{{ $debt->type === 'product_consumption' ? 'info' : ($debt->type === 'loan' ? 'primary' : 'secondary') }}">
                                    {{ $typeLabels[$debt->type] ?? $debt->type }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="text-sm text-truncate" style="max-width: 150px;" title="{{ $debt->description }}">
                                        {{ $debt->description ?: '—' }}
                                    </span>
                                    @if($debt->product)
                                        <span class="text-xs text-info">{{ $debt->product->name }} (x{{ $debt->quantity }})</span>
                                    @endif
                                </div>
                            </td>
                            <td class="text-end">
                                <span class="text-sm font-weight-bold">{{ number_format($debt->amount, 0, ',', ' ') }} FC</span>
                            </td>
                            <td class="text-end">
                                <span class="text-sm font-weight-bold text-{{ $debt->remaining_amount > 0 ? 'danger' : 'success' }}">
                                    {{ number_format($debt->remaining_amount, 0, ',', ' ') }} FC
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="text-xs">{{ $debt->debt_date->format('d/m/Y') }}</span>
                            </td>
                            <td class="text-center">
                                @if($debt->due_date)
                                    <span class="text-xs {{ $debt->is_overdue ? 'text-danger font-weight-bold' : '' }}">
                                        {{ $debt->due_date->format('d/m/Y') }}
                                        @if($debt->is_overdue)
                                            <i class="ni ni-bell-55 ms-1"></i>
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                <span class="badge bg-{{ $debt->status_color }}">
                                    {{ $statusLabels[$debt->status] ?? $debt->status }}
                                </span>
                            </td>
                            <td class="text-end pe-3">
                                <div class="btn-group">
                                    @if(auth()->user()->role === 'admin')
                                        @if($debt->status !== 'paid' && $debt->status !== 'cancelled')
                                            <button class="btn btn-sm btn-outline-warning px-2 py-1" wire:click="openDebtForm({{ $debt->id }})" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                        @endif
                                        @if($debt->paid_amount == 0 && $debt->status !== 'cancelled')
                                            <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="cancelDebt({{ $debt->id }})" onclick="return confirm('Annuler cette dette ?')" title="Annuler">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-4 text-muted">Aucune dette enregistrée</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $debts->links() }}
        </div>
    </div>

    {{-- Modal Formulaire Dette --}}
    @if($showDebtForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ni ni-credit-card me-2"></i>
                            {{ $editingDebtId ? 'Modifier la dette' : 'Nouvelle dette' }}
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeDebtForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Staff <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="user_id">
                                    <option value="">— Sélectionner —</option>
                                    @foreach($this->staffList as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }} ({{ $staff->role }})</option>
                                    @endforeach
                                </select>
                                @error('user_id') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type de dette <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model.live="type">
                                    @foreach($typeLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            @if($type === 'product_consumption')
                                <div class="col-md-6">
                                    <label class="form-label">Produit consommé</label>
                                    <select class="form-select" wire:model.live="product_id">
                                        <option value="">— Sélectionner —</option>
                                        @foreach($this->products as $product)
                                            <option value="{{ $product->id }}">{{ $product->name }} ({{ number_format($product->price, 0, ',', ' ') }} FC)</option>
                                        @endforeach
                                    </select>
                                    @error('product_id') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Quantité</label>
                                    <input type="number" class="form-control" min="1" wire:model.live="quantity">
                                    @error('quantity') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                            @endif

                            <div class="col-md-6">
                                <label class="form-label">Montant (FC) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control" wire:model="amount" {{ $type === 'product_consumption' && $product_id ? 'readonly' : '' }}>
                                @error('amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Description / Raison</label>
                                <input type="text" class="form-control" wire:model="description" placeholder="Ex: Prêt pour urgence familiale">
                                @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Date de la dette <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="debt_date">
                                @error('debt_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date d'échéance</label>
                                <input type="date" class="form-control" wire:model="due_date">
                                @error('due_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model="notes" placeholder="Notes supplémentaires..."></textarea>
                                @error('notes') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeDebtForm">Annuler</button>
                        <button type="button" class="btn btn-primary" wire:click="saveDebt">
                            <i class="ni ni-check-bold me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Paiement --}}
    @if($showPaymentForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ni ni-money-coins me-2"></i>Enregistrer un paiement
                        </h5>
                        <button type="button" class="btn-close" wire:click="closePaymentForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Montant à payer (FC) <span class="text-danger">*</span></label>
                                <input type="number" step="0.01" class="form-control form-control-lg" wire:model="paymentAmount">
                                @error('paymentAmount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Méthode de paiement</label>
                                <select class="form-select" wire:model="paymentMethod">
                                    @foreach($paymentMethodLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model="paymentNotes" placeholder="Notes sur ce paiement..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closePaymentForm">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="savePayment">
                            <i class="ni ni-check-bold me-1"></i> Enregistrer le paiement
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Détail --}}
    @if($showDebtDetail && $selectedDebt)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="ni ni-single-copy-04 me-2"></i>Détail de la dette
                        </h5>
                        <button type="button" class="btn-close" wire:click="closeDetail"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted text-xs">Staff</h6>
                                <p class="mb-0 font-weight-bold">{{ $selectedDebt->user->name ?? '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted text-xs">Type</h6>
                                <p class="mb-0">
                                    <span class="badge bg-{{ $selectedDebt->type === 'product_consumption' ? 'info' : 'primary' }}">
                                        {{ $selectedDebt->type_name }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted text-xs">Montant total</h6>
                                <p class="mb-0 font-weight-bold">{{ number_format($selectedDebt->amount, 0, ',', ' ') }} FC</p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted text-xs">Reste à payer</h6>
                                <p class="mb-0 font-weight-bold text-{{ $selectedDebt->remaining_amount > 0 ? 'danger' : 'success' }}">
                                    {{ number_format($selectedDebt->remaining_amount, 0, ',', ' ') }} FC
                                </p>
                            </div>
                            @if($selectedDebt->product)
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted text-xs">Produit</h6>
                                    <p class="mb-0">{{ $selectedDebt->product->name }} (x{{ $selectedDebt->quantity }})</p>
                                </div>
                            @endif
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted text-xs">Date de la dette</h6>
                                <p class="mb-0">{{ $selectedDebt->debt_date->format('d/m/Y') }}</p>
                            </div>
                            @if($selectedDebt->due_date)
                                <div class="col-md-6">
                                    <h6 class="text-uppercase text-muted text-xs">Échéance</h6>
                                    <p class="mb-0 {{ $selectedDebt->is_overdue ? 'text-danger' : '' }}">
                                        {{ $selectedDebt->due_date->format('d/m/Y') }}
                                        @if($selectedDebt->is_overdue)
                                            <span class="badge bg-danger">En retard</span>
                                        @endif
                                    </p>
                                </div>
                            @endif
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted text-xs">Description</h6>
                                <p class="mb-0">{{ $selectedDebt->description ?: '—' }}</p>
                            </div>
                            @if($selectedDebt->notes)
                                <div class="col-12">
                                    <h6 class="text-uppercase text-muted text-xs">Notes</h6>
                                    <p class="mb-0 text-sm">{{ $selectedDebt->notes }}</p>
                                </div>
                            @endif
                            <div class="col-12">
                                <h6 class="text-uppercase text-muted text-xs">Enregistré par</h6>
                                <p class="mb-0 text-sm">{{ $selectedDebt->creator->name ?? '—' }} le {{ $selectedDebt->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>

                        @if($selectedDebt->payments->count() > 0)
                            <hr class="my-4">
                            <h6 class="mb-3"><i class="ni ni-bullet-list-67 me-2"></i>Historique des paiements</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th class="text-xs">Date</th>
                                            <th class="text-xs">Montant</th>
                                            <th class="text-xs">Méthode</th>
                                            <th class="text-xs">Enregistré par</th>
                                            <th class="text-xs">Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedDebt->payments as $payment)
                                            <tr>
                                                <td class="text-sm">{{ $payment->payment_date->format('d/m/Y') }}</td>
                                                <td class="text-sm font-weight-bold text-success">{{ number_format($payment->amount, 0, ',', ' ') }} FC</td>
                                                <td class="text-sm">{{ $payment->payment_method_name }}</td>
                                                <td class="text-sm">{{ $payment->recorder->name ?? '—' }}</td>
                                                <td class="text-sm text-muted">{{ $payment->notes ?? '—' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeDetail">Fermer</button>
                        @if($selectedDebt->status !== 'paid' && $selectedDebt->status !== 'cancelled')
                            <button type="button" class="btn btn-success" wire:click="closeDetail(); openPaymentForm({{ $selectedDebt->id }})">
                                <i class="ni ni-money-coins me-1"></i> Enregistrer un paiement
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

