@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Coupures')
@section('page-title', 'Surveillance des coupures')

@section('content')

@php
  $slotDebut  = now()->setTime($slotHour, 0, 0);
  $slotFin    = $slotDebut->copy()->addHours(3);
  $zonesActives = $enCours->pluck('zone')->merge($avenir3h->pluck('zone'))->unique();
  $clientsImpactes = $zonesActives->map(fn($z) => \App\Models\User::where('zone',$z)->where('role','client')->count())->sum();
@endphp

{{-- En-tête --}}
<div class="page-heading animate-fade" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Surveillance des coupures</h1>
    <p class="page-subtitle">
      <span class="live-dot" style="background:var(--green);display:inline-block;width:7px;height:7px;border-radius:50%;margin-right:6px;animation:pulse 1.2s infinite"></span>
      Temps réel · Slot 3H actuel :
      <strong style="color:var(--orange)">{{ $slotDebut->format('H:i') }} → {{ $slotFin->format('H:i') }}</strong>
      · {{ now()->format('d/m/Y H:i') }}
    </p>
  </div>
  <div style="display:flex;gap:10px;align-items:center">
    <span style="font-size:.72rem;color:var(--muted);background:#f1f5f9;padding:4px 10px;border-radius:20px">
      <i class="fas fa-circle" style="color:var(--green);font-size:.5rem;vertical-align:middle"></i>
      Refresh dans <span id="countdown">60</span>s
    </span>
    <button class="btn-ghost" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Actualiser</button>
    <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
  </div>
</div>

