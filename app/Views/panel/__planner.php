<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?= $this->include('panel/planner/styles') ?>

<div class="container-fluid py-3">
  <?= $this->include('panel/planner/header') ?>

  <?= $this->include('panel/planner/alerts') ?>

  <form action="<?= site_url('panel/planner') ?>" method="post" enctype="multipart/form-data" class="row g-3">
    <?= csrf_field() ?>

    <?php if (!empty($prefill['id'])): ?>
      <input type="hidden" name="content_id" value="<?= (int)$prefill['id'] ?>">
    <?php endif; ?>

    <div class="col-lg-7">
      <?= $this->include('panel/planner/content_form') ?>
      <?= $this->include('panel/planner/platform_settings') ?>
    </div>

    <div class="col-lg-5">
      <?= $this->include('panel/planner/accounts') ?>
      <?= $this->include('panel/planner/schedule') ?>
    </div>

    <div class="col-12">
      <?= $this->include('panel/planner/preview') ?>
    </div>
  </form>
</div>

<?= $this->include('panel/planner/scripts') ?>

<?= $this->endSection() ?>
