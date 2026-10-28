@extends('layouts.base')

@section('content')
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header card-heade d-flex justify-content-between align-items-center">
                    <h3 class="header-title text-white "><i class="fas fa-list me-2"></i> Liste des produits</h3>
                    @can('create produit')
                        <a href="{{ route('produits.create') }}" class="btn btn-header fw-bold shadow-sm">
                            <i class="fas fa-plus me-1"></i> Nouveau produit
                        </a>
                    @endcan
                </div>
                <div class="mb-3 d-flex justify-content-end gap-2">
                    <a href="{{ route('export.products', ['format' => 'excel']) }}" class="btn btn-success">
                        <i class="bi bi-file-earmark-excel"></i> Exporter en Excel
                    </a>

                    <a href="{{ route('export.products', ['format' => 'pdf']) }}" class="btn btn-danger">
                        <i class="bi bi-file-earmark-pdf"></i> Exporter en PDF
                    </a>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                          <table class="table table-hover table-bordered dt-responsive nowrap w-100">
                        <thead class="table-dark">
                            <tr>
                                <th>N°</th>
                                <th>Nom</th>
                                <th>Total Entrées</th>
                                <th>Total Sorties</th>
                                <th>Stock Actuel</th>
                                <th>Prix (CFA)</th>
                                <th>Seuil d'alerte</th>
                                <th>Date</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($produits as $produit)

                                <tr @php $stock = $produit->stock_actuel; @endphp @if($stock < $produit->seuil_alerte)
                                style="background-color: #f8d7da;" @endif>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $produit->nom }}</td>
                                    <td>{{ $produit->mouvements->where('type_mouvement', 'entree')->sum('quantite') }}</td>
                                    <td>{{ $produit->mouvements->where('type_mouvement', 'sortie')->sum('quantite') }}</td>
                                    <td>{{ $produit->stock_actuel }}</td>
                                    <td>{{ $produit->prix }} CFA</td>
                                    <td>
                                        <span class="badge bg-warning text-dark">{{ $produit->seuil_alerte }}</span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($produit->date)->format('d/m/Y') }}</td>
                                    <td style="display:flex;flex-direction:row;justify-content:center; gap: 5px; ">
                                        @can('view produit')
                                            <a href="{{ route('produits.show', $produit->id) }}" class="btn btn-sm btn-header1">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        @endcan
                                        @can('edit produit')

                                            <a href="{{ route('produits.edit', $produit->id) }}" class="btn btn-sm btn-success">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        @endcan

                                        @can('delete produit')
                                            <form id="delete-form-{{ $produit->id }}"
                                                action="{{ route('produits.destroy', $produit) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                    data-form-id="delete-form-{{ $produit->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endcan

                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="table-empty-state">
                                        <i class="fas fa-boxes"></i>
                                        <h5 class="empty-title">Aucun produit trouvé</h5>
                                        <p class="empty-desc">Aucun produit n'est actuellement enregistré dans le catalogue.</p>
                                        @can('create produit')
                                            <a href="{{ route('produits.create') }}" class="btn btn-primary btn-sm mt-3">
                                                <i class="fas fa-plus me-1"></i> Créer un produit
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                  
                    {{ $produits->appends(request()->query())->links() }}
                </div> <!-- end card-body -->
            </div> <!-- end card -->
        </div>

@endsection
