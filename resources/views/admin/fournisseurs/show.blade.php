@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">{{ $fournisseur->nom }}</h3>
                <p class="text-muted mb-0">Tél : {{ $fournisseur->telephone ?? '-' }} | Adresse : {{ $fournisseur->adresse ?? '-' }}</p>
            </div>
            <div class="text-end">
                <div class="text-muted small">Dette fournisseur en cours</div>
                <div class="fs-4 fw-bold text-danger">{{ number_format($fournisseur->solde_dette, 0, ',', ' ') }} FCFA</div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white fw-bold">Historique des Achats</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>N° Achat</th>
                                <th>Date</th>
                                <th>Montant Total</th>
                                <th>Payé</th>
                                <th>Reste à payer</th>
                                <th>Réception</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($fournisseur->achats as $achat)
                                <tr>
                                    <td><strong>{{ $achat->numero_achat }}</strong></td>
                                    <td>{{ $achat->date_achat?->format('d/m/Y') }}</td>
                                    <td>{{ number_format($achat->montant_total, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ number_format($achat->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    <td class="text-danger fw-bold">{{ number_format($achat->reste_a_payer, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <span class="badge {{ $achat->statut_reception === 'recu' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ strtoupper($achat->statut_reception) }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('achats.show', $achat->id) }}" class="btn btn-sm btn-outline-primary">Voir</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted">Aucun achat pour ce fournisseur.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

