<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0">Hazır Şablonlar</h3>
      <div class="text-muted">Aktif şablonlardan birini seçip düzenleyerek hızlıca planlayabilirsin.</div>
    </div>
    <a href="<?= site_url('panel/planner') ?>" class="btn btn-outline-secondary">Planner</a>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <form class="card p-3 mb-3" method="get" action="<?= site_url('panel/templates') ?>">
    <div class="row g-2">
      <div class="col-lg-4">
        <input class="form-control" name="q" value="<?= esc($filters['q'] ?? '') ?>" placeholder="Ara (başlık/açıklama)">
      </div>
      <div class="col-lg-2">
        <select class="form-select" name="type">
          <option value="">Tür (hepsi)</option>
          <?php foreach (($typeOptions ?? []) as $o): ?>
            <option value="<?= esc($o) ?>" <?= (($filters['type'] ?? '') === $o) ? 'selected' : '' ?>><?= esc($o) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2">
        <select class="form-select" name="scope">
          <option value="">Scope (hepsi)</option>
          <?php foreach (($scopeOptions ?? []) as $o): ?>
            <option value="<?= esc($o) ?>" <?= (($filters['scope'] ?? '') === $o) ? 'selected' : '' ?>><?= esc($o) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2">
        <select class="form-select" name="format">
          <option value="">Format (hepsi)</option>
          <?php foreach (($formatOptions ?? []) as $o): ?>
            <option value="<?= esc($o) ?>" <?= (($filters['format'] ?? '') === $o) ? 'selected' : '' ?>><?= esc($o) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-lg-2 d-grid">
        <button class="btn btn-outline-primary">Filtrele</button>
      </div>
    </div>
  </form>

  <div class="row g-3">
    <?php if (empty($rows)): ?>
      <div class="col-12">
        <div class="alert alert-warning mb-0">Henüz aktif şablon yok.</div>
      </div>
    <?php endif; ?>

    <?php foreach (($rows ?? []) as $t): ?>
      <?php
        $thumb = !empty($t['thumb_path']) ? base_url($t['thumb_path']) : (!empty($t['file_path']) ? base_url($t['file_path']) : '');
        $w = (int)($t['width'] ?? 0);
        $h = (int)($t['height'] ?? 0);
      ?>
      <div class="col-12 col-sm-6 col-lg-4 col-xl-3">
        <div class="card h-100">
          <?php if ($thumb): ?>
            <img src="<?= esc($thumb) ?>" class="card-img-top" style="aspect-ratio: 1/1; object-fit: cover;">
          <?php endif; ?>
          <div class="card-body">
            <div class="fw-semibold"><?= esc($t['name'] ?? '') ?></div>
            <div class="small text-muted"><?= esc($t['description'] ?? '') ?></div>

            <div class="small mt-2 text-muted">
              <?= esc($t['platform_scope'] ?? '') ?> • <?= esc($t['format_key'] ?? '') ?> • <?= $w ?>×<?= $h ?>
            </div>
          </div>
          <div class="card-footer bg-white border-0 pt-0">
            <a class="btn btn-primary w-100" href="<?= site_url('panel/templates/' . (int)$t['id'] . '/edit') ?>">
              Düzenle &amp; Planla
            </a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<?= $this->endSection() ?>
