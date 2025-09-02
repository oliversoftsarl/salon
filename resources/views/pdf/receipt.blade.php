<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reçu #{{ $transaction->id }}</title>
    <style>
        @page { 
            margin: 0; 
            padding: 0;
            size: 70mm auto; /* Largeur ticket sans hauteur fixe */
        }
        body { 
            font-family: 'DejaVu Sans Mono', monospace, sans-serif; 
            font-size: 9px; /* Réduit légèrement */
            width: 68mm; /* Légèrement plus petit que la page */
            margin: 0;
            padding: 1mm; /* Réduit au minimum */
            line-height: 1.1;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .bold { font-weight: bold; }
        .underline { text-decoration: underline; }
        .divider { 
            border-top: 1px dashed #000; 
            margin: 2px 0; 
        }
        .double-divider { 
            border-top: 2px solid #000; 
            margin: 3px 0; 
        }
        table { 
            width: 100%; 
            border-collapse: collapse;
            margin: 0;
            padding: 0;
        }
        table.items td { 
            padding: 0;
            margin: 0;
        }
        table.items .qty { 
            width: 10%; /* Réduit */
            text-align: center; 
        }
        table.items .desc { 
            width: 60%; /* Ajusté */
            word-wrap: break-word;
        }
        table.items .price { 
            width: 30%; 
            text-align: right; 
        }
        .footer { 
            font-size: 7px; /* Réduit */
            margin-top: 5px;
        }
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
    </style>
</head>
<body>

<div class="text-center">
    <div class="bold" style="font-size: 10px;">{{ strtoupper($company['name']) }}</div>
    <div style="font-size: 8px;">{{ $company['address'] }}</div>
    <div style="font-size: 8px;">{{ $company['city'] }}</div>
    <div style="font-size: 8px;">Tél: {{ $company['phone'] }}</div>
</div>

<div class="divider"></div>

<div class="text-center bold underline" style="font-size: 10px;">
    REÇU DE VENTE
</div>

<div style="margin: 2px 0;">
    <table>
        <tr>
            <td class="text-left" style="font-size: 8px;">
                <strong>Réf:</strong> {{ $transaction->reference ?? 'TRX-'.$transaction->id }}
            </td>
            <td class="text-right" style="font-size: 8px;">
                <strong>Date:</strong> {{ $transaction->created_at->format('d/m/y H:i') }}
            </td>
        </tr>
        <tr>
            <td class="text-left" style="font-size: 8px;">
                <strong>Paiement:</strong> {{ substr(ucfirst($transaction->payment_method), 0, 8) }}
            </td>
            <td class="text-right" style="font-size: 8px;">
                <strong>Client:</strong> {{ $transaction->client ? substr($transaction->client->first_name
                ." ".$transaction->client->last_name, 0, 50) : 'Non' }}
            </td>
        </tr>
    </table>
</div>

<div class="divider"></div>

<table class="items">
    <thead>
        <tr>
            <th class="qty" style="font-size: 8px;">Qty</th>
            <th class="desc" style="font-size: 8px;">Description</th>
            <th class="price" style="font-size: 8px;">Total</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transaction->items as $item)
        <tr>
            <td class="qty" style="font-size: 8px;">{{ $item->quantity }}x</td>
            <td class="desc" style="font-size: 8px;">
                {{ substr($item->product->name ?? $item->service->name ?? 'Article', 0, 50) }}
                @if($item->stylist && $item->service)
                <br><small style="font-size: 7px;">Coiffeur: {{ substr($item->stylist->name, 0, 50) }}</small>
                @endif
            </td>
            <td class="price" style="font-size: 8px;">{{ number_format($item->line_total, 2, ',', ' ') }}$</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="double-divider"></div>

<div class="text-right bold" style="font-size: 10px;">
    TOTAL: {{ number_format($transaction->total, 2, ',', ' ') }} $
</div>

<div class="divider"></div>

<div class="text-center footer">
    <div>Merci pour votre visite !</div>
    <div>Reçu émis le {{ date('d/m/Y à H:i') }}</div>
    <div style="margin-top: 3px;">
        {{ config('app.name') }}
    </div>
</div>

</body>
</html>