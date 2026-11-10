@extends('layouts.admin')

@section('title', 'INNOVAN-CI — Alertes prédites')
@section('page-title', 'Alertes prédites')

@section('content')

@php
  $total3h  = $alerts3h->count();
  $total5j  = $alerts5j->count();
  $urgentes = $alerts3h->where('probability', '>=', 80)->count()
            + $alerts5j->where('probability', '>=', 80)->count();
  $zones    = $alerts3h->pluck('zone')->merge($alerts5j->pluck('zone'))->unique()->count();
@endphp

{{-- ═══ EN-TÊTE ═══ --}}
<div class="page-heading animate-fade" style="display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Alertes prédites</h1>
    <p class="page-subtitle">
      <span style="display:inline-block;width:7px;height:7px;border-radius:50%;background:var(--orange);margin-right:6px;vertical-align:middle"></span>
      Surveillance INNOVAN-CI · {{ now()->format('d/m/Y H:i') }}
      · <strong style="color:var(--orange)">{{ $total3h + $total5j }}</strong> alerte(s) active(s)
    </p>
  </div>
  <div style="display:flex;gap:10px;flex-wrap:wrap;align-items:center">
    <button class="btn-ghost" onclick="location.reload()">
      <i class="fas fa-sync-alt"></i> Actualiser
    </button>
    <a href="{{ route('admin.prediction') }}" class="btn-cie" style="font-size:.85rem">
      <i class="fas fa-brain"></i> Lancer prédiction IA
    </a>
  </div>
</div>

