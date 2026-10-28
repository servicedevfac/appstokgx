@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        {{-- En-tête de la page --}}
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
            <div>
                <h3 class="page-title mb-1 text-dark fw-bold">
                    <i class="fas fa-wallet text-primary me-2"></i> Gestion des Dépenses
                </h3>
            </div>
            <div class="d-flex flex-wrap gap-2">
                @can('create depense')
                    <a href="{{ route('depenses.create') }}" class="btn btn-primary fw-semibold shadow-sm">
                        <i class="fas fa-plus-circle me-1"></i> Nouvelle dépense
                    </a>
                @endcan
                <a href="{{ route('depenses.export-excel', request()->all()) }}" class="btn btn-success fw-semibold shadow-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Excel
                </a>
                <a href="{{ route('depenses.export-pdf', request()->all()) }}" class="btn btn-danger fw-semibold shadow-sm">
                    <i class="bi bi-file-earmark-pdf me-1"></i> PDF
                </a>
            </div>
        </div>

        {{-- Cartes KPIs Synthétiques --}}
        <div class="row g-3 mb-4">
            <div class="col-sm-6 col-xl-3">
                <div class="card modern-stat stat-blue h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label">Dépenses ce mois</div>
                                <div class="stat-value text-primary fw-bold">
                                    {{ number_format($totalMois, 0, ',', ' ') }} <small class="font-size-12">XOF</small>
                                </div>
                                <small class="text-muted font-size-12">{{ now()->translatedFormat('F Y') }}</small>
                            </div>
                            <div class="stat-icon bg-primary-subtle text-primary">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card modern-stat stat-gold h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label">Dépenses du jour</div>
                                <div class="stat-value text-warning fw-bold">
                                    {{ number_format($totalJour, 0, ',', ' ') }} <small class="font-size-12">XOF</small>
                                </div>
                                <small class="text-muted font-size-12">{{ now()->format('d/m/Y') }}</small>
                            </div>
                            <div class="stat-icon bg-warning-subtle text-warning">
                                <i class="fas fa-coins"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card modern-stat stat-purple h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label">Total sélection filtrée</div>
                                <div class="stat-value text-purple fw-bold">
                                    {{ number_format($totalFiltre, 0, ',', ' ') }} <small class="font-size-12">XOF</small>
                                </div>
                                <small class="text-muted font-size-12">{{ $depenses->total() }} opération(s)</small>
                            </div>
                            <div class="stat-icon bg-purple-subtle text-purple">
                                <i class="fas fa-filter"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card modern-stat stat-red h-100 border-0 shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="stat-label">Poste principal</div>
                                <div class="stat-value text-danger font-size-18 fw-bold text-truncate" style="max-width: 170px;" title="{{ $topCategorie->categorie ?? 'Aucune' }}">
                                    {{ $topCategorie->categorie ?? 'Aucun' }}
                                </div>
                                <small class="text-muted font-size-12">
                                    @if($topCategorie)
                                        {{ number_format($topCategorie->total, 0, ',', ' ') }} XOF
                                    @else
                                        0 XOF
                                    @endif
                                </small>
                            </div>
                            <div class="stat-icon bg-danger-subtle text-danger">
                                <i class="fas fa-chart-pie"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Barre de filtres --}}
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body">
                <form method="GET" action="{{ route('depenses.index') }}" class="row g-2 align-items-end">
                    {{-- Période rapide --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label font-size-13 fw-semibold text-muted">Période</label>
                        <select name="periode" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="mois" {{ request('periode', 'mois') == 'mois' ? 'selected' : '' }}>Ce mois-ci</option>
                            <option value="aujourdhui" {{ request('periode') == 'aujourdhui' ? 'selected' : '' }}>Aujourd'hui</option>
                            <option value="semaine" {{ request('periode') == 'semaine' ? 'selected' : '' }}>Cette semaine</option>
                            <option value="annee" {{ request('periode') == 'annee' ? 'selected' : '' }}>Cette année</option>
                            <option value="tout" {{ request('periode') == 'tout' ? 'selected' : '' }}>Toutes les périodes</option>
                            <option value="custom" {{ request('periode') == 'custom' ? 'selected' : '' }}>Plage personnalisée</option>
                        </select>
                    </div>

                    {{-- Catégorie --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label font-size-13 fw-semibold text-muted">Catégorie</label>
                        <select name="categorie" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Toutes les catégories</option>
                            @foreach ($categories as $catKey => $catData)
                                <option value="{{ $catKey }}" {{ request('categorie') == $catKey ? 'selected' : '' }}>
                                    {{ $catKey }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Mode de paiement --}}
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label font-size-13 fw-semibold text-muted">Mode paiement</label>
                        <select name="mode_paiement" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">Tous les modes</option>
                            @foreach ($modesPaiement as $mKey => $mLabel)
                                <option value="{{ $mKey }}" {{ request('mode_paiement') == $mKey ? 'selected' : '' }}>
                                    {{ $mKey }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Recherche --}}
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label font-size-13 fw-semibold text-muted">Recherche</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control" placeholder="Titre, bénéficiaire..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Reset --}}
                    <div class="col-md-1 col-sm-12 text-end">
                        <a href="{{ route('depenses.index') }}" class="btn btn-outline-secondary btn-sm w-100" title="Réinitialiser">
                            <i class="fas fa-undo"></i>
                        </a>
                    </div>

                    {{-- Dates personnalisées si sélectionnées --}}
                    @if(request('periode') == 'custom')
                        <div class="col-12 mt-2 pt-2 border-top">
                            <div class="row g-2">
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label font-size-12 text-muted">Date début</label>
                                    <input type="date" name="date_debut" class="form-control form-control-sm" value="{{ request('date_debut') }}">
                                </div>
                                <div class="col-sm-6 col-md-3">
                                    <label class="form-label font-size-12 text-muted">Date fin</label>
                                    <input type="date" name="date_fin" class="form-control form-control-sm" value="{{ request('date_fin') }}">
                                </div>
                                <div class="col-sm-12 col-md-2 align-self-end">
                                    <button type="submit" class="btn btn-sm btn-primary w-100">Filtrer par date</button>
                                </div>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
        </div>

        {{-- Tableau des Dépenses --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0 fw-bold text-dark font-size-16">
                    <i class="fas fa-list-alt text-primary me-2"></i> Historique des dépenses enregistrées
                </h5>
                <span class="badge bg-primary-subtle text-primary fw-medium px-2 py-1">
                    {{ $depenses->total() }} résultat(s)
                </span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 110px;">Date</th>
                                <th>Titre / Motif</th>
                                <th>Catégorie</th>
                                <th class="text-end">Montant</th>
                                <th>Paiement</th>
                                <th>Bénéficiaire</th>
                                <th class="text-center">Reçu</th>
                                <th>Auteur</th>
                                <th class="text-center" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($depenses as $depense)
                                @php
                                    $catInfo = $categories[$depense->categorie] ?? [
                                        'badge' => 'secondary',
                                        'color' => '#64748b',
                                        'icon' => 'fas fa-tag'
                                    ];
                                @endphp
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-dark font-size-13">
                                            {{ \Carbon\Carbon::parse($depense->date_depense)->format('d/m/Y') }}
                                        </div>
                                        <small class="text-muted font-size-11">
                                            {{ \Carbon\Carbon::parse($depense->date_depense)->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td>
                                        <a href="{{ route('depenses.show', $depense) }}" class="fw-bold text-dark text-decoration-none d-block">
                                            {{ $depense->titre }}
                                        </a>
                                        @if($depense->description)
                                            <small class="text-muted text-truncate d-inline-block" style="max-width: 250px;">
                                                {{ Str::limit($depense->description, 50) }}
                                            </small>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: {{ $catInfo['color'] ?? '#64748b' }}; color: #ffffff; font-weight: 500;">
                                            <i class="{{ $catInfo['icon'] ?? 'fas fa-tag' }} me-1"></i> {{ $depense->categorie }}
                                        </span>
                                    </td>
                                    <td class="text-end">
                                        <span class="fw-bold text-danger font-size-14">
                                            - {{ number_format($depense->montant, 0, ',', ' ') }}
                                        </span>
                                        <small class="text-muted font-size-11 d-block">XOF</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $depense->mode_paiement }}
                                        </span>
                                    </td>
                                    <td>
                                        {{ $depense->beneficiaire ?? '-' }}
                                    </td>
                                    <td class="text-center">
                                        @if($depense->justificatif)
                                            <a href="{{ asset($depense->justificatif) }}" target="_blank" class="btn btn-sm btn-outline-info p-1 px-2" title="Voir le justificatif">
                                                <i class="fas fa-paperclip"></i>
                                            </a>
                                        @else
                                            <span class="text-muted font-size-12">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $depense->user->nom ?? 'Admin' }}
                                        </small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm">
                                            <a href="{{ route('depenses.show', $depense) }}" class="btn btn-outline-primary" title="Détails">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('edit depense')
                                                <a href="{{ route('depenses.edit', $depense) }}" class="btn btn-outline-warning" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan
                                            @can('delete depense')
                                                <button type="button" class="btn btn-outline-danger btn-delete" data-form-id="delete-form-{{ $depense->id }}" title="Supprimer">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                                <form id="delete-form-{{ $depense->id }}" action="{{ route('depenses.destroy', $depense) }}" method="POST" style="display: none;">
                                                    @csrf
                                                    @method('DELETE')
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-receipt font-size-48 mb-3 d-block text-secondary opacity-50"></i>
                                            <h5>Aucune dépense trouvée</h5>
                                            <p class="font-size-13">Aucune dépense ne correspond aux critères sélectionnés.</p>
                                            @can('create depense')
                                                <a href="{{ route('depenses.create') }}" class="btn btn-primary btn-sm mt-2">
                                                    <i class="fas fa-plus me-1"></i> Ajouter la première dépense
                                                </a>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($depenses->hasPages())
                    <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <small class="text-muted">
                            Affichage de {{ $depenses->firstItem() ?? 0 }} à {{ $depenses->lastItem() ?? 0 }} sur {{ $depenses->total() }} dépenses
                        </small>
                        <div>
                            {{ $depenses->links('pagination::bootstrap-5') }}
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
