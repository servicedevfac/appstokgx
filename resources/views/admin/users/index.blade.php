@extends('layouts.base')
@section('content')
    <div class="row mt-5">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header card-heade d-flex justify-content-between align-items-center">
                    <h3 class="text-white m-0"><i class="fas fa-list me-2"></i> Liste des utilisateurs</h3>
                    @can('create utilisateur')
                        <a href="{{ route('users.create') }}" class="btn btn-header fw-bold shadow-sm">
                            <i class="fas fa-plus me-1"></i> Nouvel utilisateur
                        </a>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>N°</th>
                                <th>Nom</th>
                                <th>Prénom</th>
                                <th>Rôle</th>
                                <th>Email</th>
                                <th>Téléphone</th>
                                <th>Actions</th>
                            </tr>
                        </th                        <tbody>
                            @forelse($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $user->nom }}</td>
                                    <td>{{ $user->prenom }}</td>
                                    <td>
                                        @forelse($user->getRoleNames() as $role)
                                            <span class="badge bg-primary">{{ $role }}</span>
                                        @empty
                                            <span class="text-muted">Aucun rôle</span>
                                        @endforelse
                                    </td>

                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->telephone }}</td>
                                    <td>
                                        <div class="d-flex justify-content-center gap-2">

                                            {{-- Voir --}}
                                            @can('view utilisateur')
                                                <a href="{{ route('users.show', $user->id) }}"
                                                    class="btn btn-sm btn-header1 rounded-3" title="Voir">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            @endcan

                                            {{-- Éditer --}}
                                            @can('edit utilisateur')
                                                <a href="{{ route('users.edit', $user->id) }}"
                                                    class="btn btn-sm btn-success rounded-3" title="Modifier">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            @endcan

                                            {{-- Bloquer / Débloquer --}}
                                            @can('edit utilisateur')
                                                <form id="btn-toggle{{ $user->id }}" action="{{ route('users.toggle', $user->id) }}" method="POST"
                                                    class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm {{ $user->actif ? 'btn-secondary' : 'btn-warning' }} rounded-3"
                                                        title="{{ $user->actif ? 'Bloquer' : 'Débloquer' }}">
                                                        <i class="fas {{ $user->actif ? 'fa-ban' : 'fa-unlock' }}"></i>
                                                    </button>
                                                </form>
                                            @endcan

                                            @can('delete utilisateur')
                                                <form id="delete-form-{{ $user->id }}" action="{{ route('users.destroy', $user) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger btn-delete"
                                                        data-form-id="delete-form-{{ $user->id }}" title="Supprimer">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="table-empty-state">
                                        <i class="fas fa-users-cog"></i>
                                        <h5 class="empty-title">Aucun utilisateur trouvé</h5>
                                        <p class="empty-desc">Aucun compte utilisateur n'a été trouvé.</p>
                                        @can('create utilisateur')
                                            <a href="{{ route('users.create') }}" class="btn btn-primary btn-sm mt-3">
                                                <i class="fas fa-user-plus me-1"></i> Nouvel utilisateur
                                            </a>
                                        @endcan
                                    </td>
                                </tr>
                            @endforelse
                        </tbody> </tbody>
                    </table>
                    </div>

                    
                </div>
            </div>
        </div>
@endsection

