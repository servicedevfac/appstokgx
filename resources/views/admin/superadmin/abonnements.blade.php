@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">
                    <i class="fas fa-credit-card text-primary me-2"></i> Gestion des Abonnements SaaS
                </h3>
                <p class="text-muted mb-0">
                    Activez, prolongez ou suspendez les abonnements de chaque entreprise cliente.
                </p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('superadmin.entreprises.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-building me-1"></i> Entreprises
                </a>
                <a href="{{ route('superadmin.proprietaires.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-user-tie me-1"></i> Propriétaires
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

        {{-- Cartes récapitulatives --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Abonnements Actifs</div>
                                <div class="fs-3 fw-bold text-success mt-1">{{ $statsAbonnements['actifs'] ?? 0 }}</div>
                                <small class="text-muted">Entreprises opérationnelles</small>
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
                                <div class="text-muted small fw-semibold">Périodes d'Essai</div>
                                <div class="fs-3 fw-bold text-info mt-1">{{ $statsAbonnements['essai'] ?? 0 }}</div>
                                <small class="text-muted">Essais 14 jours</small>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
                                <i class="fas fa-stopwatch fa-lg"></i>
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
                                <div class="text-muted small fw-semibold">Abonnements Expirés</div>
                                <div class="fs-3 fw-bold text-danger mt-1">{{ $statsAbonnements['expires'] ?? 0 }}</div>
                                <small class="text-muted">Accès restreint</small>
                            </div>
                            <div class="rounded-circle bg-danger bg-opacity-10 p-3 text-danger">
                                <i class="fas fa-exclamation-circle fa-lg"></i>
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
                                <div class="text-muted small fw-semibold">Revenus SaaS Encaissés</div>
                                <div class="fs-4 fw-bold text-primary mt-1">{{ number_format($statsAbonnements['revenu_total'] ?? 0, 0, ',', ' ') }} FCFA</div>
                                <small class="text-muted">Facturations abonnements</small>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                <i class="fas fa-coins fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tableau des entreprises et abonnements --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold">Liste des Entreprises & État de leur Abonnement</h5>
                <div class="d-flex gap-2">
                    <a href="{{ route('superadmin.abonnements.index') }}" class="btn btn-sm btn-outline-secondary {{ !request('statut') ? 'active' : '' }}">Tous</a>
                    <a href="{{ route('superadmin.abonnements.index', ['statut' => 'actif']) }}" class="btn btn-sm btn-outline-success {{ request('statut') === 'actif' ? 'active' : '' }}">Actifs</a>
                    <a href="{{ route('superadmin.abonnements.index', ['statut' => 'essai']) }}" class="btn btn-sm btn-outline-info {{ request('statut') === 'essai' ? 'active' : '' }}">Essai</a>
                    <a href="{{ route('superadmin.abonnements.index', ['statut' => 'expire']) }}" class="btn btn-sm btn-outline-danger {{ request('statut') === 'expire' ? 'active' : '' }}">Expirés / Suspendus</a>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Entreprise</th>
                                <th>Propriétaire</th>
                                <th>Plan Actuel</th>
                                <th>Période & Échéance</th>
                                <th>Montant</th>
                                <th>Limites</th>
                                <th>Statut</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($entreprises as $ent)
                                @php
                                    $sub = $ent->abonnementActuel();
                                @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $ent->nom }}</strong>
                                        <div class="small text-muted font-monospace">{{ $ent->code }}</div>
                                    </td>
                                    <td>
                                        {{ $ent->proprietaire->nom ?? '—' }} {{ $ent->proprietaire->prenom ?? '' }}
                                        <div class="small text-muted">{{ $ent->proprietaire->email ?? '' }}</div>
                                    </td>
                                    <td>
                                        @if($sub)
                                            <span class="badge {{ $sub->badgeClass() }} fs-6">{{ $sub->nom_plan }}</span>
                                            <small class="d-block text-muted">{{ ucfirst($sub->periodicite) }}</small>
                                        @else
                                            <span class="badge bg-secondary">Aucun</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($sub)
                                            <small class="d-block">Du : {{ \Carbon\Carbon::parse($sub->date_debut)->format('d/m/Y') }}</small>
                                            <strong class="{{ $sub->isExpired() ? 'text-danger' : 'text-dark' }}">
                                                Au : {{ \Carbon\Carbon::parse($sub->date_fin)->format('d/m/Y') }}
                                            </strong>
                                            @if($sub->isExpired())
                                                <span class="badge bg-danger ms-1">Expiré</span>
                                            @endif
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($sub)
                                            <strong>{{ number_format($sub->montant, 0, ',', ' ') }}</strong> {{ $sub->devise }}
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($sub)
                                            <small class="d-block">👥 {{ $sub->max_utilisateurs ?? 'Illimité' }} users</small>
                                            <small class="d-block">📦 {{ $sub->max_produits ?? 'Illimité' }} produits</small>
                                        @else
                                            —
                                        @endif
                                    </td>
                                    <td>
                                        @if($sub)
                                            <span class="badge {{ $sub->badgeClass() }}">
                                                {{ $sub->statutLabel() }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Inactif</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button"
                                                    class="btn btn-sm btn-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalAbonnement{{ $ent->id }}">
                                                <i class="fas fa-sync-alt me-1"></i> Gérer / Renouveler
                                            </button>

                                            @if($sub)
                                                <form action="{{ route('superadmin.abonnements.toggle', $sub->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $sub->statut === 'suspendu' ? 'btn-success' : 'btn-outline-danger' }}">
                                                        {{ $sub->statut === 'suspendu' ? 'Réactiver' : 'Suspendre' }}
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-4">Aucune entreprise trouvée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ==============================================================
     MODALES DE RENOUVELLEMENT D'ABONNEMENT
     Placées hors du tableau/card pour éviter tout conflit de z-index / backdrop
     ============================================================== --}}
