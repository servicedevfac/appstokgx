<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Rapport des Dépenses - STOKGX</title>
    <style>
        @page {
            margin: 20mm 15mm 20mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #2D3748;
            line-height: 1.4;
        }
        .header {
            border-bottom: 2px solid #4361EE;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }
        .logo-title {
            font-size: 22px;
            font-weight: bold;
            color: #1E293B;
            letter-spacing: -0.5px;
        }
        .logo-title span {
            color: #4361EE;
        }
        .subtitle {
            font-size: 10px;
            color: #64748B;
            margin-top: 3px;
        }
        .report-badge {
            background-color: #EEF2FF;
            color: #4361EE;
            padding: 6px 12px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 12px;
            display: inline-block;
        }
        .kpi-table {
            width: 100%;
            margin-bottom: 20px;
            border-collapse: collapse;
        }
        .kpi-card {
            background-color: #F8FAFC;
            border: 1px solid #E2E8F0;
            border-radius: 6px;
            padding: 10px 14px;
        }
        .kpi-card .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #64748B;
            font-weight: 600;
        }
        .kpi-card .value {
            font-size: 16px;
            font-weight: bold;
            color: #0F172A;
            margin-top: 3px;
        }
        .kpi-card .value-danger {
            color: #E11D48;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 20px;
        }
        table.data-table th {
            background-color: #1E293B;
            color: #FFFFFF;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            padding: 8px 10px;
            text-align: left;
        }
        table.data-table td {
            border-bottom: 1px solid #E2E8F0;
            padding: 7px 10px;
            font-size: 10px;
        }
        table.data-table tr:nth-child(even) td {
            background-color: #F8FAFC;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            background: #E2E8F0;
            color: #334155;
            display: inline-block;
        }
        .category-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .category-table th {
            background-color: #F1F5F9;
            color: #475569;
            padding: 6px 8px;
            font-size: 9px;
            text-align: left;
            border-bottom: 1px solid #CBD5E1;
        }
        .category-table td {
            padding: 6px 8px;
            font-size: 10px;
            border-bottom: 1px solid #F1F5F9;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            border-top: 1px solid #E2E8F0;
            padding-top: 8px;
            font-size: 9px;
            color: #94A3B8;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Header -->
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: top;">
                    <div class="logo-title">STOK<span>GX</span></div>
                    <div class="subtitle">Système de Gestion Commerciale & Stocks</div>
                    <div class="subtitle">Édité le : {{ $dateGeneration }}</div>
                </td>
                <td style="vertical-align: top; text-align: right;">
                    <div class="report-badge">RAPPORT DÉTAILLÉ DES DÉPENSES</div>
                    <div class="subtitle" style="margin-top: 5px;">Total des lignes : {{ $depenses->count() }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- KPI Summary Row -->
    <table class="kpi-table">
        <tr>
            <td style="width: 50%; padding-right: 8px;">
                <div class="kpi-card">
                    <div class="label">Total Général Décaissements</div>
                    <div class="value value-danger">{{ number_format($total, 0, ',', ' ') }} FCFA</div>
                </div>
            </td>
            <td style="width: 50%; padding-left: 8px;">
                <div class="kpi-card">
                    <div class="label">Nombre d'opérations enregistrées</div>
                    <div class="value">{{ $depenses->count() }} opération(s)</div>
                </div>
            </td>
        </tr>
    </table>

    <!-- Répartition par catégorie -->
    <div style="font-weight: bold; font-size: 12px; margin-bottom: 6px; color: #1E293B;">
        Répartition par catégorie
    </div>
    <table class="category-table">
        <thead>
            <tr>
                <th>Catégorie de charge</th>
                <th class="text-center">Nombre</th>
                <th class="text-right">Montant (FCFA)</th>
                <th class="text-right">Part (%)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($repartition as $catKey => $catData)
                @php
                    $pourcentage = $total > 0 ? round(($catData['total'] / $total) * 100, 1) : 0;
                @endphp
                <tr>
                    <td><strong>{{ \App\Models\Depense::categories()[$catKey]['label'] ?? ucfirst($catKey) }}</strong></td>
                    <td class="text-center">{{ $catData['count'] }}</td>
                    <td class="text-right"><strong>{{ number_format($catData['total'], 0, ',', ' ') }}</strong></td>
                    <td class="text-right">{{ $pourcentage }} %</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Liste détaillée des dépenses -->
    <div style="font-weight: bold; font-size: 12px; margin-bottom: 6px; color: #1E293B;">
        Détail chronologique des dépenses
    </div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Date</th>
                <th style="width: 28%;">Libellé / Objet</th>
                <th style="width: 18%;">Catégorie</th>
                <th style="width: 14%;">Règlement</th>
                <th style="width: 14%;" class="text-right">Montant</th>
                <th style="width: 14%;">Bénéficiaire</th>
            </tr>
        </thead>
        <tbody>
            @forelse($depenses as $depense)
                <tr>
                    <td>{{ $depense->date_depense ? $depense->date_depense->format('d/m/Y') : '-' }}</td>
                    <td><strong>{{ $depense->titre }}</strong></td>
                    <td><span class="badge">{{ $depense->categorie_label }}</span></td>
                    <td>{{ $depense->mode_paiement }}</td>
                    <td class="text-right" style="font-weight: bold; color: #E11D48;">
                        {{ number_format($depense->montant, 0, ',', ' ') }} FCFA
                    </td>
                    <td>{{ $depense->beneficiaire ?? '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="text-center" style="padding: 20px; color: #94A3B8;">
                        Aucune dépense enregistrée pour la période sélectionnée.
                    </td>
                </tr>
            @endforelse
            @if($depenses->count() > 0)
                <tr style="background-color: #F1F5F9; font-weight: bold;">
                    <td colspan="4" class="text-right" style="padding: 10px;">TOTAL DES DÉPENSES :</td>
                    <td class="text-right" style="color: #E11D48; font-size: 11px; padding: 10px;">
                        {{ number_format($total, 0, ',', ' ') }} FCFA
                    </td>
                    <td></td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Footer -->
    <div class="footer">
        STOKGX - Rapport généré automatiquement pour la gestion commerciale • Document confidentiel
    </div>

</body>
</html>
