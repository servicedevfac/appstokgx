@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">{{ $employe->nom }} {{ $employe->prenom }}</h3>
                <p class="text-muted mb-0">Poste : {{ $employe->poste }} | Salaire de base : {{ number_format($employe->salaire_base, 0, ',', ' ') }} FCFA | Plafond avance : {{ number_format($employe->plafond_effectif, 0, ',', ' ') }} FCFA</p>
            </div>
            <div class="text-end">
                <div class="text-muted small">Encours d'avances à rembourser</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($employe->solde_avance_restant, 0, ',', ' ') }} FCFA</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">Historique des Avances et Remboursements</div>
            <div class="card-body">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Motif</th>
                            <th>Montant Avance</th>
                            <th>Remboursé</th>
                            <th>Reste à rembourser</th>
                            <th>Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($employe->avances as $av)
                            <tr>
                                <td>{{ $av->date_avance?->format('d/m/Y') }}</td>
                                <td>{{ $av->motif }}</td>
                                <td>{{ number_format($av->montant, 0, ',', ' ') }} FCFA</td>
                                <td class="text-success">{{ number_format($av->montant_rembourse, 0, ',', ' ') }} FCFA</td>
                                <td class="fw-bold text-danger">{{ number_format($av->reste_a_rembourser, 0, ',', ' ') }} FCFA</td>
                                <td><span class="badge bg-secondary">{{ strtoupper($av->statut) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted">Aucune avance enregistrée pour cet employé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

