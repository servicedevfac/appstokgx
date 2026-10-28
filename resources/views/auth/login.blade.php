<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>STOKGX — Connexion</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="{{ url('assets/images/logo-light.png') }}">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    :root {
      --primary-accent: #3b82f6;
      --primary-gold: #f5ba00;
      --bg-dark-base: #060e28;
      --card-glass: rgba(11, 23, 58, 0.76);
      --card-border: rgba(255, 255, 255, 0.16);
    }

    body {
      font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background-color: var(--bg-dark-base);
      position: relative;
      overflow-x: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* Luxury Canvas Background with Blue Gradients */
    .bg-canvas {
      position: fixed;
      inset: 0;
      z-index: 0;
      background:
        radial-gradient(ellipse 90% 70% at 50% -20%, rgba(29, 78, 216, 0.45) 0%, transparent 60%),
        radial-gradient(ellipse 65% 55% at 90% 85%, rgba(59, 130, 246, 0.22) 0%, transparent 60%),
        radial-gradient(ellipse 55% 55% at 10% 75%, rgba(15, 35, 95, 0.6) 0%, transparent 60%),
        linear-gradient(180deg, #091333 0%, #040817 100%);
    }

    /* Watermark / Logo en fond d'écran */
    .bg-watermark {
      position: fixed;
      inset: 0;
      z-index: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      pointer-events: none;
      overflow: hidden;
    }

    .bg-watermark img {
      width: min(620px, 85vw);
      max-height: 75vh;
      object-fit: contain;
      opacity: 0.12;
      filter: drop-shadow(0 0 60px rgba(59, 130, 246, 0.45));
      animation: watermarkFloat 12s ease-in-out infinite alternate;
      user-select: none;
      -webkit-user-drag: none;
    }

    @keyframes watermarkFloat {
      0% { transform: scale(1) translateY(0); }
      100% { transform: scale(1.05) translateY(-18px); }
    }

    /* Subtle geometric dots pattern */
    .bg-grid {
      position: fixed;
      inset: 0;
      z-index: 2;
      background-image: radial-gradient(rgba(255, 255, 255, 0.08) 1px, transparent 1px);
      background-size: 32px 32px;
      opacity: 0.6;
      pointer-events: none;
    }

    /* Ambient animated orbs */
    .ambient-orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(100px);
      pointer-events: none;
      z-index: 2;
      animation: pulseGlow 14s ease-in-out infinite alternate;
    }
    .orb-top {
      width: 440px;
      height: 440px;
      background: rgba(37, 99, 235, 0.25);
      top: -140px;
      left: 10%;
    }
    .orb-bottom {
      width: 380px;
      height: 380px;
      background: rgba(30, 64, 175, 0.3);
      bottom: -120px;
      right: 12%;
      animation-delay: -7s;
    }

    @keyframes pulseGlow {
      0% { transform: scale(1) translate(0, 0); opacity: 0.7; }
      50% { transform: scale(1.15) translate(25px, -20px); opacity: 1; }
      100% { transform: scale(0.95) translate(-20px, 20px); opacity: 0.7; }
    }

    /* Login container */
    .login-container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 445px;
      padding: 24px;
      animation: floatUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    @keyframes floatUp {
      from { opacity: 0; transform: translateY(35px) scale(0.97); }
      to { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Glass card */
    .login-card {
      background: var(--card-glass);
      backdrop-filter: blur(28px);
      -webkit-backdrop-filter: blur(28px);
      border: 1px solid var(--card-border);
      border-radius: 26px;
      padding: 42px 36px;
      box-shadow:
        0 30px 70px rgba(0, 0, 0, 0.6),
        0 0 40px rgba(59, 130, 246, 0.1),
        inset 0 1px 0 rgba(255, 255, 255, 0.18);
      position: relative;
      overflow: hidden;
    }

    .login-card::before {
      content: '';
      position: absolute;
      top: 0;
      left: 20%;
      right: 20%;
      height: 2px;
      background: linear-gradient(90deg, transparent, rgba(96, 165, 250, 0.8), transparent);
    }

    /* Logo area */
    .login-logo {
      text-align: center;
      margin-bottom: 30px;
    }

    .logo-badge {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      padding: 10px 18px;
      border-radius: 20px;
      background: rgba(255, 255, 255, 0.05);
      border: 1px solid rgba(255, 255, 255, 0.12);
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.35);
      margin-bottom: 14px;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .logo-badge:hover {
      transform: translateY(-2px);
      box-shadow: 0 14px 30px rgba(0, 0, 0, 0.45);
      border-color: rgba(96, 165, 250, 0.4);
    }

    .logo-badge img {
      height: 68px;
      width: auto;
      object-fit: contain;
      filter: drop-shadow(0 4px 12px rgba(0, 0, 0, 0.4));
    }

    .login-logo p {
      color: rgba(255, 255, 255, 0.65);
      font-size: 13.5px;
      margin-top: 4px;
      font-weight: 400;
      letter-spacing: 0.02em;
    }

    /* Status alert */
    .alert-status {
      background: rgba(16, 185, 129, 0.15);
      border: 1px solid rgba(16, 185, 129, 0.4);
      color: #6ee7b7;
      padding: 12px 16px;
      border-radius: 12px;
      font-size: 13px;
      margin-bottom: 20px;
    }

    /* Input groups */
    .input-group-modern {
      position: relative;
      margin-bottom: 20px;
    }

    .input-group-modern .input-icon {
      position: absolute;
      left: 16px;
      top: 50%;
      transform: translateY(-50%);
      color: rgba(255, 255, 255, 0.45);
      font-size: 15px;
      z-index: 2;
      transition: color 0.25s ease;
      pointer-events: none;
    }

    .input-group-modern input {
      width: 100%;
      padding: 14px 44px 14px 46px;
      background: rgba(255, 255, 255, 0.06);
      border: 1.5px solid rgba(255, 255, 255, 0.14);
      border-radius: 14px;
      color: #ffffff;
      font-size: 14px;
      font-family: inherit;
      font-weight: 400;
      transition: all 0.25s ease;
      outline: none;
    }

    .input-group-modern input::placeholder {
      color: rgba(255, 255, 255, 0.4);
    }

    .input-group-modern input:focus {
      background: rgba(255, 255, 255, 0.1);
      border-color: #60a5fa;
      box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.25);
    }

    .input-group-modern input:focus ~ .input-icon {
      color: #93c5fd;
    }

    .btn-toggle-pwd {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: transparent;
      border: none;
      color: rgba(255, 255, 255, 0.45);
      cursor: pointer;
      font-size: 15px;
      padding: 4px 6px;
      z-index: 2;
      transition: color 0.2s ease;
    }

    .btn-toggle-pwd:hover {
      color: #ffffff;
    }

    .input-group-modern .is-invalid {
      border-color: rgba(239, 68, 68, 0.7) !important;
    }

    .invalid-feedback {
      color: #fca5a5;
      font-size: 12.5px;
      margin-top: 6px;
      padding-left: 4px;
    }

    /* Submit button */
    .btn-login {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 14px;
      background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #3b82f6 100%);
      background-size: 200% 200%;
      color: #ffffff;
      font-size: 15px;
      font-weight: 700;
      letter-spacing: 0.02em;
      cursor: pointer;
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
      margin-top: 8px;
      box-shadow: 0 10px 25px rgba(37, 99, 235, 0.4);
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
    }

    .btn-login:hover {
      background-position: right center;
      transform: translateY(-2px);
      box-shadow: 0 14px 32px rgba(37, 99, 235, 0.55);
      color: #ffffff;
    }

    .btn-login:active {
      transform: translateY(0);
    }

    /* Footer text */
    .login-footer {
      text-align: center;
      margin-top: 28px;
      color: rgba(255, 255, 255, 0.4);
      font-size: 12.5px;
    }

    .login-footer a {
      color: #93c5fd;
      text-decoration: none;
      font-weight: 600;
      transition: color 0.2s ease;
    }

    .login-footer a:hover {
      color: #bfdbfe;
      text-decoration: underline;
    }

    /* Responsive */
    @media (max-width: 480px) {
      .login-container {
        padding: 16px;
      }
      .login-card {
        padding: 34px 22px;
        border-radius: 22px;
      }
      .logo-badge img {
        height: 56px;
      }
      .bg-watermark img {
        width: 90vw;
        opacity: 0.09;
      }
    }
  </style>
</head>
<body>
  <!-- Modern Clean Background with logo-light.png as Watermark -->
  <div class="bg-canvas"></div>
  <div class="bg-watermark">
    <img src="{{ url('assets/images/logo-light.png') }}" alt="Filigrane Fond">
  </div>
  <div class="bg-grid"></div>
  <div class="ambient-orb orb-top"></div>
  <div class="ambient-orb orb-bottom"></div>

  <!-- Login form -->
  <div class="login-container">
    <div class="login-card">
      <div class="login-logo">
        <div class="logo-badge">
          <img src="{{ url('assets/images/logo-light.png') }}" alt="Logo STOKGX">
        </div>
        <p>Connectez-vous à votre espace</p>
      </div>

      @if (session('status'))
        <div class="alert-status">
          <i class="fas fa-check-circle me-2"></i>{{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email -->
        <div class="input-group-modern">
          <i class="fas fa-envelope input-icon"></i>
          <input
            type="email"
            name="email"
            placeholder="Adresse email"
            class="@error('email') is-invalid @enderror"
            value="{{ old('email') }}"
            required
            autofocus
            autocomplete="email"
          >
          @error('email')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <!-- Password -->
        <div class="input-group-modern">
          <i class="fas fa-lock input-icon"></i>
          <input
            type="password"
            id="loginPassword"
            name="password"
            placeholder="Mot de passe"
            class="@error('password') is-invalid @enderror"
            required
            autocomplete="current-password"
          >
          <button type="button" class="btn-toggle-pwd" id="togglePasswordBtn" aria-label="Afficher le mot de passe">
            <i class="fas fa-eye" id="togglePasswordIcon"></i>
          </button>
          @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login">
          <span>Se connecter</span>
          <i class="fas fa-arrow-right"></i>
        </button>
      </form>

      <div class="login-footer">
        &copy; <script>document.write(new Date().getFullYear())</script> <strong>STOKGX</strong> — Solution de Gestion de Stock
      </div>
    </div>
  </div>

  <script>
    const toggleBtn = document.getElementById('togglePasswordBtn');
    const pwdInput = document.getElementById('loginPassword');
    const pwdIcon = document.getElementById('togglePasswordIcon');

    if (toggleBtn && pwdInput && pwdIcon) {
      toggleBtn.addEventListener('click', function () {
        const isPassword = pwdInput.getAttribute('type') === 'password';
        pwdInput.setAttribute('type', isPassword ? 'text' : 'password');
        pwdIcon.className = isPassword ? 'fas fa-eye-slash' : 'fas fa-eye';
      });
    }
  </script>
</body>
</html>
