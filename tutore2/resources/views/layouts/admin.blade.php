<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <title>@yield('title', 'INNOVAN-CI — Administration')</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="/css/cie.css" rel="stylesheet">
  {{-- Leaflet CSS dans le head pour éviter le flash de carte non stylée --}}
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
  <style>
    /* Leaflet map popups / tooltips — style INNOVAN-CI */
    .cie-map-popup .leaflet-popup-content-wrapper {
      border-radius: 12px;
      box-shadow: 0 8px 24px rgba(0,0,0,.12);
      border: none;
      padding: 4px;
    }
    .cie-map-popup .leaflet-popup-content { margin: 10px 14px; }
    .cie-map-popup .leaflet-popup-tip-container { display: none; }
    .cie-map-tooltip {
      background: #0f172a;
      color: #fff;
      border: none;
      border-radius: 6px;
      font-size: .72rem;
      font-weight: 700;
      padding: 3px 8px;
      box-shadow: 0 2px 8px rgba(0,0,0,.2);
    }
    .cie-map-tooltip::before { display: none; }
    #riskMap { border-radius: 0 0 16px 16px; }
  </style>
</head>
<body>

<div class="admin-wrap">

  {{-- ===== SIDEBAR ===== --}}
  <aside class="sidebar" id="sidebar">

    <div class="sidebar-header">
      <div class="sidebar-brand-icon">
        {{-- Logo : microchip + éclair (énergie + informatique) --}}
        <svg viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
          <rect x="5" y="5" width="14" height="14" rx="2" fill="rgba(255,255,255,.18)" stroke="white" stroke-width="1.4"/>
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
      <div class="sidebar-brand-text">
        @auth
          <div class="sidebar-brand-name">INNOVAN-CI</div>
          @if(Auth::user()->role === 'admin')
            <div class="sidebar-brand-sub">Administration g&eacute;n&eacute;rale</div>
          @else
            <div class="sidebar-brand-sub">Agent · {{ Auth::user()->zone ?? 'Toutes zones' }}</div>
          @endif
        @endauth
      </div>
    </div>

    <nav class="sidebar-body">

      <div class="sidebar-section-title">Principal</div>

      <a href="/admin" class="nav-link-cie {{ request()->is('admin') ? 'active' : '' }}">
        <i class="fas fa-chart-pie"></i>
        <span>Tableau de bord</span>
      </a>

      <a href="/admin/alerts" class="nav-link-cie {{ request()->is('admin/alerts') ? 'active' : '' }}">
        <i class="fas fa-bell"></i>
        <span>Alertes pr&eacute;dites</span>
        @php $alertCount = \App\Models\Alert::where('start_at','>=',now())->when(Auth::user()->role==='agent'&&Auth::user()->zone,fn($q)=>$q->where('zone',Auth::user()->zone))->count(); @endphp
        @if($alertCount > 0)
          <span class="nav-link-badge">{{ $alertCount }}</span>
        @endif
      </a>

      <a href="{{ route('admin.zones') }}" class="nav-link-cie {{ request()->is('admin/zones') ? 'active' : '' }}">
        <i class="fas fa-map-marked-alt"></i>
        <span>Zones &amp; Communes</span>
      </a>

      <a href="{{ route('admin.coupures') }}" class="nav-link-cie {{ request()->is('admin/coupures') ? 'active' : '' }}">
        <i class="fas fa-bolt"></i>
        <span>Coupures en cours</span>
        @php $enCoursCount = \App\Models\Alert::where('start_at','<=',now())->where(fn($q)=>$q->whereNull('end_at')->orWhere('end_at','>=',now()))->count(); @endphp
        @if($enCoursCount > 0)
          <span class="nav-link-badge" style="background:var(--red)">{{ $enCoursCount }}</span>
        @endif
      </a>

      {{-- Intelligence Artificielle / Terrain ─────────────────── --}}
      @auth
      @if(Auth::user()->role === 'admin')
        {{-- Admin : section complète IA --}}
        <div class="sidebar-section-title">Intelligence Artificielle</div>

        <a href="{{ route('dataset.index') }}" class="nav-link-cie {{ request()->is('admin/dataset*') ? 'active' : '' }}">
          <i class="fas fa-layer-group"></i>
          <span>Donn&eacute;es &amp; Dataset</span>
        </a>

        <a href="{{ route('admin.prediction') }}" class="nav-link-cie {{ request()->is('admin/prediction') ? 'active' : '' }}">
          <i class="fas fa-brain"></i>
          <span>R&eacute;sultats IA</span>
        </a>
      @else
        {{-- Agent : accès terrain uniquement (pas de prédiction) --}}
        <div class="sidebar-section-title">Terrain</div>

        <a href="{{ route('dataset.index') }}" class="nav-link-cie {{ request()->is('admin/dataset*') ? 'active' : '' }}">
          <i class="fas fa-hard-hat"></i>
          <span>Postes &amp; Terrain</span>
        </a>
      @endif
      @endauth

      <div class="sidebar-section-title">Gestion</div>

      <a href="{{ route('admin.clients') }}" class="nav-link-cie {{ request()->is('admin/clients') ? 'active' : '' }}">
        <i class="fas fa-users"></i>
        <span>Clients</span>
      </a>

      {{-- Agents — ADMIN uniquement --}}
      @auth
      @if(Auth::user()->role === 'admin')
      <a href="{{ route('admin.agents') }}" class="nav-link-cie {{ request()->is('admin/agents') ? 'active' : '' }}">
        <i class="fas fa-user-tie"></i>
        <span>Agents</span>
      </a>
      @endif
      @endauth

      <a href="{{ route('admin.signalements') }}" class="nav-link-cie {{ request()->is('admin/signalements') ? 'active' : '' }}">
        <i class="fas fa-exclamation-triangle"></i>
        <span>Signalements</span>
        @php $nbSig = \App\Models\Incident::where('source_type','client')->where('resolved',false)->count(); @endphp
        @if($nbSig > 0)
          <span class="nav-link-badge" style="background:var(--red)">{{ $nbSig }}</span>
        @endif
      </a>

      <a href="{{ route('admin.rapports') }}" class="nav-link-cie {{ request()->is('admin/rapports') ? 'active' : '' }}">
        <i class="fas fa-chart-bar"></i>
        <span>Rapports</span>
      </a>

    </nav>

    <div class="sidebar-footer">
      @auth
        <div class="sidebar-user">
          <div class="user-avatar">
            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
          </div>
          <div>
            <div class="user-name">{{ Auth::user()->name }}</div>
            <div class="user-role">
              @if(Auth::user()->role === 'admin')
                Administrateur INNOVAN-CI
              @else
                Agent terrain · {{ Auth::user()->zone ?? 'Toutes zones' }}
              @endif
            </div>
          </div>
        </div>
        <form method="POST" action="{{ route('logout') }}">
          @csrf
          <button type="submit" class="nav-logout">
            <i class="fas fa-sign-out-alt"></i>
            <span>Déconnexion</span>
          </button>
        </form>
      @endauth
    </div>

  </aside>{{-- /sidebar --}}

  {{-- ===== MAIN ===== --}}
  <div class="admin-main">

    {{-- Topbar --}}
    <div class="topbar">
      <div class="topbar-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Menu">
          <i class="fas fa-bars"></i>
        </button>
        <span class="topbar-page-title">@yield('page-title', 'Tableau de bord')</span>
      </div>

      <div class="topbar-right">
        {{-- Notifications --}}
        <div class="topbar-icon-btn" title="Notifications">
          <i class="fas fa-bell" style="font-size:.9rem"></i>
          <span class="notif-dot"></span>
        </div>

        {{-- Refresh --}}
        <div class="topbar-icon-btn" title="Actualiser" onclick="location.reload()">
          <i class="fas fa-sync-alt" style="font-size:.85rem"></i>
        </div>

        @auth
          {{-- User dropdown --}}
          <div class="dropdown">
            <button class="topbar-user-btn border-0" data-bs-toggle="dropdown">
              <div class="topbar-avatar">
                {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
              </div>
              <span class="topbar-user-name">{{ Auth::user()->name }}</span>
              <i class="fas fa-chevron-down" style="font-size:.7rem;color:var(--muted)"></i>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm border" style="border-radius:12px;padding:8px">
              <li>
                <a class="dropdown-item" href="#" style="border-radius:8px;font-size:.875rem">
                  <i class="fas fa-user me-2" style="color:var(--muted)"></i> Mon profil
                </a>
              </li>
              <li>
                <a class="dropdown-item" href="#" style="border-radius:8px;font-size:.875rem">
                  <i class="fas fa-cog me-2" style="color:var(--muted)"></i> Paramètres
                </a>
              </li>
              <li><hr class="dropdown-divider my-1"></li>
              <li>
                <form method="POST" action="{{ route('logout') }}">
                  @csrf
                  <button class="dropdown-item text-danger" style="border-radius:8px;font-size:.875rem">
                    <i class="fas fa-sign-out-alt me-2"></i> Déconnexion
                  </button>
                </form>
              </li>
            </ul>
          </div>
        @endauth
      </div>
    </div>{{-- /topbar --}}

    {{-- Page content --}}
    <div class="page-content">
      @yield('content')
    </div>

  </div>{{-- /admin-main --}}

</div>{{-- /admin-wrap --}}

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="/js/cie.js"></script>
@yield('scripts')

</body>
</html>