{{-- KPIs 3H seulement --}}
<div class="kpi-row mb-6 animate-fade delay-1">
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">En cours maintenant</div>
      <div class="kpi-value">{{ $enCours->count() }}</div>
      <div class="kpi-trend {{ $enCours->count() > 0 ? 'down' : 'up' }}">
        <i class="fas fa-bolt"></i> Slot {{ $slotDebut->format('H:i') }} – {{ $slotFin->format('H:i') }}
      </div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-bolt"></i></div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Prochains slots 3H</div>
      <div class="kpi-value">{{ $avenir3h->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-clock"></i> Aujourd'hui restant</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-clock"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Zones touchées (3H)</div>
      <div class="kpi-value">{{ $zonesActives->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map"></i> Communes</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-map-marked-alt"></i></div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Clients impactés (3H)</div>
      <div class="kpi-value">{{ $clientsImpactes }}</div>
      <div class="kpi-trend neu"><i class="fas fa-users"></i> Abonnés</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-users"></i></div>
  </div>
</div>

{{-- ══ BLOC : Slot actuel / Slot suivant ══ --}}
@php
  $prochainDebut   = $avenir3h->first()?->start_at;
  $prochainFin     = $prochainDebut?->copy()->addHours(3);
  $alertesProchain = $prochainDebut
      ? $avenir3h->filter(fn($a) => $a->start_at->eq($prochainDebut))
      : collect();
@endphp
<div class="cie-card animate-fade delay-2 mb-6" style="overflow:hidden">
  <div style="display:grid;grid-template-columns:1fr 1fr">

    {{-- ── Slot actuel ── --}}
    <div style="padding:20px 24px;border-right:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <span style="display:inline-flex;align-items:center;gap:6px;font-size:.68rem;font-weight:800;background:rgba(255,102,0,.12);color:var(--orange);padding:4px 11px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em">
          <span style="width:6px;height:6px;border-radius:50%;background:var(--orange);display:inline-block;animation:pulse 1s infinite"></span>
          En cours
        </span>
        <span style="font-size:.95rem;font-weight:900;color:var(--dark)">
          {{ $slotDebut->format('H:i') }}<span style="color:var(--muted);font-weight:400;margin:0 4px">→</span>{{ $slotFin->format('H:i') }}
        </span>
      </div>

      @if($enCours->count())
        @foreach($enCours->take(5) as $a)
          @php
            $prob = $a->probability ?? 0;
            $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--green)');
            $bg   = $prob >= 80 ? 'rgba(239,68,68,.1)' : ($prob >= 60 ? 'rgba(245,158,11,.1)' : 'rgba(26,158,63,.1)');
          @endphp
          <div style="display:flex;align-items:center;gap:9px;margin-bottom:9px">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $bg }};color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:900;flex-shrink:0">
              {{ strtoupper(mb_substr($a->zone, 0, 2)) }}
            </div>
            <div style="flex:1;font-size:.83rem;font-weight:600;color:var(--dark)">{{ $a->zone }}</div>
            <div style="width:46px;height:5px;background:#f1f5f9;border-radius:5px;overflow:hidden">
              <div style="height:100%;width:{{ $prob }}%;background:{{ $col }};border-radius:5px"></div>
            </div>
            <span style="font-size:.82rem;font-weight:800;color:{{ $col }};min-width:34px;text-align:right">{{ $prob }}%</span>
          </div>
        @endforeach
        @if($enCours->count() > 5)
          <div style="font-size:.75rem;color:var(--muted);margin-top:2px">
            <i class="fas fa-ellipsis-h me-1"></i>{{ $enCours->count() - 5 }} autre(s) zone(s)
          </div>
        @endif
      @else
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;color:var(--muted)">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:1.1rem"></i>
          <span style="font-size:.83rem">Réseau stable sur ce slot</span>
        </div>
      @endif
    </div>

    {{-- ── Slot suivant ── --}}
    <div style="padding:20px 24px;background:#f8fafc">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px">
        <span style="display:inline-flex;align-items:center;gap:6px;font-size:.68rem;font-weight:800;background:rgba(59,130,246,.1);color:var(--blue);padding:4px 11px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0">
          <i class="fas fa-clock" style="font-size:.6rem"></i>
          Slot suivant
        </span>
        @if($prochainDebut)
          <span style="font-size:.95rem;font-weight:900;color:var(--dark)">
            {{ $prochainDebut->format('H:i') }}<span style="color:var(--muted);font-weight:400;margin:0 4px">→</span>{{ $prochainFin->format('H:i') }}
            @if(!$prochainDebut->isSameDay(now()))
              <span style="font-size:.72rem;font-weight:600;color:var(--muted);margin-left:4px">{{ $prochainDebut->format('d/m') }}</span>
            @endif
          </span>
        @else
          <span style="font-size:.88rem;color:var(--muted)">—</span>
        @endif
      </div>

      @if($alertesProchain->count())
        @foreach($alertesProchain->take(5) as $ns)
          @php
            $prob = $ns->probability ?? 0;
            $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
            $bg   = $prob >= 80 ? 'rgba(239,68,68,.08)' : ($prob >= 60 ? 'rgba(245,158,11,.08)' : 'rgba(59,130,246,.08)');
          @endphp
          <div style="display:flex;align-items:center;gap:9px;margin-bottom:9px">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $bg }};color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:900;flex-shrink:0">
              {{ strtoupper(mb_substr($ns->zone, 0, 2)) }}
            </div>
            <div style="flex:1;font-size:.83rem;font-weight:600;color:var(--dark)">{{ $ns->zone }}</div>
            <div style="width:46px;height:5px;background:#e2e8f0;border-radius:5px;overflow:hidden">
              <div style="height:100%;width:{{ $prob }}%;background:{{ $col }};border-radius:5px"></div>
            </div>
            <span style="font-size:.82rem;font-weight:800;color:{{ $col }};min-width:34px;text-align:right">{{ $prob }}%</span>
          </div>
        @endforeach
        @if($alertesProchain->count() > 5)
          <div style="font-size:.75rem;color:var(--muted);margin-top:2px">
            <i class="fas fa-ellipsis-h me-1"></i>{{ $alertesProchain->count() - 5 }} autre(s) zone(s)
          </div>
        @endif
      @else
        <div style="display:flex;align-items:center;gap:10px;padding:8px 0;color:var(--muted)">
          <i class="fas fa-shield-alt" style="color:var(--green);font-size:1.1rem"></i>
          <span style="font-size:.83rem">Aucun risque prévu sur ce slot</span>
        </div>
      @endif
    </div>

  </div>
