@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title mb-1 fw-bold">
                    <i class="fas fa-gem text-primary me-2"></i> Mon Abonnement SaaS — {{ $entreprise?->nom ?? 'Entreprise' }}
                </h3>
                <p class="text-muted mb-0">
                    Consultez la formule actuelle, la date d'échéance et la consommation des quotas de votre entreprise.
                </p>
            </div>
            @if(Auth::user()->isProprietaire())
                <div>
                    <a href="{{ route('entreprises.index') }}" class="btn btn-outline-primary">
                        <i class="fas fa-building me-1"></i> Mes Entreprises
                    </a>
                </div>
            @endif
        </div>

        @if(!$entreprise)
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle me-2"></i>
                Votre compte n'est rattaché à aucune entreprise active. Veuillez contacter le propriétaire de votre entreprise ou l'administrateur de la plateforme SaaS.
            </div>
        @else
            {{-- Carte principale de l'abonnement actif --}}
            <div class="row g-4 mb-4">
                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                                <div>
                                    <span class="text-muted small text-uppercase fw-semibold">Formule souscrite</span>
                                    <h4 class="fw-bold text-primary mb-1">{{ $abonnement?->nom_plan ?? 'Pack Business Pro' }}</h4>
                                    <span class="badge bg-light text-dark border">Entreprise : {{ $entreprise->nom }} ({{ $entreprise->code }})</span>
                                </div>
                                @if($abonnement)
                                    <span class="badge {{ $abonnement->badgeClass() }} fs-6 px-3 py-2">
                                        {{ $abonnement->statutLabel() }}
                                    </span>
                                @endif
                            </div>

                            @if($abonnement && !$abonnement->isActive())
                                <div class="alert alert-danger d-flex align-items-center gap-2">
                                    <i class="fas fa-lock"></i>
                                    <div>
                                        <strong>Abonnement {{ strtolower($abonnement->statutLabel()) }} :</strong>
                                        Veuillez contacter le service client GESTOCK / STOKGX pour renouveler votre abonnement et réactiver l'ensemble de vos opérations.
                                    </div>
                                </div>
                            @endif

                            <div class="row g-3 mt-1">
                                <div class="col-sm-4">
                                    <div class="p-3 rounded bg-light">
                                        <div class="text-muted small">Tarif & Cycle</div>
                                        <div class="fw-bold fs-5 mt-1">
                                            {{ number_format($abonnement?->montant ?? 0, 0, ',', ' ') }} {{ $abonnement?->devise ?? 'FCFA' }}
                                        </div>
                                        <small class="text-muted text-capitalize">{{ $abonnement?->periodicite ?? 'mensuel' }}</small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-3 rounded bg-light">
                                        <div class="text-muted small">Date de début</div>
                                        <div class="fw-bold fs-5 mt-1">
                                            {{ $abonnement?->date_debut ? $abonnement->date_debut->format('d/m/Y') : '—' }}
                                        </div>
                                        <small class="text-muted">Activation</small>
                                    </div>
                                </div>
                                <div class="col-sm-4">
                                    <div class="p-3 rounded bg-light">
                                        <div class="text-muted small">Date d'expiration</div>
                                        <div class="fw-bold fs-5 mt-1 {{ ($abonnement && $abonnement->joursRestants() <= 7) ? 'text-danger' : 'text-dark' }}">
                                            {{ $abonnement?->date_fin ? $abonnement->date_fin->format('d/m/Y') : '—' }}
                                        </div>
                                        @if($abonnement)
                                            <small class="{{ $abonnement->joursRestants() <= 7 ? 'text-danger fw-bold' : 'text-success fw-semibold' }}">
                                                {{ $abonnement->joursRestants() >= 0 ? $abonnement->joursRestants() . ' jour(s) restants' : 'Expiré' }}
                                            </small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Quotas et utilisation --}}
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3"><i class="fas fa-tachometer-alt text-primary me-2"></i> Utilisation des Quotas</h5>

                            @php
                                $maxUsers = $usage['max_utilisateurs'];
                                $pctUsers = ($maxUsers && $maxUsers > 0) ? min(100, round(($usage['utilisateurs_count'] / $maxUsers) * 100)) : 25;
                                $maxProds = $usage['max_produits'];
                                $pctProds = ($maxProds && $maxProds > 0) ? min(100, round(($usage['produits_count'] / $maxProds) * 100)) : 20;
                            @endphp

                            <div class="mb-4">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold">Utilisateurs rattachés à l'entreprise</span>
                                    <span class="fw-bold">
                                        {{ $usage['utilisateurs_count'] }} / {{ $maxUsers ?? 'Illimité' }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar {{ $pctUsers >= 90 ? 'bg-danger' : 'bg-primary' }}"
                                         role="progressbar"
                                         style="width: {{ $pctUsers }}%"></div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="fw-semibold">Produits au catalogue</span>
                                    <span class="fw-bold">
                                        {{ $usage['produits_count'] }} / {{ $maxProds ?? 'Illimité' }}
                                    </span>
                                </div>
                                <div class="progress" style="height: 10px;">
                                    <div class="progress-bar {{ $pctProds >= 90 ? 'bg-danger' : 'bg-success' }}"
                                         role="progressbar"
                                         style="width: {{ $pctProds }}%"></div>
                                </div>
                            </div>

                            <div class="alert alert-light border small mb-0 mt-3">
                                <i class="fas fa-headset text-primary me-1"></i>
                                Besoin d'augmenter vos quotas ou de renouveler votre abonnement ? Contactez l'administration SaaS GESTOCK.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Vue multi-entreprises pour le Propriétaire --}}
            @if($entreprises->count() > 1)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="mb-0 fw-bold">État des abonnements de toutes vos entreprises</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Entreprise</th>
                                        <th>Formule</th>
                                        <th>Échéance</th>
                                        <th>Statut</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($entreprises as $ent)
                                        @php $subEnt = $ent->abonnementActuel(); @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $ent->nom }}</strong>
                                                <span class="badge bg-light text-dark border ms-1">{{ $ent->code }}</span>
                                            </td>
                                            <td>{{ $subEnt?->nom_plan ?? 'Pack Business Pro' }}</td>
                                            <td>{{ $subEnt?->date_fin?->format('d/m/Y') ?? '—' }}</td>
                                            <td>
                                                @if($subEnt)
                                                    <span class="badge {{ $subEnt->badgeClass() }}">{{ $subEnt->statutLabel() }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end">
                                                @if($ent->id !== $entreprise->id)
                                                    <form action="{{ route('entreprises.switch', $ent->id) }}" method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-primary">Basculer</button>
                                                    </form>
                                                @else
                                                    <span class="badge bg-primary">Entreprise active</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Historique des abonnements de l'entreprise --}}
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Historique des souscriptions & renouvellements</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Formule</th>
                                    <th>Périodicité</th>
                                    <th>Montant</th>
                                    <th>Période</th>
                                    <th>Mode / Réf.</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($historique as $item)
                                    <tr>
                                        <td class="fw-semibold">{{ $item->nom_plan }}</td>
                                        <td class="text-capitalize">{{ $item->periodicite }}</td>
                                        <td>{{ number_format($item->montant, 0, ',', ' ') }} {{ $item->devise }}</td>
                                        <td>Du {{ $item->date_debut?->format('d/m/Y') }} au {{ $item->date_fin?->format('d/m/Y') }}</td>
                                        <td>
                                            {{ $item->mode_paiement ?? '—' }}
                                            @if($item->reference_paiement)
                                                <small class="text-muted d-block">{{ $item->reference_paiement }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $item->badgeClass() }}">{{ $item->statutLabel() }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-3">Aucun historique d'abonnement.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

