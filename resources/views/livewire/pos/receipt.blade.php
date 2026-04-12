{{-- Reçu d'impression - Optimisé pour POS 72mm --}}
<div id="receipt-content" class="receipt-print">
    <style>
        .receipt-print {
            font-family: 'Courier New', Courier, monospace;
            font-size: 12px;
            width: 100%;
            max-width: 300px;
            margin: 0 auto;
            padding: 10px 8px;
            background: #fff;
            color: #000;
            line-height: 1.4;
        }

        .receipt-header {
            text-align: center;
            border-bottom: 1px dashed #000;
            padding-bottom: 8px;
            margin-bottom: 8px;
        }
        .receipt-logo {
            font-size: 18px;
            font-weight: 900;
            margin: 0 0 3px 0;
            text-transform: uppercase;
        }
        .receipt-header p { margin: 2px 0; font-size: 11px; }

        .receipt-info {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .receipt-info p { margin: 2px 0; font-size: 11px; }

        .receipt-items {
            border-bottom: 1px dashed #000;
            padding-bottom: 6px;
            margin-bottom: 6px;
        }
        .receipt-item {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }
        .receipt-item-name {
            flex: 1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .receipt-item-qty { width: 30px; text-align: center; flex-shrink: 0; }
        .receipt-item-price {
            width: 70px;
            text-align: right;
            white-space: nowrap;
            flex-shrink: 0;
            font-size: 11px;
        }

        .receipt-totals { margin-bottom: 6px; }
        .receipt-total-line {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 12px;
        }
        .receipt-total-line.grand-total {
            font-weight: bold;
            font-size: 15px;
            border-top: 1px solid #000;
            border-bottom: 1px solid #000;
            padding: 5px 0;
            margin-top: 4px;
        }

        .receipt-footer {
            text-align: center;
            border-top: 1px dashed #000;
            padding-top: 8px;
            margin-top: 8px;
        }
        .receipt-footer p { margin: 2px 0; font-size: 11px; }

        .receipt-barcode {
            text-align: center;
            margin: 6px 0;
            font-size: 10px;
            letter-spacing: 1px;
        }
        .receipt-staff-detail {
            font-size: 10px;
            color: #333;
            margin-top: 0;
            padding-left: 6px;
        }
        .receipt-cut-line {
            text-align: center;
            margin: 8px 0 0 0;
            font-size: 9px;
            letter-spacing: 2px;
            color: #999;
        }
    </style>

    <div class="receipt-header">
        <div class="receipt-logo">SALON GoBeL</div>
        <p><strong>Ets Gobel</strong></p>
        <p>Goma, c. Karisimbi</p>
        <p>Tél: +243 990 378 202</p>
    </div>

    <div class="receipt-info">
        <p><strong>N°:</strong> {{ $transaction->reference ?? 'TX-'.$transaction->id }}</p>
        <p><strong>Date:</strong> {{ $transaction->created_at->format('d/m/Y H:i') }}</p>
        <p><strong>Caissier:</strong> {{ auth()->user()->name ?? '-' }}</p>
        @if($transaction->client)
            <p><strong>Client:</strong> {{ $transaction->client->name ?? $transaction->client->first_name ?? 'Client' }}</p>
        @endif
        <p><strong>Paie:</strong>
            @switch($transaction->payment_method)
                @case('cash') Espèces @break
                @case('card') Carte @break
                @case('mobile') Mobile @break
                @default {{ ucfirst($transaction->payment_method) }}
            @endswitch
        </p>
    </div>

    <div class="receipt-items">
        <div class="receipt-item" style="font-weight: bold; border-bottom: 1px solid #000; padding-bottom: 2px; margin-bottom: 3px;">
            <span class="receipt-item-name">Article</span>
            <span class="receipt-item-qty">Qt</span>
            <span class="receipt-item-price">Montant</span>
        </div>

        @foreach($transaction->items as $item)
            <div class="receipt-item">
                <span class="receipt-item-name" title="{{ $item->product->name ?? $item->service->name ?? 'Article' }}">
                    {{ Str::limit($item->product->name ?? $item->service->name ?? 'Article', 14) }}
                </span>
                <span class="receipt-item-qty">x{{ $item->quantity }}</span>
                <span class="receipt-item-price">{{ number_format($item->line_total, 0, ',', '.') }}FC</span>
            </div>
            @if($item->quantity > 1)
                <div class="receipt-staff-detail">
                    ({{ number_format($item->unit_price, 0, ',', '.') }}FC x{{ $item->quantity }})
                </div>
            @endif
            @if($item->service && $item->stylist)
                <div class="receipt-staff-detail">
                    > {{ $item->stylist->name }}
                </div>
            @endif
            @if($item->service && $item->masseur)
                <div class="receipt-staff-detail">
                    > {{ $item->masseur->name }}
                </div>
            @endif
        @endforeach
    </div>

    <div class="receipt-totals">
        <div class="receipt-total-line">
            <span>Articles:</span>
            <span>{{ $transaction->items->sum('quantity') }}</span>
        </div>
        <div class="receipt-total-line grand-total">
            <span>TOTAL:</span>
            <span>{{ number_format($transaction->total, 0, ',', '.') }} FC</span>
        </div>
        @php
            $rate = \App\Models\ExchangeRate::getCurrentRate();
        @endphp
        @if($rate)
            <div class="receipt-total-line" style="font-size: 11px; color: #555;">
                <span>USD:</span>
                <span>$ {{ number_format($transaction->total / $rate->rate, 2, ',', '.') }}</span>
            </div>
        @endif
    </div>

    <div class="receipt-barcode">
        *{{ str_pad($transaction->id, 8, '0', STR_PAD_LEFT) }}*
    </div>

    <div class="receipt-footer">
        <p style="font-size: 12px; font-weight: bold;">Merci de votre visite !</p>
        <p>A bientôt</p>
        <p style="margin-top: 4px; font-size: 10px;">{{ $transaction->created_at->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="receipt-cut-line">
        - - - - - - - - - - - - -
    </div>
</div>

