@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title mb-1 fw-bold">
                    <i class="fas fa-crown text-warning me-2"></i> Console de Pilotage SaaS Global — GESTOCK / STOKGX
                </h3>
                <p class="text-muted mb-0">
                    Supervision globale des propriétaires, des entreprises clientes et des abonnements SaaS.
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('superadmin.entreprises.index') }}" class="btn btn-primary">
                    <i class="fas fa-building me-1"></i> Gérer les Entreprises
                </a>
                <a href="{{ route('superadmin.proprietaires.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-user-tie me-1"></i> Gérer les Propriétaires
                </a>
                <a href="{{ route('superadmin.abonnements.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-credit-card me-1"></i> Gérer les Abonnements
                </a>
            </div>
        </div>

        {{-- KPI Plateforme SaaS --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <div class="text-muted small fw-semibold">Propriétaires SaaS</div>
                                <div class="fs-3 fw-bold text-primary mt-1">{{ $stats['total_proprietaires'] }}</div>
                                <small class="text-success">{{ $stats['proprietaires_actifs'] }} actif(s)</small>
                            </div>
                            <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary">
                                <i class="fas fa-user-shield fa-lg"></i>
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
                                <div class="text-muted small fw-semibold">Entreprises Clientes</div>
                                <div class="fs-3 fw-bold text-dark mt-1">{{ $stats['total_entreprises'] }}</div>
                                <small class="text-muted">{{ $stats['entreprises_actives'] }} active(s) • {{ $stats['total_utilisateurs'] }} utilisateurs</small>
                            </div>
                            <div class="rounded-circle bg-info bg-opacity-10 p-3 text-info">
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
                                <div class="text-muted small fw-semibold">Abonnements Actifs</div>
                                <div class="fs-3 fw-bold text-success mt-1">{{ $stats['abonnements_actifs'] }}</div>
                                <small class="text-muted">
                                    {{ $stats['abonnements_essai'] }} en essai •
                                    <span class="{{ $stats['abonnements_expires'] > 0 ? 'text-danger fw-semibold' : '' }}">
                                        {{ $stats['abonnements_expires'] }} expiré(s)/suspendu(s)
                                    </span>
                                </small>
                            </div>
                            <div class="rounded-circle bg-success bg-opacity-10 p-3 text-success">
                                <i class="fas fa-gem fa-lg"></i>
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
                                <div class="text-muted small fw-semibold">Revenus Abonnements SaaS</div>
                                <div class="fs-4 fw-bold text-primary mt-1">
                                    {{ number_format($stats['revenu_saas_total'], 0, ',', ' ') }} FCFA
                                </div>
                                <small class="text-muted">
                                    Ce mois : {{ number_format($stats['revenu_saas_mois'], 0, ',', ' ') }} FCFA
                                </small>
                            </div>
                            <div class="rounded-circle bg-warning bg-opacity-10 p-3 text-warning">
                                <i class="fas fa-coins fa-lg"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            {{-- Dernières entreprises & état de leur abonnement --}}
            <div class="col-lg-7">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Entreprises Clientes & État des Abonnements</h5>
                        <a href="{{ route('superadmin.entreprises.index') }}" class="btn btn-sm btn-outline-primary">
                            Tout voir
                        </a>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Entreprise</th>
                                        <th>Propriétaire</th>
                                        <th>Formule</th>
                                        <th>Statut</th>
                                        <th>Expiration</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($dernieresEntreprises as $ent)
                                        @php $sub = $ent->abonnementActuel(); @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $ent->nom }}</strong>
                                                <div class="small text-muted">{{ $ent->code }}</div>
                                            </td>
                                            <td>
                                                {{ $ent->proprietaire?->nom ?? '—' }} {{ $ent->proprietaire?->prenom ?? '' }}
                                                <div class="small text-muted">{{ $ent->proprietaire?->email ?? '' }}</div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $sub?->nom_plan ?? 'Non défini' }}</span>
                                            </td>
                                            <td>
                                                @if($sub)
                                                    <span class="badge {{ $sub->badgeClass() }}">{{ $sub->statutLabel() }}</span>
                                                @else
                                                    <span class="badge bg-secondary">Aucun</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($sub && $sub->date_fin)
                                                    <div>{{ $sub->date_fin->format('d/m/Y') }}</div>
                                                    <small class="{{ $sub->joursRestants() <= 7 ? 'text-danger fw-bold' : 'text-muted' }}">
                                                        {{ $sub->joursRestants() >= 0 ? $sub->joursRestants() . ' j restants' : 'Expiré' }}
                                                    </small>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center text-muted py-4">Aucune entreprise cliente enregistrée.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Journal d'audit global de la plateforme --}}
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-bold">Activité Récente Plateforme</h5>
                        <a href="{{ route('audit-logs.index') }}" class="btn btn-sm btn-outline-secondary">
                            Journal complet
                        </a>
                    </div>
                    <div class="card-body">
                        <ul class="list-group list-group-flush">
                            @forelse($derniersLogs as $log)
                                <li class="list-group-item px-0 py-2">
                                    <div class="d-flex justify-content-between align-items-start">
                                        <div>
                                            <span class="badge bg-light text-dark border me-1">{{ $log->action }}</span>
                                            <span class="small fw-semibold">{{ $log->user?->nom ?? 'Système' }}</span>
                                            <div class="small text-muted mt-1">{{ $log->motif ?? '' }}</div>
                                        </div>
                                        <small class="text-muted text-nowrap ms-2">
                                            {{ $log->created_at?->format('d/m H:i') }}
                                        </small>
                                    </div>
                                </li>
                            @empty
                                <li class="list-group-item text-center text-muted py-4">Aucune activité récente.</li>
                            @endforelse
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

