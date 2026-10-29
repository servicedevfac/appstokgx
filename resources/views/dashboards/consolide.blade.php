@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title mb-1 fw-bold">
                    <i class="fas fa-chart-pie text-primary me-2"></i> Tableau de Bord Consolidé Multi-Entreprises
                </h3>
                <p class="text-muted mb-0">Synthèse consolidée de vos {{ $entreprises->count() }} entreprise(s) accessible(s) ({{ $periodeLabel }})</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('entreprises.index') }}" class="btn btn-outline-primary">
                    <i class="fas fa-building me-1"></i> Gérer mes entreprises
                </a>
            </div>
        </div>

        {{-- Indicateurs consolidés --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Chiffre d'affaires Net Consolidé</div>
                        <div class="fs-4 fw-bold text-primary mt-1">{{ number_format($consolide['global']['chiffre_affaires_net'], 0, ',', ' ') }} XOF</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Total Encaissé Consolidé</div>
                        <div class="fs-4 fw-bold text-success mt-1">{{ number_format($consolide['global']['total_encaisse'], 0, ',', ' ') }} XOF</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Créances Clients Consolidées</div>
                        <div class="fs-4 fw-bold text-warning mt-1">{{ number_format($consolide['global']['creances_clients'], 0, ',', ' ') }} XOF</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Dettes Fournisseurs Consolidées</div>
                        <div class="fs-4 fw-bold text-danger mt-1">{{ number_format($consolide['global']['dettes_fournisseurs'], 0, ',', ' ') }} XOF</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Dépenses Validées Consolidées</div>
                        <div class="fs-4 fw-bold text-secondary mt-1">{{ number_format($consolide['global']['depenses_validees'], 0, ',', ' ') }} XOF</div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Résultat Net Estimatif Consolidé</div>
                        <div class="fs-4 fw-bold {{ $consolide['global']['resultat_estimatif'] >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                            {{ number_format($consolide['global']['resultat_estimatif'], 0, ',', ' ') }} XOF
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-6 col-xl-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small fw-semibold">Flux de Trésorerie Net Consolidé</div>
                        <div class="fs-4 fw-bold {{ $consolide['global']['flux_tresorerie_net'] >= 0 ? 'text-info' : 'text-danger' }} mt-1">
                            {{ number_format($consolide['global']['flux_tresorerie_net'], 0, ',', ' ') }} XOF
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Détail par entreprise --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Détail par Entreprise</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Entreprise</th>
                                <th>CA Net</th>
                                <th>Encaissé</th>
                                <th>Créances Clients</th>
                                <th>Dettes Fournisseurs</th>
                                <th>Dépenses</th>
                                <th>Résultat Estimatif</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($entreprises as $ent)
                                @php $ind = $consolide['par_entreprise'][$ent->id] ?? []; @endphp
                                <tr>
                                    <td>
                                        <strong>{{ $ent->nom }}</strong>
                                        <span class="badge bg-light text-dark border ms-1">{{ $ent->code }}</span>
                                    </td>
                                    <td>{{ number_format($ind['chiffre_affaires_net'] ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td>{{ number_format($ind['total_encaisse'] ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td>{{ number_format($ind['creances_clients'] ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td>{{ number_format($ind['dettes_fournisseurs'] ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td>{{ number_format($ind['depenses_validees'] ?? 0, 0, ',', ' ') }} XOF</td>
                                    <td class="fw-bold {{ ($ind['resultat_estimatif'] ?? 0) >= 0 ? 'text-success' : 'text-danger' }}">
                                        {{ number_format($ind['resultat_estimatif'] ?? 0, 0, ',', ' ') }} XOF
                                    </td>
                                    <td>
                                        <form action="{{ route('entreprises.switch', $ent->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Accéder à l'espace
                                            </button>
                                        </form>
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
@endsection

