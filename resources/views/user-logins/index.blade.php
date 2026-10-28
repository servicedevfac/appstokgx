@extends('layouts.base')
@section('content')
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header card-heade d-flex justify-content-between align-items-center">
                    <h3 class="text-white m-0"><i class="fas fa-list me-2"></i>Historique des connexions</h3>
                </div>
                <div class="card-body">

                    <table class="table table-bordered table-striped">
                        <thead class="table-dark">

                            <tr>
                                <th class="px-4 py-2">Utilisateur</th>
                                <th class="px-4 py-2">Adresse IP</th>
                                <th class="px-4 py-2">Navigateur</th>
                                <th class="px-4 py-2">Date / Heure</th>

                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logins as $login)
                                <tr class="border-t">
                                    <td>{{ $login->user->nom ?? 'Supprimé' }}</td>
                                    <td>{{ $login['ip_address'] ?? '' }}</td>
                                    <td>{{ Str::limit($login->user_agent ?? '', 40) }}</td>
                                    <td>{{ $login->logged_in_at ? \Carbon\Carbon::parse($login->logged_in_at)->format('d/m/Y H:i') : '' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="table-empty-state">
                                        <i class="fas fa-history"></i>
                                        <h5 class="empty-title">Aucune connexion enregistrée</h5>
                                        <p class="empty-desc">Aucun historique de connexion disponible pour le moment.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $logins->links() }}
                    </div>
                </div>
            </div>
        </div>





@endsection
