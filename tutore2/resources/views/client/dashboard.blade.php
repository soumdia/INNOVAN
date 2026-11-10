@extends('layouts.client')

@section('title', 'INNOVAN-CI — Mon Espace')

@section('content')

{{-- ===== BANNIÈRE VOCALE ===== --}}
<div class="voice-alert-banner" id="voiceAlertBanner" style="display:none">
  <div class="voice-alert-inner">
    <div class="voice-alert-icon" id="voiceAlertIcon">
      <i class="fas fa-volume-up"></i>
    </div>
    <div class="voice-alert-content">
      <div class="voice-alert-title" id="voiceAlertTitle">Alerte coupure dans votre zone</div>
      <div class="voice-alert-sub" id="voiceAlertSub">Chargement...</div>
    </div>
    <div class="voice-alert-actions">
      <button class="voice-btn-play" id="voiceBtnPlay" onclick="playVoiceAlert()">
        <i class="fas fa-play"></i> Écouter
      </button>
      <button class="voice-btn-close" onclick="closeVoiceAlert()">
        <i class="fas fa-times"></i>
      </button>
    </div>
  </div>
</div>

{{-- ===== HERO v2 ===== --}}
@php
  $userZone    = $user->zone          ?? 'Non définie';
  $userMeter   = $user->meter_number  ?? 'N/A';
  $userName    = explode(' ', $user->name)[0];
  $userLang    = $user->language      ?? 'fr';
  $alertCount  = $alerts->count();
  $alertColor  = $alertCount > 0 ? '#f87171' : '#4ade80';
  $balance     = (float) ($user->balance ?? 0);
  $dailyKwh    = (float) ($user->daily_kwh ?? 0);
  $monthAmount = round($monthlyKwh * 35, 0);
  $balanceLow  = $balance < ($dailyKwh * 35 * 5);
  // Mode d'alerte : 3H si alertes automatiques dispo, sinon 5J
  $alertMode   = $alerts3h->count() > 0 ? '3h' : ($alerts5j->count() > 0 ? '5j' : null);
  $nextAlert   = $alerts->first();
@endphp

<div class="client-hero-v2">
  <div class="hero-dots"></div>
  <div class="hero-v2-inner">

    <div class="hero-v2-left">
      <div class="hero-v2-greeting">
        <span class="hero-v2-greeting-dot" style="{{ $alertCount > 0 ? 'background:var(--red);animation:pulse 1.2s infinite' : '' }}"></span>
        @if($alertCount > 0)
          <span style="color:#f87171;font-weight:700">{{ $alertCount }} coupure(s) prévue(s)</span>
        @else
          Alimentation normale
        @endif
        &middot; {{ now()->format('d M Y') }}
      </div>
      <h1 class="hero-v2-name">Bonjour, {{ $userName }} &#128075;</h1>
      <div class="hero-v2-chips">
        <div class="hero-v2-chip">
          <i class="fas fa-id-card" style="color:var(--orange)"></i> INNOVAN-CI
        </div>
        <div class="hero-v2-chip">
          <i class="fas fa-map-marker-alt" style="color:#4ade80"></i>
          Zone&nbsp;: {{ $userZone }}
        </div>
        <div class="hero-v2-chip">
          <i class="fas fa-tachometer-alt" style="color:#60a5fa"></i>
          Compteur&nbsp;: {{ $userMeter }}
        </div>
      </div>
    </div>

    <div class="hero-v2-stats">
      <div class="hero-v2-stat">
        <div class="hero-v2-stat-val" style="color:{{ $alertColor }}">{{ $alertCount }}</div>
        <div class="hero-v2-stat-lbl">Alertes à venir</div>
      </div>
      <div class="hero-v2-stat">
        <div class="hero-v2-stat-val">{{ number_format($monthlyKwh, 1) }}</div>
        <div class="hero-v2-stat-lbl">kWh ce mois</div>
      </div>
      <div class="hero-v2-stat">
        <div class="hero-v2-stat-val" style="color:{{ $balanceLow ? '#f87171' : '#4ade80' }}">
          {{ number_format($balance, 0, '.', ' ') }}
        </div>
        <div class="hero-v2-stat-lbl">
          FCFA solde
          @if($balanceLow)
            <a href="{{ route('client.recharge') }}" style="display:block;font-size:.65rem;color:#f87171;font-weight:700;margin-top:2px">⚡ Recharger</a>
          @endif
        </div>
      </div>
    </div>

  </div>
</div>

{{-- ===== ONGLETS ===== --}}
<div class="client-tabbar">
  <button class="ctab active" data-tab="home">
    <i class="fas fa-bolt"></i> Vue d'ensemble
  </button>
  <button class="ctab" data-tab="consumption">
    <i class="fas fa-chart-area"></i> Consommation
  </button>
  <button class="ctab" data-tab="billing">
    <i class="fas fa-wallet"></i> Mon solde
    @if($balanceLow)
      <span class="ctab-badge" style="background:#ef4444">!</span>
    @endif
  </button>
  <button class="ctab" data-tab="alerts">
    <i class="fas fa-bell"></i> Alertes
    @if($alertCount > 0)
      <span class="ctab-badge" id="unreadCount">{{ $alertCount }}</span>
    @endif
  </button>
  <button class="ctab" data-tab="signaler">
    <i class="fas fa-exclamation-triangle"></i> Signaler
  </button>
</div>

