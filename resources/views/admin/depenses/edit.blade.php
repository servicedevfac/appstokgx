@extends('layouts.base')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-edit"></i>
                    <span>Modifier la dépense : {{ $depense->titre }}</span>
                </h3>
                <div class="d-flex gap-2">
                    <a href="{{ route('depenses.show', $depense) }}" class="btn btn-header fw-bold shadow-sm">
                        <i class="fas fa-eye me-1"></i> Voir
                    </a>
                    <a href="{{ route('depenses.index') }}" class="btn btn-header fw-bold shadow-sm">
                        <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                    </a>
                </div>
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

                <form action="{{ route('depenses.update', $depense) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-8">
                            <label for="titre" class="form-label">Libellé / Titre de la dépense <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('titre') is-invalid @enderror" id="titre" name="titre" value="{{ old('titre', $depense->titre) }}" required>
                            @error('titre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="montant" class="form-label">Montant (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="1" min="1" class="form-control @error('montant') is-invalid @enderror" id="montant" name="montant" value="{{ old('montant', (int)$depense->montant) }}" required>
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
                                    <option value="{{ $key }}" {{ old('categorie', $depense->categorie) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('categorie')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="date_depense" class="form-label">Date de dépense <span class="text-danger">*</span></label>
                            <input type="date" class="form-control @error('date_depense') is-invalid @enderror" id="date_depense" name="date_depense" value="{{ old('date_depense', $depense->date_depense ? $depense->date_depense->format('Y-m-d') : date('Y-m-d')) }}" required>
                            @error('date_depense')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="mode_paiement" class="form-label">Mode de paiement <span class="text-danger">*</span></label>
                            <select class="form-select @error('mode_paiement') is-invalid @enderror" id="mode_paiement" name="mode_paiement" required>
                                @foreach($modesPaiement as $key => $label)
                                    <option value="{{ $key }}" {{ old('mode_paiement', $depense->mode_paiement) == $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            @error('mode_paiement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="beneficiaire" class="form-label">Bénéficiaire / Fournisseur</label>
                            <input type="text" class="form-control @error('beneficiaire') is-invalid @enderror" id="beneficiaire" name="beneficiaire" value="{{ old('beneficiaire', $depense->beneficiaire) }}">
                            @error('beneficiaire')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="justificatif" class="form-label">Pièce justificative</label>
                            @if($depense->justificatif)
                                <div class="mb-2">
                                    <span class="badge bg-light text-dark border">
                                        <i class="fas fa-paperclip me-1 text-primary"></i>Fichier actuel :
                                        <a href="{{ asset($depense->justificatif) }}" target="_blank" class="text-decoration-none fw-bold ms-1">Voir le reçu</a>
                                    </span>
                                </div>
                            @endif
                            <input type="file" class="form-control @error('justificatif') is-invalid @enderror" id="justificatif" name="justificatif" accept=".jpg,.jpeg,.png,.pdf">
                            @error('justificatif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Laisser vide pour conserver le document existant (JPG, PNG, PDF max 4 Mo).</small>
                        </div>

                        <div class="col-12">
                            <label for="description" class="form-label">Description / Détails complémentaires</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" id="description" name="description" rows="3">{{ old('description', $depense->description) }}</textarea>
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
                            <i class="fas fa-save me-2"></i> Mettre à jour la dépense
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
