@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">
                    <i class="fas fa-building text-primary me-2"></i> Gestion des Entreprises Clientes
                </h3>
                <p class="text-muted mb-0">
                    Administration SaaS : création, modification, suivi des abonnements et suspension des entreprises hébergées.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateEntreprise">
                    <i class="fas fa-plus-circle me-1"></i> Nouvelle Entreprise
                </button>
                <a href="{{ route('superadmin.abonnements.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-credit-card me-1"></i> Abonnements
                </a>
                <a href="{{ route('superadmin.proprietaires.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-user-tie me-1"></i> Propriétaires
                </a>
            </div>
        </div>

        @if(isset($errors) && $errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong><i class="fas fa-exclamation-triangle me-1"></i> Erreur lors du traitement :</strong>
                <ul class="mb-0 mt-1">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- KPI Cards --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Total Entreprises</div>
                                <div class="fs-3 fw-bold text-primary mt-1">{{ $stats['total_entreprises'] }}</div>
                                <small class="text-muted">Hébergées sur STOKGX</small>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                <i class="fas fa-building fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Entreprises Actives</div>
                                <div class="fs-3 fw-bold text-success mt-1">{{ $stats['entreprises_actives'] }}</div>
                                <small class="text-success">Accès opérationnel autorisé</small>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                <i class="fas fa-check-circle fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Inactives / Suspendues</div>
                                <div class="fs-3 fw-bold {{ $stats['entreprises_inactives'] > 0 ? 'text-danger' : 'text-secondary' }} mt-1">
                                    {{ $stats['entreprises_inactives'] }}
                                </div>
                                <small class="text-muted">Accès restreint ou suspendu</small>
                            </div>
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                                <i class="fas fa-ban fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Utilisateurs Entreprises</div>
                                <div class="fs-3 fw-bold text-info mt-1">{{ $stats['total_utilisateurs'] }}</div>
                                <small class="text-muted">Employés & gestionnaires</small>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                                <i class="fas fa-users fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Filtres & Recherche --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form method="GET" action="{{ route('superadmin.entreprises.index') }}" class="row g-2 align-items-center">
                    <div class="col-md-6 col-lg-5">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text"
                                   name="search"
                                   class="form-control border-start-0"
                                   placeholder="Rechercher par nom, code, email, téléphone ou propriétaire..."
                                   value="{{ $search ?? '' }}">
                        </div>
                    </div>
                    <div class="col-md-3 col-lg-3">
                        <select name="statut" class="form-select">
                            <option value="">Tous les statuts</option>
                            <option value="actif" {{ ($statut ?? '') === 'actif' ? 'selected' : '' }}>Actives uniquement</option>
                            <option value="inactif" {{ ($statut ?? '') === 'inactif' ? 'selected' : '' }}>Inactives uniquement</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-3">
                            <i class="fas fa-filter me-1"></i> Filtrer
                        </button>
                        @if(!empty($search) || !empty($statut))
                            <a href="{{ route('superadmin.entreprises.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-undo me-1"></i> Réinitialiser
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Tableau des Entreprises --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">
                    <i class="fas fa-list text-primary me-2"></i> Répertoire des Entreprises ({{ $entreprises->total() }})
                </h5>
                <span class="text-muted small">Page {{ $entreprises->currentPage() }} sur {{ $entreprises->lastPage() }}</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 250px;">Entreprise & Code</th>
                                <th>Propriétaire</th>
                                <th>Contact</th>
                                <th>Formule & Abonnement</th>
                                <th class="text-center">Données</th>
                                <th class="text-center">Statut</th>
                                <th class="text-end" style="width: 180px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entreprises as $ent)
                                @php
                                    $sub = $ent->abonnementActuel();
                                    $prop = $ent->proprietaire;
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            @if($ent->logo)
                                                <img src="{{ asset($ent->logo) }}"
                                                     alt="Logo"
                                                     class="rounded border p-1"
                                                     style="width: 44px; height: 44px; object-fit: contain;">
                                            @else
                                                <div class="rounded bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold fs-5"
                                                     style="width: 44px; height: 44px;">
                                                    {{ strtoupper(substr($ent->nom, 0, 1)) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-bold text-dark">{{ $ent->nom }}</div>
                                                <span class="badge bg-secondary font-monospace" style="font-size: 11px;">
                                                    {{ $ent->code }}
                                                </span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($prop)
                                            <div class="fw-semibold text-dark">{{ $prop->nom }} {{ $prop->prenom }}</div>
                                            <small class="text-muted d-block">{{ $prop->email }}</small>
                                        @else
                                            <span class="badge bg-light text-muted">Non assigné</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small">
                                            @if($ent->telephone)
                                                <div><i class="fas fa-phone fa-xs text-muted me-1"></i> {{ $ent->telephone }}</div>
                                            @endif
                                            @if($ent->email)
                                                <div><i class="fas fa-envelope fa-xs text-muted me-1"></i> {{ $ent->email }}</div>
                                            @endif
                                            @if(!$ent->telephone && !$ent->email)
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        @if($sub)
                                            <div>
                                                <span class="badge {{ $sub->badgeClass() }} fw-semibold">
                                                    {{ $sub->nom_plan }}
                                                </span>
                                                <span class="badge bg-light text-dark border ms-1" style="font-size: 11px;">
                                                    {{ $sub->statutLabel() }}
                                                </span>
                                            </div>
                                            <small class="text-muted d-block mt-1" style="font-size: 11px;">
                                                Expire le : {{ \Carbon\Carbon::parse($sub->date_fin)->format('d/m/Y') }}
                                            </small>
                                        @else
                                            <span class="badge bg-warning text-dark">Sans abonnement</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border" title="Utilisateurs">
                                            <i class="fas fa-users text-primary me-1"></i> {{ $ent->utilisateurs_count ?? 0 }}
                                        </span>
                                        <span class="badge bg-light text-dark border ms-1" title="Produits">
                                            <i class="fas fa-box text-success me-1"></i> {{ $ent->produits_count ?? 0 }}
                                        </span>
                                        <span class="badge bg-light text-dark border ms-1" title="Ventes">
                                            <i class="fas fa-shopping-cart text-info me-1"></i> {{ $ent->ventes_count ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($ent->actif)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                                <i class="fas fa-check-circle me-1"></i> Active
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                                <i class="fas fa-ban me-1"></i> Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            {{-- Détails --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalShowEntreprise{{ $ent->id }}"
                                                    title="Voir les détails">
                                                <i class="fas fa-eye"></i>
                                            </button>

                                            {{-- Modifier --}}
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalEditEntreprise{{ $ent->id }}"
                                                    title="Modifier l'entreprise">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            {{-- Activer / Désactiver --}}
                                            <form action="{{ route('superadmin.entreprises.toggle', $ent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit"
                                                        class="btn btn-sm {{ $ent->actif ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                                        title="{{ $ent->actif ? 'Suspendre / Désactiver' : 'Activer l\'entreprise' }}"
                                                        onclick="return confirm('Êtes-vous sûr de vouloir {{ $ent->actif ? 'désactiver' : 'activer' }} cette entreprise ?');">
                                                    <i class="fas {{ $ent->actif ? 'fa-pause' : 'fa-play' }}"></i>
                                                </button>
                                            </form>

                                            {{-- Supprimer --}}
                                            <form action="{{ route('superadmin.entreprises.destroy', $ent->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger"
                                                        title="Supprimer l'entreprise"
                                                        onclick="return confirm('Confirmez-vous la suppression de cette entreprise ? Cette opération est irréversible.');">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">
                                        <i class="fas fa-building fa-3x mb-3 text-secondary opacity-50"></i>
                                        <div class="fw-semibold fs-5">Aucune entreprise trouvée</div>
                                        <p class="text-muted mb-3">Créez votre première entreprise cliente ou modifiez vos critères de recherche.</p>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCreateEntreprise">
                                            <i class="fas fa-plus-circle me-1"></i> Créer une Entreprise
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($entreprises->hasPages())
                <div class="card-footer bg-white py-3">
                    <div class="d-flex justify-content-end">
                        {{ $entreprises->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ==============================================================
     MODALES DES ENTREPRISES (DÉTAILS & ÉDITION)
     Placées à la racine du document (HORS du tableau/card)
     pour éviter tout conflit de z-index / superposition de backdrop
     ============================================================== --}}
@foreach($entreprises as $ent)
    @php
        $sub = $ent->abonnementActuel();
        $prop = $ent->proprietaire;
    @endphp

    {{-- MODAL SHOW / DETAILS --}}
    <div class="modal fade" id="modalShowEntreprise{{ $ent->id }}" tabindex="-1" aria-labelledby="labelShow{{ $ent->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-bottom py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <i class="fas fa-building fa-lg"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="labelShow{{ $ent->id }}">{{ $ent->nom }}</h5>
                            <span class="text-muted small">Code : <strong class="badge bg-secondary font-monospace">{{ $ent->code }}</strong></span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        @if($ent->actif)
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <i class="fas fa-check-circle me-1"></i> Active
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <i class="fas fa-ban me-1"></i> Inactive
                            </span>
                        @endif
                        <button type="button" class="btn-close ms-2" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                </div>

                <div class="modal-body p-4">
                    {{-- Bandeau Résumé Métriques --}}
                    <div class="row g-2 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="p-2 text-center rounded border bg-light">
                                <div class="text-muted small">Propriétaire</div>
                                <div class="fw-bold text-dark text-truncate mt-1">
                                    {{ $prop ? $prop->nom . ' ' . $prop->prenom : 'Non assigné' }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 text-center rounded border bg-light">
                                <div class="text-muted small">Utilisateurs</div>
                                <div class="fw-bold text-primary mt-1">
                                    <i class="fas fa-users me-1"></i> {{ $ent->utilisateurs_count ?? 0 }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 text-center rounded border bg-light">
                                <div class="text-muted small">Produits</div>
                                <div class="fw-bold text-success mt-1">
                                    <i class="fas fa-box me-1"></i> {{ $ent->produits_count ?? 0 }}
                                </div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="p-2 text-center rounded border bg-light">
                                <div class="text-muted small">Ventes</div>
                                <div class="fw-bold text-info mt-1">
                                    <i class="fas fa-shopping-cart me-1"></i> {{ $ent->ventes_count ?? 0 }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row g-4">
                        {{-- Colonne Gauche : Identité & Logo --}}
                        <div class="col-md-4 text-center border-end">
                            <div class="mb-3">
                                @if($ent->logo)
                                    <img src="{{ asset($ent->logo) }}" alt="Logo" class="rounded border p-2 shadow-sm" style="max-width: 130px; max-height: 130px; object-fit: contain;">
                                @else
                                    <div class="rounded-4 bg-primary text-white d-flex align-items-center justify-content-center fw-bold fs-1 mx-auto shadow-sm" style="width: 100px; height: 100px;">
                                        {{ strtoupper(substr($ent->nom, 0, 1)) }}
                                    </div>
                                @endif
                            </div>
                            <h5 class="fw-bold text-dark mb-1">{{ $ent->nom }}</h5>
                            <div class="badge bg-secondary font-monospace mb-2">{{ $ent->code }}</div>
                            <div class="small text-muted">
                                Créée le {{ $ent->created_at ? $ent->created_at->format('d/m/Y') : '—' }}
                            </div>
                        </div>

                        {{-- Colonne Droite : Coordonnées & Paramètres --}}
                        <div class="col-md-8">
                            <h6 class="fw-bold text-primary mb-3">
                                <i class="fas fa-info-circle me-1"></i> Coordonnées & Paramètres
                            </h6>
                            <div class="row g-2 mb-4">
                                <div class="col-sm-6">
                                    <div class="p-2 rounded border bg-light">
                                        <small class="text-muted d-block">Email de contact</small>
                                        <span class="fw-semibold text-dark">{{ $ent->email ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="p-2 rounded border bg-light">
                                        <small class="text-muted d-block">Téléphone</small>
                                        <span class="fw-semibold text-dark">{{ $ent->telephone ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-2 rounded border bg-light">
                                        <small class="text-muted d-block">Adresse physique</small>
                                        <span class="fw-semibold text-dark">{{ $ent->adresse ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 rounded border bg-light">
                                        <small class="text-muted d-block">Identifiant Fiscal (NIF)</small>
                                        <span class="fw-semibold text-dark">{{ $ent->identifiant_fiscal ?? '—' }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 rounded border bg-light">
                                        <small class="text-muted d-block">Devise</small>
                                        <span class="fw-bold text-primary">{{ $ent->devise ?? 'FCFA' }}</span>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-2 rounded border bg-light">
                                        <small class="text-muted d-block">Plafond avance</small>
                                        <span class="fw-bold text-success">{{ number_format($ent->plafond_avance_salaire, 0, ',', ' ') }} {{ $ent->devise ?? 'FCFA' }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Section Abonnement Actuel --}}
                            <h6 class="fw-bold text-primary mb-2">
                                <i class="fas fa-gem me-1"></i> Abonnement SaaS
                            </h6>
                            @if($sub)
                                <div class="p-3 rounded border bg-light">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fw-bold fs-6 text-dark">{{ $sub->nom_plan }}</span>
                                            <span class="badge {{ $sub->badgeClass() }}">{{ $sub->statutLabel() }}</span>
                                        </div>
                                        <span class="fw-bold text-primary">
                                            {{ number_format($sub->montant, 0, ',', ' ') }} {{ $sub->devise }} / {{ $sub->periodicite }}
                                        </span>
                                    </div>
                                    <div class="small text-muted">
                                        <div class="mb-1">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Validité : <strong>{{ \Carbon\Carbon::parse($sub->date_debut)->format('d/m/Y') }}</strong> au <strong>{{ \Carbon\Carbon::parse($sub->date_fin)->format('d/m/Y') }}</strong>
                                        </div>
                                        <div>
                                            <i class="fas fa-layer-group me-1"></i>
                                            Quotas : Max {{ $sub->max_utilisateurs ?? 'Illimité' }} utilisateurs • Max {{ $sub->max_produits ?? 'Illimité' }} produits
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 mb-0">Aucun abonnement actif pour le moment.</div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fermer</button>
                    <button type="button" class="btn btn-primary" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#modalEditEntreprise{{ $ent->id }}">
                        <i class="fas fa-edit me-1"></i> Modifier cette entreprise
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal fade" id="modalEditEntreprise{{ $ent->id }}" tabindex="-1" aria-labelledby="labelEdit{{ $ent->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('superadmin.entreprises.update', $ent->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="modal-header border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-edit fa-lg"></i>
                            </div>
                            <h5 class="modal-title fw-bold mb-0 text-dark" id="labelEdit{{ $ent->id }}">Modifier l'entreprise : {{ $ent->nom }}</h5>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label fw-semibold">Nom de l'entreprise *</label>
                                <input type="text" name="nom" class="form-control" value="{{ old('nom', $ent->nom) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Code unique *</label>
                                <input type="text" name="code" class="form-control text-uppercase" value="{{ old('code', $ent->code) }}" required>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Propriétaire assigné</label>
                                <select name="proprietaire_id" class="form-select">
                                    <option value="">Sélectionner un propriétaire</option>
                                    @foreach($proprietairesDisponibles as $p)
                                        <option value="{{ $p->id }}" {{ (int) old('proprietaire_id', $ent->proprietaire_id) === (int) $p->id ? 'selected' : '' }}>
                                            {{ $p->nom }} {{ $p->prenom }} ({{ $p->email }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Statut opérationnel</label>
                                <select name="actif" class="form-select">
                                    <option value="1" {{ old('actif', $ent->actif) ? 'selected' : '' }}>Active (Autorisée)</option>
                                    <option value="0" {{ !old('actif', $ent->actif) ? 'selected' : '' }}>Inactive / Suspendue</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control" value="{{ old('telephone', $ent->telephone) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Email de contact</label>
                                <input type="email" name="email" class="form-control" value="{{ old('email', $ent->email) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Adresse physique</label>
                                <input type="text" name="adresse" class="form-control" value="{{ old('adresse', $ent->adresse) }}">
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Identifiant Fiscal / NIF</label>
                                <input type="text" name="identifiant_fiscal" class="form-control" value="{{ old('identifiant_fiscal', $ent->identifiant_fiscal) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Devise monétaire</label>
                                <input type="text" name="devise" class="form-control" value="{{ old('devise', $ent->devise ?? 'FCFA') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Plafond avance salaire</label>
                                <input type="number" step="0.01" name="plafond_avance_salaire" class="form-control" value="{{ old('plafond_avance_salaire', $ent->plafond_avance_salaire) }}">
                            </div>

                            <div class="col-md-12">
                                <label class="form-label">Changer le logo</label>
                                <input type="file" name="logo" class="form-control" accept="image/*">
                                <small class="text-muted">Format PNG, JPG ou WEBP (max 2 Mo).</small>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Enregistrer les modifications
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach

{{-- ==============================================================
     MODAL CREATE ENTREPRISE (AVEC CHOIX PROPRIÉTAIRE & ABONNEMENT)
     ============================================================== --}}
<div class="modal fade" id="modalCreateEntreprise" tabindex="-1" aria-labelledby="labelCreateEnt" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('superadmin.entreprises.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header bg-primary text-white py-3 px-4">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fas fa-plus-circle fa-lg"></i>
                        <h5 class="modal-title fw-bold text-white mb-0" id="labelCreateEnt">Créer une Nouvelle Entreprise Cliente</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fermer"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- 1. Choix du Propriétaire --}}
                    <div class="p-3 mb-4 rounded border bg-light">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-user-shield me-1"></i> 1. Rattachement du Propriétaire
                        </h6>
                        <div class="d-flex gap-4 mb-3">
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="owner_mode"
                                       id="owner_mode_new"
                                       value="new"
                                       checked
                                       onchange="document.getElementById('section_new_owner').style.display='block'; document.getElementById('section_existing_owner').style.display='none';">
                                <label class="form-check-label fw-semibold" for="owner_mode_new">
                                    Créer un nouveau compte Propriétaire
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input"
                                       type="radio"
                                       name="owner_mode"
                                       id="owner_mode_existing"
                                       value="existing"
                                       onchange="document.getElementById('section_new_owner').style.display='none'; document.getElementById('section_existing_owner').style.display='block';">
                                <label class="form-check-label fw-semibold" for="owner_mode_existing">
                                    Assigner un Propriétaire existant
                                </label>
                            </div>
                        </div>

                        {{-- Section Nouveau Propriétaire --}}
                        <div id="section_new_owner">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Nom du Propriétaire *</label>
                                    <input type="text" name="owner_nom" class="form-control form-control-sm" placeholder="Ex: Dupont">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Prénom</label>
                                    <input type="text" name="owner_prenom" class="form-control form-control-sm" placeholder="Ex: Jean">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Email de connexion *</label>
                                    <input type="email" name="owner_email" class="form-control form-control-sm" placeholder="jean.dupont@entreprise.com">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label small fw-semibold">Téléphone</label>
                                    <input type="text" name="owner_telephone" class="form-control form-control-sm" placeholder="+225 0102030405">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label small fw-semibold">Mot de passe initial *</label>
                                    <input type="password" name="owner_password" class="form-control form-control-sm" placeholder="Minimum 6 caractères">
                                </div>
                            </div>
                        </div>

                        {{-- Section Propriétaire Existant --}}
                        <div id="section_existing_owner" style="display: none;">
                            <label class="form-label small fw-semibold">Sélectionner le Propriétaire existant *</label>
                            <select name="proprietaire_id" class="form-select form-select-sm">
                                <option value="">-- Choisir un Propriétaire --</option>
                                @foreach($proprietairesDisponibles as $p)
                                    <option value="{{ $p->id }}">
                                        {{ $p->nom }} {{ $p->prenom }} ({{ $p->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- 2. Informations de l'Entreprise --}}
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="fas fa-building me-1"></i> 2. Informations de l'Entreprise
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Nom commercial de l'entreprise *</label>
                            <input type="text" name="nom" class="form-control" placeholder="Ex: ETS Distribution Pro" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Code entreprise</label>
                            <input type="text" name="code" class="form-control text-uppercase" placeholder="Auto si vide (ENT-XXXXXX)">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Téléphone</label>
                            <input type="text" name="telephone" class="form-control" placeholder="+225 ...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email professionnel</label>
                            <input type="email" name="email" class="form-control" placeholder="contact@entreprise.com">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Adresse</label>
                            <input type="text" name="adresse" class="form-control" placeholder="Abidjan, Cocody...">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Identifiant Fiscal / NIF</label>
                            <input type="text" name="identifiant_fiscal" class="form-control" placeholder="NIF-12345678">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Devise</label>
                            <input type="text" name="devise" class="form-control" value="FCFA">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Plafond avance salaire</label>
                            <input type="number" step="0.01" name="plafond_avance_salaire" class="form-control" value="100000">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Logo de l'entreprise</label>
                            <input type="file" name="logo" class="form-control" accept="image/*">
                        </div>
                    </div>

                    {{-- 3. Abonnement initial --}}
                    <div class="p-3 rounded border bg-light">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="fas fa-gem me-1"></i> 3. Formule d'Abonnement SaaS Initiale
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-5">
                                <label class="form-label small fw-semibold">Plan d'abonnement *</label>
                                <select name="plan" class="form-select form-select-sm">
                                    @foreach(($plans ?? \App\Models\Abonnement::PLANS) as $pKey => $pInfo)
                                        <option value="{{ $pKey }}" {{ $pKey === 'pro' ? 'selected' : '' }}>
                                            {{ $pInfo['nom'] }} ({{ number_format($pInfo['prix_mensuel'], 0, ',', ' ') }} FCFA/mois)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Périodicité *</label>
                                <select name="periodicite" class="form-select form-select-sm">
                                    <option value="mensuel">Facturation Mensuelle</option>
                                    <option value="annuel" selected>Facturation Annuelle</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold">Durée (mois)</label>
                                <input type="number" name="duree_mois" class="form-control form-control-sm" value="12" min="1" max="60">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-3 px-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-check-circle me-1"></i> Créer l'entreprise & activer l'accès
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
