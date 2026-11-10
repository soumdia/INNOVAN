@extends('layouts.admin')

@section('title', 'INNOVAN-CI — Tableau de bord')
@section('page-title', 'Tableau de bord')

@section('content')
{{-- Toutes les variables viennent du controller — ne pas les recalculer ici --}}

@if(session('success'))
<div class="cie-flash cie-flash-success animate-fade mb-6">
  <i class="fas fa-check-circle"></i> {{ session('success') }}
</div>
@endif

{{-- ===== COMMAND CENTER ===== --}}
<div class="cmd-center animate-fade">
  <div class="cmd-center-grid"></div>
  <div class="cmd-center-inner">
    <div>
      <div class="cmd-title">
        <i class="fas fa-bolt me-2" style="color:var(--orange)"></i>
        Centre de commande CIE
      </div>
      <div class="cmd-subtitle">
        <span class="live-dot"></span>
        Système opérationnel · Mis à jour le {{ now()->format('d/m/Y à H:i') }}
      </div>
    </div>

    <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
      {{-- Fenêtre 3H active --}}
      <div style="display:flex;align-items:center;gap:8px;background:rgba(59,130,246,.15);border:1px solid rgba(59,130,246,.3);border-radius:10px;padding:8px 14px">
        <span class="live-dot" style="background:#60a5fa"></span>
        <span style="color:rgba(255,255,255,.85);font-size:.8rem;font-weight:700">
          Fenêtre 3H :
          <strong style="color:#93c5fd">{{ $current3hStart->format('H:i') }} → {{ $current3hEnd->format('H:i') }}</strong>
        </span>
        @if($alerts3h->count() > 0)
          <span style="background:#ef4444;color:#fff;font-size:.68rem;font-weight:900;border-radius:10px;padding:2px 7px">{{ $alerts3h->count() }}</span>
        @endif
      </div>
      @if(Auth::user()->role === 'admin')
        {{-- Bouton principal : Actualiser prédiction 3H --}}
        <form method="POST" action="{{ route('admin.predict3h') }}" style="margin:0">
          @csrf
          <button type="submit" class="btn-ia-predict" onclick="showLoader(event)"
                  style="background:linear-gradient(135deg,#1a9e3f,#157a30)">
            <span class="btn-ia-icon"><i class="fas fa-sync-alt"></i></span>
            Actualiser 3H
          </button>
        </form>
      @else
        <div style="display:flex;align-items:center;gap:8px;background:rgba(255,255,255,.08);border:1px solid rgba(255,255,255,.12);border-radius:10px;padding:8px 14px">
          <i class="fas fa-eye" style="color:rgba(255,255,255,.5);font-size:.85rem"></i>
          <span style="color:rgba(255,255,255,.5);font-size:.8rem">Mode consultation</span>
        </div>
      @endif
    </div>
  </div>

  {{-- KPI Cards dark ===== --}}
  <div class="cmd-kpi-grid">
    <div class="kpi-v2 hover-lift">
      <div class="kpi-v2-info">
        <div class="kpi-v2-label">Clients actifs</div>
        <div class="kpi-v2-value" data-count="{{ $clientCount ?? 0 }}">0</div>
        <div class="kpi-v2-trend up"><i class="fas fa-arrow-up"></i> +2.4% ce mois</div>
        <svg class="kpi-sparkline" width="80" height="20" viewBox="0 0 80 20">
          <polyline points="0,18 15,12 30,15 45,8 60,10 75,4 80,6" fill="none" stroke="rgba(74,222,128,.6)" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="kpi-v2-icon green"><i class="fas fa-users"></i></div>
    </div>

    <div class="kpi-v2 hover-lift">
      <div class="kpi-v2-info">
        <div class="kpi-v2-label">Alertes prédites (slot suivant)</div>
        <div class="kpi-v2-value" data-count="{{ $totalAlertes3h }}">0</div>
        <div class="kpi-v2-trend {{ $totalAlertes3h > 0 ? 'down' : 'up' }}">
          <i class="fas fa-clock"></i>
          {{ $nextSlotStart->format('H:i') }} → {{ $nextSlotEnd->format('H:i') }}
          @if(!$nextSlotStart->isToday())<span style="font-size:.68rem;opacity:.7"> · demain</span>@endif
        </div>
        <svg class="kpi-sparkline" width="80" height="20" viewBox="0 0 80 20">
          <polyline points="0,4 15,8 30,6 45,14 60,10 75,16 80,18" fill="none" stroke="rgba(248,113,113,.6)" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="kpi-v2-icon red"><i class="fas fa-clock"></i></div>
    </div>

    <div class="kpi-v2 hover-lift">
      <div class="kpi-v2-info">
        <div class="kpi-v2-label">Zones à risque (slot suivant)</div>
        <div class="kpi-v2-value" data-count="{{ $zonesAffected }}">0</div>
        <div class="kpi-v2-trend neu"><i class="fas fa-map"></i> Communes prédites</div>
        <svg class="kpi-sparkline" width="80" height="20" viewBox="0 0 80 20">
          <polyline points="0,10 15,10 30,10 45,10 60,10 75,10 80,10" fill="none" stroke="rgba(96,165,250,.6)" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="kpi-v2-icon blue"><i class="fas fa-map-marked-alt"></i></div>
    </div>

    <a href="{{ route('admin.signalements') }}" style="text-decoration:none">
    <div class="kpi-v2 hover-lift">
      <div class="kpi-v2-info">
        <div class="kpi-v2-label">Signalements clients</div>
        <div class="kpi-v2-value" data-count="{{ $clientSignalements }}" style="{{ $clientSignalements > 0 ? 'color:#ef4444' : '' }}">0</div>
        <div class="kpi-v2-trend {{ $clientSignalements > 0 ? 'down' : 'up' }}">
          <i class="fas fa-{{ $clientSignalements > 0 ? 'exclamation-triangle' : 'check' }}"></i>
          {{ $clientSignalements > 0 ? 'Voir les signalements' : 'Aucun en attente' }}
        </div>
        <svg class="kpi-sparkline" width="80" height="20" viewBox="0 0 80 20">
          <polyline points="0,14 15,16 30,12 45,10 60,8 75,6 80,4" fill="none" stroke="rgba(251,146,60,.6)" stroke-width="1.5" stroke-linecap="round"/>
        </svg>
      </div>
      <div class="kpi-v2-icon orange"><i class="fas fa-exclamation-circle"></i></div>
    </div>
    </a>
  </div>
