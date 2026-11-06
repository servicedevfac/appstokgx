@extends('layouts.base')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-edit"></i>
                    <span>Modifier le produit</span>
                </h3>
                <a href="{{ route('produits.index') }}" class="btn btn-header fw-bold shadow-sm">
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

                <form class="needs-validation" novalidate method="POST" action="{{ route('produits.update', $produit->id) }}">
                    @csrf
                    @method('PUT')

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="nom" class="form-label">Nom du produit <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nom') is-invalid @enderror" id="nom" name="nom" value="{{ old('nom', $produit->nom) }}" placeholder="Entrez le nom du produit" required maxlength="255">
                            @error('nom')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Veuillez saisir le nom du produit.</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="prix_achat" class="form-label">Prix d'achat (FCFA)</label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control @error('prix_achat') is-invalid @enderror" id="prix_achat" name="prix_achat" value="{{ old('prix_achat', $produit->prix_achat) }}" placeholder="Ex: 3500">
                                <span class="input-group-text">FCFA</span>
                                @error('prix_achat')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <small class="text-muted">Coût d'achat servant à estimer la marge et le bénéfice net.</small>
                        </div>

                        <div class="col-md-6">
                            <label for="prix" class="form-label">Prix de vente (FCFA) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="number" step="0.01" min="0" class="form-control @error('prix') is-invalid @enderror" id="prix" name="prix" value="{{ old('prix', $produit->prix) }}" placeholder="Ex: 5000" required>
                                <span class="input-group-text">FCFA</span>
                                @error('prix')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @else
                                    <div class="invalid-feedback">Veuillez saisir un prix valide.</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="seuil_alerte" class="form-label">Seuil d'alerte <span class="text-danger">*</span></label>
                            <input type="number" min="0" class="form-control @error('seuil_alerte') is-invalid @enderror" id="seuil_alerte" name="seuil_alerte" value="{{ old('seuil_alerte', $produit->seuil_alerte) }}" placeholder="Ex: 5" required>
                            @error('seuil_alerte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @else
                                <div class="invalid-feedback">Veuillez saisir un seuil d'alerte valide.</div>
                            @enderror
                            <small class="text-muted">Alerte déclenchée lorsque le stock atteint ce niveau minimum.</small>
                        </div>

                        @if(isset($categories) && $categories->count() > 0)
                        <div class="col-md-6">
                            <label for="categorie_id" class="form-label">Catégorie</label>
                            <select class="form-select @error('categorie_id') is-invalid @enderror" id="categorie_id" name="categorie_id">
                                <option value="">-- Aucune catégorie --</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ old('categorie_id', $produit->categorie_id) == $cat->id ? 'selected' : '' }}>
                                        {{ $cat->nom }}
                                    </option>
                                @endforeach
                            </select>
                            @error('categorie_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        @endif
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('produits.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-header1 btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Mettre à jour le produit
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
