<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <ul class="mb-0">
                @foreach ($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex flex-column flex-md-row align-items-md-center gap-3">
                <div class="flex-grow-1">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in text-secondary"></i></span>
                        <input type="text" class="form-control border-start-0 ps-0" placeholder="Rechercher par nom, email, rôle..." wire:model.live.debounce.300ms="search">
                    </div>
                </div>
                <div class="flex-shrink-0">
                    <button class="btn btn-primary mb-0 w-100 w-md-auto" wire:click="create">
                        <i class="ni ni-fat-add me-1"></i> Nouvel utilisateur
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
                    <div class="col-md-4">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control" wire:model="email">
                        @error('email') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Rôle</label>
                        <select class="form-select" wire:model.live="role">
                            @foreach($availableRoles as $roleOption)
                                <option value="{{ $roleOption->name }}">{{ $roleOption->display_name }}</option>
                            @endforeach
                        </select>
                        @error('role') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <div class="form-check">
                            <input id="active" class="form-check-input" type="checkbox" wire:model="active">
                            <label for="active" class="form-check-label">Actif</label>
                        </div>
                        @error('active') <small class="text-danger d-block">{{ $message }}</small> @enderror
                    </div>

                    <div class="col-md-4">
                        <label class="form-label">Mot de passe {{ $editingId ? '(laisser vide si inchangé)' : '' }}</label>
                        <input type="password" class="form-control" wire:model="password">
                        @error('password') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Confirmation</label>
                        <input type="password" class="form-control" wire:model="password_confirmation">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Fonction / Catégorie</label>
                        <select class="form-select" wire:model="staff_category">
                            @foreach($staffCategories as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('staff_category') <small class="text-danger">{{ $message }}</small> @enderror
                        <small class="text-muted">Ex: Coiffeur, Masseur, Esthéticien...</small>
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
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Utilisateur</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Email</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-lg-table-cell">Fonction</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 80px;">Rôle</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 70px;">Statut</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 160px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex flex-column">
                                <span class="text-sm font-weight-bold text-truncate" style="max-width: 180px;" title="{{ $u->name }}">{{ $u->name }}</span>
                                <span class="text-xs text-secondary d-md-none text-truncate" style="max-width: 180px;" title="{{ $u->email }}">{{ $u->email }}</span>
                                @if($u->staffProfile?->role_title)
                                    <span class="badge bg-gradient-success text-xxs d-lg-none mt-1" style="width: fit-content;">{{ $u->staffProfile->role_title }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <span class="text-xs text-truncate d-inline-block" style="max-width: 200px;" title="{{ $u->email }}">{{ $u->email }}</span>
                        </td>
                        <td class="d-none d-lg-table-cell">
                            @if($u->staffProfile?->role_title)
                                <span class="badge bg-gradient-success">{{ $u->staffProfile->role_title }}</span>
                            @else
                                <span class="text-xs text-muted">—</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $u->role === 'admin' ? 'danger' : ($u->role === 'cashier' ? 'warning' : ($u->role === 'manager' ? 'info' : 'dark')) }}">
                                {{ $u->roleModel?->display_name ?? ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $u->active ? 'success' : 'secondary' }}">
                                {{ $u->active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="btn-group" role="group">
                                <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="edit({{ $u->id }})" title="Modifier">
                                    <i class="ni ni-ruler-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete({{ $u->id }})" onclick="return confirm('Supprimer cet utilisateur ?')" title="Supprimer">
                                    <i class="ni ni-fat-remove"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">Aucun utilisateur</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
