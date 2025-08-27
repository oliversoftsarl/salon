<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header pb-0">
            <h6 class="mb-0">Filtres</h6>
            <p class="text-sm text-secondary mb-0">Affiner la liste par type et par période</p>
        </div>
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-3">
                    <label class="form-label">Type d’article</label>
                    <select class="form-select" wire:model="filter_type">
                        <option value="all">Tous</option>
                        <option value="products">Produits</option>
                        <option value="services">Services</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Du</label>
                    <input type="date" class="form-control" wire:model="date_from">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Au</label>
                    <input type="date" class="form-control" wire:model="date_to">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Recherche</label>
                    <input type="text" class="form-control" placeholder="Réf/Article..." wire:model.debounce.300ms="search">
                </div>
                <div class="col-12 d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('today')">Aujourd’hui</button>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('week')">Cette semaine</button>
                    <button class="btn btn-outline-secondary btn-sm" wire:click="setPreset('month')">Ce mois</button>
                </div>
            </div>
        </div>
        <div class="card-footer d-flex justify-content-between align-items-center">
            <div class="text-sm text-secondary">
                Total (page courante): <strong>{{ number_format($pageTotal, 2, ',', ' ') }} €</strong>
            </div>
            <div>
                <a href="{{ route('pos.checkout') }}" class="btn btn-primary btn-sm">
                    <i class="ni ni-credit-card me-1"></i> Aller à la caisse
                </a>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table align-items-center mb-0">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Référence</th>
                        <th>Article</th>
                        <th>Type</th>
                        <th class="text-end">PU</th>
                        <th class="text-end">Qté</th>
                        <th class="text-end">Ligne</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $it)
                    <tr>
                        <td>{{ optional($it->transaction)->created_at?->format('d/m/Y H:i') }}</td>
                        <td>{{ optional($it->transaction)->reference ?? '—' }}</td>
                        <td>
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
                        <td class="text-end">{{ number_format($it->unit_price, 2, ',', ' ') }} €</td>
                        <td class="text-end">{{ $it->quantity }}</td>
                        <td class="text-end">{{ number_format($it->line_total, 2, ',', ' ') }} €</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">Aucune transaction trouvée pour ces critères</td>
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
