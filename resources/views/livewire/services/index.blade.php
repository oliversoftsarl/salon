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
                <div class="card-header pb-0">
                    <div class="row align-items-center g-3">
                        <div class="col">
                            <div class="input-group input-group-outline">
                                <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in text-secondary"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" placeholder="Rechercher un service..." wire:model.live.debounce.300ms="search">
                            </div>
                        </div>
                        <div class="col-auto d-flex gap-2">
                            <button class="btn btn-outline-dark mb-0" wire:click="printList" title="Imprimer la liste">
                                <i class="ni ni-single-copy-04 me-1"></i> Imprimer
                            </button>
                            <button class="btn btn-primary mb-0" wire:click="create">
                                <i class="ni ni-fat-add me-1"></i> Nouveau service
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Service</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 80px;">Durée</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end" style="width: 90px;">Prix</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center d-none d-md-table-cell" style="width: 80px;">Type</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 70px;">Statut</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 100px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($services as $service)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold text-truncate" style="max-width: 200px;" title="{{ $service->name }}">{{ $service->name }}</span>
                                        @if($service->description)
                                            <span class="text-xs text-muted text-truncate d-none d-lg-inline" style="max-width: 200px;" title="{{ $service->description }}">{{ $service->description }}</span>
                                        @endif
                                        <span class="d-md-none text-xs text-secondary">{{ ucfirst($service->service_type) }}</span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary">{{ $service->duration_minutes }} min</span>
                                </td>
                                <td class="text-end">
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold">{{ number_format($service->price, 0, ',', ' ') }} FC</span>
                                        @if($currentExchangeRate)
                                            <small class="text-muted">≈ $ {{ number_format($service->price / $currentExchangeRate->rate, 2, ',', ' ') }}</small>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center d-none d-md-table-cell">
                                    <span class="text-xs">{{ ucfirst($service->service_type) }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-{{ $service->active ? 'success' : 'secondary' }}">
                                        {{ $service->active ? 'Actif' : 'Inactif' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="edit({{ $service->id }})" title="Modifier">
                                        <i class="ni ni-ruler-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete({{ $service->id }})" onclick="return confirm('Supprimer ce service ?')" title="Supprimer">
                                        <i class="ni ni-fat-remove"></i>
                                    </button>
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
