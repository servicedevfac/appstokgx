@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">
                    <i class="fas fa-building text-primary me-2"></i> Mon Entreprise
                </h3>
                <p class="text-muted mb-0">
                    Configurez les informations générales, le logo et les paramètres de votre entreprise.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('abonnement.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-gem me-1"></i> Voir Mon Abonnement
                </a>
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            {{-- Fiche récapitulative de l'entreprise unique --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-id-card text-primary me-1"></i> Fiche d'identité de l'entreprise
                    </div>
                    <div class="card-body">
                        @if($activeEntreprise)
                            @php $subEnt = $activeEntreprise->abonnementActuel(); @endphp
                            <div class="d-flex align-items-center gap-3 mb-4">
                                @if($activeEntreprise->logo)
                                    <img src="{{ asset($activeEntreprise->logo) }}" alt="Logo" class="rounded border p-1" style="width: 64px; height: 64px; object-fit: contain;">
                                @else
                                    <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-3" style="width: 64px; height: 64px;">
                                        {{ strtoupper(substr($activeEntreprise->nom, 0, 1)) }}
                                    </div>
                                @endif
                                <div>
                                    <h5 class="fw-bold mb-1">{{ $activeEntreprise->nom }}</h5>
                                    <span class="badge bg-secondary">{{ $activeEntreprise->code }}</span>
                                    <span class="badge {{ $activeEntreprise->actif ? 'bg-success' : 'bg-danger' }} ms-1">
                                        {{ $activeEntreprise->actif ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>

                            <ul class="list-group list-group-flush">
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Formule d'abonnement</span>
                                    @if($subEnt)
                                        <span class="badge {{ $subEnt->badgeClass() }}">{{ $subEnt->nom_plan }} ({{ $subEnt->statutLabel() }})</span>
                                    @else
                                        <span class="fw-semibold">Standard</span>
                                    @endif
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Téléphone</span>
                                    <span class="fw-semibold">{{ $activeEntreprise->telephone ?? '—' }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Email</span>
                                    <span class="fw-semibold">{{ $activeEntreprise->email ?? '—' }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Adresse</span>
                                    <span class="fw-semibold">{{ $activeEntreprise->adresse ?? '—' }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Identifiant Fiscal (NIF)</span>
                                    <span class="fw-semibold">{{ $activeEntreprise->identifiant_fiscal ?? '—' }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Devise</span>
                                    <span class="fw-semibold">{{ $activeEntreprise->devise ?? 'FCFA' }}</span>
                                </li>
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span class="text-muted">Plafond d'avance sur salaire</span>
                                    <span class="fw-bold text-primary">{{ number_format($activeEntreprise->plafond_avance_salaire, 0, ',', ' ') }} FCFA</span>
                                </li>
                            </ul>
                        @else
                            <p class="text-muted mb-0">Aucune entreprise rattachée à ce compte.</p>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Formulaire de modification des paramètres de l'entreprise --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white fw-bold">
                        <i class="fas fa-cog text-primary me-1"></i>
                        Modifier les paramètres de mon entreprise
                    </div>
                    <div class="card-body">
                        @if($activeEntreprise)
                            <form action="{{ route('entreprises.update', $activeEntreprise->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Nom de l'entreprise *</label>
                                        <input type="text" name="nom" class="form-control" value="{{ old('nom', $activeEntreprise->nom) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Téléphone</label>
                                        <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $activeEntreprise->telephone) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Email</label>
                                        <input type="email" name="email" class="form-control" value="{{ old('email', $activeEntreprise->email) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Adresse</label>
                                        <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $activeEntreprise->adresse) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Identifiant Fiscal / NIF</label>
                                        <input type="text" name="identifiant_fiscal" class="form-control" value="{{ old('identifiant_fiscal', $activeEntreprise->identifiant_fiscal) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Devise monétaire</label>
                                        <input type="text" name="devise" class="form-control" value="{{ old('devise', $activeEntreprise->devise ?? 'FCFA') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Plafond d'avance sur salaire par défaut (FCFA)</label>
                                        <input type="number" step="0.01" name="plafond_avance_salaire" class="form-control" value="{{ old('plafond_avance_salaire', $activeEntreprise->plafond_avance_salaire ?? 100000) }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Logo</label>
                                        <input type="file" name="logo" class="form-control" accept="image/*">
                                    </div>
                                </div>
                                <div class="mt-4 text-end">
                                    <button type="submit" class="btn btn-primary px-4">
                                        <i class="fas fa-save me-1"></i> Enregistrer les modifications
                                    </button>
                                </div>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