{{-- ===== CONTENU ONGLETS ===== --}}
<div class="client-body">

  {{-- ── ACCUEIL ── --}}
  <div class="tab-pane active" data-pane="home">
    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">

      {{-- Timeline coupures --}}
      <div>
        {{-- En-tête section --}}
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:8px">
          <h2 style="font-size:1rem;font-weight:800;color:var(--dark);margin:0;display:flex;align-items:center;gap:10px">
            <span style="width:8px;height:8px;border-radius:50%;background:{{ $alertCount > 0 ? 'var(--orange)' : 'var(--green)' }};display:inline-block"></span>
            Prochaines coupures &mdash; {{ $userZone }}
          </h2>
          @if($alertMode === '3h')
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:800;background:rgba(255,102,0,.1);color:var(--orange);padding:3px 10px;border-radius:20px">
              <i class="fas fa-robot" style="font-size:.6rem"></i> Prédiction automatique 3H
            </span>
          @elseif($alertMode === '5j')
            <span style="display:inline-flex;align-items:center;gap:5px;font-size:.7rem;font-weight:800;background:rgba(59,130,246,.1);color:var(--blue);padding:3px 10px;border-radius:20px">
              <i class="fas fa-calendar-alt" style="font-size:.6rem"></i> Prédiction manuelle J+1/J+5
            </span>
          @endif
        </div>

        @if($alerts3h->count() > 0)
          {{-- ── ALERTES 3H — Les plus proches et fiables ── --}}
          <div class="outage-timeline">
            @foreach($alerts3h->take(4) as $idx => $a)
              @php
                $prob   = intval($a->probability ?? 0);
                $aColor = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
                $aLabel = $prob >= 80 ? 'Urgent' : ($prob >= 60 ? 'Probable' : 'Possible');
                $aBadge = $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-blue');
                $isNow  = $a->start_at->lte(now()) && (!$a->end_at || $a->end_at->gte(now()));
              @endphp
              <div class="outage-timeline-item">
                <div class="outage-timeline-line">
                  <div class="outage-timeline-dot" style="background:{{ $aColor }}{{ $isNow ? ';animation:pulse 1s infinite' : '' }}"></div>
                  @unless($loop->last) <div class="outage-timeline-connector"></div> @endunless
                </div>
                <div class="outage-timeline-content">
                  <div class="outage-tl-header">
                    <div class="outage-tl-title">
                      @if($isNow)
                        <span style="font-size:.65rem;background:var(--red);color:#fff;padding:1px 6px;border-radius:8px;margin-right:5px;font-weight:800">EN COURS</span>
                      @endif
                      {{ $a->zone }}
                      @if($a->quartier)
                        <span style="font-size:.75rem;font-weight:500;color:var(--muted)"> — {{ $a->quartier }}</span>
                      @endif
                    </div>
                    <div class="outage-tl-time">
                      {{ $a->start_at->isToday() ? 'Auj.' : $a->start_at->format('d/m') }}
                      &middot; {{ $a->start_at->format('H:i') }}
                      @if($a->end_at) &rarr; {{ $a->end_at->format('H:i') }} @endif
                    </div>
                  </div>
                  <div class="outage-tl-reason">
                    <i class="fas fa-info-circle me-1" style="color:var(--muted)"></i>
                    {{ $a->reason ?? 'Raison non précisée' }}
                  </div>
                  <div class="outage-tl-prob">
                    <div class="outage-tl-prob-bar">
                      <div class="outage-tl-prob-fill" style="width:{{ $prob }}%;background:{{ $aColor }}"></div>
                    </div>
                    <span style="font-size:.75rem;font-weight:800;color:{{ $aColor }}">{{ $prob }}%</span>
                    <span class="badge-cie {{ $aBadge }}" style="font-size:.65rem">{{ $aLabel }}</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

        @elseif($alerts5j->count() > 0)
          {{-- ── ALERTES 5J — Prédictions manuelles si pas de 3H ── --}}
          <div style="background:rgba(59,130,246,.04);border:1px solid rgba(59,130,246,.15);border-radius:14px;padding:12px 16px;margin-bottom:14px;font-size:.78rem;color:var(--muted)">
            <i class="fas fa-info-circle me-1" style="color:var(--blue)"></i>
            Aucune alerte automatique 3H pour votre zone. Les prédictions ci-dessous proviennent d'une <strong>analyse manuelle</strong> — moins précises, à titre indicatif.
          </div>
          <div class="outage-timeline" style="opacity:.85">
            @foreach($alerts5j->take(4) as $a)
              @php
                $prob   = intval($a->probability ?? 0);
                $aColor = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
                $aLabel = $prob >= 80 ? 'Urgent' : ($prob >= 60 ? 'Probable' : 'Possible');
                $aBadge = $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-blue');
              @endphp
              <div class="outage-timeline-item">
                <div class="outage-timeline-line">
                  <div class="outage-timeline-dot" style="background:{{ $aColor }};opacity:.7"></div>
                  @unless($loop->last) <div class="outage-timeline-connector"></div> @endunless
                </div>
                <div class="outage-timeline-content">
                  <div class="outage-tl-header">
                    <div class="outage-tl-title">
                      <span style="font-size:.62rem;background:rgba(59,130,246,.15);color:var(--blue);padding:1px 6px;border-radius:8px;margin-right:5px;font-weight:700">5J</span>
                      {{ $a->zone }}
                      @if($a->quartier) <span style="font-size:.75rem;font-weight:500;color:var(--muted)"> — {{ $a->quartier }}</span> @endif
                    </div>
                    <div class="outage-tl-time">
                      {{ $a->start_at->format('d/m') }} &middot; {{ $a->start_at->format('H:i') }}
                      @if($a->end_at) &rarr; {{ $a->end_at->format('H:i') }} @endif
                    </div>
                  </div>
                  <div class="outage-tl-reason">
                    <i class="fas fa-info-circle me-1" style="color:var(--muted)"></i>
                    {{ $a->reason ?? 'Raison non précisée' }}
                  </div>
                  <div class="outage-tl-prob">
                    <div class="outage-tl-prob-bar">
                      <div class="outage-tl-prob-fill" style="width:{{ $prob }}%;background:{{ $aColor }}"></div>
                    </div>
                    <span style="font-size:.75rem;font-weight:800;color:{{ $aColor }}">{{ $prob }}%</span>
                    <span class="badge-cie {{ $aBadge }}" style="font-size:.65rem">{{ $aLabel }}</span>
                  </div>
                </div>
              </div>
            @endforeach
          </div>

        @else
          {{-- ── Aucune alerte ── --}}
          <div style="background:linear-gradient(135deg,rgba(26,158,63,.05),rgba(26,158,63,.02));border:1px solid rgba(26,158,63,.15);border-radius:16px;padding:48px 24px;text-align:center">
            <div style="width:64px;height:64px;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;font-size:1.5rem;color:var(--green)">
              <i class="fas fa-check"></i>
            </div>
            <h4 style="font-weight:800;color:var(--dark);margin-bottom:6px">R&eacute;seau stable</h4>
            <p style="color:var(--muted);font-size:.875rem">Aucune coupure pr&eacute;vue dans votre zone pour les prochaines heures.</p>
          </div>
        @endif
      </div>

      {{-- Statut réseau --}}
      <div>
        <h2 style="font-size:1rem;font-weight:800;color:var(--dark);margin-bottom:20px;display:flex;align-items:center;gap:10px">
          <span style="width:8px;height:8px;border-radius:50%;background:var(--green);display:inline-block"></span>
          Statut du r&eacute;seau
        </h2>
        <div class="section-card">
          <div style="padding:24px;text-align:center">
            <div style="position:relative;width:90px;height:90px;margin:0 auto 16px">
              <div style="position:absolute;inset:0;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;font-size:2rem;color:var(--green)">
                <i class="fas fa-bolt"></i>
              </div>
              <svg style="position:absolute;inset:0;transform:rotate(-90deg)" width="90" height="90">
                <circle cx="45" cy="45" r="40" fill="none" stroke="var(--border)" stroke-width="4"/>
                <circle cx="45" cy="45" r="40" fill="none" stroke="var(--green)" stroke-width="4"
                        stroke-dasharray="251.3" stroke-dashoffset="3.5"
                        stroke-linecap="round"/>
              </svg>
            </div>
            <div style="font-weight:900;color:var(--green);font-size:1.1rem;margin-bottom:4px">Actif</div>
            <div style="color:var(--muted);font-size:.78rem">98.6% uptime ce mois</div>
          </div>
          <div style="border-top:1px solid var(--border);padding:16px 20px;display:flex;flex-direction:column;gap:10px">
            @foreach([['Tension r&eacute;seau','220V'],['Fr&eacute;quence','50 Hz'],['Qualit&eacute; signal','Excellente'],['Uptime','98.6%']] as $row)
              <div style="display:flex;justify-content:space-between;font-size:.82rem">
                <span style="color:var(--muted)">{!! $row[0] !!}</span>
                <span style="font-weight:700;color:var(--dark)">{{ $row[1] }}</span>
              </div>
            @endforeach
          </div>
        </div>

        {{-- Sélecteur langue --}}
        <div style="margin-top:16px">
          <div style="font-size:.72rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.08em;margin-bottom:8px">
            <i class="fas fa-globe me-1"></i> Langue des alertes
          </div>
          <div style="display:flex;gap:6px;flex-wrap:wrap">
            @foreach(['fr'=>'Français','dioula'=>'Dioula','baoule'=>'Baoulé','bete'=>'Bété','nouchi'=>'Nouchi'] as $code => $label)
              <form method="POST" action="{{ route('client.set-lang') }}" style="margin:0">
                @csrf
                <input type="hidden" name="language" value="{{ $code }}">
                <button type="submit" style="padding:5px 10px;border-radius:20px;border:1.5px solid {{ $userLang===$code ? 'var(--orange)' : 'var(--border)' }};background:{{ $userLang===$code ? 'var(--orange)' : '#fff' }};color:{{ $userLang===$code ? '#fff' : 'var(--muted)' }};font-size:.72rem;font-weight:700;cursor:pointer">
                  {{ $label }}
                </button>
              </form>
            @endforeach
          </div>
        </div>
      </div>

    </div>
  </div>{{-- /home --}}

  {{-- ── CONSOMMATION ── --}}
  <div class="tab-pane" data-pane="consumption">

    @php
      $variation    = $lastMonthKwh > 0 ? round((($monthlyKwh - $lastMonthKwh) / $lastMonthKwh) * 100) : 0;
      $varColor     = $variation <= 0 ? 'var(--green)' : 'var(--red)';
      $varSign      = $variation > 0 ? '+' : '';
    @endphp
    <div class="cons-stat-grid">
      <div class="cons-stat">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
          <div class="kpi-icon i-orange" style="width:40px;height:40px;border-radius:10px"><i class="fas fa-bolt"></i></div>
          <div class="cons-stat-val">{{ number_format($monthlyKwh, 1) }} kWh</div>
        </div>
        <div class="cons-stat-lbl">Ce mois</div>
      </div>
      <div class="cons-stat">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
          <div class="kpi-icon i-blue" style="width:40px;height:40px;border-radius:10px"><i class="fas fa-chart-bar"></i></div>
          <div class="cons-stat-val">{{ number_format($dailyKwh, 1) }} kWh</div>
        </div>
        <div class="cons-stat-lbl">Moyenne / jour</div>
      </div>
      <div class="cons-stat">
        <div style="display:flex;align-items:center;gap:12px;margin-bottom:8px">
          <div class="kpi-icon i-green" style="width:40px;height:40px;border-radius:10px">
            <i class="fas fa-arrow-{{ $variation > 0 ? 'up' : 'down' }}"></i>
          </div>
          <div class="cons-stat-val" style="color:{{ $varColor }}">{{ $varSign }}{{ $variation }}%</div>
        </div>
        <div class="cons-stat-lbl">vs mois dernier</div>
      </div>
    </div>

    <div class="section-card mb-6">
      <div class="section-card-header">
        <div class="section-card-title">
          <span class="section-dot orange"></span> Consommation (7 derniers jours)
        </div>
        <span class="badge-cie badge-orange">kWh</span>
      </div>
      <div style="padding:24px">
        <canvas id="consumptionChart" height="110"></canvas>
      </div>
    </div>

    <div class="section-card">
      <div class="section-card-header">
        <div class="section-card-title">
          <span class="section-dot blue"></span> Historique mensuel
        </div>
      </div>
      <div class="table-wrap">
        <table class="cie-table">
          <thead>
            <tr><th>Mois</th><th>kWh</th><th>Montant</th></tr>
          </thead>
          <tbody>
            @forelse($monthlyHistory as $m)
              @php
                $label = \Carbon\Carbon::createFromFormat('Y-m', $m->month)->locale('fr')->isoFormat('MMMM YYYY');
              @endphp
              <tr>
                <td style="font-weight:600;text-transform:capitalize">{{ $label }}</td>
                <td>{{ number_format($m->total_kwh, 1) }}</td>
                <td style="font-weight:700">{{ number_format($m->total_fcfa, 0, '.', ' ') }} FCFA</td>
              </tr>
            @empty
              <tr><td colspan="3" style="text-align:center;color:var(--muted);padding:24px">Aucune donnée disponible</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>

  </div>{{-- /consumption --}}

  {{-- ── MON SOLDE ── --}}
  <div class="tab-pane" data-pane="billing">
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px">

      {{-- Solde actuel --}}
      <div>
        <div class="invoice-v2">
          <div class="invoice-v2-top">
            <div>
              <div style="color:rgba(255,255,255,.5);font-size:.75rem;margin-bottom:4px">Compteur prépayé &middot; {{ $userMeter }}</div>
              <div class="invoice-v2-amount">{{ number_format($balance, 0, '.', ' ') }} <span style="font-size:1.2rem;font-weight:400;opacity:.6">FCFA</span></div>
              <div class="invoice-v2-due">
                <i class="fas fa-bolt me-1"></i>
                {{ number_format($dailyKwh, 1) }} kWh/jour &middot; ~{{ number_format($dailyKwh * 35, 0) }} FCFA/jour
              </div>
            </div>
            <div class="invoice-v2-status" style="background:{{ $balance > 0 ? 'rgba(74,222,128,.2)' : 'rgba(239,68,68,.2)' }};color:{{ $balance > 0 ? '#4ade80' : '#f87171' }}">
              {{ $balance > 0 ? 'Actif' : 'Épuisé' }}
            </div>
          </div>
          <div class="invoice-v2-items">
            <div class="invoice-v2-item">
              <span class="invoice-v2-item-lbl">Consommation ce mois</span>
              <span class="invoice-v2-item-val">{{ number_format($monthlyKwh, 1) }} kWh</span>
            </div>
            <div class="invoice-v2-item">
              <span class="invoice-v2-item-lbl">Montant consommé ce mois</span>
              <span class="invoice-v2-item-val">{{ number_format($monthAmount, 0, '.', ' ') }} FCFA</span>
            </div>
            @if($balanceLow)
            <div class="invoice-v2-item" style="background:rgba(239,68,68,.08);border-radius:8px;padding:8px 12px">
              <span class="invoice-v2-item-lbl" style="color:#ef4444"><i class="fas fa-exclamation-triangle me-1"></i>Solde faible</span>
              <span class="invoice-v2-item-val" style="color:#ef4444">Moins de 5 jours</span>
            </div>
            @endif
          </div>
        </div>

        {{-- Autonomie restante --}}
        @if($dailyKwh > 0 && $balance > 0)
        @php $jours = floor($balance / ($dailyKwh * 35)); @endphp
        <div style="margin-top:16px;padding:16px 20px;background:var(--card);border:1px solid var(--border);border-radius:14px">
          <div style="font-size:.78rem;color:var(--muted);margin-bottom:6px"><i class="fas fa-hourglass-half me-1"></i>Autonomie estimée</div>
          <div style="font-size:1.4rem;font-weight:900;color:{{ $jours < 5 ? '#ef4444' : 'var(--green)' }}">
            {{ $jours }} jour{{ $jours > 1 ? 's' : '' }}
          </div>
        </div>
        @endif
      </div>

      {{-- Recharge rapide --}}
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title">
            <span class="section-dot green"></span> Recharger mon compteur
          </div>
        </div>
        <div style="padding:20px">
          @if(session('recharge_success'))
            <div style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);border-radius:10px;padding:12px 16px;margin-bottom:16px;display:flex;gap:10px;align-items:center;color:var(--green);font-size:.85rem;font-weight:700">
              <i class="fas fa-check-circle"></i> {{ session('recharge_success') }}
            </div>
          @endif

          <form method="POST" action="{{ route('client.recharge.post') }}">
            @csrf
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:14px">
              @foreach([1000,2000,5000,10000] as $preset)
                <button type="button" onclick="setAmount({{ $preset }})"
                  style="padding:10px;border:2px solid var(--border);border-radius:10px;background:var(--card);font-weight:700;font-size:.85rem;cursor:pointer;transition:.15s"
                  class="preset-btn">
                  {{ number_format($preset, 0, '.', ' ') }} FCFA
                </button>
              @endforeach
            </div>
            <div class="fgroup">
              <label class="flabel">Montant (FCFA)</label>
              <div class="finput-wrap">
                <i class="finput-icon fas fa-coins"></i>
                <input id="rechargeAmount" type="number" name="amount" min="500" max="500000"
                       placeholder="500 minimum" class="finput" required>
              </div>
              @error('amount')<div style="color:#ef4444;font-size:.78rem;margin-top:4px">{{ $message }}</div>@enderror
            </div>

            <input type="hidden" name="method" id="payMethod" value="orange_money">
            <div style="display:flex;gap:8px;margin-bottom:14px;flex-wrap:wrap">
              @foreach(['orange_money'=>'Orange Money','mtn_momo'=>'MTN MoMo','moov'=>'Moov','carte'=>'Carte'] as $code => $label)
                <button type="button" onclick="setMethod('{{ $code }}', this)"
                  style="padding:5px 12px;border-radius:20px;border:1.5px solid {{ $code==='orange_money' ? 'var(--orange)' : 'var(--border)' }};background:{{ $code==='orange_money' ? 'var(--orange-light)' : 'var(--card)' }};color:{{ $code==='orange_money' ? 'var(--orange)' : 'var(--muted)' }};font-size:.75rem;font-weight:700;cursor:pointer"
                  class="method-btn">
                  {{ $label }}
                </button>
              @endforeach
            </div>

            <button type="submit" class="btn-cie" style="width:100%;justify-content:center;padding:13px;font-size:.95rem">
              <i class="fas fa-bolt"></i> Recharger maintenant
            </button>
          </form>
          <p style="text-align:center;font-size:.72rem;color:var(--muted);margin-top:10px">
            <i class="fas fa-shield-alt me-1"></i>Paiement sécurisé SSL
          </p>
        </div>
      </div>

    </div>
  </div>{{-- /billing --}}

  {{-- ── ALERTES ── --}}
  <div class="tab-pane" data-pane="alerts">
    @include('client.alerts')
  </div>

  {{-- ── SIGNALER UNE COUPURE ── --}}
  <div class="tab-pane" data-pane="signaler">

    @if(session('incident_reported'))
      <div style="background:rgba(26,158,63,.1);border:1.5px solid rgba(26,158,63,.3);border-radius:14px;padding:18px 22px;margin-bottom:24px;display:flex;gap:14px;align-items:flex-start">
        <i class="fas fa-check-circle" style="color:var(--green);font-size:1.3rem;margin-top:2px"></i>
        <div>
          <div style="font-weight:800;color:var(--green);font-size:.95rem">Signalement reçu</div>
          <div style="font-size:.85rem;color:var(--muted);margin-top:2px">{{ session('incident_reported') }}</div>
        </div>
      </div>
    @endif

    <div style="display:grid;grid-template-columns:2fr 1fr;gap:24px">

      {{-- Formulaire principal --}}
      <div class="section-card">
        <div class="section-card-header">
          <div class="section-card-title">
            <span class="section-dot" style="background:var(--red)"></span>
            Déclarer une coupure dans mon quartier
          </div>
          <button type="button" id="voiceToggleBtn"
                  style="display:inline-flex;align-items:center;gap:7px;padding:8px 16px;border-radius:20px;border:2px solid var(--orange);background:rgba(255,102,0,.08);color:var(--orange);font-size:.8rem;font-weight:700;cursor:pointer"
                  onclick="toggleVoiceRecorder()">
            <i class="fas fa-microphone"></i> Signaler par voix
          </button>
        </div>

        {{-- ── Enregistreur vocal ── --}}
        <div id="voiceRecorderBox" style="display:none;margin:0 24px 0;border:2px solid var(--orange);border-radius:14px;overflow:hidden">
          {{-- Header --}}
          <div style="background:rgba(255,102,0,.08);padding:14px 20px;display:flex;align-items:center;justify-content:space-between">
            <div style="font-weight:800;font-size:.88rem;color:var(--orange)">
              <i class="fas fa-microphone me-2"></i>Signalement vocal
            </div>
            <div style="font-size:.75rem;color:var(--muted)">
              Parlez en français, dioula, baoulé, bété ou nouchi
            </div>
          </div>
          {{-- Corps --}}
          <div style="padding:20px">
            {{-- Bouton enregistrement --}}
            <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
              <button type="button" id="recordBtn"
                      style="width:56px;height:56px;border-radius:50%;background:var(--orange);border:none;color:#fff;font-size:1.3rem;cursor:pointer;transition:.2s;flex-shrink:0"
                      onclick="toggleRecording()">
                <i class="fas fa-microphone" id="recordIcon"></i>
              </button>
              <div style="flex:1">
                <div id="recordStatus" style="font-size:.85rem;font-weight:700;color:var(--dark)">Appuyez pour parler</div>
                <div id="recordTimer" style="font-size:.75rem;color:var(--muted);margin-top:2px">Durée max : 60 secondes</div>
              </div>
              <div id="recordWave" style="display:none;align-items:center;gap:3px;height:32px">
                @for($i=0;$i<8;$i++)
                  <div style="width:4px;border-radius:4px;background:var(--orange);animation:wave {{ 0.4+$i*0.08 }}s ease-in-out infinite alternate;height:8px"></div>
                @endfor
              </div>
            </div>

            {{-- Zone résultat transcription --}}
            <div id="transcriptBox" style="display:none">
              <div style="font-size:.75rem;font-weight:700;color:var(--muted);text-transform:uppercase;margin-bottom:8px">
                <i class="fas fa-language me-1"></i>Transcription en français
              </div>
              <div id="transcriptText"
                   style="background:#f8fafc;border:1.5px solid var(--border);border-radius:10px;padding:14px;font-size:.88rem;color:var(--dark);min-height:60px;line-height:1.6"></div>
              <div style="display:flex;gap:10px;margin-top:12px">
                <button type="button" class="btn-cie" style="flex:1;justify-content:center;font-size:.82rem"
                        onclick="useTranscription()">
                  <i class="fas fa-check me-1"></i>Utiliser ce texte
                </button>
                <button type="button" class="btn-ghost" style="font-size:.82rem"
                        onclick="resetVoice()">
                  <i class="fas fa-redo me-1"></i>Réenregistrer
                </button>
              </div>
            </div>

            {{-- Erreur --}}
            <div id="voiceError" style="display:none;background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:10px 14px;font-size:.82rem;color:var(--red)">
              <i class="fas fa-exclamation-circle me-1"></i><span id="voiceErrorMsg"></span>
            </div>
          </div>
        </div>

        <div style="padding:24px">

          @if($errors->any())
            <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.83rem;color:#ef4444">
              @foreach($errors->all() as $e)
                <div><i class="fas fa-exclamation-circle me-1"></i>{{ $e }}</div>
              @endforeach
            </div>
          @endif

          <form method="POST" action="{{ route('client.incidents.store') }}">
            @csrf

            {{-- Type de coupure --}}
            <div style="margin-bottom:20px">
              <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">
                Type de coupure observée
              </div>
              <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px">
                @php
                  $types = [
                    'surcharge'      => ['icon'=>'fa-bolt',       'label'=>'Surcharge',        'sub'=>'Trop de consommation', 'color'=>'var(--orange)'],
                    'vieillissement' => ['icon'=>'fa-tools',      'label'=>'Panne équipement', 'sub'=>'Matériel défaillant',  'color'=>'var(--blue)'],
                    'meteo'          => ['icon'=>'fa-cloud-rain', 'label'=>'Intempérie',       'sub'=>'Pluie, orage, vent',   'color'=>'#6366f1'],
                    'vandalisme'     => ['icon'=>'fa-user-slash', 'label'=>'Vandalisme',       'sub'=>'Vol ou sabotage câble', 'color'=>'var(--red)'],
                    'autre'          => ['icon'=>'fa-question',   'label'=>'Autre',            'sub'=>'Cause inconnue',       'color'=>'var(--muted)'],
                  ];
                  $oldType = old('type', '');
                @endphp

                @foreach($types as $code => $info)
                  <label class="sig-type-card {{ $oldType === $code ? 'sig-type-card--active' : '' }}"
                         for="type_{{ $code }}"
                         onclick="document.querySelectorAll('.sig-type-card').forEach(c=>c.classList.remove('sig-type-card--active'));this.classList.add('sig-type-card--active')">
                    <input type="radio" id="type_{{ $code }}" name="type" value="{{ $code }}"
                           {{ $oldType === $code ? 'checked' : '' }} style="display:none">
                    <i class="fas {{ $info['icon'] }}" style="font-size:1.3rem;color:{{ $info['color'] }};margin-bottom:4px"></i>
                    <div style="font-weight:700;font-size:.85rem;color:var(--dark)">{{ $info['label'] }}</div>
                    <div style="font-size:.72rem;color:var(--muted)">{{ $info['sub'] }}</div>
                  </label>
                @endforeach
              </div>
            </div>

            {{-- Gravité --}}
            <div style="margin-bottom:20px">
              <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:8px">
                Gravité
              </div>
              <div style="display:flex;gap:10px">
                @foreach(['mineur'=>['Mineure','#22c55e'],'normal'=>['Normale','var(--orange)'],'critique'=>['Critique','var(--red)']] as $sev => [$slbl, $scol])
                  <label style="flex:1;display:flex;align-items:center;gap:8px;padding:10px 14px;border:2px solid {{ old('severity','normal')===$sev ? $scol : 'var(--border)' }};border-radius:10px;cursor:pointer;background:{{ old('severity','normal')===$sev ? 'rgba(0,0,0,.03)' : 'var(--card)' }}" class="sev-label">
                    <input type="radio" name="severity" value="{{ $sev }}" {{ old('severity','normal')===$sev ? 'checked' : '' }}
                           style="accent-color:{{ $scol }}" onchange="styleSeverity()">
                    <span style="font-weight:700;font-size:.85rem;color:{{ $scol }}">{{ $slbl }}</span>
                  </label>
                @endforeach
              </div>
            </div>

            {{-- Quartier --}}
            <div class="fgroup" style="margin-bottom:16px">
              <label class="flabel">Quartier précis <span style="font-weight:400;color:var(--muted)">(optionnel)</span></label>
              <div class="finput-wrap">
                <i class="finput-icon fas fa-map-pin"></i>
                <input type="text" name="quartier" value="{{ old('quartier') }}"
                       class="finput" placeholder="Ex : Riviera 2, Sicogi, Zone 4...">
              </div>
            </div>

            {{-- Description --}}
            <div class="fgroup" style="margin-bottom:22px">
              <label class="flabel">Description <span style="font-weight:400;color:var(--muted)">(optionnel)</span></label>
              <textarea name="description" class="finput" rows="3"
                        style="resize:vertical;padding:12px 14px;height:auto"
                        placeholder="Décrivez ce que vous observez : depuis quand, bruit, odeur, poteau tombé...">{{ old('description') }}</textarea>
            </div>

            <button type="submit" class="btn-cie"
                    style="width:100%;justify-content:center;padding:14px;font-size:.95rem;background:linear-gradient(135deg,#dc2626,#b91c1c)">
              <i class="fas fa-paper-plane"></i> Envoyer le signalement
            </button>

            <p style="text-align:center;font-size:.72rem;color:var(--muted);margin-top:10px">
              <i class="fas fa-shield-alt me-1"></i>
              Votre signalement est transmis aux équipes CIE de votre zone ({{ $userZone }})
            </p>
          </form>

        </div>
      </div>

      {{-- Panneau informatif --}}
      <div>
        <div class="section-card" style="margin-bottom:16px">
          <div style="padding:20px">
            <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">
              Votre zone
            </div>
            <div style="font-size:1.2rem;font-weight:900;color:var(--dark)">{{ $userZone }}</div>
            <div style="font-size:.8rem;color:var(--muted);margin-top:4px">
              <i class="fas fa-tachometer-alt me-1"></i>Compteur {{ $userMeter }}
            </div>
            @if($alertCount > 0)
              <div style="margin-top:14px;padding:10px 12px;background:rgba(239,68,68,.08);border-radius:10px;border-left:3px solid var(--red)">
                <div style="font-size:.8rem;font-weight:700;color:var(--red)">
                  <i class="fas fa-exclamation-triangle me-1"></i>
                  {{ $alertCount }} coupure(s) déjà prédite(s)
                </div>
                <div style="font-size:.72rem;color:var(--muted);margin-top:2px">dans votre zone</div>
              </div>
            @else
              <div style="margin-top:14px;padding:10px 12px;background:rgba(26,158,63,.08);border-radius:10px;border-left:3px solid var(--green)">
                <div style="font-size:.8rem;font-weight:700;color:var(--green)">
                  <i class="fas fa-check-circle me-1"></i>Réseau stable prévu
                </div>
                <div style="font-size:.72rem;color:var(--muted);margin-top:2px">aucune coupure prédite</div>
              </div>
            @endif
          </div>
        </div>

        <div class="section-card">
          <div style="padding:20px">
            <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:12px">
              Quand signaler ?
            </div>
            <div style="display:flex;flex-direction:column;gap:10px;font-size:.82rem">
              @foreach([
                ['fa-bolt','Coupure soudaine non prévue','color:var(--orange)'],
                ['fa-clock','Durée anormalement longue','color:var(--blue)'],
                ['fa-bolt fa-sm','Clignotements répétés','color:#8b5cf6'],
                ['fa-map-marker-alt','Plusieurs foyers concernés','color:var(--green)'],
                ['fa-hard-hat','Poteau ou câble endommagé','color:var(--red)'],
              ] as [$ico, $txt, $style])
                <div style="display:flex;align-items:center;gap:10px">
                  <div style="width:32px;height:32px;border-radius:8px;background:var(--card);border:1px solid var(--border);display:flex;align-items:center;justify-content:center">
                    <i class="fas {{ $ico }}" style="font-size:.8rem;{{ $style }}"></i>
                  </div>
                  <span style="color:var(--text)">{{ $txt }}</span>
                </div>
              @endforeach
            </div>
          </div>
        </div>
      </div>

    </div>

    <style>
      .sig-type-card {
        display:flex;flex-direction:column;align-items:center;gap:4px;
        padding:14px 10px;border:2px solid var(--border);border-radius:12px;
        cursor:pointer;transition:all .15s;text-align:center;background:var(--card);
      }
      .sig-type-card:hover { border-color:var(--orange);background:rgba(255,102,0,.04); }
      .sig-type-card--active { border-color:var(--orange);background:rgba(255,102,0,.08); }
    </style>

  </div>{{-- /signaler --}}

