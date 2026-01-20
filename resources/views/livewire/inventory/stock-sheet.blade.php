<div>
    {{-- En-tête avec filtres --}}
    <div class="card mb-4">
        <div class="card-header pb-0">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0"><i class="ni ni-single-copy-04 me-2"></i>Fiche de Stock</h5>
                    <p class="text-sm text-secondary mb-0">Suivi des entrées, sorties et solde par produit</p>
                </div>
                @if($product_id && $selectedProduct)
                    <button class="btn btn-dark btn-sm" wire:click="exportPdf">
                        <i class="ni ni-cloud-download-95 me-1"></i> Exporter PDF
                    </button>
                @endif
            </div>
        </div>
        <div class="card-body">
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="row g-3 align-items-end">
                {{-- Sélection du produit --}}
                <div class="col-md-4">
                    <label class="form-label">Produit</label>
                    <select class="form-select" wire:model.live="product_id">
                        <option value="">— Sélectionner un produit —</option>
                        @foreach($products as $p)
                            <option value="{{ $p->id }}">{{ $p->name }} ({{ $p->sku }})</option>
                        @endforeach
                    </select>
                </div>
                {{-- Période rapide --}}
                <div class="col-md-2">
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
                {{-- Stock actuel --}}
                <div class="col-md-2">
                    @if($selectedProduct)
                        <div class="bg-gradient-dark text-white rounded-3 p-3 text-center">
                            <small class="d-block text-uppercase" style="font-size: 10px; opacity: 0.7;">Stock Actuel</small>
                            <span class="h4 mb-0 font-weight-bold">{{ $selectedProduct->stock_quantity }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($product_id && $selectedProduct)
        {{-- Cartes de résumé --}}
        <div class="row mb-4">
            <div class="col-xl-3 col-sm-6 mb-xl-0 mb-4">
                <div class="card">
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-8">
                                <div class="numbers">
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Stock Initial</p>
                                    <h5 class="font-weight-bolder mb-0">
                                        {{ $summary['initial_stock'] ?? 0 }}
                                    </h5>
                                    <small class="text-muted">Au {{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}</small>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-secondary shadow text-center border-radius-md">
                                    <i class="ni ni-box-2 text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold text-success">Total Entrées</p>
                                    <h5 class="font-weight-bolder mb-0 text-success">
                                        +{{ $summary['total_entries'] ?? 0 }}
                                    </h5>
                                    <small class="text-muted">{{ $summary['supplies_count'] ?? 0 }} approvisionnement(s)</small>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md">
                                    <i class="ni ni-bold-up text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold text-danger">Total Sorties</p>
                                    <h5 class="font-weight-bolder mb-0 text-danger">
                                        -{{ $summary['total_exits'] ?? 0 }}
                                    </h5>
                                    <small class="text-muted">
                                        {{ $summary['sales_count'] ?? 0 }} vente(s),
                                        {{ $summary['consumptions_count'] ?? 0 }} conso.
                                    </small>
                                </div>
                            </div>
                            <div class="col-4 text-end">
                                <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md">
                                    <i class="ni ni-bold-down text-lg opacity-10"></i>
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
                                    <p class="text-sm mb-0 text-capitalize font-weight-bold">Stock Final</p>
                                    <h5 class="font-weight-bolder mb-0 {{ ($summary['final_stock'] ?? 0) < 0 ? 'text-danger' : 'text-primary' }}">
                                        {{ $summary['final_stock'] ?? 0 }}
                                    </h5>
                                    <small class="text-muted">Au {{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</small>
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
        </div>

        {{-- Valeurs financières --}}
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card bg-gradient-success text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-sm mb-0 text-uppercase" style="opacity: 0.8;">Valeur des Entrées</p>
                                <h4 class="font-weight-bolder mb-0">
                                    {{ number_format($summary['entry_value'] ?? 0, 0, ',', ' ') }} FC
                                </h4>
                            </div>
                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                <i class="ni ni-money-coins text-success text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card bg-gradient-info text-white">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <p class="text-sm mb-0 text-uppercase" style="opacity: 0.8;">Valeur des Ventes</p>
                                <h4 class="font-weight-bolder mb-0">
                                    {{ number_format($summary['sales_value'] ?? 0, 0, ',', ' ') }} FC
                                </h4>
                            </div>
                            <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                                <i class="ni ni-cart text-info text-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tableau des mouvements --}}
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-0"><i class="ni ni-bullet-list-67 me-2"></i>Détail des Mouvements</h6>
                        <p class="text-sm text-secondary mb-0">{{ $summary['total_movements'] ?? 0 }} mouvement(s) sur la période</p>
                    </div>
                    <div class="d-flex gap-2">
                        <span class="badge bg-success"><i class="ni ni-bold-up me-1"></i>Entrée</span>
                        <span class="badge bg-danger"><i class="ni ni-bold-down me-1"></i>Sortie</span>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder ps-3" style="width: 100px;">Date</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder">Type</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder d-none d-md-table-cell">Description</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center" style="width: 80px;">Entrée</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center" style="width: 80px;">Sortie</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder text-center pe-3" style="width: 80px;">Solde</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Ligne de stock initial --}}
                            <tr class="bg-light">
                                <td class="ps-3">
                                    <span class="text-xs font-weight-bold">{{ \Carbon\Carbon::parse($date_from)->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">Stock Initial</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="text-xs text-muted">Report du stock précédent</span>
                                </td>
                                <td class="text-center">—</td>
                                <td class="text-center">—</td>
                                <td class="text-center pe-3">
                                    <span class="badge bg-dark">{{ $summary['initial_stock'] ?? 0 }}</span>
                                </td>
                            </tr>

                            @php $runningBalance = $summary['initial_stock'] ?? 0; @endphp

                            @forelse($movements as $mvt)
                                @php
                                    $runningBalance += ($mvt['entry'] ?? 0) - ($mvt['exit'] ?? 0);
                                @endphp
                                <tr>
                                    <td class="ps-3">
                                        <span class="text-xs">{{ \Carbon\Carbon::parse($mvt['date'])->format('d/m/Y') }}</span>
                                    </td>
                                    <td>
                                        @if($mvt['label'] === 'Approvisionnement')
                                            <span class="badge bg-success">
                                                <i class="ni ni-delivery-fast me-1"></i>{{ $mvt['label'] }}
                                            </span>
                                        @elseif($mvt['label'] === 'Vente')
                                            <span class="badge bg-info">
                                                <i class="ni ni-cart me-1"></i>{{ $mvt['label'] }}
                                            </span>
                                        @else
                                            <span class="badge bg-warning">
                                                <i class="ni ni-scissors me-1"></i>{{ $mvt['label'] }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="d-none d-md-table-cell">
                                        <div class="d-flex flex-column">
                                            <span class="text-xs text-truncate" style="max-width: 200px;" title="{{ $mvt['description'] }}">
                                                {{ $mvt['description'] }}
                                            </span>
                                            <span class="text-xs text-muted">{{ $mvt['reference'] }}</span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($mvt['entry'] > 0)
                                            <span class="text-success font-weight-bold">+{{ $mvt['entry'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($mvt['exit'] > 0)
                                            <span class="text-danger font-weight-bold">-{{ $mvt['exit'] }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center pe-3">
                                        <span class="badge {{ $runningBalance < 0 ? 'bg-danger' : 'bg-primary' }}">
                                            {{ $runningBalance }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="ni ni-archive-2" style="font-size: 32px;"></i>
                                        <p class="mt-2 mb-0">Aucun mouvement sur cette période</p>
                                    </td>
                                </tr>
                            @endforelse

                            {{-- Ligne de stock final --}}
                            @if($movements->count() > 0)
                            <tr class="bg-light">
                                <td class="ps-3">
                                    <span class="text-xs font-weight-bold">{{ \Carbon\Carbon::parse($date_to)->format('d/m/Y') }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-primary">Stock Final</span>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="text-xs text-muted">Solde à reporter</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-success font-weight-bold">{{ $summary['total_entries'] ?? 0 }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-danger font-weight-bold">{{ $summary['total_exits'] ?? 0 }}</span>
                                </td>
                                <td class="text-center pe-3">
                                    <span class="badge bg-dark fs-6">{{ $summary['final_stock'] ?? 0 }}</span>
                                </td>
                            </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @else
        {{-- Message quand aucun produit n'est sélectionné --}}
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="ni ni-box-2 text-muted" style="font-size: 64px;"></i>
                <h5 class="mt-3">Sélectionnez un produit</h5>
                <p class="text-muted">Choisissez un produit dans la liste ci-dessus pour afficher sa fiche de stock.</p>
            </div>
        </div>
    @endif
</div>

