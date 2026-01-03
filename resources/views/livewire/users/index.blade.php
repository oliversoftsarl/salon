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
        <div class="card-header pb-0 d-flex flex-wrap gap-2 align-items-end justify-content-between">
            <div class="flex-grow-1">
                <label class="form-label mb-1">Rechercher</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                    <input type="text" class="form-control" placeholder="Nom / Email / Rôle..." wire:model.live.debounce.300ms="search">
                </div>
            </div>
            <div class="text-end">
                <button class="btn btn-primary mt-3 mt-md-0" wire:click="create">
                    <i class="ni ni-fat-add me-1"></i> Nouvel utilisateur
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
                    <div class="col-md-2">
                        <label class="form-label">Rôle</label>
                        <select class="form-select" wire:model="role">
                            <option value="staff">Staff</option>
                            <option value="cashier">Cashier</option>
                            <option value="admin">Admin</option>
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
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 80px;">Rôle</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 70px;">Statut</th>
                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 100px;">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td class="ps-3">
                            <div class="d-flex flex-column">
                                <span class="text-sm font-weight-bold text-truncate" style="max-width: 180px;" title="{{ $u->name }}">{{ $u->name }}</span>
                                <span class="text-xs text-secondary d-md-none text-truncate" style="max-width: 180px;" title="{{ $u->email }}">{{ $u->email }}</span>
                            </div>
                        </td>
                        <td class="d-none d-md-table-cell">
                            <span class="text-xs text-truncate d-inline-block" style="max-width: 200px;" title="{{ $u->email }}">{{ $u->email }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $u->role === 'admin' ? 'danger' : ($u->role === 'cashier' ? 'warning' : 'dark') }}">
                                {{ ucfirst($u->role) }}
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-{{ $u->active ? 'success' : 'secondary' }}">
                                {{ $u->active ? 'Actif' : 'Inactif' }}
                            </span>
                        </td>
                        <td class="text-end pe-3">
                            <div class="dropdown d-inline-block">
                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle px-2 py-1" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="ni ni-settings-gear-65"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow">
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="edit({{ $u->id }})"><i class="ni ni-ruler-pencil me-2 text-primary"></i>Éditer</a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="toggleActive({{ $u->id }})"><i class="ni ni-button-power me-2 text-warning"></i>{{ $u->active ? 'Désactiver' : 'Activer' }}</a></li>
                                    <li><a class="dropdown-item" href="#" wire:click.prevent="resetPassword({{ $u->id }})"><i class="ni ni-lock-circle-open me-2 text-info"></i>Réinitialiser MDP</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item text-danger" href="#" wire:click.prevent="delete({{ $u->id }})" onclick="return confirm('Supprimer cet utilisateur ?')"><i class="ni ni-fat-remove me-2"></i>Supprimer</a></li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="text-center py-4 text-muted">Aucun utilisateur</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $users->links() }}
        </div>
    </div>
</div>
