@php
    $currentUser = Auth::user();
    $isPureSuperAdmin = $currentUser && $currentUser->isSuperAdmin() && !$currentUser->isProprietaire();
    $entrepriseActive = !$isPureSuperAdmin ? \App\Services\EntrepriseContext::current() : null;
    $abonnementActif = $entrepriseActive ? $entrepriseActive->abonnementActuel() : null;
@endphp
<div class="navbar-custom">
    <div class="topbar">
        <div class="topbar-menu d-flex align-items-center gap-2">
            <!-- Sidebar Menu Toggle Button -->
            <button type="button" class="button-toggle-menu waves-effect waves-dark rounded-circle text-gray" aria-label="Menu" id="buttonToggleMenu">
                <i class="mdi mdi-menu"></i>
            </button>

            <!-- Brand Logo (visible on mobile/tablet) -->
            <div class="logo-box topbar-brand-logo d-lg-none" style="height: 38px; display: flex; align-items: center; position: static;">
                <a href="{{ $isPureSuperAdmin ? route('superadmin.dashboard') : url('dashboard') }}" class="d-flex align-items-center">
                    <img src="{{url('assets/images/logo-darkc.png')}}" alt="STOKGX" style="height: 32px; max-height: 32px; width: auto; object-fit: contain;">
                </a>
            </div>

            @if($isPureSuperAdmin)
                <div class="d-none d-sm-flex align-items-center gap-2 ms-2">
                    <span class="badge bg-warning text-dark px-3 py-2 d-flex align-items-center gap-2 fw-bold">
                        <i class="fas fa-crown"></i>
                        <span>CONSOLE D'ADMINISTRATION SAAS GLOBALE</span>
                    </span>
                </div>
            @elseif($entrepriseActive)
                <div class="d-none d-sm-flex align-items-center gap-2 ms-2">
                    <span class="badge bg-light text-dark px-3 py-2 d-flex align-items-center gap-2">
                        <i class="fas fa-building text-primary"></i>
                        <span class="fw-semibold">{{ $entrepriseActive->nom }}</span>
                        <small class="text-muted">({{ $entrepriseActive->code }})</small>
                        @if($entrepriseActive->is_demo)
                            <span class="badge bg-warning text-dark">MODE DÉMO</span>
                        @endif
                    </span>

                    @if($abonnementActif)
                        <a href="{{ route('abonnement.index') }}" class="badge {{ $abonnementActif->badgeClass() }} px-2 py-2 text-decoration-none d-flex align-items-center gap-1" title="Voir les détails de mon abonnement">
                            <i class="fas fa-gem"></i>
                            <span>{{ $abonnementActif->nom_plan }} ({{ $abonnementActif->statutLabel() }})</span>
                        </a>
                    @endif

                    @if($entrepriseActive->is_demo)
                        <form action="{{ route('demo.reset') }}" method="POST" class="m-0" onsubmit="return confirm('Réinitialiser les données de démonstration ?');">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-warning fw-semibold">
                                <i class="fas fa-sync-alt me-1"></i> Réinitialiser Démo
                            </button>
                        </form>
                    @endif
                </div>
            @endif
        </div>

        <ul class="topbar-menu d-flex align-items-center gap-2">
            <li class="nav-link waves-effect waves-dark" id="theme-mode" role="button" tabindex="0" title="Passer en mode nuit" aria-label="Basculer le mode nuit / clair">
                <i class="bx bx-moon font-size-22"></i>
            </li>

            @auth
            <li class="nav-item dropdown d-flex align-items-center">
                <a class="dropdown-toggle btn btn-header1 d-flex align-items-center px-3 py-2"
                   href="#"
                   id="userDropdown"
                   onclick="event.preventDefault(); this.nextElementSibling.classList.toggle('show');"
                   style="margin-top: 10px;">
                    <span class="d-flex align-items-center justify-content-center rounded-circle bg-white fw-bold me-2" style="width:32px;height:32px;font-size:14px;color:#1a237e;">
                        {{ strtoupper(substr(Auth::user()->nom ?? 'U', 0, 1)) }}
                    </span>
                    <span class="d-none d-md-inline-block text-white fw-medium">
                        {{ Auth::user()->nom }}
                        <small class="d-block opacity-75" style="font-size: 10px;">{{ Auth::user()->role_libelle }}</small>
                    </span>
                    <i class="mdi mdi-chevron-down ms-1 text-white"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end profile-dropdown" aria-labelledby="userDropdown">
                    <li class="dropdown-header noti-title">
                        <h6 class="text-overflow m-0">{{ Auth::user()->nom }} ({{ Auth::user()->role_libelle }})</h6>
                    </li>

                    @if(!$isPureSuperAdmin)
                        <li>
                            <a href="{{ route('abonnement.index') }}" class="dropdown-item notify-item">
                                <i data-lucide="credit-card" class="font-size-16 me-2"></i>
                                <span>Mon abonnement</span>
                            </a>
                        </li>
                    @endif

                    <li>
                        <a href="{{ route('user-logins.index') }}" class="dropdown-item notify-item">
                            <i data-lucide="history" class="font-size-16 me-2"></i>
                            <span>Historique des connexions</span>
                        </a>
                    </li>

                    @if(Auth::user()->can('afficher journal audit') || Auth::user()->isSuperAdmin())
                        <li>
                            <a href="{{ route('audit-logs.index') }}" class="dropdown-item notify-item">
                                <i data-lucide="shield-check" class="font-size-16 me-2"></i>
                                <span>Journal d'audit</span>
                            </a>
                        </li>
                    @endif

                    @if(!$isPureSuperAdmin)
                        <li>
                            <a href="{{ route('admin.horaires.index') }}" class="dropdown-item notify-item">
                                <i data-lucide="settings" class="font-size-16 me-2"></i>
                                <span>Paramètres horaires</span>
                            </a>
                        </li>
                    @endif

                    <li><hr class="dropdown-divider"></li>

                    <li>
                        <form action="{{ route('logout') }}" method="POST" class="m-0 p-0">
                            @csrf
                            <button type="submit" class="dropdown-item notify-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            @endauth
        </ul>
    </div>
</div>
