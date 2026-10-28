@extends('layouts.base')

@section('content')
    <div class="row my-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header  card-heade d-flex justify-content-between align-items-center">
                    <h3 class="header-title text-white"><i class="fas fa-list me-2"></i>Liste des permissions </h3>
                    <div class="card-tools">
                        @can('create permission')
                        <a href="{{ route('permissions.create') }}" class="btn btn-header  fw-bold shadow-sm">
                            <i class="fas fa-plus me-1"></i> Nouvelle permission
                        </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                         <table class="table table-hover table-bordered dt-responsive nowrap w-100">
                            <thead class="table-dark">
                                <tr>
                                    <th>N°</th>
                                    <th>Nom</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $permission)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $permission->name }}</td>
                                    <td>
                                        <div  style="display:flex;flex-direction:row;justify-content:center; gap: 5px; ">
                                            @can('view permission')
                                            <a href="{{ route('permissions.edit', $permission) }}" class="btn btn-sm btn-success rounded-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            @endcan
                                            @can('delete permission')
                                                <form id="delete-form-{{ $permission->id }}" action="{{ route('permissions.destroy', $permission) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                <button type="button" class="btn btn-sm  btn-delete rounded-3" data-form-id="delete-form-{{ $permission->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                            </form>
                                            @endcan

                                        </div>
                                    </td>
                                </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="table-empty-state">
                                            <i class="fas fa-key"></i>
                                            <h5 class="empty-title">Aucune permission trouvée</h5>
                                            <p class="empty-desc">Aucune permission n'est actuellement enregistrée.</p>
                                            @can('create permission')
                                                <a href="{{ route('permissions.create') }}" class="btn btn-primary btn-sm mt-3">
                                                    <i class="fas fa-plus me-1"></i> Nouvelle permission
                                                </a>
                                            @endcan
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $permissions->links() }}
                    </div>
                </div>
            </div>
        </div>



@endsection



