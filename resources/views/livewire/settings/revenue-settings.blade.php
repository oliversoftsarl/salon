<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="ni ni-check-bold me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="ni ni-fat-remove me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        {{-- Paramètre du montant cible --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-settings-gear-65 me-2"></i>Montant Cible Hebdomadaire</h6>
                    <p class="text-sm text-secondary mb-0">Définir le montant que chaque coiffeur doit atteindre par semaine</p>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label class="form-label">Montant cible (FC)</label>
                            <div class="input-group">
                                <input type="number" class="form-control form-control-lg" wire:model="weekly_revenue_target" min="0" step="1000">
                                <span class="input-group-text">FC / semaine</span>
                            </div>
                            @error('weekly_revenue_target') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-4">
                            <button class="btn btn-success w-100" wire:click="saveTarget">
                                <i class="ni ni-check-bold me-1"></i> Enregistrer
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Calculer les recettes --}}
        <div class="col-lg-6">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-calendar-grid-58 me-2"></i>Calculer les Recettes</h6>
                    <p class="text-sm text-secondary mb-0">Calculer les recettes hebdomadaires pour tous les coiffeurs</p>
                </div>
                <div class="card-body">
                    <p class="text-sm mb-3">
                        Utilisez les boutons ci-dessous pour calculer les recettes des coiffeurs et barbiers.
                    </p>

                    <div class="d-flex flex-wrap gap-2">
                        {{-- Bouton semaine dernière --}}
                        <button class="btn btn-outline-primary" wire:click="calculateLastWeek" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="calculateLastWeek">
                                <i class="ni ni-bold-left me-1"></i> Semaine Dernière
                            </span>
                            <span wire:loading wire:target="calculateLastWeek">
                                <span class="spinner-border spinner-border-sm me-1"></span> Calcul...
                            </span>
                        </button>

                        {{-- Bouton semaine en cours --}}
                        <button class="btn btn-primary" wire:click="calculateCurrentWeek" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="calculateCurrentWeek">
                                <i class="ni ni-ruler-pencil me-1"></i> Semaine en Cours
                            </span>
                            <span wire:loading wire:target="calculateCurrentWeek">
                                <span class="spinner-border spinner-border-sm me-1"></span> Calcul...
                            </span>
                        </button>

                        {{-- Bouton autre semaine --}}
                        <button class="btn btn-outline-secondary" wire:click="openCalculateModal" wire:loading.attr="disabled">
                            <i class="ni ni-calendar-grid-58 me-1"></i> Autre Semaine
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Résumé des manquants par coiffeur --}}
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-money-coins me-2"></i>Cumul des Manquants par Coiffeur</h6>
                    <p class="text-sm text-secondary mb-0">Total des montants manquants accumulés</p>
                </div>
                <div class="card-body">
                    @if($staffShortages->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Coiffeur</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Fonction</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Cumul Manquants</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffShortages as $staff)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-gradient-primary rounded-circle">
                                                        <span class="text-white text-xs">{{ substr($staff->name, 0, 1) }}</span>
                                                    </div>
                                                    <span class="text-sm font-weight-bold">{{ $staff->name }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($staff->staffProfile)
                                                    <span class="badge bg-gradient-success">{{ $staff->staffProfile->role_title }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3">
                                                @if($staff->total_shortage > 0)
                                                    <span class="text-danger font-weight-bold">
                                                        -{{ number_format($staff->total_shortage, 0, ',', ' ') }} FC
                                                    </span>
                                                @else
                                                    <span class="text-success font-weight-bold">0 FC</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ni ni-single-02" style="font-size: 48px;"></i>
                            <p class="mt-2">Aucun coiffeur trouvé</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Modal calculer recettes --}}
    @if($showCalculateModal)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ni ni-calendar-grid-58 me-2"></i>Calculer les Recettes Hebdomadaires</h5>
                        <button type="button" class="btn-close" wire:click="closeCalculateModal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Sélectionner une date de la semaine</label>
                            <input type="date" class="form-control form-control-lg" wire:model="calculate_week_start">
                            @error('calculate_week_start') <small class="text-danger">{{ $message }}</small> @enderror
                            <small class="text-muted d-block mt-2">
                                <i class="ni ni-bulb-61 me-1"></i>
                                Le système calculera automatiquement pour toute la semaine contenant cette date (du lundi au dimanche).
                            </small>
                        </div>
                        <div class="alert alert-info mb-0">
                            <i class="ni ni-info-circle me-2"></i>
                            <strong>Note :</strong> Cette action calculera les recettes pour tous les coiffeurs et barbiers pour la semaine sélectionnée.
                            Les données existantes pour cette semaine seront mises à jour.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" wire:click="closeCalculateModal" wire:loading.attr="disabled">
                            Annuler
                        </button>
                        <button type="button" class="btn btn-primary" wire:click="calculateWeeklyRevenues" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="calculateWeeklyRevenues">
                                <i class="ni ni-check-bold me-1"></i> Calculer
                            </span>
                            <span wire:loading wire:target="calculateWeeklyRevenues">
                                <span class="spinner-border spinner-border-sm me-1"></span> Calcul en cours...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
