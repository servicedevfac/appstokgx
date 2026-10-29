@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1">Aperçu et Rapport d'Importation ({{ strtoupper($batch->type) }})</h3>
                <p class="text-muted mb-0">Fichier : {{ $batch->filename }} | Lignes totales : {{ $batch->total_rows }}</p>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('imports.index') }}" class="btn btn-outline-secondary">Retour</a>
                @if(in_array($batch->statut, ['apercu', 'preview']) && count($batch->preview_data ?? []) > 0)
                    <form action="{{ route('imports.confirm', $batch->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-check me-1"></i> Confirmer l'importation ({{ count($batch->preview_data) }} ligne(s) valide(s))
                        </button>
                    </form>
                @endif
            </div>
        </div>

        @if(!empty($batch->errors))
            <div class="card border-danger mb-4">
                <div class="card-header bg-danger text-white fw-bold">
                    Anomalies détectées ({{ count($batch->errors) }} ligne(s) rejetée(s))
                </div>
                <div class="card-body">
                    <ul class="mb-0">
                        @foreach($batch->errors as $err)
                            <li class="text-danger">
                                <strong>Ligne {{ $err['line'] }} :</strong> {{ implode(' ', $err['messages']) }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-bold">
                Lignes valides prêtes à être importées ({{ count($batch->preview_data ?? []) }})
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead class="table-light">
                            <tr>
                                <th>Ligne</th>
                                <th>Données</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($batch->preview_data ?? [] as $item)
                                <tr>
                                    <td>#{{ $item['line'] }}</td>
                                    <td><code>{{ json_encode($item['data'], JSON_UNESCAPED_UNICODE) }}</code></td>
                                </tr>
                            @empty
                                <tr><td colspan="2" class="text-center text-muted">Aucune ligne valide.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
