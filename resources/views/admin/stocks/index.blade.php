@extends('layouts.base')
@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0"><i class="fas fa-list me-2"></i>  Liste des mouvements de stock</h3>
                @can('create stock')

                <a href="{{ route('mouvementStocks.create') }}" class="btn btn-header  fw-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i> Nouveau mouvement de stock
                </a>
                @endcan
            </div>
            <div class="d-flex justify-content-end">
                <a href="{{ route('mouvementStocks.export-excel') }}" class="btn btn-success">Exporter en excel</a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-bordered dt-responsive nowrap w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>N°</th>
                            <th>Produit</th>
                            <th>Utilisateur</th>
                            <th>Type de mouvement</th>
                            <th>Quantité</th>
                            <th>Stock après mouvement</th>
                            <th>Motif</th>
                            <th>Date du mouvement</th>
                            <th>Action</th>

                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($mouvements as $mouvement)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $mouvement->produit->nom }}</td>
                            <td>{{ $mouvement->user->nom }}</td>
                            <td>{{ $mouvement->type_mouvement }}</td>
                            <td>{{ $mouvement->quantite }}</td>
                            <td>{{ $mouvement->produit->stock_actuel }}</td>
                            <td>{{ $mouvement->motif }}</td>
                            <td>{{ \Carbon\Carbon::parse($mouvement->created_at)->format('d/m/Y H:i') }}</td>

                             <td style="display:flex;flex-direction:row;justify-content:center; gap: 5px; ">
                                 @if (Auth::user()->hasRole('super admin')| Auth::user()->hasRole('Administrateur'))
                                @can('edit stock')
                                <a href="{{ route('mouvementStocks.edit', $mouvement->id) }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-edit"></i>
                                    </a>
                                @endcan
                                @can('delete stock')
                                    <form action="{{ route('mouvementStocks.destroy', $mouvement->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                @endcan
                            </td>
                                @endif
                        </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="table-empty-state">
                                    <i class="fas fa-warehouse"></i>
                                    <h5 class="empty-title">Aucun mouvement de stock</h5>
                                    <p class="empty-desc">Aucune entrée ou sortie de stock n'a été enregistrée pour l'instant.</p>
                                    @can('create stock')
                                        <a href="{{ route('mouvementStocks.create') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-plus me-1"></i> Nouveau mouvement
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
                 <div>
                </div>
                </div> <!-- end table-responsive -->
            </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div> <!-- end col -->


@endsection