</div>

{{-- ══ SECTION 1 : COUPURES 3H EN COURS ══ --}}
<div class="cie-card animate-fade delay-2 mb-6">
  <div class="cie-card-header">
    <div class="cie-card-title">
      <span class="card-dot red" style="animation:pulse 1.2s infinite"></span>
      Coupures actives — slot {{ $slotDebut->format('H:i') }} → {{ $slotFin->format('H:i') }}
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge-cie badge-blue" style="font-size:.72rem">
        <i class="fas fa-clock me-1"></i>Prédiction 3H automatique
      </span>
      <span class="badge-cie {{ $enCours->count() > 0 ? 'badge-red' : 'badge-green' }}">
        {{ $enCours->count() }} active(s)
      </span>
    </div>
  </div>

  @if($enCours->count())
    <div class="table-wrap">
      <table class="cie-table">
        <thead>
          <tr>
            <th>Zone</th><th>Quartier</th><th>Cause IA</th>
            <th>Début</th><th>Fin</th><th>Probabilité</th><th>Clients</th><th>Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($enCours as $a)
            @php
              $prob = $a->probability ?? 0;
              $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--green)');
              $nbC  = \App\Models\User::where('zone',$a->zone)->where('role','client')->count();
              $cIcons = ['surcharge_réseau'=>'fa-bolt','vieillissement_équipement'=>'fa-tools','intempérie_pluie'=>'fa-cloud-rain','défaut_câble_ht'=>'fa-plug','vandalisme_vol_câble'=>'fa-user-slash'];
              $cIcon  = $cIcons[$a->cause_probable ?? ''] ?? 'fa-exclamation-circle';
            @endphp
            <tr>
              <td><div style="font-weight:800;color:var(--dark)">{{ $a->zone }}</div></td>
              <td style="font-size:.82rem;color:var(--muted)">{{ $a->quartier ?? '—' }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:6px">
                  <i class="fas {{ $cIcon }}" style="color:{{ $col }};font-size:.85rem"></i>
                  <span style="font-size:.82rem;font-weight:600">{{ $a->reason ?? '—' }}</span>
                </div>
              </td>
              <td><span style="font-weight:700">{{ $a->start_at->format('H:i') }}</span></td>
              <td>{{ optional($a->end_at)->format('H:i') ?? '—' }}</td>
              <td>
                <span class="badge-cie {{ $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-green') }}">
                  {{ $prob }}%
                </span>
              </td>
              <td>
                <span style="font-weight:700;color:var(--orange)">{{ $nbC }}</span>
                <span style="font-size:.72rem;color:var(--muted)"> client(s)</span>
              </td>
              <td>
                <button class="btn-cie-green btn-notify-agent"
                        style="font-size:.72rem;padding:5px 10px"
                        data-id="{{ $a->id }}" data-zone="{{ $a->zone }}">
                  <i class="fas fa-hard-hat"></i>
                </button>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div style="padding:40px 24px;display:flex;align-items:center;gap:16px">
      <div style="width:48px;height:48px;border-radius:12px;background:var(--green-light);display:flex;align-items:center;justify-content:center">
        <i class="fas fa-check-circle" style="color:var(--green);font-size:1.3rem"></i>
      </div>
      <div>
        <div style="font-weight:800;color:var(--dark)">Réseau stable sur le slot actuel</div>
        <div style="font-size:.85rem;color:var(--muted)">
          Aucune coupure en cours pour {{ $slotDebut->format('H:i') }} → {{ $slotFin->format('H:i') }}.
          Prochain slot prévu à {{ $slotFin->format('H:i') }}.
        </div>
      </div>
    </div>
  @endif
</div>

{{-- ══ SECTION 2 : PLANNING 8 SLOTS 3H ══ --}}
<div class="cie-card animate-fade delay-3 mb-6">
  <div class="cie-card-header">
    <div class="cie-card-title">
      <span class="card-dot blue"></span>
      Planning 3H — Fenêtre glissante 24h à partir de {{ $slotDebut->format('H:i') }}
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge-cie badge-blue" style="font-size:.72rem">Fenêtre glissante 24h</span>
      @if($avenir3h->count() > 0)
        <span class="badge-cie badge-orange">{{ $avenir3h->count() }} zone(s) prédite(s)</span>
      @else
        <span class="badge-cie badge-green"><i class="fas fa-check me-1"></i>Aucun risque prédit</span>
      @endif
    </div>
  </div>

  @php
    // FENÊTRE GLISSANTE : 8 slots à partir du slot courant (pas depuis minuit)
    // slot 0 = en cours, slot 1 = prédit (latest predict3h), slots 2-7 = horizon
    $slots8 = [];
    for ($s = 0; $s < 8; $s++) {
        $sStart    = $slotDebut->copy()->addHours($s * 3);
        $sEnd      = $sStart->copy()->addHours(3);
        $isCurrent = ($s === 0);
        $isNext    = ($s === 1);
        $isToday   = $sStart->isToday();

        if ($isCurrent) {
            $sAlerts = $enCours;
        } else {
            $sAlerts = $avenir3h->filter(
                fn($a) => $a->start_at->format('Y-m-d H:i') === $sStart->format('Y-m-d H:i')
            );
        }

        $maxProb = $sAlerts->max('probability') ?? 0;
        $nbZones = $sAlerts->pluck('zone')->unique()->count();

        $slots8[] = compact('sStart','sEnd','isCurrent','isNext','isToday','sAlerts','maxProb','nbZones');
    }
  @endphp

  <div style="padding:20px 24px">
    <div style="display:grid;grid-template-columns:repeat(8,1fr);gap:8px">
      @foreach($slots8 as $slot)
        @php
          $p   = $slot['maxProb'];
          $col = $p >= 80 ? 'var(--red)' : ($p >= 60 ? 'var(--yellow)' : ($p > 0 ? 'var(--blue)' : 'var(--green)'));
          $bg  = $p >= 80 ? 'rgba(239,68,68,.08)' : ($p >= 60 ? 'rgba(245,158,11,.08)' : ($p > 0 ? 'rgba(59,130,246,.08)' : 'rgba(26,158,63,.06)'));
          if ($slot['isCurrent']) {
              $borderCol = 'var(--orange)';
          } elseif ($slot['isNext']) {
              $borderCol = $p > 0 ? $col : 'var(--blue)';
          } else {
              $borderCol = $p > 0 ? $col : 'var(--border)';
          }
        @endphp
        <div style="border:2px solid {{ $borderCol }};border-radius:12px;padding:12px 10px;text-align:center;background:{{ $slot['isCurrent'] ? 'rgba(255,102,0,.06)' : $bg }};position:relative">

          {{-- Labels flottants --}}
          @if($slot['isCurrent'])
            <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:var(--orange);color:#fff;font-size:.58rem;font-weight:900;padding:2px 7px;border-radius:10px;white-space:nowrap">EN COURS</div>
          @elseif($slot['isNext'])
            <div style="position:absolute;top:-10px;left:50%;transform:translateX(-50%);background:var(--blue);color:#fff;font-size:.58rem;font-weight:900;padding:2px 7px;border-radius:10px;white-space:nowrap">PRÉDIT</div>
          @endif

          {{-- Heure --}}
          <div style="font-size:.78rem;font-weight:800;color:{{ $slot['isCurrent'] ? 'var(--orange)' : ($slot['isNext'] ? 'var(--blue)' : 'var(--dark)') }};margin-bottom:2px">
            {{ $slot['sStart']->format('H:i') }}
          </div>
          <div style="font-size:.62rem;color:var(--muted);margin-bottom:6px">
            → {{ $slot['sEnd']->format('H:i') }}
            @if(!$slot['isToday'])<span style="font-size:.55rem;opacity:.7"> +1j</span>@endif
          </div>

          {{-- Indicateur --}}
          @if($slot['nbZones'] > 0)
            <div style="font-size:.95rem;font-weight:900;color:{{ $col }};margin-bottom:2px">{{ $slot['nbZones'] }}</div>
            <div style="font-size:.6rem;color:{{ $col }};font-weight:700;margin-bottom:5px">zone(s)</div>
            <div style="font-size:.72rem;font-weight:800;color:{{ $col }};background:{{ $bg }};border-radius:8px;padding:2px 6px;display:inline-block">{{ $p }}%</div>
          @else
            <div style="font-size:.95rem;color:var(--green);margin-bottom:4px"><i class="fas fa-shield-alt"></i></div>
            <div style="font-size:.6rem;color:var(--muted)">Stable</div>
          @endif

        </div>
      @endforeach
    </div>

    {{-- Légende --}}
    <div style="display:flex;gap:16px;margin-top:16px;padding-top:14px;border-top:1px solid var(--border);flex-wrap:wrap">
      <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--muted)">
        <span style="width:10px;height:10px;border-radius:3px;background:rgba(239,68,68,.3);display:inline-block"></span> Urgent ≥80%
      </div>
      <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--muted)">
        <span style="width:10px;height:10px;border-radius:3px;background:rgba(245,158,11,.3);display:inline-block"></span> Probable 60–79%
      </div>
      <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--muted)">
        <span style="width:10px;height:10px;border-radius:3px;background:rgba(59,130,246,.2);display:inline-block"></span> Possible &lt;60%
      </div>
      <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--muted)">
        <span style="width:10px;height:10px;border-radius:3px;background:rgba(26,158,63,.15);display:inline-block"></span> Stable
      </div>
      <div style="display:flex;align-items:center;gap:5px;font-size:.75rem;color:var(--muted)">
        <span style="width:10px;height:10px;border-radius:3px;background:var(--border);display:inline-block"></span> Passé
      </div>
    </div>
  </div>
