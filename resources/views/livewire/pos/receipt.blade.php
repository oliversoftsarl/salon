{{-- Reçu d'impression - Style optimisé pour imprimante thermique 80mm --}}
<div id="receipt-content" class="receipt-print">
    <style>
        @media print {
            body * {
                visibility: hidden;
            }
            #receipt-modal, #receipt-modal * {
                visibility: visible;
            }
            #receipt-modal {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }

        .receipt-print {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 280px;
            margin: 0 auto;
            padding: 10px;
            background: #fff;
            color: #000;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 10px;
            margin-bottom: 10px;
        }

        .receipt-header h2 {
            margin: 0;
            font-size: 18px;
            font-weight: bold;
        }

        .receipt-header p {
            margin: 2px 0;
            font-size: 11px;
        }

        .receipt-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .receipt-info p {
            margin: 2px 0;
            font-size: 11px;
        }

        .receipt-items {
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }

        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin: 4px 0;
        }

        .receipt-item-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            max-width: 160px;
        }

        .receipt-item-qty {
            width: 40px;
            text-align: center;
        }

        .receipt-item-price {
            width: 70px;
            text-align: right;
        }

        .receipt-totals {
            margin-bottom: 10px;
        }

        .receipt-total-line {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
        }

        .receipt-total-line.grand-total {
            font-weight: bold;
            font-size: 14px;
            border-top: 1px solid #000;
            padding-top: 5px;
            margin-top: 5px;
        }

        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 10px;
            margin-top: 10px;
        }

        .receipt-footer p {
            margin: 2px 0;
            font-size: 11px;
        }

        .receipt-barcode {
            text-align: center;
            margin: 10px 0;
            font-size: 10px;
            letter-spacing: 2px;
        }
    </style>

    <div class="receipt-header">
        <h2>{{ config('app.name', 'Salon de Coiffure') }}</h2>
        <p>Goma, c. Karisimbi</p>
        <p>Tél: + 243 970 407 747</p>
    </div>

    <div class="receipt-info">
        <p><strong>Reçu N°:</strong> {{ $transaction->reference ?? 'TX-'.$transaction->id }}</p>
        <p><strong>Date:</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
        @if($transaction->client)
            <p><strong>Client:</strong> {{ $transaction->client->name ?? $transaction->client->first_name ?? 'Client' }}</p>
        @endif
        <p><strong>Paiement:</strong> {{ ucfirst($transaction->payment_method) }}</p>
    </div>

    <div class="receipt-items">
        <div class="receipt-item" style="font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 3px; margin-bottom: 5px;">
            <span class="receipt-item-name">Article</span>
            <span class="receipt-item-qty">Qté</span>
            <span class="receipt-item-price">Prix</span>
        </div>

        @foreach($transaction->items as $item)
            <div class="receipt-item">
                <span class="receipt-item-name" title="{{ $item->product->name ?? $item->service->name ?? 'Article' }}">
                    {{ Str::limit($item->product->name ?? $item->service->name ?? 'Article', 20) }}
                </span>
                <span class="receipt-item-qty">x{{ $item->quantity }}</span>
                <span class="receipt-item-price">{{ number_format($item->line_total, 0, ',', ' ') }} FC</span>
            </div>
            @if($item->service && $item->stylist)
                <div class="receipt-item" style="font-size: 10px; color: #666; margin-top: -2px; padding-left: 8px;">
                    <span class="receipt-item-name">↳ Coiffeur: {{ $item->stylist->name }}</span>
                    <span class="receipt-item-qty"></span>
                    <span class="receipt-item-price"></span>
                </div>
            @endif
        @endforeach
    </div>

    <div class="receipt-totals">
        <div class="receipt-total-line grand-total">
            <span>TOTAL:</span>
            <span>{{ number_format($transaction->total, 0, ',', ' ') }} FC</span>
        </div>
    </div>

    <div class="receipt-barcode">
        *{{ str_pad($transaction->id, 8, '0', STR_PAD_LEFT) }}*
    </div>

    <div class="receipt-footer">
        <p>Merci de votre visite !</p>
        <p>À bientôt</p>
        <p style="margin-top: 8px; font-size: 10px;">{{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</div>

