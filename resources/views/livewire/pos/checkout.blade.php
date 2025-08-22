<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @error('cart') <div class="alert alert-danger">{{ $message }}</div> @enderror
    <div class="row g-3">
        <div class="col-lg-7">
            <div class="card mb-3">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Produits</h6>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                        <input class="form-control" type="text" placeholder="Nom ou SKU..." wire:model.live.debounce.300ms="productSearch">
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($products as $p)
                            <button class="btn btn-outline-secondary btn-sm" wire:click="addProduct({{ $p->id }})">
                                {{ $p->name }} ({{ number_format($p->price, 2, ',', ' ') }} €)
                            </button>
                        @endforeach
                        @if($products->isEmpty())
                            <div class="text-muted">Aucun produit</div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card mb-3">
                <div class="card-header pb-0">
                    <h6 class="mb-0">Services</h6>
                </div>
                <div class="card-body">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="ni ni-zoom-split-in"></i></span>
                        <input class="form-control" type="text" placeholder="Nom du service..." wire:model.live.debounce.300ms="serviceSearch">
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($services as $s)
                            <button class="btn btn-outline-secondary btn-sm" wire:click="addService({{ $s->id }})">
                                {{ $s->name }} ({{ number_format($s->price, 2, ',', ' ') }} €)
                            </button>
                        @endforeach
                        @if($services->isEmpty())
                            <div class="text-muted">Aucun service</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Panier</h6>
                    <span class="badge bg-dark">{{ count($cart) }} article(s)</span>
                </div>
                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                        <tr>
                            <th>Article</th>
                            <th>PU</th>
                            <th>Qté</th>
                            <th>Sous-total</th>
                            <th></th>
                        </tr>
                        </thead>
                        <tbody>
                        @forelse($cart as $i => $it)
                            <tr>
                                <td>{{ $it['name'] }}</td>
                                <td>{{ number_format($it['price'], 2, ',', ' ') }} €</td>
                                <td>
                                    <div class="btn-group btn-group-sm">
                                        <button class="btn btn-outline-secondary" wire:click="decrementItem({{ $i }})">-</button>
                                        <button class="btn btn-light" disabled>{{ $it['qty'] }}</button>
                                        <button class="btn btn-outline-secondary" wire:click="incrementItem({{ $i }})">+</button>
                                    </div>
                                </td>
                                <td>{{ number_format($it['price'] * $it['qty'], 2, ',', ' ') }} €</td>
                                <td class="text-end">
                                    <button class="btn btn-sm btn-outline-danger" wire:click="removeItem({{ $i }})">X</button>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted">Panier vide</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <strong>Total</strong>
                        <strong>{{ number_format($this->total, 2, ',', ' ') }} €</strong>
                    </div>
                    <div class="mt-3">
                        <label class="form-label">Moyen de paiement</label>
                        <select class="form-select" wire:model="payment_method">
                            <option value="cash">Espèces</option>
                            <option value="card">Carte</option>
                            <option value="mobile">Mobile</option>
                            <option value="other">Autre</option>
                        </select>
                    </div>
                </div>
                <div class="card-footer text-end">
                    <button class="btn btn-success" wire:click="checkout" @disabled(empty($cart))>
                        <i class="ni ni-check-bold me-1"></i> Encaisser
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
