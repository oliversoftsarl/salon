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
                    <h6 class="mb-0">Enregistrer une consommation</h6>
                    <p class="text-sm text-secondary mb-0">Déduis du stock automatiquement</p>
                </div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label">Produit</label>
                        <select class="form-select" wire:model="product_id">
                            <option value="">— Sélectionner —</option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}">
                                    {{ $p->name }} @if($p->is_consumable) (consommable) @endif
                                </option>
                            @endforeach
                        </select>
                        @error('product_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Quantité utilisée</label>
                            <input type="number" min="1" class="form-control" wire:model="quantity_used">
                            @error('quantity_used') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Date</label>
                            <input type="date" class="form-control" wire:model="used_at">
                            @error('used_at') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Staff</label>
                        <select class="form-select" wire:model="staff_id">
                            <option value="">— Non attribué —</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('staff_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>

                    <div class="mb-2">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" rows="2" wire:model="notes" placeholder="Ex: soin, prestation, etc."></textarea>
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
                <div class="card-header pb-0">
                    <h6 class="mb-0">Historique des consommations</h6>
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
                            <label class="form-label">Staff</label>
                            <select class="form-select" wire:model="filter_staff_id">
                                <option value="">— Tous —</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Produit</th>
                                    <th>Qté</th>
                                    <th>Stock restant</th>
                                    <th>Staff</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($consumptions as $c)
                                    <tr>
                                        <td>{{ $c->used_at?->format('d/m/Y') }}</td>
                                        <td>
                                            {{ $c->product->name ?? '—' }}
                                            @php
                                                $left = $c->product->stock_quantity ?? null;
                                                $threshold = $c->product->low_stock_threshold ?? 0;
                                            @endphp
                                            @if($left !== null && $threshold > 0 && $left <= $threshold)
                                                <span class="badge bg-warning ms-1">Bas stock</span>
                                            @endif
                                        </td>
                                        <td><span class="badge bg-dark">{{ $c->quantity_used }}</span></td>
                                        <td>{{ $left !== null ? $left : '—' }}</td>
                                        <td>{{ $c->staff->name ?? '—' }}</td>
                                        <td class="text-truncate" style="max-width:240px">{{ $c->notes }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted">Aucune consommation</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div>
                        {{ $consumptions->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
