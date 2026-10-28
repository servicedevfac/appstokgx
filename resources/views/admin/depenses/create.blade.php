@extends('layouts.base')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-wallet"></i>
                    <span>Enregistrer une dépense</span>
                </h3>
                <a href="{{ route('depenses.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Veuillez corriger les erreurs ci-dessous :</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('depenses.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="titre" class="form-label">Libellé / Titre de la dépense <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre') }}" placeholder="Ex: Paiement loyer commercial, Facture CIE, Fournitures..." required>
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="montant" class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant') }}" placeholder="Ex: 50000" required>
                                <span class="input-group-text">FCFA</span>
                                @error('montant')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="categorie" class="form-label">Catégorie <span class="text-danger">*</span></label>
                            <select class="form-select @error('categorie') is-invalid @enderror" id="categorie" name="categorie" required>
                                <option value="">Sélectionner une catégorie</option>
                                @foreach($categories as $key => $label)
                                    <option value="{{ $key }}" {{ old('categorie') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('categorie')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="date_depense" class="form-label">Date de dépense <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_depense') is-invalid @enderror" id="date_depense" name="date_depense" value="{{ old('date_depense', date('Y-m-d')) }}" required>
                            @error('date_depense')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="mode_paiement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                            <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement" required>
                                @foreach($modesPaiement as $key => $label)
                                    <option value="{{ $key }}" {{ old('mode_paiement', 'Especes') == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('mode_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="beneficiaire" class="form-label">Bénéficiaire / Fournisseur</label>
                            <input type="text" class="form-control @error('beneficiaire') is-invalid @enderror" id="beneficiaire" name="beneficiaire" value="{{ old('beneficiaire') }}" placeholder="Ex: Propriétaire local, CIE, SODECI, Fournisseur X...">
                            @error('beneficiaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Personne, entreprise ou organisme destinataire du paiement.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="justificatif" class="form-label">Pièce justificative (Reçu / Facture)</label>
                            <input type="file" class="form-control @error('justificatif') is-invalid @enderror" id="justificatif" name="justificatif" accept=".jpg,.jpeg,.png,.pdf">
                            @error('justificatif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Formats acceptés : JPG, PNG, PDF (Max : 4 Mo).</small>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description / Détails complémentaires</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3" placeholder="Précisions ou justification éventuelle de la dépense...">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('depenses.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-header1 btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Enregistrer la dépense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
