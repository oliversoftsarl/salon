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
                                    <th>Date</th>
                                    <th>Produit</th>
                                    <th class="text-end">Quantité</th>
                                    <th>Fournisseur</th>
                                    <th class="text-end">Coût U.</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($supplies as $s)
                                    <tr>
                                        <td>{{ $s->received_at?->format('d/m/Y') }}</td>
                                        <td>{{ $s->product->name ?? '—' }}</td>
                                        <td class="text-end"><span class="badge bg-success">{{ $s->quantity_received }}</span></td>
                                        <td>{{ $s->supplier ?? '—' }}</td>
                                        <td class="text-end">{{ $s->unit_cost !== null ? number_format($s->unit_cost, 2, ',', ' ') . ' €' : '—' }}</td>
                                        <td class="text-truncate" style="max-width:240px">{{ $s->notes }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="6" class="text-center py-4 text-muted">Aucun approvisionnement</td></tr>
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
</div>