{{-- ═══ KPIs ═══ --}}
<div class="kpi-row mb-6 animate-fade delay-1">

  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Alertes automatiques 3H</div>
      <div class="kpi-value">{{ $total3h }}</div>
      <div class="kpi-trend {{ $total3h > 0 ? 'down' : 'up' }}">
        <i class="fas fa-clock"></i> En cours ou à venir
      </div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-clock"></i></div>
  </div>

  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Prédictions manuelles 5J</div>
      <div class="kpi-value">{{ $total5j }}</div>
      <div class="kpi-trend neu"><i class="fas fa-calendar-alt"></i> Horizon J+1 à J+5</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-calendar-alt"></i></div>
  </div>

  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Urgentes (&ge;80%)</div>
      <div class="kpi-value">{{ $urgentes }}</div>
      <div class="kpi-trend {{ $urgentes > 0 ? 'down' : 'up' }}">
        <i class="fas fa-exclamation-triangle"></i> Priorité haute
      </div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-exclamation-triangle"></i></div>
  </div>

  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Zones concernées</div>
      <div class="kpi-value">{{ $zones }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map"></i> Communes</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-map-marker-alt"></i></div>
  </div>

</div>

{{-- ═══ SECTION 1 : SURVEILLANCE 3H AUTOMATIQUE ═══ --}}
<div class="cie-card animate-fade delay-2 mb-6">
  <div class="cie-card-header">
    <div class="cie-card-title">
      <span class="card-dot orange" style="animation:pulse 1.2s infinite"></span>
      Surveillance automatique — Prédiction 3H
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge-cie badge-blue" style="font-size:.72rem">
        <i class="fas fa-robot me-1"></i>IA automatique · toutes les 3h
      </span>
      <span class="badge-cie {{ $total3h > 0 ? 'badge-orange' : 'badge-green' }}">
        {{ $total3h }} alerte(s)
      </span>
    </div>
  </div>

  @if($total3h)
    <div style="padding:10px 24px 4px;font-size:.78rem;color:var(--muted);background:#fffbf5;border-bottom:1px solid var(--border)">
      <i class="fas fa-info-circle" style="color:var(--orange)"></i>
      Ces alertes sont générées <strong>automatiquement</strong> par le modèle INNOVAN-CI sur la fenêtre 3H courante et les prochains slots de la journée.
    </div>
    <div class="table-wrap">
      <table class="cie-table" id="table3h">
        <thead>
          <tr>
            <th>Zone / Quartier</th>
            <th>Cause IA</th>
            <th>Début</th>
            <th>Fin</th>
            <th>Probabilité</th>
            <th>Statut</th>
            <th>Clients</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($alerts3h as $alert)
            @php
              $prob   = $alert->probability ?? 0;
              $col    = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--green)');
              $badge  = $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-green');
              $nbC    = \App\Models\User::where('zone',$alert->zone)->where('role','client')->count();
              $isNow  = $alert->start_at->lte(now()) && (!$alert->end_at || $alert->end_at->gte(now()));
              $cIcons = ['surcharge_réseau'=>['fa-bolt','Surcharge réseau'],'vieillissement_équipement'=>['fa-tools','Vieillissement équip.'],'intempérie_pluie'=>['fa-cloud-rain','Intempérie / Pluies'],'défaut_câble_ht'=>['fa-plug','Défaut câble HT'],'vandalisme_vol_câble'=>['fa-user-slash','Vandalisme']];
              [$cIcon,$cLabel] = $cIcons[$alert->cause_probable ?? ''] ?? ['fa-exclamation-circle', $alert->reason ?? '—'];
            @endphp
            <tr>
              <td>
                <div style="font-weight:700;color:var(--dark)">{{ $alert->zone }}</div>
                @if($alert->quartier)
                  <div style="font-size:.75rem;color:var(--muted)"><i class="fas fa-map-pin me-1"></i>{{ $alert->quartier }}</div>
                @endif
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:6px">
                  <i class="fas {{ $cIcon }}" style="color:{{ $col }};font-size:.82rem"></i>
                  <span style="font-size:.82rem;font-weight:600">{{ $alert->reason ?? $cLabel }}</span>
                </div>
              </td>
              <td>
                <div style="font-weight:700">{{ $alert->start_at->format('d/m') }}</div>
                <div style="font-size:.78rem;color:var(--muted)">{{ $alert->start_at->format('H:i') }}</div>
              </td>
              <td>
                @if($alert->end_at)
                  <div style="font-weight:600">{{ $alert->end_at->format('d/m') }}</div>
                  <div style="font-size:.78rem;color:var(--muted)">{{ $alert->end_at->format('H:i') }}</div>
                @else
                  <span style="color:var(--muted)">—</span>
                @endif
              </td>
              <td>
                <span class="badge-cie {{ $badge }}">{{ $prob }}%</span>
                <div style="margin-top:5px;height:4px;background:#f1f5f9;border-radius:4px;width:70px;overflow:hidden">
                  <div style="height:100%;border-radius:4px;width:{{ $prob }}%;background:{{ $col }}"></div>
                </div>
              </td>
              <td>
                @if($isNow)
                  <span class="badge-cie badge-red" style="font-size:.68rem">
                    <span style="display:inline-block;width:6px;height:6px;border-radius:50%;background:#fff;margin-right:4px;vertical-align:middle;animation:pulse 1s infinite"></span>En cours
                  </span>
                @else
                  <span class="badge-cie badge-yellow" style="font-size:.68rem"><i class="fas fa-clock me-1"></i>Prévu</span>
                @endif
              </td>
              <td>
                <span style="font-weight:700;color:var(--orange)">{{ $nbC }}</span>
                <span style="font-size:.72rem;color:var(--muted)"> client(s)</span>
              </td>
              <td>
                <div style="display:flex;gap:5px">
                  <button class="btn-cie-outline btn-alert-detail"
                    style="font-size:.72rem;padding:5px 9px"
                    data-id="{{ $alert->id }}" data-zone="{{ $alert->zone }}"
                    data-start="{{ $alert->start_at->format('d/m/Y H:i') }}"
                    data-end="{{ optional($alert->end_at)->format('d/m/Y H:i') ?? 'N/A' }}"
                    data-reason="{{ $alert->reason ?? '' }}" data-prob="{{ $prob }}"
                    title="Voir les détails">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="btn-cie-green btn-notify-agent"
                    style="font-size:.72rem;padding:5px 9px"
                    data-id="{{ $alert->id }}" data-zone="{{ $alert->zone }}"
                    title="Notifier les agents de {{ $alert->zone }}">
                    <i class="fas fa-hard-hat"></i>
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div style="padding:48px 24px;display:flex;align-items:center;gap:20px">
      <div style="width:56px;height:56px;border-radius:14px;background:var(--green-light);display:flex;align-items:center;justify-content:center;flex-shrink:0">
        <i class="fas fa-check-circle" style="color:var(--green);font-size:1.5rem"></i>
      </div>
      <div>
        <div style="font-weight:800;font-size:1rem;color:var(--dark);margin-bottom:4px">Réseau stable — aucune alerte 3H active</div>
        <div style="font-size:.85rem;color:var(--muted)">
          La prédiction automatique ne détecte aucun risque pour la fenêtre courante.
          <a href="{{ route('admin.prediction') }}" style="color:var(--orange);font-weight:700;margin-left:6px">Relancer la prédiction →</a>
        </div>
      </div>
    </div>
  @endif
