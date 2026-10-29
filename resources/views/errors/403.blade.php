<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accès Refusé - 403</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f0f2f8;
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #333;
        }

        .error-container {
            text-align: center;
            background: white;
            padding: 3rem;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            max-width: 500px;
            width: 90%;
        }

        h1 {
            font-size: 5rem;
            margin: 0;
            color: #ff4757;
        }

        h2 {
            margin-top: 0;
            font-weight: 500;
        }

        p {
            margin-bottom: 2rem;
            line-height: 1.6;
        }

        .emoji {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: #ff4757;
            color: white;
            text-decoration: none;
            border-radius: 50px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #ff6b81;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 71, 87, 0.3);
        }

        .signature {
            margin-top: 2rem;
            font-style: italic;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="emoji">🔐</div>
        <h1>403</h1>
        <h2>Accès Interdit</h2>
        <p>Désolé {{ auth()->user()?->nom ?? 'visiteur' }}, vous n'avez pas l'autorisation d'accéder à cette page. Si vous pensez qu'il s'agit d'une erreur, veuillez contacter l'administrateur.</p>
        <a href="{{route('dashboard')}}" class="btn">Retour à l'accueil</a>
        <div class="signature"> gestion-stock-StockGX</div>
    </div>
