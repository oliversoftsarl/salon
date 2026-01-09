<div>
    {{-- Filtres de période --}}
    <div class="row mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body py-3">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                        <div>
                            <h5 class="mb-0"><i class="ni ni-tv-2 me-2"></i>Tableau de Bord</h5>
                            <p class="text-sm text-secondary mb-0">Vue d'ensemble de votre activité</p>
                        </div>
                        <div class="d-flex gap-2 align-items-center">
                            <select class="form-select form-select-sm" wire:model.live="period" style="width: auto;">
                                <option value="week">Cette semaine</option>
                                <option value="month">Ce mois</option>
                                <option value="quarter">Ce trimestre</option>
                                <option value="year">Cette année</option>
                            </select>
                            <input type="date" class="form-control form-control-sm" wire:model.live="date_from" style="width: auto;">
                            <span class="text-muted">→</span>
                            <input type="date" class="form-control form-control-sm" wire:model.live="date_to" style="width: auto;">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Cartes statistiques principales --}}
    <div class="row mb-4">
        <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
            <div class="card">
                <div class="card-body p-3">
                    <div class="row">
                        <div class="col-8">
                            <div class="numbers">
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Ventes Aujourd'hui</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($generalStats['today_sales'], 0, ',', ' ') }} FC
                                </h5>
                                <small class="text-muted">{{ $generalStats['today_transactions'] }} transaction(s)</small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-lg opacity-10"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Revenus (période)</p>
                                <h5 class="font-weight-bolder mb-0">
                                    {{ number_format($revenueStats['current_total'], 0, ',', ' ') }} FC
                                </h5>
                                <small class="{{ $revenueStats['percent_change'] >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="ni ni-{{ $revenueStats['percent_change'] >= 0 ? 'bold-up' : 'bold-down' }}"></i>
                                    {{ $revenueStats['percent_change'] }}% vs période préc.
                                </small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md">
                                <i class="ni ni-chart-bar-32 text-lg opacity-10"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Clients (période)</p>
                                <h5 class="font-weight-bolder mb-0">{{ $clientStats['unique_clients'] }}</h5>
                                <small class="text-info">
                                    +{{ $clientStats['new_clients'] }} nouveaux
                                </small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md">
                                <i class="ni ni-circle-08 text-lg opacity-10"></i>
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
                                <p class="text-sm mb-0 text-capitalize font-weight-bold">Solde Caisse</p>
                                <h5 class="font-weight-bolder mb-0 {{ $cashBalance >= 0 ? 'text-success' : 'text-danger' }}">
                                    {{ number_format($cashBalance, 0, ',', ' ') }} FC
                                </h5>
                                <small class="text-muted">Total en caisse</small>
                            </div>
                        </div>
                        <div class="col-4 text-end">
                            <div class="icon icon-shape bg-gradient-warning shadow text-center border-radius-md">
                                <i class="ni ni-credit-card text-lg opacity-10"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphiques --}}
    <div class="row mb-4">
        {{-- Graphique Revenus --}}
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0"><i class="ni ni-chart-bar-32 me-2 text-primary"></i>Évolution des Revenus</h6>
                            <p class="text-sm text-secondary mb-0">
                                Panier moyen: <strong>{{ number_format($revenueStats['average_ticket'], 0, ',', ' ') }} FC</strong>
                            </p>
                        </div>
                        <span class="badge bg-gradient-primary">{{ $revenueStats['transactions_count'] }} ventes</span>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container" style="height: 300px;">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats clients --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-single-02 me-2 text-info"></i>Fréquentation Clients</h6>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-sm">Clients uniques</span>
                        <span class="badge bg-info">{{ $clientStats['unique_clients'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-sm">Nouveaux clients</span>
                        <span class="badge bg-success">{{ $clientStats['new_clients'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-sm">Clients fidèles (2+ visites)</span>
                        <span class="badge bg-primary">{{ $clientStats['loyal_clients'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-sm">Visites totales</span>
                        <span class="badge bg-dark">{{ $clientStats['total_visits'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-sm">Clients de passage</span>
                        <span class="badge bg-secondary">{{ $clientStats['walk_in_clients'] }}</span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center py-2">
                        <span class="text-sm font-weight-bold">Visites / client</span>
                        <span class="badge bg-gradient-info">{{ $clientStats['average_visits'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Graphique fréquentation + Top services/produits --}}
    <div class="row mb-4">
        {{-- Graphique fréquentation --}}
        <div class="col-lg-6 mb-4 mb-lg-0">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-chart-pie-35 me-2 text-success"></i>Fréquentation Journalière</h6>
                    <p class="text-sm text-secondary mb-0">Clients et visites par jour</p>
                </div>
                <div class="card-body p-3">
                    <div class="chart-container" style="height: 250px;">
                        <canvas id="clientChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Top Services --}}
        <div class="col-lg-3 mb-4 mb-lg-0">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-scissors me-2 text-warning"></i>Top Services</h6>
                </div>
                <div class="card-body p-3">
                    @forelse($topServices as $index => $item)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : 'dark') }} me-2">{{ $index + 1 }}</span>
                                <span class="text-sm text-truncate" style="max-width: 100px;" title="{{ $item->service->name ?? 'N/A' }}">
                                    {{ $item->service->name ?? 'N/A' }}
                                </span>
                            </div>
                            <span class="text-xs text-muted">{{ $item->count }}x</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Aucune donnée</p>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Top Produits --}}
        <div class="col-lg-3">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-box-2 me-2 text-danger"></i>Top Produits</h6>
                </div>
                <div class="card-body p-3">
                    @forelse($topProducts as $index => $item)
                        <div class="d-flex justify-content-between align-items-center py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                            <div class="d-flex align-items-center">
                                <span class="badge bg-{{ $index === 0 ? 'danger' : ($index === 1 ? 'secondary' : 'dark') }} me-2">{{ $index + 1 }}</span>
                                <span class="text-sm text-truncate" style="max-width: 100px;" title="{{ $item->product->name ?? 'N/A' }}">
                                    {{ $item->product->name ?? 'N/A' }}
                                </span>
                            </div>
                            <span class="text-xs text-muted">{{ $item->qty }} vendus</span>
                        </div>
                    @empty
                        <p class="text-muted text-center py-3">Aucune donnée</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Transactions récentes + Infos rapides --}}
    <div class="row">
        {{-- Transactions récentes --}}
        <div class="col-lg-8 mb-4 mb-lg-0">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ni ni-bullet-list-67 me-2"></i>Dernières Transactions</h6>
                    <a href="{{ route('pos.transactions') }}" class="btn btn-sm btn-outline-primary">Voir tout</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-3">Référence</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Client</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Date</th>
                                    <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-end pe-3">Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentTransactions as $tx)
                                    <tr>
                                        <td class="ps-3">
                                            <span class="text-sm font-weight-bold">{{ $tx->reference }}</span>
                                        </td>
                                        <td>
                                            <span class="text-sm">{{ $tx->client->first_name ?? 'Client de passage' }} {{ $tx->client->last_name ?? '' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-xs text-secondary">{{ $tx->created_at->format('d/m/Y H:i') }}</span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="badge bg-success">{{ number_format($tx->total, 0, ',', ' ') }} FC</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">Aucune transaction</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Raccourcis rapides --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header pb-0">
                    <h6 class="mb-0"><i class="ni ni-spaceship me-2"></i>Accès Rapides</h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <a href="{{ route('pos.checkout') }}" class="btn btn-success btn-lg">
                            <i class="ni ni-cart me-2"></i> Nouvelle Vente
                        </a>
                        <a href="{{ route('clients.index') }}" class="btn btn-info">
                            <i class="ni ni-single-02 me-2"></i> Gérer Clients
                        </a>
                        <a href="{{ route('appointments.calendar') }}" class="btn btn-primary">
                            <i class="ni ni-calendar-grid-58 me-2"></i> Rendez-vous
                        </a>
                        <a href="{{ route('cash.register') }}" class="btn btn-warning">
                            <i class="ni ni-money-coins me-2"></i> Gestion Caisse
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('livewire:navigated', initCharts);
document.addEventListener('DOMContentLoaded', initCharts);

function initCharts() {
    // Données pour les graphiques
    const revenueData = @json($dailyRevenueChart);
    const clientData = @json($clientFrequencyChart);
    const exchangeRate = {{ $currentExchangeRate?->rate ?? 2800 }};

    // Convertir les revenus FC en USD pour l'affichage
    const revenuesInUsd = revenueData.revenues.map(r => (r / exchangeRate).toFixed(2));

    // Détruire les anciens graphiques s'ils existent
    if (window.revenueChartInstance) {
        window.revenueChartInstance.destroy();
    }
    if (window.clientChartInstance) {
        window.clientChartInstance.destroy();
    }

    // Graphique des revenus
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx && typeof Chart !== 'undefined') {
        window.revenueChartInstance = new Chart(revenueCtx, {
            type: 'bar',
            data: {
                labels: revenueData.labels,
                datasets: [
                    {
                        label: 'Revenus (FC)',
                        data: revenueData.revenues,
                        backgroundColor: 'rgba(94, 114, 228, 0.8)',
                        borderRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Équivalent ($)',
                        data: revenuesInUsd,
                        type: 'line',
                        borderColor: '#f5365c',
                        backgroundColor: 'rgba(245, 54, 92, 0.1)',
                        fill: false,
                        tension: 0.4,
                        yAxisID: 'y1',
                        borderWidth: 2,
                        pointRadius: 3,
                    },
                    {
                        label: 'Transactions',
                        data: revenueData.transactions,
                        type: 'line',
                        borderColor: '#2dce89',
                        backgroundColor: 'rgba(45, 206, 137, 0.1)',
                        fill: true,
                        tension: 0.4,
                        yAxisID: 'y2',
                        borderDash: [5, 5],
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
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0,0,0,0.8)',
                        padding: 12,
                        titleFont: { size: 14 },
                        bodyFont: { size: 13 },
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                if (context.dataset.label === 'Revenus (FC)') {
                                    label += new Intl.NumberFormat('fr-FR').format(context.parsed.y) + ' FC';
                                } else if (context.dataset.label === 'Équivalent ($)') {
                                    label += '$ ' + new Intl.NumberFormat('fr-FR', {minimumFractionDigits: 2}).format(context.parsed.y);
                                } else {
                                    label += context.parsed.y;
                                }
                                return label;
                            }
                        }
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
                        },
                        grid: {
                            drawBorder: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return new Intl.NumberFormat('fr-FR', {notation: 'compact'}).format(value);
                            }
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        title: {
                            display: true,
                            text: 'USD ($)'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                        ticks: {
                            callback: function(value) {
                                return '$ ' + value;
                            }
                        }
                    },
                    y2: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Graphique de fréquentation clients
    const clientCtx = document.getElementById('clientChart');
    if (clientCtx && typeof Chart !== 'undefined') {
        window.clientChartInstance = new Chart(clientCtx, {
            type: 'line',
            data: {
                labels: clientData.labels,
                datasets: [
                    {
                        label: 'Clients uniques',
                        data: clientData.unique_clients,
                        borderColor: '#11cdef',
                        backgroundColor: 'rgba(17, 205, 239, 0.1)',
                        fill: true,
                        tension: 0.4,
                    },
                    {
                        label: 'Visites totales',
                        data: clientData.total_visits,
                        borderColor: '#5e72e4',
                        backgroundColor: 'rgba(94, 114, 228, 0.1)',
                        fill: true,
                        tension: 0.4,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            drawBorder: false,
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }
}

// Réinitialiser les graphiques quand Livewire met à jour le composant
document.addEventListener('livewire:initialized', () => {
    Livewire.hook('morph.updated', () => {
        setTimeout(initCharts, 100);
    });
});
</script>

