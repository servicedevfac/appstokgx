<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>{{ $vente->code_recu }}</title>
    <style>
        body { font-family: sans-serif; font-size: 13px; color: #222; line-height: 1.4; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #ddd; padding-bottom: 12px; }
        .header h2 { margin: 4px 0; font-size: 18px; color: #111; }
        .header p { margin: 2px 0; color: #555; font-size: 11px; }
        .info-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .info-table td { padding: 4px 0; font-size: 12px; vertical-align: top; }
        table.items { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.items th { background: #f2f4f8; border: 1px solid #ccc; padding: 7px; text-align: left; font-size: 11px; text-transform: uppercase; }
        table.items td { border: 1px solid #ddd; padding: 7px; font-size: 12px; }
        .totals-table { width: 50%; margin-left: auto; margin-top: 15px; border-collapse: collapse; }
        .totals-table td { padding: 4px 8px; font-size: 12px; }
        .totals-table tr.total-row { font-weight: bold; font-size: 14px; background: #eef3ff; }
        .footer { text-align: center; margin-top: 25px; font-size: 11px; color: #777; border-top: 1px solid #eee; padding-top: 8px; }
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
            <img src="{{ $logoBase64 }}" alt="Logo" style="max-width: 160px; max-height: 70px; margin-bottom: 6px;">
        @endif
        <h2>{{ $entreprise?->nom ?? config('app.name', 'STOKGX') }}</h2>
        @if($entreprise?->adresse || $entreprise?->telephone)
            <p>{{ implode(' - ', array_filter([$entreprise?->adresse, $entreprise?->telephone])) }}</p>
        @endif
        @if($entreprise?->email)
            <p>{{ $entreprise->email }}</p>
        @endif
        @if($entreprise?->identifiant_fiscal)
            <p>N° CC/IF : {{ $entreprise->identifiant_fiscal }}</p>
        @endif
    </div>

    <table class="info-table">
        <tr>
            <td style="width: 55%;"><strong>Reçu N° :</strong> {{ $vente->code_recu }}</td>
            <td style="width: 45%; text-align: right;"><strong>Date :</strong> {{ $vente->date_vente }}</td>
        </tr>
        <tr>
            <td><strong>Client :</strong> {{ $vente->client->nom ?? 'Client standard' }}</td>
            <td style="text-align: right;"><strong>Mode de paiement :</strong> {{ ucfirst($vente->mode_paiement) }}</td>
        </tr>
        @if($vente->user)
        <tr>
            <td><strong>Vendu par :</strong> {{ $vente->user->nom ?? '' }} {{ $vente->user->prenom ?? '' }}</td>
            <td style="text-align: right;"><strong>Statut :</strong> {{ ucfirst($vente->statut_paiement ?? 'Validé') }}</td>
        </tr>
        @endif
    </table>

    <table class="items">
        <thead>
            <tr>
                <th>Produit</th>
                <th style="text-align: right;">Prix unitaire</th>
                <th style="text-align: center;">Quantité</th>
                <th style="text-align: right;">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vente->details as $detail)
            <tr>
                <td>{{ $detail->produit->nom ?? '-' }}</td>
                <td style="text-align: right;">{{ number_format($detail->prix, 0, ',', ' ') }} FCFA</td>
                <td style="text-align: center;">{{ $detail->quantite }}</td>
                <td style="text-align: right;">{{ number_format($detail->total ?? ($detail->prix * $detail->quantite), 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td>Sous-total :</td>
            <td style="text-align: right;">{{ number_format($vente->details->sum('total'), 0, ',', ' ') }} FCFA</td>
        </tr>
        @if(($vente->remise ?? 0) > 0)
        <tr>
            <td>Remise :</td>
            <td style="text-align: right; color: #c00;">- {{ number_format($vente->remise, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
        <tr class="total-row">
            <td>Total Net :</td>
            <td style="text-align: right;">{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</td>
        </tr>
        <tr>
            <td>Montant Payé :</td>
            <td style="text-align: right; color: green;">{{ number_format($vente->montant_paye ?? 0, 0, ',', ' ') }} FCFA</td>
        </tr>
        @if(($vente->reste_a_payer ?? 0) > 0)
        <tr>
            <td>Reste à Payer :</td>
            <td style="text-align: right; color: red;">{{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA</td>
        </tr>
        @endif
    </table>

    <div class="footer">
        <p>Merci pour votre achat !</p>
    </div>
</body>
</html>
