@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Zones & Communes')
@section('page-title', 'Zones & Communes')

@section('content')

<div class="page-heading animate-fade" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Zones & Communes</h1>
    <p class="page-subtitle">Analyse des {{ $zones->count() }} zones surveillées par le système INNOVAN-CI</p>
  </div>
  <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
</div>

{{-- KPIs --}}
<div class="kpi-row mb-6 animate-fade delay-1">
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Zones surveillées</div>
      <div class="kpi-value">{{ $zones->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map"></i> Communes actives</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-map-marked-alt"></i></div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Zones urgentes</div>
      <div class="kpi-value">{{ $zones->where('avg_prob', '>=', 80)->count() }}</div>
      <div class="kpi-trend down"><i class="fas fa-exclamation-triangle"></i> Prob. &ge; 80%</div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-exclamation-triangle"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Clients couverts</div>
      <div class="kpi-value">{{ $zones->sum('nb_clients') }}</div>
      <div class="kpi-trend neu"><i class="fas fa-users"></i> Abonnés affectés</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-users"></i></div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Total alertes</div>
      <div class="kpi-value">{{ $zones->sum('total_alertes') }}</div>
      <div class="kpi-trend neu"><i class="fas fa-bell"></i> Toutes zones</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-bell"></i></div>
  </div>
</div>

{{-- Tableau zones --}}
<div class="cie-card animate-fade delay-2">
  <div class="cie-card-header">
    <div class="cie-card-title"><span class="card-dot orange"></span> Détail par zone</div>
    <div style="display:flex;gap:8px;align-items:center">
      <input type="text" id="zoneSearch" placeholder="Rechercher une zone..." class="form-control form-control-sm" style="width:200px;border-radius:8px;font-size:.85rem">
    </div>
  </div>
  <div class="table-wrap">
    <table class="cie-table" id="zonesTable">
      <thead>
        <tr>
          <th>Zone / Commune</th>
          <th>Cluster</th>
          <th>Alertes</th>
          <th>Urgentes</th>
          <th>Prob. moyenne</th>
          <th>Clients</th>
          <th>Risque</th>
        </tr>
      </thead>
      <tbody>
        @forelse($zones as $z)
          @php
            $prob = $z->avg_prob ?? 0;
            $riskClass = $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-green');
            $riskLabel = $prob >= 80 ? 'Élevé' : ($prob >= 60 ? 'Modéré' : 'Faible');
            $clColors  = ['A'=>'var(--orange)','B'=>'var(--blue)','C'=>'var(--red)'];
            $clColor   = $clColors[$z->cluster ?? 'A'] ?? 'var(--muted)';
          @endphp
          <tr>
            <td>
              <div style="font-weight:700;color:var(--dark)">{{ $z->zone }}</div>
              <div style="font-size:.75rem;color:var(--muted)"><i class="fas fa-map-marker-alt me-1"></i>Commune</div>
            </td>
            <td>
              <span style="font-weight:800;color:{{ $clColor }};font-size:.9rem">{{ $z->cluster ?? '—' }}</span>
              <div style="font-size:.72rem;color:var(--muted)">
                {{ ['A'=>'Urbain','B'=>'Secondaire','C'=>'Éloigné'][$z->cluster ?? ''] ?? '' }}
              </div>
            </td>
            <td style="font-weight:700;font-size:1rem">{{ $z->total_alertes }}</td>
            <td>
              @if($z->urgentes > 0)
                <span class="badge-cie badge-red">{{ $z->urgentes }}</span>
              @else
                <span style="color:var(--muted)">0</span>
              @endif
            </td>
            <td>
              <div style="display:flex;align-items:center;gap:8px">
                <div style="flex:1;height:6px;background:#f1f5f9;border-radius:6px;overflow:hidden;min-width:60px">
                  <div style="height:100%;width:{{ $prob }}%;background:{{ $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--green)') }};border-radius:6px"></div>
                </div>
                <span style="font-weight:800;color:var(--dark)">{{ $prob }}%</span>
              </div>
            </td>
            <td style="font-weight:600">{{ $z->nb_clients }}</td>
            <td><span class="badge-cie {{ $riskClass }}">{{ $riskLabel }}</span></td>
          </tr>
        @empty
          <tr><td colspan="7">
            <div class="empty-state" style="padding:40px">
              <i class="fas fa-map" style="font-size:2rem;opacity:.3;color:var(--muted)"></i>
              <p style="margin-top:12px">Aucune zone enregistrée. Lancez une prédiction IA d'abord.</p>
              <a href="{{ route('admin.prediction') }}" class="btn-cie" style="margin-top:16px">
                <i class="fas fa-brain"></i> Lancer la prédiction
              </a>
            </div>
          </td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection

@section('scripts')
<script>
var s = document.getElementById('zoneSearch');
if(s){ s.addEventListener('input', function(){
  var v = this.value.toLowerCase();
  document.querySelectorAll('#zonesTable tbody tr').forEach(function(r){
    r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none';
  });
}); }
</script>
@endsection
