        <div class="main-menu" id="mainMenuSidebar">
            <!-- Brand Logo -->
            <div class="logo-box text-center position-relative" style="padding: 24px 0; height: auto;">
                <button type="button" class="btn-close-sidebar d-lg-none" id="sidebarCloseBtn" aria-label="Fermer le menu">
                    <i class="fas fa-times"></i>
                </button>
                <!-- Brand Logo Light -->
                <a href="{{ Auth::check() && Auth::user()->isSuperAdmin() && !Auth::user()->isProprietaire() ? route('superadmin.dashboard') : url('dashboard') }}" class="logo-light">
                    <img src="{{url('assets/images/logo-light.png')}}" alt="logo" class="logo-lg" style="height: 60px; object-fit: contain;">
                    <img src="{{url('assets/images/logo-sm.png')}}" alt="small logo" class="logo-sm" style="height: 40px; object-fit: contain;">
                </a>

                <!-- Brand Logo Dark -->
                <a href="{{ Auth::check() && Auth::user()->isSuperAdmin() && !Auth::user()->isProprietaire() ? route('superadmin.dashboard') : url('dashboard') }}" class="logo-dark">
                    <img src="{{url('assets/images/logo-dark.png')}}" alt="dark logo" class="logo-lg" style="height: 60px; object-fit: contain;">
                    <img src="{{url('assets/images/logo-sm.png')}}" alt="small logo" class="logo-sm" style="height: 40px; object-fit: contain;">
                </a>
            </div>
            <!--- Menu -->
            <div data-simplebar>
                @auth
                <ul class="app-menu">
                    @if(Auth::user()->isSuperAdmin() && !Auth::user()->isProprietaire())
                        {{-- ==========================================================
                             MENU EXCLUSIF SUPER ADMINISTRATEUR (PILOTAGE SAAS GLOBAL)
                             ========================================================== --}}
                        <li class="menu-title">Pilotage SaaS Global</li>

                        <li class="menu-item">
                            <a href="{{ route('superadmin.dashboard') }}" class="menu-link waves-effect {{ request()->routeIs('superadmin.dashboard') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="layout-dashboard"></i></span>
                                <span class="menu-text"> Tableau de bord SaaS </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{ route('superadmin.entreprises.index') }}" class="menu-link waves-effect {{ request()->routeIs('superadmin.entreprises.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="building-2"></i></span>
                                <span class="menu-text"> Entreprises Clientes </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{ route('superadmin.proprietaires.index') }}" class="menu-link waves-effect {{ request()->routeIs('superadmin.proprietaires.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="crown"></i></span>
                                <span class="menu-text"> Propriétaires SaaS </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{ route('superadmin.abonnements.index') }}" class="menu-link waves-effect {{ request()->routeIs('superadmin.abonnements.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="credit-card"></i></span>
                                <span class="menu-text"> Abonnements & Plans </span>
                            </a>
                        </li>

                        <li class="menu-title">Sécurité & Accès Plateforme</li>

                        <li class="menu-item">
                            <a href="#role" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="scale"></i></span>
                                <span class="menu-text"> Rôles Système </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('roles.*') ? 'show' : '' }}" id="role">
                                <ul class="sub-menu">
                                    @can('create role')
                                    <li class="menu-item">
                                        <a href="{{route('roles.create')}}" class="menu-link {{ request()->routeIs('roles.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouveau rôle</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view role')
                                    <li class="menu-item">
                                        <a href="{{route('roles.index')}}" class="menu-link {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste rôles</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item">
                            <a href="#Permission" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="ruler"></i></span>
                                <span class="menu-text"> Permissions Système </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('permissions.*') ? 'show' : '' }}" id="Permission">
                                <ul class="sub-menu">
                                    @can('create permission')
                                    <li class="menu-item">
                                        <a href="{{route('permissions.create')}}" class="menu-link {{ request()->routeIs('permissions.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouvelle permission</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view permission')
                                    <li class="menu-item">
                                        <a href="{{route('permissions.index')}}" class="menu-link {{ request()->routeIs('permissions.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste permissions</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item">
                            <a href="{{ route('audit-logs.index') }}" class="menu-link waves-effect {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="shield-alert"></i></span>
                                <span class="menu-text"> Journal d'audit SaaS </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="{{ route('user-logins.index') }}" class="menu-link waves-effect {{ request()->routeIs('user-logins.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="history"></i></span>
                                <span class="menu-text"> Connexions Plateforme </span>
                            </a>
                        </li>
                    @else
                        {{-- ==========================================================
                             MENU ESPACE ENTREPRISE (PROPRIÉTAIRE, ADMIN, GESTIONNAIRE, VENDEUR)
                             ========================================================== --}}
                        <li class="menu-item">
                            <a href="{{route('dashboard')}}" class="menu-link waves-effect {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="airplay"></i></span>
                                <span class="menu-text"> Tableau de bord </span>
                            </a>
                        </li>


                        <li class="menu-title">Gestion Commerciale & Stock</li>

                        <li class="menu-item">
                            <a href="#Ventes" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="badge-dollar-sign"></i></span>
                                <span class="menu-text"> Ventes </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('ventes.*') ? 'show' : '' }}" id="Ventes">
                                <ul class="sub-menu">
                                    @can('create vente')
                                    <li class="menu-item">
                                        <a href="{{ route('ventes.create') }}" class="menu-link {{ request()->routeIs('ventes.create') ? 'active' : '' }}">
                                            <span class="menu-text">Créer Vente</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view vente')
                                    <li class="menu-item">
                                        <a href="{{ route('ventes.index') }}" class="menu-link {{ request()->routeIs('ventes.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste Ventes</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item">
                            <a href="#Produit" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('produits.*') || request()->routeIs('categories.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="package-search"></i></span>
                                <span class="menu-text"> Produits & Catégories </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('produits.*') || request()->routeIs('categories.*') ? 'show' : '' }}" id="Produit">
                                <ul class="sub-menu">
                                    @can('create produit')
                                    <li class="menu-item">
                                        <a href="{{ route('produits.create') }}" class="menu-link {{ request()->routeIs('produits.create') ? 'active' : '' }}">
                                            <span class="menu-text">Créer Produit</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view produit')
                                    <li class="menu-item">
                                        <a href="{{ route('produits.index') }}" class="menu-link {{ request()->routeIs('produits.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste Produits</span>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('categories.index') }}" class="menu-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                                            <span class="menu-text">Catégories</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        <li class="menu-item">
                            <a href="#Stocks" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('mouvementStocks.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="warehouse"></i></span>
                                <span class="menu-text">Stocks</span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('mouvementStocks.*') ? 'show' : '' }}" id="Stocks">
                                <ul class="sub-menu">
                                    @can('create stock')
                                    <li class="menu-item">
                                        <a href="{{ route('mouvementStocks.create') }}" class="menu-link {{ request()->routeIs('mouvementStocks.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouveau Mouvement</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view stock')
                                    <li class="menu-item">
                                        <a href="{{ route('mouvementStocks.index') }}" class="menu-link {{ request()->routeIs('mouvementStocks.index') ? 'active' : '' }}">
                                            <span class="menu-text">Journal des Stocks</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        @if(Auth::user()->can('afficher fournisseurs') || Auth::user()->can('afficher achats'))
                        <li class="menu-item">
                            <a href="#menuAchats" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('fournisseurs.*') || request()->routeIs('achats.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="truck"></i></span>
                                <span class="menu-text"> Fournisseurs & Achats </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('fournisseurs.*') || request()->routeIs('achats.*') ? 'show' : '' }}" id="menuAchats">
                                <ul class="sub-menu">
                                    <li class="menu-item">
                                        <a href="{{ route('fournisseurs.index') }}" class="menu-link {{ request()->routeIs('fournisseurs.*') ? 'active' : '' }}">
                                            <span class="menu-text">Fournisseurs</span>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('achats.index') }}" class="menu-link {{ request()->routeIs('achats.index') ? 'active' : '' }}">
                                            <span class="menu-text">Achats & Dettes</span>
                                        </a>
                                    </li>
                                    @can('créer achat')
                                    <li class="menu-item">
                                        <a href="{{ route('achats.create') }}" class="menu-link {{ request()->routeIs('achats.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouvel Achat</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>
                        @endif

                        <li class="menu-item">
                            <a href="#menuClients" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('clients.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="users-round"></i></span>
                                <span class="menu-text"> Clients </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('clients.*') ? 'show' : '' }}" id="menuClients">
                                <ul class="sub-menu">
                                    @can('create client')
                                    <li class="menu-item">
                                        <a href="{{ route('clients.create') }}" class="menu-link {{ request()->routeIs('clients.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouveau Client</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view client')
                                    <li class="menu-item">
                                        <a href="{{ route('clients.index') }}" class="menu-link {{ request()->routeIs('clients.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste Clients</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        @if(Auth::user()->hasAnyRole(['Propriétaire', 'Administrateur', 'Gestionnaire']) || Auth::user()->can('view depense') || Auth::user()->can('create depense'))
                        <li class="menu-item">
                            <a href="#menuDepenses" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('depenses.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="receipt"></i></span>
                                <span class="menu-text"> Dépenses & Trésorerie </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('depenses.*') ? 'show' : '' }}" id="menuDepenses">
                                <ul class="sub-menu">
                                    @if(Auth::user()->hasAnyRole(['Propriétaire', 'Administrateur', 'Gestionnaire']) || Auth::user()->can('create depense'))
                                    <li class="menu-item">
                                        <a href="{{ route('depenses.create') }}" class="menu-link {{ request()->routeIs('depenses.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouvelle Dépense</span>
                                        </a>
                                    </li>
                                    @endif
                                    @if(Auth::user()->hasAnyRole(['Propriétaire', 'Administrateur', 'Gestionnaire']) || Auth::user()->can('view depense'))
                                    <li class="menu-item">
                                        <a href="{{ route('depenses.index') }}" class="menu-link {{ request()->routeIs('depenses.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste Dépenses</span>
                                        </a>
                                    </li>
                                    @endif
                                </ul>
                            </div>
                        </li>
                        @endif

                        @if(Auth::user()->can('afficher employés') || Auth::user()->can('afficher avances salaires'))
                        <li class="menu-item">
                            <a href="#menuEmployes" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('employes.*') || request()->routeIs('avances-salaires.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="briefcase"></i></span>
                                <span class="menu-text"> Employés & Avances </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('employes.*') || request()->routeIs('avances-salaires.*') ? 'show' : '' }}" id="menuEmployes">
                                <ul class="sub-menu">
                                    <li class="menu-item">
                                        <a href="{{ route('employes.index') }}" class="menu-link {{ request()->routeIs('employes.*') ? 'active' : '' }}">
                                            <span class="menu-text">Employés</span>
                                        </a>
                                    </li>
                                    <li class="menu-item">
                                        <a href="{{ route('avances-salaires.index') }}" class="menu-link {{ request()->routeIs('avances-salaires.*') ? 'active' : '' }}">
                                            <span class="menu-text">Avances sur salaire</span>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                        @endif

                        @can('importer données')
                        <li class="menu-item">
                            <a href="{{ route('imports.index') }}" class="menu-link waves-effect {{ request()->routeIs('imports.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="file-up"></i></span>
                                <span class="menu-text"> Importation Excel/CSV </span>
                            </a>
                        </li>
                        @endcan

                        <li class="menu-title">Administration Entreprise</li>

                        @if(Auth::user()->isProprietaire() || Auth::user()->can('gérer entreprises'))
                        <li class="menu-item">
                            <a href="{{ route('entreprises.index') }}" class="menu-link waves-effect {{ request()->routeIs('entreprises.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="building-2"></i></span>
                                <span class="menu-text"> Mon Entreprise </span>
                            </a>
                        </li>
                        @endif

                        <li class="menu-item">
                            <a href="{{ route('abonnement.index') }}" class="menu-link waves-effect {{ request()->routeIs('abonnement.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="credit-card"></i></span>
                                <span class="menu-text"> Mon Abonnement </span>
                            </a>
                        </li>

                        <li class="menu-item">
                            <a href="#menuUsers" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('users.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="users"></i></span>
                                <span class="menu-text"> Utilisateurs </span>
                                <span class="menu-arrow"></span>
                            </a>
                            <div class="collapse {{ request()->routeIs('users.*') ? 'show' : '' }}" id="menuUsers">
                                <ul class="sub-menu">
                                    @can('create utilisateur')
                                    <li class="menu-item">
                                        <a href="{{route('users.create')}}" class="menu-link {{ request()->routeIs('users.create') ? 'active' : '' }}">
                                            <span class="menu-text">Nouveau utilisateur</span>
                                        </a>
                                    </li>
                                    @endcan
                                    @can('view utilisateur')
                                    <li class="menu-item">
                                        <a href="{{route('users.index')}}" class="menu-link {{ request()->routeIs('users.index') ? 'active' : '' }}">
                                            <span class="menu-text">Liste utilisateurs</span>
                                        </a>
                                    </li>
                                    @endcan
                                </ul>
                            </div>
                        </li>

                        @if(Auth::user()->can('afficher journal audit'))
                        <li class="menu-item">
                            <a href="{{ route('audit-logs.index') }}" class="menu-link waves-effect {{ request()->routeIs('audit-logs.*') ? 'active' : '' }}">
                                <span class="menu-icon"><i data-lucide="shield-alert"></i></span>
                                <span class="menu-text"> Journal d'audit </span>
                            </a>
                        </li>
                        @endif
                    @endif
                </ul>
                @endauth
            </div>
        </div>
