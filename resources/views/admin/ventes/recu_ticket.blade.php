<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GESTION_USP</title></title>
    <style>
        body {
            font-family: 'Courier New', monospace;
            max-width: 80mm; /* Largeur standard imprimante thermique */
        
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }
        .ticket{
            font-family: 'Courier New', monospace;
            margin: 0 auto;
             text-align: center;
            font-size: 12px;
            line-height: 1.4;
            color: #000;
        }

        .header, .footer {
            text-align: center !important;
        }

        h2, h3 {
            margin: 5px 0;
        }

        .details, .items, .totals {
            width: 100%;
            margin-top: 10px;
        }

        .items table, .totals table {
            width: 100%;
            border-collapse: collapse;
        }

        .items th, .items td, .totals th, .totals td {
            text-align: left;
            padding: 3px 0;
        }

        .items th {
            border-bottom: 1px dashed #000;
        }

        .items td {
            border-bottom: 1px dotted #000;
        }

        .totals td {
            font-weight: bold;
        }

        .right { text-align: right; }
        .center { text-align: center; }

        @media print {
            body {
                margin: 0;
                padding: 0px;
            }
        }
    </style>
</head>
<body>
    
    <div class="header">
    <img 
    src="{{ public_path('assets/images/logo-dark.png') }}"
    style="width:120px; display:block; margin:auto;"
>

        <p>San Pedro - 07 99 28 82 54</p>
        <hr>
    </div>

    <div class="details">
        <p>Client: {{ $vente->client->nom ?? '' }}</p>
        <p>Date: {{ $vente->created_at->format('d/m/Y H:i') }}</p>
        <p>Date de vente: {{ $vente->date_vente }}</p>
        <p>Reçu: {{ $vente->code_recu }}</p>
    </div>
    <div class="items">
        <table>
            <thead>
                <tr>
                    <th>Produit</th>
                    <th class="center">Qté</th>
                    <th class="right">Prix</th>
                    <th class="right">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($vente->details as $detail)
                <tr>
                    <td>{{ $detail->produit->nom }}</td>
                    <td class="center">{{ $detail->quantite }}</td>
                    <td class="right">{{ number_format($detail->prix,0,',',' ') }}</td>
                    <td class="right">{{ number_format($detail->total,0,',',' ') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="totals">
        <table>
            <tr>
                <td>Total brut:</td>
                <td class="right">{{ number_format($vente->details->sum('total'), 0, ',', ' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Remise:</td>
                <td class="right">{{ number_format($vente->remise ?? 0,0,',',' ') }} FCFA</td>
            </tr>
            <tr>
                <td><strong>Total net:</strong></td>
                <td class="right">
                    {{ number_format($vente->details->sum('total') - ($vente->remise ?? 0),0,',',' ') }} FCFA
                </td>
            </tr>
            <tr>
                <td>Avance:</td>
                <td class="right">{{ number_format($vente->montant_paye ?? 0,0,',',' ') }} FCFA</td>
            </tr>
            <tr>
                <td>Reste à payer:</td>
                <td class="right">
                    {{ number_format(($vente->details->sum('total') - ($vente->remise ?? 0)) - ($vente->montant_paye ?? 0),0,',',' ') }} FCFA
                </td>
            </tr>
            <tr>
                <td>Mode paiement:</td>
                <td class="right">{{ ucfirst($vente->mode_paiement ?? 'Espèces') }}</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        <hr>
        <p>Merci pour votre achat !</p>
    </div>
    


    {{-- <script>
        // Ouvre directement la fenêtre d'impression
        window.onload = function() {
            window.print();
        }
    </script> --}}
</body>
</html>
