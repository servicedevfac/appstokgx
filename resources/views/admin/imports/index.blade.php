@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <h3 class="page-title fw-bold mb-4"><i class="fas fa-file-import text-primary me-2"></i> Importation de Données Excel / CSV</h3>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif
        @if(session('info'))
            <div class="alert alert-info">{{ session('info') }}</div>
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
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Téléverser un fichier pour aperçu avant validation</div>
                    <div class="card-body">
                        <form action="{{ route('imports.preview') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label">Type de données à importer *</label>
                                <select name="type" class="form-select" required>
                                    <option value="produits">Produits (nom, prix_vente, prix_achat, reference, categorie, seuil_alerte, stock_initial)</option>
                                    <option value="categories">Catégories (nom, description)</option>
                                    <option value="clients">Clients (nom, prenom, telephone, adresse)</option>
                                    <option value="fournisseurs">Fournisseurs (nom, telephone, email, adresse)</option>
                                    <option value="stocks_initiaux">Stocks initiaux (nom ou reference, quantite)</option>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fichier (.xlsx, .xls, .csv) *</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search me-1"></i> Analyser et Afficher l'Aperçu
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white fw-bold">Historique des Lots d'Importation</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Fichier</th>
                                        <th>Total / Importées / Rejetées</th>
                                        <th>Statut</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($batches as $b)
                                        <tr>
                                            <td>{{ $b->created_at?->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge bg-info text-dark">{{ strtoupper($b->type) }}</span></td>
                                            <td>{{ $b->filename }}</td>
                                            <td>
                                                {{ $b->total_rows }} /
                                                <span class="text-success fw-bold">{{ $b->imported_rows }}</span> /
                                                <span class="text-danger fw-bold">{{ $b->rejected_rows }}</span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $b->statut === 'completed' ? 'bg-success' : 'bg-warning text-dark' }}">
                                                    {{ strtoupper($b->statut) }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('imports.show-preview', $b->id) }}" class="btn btn-sm btn-outline-primary">Voir rapport</a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr><td colspan="6" class="text-center text-muted">Aucune importation effectuée.</td></tr>
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

