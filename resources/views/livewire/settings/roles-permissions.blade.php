<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card">
        <div class="card-header pb-0">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="mb-0"><i class="ni ni-key-25 me-2"></i>Gestion des Rôles & Permissions</h5>
                    <p class="text-sm text-muted mb-0">Configurez les accès et menus pour chaque rôle</p>
                </div>
                <div class="d-flex gap-2">
                    @if($activeTab === 'roles')
                        <button class="btn btn-primary mb-0" wire:click="openRoleForm">
                            <i class="fas fa-plus me-2"></i>Nouveau rôle
                        </button>
                    @else
                        <button class="btn btn-primary mb-0" wire:click="openPermissionForm">
                            <i class="fas fa-plus me-2"></i>Nouvelle permission
                        </button>
                    @endif
                </div>
            </div>
        </div>
        <div class="card-body">
            {{-- Onglets --}}
            <ul class="nav nav-tabs mb-4" role="tablist">
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'roles' ? 'active' : '' }}"
                       href="#" wire:click.prevent="$set('activeTab', 'roles')">
                        <i class="ni ni-single-02 me-2"></i>Rôles
                        <span class="badge bg-primary ms-2">{{ count($roles) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $activeTab === 'permissions' ? 'active' : '' }}"
                       href="#" wire:click.prevent="$set('activeTab', 'permissions')">
                        <i class="ni ni-key-25 me-2"></i>Permissions
                        <span class="badge bg-info ms-2">{{ count($permissions) }}</span>
                    </a>
                </li>
            </ul>

            {{-- Recherche --}}
            <div class="row mb-4">
                <div class="col-md-4">
                    <input type="text" class="form-control" placeholder="Rechercher..." wire:model.live.debounce.300ms="search">
                </div>
            </div>

            {{-- Contenu des onglets --}}
            @if($activeTab === 'roles')
                {{-- Liste des Rôles --}}
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Rôle</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Description</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Permissions</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Utilisateurs</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Type</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex flex-column">
                                            <span class="text-sm font-weight-bold">{{ $role->display_name }}</span>
                                            <span class="text-xs text-muted">{{ $role->name }}</span>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-sm">{{ $role->description ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-info">{{ $role->permissions_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $role->users_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($role->is_system)
                                            <span class="badge bg-warning">Système</span>
                                        @else
                                            <span class="badge bg-success">Personnalisé</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning px-2 py-1" wire:click="openRoleForm({{ $role->id }})" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @if(!$role->is_system)
                                            <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="confirmDelete('role', {{ $role->id }})" title="Supprimer">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Aucun rôle trouvé</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @else
                {{-- Liste des Permissions --}}
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Permission</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Groupe</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Route</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Menu</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Ordre</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Rôles</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($permissions as $perm)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center">
                                            @if($perm->icon)
                                                <i class="{{ $perm->icon }} me-2 text-primary"></i>
                                            @endif
                                            <div class="d-flex flex-column">
                                                <span class="text-sm font-weight-bold">{{ $perm->display_name }}</span>
                                                <span class="text-xs text-muted">{{ $perm->name }}</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark">{{ $groupLabels[$perm->group] ?? $perm->group }}</span>
                                    </td>
                                    <td>
                                        <span class="text-xs text-muted">{{ $perm->route_name ?? '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        @if($perm->is_menu)
                                            <i class="fas fa-check text-success"></i>
                                        @else
                                            <i class="fas fa-times text-muted"></i>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="text-sm">{{ $perm->order }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-secondary">{{ $perm->roles_count }}</span>
                                    </td>
                                    <td class="text-center">
                                        <button class="btn btn-sm btn-outline-warning px-2 py-1" wire:click="openPermissionForm({{ $perm->id }})" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="confirmDelete('permission', {{ $perm->id }})" title="Supprimer">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted">Aucune permission trouvée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Modal Formulaire Rôle --}}
    @if($showRoleForm)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-{{ $editingRoleId ? 'edit' : 'plus' }} me-2"></i>
                            {{ $editingRoleId ? 'Modifier le rôle' : 'Nouveau rôle' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeRoleForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <h6 class="text-primary mb-3">Informations du rôle</h6>

                                <div class="mb-3">
                                    <label class="form-label">Identifiant <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="role_name"
                                           placeholder="ex: manager_junior"
                                           @if($editingRoleId && $roles->firstWhere('id', $editingRoleId)?->is_system) disabled @endif>
                                    <small class="text-muted">Lettres minuscules et underscores uniquement</small>
                                    @error('role_name') <small class="text-danger d-block">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Nom d'affichage <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" wire:model="role_display_name" placeholder="ex: Manager Junior">
                                    @error('role_display_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" wire:model="role_description" rows="3" placeholder="Description du rôle..."></textarea>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="text-primary mb-0">Permissions ({{ count($role_permissions) }} sélectionnées)</h6>
                                    <div class="btn-group btn-group-sm">
                                        <button type="button" class="btn btn-outline-primary" wire:click="selectAllPermissions">Tout sélectionner</button>
                                        <button type="button" class="btn btn-outline-secondary" wire:click="deselectAllPermissions">Tout désélectionner</button>
                                    </div>
                                </div>

                                <div class="row">
                                    @foreach($permissionsGrouped as $group => $perms)
                                        <div class="col-md-6 mb-3">
                                            <div class="card border h-100">
                                                <div class="card-header py-2 d-flex justify-content-between align-items-center">
                                                    <strong class="text-sm">{{ $groupLabels[$group] ?? $group }}</strong>
                                                    <button type="button" class="btn btn-link btn-sm p-0" wire:click="selectGroupPermissions('{{ $group }}')">
                                                        <small>Tout</small>
                                                    </button>
                                                </div>
                                                <div class="card-body py-2">
                                                    @foreach($perms as $perm)
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox"
                                                                   id="perm_{{ $perm->id }}"
                                                                   wire:click="togglePermission({{ $perm->id }})"
                                                                   @checked(in_array($perm->id, $role_permissions))>
                                                            <label class="form-check-label text-sm" for="perm_{{ $perm->id }}">
                                                                @if($perm->icon)
                                                                    <i class="{{ $perm->icon }} me-1 text-muted"></i>
                                                                @endif
                                                                {{ $perm->display_name }}
                                                            </label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeRoleForm">Annuler</button>
                        <button type="button" class="btn btn-primary" wire:click="saveRole">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Formulaire Permission --}}
    @if($showPermissionForm)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-info">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-{{ $editingPermissionId ? 'edit' : 'plus' }} me-2"></i>
                            {{ $editingPermissionId ? 'Modifier la permission' : 'Nouvelle permission' }}
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closePermissionForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Identifiant <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="perm_name" placeholder="ex: reports.sales">
                                @error('perm_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Nom d'affichage <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" wire:model="perm_display_name" placeholder="ex: Rapports Ventes">
                                @error('perm_display_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Groupe <span class="text-danger">*</span></label>
                                <select class="form-select" wire:model="perm_group">
                                    @foreach($groupLabels as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Route Laravel</label>
                                <input type="text" class="form-control" wire:model="perm_route_name" placeholder="ex: reports.sales">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Icône</label>
                                <input type="text" class="form-control" wire:model="perm_icon" placeholder="ex: ni ni-chart-bar-32">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Ordre</label>
                                <input type="number" class="form-control" wire:model="perm_order" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Menu?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" wire:model="perm_is_menu">
                                    <label class="form-check-label">{{ $perm_is_menu ? 'Oui' : 'Non' }}</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closePermissionForm">Annuler</button>
                        <button type="button" class="btn btn-info" wire:click="savePermission">
                            <i class="fas fa-save me-2"></i>Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Suppression --}}
    @if($showDeleteModal)
        <div class="modal fade show d-block" style="background-color: rgba(0,0,0,0.5);" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-danger">
                        <h5 class="modal-title text-white">
                            <i class="fas fa-trash me-2"></i>Confirmer la suppression
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeDeleteModal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <div class="mb-4">
                            <i class="fas fa-exclamation-triangle text-danger" style="font-size: 64px;"></i>
                        </div>
                        <h5>Êtes-vous sûr de vouloir supprimer {{ $deleteType === 'role' ? 'ce rôle' : 'cette permission' }} ?</h5>
                        <p class="text-muted mb-3">{{ $deleteInfo }}</p>
                        <div class="alert alert-danger">
                            <i class="fas fa-info-circle me-2"></i>
                            Cette action est irréversible.
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center">
                        <button type="button" class="btn btn-secondary" wire:click="closeDeleteModal">Annuler</button>
                        <button type="button" class="btn btn-danger" wire:click="delete">
                            <i class="fas fa-trash me-2"></i>Oui, supprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