</div>

{{-- ===== ANALYSE 3H AUJOURD'HUI + CAUSES ===== --}}
<div class="grid-2 mb-6">

  {{-- Graphe : 8 slots 3H de la journée --}}
  <div class="section-card animate-fade delay-1">
    <div class="section-card-header">
      <div class="section-card-title">
        <span class="section-dot orange"></span>
        Prédictions 3H — Fenêtre glissante 24h
      </div>
      <div style="display:flex;gap:8px;align-items:center">
        <span class="badge-cie badge-orange" style="font-size:.72rem">
          <i class="fas fa-bolt me-1"></i>En cours : {{ $current3hStart->format('H:i') }} → {{ $current3hEnd->format('H:i') }}
        </span>
        <span class="badge-cie badge-blue" style="font-size:.72rem">
          <i class="fas fa-clock me-1"></i>Prédit : {{ $nextSlotStart->format('H:i') }} → {{ $nextSlotEnd->format('H:i') }} · {{ $totalAlertes3h }} alerte(s)
        </span>
      </div>
    </div>
    <div style="padding:20px 24px">
      <canvas id="weeklyChart" height="180"></canvas>
    </div>
    <div style="display:flex;justify-content:space-around;padding:12px 24px 20px;border-top:1px solid var(--border)">
      <div style="text-align:center">
        <div style="font-size:1.4rem;font-weight:900;color:var(--orange)">{{ $alerts3h->count() }}</div>
        <div style="font-size:.72rem;color:var(--muted);font-weight:600">En cours ({{ $current3hStart->format('H:i') }})</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:1.4rem;font-weight:900;color:var(--blue)">{{ $alertsNextSlot->count() }}</div>
        <div style="font-size:.72rem;color:var(--muted);font-weight:600">Prédits ({{ $nextSlotStart->format('H:i') }})</div>
      </div>
      <div style="text-align:center">
        <div style="font-size:1.4rem;font-weight:900;color:var(--red)">{{ $highRiskCount }}</div>
        <div style="font-size:.72rem;color:var(--muted);font-weight:600">Urgentes (&ge;80%)</div>
      </div>
    </div>
  </div>

  {{-- Graphe : distribution des causes --}}
  <div class="section-card animate-fade delay-2">
    <div class="section-card-header">
      <div class="section-card-title">
        <span class="section-dot blue"></span> Causes identifiées par l'IA
      </div>
      <span class="badge-cie badge-blue">{{ $causeStats->count() }} causes</span>
    </div>
    <div style="padding:16px 24px;display:flex;align-items:center;gap:24px">
      <div style="flex:0 0 160px">
        <canvas id="causeChart" width="160" height="160"></canvas>
      </div>
      <div style="flex:1">
        @php
          $causeColors = ['#FF6600','#3b82f6','#ef4444','#8b5cf6','#f59e0b'];
          $causeIcons  = [
            'surcharge_réseau'          => 'fa-bolt',
            'vieillissement_équipement' => 'fa-tools',
            'intempérie_pluie'          => 'fa-cloud-rain',
            'défaut_câble_ht'           => 'fa-plug',
            'vandalisme_vol_câble'      => 'fa-user-slash',
          ];
          $totalCauses = $causeStats->sum('total') ?: 1;
        @endphp
        @forelse($causeStats as $ci => $c)
          @php
            $cpct = round(($c->total / $totalCauses) * 100);
            $cicon = $causeIcons[$c->cause_probable] ?? 'fa-exclamation';
            $ccol  = $causeColors[$ci % count($causeColors)];
          @endphp
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:10px">
            <div style="width:10px;height:10px;border-radius:50%;background:{{ $ccol }};flex-shrink:0"></div>
            <i class="fas {{ $cicon }}" style="color:{{ $ccol }};font-size:.8rem;width:14px"></i>
            <div style="flex:1;font-size:.78rem;font-weight:600;color:var(--text)">{{ $c->reason ?? $c->cause_probable }}</div>
            <div style="font-size:.78rem;font-weight:800;color:var(--dark)">{{ $cpct }}%</div>
          </div>
        @empty
          <div style="color:var(--muted);font-size:.85rem">Lancez une prédiction pour voir les causes.</div>
        @endforelse
      </div>
    </div>
  </div>
</div>

{{-- ===== ZONE RANKING + CLUSTERS ===== --}}
<div class="grid-2 mb-6">

  {{-- Classement zones --}}
  <div class="section-card animate-fade delay-3">
    <div class="section-card-header">
      <div class="section-card-title">
        <span class="section-dot orange"></span> Zones les plus à risque
      </div>
      <span class="badge-cie badge-orange">{{ $statsByZone->count() }} zones</span>
    </div>
    <div style="padding:16px 24px">
      @php $maxCuts = $statsByZone->max('cuts') ?: 1; @endphp
      <div class="zone-list">
        @forelse($statsByZone->take(8) as $idx => $z)
          @php
            $pct    = round(($z->cuts / $maxCuts) * 100);
            $avgP   = round($z->avg_prob ?? 0);
            $zcolor = $avgP >= 80 ? 'var(--red)' : ($avgP >= 60 ? 'var(--yellow)' : 'var(--green)');
          @endphp
          <div class="zone-row" style="margin-bottom:12px">
            <div style="display:flex;flex-direction:column;min-width:90px">
              <span class="zone-name" title="{{ $z->zone }}">{{ $z->zone }}</span>
              <span style="font-size:.7rem;color:{{ $zcolor }};font-weight:700">moy. {{ $avgP }}%</span>
            </div>
            <div class="zone-bar-bg" style="flex:1;margin:0 10px">
              <div class="zone-bar-fill" data-pct="{{ $pct }}" style="width:0%;background:{{ $zcolor }}"></div>
            </div>
            <div style="display:flex;align-items:center;gap:6px">
              <span class="badge-cie {{ $avgP >= 80 ? 'badge-red' : ($avgP >= 60 ? 'badge-yellow' : 'badge-green') }}" style="font-size:.7rem">{{ $z->cuts }}</span>
            </div>
          </div>
        @empty
          <div style="color:var(--muted);text-align:center;padding:20px">Aucune donnée</div>
        @endforelse
      </div>
    </div>
  </div>

  {{-- Stats par cluster --}}
  <div class="section-card animate-fade delay-4">
    <div class="section-card-header">
      <div class="section-card-title">
        <span class="section-dot blue"></span> Analyse par cluster
      </div>
      <span class="badge-cie badge-blue">A / B / C</span>
    </div>
    <div style="padding:20px 24px">
      @php
        $clusterInfo = [
          'A' => ['label'=>'Zones urbaines',  'color'=>'var(--orange)', 'bg'=>'rgba(255,102,0,.08)'],
          'B' => ['label'=>'Villes secondaires','color'=>'var(--blue)',  'bg'=>'rgba(59,130,246,.08)'],
          'C' => ['label'=>'Zones éloignées',  'color'=>'var(--red)',   'bg'=>'rgba(239,68,68,.08)'],
        ];
        $totalCluster = $clusterStats->sum('total') ?: 1;
      @endphp
      @forelse($clusterStats as $cl)
        @php
          $info = $clusterInfo[$cl->cluster] ?? ['label'=>$cl->cluster,'color'=>'var(--muted)','bg'=>'#f8fafc'];
          $cpct = round(($cl->total / $totalCluster) * 100);
        @endphp
        <div style="background:{{ $info['bg'] }};border-radius:12px;padding:16px 20px;margin-bottom:12px">
          <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px">
            <div>
              <span style="font-size:1rem;font-weight:900;color:{{ $info['color'] }}">Cluster {{ $cl->cluster }}</span>
              <span style="font-size:.75rem;color:var(--muted);margin-left:8px">{{ $info['label'] }}</span>
            </div>
            <div style="text-align:right">
              <div style="font-size:.9rem;font-weight:800;color:var(--dark)">{{ $cl->total }} alertes</div>
              <div style="font-size:.72rem;color:var(--muted)">prob. moy. {{ $cl->avg_prob }}%</div>
            </div>
          </div>
          <div style="height:6px;background:rgba(0,0,0,.08);border-radius:10px;overflow:hidden">
            <div style="height:100%;width:{{ $cpct }}%;background:{{ $info['color'] }};border-radius:10px;transition:width 1s ease"></div>
          </div>
        </div>
      @empty
        <div style="color:var(--muted);text-align:center;padding:30px">
          <i class="fas fa-brain" style="font-size:2rem;opacity:.3;display:block;margin-bottom:10px"></i>
          Lancez une prédiction pour voir l'analyse par cluster.
        </div>
      @endforelse
    </div>
  </div>
</div>

{{-- ===== CARTE DES ZONES À RISQUE ===== --}}
<div class="section-card animate-fade delay-3 mb-6">
  <div class="section-card-header" style="padding:16px 24px">
    <div>
      <div class="section-card-title">
        <span class="section-dot orange"></span>
        Carte des risques — Côte d'Ivoire
        <span style="font-size:.72rem;font-weight:600;color:var(--muted);margin-left:8px">
          · Prédictions slot {{ $nextSlotStart->format('H:i') }}→{{ $nextSlotEnd->format('H:i') }}
          @if(!$nextSlotStart->isToday())<span style="opacity:.7"> (demain)</span>@endif
        </span>
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      {{-- Légende --}}
      <div style="display:flex;gap:10px;font-size:.72rem;align-items:center">
        <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#ef4444;display:inline-block"></span>Urgent ≥80%</span>
        <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#f59e0b;display:inline-block"></span>60–79%</span>
        <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#3b82f6;display:inline-block"></span>&lt;60%</span>
        <span style="display:flex;align-items:center;gap:4px"><span style="width:10px;height:10px;border-radius:50%;background:#94a3b8;display:inline-block"></span>Stable</span>
      </div>
      @if($alertsNextSlot->count() > 0)
        <span class="badge-cie badge-orange">{{ $alertsNextSlot->count() }} zone(s) à risque</span>
      @else
        <span class="badge-cie badge-green"><i class="fas fa-check me-1"></i>Réseau stable</span>
      @endif
    </div>
  </div>

  {{-- Carte Leaflet --}}
  <div id="riskMap" style="height:420px;width:100%;z-index:0"></div>

  {{-- Données injectées proprement (pas dans attribut HTML pour éviter le conflit Blade) --}}
  @php
    $mapNext    = $alertsNextSlot->map(fn($a) => [
        'zone'     => $a->zone,
        'prob'     => $a->probability,
        'reason'   => $a->reason,
        'quartier' => $a->quartier,
    ])->values();
    $mapCurrent = $alerts3h->map(fn($a) => [
        'zone' => $a->zone,
        'prob' => $a->probability,
    ])->values();
  @endphp
  <script>
    window.MAP_NEXT    = @json($mapNext);
    window.MAP_CURRENT = @json($mapCurrent);
  </script>
</div>

{{-- ===== ALERTES 3H COURANTES ===== --}}
<div class="section-card animate-fade delay-3 mb-6">
  <div class="section-card-header" style="padding:16px 24px">
    <div>
      <div class="section-card-title" style="margin-bottom:2px">
        <span class="section-dot orange"></span>
        Slot en cours
        <span style="font-weight:900;color:var(--orange);margin-left:6px">
          {{ $current3hStart->format('H:i') }} → {{ $current3hEnd->format('H:i') }}
        </span>
        <span style="font-size:.72rem;font-weight:600;color:var(--muted);margin-left:10px">·</span>
        <span style="font-size:.8rem;font-weight:700;color:var(--blue);margin-left:8px">
          Prédit : {{ $nextSlotStart->format('H:i') }} → {{ $nextSlotEnd->format('H:i') }}
          @if(!$nextSlotStart->isToday())<span style="font-size:.7rem;opacity:.7"> (demain)</span>@endif
        </span>
      </div>
      <div style="font-size:.75rem;color:var(--muted);padding-left:18px">
        Prédiction automatique du slot suivant · {{ now()->format('d/m/Y H:i') }}
        @if($alertsNextSlot->count() > 0)
          · <strong style="color:var(--blue)">{{ $alertsNextSlot->count() }} zone(s) à risque prédit(es)</strong>
        @endif
      </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      @if($alerts3h->count() > 0)
        <span class="badge-cie badge-red">{{ $alerts3h->where('probability','>=',80)->count() }} urgentes</span>
        <span class="badge-cie badge-orange">{{ $alerts3h->count() }} ce slot</span>
      @else
        <span class="badge-cie badge-green"><i class="fas fa-check me-1"></i>Réseau stable</span>
      @endif
      <a href="{{ route('admin.prediction') }}" class="btn-cie" style="font-size:.78rem;padding:7px 14px">
        <i class="fas fa-list"></i> Détail complet
      </a>
    </div>
  </div>

  @if($alerts3h->count())
    <div class="outage-timeline" style="padding:0 0 8px">
      @foreach($alerts3h->take(6) as $a)
        @php
          $prob       = $a->probability ?? 0;
          $color      = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--green)');
          $clientsZone = \App\Models\User::where('zone', $a->zone)->where('role','client')->count();
          $initials   = strtoupper(mb_substr($a->zone, 0, 2));
          $cIcons     = ['surcharge_réseau'=>'fa-bolt','vieillissement_équipement'=>'fa-tools','intempérie_pluie'=>'fa-cloud-rain','défaut_câble_ht'=>'fa-plug','vandalisme_vol_câble'=>'fa-user-slash'];
          $cIcon      = $cIcons[$a->cause_probable ?? ''] ?? 'fa-exclamation-circle';
        @endphp
        <div class="alert-row-v2">
          <div class="zone-avatar" style="background:{{ $prob >= 80 ? 'rgba(239,68,68,.1)' : ($prob >= 60 ? 'rgba(245,158,11,.1)' : 'rgba(26,158,63,.1)') }};color:{{ $color }}">
            {{ $initials }}
          </div>
          <div class="zone-info">
            <div class="zone-name">
              {{ $a->zone }}
              @if($a->quartier)
                <span style="font-size:.72rem;font-weight:500;color:var(--muted);margin-left:6px">— {{ $a->quartier }}</span>
              @endif
              @if($a->cluster)
                <span style="font-size:.65rem;padding:1px 5px;border-radius:10px;background:rgba(0,0,0,.06);font-weight:700;margin-left:4px">Cl.{{ $a->cluster }}</span>
              @endif
            </div>
            <div class="zone-date">
              @if($a->cause_probable && $a->cause_probable !== 'aucune')
                <i class="fas {{ $cIcon }} me-1" style="color:{{ $color }}"></i>
              @endif
              {{ $a->reason ?? '—' }}
              &middot; <i class="fas fa-calendar me-1"></i>{{ $a->start_at->format('d/m H:i') }}
              @if($a->end_at) → {{ $a->end_at->format('H:i') }} @endif
            </div>
          </div>
          <div style="font-size:.75rem;color:var(--muted);white-space:nowrap">
            <i class="fas fa-users me-1"></i>{{ $clientsZone }} client(s)
          </div>
          <div class="prob-pill">
            <div class="prob-bar-mini">
              <div class="prob-bar-mini-fill" style="width:{{ $prob }}%;background:{{ $color }}"></div>
            </div>
            <span class="prob-pct" style="color:{{ $color }}">{{ $prob }}%</span>
          </div>
          <div style="display:flex;gap:6px">
            <button class="btn-cie-outline btn-alert-detail"
              style="font-size:.75rem;padding:6px 10px"
              data-id="{{ $a->id }}" data-zone="{{ $a->zone }}"
              data-start="{{ $a->start_at->format('d/m/Y H:i') }}"
              data-end="{{ optional($a->end_at)->format('d/m/Y H:i') ?? 'N/A' }}"
              data-reason="{{ $a->reason ?? '' }}" data-prob="{{ $prob }}">
              <i class="fas fa-eye"></i>
            </button>
            <button class="btn-cie-green btn-notify-agent"
              style="font-size:.75rem;padding:6px 10px"
              data-id="{{ $a->id }}" data-zone="{{ $a->zone }}"
              title="Notifier les agents de {{ $a->zone }}">
              <i class="fas fa-hard-hat"></i>
            </button>
          </div>
        </div>
      @endforeach
    </div>
  @else
    <div style="padding:40px 24px;display:flex;align-items:center;gap:20px">
      <div style="width:56px;height:56px;border-radius:14px;background:var(--green-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-check-circle" style="color:var(--green);font-size:1.5rem"></i>
      </div>
      <div>
        <div style="font-weight:800;font-size:1rem;color:var(--dark);margin-bottom:3px">
          Réseau stable — {{ $current3hStart->format('H:i') }} → {{ $current3hEnd->format('H:i') }}
        </div>
        <div style="font-size:.85rem;color:var(--muted)">
          Aucune coupure prédite pour les 3 prochaines heures.
          La prédiction se relance automatiquement à {{ now()->setTime($nextSlotHour, 0)->format('H:i') }}.
        </div>
      </div>
      <form method="POST" action="{{ route('admin.predict3h') }}" style="margin-left:auto;flex-shrink:0">
        @csrf
        <button type="submit" class="btn-cie" onclick="showLoader(event)" style="font-size:.82rem;padding:9px 16px">
          <i class="fas fa-sync-alt"></i> Actualiser maintenant
        </button>
      </form>
    </div>
  @endif

  {{-- ══ Comparaison Slot actuel / Slot suivant ════════════════════ --}}
  <div style="border-top:1px solid var(--border);display:grid;grid-template-columns:1fr 1fr">

    {{-- ── Slot actuel ── --}}
    <div style="padding:18px 24px;border-right:1px solid var(--border)">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
        <span style="display:inline-flex;align-items:center;gap:5px;font-size:.68rem;font-weight:800;background:rgba(255,102,0,.12);color:var(--orange);padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0">
          <span style="width:6px;height:6px;border-radius:50%;background:var(--orange);display:inline-block;animation:pulse 1s infinite"></span>
          En cours
        </span>
        <span style="font-size:.9rem;font-weight:800;color:var(--dark)">
          {{ $current3hStart->format('H:i') }} <span style="color:var(--muted)">→</span> {{ $current3hEnd->format('H:i') }}
        </span>
      </div>

      @if($alerts3h->count())
        @foreach($alerts3h->take(4) as $a)
          @php
            $prob = $a->probability ?? 0;
            $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--green)');
            $bg   = $prob >= 80 ? 'rgba(239,68,68,.1)' : ($prob >= 60 ? 'rgba(245,158,11,.1)' : 'rgba(26,158,63,.1)');
          @endphp
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $bg }};color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:900;flex-shrink:0">
              {{ strtoupper(mb_substr($a->zone, 0, 2)) }}
            </div>
            <div style="flex:1;font-size:.83rem;font-weight:600;color:var(--dark)">{{ $a->zone }}</div>
            <div style="width:44px;height:5px;background:#f1f5f9;border-radius:5px;overflow:hidden">
              <div style="height:100%;width:{{ $prob }}%;background:{{ $col }};border-radius:5px"></div>
            </div>
            <span style="font-size:.82rem;font-weight:800;color:{{ $col }};min-width:32px;text-align:right">{{ $prob }}%</span>
          </div>
        @endforeach
        @if($alerts3h->count() > 4)
          <div style="font-size:.75rem;color:var(--muted);margin-top:4px">
            <i class="fas fa-ellipsis-h me-1"></i>{{ $alerts3h->count() - 4 }} autre(s) zone(s)
          </div>
        @endif
      @else
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:1.1rem"></i>
          <span style="font-size:.83rem;color:var(--muted)">Réseau stable sur ce slot</span>
        </div>
      @endif
    </div>

    {{-- ── Slot suivant (prédiction) ── --}}
    <div style="padding:18px 24px;background:#f8fafc">
      <div style="display:flex;align-items:center;gap:10px;margin-bottom:14px">
        <span style="display:inline-flex;align-items:center;gap:5px;font-size:.68rem;font-weight:800;background:rgba(59,130,246,.1);color:var(--blue);padding:3px 10px;border-radius:20px;text-transform:uppercase;letter-spacing:.05em;flex-shrink:0">
          <i class="fas fa-clock" style="font-size:.6rem"></i>
          Slot suivant
        </span>
        <span style="font-size:.9rem;font-weight:800;color:var(--dark)">
          {{ $nextSlotStart->format('H:i') }} <span style="color:var(--muted)">→</span> {{ $nextSlotEnd->format('H:i') }}
        </span>
      </div>

      @if($alertsNextSlot->count())
        @foreach($alertsNextSlot->take(4) as $ns)
          @php
            $prob = $ns->probability ?? 0;
            $col  = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
            $bg   = $prob >= 80 ? 'rgba(239,68,68,.08)' : ($prob >= 60 ? 'rgba(245,158,11,.08)' : 'rgba(59,130,246,.08)');
          @endphp
          <div style="display:flex;align-items:center;gap:8px;margin-bottom:8px">
            <div style="width:30px;height:30px;border-radius:8px;background:{{ $bg }};color:{{ $col }};display:flex;align-items:center;justify-content:center;font-size:.68rem;font-weight:900;flex-shrink:0">
              {{ strtoupper(mb_substr($ns->zone, 0, 2)) }}
            </div>
            <div style="flex:1;font-size:.83rem;font-weight:600;color:var(--dark)">{{ $ns->zone }}</div>
            <div style="width:44px;height:5px;background:#e2e8f0;border-radius:5px;overflow:hidden">
              <div style="height:100%;width:{{ $prob }}%;background:{{ $col }};border-radius:5px"></div>
            </div>
            <span style="font-size:.82rem;font-weight:800;color:{{ $col }};min-width:32px;text-align:right">{{ $prob }}%</span>
          </div>
        @endforeach
        @if($alertsNextSlot->count() > 4)
          <div style="font-size:.75rem;color:var(--muted);margin-top:4px">
            <i class="fas fa-ellipsis-h me-1"></i>{{ $alertsNextSlot->count() - 4 }} autre(s) zone(s)
          </div>
        @endif
      @else
        <div style="display:flex;align-items:center;gap:10px;padding:10px 0">
          <i class="fas fa-shield-alt" style="color:var(--green);font-size:1.1rem"></i>
          <span style="font-size:.83rem;color:var(--muted)">Aucun risque prévu sur ce slot</span>
        </div>
      @endif
    </div>

  </div>{{-- /grid slots --}}
