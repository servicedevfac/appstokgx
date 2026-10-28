@extends('layouts.base')



@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h2 class="mb-4 text-white"><i class="fas fa-history me-2"></i> Historique des modifications d’horaires</h2>
                <a href="{{ url()->previous() }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
            <div class="card-body">

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr class="text-center">
                <th>Jour</th>
                <th>Ancienne ouverture</th>
                <th>Ancienne fermeture</th>
                <th>Nouvelle ouverture</th>
                <th>Nouvelle fermeture</th>
                <th>Modifié par</th>
                <th>Date de modification</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($historique as $log)
                <tr>
                    <td>{{ ucfirst($log->jour_semaine) }}</td>
                    <td>{{ $log->ancienne_ouverture ?? '—' }}</td>
                    <td>{{ $log->ancienne_fermeture ?? '—' }}</td>
                    <td>{{ $log->nouvelle_ouverture ?? '—' }}</td>
                    <td>{{ $log->nouvelle_fermeture ?? '—' }}</td>
                    <td>{{ $log->user->nom ?? 'Inconnu' }}</td>
                    <td>{{ $log->created_at->format('d/m/Y H:i') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Aucune modification enregistrée.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $historique->links() }}
    </div>
</div>
        </div>
    </div>

@endsection
