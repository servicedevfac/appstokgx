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
    @php
        $entreprise = $vente->entreprise ?? null;
        $logoBase64 = null;
        $logoFile = null;
        if ($entreprise && $entreprise->logo && file_exists(public_path($entreprise->logo))) {
            $logoFile = public_path($entreprise->logo);
        } elseif (file_exists(public_path('assets/images/logo-dark.png'))) {
            $logoFile = public_path('assets/images/logo-dark.png');
        }

        if ($logoFile && is_readable($logoFile)) {
            $ext = strtolower(pathinfo($logoFile, PATHINFO_EXTENSION));
            $mime = match($ext) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png'         => 'image/png',
                'webp'        => 'image/webp',
                'gif'         => 'image/gif',
                default       => 'image/png',
            };
            $data = @file_get_contents($logoFile);
            if ($data !== false) {
                $logoBase64 = 'data:' . $mime . ';base64,' . base64_encode($data);
            }
        }
    @endphp

    <div class="header">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="max-width: 140px; max-height: 65px; display: block; margin: 0 auto 6px auto; object-fit: contain;" alt="Logo">
        @endif

        <h3 style="margin: 2px 0; font-size: 14px; font-weight: bold;">{{ $entreprise?->nom ?? config('app.name', 'STOKGX') }}</h3>
        @if($entreprise?->adresse || $entreprise?->telephone)
            <p style="margin: 2px 0; font-size: 11px;">
                {{ implode(' - ', array_filter([$entreprise?->adresse, $entreprise?->telephone])) }}
            </p>
        @endif
        @if($entreprise?->email)
            <p style="margin: 2px 0; font-size: 10px;">{{ $entreprise->email }}</p>
        @endif
        @if($entreprise?->identifiant_fiscal)
            <p style="margin: 2px 0; font-size: 10px;">N° CC/IF : {{ $entreprise->identifiant_fiscal }}</p>
        @endif
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
