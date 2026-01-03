<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    <div class="row g-3">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div class="btn-group">
                        <button class="btn btn-outline-secondary btn-sm" wire:click="previousWeek"><i class="ni ni-bold-left"></i></button>
                        <button class="btn btn-outline-secondary btn-sm" wire:click="nextWeek"><i class="ni ni-bold-right"></i></button>
                    </div>
                    <h6 class="mb-0">Semaine du {{ \Carbon\Carbon::parse($weekStart)->startOfWeek()->format('d/m/Y') }}</h6>
                    <div></div>
                </div>
                <div class="card-body p-2 p-md-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" style="min-width: 600px;">
                            <thead>
                            <tr>
                                @foreach($days as $day)
                                    <th class="text-center py-2 {{ $day->isToday() ? 'bg-gradient-primary text-white' : '' }}" style="font-size: 0.75rem;">
                                        {{ $day->isoFormat('ddd') }}<br>
                                        <span class="font-weight-bold">{{ $day->format('d') }}</span>
                                    </th>
                                @endforeach
                            </tr>
                            </thead>
                            <tbody>
                            <tr>
                                @foreach($days as $day)
                                    <td style="min-height: 120px; vertical-align: top; padding: 4px; {{ $day->isToday() ? 'background: rgba(94, 114, 228, 0.05);' : '' }}">
                                        @foreach($appointments->whereBetween('start_at', [$day->copy()->startOfDay(), $day->copy()->endOfDay()]) as $rdv)
                                            <div class="p-1 mb-1 border-start border-3 border-primary bg-light rounded-1" style="font-size: 0.75rem;">
                                                <strong class="d-block text-truncate" style="max-width: 90px;" title="{{ $rdv->service->name ?? '' }}">{{ $rdv->service->name ?? '' }}</strong>
                                                <span class="text-xs text-primary">{{ \Carbon\Carbon::parse($rdv->start_at)->format('H:i') }}</span>
                                                <small class="text-secondary d-block text-truncate" style="max-width: 90px;" title="{{ $rdv->client->name ?? '' }}">{{ $rdv->client->name ?? '' }}</small>
                                            </div>
                                        @endforeach
                                    </td>
                                @endforeach
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">Nouveau rendez-vous</div>
                <div class="card-body">
                    <div class="mb-2">
                        <label class="form-label">Client</label>
                        <select class="form-select" wire:model="client_id">
                            <option value="">-- Sélectionner --</option>
                            @foreach($clients as $c)
                                <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                            @endforeach
                        </select>
                        @error('client_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Staff</label>
                        <select class="form-select" wire:model="staff_id">
                            <option value="">-- Sélectionner --</option>
                            @foreach($staff as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('staff_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Service</label>
                        <select class="form-select" wire:model="service_id">
                            <option value="">-- Sélectionner --</option>
                            @foreach($services as $s)
                                <option value="{{ $s->id }}">{{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('service_id') <small class="text-danger">{{ $message }}</small> @enderror
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label">Début</label>
                            <input type="datetime-local" class="form-control" wire:model="start_at">
                            @error('start_at') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-6">
                            <label class="form-label">Fin</label>
                            <input type="datetime-local" class="form-control" wire:model="end_at">
                            @error('end_at') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-success" wire:click="createAppointment"><i class="ni ni-check-bold me-1"></i> Créer</button>
                </div>
            </div>
        </div>
    </div>
</div>
