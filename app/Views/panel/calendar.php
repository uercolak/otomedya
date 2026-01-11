<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet"/>

<style>
  .fc .fc-event { border-radius: 12px; padding: 3px 8px; border: 1px solid transparent; }
  .fc .fc-event-title { font-weight: 600; }

  /* Platform (soft) */
  .ev-platform-INSTAGRAM { background: rgba(225,48,108,.18); border-color: rgba(225,48,108,.35); }
  .ev-platform-FACEBOOK  { background: rgba(24,119,242,.18); border-color: rgba(24,119,242,.35); }
  .ev-platform-TIKTOK    { background: rgba(0,242,234,.18); border-color: rgba(0,242,234,.35); }
  .ev-platform-YOUTUBE   { background: rgba(255,0,0,.14);   border-color: rgba(255,0,0,.28); }

  /* Status */
  .ev-status-canceled { opacity: .55; text-decoration: line-through; }
  .ev-status-failed   { box-shadow: inset 0 0 0 1px rgba(220,53,69,.35); }
  .ev-status-published{ box-shadow: inset 0 0 0 1px rgba(25,135,84,.22); }

  /* Today highlight */
  .fc .fc-day-today { background: rgba(99,102,241,.07) !important; }

  /* Tooltip (light card) */
  .fc-tooltip-light .tooltip-inner{
    background:#fff; color:#212529;
    border-radius:12px;
    box-shadow:0 10px 30px rgba(0,0,0,.12);
    border:1px solid rgba(0,0,0,.08);
    padding:0;
    max-width: 360px;
    text-align:left;
  }
  .fc-tooltip-light .tooltip-arrow::before{ border-top-color:#fff !important; }
</style>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>

<div class="row g-3">
  <div class="col-lg-8">
    <div class="card-soft p-3 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="metric-label">Takvim</div>
        <span class="metric-tag"><i class="bi bi-arrows-move me-1"></i> Sürükle &amp; Bırak</span>
      </div>

      <div id="calendar"></div>

      <div class="text-muted small mt-2">
        İpucu: <b>Sıraya Alınmış / Planlanmış</b> gönderileri sürükleyerek tarih ve saat değiştirebilirsin.
      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card-soft p-3 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="metric-label">Yeni Gönderi Planla</div>
        <a href="<?= site_url('panel/planner') ?>" class="btn btn-primary">+ Planla</a>
      </div>

      <div class="text-muted small">
        Planlama ekranı gerçek <b>content + publishes + jobs</b> üretir ✅<br>
        Takvim tarafı sürükle-bırak ile <code>schedule_at</code> günceller.
      </div>

      <hr>

      <div class="small text-muted">
        Not: “sürükle-bırak” sadece <b>Sıraya Alınmış / Planlanmış</b> kayıtlar için açık.
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const events = <?= $eventsJson ?? '[]' ?>;

  const statusIcon = {
    queued:'bi-clock', scheduled:'bi-calendar-check',
    publishing:'bi-upload', published:'bi-check-circle',
    failed:'bi-exclamation-triangle', canceled:'bi-x-circle'
  };

  const escapeHtml = (s) => {
    if (!s) return '';
    return String(s)
      .replaceAll('&','&amp;')
      .replaceAll('<','&lt;')
      .replaceAll('>','&gt;')
      .replaceAll('"','&quot;')
      .replaceAll("'","&#039;");
  };

  const truncate = (s, n) => {
    s = (s || '').trim();
    if (!s) return '';
    return s.length > n ? s.slice(0, n) + '…' : s;
  };

  const calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    locale: 'tr',

    buttonText: { month: 'Ay', week: 'Hafta', day: 'Gün' },

    headerToolbar: {
      left: 'title',
      center: '',
      right: 'dayGridMonth,timeGridWeek,timeGridDay prev,next'
    },

    editable: true,
    eventDurationEditable: false,
    events,

    eventClassNames: function(arg){
      const ep = arg.event.extendedProps || {};
      const classes = [];
      if (ep.status) classes.push('ev-status-' + ep.status);
      if (ep.platform) classes.push('ev-platform-' + ep.platform);
      return classes;
    },

    // Drag sadece queued/scheduled
    eventAllow: function(dropInfo, draggedEvent){
      const st = (draggedEvent.extendedProps && draggedEvent.extendedProps.status) ? draggedEvent.extendedProps.status : '';
      return ['queued','scheduled'].includes(st);
    },

    // Event içindeki yazıya minik ikon + saat
    eventContent: function(arg){
      const ep = arg.event.extendedProps || {};
      const st = ep.status || '';
      const icon = statusIcon[st] || 'bi-dot';

      const time = arg.timeText ? `<span class="me-1">${arg.timeText}</span>` : '';
      const platform = `<span class="fw-semibold">${arg.event.title}</span>`;

      return { html: `<i class="bi ${icon} me-1"></i>${time}${platform}` };
    },

    // ✅ Zengin tooltip: platform + status + hesap + content title + kısa metin + medya
    eventDidMount: function(info){
      const ep = info.event.extendedProps || {};
      const st = ep.status || '';
      const stLabel = ep.statusLabel || st;

      const title = escapeHtml(ep.contentTitle || '');
      const text  = escapeHtml(truncate(ep.contentText || '', 120));
      const acc   = escapeHtml(ep.account || '');

      const mediaType = (ep.mediaType || '').toLowerCase();
      const mediaUrl  = ep.mediaUrl || '';
      let mediaHtml = '';

      if (mediaUrl) {
        const icon = (mediaType === 'video') ? 'bi-camera-video' : 'bi-image';
        const label = (mediaType === 'video') ? 'Video' : 'Görsel';
        mediaHtml = `
          <div class="mt-2">
            <a href="${escapeHtml(mediaUrl)}" target="_blank" rel="noopener" class="text-decoration-none">
              <i class="bi ${icon} me-1"></i>${label}yi aç
            </a>
          </div>
        `;
      }

      const html = `
        <div class="p-2" style="min-width:280px">
          <div class="d-flex align-items-center gap-2 mb-1">
            <div class="fw-semibold">${escapeHtml(ep.platform || '')}</div>
            <span class="badge bg-light text-dark border">${escapeHtml(stLabel)}</span>
          </div>

          <div class="small text-muted mb-1">${acc}</div>

          <div class="small fw-semibold">${title}</div>
          ${text ? `<div class="small text-muted mt-1">${text}</div>` : ''}

          ${mediaHtml}
        </div>
      `;

      if (window.bootstrap?.Tooltip) {
        new bootstrap.Tooltip(info.el, {
          title: html,
          html: true,
          placement: 'top',
          trigger: 'hover',
          container: 'body',
          customClass: 'fc-tooltip-light'
        });
      }
    },

    // Published ise: remoteUrl varsa direkt aç, yoksa detay
    eventClick: function(info){
      const ep = info.event.extendedProps || {};
      const st = ep.status || '';
      const remoteUrl = ep.remoteUrl || '';

      if (st === 'published' && remoteUrl) {
        info.jsEvent.preventDefault();
        window.open(remoteUrl, '_blank', 'noopener');
        return;
      }

      if (info.event.url) {
        info.jsEvent.preventDefault();
        window.location.href = info.event.url;
      }
    },

    // Drag drop -> POST update
    eventDrop: async function(info){
        const ep = info.event.extendedProps || {};
        const st = ep.status || '';
        if (!['queued','scheduled'].includes(st)) { info.revert(); return; }

        const dt = info.event.start;
        const pad = (n) => String(n).padStart(2,'0');
        const scheduleAt =
            dt.getFullYear() + '-' + pad(dt.getMonth()+1) + '-' + pad(dt.getDate()) +
            ' ' + pad(dt.getHours()) + ':' + pad(dt.getMinutes()) + ':00';

        try {
            const res = await fetch("<?= site_url('panel/calendar') ?>", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
            body: new URLSearchParams({
                '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                publish_id: info.event.id,
                schedule_at: scheduleAt
            })
            });

            const json = await res.json().catch(() => null);

            if (!res.ok || !json || json.ok !== true) {
            info.revert();
            alert((json && json.message) ? json.message : 'Güncelleme başarısız.');
            return;
            }

            if (json.status) {
            info.event.setExtendedProp('status', json.status);
            }
            if (json.statusLabel) {
            info.event.setExtendedProp('statusLabel', json.statusLabel);
            }

        } catch (e) {
            info.revert();
            alert('Bağlantı hatası.');
        }
    }
  });

  calendar.render();
});
</script>

<?= $this->endSection() ?>
