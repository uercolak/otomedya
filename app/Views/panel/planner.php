<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?= $this->include('panel/planner/styles') ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Yeni Gönderi Planla</h3>
      <div class="text-muted">Tek ekrandan içerik oluştur, hesap seç, tarih belirle ve paylaşımı planla.</div>
    </div>
    <div class="d-flex gap-2">
      <a href="<?= site_url('panel/calendar') ?>" class="btn btn-soft">Takvime Dön</a>
      <a href="<?= site_url('panel/templates') ?>" class="btn btn-brand">Şablondan Oluştur</a>
    </div>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <form action="<?= site_url('panel/planner') ?>" method="post" enctype="multipart/form-data" class="row g-3" id="plannerForm">
    <?= csrf_field() ?>

    <?php if (!empty($prefill['id'])): ?>
      <input type="hidden" name="content_id" value="<?= (int)$prefill['id'] ?>">
    <?php endif; ?>

    <div class="col-lg-7">
      <?= $this->include('panel/planner/content_form') ?>
    </div>

    <div class="col-lg-5">
      <?= $this->include('panel/planner/accounts') ?>
      <?= $this->include('panel/planner/platform_settings') ?>
      <?= $this->include('panel/planner/schedule') ?>
    </div>

    <div class="col-12">
      <?= $this->include('panel/planner/preview') ?>
    </div>
  </form>
</div>

<?= $this->include('panel/planner/scripts') ?>

<?= $this->endSection() ?>
