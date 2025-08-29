<div>
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @error('cart') <div class="alert alert-danger">{{ $message }}</div> @enderror

    <div class="row g-3">
        {{-- Réduit l’espace du catalogue et augmente celui du panier --}}
        <div class="col-lg-4">
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

        {{-- Panier élargi --}}
        <div class="col-lg-8">
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
                                <td>
                                    <div class="d-flex flex-column">
                                        <span>{{ $it['name'] }}</span>
                                        @if(($it['type'] ?? '') === 'service')
                                            <div class="mt-1">
                                                <label class="form-label text-xs mb-1">Coiffeur</label>
                                                <select class="form-select form-select-sm" wire:model="cart.{{ $i }}.stylist_id">
                                                    <option value="">— Non attribué —</option>
                                                    @isset($staff)
                                                        @foreach($staff as $s)
                                                            <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                        @endforeach
                                                    @endisset
                                                </select>
                                            </div>
                                        @endif
                                    </div>
                                </td>
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
                    {{-- Sélection / Création rapide client --}}
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Client (optionnel)</label>
                            <select class="form-select" wire:model="client_id">
                                <option value="">— Aucun —</option>
                                @isset($clients)
                                    @foreach($clients as $c)
                                        <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                                    @endforeach
                                @endisset
                            </select>
                            @error('client_id') <small class="text-danger">{{ $message }}</small> @enderror
                        </div>
                        <div class="col-md-6 d-flex align-items-end">
                            <button type="button" class="btn btn-outline-secondary btn-sm" wire:click="$toggle('showNewClient')">
                                {{ $showNewClient ? 'Fermer' : 'Nouveau client' }}
                            </button>
                        </div>
                    </div>

                    @if($showNewClient)
                        <div class="border rounded p-3 mt-3">
                            <div class="row g-2">
                                <div class="col-md-4">
                                    <label class="form-label">Nom</label>
                                    <input type="text" class="form-control" wire:model.defer="newClient_name" placeholder="Prénom Nom">
                                    @error('newClient_name') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Email</label>
                                    <input type="email" class="form-control" wire:model.defer="newClient_email" placeholder="client@mail.com">
                                    @error('newClient_email') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Téléphone</label>
                                    <input type="text" class="form-control" wire:model.defer="newClient_phone" placeholder="+33 ...">
                                    @error('newClient_phone') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label">Date de naissance</label>
                                    <input type="date" class="form-control" wire:model.defer="newClient_birthdate">
                                    @error('newClient_birthdate') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Genre</label>
                                    <select class="form-select" wire:model.defer="newClient_gender">
                                        <option value="">—</option>
                                        <option value="male">Homme</option>
                                        <option value="female">Femme</option>
                                        <option value="other">Autre</option>
                                    </select>
                                    @error('newClient_gender') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Points fidélité</label>
                                    <input type="number" min="0" class="form-control" wire:model.defer="newClient_loyalty_point">
                                    @error('newClient_loyalty_point') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-12">
                                    <label class="form-label">Notes</label>
                                    <textarea class="form-control" rows="2" wire:model.defer="newClient_notes"></textarea>
                                    @error('newClient_notes') <small class="text-danger">{{ $message }}</small> @enderror
                                </div>

                                <div class="col-12 text-end">
                                    <button class="btn btn-primary btn-sm" wire:click="createClient">
                                        Créer et sélectionner
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mt-3">
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
