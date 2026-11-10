@extends('layouts.app')

@section('title', 'Créer un compte — INNOVAN-CI')

@section('content')
<div class="auth-page">

  {{-- ====== PANNEAU GAUCHE ====== --}}
  <div class="auth-illus auth-photo-panel"
       style="background-image:url('https://images.unsplash.com/photo-1473341304170-971dccb5ac1e?auto=format&fit=crop&w=900&q=80')">

    <div class="auth-photo-overlay" style="background:linear-gradient(160deg,rgba(0,20,40,.88) 0%,rgba(26,100,180,.40) 100%)"></div>
    <div class="auth-grid-pattern"></div>

    <div class="auth-illus-content">

      {{-- Logo --}}
      <div class="auth-logo-row">
        <div class="auth-logo-icon">
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
          <div class="auth-logo-sub">Prédiction intelligente des coupures électriques</div>
        </div>
      </div>

      <div style="flex:1"></div>

      <div class="auth-photo-bottom">

        <div class="auth-photo-badge" style="background:rgba(26,158,63,.25);border-color:rgba(26,158,63,.4)">
          <i class="fas fa-user-check"></i> Inscription gratuite
        </div>

        <h2 class="auth-photo-title">
          Rejoignez l'espace<br>client <span style="color:#4ade80">CIE</span>
        </h2>
        <p class="auth-photo-desc">
          Recevez des alertes personnalisées selon votre commune,
          suivez votre consommation et payez vos factures en ligne.
        </p>

        <div class="auth-photo-features">
          <div class="auth-photo-feat">
            <div class="auth-photo-feat-icon" style="background:rgba(26,158,63,.25)">
              <i class="fas fa-map-marker-alt" style="color:#4ade80"></i>
            </div>
            <div>
              <div class="auth-photo-feat-title">Alertes ciblées</div>
              <div class="auth-photo-feat-sub">Uniquement votre commune</div>
            </div>
          </div>
          <div class="auth-photo-feat">
            <div class="auth-photo-feat-icon" style="background:rgba(255,102,0,.2)">
              <i class="fas fa-bolt" style="color:#fb923c"></i>
            </div>
            <div>
              <div class="auth-photo-feat-title">Consommation en temps réel</div>
              <div class="auth-photo-feat-sub">Historique et statistiques</div>
            </div>
          </div>
          <div class="auth-photo-feat">
            <div class="auth-photo-feat-icon" style="background:rgba(59,130,246,.2)">
              <i class="fas fa-mobile-alt" style="color:#60a5fa"></i>
            </div>
            <div>
              <div class="auth-photo-feat-title">Paiement Mobile Money</div>
              <div class="auth-photo-feat-sub">Orange, MTN, Moov</div>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>{{-- /panneau gauche --}}

  {{-- ====== PANNEAU DROIT — Formulaire inscription ====== --}}
  <div class="auth-form-side" style="align-items:flex-start;overflow-y:auto;padding:36px 48px">
    <div class="auth-form-box" style="max-width:460px;width:100%;padding:8px 0">

      <div class="auth-tabs">
        <a href="{{ route('login') }}" class="auth-tab">
          <i class="fas fa-sign-in-alt"></i> Connexion
        </a>
        <a href="{{ route('register') }}" class="auth-tab active">
          <i class="fas fa-user-plus"></i> Créer un compte
        </a>
      </div>

      <div class="auth-form-header">
        <h1 class="auth-form-title">Créer votre espace 🏠</h1>
        <p class="auth-form-sub">Inscrivez-vous avec les coordonnées de votre compteur CIE</p>
      </div>

      @if($errors->any())
        <div class="auth-alert-err">
          <i class="fas fa-exclamation-circle" style="flex-shrink:0;margin-top:2px"></i>
          <div>
            @foreach($errors->all() as $e)
              <div>{{ $e }}</div>
            @endforeach
          </div>
        </div>
      @endif

      <form method="POST" action="{{ route('register.post') }}" autocomplete="on">
        @csrf

        {{-- Section 1 --}}
        <div class="reg-section-title">
          <div class="reg-step">1</div>
          Informations personnelles
        </div>

        <div class="fgroup">
          <label class="flabel" for="name">Nom complet <span style="color:var(--red)">*</span></label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-user"></i>
            <input id="name" type="text" name="name" value="{{ old('name') }}"
                   class="finput" placeholder="Konan Jean-Baptiste"
                   required autocomplete="name">
          </div>
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="fgroup">
            <label class="flabel" for="email">E-mail <span style="color:var(--red)">*</span></label>
            <div class="finput-wrap">
              <i class="finput-icon fas fa-envelope"></i>
              <input id="email" type="email" name="email" value="{{ old('email') }}"
                     class="finput" placeholder="votre@email.com"
                     required autocomplete="email">
            </div>
          </div>
          <div class="fgroup">
            <label class="flabel" for="phone">Téléphone</label>
            <div class="finput-wrap">
              <i class="finput-icon fas fa-phone"></i>
              <input id="phone" type="tel" name="phone" value="{{ old('phone') }}"
                     class="finput" placeholder="225 07 XX XX XX">
            </div>
          </div>
        </div>

        {{-- Section 2 --}}
        <div class="reg-section-title">
          <div class="reg-step">2</div>
          Sécurité
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="fgroup">
            <label class="flabel" for="password">Mot de passe <span style="color:var(--red)">*</span></label>
            <div class="finput-wrap">
              <i class="finput-icon fas fa-lock"></i>
              <input id="password" type="password" name="password"
                     class="finput" placeholder="Min. 8 caractères"
                     required autocomplete="new-password">
            </div>
          </div>
          <div class="fgroup">
            <label class="flabel" for="password_confirmation">Confirmer <span style="color:var(--red)">*</span></label>
            <div class="finput-wrap">
              <i class="finput-icon fas fa-lock"></i>
              <input id="password_confirmation" type="password" name="password_confirmation"
                     class="finput" placeholder="Répéter"
                     required autocomplete="new-password">
            </div>
          </div>
        </div>

        {{-- Section 3 --}}
        <div class="reg-section-title">
          <div class="reg-step" style="background:var(--orange)">3</div>
          Votre compteur CIE
        </div>

        <div class="reg-meter-hint">
          <i class="fas fa-info-circle" style="flex-shrink:0;margin-top:2px"></i>
          Ces informations figurent sur votre <strong>facture CIE</strong> ou directement sur votre compteur électrique.
        </div>

        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px">
          <div class="fgroup">
            <label class="flabel" for="zone">Commune <span style="color:var(--red)">*</span></label>
            <div class="finput-wrap">
              <i class="finput-icon fas fa-map-marker-alt"></i>
              <select id="zone" name="zone" class="finput" style="padding-left:42px;cursor:pointer" required>
                <option value="" disabled {{ old('zone') ? '' : 'selected' }}>Choisir la commune</option>
                @php
                  $communes = [
                    'Abidjan — Centre' => ['Plateau','Adjamé','Treichville','Attécoubé'],
                    'Abidjan — Nord'   => ['Abobo','Anyama','Yopougon','Songon'],
                    'Abidjan — Est'    => ['Cocody','Bingerville'],
                    'Abidjan — Sud'    => ['Port-Bouët','Marcory','Koumassi'],
                    'Autres villes'    => ['Bouaké','Yamoussoukro','San-Pédro','Daloa','Abengourou','Man','Korhogo'],
                  ];
                @endphp
                @foreach($communes as $group => $list)
                  <optgroup label="{{ $group }}">
                    @foreach($list as $c)
                      <option value="{{ $c }}" {{ old('zone') === $c ? 'selected' : '' }}>{{ $c }}</option>
                    @endforeach
                  </optgroup>
                @endforeach
              </select>
            </div>
          </div>

          <div class="fgroup">
            <label class="flabel" for="meter_number">N° de compteur <span style="color:var(--red)">*</span></label>
            <div class="finput-wrap">
              <i class="finput-icon fas fa-tachometer-alt"></i>
              <input id="meter_number" type="text" name="meter_number"
                     value="{{ old('meter_number') }}"
                     class="finput" placeholder="ex : 0012345678"
                     required maxlength="20"
                     pattern="[0-9A-Za-z\-]{6,20}">
            </div>
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel" for="address">Adresse de l'installation <span style="color:var(--muted);font-weight:400">(optionnel)</span></label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-home"></i>
            <input id="address" type="text" name="address" value="{{ old('address') }}"
                   class="finput" placeholder="Rue, quartier, immeuble...">
          </div>
        </div>

        {{-- Section 4 — Langue de notification --}}
        <div class="reg-section-title">
          <div class="reg-step" style="background:#6366f1">4</div>
          Langue de notification
        </div>

        <p style="font-size:.82rem;color:var(--muted);margin-bottom:12px">
          Choisissez la langue dans laquelle vous souhaitez recevoir vos alertes CIE.
        </p>

        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px">
          @php
            $langs = [
              'fr'     => ['label' => 'Français',  'sub' => 'Standard',          'icon' => '🇫🇷'],
              'dioula' => ['label' => 'Dioula',    'sub' => 'Nord CI',            'icon' => '🗣️'],
              'baoule' => ['label' => 'Baoulé',    'sub' => 'Centre CI',          'icon' => '🗣️'],
              'bete'   => ['label' => 'Bété',      'sub' => 'Centre-Ouest CI',    'icon' => '🗣️'],
              'nouchi' => ['label' => 'Nouchi',    'sub' => 'Abidjan',            'icon' => '🏙️'],
            ];
            $selectedLang = old('language', 'fr');
          @endphp

          @foreach($langs as $code => $info)
            <label class="lang-card {{ $selectedLang === $code ? 'lang-card--active' : '' }}"
                   for="lang_{{ $code }}"
                   onclick="document.querySelectorAll('.lang-card').forEach(c=>c.classList.remove('lang-card--active'));this.classList.add('lang-card--active')">
              <input type="radio" id="lang_{{ $code }}" name="language"
                     value="{{ $code }}" {{ $selectedLang === $code ? 'checked' : '' }}
                     style="display:none">
              <span style="font-size:1.3rem">{{ $info['icon'] }}</span>
              <span style="font-weight:600;font-size:.88rem">{{ $info['label'] }}</span>
              <span style="font-size:.73rem;color:var(--muted)">{{ $info['sub'] }}</span>
            </label>
          @endforeach
        </div>

        <style>
          .lang-card {
            display:flex;flex-direction:column;align-items:center;gap:3px;
            padding:10px 6px;border:2px solid var(--border);border-radius:10px;
            cursor:pointer;transition:all .18s;text-align:center;background:var(--card);
          }
          .lang-card:hover { border-color:#6366f1;background:rgba(99,102,241,.06); }
          .lang-card--active { border-color:#6366f1;background:rgba(99,102,241,.1); }
        </style>

        {{-- CGU --}}
        <div style="display:flex;align-items:flex-start;gap:10px;margin-bottom:22px">
          <input type="checkbox" id="terms" required
                 style="margin-top:3px;accent-color:var(--orange);flex-shrink:0;width:16px;height:16px;cursor:pointer">
          <label for="terms" style="font-size:.82rem;color:var(--muted);cursor:pointer;line-height:1.6">
            J'accepte les <a href="#" style="color:var(--orange);font-weight:600">conditions d'utilisation</a>
            et la <a href="#" style="color:var(--orange);font-weight:600">politique de confidentialité</a> de la CIE.
          </label>
        </div>

        <button type="submit" class="btn-login" style="background:linear-gradient(135deg,var(--green),#157a30)">
          <i class="fas fa-user-check"></i>
          Créer mon compte CIE
        </button>

      </form>

      <div class="auth-divider"><span>Déjà inscrit ?</span></div>

      <a href="{{ route('login') }}" class="btn-register-link" style="border-color:var(--green);color:var(--green)">
        <i class="fas fa-sign-in-alt"></i>
        Se connecter à mon espace
      </a>

    </div>
  </div>

</div>
@endsection
