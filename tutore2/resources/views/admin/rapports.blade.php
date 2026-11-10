@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Rapports')
@section('page-title', 'Rapports & Statistiques')

@section('content')

@php
  $mapNext    = $alertsNextSlot->map(fn($a) => ['zone'=>$a->zone,'prob'=>$a->probability,'reason'=>$a->reason,'quartier'=>$a->quartier])->values();
  $mapCurrent = $alerts3h->map(fn($a) => ['zone'=>$a->zone,'prob'=>$a->probability])->values();
@endphp

{{-- En-tête --}}
<div class="page-heading animate-fade" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Rapports &amp; Statistiques</h1>
    <p class="page-subtitle">
      Analyse complète INNOVAN-CI · Généré le <strong>{{ now()->format('d/m/Y à H:i') }}</strong>
      · Slot en cours : <strong style="color:var(--orange)">{{ $current3hStart->format('H:i') }} → {{ $current3hEnd->format('H:i') }}</strong>
    </p>
  </div>
  <div style="display:flex;gap:10px">
    <button class="btn-ghost" onclick="window.print()"><i class="fas fa-print"></i> Imprimer</button>
    <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
  </div>
</div>

{{-- ══ KPIs PRINCIPAUX ══ --}}
<div class="kpi-row mb-6 animate-fade delay-1">
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Total alertes en base</div>
      <div class="kpi-value">{{ $totalAlertes }}</div>
      <div class="kpi-trend neu"><i class="fas fa-database"></i> Toutes périodes</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-bell"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Alertes prédites (slot suivant)</div>
      <div class="kpi-value">{{ $alertsNextSlot->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-clock"></i> {{ $nextSlotStart->format('H:i') }} → {{ $nextSlotEnd->format('H:i') }}</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-clock"></i></div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Alertes urgentes (&ge;80%)</div>
      <div class="kpi-value">{{ $urgentes }}</div>
      <div class="kpi-trend {{ $urgentes > 0 ? 'down' : 'up' }}"><i class="fas fa-exclamation-triangle"></i> Priorité haute</div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-exclamation-triangle"></i></div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Prob. moyenne IA</div>
      <div class="kpi-value">{{ $avgProb }}%</div>
      <div class="kpi-trend {{ $avgProb >= 70 ? 'down' : 'up' }}"><i class="fas fa-brain"></i> Modèle INNOVAN-CI</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-percentage"></i></div>
  </div>
</div>

{{-- ══ GRAPHIQUE SLOTS 3H ══ --}}
<div class="cie-card animate-fade delay-2 mb-6">
  <div class="cie-card-header">
    <div class="cie-card-title">
      <span class="card-dot orange"></span>
      Prédictions 3H — Fenêtre glissante 24h
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge-cie badge-orange" style="font-size:.72rem">
        <i class="fas fa-bolt me-1"></i>En cours : {{ $current3hStart->format('H:i') }} → {{ $current3hEnd->format('H:i') }}
      </span>
      <span class="badge-cie badge-blue" style="font-size:.72rem">
        <i class="fas fa-clock me-1"></i>Prédit : {{ $nextSlotStart->format('H:i') }} → {{ $nextSlotEnd->format('H:i') }}
        @if($alertsNextSlot->count()) · {{ $alertsNextSlot->count() }} zone(s) @endif
      </span>
    </div>
  </div>
  <div style="padding:20px 24px">
    <canvas id="slotsChart" height="120"></canvas>
  </div>
  <div style="display:flex;justify-content:space-around;padding:10px 24px 20px;border-top:1px solid var(--border)">
    <div style="text-align:center">
      <div style="font-size:1.3rem;font-weight:900;color:var(--orange)">{{ $alerts3h->count() }}</div>
      <div style="font-size:.72rem;color:var(--muted)">En cours ({{ $current3hStart->format('H:i') }})</div>
    </div>
    <div style="text-align:center">
      <div style="font-size:1.3rem;font-weight:900;color:var(--blue)">{{ $alertsNextSlot->count() }}</div>
      <div style="font-size:.72rem;color:var(--muted)">Prédits ({{ $nextSlotStart->format('H:i') }})</div>
    </div>
    <div style="text-align:center">
      <div style="font-size:1.3rem;font-weight:900;color:var(--red)">{{ $alertsNextSlot->where('probability','>=',80)->count() }}</div>
      <div style="font-size:.72rem;color:var(--muted)">Urgents ≥80%</div>
    </div>
    <div style="text-align:center">
      <div style="font-size:1.3rem;font-weight:900;color:var(--green)">{{ $clientCount }}</div>
      <div style="font-size:.72rem;color:var(--muted)">Clients abonnés</div>
    </div>
  </div>
</div>

{{-- ══ CARTE + CAUSES ══ --}}
<div class="grid-2 mb-6">

  {{-- Carte des risques --}}
  <div class="cie-card animate-fade delay-2" style="overflow:visible">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot orange"></span> Carte des zones à risque</div>
      <div style="display:flex;gap:8px;font-size:.72rem;align-items:center">
        <span style="display:flex;align-items:center;gap:3px"><span style="width:8px;height:8px;border-radius:50%;background:#ef4444;display:inline-block"></span>≥80%</span>
        <span style="display:flex;align-items:center;gap:3px"><span style="width:8px;height:8px;border-radius:50%;background:#f59e0b;display:inline-block"></span>60–79%</span>
        <span style="display:flex;align-items:center;gap:3px"><span style="width:8px;height:8px;border-radius:50%;background:#3b82f6;display:inline-block"></span>&lt;60%</span>
      </div>
    </div>
    <div id="reportMap" style="height:320px;width:100%;border-radius:0 0 16px 16px"></div>
    <script>
      window.REPORT_MAP_NEXT    = @json($mapNext);
      window.REPORT_MAP_CURRENT = @json($mapCurrent);
    </script>
  </div>

  {{-- Distribution causes --}}
  <div class="cie-card animate-fade delay-2">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot blue"></span> Distribution des causes</div>
      <span class="badge-cie badge-blue">{{ $parCause->count() }} causes</span>
    </div>
    <div style="padding:16px 24px;display:flex;align-items:center;gap:20px">
      <div style="flex:0 0 140px">
        <canvas id="causeChartR" width="140" height="140"></canvas>
      </div>
      <div style="flex:1">
        @php
          $causeColors = ['#FF6600','#3b82f6','#ef4444','#8b5cf6','#f59e0b'];
          $causeIcons  = ['surcharge_réseau'=>'fa-bolt','vieillissement_équipement'=>'fa-tools','intempérie_pluie'=>'fa-cloud-rain','défaut_câble_ht'=>'fa-plug','vandalisme_vol_câble'=>'fa-user-slash'];
          $totalCauses = $parCause->sum('total') ?: 1;
        @endphp
        @forelse($parCause as $ci => $c)
          @php $cpct = round(($c->total/$totalCauses)*100); $ccol = $causeColors[$ci % count($causeColors)]; @endphp
          <div style="margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;margin-bottom:4px">
              <div style="display:flex;align-items:center;gap:6px;font-size:.8rem;font-weight:600">
                <span style="width:8px;height:8px;border-radius:50%;background:{{ $ccol }};flex-shrink:0"></span>
                <i class="fas {{ $causeIcons[$c->cause_probable] ?? 'fa-exclamation' }}" style="color:{{ $ccol }};width:12px"></i>
                {{ $c->reason ?? $c->cause_probable }}
              </div>
              <span style="font-size:.78rem;font-weight:800">{{ $c->total }} <span style="color:var(--muted);font-weight:400">({{ $cpct }}%)</span></span>
            </div>
            <div style="height:6px;background:#f1f5f9;border-radius:6px;overflow:hidden">
              <div style="height:100%;width:{{ $cpct }}%;background:{{ $ccol }};border-radius:6px"></div>
            </div>
          </div>
        @empty
          <div style="color:var(--muted);text-align:center;padding:20px">Aucune donnée de cause.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

{{-- ══ ZONES + CLUSTERS ══ --}}
<div class="grid-2 mb-6">

  {{-- Top zones --}}
  <div class="cie-card animate-fade delay-3">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot orange"></span> Classement des zones</div>
      <span class="badge-cie badge-orange">{{ $parZone->count() }} zones</span>
    </div>
    <div class="table-wrap">
      <table class="cie-table" style="font-size:.82rem">
        <thead>
          <tr><th>#</th><th>Zone</th><th>Alertes</th><th>Prob. moy.</th><th>Risque</th><th>Clients</th></tr>
        </thead>
        <tbody>
          @forelse($parZone as $idx => $z)
            @php
              $prob = $z->avg_prob ?? 0;
              $nbC  = \App\Models\User::where('zone',$z->zone)->where('role','client')->count();
            @endphp
            <tr>
              <td>
                @if($idx===0)<span style="color:var(--orange);font-weight:900">#1</span>
                @elseif($idx===1)<span style="color:#94a3b8;font-weight:800">#2</span>
                @elseif($idx===2)<span style="color:#cd7f32;font-weight:800">#3</span>
                @else<span style="color:var(--muted)">#{{ $idx+1 }}</span>@endif
              </td>
              <td style="font-weight:700">{{ $z->zone }}</td>
              <td style="font-weight:700">{{ $z->total }}</td>
              <td>
                <div style="display:flex;align-items:center;gap:6px">
                  <div style="width:60px;height:5px;background:#f1f5f9;border-radius:5px;overflow:hidden">
                    <div style="height:100%;width:{{ $prob }}%;background:{{ $prob>=80?'var(--red)':($prob>=60?'var(--yellow)':'var(--green)') }};border-radius:5px"></div>
                  </div>
                  <strong>{{ $prob }}%</strong>
                </div>
              </td>
              <td><span class="badge-cie {{ $prob>=80?'badge-red':($prob>=60?'badge-yellow':'badge-green') }}">{{ $prob>=80?'Élevé':($prob>=60?'Modéré':'Faible') }}</span></td>
              <td><i class="fas fa-users me-1" style="color:var(--muted)"></i>{{ $nbC }}</td>
            </tr>
          @empty
            <tr><td colspan="6"><div style="padding:20px;text-align:center;color:var(--muted)">Aucune donnée</div></td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  {{-- Clusters + Signalements --}}
  <div style="display:flex;flex-direction:column;gap:20px">

    {{-- Clusters --}}
    <div class="cie-card animate-fade delay-3">
      <div class="cie-card-header">
        <div class="cie-card-title"><span class="card-dot blue"></span> Performance par cluster</div>
      </div>
      <div style="padding:16px 24px">
        @php $clInfo = ['A'=>['Zones urbaines','var(--orange)'],'B'=>['Villes secondaires','var(--blue)'],'C'=>['Zones éloignées','var(--red)']]; $totalCl = $parCluster->sum('total') ?: 1; @endphp
        @forelse($parCluster as $cl)
          @php [$desc,$col] = $clInfo[$cl->cluster] ?? [$cl->cluster,'var(--muted)']; @endphp
          <div style="background:{{ $col }}11;border:1px solid {{ $col }}33;border-radius:10px;padding:12px 16px;margin-bottom:10px">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:6px">
              <div>
                <span style="font-size:.95rem;font-weight:900;color:{{ $col }}">Cluster {{ $cl->cluster }}</span>
                <span style="font-size:.75rem;color:var(--muted);margin-left:6px">{{ $desc }}</span>
              </div>
              <div style="text-align:right;font-size:.8rem">
                <div style="font-weight:800;color:var(--dark)">{{ $cl->total }} alertes</div>
                <div style="color:var(--muted)">moy. {{ $cl->avg_prob }}%</div>
              </div>
            </div>
            <div style="height:5px;background:rgba(0,0,0,.08);border-radius:10px;overflow:hidden">
              <div style="height:100%;width:{{ round(($cl->total/$totalCl)*100) }}%;background:{{ $col }};border-radius:10px"></div>
            </div>
          </div>
        @empty
          <div style="color:var(--muted);text-align:center;padding:20px">Aucune donnée cluster.</div>
        @endforelse
      </div>
    </div>

    {{-- Signalements clients --}}
    <div class="cie-card animate-fade delay-3">
      <div class="cie-card-header">
        <div class="cie-card-title"><span class="card-dot red"></span> Signalements clients (7j)</div>
      </div>
      <div style="padding:20px 24px;display:flex;gap:16px">
        <div style="flex:1;text-align:center;background:rgba(239,68,68,.06);border-radius:12px;padding:14px">
          <div style="font-size:1.8rem;font-weight:900;color:var(--red)">{{ $signalements7j }}</div>
          <div style="font-size:.75rem;color:var(--muted)">Signalements reçus</div>
        </div>
        <div style="flex:1;text-align:center;background:rgba(26,158,63,.06);border-radius:12px;padding:14px">
          <div style="font-size:1.8rem;font-weight:900;color:var(--green)">{{ $sigResolus }}</div>
          <div style="font-size:.75rem;color:var(--muted)">Résolus au total</div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ══ ACTIVITÉ 7 JOURS ══ --}}