@foreach($entreprises as $ent)
    @php
        $sub = $ent->abonnementActuel();
    @endphp
    <div class="modal fade" id="modalAbonnement{{ $ent->id }}" tabindex="-1" aria-labelledby="labelAbo{{ $ent->id }}" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('superadmin.abonnements.store', $ent->id) }}" method="POST">
                    @csrf
                    <div class="modal-header border-bottom py-3 px-4">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded p-2 bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                <i class="fas fa-credit-card fa-lg"></i>
                            </div>
                            <div>
                                <h5 class="modal-title fw-bold mb-0 text-dark" id="labelAbo{{ $ent->id }}">
                                    Abonnement — {{ $ent->nom }}
                                </h5>
                                <span class="text-muted small">Code : <strong class="badge bg-secondary font-monospace">{{ $ent->code }}</strong></span>
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fermer"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Formule d'abonnement *</label>
                                <select name="plan" class="form-select" required>
                                    @foreach($plans as $pKey => $pData)
                                        <option value="{{ $pKey }}" {{ ($sub?->plan ?? 'pro') === $pKey ? 'selected' : '' }}>
                                            {{ $pData['nom'] }} ({{ number_format($pData['prix_mensuel'], 0, ',', ' ') }} FCFA/mois)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Périodicité *</label>
                                <select name="periodicite" class="form-select" required>
                                    <option value="mensuel" {{ ($sub?->periodicite ?? 'mensuel') === 'mensuel' ? 'selected' : '' }}>Mensuel</option>
                                    <option value="annuel" {{ ($sub?->periodicite ?? '') === 'annuel' ? 'selected' : '' }}>Annuel</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Statut *</label>
                                <select name="statut" class="form-select" required>
                                    <option value="actif" {{ ($sub?->statut ?? 'actif') === 'actif' ? 'selected' : '' }}>Actif</option>
                                    <option value="essai" {{ ($sub?->statut ?? '') === 'essai' ? 'selected' : '' }}>Période d'essai</option>
                                    <option value="suspendu" {{ ($sub?->statut ?? '') === 'suspendu' ? 'selected' : '' }}>Suspendu</option>
                                    <option value="expire" {{ ($sub?->statut ?? '') === 'expire' ? 'selected' : '' }}>Expiré</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date de début *</label>
                                <input type="date" name="date_debut" class="form-control" value="{{ now()->toDateString() }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Date d'échéance (Fin) *</label>
                                <input type="date" name="date_fin" class="form-control" value="{{ now()->addMonth()->toDateString() }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Montant facturé (FCFA)</label>
                                <input type="number" name="montant" class="form-control" value="{{ $sub?->montant ?? 35000 }}" min="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quota max utilisateurs (vide = illimité)</label>
                                <input type="number" name="max_utilisateurs" class="form-control" value="{{ $sub?->max_utilisateurs }}" min="1" placeholder="Illimité">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quota max produits (vide = illimité)</label>
                                <input type="number" name="max_produits" class="form-control" value="{{ $sub?->max_produits }}" min="1" placeholder="Illimité">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Mode de paiement</label>
                                <input type="text" name="mode_paiement" class="form-control" placeholder="Virement, Mobile Money, Espèces..." value="{{ $sub?->mode_paiement }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Référence de transaction</label>
                                <input type="text" name="reference_paiement" class="form-control" placeholder="Ex: TXN-2026-001">
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold">Notes</label>
                                <textarea name="notes" class="form-control" rows="2">{{ $sub?->notes }}</textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top py-3 px-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="fas fa-save me-1"></i> Enregistrer l'abonnement
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endforeach
@endsection
