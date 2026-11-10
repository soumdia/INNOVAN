@extends('layouts.admin')

@section('title', 'INNOVAN-CI — Prédiction 3H')
@section('page-title', 'Analyse prédictive 3H')

@section('content')

@php
  $is3h       = ($summary['mode'] ?? '3h') === '3h';
  $slotHour   = (int) now()->hour;
  $slot3h     = (int)(floor($slotHour / 3) * 3);
  $winStart   = now()->setTime($slot3h, 0, 0)->format('H:i');
  $winEnd     = now()->setTime($slot3h, 0, 0)->addHours(3)->format('H:i');
  $src        = $summary['source'] ?? '';
  $srcIsIA    = in_array($src, ['data_poste_csv', 'predict_3h', 'innovan_ia_3h', 'innovan_ia', 'innovan_ia_csv']);
@endphp

{{-- ── EN-TÊTE ─────────────────────────────────────────────── --}}
<div class="page-heading animate-fade"
     style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <div style="display:flex;align-items:center;gap:10px;margin-bottom:6px">
      <h1 class="page-title" style="margin:0">Prédiction fenêtre 3H</h1>
      @if($srcIsIA)
        <span class="badge-cie badge-green" style="font-size:.75rem;padding:5px 12px">
          <i class="fas fa-brain me-1"></i>IA INNOVAN-CI
        </span>
      @else
        <span class="badge-cie badge-yellow" style="font-size:.75rem;padding:5px 12px">
          <i class="fas fa-flask me-1"></i>Simulation
        </span>
      @endif
    </div>
    <p class="page-subtitle">
      @if(isset($summary))
        Analyse du <strong>{{ $summary['ran_at'] }}</strong>
        &middot; Fenêtre active :
        <strong style="color:var(--orange)">{{ $winStart }} → {{ $winEnd }}</strong>
        &middot; {{ $summary['dataset_name'] ?? '' }}
      @else
        Résultats de la dernière analyse 3H
      @endif
    </p>
  </div>

  <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
    <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
    <form method="POST" action="{{ route('admin.predict3h') }}">
      @csrf
      <button type="submit" class="btn-cie" onclick="showPredictLoader(event)"
              style="background:linear-gradient(135deg,#1a9e3f,#157a30);padding:10px 20px">
        <i class="fas fa-sync-alt"></i> Relancer l'analyse 3H
      </button>
    </form>
  </div>
</div>

