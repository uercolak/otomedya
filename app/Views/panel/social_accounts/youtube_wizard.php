<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">YouTube Bağla</h3>
      <div class="text-muted">Google hesabın ile giriş yapıp YouTube kanalını bağla.</div>
    </div>

    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-outline-secondary">Geri</a>

      <form method="post" action="<?= site_url('panel/social-accounts/youtube/disconnect') ?>">
        <?= csrf_field() ?>
        <button type="submit" class="btn btn-outline-danger"
                onclick="return confirm('YouTube bağlantısını sıfırlamak istiyor musun?')">
          Sıfırla
        </button>
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
    $hasConnected = (bool)($hasConnected ?? false);
    $channel = $channel ?? null;
    $debug = $debug ?? [];
  ?>

  <?php if (!$hasConnected): ?>
    <div class="card">
      <div class="card-body">
        <div class="fw-semibold mb-1">Bağlantı Başlat</div>
        <div class="text-muted small mb-3">
          YouTube kanalını bağlamak için Google hesabınla giriş yapacaksın.
          İlk bağlantıda “izin ver” ekranı çıkar.
        </div>

        <a href="<?= site_url('panel/social-accounts/youtube/connect') ?>" class="btn btn-danger">
          Google ile Bağlan
        </a>

        <div class="text-muted small mt-3">
          Not: Yetkilendirme için <code>youtube.upload</code> ve <code>youtube.readonly</code> izinleri gerekir.
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="card">
      <div class="card-body d-flex gap-3 align-items-center">
        <?php if (!empty($channel['avatar'])): ?>
          <img src="<?= esc($channel['avatar']) ?>" alt="" style="width:64px;height:64px;border-radius:16px;object-fit:cover;">
        <?php else: ?>
          <div class="bg-light border" style="width:64px;height:64px;border-radius:16px;"></div>
        <?php endif; ?>

        <div class="flex-grow-1">
          <div class="fw-semibold"><?= esc($channel['title'] ?? 'YouTube Kanalı') ?></div>
          <div class="text-muted small">
            Kanal ID: <code><?= esc($channel['id'] ?? '') ?></code>
            <?php if (!empty($channel['customUrl'])): ?>
              • <span><?= esc($channel['customUrl']) ?></span>
            <?php endif; ?>
          </div>
        </div>

        <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-outline-primary">
          Hesaplara Dön
        </a>
      </div>
    </div>
  <?php endif; ?>

  <?php if (!empty($debug)): ?>
    <div class="card mt-3">
      <div class="card-header">Debug (dev)</div>
      <div class="card-body">
        <pre style="max-height:340px; overflow:auto;"><?= esc(json_encode($debug, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE)) ?></pre>
      </div>
    </div>
  <?php endif; ?>

</div>

<?= $this->endSection() ?>
