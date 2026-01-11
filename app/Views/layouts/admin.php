<?php helper('url'); ?>
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title><?= esc($pageTitle ?? 'Admin Panel'); ?> - Otomedya</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <?= $this->renderSection('scripts') ?>

    <link href="<?= base_url('/assets/css/panel.css'); ?>" rel="stylesheet">

    <style>
        .brand-logo { background: linear-gradient(135deg, #ff5a5f, #ff8a00); }
        .chip-admin {
            background: rgba(255,90,95,.12);
            border: 1px solid rgba(255,90,95,.25);
            color: #ff5a5f;
        }
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
            <div class="brand-logo">A</div>
            <div>
                <div class="brand-title">Otomedya</div>
                <div class="brand-sub">Admin Panel</div>
            </div>
        </div>

        <nav class="nav flex-column side-nav">
            <?= $this->include('partials/admin_sidebar') ?>
        </nav>

        <div class="side-footer mt-3">
            <div>Giriş yapan:</div>
            <div class="fw-semibold"><?= esc(session('user_email') ?? ''); ?></div>
        </div>
    </aside>

    <main class="main" style="overflow-x:hidden;">
        <div class="topbar">
            <div class="top-left">
                <button id="mobileMenuBtn" class="mobile-menu-btn">
                    <i class="bi bi-list"></i>
                </button>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="chip chip-admin d-none d-md-inline-flex">
                    <i class="bi bi-shield-lock me-1"></i> Admin
                </span>

                <form action="<?= base_url('auth/logout'); ?>" method="post" class="m-0">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn-ghost">
                        <i class="bi bi-box-arrow-right me-1"></i> Çıkış
                    </button>
                </form>
            </div>
        </div>

        <?= $this->renderSection('content') ?>

        <?php
        $toastSuccess = session()->getFlashdata('success');
        $toastError   = session()->getFlashdata('error');
        ?>

        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:1100;">
        <?php if ($toastSuccess): ?>
            <div class="toast align-items-center text-bg-success border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="2500">
            <div class="d-flex">
                <div class="toast-body">
                <i class="bi bi-check-circle me-1"></i> <?= esc($toastSuccess) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            </div>
        <?php endif; ?>

        <?php if ($toastError): ?>
            <div class="toast align-items-center text-bg-danger border-0" role="alert" aria-live="assertive" aria-atomic="true" data-bs-delay="3500">
            <div class="d-flex">
                <div class="toast-body">
                <i class="bi bi-exclamation-triangle me-1"></i> <?= esc($toastError) ?>
                </div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
            </div>
        <?php endif; ?>
        </div>
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
<script>
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.toast').forEach(el => new bootstrap.Toast(el).show());
});
</script>
</body>
</html>
