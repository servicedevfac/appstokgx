@extends('layouts.base')

@section('content')
<div class="container mx-auto mt-6">
    <h1 class="text-2xl font-bold mb-4">Historique des connexions</h1>
<h1>Détails de la connexion</h1>

    <p><strong>Utilisateur :</strong> {{ $logins->user->name ?? 'Inconnu' }}</p>
    <p><strong>Adresse IP :</strong> {{ $logins['ip_address'] }}</p>
    <p><strong>Appareil :</strong> {{ $logins['user_agent'] }}</p>
    <p><strong>Date :</strong>
    {{ $logins['logged_in_at'] ? $logins['logged_in_at']->format('d/m/Y H:i:s') : 'Non disponible' }}
    </p>

</div>

</div>
@endsection
