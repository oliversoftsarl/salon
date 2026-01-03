<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header pb-0 d-flex justify-content-between align-items-start flex-wrap gap-2">
            <div>
                <h6 class="mb-0">Filtres</h6>
                <p class="text-sm text-secondary mb-0">Affiner par type, période, recherche</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('today')">Aujourd’hui</button>
                <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('week')">Cette semaine</button>
                <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('month')">Ce mois</button>
                <button class="btn btn-dark btn-sm" wire:click="exportCsv">
                    <i class="ni ni-cloud-download-95 me-1"></i> Export CSV
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Type d’article</label>
                    <select class="form-select" wire:model="filter_type" wire:change="$refresh">
                        <option value="all">Tous</option>
                        <option value="products">Produits</option>
                        <option value="services">Services</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Type de transaction</label>
                    <select class="form-select" wire:model="tx_type" wire:change="$refresh">
                        <option value="all">Toutes</option>
                        <option value="sale">Vente</option>
                        <option value="refund">Remboursement</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" class="form-control" wire:model.debounce.300ms="date_from">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" class="form-control" wire:model.debounce.300ms="date_to">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="Référence / Nom article..." wire:model.debounce.300ms="search">
                </div>
            </div>
        </div>
        <div class="card-footer d-flex flex-wrap gap-3 justify-content-between align-items-center">
            <div class="text-sm text-secondary">
                Total (page): <strong>{{ number_format($pageTotal, 2, ',', ' ') }} €</strong>
            </div>
            <div class="text-sm text-secondary">
                Total (filtre global): <strong>{{ number_format($grandTotal, 2, ',', ' ') }} €</strong>
            </div>
            <div>
                <a href="{{ route('pos.checkout') }}" class="btn btn-primary btn-sm">
                    <i class="ni ni-credit-card me-1"></i> Aller à la caisse
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive" style="overflow-x: auto;">
            <table class="table align-items-center mb-0" style="table-layout: fixed; min-width: 900px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">#</th>
                        <th style="width: 130px;">Date</th>
                        <th style="width: 100px;">Type txn</th>
                        <th style="width: 100px;">Référence</th>
                        <th style="width: 160px;">Article</th>
                        <th style="width: 80px;">Type</th>
                        <th style="width: 80px;" class="text-end">PU</th>
                        <th style="width: 50px;" class="text-end">Qté</th>
                        <th style="width: 90px;" class="text-end">Ligne</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $it)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td class="text-nowrap">{{ optional($it->transaction)->created_at?->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge {{ optional($it->transaction)->type === 'refund' ? 'bg-warning' : 'bg-success' }}">
                                {{ optional($it->transaction)->type === 'refund' ? 'Remb.' : 'Vente' }}
                            </span>
                        </td>
                        <td class="text-truncate" style="max-width: 100px;" title="{{ optional($it->transaction)->reference }}">{{ optional($it->transaction)->reference ?? '—' }}</td>
                        <td class="text-truncate" style="max-width: 160px;">
                            @if($it->product_id)
                                {{ $it->product->name ?? 'Produit #'.$it->product_id }}
                            @elseif($it->service_id)
                                {{ $it->service->name ?? 'Service #'.$it->service_id }}
                            @else
                                —
                            @endif
                        </td>
                        <td>
                            <span class="badge {{ $it->product_id ? 'bg-info' : 'bg-dark' }}">
                                {{ $it->product_id ? 'Produit' : 'Service' }}
                            </span>
                        </td>
                        <td class="text-end text-nowrap">{{ number_format($it->unit_price, 2, ',', ' ') }} €</td>
                        <td class="text-end">{{ $it->quantity }}</td>
                        <td class="text-end text-nowrap">{{ number_format($it->line_total, 2, ',', ' ') }} €</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Aucune transaction trouvée pour ces critères</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $items->links() }}
        </div>
    </div>
</div>
