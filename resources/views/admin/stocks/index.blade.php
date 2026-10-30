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
                            @php
                                $typeMouvement = strtolower(trim($mouvement->type_mouvement ?? ''));
                                $isSortie = in_array($typeMouvement, ['sortie', 'sorties']);
                                $isEntree = in_array($typeMouvement, ['entree', 'entrée', 'entrees', 'entrées']);
                            @endphp
                            <td>
                                @if($isSortie)
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                        <i class="fas fa-arrow-up me-1"></i>Sortie
                                    </span>
                                @elseif($isEntree)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                        <i class="fas fa-arrow-down me-1"></i>Entrée
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1">
                                        {{ ucfirst($mouvement->type_mouvement) }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($isSortie)
                                    <span class="fw-bold text-danger fs-6" style="color: #dc3545 !important;">-{{ $mouvement->quantite }}</span>
                                @elseif($isEntree)
                                    <span class="fw-bold text-success fs-6" style="color: #198754 !important;">+{{ $mouvement->quantite }}</span>
                                @else
                                    <span class="fw-bold fs-6">{{ $mouvement->quantite }}</span>
                                @endif
                            </td>
                            <td class="fw-semibold">{{ $mouvement->stock_apres_mouvement ?? $mouvement->produit?->stock_actuel ?? 0 }}</td>
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


