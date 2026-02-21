<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<style>
  .mw-title{ font-size:32px; letter-spacing:-.6px; }
  .mw-sub{ max-width: 760px; }

  .mw-shell{
    border:1px solid rgba(0,0,0,.06);
    border-radius: 18px;
    background:#fff;
    overflow:hidden;
  }
  .mw-head{
    padding:16px 16px 10px 16px;
    border-bottom:1px solid rgba(0,0,0,.06);
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    flex-wrap:wrap;
  }
  .mw-head .mw-actions{ display:flex; gap:8px; align-items:center; flex-wrap:wrap; }
  .btn-soft{
    border-radius:999px;
    border:1px solid rgba(0,0,0,.10);
    background:#fff;
    font-weight:800;
  }
  .btn-soft:hover{ background:rgba(0,0,0,.03); }

  .btn-danger-soft{
    border-radius:999px;
    border:1px solid rgba(220,53,69,.35);
    background:#fff;
    font-weight:800;
    color:#dc3545;
  }
  .btn-danger-soft:hover{ background:rgba(220,53,69,.06); color:#dc3545; }

  .mw-body{ padding: 14px 16px 16px 16px; }

  .mw-steps{
    display:flex;
    gap:10px;
    flex-wrap:wrap;
    margin-bottom:12px;
  }
  .mw-step{
    display:inline-flex;
    align-items:center;
    gap:8px;
    padding:8px 12px;
    border-radius:999px;
    border:1px solid rgba(0,0,0,.08);
    background: rgba(0,0,0,.02);
    font-size:12.5px;
    font-weight:700;
  }
  .mw-step .dot{
    width:10px; height:10px; border-radius:999px;
    background: rgba(0,0,0,.25);
  }
  .mw-step.active{
    border-color: rgba(106,92,255,.35);
    background: rgba(106,92,255,.06);
  }
  .mw-step.active .dot{ background: rgba(106,92,255,.9); }

  .mw-help{
    border:1px solid rgba(0,0,0,.06);
    border-radius: 16px;
    background: rgba(0,0,0,.02);
    padding: 12px 14px;
  }

  .mw-finder{
    border:1px solid rgba(0,0,0,.06);
    border-radius: 16px;
    padding: 14px;
    background:#fff;
  }
  .mw-finder .mw-h{ font-weight:900; font-size:14px; }
  .mw-finder .mw-t{ color: rgba(0,0,0,.62); font-size: 13px; line-height: 1.45; }

  .mw-form{
    display:flex;
    gap:10px;
    align-items:center;
    margin-top:10px;
    flex-wrap:wrap;
  }
  .mw-form .form-control{ border-radius:999px; }
  .mw-form .btn{ border-radius:999px; font-weight:900; }

  .mw-adv details summary{
    cursor:pointer;
    color: rgba(0,0,0,.60);
    font-weight:800;
  }

  .mw-grid{
    display:grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
  }
  .mw-card{
    border:1px solid rgba(0,0,0,.06);
    border-radius: 18px;
    background:#fff;
    padding: 14px;
    display:flex;
    gap:12px;
    align-items:center;
    transition: transform .12s ease, box-shadow .12s ease, background .12s ease;
  }
  .mw-card:hover{
    transform: translateY(-1px);
    box-shadow: 0 10px 24px rgba(0,0,0,.06);
    background: rgba(0,0,0,.01);
  }
  .mw-avatar{
    width:54px; height:54px; border-radius: 16px;
    border:1px solid rgba(0,0,0,.06);
    background: rgba(0,0,0,.03);
    object-fit:cover;
    flex:0 0 auto;
  }
  .mw-meta{ min-width:0; flex:1; }
  .mw-name{ font-weight:950; line-height:1.15; }
  .mw-subline{ color: rgba(0,0,0,.62); font-size:13px; margin-top:4px; }
  .mw-pill{
    display:inline-flex;
    align-items:center;
    gap:7px;
    padding:6px 10px;
    border-radius:999px;
    border:1px solid rgba(0,0,0,.07);
    background: rgba(0,0,0,.02);
    font-size:12px;
    margin-top:8px;
  }
  .mw-actions-col{ flex:0 0 auto; }

  .mw-empty{
    border:1px dashed rgba(0,0,0,.18);
    border-radius: 18px;
    padding: 16px;
    background: rgba(0,0,0,.01);
  }

  @media (max-width: 991.98px){
    .mw-title{ font-size:26px; }
    .mw-grid{ grid-template-columns: 1fr; }
    .mw-head{ align-items:flex-start; }
    .mw-head .mw-actions{ width:100%; }
    .mw-head .mw-actions .btn{ width:100%; }
    .mw-form{ width:100%; }
    .mw-form .form-control, .mw-form .btn{ width:100%; }
  }
</style>

<div class="container-fluid py-3">

  <?php
    $hasConsent = isset($hasConsent) ? (bool) $hasConsent : false;
    $hasToken   = isset($hasToken) ? (bool) $hasToken : false;
    $igOptions  = $igOptions ?? [];
    $pageRefOld = (string)($_GET['page_ref'] ?? '');
    $pageIdOld  = (string)($_GET['page_id'] ?? '');
    $showFinder = $hasConsent && $hasToken && empty($igOptions);
  ?>

  <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
    <div>
      <div class="mw-title fw-bold">Instagram Bağla</div>
      <div class="text-muted mw-sub">
        Instagram hesabın <b>Business/Creator</b> olmalı ve bir <b>Facebook Sayfasına</b> bağlı olmalı.
        Buradan sadece bağlantı yaparsın — paylaşım planlama <b>Takvim &amp; Planlama</b> ekranındadır.
      </div>
    </div>

    <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-soft btn-sm">
      <i class="bi bi-arrow-left me-1"></i> Sosyal Hesaplara Dön
    </a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="mw-shell">
    <div class="mw-head">
      <div>
        <div class="fw-bold" style="font-size:16px;">Bağlantı Sihirbazı</div>

        <div class="mw-steps mt-2">
          <div class="mw-step <?= !$hasConsent ? 'active' : '' ?>">
            <span class="dot"></span> 1) Onay
          </div>
          <div class="mw-step <?= ($hasConsent && !$hasToken) ? 'active' : '' ?>">
            <span class="dot"></span> 2) Meta ile Giriş
          </div>
          <div class="mw-step <?= ($hasConsent && $hasToken) ? 'active' : '' ?>">
            <span class="dot"></span> 3) Instagram’ı Seç
          </div>
        </div>
      </div>

      <div class="mw-actions">
        <?php if ($hasConsent && $hasToken): ?>
          <a href="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="btn btn-soft btn-sm">
            <i class="bi bi-arrow-repeat me-1"></i> Yeniden Tara
          </a>

          <form method="post"
                action="<?= site_url('panel/social-accounts/meta/disconnect') ?>"
                onsubmit="return confirm('Meta bağlantısını sıfırlamak istediğine emin misin? Bu işlem yeniden bağlanmanı gerektirir.');">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-danger-soft btn-sm">
              <i class="bi bi-trash3 me-1"></i> Bağlantıyı Sıfırla
            </button>
          </form>
        <?php endif; ?>
      </div>
    </div>

    <div class="mw-body">

      <?php if (!$hasConsent): ?>
        <div class="mw-help">
          <div class="fw-bold mb-1">Devam etmek için onay gerekiyor</div>
          <div class="small text-muted">
            Sosyal Hesaplar sayfasına dönüp “Meta ile Bağla” butonundan onay vererek devam edebilirsin.
          </div>
          <div class="mt-3">
            <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-primary" style="border-radius:999px; font-weight:900;">
              Onay Vermeye Git
            </a>
          </div>
        </div>

      <?php elseif (!$hasToken): ?>
        <div class="mw-help">
          <div class="fw-bold mb-1">Onay tamam ✅</div>
          <div class="small text-muted">
            Şimdi Meta hesabınla giriş yaparak bağlantıyı tamamlayacağız.
          </div>
          <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="<?= site_url('panel/social-accounts/meta/connect') ?>"
               class="btn btn-primary"
               style="border-radius:999px; font-weight:900;">
              <i class="bi bi-facebook me-1"></i> Meta ile Bağlan
            </a>
            <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-soft" style="border-radius:999px;">
              Geri Dön
            </a>
          </div>
          <div class="text-muted small mt-2">
            Not: Meta giriş ekranında izin verdiğinde tekrar bu sayfaya döneceksin.
          </div>
        </div>

      <?php else: ?>

        <?php if ($showFinder): ?>
          <div class="mw-finder mb-3">
            <div class="mw-h mb-1">Instagram hesabını bulamadık</div>
            <div class="mw-t">
              Bazı Meta hesaplarında sayfa listesi boş dönebilir. En kolay yöntem:
              <b>Facebook Sayfa linkini</b> yapıştırıp “Bul ve Tara” demek.
            </div>

            <form method="get" action="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="mw-form">
              <div class="flex-grow-1 w-100">
                <input name="page_ref"
                       class="form-control"
                       placeholder="Örn: facebook.com/sayfaadi  veya  sayfaadi  veya  profile.php?id=123"
                       value="<?= esc($pageRefOld) ?>">
              </div>
              <button class="btn btn-primary" type="submit">
                <i class="bi bi-search me-1"></i> Bul ve Tara
              </button>
              <a href="<?= site_url('panel/social-accounts/meta/connect') ?>" class="btn btn-soft">
                <i class="bi bi-arrow-clockwise me-1"></i> Meta ile Yeniden Bağlan
              </a>
            </form>

            <div class="text-muted small mt-2">
              İpucu: Page ID bilmek zorunda değilsin. Sayfa linkini kopyalayıp yapıştırman yeterli.
            </div>

            <div class="mw-adv mt-3">
              <details>
                <summary>Gelişmiş: Page ID ile dene</summary>
                <div class="mt-2">
                  <form method="get" action="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="mw-form">
                    <div class="flex-grow-1 w-100">
                      <input name="page_id" class="form-control" placeholder="9300..." value="<?= esc($pageIdOld) ?>">
                    </div>
                    <button class="btn btn-soft" type="submit">
                      Sayfayı Kontrol Et
                    </button>
                  </form>
                  <div class="small text-muted mt-2">
                    Not: “Page ID” ile “User ID” farklıdır. Buraya sayfanın ID’sini girmelisin.
                  </div>
                </div>
              </details>
            </div>
          </div>
        <?php endif; ?>

        <?php if (empty($igOptions)): ?>
          <div class="mw-empty">
            <div class="fw-bold mb-1">Bağlanabilir Instagram hesabı bulunamadı.</div>
            <div class="small text-muted">
              Instagram hesabının <b>Business/Creator</b> olması ve bir <b>Facebook Sayfasına</b> bağlı olması gerekir.
              <br>Çözüm: Üstteki alana <b>Facebook sayfa linkini</b> yapıştırıp tekrar tara.
            </div>

            <div class="mt-3 d-flex gap-2 flex-wrap">
              <a href="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="btn btn-soft">
                <i class="bi bi-arrow-repeat me-1"></i> Yeniden Dene
              </a>
              <a href="<?= site_url('panel/social-accounts/meta/connect') ?>" class="btn btn-soft">
                <i class="bi bi-arrow-clockwise me-1"></i> Meta ile Yeniden Bağlan
              </a>
            </div>
          </div>
        <?php else: ?>

          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
            <div class="fw-bold" style="font-size:15px;">Bulunan Instagram Hesapları</div>
            <div class="text-muted small">
              Bağlamak istediğin hesabı seç.
            </div>
          </div>

          <div class="mw-grid">
            <?php foreach ($igOptions as $it): ?>
              <?php
                $igTitle = trim((string)($it['ig_username'] ?? ''));
                if ($igTitle === '') $igTitle = trim((string)($it['ig_name'] ?? 'Instagram'));
                $pageName = trim((string)($it['page_name'] ?? 'Facebook Sayfası'));
              ?>

              <div class="mw-card">
                <?php if (!empty($it['ig_avatar'])): ?>
                  <img src="<?= esc($it['ig_avatar']) ?>" alt="" class="mw-avatar">
                <?php else: ?>
                  <div class="mw-avatar d-flex align-items-center justify-content-center">
                    <i class="bi bi-instagram" style="font-size:18px; opacity:.65;"></i>
                  </div>
                <?php endif; ?>

                <div class="mw-meta">
                  <div class="mw-name text-truncate"><?= esc($igTitle) ?></div>
                  <div class="mw-subline text-truncate">
                    Facebook Sayfası: <span class="fw-semibold"><?= esc($pageName) ?></span>
                  </div>
                  <div class="mw-pill">
                    <i class="bi bi-shield-check"></i>
                    Bu hesap planlamada kullanılacak
                  </div>
                </div>

                <div class="mw-actions-col">
                  <form action="<?= site_url('panel/social-accounts/meta/attach') ?>" method="post">
                    <?= csrf_field() ?>
                    <input type="hidden" name="ig_id" value="<?= esc($it['ig_id']) ?>">
                    <input type="hidden" name="page_id" value="<?= esc($it['page_id']) ?>">
                    <input type="hidden" name="page_name" value="<?= esc($it['page_name']) ?>">
                    <button class="btn btn-primary" style="border-radius:999px; font-weight:900;">
                      Bağla
                    </button>
                  </form>
                </div>
              </div>

            <?php endforeach; ?>
          </div>

          <div class="text-muted small mt-3">
            Bağladıktan sonra bu hesap, <a href="<?= site_url('panel/calendar') ?>">Takvim &amp; Planlama</a> ekranında “Hesap” seçiminde görünür.
          
        <?php if (!empty($showDebug) && !empty($debug)): ?>
  <hr>
  <h5>DEBUG (wizard)</h5>
  <pre style="max-height:520px; overflow:auto; background:#111; color:#0f0; padding:12px; border-radius:8px;">
<?= esc(json_encode($debug, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES)) ?>
  </pre>
<?php endif; ?>
        
        
        </div>

        <?php endif; ?>

      <?php endif; ?>

    </div>
  </div>

</div>

<?= $this->endSection() ?>
