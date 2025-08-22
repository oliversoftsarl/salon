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
                <div class="card-header pb-0 d-flex flex-wrap gap-2 align-items-end justify-content-between">
                    <div class="flex-grow-1">
                        <label class="form-label mb-1">Rechercher</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                            <input type="text" class="form-control" placeholder="Nom ou SKU..." wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary mt-3 mt-md-0" wire:click="create">
                            <i class="ni ni-fat-add me-1"></i> Nouveau produit
                        </button>
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
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                        <tr>
                            <th>Nom</th>
                            <th>SKU</th>
                            <th>Prix</th>
                            <th>Stock</th>
                            <th>Snack</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($products as $p)
                            <tr>
                                <td>{{ $p->name }}</td>
                                <td><span class="text-secondary">{{ $p->sku }}</span></td>
                                <td>{{ number_format($p->price, 2, ',', ' ') }} €</td>
                                <td><span class="badge bg-info">{{ $p->stock_quantity }}</span></td>
                                <td>{{ $p->is_snack ? 'Oui' : 'Non' }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $p->id }})">Éditer</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $p->id }})" onclick="return confirm('Supprimer ce produit ?')">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Aucun produit</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
