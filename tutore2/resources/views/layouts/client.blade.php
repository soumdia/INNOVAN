<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('title', 'INNOVAN-CI — Mon Espace')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/cie.css" rel="stylesheet">
</head>
<body>

<div class="client-wrap">

  {{-- ===== TOP NAV ===== --}}
  <header class="client-topnav">

    <div class="topnav-logo">
      <div class="topnav-logo-icon">
        <svg viewBox="0 0 24 24" fill="none" width="18" height="18" xmlns="http://www.w3.org/2000/svg">
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
      <div class="topnav-logo-text">
        <div class="topnav-logo-name">INNOVAN-CI</div>
        <div class="topnav-logo-sub">Mon Espace</div>
      </div>
    </div>

    <div class="topnav-right">

      {{-- Notifications --}}
      <div class="topnav-notif-btn" title="Notifications">
        <i class="fas fa-bell"></i>
        <span class="topnav-notif-dot"></span>
      </div>

      @auth
        {{-- Info utilisateur --}}
        <div class="topnav-user-pill" style="cursor:default">
          <div class="topnav-avatar">
            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
          </div>
          <div style="line-height:1.2">
            <div class="topnav-user-name">{{ Auth::user()->name }}</div>
            @if(Auth::user()->zone)
              <div style="font-size:.65rem;color:rgba(255,255,255,.4)">
                <i class="fas fa-map-marker-alt"></i> {{ Auth::user()->zone }}
              </div>
            @endif
          </div>
        </div>

        {{-- Bouton déconnexion — toujours visible --}}
        <form method="POST" action="{{ route('logout') }}" style="margin:0">
          @csrf
          <button type="submit" class="topnav-logout-btn" title="Se déconnecter">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
          </button>
        </form>
      @endauth

    </div>
  </header>{{-- /client-topnav --}}

  {{-- ===== PAGE CONTENT (hero + tabs + body injectés par la vue) ===== --}}
  @yield('content')

</div>{{-- /client-wrap --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/js/cie.js"></script>
@yield('scripts')

</body>
</html>
