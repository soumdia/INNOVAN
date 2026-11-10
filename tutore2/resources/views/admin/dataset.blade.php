@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Données & Terrain')

@section('page-title')
{{ Auth::user()->role === 'admin' ? 'Données centralisées' : 'Postes & Terrain' }}
@endsection

@section('content')

@php $isAdmin = Auth::user()->role === 'admin'; @endphp

@if(session('success'))
  <div class="cie-flash cie-flash-success animate-fade mb-4">
    <i class="fas fa-check-circle"></i> {{ session('success') }}
  </div>
@endif
@if(session('error'))
  <div class="cie-flash cie-flash-error animate-fade mb-4" style="background:rgba(239,68,68,.1);border:1px solid rgba(239,68,68,.3);border-radius:12px;padding:12px 18px;color:var(--red)">
    <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
  </div>
@endif

{{-- ── EN-TÊTE ── --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px;margin-bottom:24px" class="animate-fade">
  <div>
    @if($isAdmin)
      <h1 class="page-title">Données centralisées — Dataset INNOVAN-CI</h1>
      <p class="page-subtitle">
        Toutes les sources sont agrégées ici avant la prédiction ·
        Dernière mise à jour : <strong>{{ $stats['derniere_maj'] ? \Carbon\Carbon::parse($stats['derniere_maj'])->format('d/m/Y H:i') : 'Jamais' }}</strong>
      </p>
    @else
      <h1 class="page-title">Postes &amp; Terrain — {{ Auth::user()->zone ?? 'Toutes zones' }}</h1>
      <p class="page-subtitle">
        Déclarez les maintenances et incidents sur vos postes ·
        Dernière mise à jour : <strong>{{ $stats['derniere_maj'] ? \Carbon\Carbon::parse($stats['derniere_maj'])->format('d/m/Y H:i') : 'Jamais' }}</strong>
      </p>
    @endif
  </div>
  {{-- Bouton Construire dataset : ADMIN uniquement --}}
  @if($isAdmin)
  <form method="POST" action="{{ route('dataset.build') }}" id="buildForm">
    @csrf
    <button type="submit" class="btn-ia-predict" onclick="document.getElementById('buildLoader').style.display='flex'">
      <span class="btn-ia-icon"><i class="fas fa-brain"></i></span>
      Construire dataset &amp; Lancer prédiction
    </button>
  </form>
  @else
  <div style="display:flex;align-items:center;gap:8px;background:rgba(167,139,250,.1);border:1px solid rgba(167,139,250,.3);border-radius:10px;padding:10px 16px">
    <i class="fas fa-hard-hat" style="color:#a78bfa;font-size:.9rem"></i>
    <span style="font-size:.82rem;color:#a78bfa;font-weight:700">Mode agent terrain — Saisie uniquement</span>
  </div>
  @endif
</div>

{{-- ── SCHÉMA DES SOURCES ── --}}
<div class="cie-card mb-6 animate-fade delay-1" style="background:var(--dark);color:#fff">
  <div style="padding:24px">
    <div style="font-size:.75rem;font-weight:800;color:rgba(255,255,255,.4);text-transform:uppercase;letter-spacing:.1em;margin-bottom:16px">
      Architecture des données
    </div>
    <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:12px">
      @php
        $sources = [
          ['icon'=>'fa-calculator',       'color'=>'#4ade80', 'label'=>'Auto-calculées',       'desc'=>'16 features dérivées (heure, saison, interactions…)',  'count'=>'16'],
          ['icon'=>'fa-cloud-sun',         'color'=>'#60a5fa', 'label'=>'Météo',                'desc'=>'4 features (temp, humidité, pluie, temp_max)',         'count'=>'4', 'badge'=>$stats['meteo_ok'].' zones API'],
          ['icon'=>'fa-database',          'color'=>'#f59e0b', 'label'=>'Postes (statique)',    'desc'=>'4 features (poste_id, zone, quartier, cluster)',        'count'=>'4'],
          ['icon'=>'fa-tachometer-alt',    'color'=>'#FF6600', 'label'=>'Réseau / Barrage',     'desc'=>'3 features (charge, capacité, niveau barrage)',         'count'=>'3'],
          ['icon'=>'fa-hard-hat',          'color'=>'#a78bfa', 'label'=>'Saisie agent',         'desc'=>'4 features (maintenance, incidents, âge câble)',        'count'=>'4'],
        ];
      @endphp
      @foreach($sources as $s)
        <div style="background:rgba(255,255,255,.06);border-radius:12px;padding:16px">
          <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px">
            <div style="width:32px;height:32px;border-radius:8px;background:{{ $s['color'] }}22;display:flex;align-items:center;justify-content:center">
              <i class="fas {{ $s['icon'] }}" style="color:{{ $s['color'] }};font-size:.85rem"></i>
            </div>
            <div>
              <div style="font-weight:700;font-size:.85rem">{{ $s['label'] }}</div>
              @if(isset($s['badge']))
                <span style="font-size:.65rem;background:{{ $s['color'] }}33;color:{{ $s['color'] }};padding:1px 6px;border-radius:20px">{{ $s['badge'] }}</span>
              @endif
            </div>
            <div style="margin-left:auto;font-size:1.1rem;font-weight:900;color:{{ $s['color'] }}">{{ $s['count'] }}</div>
          </div>
          <div style="font-size:.72rem;color:rgba(255,255,255,.4)">{{ $s['desc'] }}</div>
        </div>
      @endforeach
    </div>
    <div style="margin-top:16px;padding:12px 16px;background:rgba(255,102,0,.1);border:1px solid rgba(255,102,0,.3);border-radius:10px;font-size:.8rem;color:rgba(255,255,255,.7)">
      <i class="fas fa-info-circle me-2" style="color:var(--orange)"></i>
      <strong>31 features totales</strong> sont construites automatiquement depuis ces 5 sources et envoyées au modèle RandomForest INNOVAN-CI v8.
    </div>
  </div>
</div>

{{-- ── ACTIONS GLOBALES (admin) / INFOS CONTEXTE (agent) ── --}}
<div class="grid-2 mb-6 animate-fade delay-2">

  {{-- Météo --}}
  <div class="cie-card">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot blue"></span> Météo</div>
      <span class="badge-cie {{ $stats['meteo_ok'] > 0 ? 'badge-green' : 'badge-yellow' }}">
        {{ $stats['meteo_ok'] > 0 ? $stats['meteo_ok'].' zones API' : 'Estimée' }}
      </span>
    </div>
    <div style="padding:20px">
      <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px">
        <i class="fas fa-cloud-sun me-1" style="color:var(--blue)"></i>
        Température, humidité, pluviométrie pour chaque zone.
      </p>
      @if($isAdmin)
        @if(!env('OPENWEATHER_API_KEY'))
          <div style="font-size:.78rem;color:var(--yellow);margin-bottom:12px">
            <i class="fas fa-exclamation-triangle me-1"></i>Clé OpenWeatherMap non configurée — estimation saisonnière.
          </div>
        @endif
        <form method="POST" action="{{ route('dataset.weather') }}">
          @csrf
          <button type="submit" class="btn-cie" style="width:100%;justify-content:center">
            <i class="fas fa-sync-alt"></i>
            {{ env('OPENWEATHER_API_KEY') ? 'Rafraîchir depuis OpenWeatherMap' : 'Mettre à jour (estimation)' }}
          </button>
        </form>
      @else
        <div style="font-size:.82rem;color:var(--muted);background:#f8fafc;border-radius:10px;padding:12px">
          <i class="fas fa-info-circle me-1" style="color:var(--blue)"></i>
          Mise à jour automatique par l'administrateur. Aucune action requise de votre part.
        </div>
      @endif
    </div>
  </div>

  {{-- Niveau barrage --}}
  <div class="cie-card">
    <div class="cie-card-header">
      <div class="cie-card-title"><span class="card-dot orange"></span> Niveau du barrage</div>
      <span class="badge-cie badge-blue">Moy. réseau : {{ round($postes->avg('niveau_barrage_pct'), 1) }}%</span>
    </div>
    <div style="padding:20px">
      <p style="font-size:.85rem;color:var(--muted);margin-bottom:16px">
        <i class="fas fa-water me-1" style="color:var(--blue)"></i>
        Valeur actuelle : <strong>{{ round($postes->avg('niveau_barrage_pct'), 1) }}%</strong> (moyenne réseau)
      </p>
      @if($isAdmin)
        <form method="POST" action="{{ route('dataset.barrage') }}" style="display:flex;gap:10px">
          @csrf
          <div class="finput-wrap" style="flex:1">
            <i class="finput-icon fas fa-percentage"></i>
            <input type="number" name="niveau_barrage_pct" class="finput" min="0" max="100" step="0.1"
                   value="{{ round($postes->avg('niveau_barrage_pct'), 1) }}" placeholder="Ex: 62.5">
          </div>
          <button type="submit" class="btn-cie"><i class="fas fa-save"></i> Appliquer</button>
        </form>
      @else
        <div style="font-size:.82rem;color:var(--muted);background:#f8fafc;border-radius:10px;padding:12px">
          <i class="fas fa-info-circle me-1" style="color:var(--blue)"></i>
          Niveau mis à jour par l'administrateur. Consultez votre superviseur pour toute anomalie.
        </div>
      @endif
    </div>
  </div>
</div>

{{-- ── KPIs ── --}}
<div class="kpi-row mb-6 animate-fade delay-2">
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Postes dans le dataset</div>
      <div class="kpi-value">{{ $stats['total_postes'] }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map-pin"></i> {{ $zones->count() }} zones</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-map-pin"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Météo depuis API</div>
      <div class="kpi-value">{{ $stats['meteo_ok'] }}</div>
      <div class="kpi-trend neu"><i class="fas fa-cloud-sun"></i> zones mises à jour</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-cloud-sun"></i></div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Postes avec incidents</div>
      <div class="kpi-value">{{ $stats['avec_incidents'] }}</div>
      <div class="kpi-trend down"><i class="fas fa-exclamation-triangle"></i> 7 derniers jours</div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-bolt"></i></div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Maintenances récentes</div>
      <div class="kpi-value">{{ $stats['maintenance_faite'] }}</div>
      <div class="kpi-trend up"><i class="fas fa-tools"></i> Moins de 7 jours</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-tools"></i></div>
  </div>
</div>

{{-- ── FILTRES ET TABLE DES POSTES ── --}}
<div class="cie-card animate-fade delay-3">
  <div class="cie-card-header">
    <div class="cie-card-title">
      <span class="card-dot orange"></span>
      @if($isAdmin) État actuel par poste @else Mes postes — {{ Auth::user()->zone ?? 'Zone assignée' }} @endif
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
      <select id="filterZone" class="form-select form-select-sm" style="width:160px;border-radius:8px;font-size:.82rem">
        <option value="">Toutes les zones</option>
        @foreach($zones as $z)
          <option value="{{ strtolower($z) }}">{{ $z }}</option>
        @endforeach
      </select>
      <select id="filterCluster" class="form-select form-select-sm" style="width:130px;border-radius:8px;font-size:.82rem">
        <option value="">Tous clusters</option>
        <option value="a">Cluster A</option>
        <option value="b">Cluster B</option>
        <option value="c">Cluster C</option>
      </select>
      <input type="text" id="searchPoste" placeholder="Rechercher..." class="form-control form-control-sm" style="width:160px;border-radius:8px;font-size:.82rem">
    </div>
  </div>

  <div class="table-wrap" style="max-height:520px;overflow-y:auto">
    <table class="cie-table" id="postesTable" style="font-size:.82rem">
      <thead style="position:sticky;top:0;background:#fff;z-index:1">
        <tr>
          <th>Poste</th>
          <th>Zone / Quartier</th>
          <th>Cl.</th>
          <th>Charge MW</th>
          <th>Barrage %</th>
          <th>Temp °C</th>
          <th>Incidents 7j</th>
          <th>Maint.</th>
          <th>Source météo</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        @foreach($postes as $p)
          @php
            $taux    = $p->capacite_mw > 0 ? round($p->charge_mw / $p->capacite_mw * 100) : 0;
            $tauCol  = $taux >= 85 ? 'var(--red)' : ($taux >= 70 ? 'var(--yellow)' : 'var(--green)');
            $bCol    = $p->niveau_barrage_pct < 30 ? 'var(--red)' : ($p->niveau_barrage_pct < 50 ? 'var(--yellow)' : 'var(--green)');
            $clColor = ['A'=>'var(--orange)','B'=>'var(--blue)','C'=>'var(--red)'][$p->cluster ?? 'A'] ?? 'var(--muted)';
          @endphp
          <tr data-zone="{{ strtolower($p->zone) }}" data-cluster="{{ strtolower($p->cluster) }}"
              data-search="{{ strtolower($p->poste_id.' '.$p->zone.' '.$p->quartier) }}">
            <td style="font-weight:700;font-family:monospace;font-size:.78rem;white-space:nowrap">{{ $p->poste_id }}</td>
            <td>
              <div style="font-weight:600">{{ $p->zone }}</div>
              <div style="font-size:.72rem;color:var(--muted)">{{ $p->quartier }}</div>
            </td>
            <td><span style="font-weight:900;color:{{ $clColor }}">{{ $p->cluster }}</span></td>
            <td>
              <div style="display:flex;align-items:center;gap:6px">
                <div style="width:40px;height:5px;background:#f1f5f9;border-radius:5px;overflow:hidden">
                  <div style="height:100%;width:{{ min(100,$taux) }}%;background:{{ $tauCol }};border-radius:5px"></div>
                </div>
                <span style="color:{{ $tauCol }};font-weight:700">{{ $p->charge_mw }}</span>
              </div>
              <div style="font-size:.68rem;color:var(--muted)">/ {{ $p->capacite_mw }} MW · {{ $taux }}%</div>
            </td>
            <td>
              <span style="font-weight:700;color:{{ $bCol }}">{{ $p->niveau_barrage_pct }}%</span>
            </td>
            <td style="font-weight:600">{{ $p->temperature_c }}°</td>
            <td>
              @if($p->nb_incidents_7j > 0)
                <span class="badge-cie {{ $p->nb_incidents_7j >= 5 ? 'badge-red' : 'badge-yellow' }}">{{ $p->nb_incidents_7j }}</span>
              @else
                <span style="color:var(--green);font-size:.8rem"><i class="fas fa-check"></i></span>
              @endif
            </td>
            <td>
              @if($p->maintenance_recente)
                <span class="badge-cie badge-green" style="font-size:.68rem"><i class="fas fa-tools me-1"></i>OK</span>
              @else
                <span style="color:var(--muted);font-size:.78rem">—</span>
              @endif
            </td>
            <td>
              @php $mColors = ['api'=>'badge-green','manuel'=>'badge-blue','estime'=>'badge-yellow']; @endphp
              <span class="badge-cie {{ $mColors[$p->source_meteo] ?? 'badge-yellow' }}" style="font-size:.68rem">{{ $p->source_meteo }}</span>
            </td>
            <td>
              <div style="display:flex;gap:4px">
                {{-- Modifier charge/barrage --}}
                <button class="btn-cie-outline" style="font-size:.68rem;padding:4px 8px"
                        onclick="openEditModal('{{ $p->poste_id }}','{{ $p->zone }}','{{ $p->charge_mw }}','{{ $p->niveau_barrage_pct }}','{{ $p->age_cable_ans }}')"
                        title="Modifier charge / barrage">
                  <i class="fas fa-edit"></i>
                </button>
                {{-- Déclarer incident --}}
                <button class="btn-ghost" style="font-size:.68rem;padding:4px 8px"
                        onclick="openIncidentModal('{{ $p->poste_id }}','{{ $p->zone }}')"
                        title="Déclarer un incident">
                  <i class="fas fa-exclamation-triangle" style="color:var(--red)"></i>
                </button>
                {{-- Déclarer maintenance --}}
                <button class="btn-ghost" style="font-size:.68rem;padding:4px 8px"
                        onclick="openMaintenanceModal('{{ $p->poste_id }}','{{ $p->zone }}')"
                        title="Déclarer maintenance">
                  <i class="fas fa-tools" style="color:var(--green)"></i>
                </button>
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>

{{-- ── MODAL EDIT POSTE ── --}}
<div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 24px">
        <h5 class="modal-title text-white fw-bold"><i class="fas fa-edit me-2" style="color:var(--orange)"></i>Modifier le poste <span id="editPosteId"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" id="editForm" action="">
        @csrf @method('PUT')
        <div class="modal-body" style="padding:24px;display:flex;flex-direction:column;gap:16px">
          <div>
            <label style="font-size:.82rem;font-weight:700;color:var(--text);margin-bottom:6px;display:block">Charge actuelle (MW)</label>
            <div class="finput-wrap"><i class="finput-icon fas fa-bolt"></i>
              <input type="number" name="charge_mw" id="editCharge" class="finput" step="0.1" min="0" max="500" placeholder="Ex: 17.5">
            </div>
          </div>
          <div>
            <label style="font-size:.82rem;font-weight:700;color:var(--text);margin-bottom:6px;display:block">Niveau barrage (%)</label>
            <div class="finput-wrap"><i class="finput-icon fas fa-water"></i>
              <input type="number" name="niveau_barrage_pct" id="editBarrage" class="finput" step="0.1" min="0" max="100" placeholder="Ex: 62.5">
            </div>
          </div>
          <div>
            <label style="font-size:.82rem;font-weight:700;color:var(--text);margin-bottom:6px;display:block">Âge du câble (années)</label>
            <div class="finput-wrap"><i class="finput-icon fas fa-plug"></i>
              <input type="number" name="age_cable_ans" id="editAge" class="finput" step="0.5" min="0" max="80" placeholder="Ex: 13.5">
            </div>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 24px;gap:10px">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-cie"><i class="fas fa-save"></i> Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ── MODAL INCIDENT ── --}}
<div class="modal fade" id="incidentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 24px">
        <h5 class="modal-title text-white fw-bold"><i class="fas fa-exclamation-triangle me-2" style="color:var(--red)"></i>Déclarer un incident — <span id="incPosteId"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('dataset.incident') }}">
        @csrf
        <input type="hidden" name="poste_id" id="incPosteIdInput">
        <div class="modal-body" style="padding:24px;display:flex;flex-direction:column;gap:14px">
          <div>
            <label style="font-size:.82rem;font-weight:700;margin-bottom:6px;display:block">Type d'incident</label>
            <select name="type" class="form-select" style="border-radius:8px;font-size:.85rem">
              <option value="technique">Technique</option>
              <option value="vandalisme">Vandalisme / Vol de câble</option>
              <option value="meteo">Météo / Intempérie</option>
              <option value="autre">Autre</option>
            </select>
          </div>
          <div>
            <label style="font-size:.82rem;font-weight:700;margin-bottom:6px;display:block">Sévérité</label>
            <select name="severity" class="form-select" style="border-radius:8px;font-size:.85rem">
              <option value="mineur">Mineur</option>
              <option value="normal" selected>Normal</option>
              <option value="critique">Critique</option>
            </select>
          </div>
          <div>
            <label style="font-size:.82rem;font-weight:700;margin-bottom:6px;display:block">Description (optionnel)</label>
            <textarea name="description" class="form-control" rows="2" style="border-radius:8px;font-size:.85rem;resize:none" placeholder="Détails de l'incident…"></textarea>
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 24px;gap:10px">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-cie" style="background:var(--red);box-shadow:0 3px 10px rgba(239,68,68,.4)"><i class="fas fa-exclamation-triangle"></i> Déclarer</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- ── MODAL MAINTENANCE ── --}}
<div class="modal fade" id="maintenanceModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none">
      <div class="modal-header" style="background:var(--dark);border:none;padding:20px 24px">
        <h5 class="modal-title text-white fw-bold"><i class="fas fa-tools me-2" style="color:var(--green)"></i>Maintenance effectuée — <span id="maintPosteId"></span></h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <form method="POST" action="{{ route('dataset.maintenance') }}">
        @csrf
        <input type="hidden" name="poste_id" id="maintPosteIdInput">
        <div class="modal-body" style="padding:24px;display:flex;flex-direction:column;gap:14px">
          <div>
            <label style="font-size:.82rem;font-weight:700;margin-bottom:6px;display:block">Type de maintenance</label>
            <select name="type" class="form-select" style="border-radius:8px;font-size:.85rem">
              <option value="preventive">Préventive</option>
              <option value="corrective">Corrective</option>
              <option value="urgence">Urgence</option>
            </select>
          </div>
          <div>
            <label style="font-size:.82rem;font-weight:700;margin-bottom:6px;display:block">Notes (optionnel)</label>
            <textarea name="notes" class="form-control" rows="2" style="border-radius:8px;font-size:.85rem;resize:none" placeholder="Travaux effectués…"></textarea>
          </div>
          <div style="background:var(--green-light);border-radius:10px;padding:12px;font-size:.8rem;color:var(--green)">
            <i class="fas fa-info-circle me-1"></i>
            Le poste sera marqué "maintenance récente = 1" pendant <strong>7 jours</strong>, ce qui réduit le risque prédit par le modèle.
          </div>
        </div>
        <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 24px;gap:10px">
          <button type="button" class="btn-ghost" data-bs-dismiss="modal">Annuler</button>
          <button type="submit" class="btn-cie-green"><i class="fas fa-check"></i> Enregistrer</button>
        </div>
      </form>
    </div>
  </div>