</div>

{{-- ===== BLOC PRÉDICTION MANUELLE (5J) ===== --}}
<div class="section-card animate-fade delay-4 mb-6" style="border-left:3px solid var(--blue)">
  <div style="padding:18px 24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px">
    <div style="display:flex;align-items:center;gap:14px">
      <div style="width:44px;height:44px;border-radius:12px;background:var(--blue-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-calendar-alt" style="color:var(--blue);font-size:1rem"></i>
      </div>
      <div>
        <div style="font-weight:800;font-size:.95rem;color:var(--dark)">
          Prédiction manuelle (journée / horizon)
        </div>
        <div style="font-size:.8rem;color:var(--muted);margin-top:2px">
          Déclenchez une analyse sur un dataset CSV pour voir les risques sur la journée ou les jours suivants.
          @if($dernierePred5j)
            Dernière exécution : <strong>{{ \Carbon\Carbon::parse($dernierePred5j)->format('d/m/Y à H:i') }}</strong>
          @else
            <span style="color:var(--orange)">Aucune prédiction manuelle effectuée aujourd'hui.</span>
          @endif
        </div>
      </div>
    </div>
    <div style="display:flex;gap:10px">
      @if(Auth::user()->role === 'admin')
        <button class="btn-ghost" onclick="document.getElementById('uploadModal').classList.add('open')" style="font-size:.82rem">
          <i class="fas fa-database"></i> Charger CSV
        </button>
      @endif
      <a href="{{ route('admin.prediction') }}" class="btn-cie" style="background:linear-gradient(135deg,var(--blue),#1255a0);font-size:.82rem;padding:9px 16px">
        <i class="fas fa-brain"></i> Résultats prédiction
      </a>
    </div>
  </div>
