@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        

        <div class="row g-4">
            @can('créer employé')
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Nouvel Employé</div>
                    <div class="card-body">
                        <form action="{{ route('employes.store') }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label class="form-label">Nom *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Prénom</label>
                                <input type="text" name="prenom" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Poste / Fonction *</label>
                                <input type="text" name="poste" class="form-control" required>
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control">
                            </div>
                            <div class="mb-2">
                                <label class="form-label">Salaire de base (FCFA) *</label>
                                <input type="number" step="0.01" name="salaire_base" class="form-control" min="0" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Plafond d'avance autorisé (FCFA)</label>
                                <input type="number" step="0.01" name="plafond_avance" class="form-control" placeholder="Par défaut: {{ $entreprise?->plafond_avance_salaire ?? 0 }}">
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Liste des Employés</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Employé</th>
                                        <th>Poste</th>
                                        <th>Salaire de base</th>
                                        <th>Plafond Avance</th>
                                        <th>Avance Restante à Rembourser</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employes as $emp)
                                        <tr>
                                            <td><strong>{{ $emp->nom }} {{ $emp->prenom }}</strong></td>
                                            <td>{{ $emp->poste }}</td>
                                            <td>{{ number_format($emp->salaire_base, 0, ',', ' ') }} FCFA</td>
                                            <td>{{ number_format($emp->plafond_effectif, 0, ',', ' ') }} FCFA</td>
                                            <td class="fw-bold {{ $emp->solde_avance_restant > 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($emp->solde_avance_restant, 0, ',', ' ') }} FCFA
                                            </td>
                                            <td>
                                                <a href="{{ route('employes.show', $emp->id) }}" class="btn btn-sm btn-outline-primary">Historique</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">Aucun employé enregistré.</td></tr>
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