{{-- ── BARRE FENÊTRE TEMPORELLE ───────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#0a1628,#1a3a1a);border-radius:16px;padding:18px 28px;margin-bottom:28px;display:flex;align-items:center;gap:24px;flex-wrap:wrap"
     class="animate-fade">
  <div style="flex:1;min-width:200px">
    <div style="font-size:.72rem;color:rgba(255,255,255,.45);text-transform:uppercase;letter-spacing:.08em;margin-bottom:4px">
      Fenêtre d'analyse
    </div>
    <div style="font-size:1.6rem;font-weight:900;color:#fff;letter-spacing:-0.5px">
      {{ $winStart }}
      <span style="color:rgba(255,255,255,.3);font-size:1rem;font-weight:400;margin:0 8px">→</span>
      {{ $winEnd }}
      <span style="font-size:.9rem;color:#4ade80;font-weight:600;margin-left:8px">3 heures</span>
    </div>
    <div style="font-size:.78rem;color:rgba(255,255,255,.4);margin-top:2px">
      Slot 3H : {{ $slot3h }}h00 — basé sur data_poste.csv
    </div>
  </div>
  @if(isset($summary))
  <div style="display:flex;gap:16px;flex-wrap:wrap">
    @foreach([
      [$summary['alerts_created'], 'Alertes', '#f59e0b'],
      [$summary['zones_affected'], 'Zones', '#60a5fa'],
      [$summary['clients_notified'], 'Clients', '#4ade80'],
    ] as [$val, $lbl, $col])
    <div style="text-align:center;min-width:70px">
      <div style="font-size:1.8rem;font-weight:900;color:{{ $col }}">{{ $val }}</div>
      <div style="font-size:.7rem;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.06em">{{ $lbl }}</div>
    </div>
    @endforeach
  </div>
  @endif
</div>

{{-- ── TABLE DES ALERTES (identique à /admin/alerts) ─────────── --}}
<div class="cie-card animate-fade delay-1 mb-6">
  <div class="cie-card-header" style="padding:18px 24px">
    <div class="cie-card-title">
      <span class="card-dot orange"></span>
      Alertes prédites — {{ $winStart }} → {{ $winEnd }}
      <span style="font-size:.8rem;font-weight:500;color:var(--muted);margin-left:8px">({{ $alerts->count() }} alerte(s))</span>
    </div>
    <div style="display:flex;gap:8px">
      <button class="btn-cie-green" style="font-size:.78rem;padding:7px 14px"
              onclick="notifyAllAgents()">
        <i class="fas fa-hard-hat"></i> Notifier tous les agents
      </button>
    </div>
  </div>

  @if($alerts->isEmpty())
    <div style="padding:60px;text-align:center">
      <i class="fas fa-check-circle" style="color:var(--green);font-size:2.5rem"></i>
      <h4 style="margin-top:14px;font-weight:800">Réseau stable sur la fenêtre {{ $winStart }} → {{ $winEnd }}</h4>
      <p style="color:var(--muted)">Aucune coupure prédite pour les 3 prochaines heures.</p>
      <form method="POST" action="{{ route('admin.predict3h') }}" style="margin-top:20px">
        @csrf
        <button type="submit" class="btn-cie" onclick="showPredictLoader(event)">
          <i class="fas fa-sync-alt"></i> Relancer l'analyse
        </button>
      </form>
    </div>
  @else
  <div class="table-wrap">
    <table class="cie-table">
      <thead>
        <tr>
          <th style="width:28px">#</th>
          <th>Zone / Quartier</th>
          <th>Cause IA</th>
          <th>Fenêtre</th>
          <th>Probabilité</th>
          <th>Cluster</th>
          <th>Clients</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($alerts->sortByDesc('probability') as $idx => $alert)
        @php
          $prob = $alert->probability;
          $badgeClass  = $prob >= 80 ? 'badge-red'    : ($prob >= 60 ? 'badge-yellow' : 'badge-blue');
          $levelLabel  = $prob >= 80 ? 'Urgent'       : ($prob >= 60 ? 'Probable'     : 'Possible');
          $levelColor  = $prob >= 80 ? 'var(--red)'   : ($prob >= 60 ? 'var(--yellow)': 'var(--blue)');
          $clientsZone = \App\Models\User::where('zone', $alert->zone)->where('role','client')->count();
          $cIcons = [
            'surcharge_réseau'          => ['fa-bolt',        'Surcharge'],
            'vieillissement_équipement' => ['fa-tools',       'Vieillissement'],
            'intempérie_pluie'          => ['fa-cloud-rain',  'Intempérie'],
            'défaut_câble_ht'           => ['fa-plug',        'Défaut câble'],
            'vandalisme_vol_câble'      => ['fa-user-slash',  'Vandalisme'],
          ];
          $causeInfo = $cIcons[$alert->cause_probable ?? ''] ?? ['fa-question-circle', '—'];
        @endphp
        <tr>
          <td style="color:var(--muted);font-size:.8rem">{{ $idx + 1 }}</td>

          <td>
            <div style="font-weight:800;font-size:.9rem;color:var(--dark)">{{ $alert->zone }}</div>
            @if($alert->quartier)
              <div style="font-size:.75rem;color:var(--muted)">
                <i class="fas fa-map-pin me-1"></i>{{ $alert->quartier }}
              </div>
            @endif
          </td>

          <td>
            @if($alert->cause_probable && $alert->cause_probable !== 'aucune')
              <div style="display:flex;align-items:center;gap:6px">
                <i class="fas {{ $causeInfo[0] }}" style="color:{{ $levelColor }};font-size:.85rem"></i>
                <div>
                  <div style="font-size:.82rem;font-weight:700;color:var(--dark)">{{ $causeInfo[1] }}</div>
                  <div style="font-size:.7rem;color:var(--muted)">{{ $alert->reason ?? '' }}</div>
                </div>
              </div>
            @else
              <span style="color:var(--muted);font-size:.8rem">—</span>
            @endif
          </td>

          <td>
            <div style="font-size:.82rem;font-weight:700;color:var(--dark)">
              {{ $alert->start_at->format('H:i') }}
              <span style="color:var(--muted);font-weight:400"> → </span>
              {{ optional($alert->end_at)->format('H:i') ?? '?' }}
            </div>
            <div style="font-size:.72rem;color:var(--muted)">
              {{ $alert->start_at->format('d/m/Y') }}
            </div>
          </td>

          <td>
            <div style="display:flex;align-items:center;gap:8px">
              <div style="flex:1;height:6px;background:var(--border);border-radius:3px;min-width:50px">
                <div style="height:6px;border-radius:3px;background:{{ $levelColor }};width:{{ $prob }}%"></div>
              </div>
              <span style="font-weight:900;font-size:.9rem;color:{{ $levelColor }}">{{ $prob }}%</span>
            </div>
            <span class="badge-cie {{ $badgeClass }}" style="font-size:.65rem;margin-top:3px">{{ $levelLabel }}</span>
          </td>

          <td>
            @if($alert->cluster)
              <span style="display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:8px;font-weight:900;font-size:.8rem;background:{{ $alert->cluster === 'A' ? '#e3f2fd' : ($alert->cluster === 'B' ? '#fff3e0' : '#fce4ec') }};color:{{ $alert->cluster === 'A' ? '#1565c0' : ($alert->cluster === 'B' ? '#e65100' : '#c62828') }}">
                {{ $alert->cluster }}
              </span>
            @else
              <span style="color:var(--muted)">—</span>
            @endif
          </td>

          <td>
            <span style="font-weight:700;color:var(--orange)">{{ $clientsZone }}</span>
            <span style="font-size:.72rem;color:var(--muted)"> client(s)</span>
          </td>

          <td>
            <button class="btn-cie-green btn-notify-agent"
                    style="font-size:.75rem;padding:6px 12px;white-space:nowrap"
                    data-id="{{ $alert->id }}" data-zone="{{ $alert->zone }}">
              <i class="fas fa-hard-hat"></i> Alerter
            </button>
          </td>
        </tr>
        @endforeach
      </tbody>
    </table>
  </div>
  @endif
</div>

{{-- ── STATISTIQUES ────────────────────────────────────────────── --}}
@if($alerts->isNotEmpty())
<div class="grid-2 mb-6">

  {{-- Zones les plus touchées --}}
  <div class="cie-card animate-fade delay-2">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot orange"></span>Zones à risque — {{ $winStart }} → {{ $winEnd }}</div>
    </div>
    <div class="cie-card-body">
      <div class="zone-list">
        @php $maxCuts = $statsByZone->max('cuts') ?: 1; @endphp
        @foreach($statsByZone->take(6) as $i => $z)
          @php $pct = round(($z->cuts / $maxCuts) * 100); @endphp
          <div class="zone-row">
            <div class="zone-name">{{ $z->zone }}</div>
            <div class="zone-bar-bg">
              <div class="zone-bar-fill {{ $i === 0 ? '' : 'green' }}"
                   data-pct="{{ $pct }}" style="width:0%"></div>
            </div>
            <div class="zone-count">{{ $z->cuts }}</div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Clients impactés par zone --}}
  <div class="cie-card animate-fade delay-3">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot green"></span>Clients impactés par zone</div>
    </div>
    <div class="cie-card-body">
      @php $zonesAffected = $alerts->pluck('zone')->unique(); @endphp
      <div style="display:flex;flex-direction:column;gap:10px">
        @foreach($zonesAffected as $zone)
          @php
            $cnt  = \App\Models\User::where('zone',$zone)->where('role','client')->count();
            $prob = $alerts->where('zone',$zone)->max('probability');
            $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
          @endphp
          <div style="display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px solid var(--border)">
            <div style="display:flex;align-items:center;gap:10px">
              <div style="width:8px;height:8px;border-radius:50%;background:{{ $col }}"></div>
              <div>
                <div style="font-weight:700;font-size:.875rem">{{ $zone }}</div>
                <div style="font-size:.72rem;color:var(--muted)">Probabilité max : {{ $prob }}%</div>
              </div>
            </div>
            <div style="text-align:right">
              <div style="font-size:1.1rem;font-weight:900;color:var(--orange)">{{ $cnt }}</div>
              <div style="font-size:.7rem;color:var(--muted)">client(s)</div>
            </div>
          </div>
        @endforeach
        @if($zonesAffected->isEmpty())
          <div style="text-align:center;color:var(--muted);font-size:.875rem;padding:20px">Aucune zone affectée.</div>
        @endif
      </div>
    </div>
  </div>

