@extends('layouts.admin')
@section('title', 'INNOVAN-CI — Agents')
@section('page-title', 'Gestion des agents')

@section('content')

<div class="page-heading animate-fade" style="display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px">
  <div>
    <h1 class="page-title">Agents & Administrateurs</h1>
    <p class="page-subtitle">{{ $agents->count() }} membres de l'équipe CIE</p>
  </div>
  <div style="display:flex;gap:10px">
    <button onclick="document.getElementById('addAgentForm').classList.toggle('hidden')"
            class="btn-cie" style="padding:10px 18px;font-size:.88rem">
      <i class="fas fa-user-plus"></i> Ajouter un agent
    </button>
    <a href="/admin" class="btn-ghost"><i class="fas fa-arrow-left"></i> Tableau de bord</a>
  </div>
</div>

{{-- Notification succès --}}
@if(session('agent_created'))
  <div style="background:rgba(26,158,63,.1);border:1px solid rgba(26,158,63,.3);border-radius:12px;padding:14px 18px;margin-bottom:20px;display:flex;gap:10px;align-items:center" class="animate-fade">
    <i class="fas fa-check-circle" style="color:var(--green);font-size:1.1rem"></i>
    <span style="font-weight:700;color:var(--green)">{{ session('agent_created') }}</span>
  </div>
@endif

