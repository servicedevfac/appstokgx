<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>GESTION_USP</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 6px; text-align: left; }
        h2 { text-align: center; }
    </style>
</head>
<body>
    <div style="text-align: center;">
        <img src="{{ asset('images/logo-dark.png') }}" alt="Logo" style="width: 150px; margin-bottom: 15px;">
    </div>
    <h2>Reçu de vente</h2>
    <p><strong>Client :</strong> {{ $vente->client->nom }}</p>
    <p><strong>Vendu par :</strong> {{ $vente->user->nom }}</p>
    <p><strong>Code reçu :</strong> {{ $vente->code_recu }}</p>
    <p><strong>Date :</strong> {{ $vente->date_vente }}</p>
    <p><strong>Mode de paiement :</strong> {{ $vente->mode_paiement }}</p>

    <table>
        <thead>
            <tr>
                <th>Produit</th>
                <th>Prix unitaire</th>
                <th>Quantité</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vente->details as $detail)
            <tr>
                <td>{{ $detail->produit->nom }}</td>
                <td>{{ number_format($detail->prix, 0, ',', ' ') }} FCFA</td>
                <td>{{ $detail->quantite }}</td>
                <td>{{ number_format($detail->prix * $detail->quantite, 0, ',', ' ') }} FCFA</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <p><strong>Remise :</strong> {{ number_format($vente->remise, 0, ',', ' ') }} FCFA</p>
    <p><strong>Total payé :</strong> {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</p>
</body>
</html>
