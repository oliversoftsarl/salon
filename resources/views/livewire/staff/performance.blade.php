<div>
    {{-- En-tête avec filtres --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="mb-0"><i class="ni ni-chart-bar-32 me-2"></i>Performance des Prestataires</h5>
                            <p class="text-sm text-secondary mb-0">Statistiques des prestations par membre du staff</p>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3 align-items-end">
                        {{-- Filtre période rapide --}}
                        <div class="col-md-3">
                            <label class="form-label">Période</label>
                            <select class="form-select" wire:model.live="period">
                                <option value="today">Aujourd'hui</option>
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                                <option value="year">Cette année</option>
                                <option value="custom">Personnalisée</option>
                            </select>
                        </div>
                        {{-- Date début --}}
                        <div class="col-md-2">
                            <label class="form-label">Du</label>
                            <input type="date" class="form-control" wire:model.live="date_from">
                        </div>
                        {{-- Date fin --}}
                        <div class="col-md-2">
                            <label class="form-label">Au</label>
                            <input type="date" class="form-control" wire:model.live="date_to">
                        </div>
                        {{-- Filtre prestataire --}}
                        <div class="col-md-3">
                            <label class="form-label">Prestataire</label>
                            <select class="form-select" wire:model.live="selected_staff_id">
                                <option value="">— Tous les prestataires —</option>
                                @foreach($staffList as $staff)
                                    <option value="{{ $staff->id }}">{{ $staff->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Bouton reset --}}
                        <div class="col-md-2">
                            <button class="btn btn-outline-secondary w-100" wire:click="$set('selected_staff_id', null)">
                                <i class="ni ni-zoom-split-in me-1"></i> Réinitialiser
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes statistiques globales --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Total Prestations</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($globalStats['total_prestations'] ?? 0) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-scissors text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Chiffre d'affaires</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($globalStats['total_revenue'] ?? 0, 2, ',', ' ') }} FC
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Moyenne / Prestation</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($globalStats['avg_per_prestation'] ?? 0, 2, ',', ' ') }} FC
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-chart-pie-35 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-sm-6">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Clients uniques</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($globalStats['unique_clients'] ?? 0) }}
                                </h5>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-circle-08 text-lg opacity-10" aria-hidden="true"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        {{-- Classement des prestataires --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-trophy me-2"></i>Classement des Prestataires</h6>
                    <p class="text-sm text-secondary mb-0">Par chiffre d'affaires généré</p>
                </div>
                <div class="card-body p-3">
                    @if($staffPerformance->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">#</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prestataire</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Prestations</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">CA Généré</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($staffPerformance as $index => $perf)
                                        <tr>
                                            <td class="ps-2">
                                                @if($index === 0)
                                                    <span class="badge bg-gradient-warning">🥇</span>
                                                @elseif($index === 1)
                                                    <span class="badge bg-gradient-secondary">🥈</span>
                                                @elseif($index === 2)
                                                    <span class="badge bg-gradient-dark">🥉</span>
                                                @else
                                                    <span class="text-sm text-secondary">{{ $index + 1 }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm me-2 bg-gradient-primary rounded-circle">
                                                        <span class="text-white text-xs">{{ substr($perf->staff->name ?? '?', 0, 1) }}</span>
                                                    </div>
                                                    <div>
                                                        <span class="text-sm font-weight-bold">{{ $perf->staff->name ?? 'Inconnu' }}</span>
                                                        @if($perf->staff?->staffProfile)
                                                            <br><span class="badge bg-gradient-success text-xxs">{{ $perf->staff->staffProfile->role_title }}</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-info">{{ $perf->total_prestations }}</span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <span class="text-sm font-weight-bold text-success">
                                                    {{ number_format($perf->total_revenue, 2, ',', ' ') }} FC
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ni ni-chart-bar-32" style="font-size: 48px;"></i>
                            <p class="mt-2">Aucune prestation sur cette période</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Détail par service (si prestataire sélectionné) --}}
        <div class="col-lg-6">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-bullet-list-67 me-2"></i>Répartition par Service</h6>
                    <p class="text-sm text-secondary mb-0">
                        @if($selected_staff_id)
                            Services réalisés par le prestataire sélectionné
                        @else
                            Sélectionnez un prestataire pour voir le détail
                        @endif
                    </p>
                </div>
                <div class="card-body p-3">
                    @if($selected_staff_id && $servicesByStaff->count() > 0)
                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Service</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Nombre</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Revenus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($servicesByStaff as $item)
                                        <tr>
                                            <td class="ps-2">
                                                <span class="text-sm font-weight-bold">{{ $item->service->name ?? 'Service supprimé' }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-primary">{{ $item->count }}</span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <span class="text-sm font-weight-bold">{{ number_format($item->revenue, 2, ',', ' ') }} FC</span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @elseif($selected_staff_id)
                        <div class="text-center py-4 text-muted">
                            <i class="ni ni-scissors" style="font-size: 48px;"></i>
                            <p class="mt-2">Aucune prestation pour ce prestataire sur cette période</p>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ni ni-single-02" style="font-size: 48px;"></i>
                            <p class="mt-2">Sélectionnez un prestataire pour voir ses services</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Classement des Masseurs --}}
    @if(isset($masseurPerformance) && $masseurPerformance->count() > 0)
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-satisfied me-2"></i>Classement des Masseurs</h6>
                    <p class="text-sm text-secondary mb-0">Performance des masseurs par chiffre d'affaires généré</p>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">#</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Masseur</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center">Massages</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">CA Généré</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($masseurPerformance as $index => $perf)
                                    <tr>
                                        <td class="ps-2">
                                            @if($index === 0)
                                                <span class="badge bg-gradient-warning">🥇</span>
                                            @elseif($index === 1)
                                                <span class="badge bg-gradient-secondary">🥈</span>
                                            @elseif($index === 2)
                                                <span class="badge bg-gradient-dark">🥉</span>
                                            @else
                                                <span class="text-sm text-secondary">{{ $index + 1 }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm me-2 bg-gradient-info rounded-circle">
                                                    <span class="text-white text-xs">{{ substr($perf->staff->name ?? '?', 0, 1) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-sm font-weight-bold">{{ $perf->staff->name ?? 'Inconnu' }}</span>
                                                    @if($perf->staff?->staffProfile)
                                                        <br><span class="badge bg-gradient-info text-xxs">{{ $perf->staff->staffProfile->role_title }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info">{{ $perf->total_prestations }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="text-sm font-weight-bold text-success">
                                                {{ number_format($perf->total_revenue, 2, ',', ' ') }} FC
                                            </span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Graphique évolution journalière --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-chart-pie-35 me-2"></i>Évolution Journalière</h6>
                    <p class="text-sm text-secondary mb-0">Prestations et revenus par jour</p>
                </div>
                <div class="card-body">
                    @if($dailyStats->count() > 0)
                        <div class="chart-container" style="height: 300px;">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    @else
                        <div class="text-center py-4 text-muted">
                            <i class="ni ni-chart-bar-32" style="font-size: 48px;"></i>
                            <p class="mt-2">Aucune donnée disponible pour cette période</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Liste des prestations récentes --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-time-alarm me-2"></i>Prestations Récentes</h6>
                    <p class="text-sm text-secondary mb-0">Dernières prestations effectuées</p>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Service</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Prestataire</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 d-none d-md-table-cell">Client</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPrestations as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="text-xs text-secondary">{{ $item->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm font-weight-bold">{{ $item->service->name ?? '—' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2 bg-gradient-primary rounded-circle">
                                                    <span class="text-white" style="font-size: 10px;">{{ substr($item->stylist->name ?? '?', 0, 1) }}</span>
                                                </div>
                                                <span class="text-sm">{{ $item->stylist->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            <span class="text-sm">{{ $item->transaction->client->name ?? 'Client de passage' }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success">{{ number_format($item->line_total, 2, ',', ' ') }} FC</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted">Aucune prestation</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                @if($recentPrestations->hasPages())
                    <div class="card-footer">
                        {{ $recentPrestations->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@script
<script>
    // Graphique Chart.js pour l'évolution journalière
    const dailyData = @json($dailyStats);

    if (dailyData.length > 0) {
        const ctx = document.getElementById('dailyChart');
        if (ctx) {
            new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: dailyData.map(d => {
                        const date = new Date(d.date);
                        return date.toLocaleDateString('fr-FR', { day: '2-digit', month: 'short' });
                    }),
                    datasets: [
                        {
                            label: 'Revenus (FC)',
                            data: dailyData.map(d => d.revenue),
                            backgroundColor: 'rgba(94, 114, 228, 0.8)',
                            borderRadius: 4,
                            yAxisID: 'y',
                        },
                        {
                            label: 'Prestations',
                            data: dailyData.map(d => d.count),
                            type: 'line',
                            borderColor: '#2dce89',
                            backgroundColor: 'transparent',
                            tension: 0.4,
                            yAxisID: 'y1',
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    interaction: {
                        mode: 'index',
                        intersect: false,
                    },
                    plugins: {
                        legend: {
                            position: 'top',
                        }
                    },
                    scales: {
                        y: {
                            type: 'linear',
                            display: true,
                            position: 'left',
                            title: {
                                display: true,
                                text: 'Revenus (FC)'
                            }
                        },
                        y1: {
                            type: 'linear',
                            display: true,
                            position: 'right',
                            title: {
                                display: true,
                                text: 'Prestations'
                            },
                            grid: {
                                drawOnChartArea: false,
                            },
                        },
                    }
                }
            });
        }
    }
</script>
@endscript

