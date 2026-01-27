<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="page-title">Sosyal Hesaplar</div>
    <div class="text-muted">Platform hesaplarını ekle, düzenle ve planlı paylaşımlarda kullan.</div>
  </div>

  <a href="<?= site_url('panel/calendar') ?>" class="btn btn-outline-secondary btn-sm" style="border-radius:999px;">
    <i class="bi bi-calendar3 me-1"></i> Takvime Git
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="row g-3">
  <!-- Sol: Instagram Wizard + Manuel Ekle -->

    <div class="card-soft p-3 mb-3">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="metric-label">TikTok Bağla</div>
            <span class="metric-tag"><i class="bi bi-link-45deg me-1"></i>TikTok</span>
        </div>

        <div class="text-muted small mb-2">
            TikTok hesabını bağla. Böylece videoları panelden planlayıp TikTok’a gönderebiliriz.
        </div>

        <a href="<?= site_url('panel/auth/tiktok') ?>"
            class="btn btn-dark w-100 btn-sm"
            style="border-radius:999px;">
            <i class="bi bi-music-note-beamed me-1"></i> TikTok’u Bağla
        </a>

        <div class="text-muted small mt-2">
            Not: İlk bağlantıda TikTok izin ekranı açılır.
        </div>
    </div>


    <div class="card-soft p-3">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="metric-label">Hesap Ekle</div>
        <span class="metric-tag"><i class="bi bi-plus-lg me-1"></i>Manuel</span>
      </div>

      <form method="post" action="<?= site_url('panel/social-accounts') ?>">
        <?= csrf_field() ?>

        <div class="mb-2">
          <label class="form-label small text-muted mb-1">Platform</label>
          <select name="platform" class="form-select form-select-sm">
            <?php
              $platformOld = strtolower((string)(old('platform') ?? 'instagram'));
              $platforms = ['instagram' => 'Instagram','facebook'=>'Facebook','tiktok'=>'TikTok','youtube'=>'YouTube'];
            ?>
            <?php foreach ($platforms as $k => $label): ?>
              <option value="<?= esc($k) ?>" <?= $platformOld === $k ? 'selected' : '' ?>><?= esc($label) ?></option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="mb-2">
          <label class="form-label small text-muted mb-1">Hesap Adı</label>
          <input name="name" type="text" class="form-control form-control-sm"
                 value="<?= esc(old('name') ?? '') ?>"
                 placeholder="Örn: Test Instagram">
          <div class="text-muted small mt-1">Panelde görünecek ad.</div>
        </div>

        <div class="mb-2">
          <label class="form-label small text-muted mb-1">Kullanıcı Adı (opsiyonel)</label>
          <input name="username" type="text" class="form-control form-control-sm"
                 value="<?= esc(old('username') ?? '') ?>"
                 placeholder="Örn: test_ig">
        </div>

        <div class="mb-3">
          <label class="form-label small text-muted mb-1">External ID (opsiyonel)</label>
          <input name="external_id" type="text" class="form-control form-control-sm"
                 value="<?= esc(old('external_id') ?? '') ?>"
                 placeholder="OAuth bağlayınca dolacak">
        </div>

        <button type="submit" class="btn btn-primary w-100 btn-sm"
                style="border-radius:999px; background: linear-gradient(135deg,#6366f1,#ec4899); border:none;">
          <i class="bi bi-check2-circle me-1"></i> Hesabı Kaydet
        </button>
      </form>
    </div>
  </div>

  <!-- Sağ: Liste -->
  <div class="col-lg-8">
    <div class="card-soft p-3 h-100">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <div class="metric-label">Hesap Listesi</div>
        <span class="metric-tag"><i class="bi bi-shield-check me-1"></i>Sana ait hesaplar</span>
      </div>

      <div class="table-responsive">
        <table class="table align-middle">
          <thead>
            <tr>
              <th style="width:70px;">ID</th>
              <th>Platform</th>
              <th>Hesap</th>
              <th>Kullanıcı Adı</th>
              <th>External ID</th>
              <th class="text-end" style="width:140px;">Aksiyon</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($rows)): ?>
            <tr>
              <td colspan="6" class="text-center text-muted py-4">
                Henüz hesap eklenmemiş. Soldan bir hesap ekleyebilirsin.
              </td>
            </tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <tr>
                <td><?= (int)$r['id'] ?></td>
                <td><span class="badge text-bg-light"><?= strtoupper(esc($r['platform'] ?? '')) ?></span></td>
                <td>
                  <div class="fw-semibold"><?= esc($r['name'] ?? '') ?></div>
                  <div class="text-muted small">Kullanıcı id #<?= (int)($r['user_id'] ?? 0) ?></div>
                </td>
                <td><?= !empty($r['username']) ? esc($r['username']) : '<span class="text-muted">—</span>' ?></td>
                <td><?= !empty($r['external_id']) ? esc($r['external_id']) : '<span class="text-muted">—</span>' ?></td>
                <td class="text-end">
                  <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Düzenleme sonraki adım.">
                    <i class="bi bi-pencil"></i>
                  </button>

                  <form method="post"
                        action="<?= site_url('panel/social-accounts/' . (int)$r['id'] . '/delete') ?>"
                        class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit"
                            class="btn btn-outline-danger btn-sm"
                            data-confirm="Bu sosyal hesabı silmek istediğine emin misin?">
                      <i class="bi bi-trash"></i>
                    </button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>

      <div class="text-muted small mt-2">
        İpucu: Hesap ekledikten sonra <a href="<?= site_url('panel/calendar') ?>">Takvim &amp; Planlama</a> ekranında “Hesap” alanında görünecek.
      </div>
    </div>
  </div>
</div>

<!-- Consent Modal -->
<div class="modal fade" id="metaConsentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Meta Yetkilendirme Onayı</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>

      <form method="post" action="<?= site_url('panel/social-accounts/meta/consent') ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="consentCheck" name="consent" required>
            <label class="form-check-label" for="consentCheck">
              Meta üzerinden Instagram hesabımı bağlamak için gerekli yetkilendirmeyi kabul ediyorum.
            </label>
          </div>
          <div class="text-muted small mt-2">
            Onaydan sonra Meta giriş ekranına yönlendirileceksin.
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Vazgeç</button>
          <button type="submit" class="btn btn-primary">Devam</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
