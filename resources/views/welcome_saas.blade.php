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
    <!-- Alpine.js Collapse + Core -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <style>
        :root {
            --sb-night: #0F172A;
            --sb-night-light: #1E293B;
            --sb-blue: #2563EB;
            --sb-blue-hover: #1D4ED8;
            --sb-blue-light: #60A5FA;
            --sb-blue-subtle: #EFF6FF;
            --sb-bg: #F8FAFC;
            --sb-white: #FFFFFF;
            --sb-text: #111827;
            --sb-muted: #64748B;
            --sb-green: #16A34A;
            --sb-orange: #F59E0B;
            --sb-border: #E2E8F0;
            --sb-border-focus: #93C5FD;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background-color: var(--sb-bg);
            color: var(--sb-text);
            line-height: 1.6;
            overflow-x: hidden;
            -webkit-font-smoothing: antialiased;
        }

        /* ========================================================
           1. NAVBAR ANIMÉE & STICKY BLUR
           ======================================================== */
        .navbar-stockbox {
            background: rgba(255, 255, 255, 0.88);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(226, 232, 240, 0.8);
            transition: padding 0.35s cubic-bezier(0.16, 1, 0.3, 1), 
                        background-color 0.35s ease, 
                        box-shadow 0.35s ease, 
                        border-color 0.35s ease;
            animation: navSlideDown 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
        }
        .navbar-stockbox.navbar-scrolled {
            padding-top: 0.65rem !important;
            padding-bottom: 0.65rem !important;
            background: rgba(255, 255, 255, 0.96) !important;
            box-shadow: 0 10px 25px -5px rgba(15, 23, 42, 0.08);
            border-bottom-color: rgba(203, 213, 225, 0.85);
        }

        @keyframes navSlideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .brand-logo-badge {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s ease;
        }
        .brand-logo-badge:hover {
            transform: scale(1.05) rotate(-3deg);
            box-shadow: 0 6px 16px rgba(37, 99, 235, 0.35);
        }

        /* Nav links avec underline animé */
        .nav-link-sb {
            color: var(--sb-muted);
            font-weight: 500;
            font-size: 0.95rem;
            padding: 0.5rem 0.85rem;
            border-radius: 0.5rem;
            position: relative;
            text-decoration: none;
            transition: color 0.25s ease;
            display: inline-block;
        }
        .nav-link-sb::after {
            content: '';
            position: absolute;
            bottom: 4px;
            left: 50%;
            width: 0;
            height: 2px;
            background-color: var(--sb-blue);
            border-radius: 2px;
            transform: translateX(-50%);
            transition: width 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-link-sb:hover {
            color: var(--sb-blue);
        }
        .nav-link-sb:hover::after {
            width: calc(100% - 1.7rem);
        }

        /* ========================================================
           2. BOUTONS & MICRO-INTERACTIONS
           ======================================================== */
        .btn-sb-primary {
            background-color: var(--sb-blue);
            color: #ffffff;
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: 0.65rem;
            border: 1px solid var(--sb-blue);
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.25);
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        .btn-sb-primary:hover {
            background-color: var(--sb-blue-hover);
            border-color: var(--sb-blue-hover);
            color: #ffffff;
            transform: translateY(-2px);
            box-shadow: 0 8px 22px rgba(37, 99, 235, 0.38);
        }
        .btn-sb-primary:active {
            transform: translateY(1px) scale(0.98);
            box-shadow: 0 2px 6px rgba(37, 99, 235, 0.2);
        }

        .btn-sb-secondary {
            background-color: #ffffff;
            color: var(--sb-text);
            font-weight: 600;
            padding: 0.65rem 1.4rem;
            border-radius: 0.65rem;
            border: 1px solid var(--sb-border);
            box-shadow: 0 2px 6px rgba(15, 23, 42, 0.04);
            transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            text-decoration: none;
        }
        .btn-sb-secondary:hover {
            background-color: #f8fafc;
            color: var(--sb-blue);
            border-color: #cbd5e1;
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.08);
        }
        .btn-sb-secondary:active {
            transform: translateY(1px) scale(0.98);
        }

        /* ========================================================
           3. SECTION HERO & ANIMATIONS
           ======================================================== */
        .hero-section {
            padding: 4.8rem 0 5.5rem;
            background: linear-gradient(180deg, #FFFFFF 0%, #F8FAFC 100%);
            position: relative;
            overflow: hidden;
        }

        /* Halos lumineux d'ambiance */
        .hero-ambient-glow-1 {
            position: absolute;
            top: -120px;
            right: -80px;
            width: 520px;
            height: 520px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(96, 165, 250, 0.16) 0%, rgba(37, 99, 235, 0.05) 50%, transparent 70%);
            filter: blur(48px);
            pointer-events: none;
            z-index: 0;
            animation: ambientGlowMove1 14s ease-in-out infinite alternate;
        }
        .hero-ambient-glow-2 {
            position: absolute;
            bottom: -100px;
            left: -80px;
            width: 480px;
            height: 480px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.12) 0%, rgba(14, 165, 233, 0.04) 50%, transparent 70%);
            filter: blur(52px);
            pointer-events: none;
            z-index: 0;
            animation: ambientGlowMove2 16s ease-in-out infinite alternate-reverse;
        }
        @keyframes ambientGlowMove1 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(-40px, 50px) scale(1.12); }
        }
        @keyframes ambientGlowMove2 {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(50px, -40px) scale(1.08); }
        }

        .hero-grid-pattern {
            position: absolute;
            inset: 0;
            background-image: radial-gradient(rgba(148, 163, 184, 0.15) 1px, transparent 1px);
            background-size: 26px 26px;
            mask-image: radial-gradient(ellipse 65% 55% at 50% 45%, black 60%, transparent 100%);
            -webkit-mask-image: radial-gradient(ellipse 65% 55% at 50% 45%, black 60%, transparent 100%);
            pointer-events: none;
            z-index: 0;
        }

        .hero-badge-in {
            animation: heroFadeSlideUp 0.75s cubic-bezier(0.16, 1, 0.3, 1) 0.1s both;
        }
        .hero-title-in {
            animation: heroFadeSlideUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.25s both;
        }
        .hero-subtitle-in {
            animation: heroFadeSlideUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.4s both;
        }
        .hero-cta-in {
            animation: heroFadeSlideUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.55s both;
        }
        .hero-trust-in {
            animation: heroFadeSlideUp 0.85s cubic-bezier(0.16, 1, 0.3, 1) 0.7s both;
        }
        .hero-mockup-in {
            animation: heroMockupEntrance 1.1s cubic-bezier(0.16, 1, 0.3, 1) 0.35s both;
        }

        @keyframes heroFadeSlideUp {
            from {
                opacity: 0;
                transform: translateY(24px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes heroMockupEntrance {
            from {
                opacity: 0;
                transform: perspective(1000px) rotateX(10deg) translateY(45px);
            }
            to {
                opacity: 1;
                transform: perspective(1000px) rotateX(0deg) translateY(0);
            }
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.4rem 1rem;
            font-size: 0.86rem;
            font-weight: 600;
            color: var(--sb-blue);
            background-color: var(--sb-blue-subtle);
            border: 1px solid rgba(37, 99, 235, 0.22);
            border-radius: 9999px;
            margin-bottom: 1.25rem;
            box-shadow: 0 2px 8px rgba(37, 99, 235, 0.08);
        }
        .hero-badge i {
            animation: badgeSparkle 3s ease-in-out infinite;
        }
        @keyframes badgeSparkle {
            0%, 100% { transform: scale(1) rotate(0deg); opacity: 1; }
            50% { transform: scale(1.25) rotate(20deg); opacity: 0.8; }
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
                font-size: 3.45rem;
            }
        }

        /* Typing Text Animation */
        .typing-container {
            display: inline-flex;
            align-items: baseline;
            min-height: 1.25em;
            vertical-align: bottom;
            white-space: nowrap;
        }
        .typing-text {
            color: var(--sb-blue);
            font-weight: 800;
            text-shadow: 0 0 24px rgba(37, 99, 235, 0.25);
            transition: opacity 0.15s ease;
        }
        .typing-cursor {
            display: inline-block;
            width: 3.5px;
            height: 1.05em;
            background-color: var(--sb-blue);
            margin-left: 6px;
            vertical-align: -0.1em;
            border-radius: 2px;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.6);
            animation: cursorBlink 0.85s infinite;
        }
        @keyframes cursorBlink {
            0%, 45% { opacity: 1; }
            50%, 95% { opacity: 0; }
            100% { opacity: 1; }
        }

        .hero-subtitle {
            font-size: 1.15rem;
            color: var(--sb-muted);
            margin-bottom: 2rem;
            max-width: 540px;
        }

        /* ========================================================
           4. ILLUSTRATION 3D HERO : ENVIRONNEMENT DE STOCK
           ======================================================== */
        .hero-warehouse-stage {
            background: linear-gradient(135deg, #0F172A 0%, #1E293B 100%);
            border-radius: 1.25rem 1.25rem 0 0;
            padding: 1.25rem 1.5rem;
            border: 1px solid rgba(226, 232, 240, 0.15);
            border-bottom: 1px solid rgba(226, 232, 240, 0.1);
            position: relative;
            overflow: hidden;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.1);
        }
        .scanner-laser-beam {
            animation: laserScanPulse 2.2s ease-in-out infinite alternate;
        }
        @keyframes laserScanPulse {
            0% { opacity: 0.45; transform: scaleY(0.9); }
            100% { opacity: 1; transform: scaleY(1.1); }
        }
        .barcode-scanner-anim {
            animation: scannerTilt 4s ease-in-out infinite alternate;
        }
        @keyframes scannerTilt {
            0% { transform: translate(190px, 66px) rotate(0deg); }
            100% { transform: translate(190px, 64px) rotate(-3deg); }
        }
        .floating-mini-badge-1 {
            animation: floatMiniBadge1 4.5s ease-in-out infinite alternate;
        }
        .floating-mini-badge-2 {
            animation: floatMiniBadge2 5s ease-in-out infinite alternate-reverse;
        }
        @keyframes floatMiniBadge1 {
            0% { transform: translate(415px, 20px); }
            100% { transform: translate(415px, 16px); }
        }
        @keyframes floatMiniBadge2 {
            0% { transform: translate(415px, 68px); }
            100% { transform: translate(415px, 72px); }
        }

        .dashboard-mockup-wrapper {
            perspective: 1200px;
            position: relative;
            z-index: 1;
        }
        .dashboard-mockup {
            background: #ffffff;
            border-radius: 0 0 1.25rem 1.25rem;
            border: 1px solid var(--sb-border);
            border-top: none;
            box-shadow: 0 25px 60px -15px rgba(15, 23, 42, 0.16), 
                        0 0 0 1px rgba(15, 23, 42, 0.04);
            position: relative;
            overflow: hidden;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.25s ease;
            transform-style: preserve-3d;
        }
        .dashboard-mockup:hover {
            box-shadow: 0 32px 70px -15px rgba(15, 23, 42, 0.2), 
                        0 0 0 1px rgba(37, 99, 235, 0.12);
        }

        .dashboard-mockup-header {
            background: #f8fafc;
            border-bottom: 1px solid var(--sb-border);
            padding: 0.8rem 1.25rem;
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
            transition: transform 0.2s ease;
        }
        .window-dots:hover .window-dot {
            transform: scale(1.15);
        }
        .dot-red { background: #ef4444; }
        .dot-amber { background: #f59e0b; }
        .dot-green { background: #10b981; }

        .dashboard-kpi-card {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            padding: 1rem 1.15rem;
            transition: transform 0.25s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.25s ease, 
                        border-color 0.25s ease;
        }
        .dashboard-kpi-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 18px rgba(15, 23, 42, 0.06);
            border-color: #cbd5e1;
        }

        /* Cartes flottantes extérieures */
        .floating-card-1 {
            position: absolute;
            top: -24px;
            right: -24px;
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            padding: 0.75rem 1rem;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: floatSmooth1 5s ease-in-out infinite alternate;
            transition: transform 0.3s ease;
        }
        .floating-card-1:hover {
            animation-play-state: paused;
            transform: scale(1.04) translateY(-4px);
        }

        .floating-card-2 {
            position: absolute;
            bottom: -24px;
            left: -24px;
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.85rem;
            padding: 0.75rem 1rem;
            box-shadow: 0 14px 32px rgba(15, 23, 42, 0.12);
            z-index: 10;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            animation: floatSmooth2 5.5s ease-in-out infinite alternate-reverse;
            transition: transform 0.3s ease;
        }
        .floating-card-2:hover {
            animation-play-state: paused;
            transform: scale(1.04) translateY(-4px);
        }

        @keyframes floatSmooth1 {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-10px) rotate(1deg); }
        }
        @keyframes floatSmooth2 {
            0% { transform: translateY(0px) rotate(0deg); }
            100% { transform: translateY(-8px) rotate(-1deg); }
        }

        /* Courbe SVG */
        .chart-svg-curve {
            stroke-dasharray: 600;
            stroke-dashoffset: 600;
            animation: drawChartCurve 2.2s cubic-bezier(0.16, 1, 0.3, 1) 0.8s forwards;
        }
        .chart-svg-area {
            opacity: 0;
            animation: fadeInArea 1s ease 1.6s forwards;
        }
        .chart-svg-point {
            transform-box: fill-box;
            transform-origin: center;
            transform: scale(0);
            animation: popInPoint 0.5s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
        }
        .chart-pt-1 { animation-delay: 1.4s; }
        .chart-pt-2 { animation-delay: 1.8s; }
        .chart-pt-3 { animation-delay: 2.2s; }

        @keyframes drawChartCurve {
            to { stroke-dashoffset: 0; }
        }
        @keyframes fadeInArea {
            to { opacity: 1; }
        }
        @keyframes popInPoint {
            to { transform: scale(1); }
        }

        .progress-bar-animated-width {
            width: 0%;
            transition: width 1.6s cubic-bezier(0.16, 1, 0.3, 1) 0.6s;
        }
        .progress-bar-animated-width.animated {
            width: 97.8%;
        }

        /* ========================================================
           5. ILLUSTRATIONS VISUELLES PREMIUM DÉDIÉES
           ======================================================== */
        .feature-illustration-box {
            width: 100%;
            height: 120px;
            background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
            border-radius: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.35rem;
            border: 1px solid rgba(226, 232, 240, 0.85);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.3s ease, 
                        box-shadow 0.3s ease;
        }
        .sb-card:hover .feature-illustration-box {
            border-color: rgba(37, 99, 235, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.08);
        }

        .step-illustration-box {
            width: 100%;
            height: 110px;
            background: linear-gradient(135deg, #F8FAFC 0%, #EFF6FF 100%);
            border-radius: 0.95rem;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.35rem;
            border: 1px solid rgba(226, 232, 240, 0.85);
            position: relative;
            overflow: hidden;
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.3s ease, 
                        box-shadow 0.3s ease;
        }
        .sb-card:hover .step-illustration-box {
            border-color: rgba(37, 99, 235, 0.35);
            transform: translateY(-2px);
            box-shadow: 0 10px 22px rgba(37, 99, 235, 0.08);
        }

        .advantage-icon-box {
            width: 52px;
            height: 52px;
            border-radius: 0.9rem;
            background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%);
            border: 1px solid rgba(191, 219, 254, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }
        .sb-card:hover .advantage-icon-box {
            transform: scale(1.1) rotate(3deg);
            box-shadow: 0 8px 18px rgba(37, 99, 235, 0.15);
        }

        /* ========================================================
           6. MOTEUR SCROLL REVEAL (INTERSECTION OBSERVER)
           ======================================================== */
        .reveal-init {
            opacity: 0;
            transform: translateY(32px);
            transition: opacity 0.75s cubic-bezier(0.16, 1, 0.3, 1), 
                        transform 0.75s cubic-bezier(0.16, 1, 0.3, 1);
            will-change: opacity, transform;
        }
        .reveal-init.reveal-left {
            transform: translateX(-35px);
        }
        .reveal-init.reveal-right {
            transform: translateX(35px);
        }
        .reveal-init.reveal-scale {
            transform: scale(0.92);
        }
        .reveal-visible {
            opacity: 1 !important;
            transform: translate(0, 0) scale(1) !important;
        }

        .delay-100 { transition-delay: 100ms; }
        .delay-150 { transition-delay: 150ms; }
        .delay-200 { transition-delay: 200ms; }
        .delay-250 { transition-delay: 250ms; }
        .delay-300 { transition-delay: 300ms; }
        .delay-350 { transition-delay: 350ms; }
        .delay-400 { transition-delay: 400ms; }
        .delay-500 { transition-delay: 500ms; }

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

        .sb-card {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 1.15rem;
            padding: 1.75rem;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        border-color 0.28s ease;
            height: 100%;
            position: relative;
        }
        .sb-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 18px 36px -10px rgba(15, 23, 42, 0.1), 
                        0 0 0 1px rgba(37, 99, 235, 0.15);
            border-color: #cbd5e1;
        }

        .icon-box-primary {
            width: 44px;
            height: 44px;
            border-radius: 0.75rem;
            background: var(--sb-blue-subtle);
            color: var(--sb-blue);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.25rem;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), background-color 0.3s ease;
        }
        .sb-card:hover .icon-box-primary {
            transform: scale(1.1) rotate(4deg);
            background-color: #dbeafe;
        }

        .tab-btn {
            padding: 0.65rem 1.25rem;
            border-radius: 0.65rem;
            font-weight: 600;
            font-size: 0.95rem;
            color: var(--sb-muted);
            background: transparent;
            border: 1px solid transparent;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            cursor: pointer;
        }
        .tab-btn:hover {
            color: var(--sb-text);
        }
        .tab-btn.active {
            background: #ffffff;
            color: var(--sb-blue);
            border-color: var(--sb-border);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.06);
        }

        /* 4 Étapes */
        .steps-container {
            position: relative;
        }
        @media (min-width: 992px) {
            .steps-container::before {
                content: '';
                position: absolute;
                top: 36px;
                left: 12%;
                width: 76%;
                height: 2px;
                background: linear-gradient(90deg, 
                    rgba(37, 99, 235, 0.2) 0%, 
                    rgba(37, 99, 235, 0.8) 50%, 
                    rgba(37, 99, 235, 0.2) 100%);
                z-index: 0;
            }
        }
        .step-number {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            background: #ffffff;
            color: var(--sb-blue);
            font-weight: 800;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            margin-bottom: 1.25rem;
            border: 2px solid var(--sb-blue);
            box-shadow: 0 0 0 4px var(--sb-blue-subtle);
            position: relative;
            z-index: 1;
            transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease;
        }
        .sb-card:hover .step-number {
            transform: scale(1.12);
            box-shadow: 0 0 0 6px #dbeafe;
        }

        /* ========================================================
           7. CARTES TARIFS
           ======================================================== */
        .pricing-card {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 1.35rem;
            padding: 2.1rem;
            transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), 
                        box-shadow 0.28s ease, 
                        border-color 0.28s ease;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            height: 100%;
        }
        .pricing-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 42px -10px rgba(15, 23, 42, 0.1);
        }
        .pricing-card.featured {
            border: 2px solid var(--sb-blue);
            box-shadow: 0 20px 46px rgba(37, 99, 235, 0.15);
            animation: proFeaturedGlow 5s ease-in-out infinite alternate;
        }
        @keyframes proFeaturedGlow {
            0% { box-shadow: 0 20px 46px rgba(37, 99, 235, 0.12); }
            100% { box-shadow: 0 26px 54px rgba(37, 99, 235, 0.22); }
        }
        .featured-badge {
            position: absolute;
            top: -14px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--sb-blue);
            color: #ffffff;
            font-size: 0.75rem;
            font-weight: 700;
            letter-spacing: 0.06em;
            padding: 0.32rem 1rem;
            border-radius: 9999px;
            text-transform: uppercase;
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.35);
        }

        /* ========================================================
           8. ACCORDÉON FAQ
           ======================================================== */
        .faq-item {
            background: #ffffff;
            border: 1px solid var(--sb-border);
            border-radius: 0.95rem;
            margin-bottom: 0.85rem;
            overflow: hidden;
            transition: border-color 0.25s ease, box-shadow 0.25s ease;
        }
        .faq-item:hover {
            border-color: #cbd5e1;
            box-shadow: 0 6px 16px rgba(15, 23, 42, 0.04);
        }
        .faq-button {
            width: 100%;
            padding: 1.25rem 1.45rem;
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
            transition: color 0.2s ease;
        }
        .faq-button:hover {
            color: var(--sb-blue);
        }
        .faq-chevron {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), color 0.2s ease;
        }
        .faq-answer {
            padding: 0 1.45rem 1.35rem;
            color: var(--sb-muted);
            font-size: 0.97rem;
            line-height: 1.68;
        }

        /* ========================================================
           9. SECTION FINALE CTA
           ======================================================== */
        .final-cta-section {
            background: radial-gradient(circle at top right, #1e3a8a 0%, #0F172A 70%);
            color: #ffffff;
            padding: 6rem 0;
            position: relative;
            overflow: hidden;
        }
        .final-cta-glow {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 650px;
            height: 380px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37, 99, 235, 0.32) 0%, rgba(30, 58, 138, 0.12) 50%, transparent 75%);
            filter: blur(55px);
            pointer-events: none;
            animation: finalGlowPulse 6s ease-in-out infinite alternate;
        }
        @keyframes finalGlowPulse {
            0% { transform: translate(-50%, -50%) scale(0.92); opacity: 0.75; }
            100% { transform: translate(-50%, -50%) scale(1.15); opacity: 1; }
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
            transition: color 0.2s ease, transform 0.2s ease;
        }
        .footer-link:hover {
            color: #ffffff;
            transform: translateX(3px);
        }

        /* Accessibilité Reduced Motion */
        @media (prefers-reduced-motion: reduce) {
            *, ::before, ::after {
                animation-duration: 0.01ms !important;
                animation-iteration-count: 1 !important;
                transition-duration: 0.01ms !important;
                scroll-behavior: auto !important;
            }
            .reveal-init {
                opacity: 1 !important;
                transform: none !important;
            }
            .chart-svg-curve {
                stroke-dashoffset: 0 !important;
            }
            .chart-svg-area {
                opacity: 1 !important;
            }
            .chart-svg-point {
                transform: scale(1) !important;
            }
            .progress-bar-animated-width {
                width: 97.8% !important;
            }
            .typing-cursor {
                display: none !important;
                animation: none !important;
            }
        }
    </style>
