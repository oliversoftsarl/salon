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

    <div class="row mb-4">
        <div class="col-lg-7">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-fat-add me-2"></i>Nouvelle Catégorie de Dépense</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Clé (optionnelle)</label>
                            <input type="text" class="form-control" wire:model.defer="new_key" placeholder="ex: transport">
                            @error('new_key') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-5">
                            <label class="form-label">Libellé *</label>
                            <input type="text" class="form-control" wire:model.defer="new_label" placeholder="ex: Transport">
                            @error('new_label') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-3">
                            <button class="btn btn-dark w-100" wire:click="add">
                                <i class="ni ni-check-bold me-1"></i> Ajouter
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="mb-2"><i class="ni ni-bullet-list-67 me-2"></i>Gestion centralisée</h6>
                    <p class="text-sm text-muted mb-0">
                        Les catégories définies ici sont utilisées dans la caisse (formulaire, filtres, statistiques et export Excel).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <h6 class="mb-0"><i class="ni ni-settings me-2"></i>Catégories de Dépenses</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Clé</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Libellé</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $key => $label)
                            <tr>
                                <td class="ps-3"><code>{{ $key }}</code></td>
                                <td>
                                    <span class="text-sm font-weight-bold">{{ $label }}</span>
                                </td>
                                <td class="text-end pe-3">
                                    <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="openEdit('{{ $key }}')" title="Modifier">
                                        <i class="ni ni-ruler-pencil"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete('{{ $key }}')" onclick="return confirm('Supprimer cette catégorie ?')" title="Supprimer">
                                        <i class="ni ni-fat-remove"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-4 text-muted">
                                    Aucune catégorie configurée.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($showEditModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title"><i class="ni ni-ruler-pencil me-2"></i>Modifier la catégorie</h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeEdit"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Clé *</label>
                            <input type="text" class="form-control" wire:model.defer="editing_key">
                            @error('editing_key') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div>
                            <label class="form-label">Libellé *</label>
                            <input type="text" class="form-control" wire:model.defer="editing_label">
                            @error('editing_label') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeEdit">Annuler</button>
                        <button type="button" class="btn btn-primary" wire:click="saveEdit">
                            <i class="ni ni-check-bold me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

