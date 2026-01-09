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

    {{-- Taux actuel --}}
    <div class="row mb-4">
        <div class="col-lg-6">
            <div class="card bg-gradient-primary text-white">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-sm mb-1 text-uppercase" style="opacity: 0.8;">Taux de Change Actuel</p>
                            <h2 class="mb-0 font-weight-bolder">
                                @if($currentRate)
                                    1 $ = {{ number_format($currentRate->rate, 2, ',', ' ') }} FC
                                @else
                                    Non défini
                                @endif
                            </h2>
                            @if($currentRate)
                                <small style="opacity: 0.8;">
                                    Effectif depuis le {{ $currentRate->effective_date->format('d/m/Y') }}
                                </small>
                            @endif
                        </div>
                        <div>
                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-primary text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-body d-flex flex-column justify-content-center">
                    <h6 class="mb-3"><i class="ni ni-settings-gear-65 me-2"></i>Configuration du Taux</h6>
                    <p class="text-sm text-muted mb-3">
                        Définissez le taux de change USD/CDF pour convertir automatiquement les prix affichés.
                    </p>
                    <button class="btn btn-primary" wire:click="openForm">
                        <i class="ni ni-fat-add me-1"></i> Nouveau Taux
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Convertisseur rapide --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-curved-next me-2"></i>Convertisseur Rapide</h6>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label">Montant en FC</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="amountCdf" placeholder="0" oninput="convertCurrency('cdf')">
                                <span class="input-group-text">FC</span>
                            </div>
                        </div>
                        <div class="col-md-1 text-center">
                            <i class="ni ni-bold-right text-primary" style="font-size: 24px;"></i>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Équivalent en USD</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" class="form-control" id="amountUsd" placeholder="0.00" step="0.01" oninput="convertCurrency('usd')">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="bg-light rounded p-2 text-center">
                                <small class="text-muted">Taux:</small>
                                <strong id="currentRateDisplay">{{ $currentRate ? number_format($currentRate->rate, 2, ',', ' ') : '0' }}</strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Historique des taux --}}
    <div class="card">
        <div class="card-header pb-0">
            <h6 class="mb-0"><i class="ni ni-bullet-list-67 me-2"></i>Historique des Taux de Change</h6>
            <p class="text-sm text-muted mb-0">USD → CDF</p>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Date d'effet</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Taux</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Statut</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Notes</th>
                            <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($rates as $rate)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex flex-column">
                                        <span class="text-sm font-weight-bold">{{ $rate->effective_date->format('d/m/Y') }}</span>
                                        <span class="text-xs text-muted">Créé le {{ $rate->created_at->format('d/m/Y H:i') }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-sm font-weight-bold">1 $ = {{ number_format($rate->rate, 2, ',', ' ') }} FC</span>
                                </td>
                                <td class="text-center">
                                    @if($rate->is_active)
                                        <span class="badge bg-success">
                                            <i class="ni ni-check-bold me-1"></i>Actif
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">Inactif</span>
                                    @endif
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="text-xs text-muted text-truncate d-inline-block" style="max-width: 150px;" title="{{ $rate->notes }}">
                                        {{ $rate->notes ?? '—' }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    @if(!$rate->is_active)
                                        <button class="btn btn-sm btn-outline-success px-2 py-1" wire:click="activate({{ $rate->id }})" title="Activer">
                                            <i class="ni ni-check-bold"></i>
                                        </button>
                                    @endif
                                    <button class="btn btn-sm btn-outline-primary px-2 py-1" wire:click="edit({{ $rate->id }})" title="Modifier">
                                        <i class="ni ni-ruler-pencil"></i>
                                    </button>
                                    @if(!$rate->is_active)
                                        <button class="btn btn-sm btn-outline-danger px-2 py-1" wire:click="delete({{ $rate->id }})" onclick="return confirm('Supprimer ce taux ?')" title="Supprimer">
                                            <i class="ni ni-fat-remove"></i>
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">
                                    <i class="ni ni-money-coins" style="font-size: 32px;"></i>
                                    <p class="mt-2 mb-0">Aucun taux de change configuré</p>
                                    <button class="btn btn-sm btn-primary mt-2" wire:click="openForm">
                                        <i class="ni ni-fat-add me-1"></i> Créer le premier taux
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($rates->hasPages())
            <div class="card-footer">
                {{ $rates->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Formulaire --}}
    @if($showForm)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title">
                            <i class="ni ni-money-coins me-2"></i>
                            {{ $editingId ? 'Modifier' : 'Nouveau' }} Taux de Change
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeForm"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            {{-- Conversion affichée --}}
                            <div class="col-12">
                                <div class="bg-light rounded p-3 text-center">
                                    <span class="h5">1 $ (USD) = </span>
                                    <span class="h4 text-primary font-weight-bold">{{ number_format($form_rate, 2, ',', ' ') }}</span>
                                    <span class="h5"> FC (CDF)</span>
                                </div>
                            </div>

                            {{-- Taux --}}
                            <div class="col-md-6">
                                <label class="form-label">Taux de change *</label>
                                <div class="input-group">
                                    <span class="input-group-text">1 $ =</span>
                                    <input type="number" step="0.0001" min="0" class="form-control" wire:model.live="form_rate" placeholder="2800">
                                    <span class="input-group-text">FC</span>
                                </div>
                                @error('form_rate') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Date d'effet --}}
                            <div class="col-md-6">
                                <label class="form-label">Date d'effet *</label>
                                <input type="date" class="form-control" wire:model="form_effective_date">
                                @error('form_effective_date') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>

                            {{-- Actif --}}
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="form_is_active" wire:model="form_is_active">
                                    <label class="form-check-label" for="form_is_active">
                                        <strong>Activer ce taux</strong>
                                        <span class="text-muted d-block text-xs">Ce taux sera utilisé pour toutes les conversions</span>
                                    </label>
                                </div>
                            </div>

                            {{-- Notes --}}
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model="form_notes" placeholder="Ex: Taux du marché parallèle..."></textarea>
                                @error('form_notes') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" wire:click="closeForm">Annuler</button>
                        <button type="button" class="btn btn-primary" wire:click="save">
                            <i class="ni ni-check-bold me-1"></i> {{ $editingId ? 'Modifier' : 'Enregistrer' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    const currentRate = {{ $currentRate ? $currentRate->rate : 2800 }};

    function convertCurrency(from) {
        const cdfInput = document.getElementById('amountCdf');
        const usdInput = document.getElementById('amountUsd');

        if (from === 'cdf') {
            const cdf = parseFloat(cdfInput.value) || 0;
            const usd = cdf / currentRate;
            usdInput.value = usd.toFixed(2);
        } else {
            const usd = parseFloat(usdInput.value) || 0;
            const cdf = usd * currentRate;
            cdfInput.value = Math.round(cdf);
        }
    }
</script>

