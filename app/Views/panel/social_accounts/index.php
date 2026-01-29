<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  .sa-page-title{ font-size:36px; letter-spacing:-.6px; }
  .sa-sub{ max-width: 720px; }
  .sa-connect-card{
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 18px;
    background: #fff;
    overflow: hidden;
  }
  .sa-connect-head{
    display:flex; align-items:center; justify-content:space-between;
    gap:10px;
    padding:14px 14px 10px 14px;
  }
  .sa-connect-badge{
    display:inline-flex; align-items:center; gap:8px;
    padding:7px 10px; border-radius:999px;
    background: rgba(0,0,0,.04);
    border: 1px solid rgba(0,0,0,.06);
    font-size:12px; font-weight:600;
    white-space:nowrap;
  }
  .sa-connect-body{ padding:0 14px 14px 14px; }
  .sa-connect-desc{ color: rgba(0,0,0,.62); font-size: 13px; line-height: 1.4; }
  .btn-brand{
    background: linear-gradient(90deg, #6a5cff, #ff4fd8);
    border: 0;
    color: #fff;
    font-weight: 700;
    border-radius: 999px;
  }
  .btn-brand:hover{ filter: brightness(.98); color:#fff; }
  .btn-soft{
    border-radius: 999px;
    border: 1px solid rgba(0,0,0,.10);
    background: #fff;
    font-weight: 700;
  }
  .btn-soft:hover{
    background: rgba(0,0,0,.03);
  }

  .sa-list-card{
    border: 1px solid rgba(0,0,0,.06);
    border-radius: 18px;
    background: #fff;
  }

  .sa-item{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:14px;
    padding:14px;
    border-top: 1px solid rgba(0,0,0,.06);
  }
  .sa-item:first-child{ border-top:0; }
  .sa-left{ display:flex; align-items:center; gap:12px; min-width:0; }
  .sa-icon{
    width:42px; height:42px; border-radius:12px;
    display:flex; align-items:center; justify-content:center;
    border: 1px solid rgba(0,0,0,.06);
    background: rgba(0,0,0,.03);
    flex: 0 0 auto;
    font-size:18px;
  }
  .sa-meta{ min-width:0; }
  .sa-name{ font-weight:800; line-height:1.15; }
  .sa-subline{ color: rgba(0,0,0,.55); font-size: 12.5px; }
  .sa-kv{
    display:flex; flex-wrap:wrap; gap:8px;
    margin-top:6px;
  }
  .sa-pill{
    display:inline-flex; align-items:center; gap:6px;
    padding:6px 10px; border-radius:999px;
    border: 1px solid rgba(0,0,0,.07);
    background: rgba(0,0,0,.02);
    font-size: 12px;
    max-width: 100%;
  }
  .sa-pill code{
    font-size: 12px;
    color: rgba(0,0,0,.7);
    background: transparent;
    padding:0;
  }
  .sa-actions{ display:flex; align-items:center; gap:8px; flex:0 0 auto; }

  .sa-empty{
    padding: 22px 14px;
    color: rgba(0,0,0,.55);
    text-align:center;
  }

  @media (max-width: 991.98px){
    .sa-page-title{ font-size:28px; }
    .sa-item{ align-items:flex-start; flex-direction:column; }
    .sa-actions{ width:100%; }
    .sa-actions form{ width:100%; }
    .sa-actions .btn{ width:100%; }
  }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="sa-page-title fw-bold">Sosyal Hesaplar</div>
    <div class="text-muted sa-sub">
      Hesaplarını bağla, planlı paylaşımlarda kullan. Bu ekranda sadece bağlantı yaparsın — paylaşım planlama Takvim &amp; Planlama ekranındadır.
    </div>
  </div>

  <a href="<?= site_url('panel/calendar') ?>" class="btn btn-soft btn-sm" style="border-radius:999px;">
    <i class="bi bi-calendar3 me-1"></i> Takvime Git
  </a>
</div>

<?php if (session()->getFlashdata('success')): ?>
  <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
  <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<!-- CONNECT ROW -->
<div class="row g-3 mb-3">
  <!-- META -->
  <div class="col-lg-4">
    <div class="sa-connect-card h-100">
      <div class="sa-connect-head">
        <div class="fw-bold">Instagram &amp; Facebook (Meta)</div>
        <span class="sa-connect-badge"><i class="bi bi-shield-check"></i> En önerilen</span>
      </div>
      <div class="sa-connect-body">
        <div class="sa-connect-desc mb-3">
          Bu bağlantı ile <b>Facebook Sayfanı</b> ve o sayfaya bağlı <b>Instagram Business/Creator</b> hesabını tek akışta ekleyebilirsin.
        </div>

        <button type="button"
                class="btn btn-brand w-100 btn-sm"
                data-bs-toggle="modal"
                data-bs-target="#metaConsentModal">
          <i class="bi bi-link-45deg me-1"></i> Meta ile Bağla
        </button>

        <div class="text-muted small mt-2">
          Not: Instagram hesabın Business/Creator olmalı ve bir Facebook Page’e bağlı olmalı.
        </div>

        <a href="<?= site_url('panel/social-accounts/meta/wizard') ?>"
           class="btn btn-link btn-sm px-0 mt-2">
          Bağlantıda sorun mu var? Kontrol et →
        </a>
      </div>
    </div>
  </div>

  <!-- YOUTUBE -->
  <div class="col-lg-4">
    <div class="sa-connect-card h-100">
      <div class="sa-connect-head">
        <div class="fw-bold">YouTube</div>
        <span class="sa-connect-badge"><i class="bi bi-youtube"></i> Google</span>
      </div>
      <div class="sa-connect-body">
        <div class="sa-connect-desc mb-3">
          YouTube kanalını bağla. Videolarını panelden planlayıp otomatik yayınlayabilirsin.
        </div>

        <a href="<?= site_url('panel/social-accounts/youtube/connect') ?>"
           class="btn btn-outline-danger w-100 btn-sm"
           style="border-radius:999px; font-weight:800;">
          <i class="bi bi-youtube me-1"></i> YouTube’u Bağla
        </a>

        <div class="text-muted small mt-2">
          Not: İlk bağlantıda Google izin ekranı açılır.
        </div>
      </div>
    </div>
  </div>

  <!-- TIKTOK -->
  <div class="col-lg-4">
    <div class="sa-connect-card h-100">
      <div class="sa-connect-head">
        <div class="fw-bold">TikTok</div>
        <span class="sa-connect-badge"><i class="bi bi-music-note-beamed"></i> TikTok</span>
      </div>
      <div class="sa-connect-body">
        <div class="sa-connect-desc mb-3">
          TikTok hesabını bağla. Videoları panelden planlayıp TikTok’a gönderebilirsin.
        </div>

        <a href="<?= site_url('panel/auth/tiktok') ?>"
           class="btn btn-dark w-100 btn-sm"
           style="border-radius:999px; font-weight:800;">
          <i class="bi bi-music-note-beamed me-1"></i> TikTok’u Bağla
        </a>

        <div class="text-muted small mt-2">
          Not: İlk bağlantıda TikTok izin ekranı açılır.
        </div>
      </div>
    </div>
  </div>
</div>

<!-- CONNECTED ACCOUNTS LIST -->
<div class="sa-list-card">
  <div class="p-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
    <div>
      <div class="fw-bold" style="font-size:16px;">Bağlı Hesaplar</div>
      <div class="text-muted small">Planlamada görünür. İstersen buradan kaldırabilirsin.</div>
    </div>
    <span class="sa-connect-badge"><i class="bi bi-shield-check"></i> Sana ait hesaplar</span>
  </div>

  <?php if (empty($rows)): ?>
    <div class="sa-empty">
      Henüz hesap eklenmemiş. Yukarıdan bir platform bağlayarak başlayabilirsin.
    </div>
  <?php else: ?>

    <?php
      $iconFor = function($platform){
        $p = strtolower((string)$platform);
        return match($p){
          'instagram' => 'bi-instagram',
          'facebook'  => 'bi-facebook',
          'youtube'   => 'bi-youtube',
          'tiktok'    => 'bi-music-note-beamed',
          'x','twitter' => 'bi-twitter-x',
          default     => 'bi-link-45deg',
        };
      };
      $labelFor = function($platform){
        $p = strtolower((string)$platform);
        return match($p){
          'instagram' => 'Instagram',
          'facebook'  => 'Facebook',
          'youtube'   => 'YouTube',
          'tiktok'    => 'TikTok',
          'x','twitter' => 'X',
          default     => strtoupper($p ?: 'PLATFORM'),
        };
      };
    ?>

    <?php foreach ($rows as $r): ?>
      <?php
        $platform = strtolower((string)($r['platform'] ?? ''));
        $name     = trim((string)($r['name'] ?? ''));
        $username = trim((string)($r['username'] ?? ''));
        $extId    = trim((string)($r['external_id'] ?? ''));

        // Görünür başlık: name varsa onu, yoksa @username, yoksa platform + id
        $title = $name !== '' ? $name : ($username !== '' ? '@'.$username : ($labelFor($platform) . ' hesabı'));

        // Not: TikTok/FB bazen username/name boş gelebilir → dış id üzerinden göster
      ?>

      <div class="sa-item">
        <div class="sa-left">
          <div class="sa-icon">
            <i class="bi <?= esc($iconFor($platform)) ?>"></i>
          </div>

          <div class="sa-meta">
            <div class="sa-name text-truncate"><?= esc($title) ?></div>
            <div class="sa-subline">
              <?= esc($labelFor($platform)) ?>
              <?php if ($username !== ''): ?>
                · <span class="fw-semibold">@<?= esc($username) ?></span>
              <?php endif; ?>
            </div>

            <div class="sa-kv">
              <?php if ($extId !== ''): ?>
                <span class="sa-pill">
                  <span class="text-muted">External ID:</span>
                  <code class="text-break"><?= esc($extId) ?></code>
                </span>
              <?php endif; ?>

              <span class="sa-pill">
                <span class="text-muted">Kayıt ID:</span>
                <code>#<?= (int)($r['id'] ?? 0) ?></code>
              </span>
            </div>
          </div>
        </div>

        <div class="sa-actions">
          <button type="button"
                  class="btn btn-outline-secondary btn-sm"
                  disabled
                  title="Düzenleme sonraki adım.">
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
        </div>
      </div>
    <?php endforeach; ?>

    <div class="p-3 pt-0">
      <div class="text-muted small">
        İpucu: Hesap ekledikten sonra <a href="<?= site_url('panel/calendar') ?>">Takvim &amp; Planlama</a> ekranında “Hesap” alanında görünür.
      </div>
      <div class="text-muted small mt-1">
        Not: Eğer TikTok/Facebook kullanıcı adı boş görünüyorsa, bağlantı akışında platformdan isim/username izni alınmamış veya kaydetme adımında DB’ye yazılmıyor olabilir.
      </div>
    </div>

  <?php endif; ?>
</div>

<!-- Consent Modal -->
<div class="modal fade" id="metaConsentModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Meta Yetkilendirme</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
      </div>

      <form method="post" action="<?= site_url('panel/social-accounts/meta/consent') ?>">
        <?= csrf_field() ?>
        <div class="modal-body">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" value="1" id="consentCheck" name="consent" required>
            <label class="form-check-label" for="consentCheck">
              Instagram &amp; Facebook hesaplarımı bağlamak için Meta yetkilendirmesini kabul ediyorum.
            </label>
          </div>
          <div class="text-muted small mt-2">
            Devam edince Meta giriş ekranına yönlendirileceksin.
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
