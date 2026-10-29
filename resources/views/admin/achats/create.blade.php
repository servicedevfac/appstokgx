@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <h3 class="page-title fw-bold mb-4"><i class="fas fa-plus-circle text-primary me-2"></i> Enregistrer un Achat Fournisseur</h3>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <form action="{{ route('achats.store') }}" method="POST">
                    @csrf
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Fournisseur *</label>
                            <select name="fournisseur_id" class="form-select" required>
                                <option value="">-- Sélectionner --</option>
                                @foreach($fournisseurs as $f)
                                    <option value="{{ $f->id }}">{{ $f->nom }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date d'achat *</label>
                            <input type="date" name="date_achat" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Date d'échéance (si dette)</label>
                            <input type="date" name="date_echeance" class="form-control">
                        </div>
                    </div>

                    <h5 class="fw-bold mb-3">Lignes d'achat</h5>
                    <div id="lignes-achat">
                        <div class="row g-2 mb-2 ligne-item">
                            <div class="col-md-5">
                                <select name="produits[0][produit_id]" class="form-select" required>
                                    <option value="">-- Produit --</option>
                                    @foreach($produits as $p)
                                        <option value="{{ $p->id }}">{{ $p->nom }} (Stock: {{ $p->stock }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <input type="number" name="produits[0][quantite]" class="form-control" placeholder="Quantité" min="1" required>
                            </div>
                            <div class="col-md-4">
                                <input type="number" step="0.01" name="produits[0][prix_achat_unitaire]" class="form-control" placeholder="Prix d'achat unitaire (FCFA)" min="0" required>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Montant payé immédiatement (FCFA)</label>
                            <input type="number" step="0.01" name="montant_paye_initial" class="form-control" value="0" min="0">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Mode de paiement</label>
                            <select name="mode_paiement" class="form-select">
                                <option value="especes">Espèces</option>
                                <option value="virement">Virement</option>
                                <option value="mobile_money">Mobile Money</option>
                                <option value="cheque">Chèque</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="reception_immediate" value="1" id="recImm">
                                <label class="form-check-label fw-semibold" for="recImm">
                                    Marchandise déjà réceptionnée (mettre à jour le stock immédiatement)
                                </label>
                            </div>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Enregistrer l'achat</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

