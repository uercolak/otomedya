<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">

  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Instagram Test Paylaşımı</h3>
      <div class="text-muted">Bağlı hesapta Post / Reels / Story test edebilirsin.</div>
    </div>
    <a href="<?= site_url('panel/social-accounts/meta/wizard') ?>" class="btn btn-outline-secondary">Geri</a>
  </div>

  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">

      <form method="post" action="<?= site_url('panel/social-accounts/meta/test-publish') ?>">
        <?= csrf_field() ?>

        <div class="row g-3">
          <div class="col-lg-6">
            <label class="form-label">Hesap</label>
            <select class="form-select" name="social_account_id" required>
              <option value="">Seç...</option>
              <?php foreach (($accounts ?? []) as $a): ?>
                <option value="<?= (int)$a['id'] ?>">
                  <?= esc(($a['username'] ?: $a['name']) . ' (IG: ' . $a['external_id'] . ')') ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-lg-3">
            <label class="form-label">Tip</label>
            <!-- ÖNEMLİ: value'lar controller ile birebir -->
            <select class="form-select" name="type" required>
              <option value="post" selected>Post</option>
              <option value="reels">Reels</option>
              <option value="story">Story</option>
            </select>
          </div>

          <div class="col-lg-3">
            <label class="form-label">Media Türü</label>
            <!-- ÖNEMLİ: value'lar controller ile birebir -->
            <select class="form-select" name="media_kind" required>
              <option value="image" selected>Image</option>
              <option value="video">Video</option>
            </select>
          </div>

          <div class="col-12">
            <label class="form-label">Media URL</label>
            <input class="form-control" name="media_url"
                   value="<?= esc((string)($default_media_url ?? '')) ?>"
                   placeholder="https://..." required>
            <div class="form-text">
              Reels için video URL kullan. Story için image veya video olabilir. Video URL erişilebilir olmalı (https önerilir).
            </div>
          </div>

          <div class="col-12">
            <label class="form-label">Caption</label>
            <input class="form-control" name="caption" value="Otomedya test ✅">
          </div>
        </div>

        <div class="mt-3 d-flex gap-2">
          <button class="btn btn-primary" type="submit">Paylaşımı Tetikle</button>
          <a class="btn btn-outline-secondary" href="<?= site_url('panel/social-accounts/meta/wizard') ?>">Wizard</a>
        </div>

      </form>

    </div>
  </div>

</div>

<?= $this->endSection() ?>