</div>

<div id="cause-data"
     data-labels="@json($causeStats->pluck('cause_probable')->map(fn($c) => Str::limit($c, 22))->values())"
     data-values="@json($causeStats->pluck('total')->values())"
     hidden></div>

{{-- Modal Notifier les agents --}}
<div class="modal fade" id="notifyAgentsModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 28px">
        <h5 class="modal-title text-white fw-bold">
          <i class="fas fa-hard-hat me-2" style="color:var(--orange)"></i>
          Notifier les agents — <span id="na-zone">—</span>
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:24px">
        <div id="na-loading" style="text-align:center;padding:20px;color:var(--muted)">
          <i class="fas fa-spinner fa-spin me-2"></i>Chargement des agents...
        </div>
        <div id="na-list" style="display:none;flex-direction:column;gap:10px"></div>
        <div id="na-done" style="display:none;text-align:center;padding:20px">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:2.5rem"></i>
          <div style="font-weight:700;margin-top:10px;color:var(--dark)">Agents notifiés avec succès</div>
          <div style="font-size:.82rem;color:var(--muted);margin-top:4px" id="na-done-time"></div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 24px;gap:10px">
        <button type="button" class="btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
        <button type="button" class="btn-cie-green" id="na-confirm-btn" onclick="confirmNotifyAgents()" style="display:none">
          <i class="fas fa-paper-plane"></i> Confirmer la notification
        </button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Détail --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 28px">
        <h5 class="modal-title text-white fw-bold"><i class="fas fa-bolt me-2" style="color:var(--orange)"></i>Détails de l'alerte</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:28px">
        <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:14px;padding:20px 24px;margin-bottom:20px">
          <div>
            <div style="font-size:.72rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Zone</div>
            <div id="m-zone" style="font-size:1.4rem;font-weight:900;color:var(--dark)">—</div>
          </div>
          <div style="text-align:right">
            <div style="font-size:.72rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Probabilité</div>
            <div id="m-prob" style="font-size:1.8rem;font-weight:900;color:var(--orange)">—</div>
          </div>
        </div>
        <div style="height:8px;background:#e2e8f0;border-radius:20px;overflow:hidden;margin-bottom:24px">
          <div id="m-prob-bar" style="height:100%;border-radius:20px;background:var(--orange);transition:width .8s ease;width:0%"></div>
        </div>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:16px">
          <div style="background:#f8fafc;border-radius:12px;padding:16px">
            <div style="font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;margin-bottom:6px"><i class="fas fa-play-circle me-1" style="color:var(--green)"></i>Début</div>
            <div id="m-start" style="font-weight:700;color:var(--dark)">—</div>
          </div>
          <div style="background:#f8fafc;border-radius:12px;padding:16px">
            <div style="font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;margin-bottom:6px"><i class="fas fa-stop-circle me-1" style="color:var(--red)"></i>Fin</div>
            <div id="m-end" style="font-weight:700;color:var(--dark)">—</div>
          </div>
        </div>
        <div style="background:#fff8f0;border:1.5px solid rgba(255,102,0,.2);border-radius:12px;padding:18px">
          <div style="font-size:.7rem;font-weight:800;color:var(--orange);text-transform:uppercase;margin-bottom:8px"><i class="fas fa-info-circle me-1"></i>Cause identifiée</div>
          <div id="m-reason" style="font-size:.95rem;font-weight:600;color:var(--dark)">—</div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);padding:16px 28px;background:#f8fafc;gap:10px">
        <button type="button" class="btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
        <button type="button" class="btn-cie-green" id="modalNotifyBtn"><i class="fas fa-paper-plane"></i> Notifier les agents</button>
      </div>
    </div>
  </div>
