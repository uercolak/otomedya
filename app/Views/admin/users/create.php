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
              <label class="form-label">Ad Soyad</label>
              <input name="name" class="form-control" value="<?= old('name') ?>" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">E-posta</label>
              <input type="email" name="email" class="form-control" value="<?= old('email') ?>" required>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Rol</label>
              <select name="role" class="form-select" required>
                <option value="user"  <?= old('role')==='user'  ? 'selected' : '' ?>>User</option>
                <option value="admin" <?= old('role')==='admin' ? 'selected' : '' ?>>Admin</option>
              </select>
              <div class="form-text">Admin rolü kullanıcı yönetimine erişir.</div>
            </div>

            <div class="col-12 col-md-6">
              <label class="form-label">Durum</label>
              <select name="status" class="form-select">
                <option value="active"   <?= old('status','active')==='active' ? 'selected' : '' ?>>Aktif</option>
                <option value="inactive" <?= old('status')==='inactive' ? 'selected' : '' ?>>Pasif</option>
              </select>
              <div class="form-text">Pasif kullanıcı giriş yapamaz.</div>
            </div>
          </div>

          <div class="alert alert-info mt-3 mb-0">
            <div class="fw-semibold">Yetki</div>
            <div class="small">Rolü <b>Admin</b> yaparsanız tüm sistem yetkilerini kazanır.</div>
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

<?= $this->endSection() ?>