</div>

{{-- ═══ SECTION 2 : PRÉDICTIONS MANUELLES 5J ═══ --}}
<div class="cie-card animate-fade delay-3 mb-6" style="border-left:3px solid var(--blue)">
  <div class="cie-card-header" style="background:#f0f7ff">
    <div class="cie-card-title">
      <span class="card-dot blue"></span>
      Prédictions manuelles — Horizon J+1 à J+5
    </div>
    <div style="display:flex;gap:8px;align-items:center">
      <span class="badge-cie badge-blue" style="font-size:.72rem">
        <i class="fas fa-flask me-1"></i>Prédiction manuelle · non confirmée
      </span>
      <span class="badge-cie badge-blue">{{ $total5j }} alerte(s)</span>
    </div>
  </div>

  <div style="padding:10px 24px 4px;font-size:.78rem;color:var(--muted);background:#f8fbff;border-bottom:1px solid var(--border)">
    <i class="fas fa-info-circle" style="color:var(--blue)"></i>
    Ces alertes ont été générées par une <strong>prédiction manuelle</strong> (dataset CSV ou modèle horizon).
    Elles sont indicatives — aucune validation terrain n'a été effectuée.
    <a href="{{ route('admin.prediction') }}" style="color:var(--blue);font-weight:700;margin-left:4px">Voir les résultats IA →</a>
  </div>

  @if($total5j)
    <div class="table-wrap">
      <table class="cie-table" id="table5j">
        <thead>
          <tr>
            <th>Zone / Quartier</th>
            <th>Cause estimée</th>
            <th>Date prévue</th>
            <th>Créneau</th>
            <th>Probabilité</th>
            <th>Clients</th>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          @foreach($alerts5j as $alert)
            @php
              $prob   = $alert->probability ?? 0;
              $col    = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
              $badge  = $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-blue');
              $nbC    = \App\Models\User::where('zone',$alert->zone)->where('role','client')->count();
              $cIcons = ['surcharge_réseau'=>'fa-bolt','vieillissement_équipement'=>'fa-tools','intempérie_pluie'=>'fa-cloud-rain','défaut_câble_ht'=>'fa-plug','vandalisme_vol_câble'=>'fa-user-slash'];
              $cIcon  = $cIcons[$alert->cause_probable ?? ''] ?? 'fa-exclamation-circle';
            @endphp
            <tr>
              <td>
                <div style="font-weight:700;color:var(--dark)">{{ $alert->zone }}</div>
                @if($alert->quartier)
                  <div style="font-size:.75rem;color:var(--muted)"><i class="fas fa-map-pin me-1"></i>{{ $alert->quartier }}</div>
                @endif
              </td>
              <td>
                <div style="display:flex;align-items:center;gap:6px">
                  <i class="fas {{ $cIcon }}" style="color:{{ $col }};font-size:.82rem"></i>
                  <span style="font-size:.82rem">{{ $alert->reason ?? '—' }}</span>
                </div>
              </td>
              <td>
                <div style="font-weight:700">{{ $alert->start_at->translatedFormat('l d M') }}</div>
                <div style="font-size:.75rem;color:var(--muted)">{{ $alert->start_at->diffForHumans() }}</div>
              </td>
              <td style="font-size:.82rem;white-space:nowrap">
                <i class="fas fa-clock me-1" style="color:var(--muted)"></i>
                {{ $alert->start_at->format('H:i') }}
                @if($alert->end_at) → {{ $alert->end_at->format('H:i') }} @endif
              </td>
              <td>
                <span class="badge-cie {{ $badge }}">{{ $prob }}%</span>
              </td>
              <td>
                <span style="font-weight:700;color:var(--orange)">{{ $nbC }}</span>
                <span style="font-size:.72rem;color:var(--muted)"> client(s)</span>
              </td>
              <td>
                <div style="display:flex;gap:5px">
                  <button class="btn-cie-outline btn-alert-detail"
                    style="font-size:.72rem;padding:5px 9px"
                    data-id="{{ $alert->id }}" data-zone="{{ $alert->zone }}"
                    data-start="{{ $alert->start_at->format('d/m/Y H:i') }}"
                    data-end="{{ optional($alert->end_at)->format('d/m/Y H:i') ?? 'N/A' }}"
                    data-reason="{{ $alert->reason ?? '' }}" data-prob="{{ $prob }}"
                    title="Voir les détails">
                    <i class="fas fa-eye"></i>
                  </button>
                  <button class="btn-cie-green btn-notify-agent"
                    style="font-size:.72rem;padding:5px 9px"
                    data-id="{{ $alert->id }}" data-zone="{{ $alert->zone }}"
                    title="Notifier les agents de {{ $alert->zone }}">
                    <i class="fas fa-hard-hat"></i>
                  </button>
                </div>
              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  @else
    <div style="padding:40px;text-align:center;color:var(--muted)">
      <i class="fas fa-calendar-alt" style="font-size:2rem;margin-bottom:10px;display:block;color:var(--blue);opacity:.5"></i>
      Aucune prédiction manuelle active pour les 5 prochains jours.
      <br><a href="{{ route('admin.prediction') }}" style="color:var(--blue);font-weight:700;font-size:.85rem;margin-top:8px;display:inline-block">
        <i class="fas fa-brain me-1"></i>Lancer une prédiction manuelle
      </a>
    </div>
  @endif