</head>
<body x-data="{ mobileMenuOpen: false, activeTab: 'dashboard', billing: 'mensuel', openFaq: null }">

    {{-- 1. BARRE DE NAVIGATION STICKY BLUR --}}
    <nav class="navbar navbar-expand-lg navbar-stockbox sticky-top py-3" id="mainNavbar">
        <div class="container">
            <!-- Logo STOCKBOX -->
            <a class="d-flex align-items-center gap-2 text-decoration-none" href="{{ route('landing') }}">
                <div class="brand-logo-badge d-flex align-items-center justify-content-center bg-primary text-white rounded-3 p-2" style="width: 38px; height: 38px; background-color: var(--sb-blue) !important;">
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

    {{-- 2. SECTION HERO CHORÉGRAPHIÉE AVEC ILLUSTRATION D'ENTREPÔT MODERNE --}}
    <section class="hero-section" id="accueil">
        <div class="hero-ambient-glow-1"></div>
        <div class="hero-ambient-glow-2"></div>
        <div class="hero-grid-pattern"></div>

        <div class="container position-relative" style="z-index: 1;">
            <div class="row align-items-center g-5">
                <!-- Colonne Gauche : Copywriting avec Typing Effect & CTAs -->
                <div class="col-lg-6">
                    <div class="hero-badge-in">
                        <div class="hero-badge">
                            <i data-lucide="sparkles" style="width: 15px; height: 15px;"></i>
                            <span>La gestion de stock, simplement.</span>
                        </div>
                    </div>

                    <div class="hero-title-in">
                        <h1 class="hero-title">
                            Votre gestion de stock devient<br>
                            <span class="typing-container">
                                <span class="typing-text" id="typingText">Plus intelligente.</span><span class="typing-cursor" aria-hidden="true"></span>
                            </span>
                        </h1>
                    </div>

                    <div class="hero-subtitle-in">
                        <p class="hero-subtitle">
                            Centralisez vos produits, maîtrisez vos entrées et sorties, anticipez les ruptures et prenez de meilleures décisions grâce à une gestion de stock simple, rapide et intelligente.
                        </p>
                    </div>

                    <div class="hero-cta-in">
                        <div class="d-flex flex-wrap gap-3 align-items-center mb-4">
                            <form action="{{ route('demo.login') }}" method="POST" class="d-inline m-0">
                                @csrf
                                <button type="submit" class="btn-sb-primary" style="padding: 0.85rem 1.65rem; font-size: 1.05rem;">
                                    <span>Commencer gratuitement</span>
                                    <i data-lucide="arrow-right" style="width: 18px; height: 18px;"></i>
                                </button>
                            </form>
                            <a href="#logiciel" class="btn-sb-secondary" style="padding: 0.85rem 1.45rem; font-size: 1.05rem;">
                                <i data-lucide="play-circle" style="width: 20px; height: 20px; color: var(--sb-blue);"></i>
                                <span>Découvrir la plateforme</span>
                            </a>
                        </div>
                    </div>

                    <!-- 3 Éléments Rassurants Vérifiables -->
                    <div class="hero-trust-in">
                        <div class="d-flex flex-wrap gap-4 pt-3 border-top border-slate-200">
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
                </div>

                <!-- Colonne Droite : Illustration Entrepôt 3D + Dashboard Vivant -->
                <div class="col-lg-6 hero-mockup-in">
                    <div class="dashboard-mockup-wrapper" id="heroMockupWrapper">
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

                        <!-- 1. ILLUSTRATION 3D : ENVIRONNEMENT MODERNE DE GESTION DE STOCK -->
                        <div class="hero-warehouse-stage">
                            <svg viewBox="0 0 540 135" class="w-100 h-auto" preserveAspectRatio="xMidYMid meet" aria-label="Illustration 3D environnement moderne de gestion de stock STOCKBOX">
                                <defs>
                                    <linearGradient id="hwShelf" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#334155"/>
                                        <stop offset="100%" stop-color="#1E293B"/>
                                    </linearGradient>
                                    <linearGradient id="hwBoxBlue" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#60A5FA"/>
                                        <stop offset="100%" stop-color="#2563EB"/>
                                    </linearGradient>
                                    <linearGradient id="hwBoxAmber" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#FBBF24"/>
                                        <stop offset="100%" stop-color="#D97706"/>
                                    </linearGradient>
                                    <linearGradient id="hwBoxGreen" x1="0" y1="0" x2="1" y2="1">
                                        <stop offset="0%" stop-color="#4ADE80"/>
                                        <stop offset="100%" stop-color="#16A34A"/>
                                    </linearGradient>
                                    <linearGradient id="hwLaserBeam" x1="0" y1="0" x2="1" y2="0">
                                        <stop offset="0%" stop-color="#EF4444" stop-opacity="0.85"/>
                                        <stop offset="60%" stop-color="#F87171" stop-opacity="0.3"/>
                                        <stop offset="100%" stop-color="#EF4444" stop-opacity="0"/>
                                    </linearGradient>
                                    <filter id="hwLaserGlow" x="-20%" y="-20%" width="140%" height="140%">
                                        <feGaussianBlur stdDeviation="3" result="blur"/>
                                        <feComposite in="SourceGraphic" in2="blur" operator="over"/>
                                    </filter>
                                </defs>

                                <!-- Étagères métalliques d'entrepôt moderne -->
                                <path d="M 15,22 L 180,22 M 15,68 L 180,68 M 15,114 L 180,114" stroke="#475569" stroke-width="3" stroke-linecap="round"/>
                                <line x1="20" y1="12" x2="20" y2="124" stroke="#64748B" stroke-width="4" stroke-linecap="round"/>
                                <line x1="100" y1="12" x2="100" y2="124" stroke="#475569" stroke-width="3" stroke-linecap="round"/>
                                <line x1="175" y1="12" x2="175" y2="124" stroke="#64748B" stroke-width="4" stroke-linecap="round"/>

                                <!-- Rayon Haut : Cartons & Emballages -->
                                <g>
                                    <rect x="28" y="34" width="30" height="30" rx="3" fill="url(#hwBoxBlue)"/>
                                    <path d="M 28,44 L 58,44 M 43,34 L 43,64" stroke="#BFDBFE" stroke-width="1.5" stroke-opacity="0.6"/>
                                    <rect x="32" y="50" width="14" height="9" rx="1.5" fill="#FFFFFF"/>
                                    <line x1="35" y1="52" x2="35" y2="57" stroke="#1E293B" stroke-width="1"/>
                                    <line x1="38" y1="52" x2="38" y2="57" stroke="#1E293B" stroke-width="1.8"/>
                                    <line x1="42" y1="52" x2="42" y2="57" stroke="#1E293B" stroke-width="1"/>
                                </g>
                                <g>
                                    <rect x="64" y="42" width="26" height="22" rx="3" fill="url(#hwBoxAmber)"/>
                                    <path d="M 64,49 L 90,49 M 77,42 L 77,64" stroke="#FEF3C7" stroke-width="1.2" stroke-opacity="0.6"/>
                                </g>
                                <g>
                                    <rect x="108" y="32" width="34" height="32" rx="3" fill="url(#hwBoxBlue)"/>
                                    <path d="M 108,42 L 142,42 M 125,32 L 125,64" stroke="#BFDBFE" stroke-width="1.5" stroke-opacity="0.6"/>
                                </g>
                                <g>
                                    <rect x="146" y="38" width="24" height="26" rx="3" fill="url(#hwBoxGreen)"/>
                                </g>

                                <!-- Rayon Bas : Cartons en inventaire & Détection Code-barres -->
                                <g>
                                    <rect x="28" y="82" width="32" height="28" rx="3" fill="url(#hwBoxBlue)"/>
                                    <rect x="66" y="78" width="26" height="32" rx="3" fill="url(#hwBoxAmber)"/>
                                    <rect x="106" y="80" width="32" height="30" rx="3" fill="url(#hwBoxGreen)"/>
                                    <!-- Étiquette scannée -->
                                    <rect x="112" y="88" width="18" height="12" rx="1.5" fill="#FFFFFF"/>
                                    <line x1="116" y1="91" x2="116" y2="97" stroke="#0F172A" stroke-width="1"/>
                                    <line x1="119" y1="91" x2="119" y2="97" stroke="#0F172A" stroke-width="2"/>
                                    <line x1="123" y1="91" x2="123" y2="97" stroke="#0F172A" stroke-width="1"/>
                                    <line x1="126" y1="91" x2="126" y2="97" stroke="#0F172A" stroke-width="1.5"/>
                                </g>

                                <!-- Scanner Code-barres avec laser rouge animé -->
                                <g class="barcode-scanner-anim">
                                    <path d="M 2,12 L 18,2 L 32,8 L 22,24 L 18,44 L 8,42 L 12,24 Z" fill="#2563EB"/>
                                    <path d="M 18,2 L 32,8 L 30,14 L 16,8 Z" fill="#60A5FA"/>
                                    <rect x="28" y="6" width="6" height="10" rx="1.5" fill="#EF4444" filter="url(#hwLaserGlow)"/>
                                    <!-- Faisceau laser dynamique pointant vers l'étiquette -->
                                    <polygon points="34,11 34,13 -60,25 -60,18" fill="url(#hwLaserBeam)" class="scanner-laser-beam"/>
                                    <line x1="34" y1="11" x2="-60" y2="22" stroke="#EF4444" stroke-width="2" class="scanner-laser-beam"/>
                                </g>

                                <!-- Tablette Tactile affichant STOCKBOX -->
                                <g transform="translate(255, 16)">
                                    <rect x="0" y="0" width="148" height="98" rx="8" fill="#0B132B" stroke="#334155" stroke-width="2"/>
                                    <rect x="4" y="4" width="140" height="90" rx="5" fill="#1E293B"/>
                                    <!-- Écran : Mini tableau de bord -->
                                    <rect x="8" y="8" width="132" height="12" rx="3" fill="#2563EB" fill-opacity="0.25"/>
                                    <circle cx="16" cy="14" r="3" fill="#60A5FA"/>
                                    <rect x="24" y="12" width="45" height="4" rx="2" fill="#93C5FD"/>
                                    <!-- 3 mini blocs statistiques -->
                                    <rect x="8" y="24" width="38" height="26" rx="3" fill="#0F172A"/>
                                    <text x="11" y="34" fill="#94A3B8" font-size="5" font-family="Inter">Valeur Stock</text>
                                    <text x="11" y="44" fill="#38BDF8" font-size="7" font-weight="bold" font-family="Inter">18.4M</text>

                                    <rect x="50" y="24" width="42" height="26" rx="3" fill="#0F172A"/>
                                    <text x="54" y="34" fill="#94A3B8" font-size="5" font-family="Inter">Références</text>
                                    <text x="54" y="44" fill="#4ADE80" font-size="7" font-weight="bold" font-family="Inter">428 réf</text>

                                    <rect x="96" y="24" width="44" height="26" rx="3" fill="#0F172A"/>
                                    <text x="99" y="34" fill="#F59E0B" font-size="5" font-family="Inter">Alerte Seuil</text>
                                    <text x="99" y="44" fill="#F59E0B" font-size="7" font-weight="bold" font-family="Inter">3 crit.</text>

                                    <!-- Mini courbe flux sur tablette -->
                                    <path d="M 12,85 Q 35,62 65,72 T 105,58 T 132,52" fill="none" stroke="#38BDF8" stroke-width="2"/>
                                    <circle cx="132" cy="52" r="2.5" fill="#38BDF8"/>
                                </g>

                                <!-- Badges Flottants Connectés -->
                                <g class="floating-mini-badge-1">
                                    <rect x="0" y="0" width="112" height="38" rx="7" fill="#1E293B" stroke="#38BDF8" stroke-width="1.2" stroke-opacity="0.5"/>
                                    <circle cx="17" cy="19" r="8" fill="#0284C7" fill-opacity="0.25"/>
                                    <circle cx="17" cy="19" r="4" fill="#38BDF8"/>
                                    <text x="31" y="16" fill="#FFFFFF" font-size="7.5" font-weight="bold" font-family="Inter">Réception Stock</text>
                                    <text x="31" y="27" fill="#4ADE80" font-size="6.8" font-weight="600" font-family="Inter">+50 Riz Dinor</text>
                                </g>

                                <g class="floating-mini-badge-2">
                                    <rect x="0" y="0" width="112" height="38" rx="7" fill="#1E293B" stroke="#F59E0B" stroke-width="1.2" stroke-opacity="0.5"/>
                                    <circle cx="17" cy="19" r="8" fill="#F59E0B" fill-opacity="0.2"/>
                                    <path d="M 17,13 L 22,23 L 12,23 Z" fill="#F59E0B"/>
                                    <text x="31" y="16" fill="#FFFFFF" font-size="7.5" font-weight="bold" font-family="Inter">Alerte Réassort</text>
                                    <text x="31" y="27" fill="#FBBF24" font-size="6.8" font-weight="600" font-family="Inter">Huile 5L : reste 4</text>
                                </g>
                            </svg>
                        </div>

                        <!-- 2. FENÊTRE DASHBOARD PRINCIPALE -->
                        <div class="dashboard-mockup" id="heroDashboardMockup">
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
                                <!-- 3 Cartes KPIs Principales avec Compteurs Numériques Vivants -->
                                <div class="row g-3 mb-3">
                                    <div class="col-4">
                                        <div class="dashboard-kpi-card">
                                            <div class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Valeur du stock</div>
                                            <div class="fw-bold fs-6 mt-1 text-dark">
                                                <span class="count-up-trigger" data-target="18450000" data-currency="true">18 450 000</span>
                                                <span style="font-size: 0.7rem;">FCFA</span>
                                            </div>
                                            <div class="text-success small fw-semibold mt-1" style="font-size: 0.72rem;">
                                                <i data-lucide="trending-up" style="width: 12px; height: 12px;" class="d-inline"></i> +8.2%
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="dashboard-kpi-card">
                                            <div class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Produits en stock</div>
                                            <div class="fw-bold fs-6 mt-1 text-dark">
                                                <span class="count-up-trigger" data-target="428">428</span>
                                                <span style="font-size: 0.7rem; font-weight: 400;" class="text-muted">réf.</span>
                                            </div>
                                            <div class="text-primary small fw-semibold mt-1" style="font-size: 0.72rem;">
                                                14 catégories
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <div class="dashboard-kpi-card" style="border-left: 3px solid var(--sb-orange);">
                                            <div class="text-muted" style="font-size: 0.75rem; font-weight: 500;">Alertes de stock</div>
                                            <div class="fw-bold fs-6 mt-1 text-warning" style="color: var(--sb-orange) !important;">
                                                <span class="count-up-trigger" data-target="3">3</span>
                                                <span style="font-size: 0.7rem; font-weight: 400;" class="text-muted">critiques</span>
                                            </div>
                                            <div class="text-danger small fw-semibold mt-1" style="font-size: 0.72rem;">
                                                À commander
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Graphique SVG Évolution des mouvements avec tracé progressif -->
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
                                            <line x1="0" y1="20" x2="500" y2="20" stroke="#E2E8F0" stroke-dasharray="3,3" />
                                            <line x1="0" y1="60" x2="500" y2="60" stroke="#E2E8F0" stroke-dasharray="3,3" />
                                            <line x1="0" y1="100" x2="500" y2="100" stroke="#E2E8F0" />
                                            <path d="M 0,90 Q 70,30 140,65 T 280,35 T 420,50 T 500,20 L 500,110 L 0,110 Z" fill="url(#gradEntrees)" class="chart-svg-area" />
                                            <path d="M 0,90 Q 70,30 140,65 T 280,35 T 420,50 T 500,20" fill="none" stroke="#2563EB" stroke-width="2.5" class="chart-svg-curve" />
                                            <circle cx="140" cy="65" r="3.5" fill="#2563EB" stroke="#ffffff" stroke-width="2" class="chart-svg-point chart-pt-1" />
                                            <circle cx="280" cy="35" r="3.5" fill="#2563EB" stroke="#ffffff" stroke-width="2" class="chart-svg-point chart-pt-2" />
                                            <circle cx="500" cy="20" r="4" fill="#2563EB" stroke="#ffffff" stroke-width="2" class="chart-svg-point chart-pt-3" />
                                        </svg>
                                    </div>
                                    <div class="d-flex justify-content-between text-muted" style="font-size: 0.7rem;">
                                        <span>Lun</span><span>Mar</span><span>Mer</span><span>Jeu</span><span>Ven</span><span>Sam</span><span>Dim</span>
                                    </div>
                                </div>

                                <!-- Derniers Mouvements & Taux de Disponibilité Animé -->
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
                                            <div class="fw-bold small text-success">
                                                <span class="count-up-trigger" data-target="97.8" data-percent="true">97.8%</span>
                                            </div>
                                            <div class="progress mt-1" style="height: 4px;">
                                                <div class="progress-bar bg-success progress-bar-animated-width" id="heroProgressBar"></div>
                                            </div>
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
            <div class="text-center section-header reveal-init">
                <span class="section-badge">Vision Globale</span>
                <h2 class="section-title">Tout votre stock. Une seule plateforme.</h2>
                <p class="section-desc">
                    STOCKBOX vous aide à organiser vos marchandises, suivre vos mouvements et garder une vision claire de vos disponibilités.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4">
                    <div class="sb-card text-start reveal-init delay-100">
                        <div class="icon-box-primary">
                            <i data-lucide="eye" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h3 class="fs-5 fw-bold text-dark mb-2">Visibilité totale</h3>
                        <p class="text-muted small mb-0">
                            Consultez rapidement vos quantités disponibles, identifiez les produits importants et gardez une vue d'ensemble instantanée de votre stock sans calcul manuel.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="sb-card text-start reveal-init delay-200">
                        <div class="icon-box-primary">
                            <i data-lucide="layers" style="width: 24px; height: 24px;"></i>
                        </div>
                        <h3 class="fs-5 fw-bold text-dark mb-2">Gestion simplifiée</h3>
                        <p class="text-muted small mb-0">
                            Centralisez les informations sur vos produits, vos catégories, vos fournisseurs et vos mouvements au sein d'une interface claire, rapide et intuitive.
                        </p>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="sb-card text-start reveal-init delay-300">
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

    {{-- 4. SECTION FONCTIONNALITÉS ESSENTIELLES AVEC ILLUSTRATIONS VISUELLES DÉDIÉES --}}
    <section class="section-padding" id="fonctionnalites">
        <div class="container">
            <div class="text-center section-header reveal-init">
                <span class="section-badge">Capacités Métier</span>
                <h2 class="section-title">Tous les outils essentiels pour maîtriser votre stock.</h2>
                <p class="section-desc">
                    Des fonctionnalités conçues pour répondre aux défis quotidiens des boutiques, grossistes et entreprises commerciales.
                </p>
            </div>

            <div class="row g-4">
                <!-- 1. Gestion des produits -->
                <div class="col-lg-4 col-md-6">
                    <div class="sb-card reveal-init delay-100">
                        <!-- Illustration Spécifique : Catalogue & Fiches Produits -->
                        <div class="feature-illustration-box">
                            <svg viewBox="0 0 240 100" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration catalogue de produits">
                                <rect x="25" y="15" width="75" height="70" rx="8" fill="#2563EB" fill-opacity="0.1" stroke="#2563EB" stroke-width="1.5"/>
                                <rect x="35" y="25" width="55" height="30" rx="4" fill="#2563EB"/>
                                <path d="M 35,38 L 90,38 M 62,25 L 62,55" stroke="#93C5FD" stroke-width="1.5"/>
                                <rect x="35" y="63" width="30" height="5" rx="2" fill="#1E293B"/>
                                <rect x="35" y="72" width="45" height="4" rx="2" fill="#94A3B8"/>

                                <!-- Fiche produit volante avec code-barre -->
                                <g transform="translate(115, 20)">
                                    <rect x="0" y="0" width="100" height="60" rx="6" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" filter="drop-shadow(0 6px 12px rgba(15,23,42,0.08))"/>
                                    <rect x="10" y="10" width="45" height="6" rx="2" fill="#2563EB"/>
                                    <rect x="10" y="20" width="30" height="4" rx="2" fill="#94A3B8"/>
                                    <!-- Barcode -->
                                    <rect x="10" y="32" width="40" height="16" rx="2" fill="#F8FAFC" stroke="#E2E8F0"/>
                                    <line x1="15" y1="35" x2="15" y2="45" stroke="#1E293B" stroke-width="1.2"/>
                                    <line x1="18" y1="35" x2="18" y2="45" stroke="#1E293B" stroke-width="2"/>
                                    <line x1="22" y1="35" x2="22" y2="45" stroke="#1E293B" stroke-width="1"/>
                                    <line x1="25" y1="35" x2="25" y2="45" stroke="#1E293B" stroke-width="1.8"/>
                                    <line x1="29" y1="35" x2="29" y2="45" stroke="#1E293B" stroke-width="1"/>
                                    <line x1="33" y1="35" x2="33" y2="45" stroke="#1E293B" stroke-width="2"/>
                                    <line x1="37" y1="35" x2="37" y2="45" stroke="#1E293B" stroke-width="1.2"/>

                                    <circle cx="78" cy="38" r="12" fill="#DCFCE7"/>
                                    <path d="M 73,38 L 77,42 L 84,34" fill="none" stroke="#16A34A" stroke-width="2" stroke-linecap="round"/>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fs-5 fw-bold text-dark mb-0">Gestion des produits</h4>
                            <span class="badge bg-light text-secondary border small">Catalogue</span>
                        </div>
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
                    <div class="sb-card reveal-init delay-150">
                        <!-- Illustration Spécifique : Flux In & Out avec flèches directionnelles -->
                        <div class="feature-illustration-box">
                            <svg viewBox="0 0 240 100" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration entrées et sorties de marchandises">
                                <!-- Quai entrepôt -->
                                <path d="M 40,80 L 200,80" stroke="#CBD5E1" stroke-width="3" stroke-linecap="round"/>
                                <!-- Flèche Verte Entrée -->
                                <g transform="translate(45, 20)">
                                    <rect x="0" y="20" width="34" height="28" rx="4" fill="#16A34A" fill-opacity="0.15" stroke="#16A34A" stroke-width="1.5"/>
                                    <path d="M 0,30 L 34,30 M 17,20 L 17,48" stroke="#16A34A" stroke-width="1"/>
                                    <!-- Flèche entrant -->
                                    <path d="M 42,34 L 62,34 M 55,27 L 62,34 L 55,41" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="42" y="10" width="22" height="12" rx="3" fill="#DCFCE7"/>
                                    <text x="47" y="19" fill="#16A34A" font-size="7" font-weight="bold" font-family="Inter">+50</text>
                                </g>

                                <!-- Séparateur hub -->
                                <circle cx="120" cy="50" r="16" fill="#F1F5F9" stroke="#E2E8F0" stroke-width="1.5"/>
                                <path d="M 115,46 L 125,54 M 125,46 L 115,54" stroke="#64748B" stroke-width="2" stroke-linecap="round"/>

                                <!-- Flèche Rouge/Bleu Sortie -->
                                <g transform="translate(135, 20)">
                                    <!-- Flèche sortant -->
                                    <path d="M 5,34 L 25,34 M 18,27 L 25,34 L 18,41" stroke="#EF4444" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                                    <rect x="5" y="10" width="22" height="12" rx="3" fill="#FEE2E2"/>
                                    <text x="10" y="19" fill="#DC2626" font-size="7" font-weight="bold" font-family="Inter">-12</text>
                                    <rect x="34" y="20" width="34" height="28" rx="4" fill="#EF4444" fill-opacity="0.15" stroke="#EF4444" stroke-width="1.5"/>
                                    <path d="M 34,30 L 68,30 M 51,20 L 51,48" stroke="#EF4444" stroke-width="1"/>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fs-5 fw-bold text-dark mb-0">Entrées et sorties de stock</h4>
                            <span class="badge bg-light text-secondary border small">Mouvements</span>
                        </div>
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
                    <div class="sb-card reveal-init delay-200">
                        <!-- Illustration Spécifique : Balise d'alerte et Seuil critique -->
                        <div class="feature-illustration-box">
                            <svg viewBox="0 0 240 100" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration alertes de stock faible">
                                <circle cx="120" cy="50" r="38" fill="#FEF3C7" fill-opacity="0.4"/>
                                <circle cx="120" cy="50" r="28" fill="#FEF3C7" fill-opacity="0.8"/>
                                <!-- Jauge de niveau -->
                                <circle cx="120" cy="50" r="22" fill="#FFFFFF" stroke="#F59E0B" stroke-width="3"/>
                                <!-- Aiguille en zone critique -->
                                <path d="M 120,50 L 108,38" stroke="#DC2626" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="120" cy="50" r="4" fill="#DC2626"/>

                                <!-- Cloche Alerte avec onde -->
                                <g transform="translate(60, 30)">
                                    <circle cx="16" cy="18" r="14" fill="#FFFFFF" stroke="#E2E8F0" filter="drop-shadow(0 4px 8px rgba(0,0,0,0.06))"/>
                                    <path d="M 16,10 C 13,10 11,12 11,15 L 11,19 L 9,21 L 9,22 L 23,22 L 23,21 L 21,19 L 21,15 C 21,12 19,10 16,10 Z M 14,23 C 14,24 15,25 16,25 C 17,25 18,24 18,23 Z" fill="#F59E0B"/>
                                </g>

                                <!-- Badge Alerte Seuil Atteint -->
                                <g transform="translate(155, 30)">
                                    <rect x="0" y="0" width="55" height="34" rx="6" fill="#FFFFFF" stroke="#FDE68A" stroke-width="1.5" filter="drop-shadow(0 4px 8px rgba(0,0,0,0.06))"/>
                                    <text x="8" y="14" fill="#D97706" font-size="6.5" font-weight="bold" font-family="Inter">Stock Bas</text>
                                    <text x="8" y="26" fill="#DC2626" font-size="9" font-weight="bold" font-family="Inter">4 / 15</text>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fs-5 fw-bold text-dark mb-0">Alertes de rupture de stock</h4>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle small">Seuils Mini</span>
                        </div>
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
                    <div class="sb-card reveal-init delay-250">
                        <!-- Illustration Spécifique : Camion de livraison & Hub Fournisseur -->
                        <div class="feature-illustration-box">
                            <svg viewBox="0 0 240 100" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration relation fournisseurs et réapprovisionnements">
                                <!-- Ligne route de livraison pointillée -->
                                <path d="M 30,75 L 210,75" stroke="#CBD5E1" stroke-width="2.5" stroke-dasharray="6,4"/>

                                <!-- Camion de fret moderne -->
                                <g transform="translate(45, 32)">
                                    <rect x="0" y="8" width="55" height="30" rx="3" fill="#2563EB"/>
                                    <path d="M 55,18 L 72,18 L 78,28 L 78,38 L 55,38 Z" fill="#3B82F6"/>
                                    <rect x="58" y="21" width="12" height="7" rx="1.5" fill="#BFDBFE"/>
                                    <!-- Roues -->
                                    <circle cx="16" cy="40" r="6" fill="#1E293B" stroke="#CBD5E1" stroke-width="2"/>
                                    <circle cx="66" cy="40" r="6" fill="#1E293B" stroke="#CBD5E1" stroke-width="2"/>
                                    <!-- Logo colis sur camion -->
                                    <rect x="18" y="16" width="16" height="14" rx="2" fill="#FFFFFF" fill-opacity="0.3"/>
                                </g>

                                <!-- Entrepot Réception & Bon de commande -->
                                <g transform="translate(150, 20)">
                                    <rect x="0" y="0" width="55" height="52" rx="6" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" filter="drop-shadow(0 6px 12px rgba(15,23,42,0.08))"/>
                                    <rect x="8" y="8" width="30" height="5" rx="2" fill="#2563EB"/>
                                    <line x1="8" y1="18" x2="47" y2="18" stroke="#E2E8F0" stroke-width="1.5"/>
                                    <rect x="8" y="23" width="38" height="4" rx="1.5" fill="#94A3B8"/>
                                    <rect x="8" y="30" width="28" height="4" rx="1.5" fill="#94A3B8"/>
                                    <!-- Tampon validé -->
                                    <rect x="25" y="38" width="22" height="8" rx="2" fill="#DCFCE7"/>
                                    <text x="28" y="44" fill="#16A34A" font-size="5" font-weight="bold" font-family="Inter">REÇU</text>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fs-5 fw-bold text-dark mb-0">Gestion des fournisseurs</h4>
                            <span class="badge bg-light text-secondary border small">Fournisseurs</span>
                        </div>
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
                    <div class="sb-card reveal-init delay-300">
                        <!-- Illustration Spécifique : Graphiques 3D & Analytics -->
                        <div class="feature-illustration-box">
                            <svg viewBox="0 0 240 100" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration tableau de bord et analytics financiers">
                                <rect x="25" y="15" width="190" height="70" rx="8" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" filter="drop-shadow(0 6px 14px rgba(15,23,42,0.06))"/>
                                <!-- Barres 3D montantes -->
                                <rect x="45" y="55" width="14" height="22" rx="3" fill="#93C5FD"/>
                                <rect x="68" y="44" width="14" height="33" rx="3" fill="#60A5FA"/>
                                <rect x="91" y="32" width="14" height="45" rx="3" fill="#2563EB"/>
                                <rect x="114" y="24" width="14" height="53" rx="3" fill="#1D4ED8"/>

                                <!-- Courbe de croissance financière ascendante -->
                                <path d="M 45,52 Q 80,38 114,20 T 150,15" fill="none" stroke="#16A34A" stroke-width="2.5" stroke-linecap="round"/>
                                <circle cx="150" cy="15" r="4" fill="#16A34A"/>

                                <!-- Badge valorisation financière -->
                                <g transform="translate(145, 38)">
                                    <rect x="0" y="0" width="60" height="34" rx="5" fill="#0F172A"/>
                                    <text x="8" y="14" fill="#94A3B8" font-size="5.5" font-family="Inter">Valorisation</text>
                                    <text x="8" y="26" fill="#38BDF8" font-size="8" font-weight="bold" font-family="Inter">18 450 K</text>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fs-5 fw-bold text-dark mb-0">Tableau de bord & statistiques</h4>
                            <span class="badge bg-light text-secondary border small">Analytics</span>
                        </div>
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
                    <div class="sb-card reveal-init delay-350">
                        <!-- Illustration Spécifique : Bouclier de sécurité & Rôles hiérarchiques -->
                        <div class="feature-illustration-box">
                            <svg viewBox="0 0 240 100" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration contrôle d'accès rôles et sécurité">
                                <!-- Bouclier central -->
                                <g transform="translate(95, 12)">
                                    <path d="M 25,6 L 45,14 L 45,40 C 45,55 25,68 25,68 C 25,68 5,55 5,40 L 5,14 Z" fill="#2563EB" stroke="#60A5FA" stroke-width="2"/>
                                    <!-- Cadenas intérieur -->
                                    <rect x="17" y="30" width="16" height="13" rx="2" fill="#FFFFFF"/>
                                    <path d="M 20,30 L 20,24 C 20,21 30,21 30,24 L 30,30" fill="none" stroke="#FFFFFF" stroke-width="2.5"/>
                                </g>

                                <!-- Profil Super Admin -->
                                <g transform="translate(30, 24)">
                                    <rect x="0" y="0" width="55" height="48" rx="6" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.2" filter="drop-shadow(0 4px 8px rgba(0,0,0,0.06))"/>
                                    <circle cx="27" cy="18" r="8" fill="#FEF3C7"/>
                                    <circle cx="27" cy="18" r="4" fill="#D97706"/>
                                    <text x="9" y="36" fill="#1E293B" font-size="6" font-weight="bold" font-family="Inter">Super Admin</text>
                                    <rect x="12" y="40" width="30" height="3" rx="1.5" fill="#F59E0B"/>
                                </g>

                                <!-- Profil Gestionnaire / Caissier -->
                                <g transform="translate(155, 24)">
                                    <rect x="0" y="0" width="55" height="48" rx="6" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.2" filter="drop-shadow(0 4px 8px rgba(0,0,0,0.06))"/>
                                    <circle cx="27" cy="18" r="8" fill="#DCFCE7"/>
                                    <circle cx="27" cy="18" r="4" fill="#16A34A"/>
                                    <text x="7" y="36" fill="#1E293B" font-size="6" font-weight="bold" font-family="Inter">Gestionnaire</text>
                                    <rect x="12" y="40" width="30" height="3" rx="1.5" fill="#10B981"/>
                                </g>
                            </svg>
                        </div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h4 class="fs-5 fw-bold text-dark mb-0">Gestion des rôles & accès</h4>
                            <span class="badge bg-light text-secondary border small">Sécurité</span>
                        </div>
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

    {{-- 5. SECTION APERÇU DU LOGICIEL (ONGLETS INTERACTIFS FLUIDES) --}}
    <section class="section-padding bg-white" id="logiciel">
        <div class="container">
            <div class="text-center section-header reveal-init">
                <span class="section-badge">Expérience Utilisateur</span>
                <h2 class="section-title">Une interface conçue pour votre rythme de travail.</h2>
                <p class="section-desc">
                    Explorez les écrans clés de STOCKBOX. Chaque vue est pensée pour offrir une rapidité de saisie maximale et une clarté absolue.
                </p>

                <!-- Boutons d'onglets Alpine.js -->
                <div class="d-inline-flex p-1 bg-light rounded-3 border mt-4 shadow-sm">
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

            <!-- Contenu de la maquette logicielle avec transitions Alpine -->
            <div class="dashboard-mockup reveal-init reveal-scale" style="border-radius: 1.25rem;">
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
                <div x-show="activeTab === 'dashboard'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-white">
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
                <div x-show="activeTab === 'produits'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-white" style="display: none;">
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
                <div x-show="activeTab === 'mouvements'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-white" style="display: none;">
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
                <div x-show="activeTab === 'alertes'" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-white" style="display: none;">
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

    {{-- 6. SECTION COMMENT ÇA MARCHE AVEC ILLUSTRATIONS PARCOURS --}}
    <section class="section-padding" id="comment-ca-marche">
        <div class="container">
            <div class="text-center section-header reveal-init">
                <span class="section-badge">Prise en Main Rapide</span>
                <h2 class="section-title">Démarrez en toute simplicité en 4 étapes.</h2>
                <p class="section-desc">
                    Pas besoin de jours de formation : l'interface STOCKBOX est conçue pour être opérationnelle dès vos premières minutes.
                </p>
            </div>

            <div class="row g-4 steps-container">
                <!-- Étape 1 : Création Espace -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card reveal-init delay-100">
                        <div class="step-illustration-box">
                            <svg viewBox="0 0 200 85" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration création de compte et espace de travail">
                                <rect x="35" y="10" width="130" height="65" rx="7" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" filter="drop-shadow(0 4px 10px rgba(0,0,0,0.06))"/>
                                <rect x="42" y="18" width="116" height="12" rx="3" fill="#2563EB" fill-opacity="0.15"/>
                                <circle cx="50" cy="24" r="3" fill="#2563EB"/>
                                <rect x="58" y="22" width="35" height="4" rx="2" fill="#2563EB"/>
                                <!-- Champs formulaire -->
                                <rect x="42" y="36" width="70" height="6" rx="2" fill="#F1F5F9" stroke="#E2E8F0"/>
                                <rect x="42" y="46" width="70" height="6" rx="2" fill="#F1F5F9" stroke="#E2E8F0"/>
                                <!-- Bouton activation avec clé -->
                                <circle cx="135" cy="46" r="14" fill="#2563EB"/>
                                <path d="M 130,46 C 130,43 133,40 137,40 C 140,40 142,42 142,45 L 140,48 L 138,47 L 136,49 L 134,48 L 130,52 Z" fill="#FFFFFF"/>
                            </svg>
                        </div>
                        <div class="step-number">1</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Créez votre espace</h4>
                        <p class="text-muted small mb-0">
                            Accédez à votre compte en ligne et configurez les informations essentielles de votre établissement commercial.
                        </p>
                    </div>
                </div>

                <!-- Étape 2 : Ajout Produits -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card reveal-init delay-200">
                        <div class="step-illustration-box">
                            <svg viewBox="0 0 200 85" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration ajout de produits et scanning">
                                <g transform="translate(30, 18)">
                                    <rect x="0" y="8" width="42" height="36" rx="4" fill="#2563EB"/>
                                    <path d="M 0,20 L 42,20 M 21,8 L 21,44" stroke="#93C5FD" stroke-width="1.2"/>
                                    <rect x="6" y="26" width="16" height="10" rx="1.5" fill="#FFFFFF"/>
                                    <line x1="9" y1="28" x2="9" y2="34" stroke="#0F172A" stroke-width="1"/>
                                    <line x1="12" y1="28" x2="12" y2="34" stroke="#0F172A" stroke-width="1.8"/>
                                    <line x1="16" y1="28" x2="16" y2="34" stroke="#0F172A" stroke-width="1"/>
                                </g>
                                <!-- Scanner pointant sur le carton -->
                                <g transform="translate(100, 20)">
                                    <path d="M 2,10 L 16,2 L 28,8 L 20,20 L 16,36 L 6,34 L 10,20 Z" fill="#1E293B"/>
                                    <line x1="2" y1="12" x2="-28" y2="28" stroke="#EF4444" stroke-width="2" stroke-linecap="round"/>
                                    <!-- Bulle + Produit catalogué -->
                                    <rect x="36" y="6" width="28" height="20" rx="4" fill="#DCFCE7" stroke="#16A34A"/>
                                    <text x="41" y="19" fill="#16A34A" font-size="9" font-weight="bold" font-family="Inter">+1</text>
                                </g>
                            </svg>
                        </div>
                        <div class="step-number">2</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Ajoutez vos produits</h4>
                        <p class="text-muted small mb-0">
                            Enregistrez votre catalogue de références, vos familles de produits et fixez vos seuils d'alerte de sécurité.
                        </p>
                    </div>
                </div>

                <!-- Étape 3 : Suivi Mouvements -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card reveal-init delay-300">
                        <div class="step-illustration-box">
                            <svg viewBox="0 0 200 85" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration suivi des mouvements de stock">
                                <!-- Convoyeur de transit -->
                                <path d="M 20,52 L 180,52" stroke="#CBD5E1" stroke-width="3" stroke-linecap="round"/>
                                <circle cx="35" cy="52" r="5" fill="#64748B"/>
                                <circle cx="75" cy="52" r="5" fill="#64748B"/>
                                <circle cx="115" cy="52" r="5" fill="#64748B"/>
                                <circle cx="155" cy="52" r="5" fill="#64748B"/>
                                <!-- Colis en circulation avec flèche -->
                                <g transform="translate(60, 16)">
                                    <rect x="0" y="0" width="34" height="28" rx="3" fill="#2563EB"/>
                                    <path d="M 0,10 L 34,10 M 17,0 L 17,28" stroke="#BFDBFE" stroke-width="1.2"/>
                                </g>
                                <g transform="translate(110, 16)">
                                    <rect x="0" y="0" width="34" height="28" rx="3" fill="#F59E0B"/>
                                    <path d="M 0,10 L 34,10 M 17,0 L 17,28" stroke="#FEF3C7" stroke-width="1.2"/>
                                </g>
                                <path d="M 100,66 L 125,66 M 118,60 L 125,66 L 118,72" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div class="step-number">3</div>
                        <h4 class="fs-5 fw-bold text-dark mb-2">Suivez vos mouvements</h4>
                        <p class="text-muted small mb-0">
                            Saisissez les réceptions d'achats et enregistrez vos sorties pour conserver un historique horodaté certifié.
                        </p>
                    </div>
                </div>

                <!-- Étape 4 : Pilotage Activité -->
                <div class="col-lg-3 col-sm-6">
                    <div class="sb-card reveal-init delay-400">
                        <div class="step-illustration-box">
                            <svg viewBox="0 0 200 85" class="w-100 h-100" preserveAspectRatio="xMidYMid meet" aria-label="Illustration pilotage et analyse de gestion">
                                <rect x="35" y="10" width="130" height="65" rx="7" fill="#FFFFFF" stroke="#E2E8F0" stroke-width="1.5" filter="drop-shadow(0 4px 10px rgba(0,0,0,0.06))"/>
                                <!-- Graphique KPI & jauge -->
                                <rect x="44" y="18" width="60" height="22" rx="3" fill="#F8FAFC" stroke="#E2E8F0"/>
                                <path d="M 48,34 Q 60,24 72,28 T 98,22" fill="none" stroke="#2563EB" stroke-width="2"/>
                                <!-- Donut de disponibilité -->
                                <circle cx="132" cy="29" r="13" fill="none" stroke="#E2E8F0" stroke-width="4"/>
                                <circle cx="132" cy="29" r="13" fill="none" stroke="#16A34A" stroke-width="4" stroke-dasharray="80" stroke-dashoffset="15"/>
                                <text x="124" y="32" fill="#16A34A" font-size="7" font-weight="bold" font-family="Inter">98%</text>

                                <rect x="44" y="47" width="112" height="6" rx="2" fill="#F1F5F9"/>
                                <rect x="44" y="57" width="75" height="5" rx="2" fill="#2563EB" fill-opacity="0.3"/>
                            </svg>
                        </div>
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

    {{-- 7. SECTION AVANTAGES DÉDIÉE AVEC BADGES ILLUSTRÉS --}}
    <section class="section-padding bg-white" id="avantages">
        <div class="container">
            <div class="text-center section-header reveal-init">
                <span class="section-badge">Bénéfices Concrets</span>
                <h2 class="section-title">Moins d'incertitudes. Plus de maîtrise.</h2>
                <p class="section-desc">
                    Des gains tangibles et durables observés sur le terrain dès le premier mois d'utilisation.
                </p>
            </div>

            <div class="row g-4">
                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 border bg-white sb-card reveal-init delay-100">
                        <div class="advantage-icon-box">
                            <svg viewBox="0 0 32 32" width="28" height="28">
                                <circle cx="16" cy="16" r="13" fill="#2563EB" fill-opacity="0.15"/>
                                <path d="M 10,16 L 14,20 L 22,12" fill="none" stroke="#2563EB" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Réduire les erreurs de suivi</h5>
                            <p class="text-muted small mb-0">Fini les erreurs de comptage manuel sur papier ou feuilles de calcul non partagées.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 border bg-white sb-card reveal-init delay-150">
                        <div class="advantage-icon-box" style="background: linear-gradient(135deg, #FEF3C7 0%, #FDE68A 100%); border-color: #FCD34D;">
                            <svg viewBox="0 0 32 32" width="28" height="28">
                                <circle cx="16" cy="16" r="13" fill="#F59E0B" fill-opacity="0.15"/>
                                <path d="M 16,8 L 24,23 L 8,23 Z" fill="#F59E0B"/>
                                <line x1="16" y1="13" x2="16" y2="18" stroke="#FFFFFF" stroke-width="1.8" stroke-linecap="round"/>
                                <circle cx="16" cy="20.5" r="1" fill="#FFFFFF"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Éviter les ruptures imprévues</h5>
                            <p class="text-muted small mb-0">Anticipez chaque réapprovisionnement grâce aux seuils d'alerte automatiques.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 border bg-white sb-card reveal-init delay-200">
                        <div class="advantage-icon-box">
                            <svg viewBox="0 0 32 32" width="28" height="28">
                                <circle cx="16" cy="16" r="13" fill="#2563EB" fill-opacity="0.15"/>
                                <circle cx="16" cy="16" r="9" fill="none" stroke="#2563EB" stroke-width="2"/>
                                <polyline points="16,11 16,16 20,18" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Gagner du temps chaque jour</h5>
                            <p class="text-muted small mb-0">Accélérez la saisie des opérations quotidiennes et simplifiez vos inventaires.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 border bg-white sb-card reveal-init delay-250">
                        <div class="advantage-icon-box">
                            <svg viewBox="0 0 32 32" width="28" height="28">
                                <circle cx="16" cy="16" r="13" fill="#2563EB" fill-opacity="0.15"/>
                                <ellipse cx="16" cy="10" rx="8" ry="3" fill="none" stroke="#2563EB" stroke-width="1.8"/>
                                <path d="M 8,10 L 8,16 C 8,17.6 11.6,19 16,19 C 20.4,19 24,17.6 24,16 L 24,10" fill="none" stroke="#2563EB" stroke-width="1.8"/>
                                <path d="M 8,16 L 8,22 C 8,23.6 11.6,25 16,25 C 20.4,25 24,23.6 24,22 L 24,16" fill="none" stroke="#2563EB" stroke-width="1.8"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Centraliser les informations utiles</h5>
                            <p class="text-muted small mb-0">Un seul point de vérité pour les équipes de vente, de stock et la direction.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 border bg-white sb-card reveal-init delay-300">
                        <div class="advantage-icon-box">
                            <svg viewBox="0 0 32 32" width="28" height="28">
                                <circle cx="16" cy="16" r="13" fill="#2563EB" fill-opacity="0.15"/>
                                <path d="M 8,16 C 8,11.6 11.6,8 16,8 C 20.4,8 24,11.6 24,16 C 24,20.4 20.4,24 16,24" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"/>
                                <polyline points="14,13 16,16 19,16" fill="none" stroke="#2563EB" stroke-width="2" stroke-linecap="round"/>
                                <polygon points="6,16 9,12 9,20" fill="#2563EB"/>
                            </svg>
                        </div>
                        <div>
                            <h5 class="fw-bold fs-6 text-dark mb-1">Améliorer la traçabilité</h5>
                            <p class="text-muted small mb-0">Sachez exactement qui a validé chaque mouvement, à quelle date et pour quel motif.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 col-sm-6">
                    <div class="d-flex align-items-start gap-3 p-3 rounded-3 border bg-white sb-card reveal-init delay-350">
                        <div class="advantage-icon-box">
                            <svg viewBox="0 0 32 32" width="28" height="28">
                                <circle cx="16" cy="16" r="13" fill="#2563EB" fill-opacity="0.15"/>
                                <rect x="9" y="8" width="6" height="6" rx="1.5" fill="#2563EB"/>
                                <rect x="17" y="14" width="6" height="6" rx="1.5" fill="#3B82F6"/>
                                <rect x="17" y="22" width="6" height="6" rx="1.5" fill="#60A5FA"/>
                                <path d="M 12,14 L 12,25 L 17,25 M 12,17 L 17,17" stroke="#2563EB" stroke-width="1.8" stroke-linecap="round"/>
                            </svg>
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
            <div class="text-center section-header reveal-init">
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
                        <div class="pricing-card {{ $key === 'pro' ? 'featured' : '' }} reveal-init delay-{{ ($loop->index + 1) * 100 }}">
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

                                <!-- Prix Dynamique Mensuel / Annuel avec transition -->
                                <div class="mb-4" style="min-height: 70px;">
                                    <div x-show="billing === 'mensuel'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0">
                                        <span class="fs-2 fw-extrabold text-dark">{{ number_format($p['prix_mensuel'], 0, ',', ' ') }}</span>
                                        <span class="text-muted small">FCFA / mois</span>
                                    </div>
                                    <div x-show="billing === 'annuel'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform -translate-y-1" x-transition:enter-end="opacity-100 transform translate-y-0" style="display: none;">
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

    {{-- 9. SECTION FAQ AVEC ACCORDÉON FLUIDE ET CHEVRON ANIMÉ --}}
    <section class="section-padding bg-white" id="faq">
        <div class="container">
            <div class="text-center section-header reveal-init">
                <span class="section-badge">Réponses & Aide</span>
                <h2 class="section-title">Questions fréquentes</h2>
                <p class="section-desc">
                    Tout ce que vous devez savoir avant de commencer avec la solution STOCKBOX.
                </p>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <!-- Q1 -->
                    <div class="faq-item reveal-init delay-100">
                        <button class="faq-button" @click="openFaq = openFaq === 1 ? null : 1" type="button">
                            <span>Qu'est-ce que STOCKBOX exactement ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 1 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 1" x-collapse>
                            STOCKBOX est un logiciel SaaS complet de gestion de stock et de flux de marchandises conçu pour les commerces, distributeurs et entreprises. Il permet de centraliser vos références produits, de suivre en temps réel chaque entrée et sortie de stock, de recevoir des alertes automatiques avant toute rupture, et de piloter votre rentabilité grâce à des tableaux de bord clairs.
                        </div>
                    </div>

                    <!-- Q2 -->
                    <div class="faq-item reveal-init delay-150">
                        <button class="faq-button" @click="openFaq = openFaq === 2 ? null : 2" type="button">
                            <span>À qui s'adresse STOCKBOX ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 2 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 2" x-collapse>
                            STOCKBOX s'adresse à tout commerce, boutique, grossiste, importateur ou PME qui gère des marchandises physiques et souhaite abandonner les feuilles de calcul complexes au profit d'un outil simple, rapide et partagé en équipe.
                        </div>
                    </div>

                    <!-- Q3 -->
                    <div class="faq-item reveal-init delay-200">
                        <button class="faq-button" @click="openFaq = openFaq === 3 ? null : 3" type="button">
                            <span>Faut-il installer un logiciel sur mon ordinateur ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 3 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 3" x-collapse>
                            Non, aucune installation n'est requise. STOCKBOX est 100% accessible en ligne depuis n'importe quel navigateur web moderne (Google Chrome, Mozilla Firefox, Safari, Edge), que vous utilisiez un ordinateur, une tablette ou un smartphone.
                        </div>
                    </div>

                    <!-- Q4 -->
                    <div class="faq-item reveal-init delay-250">
                        <button class="faq-button" @click="openFaq = openFaq === 4 ? null : 4" type="button">
                            <span>Puis-je tester STOCKBOX gratuitement ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 4 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 4" x-collapse>
                            Oui, absolument. Nous proposons une période d'essai gratuite de 14 jours sans aucun engagement et sans carte bancaire requise. Vous bénéficiez de toutes les fonctionnalités pour tester la solution dans vos conditions réelles d'activité.
                        </div>
                    </div>

                    <!-- Q5 -->
                    <div class="faq-item reveal-init delay-300">
                        <button class="faq-button" @click="openFaq = openFaq === 5 ? null : 5" type="button">
                            <span>Mes données sont-elles sécurisées ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 5 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 5" x-collapse>
                            La sécurité et la confidentialité sont notre priorité absolue. Vos données sont isolées de manière étanche par entreprise, chiffrées selon les standards bancaires et sauvegardées quotidiennement sur des serveurs sécurisés. Vous restez propriétaire exclusif de toutes vos données.
                        </div>
                    </div>

                    <!-- Q6 -->
                    <div class="faq-item reveal-init delay-350">
                        <button class="faq-button" @click="openFaq = openFaq === 6 ? null : 6" type="button">
                            <span>Puis-je gérer plusieurs utilisateurs avec des droits différents ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 6 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 6" x-collapse>
                            Oui. STOCKBOX intègre une gestion avancée des rôles et des autorisations. Vous pouvez définir des profils précis (Administrateur, Gestionnaire de stock, Vendeur/Caissier) afin que chaque collaborateur accède uniquement aux fonctions indispensables à ses missions.
                        </div>
                    </div>

                    <!-- Q7 -->
                    <div class="faq-item reveal-init delay-400">
                        <button class="faq-button" @click="openFaq = openFaq === 7 ? null : 7" type="button">
                            <span>Que se passe-t-il lorsque le stock atteint un niveau critique ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 7 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 7" x-collapse>
                            Dès qu'une quantité de produit passe en dessous du seuil minimal de sécurité que vous avez fixé, STOCKBOX génère immédiatement une alerte visuelle sur votre tableau de bord et dans la liste dédiée aux réapprovisionnements pour vous permettre de passer commande à temps.
                        </div>
                    </div>

                    <!-- Q8 -->
                    <div class="faq-item reveal-init delay-500">
                        <button class="faq-button" @click="openFaq = openFaq === 8 ? null : 8" type="button">
                            <span>Comment passer à un abonnement supérieur ?</span>
                            <i data-lucide="chevron-down" class="faq-chevron" style="width: 20px; height: 20px;" :style="openFaq === 8 ? 'transform: rotate(180deg); color: var(--sb-blue);' : ''"></i>
                        </button>
                        <div class="faq-answer" x-show="openFaq === 8" x-collapse>
                            Vous pouvez faire évoluer votre formule à tout moment en quelques clics depuis votre espace administrateur. Le changement de forfait s'applique immédiatement sans interruption de service ni perte de données.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- 10. SECTION CTA FINALE --}}
    <section class="final-cta-section">
        <div class="final-cta-glow"></div>
        <div class="container text-center position-relative" style="z-index: 1;">
            <div class="row justify-content-center">
                <div class="col-lg-8 reveal-init">
                    <span class="badge rounded-pill bg-primary bg-opacity-25 text-info px-3 py-2 fw-semibold mb-3 border border-primary border-opacity-25">
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
                            <button type="submit" class="btn-sb-primary" style="padding: 0.85rem 1.85rem; font-size: 1.05rem;">
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

    <!-- Moteur d'Animation JavaScript Performant & Léger -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // 1. Initialiser les icônes Lucide
            if (typeof lucide !== 'undefined' && lucide.createIcons) {
                lucide.createIcons();
            }

            // 2. Transformation de la Navbar au défilement (Sticky Blur & Shadow)
            const navbar = document.getElementById('mainNavbar');
            if (navbar) {
                const handleNavScroll = () => {
                    if (window.scrollY > 20) {
                        navbar.classList.add('navbar-scrolled');
                    } else {
                        navbar.classList.remove('navbar-scrolled');
                    }
                };
                window.addEventListener('scroll', handleNavScroll, { passive: true });
                handleNavScroll();
            }

            // 3. Scroll Reveal avec IntersectionObserver
            const revealElements = document.querySelectorAll('.reveal-init');
            if ('IntersectionObserver' in window) {
                const revealObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            entry.target.classList.add('reveal-visible');
                            observer.unobserve(entry.target);
                        }
                    });
                }, {
                    threshold: 0.1,
                    rootMargin: '0px 0px -40px 0px'
                });

                revealElements.forEach(el => revealObserver.observe(el));
            } else {
                revealElements.forEach(el => el.classList.add('reveal-visible'));
            }

            // 4. Compteurs Numériques Vivants (Count-Up Animation)
            const countElements = document.querySelectorAll('.count-up-trigger');
            const animateCounter = (el) => {
                const target = parseFloat(el.getAttribute('data-target'));
                const isCurrency = el.getAttribute('data-currency') === 'true';
                const isPercent = el.getAttribute('data-percent') === 'true';
                const duration = 1800;
                const startTime = performance.now();

                const updateCount = (currentTime) => {
                    const elapsed = currentTime - startTime;
                    const progress = Math.min(elapsed / duration, 1);
                    const easeOut = 1 - Math.pow(1 - progress, 3);
                    const current = progress === 1 ? target : (easeOut * target);

                    if (isCurrency) {
                        el.textContent = Math.round(current).toLocaleString('fr-FR');
                    } else if (isPercent) {
                        el.textContent = current.toFixed(1) + '%';
                    } else {
                        el.textContent = Math.round(current).toString();
                    }

                    if (progress < 1) {
                        requestAnimationFrame(updateCount);
                    }
                };
                requestAnimationFrame(updateCount);
            };

            setTimeout(() => {
                countElements.forEach(el => animateCounter(el));
                const bar = document.getElementById('heroProgressBar');
                if (bar) {
                    bar.classList.add('animated');
                }
            }, 600);

            // 5. Effet Parallaxe 3D Subtil au survol du Hero Dashboard (Desktop)
            const mockupWrapper = document.getElementById('heroMockupWrapper');
            const mockup = document.getElementById('heroDashboardMockup');
            if (mockupWrapper && mockup && window.innerWidth >= 992) {
                mockupWrapper.addEventListener('mousemove', (e) => {
                    const rect = mockupWrapper.getBoundingClientRect();
                    const x = e.clientX - rect.left - (rect.width / 2);
                    const y = e.clientY - rect.top - (rect.height / 2);
                    const rotateX = -(y / rect.height) * 8;
                    const rotateY = (x / rect.width) * 8;
                    mockup.style.transform = `perspective(1000px) rotateX(${rotateX.toFixed(2)}deg) rotateY(${rotateY.toFixed(2)}deg) translateY(-4px)`;
                });

                mockupWrapper.addEventListener('mouseleave', () => {
                    mockup.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) translateY(0)';
                });
            }

            // 6. Animation Typing Machine à écrire dans le Titre Hero
            const typingElement = document.getElementById('typingText');
            if (typingElement) {
                const phrases = [
                    "Plus intelligente.",
                    "Plus simple.",
                    "Plus rapide.",
                    "Plus performante."
                ];

                const prefersReduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                if (!prefersReduced) {
                    let phraseIndex = 0;
                    let charIndex = phrases[0].length;
                    let isDeleting = true;
                    let typingTimeout = null;

                    const typeTick = () => {
                        const currentPhrase = phrases[phraseIndex];

                        if (isDeleting) {
                            charIndex--;
                            typingElement.textContent = currentPhrase.substring(0, charIndex);

                            if (charIndex === 0) {
                                isDeleting = false;
                                phraseIndex = (phraseIndex + 1) % phrases.length;
                                typingTimeout = setTimeout(typeTick, 300);
                                return;
                            }
                            typingTimeout = setTimeout(typeTick, 42);
                        } else {
                            charIndex++;
                            typingElement.textContent = currentPhrase.substring(0, charIndex);

                            if (charIndex === currentPhrase.length) {
                                isDeleting = true;
                                typingTimeout = setTimeout(typeTick, 1800);
                                return;
                            }
                            typingTimeout = setTimeout(typeTick, 85);
                        }
                    };

                    typingTimeout = setTimeout(typeTick, 1800);

                    window.addEventListener('beforeunload', () => {
                        if (typingTimeout) clearTimeout(typingTimeout);
                    });
                }
            }
        });
    </script>
</body>
</html>
