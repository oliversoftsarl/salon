<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Services</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
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
            border-bottom: 3px solid #4a5568;
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
            font-size: 22px;
            font-weight: bold;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .company-info {
            font-size: 10px;
            color: #666;
            line-height: 1.6;
        }

        .document-title {
            font-size: 24px;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 5px;
        }

        .document-date {
            font-size: 11px;
            color: #666;
        }

        /* Section titre de catégorie */
        .category-title {
            background: #4a5568;
            color: white;
            padding: 10px 15px;
            margin-top: 20px;
            margin-bottom: 10px;
            font-size: 14px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .category-title:first-of-type {
            margin-top: 0;
        }

        /* Tableau des services */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background: #e2e8f0;
            padding: 10px 12px;
            text-align: left;
            font-weight: bold;
            font-size: 10px;
            text-transform: uppercase;
            color: #4a5568;
            border-bottom: 2px solid #cbd5e0;
        }

        th.price {
            text-align: right;
            width: 140px;
        }

        th.duration {
            text-align: center;
            width: 80px;
        }

        td {
            padding: 10px 12px;
            border-bottom: 1px solid #e2e8f0;
        }

        td.service-name {
            font-weight: 600;
            color: #2d3748;
        }

        td.service-description {
            color: #718096;
            font-size: 10px;
            font-style: italic;
        }

        td.duration {
            text-align: center;
            color: #718096;
        }

        td.price {
            text-align: right;
            font-weight: bold;
            color: #2d3748;
        }

        td.price .usd {
            display: block;
            font-size: 9px;
            color: #718096;
            font-weight: normal;
        }

        tr:nth-child(even) {
            background: #f7fafc;
        }

        tr:hover {
            background: #edf2f7;
        }

        /* Résumé */
        .summary {
            background: #f7fafc;
            padding: 15px;
            margin-top: 25px;
            border: 1px solid #e2e8f0;
            border-radius: 5px;
        }

        .summary-title {
            font-size: 12px;
            font-weight: bold;
            color: #4a5568;
            margin-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 5px;
        }

        .summary-item {
            display: inline-block;
            margin-right: 30px;
            font-size: 10px;
        }

        .summary-item strong {
            color: #2d3748;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #a0aec0;
            border-top: 1px solid #e2e8f0;
            padding-top: 10px;
        }

        .exchange-rate {
            background: #edf2f7;
            padding: 8px 12px;
            margin-top: 15px;
            font-size: 10px;
            color: #4a5568;
            border-left: 3px solid #4a5568;
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
                <div class="document-title">Liste des Services</div>
                <div class="document-date">Date: {{ $date }}</div>
            </div>
        </div>

        @if($exchangeRate)
            <div class="exchange-rate">
                <strong>Taux de change du jour:</strong> 1 USD = {{ number_format($exchangeRate->rate, 2, ',', ' ') }} FC
            </div>
        @endif

        @php
            $servicesByType = $services->groupBy('service_type');
            $typeLabels = [
                'woman' => 'Services Femme',
                'home' => 'Services à Domicile',
                'other' => 'Autres Services'
            ];
            $typeOrder = ['woman', 'home', 'other'];
        @endphp

        @foreach($typeOrder as $type)
            @if(isset($servicesByType[$type]) && $servicesByType[$type]->count() > 0)
                <div class="category-title">{{ $typeLabels[$type] ?? ucfirst($type) }}</div>
                <table>
                    <thead>
                        <tr>
                            <th>Service</th>
                            <th class="duration">Durée</th>
                            <th class="price">Prix</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($servicesByType[$type] as $service)
                            <tr>
                                <td>
                                    <div class="service-name">{{ $service->name }}</div>
                                    @if($service->description)
                                        <div class="service-description">{{ $service->description }}</div>
                                    @endif
                                </td>
                                <td class="duration">{{ $service->duration_minutes }} min</td>
                                <td class="price">
                                    {{ number_format($service->price, 0, ',', ' ') }} FC
                                    @if($exchangeRate && $exchangeRate->rate > 0)
                                        <span class="usd">≈ ${{ number_format($service->price / $exchangeRate->rate, 2, ',', ' ') }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        @endforeach

        <!-- Résumé -->
        <div class="summary">
            <div class="summary-title">Résumé</div>
            <div class="summary-item">
                <strong>Total services:</strong> {{ $services->count() }}
            </div>
            @foreach($typeOrder as $type)
                @if(isset($servicesByType[$type]))
                    <div class="summary-item">
                        <strong>{{ $typeLabels[$type] ?? ucfirst($type) }}:</strong> {{ $servicesByType[$type]->count() }}
                    </div>
                @endif
            @endforeach
        </div>

        <!-- Footer -->
        <div class="footer">
            Document généré le {{ now()->format('d/m/Y à H:i') }} - {{ $company['name'] }}
        </div>
    </div>
</body>
</html>
