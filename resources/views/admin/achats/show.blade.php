@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">Achat {{ $achat->numero_achat }}</h3>
                <p class="text-muted mb-0">Fournisseur : <strong>{{ $achat->fournisseur?->nom }}</strong> | Date : {{ $achat->date_achat?->format('d/m/Y') }}</p>
            </div>
            <div>
                @if($achat->statut_reception !== 'recu')
                    @can('réceptionner achat')
                        <form action="{{ route('achats.receptionner', $achat->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success fw-semibold">
                                <i class="fas fa-check-circle me-1"></i> Valider la Réception (Entrée en stock)
                            </button>
                        </form>
                    @endcan
                @else
                    <span class="badge bg-success fs-6">Marchandise Réceptionnée</span>
                @endif
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Produits commandés</div>
                    <div class="card-body">
                        <table class="table align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Qté reçue</th>
                                    <th>Prix Achat Unitaire</th>
                                    <th>Total Ligne</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($achat->details as $d)
                                    <tr>
                                        <td>{{ $d->produit?->nom }}</td>
                                        <td>{{ $d->quantite }}</td>
                                        <td>{{ $d->quantite_recue }}</td>
                                        <td>{{ number_format($d->prix_achat_unitaire, 0, ',', ' ') }} FCFA</td>
                                        <td class="fw-bold">{{ number_format($d->total_ligne, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Historique des Paiements Fournisseur</div>
                    <div class="card-body">
                        <table class="table table-sm align-middle">
                            <thead>
                                <tr>
                                    <th>Référence</th>
                                    <th>Date</th>
                                    <th>Mode</th>
                                    <th>Montant</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($achat->paiements as $p)
                                    <tr>
                                        <td>{{ $p->reference }}</td>
                                        <td>{{ $p->date_paiement?->format('d/m/Y') }}</td>
                                        <td>{{ ucfirst($p->mode_paiement) }}</td>
                                        <td class="fw-bold text-success">{{ number_format($p->montant, 0, ',', ' ') }} FCFA</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-muted text-center">Aucun paiement enregistré.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white fw-bold">Situation Financière</div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span>Montant Total :</span>
                            <strong>{{ number_format($achat->montant_total, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span>Déjà Payé :</span>
                            <strong>{{ number_format($achat->montant_paye, 0, ',', ' ') }} FCFA</strong>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2 text-danger fs-5">
                            <span>Reste à payer :</span>
                            <strong>{{ number_format($achat->reste_a_payer, 0, ',', ' ') }} FCFA</strong>
                        </div>

                        @if($achat->reste_a_payer > 0)
                            @can('payer fournisseur')
                            <hr>
                            <form action="{{ route('achats.payer', $achat->id) }}" method="POST">
                                @csrf
                                <div class="mb-2">
                                    <label class="form-label small">Montant du règlement *</label>
                                    <input type="number" step="0.01" name="montant" class="form-control" max="{{ $achat->reste_a_payer }}" required>
                                </div>
                                <div class="mb-2">
                                    <label class="form-label small">Mode de paiement</label>
                                    <select name="mode_paiement" class="form-select">
                                        <option value="especes">Espèces</option>
                                        <option value="virement">Virement</option>
                                        <option value="mobile_money">Mobile Money</option>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">Enregistrer le paiement</button>
                            </form>
                            @endcan
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

