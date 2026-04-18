<div class="pos-container">
    {{-- Styles POS Tactile - Responsive toutes dimensions --}}
    <style>
        .pos-container {
            min-height: calc(100vh - 140px);
            overflow-x: hidden;
        }
        .pos-btn {
            min-height: 54px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            transition: all 0.15s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 6px 4px;
            touch-action: manipulation;
            user-select: none;
            word-break: break-word;
            line-height: 1.2;
        }
        .pos-btn:active { transform: scale(0.95); }
        .pos-btn-product {
            background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
            color: white; border: none;
        }
        .pos-btn-product:hover, .pos-btn-product:active {
            background: linear-gradient(145deg, #5a67d8 0%, #6b46c1 100%); color: white;
        }
        .pos-btn-service {
            background: linear-gradient(145deg, #11998e 0%, #38ef7d 100%);
            color: white; border: none;
        }
        .pos-btn-service:hover, .pos-btn-service:active {
            background: linear-gradient(145deg, #0f8b81 0%, #2ed872 100%); color: white;
        }
        .pos-btn-price { font-size: 10px; opacity: 0.9; margin-top: 2px; }
        .pos-qty-btn {
            width: 36px; height: 36px; border-radius: 50%;
            font-size: 18px; font-weight: bold;
            display: flex; align-items: center; justify-content: center;
            touch-action: manipulation; padding: 0;
        }
        .pos-qty-display {
            min-width: 30px; text-align: center;
            font-size: 16px; font-weight: 600;
        }
        .pos-cart-item {
            background: #f8f9fa; border-radius: 10px;
            padding: 10px; margin-bottom: 6px;
            transition: all 0.2s ease;
        }
        .pos-cart-item:hover { background: #e9ecef; }
        .pos-delete-btn {
            width: 34px; height: 34px; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            touch-action: manipulation; padding: 0;
        }
        .pos-total-display {
            background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
            color: white; border-radius: 12px; padding: 14px;
        }
        .pos-total-amount { font-size: 26px; font-weight: 700; }
        .pos-checkout-btn {
            min-height: 56px; font-size: 18px; font-weight: 700;
            border-radius: 12px; touch-action: manipulation;
        }
        .pos-payment-btn {
            min-height: 44px; border-radius: 10px;
            font-weight: 600; touch-action: manipulation; font-size: 13px;
        }
        .pos-payment-btn.active {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.5);
        }
        .pos-search-input { height: 44px; font-size: 14px; border-radius: 10px; }
        .pos-catalog-scroll {
            max-height: calc(100vh - 300px);
            overflow-y: auto; padding-right: 4px;
            -webkit-overflow-scrolling: touch;
        }
        .pos-cart-scroll {
            max-height: calc(100vh - 420px);
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }
        .pos-section-title {
            font-size: 11px; text-transform: uppercase;
            letter-spacing: 1px; color: #6c757d;
            font-weight: 700; margin-bottom: 8px;
        }
        .pos-client-select { height: 44px; font-size: 14px; border-radius: 10px; }
        .pos-tabs .nav-link {
            padding: 10px 16px; font-weight: 600;
            border-radius: 10px 10px 0 0; font-size: 14px;
        }
        /* === Grands écrans (≥1400px) === */
        @media (min-width: 1400px) {
            .pos-btn { min-height: 60px; font-size: 14px; }
            .pos-total-amount { font-size: 30px; }
            .pos-checkout-btn { min-height: 64px; font-size: 20px; }
            .pos-payment-btn { min-height: 50px; font-size: 14px; }
            .pos-qty-btn { width: 42px; height: 42px; }
        }
        /* === Tablettes (768-991px) === */
        @media (max-width: 991.98px) {
            .pos-container { min-height: auto; }
            .pos-catalog-scroll { max-height: 35vh; }
            .pos-cart-scroll { max-height: 30vh; }
            .pos-total-amount { font-size: 24px; }
            .pos-checkout-btn { min-height: 50px; font-size: 16px; }
        }
        /* === Petits écrans (<768px) === */
        @media (max-width: 767.98px) {
            .pos-catalog-scroll { max-height: 30vh; }
            .pos-cart-scroll { max-height: 25vh; }
            .pos-btn { min-height: 48px; font-size: 12px; padding: 4px; }
            .pos-btn-price { font-size: 9px; }
            .pos-total-display { padding: 10px; }
            .pos-total-amount { font-size: 22px; }
            .pos-checkout-btn { min-height: 48px; font-size: 15px; }
            .pos-payment-btn { min-height: 40px; font-size: 12px; }
            .pos-qty-btn { width: 32px; height: 32px; font-size: 16px; }
            .pos-qty-display { font-size: 14px; min-width: 24px; }
            .pos-delete-btn { width: 30px; height: 30px; }
            .pos-search-input { height: 40px; font-size: 13px; }
            .pos-client-select { height: 40px; font-size: 13px; }
            .pos-tabs .nav-link { padding: 8px 12px; font-size: 13px; }
            .pos-cart-item { padding: 8px; margin-bottom: 5px; }
        }
        /* === Très petits (<576px) === */
        @media (max-width: 575.98px) {
            .pos-catalog-scroll { max-height: 28vh; }
            .pos-cart-scroll { max-height: 22vh; }
            .pos-btn { min-height: 44px; font-size: 11px; border-radius: 8px; }
            .pos-total-amount { font-size: 20px; }
            .pos-checkout-btn { min-height: 44px; font-size: 14px; border-radius: 10px; }
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="ni ni-check-bold me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @error('cart') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    <div class="row g-2 g-lg-3">
        {{-- COLONNE GAUCHE : Catalogue Produits/Services --}}
        <div class="col-12 col-md-5 col-lg-5 col-xl-4">
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-header p-0 border-0">
                    <ul class="nav nav-tabs pos-tabs" role="tablist">
                        <li class="nav-item flex-fill text-center">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-services" role="tab">
                                <i class="ni ni-scissors me-1"></i>Services
                            </a>
                        </li>
                        <li class="nav-item flex-fill text-center">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-products" role="tab">
                                <i class="ni ni-box-2 me-1"></i>Produits
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-2 p-lg-3">
                    <div class="tab-content">
                        {{-- Onglet Services --}}
                        <div class="tab-pane fade show active" id="tab-services" role="tabpanel">
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-white border-end-0 px-2"><i class="ni ni-zoom-split-in"></i></span>
                                <input class="form-control pos-search-input border-start-0" type="text"
                                       placeholder="Rechercher un service..."
                                       wire:model.live.debounce.300ms="serviceSearch">
                            </div>
                            <div class="pos-catalog-scroll">
                                <div class="row g-2">
                                    @forelse($services as $s)
                                        <div class="col-6 col-sm-4 col-md-6">
                                            <button class="btn pos-btn pos-btn-service w-100" wire:click="addService({{ $s->id }})">
                                                <span class="text-truncate w-100">{{ $s->name }}</span>
                                                <span class="pos-btn-price">{{ number_format($s->price, 0, ',', ' ') }} FC</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-3">
                                            <i class="ni ni-scissors" style="font-size: 28px;"></i>
                                            <p class="mb-0 mt-2 text-sm">Aucun service trouvé</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        {{-- Onglet Produits --}}
                        <div class="tab-pane fade" id="tab-products" role="tabpanel">
                            <div class="input-group mb-2">
                                <span class="input-group-text bg-white border-end-0 px-2"><i class="ni ni-zoom-split-in"></i></span>
                                <input class="form-control pos-search-input border-start-0" type="text"
                                       placeholder="Rechercher un produit..."
                                       wire:model.live.debounce.300ms="productSearch">
                            </div>
                            <div class="pos-catalog-scroll">
                                <div class="row g-2">
                                    @forelse($products as $p)
                                        <div class="col-6 col-sm-4 col-md-6">
                                            <button class="btn pos-btn pos-btn-product w-100" wire:click="addProduct({{ $p->id }})">
                                                <span class="text-truncate w-100">{{ $p->name }}</span>
                                                <span class="pos-btn-price">{{ number_format($p->price, 0, ',', ' ') }} FC</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-3">
                                            <i class="ni ni-box-2" style="font-size: 28px;"></i>
                                            <p class="mb-0 mt-2 text-sm">Aucun produit trouvé</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLONNE DROITE : Panier + Paiement --}}
        <div class="col-12 col-md-7 col-lg-7 col-xl-8">
            <div class="card shadow-sm" style="height: 100%;">
                <div class="card-header py-2 py-lg-3 d-flex justify-content-between align-items-center">
                    <h6 class="mb-0"><i class="ni ni-cart me-1"></i>Panier</h6>
                    <span class="badge bg-gradient-dark px-2 py-1" style="font-size: 12px;">
                        {{ count($cart) }} article(s)
                    </span>
                </div>

                <div class="card-body p-2 p-lg-3">
                    <div class="row g-2 g-lg-3">
                        {{-- Liste Panier --}}
                        <div class="col-12 col-lg-7">
                            <div class="pos-cart-scroll pe-1">
                                @forelse($cart as $i => $it)
                                    <div class="pos-cart-item">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="flex-grow-1 me-2" style="min-width: 0;">
                                                <div class="d-flex align-items-center mb-1 flex-wrap">
                                                    @if(($it['type'] ?? '') === 'service')
                                                        <span class="badge bg-success me-1" style="font-size: 10px;">Service</span>
                                                    @else
                                                        <span class="badge bg-primary me-1" style="font-size: 10px;">Produit</span>
                                                    @endif
                                                    <strong class="text-truncate" style="max-width: 120px; font-size: 13px;" title="{{ $it['name'] }}">
                                                        {{ $it['name'] }}
                                                    </strong>
                                                </div>
                                                <div style="font-size: 12px;" class="text-muted">
                                                    {{ number_format($it['price'], 0, ',', ' ') }} FC × {{ $it['qty'] }} =
                                                    <strong class="text-dark">{{ number_format($it['price'] * $it['qty'], 0, ',', ' ') }} FC</strong>
                                                </div>
                                                @if(($it['type'] ?? '') === 'service')
                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                        <select class="form-select form-select-sm {{ empty($it['stylist_id']) ? 'border-danger' : 'border-success' }}"
                                                                style="max-width: 140px; font-size: 11px; height: 28px; padding: 2px 6px;"
                                                                wire:model.live="cart.{{ $i }}.stylist_id"
                                                                required>
                                                            <option value="">— Coiffeur * —</option>
                                                            @isset($staff)
                                                                @foreach($staff as $s)
                                                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                                                @endforeach
                                                            @endisset
                                                        </select>
                                                        @if(isset($masseurs) && $masseurs->count() > 0)
                                                            <select class="form-select form-select-sm border-info"
                                                                    style="max-width: 140px; font-size: 11px; height: 28px; padding: 2px 6px;"
                                                                    wire:model.live="cart.{{ $i }}.masseur_id">
                                                                <option value="">— Masseur —</option>
                                                                @foreach($masseurs as $m)
                                                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        @endif
                                                    </div>
                                                    @if(empty($it['stylist_id']))
                                                        <small class="text-danger d-block mt-1" style="font-size: 10px;">Prestataire requis</small>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-1 flex-shrink-0">
                                                <button class="btn btn-outline-secondary pos-qty-btn" wire:click="decrementItem({{ $i }})">
                                                    <i class="ni ni-fat-delete"></i>
                                                </button>
                                                <span class="pos-qty-display">{{ $it['qty'] }}</span>
                                                <button class="btn btn-outline-secondary pos-qty-btn" wire:click="incrementItem({{ $i }})">
                                                    <i class="ni ni-fat-add"></i>
                                                </button>
                                                <button class="btn btn-danger pos-delete-btn ms-1" wire:click="removeItem({{ $i }})">
                                                    <i class="ni ni-fat-remove"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-4">
                                        <i class="ni ni-cart text-muted" style="font-size: 48px;"></i>
                                        <p class="text-muted mt-2 mb-0 text-sm">Le panier est vide</p>
                                        <p class="text-muted" style="font-size: 11px;">Ajoutez des services ou produits</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Résumé + Paiement --}}
                        <div class="col-12 col-lg-5">
                            {{-- Client --}}
                            <div class="mb-2">
                                <label class="pos-section-title">Client</label>
                                @if($client_id)
                                    {{-- Client sélectionné --}}
                                    <div class="d-flex align-items-center gap-1">
                                        <div class="form-control pos-client-select d-flex align-items-center flex-grow-1 bg-light">
                                            <i class="ni ni-single-02 text-success me-2"></i>
                                            <span class="text-truncate">{{ $selectedClientLabel }}</span>
                                        </div>
                                        <button type="button" class="btn btn-outline-danger px-2" wire:click="clearClient"
                                                title="Retirer le client" style="min-width: 40px; height: 44px;">
                                            <i class="ni ni-fat-remove"></i>
                                        </button>
                                        <button type="button" class="btn btn-outline-primary px-2"
                                                wire:click="$toggle('showNewClient')"
                                                title="Nouveau client" style="min-width: 40px; height: 44px;">
                                            <i class="ni ni-fat-add"></i>
                                        </button>
                                    </div>
                                @else
                                    {{-- Recherche client --}}
                                    <div class="d-flex gap-1">
                                        <div class="position-relative flex-grow-1" x-data="{ open: false }" @click.outside="open = false">
                                            <div class="input-group">
                                                <span class="input-group-text bg-white border-end-0 px-2"><i class="ni ni-zoom-split-in"></i></span>
                                                <input type="text" class="form-control pos-client-select border-start-0"
                                                       placeholder="Rechercher un client..."
                                                       wire:model.live.debounce.300ms="clientSearch"
                                                       @focus="open = true"
                                                       @input="open = true"
                                                       autocomplete="off">
                                            </div>
                                            {{-- Dropdown résultats --}}
                                            @if($clients && count($clients) > 0)
                                                <div x-show="open || $wire.clientSearch.length > 0"
                                                     x-transition
                                                     class="position-absolute w-100 bg-white border rounded-2 shadow-lg mt-1"
                                                     style="z-index: 1050; max-height: 200px; overflow-y: auto;">
                                                    <div class="p-1 border-bottom bg-light" style="font-size: 10px; color: #6c757d;">
                                                        {{ count($clients) }} client(s) trouvé(s)
                                                    </div>
                                                    @foreach($clients as $c)
                                                        <button type="button"
                                                                class="btn btn-sm w-100 text-start px-3 py-2 border-0 rounded-0"
                                                                style="font-size: 13px;"
                                                                wire:click="selectClient({{ $c['id'] }})"
                                                                @click="open = false"
                                                                onmouseover="this.style.background='#f0f4ff'"
                                                                onmouseout="this.style.background='transparent'">
                                                            <i class="ni ni-single-02 text-primary me-1"></i>
                                                            {{ $c['label'] }}
                                                        </button>
                                                    @endforeach
                                                </div>
                                            @elseif($clientSearch)
                                                <div x-show="open"
                                                     class="position-absolute w-100 bg-white border rounded-2 shadow-lg mt-1 p-3 text-center"
                                                     style="z-index: 1050;">
                                                    <p class="text-muted mb-0" style="font-size: 12px;">Aucun client trouvé</p>
                                                </div>
                                            @endif
                                        </div>
                                        <button type="button" class="btn btn-outline-primary px-2"
                                                wire:click="$toggle('showNewClient')"
                                                title="Nouveau client" style="min-width: 40px; height: 44px;">
                                            <i class="ni ni-fat-add"></i>
                                        </button>
                                    </div>
                                    <small class="text-muted" style="font-size: 10px;">Tapez pour rechercher ou laissez vide pour client de passage</small>
                                @endif
                            </div>

                            {{-- Moyen de paiement --}}
                            <div class="mb-2">
                                <label class="pos-section-title">Paiement</label>
                                <div class="row g-1">
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'cash' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'cash')">
                                            <i class="ni ni-money-coins me-1"></i>Espèces
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'card' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'card')">
                                            <i class="ni ni-credit-card me-1"></i>Carte
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'mobile' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'mobile')">
                                            <i class="ni ni-mobile-button me-1"></i>Mobile
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'other' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'other')">
                                            <i class="ni ni-tag me-1"></i>Autre
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Total --}}
                            <div class="pos-total-display mb-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-uppercase" style="opacity: 0.7; font-size: 11px;">Total</span>
                                    <div class="text-end">
                                        <span class="pos-total-amount">{{ number_format($this->total, 0, ',', ' ') }} FC</span>
                                        @if($currentExchangeRate)
                                            <div style="opacity: 0.7; font-size: 11px;">
                                                ≈ $ {{ number_format($this->total / $currentExchangeRate->rate, 2, ',', ' ') }}
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- Bouton Encaisser --}}
                            <button class="btn btn-success pos-checkout-btn w-100"
                                    wire:click="checkout"
                                    @disabled(empty($cart))
                                    wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="checkout">
                                    <i class="ni ni-check-bold me-2"></i>ENCAISSER
                                </span>
                                <span wire:loading wire:target="checkout">
                                    <span class="spinner-border spinner-border-sm me-2"></span>Traitement...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Nouveau Client --}}
    @if($showNewClient)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header py-2">
                        <h6 class="modal-title"><i class="ni ni-single-02 me-2"></i>Nouveau client</h6>
                        <button type="button" class="btn-close" wire:click="$set('showNewClient', false)"></button>
                    </div>
                    <div class="modal-body py-2">
                        <div class="row g-2">
                            <div class="col-12">
                                <label class="form-label mb-1">Nom complet *</label>
                                <input type="text" class="form-control" wire:model.defer="newClient_name" placeholder="Prénom Nom">
                                @error('newClient_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1">Téléphone</label>
                                <input type="text" class="form-control" wire:model.defer="newClient_phone" placeholder="+243...">
                                @error('newClient_phone') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1">Email</label>
                                <input type="email" class="form-control" wire:model.defer="newClient_email" placeholder="email@ex.com">
                                @error('newClient_email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1">Genre</label>
                                <select class="form-select" wire:model.defer="newClient_gender">
                                    <option value="">—</option>
                                    <option value="male">Homme</option>
                                    <option value="female">Femme</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label mb-1">Naissance</label>
                                <input type="date" class="form-control" wire:model.defer="newClient_birthdate">
                            </div>
                            <div class="col-12">
                                <label class="form-label mb-1">Notes</label>
                                <textarea class="form-control" rows="2" wire:model.defer="newClient_notes" placeholder="Notes..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="newClient_publish_consent" wire:model.defer="newClient_publish_consent">
                                    <label class="form-check-label" for="newClient_publish_consent">
                                        <i class="ni ni-world me-1 text-primary"></i>
                                        <strong>Publication</strong>
                                        <span class="text-muted d-block" style="font-size: 10px;">Accepte d'apparaître sur le site</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-outline-secondary" wire:click="$set('showNewClient', false)">Annuler</button>
                        <button type="button" class="btn btn-primary" wire:click="createClient">
                            <i class="ni ni-check-bold me-1"></i>Créer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Reçu d'impression --}}
    @if($showReceipt && $this->lastTransaction)
        <div class="modal fade show d-block" id="receipt-modal" tabindex="-1" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 340px;">
                <div class="modal-content" style="border-radius: 14px; overflow: hidden;">
                    <div class="modal-header py-2 bg-gradient-success text-white">
                        <h6 class="modal-title">
                            <i class="ni ni-check-bold me-2"></i>Transaction réussie !
                        </h6>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeReceipt"></button>
                    </div>
                    <div class="modal-body p-0" id="receipt-printable">
                        @include('livewire.pos.receipt', ['transaction' => $this->lastTransaction])
                    </div>
                    <div class="modal-footer no-print py-2 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary px-3" wire:click="closeReceipt">
                            <i class="ni ni-fat-remove me-1"></i>Fermer
                        </button>
                        <button type="button" class="btn btn-primary px-3" onclick="openPrintDialog()">
                            <i class="ni ni-single-copy-04 me-1"></i>Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

</div>

{{-- Script d'impression global (hors @script Livewire pour être accessible via onclick) --}}
<script>
    window.openPrintDialog = function() {
        var receiptEl = document.getElementById('receipt-printable');
        if (!receiptEl) {
            alert('Reçu introuvable. Veuillez réessayer.');
            return;
        }

        // Cloner le contenu et retirer tous les <style> internes pour éviter les conflits
        var clone = receiptEl.cloneNode(true);
        var inlineStyles = clone.querySelectorAll('style');
        inlineStyles.forEach(function(s) { s.remove(); });
        var content = clone.innerHTML;

        // Styles optimisés pour impression POS 72mm
        // @page size:72mm force le navigateur à créer une page de 72mm (pas A4)
        // Ainsi le contenu est rendu à taille réelle sur l'imprimante POS
        // Sur imprimante 80mm, il y aura juste ~4mm de marge de chaque côté
        var printStyles =
            '*{margin:0;padding:0;box-sizing:border-box}' +
            '@page{size:72mm auto;margin:0}' +
            'html,body{width:72mm;margin:0;padding:0;background:#fff;color:#000;font-family:"Courier New",Courier,monospace;font-size:10pt;line-height:1.4}' +
            '.receipt-print{width:100%;margin:0;padding:1mm 2mm}' +
            '.receipt-header{text-align:center;border-bottom:1px dashed #000;padding-bottom:4pt;margin-bottom:4pt}' +
            '.receipt-logo{font-size:14pt;font-weight:900;margin:0 0 2pt 0;text-transform:uppercase}' +
            '.receipt-header p{margin:1pt 0;font-size:8pt}' +
            '.receipt-info{border-bottom:1px dashed #000;padding-bottom:4pt;margin-bottom:4pt}' +
            '.receipt-info p{margin:1pt 0;font-size:8pt}' +
            '.receipt-items{border-bottom:1px dashed #000;padding-bottom:4pt;margin-bottom:4pt}' +
            '.receipt-item{display:flex;justify-content:space-between;margin:2pt 0;font-size:9pt}' +
            '.receipt-item-name{flex:1;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}' +
            '.receipt-item-qty{width:20pt;text-align:center;flex-shrink:0}' +
            '.receipt-item-price{width:50pt;text-align:right;white-space:nowrap;flex-shrink:0;font-size:8pt}' +
            '.receipt-totals{margin-bottom:4pt}' +
            '.receipt-total-line{display:flex;justify-content:space-between;margin:2pt 0;font-size:9pt}' +
            '.receipt-total-line.grand-total{font-weight:bold;font-size:12pt;border-top:1px solid #000;border-bottom:1px solid #000;padding:3pt 0;margin-top:3pt}' +
            '.receipt-footer{text-align:center;border-top:1px dashed #000;padding-top:4pt;margin-top:4pt}' +
            '.receipt-footer p{margin:1pt 0;font-size:8pt}' +
            '.receipt-barcode{text-align:center;margin:3pt 0;font-size:7pt;letter-spacing:1pt}' +
            '.receipt-staff-detail{font-size:7pt;color:#333;margin-top:0;padding-left:3pt}' +
            '.receipt-cut-line{text-align:center;margin:4pt 0 0;font-size:7pt;letter-spacing:2pt;color:#666}';

        // Créer un iframe caché pour l'impression (plus fiable que window.open)
        var existingFrame = document.getElementById('receipt-print-frame');
        if (existingFrame) existingFrame.remove();

        var iframe = document.createElement('iframe');
        iframe.id = 'receipt-print-frame';
        iframe.style.cssText = 'position:fixed;top:-10000px;left:-10000px;width:1px;height:1px;border:none;';
        document.body.appendChild(iframe);

        var doc = iframe.contentWindow || iframe.contentDocument;
        if (doc.document) doc = doc.document;

        var html = '<!DOCTYPE html>' +
            '<html lang="fr"><head><meta charset="UTF-8">' +
            '<title>Reçu - Salon Gobel</title>' +
            '<style>' + printStyles + '</style>' +
            '</head><body>' + content + '</body></html>';

        doc.open();
        doc.write(html);
        doc.close();

        // Attendre le rendu puis imprimer
        setTimeout(function() {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
            } catch(e) {
                // Fallback: window.open si l'iframe échoue
                var w = window.open('', '_blank', 'width=400,height=650');
                if (w) {
                    w.document.open();
                    w.document.write(html);
                    w.document.close();
                    w.onload = function() { w.focus(); w.print(); };
                }
            }
            // Nettoyer l'iframe après impression
            setTimeout(function() {
                var f = document.getElementById('receipt-print-frame');
                if (f) f.remove();
            }, 3000);
        }, 500);
    };
</script>

@script
<script>
    /**
     * Attendre que le reçu soit rendu dans le DOM puis ouvrir l'impression
     */
    function waitForReceiptAndPrint(maxAttempts = 30) {
        let attempts = 0;
        const interval = setInterval(() => {
            attempts++;
            const el = document.getElementById('receipt-printable');
            if (el && el.innerHTML.trim().length > 100) {
                clearInterval(interval);
                setTimeout(() => {
                    if (typeof window.openPrintDialog === 'function') {
                        window.openPrintDialog();
                    }
                }, 400);
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
            }
        }, 200);
    }

    // ✅ Après chaque vente : bip + ouverture automatique du dialogue d'impression
    $wire.on('transaction-completed', () => {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain); gain.connect(ctx.destination);
            osc.frequency.value = 800; gain.gain.value = 0.1;
            osc.start(); osc.stop(ctx.currentTime + 0.15);
        } catch (e) {}

        waitForReceiptAndPrint();
    });

    // Impression manuelle via événement Livewire
    $wire.on('print-receipt', () => {
        if (typeof window.openPrintDialog === 'function') {
            window.openPrintDialog();
        }
    });
</script>
@endscript