@if($parJour->count())
<div class="cie-card animate-fade delay-4 mb-6">
  <div class="cie-card-header">
    <div class="cie-card-title"><span class="card-dot orange"></span> Activité des 7 derniers jours</div>
    <span class="badge-cie badge-orange">{{ $parJour->sum('total') }} alertes</span>
  </div>
  <div style="padding:20px 24px">
    <canvas id="activityChart" height="90"></canvas>
  </div>
</div>
@endif

@endsection

@section('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(function () {

  /* ── Mini-carte rapport ─────────────────────────────────────── */
  try {
    var mapEl = document.getElementById('reportMap');
    if (mapEl && typeof L !== 'undefined') {
      var COORDS = {
        'Abobo':[5.4106,-4.0086],'Yopougon':[5.3490,-4.0702],'Cocody':[5.3714,-3.9637],
        'Marcory':[5.2985,-3.9818],'Koumassi':[5.2914,-3.9558],'Port-Bouët':[5.2506,-3.9296],
        'Adjamé':[5.3588,-4.0153],'Treichville':[5.2966,-4.0107],'Attécoubé':[5.3402,-4.0517],
        'Plateau':[5.3167,-4.0231],'Bingerville':[5.3568,-3.8825],'Anyama':[5.4833,-4.0500],
        'Songon':[5.3367,-4.1833],'Bouaké':[7.6931,-5.0310],'Yamoussoukro':[6.8276,-5.2893],
        'San-Pédro':[4.7485,-6.6363],'Daloa':[6.8774,-6.4502],'Abengourou':[6.7272,-3.4969],
        'Man':[7.4126,-7.5536],'Korhogo':[9.4581,-5.6296],'Gagnoa':[6.1309,-5.9555],
        'Agboville':[5.9282,-4.2153],'Divo':[5.8373,-5.3567],'Adzopé':[6.1070,-3.8640],
      };
      var rmap = L.map('reportMap', { center:[7.54,-5.55], zoom:6, scrollWheelZoom:false, zoomControl:true });
      L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom:18, attribution:'© OpenStreetMap'
      }).addTo(rmap);

      var nextD = window.REPORT_MAP_NEXT    || [];
      var currD = window.REPORT_MAP_CURRENT || [];
      var ni = {}, ci = {};
      nextD.forEach(function(a){ ni[a.zone]=a; });
      currD.forEach(function(a){ ci[a.zone]=a; });

      Object.keys(COORDS).forEach(function(zone){
        var n = ni[zone], c = ci[zone];
        var p = n?n.prob:(c?c.prob:0), has=!!n;
        var col = !has?'#94a3b8':p>=80?'#ef4444':p>=60?'#f59e0b':'#3b82f6';
        var r   = !has?7:p>=80?16:p>=60?12:9;
        var mk  = L.circleMarker(COORDS[zone],{color:col,fillColor:col,radius:r,weight:1.5,fillOpacity:has?.5:.2,opacity:1}).addTo(rmap);
        var body = has
          ? '<b>'+zone+'</b><br><span style="color:'+col+'">'+p+'%</span>'+(n.reason?'<br><small>'+n.reason+'</small>':'')
          : '<b>'+zone+'</b><br><span style="color:#94a3b8">Stable</span>';
        mk.bindPopup(body);
        if(has && p>=70) mk.bindTooltip(zone,{permanent:true,direction:'top',className:'cie-map-tooltip',offset:[0,-6]});
      });

      if(nextD.length>0){
        var bb=nextD.filter(function(a){return COORDS[a.zone];}).map(function(a){return COORDS[a.zone];});
        if(bb.length>0) rmap.fitBounds(L.latLngBounds(bb).pad(0.5));
      }
      setTimeout(function(){ rmap.invalidateSize(); }, 200);
    }
  } catch(e) { console.warn('Report map error:', e); }

  /* ── Graphique slots 3H ─────────────────────────────────────── */
  var slots = @json($slots3hData);
  var sCtx  = document.getElementById('slotsChart');
  if (sCtx && slots && typeof Chart !== 'undefined') {
    new Chart(sCtx, {
      type: 'bar',
      data: {
        labels: slots.map(function(s){ return s.label; }),
        datasets: [
          {
            label: 'Alertes',
            data:  slots.map(function(s){ return s.count; }),
            backgroundColor: slots.map(function(s){
              if (s.is_current) return 'rgba(255,102,0,.85)';
              if (s.is_next)    return 'rgba(59,130,246,.75)';
              return 'rgba(148,163,184,.35)';
            }),
            borderRadius: 6, borderSkipped: false, borderWidth: 0,
          },
          {
            label: 'Prob. moy (%)',
            data:  slots.map(function(s){ return s.avg_prob; }),
            type:  'line',
            borderColor: 'rgba(255,102,0,.5)',
            backgroundColor: 'transparent',
            borderWidth: 2,
            pointRadius: 4,
            pointBackgroundColor: slots.map(function(s){
              return s.is_current ? '#FF6600' : s.is_next ? '#3b82f6' : '#94a3b8';
            }),
            tension: 0.3,
            yAxisID: 'yProb',
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index' },
        plugins: {
          legend: { display: true, labels: { font: { size: 11 }, color: '#64748b' } },
          tooltip: { backgroundColor:'#0f172a', padding:10, cornerRadius:8 }
        },
        scales: {
          x: { grid: { display:false }, ticks: { font:{ size:11 }, color:'#94a3b8' } },
          y: { beginAtZero:true, ticks: { stepSize:1, font:{ size:11 }, color:'#94a3b8' }, grid: { color:'rgba(0,0,0,.04)' } },
          yProb: { position:'right', min:0, max:100, ticks: { font:{ size:10 }, color:'rgba(255,102,0,.6)', callback: function(v){ return v+'%'; } }, grid: { display:false } }
        }
      }
    });
  }

  /* ── Graphique causes (donut) ───────────────────────────────── */
  var cCtx = document.getElementById('causeChartR');
  if (cCtx && typeof Chart !== 'undefined') {
    var causeEl = document.querySelectorAll('#causeChartR');
    var causeLbl = @json($parCause->pluck('cause_probable')->map(fn($c) => \Illuminate\Support\Str::limit($c,18))->values());
    var causeVal = @json($parCause->pluck('total')->values());
    if (causeVal.length > 0) {
      new Chart(cCtx, {
        type: 'doughnut',
        data: {
          labels: causeLbl,
          datasets: [{ data: causeVal, backgroundColor: ['#FF6600','#3b82f6','#ef4444','#8b5cf6','#f59e0b'], borderWidth: 2, borderColor: '#fff' }]
        },
        options: { responsive:false, plugins: { legend: { display:false }, tooltip: { backgroundColor:'#0f172a', cornerRadius:8 } }, cutout:'65%' }
      });
    }
  }

  /* ── Activité 7 jours ───────────────────────────────────────── */
  var aEl = document.getElementById('activityChart');
  if (aEl && typeof Chart !== 'undefined') {
    var aLbl = @json($parJour->pluck('jour')->map(fn($d) => \Carbon\Carbon::parse($d)->translatedFormat('d M'))->values());
    var aVal = @json($parJour->pluck('total')->values());
    new Chart(aEl, {
      type: 'bar',
      data: {
        labels: aLbl,
        datasets: [{ label:'Alertes', data: aVal, backgroundColor:'rgba(255,102,0,.75)', borderRadius:6, borderSkipped:false, borderWidth:0 }]
      },
      options: {
        responsive:true,
        plugins: { legend:{ display:false }, tooltip:{ backgroundColor:'#0f172a', padding:10, cornerRadius:8 } },
        scales: {
          x: { grid:{ display:false }, ticks:{ font:{ size:11 }, color:'#94a3b8' } },
          y: { beginAtZero:true, ticks:{ stepSize:1, font:{ size:11 }, color:'#94a3b8' }, grid:{ color:'rgba(0,0,0,.04)' } }
        }
      }
    });
  }

})();
</script>
@endsection
