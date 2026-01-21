<?php helper('url'); ?>
<?php $uri = service('uri'); ?>

<?php
// Güvenli: segment yoksa bile hata vermez
$segments = $uri->getSegments();   // örn: ['admin','publishes','create']
$seg1 = $segments[0] ?? '';
$seg2 = $segments[1] ?? '';
$seg3 = $segments[2] ?? '';
?>

<nav class="nav flex-column side-nav">
  <div class="nav-section-label">Genel</div>

  <a href="<?= site_url('admin'); ?>"
     class="nav-link <?= ($seg1 === 'admin' && $seg2 === '') ? 'active' : '' ?>">
    <i class="bi bi-speedometer2"></i> <span>Gösterge Paneli</span>
  </a>

  <div class="nav-section-label">Yönetim</div>

  <a href="<?= site_url('admin/users'); ?>"
     class="nav-link <?= ($seg1 === 'admin' && $seg2 === 'users') ? 'active' : '' ?>">
    <i class="bi bi-people"></i> <span>Kullanıcılar</span>
  </a>

  <a href="<?= site_url('admin/publishes/create') ?>"
     class="nav-link <?= ($seg1 === 'admin' && $seg2 === 'publishes' && $seg3 === 'create') ? 'active' : '' ?>">
    <span class="me-2">🗓️</span> Planlı Paylaşım Oluştur
  </a>

  <a href="<?= site_url('admin/publishes') ?>"
     class="nav-link <?= ($seg1 === 'admin' && $seg2 === 'publishes' && $seg3 !== 'create') ? 'active' : '' ?>">
    <span class="me-2">📤</span> Paylaşımlar
  </a>

  <a href="<?= site_url('admin/jobs') ?>"
     class="nav-link <?= ($seg1 === 'admin' && $seg2 === 'jobs') ? 'active' : '' ?>">
    <span class="me-2">🧾</span> Planlı İşler
  </a>

  <a href="<?= site_url('admin/logs') ?>"
     class="nav-link <?= ($seg1 === 'admin' && $seg2 === 'logs') ? 'active' : '' ?>">
    <i class="bi bi-journal-text"></i> <span>İşlem Geçmişi</span>
  </a>

  <a href="<?= site_url('admin/templates'); ?>"
    class="nav-link <?= ($seg1 === 'admin' && $seg2 === 'templates') ? 'active' : '' ?>">
    <span class="me-2">🧩</span> Hazır Şablonlar
  </a>

  <a href="#" class="nav-link">
    <i class="bi bi-gear"></i> <span>Sistem Ayarları</span>
  </a>

  <div class="nav-section-label">Geçiş</div>

  <a href="<?= site_url('panel'); ?>" class="nav-link">
    <i class="bi bi-box-arrow-in-right"></i> <span>Kullanıcı Paneli</span>
  </a>
</nav>