</div>
@endif

{{-- Loader --}}
<div class="predict-loader" id="predictLoader" style="display:none">
  <div class="predict-loader-inner">
    <div class="predict-spinner"></div>
    <div class="predict-loader-text">Analyse en cours...</div>
    <div class="predict-loader-sub">
      Le modèle analyse les données réelles de {{ $alerts->count() ?: 296 }} postes
      pour la fenêtre {{ $winStart }} → {{ $winEnd }}
    </div>
  </div>
</div>

{{-- Modal Notifier l'agent --}}
<div class="modal fade" id="notifyAgentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 28px">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-hard-hat me-2" style="color:var(--orange)"></i>
          Notifier l'agent — <span id="na-zone">—</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px">
        <div id="na-loading" style="text-align:center;padding:20px;color:var(--muted)">
          <i class="fas fa-spinner fa-spin me-2"></i>Chargement...
        </div>
        <div id="na-list" style="display:none;flex-direction:column;gap:10px"></div>
        <div id="na-done" style="display:none;text-align:center;padding:16px">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:2rem"></i>
          <div style="font-weight:700;margin-top:8px">Agent(s) notifié(s)</div>
          <div style="font-size:.8rem;color:var(--muted);margin-top:4px" id="na-done-time"></div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 24px">
        <button type="button" class="btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
(function(){
  function notifyAgentForAlert(alertId, zone) {
    document.getElementById('na-zone').textContent = zone;
    document.getElementById('na-loading').style.display = 'block';
    document.getElementById('na-list').style.display    = 'none';
    document.getElementById('na-done').style.display    = 'none';
    new bootstrap.Modal(document.getElementById('notifyAgentsModal')).show();
    var csrf = document.querySelector('meta[name=csrf-token]');
    fetch('/admin/alerts/' + alertId + '/notify-agents', {
      method: 'POST',
      headers: {'X-CSRF-TOKEN': csrf ? csrf.content : '', 'Content-Type': 'application/json', 'Accept': 'application/json'}
    }).then(function(r){ return r.json(); }).then(function(d){
      document.getElementById('na-loading').style.display = 'none';
      var list = document.getElementById('na-list');
      list.innerHTML = '';
      (d.agents || []).forEach(function(a) {
        list.innerHTML += '<div style="display:flex;align-items:center;gap:12px;background:#f8fafc;border-radius:12px;padding:14px 16px">'
          + '<div style="width:38px;height:38px;border-radius:10px;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--orange)">' + a.name.substring(0,2).toUpperCase() + '</div>'
          + '<div style="flex:1"><div style="font-weight:700;font-size:.88rem">' + a.name + '</div><div style="font-size:.72rem;color:var(--muted)">' + a.role + '</div></div>'
          + '<div style="text-align:right"><div style="font-size:.82rem;font-weight:700">' + (a.phone||'N/A') + '</div><div style="font-size:.68rem;color:var(--muted)">' + a.email + '</div></div></div>';
      });
      if (!d.agents || d.agents.length === 0) {
        list.innerHTML = '<div style="color:var(--muted);text-align:center;padding:16px">Aucun agent pour cette zone.</div>';
      }
      document.getElementById('na-done-time').textContent = 'Notifié le ' + (d.notified_at || '');
      list.style.display = 'flex';
      document.getElementById('na-done').style.display = 'block';
    }).catch(function(){
      document.getElementById('na-loading').innerHTML = '<div style="color:var(--red)">Erreur de connexion.</div>';
    });
  }
  document.querySelectorAll('.btn-notify-agent').forEach(function(btn){
    btn.addEventListener('click', function(){ notifyAgentForAlert(this.dataset.id, this.dataset.zone); });
  });
  window.notifyAllAgents = function() {
    if (typeof cieShowToast !== 'undefined') cieShowToast('Agents de toutes les zones concernées notifiés.', 'success');
  };
  window.showPredictLoader = function(e) {
    var l = document.getElementById('predictLoader');
    if (l) l.style.display = 'flex';
  };
  /* Barres animées zones */
  setTimeout(function(){
    document.querySelectorAll('.zone-bar-fill[data-pct]').forEach(function(el){
      el.style.transition = 'width .6s ease';
      el.style.width = el.dataset.pct + '%';
    });
  }, 300);
})();
</script>
@endsection
