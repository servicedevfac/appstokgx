@extends('layouts.base')

@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0"><i class="fas fa-list me-2"></i>  Liste des rôles</h3>
                @can('create role')
                <a href="{{ route('roles.create') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-plus me-1"></i> Nouveau rôle
                </a>
                @endcan
            </div>
        <div class="card-body">
            <div class="table-responsive">
                 <table class="table table-hover table-bordered dt-responsive nowrap w-100">
                    <thead class="table-dark">
                        <tr>
                            <th>N°</th>
                            <th>Nom</th>
                            <th>Permission</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($roles as $role)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $role->name }}</td>
                            <td>
                                @foreach($role->permissions as $permission)
                                    <span class="badge card-heade">{{ $permission->name }}</span>
                                @endforeach
                            </td>
                            <td >
                                <div style="display:flex;flex-direction:row;justify-content:end; gap: 5px; ">
                                        @can('view role')
                                        <a href="{{ route('roles.show', $role->id) }}" class="btn btn-header1 btn-sm rounded-3" title="Voir">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @endcan
                                        @can('edit role')
                                        <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-success  btn-sm rounded-3" title="Modifier">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        @endcan
                                       @can('delete role')
                                       <form id="delete-form-{{ $role->id }}" action="{{ route('roles.destroy', $role) }}" method="POST" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="button" class="btn btn-sm  btn-delete rounded-3" data-form-id="delete-form-{{ $role->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>

                                        </form>
                                        @endcan

                                </div>
                            </td>
                        </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="table-empty-state">
                                    <i class="fas fa-shield-alt"></i>
                                    <h5 class="empty-title">Aucun rôle trouvé</h5>
                                    <p class="empty-desc">Aucun rôle n'est actuellement défini dans le système.</p>
                                    @can('create role')
                                        <a href="{{ route('roles.create') }}" class="btn btn-primary btn-sm mt-3">
                                            <i class="fas fa-plus me-1"></i> Nouveau rôle
                                        </a>
                                    @endcan
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    </div>


@endsection


