<?php /** @var \Illuminate\Pagination\LengthAwarePaginator $services */ ?>
<div>
    <div class="row">
        <div class="col-12">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            <div class="card">
                <div class="card-header pb-0 d-flex flex-wrap gap-2 align-items-end justify-content-between">
                    <div class="flex-grow-1">
                        <label class="form-label mb-1">Rechercher</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                            <input type="text" class="form-control" placeholder="Nom du service..." wire:model.live.debounce.300ms="search">
                        </div>
                    </div>
                    <div class="text-end">
                        <button class="btn btn-primary mt-3 mt-md-0" wire:click="create">
                            <i class="ni ni-fat-add me-1"></i> Nouveau service
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
                                <label class="form-label">Durée (min)</label>
                                <input type="number" class="form-control" wire:model="duration_minutes" min="5" max="1440">
                                @error('duration_minutes') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Prix</label>
                                <input type="number" step="0.01" class="form-control" wire:model="price">
                                @error('price') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Type</label>
                                <select class="form-select" wire:model="service_type">
                                    <option value="home">Domicile</option>
                                    <option value="woman">Femme</option>
                                    <option value="other">Autre</option>
                                </select>
                                @error('service_type') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <label class="form-label">Description</label>
                                <textarea class="form-control" rows="2" wire:model="description"></textarea>
                                @error('description') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12 d-flex align-items-center gap-3">
                                <div class="form-check">
                                    <input id="active" class="form-check-input" type="checkbox" wire:model="active">
                                    <label for="active" class="form-check-label">Actif</label>
                                </div>
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
                            <th>Durée</th>
                            <th>Prix</th>
                            <th>Type</th>
                            <th>Actif</th>
                            <th class="text-end">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td>{{ $service->name }}</td>
                                <td><span class="badge bg-secondary">{{ $service->duration_minutes }} min</span></td>
                                <td>{{ number_format($service->price, 2, ',', ' ') }} €</td>
                                <td>{{ ucfirst($service->service_type) }}</td>
                                <td>
                                    <span class="badge {{ $service->active ? 'bg-success' : 'bg-dark' }}">{{ $service->active ? 'Actif' : 'Inactif' }}</span>
                                </td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $service->id }})">Éditer</button>
                                    <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $service->id }})" onclick="return confirm('Supprimer ce service ?')">Supprimer</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted">Aucun service</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-footer">
                    {{ $services->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
