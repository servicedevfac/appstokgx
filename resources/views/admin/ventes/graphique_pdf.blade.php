<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Graphique des ventes</title>
    <style>

        h1 {
            color: #333;
            text-align: center;
        }
        .info {
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-radius: 5px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>
    <h1>Rapport des ventes</h1>

    <div class="info">
        <p><strong>Période:</strong> {{ $dateDebut }} au {{ $dateFin }}</p>
        @if($recherche)
        <p><strong>Recherche:</strong> {{ $recherche }}</p>
        @endif
    </div>

    <table>
        <thead>
            <tr>
                <th>Période</th>
                <th>Montant total (FCFA)</th>
            </tr>
        </thead>
        <tbody>
            @foreach($labels as $index => $periode)
            <tr>
                <td>{{ $periode }}</td>
                <td>{{ number_format($data[$index], 0, ',', ' ') }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th>Total</th>
                <th>{{ number_format(array_sum($data), 0, ',', ' ') }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>