</div>{{-- /client-body --}}

@if(session('welcome'))
  <div class="welcome-banner" style="position:fixed;bottom:24px;left:50%;transform:translateX(-50%);width:calc(100% - 48px);max-width:500px;z-index:400">
    <i class="fas fa-check-circle"></i>
    <div>
      <div class="welcome-banner-text">{{ session('welcome') }}</div>
      <div class="welcome-banner-sub">Votre espace client est pr&ecirc;t.</div>
    </div>
  </div>
@endif

@endsection

@section('scripts')
<style>
@keyframes wave {
  from { height: 6px; }
  to   { height: 28px; }
}
</style>
<script>
/* ── Enregistreur vocal ──────────────────────────────────────────── */
(function () {
  var mediaRecorder = null;
  var audioChunks   = [];
  var timerInterval = null;
  var elapsed       = 0;
  var MAX_SEC       = 60;

  window.toggleVoiceRecorder = function () {
    var box = document.getElementById('voiceRecorderBox');
    var btn = document.getElementById('voiceToggleBtn');
    var open = box.style.display !== 'none';
    box.style.display = open ? 'none' : 'block';
    btn.style.background = open ? 'rgba(255,102,0,.08)' : 'rgba(255,102,0,.18)';
    if (open) resetVoice();
  };

  window.toggleRecording = function () {
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      stopRecording();
    } else {
      startRecording();
    }
  };

  function startRecording() {
    navigator.mediaDevices.getUserMedia({ audio: true })
      .then(function (stream) {
        audioChunks = [];
        elapsed     = 0;
        mediaRecorder = new MediaRecorder(stream, { mimeType: 'audio/webm' });

        mediaRecorder.ondataavailable = function (e) {
          if (e.data.size > 0) audioChunks.push(e.data);
        };
        mediaRecorder.onstop = function () {
          stream.getTracks().forEach(function (t) { t.stop(); });
          sendAudio();
        };

        mediaRecorder.start(250);
        setRecordingUI(true);

        timerInterval = setInterval(function () {
          elapsed++;
          document.getElementById('recordTimer').textContent = elapsed + 's / ' + MAX_SEC + 's';
          if (elapsed >= MAX_SEC) stopRecording();
        }, 1000);
      })
      .catch(function () {
        showVoiceError('Microphone inaccessible. Vérifiez les permissions du navigateur.');
      });
  }

  function stopRecording() {
    clearInterval(timerInterval);
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stop();
    }
    setRecordingUI(false);
    document.getElementById('recordStatus').textContent = 'Transcription en cours…';
    document.getElementById('recordBtn').disabled = true;
  }

  function setRecordingUI(recording) {
    var btn  = document.getElementById('recordBtn');
    var icon = document.getElementById('recordIcon');
    var wave = document.getElementById('recordWave');
    var stat = document.getElementById('recordStatus');
    if (recording) {
      btn.style.background  = 'var(--red)';
      icon.className        = 'fas fa-stop';
      wave.style.display    = 'flex';
      stat.textContent      = 'Enregistrement en cours… Appuyez pour arrêter';
    } else {
      btn.style.background  = 'var(--orange)';
      icon.className        = 'fas fa-microphone';
      wave.style.display    = 'none';
    }
  }

  function sendAudio() {
    var blob = new Blob(audioChunks, { type: 'audio/webm' });
    var form = new FormData();
    form.append('audio', blob, 'signal.webm');
    form.append('_token', document.querySelector('meta[name=csrf-token]').content);

    fetch('{{ route("client.voice-transcribe") }}', {
      method: 'POST',
      body:   form,
    })
    .then(function (r) { return r.json(); })
    .then(function (d) {
      document.getElementById('recordBtn').disabled = false;
      document.getElementById('recordStatus').textContent = 'Appuyez pour parler';
      document.getElementById('recordTimer').textContent  = 'Durée max : 60 secondes';

      if (!d.ok) {
        showVoiceError(d.error || 'Erreur inconnue.');
        return;
      }
      document.getElementById('transcriptText').textContent = d.translated || d.original;
      document.getElementById('transcriptBox').style.display = 'block';
      document.getElementById('voiceError').style.display    = 'none';
    })
    .catch(function () {
      document.getElementById('recordBtn').disabled = false;
      showVoiceError('Erreur réseau. Veuillez réessayer.');
    });
  }

  window.useTranscription = function () {
    var text = document.getElementById('transcriptText').textContent;
    var desc = document.querySelector('textarea[name="description"]');
    if (desc && text) {
      desc.value = text;
      // Scroll vers le formulaire
      desc.scrollIntoView({ behavior: 'smooth', block: 'center' });
      desc.focus();
    }
    // Fermer le recorder
    document.getElementById('voiceRecorderBox').style.display = 'none';
    document.getElementById('voiceToggleBtn').style.background = 'rgba(255,102,0,.08)';
    resetVoice();
    // Toast
    if (typeof cieShowToast !== 'undefined') cieShowToast('Texte ajouté à la description', 'success');
  };

  window.resetVoice = function () {
    clearInterval(timerInterval);
    if (mediaRecorder && mediaRecorder.state === 'recording') {
      mediaRecorder.stream && mediaRecorder.stream.getTracks().forEach(function (t) { t.stop(); });
      mediaRecorder.stop();
    }
    mediaRecorder = null;
    audioChunks   = [];
    elapsed       = 0;
    setRecordingUI(false);
    document.getElementById('recordBtn').disabled            = false;
    document.getElementById('recordStatus').textContent      = 'Appuyez pour parler';
    document.getElementById('recordTimer').textContent       = 'Durée max : 60 secondes';
    document.getElementById('transcriptBox').style.display   = 'none';
    document.getElementById('voiceError').style.display      = 'none';
    document.getElementById('transcriptText').textContent    = '';
  };

  function showVoiceError(msg) {
    document.getElementById('voiceErrorMsg').textContent  = msg;
    document.getElementById('voiceError').style.display   = 'block';
    document.getElementById('transcriptBox').style.display = 'none';
  }
})();

