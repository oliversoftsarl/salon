<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="row g-3">
        <div class="col-12">
            <div class="card mb-3">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Gestion du staff</h6>
                    <p class="text-sm text-secondary mb-0">Sélectionne un membre du staff pour éditer ses disponibilités et pauses</p>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-6">
                            <label class="form-label">Membre du staff</label>
                            <select class="form-select" wire:model.live="selected_user_id">
                                <option value="">-- Choisir --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($profile)
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <div>
                                        <span class="text-secondary text-sm d-block">Nom d'affichage</span>
                                        <strong>{{ $profile->display_name }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-secondary text-sm d-block">Rôle</span>
                                        <span class="badge bg-gradient-success">{{ $profile->role_title }}</span>
                                    </div>
                                    <div>
                                        <span class="text-secondary text-sm d-block">Taux horaire</span>
                                        <strong>{{ number_format($profile->hourly_rate, 2, ',', ' ') }} FC</strong>
                                    </div>
                                    <div class="ms-auto">
                                        <button class="btn btn-sm btn-outline-primary" wire:click="openProfileModal" title="Modifier le profil">
                                            <i class="ni ni-settings-gear-65 me-1"></i> Modifier
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @if($profile)
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Disponibilités</h6>
                        <span class="text-xs text-secondary">Format JSON</span>
                    </div>
                    <div class="card-body">
                        <textarea class="form-control" rows="12" wire:model="availability_json" placeholder='{"monday":[["09:00","12:00"],["14:00","18:00"]]}'></textarea>
                        @error('availability_json') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-success" wire:click="saveAvailability">
                            <i class="ni ni-check-bold me-1"></i> Enregistrer
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card h-100">
                    <div class="card-header pb-0">
                        <h6 class="mb-0">Ajouter une pause</h6>
                        <p class="text-sm text-secondary mb-0">Définis un créneau de pause (évite les chevauchements)</p>
                    </div>
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label">Début</label>
                                <input type="datetime-local" class="form-control" wire:model="break_start_at">
                                @error('break_start_at') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Fin</label>
                                <input type="datetime-local" class="form-control" wire:model="break_end_at">
                                @error('break_end_at') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-12">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="recurring" wire:model="break_recurring">
                                    <label class="form-check-label" for="recurring">Récurrente</label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button class="btn btn-primary" wire:click="addBreak">
                            <i class="ni ni-fat-add me-1"></i> Ajouter
                        </button>
                    </div>
                </div>
            </div>
            <div class="col-12">
                <div class="card">
                    <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                        <h6 class="mb-0">Pauses</h6>
                        <span class="text-sm text-secondary">Dernières 50</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Début</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Fin</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 90px;">Récurrente</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3" style="width: 80px;">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($breaks as $b)
                                <tr>
                                    <td class="ps-3">
                                        <div class="d-flex flex-column">
                                            <span class="text-sm font-weight-bold">{{ \Carbon\Carbon::parse($b->start_at)->format('d/m/Y H:i') }}</span>
                                            <span class="text-xs text-secondary d-md-none">→ {{ \Carbon\Carbon::parse($b->end_at)->format('H:i') }}</span>
                                        </div>
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <span class="text-sm">{{ \Carbon\Carbon::parse($b->end_at)->format('d/m/Y H:i') }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-{{ $b->recurring ? 'success' : 'secondary' }}">{{ $b->recurring ? 'Oui' : 'Non' }}</span>
                                    </td>
                                    <td class="text-end pe-3">
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="deleteBreak({{ $b->id }})" onclick="return confirm('Supprimer cette pause ?')" title="Supprimer">
                                            <i class="ni ni-fat-remove"></i>
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="4" class="text-center py-4 text-muted">Aucune pause</td></tr>
                            @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal d'édition du profil -->
    @if($showProfileModal)
    <div class="modal fade show d-block" tabindex="-1" style="background-color: rgba(0,0,0,0.5);">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="ni ni-single-02 me-2"></i>Modifier le profil du staff
                    </h5>
                    <button type="button" class="btn-close" wire:click="closeProfileModal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nom d'affichage <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" wire:model="edit_display_name" placeholder="Ex: Jean-Pierre">
                        @error('edit_display_name') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Rôle / Fonction <span class="text-danger">*</span></label>
                        <select class="form-select form-select-lg" wire:model="edit_role_title">
                            <option value="">-- Sélectionner un rôle --</option>
                            @foreach($availableRoles as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('edit_role_title') <small class="text-danger">{{ $message }}</small> @enderror
                        <small class="text-muted">Inclut: Coiffeur, Masseuse/Masseur, Esthéticien, etc.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Taux horaire (FC) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" step="0.01" min="0" class="form-control form-control-lg" wire:model="edit_hourly_rate" placeholder="0.00">
                            <span class="input-group-text">FC/heure</span>
                        </div>
                        @error('edit_hourly_rate') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" wire:click="closeProfileModal">Annuler</button>
                    <button type="button" class="btn btn-success" wire:click="saveProfile">
                        <i class="ni ni-check-bold me-1"></i> Enregistrer
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
