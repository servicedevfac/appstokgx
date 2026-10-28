@extends('layouts.base')
@section('content')

    {{-- Stock faible alert --}}
    @if($produitsStockFaible->count() > 0)
        <div class="marquee-wrapper mb-3">
            <div class="marquee-content">
                <span class="marquee-badge"><i class="fas fa-exclamation-triangle me-1"></i> Stock faible</span>
                <span class="marquee-track">
                    @foreach($produitsStockFaible as $produit)
                        <strong>{{ $produit->nom }}</strong> ({{ $produit->stock_actuel }}) &nbsp;&nbsp;|&nbsp;&nbsp;
                    @endforeach
                    Veuillez approvisionner ces produits.
                </span>
            </div>
        </div>
    @endif

    <div class="row mt-3">
        <div class="col-lg-6">
            <h4 class="page-title mb-0">Tableau de bord gestionnaire</h4>
        </div>
    </div>

    {{-- Filtre de Période (Jour, Semaine, Mois, Année) pour les Indicateurs --}}
    <div class="card border-0 shadow-sm mt-3 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3">
                {{-- Boutons rapides de période : Jour, Semaine, Mois, Année, Tout --}}
                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="text-muted fw-semibold small me-1">
                        <i class="fas fa-calendar-alt text-primary me-1"></i> Filtrer par période :
                    </span>
                    <div class="period-buttons-container">
                        <a href="{{ route('dashboard', ['periode' => 'jour']) }}" 
                           class="period-filter-btn {{ $periode === 'jour' ? 'btn-active-period' : '' }}">
                            <i class="fas fa-calendar-day me-1"></i> Jour
                        </a>
                        <a href="{{ route('dashboard', ['periode' => 'semaine']) }}" 
                           class="period-filter-btn {{ $periode === 'semaine' ? 'btn-active-period' : '' }}">
                            <i class="fas fa-calendar-week me-1"></i> Semaine
                        </a>
                        <a href="{{ route('dashboard', ['periode' => 'mois']) }}" 
                           class="period-filter-btn {{ $periode === 'mois' ? 'btn-active-period' : '' }}">
                            <i class="fas fa-calendar-alt me-1"></i> Mois
                        </a>
                        <a href="{{ route('dashboard', ['periode' => 'annee']) }}" 
                           class="period-filter-btn {{ $periode === 'annee' ? 'btn-active-period' : '' }}">
                            <i class="fas fa-calendar me-1"></i> Année
                        </a>
                        <a href="{{ route('dashboard', ['periode' => 'tout']) }}" 
                           class="period-filter-btn {{ $periode === 'tout' ? 'btn-active-period' : '' }}">
                            <i class="fas fa-infinity me-1"></i> Tout
                        </a>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle period-badge-status ms-md-2">
                        <i class="fas fa-clock me-1"></i> {{ $periodeLabel }}
                    </span>
                </div>

                {{-- Filtre personnalisé de dates (Du / Au) --}}
                <form method="GET" action="{{ route('dashboard') }}" class="d-flex flex-wrap align-items-center gap-2 m-0">
                    <input type="hidden" name="periode" value="custom">
                    <div class="input-group input-group-sm" style="width: auto;">
                        <span class="input-group-text bg-light text-muted small">Du</span>
                        <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut', $dateDebut ?? '') }}" required>
                        <span class="input-group-text bg-light text-muted small">Au</span>
                        <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin', $dateFin ?? '') }}" required>
                        <button type="submit" class="btn btn-primary btn-sm" title="Appliquer la plage personnalisée">
                            <i class="fas fa-filter"></i>
                        </button>
                    </div>
                    @if(request()->hasAny(['periode', 'date_debut', 'date_fin']))
                        <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm" title="Réinitialiser au mois">
                            <i class="fas fa-undo"></i>
                        </a>
                    @endif
                </form>
            </div>
        </div>
    </div>

    {{-- Stat cards --}}
    <div class="row mt-3 gx-3 gy-3" id="indicator-cards-container">
        {{-- Encaissé --}}
        <div class="col-sm-6 col-lg-3 indicator-card-col" id="card-encaisse">
            <div class="card modern-stat stat-green h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Encaissé ({{ ucfirst($periodeTitle) }})</div>
                            <div class="stat-value">{{ number_format($encaissePeriode, 0, ',', ' ') }} <small>XOF</small></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Chiffre d'affaires --}}
        <div class="col-sm-6 col-lg-3 indicator-card-col" id="card-ca">
            <div class="card modern-stat stat-purple h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Chiffre d'affaires</div>
                            <div class="stat-value">{{ number_format($caPeriode, 0, ',', ' ') }} <small>XOF</small></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Reste à payer --}}
        <div class="col-sm-6 col-lg-3 indicator-card-col" id="card-reste-payer">
            <div class="card modern-stat stat-red h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Reste à payer</div>
                            <div class="stat-value">{{ number_format($resteAPayerPeriode, 0, ',', ' ') }} <small>XOF</small></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ventes réalisées --}}
        <div class="col-sm-6 col-lg-3 indicator-card-col" id="card-nb-ventes">
            <div class="card modern-stat stat-blue h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Ventes réalisées</div>
                            <div class="stat-value">{{ number_format($nbVentesPeriode, 0, ',', ' ') }} <small>vente(s)</small></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Panier moyen --}}
        <div class="col-sm-6 col-lg-3 indicator-card-col" id="card-panier-moyen">
            <div class="card modern-stat stat-gold h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Panier moyen</div>
                            <div class="stat-value">{{ number_format($panierMoyenPeriode, 0, ',', ' ') }} <small>XOF</small></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Taux de recouvrement --}}
        <div class="col-sm-6 col-lg-3 indicator-card-col" id="card-taux-recouvrement">
            <div class="card modern-stat stat-teal h-100">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="stat-label">Taux d'encaissement</div>
                            <div class="stat-value">{{ $tauxRecouvrement }} <small>%</small></div>
                        </div>
                        <div class="stat-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Alerte aucun indicateur sélectionné --}}
    <div id="no-indicators-alert" class="alert alert-info d-none mt-3 text-center py-3 rounded-3 shadow-sm border-0">
        <i class="fas fa-info-circle me-2 text-primary"></i> Aucun indicateur n'est visible avec la sélection actuelle.
        <button type="button" class="btn btn-sm btn-link p-0 ms-2 fw-semibold text-primary text-decoration-underline" id="btn-reset-indicators">
            Afficher tous les indicateurs
        </button>
    </div>

    {{-- Chart --}}
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header card-heade">
                    <h4 class="card-title"><i class="fas fa-chart-bar me-2"></i>Chiffre d'affaires des 12 derniers mois</h4>
                </div>
                <div class="chart-container">
                    <canvas id="caLineChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Stock faible table --}}
    <div class="row mt-4">
        <div class="col-xl-12">
            <div class="card">
                <div class="card-header redoff d-flex align-items-center gap-2">
                    <i class="fas fa-exclamation-circle"></i>
                    <h4 class="card-title mb-0">Produits en stock faible</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered table-nowrap mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Nom produit</th>
                                    <th>Stock actuel</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($produitsStockFaible as $produit)
                                    <tr class="low-stock-row">
                                        <td>{{ $loop->iteration }}</td>
                                        <td><strong>{{ $produit->nom }}</strong></td>
                                        <td><span class="badge bg-danger">{{ $produit->stock_actuel }}</span></td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-success">
                                            <i class="fas fa-check-circle font-size-24 mb-2 d-block opacity-75"></i>
                                            Tous les stocks sont à un niveau optimal !
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent sales --}}
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header card-heade d-flex align-items-center gap-2">
                    <i class="fas fa-shopping-cart"></i>
                    <h4 class="card-title mb-0">Ventes récentes</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-centered table-striped table-nowrap mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Code reçu</th>
                                    <th>Client</th>
                                    <th>Paiement</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($ventes as $vente)
                                    <tr>
                                        <td class="fw-semibold">{{ $vente->code_recu }}</td>
                                        <td>{{ $vente->client->nom }}</td>
                                        <td>{{ $vente->mode_paiement }}</td>
                                        <td>{{ $vente->date_vente }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            <i class="fas fa-shopping-cart font-size-24 mb-2 d-block opacity-50"></i>
                                            Aucune vente enregistrée récemment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent clients --}}
        <div class="col-xl-6">
            <div class="card">
                <div class="card-header card-heade d-flex align-items-center gap-2">
                    <i class="fas fa-users"></i>
                    <h4 class="card-title mb-0">Clients récents</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered table-nowrap mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Nom</th>
                                    <th>Adresse</th>
                                    <th>Téléphone</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($derniersClients as $client)
                                    <tr>
                                        <td class="fw-semibold">{{ $client->client->nom }}</td>
                                        <td>{{ $client->client->adresse }}</td>
                                        <td>{{ $client->client->telephone }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fas fa-users-slash font-size-24 mb-2 d-block opacity-50"></i>
                                            Aucun client enregistré récemment.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection
@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const labels = @json($labels);
    const data = @json($data);
    const data1 = @json($data1);
    const reste = @json($dataReste);

    const ctx = document.getElementById('caLineChart').getContext('2d');

    const caLineChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: "Chiffre d'affaires",
                    data: data,
                    backgroundColor: 'rgba(26, 35, 126, 0.85)',
                    borderColor: 'rgba(26, 35, 126, 1)',
                    borderRadius: 6,
                    borderWidth: 0,
                },
                {
                    label: 'Montant encaissé',
                    data: data1,
                    backgroundColor: 'rgba(22, 163, 74, 0.85)',
                    borderColor: 'rgba(22, 163, 74, 1)',
                    borderRadius: 6,
                    borderWidth: 0,
                },
                {
                    label: 'Reste à payer',
                    data: reste,
                    backgroundColor: 'rgba(220, 38, 38, 0.85)',
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderRadius: 6,
                    borderWidth: 0,
                }
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                    labels: {
                        usePointStyle: true,
                        pointStyle: 'rectRounded',
                        padding: 20,
                        font: { family: "'Inter', sans-serif", size: 12, weight: '500' }
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false,
                    backgroundColor: 'rgba(15, 23, 42, 0.9)',
                    titleFont: { family: "'Inter', sans-serif", size: 13, weight: '600' },
                    bodyFont: { family: "'Inter', sans-serif", size: 12 },
                    padding: 12,
                    cornerRadius: 8,
                    callbacks: {
                        label: function(context) {
                            return context.dataset.label + ': ' + new Intl.NumberFormat('fr-FR', { style: 'currency', currency: 'XOF' }).format(context.raw);
                        }
                    }
                }
            },
            interaction: { mode: 'nearest', intersect: false },
            scales: {
                x: {
                    display: true,
                    title: { display: true, text: 'Mois', font: { family: "'Inter', sans-serif", weight: '600' } },
                    grid: { display: false },
                    ticks: { font: { family: "'Inter', sans-serif", size: 11 } }
                },
                y: {
                    display: true,
                    title: { display: true, text: 'Montant', font: { family: "'Inter', sans-serif", weight: '600' } },
                    beginAtZero: true,
                    grid: { color: 'rgba(0,0,0,0.04)' },
                    ticks: {
                        font: { family: "'Inter', sans-serif", size: 11 },
                        callback: function(value) {
                            return new Intl.NumberFormat('fr-FR', { notation: 'compact' }).format(value);
                        }
                    }
                }
            }
        }
    });
</script>
@endsection