</div>

{{-- Modal Upload --}}
<div class="cie-modal-backdrop" id="uploadModal">
  <div class="cie-modal animate-fade">
    <div class="cie-modal-header">
      <h3><i class="fas fa-database me-2" style="color:var(--orange)"></i>Charger un dataset</h3>
      <button class="cie-modal-close" type="button" onclick="document.getElementById('uploadModal').classList.remove('open')"><i class="fas fa-times"></i></button>
    </div>
    <div class="cie-modal-body">
      <form method="POST" action="{{ route('admin.upload') }}" enctype="multipart/form-data" id="uploadForm">
        @csrf

        {{-- Sélecteur de fichier visible --}}
        <div style="margin-bottom:16px">
          <label style="display:block;font-size:.85rem;font-weight:700;color:var(--text);margin-bottom:10px">
            <i class="fas fa-file-csv me-1" style="color:var(--orange)"></i>
            Sélectionner le fichier CSV
          </label>
          <input type="file" name="dataset" id="datasetInput" accept=".csv,.txt"
                 style="display:block;width:100%;padding:10px 14px;border:2px dashed var(--border);border-radius:10px;background:#f8fafc;font-size:.875rem;cursor:pointer;color:var(--text)"
                 onchange="document.getElementById('uploadSubmitBtn').disabled=false; document.getElementById('uploadSubmitBtn').style.opacity='1';">
        </div>

        <div class="upload-format-hint">
          <i class="fas fa-info-circle"></i>
          Colonnes requises :
          <code style="font-size:.7rem;word-break:break-all">poste_id, zone, quartier, cluster, timestamp, charge_mw, capacite_mw, temperature_c, humidite_pct, pluviometrie_norm, niveau_barrage_pct, age_cable_ans, maintenance_recente, nb_incidents_7j, incidents_moy_3j</code>
        </div>

        <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:20px">
          <button type="button" class="btn-ghost" onclick="document.getElementById('uploadModal').classList.remove('open')">Annuler</button>
          <button type="submit" id="uploadSubmitBtn" class="btn-cie" style="opacity:.5" disabled>
            <i class="fas fa-upload"></i> Charger
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Loader prédiction --}}
<div class="predict-loader" id="predictLoader" style="display:none">
  <div class="predict-loader-inner">
    <div class="predict-spinner"></div>
    <div class="predict-loader-text">Analyse en cours...</div>
    <div class="predict-loader-sub">Le modèle IA analyse les données réelles de 296 postes sur la fenêtre 3H</div>
  </div>
