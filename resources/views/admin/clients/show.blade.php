@extends('layouts.base')


@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0"><i class="fas fa-user me-3"></i> Détails du client</h3>
                <a href="{{ route('clients.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
            <div class="card-body p-4">
                <dl class="row">
                    <dt class="col-sm-3">Nom</dt>
                    <dd class="col-sm-9">{{ $client->nom }}</dd>

                    <dt class="col-sm-3">Prénom</dt>
                    <dd class="col-sm-9">{{ $client->prenom }}</dd>

                    <dt class="col-sm-3">Téléphone</dt>
                    <dd class="col-sm-9">{{ $client->telephone }}</dd>

                    <dt class="col-sm-3">Adresse</dt>
                    <dd class="col-sm-9">{{ $client->adresse }}</dd>
                </dl>
                @can('edit client')
                    <a href="{{ route('clients.edit', $client->id) }}" class="btn btn-header1 me-2">
                        <i class="fas fa-edit me-1"></i> Modifier
                    </a>
                @endcan
            </div>
        </div>
    </div>
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0"><i class="fas fa-user me-3"></i> Détails du client</h3>
                <a href="{{ route('clients.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>
            <div class="card-body p-4">
                  <table class="table table-hover table-bordered dt-responsive nowrap w-100">
        <thead class="card-heade  table-dark">
            <tr>
                <th>Numéro</th>
                <th>Code reçu</th>
                <th>Client</th>
                <th>Date</th>
                <th>Montant total</th>
                <th>Remise</th>
                <th>Recu de vente</th>
               <th>Actions</th>


            </tr>
        </thead>
        <tbody>
            @forelse($ventes as $vente)
            <tr @if ($vente->statut=='valide') style="background-color:#d4edda;"
            @elseif ($vente->statut=='annulee') style="background-color:#f8d7da ;"

            @else style="background-color:#e2e3e5 ;"
            @endif>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $vente->code_recu }}</td>
                <td>{{ $vente->client->nom ?? '' }}</td>
                <td>{{ $vente->created_at->format('d/m/Y H:i') }}</td>
                <td>{{ number_format($vente->montant_total, 0, ',', ' ') }} FCFA</td>
                <td>{{ number_format($vente->remise, 0, ',', ' ') }} FCFA</td>
                <td>
                    @if($vente->code_recu)
                        <a href="{{ asset('storage/recus/recu_vente_'.$vente->client->nom.'_'.$vente->code_recu.'.pdf') }}" class="btn btn-header1 btn-lg" target="_blank"><i class="fas fa-file-pdf"></i></a>
                    @else
                        <span class="text-muted">Pas de reçu</span>
                    @endif
                </td>
                <td style="display:flex;flex-direction:row;justify-content:center; gap: 5px; ">
                    @can('view vente')
                        <a href="{{ route('ventes.show', $vente->id) }}" class="btn btn-header1 text-white-bold btn-lg rounded-3"><i class="fas fa-eye"></i></a>
                    @endcan
                    @if (Auth::user()->can('edit vente'))
                    @can('annuler vente')
                    <form id="form-annuler-{{ $vente->id }}" action="{{ route('ventes.annuler', $vente->id) }}" method="POST">
                    @csrf
                    <button type="button" class="btn btn-delete btn-lg" onclick="confirmerAnnulation({{ $vente->id }})"><i class="fas fa-cancel"></i></button>
                    </form>
                 @endcan
                    @endif
                </td>
            </tr>
            @empty
                <tr>
                    <td colspan="8" class="table-empty-state">
                        <i class="fas fa-receipt"></i>
                        <h5 class="empty-title">Aucune vente pour ce client</h5>
                        <p class="empty-desc">Ce client n'a effectué aucun achat pour le moment.</p>
                        @can('create vente')
                            <a href="{{ route('ventes.create') }}" class="btn btn-primary btn-sm mt-3">
                                <i class="fas fa-plus me-1"></i> Enregistrer une vente
                            </a>
                        @endcan
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    {{ $ventes->appends(request()->query())->links() }}
        </div>
    </div>
</div>

@endsection
