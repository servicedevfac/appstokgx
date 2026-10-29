@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title fw-bold mb-0"><i class="fas fa-hand-holding-usd text-primary me-2"></i> Avances sur Salaire & Remboursements</h3>
            <a href="{{ route('employes.index') }}" class="btn btn-outline-primary">Voir les Employés</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="row g-4">
            @can('créer avance salaire')
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Accorder une Avance sur Salaire</div>
                    <div class="card-body">
                        <form action="{{ route('avances-salaires.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Employé *</label>
                                <select name="employe_id" class="form-select" required>
                                    <option value="">-- Sélectionner --</option>
                                    @foreach($employes as $emp)
                                        <option value="{{ $emp->id }}">
                                            {{ $emp->nom }} {{ $emp->prenom }} (Plafond: {{ number_format($emp->plafond_effectif, 0, ',', ' ') }} FCFA)
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Montant de l'avance (FCFA) *</label>
                                <input type="number" step="0.01" name="montant" class="form-control" min="1" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Date *</label>
                                <input type="date" name="date_avance" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Motif</label>
                                <input type="text" name="motif" class="form-control" placeholder="Ex: Avance quinzaine">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enregistrer l'avance</button>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Liste des Avances et Remboursements</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employé</th>
                                        <th>Date</th>
                                        <th>Montant</th>
                                        <th>Remboursé</th>
                                        <th>Reste</th>
                                        <th>Rembourser</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($avances as $av)
                                        <tr>
                                            <td>
                                                <strong>{{ $av->employe?->nom }} {{ $av->employe?->prenom }}</strong>
                                                <small class="d-block text-muted">{{ $av->motif }}</small>
                                            </td>
                                            <td>{{ $av->date_avance?->format('d/m/Y') }}</td>
                                            <td>{{ number_format($av->montant, 0, ',', ' ') }} FCFA</td>
                                            <td class="text-success">{{ number_format($av->montant_rembourse, 0, ',', ' ') }} FCFA</td>
                                            <td class="fw-bold {{ $av->reste_a_rembourser > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($av->reste_a_rembourser, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td>
                                                @if($av->reste_a_rembourser > 0)
                                                    @can('rembourser avance salaire')
                                                    <form action="{{ route('avances-salaires.rembourser', $av->id) }}" method="POST" class="d-flex gap-1">
                                                        @csrf
                                                        <input type="number" step="0.01" name="montant" class="form-control form-control-sm" style="width: 110px;" max="{{ $av->reste_a_rembourser }}" placeholder="Montant" required>
                                                        <button type="submit" class="btn btn-sm btn-success">Rembourser</button>
                                                    </form>
                                                    @endcan
                                                @else
                                                    <span class="badge bg-success">Soldée</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">Aucune avance enregistrée.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