</div>

{{-- Loader prédiction — admin uniquement --}}
@if($isAdmin)
<div class="predict-loader" id="buildLoader" style="display:none">
  <div class="predict-loader-inner">
    <div class="predict-spinner"></div>
    <div class="predict-loader-text">Construction du dataset...</div>
    <div class="predict-loader-sub">Agrégation des {{ $stats['total_postes'] }} postes · Envoi au modèle INNOVAN-CI</div>
  </div>
</div>
@endif

@endsection

@section('scripts')
<script>
function openEditModal(posteId, zone, charge, barrage, age) {
  document.getElementById('editPosteId').textContent = posteId;
  document.getElementById('editForm').action = '/admin/dataset/poste/' + posteId;
  document.getElementById('editCharge').value  = charge;
  document.getElementById('editBarrage').value = barrage;
  document.getElementById('editAge').value     = age;
  new bootstrap.Modal(document.getElementById('editModal')).show();
}

function openIncidentModal(posteId, zone) {
  document.getElementById('incPosteId').textContent   = posteId;
  document.getElementById('incPosteIdInput').value    = posteId;
  new bootstrap.Modal(document.getElementById('incidentModal')).show();
}

function openMaintenanceModal(posteId, zone) {
  document.getElementById('maintPosteId').textContent  = posteId;
  document.getElementById('maintPosteIdInput').value   = posteId;
  new bootstrap.Modal(document.getElementById('maintenanceModal')).show();
}

// Filtres tableau
var filterZone    = document.getElementById('filterZone');
var filterCluster = document.getElementById('filterCluster');
var searchPoste   = document.getElementById('searchPoste');

function applyFilters() {
  var zone    = filterZone.value;
  var cluster = filterCluster.value;
  var search  = searchPoste.value.toLowerCase();
  document.querySelectorAll('#postesTable tbody tr').forEach(function(r) {
    var ok = (!zone    || r.dataset.zone    === zone)
          && (!cluster || r.dataset.cluster === cluster)
          && (!search  || r.dataset.search.includes(search));
    r.style.display = ok ? '' : 'none';
  });
}
if(filterZone)    filterZone.addEventListener('change', applyFilters);
if(filterCluster) filterCluster.addEventListener('change', applyFilters);
if(searchPoste)   searchPoste.addEventListener('input', applyFilters);
</script>
@endsection
