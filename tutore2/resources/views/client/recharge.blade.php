@extends('layouts.client')
@section('title', 'Recharger mon compteur — INNOVAN-CI')

@section('content')
@php
  $balance  = (float) ($user->balance ?? 0);
  $dailyKwh = (float) ($user->daily_kwh ?? 0);
  $jours    = $dailyKwh > 0 && $balance > 0 ? floor($balance / ($dailyKwh * 35)) : 0;
@endphp

<div style="max-width:680px;margin:0 auto;padding:32px 16px">

  <div style="margin-bottom:28px">
    <a href="/client" style="display:inline-flex;align-items:center;gap:8px;font-size:.85rem;color:var(--muted);text-decoration:none;font-weight:600">
      <i class="fas fa-arrow-left"></i> Retour au tableau de bord
    </a>
    <h1 style="font-size:1.5rem;font-weight:900;color:var(--dark);margin-top:12px">
      Recharger mon compteur
    </h1>
    <p style="color:var(--muted);font-size:.9rem">Compteur {{ $user->meter_number }} &mdash; Zone {{ $user->zone ?? 'N/A' }}</p>
  </div>

  {{-- Solde actuel --}}
  <div style="background:linear-gradient(135deg,#1a3a1a,#0f2010);border-radius:20px;padding:28px 32px;color:#fff;margin-bottom:24px;position:relative;overflow:hidden">
    <div style="position:absolute;top:-30px;right:-30px;width:140px;height:140px;border-radius:50%;background:rgba(74,222,128,.06)"></div>
    <div style="font-size:.8rem;opacity:.6;margin-bottom:6px">Solde actuel</div>
    <div style="font-size:2.8rem;font-weight:900;color:{{ $balance > 0 ? '#4ade80' : '#f87171' }}">
      {{ number_format($balance, 0, '.', ' ') }}
      <span style="font-size:1.1rem;opacity:.6;font-weight:400">FCFA</span>
    </div>
    <div style="font-size:.82rem;opacity:.7;margin-top:8px">
      <i class="fas fa-bolt me-1"></i>
      {{ number_format($dailyKwh, 1) }} kWh/jour &middot;
      @if($jours > 0)
        Autonomie : <strong style="color:{{ $jours < 5 ? '#fbbf24' : '#4ade80' }}">{{ $jours }} jour{{ $jours > 1 ? 's' : '' }}</strong>
      @else
        <strong style="color:#f87171">Solde épuisé</strong>
      @endif
    </div>
  </div>

  @if(session('recharge_success'))
    <div style="background:rgba(74,222,128,.1);border:1px solid rgba(74,222,128,.3);border-radius:12px;padding:16px 20px;margin-bottom:20px;display:flex;gap:12px;align-items:center">
      <i class="fas fa-check-circle" style="color:var(--green);font-size:1.2rem"></i>
      <div>
        <div style="font-weight:800;color:var(--green)">Rechargement effectué</div>
        <div style="font-size:.85rem;color:var(--muted)">{{ session('recharge_success') }}</div>
      </div>
    </div>
  @endif

  @if($errors->any())
    <div style="background:rgba(239,68,68,.08);border:1px solid rgba(239,68,68,.25);border-radius:12px;padding:14px 18px;margin-bottom:20px;color:#ef4444;font-size:.85rem">
      @foreach($errors->all() as $e)<div><i class="fas fa-exclamation-circle me-1"></i>{{ $e }}</div>@endforeach
    </div>
  @endif

  <form method="POST" action="{{ route('client.recharge.post') }}">
    @csrf

    {{-- Montants rapides --}}
    <div style="margin-bottom:20px">
      <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">
        Montant rapide
      </div>
      <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:10px">
        @foreach([1000,2000,5000,10000,15000,20000,30000,50000] as $preset)
          <button type="button" onclick="setAmt({{ $preset }})"
            style="padding:12px 8px;border:2px solid var(--border);border-radius:12px;background:var(--card);font-weight:700;font-size:.82rem;cursor:pointer;transition:.15s;color:var(--text)"
            class="preset-btn">
            {{ number_format($preset, 0, '.', ' ') }}<br>
            <span style="font-size:.7rem;font-weight:500;color:var(--muted)">FCFA</span>
          </button>
        @endforeach
      </div>
    </div>

    {{-- Montant personnalisé --}}
    <div class="fgroup" style="margin-bottom:20px">
      <label class="flabel">Ou saisir un montant personnalisé (FCFA)</label>
      <div class="finput-wrap">
        <i class="finput-icon fas fa-coins"></i>
        <input id="rechargeAmt" type="number" name="amount" min="500" max="500000"
               value="{{ old('amount') }}" placeholder="Min. 500 FCFA" class="finput" required>
      </div>
    </div>

    {{-- Méthode de paiement --}}
    <div style="margin-bottom:24px">
      <div style="font-size:.78rem;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:.06em;margin-bottom:10px">
        Mode de paiement
      </div>
      <input type="hidden" name="method" id="payMethod" value="orange_money">
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">
        @foreach([
          'orange_money' => ['label'=>'Orange Money',   'icon'=>'📱', 'sub'=>'*144#'],
          'mtn_momo'     => ['label'=>'MTN MoMo',       'icon'=>'📲', 'sub'=>'*126#'],
          'moov'         => ['label'=>'Moov Money',     'icon'=>'📳', 'sub'=>'*555#'],
          'carte'        => ['label'=>'Carte bancaire', 'icon'=>'💳', 'sub'=>'Visa / Mastercard'],
        ] as $code => $info)
          <div class="pay-card {{ $code === 'orange_money' ? 'pay-card--active' : '' }}"
               id="card_{{ $code }}"
               onclick="selectMethod('{{ $code }}')"
               style="padding:14px 16px;border:2px solid {{ $code==='orange_money' ? 'var(--orange)' : 'var(--border)' }};border-radius:14px;cursor:pointer;background:{{ $code==='orange_money' ? 'var(--orange-light)' : 'var(--card)' }};transition:.15s">
            <div style="font-size:1.4rem;margin-bottom:4px">{{ $info['icon'] }}</div>
            <div style="font-weight:700;font-size:.88rem;color:var(--dark)">{{ $info['label'] }}</div>
            <div style="font-size:.72rem;color:var(--muted)">{{ $info['sub'] }}</div>
          </div>
        @endforeach
      </div>
    </div>

    {{-- Numéro de téléphone (Mobile Money) --}}
    <div class="fgroup" id="phoneGroup" style="margin-bottom:24px">
      <label class="flabel">Numéro de téléphone</label>
      <div class="finput-wrap">
        <i class="finput-icon fas fa-phone"></i>
        <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}"
               placeholder="225 07 XX XX XX" class="finput">
      </div>
    </div>

    <button type="submit" class="btn-cie" style="width:100%;justify-content:center;padding:16px;font-size:1rem">
      <i class="fas fa-bolt"></i> Confirmer le rechargement
    </button>

    <p style="text-align:center;font-size:.72rem;color:var(--muted);margin-top:12px">
      <i class="fas fa-shield-alt me-1"></i>Transaction sécurisée SSL &middot; INNOVAN-CI
    </p>
  </form>
</div>

@endsection

@section('scripts')
<script>
  function setAmt(v) {
    document.getElementById('rechargeAmt').value = v;
    document.querySelectorAll('.preset-btn').forEach(function(b) {
      b.style.borderColor = 'var(--border)';
      b.style.background  = 'var(--card)';
    });
  }

  function selectMethod(code) {
    document.getElementById('payMethod').value = code;
    var phoneGroup = document.getElementById('phoneGroup');
    phoneGroup.style.display = (code === 'carte') ? 'none' : 'block';

    ['orange_money','mtn_momo','moov','carte'].forEach(function(c) {
      var el = document.getElementById('card_' + c);
      if (!el) return;
      if (c === code) {
        el.style.borderColor = 'var(--orange)';
        el.style.background  = 'var(--orange-light)';
      } else {
        el.style.borderColor = 'var(--border)';
        el.style.background  = 'var(--card)';
      }
    });
  }
  window.setAmt = setAmt;
  window.selectMethod = selectMethod;
</script>
@endsection
