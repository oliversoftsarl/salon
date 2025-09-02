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
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Actif</th>
                    <th class="text-end">Actions</th>
                </tr>
                </thead>
                <tbody>
                @forelse($users as $u)
                    <tr>
                        <td>{{ $u->name }}</td>
                        <td>{{ $u->email }}</td>
                        <td><span class="badge bg-dark">{{ ucfirst($u->role) }}</span></td>
                        <td>
                            <span class="badge {{ $u->active ? 'bg-success' : 'bg-secondary' }}">{{ $u->active ? 'Oui' : 'Non' }}</span>
                        </td>
                        <td class="text-end">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-primary" wire:click="edit({{ $u->id }})">Éditer</button>
                                <button class="btn btn-sm btn-outline-warning" wire:click="toggleActive({{ $u->id }})">
                                    {{ $u->active ? 'Désactiver' : 'Activer' }}
                                </button>
                                <button class="btn btn-sm btn-outline-secondary" wire:click="resetPassword({{ $u->id }})">
                                    Réinitialiser MDP
                                </button>
                                <button class="btn btn-sm btn-outline-danger" wire:click="delete({{ $u->id }})" onclick="return confirm('Supprimer cet utilisateur ?')">Supprimer</button>
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
