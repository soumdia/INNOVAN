{{-- Vue partielle pure — incluse dans dashboard (onglet) ET dans alerts_page --}}

<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;flex-wrap:wrap;gap:12px">
  <div>
    <h2 style="font-size:1rem;font-weight:800;color:var(--dark);margin-bottom:3px;display:flex;align-items:center;gap:10px">
      <span style="width:8px;height:8px;border-radius:50%;background:var(--orange);display:inline-block"></span>
      Alertes de coupures
    </h2>
    <p style="font-size:.82rem;color:var(--muted)">
      {{ isset($alerts) ? $alerts->count() : 0 }} alerte(s) pour votre zone
    </p>
  </div>
  <button class="btn-ghost" style="font-size:.8rem;padding:7px 14px" onclick="markAllRead()">
    <i class="fas fa-check-double"></i> Tout marquer lu
  </button>
</div>

@if(isset($alerts) && $alerts->count())
  @foreach($alerts as $idx => $alert)
    @php
      $prob = $alert->probability ?? 0;
      $iconType  = $prob >= 80 ? 'danger' : ($prob >= 60 ? 'warn' : 'info');
      $iconClass = $prob >= 80 ? 'fa-exclamation-triangle' : ($prob >= 60 ? 'fa-bolt' : 'fa-info-circle');
      $badgeClass = $prob >= 80 ? 'badge-red' : ($prob >= 60 ? 'badge-yellow' : 'badge-blue');
      $color = $prob >= 80 ? 'var(--red)' : ($prob >= 60 ? 'var(--yellow)' : 'var(--blue)');
    @endphp
    <div class="alert-card unread" id="alertCard{{ $idx }}">
      <div class="alert-icon {{ $iconType }}">
        <i class="fas {{ $iconClass }}"></i>
      </div>
      <div class="alert-body">
        <div class="alert-title">
          Coupure prévue — {{ $alert->zone }}
          @if($alert->quartier)
            <span style="font-size:.78rem;font-weight:500;color:var(--muted)"> · {{ $alert->quartier }}</span>
          @endif
        </div>
        <div class="alert-meta">
          <span><i class="fas fa-calendar"></i> {{ $alert->start_at->translatedFormat('l d M Y') }}</span>
          <span><i class="fas fa-clock"></i> {{ $alert->start_at->format('H:i') }}@if($alert->end_at) → {{ $alert->end_at->format('H:i') }}@endif</span>
          @if($alert->reason)
            <span><i class="fas fa-info-circle"></i> {{ Str::limit($alert->reason, 50) }}</span>
          @endif
        </div>
        <div class="outage-tl-prob" style="margin-top:8px">
          <div class="outage-tl-prob-bar">
            <div class="outage-tl-prob-fill" style="width:{{ $prob }}%;background:{{ $color }}"></div>
          </div>
          <span style="font-size:.75rem;font-weight:800;color:{{ $color }}">{{ $prob }}%</span>
          <span class="badge-cie {{ $badgeClass }}" style="font-size:.65rem">
            {{ $prob >= 80 ? 'Urgent' : ($prob >= 60 ? 'Probable' : 'Possible') }}
          </span>
        </div>
      </div>
      <div class="alert-actions">
        <button class="btn-mark-read btn-ghost" style="font-size:.78rem;padding:6px 12px" data-idx="{{ $idx }}">
          <i class="fas fa-check"></i> Lu
        </button>
      </div>
    </div>
  @endforeach
@else
  <div style="background:linear-gradient(135deg,rgba(26,158,63,.05),rgba(26,158,63,.02));border:1px solid rgba(26,158,63,.15);border-radius:16px;padding:60px 24px;text-align:center">
    <div style="width:72px;height:72px;border-radius:50%;background:var(--green-light);display:flex;align-items:center;justify-content:center;margin:0 auto 18px;font-size:1.6rem;color:var(--green)">
      <i class="fas fa-check-circle"></i>
    </div>
    <h3 style="font-weight:800;color:var(--dark);margin-bottom:8px">Aucune alerte prévue</h3>
    <p style="color:var(--muted);font-size:.875rem">Votre zone ne présente aucune coupure prévue pour les 5 prochains jours.</p>
  </div>
@endif

<script>
function markAllRead() {
  document.querySelectorAll('.alert-card.unread').forEach(function(card) {
    card.classList.remove('unread');
    card.classList.add('read');
    var btn = card.querySelector('.btn-mark-read');
    if (btn) { btn.innerHTML = 'Lu ✓'; btn.disabled = true; }
  });
  var badge = document.getElementById('unreadCount');
  if (badge) badge.style.display = 'none';
  if (typeof cieShowToast !== 'undefined') cieShowToast('Toutes les alertes marquées comme lues', 'success');
}
</script>
