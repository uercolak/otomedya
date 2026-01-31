<?php helper('url'); ?>
<?php $uri = service('uri'); ?>

<?php
  $segments = $uri->getSegments();
  $seg1 = $segments[0] ?? '';
  $seg2 = $segments[1] ?? '';
  $seg3 = $segments[2] ?? '';
?>

<nav class="nav flex-column side-nav">

  <div class="nav-section-label">TikTok</div>

  <a href="<?= site_url('panel/social-accounts'); ?>"
     class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'social-accounts') ? 'active' : '' ?>">
    <i class="bi bi-music-note-beamed"></i>
    <span>TikTok Bağlantısı</span>
  </a>

  <a href="<?= site_url('panel/planner'); ?>"
     class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'planner') ? 'active' : '' ?>">
    <i class="bi bi-plus-circle"></i>
    <span>Post to TikTok</span>
  </a>

  <a href="<?= site_url('panel/publishes'); ?>"
     class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'publishes') ? 'active' : '' ?>">
    <span class="me-2">📤</span>
    <span>Paylaşımlar</span>
  </a>

  <div class="nav-section-label">Güvenlik</div>

  <a href="<?= site_url('panel/settings'); ?>"
     class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'settings') ? 'active' : '' ?>">
    <i class="bi bi-gear"></i>
    <span>Ayarlar</span>
  </a>

  <div class="nav-section-label">Destek</div>

  <a href="<?= site_url('panel/help/account-linking'); ?>"
     class="nav-link <?= ($seg1 === 'panel' && $seg2 === 'help' && $seg3 === 'account-linking') ? 'active' : '' ?>">
    <i class="bi bi-question-circle"></i>
    <span>Yardım / Hesap Bağlama</span>
  </a>

  <?php if (session('user_role') === 'admin'): ?>
    <div class="nav-section-label">Admin</div>

    <a href="<?= site_url('admin/users'); ?>"
       class="nav-link <?= ($seg1 === 'admin' && ($seg2 ?? '') === 'users') ? 'active' : '' ?>">
      <i class="bi bi-people"></i>
      <span>Kullanıcılar</span>
    </a>
  <?php endif; ?>

</nav>
