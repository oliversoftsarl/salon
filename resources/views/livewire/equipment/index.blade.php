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

    {{-- Statistiques --}}
    <div class="row mb-4">
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-gradient-primary">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Total</p>
                        <h4 class="font-weight-bolder text-white mb-0">{{ $stats['total'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-gradient-success">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Opérationnels</p>
                        <h4 class="font-weight-bolder text-white mb-0">{{ $stats['operational'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-gradient-warning">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">En maintenance</p>
                        <h4 class="font-weight-bolder text-white mb-0">{{ $stats['maintenance'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-gradient-danger">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">En panne</p>
                        <h4 class="font-weight-bolder text-white mb-0">{{ $stats['broken'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-gradient-info">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Maint. requise</p>
                        <h4 class="font-weight-bolder text-white mb-0">{{ $stats['needs_maintenance'] }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-2 col-md-4 col-6 mb-3">
            <div class="card bg-gradient-secondary">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">À renouveler</p>
                        <h4 class="font-weight-bolder text-white mb-0">{{ $stats['needs_renewal'] ?? 0 }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerte équipements à renouveler --}}
    @if(($stats['needs_renewal'] ?? 0) > 0)
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <strong>{{ $stats['needs_renewal'] }}</strong> équipement(s) approche(nt) de leur fin de vie et doivent être renouvelés.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    {{-- Liste des équipements --}}
    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Équipements du Salon</h5>
                </div>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-success mb-0" wire:click="exportExcel">
                        <i class="fas fa-file-excel me-2"></i>Exporter Excel
                    </button>
                    <button class="btn btn-primary mb-0" wire:click="openForm">
                        <i class="fas fa-plus me-2"></i>Nouvel équipement
                    </button>
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Filtres --}}
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <input type="text" class="form-control" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">Toutes catégories</option>
                        @foreach($categoryLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterSubCategory" {{ empty($filterCategory) ? 'disabled' : '' }}>
                        <option value="">Toutes sous-catégories</option>
                        @foreach($filterSubCategories as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Tous statuts</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterCondition">
                        <option value="">Tous états</option>
                        @foreach($conditionLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Tableau --}}
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Sous-catégorie</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Équipement</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Statut</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center d-none d-md-table-cell">Amortissement</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-lg-table-cell">Assigné à</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipmentList as $eq)
                            <tr class="{{ $eq->needs_renewal ? 'table-warning' : '' }}">
                                <td class="ps-3">
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold">{{ $eq->sub_category_label }}</span>
                                        <span class="text-xs text-muted">{{ $eq->category_label }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold">{{ $eq->name }}</span>
                                        @if($eq->code)
                                            <span class="text-xs text-muted">{{ $eq->code }}</span>
                                        @endif
                                        @if($eq->brand)
                                            <span class="text-xs text-secondary">{{ $eq->brand }} {{ $eq->model }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $eq->status_color }}">{{ $eq->status_label }}</span>
                                    @if($eq->needs_maintenance)
                                        <br><span class="badge bg-warning text-dark mt-1"><i class="fas fa-wrench me-1"></i>Maint.</span>
                                    @endif
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    @if($eq->lifespan_months)
                                        <div class="d-flex flex-column align-items-center">
                                            <div class="progress w-100" style="height: 6px; max-width: 80px;">
                                                <div class="progress-bar {{ $eq->is_amortized ? 'bg-danger' : ($eq->needs_renewal ? 'bg-warning' : 'bg-success') }}"
                                                     style="width: {{ $eq->amortization_percent }}%"></div>
                                            </div>
                                            <span class="text-xs {{ $eq->is_amortized ? 'text-danger' : ($eq->needs_renewal ? 'text-warning' : '') }}">
                                                {{ $eq->amortization_percent }}%
                                                @if($eq->is_amortized)
                                                    <i class="fas fa-exclamation-circle ms-1"></i>
                                                @elseif($eq->remaining_months !== null)
                                                    ({{ $eq->remaining_months }} mois)
                                                @endif
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-xs text-muted">—</span>
                                    @endif
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="text-sm">{{ $eq->assignedUser->name ?? '—' }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-1">
                                        <button class="btn btn-sm btn-outline-info px-2 py-1" wire:click="showEquipmentDetails({{ $eq->id }})" title="Détails">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-success px-2 py-1" wire:click="openMaintenanceForm({{ $eq->id }})" title="Maintenance">
                                            <i class="fas fa-wrench"></i>
                                        </button>
                                        @if(auth()->user()->role === 'admin')
                                            <button class="btn btn-sm btn-outline-warning px-2 py-1" wire:click="openForm({{ $eq->id }})" title="Modifier">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="confirmDelete({{ $eq->id }})" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-tools" style="font-size: 48px;"></i>
                                    <p class="mt-2 mb-0">Aucun équipement enregistré</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $equipmentList->links() }}
            </div>
        </div>
    </div>

    {{-- Modal Formulaire Équipement --}}
    @if($showForm)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-{{ $editingId ? 'edit' : 'plus' }} me-2"></i>
                            {{ $editingId ? 'Modifier l\'équipement' : 'Nouvel équipement' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nom de l'équipement <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="name" placeholder="Ex: Sèche-cheveux Pro 2000">
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Code interne</label>
                                <input type="text" class="form-control" wire:model="code" placeholder="Ex: EQ-001">
                                @error('code') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Catégorie <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model.live="category">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($categoryLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sous-catégorie</label>
                                <select class="form-select" wire:model="sub_category" {{ empty($category) ? 'disabled' : '' }}>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($subCategories as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('sub_category') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Marque</label>
                                <input type="text" class="form-control" wire:model="brand" placeholder="Ex: Babyliss">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Modèle</label>
                                <input type="text" class="form-control" wire:model="model" placeholder="Ex: Pro 2000W">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Numéro de série</label>
                                <input type="text" class="form-control" wire:model="serial_number" placeholder="Ex: SN123456789">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date d'achat</label>
                                <input type="date" class="form-control" wire:model="purchase_date">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Prix d'achat (FC)</label>
                                <input type="number" class="form-control" wire:model="purchase_price" min="0" step="100">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Durée de vie (mois)</label>
                                <input type="number" class="form-control" wire:model="lifespan_months" min="1" max="600" placeholder="Ex: 36">
                                <small class="text-muted">Pour le calcul d'amortissement</small>
                                @error('lifespan_months') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Fournisseur</label>
                                <input type="text" class="form-control" wire:model="supplier" placeholder="Nom du fournisseur">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Emplacement</label>
                                <input type="text" class="form-control" wire:model="location" placeholder="Ex: Poste 3, Zone massage">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Statut <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="status">
                                    @foreach($statusLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">État <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="condition">
                                    @foreach($conditionLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Fin de garantie</label>
                                <input type="date" class="form-control" wire:model="warranty_expiry">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Proch. maintenance</label>
                                <input type="date" class="form-control" wire:model="next_maintenance">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Assigné à</label>
                                <select class="form-select" wire:model="assigned_to">
                                    <option value="">-- Non assigné --</option>
                                    @foreach($staffList as $staff)
                                        <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" wire:model="description" rows="2"></textarea>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" wire:model="notes" rows="2" placeholder="Informations supplémentaires..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeForm">Annuler</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Maintenance --}}
    @if($showMaintenanceForm)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-success">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-wrench me-2"></i>Enregistrer une maintenance
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeMaintenanceForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Date <span class="text-danger">*</span></label>
                                <input type="date" class="form-control" wire:model="maintenance_date">
                                @error('maintenance_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Type <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="maintenance_type">
                                    @foreach($maintenanceTypeLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Effectué par</label>
                                <input type="text" class="form-control" wire:model="performed_by" placeholder="Nom du technicien">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Coût (FC)</label>
                                <input type="number" class="form-control" wire:model="maintenance_cost" min="0" step="100">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" wire:model="maintenance_description" rows="2" placeholder="Détails de l'intervention..."></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Pièces remplacées</label>
                                <input type="text" class="form-control" wire:model="parts_replaced" placeholder="Ex: Filtre, courroie...">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Prochaine maintenance</label>
                                <input type="date" class="form-control" wire:model="maintenance_next_date">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeMaintenanceForm">Annuler</button>
                        <button type="button" class="btn btn-success" wire:click="saveMaintenance">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Détails --}}
    @if($showDetails && $selectedEquipment)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-info">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-info-circle me-2"></i>Détails de l'équipement
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDetails"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Informations générales</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Nom</td>
                                        <td class="font-weight-bold">{{ $selectedEquipment->name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Code</td>
                                        <td>{{ $selectedEquipment->code ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Catégorie</td>
                                        <td>{{ $selectedEquipment->category_label }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Sous-catégorie</td>
                                        <td>{{ $selectedEquipment->sub_category_label }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Marque / Modèle</td>
                                        <td>{{ $selectedEquipment->brand ?? '—' }} {{ $selectedEquipment->model }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">N° Série</td>
                                        <td>{{ $selectedEquipment->serial_number ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Statut</td>
                                        <td><span class="badge bg-{{ $selectedEquipment->status_color }}">{{ $selectedEquipment->status_label }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">État</td>
                                        <td>{{ $selectedEquipment->condition_label }}</td>
                                    </tr>
                                </table>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-3">Informations d'achat & Amortissement</h6>
                                <table class="table table-sm">
                                    <tr>
                                        <td class="text-muted" style="width: 40%;">Date d'achat</td>
                                        <td>{{ $selectedEquipment->purchase_date?->format('d/m/Y') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Prix d'achat</td>
                                        <td>{{ $selectedEquipment->purchase_price ? number_format($selectedEquipment->purchase_price, 0, ',', ' ') . ' FC' : '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Durée de vie</td>
                                        <td>{{ $selectedEquipment->lifespan_months ? $selectedEquipment->lifespan_months . ' mois' : '—' }}</td>
                                    </tr>
                                    @if($selectedEquipment->lifespan_months)
                                    <tr>
                                        <td class="text-muted">Fin de vie prévue</td>
                                        <td>
                                            {{ $selectedEquipment->end_of_life_date?->format('d/m/Y') ?? '—' }}
                                            @if($selectedEquipment->is_amortized)
                                                <span class="badge bg-danger ms-1">Amorti</span>
                                            @elseif($selectedEquipment->needs_renewal)
                                                <span class="badge bg-warning ms-1">À renouveler</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Amortissement</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 8px;">
                                                    <div class="progress-bar {{ $selectedEquipment->is_amortized ? 'bg-danger' : ($selectedEquipment->needs_renewal ? 'bg-warning' : 'bg-success') }}"
                                                         style="width: {{ $selectedEquipment->amortization_percent }}%"></div>
                                                </div>
                                                <span class="text-sm">{{ $selectedEquipment->amortization_percent }}%</span>
                                            </div>
                                            @if($selectedEquipment->remaining_months !== null && !$selectedEquipment->is_amortized)
                                                <small class="text-muted">{{ $selectedEquipment->remaining_months }} mois restants</small>
                                            @endif
                                        </td>
                                    </tr>
                                    @endif
                                    <tr>
                                        <td class="text-muted">Fournisseur</td>
                                        <td>{{ $selectedEquipment->supplier ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Fin garantie</td>
                                        <td>
                                            @if($selectedEquipment->warranty_expiry)
                                                {{ $selectedEquipment->warranty_expiry->format('d/m/Y') }}
                                                @if($selectedEquipment->is_under_warranty)
                                                    <span class="badge bg-success ms-1">Sous garantie</span>
                                                @else
                                                    <span class="badge bg-secondary ms-1">Expirée</span>
                                                @endif
                                            @else
                                                —
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Emplacement</td>
                                        <td>{{ $selectedEquipment->location ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Assigné à</td>
                                        <td>{{ $selectedEquipment->assignedUser->name ?? '—' }}</td>
                                    </tr>
                                </table>
                            </div>
                        </div>

                        @if($selectedEquipment->description)
                            <div class="mt-3">
                                <h6 class="text-primary">Description</h6>
                                <p class="text-sm">{{ $selectedEquipment->description }}</p>
                            </div>
                        @endif

                        {{-- Historique maintenances --}}
                        <div class="mt-4">
                            <h6 class="text-primary mb-3"><i class="fas fa-history me-2"></i>Historique des maintenances</h6>
                            @if($selectedEquipment->maintenances->count() > 0)
                                <div class="table-responsive">
                                    <table class="table table-sm align-middle">
                                        <thead>
                                            <tr>
                                                <th>Date</th>
                                                <th>Type</th>
                                                <th>Effectué par</th>
                                                <th class="text-end">Coût</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach($selectedEquipment->maintenances as $maint)
                                                <tr>
                                                    <td>{{ $maint->maintenance_date->format('d/m/Y') }}</td>
                                                    <td>{{ $maint->type_label }}</td>
                                                    <td>{{ $maint->performed_by ?? '—' }}</td>
                                                    <td class="text-end">{{ number_format($maint->cost, 0, ',', ' ') }} FC</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <p class="text-muted text-center py-3">Aucune maintenance enregistrée</p>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeDetails">Fermer</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Suppression --}}
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
                        <h5>Êtes-vous sûr de vouloir supprimer cet équipement ?</h5>
                        <p class="text-muted mb-3">{{ $deletingInfo }}</p>
                        <div class="alert alert-danger">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette action est irréversible. L'historique des maintenances sera également supprimé.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">Annuler</button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="fas fa-trash me-2"></i>Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
