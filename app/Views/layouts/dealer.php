<!doctype html>
<html lang="tr">
<head>
  <meta charset="utf-8">
  <title><?= esc($pageTitle ?? 'Dealer Panel') ?> | Sosyal Medya Planlama</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="icon" type="image/png" href="<?= base_url('/logo2.png'); ?>">

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">

  <style>
    body { background:#f6f7fb; }
    .sidebar{
      width:260px; min-height:100vh; background:#fff; border-right:1px solid rgba(0,0,0,.06);
      position:sticky; top:0;
    }
    .brand{
      padding:18px 16px; border-bottom:1px solid rgba(0,0,0,.06);
      display:flex; align-items:center; gap:10px;
    }
    .brand img{ height:34px; width:auto; }
    .nav-link{
      color:#0f172a; border-radius:12px; padding:.65rem .75rem;
    }
    .nav-link:hover{ background:rgba(124,58,237,.06); }
    .nav-link.active{ background:rgba(124,58,237,.10); color:#5b21b6; font-weight:600; }
    .topbar{
      background:#fff; border-bottom:1px solid rgba(0,0,0,.06);
      padding:12px 18px; border-radius:16px;
    }
    .content-wrap{ padding:18px; }
    .card{ border-radius:16px; }
    .shadow-soft{ box-shadow: 0 10px 30px rgba(17,24,39,.06) !important; }
  </style>
</head>
<body>

<div class="d-flex">
  <!-- Sidebar -->
  <aside class="sidebar p-3">
    <div class="brand">
      <img src="<?= base_url('/logo.png'); ?>" alt="Sosyal Medya Planlama">
      <div class="lh-sm">
        <div class="fw-bold">Sosyal Medya Planlama</div>
        <div class="text-muted small">Dealer Paneli</div>
      </div>
    </div>

    <div class="mt-3">
      <div class="text-uppercase text-muted small px-2 mb-2" style="letter-spacing:.08em;">GENEL</div>

      <?php $uri = service('uri')->getPath(); ?>
      <a class="nav-link <?= str_starts_with($uri, 'dealer') && ($uri === 'dealer' || $uri === 'dealer/') ? 'active' : '' ?>"
         href="<?= base_url('dealer') ?>">
        <i class="bi bi-speedometer2 me-2"></i> Gösterge Paneli
      </a>

      <div class="text-uppercase text-muted small px-2 mt-3 mb-2" style="letter-spacing:.08em;">YÖNETİM</div>

      <a class="nav-link <?= str_starts_with($uri, 'dealer/users') ? 'active' : '' ?>"
         href="<?= base_url('dealer/users') ?>">
        <i class="bi bi-people me-2"></i> Kullanıcılar
      </a>

      <hr class="my-3">

      <a class="nav-link text-danger" href="<?= base_url('auth/logout') ?>">
        <i class="bi bi-box-arrow-right me-2"></i> Çıkış
      </a>

      <div class="mt-4 small text-muted">
        <div><b>Aktif:</b> <?= esc(session('user_email') ?? '-') ?></div>
        <div><b>Tenant:</b> <?= esc(session('tenant_id') ?? '-') ?></div>
      </div>
    </div>
  </aside>

  <!-- Main -->
  <main class="flex-grow-1">
    <div class="content-wrap">
      <div class="topbar d-flex align-items-center justify-content-between mb-3">
        <div>
          <div class="fw-semibold"><?= esc($pageTitle ?? 'Dealer Panel') ?></div>
          <?php if (!empty($pageSubtitle)): ?>
            <div class="text-muted small"><?= esc($pageSubtitle) ?></div>
          <?php endif; ?>
        </div>

        <div class="d-flex align-items-center gap-2">
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-shield-check me-1"></i> dealer
          </span>
          <a class="btn btn-outline-secondary btn-sm" href="<?= base_url('auth/logout') ?>">
            <i class="bi bi-box-arrow-right me-1"></i> Çıkış
          </a>
        </div>
      </div>

      <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success d-flex align-items-center gap-2 shadow-soft">
          <i class="bi bi-check-circle"></i>
          <div><?= esc(session()->getFlashdata('success')) ?></div>
        </div>
      <?php endif; ?>

      <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger d-flex align-items-center gap-2 shadow-soft">
          <i class="bi bi-exclamation-triangle"></i>
          <div><?= esc(session()->getFlashdata('error')) ?></div>
        </div>
      <?php endif; ?>

      <?= $this->renderSection('content') ?>
    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<?= $this->renderSection('scripts') ?>
</body>
</html>
