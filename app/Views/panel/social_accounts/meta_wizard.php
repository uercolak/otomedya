<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Instagram Bağla</h3>
      <div class="text-muted">Facebook Page üzerinden bağlı Instagram Business/Creator hesabını seç.</div>
    </div>

    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-outline-secondary">Geri</a>

      <form method="post" action="<?= site_url('panel/social-accounts/meta/disconnect') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger">Sıfırla</button>
      </form>
    </div>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <?php
    $hasConsent = isset($hasConsent) ? (bool) $hasConsent : false;
    $hasToken   = isset($hasToken) ? (bool) $hasToken : false;
    $igOptions  = $igOptions ?? [];
    $debug      = $debug ?? [];
    $pageRefOld = (string)($_GET['page_ref'] ?? '');
    $pageIdOld  = (string)($_GET['page_id'] ?? '');
  ?>

  <?php if (!$hasConsent): ?>
    <div class="alert alert-warning">
      Devam etmek için önce onayı kabul etmelisin.
      <div class="small mt-1">Sosyal hesaplar sayfasına dönüp “Instagram’ı Meta ile Bağla” butonundan onay ver.</div>
    </div>
    <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-primary">Onay Ver</a>

  <?php elseif (!$hasToken): ?>
    <div class="alert alert-info">
      Onay tamam. Şimdi Meta hesabın ile giriş yapıp bağlantıyı tamamla.
    </div>
    <a href="<?= site_url('panel/social-accounts/meta/connect') ?>" class="btn btn-primary">Meta ile Bağlan</a>

  <?php else: ?>

    <?php $showFinder = empty($igOptions); ?>

    <div class="row g-3">
      <div class="col-lg-8">

        <!-- Auto scan + Page URL/username resolver -->
         <?php if ($showFinder): ?>
        <div class="card mb-3">
          <div class="card-body">
            <div class="fw-semibold mb-1">Otomatik Tara</div>
            <div class="text-muted small mb-3">
              Sistem otomatik olarak Facebook Page’lerini tarayıp bağlı Instagram hesabını bulmaya çalışır.
              Bazı Meta hesaplarında listeleme boş dönebilir; bu durumda sayfa linki / kullanıcı adı ile buldurabilirsin.
            </div>

            <div class="d-flex flex-wrap gap-2 mb-3">
              <a href="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="btn btn-outline-primary">
                Yeniden Tara
              </a>
              <a href="<?= site_url('panel/social-accounts/meta/connect') ?>" class="btn btn-outline-secondary">
                Meta ile Yeniden Bağlan
              </a>
              <a href="<?= site_url('panel/social-accounts/meta/publish-test') ?>" class="btn btn-outline-secondary">
                Test Paylaşımı
              </a>
            </div>

            <div class="fw-semibold mb-2">Sayfa Linki / Kullanıcı Adı ile Bul (Önerilen)</div>

            <form method="get" action="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="d-flex gap-2">
              <input name="page_ref"
                     class="form-control"
                     placeholder="Örn: facebook.com/sayfaadi  veya  profile.php?id=123  veya  sayfaadi"
                     value="<?= esc($pageRefOld) ?>">
              <button class="btn btn-primary" type="submit">Bul ve Tara</button>
            </form>

            <div class="text-muted small mt-2">
              İpucu: Müşteri Page ID bilmek zorunda değil. Sayfa linkini kopyalayıp buraya yapıştırması yeterli.
            </div>

            <hr class="my-3">

            <!-- Advanced: manual Page ID -->
            <details>
              <summary class="text-muted" style="cursor:pointer;">Gelişmiş: Page ID ile dene</summary>
              <div class="mt-2">
                <form method="get" action="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="d-flex gap-2">
                  <input name="page_id" class="form-control" placeholder="9300..." value="<?= esc($pageIdOld) ?>">
                  <button class="btn btn-outline-primary" type="submit">Sayfayı Kontrol Et</button>
                </form>
                <div class="small text-muted mt-2">
                  Not: “Page ID” ile “User ID” farklıdır. Buraya Page ID girmelisin (sayfanın id’si).
                </div>
              </div>
            </details>
          </div>
        </div>
        <?php endif; ?>

        <?php if (empty($igOptions)): ?>
          <div class="alert alert-warning">
            <div class="fw-semibold mb-1">Bağlanabilir Instagram hesabı bulunamadı.</div>
            <div class="small">
              Not: Instagram hesabının Business/Creator olması ve bir Facebook Page’e bağlı olması gerekir.
              <br>Meta bazı hesaplarda sayfa listesini boş döndürebilir.
              <br><b>Çözüm:</b> Yukarıdan “Sayfa Linki / Kullanıcı Adı” ile sayfanı buldur ve tekrar tara.
            </div>
          </div>
        <?php else: ?>

            

          <div class="row g-3">
            <?php foreach ($igOptions as $it): ?>
              <div class="col-lg-6">
                <div class="card">
                  <div class="card-body d-flex gap-3 align-items-center">
                    <?php if (!empty($it['ig_avatar'])): ?>
                      <img src="<?= esc($it['ig_avatar']) ?>" alt="" style="width:56px;height:56px;border-radius:14px;object-fit:cover;">
                    <?php else: ?>
                      <div class="bg-light border" style="width:56px;height:56px;border-radius:14px;"></div>
                    <?php endif; ?>

                    <div class="flex-grow-1">
                      <div class="fw-semibold"><?= esc($it['ig_username'] ?: $it['ig_name']) ?></div>
                      <div class="text-muted small">
                        Sayfa: <?= esc($it['page_name']) ?> • IG ID: <?= esc($it['ig_id']) ?>
                      </div>
                    </div>

                    <form action="<?= site_url('panel/social-accounts/meta/attach') ?>" method="post">
                      <?= csrf_field() ?>
                      <input type="hidden" name="ig_id" value="<?= esc($it['ig_id']) ?>">
                      <input type="hidden" name="page_id" value="<?= esc($it['page_id']) ?>">
                      <input type="hidden" name="page_name" value="<?= esc($it['page_name']) ?>">
                      <button class="btn btn-primary">Bağla</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>

        <?php endif; ?>
      </div>

      <div class="col-lg-4">
        <div class="card">
          <div class="card-header">Hızlı Kontroller</div>
          <div class="card-body d-grid gap-2">
            <a class="btn btn-outline-secondary"
               href="<?= site_url('panel/social-accounts/meta/health?key=' . urlencode(getenv('META_HEALTH_KEY') ?: getenv('IDEMPOTENCY_SECRET'))) ?>"
               target="_blank">Health / Token Durumu</a>

            <a class="btn btn-outline-secondary"
               href="<?= site_url('panel/social-accounts/meta/cron?key=' . urlencode(getenv('META_CRON_SECRET') ?: getenv('IDEMPOTENCY_SECRET'))) ?>"
               target="_blank">Cron (Refresh) Çalıştır</a>

            <a class="btn btn-outline-secondary"
               href="<?= site_url('panel/social-accounts/meta/publish-test') ?>">Instagram Test Paylaşımı</a>

            <div class="small text-muted mt-1">
              Not: Token’ların “düşmemesi” için sistem periyodik health + refresh yapar.
              Kullanıcı şifre değiştirirse / güvenlik tetiklenirse yeniden bağlanma gerekebilir.
            </div>
          </div>
        </div>

        <?php if (!empty($debug)): ?>
          <div class="card mt-3">
            <div class="card-header">Debug (dev)</div>
            <div class="card-body">
              <pre style="max-height:340px; overflow:auto;"><?= esc(json_encode($debug, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>

  <?php endif; ?>

</div>

<?= $this->endSection() ?>
