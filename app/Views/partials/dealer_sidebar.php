<a class="nav-link <?= url_is('dealer') ? 'active' : '' ?>" href="<?= base_url('dealer') ?>">
  <i class="bi bi-speedometer2 me-2"></i> Gösterge Paneli
</a>

<a class="nav-link <?= url_is('dealer/users*') ? 'active' : '' ?>" href="<?= base_url('dealer/users') ?>">
  <i class="bi bi-people me-2"></i> Kullanıcılarım
</a>

<a class="nav-link <?= url_is('dealer/publishes*') ? 'active' : '' ?>" href="<?= base_url('dealer/publishes') ?>">
  <i class="bi bi-send me-2"></i> Paylaşımlar
</a>
