<div class="pos-container">
    {{-- Styles POS Tactile --}}
    <style>
        .pos-container {
            height: calc(100vh - 120px);
            overflow: hidden;
        }
        .pos-btn {
            min-height: 60px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 12px;
            transition: all 0.15s ease;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 8px;
            touch-action: manipulation;
            user-select: none;
        }
        .pos-btn:active {
            transform: scale(0.95);
        }
        .pos-btn-product {
            background: linear-gradient(145deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }
        .pos-btn-product:hover, .pos-btn-product:active {
            background: linear-gradient(145deg, #5a67d8 0%, #6b46c1 100%);
            color: white;
        }
        .pos-btn-service {
            background: linear-gradient(145deg, #11998e 0%, #38ef7d 100%);
            color: white;
            border: none;
        }
        .pos-btn-service:hover, .pos-btn-service:active {
            background: linear-gradient(145deg, #0f8b81 0%, #2ed872 100%);
            color: white;
        }
        .pos-btn-price {
            font-size: 11px;
            opacity: 0.9;
            margin-top: 2px;
        }
        .pos-qty-btn {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            font-size: 20px;
            font-weight: bold;
            display: flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
        }
        .pos-qty-display {
            min-width: 40px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
        }
        .pos-cart-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 12px;
            margin-bottom: 8px;
            transition: all 0.2s ease;
        }
        .pos-cart-item:hover {
            background: #e9ecef;
        }
        .pos-delete-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            touch-action: manipulation;
        }
        .pos-total-display {
            background: linear-gradient(145deg, #1a1a2e 0%, #16213e 100%);
            color: white;
            border-radius: 16px;
            padding: 20px;
        }
        .pos-total-amount {
            font-size: 32px;
            font-weight: 700;
        }
        .pos-checkout-btn {
            min-height: 70px;
            font-size: 20px;
            font-weight: 700;
            border-radius: 16px;
            touch-action: manipulation;
        }
        .pos-payment-btn {
            min-height: 56px;
            border-radius: 12px;
            font-weight: 600;
            touch-action: manipulation;
        }
        .pos-payment-btn.active {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.5);
        }
        .pos-search-input {
            height: 50px;
            font-size: 16px;
            border-radius: 12px;
        }
        .pos-catalog-scroll {
            max-height: calc(100vh - 280px);
            overflow-y: auto;
            padding-right: 5px;
        }
        .pos-cart-scroll {
            max-height: calc(100vh - 450px);
            overflow-y: auto;
        }
        .pos-section-title {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #6c757d;
            font-weight: 700;
            margin-bottom: 12px;
        }
        .pos-client-select {
            height: 50px;
            font-size: 15px;
            border-radius: 12px;
        }
        .pos-tabs .nav-link {
            padding: 12px 20px;
            font-weight: 600;
            border-radius: 12px 12px 0 0;
        }
        @media (max-width: 992px) {
            .pos-container {
                height: auto;
                overflow: visible;
            }
            .pos-catalog-scroll, .pos-cart-scroll {
                max-height: 300px;
            }
        }
    </style>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
            <i class="ni ni-check-bold me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @error('cart') <div class="alert alert-danger py-2">{{ $message }}</div> @enderror

    <div class="row g-3" style="height: 100%;">
        {{-- COLONNE GAUCHE : Catalogue Produits/Services --}}
        <div class="col-lg-5 col-xl-4">
            <div class="card h-100 shadow-sm">
                <div class="card-header p-0 border-0">
                    <ul class="nav nav-tabs pos-tabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active" data-bs-toggle="tab" href="#tab-services" role="tab">
                                <i class="ni ni-scissors me-2"></i>Services
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" data-bs-toggle="tab" href="#tab-products" role="tab">
                                <i class="ni ni-box-2 me-2"></i>Produits
                            </a>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content">
                        {{-- Onglet Services --}}
                        <div class="tab-pane fade show active" id="tab-services" role="tabpanel">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in"></i></span>
                                <input class="form-control pos-search-input border-start-0" type="text"
                                       placeholder="Rechercher un service..."
                                       wire:model.live.debounce.300ms="serviceSearch">
                            </div>
                            <div class="pos-catalog-scroll">
                                <div class="row g-2">
                                    @forelse($services as $s)
                                        <div class="col-6">
                                            <button class="btn pos-btn pos-btn-service w-100" wire:click="addService({{ $s->id }})">
                                                <span class="text-truncate w-100">{{ $s->name }}</span>
                                                <span class="pos-btn-price">{{ number_format($s->price, 0, ',', ' ') }} FC</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-4">
                                            <i class="ni ni-scissors" style="font-size: 32px;"></i>
                                            <p class="mb-0 mt-2">Aucun service trouvé</p>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                        {{-- Onglet Produits --}}
                        <div class="tab-pane fade" id="tab-products" role="tabpanel">
                            <div class="input-group mb-3">
                                <span class="input-group-text bg-white border-end-0"><i class="ni ni-zoom-split-in"></i></span>
                                <input class="form-control pos-search-input border-start-0" type="text"
                                       placeholder="Rechercher un produit..."
                                       wire:model.live.debounce.300ms="productSearch">
                            </div>
                            <div class="pos-catalog-scroll">
                                <div class="row g-2">
                                    @forelse($products as $p)
                                        <div class="col-6">
                                            <button class="btn pos-btn pos-btn-product w-100" wire:click="addProduct({{ $p->id }})">
                                                <span class="text-truncate w-100">{{ $p->name }}</span>
                                                <span class="pos-btn-price">{{ number_format($p->price, 0, ',', ' ') }} FC</span>
                                            </button>
                                        </div>
                                    @empty
                                        <div class="col-12 text-center text-muted py-4">
                                            <i class="ni ni-box-2" style="font-size: 32px;"></i>
                                            <p class="mb-0 mt-2">Aucun produit trouvé</p>
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
        <div class="col-lg-7 col-xl-8">
            <div class="card h-100 shadow-sm">
                <div class="card-header py-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0"><i class="ni ni-cart me-2"></i>Panier</h5>
                    </div>
                    <span class="badge bg-gradient-dark px-3 py-2" style="font-size: 14px;">
                        {{ count($cart) }} article(s)
                    </span>
                </div>

                <div class="card-body p-3">
                    <div class="row g-3">
                        {{-- Liste Panier --}}
                        <div class="col-lg-7">
                            <div class="pos-cart-scroll pe-2">
                                @forelse($cart as $i => $it)
                                    <div class="pos-cart-item">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <div class="flex-grow-1 me-2">
                                                <div class="d-flex align-items-center mb-1">
                                                    @if(($it['type'] ?? '') === 'service')
                                                        <span class="badge bg-success me-2">Service</span>
                                                    @else
                                                        <span class="badge bg-primary me-2">Produit</span>
                                                    @endif
                                                    <strong class="text-truncate" style="max-width: 150px;" title="{{ $it['name'] }}">
                                                        {{ $it['name'] }}
                                                    </strong>
                                                </div>
                                                <div class="text-sm text-muted">
                                                    {{ number_format($it['price'], 0, ',', ' ') }} FC × {{ $it['qty'] }} =
                                                    <strong class="text-dark">{{ number_format($it['price'] * $it['qty'], 0, ',', ' ') }} FC</strong>
                                                </div>
                                                @if(($it['type'] ?? '') === 'service')
                                                    <div class="d-flex flex-wrap gap-2 mt-2">
                                                        <select class="form-select form-select-sm {{ empty($it['stylist_id']) ? 'border-danger' : 'border-success' }}"
                                                                style="max-width: 160px;"
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
                                                                    style="max-width: 160px;"
                                                                    wire:model.live="cart.{{ $i }}.masseur_id">
                                                                <option value="">— Masseur —</option>
                                                                @foreach($masseurs as $m)
                                                                    <option value="{{ $m->id }}">{{ $m->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        @endif
                                                    </div>
                                                    @if(empty($it['stylist_id']))
                                                        <small class="text-danger d-block mt-1">Prestataire requis</small>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="d-flex align-items-center gap-2">
                                                <button class="btn btn-outline-secondary pos-qty-btn" wire:click="decrementItem({{ $i }})">
                                                    <i class="ni ni-fat-delete"></i>
                                                </button>
                                                <span class="pos-qty-display">{{ $it['qty'] }}</span>
                                                <button class="btn btn-outline-secondary pos-qty-btn" wire:click="incrementItem({{ $i }})">
                                                    <i class="ni ni-fat-add"></i>
                                                </button>
                                                <button class="btn btn-danger pos-delete-btn ms-2" wire:click="removeItem({{ $i }})">
                                                    <i class="ni ni-fat-remove"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center py-5">
                                        <i class="ni ni-cart text-muted" style="font-size: 64px;"></i>
                                        <p class="text-muted mt-3 mb-0">Le panier est vide</p>
                                        <p class="text-muted text-sm">Ajoutez des services ou produits</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Résumé + Paiement --}}
                        <div class="col-lg-5">
                            {{-- Client --}}
                            <div class="mb-3">
                                <label class="pos-section-title">Client</label>
                                <div class="d-flex gap-2">
                                    <select class="form-select pos-client-select flex-grow-1" wire:model="client_id">
                                        <option value="">— Client de passage —</option>
                                        @isset($clients)
                                            @foreach($clients as $c)
                                                <option value="{{ $c['id'] }}">{{ $c['label'] }}</option>
                                            @endforeach
                                        @endisset
                                    </select>
                                    <button type="button" class="btn btn-outline-primary px-3"
                                            wire:click="$toggle('showNewClient')"
                                            title="Nouveau client">
                                        <i class="ni ni-single-02"></i>
                                    </button>
                                </div>
                            </div>

                            {{-- Moyen de paiement --}}
                            <div class="mb-3">
                                <label class="pos-section-title">Paiement</label>
                                <div class="row g-2">
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'cash' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'cash')">
                                            <i class="ni ni-money-coins me-1"></i> Espèces
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'card' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'card')">
                                            <i class="ni ni-credit-card me-1"></i> Carte
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'mobile' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'mobile')">
                                            <i class="ni ni-mobile-button me-1"></i> Mobile
                                        </button>
                                    </div>
                                    <div class="col-6">
                                        <button type="button"
                                                class="btn pos-payment-btn w-100 {{ $payment_method === 'other' ? 'btn-dark active' : 'btn-outline-secondary' }}"
                                                wire:click="$set('payment_method', 'other')">
                                            <i class="ni ni-tag me-1"></i> Autre
                                        </button>
                                    </div>
                                </div>
                            </div>

                            {{-- Total --}}
                            <div class="pos-total-display mb-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-uppercase" style="opacity: 0.7;">Total à payer</span>
                                    <div class="text-end">
                                        <span class="pos-total-amount">{{ number_format($this->total, 0, ',', ' ') }} FC</span>
                                        @if($currentExchangeRate)
                                            <div class="text-sm" style="opacity: 0.7;">
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
                                    <i class="ni ni-check-bold me-2"></i> ENCAISSER
                                </span>
                                <span wire:loading wire:target="checkout">
                                    <span class="spinner-border spinner-border-sm me-2"></span> Traitement...
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Nouveau Client (simplifié pour POS) --}}
    @if($showNewClient)
        <div class="modal fade show d-block" tabindex="-1" style="background: rgba(0,0,0,0.5);">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="ni ni-single-02 me-2"></i>Nouveau client</h5>
                        <button type="button" class="btn-close" wire:click="$set('showNewClient', false)"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">Nom complet *</label>
                                <input type="text" class="form-control form-control-lg" wire:model.defer="newClient_name" placeholder="Prénom Nom">
                                @error('newClient_name') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" class="form-control form-control-lg" wire:model.defer="newClient_phone" placeholder="+33 6 ...">
                                @error('newClient_phone') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control form-control-lg" wire:model.defer="newClient_email" placeholder="email@exemple.com">
                                @error('newClient_email') <small class="text-danger">{{ $message }}</small> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Genre</label>
                                <select class="form-select form-select-lg" wire:model.defer="newClient_gender">
                                    <option value="">—</option>
                                    <option value="male">Homme</option>
                                    <option value="female">Femme</option>
                                    <option value="other">Autre</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Date de naissance</label>
                                <input type="date" class="form-control form-control-lg" wire:model.defer="newClient_birthdate">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" rows="2" wire:model.defer="newClient_notes" placeholder="Notes sur le client..."></textarea>
                            </div>
                            <div class="col-12">
                                <div class="form-check form-switch">
                                    <input class="form-check-input" type="checkbox" id="newClient_publish_consent" wire:model.defer="newClient_publish_consent">
                                    <label class="form-check-label" for="newClient_publish_consent">
                                        <i class="ni ni-world me-1 text-primary"></i>
                                        <strong>Consentement publication</strong>
                                        <span class="text-muted d-block text-xs">Le client accepte d'apparaître sur notre site web</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary btn-lg" wire:click="$set('showNewClient', false)">
                            Annuler
                        </button>
                        <button type="button" class="btn btn-primary btn-lg" wire:click="createClient">
                            <i class="ni ni-check-bold me-1"></i> Créer et sélectionner
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Modal Reçu d'impression --}}
    @if($showReceipt && $this->lastTransaction)
        <div class="modal fade show d-block" id="receipt-modal" tabindex="-1" style="background: rgba(0,0,0,0.6);">
            <div class="modal-dialog modal-dialog-centered" style="max-width: 380px;">
                <div class="modal-content" style="border-radius: 16px; overflow: hidden;">
                    <div class="modal-header py-3 bg-gradient-success text-white">
                        <h5 class="modal-title">
                            <i class="ni ni-check-bold me-2"></i>Transaction réussie !
                        </h5>
                        <button type="button" class="btn-close btn-close-white" wire:click="closeReceipt"></button>
                    </div>
                    <div class="modal-body p-0" id="receipt-printable">
                        @include('livewire.pos.receipt', ['transaction' => $this->lastTransaction])
                    </div>
                    <div class="modal-footer no-print py-3 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-lg px-4" wire:click="closeReceipt">
                            <i class="ni ni-fat-remove me-1"></i> Fermer
                        </button>
                        <button type="button" class="btn btn-primary btn-lg px-4" onclick="printReceipt()">
                            <i class="ni ni-single-copy-04 me-1"></i> Imprimer
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    {{-- Iframe caché pour impression thermique GOLDEN GATE --}}
    <iframe id="receipt-print-frame" style="position:fixed;top:-9999px;left:-9999px;width:80mm;height:0;border:none;visibility:hidden;"></iframe>
