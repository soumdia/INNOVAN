<nav class="navbar navbar-expand-lg" style="background: linear-gradient(90deg, rgba(255,102,0,0.95), rgba(46,164,79,0.95));">
  <div class="container-fluid">
    <a class="navbar-brand text-white d-flex align-items-center" href="#">
      <svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="me-2">
        <circle cx="12" cy="12" r="10" fill="#fff" opacity="0.12"/>
        <path d="M12 6v6l4 2" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
      </svg>
      <span style="font-weight:700">CIE</span>
      <small class="ms-2 text-white-50">Gestion Coupures</small>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarSupportedContent">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item">
          <a class="nav-link text-white" href="/admin">Admin</a>
        </li>
        <li class="nav-item">
          <a class="nav-link text-white" href="/client">Client</a>
        </li>
      </ul>
      <div class="d-flex align-items-center">
        @if(\Illuminate\Support\Facades\Auth::check())
          <div class="me-3 text-white">Bonjour, {{ \Illuminate\Support\Facades\Auth::user()->name }}</div>
          <div class="dropdown">
            <button class="btn btn-sm btn-light dropdown-toggle" data-bs-toggle="dropdown">Compte</button>
            <ul class="dropdown-menu dropdown-menu-end">
              <li><a class="dropdown-item" href="/client">Mon espace</a></li>
              <li>
                <form method="POST" action="{{ route('logout') }}" class="m-0">
                  @csrf
                  <button class="dropdown-item text-danger">Déconnexion</button>
                </form>
              </li>
            </ul>
          </div>
        @else
          <a href="/login" class="btn btn-light btn-sm">Se connecter</a>
        @endif
      </div>
    </div>
  </div>
</nav>
