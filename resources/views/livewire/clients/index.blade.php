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
                            <input type="text" class="form-control" placeholder="Nom / Email / Téléphone..." wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary mt-3 mt-md-0" wire:click="create">
                            <i class="ni ni-fat-add me-1"></i> Nouveau client
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
                            <div class="col-md-4">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" wire:model="email">
                                @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control" wire:model="phone">
                                @error('phone') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model="notes"></textarea>
                                @error('notes') <small class="text-danger">{{ $message }}</small> @enderror
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
                            <th>Email</th>
                            <th>Téléphone</th>
                            <th>Notes</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($clients as $c)
                            <tr>
                                <td>{{ $c->name }}</td>
                                <td>{{ $c->email }}</td>
                                <td>{{ $c->phone }}</td>
                                <td class="text-truncate" style="max-width: 260px">{{ $c->notes }}</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $c->id }})">Éditer</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $c->id }})" onclick="return confirm('Supprimer ce client ?')">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Aucun client</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $clients->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
