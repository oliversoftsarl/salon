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
            <div class="card bg-gradient-dark">
                <div class="card-body p-3">
                    <div class="text-white">
                        <p class="text-sm mb-0 text-uppercase font-weight-bold opacity-7">Valeur totale</p>
                        <h5 class="font-weight-bolder text-white mb-0">{{ number_format($stats['total_value'], 0, ',', ' ') }} FC</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des équipements --}}
    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-0"><i class="fas fa-tools me-2"></i>Équipements du Salon</h5>
                </div>
                <button class="btn btn-primary mb-0" wire:click="openForm">
                    <i class="fas fa-plus me-2"></i>Nouvel équipement
                </button>
            </div>
        </div>
        <div class="card-body">
            {{-- Filtres --}}
            <div class="row g-3 mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterCategory">
                        <option value="">Toutes catégories</option>
                        @foreach($categoryLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3">
                    <select class="form-select" wire:model.live="filterStatus">
                        <option value="">Tous statuts</option>
                        @foreach($statusLabels as $key => $label)
                            <option value="{{ $key }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <select class="form-select" wire:model.live="filterCondition">
                        <option value="">Toutes conditions</option>
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Équipement</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Catégorie</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Statut</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center d-none d-md-table-cell">État</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-lg-table-cell">Assigné à</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-xl-table-cell">Proch. maintenance</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($equipmentList as $eq)
                            <tr>
                                <td class="ps-3">
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
                                <td>
                                    <span class="text-sm">{{ $eq->category_label }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $eq->status_color }}">{{ $eq->status_label }}</span>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <span class="text-sm">{{ $eq->condition_label }}</span>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <span class="text-sm">{{ $eq->assignedUser->name ?? '—' }}</span>
                                </td>
                                <td class="d-none d-xl-table-cell">
                                    @if($eq->next_maintenance)
                                        <span class="text-sm {{ $eq->needs_maintenance ? 'text-danger font-weight-bold' : '' }}">
                                            {{ $eq->next_maintenance->format('d/m/Y') }}
                                            @if($eq->needs_maintenance)
                                                <i class="fas fa-exclamation-triangle text-danger ms-1"></i>
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-sm text-muted">—</span>
                                    @endif
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
                                <td colspan="7" class="text-center py-4 text-muted">
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
                                <select class="form-select" wire:model="category">
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($categoryLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('category') <small class="text-danger">{{ $message }}</small> @enderror
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

                            <div class="col-md-6">
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
                            <div class="col-md-3">
                                <label class="form-label">Fin de garantie</label>
                                <input type="date" class="form-control" wire:model="warranty_expiry">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Proch. maintenance</label>
                                <input type="date" class="form-control" wire:model="next_maintenance">
                            </div>

                            <div class="col-md-6">
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

                            <div class="col-12">
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
                                <h6 class="text-primary mb-3">Informations d'achat</h6>
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
