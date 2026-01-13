<div>
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-header pb-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <div class="input-group input-group-outline">
                                <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in text-secondary"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" placeholder="Rechercher par nom ou SKU..." wire:model.live.debounce.300ms="search">
                            </div>
                        </div>
                        <div class="col-auto">
                            <button class="btn btn-primary mb-0" wire:click="create">
                                <i class="ni ni-fat-add me-1"></i> Nouveau produit
                            </button>
                        </div>
                    </div>
                </div>
                @if($editingId !== null)
                    <div class="card-body border-top">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nom</label>
                                <input type="text" class="form-control" wire:model="name">
                                @error('name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">SKU</label>
                                <input type="text" class="form-control" wire:model="sku">
                                @error('sku') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Prix</label>
                                <input type="number" step="0.01" class="form-control" wire:model="price">
                                @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Stock</label>
                                <input type="number" min="0" class="form-control" wire:model="stock_quantity">
                                @error('stock_quantity') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-1 d-flex align-items-end">
                                <div class="form-check">
                                    <input id="is_snack" class="form-check-input" type="checkbox" wire:model="is_snack">
                                    <label for="is_snack" class="form-check-label">Snack</label>
                                </div>
                            </div>
                            <div class="col-12 d-flex gap-2">
                                <button class="btn btn-success" wire:click="save"><i class="ni ni-check-bold me-1"></i> Enregistrer</button>
                                <button class="btn btn-outline-secondary" wire:click="$set('editingId', null)">Annuler</button>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="card-body px-0 pt-0 pb-2">
                    <div class="table-responsive p-0">
                        <table class="table align-items-center justify-content-center mb-0">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 40px;">#</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Produit</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">SKU</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end">Prix</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Stock</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center d-none d-lg-table-cell" style="width: 60px;">Snack</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($products as $p)
                                <tr>
                                    <td class="ps-3">
                                        <span class="text-xs text-secondary">{{ $loop->iteration }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-sm font-weight-bold text-truncate" style="max-width: 180px;" title="{{ $p->name }}">{{ $p->name }}</span>
                                            <span class="text-xs text-secondary d-md-none">{{ $p->sku }}</span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="text-xs text-secondary">{{ $p->sku }}</span>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex flex-column">
                                            <span class="text-sm font-weight-bold">{{ number_format($p->price, 0, ',', ' ') }} FC</span>
                                            @if($currentExchangeRate)
                                                <small class="text-muted">≈ $ {{ number_format($p->price / $currentExchangeRate->rate, 2, ',', ' ') }}</small>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $p->stock_quantity <= 5 ? 'danger' : ($p->stock_quantity <= 10 ? 'warning' : 'success') }}">
                                            {{ $p->stock_quantity }}
                                        </span>
                                    </td>
                                    <td class="text-center d-none d-lg-table-cell">
                                        @if($p->is_snack)
                                            <span class="badge bg-info">Oui</span>
                                        @else
                                            <span class="text-xs text-muted">Non</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="edit({{ $p->id }})" title="Modifier">
                                            <i class="ni ni-ruler-pencil"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete({{ $p->id }})" onclick="return confirm('Supprimer ce produit ?')" title="Supprimer">
                                            <i class="ni ni-fat-remove"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center py-4 text-muted">Aucun produit</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
