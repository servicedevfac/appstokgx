@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3 class="page-title fw-bold mb-0"><i class="fas fa-truck text-primary me-2"></i> Fournisseurs</h3>
            <a href="{{ route('achats.index') }}" class="btn btn-outline-primary">Voir les Achats</a>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            @can('créer fournisseur')
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Nouveau Fournisseur</div>
                    <div class="card-body">
                        <form action="{{ route('fournisseurs.store') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Nom / Raison sociale *</label>
                                <input type="text" name="nom" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Téléphone</label>
                                <input type="text" name="telephone" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" name="email" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Adresse</label>
                                <input type="text" name="adresse" class="form-control">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Notes</label>
                                <textarea name="notes" class="form-control" rows="2"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Enregistrer</button>
                        </form>
                    </div>
                </div>
            </div>
            @endcan

            <div class="col-lg-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Liste des Fournisseurs</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fournisseur</th>
                                        <th>Contact</th>
                                        <th>Nb Achats</th>
                                        <th>Dette en cours</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($fournisseurs as $f)
                                        <tr>
                                            <td><strong>{{ $f->nom }}</strong></td>
                                            <td>
                                                <div>{{ $f->telephone ?? '-' }}</div>
                                                <small class="text-muted">{{ $f->email ?? '' }}</small>
                                            </td>
                                            <td><span class="badge bg-secondary">{{ $f->achats_count }}</span></td>
                                            <td class="fw-bold text-danger">{{ number_format($f->solde_dette, 0, ',', ' ') }} FCFA</td>
                                            <td>
                                                <a href="{{ route('fournisseurs.show', $f->id) }}" class="btn btn-sm btn-outline-primary">Détails</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="5" class="text-center text-muted">Aucun fournisseur enregistré.</td></tr>
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

