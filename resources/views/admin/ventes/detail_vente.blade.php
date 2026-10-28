@extends('layouts.base')

@section('content')

<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center card-heade">
                <h3 class="text-white m-0"><i class="fas fa-list me-2"></i>Détail de Vente</h3>
                <a href="{{ route('ventes.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i>Retour
                </a>
            </div>
            <div class="card-body">
                <p><strong>Code reçu :</strong> {{ $vente->code_recu }}</p>
                <p><strong>Client :</strong> {{ $vente->client->nom ?? '-' }} {{ $vente->client->prenom }}</p>
                <p><strong>Utilisateur :</strong> {{ $vente->user->nom ?? '-' }}</p>
                <p><strong>Date :</strong> {{ $vente->created_at ? $vente->created_at->format('d/m/Y H:i') : '-' }}</p>
                <p><strong>Total :</strong> {{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</p>
                <p><strong>Remise :</strong> {{ number_format($vente->remise, 0, ',', ' ') }} FCFA</p>
                <p><strong>Montant payé :</strong> {{ number_format($vente->montant_paye, 0, ',', ' ') }} FCFA</p>
                <p><strong>Reste à payer :</strong> {{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA</p>
                <p><strong>État de paiement :</strong>
                    @if ($vente->est_paye)
                        <span class="badge bg-success">Payé</span>
                    @else
                        <span class="badge bg-danger">Non payé</span>
                    @endif
                </p>
            </div>
        </div>

        {{-- Produits --}}
        <div class="card mt-3">
            <div class="card-header card-heade">
                <h5 class="mb-0 text-white">Produits vendus</h5>
            </div>
            <div class="card-body p-0">
                <table class="table table-bordered mb-0">
                    <thead>
                        <tr>
                            <th>Produit</th>
                            <th>Prix unitaire</th>
                            <th>Quantité</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($vente->details as $detail)
                            <tr>
                                <td>{{ $detail->produit->nom ?? '-' }}</td>
                                <td>{{ number_format($detail->prix, 0, ',', ' ') }} FCFA</td>
                                <td>{{ $detail->quantite }}</td>
                                <td>{{ number_format($detail->prix * $detail->quantite, 0, ',', ' ') }} FCFA</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Paiements --}}
        <div class="card mt-3">
            <div class="card-header card-heade">
                <h5 class="mb-0 text-white">Historique des paiements</h5>
            </div>
            <div class="card-body">
                @if($vente->paiements->count() > 0)
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Montant</th>
                                <th>Mode</th>
                                <th>Reste à payer</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vente->paiements as $paiement)
                                <tr>
                                    <td>{{ $paiement->date_paiement }}</td>
                                    <td>{{ number_format($paiement->montant, 0, ',', ' ') }} FCFA</td>
                                    <td>{{ ucfirst($paiement->mode_paiement) }}</td>
                                    <td>{{ number_format($paiement->reste_a_payer, 0, ',', ' ') }} FCFA</td>
                                    <td>
                                        <a href="{{ route('paiements.ticket', $paiement->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-print"></i> Ticket
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <p>Aucun paiement enregistré pour cette vente.</p>
                @endif

                {{-- Formulaire d’ajout d’un paiement si pas encore payé --}}
                @if (!$vente->est_paye && $vente->reste_a_payer > 0)
                    <hr class="my-4">
                    <div class="card border-0 shadow-sm bg-light p-3">
                        <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
                            <h5 class="mb-0 fw-bold text-dark">
                                <i class="fas fa-hand-holding-usd text-success me-2"></i>Enregistrer un règlement
                            </h5>
                            <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                Reste à payer : {{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA
                            </span>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <strong>Erreur de validation :</strong>
                                </div>
                                <ul class="mb-0 ps-3">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('paiements.store', $vente->id) }}" method="POST" id="form-paiement" class="needs-validation" novalidate>
                            @csrf
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="montant_paiement" class="form-label fw-bold">
                                        Montant à payer (FCFA) <span class="text-danger">*</span>
                                    </label>
                                    <input type="number" 
                                           id="montant_paiement" 
                                           name="montant" 
                                           class="form-control @error('montant') is-invalid @enderror" 
                                           min="1" 
                                           max="{{ $vente->reste_a_payer }}" 
                                           step="any" 
                                           value="{{ old('montant', $vente->reste_a_payer) }}" 
                                           required>
                                    @error('montant')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text" id="montant-helper">
                                        Montant maximum autorisé : <strong>{{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA</strong>
                                    </div>
                                    <div id="montant-erreur" class="text-danger mt-1 small d-none fw-bold">
                                        <i class="fas fa-exclamation-triangle me-1"></i>Le montant ne peut pas dépasser le solde restant à payer ({{ number_format($vente->reste_a_payer, 0, ',', ' ') }} FCFA).
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="mode_paiement" class="form-label fw-bold">
                                        Mode de paiement <span class="text-danger">*</span>
                                    </label>
                                    <select name="mode_paiement" id="mode_paiement" class="form-select @error('mode_paiement') is-invalid @enderror" required>
                                        <option value="">-- Choisir un moyen de paiement --</option>
                                        <option value="espèces" {{ old('mode_paiement') == 'espèces' ? 'selected' : '' }}>Espèces</option>
                                        <option value="mobile_money" {{ old('mode_paiement') == 'mobile_money' ? 'selected' : '' }}>Mobile Money</option>
                                        <option value="carte" {{ old('mode_paiement') == 'carte' ? 'selected' : '' }}>Carte bancaire</option>
                                        <option value="virement" {{ old('mode_paiement') == 'virement' ? 'selected' : '' }}>Virement</option>
                                        <option value="chèque" {{ old('mode_paiement') == 'chèque' ? 'selected' : '' }}>Chèque</option>
                                    </select>
                                    @error('mode_paiement')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <button type="submit" class="btn btn-success" id="btn-submit-paiement">
                                <i class="fas fa-check-circle me-1"></i> Valider le paiement
                            </button>
                        </form>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function() {
                            const montantInput = document.getElementById('montant_paiement');
                            const maxMontant = {{ (float) $vente->reste_a_payer }};
                            const erreurMsg = document.getElementById('montant-erreur');
                            const btnSubmit = document.getElementById('btn-submit-paiement');

                            if (montantInput) {
                                montantInput.addEventListener('input', function() {
                                    const val = parseFloat(this.value || 0);
                                    if (val > maxMontant) {
                                        this.classList.add('is-invalid');
                                        erreurMsg.classList.remove('d-none');
                                        btnSubmit.disabled = true;
                                    } else {
                                        this.classList.remove('is-invalid');
                                        erreurMsg.classList.add('d-none');
                                        btnSubmit.disabled = false;
                                    }
                                });
                            }
                        });
                    </script>
                @else
                    <hr class="my-4">
                    <div class="alert alert-success d-flex align-items-center mb-0" role="alert">
                        <i class="fas fa-check-double fa-2x me-3"></i>
                        <div>
                            <h6 class="alert-heading fw-bold mb-1">Vente entièrement soldée</h6>
                            <p class="mb-0">Tous les paiements nécessaires ont été effectués pour cette vente. Aucun solde restant dû.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

@endsection
