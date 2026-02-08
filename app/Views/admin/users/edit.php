<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-end mb-3">
  <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
    <i class="bi bi-arrow-left me-1"></i> Listeye Dön
  </a>
</div>

<form action="<?= base_url('admin/users/' . (int)$user['id']) ?>" method="post" id="editUserForm">
  <?= csrf_field() ?>

  <div class="row g-3">
    <!-- Temel bilgiler -->
    <div class="col-lg-7">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="d-flex align-items-center justify-content-between mb-3">
            <div class="fw-semibold">Temel Bilgiler</div>
            <span class="badge rounded-pill text-bg-light">
              ID: <?= (int)$user['id'] ?>
            </span>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Ad Soyad</label>
              <input type="text" name="name" class="form-control"
                     value="<?= esc(old('name', $user['name'] ?? '')) ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">E-posta</label>
              <input type="email" name="email" class="form-control"
                     value="<?= esc(old('email', $user['email'] ?? '')) ?>" required>
            </div>

            <div class="col-md-6">
              <label class="form-label">Rol</label>
              <?php $role = old('role', $user['role'] ?? 'user'); ?>
              <select name="role" class="form-select" disabled>
                <option value="user"  <?= $role === 'user' ? 'selected' : '' ?>>Kullanıcı</option>
              </select>

              <div class="alert alert-info d-flex gap-2 mt-3 mb-0" style="border-radius:14px;">
                <i class="bi bi-info-circle mt-1"></i>
                <div class="small">
                  <div class="fw-semibold">Dikkat</div>
                  <div>Rolü : <b>Admin</b> yaparsanız tüm sistem yetkilerini kazanır.</div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
                <label class="form-label">Durum</label>
                <?php $status = old('status', $user['status'] ?? 'active'); ?>
                <select name="status" class="form-select" required>
                    <option value="active"   <?= $status === 'active' ? 'selected' : '' ?>>Aktif</option>
                    <option value="disabled" <?= $status === 'disabled' ? 'selected' : '' ?>>Pasif</option>
                </select>
            </div>

            <div class="col-md-6">
              <label class="form-label">Oluşturma</label>
              <input type="text" class="form-control" disabled
                     value="<?= esc($user['created_at'] ?? '-') ?>">
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Güvenlik -->
    <div class="col-lg-5">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body p-4">
          <div class="fw-semibold mb-3">Güvenlik</div>

          <div class="mb-3">
            <label class="form-label">Yeni Şifre (opsiyonel)</label>
            <input type="password" name="password" class="form-control"
                   placeholder="Boş bırak: değişmez" autocomplete="new-password">
          </div>

          <div class="mb-0">
            <label class="form-label">Yeni Şifre (Tekrar)</label>
            <input type="password" name="password_confirm" class="form-control"
                   placeholder="Boş bırak: değişmez" autocomplete="new-password">
          </div>

          <div class="text-muted small mt-3">
            Şifreyi değiştirmek istemiyorsan iki alanı da boş bırak.
          </div>
        </div>
      </div>

      <!-- Aksiyon bar -->
      <div class="card border-0 shadow-sm mt-3" style="border-radius:16px;">
        <div class="card-body d-flex gap-2 justify-content-end p-3">
          <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-secondary">
            Vazgeç
          </a>
          <button type="submit" class="btn btn-primary" id="saveBtn">
            <i class="bi bi-check2-circle me-1"></i> Kaydet
          </button>
        </div>
      </div>

    </div>
  </div>
</form>

<?= $this->endSection() ?>
