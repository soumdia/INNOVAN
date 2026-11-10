@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Clients')
@section('page-title', 'Gestion des clients')

@section('content')

<div class="page-heading animate-fade" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Clients</h1>
    <p class="page-subtitle">{{ $totalClients }} abonnés CIE enregistrés</p>
  </div>
  <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
</div>

{{-- KPIs --}}
<div class="kpi-row mb-6 animate-fade delay-1">
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Total clients</div>
      <div class="kpi-value">{{ $totalClients }}</div>
      <div class="kpi-trend neu"><i class="fas fa-users"></i> Abonnés actifs</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-users"></i></div>
  </div>
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Zones couvertes</div>
      <div class="kpi-value">{{ $zonesStats->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map"></i> Communes</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-map-marked-alt"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Clients en zone à risque</div>
      <div class="kpi-value">
        {{ $clients->filter(fn($c) => $c->alert_count > 0)->count() }}
      </div>
      <div class="kpi-trend down"><i class="fas fa-exclamation-triangle"></i> Avec alertes actives</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-exclamation-triangle"></i></div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Langues</div>
      <div class="kpi-value">{{ $clients->pluck('language')->unique()->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-language"></i> FR / Dioula / Baoulé</div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-language"></i></div>
  </div>
</div>

{{-- Répartition par zone --}}
<div class="grid-2 mb-6 animate-fade delay-2">
  <div class="cie-card">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot orange"></span> Clients par zone</div>
    </div>
    <div class="cie-card-body">
      @php $maxZC = $zonesStats->max() ?: 1; @endphp
      @foreach($zonesStats->sortDesc() as $zone => $count)
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:10px">
          <div style="min-width:110px;font-weight:600;font-size:.82rem;color:var(--dark)">{{ $zone }}</div>
          <div style="flex:1;height:8px;background:#f1f5f9;border-radius:8px;overflow:hidden">
            <div style="height:100%;width:{{ round(($count/$maxZC)*100) }}%;background:var(--orange);border-radius:8px"></div>
          </div>
          <div style="min-width:30px;font-weight:800;font-size:.85rem;text-align:right">{{ $count }}</div>
        </div>
      @endforeach
    </div>
  </div>

  <div class="cie-card">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot blue"></span> Langues de notification</div>
    </div>
    <div class="cie-card-body">
      @php
        $langLabels = ['fr'=>'Français','dioula'=>'Dioula','baoule'=>'Baoulé','bete'=>'Bété'];
        $langStats  = $clients->groupBy('language')->map->count();
        $langTotal  = $langStats->sum() ?: 1;
      @endphp
      @foreach($langStats->sortDesc() as $lang => $cnt)
        @php $lpct = round(($cnt/$langTotal)*100); @endphp
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
          <div style="min-width:90px;font-weight:600;font-size:.82rem">{{ $langLabels[$lang] ?? $lang }}</div>
          <div style="flex:1;height:8px;background:#f1f5f9;border-radius:8px;overflow:hidden">
            <div style="height:100%;width:{{ $lpct }}%;background:var(--blue);border-radius:8px"></div>
          </div>
          <div style="font-size:.82rem;font-weight:800;min-width:50px;text-align:right">{{ $cnt }} ({{ $lpct }}%)</div>
        </div>
      @endforeach
    </div>
  </div>
</div>

{{-- Liste clients --}}
<div class="cie-card animate-fade delay-3">
  <div class="cie-card-header">
    <div class="cie-card-title"><span class="card-dot green"></span> Liste des abonnés</div>
    <input type="text" id="clientSearch" placeholder="Rechercher..." class="form-control form-control-sm" style="width:200px;border-radius:8px;font-size:.85rem">
  </div>
  <div class="table-wrap">
    <table class="cie-table" id="clientsTable">
      <thead>
        <tr><th>Nom</th><th>Email</th><th>Zone</th><th>N° Compteur</th><th>Téléphone</th><th>Langue</th><th>Alertes actives</th></tr>
      </thead>
      <tbody>
        @forelse($clients as $c)
          <tr>
            <td style="font-weight:700">{{ $c->name }}</td>
            <td style="font-size:.82rem;color:var(--muted)">{{ $c->email }}</td>
            <td>
              <span class="badge-cie {{ $c->alert_count > 0 ? 'badge-red' : 'badge-green' }}">{{ $c->zone ?? '—' }}</span>
            </td>
            <td style="font-size:.82rem;font-family:monospace">{{ $c->meter_number ?? '—' }}</td>
            <td style="font-size:.82rem">{{ $c->phone ?? '—' }}</td>
            <td>
              <span class="badge-cie badge-blue" style="font-size:.72rem">
                {{ ['fr'=>'FR','dioula'=>'Dioula','baoule'=>'Baoulé','bete'=>'Bété'][$c->language ?? 'fr'] ?? $c->language }}
              </span>
            </td>
            <td>
              @if($c->alert_count > 0)
                <span class="badge-cie badge-red"><i class="fas fa-bell me-1"></i>{{ $c->alert_count }}</span>
              @else
                <span style="color:var(--green);font-size:.82rem"><i class="fas fa-check me-1"></i>Aucune</span>
              @endif
            </td>
          </tr>
        @empty
          <tr><td colspan="7"><div class="empty-state" style="padding:40px">Aucun client enregistré.</div></td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@endsection
@section('scripts')
<script>
var s = document.getElementById('clientSearch');
if(s){ s.addEventListener('input', function(){
  var v = this.value.toLowerCase();
  document.querySelectorAll('#clientsTable tbody tr').forEach(function(r){
    r.style.display = r.textContent.toLowerCase().includes(v) ? '' : 'none';
  });
}); }
</script>
@endsection
