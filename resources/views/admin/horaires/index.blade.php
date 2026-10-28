@extends('layouts.base')

@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h2 class="text-white mb-0"><i class="fas fa-clock"></i> Liste des horaires de ventes </h2>
                <a href="{{ route('admin.horaires.historique') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-history me-1"></i> Historique des modifications
                </a>
            </div>
            <div class="card-body">
            <div class="card-body">
                <a href="{{ route('admin.horaires.edit') }}" class="btn btn-header1 mb-3 fw-semibold">
                    <i class="fas fa-clock me-1"></i> Modifier les horaires
                </a>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Jour</th>
                                <th>Heure d'ouverture</th>
                                <th>Heure de fermeture</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($horaires as $horaire)
                                <tr>
                                    <td>{{ ucfirst($horaire->jour_semaine) }}</td>
                                    <td>{{ $horaire->heure_ouverture }}</td>
                                    <td>{{ $horaire->heure_fermeture }}</td>
                                    <td class="text-end">
                                        <a class="btn  btn-success" href="{{ route('admin.horaires.edit', $horaire->id) }}"><i class="fas fa-edit "></i></a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="table-empty-state">
                                        <i class="fas fa-clock"></i>
                                        <h5 class="empty-title">Aucun horaire configuré</h5>
                                        <p class="empty-desc">Aucun horaire d'ouverture n'a été défini pour le moment.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
