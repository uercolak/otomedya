<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  .st-card{ border:1px solid rgba(0,0,0,.06); border-radius:18px; background:#fff; }
  .st-head{ padding:14px 14px 10px 14px; display:flex; align-items:flex-start; justify-content:space-between; gap:10px; }
  .st-title{ font-size:28px; letter-spacing:-.4px; font-weight:900; margin:0; }
  .st-sub{ color: rgba(0,0,0,.62); max-width: 780px; }
  .st-body{ padding: 0 14px 14px 14px; }
  .btn-soft{
    border-radius:999px; border:1px solid rgba(0,0,0,.10);
    background:#fff; font-weight:800;
  }
  .btn-soft:hover{ background: rgba(0,0,0,.03); }
  .st-section{ border:1px solid rgba(0,0,0,.06); border-radius:16px; padding:14px; }
  .st-section h5{ font-weight:900; margin:0 0 6px 0; }
  .st-muted{ color: rgba(0,0,0,.60); font-size: 13px; }
</style>

<div class="st-card">
  <div class="st-head">
    <div>
      <h1 class="st-title">Ayarlar</h1>
      <div class="st-sub">
        Hesap güvenliği ve giriş ayarlarını buradan yönetebilirsin.
      </div>
    </div>

    <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-soft btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Geri
    </a>
  </div>

  <div class="st-body">

    <?php if (session()->getFlashdata('success')): ?>
      <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
      <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3">
      <!-- Şifre Değiştir -->
      <div class="col-lg-7">
        <div class="st-section">
          <h5>Şifre Değiştir</h5>
          <div class="st-muted mb-3">
            Güvenlik için en az 8 karakter, mümkünse harf + rakam kombinasyonu kullan.
          </div>

          <form method="post" action="<?= site_url('panel/settings/password') ?>" class="row g-3">
            <?= csrf_field() ?>

            <div class="col-12">
              <label class="form-label fw-semibold">Mevcut Şifre</label>
              <input type="password" name="current_password" class="form-control" required autocomplete="current-password">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Yeni Şifre</label>
              <input type="password" name="new_password" class="form-control" required minlength="8" autocomplete="new-password">
            </div>

            <div class="col-md-6">
              <label class="form-label fw-semibold">Yeni Şifre (Tekrar)</label>
              <input type="password" name="new_password2" class="form-control" required minlength="8" autocomplete="new-password">
            </div>

            <div class="col-12 d-flex gap-2 justify-content-end">
              <button type="submit" class="btn btn-primary" style="border-radius:999px; font-weight:900;">
                <i class="bi bi-shield-lock me-1"></i> Şifreyi Güncelle
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- 2FA (Yakında) -->
      <div class="col-lg-5">
        <div class="st-section h-100">
          <h5>İki Aşamalı Doğrulama (2FA)</h5>
          <div class="st-muted mb-3">
            Google Authenticator / Microsoft Authenticator ile ekstra güvenlik katmanı.
          </div>

          <div class="alert alert-warning mb-0">
            <strong>Yakında:</strong> 2FA eklemesi kısa süre içerisinde aktif olacaktır.
            (QR kod, doğrulama, yedek kodlar)
          </div>
        </div>
      </div>

    </div>

  </div>
</div>

<?= $this->endSection() ?>