/* ── Reste du dashboard ─────────────────────────────────────────── */
(function () {

  /* Sélection méthode paiement */
  function selectPay(el) {
    document.querySelectorAll('.pay-method').forEach(function (m) { m.classList.remove('selected'); });
    el.classList.add('selected');
  }
  window.selectPay = selectPay;

  /* ── Notification vocale ── */
  var _alert = null, _audio = null;
  var _dismissed = sessionStorage.getItem('cie_voice_dismissed') === '1';

  if (!_dismissed) {
    fetch('/api/client/alerts/latest')
      .then(function (r) { return r.json(); })
      .then(function (d) { if (d.alert) { _alert = d.alert; showBanner(d.alert); } })
      .catch(function () {});
  }

  function showBanner(a) {
    var b  = document.getElementById('voiceAlertBanner');
    var t  = document.getElementById('voiceAlertTitle');
    var s  = document.getElementById('voiceAlertSub');
    var ic = document.getElementById('voiceAlertIcon');
    var p  = parseInt(a.probability);
    var c  = p >= 80 ? '#ef4444' : (p >= 60 ? '#f59e0b' : '#3b82f6');
    ic.style.background = c + '20';
    ic.style.color = c;
    t.textContent = 'Coupure prévue dans votre zone — ' + a.zone + ' (' + p + '%)';
    s.textContent = 'Prévu le ' + a.start_at + (a.reason ? ' · ' + a.reason : '');
    b.style.display = 'block';
    setTimeout(function () {
      b.classList.add('visible');
      // Déclenchement automatique de la voix après que le banner est visible
      setTimeout(function () { playVoiceAlert(); }, 600);
    }, 50);
  }

  function playVoiceAlert() {
    if (!_alert) return;
    var btn = document.getElementById('voiceBtnPlay');
    if (btn) { btn.innerHTML = '<i class="fas fa-stop"></i> Arrêter'; btn.onclick = stopVoiceAlert; }
    if (_alert.audio_url) {
      _audio = new Audio(_alert.audio_url);
      _audio.onended = resetBtn;
      _audio.onerror = function () { useTTS(_alert.tts_text); };
      _audio.play().catch(function () { useTTS(_alert.tts_text); });
    } else {
      useTTS(_alert.tts_text);
    }
  }

  function useTTS(text) {
    if (!('speechSynthesis' in window)) { resetBtn(); return; }
    // Texte de secours si Groq n'a pas répondu
    var msg = (text && text.trim()) ? text.trim()
            : 'Alerte coupure prévue dans votre zone. Rechargez vos appareils.';

    window.speechSynthesis.cancel();

    function doSpeak() {
      var u = new SpeechSynthesisUtterance(msg);
      // Sélectionne une voix française si disponible
      var frVoice = window.speechSynthesis.getVoices().find(function(v) {
        return v.lang && v.lang.toLowerCase().startsWith('fr');
      });
      if (frVoice) u.voice = frVoice;
      u.lang  = 'fr-FR';
      u.rate  = 0.88;
      u.pitch = 1;
      u.onend  = resetBtn;
      u.onerror = function () { resetBtn(); };
      window.speechSynthesis.speak(u);
    }

    // Chrome charge les voix de façon asynchrone — attendre qu'elles soient prêtes
    var voices = window.speechSynthesis.getVoices();
    if (voices.length > 0) {
      doSpeak();
    } else {
      var t = setTimeout(doSpeak, 800);
      window.speechSynthesis.onvoiceschanged = function () {
        clearTimeout(t);
        window.speechSynthesis.onvoiceschanged = null;
        doSpeak();
      };
    }
  }

  function stopVoiceAlert() {
    if (_audio) { _audio.pause(); _audio.currentTime = 0; }
    if ('speechSynthesis' in window) window.speechSynthesis.cancel();
    resetBtn();
  }

  function resetBtn() {
    var btn = document.getElementById('voiceBtnPlay');
    if (btn) { btn.innerHTML = '<i class="fas fa-play"></i> Écouter'; btn.onclick = playVoiceAlert; }
  }

  function closeVoiceAlert() {
    var b = document.getElementById('voiceAlertBanner');
    b.classList.remove('visible');
    setTimeout(function () { b.style.display = 'none'; }, 300);
    stopVoiceAlert();
    sessionStorage.setItem('cie_voice_dismissed', '1');
  }

  window.playVoiceAlert  = playVoiceAlert;
  window.closeVoiceAlert = closeVoiceAlert;

  /* Chart consommation (données réelles serveur) */
  var _chartData = @json($weeklyChartData);
  window._consumptionChartDone = false;
  document.querySelectorAll('.ctab[data-tab]').forEach(function (tab) {
    tab.addEventListener('click', function () {
      if (this.dataset.tab === 'consumption' && !window._consumptionChartDone) {
        window._consumptionChartDone = true;
        var ctx = document.getElementById('consumptionChart');
        if (!ctx || typeof Chart === 'undefined') return;
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: _chartData.map(function(d){ return d.label; }),
            datasets: [{
              label: 'kWh',
              data: _chartData.map(function(d){ return d.kwh; }),
              borderColor: '#FF6600',
              backgroundColor: 'rgba(255,102,0,.08)',
              borderWidth: 2.5,
              pointBackgroundColor: '#FF6600',
              pointRadius: 4,
              tension: 0.4,
              fill: true
            }]
          },
          options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
              x: { grid: { display: false } },
              y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } }
            }
          }
        });
      }
    });
  });

  /* Recharge : montants prédéfinis + méthode */
  function setAmount(v) { document.getElementById('rechargeAmount').value = v; }
  function setMethod(code, btn) {
    document.getElementById('payMethod').value = code;
    document.querySelectorAll('.method-btn').forEach(function(b) {
      b.style.borderColor = 'var(--border)';
      b.style.background  = 'var(--card)';
      b.style.color       = 'var(--muted)';
    });
    btn.style.borderColor = 'var(--orange)';
    btn.style.background  = 'var(--orange-light)';
    btn.style.color       = 'var(--orange)';
  }
  window.setAmount = setAmount;
  window.setMethod = setMethod;

})();
</script>
@endsection
