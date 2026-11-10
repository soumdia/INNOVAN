/* ============================================================
   CIE — JavaScript Principal
   ============================================================ */
(function () {
  'use strict';

  document.addEventListener('DOMContentLoaded', function () {

    /* ---- Sidebar toggle (admin) ---- */
    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebar = document.getElementById('sidebar');
    if (sidebarToggle && sidebar) {
      sidebarToggle.addEventListener('click', function () {
        sidebar.classList.toggle('open');
      });
      // Fermer en cliquant en dehors
      document.addEventListener('click', function (e) {
        if (!sidebar.contains(e.target) && !sidebarToggle.contains(e.target)) {
          sidebar.classList.remove('open');
        }
      });
    }

    /* ---- Password visibility toggle ---- */
    var eyeBtn = document.getElementById('togglePassword');
    var passInput = document.getElementById('passwordInput');
    if (eyeBtn && passInput) {
      eyeBtn.addEventListener('click', function () {
        var isText = passInput.type === 'text';
        passInput.type = isText ? 'password' : 'text';
        eyeBtn.innerHTML = isText
          ? '<i class="fas fa-eye"></i>'
          : '<i class="fas fa-eye-slash"></i>';
      });
    }

    /* ---- Client tabs ---- */
    var tabs = document.querySelectorAll('.ctab[data-tab]');
    var panes = document.querySelectorAll('.tab-pane[data-pane]');
    tabs.forEach(function (tab) {
      tab.addEventListener('click', function () {
        var target = this.dataset.tab;
        tabs.forEach(function (t) { t.classList.remove('active'); });
        panes.forEach(function (p) { p.classList.remove('active'); });
        this.classList.add('active');
        var pane = document.querySelector('.tab-pane[data-pane="' + target + '"]');
        if (pane) {
          pane.classList.add('active');
          // Init chart si première fois
          if (target === 'consumption' && !pane.dataset.chartInit) {
            pane.dataset.chartInit = '1';
            initConsumptionChart();
          }
        }
      });
    });

    /* ---- Zone bar animations ---- */
    animateBars();

    /* ---- Admin: modal alertes ---- */
    var detailBtns = document.querySelectorAll('.btn-alert-detail');
    var alertModal = document.getElementById('alertModal');
    if (alertModal && detailBtns.length) {
      detailBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
          fillModal(this.dataset);
          var modal = new bootstrap.Modal(alertModal);
          modal.show();
        });
      });
    }

    /* ---- Notify agents (modal) ---- */
    var modalNotifyBtn = document.getElementById('modalNotifyBtn');
    if (modalNotifyBtn) {
      modalNotifyBtn.addEventListener('click', function () {
        var id = this.dataset.alertId;
        showToast('Agents notifiés pour l\'alerte #' + id, 'success');
        var modal = bootstrap.Modal.getInstance(alertModal);
        if (modal) modal.hide();
      });
    }

    /* ---- Notify quick (table) ---- */
    document.querySelectorAll('.btn-notify-quick').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = this.dataset.id;
        showToast('Agents notifiés pour l\'alerte #' + id, 'success');
        this.textContent = 'Notifié ✓';
        this.disabled = true;
        this.classList.remove('btn-cie-green');
        this.classList.add('btn-ghost');
      });
    });

    /* ---- Mark alert read (client) ---- */
    document.querySelectorAll('.btn-mark-read').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var card = this.closest('.alert-card');
        if (card) {
          card.classList.remove('unread');
          card.classList.add('read');
        }
        this.textContent = 'Lu ✓';
        this.disabled = true;
        this.classList.add('btn-ghost');
        updateUnreadCount(-1);
      });
    });

    /* ---- Payment method selection ---- */
    document.querySelectorAll('.pay-method').forEach(function (method) {
      method.addEventListener('click', function () {
        document.querySelectorAll('.pay-method').forEach(function (m) {
          m.classList.remove('selected');
        });
        this.classList.add('selected');
      });
    });

    /* ---- Pay button ---- */
    var payBtn = document.getElementById('payBtn');
    if (payBtn) {
      payBtn.addEventListener('click', function () {
        var selected = document.querySelector('.pay-method.selected');
        if (!selected) {
          showToast('Veuillez choisir un mode de paiement', 'warning');
          return;
        }
        var phoneInput = document.getElementById('phoneInput');
        if (phoneInput && !phoneInput.value.trim()) {
          showToast('Veuillez entrer votre numéro', 'warning');
          return;
        }
        showToast('Redirection vers le paiement...', 'info');
      });
    }

    /* ---- Zone filter (admin alerts) ---- */
    var zoneFilter = document.getElementById('zoneFilter');
    if (zoneFilter) {
      zoneFilter.addEventListener('change', function () {
        var val = this.value.toLowerCase();
        document.querySelectorAll('tr[data-zone]').forEach(function (row) {
          if (!val || row.dataset.zone.toLowerCase().includes(val)) {
            row.style.display = '';
          } else {
            row.style.display = 'none';
          }
        });
      });
    }

  }); // end DOMContentLoaded

  /* ============================================================
     HELPERS
  ============================================================ */

  function fillModal(data) {
    setText('m-zone',    data.zone    || '—');
    setText('m-start',   data.start   || '—');
    setText('m-end',     data.end     || '—');
    setText('m-reason',  data.reason  || '—');
    setText('m-prob',    (data.prob   || '—') + '%');

    var btn = document.getElementById('modalNotifyBtn');
    if (btn) btn.dataset.alertId = data.id || '';

    var prob = parseInt(data.prob) || 0;
    var bar = document.getElementById('m-prob-bar');
    if (bar) {
      bar.style.width = prob + '%';
      bar.style.background = prob >= 80 ? '#ef4444' : prob >= 50 ? '#f59e0b' : '#1a9e3f';
    }
  }

  function setText(id, val) {
    var el = document.getElementById(id);
    if (el) el.textContent = val;
  }

  function animateBars() {
    var bars = document.querySelectorAll('.zone-bar-fill[data-pct]');
    bars.forEach(function (bar) {
      bar.style.width = '0%';
      setTimeout(function () {
        bar.style.width = bar.dataset.pct + '%';
      }, 150);
    });
  }

  function updateUnreadCount(delta) {
    var badge = document.getElementById('unreadCount');
    if (!badge) return;
    var count = parseInt(badge.textContent) + delta;
    badge.textContent = count;
    if (count <= 0) badge.style.display = 'none';
  }

  function initConsumptionChart() {
    var ctx = document.getElementById('consumptionChart');
    if (!ctx || typeof Chart === 'undefined') return;
    var labels = [], data = [];
    for (var i = 6; i >= 0; i--) {
      var d = new Date();
      d.setDate(d.getDate() - i);
      labels.push(d.toLocaleDateString('fr-FR', { weekday: 'short', day: 'numeric' }));
      data.push(Math.floor(180 + Math.random() * 320));
    }
    new Chart(ctx, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: 'kWh consommés',
          data: data,
          borderColor: '#FF6600',
          backgroundColor: 'rgba(255,102,0,.08)',
          borderWidth: 2.5,
          pointBackgroundColor: '#FF6600',
          pointRadius: 4,
          tension: 0.4,
          fill: true
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { display: false },
          tooltip: { mode: 'index', intersect: false }
        },
        scales: {
          x: { grid: { display: false } },
          y: {
            beginAtZero: false,
            grid: { color: 'rgba(0,0,0,.04)' },
            ticks: { callback: function (v) { return v + ' kWh'; } }
          }
        }
      }
    });
  }

  /* ---- Toast notification ---- */
  function showToast(msg, type) {
    type = type || 'info';
    var colors = {
      success: '#1a9e3f',
      warning: '#f59e0b',
      info:    '#3b82f6',
      error:   '#ef4444'
    };
    var icons = {
      success: 'fa-check-circle',
      warning: 'fa-exclamation-triangle',
      info:    'fa-info-circle',
      error:   'fa-times-circle'
    };

    var toast = document.createElement('div');
    toast.style.cssText = [
      'position:fixed', 'bottom:24px', 'right:24px', 'z-index:9999',
      'background:#fff', 'border-radius:12px',
      'padding:14px 20px', 'display:flex', 'align-items:center', 'gap:12px',
      'box-shadow:0 8px 24px rgba(0,0,0,.12)', 'border:1px solid #e2e8f0',
      'font-family:var(--font,Inter,sans-serif)',
      'font-size:.875rem', 'font-weight:600', 'color:#1e293b',
      'min-width:260px', 'max-width:360px',
      'animation:fadeUp .3s ease both'
    ].join(';');

    toast.innerHTML =
      '<i class="fas ' + icons[type] + '" style="color:' + colors[type] + ';font-size:1rem;flex-shrink:0"></i>' +
      '<span>' + msg + '</span>';

    document.body.appendChild(toast);
    setTimeout(function () {
      toast.style.opacity = '0';
      toast.style.transform = 'translateY(8px)';
      toast.style.transition = '.3s ease';
      setTimeout(function () { toast.remove(); }, 300);
    }, 3200);
  }

  // Expose for inline calls
  window.cieShowToast = showToast;

})();
