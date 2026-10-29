<!DOCTYPE html>
<html lang="fr" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>STOCKBOX — Logiciel SaaS de Gestion de Stock & Approvisionnements</title>
    <meta name="description" content="Centralisez vos produits, maîtrisez vos entrées et sorties, anticipez les ruptures et prenez de meilleures décisions grâce à STOCKBOX.">
    <meta name="keywords" content="gestion de stock, saas stock, inventaire, mouvements stock, alertes rupture, gestion approvisionnements">
    
    <!-- Open Graph / Meta Partage -->
    <meta property="og:type" content="website">
    <meta property="og:title" content="STOCKBOX — Votre stock sous contrôle. Votre entreprise en avance.">
    <meta property="og:description" content="Plateforme SaaS moderne pour piloter vos stocks, catalogues, mouvements et alertes de réapprovisionnement.">
    <meta property="og:url" content="{{ url('/') }}">
    <meta property="og:image" content="{{ url('assets/images/logo-sm.png') }}">

    <link rel="shortcut icon" href="{{ url('assets/images/logo-sm.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 & FontAwesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ url('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --sb-night: #0F172A;
            --sb-blue: #2563EB;
            --sb-blue-light: #60A5FA;
            --sb-blue-subtle: #EFF6FF;
            --sb-bg: #F8FAFC;
            --sb-white: #FFFFFF;
            --sb-text: #111827;
            --sb-muted: #64748B;
            --sb-green: #16A34A;
            --sb-orange: #F59E0B;
            --sb-border: #E2E8F0;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--sb-bg);
            color: var(--sb-text);
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Navbar Blur */
        .navbar-stockbox {
            background: rgba(255, 255, 255, 0.92);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.85);
            transition: all 0.25s ease;
        }

        .nav-link-sb {
            color: var(--sb-muted);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 0.85rem;
            border-radius: 0.5rem;
            transition: color 0.15s ease, background-color 0.15s ease;
            text-decoration: none;
        }
        .nav-link-sb:hover {
            color: var(--sb-blue);
            background-color: var(--sb-blue-subtle);
        }

        /* Buttons */
        .btn-sb-primary {
            background-color: var(--sb-blue);
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: 0.65rem;
            border: 1px solid var(--sb-blue);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.25);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-sb-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
            transform: translateY(-1px);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        .btn-sb-secondary {
            background-color: #ffffff;
            color: var(--sb-text);
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: 0.65rem;
            border: 1px solid var(--sb-border);
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.04);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-sb-secondary:hover {
            background-color: #f1f5f9;
            color: var(--sb-blue);
            border-color: #cbd5e1;
            transform: translateY(-1px);
        }

        .btn-sb-night {
            background-color: var(--sb-night);
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: 0.65rem;
            border: 1px solid var(--sb-night);
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-sb-night:hover {
            background-color: #1e293b;
            color: #ffffff;
            transform: translateY(-1px);
        }

        /* Hero */
        .hero-section {
            padding: 4.5rem 0 5rem;
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
            position: relative;
            overflow: hidden;
        }
        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.35rem 0.9rem;
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--sb-blue);
            background-color: var(--sb-blue-subtle);
            border: 1px solid rgba(37, 99, 235, 0.2);
            border-radius: 9999px;
            margin-bottom: 1.25rem;
        }
        .hero-title {
            font-size: 2.75rem;
            font-weight: 800;
            line-height: 1.18;
            letter-spacing: -0.025em;
            color: var(--sb-night);
            margin-bottom: 1.25rem;
        }
        @media (min-width: 992px) {
            .hero-title {
                font-size: 3.4rem;
            }
        }
        .text-accent-blue {
            color: var(--sb-blue);
            position: relative;
            display: inline-block;
        }
        .text-accent-blue::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 0;
            width: 100%;
            height: 6px;
            background: rgba(96, 165, 250, 0.3);
            border-radius: 3px;
            z-index: -1;
        }

        .hero-subtitle {
            font-size: 1.12rem;
            color: var(--sb-muted);
            margin-bottom: 2rem;
            max-width: 540px;
        }

        /* Mockup Dashboard Window */
        .dashboard-mockup {
            background: #ffffff;
            border-radius: 1.25rem;
            border: 1px solid var(--sb-border);
            box-shadow: 0 25px 50px -12px rgba(15, 23, 42, 0.16), 0 0 0 1px rgba(15, 23, 42, 0.05);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s ease;
        }
        .dashboard-mockup-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--sb-border);
            padding: 0.75rem 1.25rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .window-dots {
            display: flex;
            gap: 6px;
        }
        .window-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
        }
        .dot-red { background: #ef4444; }
        .dot-amber { background: #f59e0b; }
        .dot-green { background: #10b981; }

        .dashboard-kpi-card {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            padding: 1rem 1.15rem;
            transition: box-shadow 0.2s ease;
        }
        .dashboard-kpi-card:hover {
            box-shadow: 0 6px 14px rgba(15, 23, 42, 0.05);
        }

        /* Floating Alert Cards */
        .floating-card-1 {
            position: absolute;
            top: -20px;
            right: -20px;
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            padding: 0.75rem 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: floatSlow 4s ease-in-out infinite alternate;
        }
        .floating-card-2 {
            position: absolute;
            bottom: -22px;
            left: -20px;
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            padding: 0.75rem 1rem;
            box-shadow: 0 12px 28px rgba(15, 23, 42, 0.12);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: floatSlow 4.5s ease-in-out infinite alternate-reverse;
        }
        @keyframes floatSlow {
            0% { transform: translateY(0); }
            100% { transform: translateY(-8px); }
        }

        /* Section Styling */
        .section-padding {
            padding: 5.5rem 0;
        }
        .section-header {
            margin-bottom: 3.5rem;
        }
        .section-badge {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
            color: var(--sb-blue);
            margin-bottom: 0.6rem;
            display: block;
        }
        .section-title {
            font-size: 2.25rem;
            font-weight: 800;
            color: var(--sb-night);
            letter-spacing: -0.02em;
            line-height: 1.25;
        }
        .section-desc {
            font-size: 1.08rem;
            color: var(--sb-muted);
            max-width: 620px;
            margin: 0.75rem auto 0;
        }

        /* Cards */
        .sb-card {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 1rem;
            padding: 1.75rem;
            transition: all 0.25s ease;
            height: 100%;
        }
        .sb-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
            border-color: #cbd5e1;
        }

        .icon-box-primary {
            width: 48px;
            height: 48px;
            border-radius: 0.75rem;
            background: var(--sb-blue-subtle);
            color: var(--sb-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
        }

        /* Interactive Tabs */
        .tab-btn {
            padding: 0.65rem 1.25rem;
            border-radius: 0.65rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--sb-muted);
            background: transparent;
            border: 1px solid transparent;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .tab-btn.active {
            background: #ffffff;
            color: var(--sb-blue);
            border-color: var(--sb-border);
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.05);
        }

        /* Steps */
        .step-number {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--sb-blue-subtle);
            color: var(--sb-blue);
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            margin-bottom: 1.25rem;
            border: 2px solid rgba(37, 99, 235, 0.2);
        }

        /* Pricing Card */
        .pricing-card {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 1.25rem;
            padding: 2rem;
            transition: all 0.25s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .pricing-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 16px 36px rgba(15, 23, 42, 0.08);
        }
        .pricing-card.featured {
            border: 2px solid var(--sb-blue);
            box-shadow: 0 20px 42px rgba(37, 99, 235, 0.12);
        }
        .featured-badge {
            position: absolute;
            top: -13px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--sb-blue);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.05em;
            padding: 0.3rem 0.9rem;
            border-radius: 9999px;
            text-transform: uppercase;
        }

        /* FAQ Accordion */
        .faq-item {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            margin-bottom: 0.85rem;
            overflow: hidden;
            transition: border-color 0.2s ease;
        }
        .faq-item:hover {
            border-color: #cbd5e1;
        }
        .faq-button {
            width: 100%;
            padding: 1.15rem 1.4rem;
            text-align: left;
            background: transparent;
            border: none;
            font-weight: 600;
            font-size: 1.05rem;
            color: var(--sb-night);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            cursor: pointer;
        }
        .faq-answer {
            padding: 0 1.4rem 1.25rem;
            color: var(--sb-muted);
            font-size: 0.96rem;
            line-height: 1.65;
        }

        /* Final CTA */
        .final-cta-section {
            background: radial-gradient(circle at top right, #1e3a8a 0%, #0F172A 70%);
            color: #ffffff;
            padding: 5.5rem 0;
            position: relative;
            overflow: hidden;
        }

        /* Footer */
        .footer-stockbox {
            background: #090e17;
            color: #94a3b8;
            padding: 4.5rem 0 2rem;
            font-size: 0.92rem;
        }
        .footer-title {
            color: #ffffff;
            font-weight: 700;
            font-size: 0.95rem;
            margin-bottom: 1.25rem;
            letter-spacing: 0.02em;
        }
        .footer-link {
            color: #94a3b8;
            text-decoration: none;
            display: block;
            margin-bottom: 0.65rem;
            transition: color 0.15s ease;
        }
        .footer-link:hover {
            color: #ffffff;
        }

        /* Accessibility: Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            * {
                animation: none !important;
                transition: none !important;
            }
        }
    </style>
</head>
<body x-data="{ mobileMenuOpen: false, activeTab: 'dashboard', billing: 'mensuel', openFaq: null }">

    {{-- 1. BARRE DE NAVIGATION STICKY BLUR --}}
    <nav class="navbar navbar-expand-lg navbar-stockbox sticky-top py-3" id="accueil">
        <div class="container">
            <!-- Logo STOCKBOX -->
            <a class="d-flex align-items-center gap-2 text-decoration-none" href="{{ route('landing') }}">
                <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2" style="width: 38px; height: 38px; background-color: var(--sb-blue) !important;">
                    <i data-lucide="package" style="width: 22px; height: 22px;"></i>
                </div>
                <div class="d-flex flex-column leading-none">
                    <span class="fw-bold fs-4 tracking-tight" style="color: var(--sb-night); line-height: 1.1;">STOCK<span style="color: var(--sb-blue);">BOX</span></span>
                    <span class="text-uppercase fw-semibold" style="font-size: 0.65rem; letter-spacing: 0.12em; color: var(--sb-muted);">Inventory SaaS</span>
                </div>
            </a>

            <!-- Bouton Hamburger Mobile -->
            <button class="navbar-toggler border-0 shadow-none" type="button" @click="mobileMenuOpen = !mobileMenuOpen" aria-label="Menu de navigation">
                <i data-lucide="menu" style="width: 26px; height: 26px; color: var(--sb-night);"></i>
            </button>

            <!-- Liens de navigation Desktop -->
            <div class="collapse navbar-collapse" :class="{ 'show': mobileMenuOpen }" id="navbarMain">
                <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1 mt-3 mt-lg-0">
                    <li class="nav-item"><a class="nav-link-sb" href="#accueil" @click="mobileMenuOpen = false">Accueil</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#presentation" @click="mobileMenuOpen = false">Plateforme</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#fonctionnalites" @click="mobileMenuOpen = false">Fonctionnalités</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#logiciel" @click="mobileMenuOpen = false">Aperçu Logiciel</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#comment-ca-marche" @click="mobileMenuOpen = false">Comment ça marche</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#avantages" @click="mobileMenuOpen = false">Avantages</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#tarifs" @click="mobileMenuOpen = false">Tarifs</a></li>
                    <li class="nav-item"><a class="nav-link-sb" href="#faq" @click="mobileMenuOpen = false">FAQ</a></li>
                </ul>

                <!-- Actions Droite -->
                <div class="d-flex align-items-center gap-2 mt-3 mt-lg-0">
                    @auth
                        <a href="{{ Auth::user()->isSuperAdmin() && !Auth::user()->isProprietaire() ? route('superadmin.dashboard') : route('dashboard') }}" class="btn-sb-primary">
                            <i data-lucide="layout-dashboard" style="width: 18px; height: 18px;"></i>
                            <span>Mon Espace</span>
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="btn-sb-secondary">
                            <i data-lucide="log-in" style="width: 17px; height: 17px;"></i>
                            <span>Connexion</span>
                        </a>
                        <form action="{{ route('demo.login') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn-sb-primary">
                                <span>Commencer gratuitement</span>
                                <i data-lucide="arrow-right" style="width: 17px; height: 17px;"></i>
                            </button>
                        </form>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- 2. SECTION HERO --}}
    <section class="hero-section">
        <div class="container">
            <div class="row align-items-center g-5">
                <!-- Colonne Gauche : Copywriting & CTAs -->
                <div class="col-lg-6">
                    <div class="hero-badge">
                        <i data-lucide="sparkles" style="width: 15px; height: 15px;"></i>
                        <span>La gestion de stock, simplement.</span>
                    </div>

                    <h1 class="hero-title">
                        Votre <span class="text-accent-blue">stock sous contrôle</span>.<br>Votre entreprise en avance.
                    </h1>

                    <p class="hero-subtitle">
                        Centralisez vos produits, maîtrisez vos entrées et sorties, anticipez les ruptures et prenez de meilleures décisions grâce à une gestion de stock simple, rapide et intelligente.
                    </p>

                    <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                        <form action="{{ route('demo.login') }}" method="POST" class="d-inline m-0">
                            @csrf
                            <button type="submit" class="btn-sb-primary" style="padding: 0.8rem 1.6rem; font-size: 1.05rem;">
                                <span>Commencer gratuitement</span>
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </form>
                        <a href="#logiciel" class="btn-sb-secondary" style="padding: 0.8rem 1.4rem; font-size: 1.05rem;">
                            <i data-lucide="play-circle" style="width: 20px; height: 20px; color: var(--sb-blue);"></i>
                            <span>Découvrir la plateforme</span>
                        </a>
                    </div>

                    <!-- 3 Éléments Rassurants Vérifiables -->
                    <div class="d-flex flex-wrap gap-4 pt-2 border-top border-slate-200">
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="check-circle-2" style="width: 18px; height: 18px; color: var(--sb-green);"></i>
                            <span class="small fw-semibold text-secondary">Gestion simplifiée</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="check-circle-2" style="width: 18px; height: 18px; color: var(--sb-green);"></i>
                            <span class="small fw-semibold text-secondary">Suivi en temps réel</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i data-lucide="check-circle-2" style="width: 18px; height: 18px; color: var(--sb-green);"></i>
                            <span class="small fw-semibold text-secondary">Données centralisées</span>
                        </div>
                    </div>
                </div>

                <!-- Colonne Droite : Maquette Réaliste Dashboard STOCKBOX -->
                <div class="col-lg-6 position-relative">
                    <!-- Carte Flottante 1 (Haut Droite) : Alerte Réapprovisionnement -->
                    <div class="floating-card-1 d-none d-sm-flex">
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background: #FEF3C7; color: var(--sb-orange);">
                            <i data-lucide="alert-triangle" style="width: 19px; height: 19px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark">Alerte Seuil Atteint</div>
                            <div class="text-muted" style="font-size: 0.78rem;">Huile Végétale 5L : reste 4 unités</div>
                        </div>
                    </div>

                    <!-- Carte Flottante 2 (Bas Gauche) : Mouvement Validé -->
                    <div class="floating-card-2 d-none d-sm-flex">
                        <div class="d-flex align-items-center justify-content-center rounded-circle" style="width: 38px; height: 38px; background: #DCFCE7; color: var(--sb-green);">
                            <i data-lucide="arrow-down-left" style="width: 19px; height: 19px;"></i>
                        </div>
                        <div>
                            <div class="fw-bold small text-dark">Réception Validée</div>
                            <div class="text-muted" style="font-size: 0.78rem;">+50 Sacs Riz Parfumé (Fournisseur SITA)</div>
                        </div>
                    </div>

                    <!-- Fenêtre Dashboard Principale -->
                    <div class="dashboard-mockup">
                        <!-- En-tête de fenêtre -->
                        <div class="dashboard-mockup-header">
                            <div class="d-flex align-items-center gap-2">
                                <div class="window-dots">
                                    <div class="window-dot dot-red"></div>
                                    <div class="window-dot dot-amber"></div>
                                    <div class="window-dot dot-green"></div>
                                </div>
                                <span class="text-muted small ms-2 fw-medium">app.stockbox.io/dashboard</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge rounded-pill bg-success-subtle text-success small fw-semibold px-2 py-1">
                                    <i data-lucide="activity" style="width: 12px; height: 12px;" class="me-1"></i>En direct
                                </span>
                            </div>
                        </div>

                        <!-- Corps du Dashboard Réaliste -->
                        <div class="p-3 p-sm-4 bg-white">
                            <!-- 3 Cartes KPIs Principales -->
                            <div class="row g-3 mb-3">
                                <div class="col-4">
                                    <div class="dashboard-kpi-card">
                                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Valeur du stock</div>
                                        <div class="fw-bold fs-6 mt-1 text-dark">18 450 000 <span style="font-size: 0.7rem;">FCFA</span></div>
                                        <div class="text-success small fw-semibold mt-1" style="font-size: 0.72rem;">
                                            <i data-lucide="trending-up" style="width: 12px; height: 12px;" class="d-inline"></i> +8.2%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="dashboard-kpi-card">
                                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Produits en stock</div>
                                        <div class="fw-bold fs-6 mt-1 text-dark">428 <span style="font-size: 0.7rem; font-weight: 400;" class="text-muted">réf.</span></div>
                                        <div class="text-primary small fw-semibold mt-1" style="font-size: 0.72rem;">
                                            14 catégories
                                        </div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="dashboard-kpi-card" style="border-left: 3px solid var(--sb-orange);">
                                        <div class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Alertes de stock</div>
                                        <div class="fw-bold fs-6 mt-1 text-warning" style="color: var(--sb-orange) !important;">3 <span style="font-size: 0.7rem; font-weight: 400;" class="text-muted">critiques</span></div>
                                        <div class="text-danger small fw-semibold mt-1" style="font-size: 0.72rem;">
                                            À commander
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Graphique SVG Évolution des mouvements -->
                            <div class="border rounded-3 p-3 mb-3 bg-light bg-opacity-50">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="small fw-bold text-dark">Flux des entrées et sorties (7 derniers jours)</span>
                                    <span class="text-muted" style="font-size: 0.75rem;">Mouvements consolidés</span>
                                </div>
                                <div class="position-relative" style="height: 110px;">
                                    <svg viewBox="0 0 500 120" class="w-100 h-100" preserveAspectRatio="none">
                                        <defs>
                                            <linearGradient id="gradEntrees" x1="0%" y1="0%" x2="0%" y2="100%">
                                                <stop offset="0%" style="stop-color: #2563EB; stop-opacity: 0.35" />
                                                <stop offset="100%" style="stop-color: #2563EB; stop-opacity: 0.0" />
                                            </linearGradient>
                                        </defs>
                                        <!-- Lignes de repère -->
                                        <line x1="0" y1="20" x2="500" y2="20" stroke="#E2E8F0" stroke-dasharray="3,3" />
                                        <line x1="0" y1="60" x2="500" y2="60" stroke="#E2E8F0" stroke-dasharray="3,3" />
                                        <line x1="0" y1="100" x2="500" y2="100" stroke="#E2E8F0" />
                                        <!-- Aire courbe -->
                                        <path d="M 0,90 Q 70,30 140,65 T 280,35 T 420,50 T 500,20 L 500,110 L 0,110 Z" fill="url(#gradEntrees)" />
                                        <!-- Courbe principale Entrées -->
                                        <path d="M 0,90 Q 70,30 140,65 T 280,35 T 420,50 T 500,20" fill="none" stroke="#2563EB" stroke-width="2.5" />
                                        <!-- Points de données -->
                                        <circle cx="140" cy="65" r="3.5" fill="#2563EB" stroke="#ffffff" stroke-width="2" />
                                        <circle cx="280" cy="35" r="3.5" fill="#2563EB" stroke="#ffffff" stroke-width="2" />
                                        <circle cx="500" cy="20" r="4" fill="#2563EB" stroke="#ffffff" stroke-width="2" />
                                    </svg>
                                </div>
                                <div class="d-flex justify-content-between text-muted" style="font-size: 0.7rem;">
                                    <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
                                </div>
                            </div>

                            <!-- Derniers Mouvements & Taux de Disponibilité -->
                            <div class="row g-2 align-items-center">
                                <div class="col-8">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-2 border bg-white">
                                        <div class="d-flex align-items-center justify-content-center rounded bg-primary-subtle text-primary" style="width: 28px; height: 28px;">
                                            <i data-lucide="package-plus" style="width: 15px; height: 15px;"></i>
                                        </div>
                                        <div class="small flex-grow-1 text-truncate">
                                            <span class="fw-semibold text-dark">Lait Bonnet Rouge 410g</span>
                                            <span class="text-muted d-block" style="font-size: 0.7rem;">Il y a 12 min • Entrée +24 cartons</span>
                                        </div>
                                        <span class="badge bg-success-subtle text-success small">+24</span>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2 rounded-2 border bg-white text-center">
                                        <div class="text-muted" style="font-size: 0.7rem;">Taux Disponibilité</div>
                                        <div class="fw-bold small text-success">97.8%</div>
                                        <div class="progress mt-1" style="height: 4px;">
                                            <div class="progress-bar bg-success" style="width: 97.8%;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 3. SECTION PRÉSENTATION (3 BÉNÉFICES CLÉS) --}}
    <section class="section-padding bg-white" id="presentation">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Vision Globale</span>
                <h2 class="section-title">Tout votre stock. Une seule plateforme.</h2>
                <p class="section-desc">
                    STOCKBOX vous aide à organiser vos marchandises, suivre vos mouvements et garder une vision claire de vos disponibilités.
                </p>
            </div>

            <div class="row g-4">
                <!-- Bénéfice 1 : Visibilité totale -->
                <div class="col-md-4">
                    <div class="sb-card text-start">
                        <div class="icon-box-primary">
                            <i data-lucide="eye" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h3 class="fs-5 fw-bold text-dark mb-2">Visibilité totale</h3>
                        <p class="text-muted small mb-0">
                            Consultez rapidement vos quantités disponibles, identifiez les produits importants et gardez une vue d'ensemble instantanée de votre stock sans calcul manuel.
                        </p>
                    </div>
                </div>

                <!-- Bénéfice 2 : Gestion simplifiée -->
                <div class="col-md-4">
                    <div class="sb-card text-start">
                        <div class="icon-box-primary">
                            <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h3 class="fs-5 fw-bold text-dark mb-2">Gestion simplifiée</h3>
                        <p class="text-muted small mb-0">
                            Centralisez les informations sur vos produits, vos catégories, vos fournisseurs et vos mouvements au sein d'une interface claire, rapide et intuitive.
                        </p>
                    </div>
                </div>

                <!-- Bénéfice 3 : Décisions plus intelligentes -->
                <div class="col-md-4">
                    <div class="sb-card text-start">
                        <div class="icon-box-primary">
                            <i data-lucide="trending-up" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h3 class="fs-5 fw-bold text-dark mb-2">Décisions plus intelligentes</h3>
                        <p class="text-muted small mb-0">
                            Repérez les produits à réapprovisionner en priorité, analysez l'historique des sorties et exploitez des données fiables pour préserver votre trésorerie.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 4. SECTION FONCTIONNALITÉS ESSENTIELLES (6 CARTES VÉRIFIÉES) --}}
    <section class="section-padding" id="fonctionnalites">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Capacités Métier</span>
                <h2 class="section-title">Tous les outils essentiels pour maîtriser votre stock.</h2>
                <p class="section-desc">
                    Des fonctionnalités conçues pour répondre aux défis quotidiens des boutiques, grossistes et entreprises commerciales.
                </p>
            </div>

            <div class="row g-4">
                <!-- 1. Gestion des produits -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="icon-box-primary m-0">
                                <i data-lucide="boxes" style="width: 24px; height: 24px;"></i>
                            </div>
                            <span class="badge bg-light text-secondary border small">Catalogue</span>
                        </div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Gestion des produits</h4>
                        <p class="text-muted small mb-3">
                            Catalogue centralisé avec références, codes SKU, catégories et prix. Consultation immédiate des quantités réelles et disponibles en rayon ou en réserve.
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Fiches produits avec prix d'achat & vente</li>
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Classement par familles et catégories</li>
                            <li><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Recherche instantanée par nom ou référence</li>
                        </ul>
                    </div>
                </div>

                <!-- 2. Entrées et sorties de stock -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="icon-box-primary m-0">
                                <i data-lucide="arrow-left-right" style="width: 24px; height: 24px;"></i>
                            </div>
                            <span class="badge bg-light text-secondary border small">Mouvements</span>
                        </div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Entrées et sorties de stock</h4>
                        <p class="text-muted small mb-3">
                            Enregistrement précis de toutes les opérations : réceptions d'achats, sorties de vente, ajustements d'inventaire et retours avec historique horodaté.
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Traçabilité intégrale de chaque opération</li>
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Saisie de motifs (inventaire, perte, vente)</li>
                            <li><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Filtres par date et type de flux</li>
                        </ul>
                    </div>
                </div>

                <!-- 3. Alertes de stock -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="icon-box-primary m-0" style="background: #FEF3C7; color: var(--sb-orange);">
                                <i data-lucide="bell-ring" style="width: 24px; height: 24px;"></i>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">Seuils Mini</span>
                        </div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Alertes de rupture de stock</h4>
                        <p class="text-muted small mb-3">
                            Détection automatique des articles passant sous le seuil d'alerte configuré. Anticipez vos commandes fournisseurs avant de perdre des ventes.
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-orange);" class="me-1"></i> Seuils personnalisables par référence</li>
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-orange);" class="me-1"></i> Indicateur visuel d'urgence de réassort</li>
                            <li><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-orange);" class="me-1"></i> Liste dédiée des produits en tension</li>
                        </ul>
                    </div>
                </div>

                <!-- 4. Gestion des fournisseurs -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="icon-box-primary m-0">
                                <i data-lucide="truck" style="width: 24px; height: 24px;"></i>
                            </div>
                            <span class="badge bg-light text-secondary border small">Fournisseurs</span>
                        </div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Gestion des fournisseurs</h4>
                        <p class="text-muted small mb-3">
                            Répertoire centralisé de tous vos fournisseurs, coordonnées directes, délais de livraison et historique des commandes d'approvisionnement.
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Fiches contacts et coordonnées complètes</li>
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Association des produits aux fournisseurs</li>
                            <li><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Suivi des factures d'achat et paiements</li>
                        </ul>
                    </div>
                </div>

                <!-- 5. Tableau de bord & statistiques -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="icon-box-primary m-0">
                                <i data-lucide="bar-chart-3" style="width: 24px; height: 24px;"></i>
                            </div>
                            <span class="badge bg-light text-secondary border small">Analytics</span>
                        </div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Tableau de bord & statistiques</h4>
                        <p class="text-muted small mb-3">
                            Visualisez en direct la valorisation financière globale de votre stock, les produits les plus vendus et l'évolution temporelle de vos mouvements.
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Valorisation globale au prix de revient</li>
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Indicateurs de rotation et de disponibilité</li>
                            <li><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Graphiques clairs pour piloter vos flux</li>
                        </ul>
                    </div>
                </div>

                <!-- 6. Gestion des utilisateurs et rôles -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="icon-box-primary m-0">
                                <i data-lucide="shield-check" style="width: 24px; height: 24px;"></i>
                            </div>
                            <span class="badge bg-light text-secondary border small">Sécurité</span>
                        </div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Gestion des rôles & accès</h4>
                        <p class="text-muted small mb-3">
                            Contrôle granulaire des droits : administrateur, gestionnaire de stock, caissier ou superviseur. Protégez vos marges et sécurisez vos données sensibles.
                        </p>
                        <ul class="list-unstyled small text-secondary mb-0">
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Rôles prédéfinis et permissions précises</li>
                            <li class="mb-1"><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Journal d'audit des actions critiques</li>
                            <li><i data-lucide="check" style="width: 15px; height: 15px; color: var(--sb-blue);" class="me-1"></i> Isolation stricte des données de l'entreprise</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 5. SECTION APERÇU DU LOGICIEL (ONGLETS INTERACTIFS) --}}
    <section class="section-padding bg-white" id="logiciel">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Expérience Utilisateur</span>
                <h2 class="section-title">Une interface conçue pour votre rythme de travail.</h2>
                <p class="section-desc">
                    Explorez les écrans clés de STOCKBOX. Chaque vue est pensée pour offrir une rapidité de saisie maximale et une clarté absolue.
                </p>

                <!-- Boutons d'onglets Alpine.js -->
                <div class="d-inline-flex p-1 bg-light rounded-3 border mt-4">
                    <button class="tab-btn" :class="{ 'active': activeTab === 'dashboard' }" @click="activeTab = 'dashboard'">
                        <i data-lucide="layout-dashboard" style="width: 17px; height: 17px;"></i>
                        <span>Tableau de bord</span>
                    </button>
                    <button class="tab-btn" :class="{ 'active': activeTab === 'produits' }" @click="activeTab = 'produits'">
                        <i data-lucide="package" style="width: 17px; height: 17px;"></i>
                        <span>Catalogue Produits</span>
                    </button>
                    <button class="tab-btn" :class="{ 'active': activeTab === 'mouvements' }" @click="activeTab = 'mouvements'">
                        <i data-lucide="arrow-left-right" style="width: 17px; height: 17px;"></i>
                        <span>Suivi Mouvements</span>
                    </button>
                    <button class="tab-btn" :class="{ 'active': activeTab === 'alertes' }" @click="activeTab = 'alertes'">
                        <i data-lucide="alert-circle" style="width: 17px; height: 17px;"></i>
                        <span>Alertes Seuil</span>
                    </button>
                </div>
            </div>

            <!-- Contenu de la maquette logicielle -->
            <div class="dashboard-mockup">
                <!-- Barre de navigation d'application simulée -->
                <div class="dashboard-mockup-header">
                    <div class="d-flex align-items-center gap-3">
                        <div class="window-dots">
                            <div class="window-dot dot-red"></div>
                            <div class="window-dot dot-amber"></div>
                            <div class="window-dot dot-green"></div>
                        </div>
                        <span class="fw-bold small text-dark d-none d-sm-inline">STOCKBOX Entreprise</span>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="input-group input-group-sm d-none d-md-flex" style="width: 220px;">
                            <span class="input-group-text bg-white border-end-0"><i data-lucide="search" style="width: 13px; height: 13px;" class="text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" placeholder="Rechercher produit..." readonly value="Riz Parfumé">
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold small" style="width: 28px; height: 28px; background-color: var(--sb-blue) !important;">
                                G
                            </div>
                            <span class="small fw-semibold text-secondary d-none d-sm-inline">Gestionnaire</span>
                        </div>
                    </div>
                </div>

                <!-- ONGLET 1 : TABLEAU DE BORD -->
                <div x-show="activeTab === 'dashboard'" class="p-4 bg-white">
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                <span class="text-muted small">Valeur Totale Stock</span>
                                <h4 class="fw-bold mt-1 text-dark mb-0">24 180 500 <small class="text-muted fs-6">FCFA</small></h4>
                                <span class="badge bg-success-subtle text-success small mt-2">+14% ce mois</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                <span class="text-muted small">Références Actives</span>
                                <h4 class="fw-bold mt-1 text-dark mb-0">512 <small class="text-muted fs-6">produits</small></h4>
                                <span class="badge bg-primary-subtle text-primary small mt-2">12 familles</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded-3 bg-light bg-opacity-50">
                                <span class="text-muted small">Mouvements Aujourd'hui</span>
                                <h4 class="fw-bold mt-1 text-dark mb-0">38 <small class="text-muted fs-6">opérations</small></h4>
                                <span class="badge bg-info-subtle text-info small mt-2">26 sorties • 12 entrées</span>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="p-3 border rounded-3 bg-warning-subtle bg-opacity-25 border-warning-subtle">
                                <span class="text-warning-emphasis small fw-semibold">Articles en Alerte</span>
                                <h4 class="fw-bold mt-1 text-warning mb-0" style="color: var(--sb-orange) !important;">4 <small class="text-muted fs-6">produits</small></h4>
                                <span class="badge bg-warning text-dark small mt-2">Action requise</span>
                            </div>
                        </div>
                    </div>

                    <!-- Graphique & Dernières opérations -->
                    <div class="row g-3">
                        <div class="col-lg-8">
                            <div class="border rounded-3 p-3">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h6 class="fw-bold text-dark m-0">Évolution de la valeur du stock (Derniers 30 jours)</h6>
                                    <span class="badge bg-light text-secondary border">Mise à jour automatique</span>
                                </div>
                                <div style="height: 160px;" class="w-100">
                                    <svg viewBox="0 0 600 160" class="w-100 h-100" preserveAspectRatio="none">
                                        <polyline fill="none" stroke="#2563EB" stroke-width="3" points="0,130 80,110 160,120 240,85 320,95 400,60 480,70 560,35 600,40" />
                                        <circle cx="240" cy="85" r="4" fill="#2563EB" />
                                        <circle cx="400" cy="60" r="4" fill="#2563EB" />
                                        <circle cx="600" cy="40" r="5" fill="#2563EB" stroke="#ffffff" stroke-width="2" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="border rounded-3 p-3">
                                <h6 class="fw-bold text-dark mb-3">Répartition par Catégorie</h6>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Alimentation Générale</span>
                                        <span class="fw-bold">54%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-primary" style="width: 54%;"></div></div>
                                </div>
                                <div class="mb-2">
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Boissons & Jus</span>
                                        <span class="fw-bold">28%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-info" style="width: 28%;"></div></div>
                                </div>
                                <div>
                                    <div class="d-flex justify-content-between small mb-1">
                                        <span>Produits d'Entretien</span>
                                        <span class="fw-bold">18%</span>
                                    </div>
                                    <div class="progress" style="height: 6px;"><div class="progress-bar bg-warning" style="width: 18%;"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ONGLET 2 : CATALOGUE PRODUITS -->
                <div x-show="activeTab === 'produits'" class="p-4 bg-white" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div class="d-flex gap-2">
                            <span class="badge bg-primary px-3 py-2">Tous (512)</span>
                            <span class="badge bg-light text-secondary border px-3 py-2">En stock (508)</span>
                            <span class="badge bg-light text-warning border px-3 py-2">Alerte (4)</span>
                        </div>
                        <button class="btn btn-sm btn-outline-primary fw-semibold">+ Nouveau Produit</button>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover border">
                            <thead class="table-light small">
                                <tr>
                                    <th>Référence</th>
                                    <th>Désignation Produit</th>
                                    <th>Catégorie</th>
                                    <th>Prix Achat</th>
                                    <th>Prix Vente</th>
                                    <th>Quantité</th>
                                    <th>Statut</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td class="fw-bold text-primary">PRD-00124</td>
                                    <td class="fw-semibold">Riz Parfumé Dinor 25kg</td>
                                    <td>Alimentation</td>
                                    <td>17 500 FCFA</td>
                                    <td>21 000 FCFA</td>
                                    <td class="fw-bold">145 sacs</td>
                                    <td><span class="badge bg-success-subtle text-success">En Stock</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-primary">PRD-00125</td>
                                    <td class="fw-semibold">Huile Végétale Aya 5L</td>
                                    <td>Alimentation</td>
                                    <td>4 200 FCFA</td>
                                    <td>5 500 FCFA</td>
                                    <td class="fw-bold text-warning">4 bidons</td>
                                    <td><span class="badge bg-warning-subtle text-warning">Seuil Critique</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-primary">PRD-00126</td>
                                    <td class="fw-semibold">Sucre Blanc Morceaux 1kg</td>
                                    <td>Épicerie</td>
                                    <td>800 FCFA</td>
                                    <td>1 000 FCFA</td>
                                    <td class="fw-bold">280 paquets</td>
                                    <td><span class="badge bg-success-subtle text-success">En Stock</span></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-primary">PRD-00127</td>
                                    <td class="fw-semibold">Lait Concentré Sucré 1kg</td>
                                    <td>Produits Laitiers</td>
                                    <td>1 400 FCFA</td>
                                    <td>1 750 FCFA</td>
                                    <td class="fw-bold">92 boîtes</td>
                                    <td><span class="badge bg-success-subtle text-success">En Stock</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ONGLET 3 : SUIVI MOUVEMENTS -->
                <div x-show="activeTab === 'mouvements'" class="p-4 bg-white" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold text-dark small">Journal chronologique des entrées / sorties</span>
                        <span class="text-muted small">Horodatage précis certifié</span>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover border">
                            <thead class="table-light small">
                                <tr>
                                    <th>Date & Heure</th>
                                    <th>Type</th>
                                    <th>Produit</th>
                                    <th>Quantité</th>
                                    <th>Auteur</th>
                                    <th>Motif / Référence</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td>Aujourd'hui 11:42</td>
                                    <td><span class="badge bg-success-subtle text-success"><i data-lucide="arrow-down-left" style="width: 12px; height: 12px;" class="me-1"></i>Entrée</span></td>
                                    <td class="fw-semibold">Riz Parfumé Dinor 25kg</td>
                                    <td class="fw-bold text-success">+50 unités</td>
                                    <td>M. Kouamé</td>
                                    <td>Bon Réception BR-2026-042</td>
                                </tr>
                                <tr>
                                    <td>Aujourd'hui 10:15</td>
                                    <td><span class="badge bg-danger-subtle text-danger"><i data-lucide="arrow-up-right" style="width: 12px; height: 12px;" class="me-1"></i>Sortie</span></td>
                                    <td class="fw-semibold">Huile Végétale Aya 5L</td>
                                    <td class="fw-bold text-danger">-12 unités</td>
                                    <td>A. Konan</td>
                                    <td>Facture Vente FV-9841</td>
                                </tr>
                                <tr>
                                    <td>Hier 16:30</td>
                                    <td><span class="badge bg-primary-subtle text-primary"><i data-lucide="refresh-cw" style="width: 12px; height: 12px;" class="me-1"></i>Ajustement</span></td>
                                    <td class="fw-semibold">Sucre Blanc Morceaux 1kg</td>
                                    <td class="fw-bold text-primary">+2 unités</td>
                                    <td>Superviseur</td>
                                    <td>Régularisation inventaire mensuel</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- ONGLET 4 : ALERTES SEUILS -->
                <div x-show="activeTab === 'alertes'" class="p-4 bg-white" style="display: none;">
                    <div class="alert alert-warning d-flex align-items-center gap-3 mb-3 border-0 bg-warning-subtle">
                        <i data-lucide="alert-triangle" style="width: 24px; height: 24px; color: var(--sb-orange);"></i>
                        <div class="small">
                            <strong>4 produits ont atteint ou franchi leur seuil minimal de sécurité.</strong>
                            Une commande de réassort auprès des fournisseurs désignés est vivement recommandée.
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table align-middle border">
                            <thead class="table-light small">
                                <tr>
                                    <th>Produit</th>
                                    <th>Stock Actuel</th>
                                    <th>Seuil Alerte</th>
                                    <th>Besoin Estimé</th>
                                    <th>Fournisseur Attitré</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody class="small">
                                <tr>
                                    <td class="fw-bold text-dark">Huile Végétale Aya 5L</td>
                                    <td><span class="badge bg-danger">4 unités</span></td>
                                    <td>15 unités</td>
                                    <td class="fw-semibold text-danger">+20 unités</td>
                                    <td>SANIA Distribution</td>
                                    <td><button class="btn btn-sm btn-primary py-0 px-2">Commander</button></td>
                                </tr>
                                <tr>
                                    <td class="fw-bold text-dark">Savon Liquide Citron 1L</td>
                                    <td><span class="badge bg-warning text-dark">6 flacons</span></td>
                                    <td>10 flacons</td>
                                    <td class="fw-semibold text-warning">+15 flacons</td>
                                    <td>COSMED CI</td>
                                    <td><button class="btn btn-sm btn-primary py-0 px-2">Commander</button></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. SECTION COMMENT ÇA MARCHE (4 ÉTAPES NUMÉROTÉES) --}}
    <section class="section-padding" id="comment-ca-marche">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Prise en Main Rapide</span>
                <h2 class="section-title">Démarrez en toute simplicité en 4 étapes.</h2>
                <p class="section-desc">
                    Pas besoin de jours de formation : l'interface STOCKBOX est conçue pour être opérationnelle dès vos premières minutes.
                </p>
            </div>

            <div class="row g-4 position-relative">
                <!-- Étape 1 -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card">
                        <div class="step-number">1</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Créez votre espace</h4>
                        <p class="text-muted small mb-0">
                            Accédez à votre compte en ligne et configurez les informations essentielles de votre établissement commercial.
                        </p>
                    </div>
                </div>

                <!-- Étape 2 -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card">
                        <div class="step-number">2</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Ajoutez vos produits</h4>
                        <p class="text-muted small mb-0">
                            Enregistrez votre catalogue de références, vos familles de produits et fixez vos seuils d'alerte de sécurité.
                        </p>
                    </div>
                </div>

                <!-- Étape 3 -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card">
                        <div class="step-number">3</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Suivez vos mouvements</h4>
                        <p class="text-muted small mb-0">
                            Saisissez les réceptions d'achats et enregistrez vos sorties pour conserver un historique horodaté certifié.
                        </p>
                    </div>
                </div>

                <!-- Étape 4 -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card">
                        <div class="step-number">4</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Pilotez votre activité</h4>
                        <p class="text-muted small mb-0">
                            Consultez les tableaux de bord et indicateurs pour identifier les besoins de réapprovisionnement à temps.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 7. SECTION AVANTAGES DÉDIÉE --}}
    <section class="section-padding bg-white" id="avantages">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Bénéfices Concrets</span>
                <h2 class="section-title">Moins d'incertitudes. Plus de maîtrise.</h2>
                <p class="section-desc">
                    Des gains tangibles et durables observés sur le terrain dès le premier mois d'utilisation.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary flex-shrink-0" style="width: 44px; height: 44px; background: var(--sb-blue-subtle);">
                            <i data-lucide="check-check" style="width: 22px; height: 22px;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Réduire les erreurs de suivi</h5>
                            <p class="text-muted small mb-0">Fini les erreurs de comptage manuel sur papier ou feuilles de calcul non partagées.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary flex-shrink-0" style="width: 44px; height: 44px; background: var(--sb-blue-subtle);">
                            <i data-lucide="shield-alert" style="width: 22px; height: 22px;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Éviter les ruptures imprévues</h5>
                            <p class="text-muted small mb-0">Anticipez chaque réapprovisionnement grâce aux seuils d'alerte automatiques.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary flex-shrink-0" style="width: 44px; height: 44px; background: var(--sb-blue-subtle);">
                            <i data-lucide="clock" style="width: 22px; height: 22px;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Gagner du temps chaque jour</h5>
                            <p class="text-muted small mb-0">Accélérez la saisie des opérations quotidiennes et simplifiez vos inventaires.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary flex-shrink-0" style="width: 44px; height: 44px; background: var(--sb-blue-subtle);">
                            <i data-lucide="database" style="width: 22px; height: 22px;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Centraliser les informations utiles</h5>
                            <p class="text-muted small mb-0">Un seul point de vérité pour les équipes de vente, de stock et la direction.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary flex-shrink-0" style="width: 44px; height: 44px; background: var(--sb-blue-subtle);">
                            <i data-lucide="history" style="width: 22px; height: 22px;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Améliorer la traçabilité</h5>
                            <p class="text-muted small mb-0">Sachez exactement qui a validé chaque mouvement, à quelle date et pour quel motif.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3">
                        <div class="d-flex align-items-center justify-content-center rounded-3 text-primary flex-shrink-0" style="width: 44px; height: 44px; background: var(--sb-blue-subtle);">
                            <i data-lucide="folder-tree" style="width: 22px; height: 22px;"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Suivi de multiples catégories</h5>
                            <p class="text-muted small mb-0">Structurez votre inventaire sans limite de complexité ou de types d'articles.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 8. SECTION TARIFS & ABONNEMENTS DYNAMIQUES --}}
    <section class="section-padding" id="tarifs">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Formules & Tarifs</span>
                <h2 class="section-title">Des tarifs clairs, adaptés à la taille de votre entreprise.</h2>
                <p class="section-desc">
                    Démarrez avec l'essai gratuit de 14 jours sans carte bancaire, puis choisissez la formule adaptée à votre croissance.
                </p>

                <!-- Toggle Mensuel / Annuel -->
                <div class="d-inline-flex align-items-center gap-3 p-1 bg-white rounded-pill border shadow-sm mt-4">
                    <button class="btn btn-sm rounded-pill px-4 fw-semibold transition" :class="billing === 'mensuel' ? 'btn-primary' : 'btn-light text-muted'" @click="billing = 'mensuel'">
                        Paiement Mensuel
                    </button>
                    <button class="btn btn-sm rounded-pill px-4 fw-semibold transition" :class="billing === 'annuel' ? 'btn-primary' : 'btn-light text-muted'" @click="billing = 'annuel'">
                        Paiement Annuel <span class="badge bg-success-subtle text-success ms-1 small">2 mois offerts</span>
                    </button>
                </div>
            </div>

            <!-- Grille des 4 Formules réelles issues de \App\Models\Abonnement::PLANS -->
            <div class="row g-4 align-items-stretch">
                @php
                    $planIcons = [
                        'essai' => 'sparkles',
                        'starter' => 'store',
                        'pro' => 'trending-up',
                        'enterprise' => 'building-2'
                    ];
                @endphp

                @foreach($plans as $key => $p)
                    <div class="col-lg-3 col-md-6">
                        <div class="pricing-card {{ $key === 'pro' ? 'featured' : '' }}">
                            @if($key === 'pro')
                                <div class="featured-badge">Recommandé</div>
                            @endif

                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <div class="icon-box-primary m-0" style="{{ $key === 'pro' ? 'background: var(--sb-blue); color: #fff;' : '' }}">
                                        <i data-lucide="{{ $planIcons[$key] ?? 'check-circle' }}" style="width: 22px; height: 22px;"></i>
                                    </div>
                                    <span class="badge bg-light text-secondary border small text-uppercase fw-bold">{{ $key }}</span>
                                </div>

                                <h4 class="fs-5 fw-bold text-dark mb-1">{{ $p['nom'] }}</h4>
                                <p class="text-muted small mb-3">{{ $p['description'] }}</p>

                                <!-- Prix Dynamique Mensuel / Annuel -->
                                <div class="mb-4">
                                    <div x-show="billing === 'mensuel'">
                                        <span class="fs-2 fw-extrabold text-dark">{{ number_format($p['prix_mensuel'], 0, ',', ' ') }}</span>
                                        <span class="text-muted small">FCFA / mois</span>
                                    </div>
                                    <div x-show="billing === 'annuel'" style="display: none;">
                                        <span class="fs-2 fw-extrabold text-dark">{{ number_format($p['prix_annuel'], 0, ',', ' ') }}</span>
                                        <span class="text-muted small">FCFA / an</span>
                                        @if($p['prix_annuel'] > 0)
                                            <div class="text-success small fw-semibold mt-1">
                                                soit {{ number_format(round($p['prix_annuel'] / 12), 0, ',', ' ') }} FCFA / mois
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <hr class="border-slate-200 mb-3">

                                <!-- Caractéristiques du forfait -->
                                <ul class="list-unstyled small text-secondary mb-4">
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                        <span><strong>{{ $p['max_utilisateurs'] ? $p['max_utilisateurs'] . ' utilisateurs' : 'Utilisateurs illimités' }}</strong></span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                        <span><strong>{{ $p['max_produits'] ? $p['max_produits'] . ' références' : 'Produits illimités' }}</strong></span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                        <span>Mouvements entrées / sorties</span>
                                    </li>
                                    <li class="mb-2 d-flex align-items-center gap-2">
                                        <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                        <span>Alertes seuils de rupture</span>
                                    </li>
                                    @if($key === 'pro' || $key === 'enterprise')
                                        <li class="mb-2 d-flex align-items-center gap-2">
                                            <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                            <span>Fiches et commandes fournisseurs</span>
                                        </li>
                                        <li class="mb-2 d-flex align-items-center gap-2">
                                            <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                            <span>Exports Excel & Rapports avancés</span>
                                        </li>
                                    @endif
                                    @if($key === 'enterprise')
                                        <li class="mb-2 d-flex align-items-center gap-2">
                                            <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                            <span>Multi-entreprises & multi-dépôts</span>
                                        </li>
                                        <li class="d-flex align-items-center gap-2">
                                            <i data-lucide="check" style="width: 16px; height: 16px; color: var(--sb-green);"></i>
                                            <span>Support dédié prioritaire 7j/7</span>
                                        </li>
                                    @endif
                                </ul>
                            </div>

                            <!-- Bouton d'action CTA selon forfait -->
                            <div>
                                @if($key === 'essai')
                                    <form action="{{ route('demo.login') }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary w-100 fw-semibold py-2">
                                            Essai Gratuit 14 Jours
                                        </button>
                                    </form>
                                @elseif($key === 'pro')
                                    <a href="{{ route('login') }}" class="btn btn-primary w-100 fw-bold py-2 shadow-sm" style="background-color: var(--sb-blue);">
                                        Choisir le Pack Pro
                                    </a>
                                @else
                                    <a href="{{ route('login') }}" class="btn btn-outline-secondary w-100 fw-semibold py-2">
                                        Sélectionner
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 9. SECTION FAQ (ACCORDÉON INTERACTIF AVEC LES 8 QUESTIONS SPÉCIFIÉES) --}}
    <section class="section-padding bg-white" id="faq">
        <div class="container">
            <div class="text-center section-header">
                <span class="section-badge">Réponses & Aide</span>
                <h2 class="section-title">Questions fréquentes</h2>
                <p class="section-desc">
                    Tout ce que vous devez savoir avant de commencer avec la solution STOCKBOX.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <!-- Q1 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 1 ? null : 1">
                            <span>Qu'est-ce que STOCKBOX exactement ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 1 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 1" x-collapse>
                            STOCKBOX est un logiciel SaaS complet de gestion de stock et de flux de marchandises conçu pour les commerces, distributeurs et entreprises. Il permet de centraliser vos références produits, de suivre en temps réel chaque entrée et sortie de stock, de recevoir des alertes automatiques avant toute rupture, et de piloter votre rentabilité grâce à des tableaux de bord clairs.
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 2 ? null : 2">
                            <span>À qui s'adresse STOCKBOX ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 2 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 2" x-collapse>
                            STOCKBOX s'adresse à tout commerce, boutique, grossiste, importateur ou PME qui gère des marchandises physiques et souhaite abandonner les feuilles de calcul complexes au profit d'un outil simple, rapide et partagé en équipe.
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 3 ? null : 3">
                            <span>Faut-il installer un logiciel sur mon ordinateur ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 3 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 3" x-collapse>
                            Non, aucune installation n'est requise. STOCKBOX est 100% accessible en ligne depuis n'importe quel navigateur web moderne (Google Chrome, Mozilla Firefox, Safari, Edge), que vous utilisiez un ordinateur, une tablette ou un smartphone.
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 4 ? null : 4">
                            <span>Puis-je tester STOCKBOX gratuitement ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 4 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 4" x-collapse>
                            Oui, absolument. Nous proposons une période d'essai gratuite de 14 jours sans aucun engagement et sans carte bancaire requise. Vous bénéficiez de toutes les fonctionnalités pour tester la solution dans vos conditions réelles d'activité.
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 5 ? null : 5">
                            <span>Mes données sont-elles sécurisées ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 5 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 5" x-collapse>
                            La sécurité et la confidentialité sont notre priorité absolue. Vos données sont isolées de manière étanche par entreprise, chiffrées selon les standards bancaires et sauvegardées quotidiennement sur des serveurs sécurisés. Vous restez propriétaire exclusif de toutes vos données.
                        </div>
                    </div>

                    <!-- Q6 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 6 ? null : 6">
                            <span>Puis-je gérer plusieurs utilisateurs avec des droits différents ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 6 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 6" x-collapse>
                            Oui. STOCKBOX intègre une gestion avancée des rôles et des autorisations. Vous pouvez définir des profils précis (Administrateur, Gestionnaire de stock, Vendeur/Caissier) afin que chaque collaborateur accède uniquement aux fonctions indispensables à ses missions.
                        </div>
                    </div>

                    <!-- Q7 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 7 ? null : 7">
                            <span>Que se passe-t-il lorsque le stock atteint un niveau critique ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 7 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 7" x-collapse>
                            Dès qu'une quantité de produit passe en dessous du seuil minimal de sécurité que vous avez fixé, STOCKBOX génère immédiatement une alerte visuelle sur votre tableau de bord et dans la liste dédiée aux réapprovisionnements pour vous permettre de passer commande à temps.
                        </div>
                    </div>

                    <!-- Q8 -->
                    <div class="faq-item">
                        <button class="faq-button" @click="openFaq = openFaq === 8 ? null : 8">
                            <span>Comment passer à un abonnement supérieur ?</span>
                            <i data-lucide="chevron-down" style="width: 20px; height: 20px; transition: transform 0.2s;" :style="openFaq === 8 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 8" x-collapse>
                            Vous pouvez faire évoluer votre formule à tout moment en quelques clics depuis votre espace administrateur. Le changement de forfait s'applique immédiatement sans interruption de service ni perte de données.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 10. SECTION CTA FINALE (FOND #0F172A) --}}
    <section class="final-cta-section">
        <div class="container text-center">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <span class="badge rounded-pill bg-primary bg-opacity-25 text-info px-3 py-2 fw-semibold mb-3">
                        <i data-lucide="shield-check" style="width: 16px; height: 16px;" class="me-1"></i>Essai 14 jours offert • Sans engagement
                    </span>
                    <h2 class="display-5 fw-extrabold text-white mb-3">
                        Prêt à reprendre le contrôle total de votre stock ?
                    </h2>
                    <p class="lead text-slate-300 mb-4 opacity-75">
                        Rejoignez les commerces et PME qui font confiance à STOCKBOX pour fiabiliser leurs mouvements, éliminer les ruptures et prendre de meilleures décisions.
                    </p>
                    <div class="d-flex flex-wrap justify-content-center gap-3">
                        <form action="{{ route('demo.login') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn-sb-primary" style="padding: 0.85rem 1.8rem; font-size: 1.05rem;">
                                <span>Commencer gratuitement</span>
                                <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                            </button>
                        </form>
                        <a href="{{ route('login') }}" class="btn btn-outline-light px-4 py-2 fw-semibold rounded-3 d-inline-flex align-items-center gap-2">
                            <i data-lucide="log-in" style="width: 18px; height: 18px;"></i>
                            <span>Se connecter</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 11. PIED DE PAGE (FOOTER 4 COLONNES) --}}
    <footer class="footer-stockbox">
        <div class="container">
            <div class="row g-4 mb-5">
                <!-- Col 1 : Marque & Statut -->
                <div class="col-lg-4 col-md-6">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="d-flex align-items-center justify-content-center bg-primary text-white rounded-2 p-1" style="width: 32px; height: 32px; background-color: var(--sb-blue) !important;">
                            <i data-lucide="package" style="width: 18px; height: 18px;"></i>
                        </div>
                        <span class="fw-bold fs-4 text-white">STOCK<span style="color: var(--sb-blue-light);">BOX</span></span>
                    </div>
                    <p class="text-slate-400 small mb-3">
                        La plateforme SaaS moderne et intuitive pour centraliser vos produits, automatiser vos alertes de stock et piloter vos approvisionnements en temps réel.
                    </p>
                    <div class="d-inline-flex align-items-center gap-2 px-2 py-1 rounded bg-dark border border-secondary border-opacity-25 small text-slate-300">
                        <span class="rounded-circle bg-success d-inline-block" style="width: 8px; height: 8px;"></span>
                        <span style="font-size: 0.8rem;">Tous les services opérationnels</span>
                    </div>
                </div>

                <!-- Col 2 : Produit -->
                <div class="col-lg-2 col-md-3 col-6">
                    <div class="footer-title">Produit</div>
                    <a href="#fonctionnalites" class="footer-link">Gestion des produits</a>
                    <a href="#fonctionnalites" class="footer-link">Entrées & Sorties</a>
                    <a href="#fonctionnalites" class="footer-link">Alertes de seuil</a>
                    <a href="#fonctionnalites" class="footer-link">Fournisseurs</a>
                    <a href="#logiciel" class="footer-link">Aperçu interactif</a>
                </div>

                <!-- Col 3 : Ressources & Navigation -->
                <div class="col-lg-3 col-md-3 col-6">
                    <div class="footer-title">Navigation & Aide</div>
                    <a href="#accueil" class="footer-link">Accueil</a>
                    <a href="#comment-ca-marche" class="footer-link">Comment ça marche</a>
                    <a href="#avantages" class="footer-link">Avantages concrets</a>
                    <a href="#tarifs" class="footer-link">Formules d'abonnement</a>
                    <a href="#faq" class="footer-link">Foire aux questions</a>
                </div>

                <!-- Col 4 : Sécurité & Connexion -->
                <div class="col-lg-3 col-md-6">
                    <div class="footer-title">Sécurité & Accès</div>
                    <p class="small text-slate-400 mb-3">
                        Données isolées et chiffrées selon les meilleures pratiques industrielles.
                    </p>
                    <div class="d-flex flex-column gap-2">
                        <a href="{{ route('login') }}" class="btn btn-sm btn-outline-light fw-semibold text-start px-3 py-2">
                            <i data-lucide="lock" style="width: 14px; height: 14px;" class="me-1"></i> Espace Sécurisé
                        </a>
                        <form action="{{ route('demo.login') }}" method="POST" class="m-0">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold text-start px-3 py-2" style="background-color: var(--sb-blue);">
                                <i data-lucide="play" style="width: 14px; height: 14px;" class="me-1"></i> Tester la Démo
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <hr class="border-secondary border-opacity-25 my-4">

            <!-- Copyright dynamique -->
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3 small text-slate-500">
                <div>
                    &copy; {{ date('Y') }} STOCKBOX SaaS. Tous droits réservés.
                </div>
                <div class="d-flex gap-3">
                    <span class="text-slate-400">Isolation SaaS multi-entreprises</span>
                    <span>•</span>
                    <span class="text-slate-400">Stock & Logistique</span>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Initialisation des icônes Lucide -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }
        });
    </script>
</body>
</html>
