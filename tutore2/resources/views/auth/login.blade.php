@extends('layouts.app')

@section('title', 'Connexion — CIE')

@section('content')
<div class="auth-page">

  {{-- ====== PANNEAU GAUCHE — Photo agent CIE ====== --}}
  <div class="auth-illus auth-photo-panel"
       style="background-image:url('https://images.unsplash.com/photo-1621905252507-b35492cc74b4?auto=format&fit=crop&w=900&q=80')">

    {{-- Overlay dégradé sur la photo --}}
    <div class="auth-photo-overlay"></div>
    <div class="auth-grid-pattern"></div>

    <div class="auth-illus-content">

      {{-- Logo --}}
      <div class="auth-logo-row">
        <div class="auth-logo-icon">
          {{-- Logo microchip + éclair --}}
          <svg viewBox="0 0 24 24" fill="none" width="24" height="24" xmlns="http://www.w3.org/2000/svg">
            <rect x="5" y="5" width="14" height="14" rx="2" fill="rgba(255,255,255,.2)" stroke="white" stroke-width="1.4"/>
            <line x1="8"  y1="5"  x2="8"  y2="2"  stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="12" y1="5"  x2="12" y2="2"  stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="16" y1="5"  x2="16" y2="2"  stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="8"  y1="19" x2="8"  y2="22" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="12" y1="19" x2="12" y2="22" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="16" y1="19" x2="16" y2="22" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="5"  y1="9"  x2="2"  y2="9"  stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="5"  y1="15" x2="2"  y2="15" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="19" y1="9"  x2="22" y2="9"  stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <line x1="19" y1="15" x2="22" y2="15" stroke="white" stroke-width="1.4" stroke-linecap="round"/>
            <path d="M13.5 8L9.5 13H12.5L11 16L15.5 11H12.5L13.5 8Z" fill="white"/>
          </svg>
        </div>
        <div>
          <div class="auth-logo-name">INNOVAN-CI</div>
          <div class="auth-logo-sub">Pr&eacute;diction intelligente des coupures &eacute;lectriques</div>
        </div>
      </div>

      {{-- Espace pour pousser le contenu vers le bas --}}
      <div style="flex:1"></div>

      {{-- Contenu bas --}}
      <div class="auth-photo-bottom">

        <div class="auth-photo-badge">
          <i class="fas fa-shield-alt"></i> Plateforme officielle CIE
        </div>

        <h2 class="auth-photo-title">
          Gérez les coupures<br>avec <span style="color:var(--orange)">intelligence</span>
        </h2>
        <p class="auth-photo-desc">
          Système de prévention et d'alerte des coupures d'électricité
          en Côte d'Ivoire, piloté par intelligence artificielle.
        </p>

        <div class="auth-photo-stats">
          <div class="auth-photo-stat">
            <div class="auth-photo-stat-val">+850k</div>
            <div class="auth-photo-stat-lbl">Clients connectés</div>
          </div>
          <div class="auth-photo-stat-sep"></div>
          <div class="auth-photo-stat">
            <div class="auth-photo-stat-val">14</div>
            <div class="auth-photo-stat-lbl">Communes couvertes</div>
          </div>
          <div class="auth-photo-stat-sep"></div>
          <div class="auth-photo-stat">
            <div class="auth-photo-stat-val">3H</div>
            <div class="auth-photo-stat-lbl">Prévision à l'avance</div>
          </div>
        </div>

      </div>
    </div>
  </div>{{-- /panneau gauche --}}

  {{-- ====== PANNEAU DROIT — Formulaire ====== --}}
  <div class="auth-form-side">
    <div class="auth-form-box">

      {{-- Onglets --}}
      <div class="auth-tabs">
        <a href="{{ route('login') }}" class="auth-tab active">
          <i class="fas fa-sign-in-alt"></i> Connexion
        </a>
        <a href="{{ route('register') }}" class="auth-tab">
          <i class="fas fa-user-plus"></i> Créer un compte
        </a>
      </div>

      <div class="auth-form-header">
        <h1 class="auth-form-title">Bon retour !</h1>
        <p class="auth-form-sub">Connectez-vous à votre espace personnel CIE</p>
      </div>

      @if($errors->any())
        <div class="auth-alert-err">
          <i class="fas fa-exclamation-circle" style="flex-shrink:0"></i>
          {{ $errors->first() }}
        </div>
      @endif

      <form method="POST" action="{{ route('login.post') }}" autocomplete="on">
        @csrf

        <div class="fgroup">
          <label class="flabel" for="email">Adresse e-mail</label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-envelope"></i>
            <input id="email" type="email" name="email"
                   value="{{ old('email') }}"
                   class="finput" placeholder="votre@email.com"
                   required autocomplete="email" autofocus>
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel" for="passwordInput">Mot de passe</label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-lock"></i>
            <input id="passwordInput" type="password" name="password"
                   class="finput" placeholder="••••••••"
                   required autocomplete="current-password">
            <span class="finput-right" id="togglePassword" title="Afficher/masquer">
              <i class="fas fa-eye"></i>
            </span>
          </div>
        </div>

        <div class="remember-row">
          <label class="remember-label">
            <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
            Se souvenir de moi
          </label>
          <a href="#" class="forgot-link">Mot de passe oublié ?</a>
        </div>

        <button type="submit" class="btn-login">
          <i class="fas fa-sign-in-alt"></i>
          Se connecter
        </button>
      </form>

      <div class="auth-divider"><span>Nouveau sur la plateforme ?</span></div>

      <a href="{{ route('register') }}" class="btn-register-link">
        <i class="fas fa-user-plus"></i>
        Créer mon espace client CIE
      </a>

      <p class="login-footer-note" style="margin-top:20px">
        <i class="fas fa-shield-alt me-1" style="color:var(--green)"></i>
        Connexion sécurisée SSL · CIE © {{ date('Y') }}
      </p>

    </div>
  </div>

</div>
@endsection
