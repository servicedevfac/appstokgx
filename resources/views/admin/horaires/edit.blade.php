@extends('layouts.base')

@section('content')
<div class="row mt-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-header card-heade d-flex justify-content-between align-items-center">
                <h3 class="text-white m-0 d-flex align-items-center gap-2">
                    <i class="fas fa-clock"></i>
                    <span>Modification des horaires</span>
                </h3>
                <a href="{{ route('admin.horaires.index') }}" class="btn btn-header fw-bold shadow-sm">
                    <i class="fas fa-arrow-left me-1"></i> Retour aux horaires
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

                <form action="{{ route('admin.horaires.update') }}" method="POST" class="needs-validation" novalidate>
                    @csrf

                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th style="width: 30%;">Jour</th>
                                    <th style="width: 35%;">Heure d'ouverture</th>
                                    <th style="width: 35%;">Heure de fermeture</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($jours as $jour)
                                <tr>
                                    <td class="fw-semibold text-dark">{{ ucfirst($jour) }}</td>
                                    <td>
                                        <input type="time" class="form-control @error('heure_ouverture.'.$jour) is-invalid @enderror" name="heure_ouverture[{{ $jour }}]"
                                            value="{{ old('heure_ouverture.'.$jour, $horaires[$jour]->heure_ouverture ?? '') }}" required>
                                        @error('heure_ouverture.'.$jour)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                    <td>
                                        <input type="time" class="form-control @error('heure_fermeture.'.$jour) is-invalid @enderror" name="heure_fermeture[{{ $jour }}]"
                                            value="{{ old('heure_fermeture.'.$jour, $horaires[$jour]->heure_fermeture ?? '') }}" required>
                                        @error('heure_fermeture.'.$jour)
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex align-items-center justify-content-end gap-2 mt-4 pt-3 border-top">
                        <a href="{{ route('admin.horaires.index') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-times me-1"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-header1 btn-lg px-4">
                            <i class="fas fa-save me-2"></i> Enregistrer les horaires
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