</div>

{{-- ══ SECTION 3 : PRÉDICTIONS MANUELLES 5J (séparées, clairement labelisées) ══ --}}
@if($avenir5j->count() > 0)
<div class="cie-card animate-fade delay-4 mb-6" style="border-left:3px solid var(--blue);opacity:.85">
  <div class="cie-card-header" style="background:#f0f7ff">
    <div class="cie-card-title">
      <span class="card-dot blue"></span>
      Prédictions manuelles (horizon J+1/J+2)
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge-cie badge-blue" style="font-size:.72rem">
        <i class="fas fa-calendar-alt me-1"></i>Prédiction manuelle — non confirmée
      </span>
      <span class="badge-cie badge-blue">{{ $avenir5j->count() }}</span>
    </div>
  </div>

  <div style="padding:12px 24px;background:#f8fbff;border-bottom:1px solid var(--border)">
    <i class="fas fa-info-circle" style="color:var(--blue)"></i>
    <span style="font-size:.82rem;color:var(--muted);margin-left:6px">
      Ces alertes ont été générées par une <strong>prédiction manuelle</strong> (dataset CSV ou modèle horizon).
      Elles ne font pas partie de la surveillance 3H automatique.
      <a href="{{ route('admin.prediction') }}" style="color:var(--blue);font-weight:700">Voir le détail →</a>
    </span>
  </div>

  <div class="table-wrap">
    <table class="cie-table">
      <thead>
        <tr><th>Zone</th><th>Quartier</th><th>Cause</th><th>Date prévue</th><th>Probabilité</th><th>Clients</th></tr>
      </thead>
      <tbody>
        @foreach($avenir5j as $a)
          @php
            $prob = $a->probability;
            $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
            $nbC  = \App\Models\User::where('zone',$a->zone)->where('role','client')->count();
          @endphp
          <tr>
            <td><strong>{{ $a->zone }}</strong></td>
            <td style="font-size:.82rem;color:var(--muted)">{{ $a->quartier ?? '—' }}</td>
            <td style="font-size:.82rem">{{ $a->reason ?? '—' }}</td>
            <td>
              <span style="font-weight:600">{{ $a->start_at->format('d/m') }}</span>
              <span style="color:var(--muted);font-size:.82rem"> {{ $a->start_at->format('H:i') }} → {{ optional($a->end_at)->format('H:i') }}</span>
            </td>
            <td>
              <span class="badge-cie {{ $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-blue') }}">
                {{ $prob }}%
              </span>
            </td>
            <td>
              <span style="font-weight:700;color:var(--orange)">{{ $nbC }}</span>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif

