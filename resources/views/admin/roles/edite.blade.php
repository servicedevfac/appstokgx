@extends('layouts.base')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-edit"></i>
                    <span>Modifier le rôle : {{ $role->name }}</span>
                </h3>
                <a href="{{ route('roles.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour à la liste
                </a>
            </div>

            <div class="card-body p-4">
                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Veuillez corriger les erreurs ci-dessous :</strong>
                        </div>
                        <ul class="mb-0 ps-3">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form action="{{ route('roles.update', $role->id) }}" method="POST" class="needs-validation" novalidate>
                    @csrf
                    @method('PUT')

                    <div class="mb-4">
                        <label for="name" class="form-label">Nom du rôle <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $role->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label d-block mb-2">Permissions associées <span class="text-danger">*</span></label>
                        @error('permissions')
                            <div class="alert alert-danger py-2 px-3 mb-3 d-flex align-items-center gap-2 font-size-13">
                                <i class="fas fa-exclamation-circle"></i>
                                <span>{{ $message }}</span>
                            </div>
                        @enderror
                        <div class="row g-3">
                            @foreach($permissions as $permission)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check p-2 rounded-2 border bg-light bg-opacity-50">
                                        <input class="form-check-input ms-0 me-2" type="checkbox"
                                               name="permissions[]"
                                               value="{{ $permission->name }}"
                                               id="perm-{{ $permission->id }}"
                                               {{ (old('permissions') !== null ? in_array($permission->name, old('permissions', [])) : $role->hasPermissionTo($permission->name)) ? 'checked' : '' }}>
                                        <label class="form-check-label text-dark" for="perm-{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-header1 btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Mettre à jour le rôle
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
