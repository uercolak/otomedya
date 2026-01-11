<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="page-title">Instagram Bağlama Sihirbazı</div>
    <div class="text-muted">Meta üzerinden güvenli şekilde bağlanıp Instagram Business/Creator hesabını seç.</div>
  </div>
  <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:999px;">
    <i class="bi bi-arrow-left me-1"></i> Geri
  </a>
</div>

<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card-soft p-3">
  <div class="fw-semibold mb-2">Yetkilendirme & Onay</div>

  <div class="text-muted small mb-3">
    Devam ettiğinde Meta (Facebook/Instagram) hesabın üzerinden gerekli izinleri vereceksin.
    Bu işlem planlama/yayınlama için gereklidir.
  </div>

  <form method="post" action="<?= site_url('panel/social-accounts/meta/connect') ?>">
    <?= csrf_field() ?>

    <div class="form-check mb-2">
      <input class="form-check-input" type="checkbox" value="1" id="consent" name="consent">
      <label class="form-check-label" for="consent">
        Gizlilik Politikası ve Hizmet Koşullarını okudum, Meta hesabımı bağlamayı kabul ediyorum.
      </label>
    </div>

    <div class="form-check mb-3">
      <input class="form-check-input" type="checkbox" value="1" id="delegate" name="delegate">
      <label class="form-check-label" for="delegate">
        Gerekirse kurulum sürecinde temsilcimizin benim adıma ayarları yapmasına izin veriyorum.
      </label>
      <div class="text-muted small mt-1">
        (Bu bir “izin kaydıdır”. İstersen ileride bunu kullanıcı ayarlarına/log’a da yazarız.)
      </div>
    </div>

    <button class="btn btn-primary btn-sm" style="border-radius:999px; background: linear-gradient(135deg,#6366f1,#ec4899); border:none;">
      <i class="bi bi-link-45deg me-1"></i> Meta ile Bağlanmayı Başlat
    </button>
  </form>
</div>

<?= $this->endSection() ?>
