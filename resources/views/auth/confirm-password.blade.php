<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>STOKGX — Confirmation du mot de passe</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"/>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="shortcut icon" href="{{ url('assets/images/logo-light.png') }}">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; }

    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      background: #0d1642;
      position: relative;
      overflow: hidden;
      -webkit-font-smoothing: antialiased;
    }

    /* Animated background */
    .bg-pattern {
      position: fixed;
      inset: 0;
      z-index: 0;
      background: #0d1642;
    }

    .bg-pattern::before {
      content: '';
      position: absolute;
      inset: 0;
      background: url('{{ url('assets/images/bg.png') }}') no-repeat center center;
      background-size: cover;
      opacity: .1;
    }

    /* Floating orbs */
    .orb {
      position: fixed;
      border-radius: 50%;
      filter: blur(80px);
      opacity: .3;
      animation: float 8s ease-in-out infinite;
    }
    .orb-1 { width: 300px; height: 300px; background: #1a237e; top: -10%; left: -5%; animation-delay: 0s; }
    .orb-2 { width: 250px; height: 250px; background: #f9a825; bottom: -10%; right: -5%; animation-delay: 2s; }
    .orb-3 { width: 200px; height: 200px; background: #283593; top: 50%; right: 20%; animation-delay: 4s; }

    @keyframes float {
      0%, 100% { transform: translateY(0) scale(1); }
      50% { transform: translateY(-30px) scale(1.05); }
    }

    /* Login container */
    .login-container {
      position: relative;
      z-index: 10;
      width: 100%;
      max-width: 440px;
      padding: 20px;
      animation: slideUp .6s cubic-bezier(.16,1,.3,1) both;
    }

    @keyframes slideUp {
      from { opacity: 0; transform: translateY(40px); }
      to { opacity: 1; transform: translateY(0); }
    }

    /* Glass card */
    .login-card {
      background: rgba(255,255,255,.07);
      backdrop-filter: blur(24px);
      -webkit-backdrop-filter: blur(24px);
      border: 1px solid rgba(255,255,255,.1);
      border-radius: 24px;
      padding: 48px 40px;
      box-shadow:
        0 32px 64px rgba(0,0,0,.3),
        inset 0 1px 0 rgba(255,255,255,.1);
    }

    /* Logo area */
    .login-logo {
      text-align: center;
      margin-bottom: 32px;
    }

    .login-logo img {
      height: 60px;
      margin-bottom: 16px;
      filter: brightness(1.2);
    }

    .login-logo h1 {
      color: #ffffff;
      font-size: 1.5rem;
      font-weight: 700;
      letter-spacing: -0.025em;
      margin: 0;
    }

    .login-logo p {
      color: rgba(255,255,255,.7);
      font-size: 14px;
      margin-top: 10px;
      line-height: 1.5;
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
      color: rgba(255,255,255,.4);
      font-size: 15px;
      z-index: 2;
      transition: color .25s ease;
    }

    .input-group-modern input {
      width: 100%;
      padding: 14px 16px 14px 46px;
      background: rgba(255,255,255,.08);
      border: 1.5px solid rgba(255,255,255,.1);
      border-radius: 12px;
      color: #ffffff;
      font-size: 14px;
      font-family: 'Inter', sans-serif;
      font-weight: 400;
      transition: all .25s ease;
      outline: none;
    }

    .input-group-modern input::placeholder {
      color: rgba(255,255,255,.4);
    }

    .input-group-modern input:focus {
      background: rgba(255,255,255,.12);
      border-color: rgba(249,168,37,.5);
      box-shadow: 0 0 0 3px rgba(249,168,37,.1);
    }

    .input-group-modern input:focus ~ .input-icon {
      color: #fbc02d;
    }

    .input-group-modern .is-invalid {
      border-color: rgba(239,68,68,.6) !important;
    }

    .invalid-feedback {
      color: #fca5a5;
      font-size: 12px;
      margin-top: 6px;
    }

    /* Submit button */
    .btn-login {
      width: 100%;
      padding: 14px;
      border: none;
      border-radius: 12px;
      background: #1a237e;
      color: #ffffff;
      font-size: 15px;
      font-weight: 600;
      font-family: 'Inter', sans-serif;
      cursor: pointer;
      transition: all .3s ease;
      position: relative;
      overflow: hidden;
      margin-top: 8px;
    }

    .btn-login:hover {
      background-position: right center;
      transform: translateY(-2px);
      box-shadow: 0 8px 24px rgba(26,35,126,.4);
    }

    .btn-login:active {
      transform: translateY(0);
    }

    .btn-login i {
      margin-right: 8px;
    }

    /* Responsive */
    @media (max-width: 480px) {
      .login-card {
        padding: 36px 24px;
        border-radius: 20px;
      }
    }
  </style>
</head>
<body>
  <!-- Background -->
  <div class="bg-pattern"></div>
  <div class="orb orb-1"></div>
  <div class="orb orb-2"></div>
  <div class="orb orb-3"></div>

  <!-- Form container -->
  <div class="login-container">
    <div class="login-card">
      <div class="login-logo">
        <div style="display:inline-flex; align-items:center; justify-content:center; padding:10px 18px; border-radius:20px; background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12); box-shadow:0 10px 25px rgba(0,0,0,0.35); margin-bottom:14px;">
          <img src="{{ url('assets/images/logo-light.png') }}" alt="Logo STOKGX" style="height:60px; width:auto; object-fit:contain;">
        </div>
        <h1>Zone sécurisée</h1>
        <p>Veuillez confirmer votre mot de passe pour continuer.</p>
      </div>

      <form method="POST" action="{{ route('password.confirm') }}">
        @csrf

        <!-- Password -->
        <div class="input-group-modern">
          <i class="fas fa-lock input-icon"></i>
          <input
            type="password"
            name="password"
            placeholder="Mot de passe"
            class="@error('password') is-invalid @enderror"
            required
            autocomplete="current-password"
            autofocus
          >
          @error('password')
            <div class="invalid-feedback d-block">{{ $message }}</div>
          @enderror
        </div>

        <!-- Submit -->
        <button type="submit" class="btn-login">
          <i class="fas fa-check-circle"></i> Confirmer
        </button>
      </form>

    </div>
  </div>

</body>
</html>
