@extends('layouts.base')

@section('title', 'Détails de la dépense')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between mb-4">
                <h4 class="mb-sm-0 text-dark fw-bold">Détails de la dépense #{{ $depense->id }}</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('depenses.index') }}">Dépenses</a></li>
                        <li class="breadcrumb-item active">Détails</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-soft-primary text-primary px-3 py-2 rounded-pill font-size-13 fw-semibold">
                            {{ $depense->categorie_label }}
                        </span>
                        <h5 class="card-title mb-0 fw-bold text-dark">{{ $depense->titre }}</h5>
                    </div>
                    <div class="d-flex gap-2">
                        @can('edit depense')
                            <a href="{{ route('depenses.edit', $depense) }}" class="btn btn-sm btn-outline-warning rounded-pill px-3">
                                <i class="ri-edit-line me-1"></i>Modifier
                            </a>
                        @endcan
                        <a href="{{ route('depenses.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                            <i class="ri-arrow-left-line me-1"></i>Retour
                        </a>
                    </div>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small mb-1 fw-medium">Montant décaissé</div>
                                <div class="fs-3 fw-bold text-danger">{{ number_format($depense->montant, 0, ',', ' ') }} <small class="fs-6 text-muted">FCFA</small></div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="p-3 bg-light rounded-3">
                                <div class="text-muted small mb-1 fw-medium">Date d'opération</div>
                                <div class="fs-4 fw-bold text-dark">
                                    <i class="ri-calendar-event-line text-primary me-2"></i>{{ $depense->date_depense ? $depense->date_depense->translatedFormat('d F Y') : '-' }}
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">Mode de règlement</span>
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-2 rounded-pill font-size-13 fw-semibold">
                                <i class="ri-bank-card-line me-1"></i>{{ $depense->mode_paiement }}
                            </span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">Bénéficiaire / Prestataire</span>
                            <span class="fw-bold text-dark font-size-14">
                                {{ $depense->beneficiaire ?? 'Non spécifié' }}
                            </span>
                        </div>

                        <div class="col-12">
                            <span class="text-muted small d-block mb-1">Description / Observations</span>
                            <div class="p-3 bg-light rounded-3 text-dark">
                                {!! nl2br(e($depense->description ?? 'Aucune note ou observation complémentaire.')) !!}
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">Enregistré par</span>
                            <span class="fw-semibold text-dark">
                                <i class="ri-user-smile-line text-muted me-1"></i>{{ trim(($depense->user->prenom ?? '') . ' ' . ($depense->user->nom ?? '')) ?: ($depense->user->name ?? 'Système') }}
                            </span>
                        </div>

                        <div class="col-sm-6">
                            <span class="text-muted small d-block mb-1">Créé le</span>
                            <span class="text-muted">
                                {{ $depense->created_at ? $depense->created_at->format('d/m/Y à H:i') : '-' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title mb-0 fw-bold text-dark">
                        <i class="ri-file-text-line text-primary me-2 align-middle"></i>Pièce justificative
                    </h5>
                </div>
                <div class="card-body p-4 text-center">
                    @if($depense->justificatif)
                        @php
                            $ext = strtolower(pathinfo($depense->justificatif, PATHINFO_EXTENSION));
                        @endphp

                        @if(in_array($ext, ['jpg', 'jpeg', 'png', 'webp']))
                            <div class="mb-3 overflow-hidden rounded-3 border">
                                <img src="{{ asset($depense->justificatif) }}" alt="Justificatif" class="img-fluid" style="max-height: 280px; object-fit: contain;">
                            </div>
                        @else
                            <div class="py-4">
                                <i class="ri-file-pdf-fill text-danger" style="font-size: 4rem;"></i>
                                <p class="mt-2 text-muted fw-medium">Document PDF associé</p>
                            </div>
                        @endif

                        <div class="d-grid mt-3">
                            <a href="{{ asset($depense->justificatif) }}" target="_blank" class="btn btn-outline-primary rounded-pill">
                                <i class="ri-external-link-line me-1"></i>Ouvrir le document complet
                            </a>
                        </div>
                    @else
                        <div class="py-4 text-muted">
                            <i class="ri-inbox-archive-line" style="font-size: 3rem; opacity: 0.4;"></i>
                            <p class="mt-2 mb-0">Aucun justificatif ou reçu attaché à cette dépense.</p>
                        </div>
                    @endif
                </div>
            </div>

            @can('delete depense')
                <div class="card border-0 shadow-sm border-danger border-opacity-25" style="border-radius: 14px;">
                    <div class="card-body p-3 text-center">
                        <form id="delete-form-{{ $depense->id }}" action="{{ route('depenses.destroy', $depense) }}" method="POST" class="d-inline form-delete">
                            @csrf
                            @method('DELETE')
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-4 btn-delete" data-form-id="delete-form-{{ $depense->id }}">
                                <i class="ri-delete-bin-line me-1"></i>Supprimer cette dépense
                            </button>
                        </form>
                    </div>
                </div>
            @endcan
        </div>
    </div>
</div>
@endsection
