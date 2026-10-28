@extends('layouts.base')

@section('content')
<div class="row mt-5">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0"><i class="fas fa-user me-2"></i> Détails de l'utilisateur</h3>
                <a href="{{ route('users.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour
                </a>
            </div>
            <div class="card-body">
                <dl class="row">
                    <dt class="col-sm-3">Nom</dt>
                    <dd class="col-sm-9">{{ $user->nom }}</dd>

                    <dt class="col-sm-3">Prénom</dt>
                    <dd class="col-sm-9">{{ $user->prenom }}</dd>

                    <dt class="col-sm-3">Adresse email</dt>
                    <dd class="col-sm-9">{{ $user->email }}</dd>

                    <dt class="col-sm-3">Numéro de téléphone</dt>
                    <dd class="col-sm-9">{{ $user->telephone }}</dd>

                    <dt class="col-sm-3">Rôle</dt>
                    <dd class="col-sm-9">
                        @foreach($user->getRoleNames() as $role)
                            <span class="badge bg-primary">{{ $role }}</span>
                        @endforeach
                    </dd>
                </dl>
            </div>
        </div>
    </div>

@endsection