</div>

{{-- ═══ MODAL DÉTAIL ═══ --}}
<div class="modal fade" id="alertModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;border:none;overflow:hidden;box-shadow:0 24px 48px rgba(0,0,0,.15)">
      <div class="modal-header" style="background:var(--dark);border:none;padding:22px 28px">
        <div>
          <h5 class="modal-title text-white fw-bold mb-1">
            <i class="fas fa-bolt me-2" style="color:var(--orange)"></i>
            Alerte — <span id="m-zone">—</span>
          </h5>
          <div style="color:rgba(255,255,255,.4);font-size:.8rem">Détails de la prédiction</div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" style="padding:28px">
        <div style="display:flex;justify-content:space-between;align-items:center;background:#f8fafc;border-radius:14px;padding:20px 24px;margin-bottom:20px">
          <div>
            <div style="font-size:.72rem;font-weight:800;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:5px">Zone</div>
            <div id="m-zone-big" style="font-size:1.4rem;font-weight:900;color:var(--dark)">—</div>
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
            <div style="font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;margin-bottom:6px">
              <i class="fas fa-play-circle me-1" style="color:var(--green)"></i>Début prévu
            </div>
            <div id="m-start" style="font-weight:700;color:var(--dark)">—</div>
          </div>
          <div style="background:#f8fafc;border-radius:12px;padding:16px">
            <div style="font-size:.7rem;font-weight:800;color:var(--muted);text-transform:uppercase;margin-bottom:6px">
              <i class="fas fa-stop-circle me-1" style="color:var(--red)"></i>Fin prévue
            </div>
            <div id="m-end" style="font-weight:700;color:var(--dark)">—</div>
          </div>
        </div>
        <div style="background:#fff8f0;border:1.5px solid rgba(255,102,0,.2);border-radius:12px;padding:18px">
          <div style="font-size:.7rem;font-weight:800;color:var(--orange);text-transform:uppercase;margin-bottom:8px">
            <i class="fas fa-info-circle me-1"></i>Cause identifiée
          </div>
          <div id="m-reason" style="font-size:.95rem;font-weight:600;color:var(--dark);line-height:1.5">—</div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);padding:16px 28px;background:#f8fafc;gap:10px">
        <button type="button" class="btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
        <button type="button" class="btn-cie-green" id="modalNotifyBtn">
          <i class="fas fa-hard-hat"></i> Notifier l'agent
        </button>
      </div>
    </div>
  </div>
</div>

