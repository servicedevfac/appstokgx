<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Alerte Stock</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background-color: #dd1313;
            color: white;
            padding: 15px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .content {
            padding: 20px;
            border: 1px solid #ddd;
            border-top: none;
            border-radius: 0 0 5px 5px;
        }

        .product {
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f9f9f9;
            border-left: 4px solid #ff9800;
        }

        .product-name {
            font-weight: bold;
            color: #e65100;
        }

        .stock-info {
            color: #d32f2f;
        }

        .threshold-info {
            color: #666;
        }

        .action-button {
            display: inline-block;
            color: white;
            padding: 10px 15px;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 10px;
            margin-right: 10px;
        }

        .view-button {
            background-color: #02228b;
        }

        .restock-button {
            background-color: #4CAF50;
        }

        .footer {
            margin-top: 20px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>⚠️ Alerte Stock</h1>
    </div>

    <div class="content">
        <p>Bonjour,</p>

        <p>Les produits suivants ont atteint un niveau de stock critique :</p>

        @foreach($produits as $produit)
            <div class="product">
                <div class="product-name">{{ $produit['nom'] }}</div>
                <div class="stock-info">Stock actuel: <strong>{{ $produit['stock'] }}</strong></div>
                <div class="threshold-info">Seuil d'alerte: {{ $produit['seuil'] }}</div>

                @if(isset($produit['url']) && isset($produit['id']))
                    <a href="https://gestusp.app/produits/{{ $produit['id'] }}" class="action-button view-button">Voir le
                        produit</a>
                @elseif(isset($produit['url']))
                    <a href="https://gestusp.app/produits/{{ $produit }}" class="action-button view-button">Voir le produit</a>
                @endif
                <a href="https://gestusp.app/mouvementStocks/create" class="action-button restock-button">Approvisionner</a>
            </div>
        @endforeach
        <p><strong>Veuillez approvisionner ces produits dès que possible.</strong></p>

        <p>Cordialement,<br>L'équipe {{ config('app.name') }}</p>
    </div>
    <div class="footer">
        <p>Ce message a été envoyé automatiquement. Merci de ne pas y répondre.</p>
        <p>© {{ date('Y') }} Gestion_USP</p>
    </div>
</body>

</html>