</div>

{{-- Footer --}}
<div class="admin-footer">
  <div>© {{ date('Y') }} <strong>INNOVAN-CI</strong> — Compagnie Ivoirienne d'Électricité</div>
  <div>Syst&egrave;me de pr&eacute;diction intelligente des coupures v2.0</div>
</div>

@endsection

@section('scripts')
{{-- Leaflet.js (CSS déjà dans le head du layout) --}}
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

{{-- Script carte SÉPARÉ des graphiques (une erreur n'en bloque pas l'autre) --}}
<script>
/* ── Carte des risques INNOVAN-CI ────────────────────────────── */
(function () {
  try {
  var el = document.getElementById('riskMap');
  if (!el) return;
  if (typeof L === 'undefined') { el.innerHTML = '<div style="display:flex;align-items:center;justify-content:center;height:100%;color:#94a3b8;font-size:.85rem"><i class="fas fa-map me-2"></i>Carte indisponible hors ligne</div>'; return; }

  // Coordonnées des zones CI (lat, lng)
  var COORDS = {
    'Abobo':         [5.4106, -4.0086],
    'Yopougon':      [5.3490, -4.0702],
    'Cocody':        [5.3714, -3.9637],
    'Marcory':       [5.2985, -3.9818],
    'Koumassi':      [5.2914, -3.9558],
    'Port-Bouët':    [5.2506, -3.9296],
    'Adjamé':        [5.3588, -4.0153],
    'Treichville':   [5.2966, -4.0107],
    'Attécoubé':     [5.3402, -4.0517],
    'Plateau':       [5.3167, -4.0231],
    'Bingerville':   [5.3568, -3.8825],
    'Anyama':        [5.4833, -4.0500],
    'Songon':        [5.3367, -4.1833],
    'Bouaké':        [7.6931, -5.0310],
    'Yamoussoukro':  [6.8276, -5.2893],
    'San-Pédro':     [4.7485, -6.6363],
    'Daloa':         [6.8774, -6.4502],
    'Abengourou':    [6.7272, -3.4969],
    'Man':           [7.4126, -7.5536],
    'Korhogo':       [9.4581, -5.6296],
    'Gagnoa':        [6.1309, -5.9555],
    'Agboville':     [5.9282, -4.2153],
    'Divo':          [5.8373, -5.3567],
    'Adzopé':        [6.1070, -3.8640],
  };

  // Initialiser la carte centrée sur la CI
  var map = L.map('riskMap', {
    center:           [7.54, -5.55],
    zoom:             7,
    scrollWheelZoom:  false,
    zoomControl:      true,
    attributionControl: true,
  });

  // Fond de carte OpenStreetMap
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    maxZoom:     18,
    attribution: '© <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
  }).addTo(map);

  var nextData = window.MAP_NEXT    || [];
  var currData = window.MAP_CURRENT || [];

  // Index par zone
  var nextIndex = {};
  nextData.forEach(function (a) { nextIndex[a.zone] = a; });
  var currIndex = {};
  currData.forEach(function (a) { currIndex[a.zone] = a; });

  // Couleur et taille selon probabilité
  function circleOpts(prob, hasPred) {
    if (!hasPred) return { color:'#94a3b8', fillColor:'#94a3b8', radius:9,  weight:1, fillOpacity:.25 };
    if (prob >= 80) return { color:'#dc2626', fillColor:'#ef4444', radius:18, weight:2, fillOpacity:.55 };
    if (prob >= 60) return { color:'#d97706', fillColor:'#f59e0b', radius:14, weight:2, fillOpacity:.50 };
    return               { color:'#1d4ed8', fillColor:'#3b82f6', radius:11, weight:2, fillOpacity:.45 };
  }

  // Toutes les zones connues — afficher même celles sans prédiction
  var allZones = Object.keys(COORDS);

  allZones.forEach(function (zone) {
    var latlng = COORDS[zone];
    var next   = nextIndex[zone];
    var curr   = currIndex[zone];
    var hasPred = !!next;
    var prob    = next ? next.prob : (curr ? curr.prob : 0);

    var opts = Object.assign({ opacity:1 }, circleOpts(prob, hasPred));
    var circle = L.circleMarker(latlng, opts).addTo(map);

    // Popup
    var status = hasPred
      ? '<span style="color:' + (prob>=80?'#ef4444':prob>=60?'#f59e0b':'#3b82f6') + ';font-weight:800">'
          + prob + '% — ' + (prob>=80?'Urgent':prob>=60?'Probable':'Possible') + '</span>'
          + (next.reason ? '<br><small style="color:#64748b">' + next.reason + '</small>' : '')
          + (next.quartier ? '<br><small style="color:#94a3b8">' + next.quartier + '</small>' : '')
      : '<span style="color:#22c55e;font-weight:700">Stable</span>';

    circle.bindPopup(
      '<div style="font-family:Inter,sans-serif;min-width:160px">'
      + '<div style="font-weight:800;font-size:.95rem;margin-bottom:4px">' + zone + '</div>'
      + status
      + '</div>',
      { className: 'cie-map-popup' }
    );

    circle.bindTooltip(zone, {
      permanent:  prob >= 70,
      direction:  'top',
      className:  'cie-map-tooltip',
      offset:     [0, -8],
    });
  });

  // Recadrer sur les zones à risque si elles existent
  if (nextData.length > 0) {
    var riskLatLngs = nextData
      .filter(function(a) { return COORDS[a.zone]; })
      .map(function(a)    { return COORDS[a.zone]; });
    if (riskLatLngs.length > 0) {
      map.fitBounds(L.latLngBounds(riskLatLngs).pad(0.4));
    }
  }

  // Forcer le recalcul de taille (nécessaire dans les containers flex/grid)
  setTimeout(function () { map.invalidateSize(); }, 300);

  } catch(e) { console.warn('Map init error:', e); }
})();
</script>

