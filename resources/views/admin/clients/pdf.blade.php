<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Liste des Clients</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table, th, td {
            border: 1px solid black;
        }
        th, td {
            padding: 6px;
            text-align: left;
        }
    </style>
</head>
<body>
    <header class="header" style="margin: 0 20px;">
    <div class="entete" style="display: flex; justify-content: space-between; align-items: center;">
        <div class="first">
            <p>Édité le : {{ now()->format('d/m/Y H:i') }}</p>
        </div>
        <div class="second">
            {{-- <p>Par : {{ auth()->user()->nom }} {{ auth()->user()->prenom }}</p> --}}
        </div>
    </div>
</header>
    <h2>Liste des Clients</h2>
    <table>
        <thead>
            <tr>
                <th>Nom</th>
                <th>Prénom</th>
                <th>Email</th>
                <th>Téléphone</th>
                <th>Date d’inscription</th>
            </tr>
        </thead>
        <tbody>
            @foreach($clients as $client)
            <tr>
                <td>{{ $client->nom }}</td>
                <td>{{ $client->prenom }}</td>
                <td>{{ $client->email }}</td>
                <td>{{ $client->telephone }}</td>
                <td>{{ $client->created_at->format('d/m/Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
