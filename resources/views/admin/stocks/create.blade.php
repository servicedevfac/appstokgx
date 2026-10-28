@extends('layouts.base')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-boxes"></i>
                    <span>Nouveau mouvement de stock</span>
                </h3>
                <a href="{{ route('mouvementStocks.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>

            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Veuillez corriger les erreurs ci-dessous :</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('mouvementStocks.store') }}" class="needs-validation" novalidate>
                    @csrf

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="produit_id" class="form-label">Produit <span class="text-danger">*</span></label>
                            <select name="produit_id" id="produit_id" class="form-select @error('produit_id') is-invalid @enderror" required>
                                <option value="">-- Choisir un produit --</option>
                                @foreach($produits as $produit)
                                    <option value="{{ $produit->id }}" {{ old('produit_id') == $produit->id ? 'selected' : '' }}>
                                        {{ $produit->nom }} (Stock actuel: {{ $produit->stock_actuel }})
                                    </option>
                                @endforeach
                            </select>
                            @error('produit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6">
                            <label for="type_mouvement" class="form-label">Type de mouvement <span class="text-danger">*</span></label>
                            <select name="type_mouvement" id="type_mouvement" class="form-select @error('type_mouvement') is-invalid @enderror" required>
                                <option value="">-- Choisir un type de mouvement --</option>
                                <option value="entree" {{ old('type_mouvement') == 'entree' ? 'selected' : '' }}>Entrée</option>
                                <option value="sortie" {{ old('type_mouvement') == 'sortie' ? 'selected' : '' }}>Sortie</option>
                            </select>
                            @error('type_mouvement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="quantite" class="form-label">Quantité <span class="text-danger">*</span></label>
                            <input type="number" name="quantite" id="quantite" class="form-control @error('quantite') is-invalid @enderror" required min="1" value="{{ old('quantite', 1) }}" placeholder="Ex: 10">
                            @error('quantite')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="motif" class="form-label">Motif <span class="text-danger">*</span></label>
                            <select name="motif" id="motif" class="form-select @error('motif') is-invalid @enderror" required>
                                <option value="">-- Choisir un motif --</option>
                                <option value="reapprovisionnement" {{ old('motif') == 'reapprovisionnement' ? 'selected' : '' }}>Approvisionnement</option>
                                <option value="defaut" {{ old('motif') == 'defaut' ? 'selected' : '' }}>Défaillance</option>
                                <option value="autre" {{ old('motif') == 'autre' ? 'selected' : '' }}>Autre</option>
                            </select>
                            @error('motif')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-4">
                            <label for="date_mouvement" class="form-label">Date du mouvement <span class="text-danger">*</span></label>
                            <input type="date" name="date_mouvement" id="date_mouvement" class="form-control @error('date_mouvement') is-invalid @enderror" value="{{ old('date_mouvement', date('Y-m-d')) }}" required>
                            @error('date_mouvement')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('mouvementStocks.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-header1 btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Enregistrer le mouvement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
