@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <h3 class="page-title fw-bold mb-0">
                <i class="fas fa-crown text-warning me-2"></i> Administration SaaS — Propriétaires & Entreprises
            </h3>
            <a href="{{ route('superadmin.abonnements.index') }}" class="btn btn-outline-primary">
                <i class="fas fa-credit-card me-1"></i> Gérer les Abonnements
            </a>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-3 mb-4">
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Propriétaires</div>
                    <div class="fs-4 fw-bold">{{ $statsPlateforme['total_proprietaires'] }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Entreprises Totales</div>
                    <div class="fs-4 fw-bold">{{ $statsPlateforme['total_entreprises'] }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Entreprises Actives</div>
                    <div class="fs-4 fw-bold text-success">{{ $statsPlateforme['entreprises_actives'] }}</div>
                </div></div>
            </div>
            <div class="col-md-3">
                <div class="card border-0 shadow-sm"><div class="card-body">
                    <div class="text-muted small">Utilisateurs Totaux</div>
                    <div class="fs-4 fw-bold">{{ $statsPlateforme['total_utilisateurs'] }}</div>
                </div></div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Nouveau Compte Propriétaire & Entreprise</div>
                    <div class="card-body">
                        <form action="{{ route('superadmin.proprietaires.store') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Nom *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Prénom *</label>
                                <input type="text" name="prenom" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Mot de passe *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <hr>
                            <div class="mb-2">
                                <label class="form-label">Nom de la 1ère entreprise *</label>
                                <input type="text" name="entreprise_nom" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Adresse entreprise</label>
                                <input type="text" name="entreprise_adresse" class="form-control">
                            </div>
                            <div class="row g-2 mb-3">
                                <div class="col-7">
                                    <label class="form-label">Formule d'abonnement *</label>
                                    <select name="plan" class="form-select">
                                        @foreach(($plans ?? \App\Models\Abonnement::PLANS) as $pKey => $pInfo)
                                            <option value="{{ $pKey }}" {{ $pKey === 'pro' ? 'selected' : '' }}>
                                                {{ $pInfo['nom'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label">Cycle *</label>
                                    <select name="periodicite" class="form-select">
                                        <option value="mensuel">Mensuel</option>
                                        <option value="annuel" selected>Annuel</option>
                                    </select>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                Créer Propriétaire, Entreprise & Abonnement
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Liste des Propriétaires & Entreprises</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Propriétaire</th>
                                        <th>Email / Tél</th>
                                        <th>Entreprises & Abonnements</th>
                                        <th>Statut</th>
                                        <th class="text-end">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($proprietaires as $prop)
                                        <tr>
                                            <td><strong>{{ $prop->nom }} {{ $prop->prenom }}</strong></td>
                                            <td>
                                                <div>{{ $prop->email }}</div>
                                                <small class="text-muted">{{ $prop->telephone }}</small>
                                            </td>
                                            <td>
                                                @foreach($prop->entreprisesPossedees as $ent)
                                                    @php $sub = $ent->abonnementActuel(); @endphp
                                                    <div class="mb-1">
                                                        <span class="badge bg-info text-dark">{{ $ent->nom }}</span>
                                                        @if($sub)
                                                            <span class="badge {{ $sub->badgeClass() }} ms-1">
                                                                {{ $sub->nom_plan }} ({{ $sub->statutLabel() }})
                                                            </span>
                                                        @endif
                                                    </div>
                                                @endforeach
                                            </td>
                                            <td>
                                                <span class="badge {{ $prop->actif ? 'bg-success' : 'bg-danger' }}">
                                                    {{ $prop->actif ? 'Actif' : 'Désactivé' }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <div class="d-inline-flex gap-1">
                                                    @if($prop->entreprisesPossedees->isEmpty())
                                                        <button type="button"
                                                                class="btn btn-sm btn-primary"
                                                                data-bs-toggle="modal"
                                                                data-bs-target="#modalAddEnt{{ $prop->id }}">
                                                            <i class="fas fa-plus me-1"></i> Rattacher Entreprise
                                                        </button>
                                                    @endif

                                                    <form action="{{ route('superadmin.proprietaires.toggle', $prop->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm {{ $prop->actif ? 'btn-outline-danger' : 'btn-outline-success' }}">
                                                            {{ $prop->actif ? 'Désactiver' : 'Activer' }}
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modales de rattachement d'entreprise (hors tableau) --}}
@foreach($proprietaires as $prop)
    @if($prop->entreprisesPossedees->isEmpty())
        <div class="modal fade text-start" id="modalAddEnt{{ $prop->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <form action="{{ route('superadmin.proprietaires.store-entreprise', $prop->id) }}" method="POST">
                        @csrf
                        <div class="modal-header border-bottom py-3 px-4">
                            <h5 class="modal-title fw-bold text-dark mb-0">
                                Nouvelle Entreprise pour {{ $prop->nom }} {{ $prop->prenom }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                        </div>
                        <div class="modal-body p-4">
                            <div class="mb-2">
                                <label class="form-label">Nom de l'entreprise *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control">
                            </div>
                            <div class="row g-2 mt-2">
                                <div class="col-7">
                                    <label class="form-label">Formule d'abonnement *</label>
                                    <select name="plan" class="form-select">
                                        @foreach(($plans ?? \App\Models\Abonnement::PLANS) as $pKey => $pInfo)
                                            <option value="{{ $pKey }}" {{ $pKey === 'pro' ? 'selected' : '' }}>
                                                {{ $pInfo['nom'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <label class="form-label">Cycle *</label>
                                    <select name="periodicite" class="form-select">
                                        <option value="mensuel">Mensuel</option>
                                        <option value="annuel" selected>Annuel</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer border-top py-3 px-4">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                            <button type="submit" class="btn btn-primary">Créer l'entreprise & l'abonnement</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
