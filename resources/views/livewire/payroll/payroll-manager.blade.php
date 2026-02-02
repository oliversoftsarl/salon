<div>
    {{-- Messages Flash --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ni ni-check-bold me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ni ni-fat-remove me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- En-tête avec statistiques --}}
    <div class="row mb-4">
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card bg-gradient-primary">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white text-sm mb-0 opacity-8">Payé ce mois</p>
                            <h4 class="text-white mb-0">{{ number_format($totalPaidThisMonth, 0, ',', ' ') }} FC</h4>
                        </div>
                        <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                            <i class="ni ni-money-coins text-primary text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card bg-gradient-success">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white text-sm mb-0 opacity-8">Staff actifs</p>
                            <h4 class="text-white mb-0">{{ $staffList->count() }}</h4>
                        </div>
                        <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                            <i class="ni ni-single-02 text-success text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4 col-md-6 mb-3">
            <div class="card bg-gradient-info">
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-white text-sm mb-0 opacity-8">Paiements ce mois</p>
                            <h4 class="text-white mb-0">{{ $payments->total() }}</h4>
                        </div>
                        <div class="icon icon-shape bg-white shadow text-center rounded-circle">
                            <i class="ni ni-paper-diploma text-info text-lg opacity-10"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Carte principale --}}
    <div class="card">
        <div class="card-header pb-0">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h5 class="mb-0"><i class="ni ni-credit-card me-2"></i>Gestion de la Paie</h5>
                </div>
                <div class="col-md-6 text-end">
                    <button class="btn btn-primary" wire:click="openPaymentModal">
                        <i class="ni ni-fat-add me-1"></i> Nouveau Paiement
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filtres --}}
            <div class="row mb-4 g-3">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                        <input type="text" class="form-control" placeholder="Rechercher un staff..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterType">
                        <option value="">Tous les types</option>
                        <option value="weekly">Hebdomadaire</option>
                        <option value="monthly">Mensuel</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <input type="month" class="form-control" wire:model.live="filterPeriod" placeholder="Période">
                </div>
            </div>

            {{-- Tableau des paiements --}}
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Staff</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Type</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Période</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Salaire Base</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Déductions</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Net Payé</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2 bg-gradient-primary rounded-circle">
                                            <span class="text-white text-xs">{{ substr($payment->user->name ?? '?', 0, 1) }}</span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 text-sm">{{ $payment->user->name ?? 'N/A' }}</h6>
                                            <p class="text-xs text-secondary mb-0">{{ $payment->user->staffProfile->role_title ?? '' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $payment->payment_type === 'weekly' ? 'info' : 'success' }}">
                                        {{ $payment->payment_type_label }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $payment->period_label }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-sm">{{ number_format($payment->base_salary + $payment->bonus, 0, ',', ' ') }} FC</span>
                                </td>
                                <td class="text-end">
                                    @if($payment->total_deductions > 0)
                                        <span class="text-sm text-danger">-{{ number_format($payment->total_deductions, 0, ',', ' ') }} FC</span>
                                    @else
                                        <span class="text-sm text-muted">0 FC</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="text-sm font-weight-bold text-success">{{ number_format($payment->net_amount, 0, ',', ' ') }} FC</span>
                                </td>
                                <td>
                                    <span class="text-sm">{{ $payment->payment_date->format('d/m/Y') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-info px-2 py-1" wire:click="showPaymentDetails({{ $payment->id }})" title="Détails">
                                            <i class="ni ni-zoom-split-in"></i>
                                        </button>
                                        @if(auth()->user()->role === 'admin')
                                            <button class="btn btn-sm btn-outline-warning px-2 py-1" wire:click="openEditModal({{ $payment->id }})" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="confirmDelete({{ $payment->id }})" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4">
                                    <div class="text-muted">
                                        <i class="ni ni-credit-card" style="font-size: 48px;"></i>
                                        <p class="mt-2">Aucun paiement trouvé</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                {{ $payments->links() }}
            </div>
        </div>
    </div>

    {{-- Modal de paiement --}}
    @if($showPaymentModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary">
                        <h5 class="modal-title text-white"><i class="ni ni-credit-card me-2"></i>Nouveau Paiement de Salaire</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePaymentModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            {{-- Colonne gauche: Sélection et paramètres --}}
                            <div class="col-lg-6">
                                {{-- Sélection du staff --}}
                                <div class="card mb-3">
                                    <div class="card-header pb-0">
                                        <h6 class="mb-0"><i class="ni ni-single-02 me-2"></i>Sélectionner le Staff</h6>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select form-select-lg" wire:model.live="selectedStaffId">
                                            <option value="">-- Choisir un employé --</option>
                                            @foreach($staffList as $staff)
                                                <option value="{{ $staff->id }}">
                                                    {{ $staff->name }} ({{ $staff->staffProfile->role_title ?? 'N/A' }})
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('selectedStaffId') <small class="text-danger">{{ $message }}</small> @enderror

                                        @if(!empty($selectedStaff))
                                            <div class="mt-3 p-3 bg-light rounded">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-lg me-3 bg-gradient-primary rounded-circle">
                                                        <span class="text-white">{{ substr($selectedStaff['name'], 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $selectedStaff['name'] }}</h6>
                                                        <span class="badge bg-gradient-success">{{ $selectedStaff['role'] }}</span>
                                                        <p class="text-sm text-muted mb-0 mt-1">
                                                            Type de paie: <strong>{{ $paymentType === 'weekly' ? 'Hebdomadaire' : 'Mensuel' }}</strong>
                                                        </p>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                {{-- Période et montants --}}
                                @if($selectedStaffId)
                                    <div class="card mb-3">
                                        <div class="card-header pb-0">
                                            <h6 class="mb-0"><i class="ni ni-calendar-grid-58 me-2"></i>Période et Montants</h6>
                                        </div>
                                        <div class="card-body">
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <label class="form-label">Début période</label>
                                                    <input type="date" class="form-control" wire:model.live="periodStart">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Fin période</label>
                                                    <input type="date" class="form-control" wire:model="periodEnd" readonly>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">
                                                        @if($paymentType === 'weekly')
                                                            Chiffre d'affaires (FC)
                                                            <i class="fas fa-info-circle text-muted ms-1" title="CA généré par ce staff pendant la période"></i>
                                                        @else
                                                            Salaire de base (FC)
                                                        @endif
                                                    </label>
                                                    <input type="number" class="form-control" wire:model.live="baseSalary" min="0" step="1000" @if($paymentType === 'weekly') readonly @endif>
                                                    @error('baseSalary') <small class="text-danger">{{ $message }}</small> @enderror
                                                    @if($paymentType === 'weekly')
                                                        <small class="text-muted">Calculé automatiquement selon les prestations</small>
                                                    @endif
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label">Bonus (FC)</label>
                                                    <input type="number" class="form-control" wire:model.live="bonus" min="0" step="1000">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Mode de paiement</label>
                                                    <select class="form-select" wire:model="paymentMethod">
                                                        <option value="cash">Espèces</option>
                                                        <option value="transfer">Virement</option>
                                                        <option value="mobile_money">Mobile Money</option>
                                                        <option value="check">Chèque</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label">Notes</label>
                                                    <textarea class="form-control" wire:model="notes" rows="2"></textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            {{-- Colonne droite: Dettes et Manquants --}}
                            <div class="col-lg-6">
                                @if($selectedStaffId)
                                    {{-- Dettes --}}
                                    <div class="card mb-3">
                                        <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                            <h6 class="mb-0"><i class="ni ni-notification-70 me-2 text-danger"></i>Dettes en cours</h6>
                                            @if(count($staffDebts) > 0)
                                                <div class="btn-group btn-group-sm">
                                                    <button class="btn btn-outline-primary btn-sm" wire:click="selectAllDebts">Tout</button>
                                                    <button class="btn btn-outline-secondary btn-sm" wire:click="deselectAllDebts">Aucun</button>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                            @if(count($staffDebts) > 0)
                                                @foreach($staffDebts as $debt)
                                                    <div class="form-check d-flex align-items-center justify-content-between py-2 border-bottom">
                                                        <div class="d-flex align-items-center">
                                                            <input class="form-check-input me-2" type="checkbox"
                                                                   wire:click="toggleDebtSelection({{ $debt['id'] }})"
                                                                   {{ in_array($debt['id'], $selectedDebtsToDeduct) ? 'checked' : '' }}>
                                                            <div>
                                                                <span class="text-sm font-weight-bold">{{ $debt['type_label'] }}</span>
                                                                <p class="text-xs text-muted mb-0">{{ $debt['description'] }} - {{ $debt['date'] }}</p>
                                                            </div>
                                                        </div>
                                                        <span class="badge bg-danger">{{ number_format($debt['remaining'], 0, ',', ' ') }} FC</span>
                                                    </div>
                                                @endforeach
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Total dettes:</strong>
                                                        <strong class="text-danger">{{ number_format($totalDebts, 0, ',', ' ') }} FC</strong>
                                                    </div>
                                                    <div class="d-flex justify-content-between mt-1">
                                                        <span>À déduire:</span>
                                                        <span class="text-danger">{{ number_format($deductDebts, 0, ',', ' ') }} FC</span>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="text-center text-muted py-3">
                                                    <i class="ni ni-check-bold text-success" style="font-size: 24px;"></i>
                                                    <p class="mb-0 mt-2">Aucune dette en cours</p>
                                                </div>
                                            @endif
                                        </div>
                                    </div>

                                    {{-- Manquants (seulement pour coiffeurs/barbiers) --}}
                                    @if(count($staffShortages) > 0 || $totalShortage > 0)
                                        <div class="card mb-3">
                                            <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                                                <h6 class="mb-0"><i class="ni ni-chart-bar-32 me-2 text-warning"></i>Manquants Recettes</h6>
                                                @if($totalShortage > 0)
                                                    <button class="btn btn-outline-warning btn-sm" wire:click="applyFullShortageDeduction">
                                                        Déduire tout
                                                    </button>
                                                @endif
                                            </div>
                                            <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                                @if(count($staffShortages) > 0)
                                                    @foreach($staffShortages as $shortage)
                                                        <div class="d-flex justify-content-between py-2 border-bottom">
                                                            <div>
                                                                <span class="text-sm">{{ $shortage['week'] }}</span>
                                                                <p class="text-xs text-muted mb-0">
                                                                    Objectif: {{ number_format($shortage['target'], 0, ',', ' ') }} FC |
                                                                    Réalisé: {{ number_format($shortage['actual'], 0, ',', ' ') }} FC
                                                                </p>
                                                            </div>
                                                            <span class="badge bg-warning text-dark">-{{ number_format($shortage['shortage'], 0, ',', ' ') }} FC</span>
                                                        </div>
                                                    @endforeach
                                                @endif
                                                <div class="mt-2 p-2 bg-light rounded">
                                                    <div class="d-flex justify-content-between">
                                                        <strong>Cumul manquants:</strong>
                                                        <strong class="text-warning">{{ number_format($totalShortage, 0, ',', ' ') }} FC</strong>
                                                    </div>
                                                    <div class="mt-2">
                                                        <label class="form-label text-sm">Montant à déduire:</label>
                                                        <input type="number" class="form-control form-control-sm"
                                                               wire:model.live="deductShortage" min="0" max="{{ $totalShortage }}" step="1000">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Récapitulatif --}}
                                    <div class="card bg-gradient-dark">
                                        <div class="card-body text-white">
                                            <h6 class="text-white mb-3"><i class="ni ni-money-coins me-2"></i>Récapitulatif</h6>
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>{{ $paymentType === 'weekly' ? "Chiffre d'affaires:" : "Salaire de base:" }}</span>
                                                <span>{{ number_format($baseSalary, 0, ',', ' ') }} FC</span>
                                            </div>
                                            @if($bonus > 0)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Bonus:</span>
                                                    <span class="text-success">+{{ number_format($bonus, 0, ',', ' ') }} FC</span>
                                                </div>
                                            @endif
                                            @if($deductDebts > 0)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Déduction dettes:</span>
                                                    <span class="text-danger">-{{ number_format($deductDebts, 0, ',', ' ') }} FC</span>
                                                </div>
                                            @endif
                                            @if($deductShortage > 0)
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span>Déduction manquants:</span>
                                                    <span class="text-warning">-{{ number_format($deductShortage, 0, ',', ' ') }} FC</span>
                                                </div>
                                            @endif
                                            <hr class="bg-white opacity-5">
                                            <div class="d-flex justify-content-between">
                                                <strong class="h5 text-white mb-0">MONTANT NET:</strong>
                                                <strong class="h5 text-white mb-0">{{ number_format($netAmount, 0, ',', ' ') }} FC</strong>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePaymentModal">Annuler</button>
                        @if($selectedStaffId)
                            <button type="button" class="btn btn-success" wire:click="processPayment" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="processPayment">
                                    <i class="ni ni-check-bold me-1"></i> Confirmer le Paiement ({{ number_format($netAmount, 0, ',', ' ') }} FC)
                                </span>
                                <span wire:loading wire:target="processPayment">
                                    <span class="spinner-border spinner-border-sm me-1"></span> Traitement...
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal détails paiement --}}
    @if($showDetailsModal && $selectedPayment)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-info">
                        <h5 class="modal-title text-white"><i class="ni ni-paper-diploma me-2"></i>Détails du Paiement</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetailsModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted mb-3">Informations Générales</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-muted">Staff:</td>
                                        <td class="font-weight-bold">{{ $selectedPayment->user->name ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Fonction:</td>
                                        <td>{{ $selectedPayment->user->staffProfile->role_title ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Type:</td>
                                        <td><span class="badge bg-{{ $selectedPayment->payment_type === 'weekly' ? 'info' : 'success' }}">{{ $selectedPayment->payment_type_label }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Période:</td>
                                        <td>{{ $selectedPayment->period_label }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Date paiement:</td>
                                        <td>{{ $selectedPayment->payment_date->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Mode:</td>
                                        <td>{{ $selectedPayment->payment_method_label }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Payé par:</td>
                                        <td>{{ $selectedPayment->creator->name ?? 'N/A' }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-uppercase text-muted mb-3">Détail Financier</h6>
                                <div class="card bg-light">
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Salaire de base:</span>
                                            <span>{{ number_format($selectedPayment->base_salary, 0, ',', ' ') }} FC</span>
                                        </div>
                                        @if($selectedPayment->bonus > 0)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Bonus:</span>
                                                <span class="text-success">+{{ number_format($selectedPayment->bonus, 0, ',', ' ') }} FC</span>
                                            </div>
                                        @endif
                                        @if($selectedPayment->deductions > 0)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Déduction dettes:</span>
                                                <span class="text-danger">-{{ number_format($selectedPayment->deductions, 0, ',', ' ') }} FC</span>
                                            </div>
                                        @endif
                                        @if($selectedPayment->shortage_deduction > 0)
                                            <div class="d-flex justify-content-between mb-2">
                                                <span>Déduction manquants:</span>
                                                <span class="text-warning">-{{ number_format($selectedPayment->shortage_deduction, 0, ',', ' ') }} FC</span>
                                            </div>
                                        @endif
                                        <hr>
                                        <div class="d-flex justify-content-between">
                                            <strong>MONTANT NET:</strong>
                                            <strong class="text-success">{{ number_format($selectedPayment->net_amount, 0, ',', ' ') }} FC</strong>
                                        </div>
                                    </div>
                                </div>

                                @if($selectedPayment->notes)
                                    <div class="mt-3">
                                        <h6 class="text-muted">Notes:</h6>
                                        <p class="text-sm">{{ $selectedPayment->notes }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Détails des dettes déduites --}}
                        @if(!empty($selectedPayment->debt_details))
                            <hr>
                            <h6 class="text-uppercase text-muted mb-3">Dettes Déduites</h6>
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th class="text-end">Montant</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($selectedPayment->debt_details as $debt)
                                            <tr>
                                                <td>{{ \App\Models\StaffDebt::$typeLabels[$debt['type']] ?? $debt['type'] }}</td>
                                                <td>{{ $debt['description'] ?? '-' }}</td>
                                                <td class="text-end text-danger">{{ number_format($debt['amount_deducted'], 0, ',', ' ') }} FC</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetailsModal">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal d'édition (Admin uniquement) --}}
    @if($showEditModal && auth()->user()->role === 'admin')
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-warning">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-edit me-2"></i>Modifier le paiement
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-warning mb-3">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            <strong>Attention :</strong> La modification de ce paiement affectera également le mouvement de caisse associé.
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Salaire de base / Chiffre d'affaires (FC)</label>
                                <input type="number" class="form-control" wire:model.live="editBaseSalary" min="0" step="1000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Bonus (FC)</label>
                                <input type="number" class="form-control" wire:model.live="editBonus" min="0" step="1000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Déductions dettes (FC)</label>
                                <input type="number" class="form-control" wire:model.live="editDeductions" min="0" step="1000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Déductions manquants (FC)</label>
                                <input type="number" class="form-control" wire:model.live="editShortageDeduction" min="0" step="1000">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mode de paiement</label>
                                <select class="form-select" wire:model="editPaymentMethod">
                                    <option value="cash">Espèces</option>
                                    <option value="transfer">Virement</option>
                                    <option value="mobile_money">Mobile Money</option>
                                    <option value="check">Chèque</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Montant net calculé</label>
                                <div class="form-control bg-light text-success font-weight-bold">
                                    {{ number_format(max(0, $editBaseSalary + $editBonus - $editDeductions - $editShortageDeduction), 0, ',', ' ') }} FC
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" wire:model="editNotes" rows="2"></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">Annuler</button>
                        <button type="button" class="btn btn-warning" wire:click="updatePayment">
                            <i class="fas fa-save me-2"></i>Enregistrer les modifications
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal de confirmation de suppression (Admin uniquement) --}}
    @if($showDeleteModal && auth()->user()->role === 'admin')
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-danger">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-trash me-2"></i>Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 64px;"></i>
                        </div>
                        <h5>Êtes-vous sûr de vouloir supprimer ce paiement ?</h5>
                        <p class="text-muted mb-3">{{ $deletingPaymentInfo }}</p>
                        <div class="alert alert-danger">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Cette action est irréversible !</strong><br>
                            <small>Le mouvement de caisse associé sera également supprimé et les dettes seront restaurées.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deletePayment">
                            <i class="fas fa-trash me-2"></i>Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