{{-- ═══ MODAL NOTIFIER AGENTS ═══ --}}
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
          <i class="fas fa-spinner fa-spin me-2"></i>Chargement des agents...
        </div>
        <div id="na-list" style="display:none;flex-direction:column;gap:10px"></div>
        <div id="na-done" style="display:none;text-align:center;padding:20px">
          <i class="fas fa-check-circle" style="color:var(--green);font-size:2.5rem"></i>
          <div style="font-weight:700;margin-top:10px;color:var(--dark)">Agent(s) notifié(s)</div>
          <div style="font-size:.82rem;color:var(--muted);margin-top:4px" id="na-done-time"></div>
        </div>
      </div>
      <div class="modal-footer" style="border-top:1px solid var(--border);padding:14px 24px;gap:10px">
        <button type="button" class="btn-ghost" data-bs-dismiss="modal"><i class="fas fa-times"></i> Fermer</button>
      </div>
    </div>
  </div>
</div>

@endsection

@section('scripts')
<script>
(function () {

  /* ── Notifier agent (bouton par ligne) ── */
  function notifyAgentForAlert(alertId, zone) {
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
        'Content-Type': 'application/json',
        'Accept': 'application/json'
      }
    }).then(function(r){ return r.json(); }).then(function(d){
      document.getElementById('na-loading').style.display = 'none';
      var list = document.getElementById('na-list');
      list.innerHTML = '';
      if (!d.agents || d.agents.length === 0) {
        list.innerHTML = '<div style="color:var(--muted);text-align:center;padding:16px"><i class="fas fa-hard-hat me-2"></i>Aucun agent assigné à cette zone.</div>';
      } else {
        d.agents.forEach(function(a) {
          list.innerHTML += '<div style="display:flex;align-items:center;gap:12px;background:#f8fafc;border-radius:12px;padding:14px 16px">'
            + '<div style="width:40px;height:40px;border-radius:10px;background:var(--orange-light);display:flex;align-items:center;justify-content:center;font-weight:900;color:var(--orange)">'
            + a.name.substring(0,2).toUpperCase() + '</div>'
            + '<div style="flex:1"><div style="font-weight:700;font-size:.9rem">' + a.name + '</div>'
            + '<div style="font-size:.75rem;color:var(--muted)">' + a.role + ' · ' + a.email + '</div></div>'
            + '<div style="font-size:.82rem;font-weight:700;color:var(--dark)">' + (a.phone || '') + '</div></div>';
        });
        document.getElementById('na-done-time').textContent = 'Notifié le ' + d.notified_at;
      }
      list.style.display = 'flex';
      document.getElementById('na-done').style.display = 'block';
    }).catch(function() {
      document.getElementById('na-loading').innerHTML = '<div style="color:var(--red)"><i class="fas fa-exclamation-triangle me-2"></i>Erreur de connexion.</div>';
    });
  }

  document.querySelectorAll('.btn-notify-agent').forEach(function(btn) {
    btn.addEventListener('click', function() {
      notifyAgentForAlert(this.dataset.id, this.dataset.zone);
    });
  });

  /* ── Modal détail ── */
  var currentAlertId = null;
  document.querySelectorAll('.btn-alert-detail').forEach(function(btn) {
    btn.addEventListener('click', function() {
      currentAlertId = this.dataset.id;
      var zone  = this.dataset.zone;
      var prob  = parseInt(this.dataset.prob) || 0;
      var color = prob >= 80 ? 'var(--red)' : (prob >= 60 ? 'var(--yellow)' : 'var(--orange)');
      document.getElementById('m-zone').textContent     = zone;
      document.getElementById('m-zone-big').textContent = zone;
      document.getElementById('m-prob').textContent     = prob + '%';
      document.getElementById('m-prob').style.color     = color;
      document.getElementById('m-prob-bar').style.width = prob + '%';
      document.getElementById('m-prob-bar').style.background = color;
      document.getElementById('m-start').textContent    = this.dataset.start;
      document.getElementById('m-end').textContent      = this.dataset.end;
      document.getElementById('m-reason').textContent   = this.dataset.reason || '—';
      new bootstrap.Modal(document.getElementById('alertModal')).show();
    });
  });

  document.getElementById('modalNotifyBtn').addEventListener('click', function() {
    if (currentAlertId) {
      bootstrap.Modal.getInstance(document.getElementById('alertModal')).hide();
      var btn = document.querySelector('.btn-notify-agent[data-id="' + currentAlertId + '"]');
      if (btn) btn.click();
    }
  });

})();
</script>
@endsection