</div>

@script
<script>
    /**
     * Impression du reçu via iframe caché — Imprimante thermique GOLDEN GATE 80mm
     * Pas de popup bloqué, impression directe.
     */
    function printReceipt() {
        const receiptEl = document.getElementById('receipt-printable');
        if (!receiptEl) {
            console.warn('[POS] Contenu du reçu introuvable');
            return;
        }

        const iframe = document.getElementById('receipt-print-frame');
        if (!iframe) {
            console.warn('[POS] Iframe d\'impression introuvable');
            // Fallback: impression de la page entière
            window.print();
            return;
        }

        const iframeDoc = iframe.contentWindow || iframe.contentDocument;
        const doc = iframeDoc.document || iframeDoc;

        const receiptHTML = `
            <!DOCTYPE html>
            <html lang="fr">
            <head>
                <meta charset="UTF-8">
                <title>Reçu - Salon Gobel</title>
                <style>
                    * { margin: 0; padding: 0; box-sizing: border-box; }
                    html, body {
                        width: 80mm;
                        margin: 0;
                        padding: 0;
                        background: #fff;
                        color: #000;
                        font-family: 'Courier New', Courier, monospace;
                        font-size: 12px;
                    }
                    @page {
                        size: 80mm auto;
                        margin: 0mm;
                    }
                    .receipt-print {
                        width: 72mm;
                        max-width: 72mm;
                        margin: 0 auto;
                        padding: 2mm;
                    }
                    .receipt-header { text-align: center; border-bottom: 2px dashed #000; padding-bottom: 8px; margin-bottom: 8px; }
                    .receipt-logo { font-size: 22px; font-weight: 900; letter-spacing: 1px; margin: 0 0 2px 0; text-transform: uppercase; }
                    .receipt-header p { margin: 1px 0; font-size: 10px; }
                    .receipt-info { border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
                    .receipt-info p { margin: 1px 0; font-size: 11px; }
                    .receipt-items { border-bottom: 1px dashed #000; padding-bottom: 6px; margin-bottom: 6px; }
                    .receipt-item { display: flex; justify-content: space-between; margin: 3px 0; font-size: 11px; }
                    .receipt-item-name { flex: 1; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 130px; }
                    .receipt-item-qty { width: 35px; text-align: center; }
                    .receipt-item-price { width: 75px; text-align: right; white-space: nowrap; }
                    .receipt-totals { margin-bottom: 8px; }
                    .receipt-total-line { display: flex; justify-content: space-between; margin: 2px 0; font-size: 12px; }
                    .receipt-total-line.grand-total { font-weight: bold; font-size: 16px; border-top: 2px solid #000; border-bottom: 2px solid #000; padding: 6px 0; margin-top: 4px; }
                    .receipt-footer { text-align: center; border-top: 1px dashed #000; padding-top: 8px; margin-top: 8px; }
                    .receipt-footer p { margin: 2px 0; font-size: 10px; }
                    .receipt-barcode { text-align: center; margin: 6px 0; font-size: 10px; letter-spacing: 2px; }
                    .receipt-staff-detail { font-size: 9px; color: #333; margin-top: -1px; padding-left: 6px; }
                    .receipt-cut-line { text-align: center; margin: 10px 0 0 0; font-size: 8px; letter-spacing: 3px; color: #999; }
                    .no-print { display: none !important; }
                </style>
            </head>
            <body>${receiptEl.innerHTML}</body>
            </html>
        `;

        doc.open();
        doc.write(receiptHTML);
        doc.close();

        // Laisser l'iframe charger le contenu, puis imprimer
        setTimeout(() => {
            try {
                iframe.contentWindow.focus();
                iframe.contentWindow.print();
                console.log('[POS] ✅ Impression envoyée vers GOLDEN GATE');
            } catch (e) {
                console.error('[POS] Erreur impression iframe, fallback window.print():', e);
                window.print();
            }
        }, 400);
    }

    /**
     * Attendre que le reçu apparaisse dans le DOM, puis imprimer
     */
    function waitForReceiptAndPrint(maxAttempts = 20) {
        let attempts = 0;
        const interval = setInterval(() => {
            attempts++;
            const receiptEl = document.getElementById('receipt-printable');
            if (receiptEl && receiptEl.innerHTML.trim().length > 50) {
                clearInterval(interval);
                console.log('[POS] Reçu détecté, lancement impression GOLDEN GATE...');
                printReceipt();
            } else if (attempts >= maxAttempts) {
                clearInterval(interval);
                console.warn('[POS] Timeout: reçu non trouvé après', maxAttempts * 100, 'ms');
            }
        }, 100);
    }

    // ✅ Impression automatique après chaque vente (imprimante GOLDEN GATE)
    $wire.on('transaction-completed', () => {
        // Son de confirmation (bip caisse)
        try {
            const audio = new Audio('data:audio/wav;base64,UklGRnoGAABXQVZFZm10IBAAAAABAAEAQB8AAEAfAAABAAgAZGF0YQoGAACBhYqFbF1fdJivrJBhNjVgodDbsGckAABnpeXl0YlAAABstPv/9KhKAABvuv//+7tZAABwvP//+8JjAABtvf//9sdrAABpu/r/8MlyAABltfP/6splAABhruz/58xYAABdrOX/4cxNAABZqd7/3MpCAABWpdf/18g4AABSnc//0sYvAABPlsf/zsUoAABMj7//ycQhAABIiLb/xMIaAABFgq3/v8ETAABCVKX/usAPAAA+b5z/tb8MAABaa5P/sb4JAAB2Z4n/rbwGAACSY4D/qbsEAACuX3b/pbkCAADLW2z/obsBAOznVmL/n7kAABB0UTj/nbgAADSPTAD/m7gAAFqrRwD/mbgAAICIQgD/l7gAAKZlPQD/lbgAAMxCOAD/k7gAAO0dNAD/kbgAABAI8P+OuAAA');
            audio.volume = 0.3;
            audio.play().catch(() => {});
        } catch (e) {}

        // Attendre que Livewire ait fini de rendre le reçu, puis imprimer
        waitForReceiptAndPrint();
    });

    // Impression manuelle (bouton "Imprimer")
    $wire.on('print-receipt', () => {
        printReceipt();
    });
</script>
@endscript
