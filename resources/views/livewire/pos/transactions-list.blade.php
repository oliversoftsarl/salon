<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header pb-0 d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h6 class="mb-0">Filtres</h6>
                <p class="text-sm text-secondary mb-0">Affiner par type, période, prestataire</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('today')">Aujourd'hui</button>
                <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('week')">Cette semaine</button>
                <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('month')">Ce mois</button>
                <button class="btn btn-dark btn-sm" wire:click="exportCsv">
                    <i class="ni ni-cloud-download-95 me-1"></i> Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-2">
                    <label class="form-label">Type d'article</label>
                    <select class="form-select" wire:model.live="filter_type">
                        <option value="all">Tous</option>
                        <option value="products">Produits</option>
                        <option value="services">Services</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Type transaction</label>
                    <select class="form-select" wire:model.live="tx_type">
                        <option value="all">Toutes</option>
                        <option value="sale">Vente</option>
                        <option value="refund">Remboursement</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Prestataire</label>
                    <select class="form-select" wire:model.live="stylist_id">
                        <option value="">— Tous —</option>
                        @foreach($this->staffList as $staff)
                            <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Du</label>
                    <input type="date" class="form-control" wire:model.live="date_from">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Au</label>
                    <input type="date" class="form-control" wire:model.live="date_to">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="Référence..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <div class="text-sm text-secondary">
                Total (page): <strong class="text-dark">{{ number_format($pageTotal, 0, ',', ' ') }} FC</strong>
            </div>
            <div class="text-sm text-secondary">
                Total (filtre global): <strong class="text-success">{{ number_format($grandTotal, 0, ',', ' ') }} FC</strong>
            </div>
            <div>
                <a href="{{ route('pos.checkout') }}" class="btn btn-primary btn-sm">
                    <i class="ni ni-cart me-1"></i> Nouvelle vente
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 120px;">Date</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Article</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Prestataire</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 70px;">Type</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end d-none d-lg-table-cell" style="width: 80px;">PU</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 50px;">Qté</th>
                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 90px;">Total</th>
                        @if(auth()->user()->role === 'admin')
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 70px;">Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $it)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex flex-column">
                                <span class="text-xs font-weight-bold">{{ optional($it->transaction)->created_at?->format('d/m/Y') }}</span>
                                <span class="text-xs text-secondary">{{ optional($it->transaction)->created_at?->format('H:i') }}</span>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-sm font-weight-bold text-truncate" style="max-width: 180px;">
                                    @if($it->product_id)
                                        {{ $it->product->name ?? 'Produit #'.$it->product_id }}
                                    @elseif($it->service_id)
                                        {{ $it->service->name ?? 'Service #'.$it->service_id }}
                                    @else
                                        —
                                    @endif
                                </span>
                                <span class="text-xs text-secondary text-truncate d-md-none" style="max-width: 150px;">
                                    {{ $it->stylist->name ?? '' }}
                                </span>
                                <span class="text-xs text-muted">{{ optional($it->transaction)->reference ?? '' }}</span>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            @if($it->stylist_id)
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xs me-2 bg-gradient-primary rounded-circle">
                                        <span class="text-white" style="font-size: 9px;">{{ substr($it->stylist->name ?? '?', 0, 1) }}</span>
                                    </div>
                                    <span class="text-xs">{{ $it->stylist->name ?? '—' }}</span>
                                </div>
                            @else
                                <span class="text-xs text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $it->product_id ? 'info' : 'success' }}">
                                {{ $it->product_id ? 'Produit' : 'Service' }}
                            </span>
                        </td>
                        <td class="text-end d-none d-lg-table-cell">
                            <span class="text-xs">{{ number_format($it->unit_price, 0, ',', ' ') }} FC</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $it->quantity }}</span>
                        </td>
                        <td class="text-end pe-3">
                            <span class="text-sm font-weight-bold">{{ number_format($it->line_total, 0, ',', ' ') }} FC</span>
                        </td>
                        @if(auth()->user()->role === 'admin')
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="confirmDeleteItem({{ $it->id }})" title="Supprimer">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        @endif
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ auth()->user()->role === 'admin' ? 8 : 7 }}" class="text-center py-4 text-muted">
                            <i class="ni ni-cart" style="font-size: 32px;"></i>
                            <p class="mt-2 mb-0">Aucune transaction trouvée</p>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $items->links() }}
        </div>
    </div>

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
                        <h5>Êtes-vous sûr de vouloir supprimer cet article ?</h5>
                        <p class="text-muted mb-3">{{ $deletingItemInfo }}</p>
                        <div class="alert alert-warning">
                            <i class="fas fa-info-circle me-2"></i>
                            <strong>Attention :</strong><br>
                            <small>Le stock sera restauré pour les produits. Les totaux de la transaction et de la caisse seront mis à jour.</small>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">
                            <i class="fas fa-times me-2"></i>Annuler
                        </button>
                        <button type="button" class="btn btn-danger" wire:click="deleteItem">
                            <i class="fas fa-trash me-2"></i>Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

