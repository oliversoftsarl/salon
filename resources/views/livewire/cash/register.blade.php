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

    {{-- En-tête avec filtres --}}
    <div class="card mb-4">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0"><i class="ni ni-money-coins me-2"></i>Gestion de Caisse</h5>
                    <p class="text-sm text-secondary mb-0">Entrées, sorties et solde de caisse</p>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" wire:click="openForm('entry')">
                        <i class="ni ni-fat-add me-1"></i> Entrée
                    </button>
                    <button class="btn btn-danger" wire:click="openForm('exit')">
                        <i class="ni ni-fat-remove me-1"></i> Sortie
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                {{-- Période rapide --}}
                <div class="col-md-2">
                    <label class="form-label">Période</label>
                    <select class="form-select" wire:model.live="period">
                        <option value="today">Aujourd'hui</option>
                        <option value="yesterday">Hier</option>
                        <option value="week">Cette semaine</option>
                        <option value="month">Ce mois</option>
                        <option value="year">Cette année</option>
                        <option value="custom">Personnalisée</option>
                    </select>
                </div>
                {{-- Date début --}}
                <div class="col-md-2">
                    <label class="form-label">Du</label>
                    <input type="date" class="form-control" wire:model.live="date_from">
                </div>
                {{-- Date fin --}}
                <div class="col-md-2">
                    <label class="form-label">Au</label>
                    <input type="date" class="form-control" wire:model.live="date_to">
                </div>
                {{-- Type --}}
                <div class="col-md-2">
                    <label class="form-label">Type</label>
                    <select class="form-select" wire:model.live="filter_type">
                        <option value="all">Tous</option>
                        <option value="entry">Entrées</option>
                        <option value="exit">Sorties</option>
                    </select>
                </div>
                {{-- Catégorie --}}
                <div class="col-md-2">
                    <label class="form-label">Catégorie</label>
                    <select class="form-select" wire:model.live="filter_category">
                        <option value="">Toutes</option>
                        <optgroup label="Entrées">
                            @foreach($entryCategories as $cat)
                                <option value="{{ $cat }}">{{ $categoryLabels[$cat] }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Sorties">
                            @foreach($exitCategories as $cat)
                                <option value="{{ $cat }}">{{ $categoryLabels[$cat] }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>
                {{-- Recherche --}}
                <div class="col-md-2">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="Description..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes de résumé --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Solde Initial</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($stats['initial_balance'], 0, ',', ' ') }} FC
                                </h5>
                                <small class="text-muted">Avant le {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}</small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-secondary shadow text-center border-radius-md">
                                <i class="ni ni-archive-2 text-lg opacity-10"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold text-success">Total Entrées</p>
                                <h5 class="font-weight-bolder mb-0 text-success">
                                    +{{ number_format($stats['total_entries'], 0, ',', ' ') }} FC
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-bold-up text-lg opacity-10"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold text-danger">Total Sorties</p>
                                <h5 class="font-weight-bolder mb-0 text-danger">
                                    -{{ number_format($stats['total_exits'], 0, ',', ' ') }} FC
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                <i class="ni ni-bold-down text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card bg-gradient-dark">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold text-white">Solde Final</p>
                                <h5 class="font-weight-bolder mb-0 text-white">
                                    {{ number_format($stats['final_balance'], 0, ',', ' ') }} FC
                                </h5>
                                <small class="text-white opacity-8">En caisse</small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-dark text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Détail par catégorie --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0 text-success"><i class="ni ni-bold-up me-2"></i>Détail Entrées</h6>
                </div>
                <div class="card-body pt-2">
                    @if(count($stats['entries_by_category']) > 0)
                        @foreach($stats['entries_by_category'] as $cat => $total)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-sm">{{ $categoryLabels[$cat] ?? $cat }}</span>
                                <span class="badge bg-success">{{ number_format($total, 0, ',', ' ') }} FC</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">Aucune entrée sur cette période</p>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0 text-danger"><i class="ni ni-bold-down me-2"></i>Détail Sorties</h6>
                </div>
                <div class="card-body pt-2">
                    @if(count($stats['exits_by_category']) > 0)
                        @foreach($stats['exits_by_category'] as $cat => $total)
                            <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                                <span class="text-sm">{{ $categoryLabels[$cat] ?? $cat }}</span>
                                <span class="badge bg-danger">{{ number_format($total, 0, ',', ' ') }} FC</span>
                            </div>
                        @endforeach
                    @else
                        <p class="text-muted text-center py-3">Aucune sortie sur cette période</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Tableau des mouvements --}}
    <div class="card">
        <div class="card-header pb-0">
            <h6 class="mb-0"><i class="ni ni-bullet-list-67 me-2"></i>Mouvements de Caisse</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 100px;">Date</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Catégorie</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center d-none d-lg-table-cell" style="width: 100px;">Paiement</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end" style="width: 120px;">Montant</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 80px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($movements as $mvt)
                            <tr>
                                <td class="ps-3">
                                    <span class="text-xs font-weight-bold">{{ $mvt->date->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold text-truncate" style="max-width: 200px;" title="{{ $mvt->description }}">
                                            {{ $mvt->description }}
                                        </span>
                                        @if($mvt->reference)
                                            <span class="text-xs text-muted">Réf: {{ $mvt->reference }}</span>
                                        @endif
                                        @if($mvt->user_id && $mvt->user)
                                            <span class="text-xs text-info">{{ $mvt->user->name }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-{{ $mvt->type === 'entry' ? 'success' : 'danger' }} badge-sm">
                                        {{ $mvt->category_label }}
                                    </span>
                                </td>
                                <td class="text-center d-none d-lg-table-cell">
                                    <span class="text-xs">{{ $mvt->payment_method_label }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="text-sm font-weight-bold {{ $mvt->type === 'entry' ? 'text-success' : 'text-danger' }}">
                                        {{ $mvt->type === 'entry' ? '+' : '-' }}{{ number_format($mvt->amount, 0, ',', ' ') }} FC
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if(!$mvt->transaction_id)
                                        <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="edit({{ $mvt->id }})" title="Modifier">
                                            <i class="ni ni-ruler-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete({{ $mvt->id }})" onclick="return confirm('Supprimer ce mouvement ?')" title="Supprimer">
                                            <i class="ni ni-fat-remove"></i>
                                        </button>
                                    @else
                                        <span class="badge bg-secondary" title="Mouvement automatique (vente)">
                                            <i class="ni ni-lock-circle-open"></i>
                                        </span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="ni ni-money-coins" style="font-size: 32px;"></i>
                                    <p class="mt-2 mb-0">Aucun mouvement sur cette période</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($movements->hasPages())
            <div class="card-footer">
                {{ $movements->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Formulaire --}}
    @if($showForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-{{ $form_type === 'entry' ? 'success' : 'danger' }} text-white">
                        <h5 class="modal-title">
                            <i class="ni ni-{{ $form_type === 'entry' ? 'bold-up' : 'bold-down' }} me-2"></i>
                            {{ $editingId ? 'Modifier' : 'Nouveau' }} {{ $form_type === 'entry' ? 'Entrée' : 'Sortie' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- Type --}}
                            <div class="col-md-6">
                                <label class="form-label">Type de mouvement *</label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="form_type" id="type_entry" value="entry" wire:model.live="form_type">
                                    <label class="btn btn-outline-success" for="type_entry">
                                        <i class="ni ni-bold-up me-1"></i> Entrée
                                    </label>
                                    <input type="radio" class="btn-check" name="form_type" id="type_exit" value="exit" wire:model.live="form_type">
                                    <label class="btn btn-outline-danger" for="type_exit">
                                        <i class="ni ni-bold-down me-1"></i> Sortie
                                    </label>
                                </div>
                            </div>

                            {{-- Catégorie --}}
                            <div class="col-md-6">
                                <label class="form-label">Catégorie *</label>
                                <select class="form-select" wire:model.live="form_category">
                                    @if($form_type === 'entry')
                                        @foreach($entryCategories as $cat)
                                            <option value="{{ $cat }}">{{ $categoryLabels[$cat] }}</option>
                                        @endforeach
                                    @else
                                        @foreach($exitCategories as $cat)
                                            <option value="{{ $cat }}">{{ $categoryLabels[$cat] }}</option>
                                        @endforeach
                                    @endif
                                </select>
                                @error('form_category') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Date --}}
                            <div class="col-md-4">
                                <label class="form-label">Date *</label>
                                <input type="date" class="form-control" wire:model="form_date">
                                @error('form_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Montant --}}
                            <div class="col-md-4">
                                <label class="form-label">Montant (FC) *</label>
                                <input type="number" step="0.01" min="0" class="form-control" wire:model="form_amount" placeholder="0.00">
                                @error('form_amount') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Méthode de paiement --}}
                            <div class="col-md-4">
                                <label class="form-label">Mode de paiement *</label>
                                <select class="form-select" wire:model="form_payment_method">
                                    @foreach($paymentMethodLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('form_payment_method') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Description --}}
                            <div class="col-md-8">
                                <label class="form-label">Description *</label>
                                <input type="text" class="form-control" wire:model="form_description" placeholder="Ex: Achat fournitures bureau">
                                @error('form_description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Référence --}}
                            <div class="col-md-4">
                                <label class="form-label">Référence</label>
                                <input type="text" class="form-control" wire:model="form_reference" placeholder="N° facture, reçu...">
                                @error('form_reference') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Employé (pour avance sur salaire) --}}
                            @if($form_category === 'salary_advance')
                                <div class="col-md-6">
                                    <label class="form-label">Employé concerné <span class="text-danger">*</span></label>
                                    <select class="form-select @error('form_user_id') is-invalid @enderror" wire:model="form_user_id">
                                        <option value="">— Sélectionner l'employé —</option>
                                        @foreach($staffList as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                        @endforeach
                                    </select>
                                    @error('form_user_id') <small class="text-danger">{{ $message }}</small> @enderror
                                    <small class="text-muted">
                                        <i class="ni ni-bulb-61 me-1"></i>
                                        Une dette sera automatiquement créée pour cet employé
                                    </small>
                                </div>
                            @endif

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model="form_notes" placeholder="Informations complémentaires..."></textarea>
                                @error('form_notes') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeForm">Annuler</button>
                        <button type="button" class="btn btn-{{ $form_type === 'entry' ? 'success' : 'danger' }}" wire:click="save">
                            <i class="ni ni-check-bold me-1"></i> {{ $editingId ? 'Modifier' : 'Enregistrer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