{{-- Script graphiques SÉPARÉ — indépendant de Leaflet --}}
<script>
(function(){

  /* ── Count-up animation ── */
  document.querySelectorAll('.kpi-v2-value[data-count]').forEach(function(el){
    var target  = parseInt(el.dataset.count) || 0;
    var suffix  = el.dataset.suffix || '';
    var start   = null;
    var dur     = 1200;
    function step(ts){
      if(!start) start = ts;
      var p    = Math.min((ts - start) / dur, 1);
      var ease = 1 - Math.pow(1 - p, 3);
      el.textContent = Math.floor(ease * target).toLocaleString('fr-FR') + suffix;
      if(p < 1) requestAnimationFrame(step);
      else el.textContent = target.toLocaleString('fr-FR') + suffix;
    }
    requestAnimationFrame(step);
  });

  /* ── Graphe : 8 slots 3H d'aujourd'hui ── */
  var slots3h = @json($slots3hData);
  var wCtx = document.getElementById('weeklyChart');
  if(wCtx && slots3h){
    var wLabels = slots3h.map(function(s){ return s.label; });
    var wTotals = slots3h.map(function(s){ return s.count; });
    var wProbs  = slots3h.map(function(s){ return s.avg_prob; });
    var isCurr  = slots3h.map(function(s){ return s.is_current; });
    new Chart(wCtx, {
      type: 'bar',
      data: {
        labels: wLabels,
        datasets: [
          {
            label: 'Alertes 3H',
            data: wTotals,
            backgroundColor: wTotals.map(function(v, i){
              if (isCurr[i]) return 'rgba(26,158,63,.9)';
              return v === 0 ? 'rgba(148,163,184,.25)' : 'rgba(255,102,0,.75)';
            }),
            borderColor: wTotals.map(function(v, i){
              return isCurr[i] ? 'rgba(74,222,128,1)' : 'transparent';
            }),
            borderWidth: 2,
            borderRadius: 8, borderSkipped: false,
            yAxisID: 'y'
          },
          {
            label: 'Prob. moy. (%)',
            data: wProbs,
            type: 'line',
            borderColor: '#3b82f6',
            backgroundColor: 'rgba(59,130,246,.06)',
            borderWidth: 2, pointRadius: 5,
            pointBackgroundColor: wProbs.map(function(v,i){ return isCurr[i] ? '#4ade80' : '#3b82f6'; }),
            tension: 0.4, fill: true,
            yAxisID: 'y2'
          }
        ]
      },
      options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: { position: 'bottom', labels: { font: { size: 11 }, boxWidth: 12 } },
          tooltip: { backgroundColor: '#0f172a', titleColor: '#fff', bodyColor: 'rgba(255,255,255,.7)', padding: 10, cornerRadius: 8 }
        },
        scales: {
          x: { grid: { display: false }, ticks: { font: { size: 10 }, color: '#94a3b8' } },
          y: { beginAtZero: true, position: 'left', grid: { color: 'rgba(0,0,0,.04)' }, ticks: { font: { size: 10 }, color: '#94a3b8', stepSize: 1 }, title: { display: true, text: 'Alertes', font: { size: 10 } } },
          y2: { beginAtZero: true, max: 100, position: 'right', grid: { display: false }, ticks: { font: { size: 10 }, color: '#3b82f6', callback: function(v){ return v + '%'; } } }
        }
      }
    });
  }

  /* ── Graphe : causes (doughnut) ── */
  var cEl  = document.getElementById('cause-data');
  var cCtx = document.getElementById('causeChart');
  if(cEl && cCtx){
    var cLabels = JSON.parse(cEl.dataset.labels);
    var cValues = JSON.parse(cEl.dataset.values);
    if(cValues.length === 0){ cLabels = ['Aucune donnée']; cValues = [1]; }
    new Chart(cCtx, {
      type: 'doughnut',
      data: {
        labels: cLabels,
        datasets: [{ data: cValues, backgroundColor: ['#FF6600','#3b82f6','#ef4444','#8b5cf6','#f59e0b'], borderWidth: 2, borderColor: '#fff' }]
      },
      options: {
        responsive: false,
        cutout: '68%',
        plugins: { legend: { display: false }, tooltip: { backgroundColor: '#0f172a', bodyColor: '#fff', padding: 8, cornerRadius: 8 } }
      }
    });
  }

  /* ── Notifier les agents ── */
  var _notifyAlertId = null;
  document.querySelectorAll('.btn-notify-agent').forEach(function(btn){
    btn.addEventListener('click', function(){
      _notifyAlertId = this.dataset.id;
      var zone       = this.dataset.zone;
      document.getElementById('na-zone').textContent = zone;
      document.getElementById('na-loading').style.display = 'block';
      document.getElementById('na-list').style.display    = 'none';
      document.getElementById('na-done').style.display    = 'none';
      document.getElementById('na-confirm-btn').style.display = 'none';
      new bootstrap.Modal(document.getElementById('notifyAgentsModal')).show();
      // Charger les agents via l'API
      fetch('/admin/alerts/' + _notifyAlertId + '/notify-agents', {
        method: 'POST',
        headers: {'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]') ? document.querySelector('meta[name=csrf-token]').content : '{{ csrf_token() }}', 'Content-Type':'application/json', 'Accept':'application/json'}
      }).then(function(r){ return r.json(); }).then(function(d){
        document.getElementById('na-loading').style.display = 'none';
        var list = document.getElementById('na-list');
        list.innerHTML = '';
        if(!d.agents || d.agents.length === 0){
          list.innerHTML = '<div style="color:var(--muted);text-align:center;padding:16px">Aucun agent assigné à cette zone.</div>';
        } else {
          d.agents.forEach(function(a){
            list.innerHTML += '<div style="display:flex;align-items:center;gap:12px;background:#f8fafc;border-radius:12px;padding:14px 16px">'
              + '<div style="width:40px;height:40px;border-radius:10px;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--orange)">'
              + a.name.substring(0,2).toUpperCase() + '</div>'
              + '<div style="flex:1"><div style="font-weight:700;font-size:.9rem">' + a.name + '</div>'
              + '<div style="font-size:.75rem;color:var(--muted)">' + a.role + '</div></div>'
              + '<div style="text-align:right"><div style="font-size:.8rem;font-weight:600">' + a.phone + '</div>'
              + '<div style="font-size:.7rem;color:var(--muted)">' + a.email + '</div></div></div>';
          });
          document.getElementById('na-done-time').textContent = 'Notifiés le ' + d.notified_at;
        }
        list.style.display = 'flex';
        document.getElementById('na-done').style.display = 'block';
      }).catch(function(){
        document.getElementById('na-loading').innerHTML = '<div style="color:var(--red)">Erreur de connexion.</div>';
      });
    });
  });

  window.confirmNotifyAgents = function() {
    // ferme juste le modal — la notification est déjà envoyée via fetch au click
    var modal = bootstrap.Modal.getInstance(document.getElementById('notifyAgentsModal'));
    if (modal) modal.hide();
  };

  /* ── Loader prédiction ── */
  function showPredictLoader(e){
    var l = document.getElementById('predictLoader');
    if(l) l.style.display = 'flex';
  }
  window.showPredictLoader = showPredictLoader;
  window.showLoader = showPredictLoader; // alias pour les boutons 3H

})();
</script>
@endsection
