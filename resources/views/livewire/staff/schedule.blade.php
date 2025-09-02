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
                            <select class="form-select" wire:model="selected_user_id">
                                <option value="">-- Choisir --</option>
                                @foreach($users as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if($profile)
                            <div class="col-md-6">
                                <div class="d-flex flex-wrap gap-3">
                                    <div>
                                        <span class="text-secondary text-sm d-block">Nom d’affichage</span>
                                        <strong>{{ $profile->display_name }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-secondary text-sm d-block">Rôle</span>
                                        <strong>{{ $profile->role_title }}</strong>
                                    </div>
                                    <div>
                                        <span class="text-secondary text-sm d-block">Taux horaire</span>
                                        <strong>{{ number_format($profile->hourly_rate, 2, ',', ' ') }} €</strong>
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
                                <th>Début</th>
                                <th>Fin</th>
                                <th>Récurrente</th>
                                <th class="text-end">Actions</th>
                            </tr>
                            </thead>
                            <tbody>
                            @forelse($breaks as $b)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($b->start_at)->format('d/m/Y H:i') }}</td>
                                    <td>{{ \Carbon\Carbon::parse($b->end_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge {{ $b->recurring ? 'bg-success' : 'bg-dark' }}">{{ $b->recurring ? 'Oui' : 'Non' }}</span>
                                    </td>
                                    <td class="text-end">
                                        <button class="btn btn-sm btn-outline-danger" wire:click="deleteBreak({{ $b->id }})" onclick="return confirm('Supprimer cette pause ?')">Supprimer</button>
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
</div>
