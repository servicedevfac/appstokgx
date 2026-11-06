@extends('layouts.base')


@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0"><i class="fas fa-user me-3"></i> Détails du produit</h3>
                <a href="{{ route('produits.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
            <div class="card-body p-4">
                <dl class="row">
                    <dt class="col-sm-3">Nom</dt>
                    <dd class="col-sm-9">{{ $produit->nom }}</dd>

                    @if($produit->reference)
                        <dt class="col-sm-3">Référence</dt>
                        <dd class="col-sm-9"><code>{{ $produit->reference }}</code></dd>
                    @endif

                    @if($produit->categorie)
                        <dt class="col-sm-3">Catégorie</dt>
                        <dd class="col-sm-9">{{ $produit->categorie->nom }}</dd>
                    @endif

                    <dt class="col-sm-3">Prix d'achat</dt>
                    <dd class="col-sm-9 text-secondary fw-semibold">{{ number_format($produit->prix_achat ?? 0, 0, ',', ' ') }} FCFA</dd>

                    <dt class="col-sm-3">Prix de vente</dt>
                    <dd class="col-sm-9 text-primary fw-bold">{{ number_format($produit->prix, 0, ',', ' ') }} FCFA</dd>

                    <dt class="col-sm-3">Seuil d'alerte</dt>
                    <dd class="col-sm-9">{{ $produit->seuil_alerte }}</dd>

                    <dt class="col-sm-3">Stock actuel</dt>
                    <dd class="col-sm-9">{{ $produit->stock_actuel }}</dd>
                </dl>
                @can('edit produit')
                    <a href="{{ route('produits.edit', $produit->id) }}" class="btn btn-header1 me-2">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endcan
            </div>
        </div>
    </div>
    @endsection
