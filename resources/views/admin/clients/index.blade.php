@extends('layouts.base')

@section('content')

@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h4 class=" text-white mb-0"> <i class="fas fa-list me-3"></i> Liste des clients</h4>
                @can('create client')
                <a href="{{ route('clients.create') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i> Nouveau client
                </a>
                @endcan
            </div>


            <div class="card-body">
                <!-- Formulaire de recherche -->
                <form method="GET" action="" class="mb-3">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Rechercher un client..." value="{{ request('search') }}">
                        <button class="btn btn-header1" type="submit">
                            <i class="fas fa-search"></i> Rechercher
                        </button>
                        <a href="{{ route('clients.exportclien.pdf', request()->query()) }}" class="btn btn-danger">
                            <i class="fas fa-download me-1"></i> Exporter PDF
                        </a>

                    </div>
                </form>
                <!-- Fin formulaire de recherche -->
                
                <div class="table-responsive">
                    <table class="table table-hover table-bordered dt-responsive nowrap w-100">
                        <thead class="table-dark">
                            <tr class="align-item-center">
                                <th>Code Client</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Téléphone</th>
                                <th>Adresse</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($clients as $client)
                                <tr>
                                    <td>{{ $client->code_client }}</td>
                                    <td>{{ $client->nom }}</td>
                                    <td>{{ $client->prenom }}</td>
                                    <td>{{ $client->telephone }}</td>
                                    <td>{{ $client->adresse }}</td>
                                    <td>
                                        <div class="btn-group gap-2">
                                            @can('view client')
                                            <a href="{{ route('clients.show', $client) }}" class="btn btn-sm btn-header1 rounded-3">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @endcan
                                            @can('edit client')
                                            <a href="{{ route('clients.edit', $client) }}" class="btn btn-sm btn-success rounded-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete client')
                                                <form id="delete-form-{{ $client->id }}" action="{{ route('clients.destroy', $client) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                                <button type="button" class="btn btn-sm btn-danger btn-delete rounded-3" data-form-id="delete-form-{{ $client->id }}">
                                                                    <i class="fas fa-trash"></i>
                                                                </button>
                                                            </form>
                                                        </div>
                                               @endcan
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="table-empty-state">
                                        <i class="fas fa-users-slash"></i>
                                        <h5 class="empty-title">Aucun client trouvé</h5>
                                        <p class="empty-desc">Aucun client ne correspond à votre recherche ou la liste est vide.</p>
                                        @can('create client')
                                            <a href="{{ route('clients.create') }}" class="btn btn-primary btn-sm mt-3">
                                                <i class="fas fa-user-plus me-1"></i> Nouveau client
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
    

    {{ $clients->links() }} <!-- Pagination links -->
 </div> <!-- end card-body -->
        </div> <!-- end card -->
    </div> <!-- end col -->




@endsection