{{-- Formulaire d'ajout --}}
<div id="addAgentForm" class="cie-card animate-fade {{ $errors->any() ? '' : 'hidden' }}" style="margin-bottom:28px">
  <div style="padding:24px 28px">
    <h3 style="font-size:1rem;font-weight:800;color:var(--dark);margin-bottom:20px;display:flex;align-items:center;gap:10px">
      <div style="width:32px;height:32px;border-radius:8px;background:var(--blue-light);display:flex;align-items:center;justify-content:center;color:var(--blue)">
        <i class="fas fa-user-plus" style="font-size:.85rem"></i>
      </div>
      Nouvel agent / administrateur
    </h3>

    @if($errors->any())
      <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.2);border-radius:10px;padding:12px 16px;margin-bottom:18px;font-size:.83rem;color:#ef4444">
        @foreach($errors->all() as $e)<div><i class="fas fa-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
      </div>
    @endif

    <form method="POST" action="{{ route('admin.agents.store') }}">
      @csrf
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px">

        <div class="fgroup">
          <label class="flabel">Nom complet <span style="color:var(--red)">*</span></label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-user"></i>
            <input type="text" name="name" value="{{ old('name') }}" class="finput" placeholder="Kouamé André" required>
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">E-mail <span style="color:var(--red)">*</span></label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-envelope"></i>
            <input type="email" name="email" value="{{ old('email') }}" class="finput" placeholder="agent@cie.ci" required>
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Téléphone</label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-phone"></i>
            <input type="tel" name="phone" value="{{ old('phone') }}" class="finput" placeholder="225 07 XX XX XX">
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Mot de passe <span style="color:var(--red)">*</span></label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-lock"></i>
            <input type="password" name="password" class="finput" placeholder="Min. 8 caractères" required>
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Zone assignée</label>
          <div class="finput-wrap">
            <i class="finput-icon fas fa-map-marker-alt"></i>
            <select name="zone" class="finput" style="padding-left:42px;cursor:pointer">
              <option value="">Toutes les zones (admin)</option>
              @php
                $zones = ['Plateau','Adjamé','Treichville','Attécoubé','Abobo','Anyama','Yopougon',
                          'Songon','Cocody','Bingerville','Port-Bouët','Marcory','Koumassi',
                          'Bouaké','Yamoussoukro','San-Pédro','Daloa','Abengourou','Man','Korhogo'];
              @endphp
              @foreach($zones as $z)
                <option value="{{ $z }}" {{ old('zone') === $z ? 'selected' : '' }}>{{ $z }}</option>
              @endforeach
            </select>
          </div>
        </div>

        <div class="fgroup">
          <label class="flabel">Rôle <span style="color:var(--red)">*</span></label>
          <div style="display:flex;gap:10px;margin-top:6px">
            <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid {{ old('role','agent')==='agent' ? 'var(--blue)' : 'var(--border)' }};border-radius:10px;cursor:pointer;flex:1;background:{{ old('role','agent')==='agent' ? 'var(--blue-light)' : 'var(--card)' }}" id="roleAgentLbl">
              <input type="radio" name="role" value="agent" {{ old('role','agent')==='agent' ? 'checked' : '' }}
                     onchange="styleRole()" style="accent-color:var(--blue)">
              <span>
                <div style="font-weight:700;font-size:.88rem;color:var(--blue)"><i class="fas fa-hard-hat me-1"></i>Agent terrain</div>
                <div style="font-size:.72rem;color:var(--muted)">Accès zone uniquement</div>
              </span>
            </label>
            <label style="display:flex;align-items:center;gap:8px;padding:10px 16px;border:2px solid {{ old('role')==='admin' ? 'var(--orange)' : 'var(--border)' }};border-radius:10px;cursor:pointer;flex:1;background:{{ old('role')==='admin' ? 'var(--orange-light)' : 'var(--card)' }}" id="roleAdminLbl">
              <input type="radio" name="role" value="admin" {{ old('role')==='admin' ? 'checked' : '' }}
                     onchange="styleRole()" style="accent-color:var(--orange)">
              <span>
                <div style="font-weight:700;font-size:.88rem;color:var(--orange)"><i class="fas fa-user-shield me-1"></i>Administrateur</div>
                <div style="font-size:.72rem;color:var(--muted)">Accès complet</div>
              </span>
            </label>
          </div>
        </div>

      </div>

      <div style="display:flex;gap:12px;margin-top:20px;justify-content:flex-end">
        <button type="button" onclick="document.getElementById('addAgentForm').classList.add('hidden')" class="btn-ghost">
          Annuler
        </button>
        <button type="submit" class="btn-cie" style="padding:10px 24px">
          <i class="fas fa-user-check"></i> Créer l'agent
        </button>
      </div>
    </form>
  </div>
</div>

<style>
  .hidden { display: none !important; }
</style>
<script>
function styleRole() {
  var isAdmin = document.querySelector('input[name="role"][value="admin"]').checked;
  var agentLbl = document.getElementById('roleAgentLbl');
  var adminLbl = document.getElementById('roleAdminLbl');
  agentLbl.style.borderColor = isAdmin ? 'var(--border)' : 'var(--blue)';
  agentLbl.style.background  = isAdmin ? 'var(--card)'   : 'var(--blue-light)';
  adminLbl.style.borderColor = isAdmin ? 'var(--orange)'      : 'var(--border)';
  adminLbl.style.background  = isAdmin ? 'var(--orange-light)': 'var(--card)';
}
</script>

{{-- KPIs --}}
<div class="kpi-row mb-6 animate-fade delay-1">
  <div class="kpi-card kpi-orange">
    <div class="kpi-info">
      <div class="kpi-label">Total membres</div>
      <div class="kpi-value">{{ $agents->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-user-tie"></i> Équipe CIE</div>
    </div>
    <div class="kpi-icon i-orange"><i class="fas fa-user-tie"></i></div>
  </div>
  <div class="kpi-card kpi-blue">
    <div class="kpi-info">
      <div class="kpi-label">Agents terrain</div>
      <div class="kpi-value">{{ $agents->where('role','agent')->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-hard-hat"></i> Sur le terrain</div>
    </div>
    <div class="kpi-icon i-blue"><i class="fas fa-hard-hat"></i></div>
  </div>
  <div class="kpi-card kpi-green">
    <div class="kpi-info">
      <div class="kpi-label">Administrateurs</div>
      <div class="kpi-value">{{ $agents->where('role','admin')->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-user-shield"></i> Accès complet</div>
    </div>
    <div class="kpi-icon i-green"><i class="fas fa-user-shield"></i></div>
  </div>
  <div class="kpi-card kpi-red">
    <div class="kpi-info">
      <div class="kpi-label">Zones couvertes</div>
      <div class="kpi-value">{{ $agents->whereNotNull('zone')->pluck('zone')->unique()->count() }}</div>
      <div class="kpi-trend neu"><i class="fas fa-map"></i> Communes assignées</div>
    </div>
    <div class="kpi-icon i-red"><i class="fas fa-map-marked-alt"></i></div>
  </div>
</div>

{{-- Grille agents --}}
<div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:20px" class="animate-fade delay-2">
  @foreach($agents as $a)
    @php
      $isAdmin  = $a->role === 'admin';
      $roleColor = $isAdmin ? 'var(--orange)' : 'var(--blue)';
      $roleLabel = $isAdmin ? 'Administrateur' : 'Agent terrain';
      $roleIcon  = $isAdmin ? 'fa-user-shield' : 'fa-hard-hat';
      $initials  = strtoupper(substr($a->name, 0, 2));
    @endphp
    <div class="cie-card" style="transition:.2s" onmouseover="this.style.transform='translateY(-3px)'" onmouseout="this.style.transform=''">
      <div style="padding:24px">
        <div style="display:flex;align-items:center;gap:16px;margin-bottom:16px">
          <div style="width:52px;height:52px;border-radius:14px;background:{{ $isAdmin ? 'var(--orange-light)' : 'var(--blue-light)' }};display:flex;align-items:center;justify-content:center;font-size:1.2rem;font-weight:900;color:{{ $roleColor }}">
            {{ $initials }}
          </div>
          <div>
            <div style="font-weight:800;color:var(--dark);font-size:.95rem">{{ $a->name }}</div>
            <span style="display:inline-flex;align-items:center;gap:4px;font-size:.72rem;font-weight:700;color:{{ $roleColor }};background:{{ $isAdmin ? 'var(--orange-light)' : 'var(--blue-light)' }};padding:2px 8px;border-radius:20px;margin-top:3px">
              <i class="fas {{ $roleIcon }}"></i> {{ $roleLabel }}
            </span>
          </div>
        </div>

        <div style="display:flex;flex-direction:column;gap:8px;font-size:.82rem">
          <div style="display:flex;align-items:center;gap:8px;color:var(--muted)">
            <i class="fas fa-envelope" style="width:14px;text-align:center"></i>
            <span style="color:var(--text)">{{ $a->email }}</span>
          </div>
          @if($a->phone)
            <div style="display:flex;align-items:center;gap:8px;color:var(--muted)">
              <i class="fas fa-phone" style="width:14px;text-align:center"></i>
              <span style="color:var(--text)">{{ $a->phone }}</span>
            </div>
          @endif
          <div style="display:flex;align-items:center;gap:8px;color:var(--muted)">
            <i class="fas fa-map-marker-alt" style="width:14px;text-align:center"></i>
            <span style="color:var(--text)">{{ $a->zone ? 'Zone : '.$a->zone : 'Toutes les zones' }}</span>
          </div>
        </div>

        <div style="margin-top:16px;padding-top:14px;border-top:1px solid var(--border);display:flex;justify-content:space-between;align-items:center">
          <div style="font-size:.78rem;color:var(--muted)">
            <i class="fas fa-bell me-1"></i>
            @if($a->alert_count > 0)
              <span style="color:var(--red);font-weight:700">{{ $a->alert_count }} alerte(s) dans sa zone</span>
            @else
              <span style="color:var(--green)">Zone stable</span>
            @endif
          </div>
          @if($a->zone)
            <span class="badge-cie badge-{{ $a->alert_count > 0 ? 'red' : 'green' }}" style="font-size:.7rem">{{ $a->zone }}</span>
          @endif
        </div>
      </div>
    </div>
  @endforeach
</div>

@endsection
