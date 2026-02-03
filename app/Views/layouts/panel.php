<?php helper('url'); ?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Sosyal Medya Planlama</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Panel CSS -->
    <link rel="icon" type="image/png" href="<?= base_url('/panellogo.png'); ?>">
    <link href="<?= base_url('/assets/css/panel.css'); ?>" rel="stylesheet">

    <style>
        :root{
            --ink: rgba(17,24,39,.88);
            --muted: rgba(17,24,39,.58);
            --border: rgba(0,0,0,.06);
            --bg: #f6f7fb;
            --primary: #7c3aed;
            --primarySoft: rgba(124,58,237,.08);
            --radius: 16px;
        }

        body{
            font-family: Inter, system-ui, -apple-system, Segoe UI, Roboto, Arial, sans-serif;
            color: var(--ink);
            background: var(--bg);
            font-size: 14px;
        }

        a{ text-decoration:none !important; }

        .spin { display:inline-block; animation: spin 1s linear infinite; }
        @keyframes spin { from { transform: rotate(0deg);} to { transform: rotate(360deg);} }

        .brand{ display:flex; align-items:center; gap:10px; }
        .brand-logo-img{
            width:40px; height:40px; border-radius:12px;
            display:block; object-fit:contain;
            background:#fff;
            box-shadow: 0 10px 24px rgba(0,0,0,.05);
            border: 1px solid var(--border);
            padding:6px;
        }
        .brand-title{ font-weight:700; letter-spacing:-.2px; font-size:14px; }
        .brand-sub{ color: var(--muted); font-weight:500; font-size:12px; }

        .topbar{
            position: sticky;
            top: 0;
            z-index: 50;
            backdrop-filter: blur(10px);
            background: rgba(255,255,255,.72);
            border-bottom: 1px solid var(--border);
            padding: 10px 14px;
        }
        .top-title{
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -.3px;
            color: rgba(17,24,39,.86);
        }
        .top-sub{
            font-size: 13px;
            color: var(--muted);
            font-weight: 500;
        }

        .chip{
            border: 1px solid rgba(124,58,237,.16);
            background: var(--primarySoft);
            color: rgba(124,58,237,.92);
            border-radius: 999px;
            padding: 6px 10px;
            font-weight: 600;
            font-size: 12px;
        }

        .btn-ghost{
            border: 1px solid var(--border);
            background: #fff;
            border-radius: 999px;
            padding: 7px 10px;
            font-weight: 600;
            font-size: 13px;
            color: rgba(17,24,39,.78);
        }
        .btn-ghost:hover{
            background: rgba(124,58,237,.06);
            border-color: rgba(124,58,237,.14);
            color: rgba(17,24,39,.86);
        }

        /* ✅ FIX: Modal’ın sidebar/topbar altında kalmasını engelle */
        .modal{ z-index: 99999 !important; }
        .modal-backdrop{ z-index: 99998 !important; }

        /* ✅ FIX: Sol overlay’in de modal backdrop’undan yukarı çıkmasını engelle */
        .sidebar-overlay{ z-index: 1000; } /* zaten genelde 999 olur, modal 99998 üstte */
    </style>

    <!-- ✅ FIX: Sayfa özel style section (help sayfası kendi CSS'ini buraya basabilir) -->
    <?= $this->renderSection('styles') ?>
</head>

<?php
  $uri = service('uri');
  $headerVariant = $headerVariant ?? 'compact';

  $env = defined('ENVIRONMENT') ? ENVIRONMENT : 'production';
  $showEnvChip = ($env !== 'production');
  $envLabel = $showEnvChip ? 'Test Ortamı' : '';
?>

<body>
<div class="layout">
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <aside id="sidebar" class="sidebar">
        <div class="brand mb-3">
            <img class="brand-logo-img" src="<?= base_url('panellogo.png') ?>" alt="Logo">
            <div>
                <div class="brand-title">Sosyal Medya Planlama</div>
                <div class="brand-sub">Planla • Tasarla • Yayınla</div>
            </div>
        </div>

        <nav class="nav flex-column side-nav">
            <?= $this->include('partials/panel_sidebar') ?>
        </nav>

        <div class="side-footer mt-3">
            <div class="text-muted small">Aktif kullanıcı</div>
            <div class="fw-semibold" style="color:rgba(17,24,39,.82)"><?= esc(session('user_email') ?? '') ?></div>
        </div>
    </aside>

    <main class="main">
        <div class="topbar">
            <div class="top-left">
                <button id="mobileMenuBtn" class="mobile-menu-btn" type="button">
                    <i class="bi bi-list"></i>
                </button>

                <?php if ($headerVariant === 'dashboard'): ?>
                    <div>
                        <div class="top-title"><?= esc($pageTitle ?? 'Gösterge Paneli'); ?></div>
                        <div class="top-sub"><?= esc($pageSubtitle ?? 'Planlanan gönderiler, hesaplar ve son işlemler.'); ?></div>
                    </div>
                <?php else: ?>
                    <div class="d-flex flex-column">
                        <div class="small text-muted" style="line-height:1.1;">Panel</div>
                        <div class="fw-semibold" style="line-height:1.1; font-size: 13px; color:rgba(17,24,39,.78)">
                            <?= esc($pageTitle ?? '') ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if ($showEnvChip): ?>
                    <span class="chip d-none d-md-inline-flex">
                        <i class="bi bi-shield-check me-1"></i> <?= esc($envLabel) ?>
                    </span>
                <?php endif; ?>

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

<!-- ✅ Bootstrap JS (tek kere) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Sidebar toggle -->
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

    const form = el.tagName === 'FORM' ? el : el.closest('form');
    if (!form) return;

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

<div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:2000">
  <div id="appToast" class="toast align-items-center" role="alert" aria-live="assertive" aria-atomic="true">
    <div class="d-flex">
      <div id="appToastBody" class="toast-body"></div>
      <button type="button" class="btn-close me-2 m-auto" data-bs-dismiss="toast" aria-label="Kapat"></button>
    </div>
  </div>
</div>

<script>
window.appToast = function(message, variant='success'){
  const el = document.getElementById('appToast');
  const body = document.getElementById('appToastBody');
  if (!el || !body) return alert(message);

  el.className = 'toast align-items-center text-bg-' + (variant === 'error' ? 'danger' : variant);
  body.textContent = message;

  const t = bootstrap.Toast.getOrCreateInstance(el, { delay: 2500 });
  t.show();
}
</script>

<!-- ✅ FIX: Sayfa özel script section (help sayfasının modal + copy JS'i burada çalışacak) -->
<?= $this->renderSection('scripts') ?>

</body>
</html>
