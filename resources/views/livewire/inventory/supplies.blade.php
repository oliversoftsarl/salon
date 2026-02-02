<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Nouvel approvisionnement</h6>
                    <p class="text-sm text-secondary mb-0">Augmente le stock et trace le mouvement</p>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label">Produit</label>
                        <select class="form-select" wire:model="product_id">
                            <option value="">— Sélectionner —</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                        @error('product_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-md-4">
                            <label class="form-label">Quantité</label>
                            <input type="number" min="1" class="form-control" wire:model="quantity_received">
                            @error('quantity_received') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Coût unitaire (€)</label>
                            <input type="number" step="0.01" min="0" class="form-control" wire:model="unit_cost">
                            @error('unit_cost') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" wire:model="received_at">
                            @error('received_at') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Fournisseur</label>
                        <input type="text" class="form-control" wire:model="supplier" placeholder="Nom du fournisseur">
                        @error('supplier') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="2" wire:model="notes" placeholder="Référence, BL, conditions..."></textarea>
                        @error('notes') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-success" wire:click="save">
                        <i class="ni ni-check-bold me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="mb-0">Historique des approvisionnements</h6>
                        <p class="text-sm text-secondary mb-0">Total (page): <strong>{{ $pageTotalQty }}</strong></p>
                    </div>
                    <div class="d-flex gap-2">
                        {{-- Presets rapides --}}
                        <button class="btn btn-outline-secondary btn-sm" wire:click="$set('date_from', '{{ now()->toDateString() }}'); $set('date_to', '{{ now()->toDateString() }}')">Aujourd’hui</button>
                        <button class="btn btn-outline-secondary btn-sm" wire:click="$set('date_from', '{{ now()->startOfWeek()->toDateString() }}'); $set('date_to', '{{ now()->toDateString() }}')">Semaine</button>
                        <button class="btn btn-outline-secondary btn-sm" wire:click="$set('date_from', '{{ now()->startOfMonth()->toDateString() }}'); $set('date_to', '{{ now()->toDateString() }}')">Mois</button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-2 mb-3">
                        <div class="col-md-3">
                            <label class="form-label">Du</label>
                            <input type="date" class="form-control" wire:model="date_from">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Au</label>
                            <input type="date" class="form-control" wire:model="date_to">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Produit</label>
                            <select class="form-select" wire:model="filter_product_id">
                                <option value="">— Tous —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Fournisseur</label>
                            <input type="text" class="form-control" placeholder="Filtrer fournisseur..." wire:model.debounce.300ms="filter_supplier">
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 90px;">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produit</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 60px;">Qté</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Fournisseur</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end d-none d-lg-table-cell" style="width: 80px;">Coût U.</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-xl-table-cell">Notes</th>
                                    @if(auth()->user()->role === 'admin')
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 90px;">Actions</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplies as $s)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="text-xs">{{ $s->received_at?->format('d/m/Y') }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="text-sm font-weight-bold text-truncate" style="max-width: 150px;" title="{{ $s->product->name ?? '' }}">{{ $s->product->name ?? '—' }}</span>
                                                <span class="text-xs text-secondary d-md-none">{{ $s->supplier ?? '' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">{{ $s->quantity_received }}</span>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="text-xs text-truncate d-inline-block" style="max-width: 120px;" title="{{ $s->supplier }}">{{ $s->supplier ?? '—' }}</span>
                                        </td>
                                        <td class="text-end d-none d-lg-table-cell">
                                            <span class="text-xs">{{ $s->unit_cost !== null ? number_format($s->unit_cost, 0, ',', ' ') . ' FC' : '—' }}</span>
                                        </td>
                                        <td class="d-none d-xl-table-cell">
                                            <span class="text-xs text-truncate d-inline-block" style="max-width: 100px;" title="{{ $s->notes }}">{{ $s->notes }}</span>
                                        </td>
                                        @if(auth()->user()->role === 'admin')
                                            <td class="text-center">
                                                <button class="btn btn-sm btn-outline-warning px-2 py-1" wire:click="openEditModal({{ $s->id }})" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="confirmDelete({{ $s->id }})" title="Supprimer">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        @endif
                                    </tr>
                                @empty
                                    <tr><td colspan="{{ auth()->user()->role === 'admin' ? 7 : 6 }}" class="text-center py-4 text-muted">Aucun approvisionnement</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $supplies->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal d'édition (Admin uniquement) --}}
    @if($showEditModal && auth()->user()->role === 'admin')
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-warning">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-edit me-2"></i>Modifier l'approvisionnement
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEditModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Produit</label>
                            <select class="form-select" wire:model="edit_product_id">
                                <option value="">— Sélectionner —</option>
                                @foreach($products as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }}</option>
                                @endforeach
                            </select>
                            @error('edit_product_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Quantité</label>
                                <input type="number" min="1" class="form-control" wire:model="edit_quantity_received">
                                @error('edit_quantity_received') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Coût unitaire (FC)</label>
                                <input type="number" step="1" min="0" class="form-control" wire:model="edit_unit_cost">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Date</label>
                                <input type="date" class="form-control" wire:model="edit_received_at">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Fournisseur</label>
                            <input type="text" class="form-control" wire:model="edit_supplier">
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea class="form-control" rows="2" wire:model="edit_notes"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeEditModal">Annuler</button>
                        <button type="button" class="btn btn-warning" wire:click="updateSupply">
                            <i class="fas fa-save me-2"></i>Enregistrer
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
                        <h5>Êtes-vous sûr de vouloir supprimer cet approvisionnement ?</h5>
                        <p class="text-muted mb-3">{{ $deletingInfo }}</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            Le stock sera automatiquement réduit.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteSupply">
                            <i class="fas fa-trash me-2"></i>Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
