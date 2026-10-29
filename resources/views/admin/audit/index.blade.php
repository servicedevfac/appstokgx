@extends('layouts.base')

@section('content')
<div class="row mt-3">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="page-title fw-bold mb-1"><i class="fas fa-shield-alt text-primary me-2"></i> Journal d'Audit Inaltérable</h3>
                <p class="text-muted mb-0">Traçabilité complète des opérations sensibles (RM-073, RM-074)</p>
            </div>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Horodatage</th>
                                <th>Entreprise</th>
                                <th>Utilisateur</th>
                                <th>Action</th>
                                <th>Description</th>
                                <th>IP</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $log)
                                <tr>
                                    <td><small>{{ $log->created_at?->format('d/m/Y H:i:s') }}</small></td>
                                    <td><span class="badge bg-light text-dark border">{{ $log->entreprise?->nom ?? 'Plateforme' }}</span></td>
                                    <td>{{ $log->user?->nom ?? 'Système' }}</td>
                                    <td><code>{{ $log->action }}</code></td>
                                    <td>{{ $log->description }}</td>
                                    <td><small class="text-muted">{{ $log->ip_address }}</small></td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted">Aucune entrée dans le journal d'audit.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-3">
                    {{ $logs->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

