<?php helper('url'); ?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Sosyal Medya Paneli</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Panel CSS (harici dosya) -->
    <link href="<?= base_url('/assets/css/panel.css'); ?>" rel="stylesheet">
    <style>
        .spin { display:inline-block; animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }
    </style>
</head>
<?php $uri = service('uri'); ?>
<body>
<div class="layout">
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <aside id="sidebar" class="sidebar">
        <div class="brand mb-3">
            <div class="brand-logo">S</div>
            <div>
                <div class="brand-title">Sosyal Medya Planlama</div>
                <div class="brand-sub">Planla • Yayınla • Yönet</div>
            </div>
        </div>

        <nav class="nav flex-column side-nav">
            <?= $this->include('partials/panel_sidebar') ?>
        </nav>

        <div class="side-footer mt-3">
            <div>Aktif kullanıcı:</div>
            <div class="fw-semibold"><?= esc(session('user_email') ?? ''); ?></div>
        </div>
    </aside>
    <?php
    $headerVariant = $headerVariant ?? 'compact'; 
    ?>
    <main class="main">
        <div class="topbar">
            <div class="top-left">
                <button id="mobileMenuBtn" class="mobile-menu-btn">
                <i class="bi bi-list"></i>
                </button>

                <?php if ($headerVariant === 'dashboard'): ?>
                <div>
                    <div class="top-title"><?= esc($pageTitle ?? 'Gösterge Paneli'); ?></div>
                    <div class="top-sub"><?= esc($pageSubtitle ?? 'Planlanmış içeriklerin, hesapların ve akışın genel görünümü.'); ?></div>
                </div>
                <?php else: ?>
                <div class="d-flex flex-column">
                    <div class="small text-muted" style="line-height:1.1;">Panel</div>
                    <div class="fw-semibold" style="line-height:1.1; font-size: 14px;">
                    <?= esc($pageTitle ?? ''); ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="chip d-none d-md-inline-flex">
                <i class="bi bi-check-circle me-1"></i> Geliştirme Ortamı
                </span>
                <form action="<?= site_url('auth/logout'); ?>" method="post" class="m-0">
                <?= csrf_field() ?>
                <button type="submit" class="btn-ghost">
                    <i class="bi bi-box-arrow-right me-1"></i> Çıkış
                </button>
                </form>             
            </div>
            </div>

        <?= $this->renderSection('content') ?>
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mobileMenuBtn = document.getElementById('mobileMenuBtn');

    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            sidebarOverlay.classList.toggle('show');
        });
    }

    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            sidebarOverlay.classList.remove('show');
        });
    }
</script>

<!-- Global Confirm Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" style="border-radius:16px;">
      <div class="modal-header">
        <h5 class="modal-title" id="confirmModalTitle">Emin misin?</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">
        Bu işlemi yapmak istediğine emin misin?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
        <button type="button" class="btn btn-danger" id="confirmModalOk">Evet, devam et</button>
      </div>
    </div>
  </div>
</div>

<script>
(function () {
  const modalEl = document.getElementById('confirmModal');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  const okBtn = document.getElementById('confirmModalOk');
  const titleEl = document.getElementById('confirmModalTitle');
  const bodyEl = document.getElementById('confirmModalBody');

  let pendingForm = null;

  document.addEventListener('click', function (e) {
  const el = e.target.closest('[data-confirm]');
  if (!el) return;

  // Buton değil form'a konduysa da çalışsın:
  const form = el.tagName === 'FORM' ? el : el.closest('form');
  if (!form) return;

  // Eğer bu bir submit butonu ise submit'i kesin engelle
  e.preventDefault();
  e.stopPropagation();

  pendingForm = form;

  const title = el.getAttribute('data-confirm-title') || 'Emin misin?';
  const body  = el.getAttribute('data-confirm') || 'Bu işlemi yapmak istediğine emin misin?';
  const okText = el.getAttribute('data-confirm-ok') || 'Evet, devam et';
  const okClass = el.getAttribute('data-confirm-ok-class') || 'btn-danger';

  titleEl.textContent = title;
  bodyEl.textContent = body;

  okBtn.textContent = okText;
  okBtn.className = 'btn ' + okClass;

  modal.show();
}, true);

  okBtn.addEventListener('click', function () {
    if (pendingForm) pendingForm.submit();
  });
})();
</script>

</body>
</html>
