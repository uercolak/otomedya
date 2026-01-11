<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="page-title">Instagram Hesabı Seç</div>
    <div class="text-muted">Bağlı Facebook Page üzerinden gelen Instagram Business/Creator hesabını seçip kaydet.</div>
  </div>
  <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:999px;">
    <i class="bi bi-x-lg me-1"></i> Kapat
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (empty($items)): ?>
  <div class="alert alert-warning">
    <div class="fw-semibold mb-1">Bağlanabilir Instagram hesabı bulunamadı.</div>
    <div class="small text-muted">
      Instagram hesabının <b>Business/Creator</b> olması ve bir <b>Facebook Page</b>’e bağlı olması gerekir.
    </div>
  </div>

  <div class="card-soft p-3">
    <div class="fw-semibold mb-2">Adım adım çöz</div>
    <ol class="small text-muted mb-3">
      <li>Facebook’ta bir <b>Page</b> oluştur veya mevcut Page’i kullan.</li>
      <li>Instagram hesabını <b>Business/Creator</b>’a çevir.</li>
      <li>Instagram → Ayarlar → Hesap → “Bağlı Hesaplar / Professional” alanından ilgili Facebook Page’i bağla.</li>
      <li>Tekrar buraya dön ve “Kontrol Et” de.</li>
    </ol>

    <a class="btn btn-outline-primary btn-sm" href="<?= site_url('panel/social-accounts/meta/select') ?>" style="border-radius:999px;">
      <i class="bi bi-arrow-repeat me-1"></i> Kontrol Et
    </a>

    <div class="text-muted small mt-2">
      Not: Meta tarafında izinleri verdikten sonra Page-IG bağlantısı yoksa listede görünmez.
    </div>
  </div>

<?php else: ?>
  <div class="card-soft p-3">
    <div class="fw-semibold mb-2">Bulunan hesaplar</div>

    <div class="table-responsive">
      <table class="table align-middle">
        <thead>
          <tr>
            <th>Facebook Page</th>
            <th>Instagram</th>
            <th class="text-end" style="width:160px;">İşlem</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($items as $it): ?>
            <tr>
              <td>
                <div class="fw-semibold"><?= esc($it['page_name']) ?></div>
                <div class="text-muted small">Page ID: <?= esc($it['page_id']) ?></div>
              </td>
              <td>
                <div class="fw-semibold">@<?= esc($it['ig_username'] ?: '—') ?></div>
                <div class="text-muted small">IG ID: <?= esc($it['ig_id']) ?></div>
              </td>
              <td class="text-end">
                <form method="post" action="<?= site_url('panel/social-accounts/meta/attach') ?>">
                  <?= csrf_field() ?>
                  <input type="hidden" name="page_id" value="<?= esc($it['page_id']) ?>">
                  <input type="hidden" name="ig_id" value="<?= esc($it['ig_id']) ?>">
                  <button class="btn btn-primary btn-sm" style="border-radius:999px;">
                    <i class="bi bi-check2-circle me-1"></i> Kaydet
                  </button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>

    <div class="text-muted small">
      Kaydettikten sonra bu Instagram hesabı “Sosyal Hesaplar” listesine otomatik düşecek.
    </div>
  </div>
<?php endif; ?>

<?= $this->endSection() ?>
