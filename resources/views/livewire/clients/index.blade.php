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
                    <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                        <div class="flex-grow-1">
                            <div class="input-group">
                                <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in text-secondary"></i></span>
                                <input type="text" class="form-control border-start-0 ps-0" placeholder="Rechercher par nom, email, téléphone..." wire:model.live.debounce.300ms="search">
                            </div>
                        </div>
                        <div class="flex-shrink-0">
                            <button class="btn btn-primary mb-0 w-100 w-md-auto" wire:click="create">
                                <i class="ni ni-fat-add me-1"></i> Nouveau client
                            </button>
                        </div>
                    </div>
                </div>
                @if($editingId !== null)
                    <div class="card-body border-top">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Nom complet</label>
                                <input type="text" class="form-control" wire:model="name" placeholder="Prénom Nom">
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

                            <div class="col-md-4">
                                <label class="form-label">Date de naissance</label>
                                <input type="date" class="form-control" wire:model="birthdate">
                                @error('birthdate') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Genre</label>
                                <select class="form-select" wire:model="gender">
                                    <option value="">—</option>
                                    <option value="male">Homme</option>
                                    <option value="female">Femme</option>
                                    <option value="other">Autre</option>
                                </select>
                                @error('gender') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Points fidélité</label>
                                <input type="number" min="0" class="form-control" wire:model="loyalty_point">
                                @error('loyalty_point') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model="notes"></textarea>
                                @error('notes') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="publish_consent" wire:model="publish_consent">
                                    <label class="form-check-label" for="publish_consent">
                                        <i class="ni ni-world me-1"></i>
                                        <strong>Consentement publication</strong> — Le client accepte d'apparaître sur notre site web
                                    </label>
                                </div>
                                @error('publish_consent') <small class="text-danger">{{ $message }}</small> @enderror
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
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3" style="width: 40px;">#</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Nom</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Contact</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-lg-table-cell">Infos</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 70px;">Points</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 80px;">Publication</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 100px;">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($clients as $c)
                            <tr>
                                <td class="ps-3">
                                    <span class="text-xs text-secondary">{{ $loop->iteration }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold text-truncate" style="max-width: 150px;" title="{{ $c->name }}">{{ $c->name }}</span>
                                        <span class="text-xs text-secondary d-md-none">{{ $c->phone ?? $c->phone_number }}</span>
                                    </div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <div class="d-flex flex-column">
                                        <span class="text-xs text-truncate" style="max-width: 150px;" title="{{ $c->email }}">{{ $c->email ?? '—' }}</span>
                                        <span class="text-xs text-secondary">{{ $c->phone ?? $c->phone_number ?? '—' }}</span>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">
                                    <div class="d-flex flex-column">
                                        <span class="text-xs">
                                            @if($c->gender)
                                                <span class="badge bg-{{ $c->gender === 'male' ? 'primary' : ($c->gender === 'female' ? 'pink' : 'secondary') }} badge-sm me-1">
                                                    {{ $c->gender === 'male' ? 'H' : ($c->gender === 'female' ? 'F' : 'A') }}
                                                </span>
                                            @endif
                                            {{ optional($c->birthdate)->format('d/m/Y') ?? '' }}
                                        </span>
                                        @if($c->notes)
                                            <span class="text-xs text-muted text-truncate" style="max-width: 120px;" title="{{ $c->notes }}">{{ $c->notes }}</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-gradient-info">{{ (int)($c->loyalty_point ?? 0) }}</span>
                                </td>
                                <td class="text-center">
                                    @if($c->publish_consent)
                                        <span class="badge bg-success" title="Le client accepte d'être publié sur le site">
                                            <i class="ni ni-check-bold me-1"></i>Oui
                                        </span>
                                    @else
                                        <span class="badge bg-secondary" title="Le client n'a pas donné son consentement">
                                            <i class="ni ni-fat-remove me-1"></i>Non
                                        </span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="edit({{ $c->id }})" title="Modifier">
                                        <i class="ni ni-ruler-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete({{ $c->id }})" onclick="return confirm('Supprimer ce client ?')" title="Supprimer">
                                        <i class="ni ni-fat-remove"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted">Aucun client</td></tr>
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
