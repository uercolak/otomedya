<?php helper('url'); ?>
<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title><?= esc($pageTitle ?? 'Bayi Paneli'); ?> - Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/png" href="<?= base_url('/panellogo.png'); ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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
    .chip-dealer{
      border: 1px solid rgba(124,58,237,.16);
      background: var(--primarySoft);
      color: rgba(124,58,237,.92);
      border-radius: 999px;
      padding: 6px 10px;
      font-weight: 700;
      font-size: 12px;
    }

    .modal{ z-index: 99999 !important; }
    .modal-backdrop{ z-index: 99998 !important; }
    .sidebar-overlay{ z-index: 1000; }

    <?= $this->renderSection('styles') ?>
  </style>
</head>
<body>

<div class="layout">
  <div id="sidebarOverlay" class="sidebar-overlay"></div>

  <aside id="sidebar" class="sidebar">
    <div class="brand mb-3">
      <div class="brand-logo">S</div>
      <div>
        <div class="brand-title">Sosyal Medya Planlama</div>
        <div class="brand-sub">Bayi Paneli</div>
      </div>
    </div>

    <nav class="nav flex-column side-nav">
      <?= $this->include('partials/dealer_sidebar') ?>
    </nav>

    <div class="side-footer mt-3">
      <div class="text-muted small">Aktif bayi</div>
      <div class="fw-semibold" style="color:rgba(17,24,39,.82)"><?= esc(session('user_email') ?? '') ?></div>
    </div>
  </aside>

  <main class="main">
    <div class="topbar">
      <div class="top-left">
        <button id="mobileMenuBtn" class="mobile-menu-btn" type="button">
          <i class="bi bi-list"></i>
        </button>

        <div class="d-flex flex-column">
          <div class="small text-muted" style="line-height:1.1;">Bayi</div>
          <div class="fw-semibold" style="line-height:1.1; font-size: 13px; color:rgba(17,24,39,.78)">
            <?= esc($pageTitle ?? '') ?>
          </div>
        </div>
      </div>

      <div class="d-flex align-items-center gap-2">
        <span class="chip-dealer d-none d-md-inline-flex">
          <i class="bi bi-briefcase me-1"></i> Bayi
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

<?= $this->renderSection('scripts') ?>
</body>
</html>
