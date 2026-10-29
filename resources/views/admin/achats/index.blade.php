@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title fw-bold mb-0"><i class="fas fa-shopping-bag text-primary me-2"></i> Achats Fournisseurs & Dettes</h3>
            @can('créer achat')
                <a href="{{ route('achats.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nouvel Achat</a>
            @endcan
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>N° Achat</th>
                                <th>Fournisseur</th>
                                <th>Date</th>
                                <th>Montant Total</th>
                                <th>Payé</th>
                                <th>Reste à payer</th>
                                <th>Statut Réception</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($achats as $achat)
                                <tr>
                                    <td><strong>{{ $achat->numero_achat }}</strong></td>
                                    <td>{{ $achat->fournisseur?->nom }}</td>
                                    <td>{{ $achat->date_achat?->format('d/m/Y') }}</td>
                                    <td>{{ number_format($achat->montant_total, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ number_format($achat->montant_paye, 0, ',', ' ') }} FCFA</td>
                                    <td class="fw-bold {{ $achat->reste_a_payer > 0 ? 'text-danger' : 'text-success' }}">
                                        {{ number_format($achat->reste_a_payer, 0, ',', ' ') }} FCFA
                                    </td>
                                    <td>
                                        <span class="badge {{ $achat->statut_reception === 'recu' ? 'bg-success' : 'bg-warning text-dark' }}">
                                            {{ $achat->statut_reception === 'recu' ? 'Réceptionné' : 'En attente' }}
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('achats.show', $achat->id) }}" class="btn btn-sm btn-outline-primary">Gérer</a>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="text-center text-muted">Aucun achat enregistré.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

