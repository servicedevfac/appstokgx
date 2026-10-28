
        <div class="main-menu" id="mainMenuSidebar">
            <!-- Brand Logo -->
            <div class="logo-box text-center position-relative" style="padding: 24px 0; height: auto;">
                <button type="button" class="btn-close-sidebar d-lg-none" id="sidebarCloseBtn" aria-label="Fermer le menu">
                    <i class="fas fa-times"></i>
                </button>
                <!-- Brand Logo Light -->
                <a href="{{url(path: 'dashboard')}}" class="logo-light">
                    <img src="{{url('assets/images/logo-light.png')}}" alt="logo" class="logo-lg" style="height: 60px; object-fit: contain;">
                    <img src="{{url('assets/images/logo-sm.png')}}" alt="small logo" class="logo-sm" style="height: 40px; object-fit: contain;">
                </a>

                <!-- Brand Logo Dark -->
                <a href="{{url(path: 'dashboard')}}" class="logo-dark">
                    <img src="{{url('assets/images/logo-dark.png')}}" alt="dark logo" class="logo-lg" style="height: 60px; object-fit: contain;">
                    <img src="{{url('assets/images/logo-sm.png')}}" alt="small logo" class="logo-sm" style="height: 40px; object-fit: contain;">
                </a>
            </div>
            <!--- Menu -->
            <div data-simplebar >
                <ul class="app-menu">
                    <li class="menu-title">Navigation</li>
                    <li class="menu-item">
                        <a href="{{route('dashboard')}}" class="menu-link waves-effect {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <span class="menu-icon"><i data-lucide="airplay "></i></span>
                            <span class="menu-text"> Tableau de bord </span>
                        </a>
                    </li>

                    <li class="menu-title">Gestion</li>

                    <li class="menu-item">
                    <a href="#Ventes" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('ventes.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="badge-dollar-sign"></i></span>
                        <span class="menu-text"> Ventes  </span>
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
                    <a href="#Produit" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('produits.*') ? 'active' : '' }}">
                        <span class="menu-icon"><i data-lucide="package-search"></i></span>
                        <span class="menu-text"> Produits  </span>
                        <span class="menu-arrow"></span>
                    </a>
                    <div class="collapse {{ request()->routeIs('produits.*') ? 'show' : '' }}" id="Produit">
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
                                        <span class="menu-text">Nouveau Stock</span>
                                    </a>
                                </li>
                                @endcan

                                @can('view stock')
                                <li class="menu-item">
                                    <a href="{{ route('mouvementStocks.index') }}" class="menu-link {{ request()->routeIs('mouvementStocks.index') ? 'active' : '' }}">
                                        <span class="menu-text">Liste Stocks</span>
                                    </a>
                                </li>
                                @endcan

                            </ul>
                        </div>
                    </li>
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
                    @if(Auth::user()->hasAnyRole(['super admin', 'Administrateur', 'Gestionnaire']) || Auth::user()->can('view depense') || Auth::user()->can('create depense'))
                    <li class="menu-item">
                        <a href="#menuDepenses" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('depenses.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i data-lucide="receipt"></i></span>
                            <span class="menu-text"> Dépenses </span>
                            <span class="menu-arrow"></span>
                        </a>
                        <div class="collapse {{ request()->routeIs('depenses.*') ? 'show' : '' }}" id="menuDepenses">
                            <ul class="sub-menu">
                                @if(Auth::user()->hasAnyRole(['super admin', 'Administrateur', 'Gestionnaire']) || Auth::user()->can('create depense'))
                                <li class="menu-item">
                                    <a href="{{ route('depenses.create') }}" class="menu-link {{ request()->routeIs('depenses.create') ? 'active' : '' }}">
                                        <span class="menu-text">Nouvelle Dépense</span>
                                    </a>
                                </li>
                                @endif
                                @if(Auth::user()->hasAnyRole(['super admin', 'Administrateur', 'Gestionnaire']) || Auth::user()->can('view depense'))
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

                    <li class="menu-title">Administration</li>

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

                @if (Auth::user()->hasRole('super admin'))

                    <li class="menu-item">
                        <a href="#role" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i data-lucide="scale"></i></span>
                            <span class="menu-text"> Rôles </span>
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
                    @endif

                    @if (Auth::user()->hasRole('super admin'))
                    <li class="menu-item">
                        <a href="#Permission" data-bs-toggle="collapse" class="menu-link waves-effect {{ request()->routeIs('permissions.*') ? 'active' : '' }}">
                            <span class="menu-icon"><i data-lucide="ruler"></i></span>
                            <span class="menu-text"> Permissions </span>
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
                    @endif
                </ul>
            </div>
        </div>
