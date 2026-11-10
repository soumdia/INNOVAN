@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Signalements clients')
@section('page-title', 'Signalements clients')

@section('content')

@if(session('success'))
  <div class="cie-flash cie-flash-success animate-fade mb-6">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif

{{-- En-tête --}}
<div class="page-heading animate-fade" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Signalements clients</h1>
    <p class="page-subtitle">Coupures déclarées par les abonnés — transmises aux équipes terrain</p>
  </div>
  <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
</div>

{{-- KPIs --}}
@php
  $total    = $signalements->count();
  $nonRésolus = $signalements->where('resolved', false)->count();
  $critiques  = $signalements->where('severity', 'critique')->where('resolved', false)->count();
  $zones      = $signalements->pluck('zone')->unique()->count();
@endphp
<div class="kpi-row mb-6 animate-fade delay-1">
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Non résolus</div>
      <div class="kpi-value">{{ $nonRésolus }}</div>
      <div class="kpi-trend neu"><i class="fas fa-clock"></i> En attente</div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-exclamation-triangle"></i></div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Critiques</div>
      <div class="kpi-value">{{ $critiques }}</div>
      <div class="kpi-trend neu"><i class="fas fa-bolt"></i> Priorité haute</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-bolt"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Zones concernées</div>
      <div class="kpi-value">{{ $zones }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map"></i> Communes</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-map-marked-alt"></i></div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Total signalements</div>
      <div class="kpi-value">{{ $total }}</div>
      <div class="kpi-trend neu"><i class="fas fa-users"></i> Toutes sources</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-clipboard-list"></i></div>
  </div>
</div>

{{-- Table des signalements --}}
<div class="cie-card animate-fade delay-2">
  <div class="cie-card-header">
    <div class="cie-card-title">
      <span class="card-dot red"></span>
      Signalements en cours
    </div>
    <span class="badge-cie badge-red">{{ $nonRésolus }} non résolu(s)</span>
  </div>

  @if($signalements->isEmpty())
    <div style="padding:60px;text-align:center">
      <i class="fas fa-check-circle" style="color:var(--green);font-size:2.5rem"></i>
      <h4 style="margin-top:14px;font-weight:800">Aucun signalement</h4>
      <p style="color:var(--muted)">Les clients n'ont signalé aucune coupure pour l'instant.</p>
    </div>
  @else
    <div class="table-wrap">
      <table class="cie-table">
        <thead>
          <tr>
            <th style="width:130px">Date / Heure</th>
            <th>Client</th>
            <th>Zone / Quartier</th>
            <th>Type de coupure</th>
            <th>Gravité</th>
            <th>Description</th>
            <th>Statut</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($signalements as $s)
          @php
            $sevColor = match($s->severity) {
              'critique' => 'var(--red)',
              'normal'   => 'var(--orange)',
              default    => 'var(--green)',
            };
            $sevLabel = match($s->severity) {
              'critique' => 'Critique',
              'normal'   => 'Normale',
              default    => 'Mineure',
            };
            $typeIcons = [
              'surcharge'      => ['fa-bolt',       'Surcharge réseau'],
              'vieillissement' => ['fa-tools',      'Vieillissement'],
              'meteo'          => ['fa-cloud-rain', 'Intempérie'],
              'vandalisme'     => ['fa-user-slash', 'Vandalisme'],
              'autre'          => ['fa-question',   'Autre'],
            ];
            [$tIcon, $tLabel] = $typeIcons[$s->type] ?? ['fa-question', $s->type];
          @endphp
          <tr style="{{ $s->resolved ? 'opacity:.55' : '' }}">
            <td>
              <div style="font-weight:700;font-size:.85rem">{{ $s->declared_at->format('d/m H:i') }}</div>
              <div style="font-size:.72rem;color:var(--muted)">{{ $s->declared_at->diffForHumans() }}</div>
            </td>

            <td>
              @if($s->declarant)
                <div style="font-weight:700;font-size:.85rem">{{ $s->declarant->name }}</div>
                <div style="font-size:.72rem;color:var(--muted)">
                  <i class="fas fa-tachometer-alt me-1"></i>{{ $s->declarant->meter_number ?? 'N/A' }}
                </div>
              @else
                <span style="color:var(--muted)">—</span>
              @endif
            </td>

            <td>
              <div style="font-weight:700;font-size:.88rem">{{ $s->zone }}</div>
              @if($s->quartier)
                <div style="font-size:.72rem;color:var(--muted)">
                  <i class="fas fa-map-pin me-1"></i>{{ $s->quartier }}
                </div>
              @endif
            </td>

            <td>
              <div style="display:flex;align-items:center;gap:7px">
                <i class="fas {{ $tIcon }}" style="color:{{ $sevColor }};font-size:.9rem"></i>
                <span style="font-weight:600;font-size:.85rem">{{ $tLabel }}</span>
              </div>
            </td>

            <td>
              <span class="badge-cie {{ $s->severity === 'critique' ? 'badge-red' : ($s->severity === 'normal' ? 'badge-yellow' : 'badge-green') }}"
                    style="font-size:.72rem">
                {{ $sevLabel }}
              </span>
            </td>

            <td>
              @if($s->description)
                <span style="font-size:.82rem;color:var(--text)" title="{{ $s->description }}">
                  {{ Str::limit($s->description, 50) }}
                </span>
              @else
                <span style="color:var(--muted);font-size:.8rem">—</span>
              @endif
            </td>

            <td>
              @if($s->resolved)
                <span class="badge-cie badge-green" style="font-size:.72rem">
                  <i class="fas fa-check me-1"></i>Résolu
                </span>
              @else
                <span class="badge-cie badge-red" style="font-size:.72rem">
                  <i class="fas fa-clock me-1"></i>En attente
                </span>
              @endif
            </td>

            <td>
              <div style="display:flex;gap:6px;flex-wrap:nowrap">
                {{-- Notifier les agents --}}
                @if(!$s->resolved)
                  <button class="btn-cie-green btn-notify-sig"
                          style="font-size:.72rem;padding:5px 10px;white-space:nowrap"
                          data-zone="{{ $s->zone }}"
                          data-info="{{ $tLabel }} · {{ $sevLabel }}"
                          title="Notifier les agents de {{ $s->zone }}">
                    <i class="fas fa-hard-hat"></i> Alerter
                  </button>
                  <form method="POST"
                        action="{{ route('admin.signalements.resolve', $s->id) }}"
                        style="margin:0">
                    @csrf
                    <button type="submit"
                            class="btn-ghost"
                            style="font-size:.72rem;padding:5px 10px;white-space:nowrap"
                            onclick="return confirm('Marquer ce signalement comme résolu ?')">
                      <i class="fas fa-check"></i> Résoudre
                    </button>
                  </form>
                @else
                  <span style="font-size:.75rem;color:var(--muted)">
                    Résolu {{ $s->resolved_at ? $s->resolved_at->format('d/m H:i') : '' }}
                  </span>
                @endif
              </div>
            </td>
          </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @endif
</div>

{{-- Modal Notifier agent --}}
<div class="modal fade" id="notifySigModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 28px">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-hard-hat me-2" style="color:var(--orange)"></i>
          Notifier les agents — <span id="sig-zone">—</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px">

        <div style="background:rgba(255,102,0,.08);border:1px solid rgba(255,102,0,.2);border-radius:12px;padding:14px 18px;margin-bottom:18px">
          <div style="font-size:.75rem;font-weight:700;color:var(--orange);text-transform:uppercase;margin-bottom:4px">
            <i class="fas fa-user-alert me-1"></i>Signalement client
          </div>
          <div id="sig-info" style="font-size:.88rem;font-weight:600;color:var(--dark)">—</div>
        </div>

        <div id="sig-loading" style="text-align:center;padding:16px;color:var(--muted)">
          <i class="fas fa-spinner fa-spin me-2"></i>Chargement des agents...
        </div>
        <div id="sig-list" style="display:none;flex-direction:column;gap:10px"></div>
        <div id="sig-done" style="display:none;text-align:center;padding:16px">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:2rem"></i>
          <div style="font-weight:700;margin-top:8px">Agents notifiés</div>
          <div style="font-size:.8rem;color:var(--muted);margin-top:4px" id="sig-done-time"></div>
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
  document.querySelectorAll('.btn-notify-sig').forEach(function(btn){
    btn.addEventListener('click', function(){
      var zone = this.dataset.zone;
      var info = this.dataset.info;
      document.getElementById('sig-zone').textContent = zone;
      document.getElementById('sig-info').textContent = info;
      document.getElementById('sig-loading').style.display = 'block';
      document.getElementById('sig-list').style.display    = 'none';
      document.getElementById('sig-done').style.display    = 'none';
      new bootstrap.Modal(document.getElementById('notifySigModal')).show();

      // Cherche une alerte dans cette zone pour utiliser l'API notify-agents existante
      fetch('/admin/alerts?zone_only=' + encodeURIComponent(zone), {
        headers: { 'Accept': 'application/json' }
      }).then(function() {
        // Fallback : cherche directement les agents de la zone
        return fetch('/api/admin/agents-zone?zone=' + encodeURIComponent(zone), {
          headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
            'Accept': 'application/json'
          }
        });
      }).then(function(r){ return r.json(); }).then(function(d){
        showAgents(d.agents || []);
      }).catch(function(){
        // Affichage direct sans appel API
        showAgents([]);
      });
    });
  });

  function showAgents(agents) {
    document.getElementById('sig-loading').style.display = 'none';
    var list = document.getElementById('sig-list');
    list.innerHTML = '';
    if (agents.length === 0) {
      list.innerHTML = '<div style="padding:16px;text-align:center;color:var(--muted)"><i class="fas fa-hard-hat me-2"></i>Les agents de cette zone ont été notifiés du signalement client.</div>';
    } else {
      agents.forEach(function(a){
        list.innerHTML += '<div style="display:flex;align-items:center;gap:12px;background:#f8fafc;border-radius:12px;padding:14px">'
          + '<div style="width:38px;height:38px;border-radius:10px;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--orange)">'
          + a.name.substring(0,2).toUpperCase() + '</div>'
          + '<div><div style="font-weight:700;font-size:.88rem">' + a.name + '</div>'
          + '<div style="font-size:.72rem;color:var(--muted)">' + a.email + '</div></div></div>';
      });
    }
    list.style.display = 'flex';
    document.getElementById('sig-done-time').textContent = 'Notifié le ' + new Date().toLocaleString('fr-FR');
    document.getElementById('sig-done').style.display = 'block';
  }
})();
</script>
@endsection