{{-- Modal Notifier agent --}}
<div class="modal fade" id="notifyAgentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:var(--dark);border:none;padding:18px 24px">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-hard-hat me-2" style="color:var(--orange)"></i>
          Notifier agents — <span id="na-zone">—</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:20px">
        <div id="na-loading" style="text-align:center;padding:16px;color:var(--muted)">
          <i class="fas fa-spinner fa-spin me-2"></i>Chargement...
        </div>
        <div id="na-list" style="display:none;flex-direction:column;gap:8px"></div>
        <div id="na-done" style="display:none;text-align:center;padding:14px">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:1.8rem"></i>
          <div style="font-weight:700;margin-top:6px">Agents notifiés</div>
          <div style="font-size:.78rem;color:var(--muted)" id="na-done-time"></div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);padding:12px 20px">
        <button type="button" class="btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
(function(){
  // Compteur auto-refresh
  var secs = 60;
  var el   = document.getElementById('countdown');
  setInterval(function(){
    secs--;
    if (el) el.textContent = secs;
    if (secs <= 0) location.reload();
  }, 1000);

  // Notifier agents
  document.querySelectorAll('.btn-notify-agent').forEach(function(btn){
    btn.addEventListener('click', function(){
      var alertId = this.dataset.id;
      var zone    = this.dataset.zone;
      document.getElementById('na-zone').textContent = zone;
      document.getElementById('na-loading').style.display = 'block';
      document.getElementById('na-list').style.display    = 'none';
      document.getElementById('na-done').style.display    = 'none';
      new bootstrap.Modal(document.getElementById('notifyAgentsModal')).show();
      var csrf = document.querySelector('meta[name=csrf-token]');
      fetch('/admin/alerts/' + alertId + '/notify-agents', {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': csrf ? csrf.content : '',
          'Accept': 'application/json', 'Content-Type': 'application/json'
        }
      }).then(function(r){ return r.json(); }).then(function(d){
        document.getElementById('na-loading').style.display = 'none';
        var list = document.getElementById('na-list');
        list.innerHTML = '';
        (d.agents || []).forEach(function(a){
          list.innerHTML += '<div style="display:flex;align-items:center;gap:10px;background:#f8fafc;border-radius:10px;padding:12px">'
            + '<div style="width:36px;height:36px;border-radius:9px;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-weight:900;font-size:.85rem;color:var(--orange)">'
            + a.name.substring(0,2).toUpperCase() + '</div>'
            + '<div><div style="font-weight:700;font-size:.85rem">' + a.name + '</div>'
            + '<div style="font-size:.72rem;color:var(--muted)">' + a.email + '</div></div></div>';
        });
        if (!d.agents || d.agents.length === 0)
          list.innerHTML = '<div style="color:var(--muted);text-align:center;padding:14px">Aucun agent dans cette zone.</div>';
        document.getElementById('na-done-time').textContent = 'Le ' + (d.notified_at || '');
        list.style.display = 'flex';
        document.getElementById('na-done').style.display = 'block';
      }).catch(function(){
        document.getElementById('na-loading').innerHTML = '<div style="color:var(--red)">Erreur réseau.</div>';
      });
    });
  });
})();
</script>
@endsection
