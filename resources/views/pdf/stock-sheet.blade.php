<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche de Stock - {{ $product->name }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 10px;
            line-height: 1.4;
            color: #333;
        }

        .container {
            padding: 20px;
        }

        /* En-tête */
        .header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #333;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header-left {
            display: table-cell;
            width: 60%;
            vertical-align: top;
        }

        .header-right {
            display: table-cell;
            width: 40%;
            vertical-align: top;
            text-align: right;
        }

        .company-name {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 9px;
            color: #666;
        }

        .document-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .document-date {
            font-size: 10px;
            color: #666;
        }

        /* Informations produit */
        .product-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        .product-info h3 {
            font-size: 14px;
            color: #2c3e50;
            margin-bottom: 10px;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .product-details {
            display: table;
            width: 100%;
        }

        .product-detail-item {
            display: table-cell;
            width: 25%;
            padding: 5px;
        }

        .product-detail-label {
            font-size: 8px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 2px;
        }

        .product-detail-value {
            font-size: 12px;
            font-weight: bold;
            color: #333;
        }

        /* Résumé */
        .summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .summary-box {
            display: table-cell;
            width: 25%;
            padding: 10px;
            text-align: center;
            border: 1px solid #ddd;
        }

        .summary-box.initial {
            background: #e9ecef;
        }

        .summary-box.entries {
            background: #d4edda;
        }

        .summary-box.exits {
            background: #f8d7da;
        }

        .summary-box.final {
            background: #cce5ff;
        }

        .summary-label {
            font-size: 8px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }

        .summary-value {
            font-size: 16px;
            font-weight: bold;
        }

        .summary-box.entries .summary-value {
            color: #155724;
        }

        .summary-box.exits .summary-value {
            color: #721c24;
        }

        .summary-box.final .summary-value {
            color: #004085;
        }

        /* Tableau des mouvements */
        .movements-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .movements-table th {
            background: #2c3e50;
            color: white;
            padding: 8px 5px;
            text-align: left;
            font-size: 9px;
            text-transform: uppercase;
        }

        .movements-table th.center {
            text-align: center;
        }

        .movements-table td {
            padding: 6px 5px;
            border-bottom: 1px solid #ddd;
            font-size: 9px;
        }

        .movements-table td.center {
            text-align: center;
        }

        .movements-table tr:nth-child(even) {
            background: #f8f9fa;
        }

        .movements-table tr.initial-row,
        .movements-table tr.final-row {
            background: #e9ecef;
            font-weight: bold;
        }

        .entry-value {
            color: #155724;
            font-weight: bold;
        }

        .exit-value {
            color: #721c24;
            font-weight: bold;
        }

        .balance-value {
            font-weight: bold;
            background: #2c3e50;
            color: white;
            padding: 2px 6px;
            border-radius: 3px;
        }

        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background: #28a745;
            color: white;
        }

        .badge-info {
            background: #17a2b8;
            color: white;
        }

        .badge-warning {
            background: #ffc107;
            color: #333;
        }

        /* Pied de page */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 20px;
            right: 20px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
            font-size: 8px;
            color: #666;
        }

        .footer-left {
            float: left;
        }

        .footer-right {
            float: right;
        }

        /* Valeurs financières */
        .financial-summary {
            display: table;
            width: 100%;
            margin-bottom: 20px;
        }

        .financial-box {
            display: table-cell;
            width: 50%;
            padding: 10px;
        }

        .financial-box-inner {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            border-left: 4px solid;
        }

        .financial-box-inner.entries {
            border-color: #28a745;
        }

        .financial-box-inner.sales {
            border-color: #17a2b8;
        }

        .financial-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
        }

        .financial-value {
            font-size: 14px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- En-tête -->
        <div class="header">
            <div class="header-left">
                <div class="company-name">{{ $company['name'] }}</div>
                <div class="company-info">
                    {{ $company['address'] }}<br>
                    {{ $company['city'] }}<br>
                    Tél: {{ $company['phone'] }}
                </div>
            </div>
            <div class="header-right">
                <div class="document-title">FICHE DE STOCK</div>
                <div class="document-date">
                    Période: {{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}
                    au {{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}
                </div>
                <div class="document-date" style="margin-top: 5px;">
                    Édité le: {{ now()->format('d/m/Y à H:i') }}
                </div>
            </div>
        </div>

        <!-- Informations produit -->
        <div class="product-info">
            <h3>Produit: {{ $product->name }}</h3>
            <div class="product-details">
                <div class="product-detail-item">
                    <div class="product-detail-label">SKU / Référence</div>
                    <div class="product-detail-value">{{ $product->sku }}</div>
                </div>
                <div class="product-detail-item">
                    <div class="product-detail-label">Prix unitaire</div>
                    <div class="product-detail-value">{{ number_format($product->price, 0, ',', ' ') }} FC</div>
                </div>
                <div class="product-detail-item">
                    <div class="product-detail-label">Stock actuel</div>
                    <div class="product-detail-value">{{ $product->stock_quantity }} unités</div>
                </div>
                <div class="product-detail-item">
                    <div class="product-detail-label">Type</div>
                    <div class="product-detail-value">
                        {{ $product->is_consumable ? 'Consommable' : ($product->is_snack ? 'Snack' : 'Standard') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Résumé des stocks -->
        <div class="summary">
            <div class="summary-box initial">
                <div class="summary-label">Stock Initial</div>
                <div class="summary-value">{{ $summary['initial_stock'] ?? 0 }}</div>
            </div>
            <div class="summary-box entries">
                <div class="summary-label">Entrées</div>
                <div class="summary-value">+{{ $summary['total_entries'] ?? 0 }}</div>
            </div>
            <div class="summary-box exits">
                <div class="summary-label">Sorties</div>
                <div class="summary-value">-{{ $summary['total_exits'] ?? 0 }}</div>
            </div>
            <div class="summary-box final">
                <div class="summary-label">Stock Final</div>
                <div class="summary-value">{{ $summary['final_stock'] ?? 0 }}</div>
            </div>
        </div>

        <!-- Valeurs financières -->
        <div class="financial-summary">
            <div class="financial-box">
                <div class="financial-box-inner entries">
                    <div class="financial-label">Valeur des entrées (coût)</div>
                    <div class="financial-value">{{ number_format($summary['entry_value'] ?? 0, 0, ',', ' ') }} FC</div>
                </div>
            </div>
            <div class="financial-box">
                <div class="financial-box-inner sales">
                    <div class="financial-label">Valeur des ventes (prix)</div>
                    <div class="financial-value">{{ number_format($summary['sales_value'] ?? 0, 0, ',', ' ') }} FC</div>
                </div>
            </div>
        </div>

        <!-- Tableau des mouvements -->
        <table class="movements-table">
            <thead>
                <tr>
                    <th style="width: 70px;">Date</th>
                    <th style="width: 100px;">Type</th>
                    <th>Description</th>
                    <th class="center" style="width: 60px;">Entrée</th>
                    <th class="center" style="width: 60px;">Sortie</th>
                    <th class="center" style="width: 60px;">Solde</th>
                </tr>
            </thead>
            <tbody>
                <!-- Stock initial -->
                <tr class="initial-row">
                    <td>{{ \Carbon\Carbon::parse($dateFrom)->format('d/m/Y') }}</td>
                    <td><span class="badge" style="background: #6c757d; color: white;">Stock Initial</span></td>
                    <td>Report du stock précédent</td>
                    <td class="center">—</td>
                    <td class="center">—</td>
                    <td class="center"><span class="balance-value">{{ $summary['initial_stock'] ?? 0 }}</span></td>
                </tr>

                @php $runningBalance = $summary['initial_stock'] ?? 0; @endphp

                @foreach($movements as $mvt)
                    @php
                        $runningBalance += ($mvt['entry'] ?? 0) - ($mvt['exit'] ?? 0);
                    @endphp
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($mvt['date'])->format('d/m/Y') }}</td>
                        <td>
                            @if($mvt['label'] === 'Approvisionnement')
                                <span class="badge badge-success">{{ $mvt['label'] }}</span>
                            @elseif($mvt['label'] === 'Vente')
                                <span class="badge badge-info">{{ $mvt['label'] }}</span>
                            @else
                                <span class="badge badge-warning">{{ $mvt['label'] }}</span>
                            @endif
                        </td>
                        <td>
                            {{ $mvt['description'] }}
                            <br><small style="color: #666;">{{ $mvt['reference'] }}</small>
                        </td>
                        <td class="center">
                            @if($mvt['entry'] > 0)
                                <span class="entry-value">+{{ $mvt['entry'] }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="center">
                            @if($mvt['exit'] > 0)
                                <span class="exit-value">-{{ $mvt['exit'] }}</span>
                            @else
                                —
                            @endif
                        </td>
                        <td class="center"><span class="balance-value">{{ $runningBalance }}</span></td>
                    </tr>
                @endforeach

                <!-- Stock final -->
                @if(count($movements) > 0)
                <tr class="final-row">
                    <td>{{ \Carbon\Carbon::parse($dateTo)->format('d/m/Y') }}</td>
                    <td><span class="badge" style="background: #007bff; color: white;">Stock Final</span></td>
                    <td>Solde à reporter</td>
                    <td class="center"><span class="entry-value">{{ $summary['total_entries'] ?? 0 }}</span></td>
                    <td class="center"><span class="exit-value">{{ $summary['total_exits'] ?? 0 }}</span></td>
                    <td class="center"><span class="balance-value" style="background: #007bff;">{{ $summary['final_stock'] ?? 0 }}</span></td>
                </tr>
                @endif
            </tbody>
        </table>

        <!-- Statistiques -->
        <div class="product-info" style="margin-top: 20px;">
            <h3>Récapitulatif des mouvements</h3>
            <div class="product-details">
                <div class="product-detail-item">
                    <div class="product-detail-label">Approvisionnements</div>
                    <div class="product-detail-value">{{ $summary['supplies_count'] ?? 0 }}</div>
                </div>
                <div class="product-detail-item">
                    <div class="product-detail-label">Ventes</div>
                    <div class="product-detail-value">{{ $summary['sales_count'] ?? 0 }}</div>
                </div>
                <div class="product-detail-item">
                    <div class="product-detail-label">Consommations</div>
                    <div class="product-detail-value">{{ $summary['consumptions_count'] ?? 0 }}</div>
                </div>
                <div class="product-detail-item">
                    <div class="product-detail-label">Total mouvements</div>
                    <div class="product-detail-value">{{ $summary['total_movements'] ?? 0 }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pied de page -->
    <div class="footer">
        <div class="footer-left">
            {{ $company['name'] }} - Fiche de Stock
        </div>
        <div class="footer-right">
            Document généré le {{ now()->format('d/m/Y à H:i') }}
        </div>
    </div>
</body>
</html>

