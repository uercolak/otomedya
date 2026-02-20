<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-start mb-3">
  <div>
    <h4 class="mb-1">Yeni Kullanıcı</h4>
    <div class="text-muted">Yeni kullanıcı oluşturun ve sisteme erişim verin.</div>
  </div>

  <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i> Listeye Dön
  </a>
</div>

<?php $errors = session()->getFlashdata('errors') ?? []; ?>
<?php if (!empty($errors)): ?>
  <div class="alert alert-danger">
    <div class="fw-semibold mb-1">Hata</div>
    <ul class="mb-0">
      <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form action="<?= base_url('admin/users') ?>" method="post">
  <?= csrf_field() ?>

  <div class="row g-3">
    <!-- Temel Bilgiler -->
    <div class="col-12 col-lg-8">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="fw-semibold mb-3">Temel Bilgiler</div>

          <div class="row g-3">
            <div class="col-12 col-md-6">
              <label class="form-label">Ad Soyad / Şirket İsmi</label>
              <input name="name" class="form-control" value="<?= old('name') ?>" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">E-posta</label>
              <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
            </div>

            <div class="col-12 col-md-6">
            <label class="form-label">Rol</label>

            <?php $roleOld = old('role', 'user'); ?>
            <select name="role" id="roleSelect" class="form-select" required>
                <option value="user"   <?= $roleOld === 'user' ? 'selected' : '' ?>>Kullanıcı</option>
                <option value="dealer" <?= $roleOld === 'dealer' ? 'selected' : '' ?>>Bayi</option>
            </select>

            <div class="form-text">
                Bayi seçersen bu kullanıcı bayi paneline girebilir ve kendi alt kullanıcılarını oluşturabilir.
            </div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Durum</label>
              <select name="status" class="form-select">
                <option value="active"   <?= old('status','active')==='active' ? 'selected' : '' ?>>Aktif</option>
                <option value="passive" <?= old('status')==='passive' ? 'selected' : '' ?>>Pasif</option>
              </select>
              <div class="form-text">Pasif kullanıcı giriş yapamaz.</div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Güvenlik -->
    <div class="col-12 col-lg-4">
      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <div class="fw-semibold mb-3">Güvenlik</div>

          <div class="mb-3">
            <label class="form-label">Şifre</label>
            <input type="password" name="password" class="form-control" required minlength="6">
            <div class="form-text">En az 6 karakter.</div>
          </div>

          <div class="mb-0">
            <label class="form-label">Şifre (Tekrar)</label>
            <input type="password" name="password_confirm" class="form-control" required minlength="6">
          </div>
        </div>
      </div>

      <div class="d-flex justify-content-end gap-2 mt-3">
        <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">Vazgeç</a>
        <button class="btn btn-primary">
          <i class="bi bi-plus-lg me-1"></i> Kullanıcı Oluştur
        </button>
      </div>
    </div>
  </div>
</form>
<script>
  (function(){
    const role = document.getElementById('roleSelect');
    const tenantWrap = document.getElementById('tenantWrap');

    function sync(){
      const v = role.value;
      // user ve dealer seçilince tenant göster (çünkü controller zorunlu tutuyor)
      tenantWrap.style.display = (v === 'user' || v === 'dealer') ? '' : 'none';
    }

    role.addEventListener('change', sync);
    sync();
  })();
</script>
<?= $this->endSection() ?>
