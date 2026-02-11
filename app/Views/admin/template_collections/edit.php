<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0"><?= esc($pageTitle ?? 'Tema Düzenle') ?></h3>
      <div class="text-muted">Tema bilgilerini güncelle.</div>
    </div>
    <a class="btn btn-outline-secondary" href="<?= site_url('admin/template-collections') ?>">
      <i class="bi bi-arrow-left me-1"></i> Geri
    </a>
  </div>

  <?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
  <?php endif; ?>
  <?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
  <?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="post" action="<?= site_url('admin/template-collections/' . (int)$row['id']) ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="_method" value="PUT">

        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Tema Adı <span class="text-danger">*</span></label>
            <input type="text" name="name" class="form-control"
                   value="<?= esc(old('name') ?? ($row['name'] ?? '')) ?>" required>
          </div>

          <div class="col-md-6">
            <label class="form-label">Slug</label>
            <input type="text" name="slug" class="form-control"
                   value="<?= esc(old('slug') ?? ($row['slug'] ?? '')) ?>">
          </div>

          <div class="col-12">
            <label class="form-label">Açıklama</label>
            <textarea name="description" class="form-control" rows="3"><?= esc(old('description') ?? ($row['description'] ?? '')) ?></textarea>
          </div>

          <div class="col-md-4">
            <label class="form-label">Sıra</label>
            <input type="number" name="sort_order" class="form-control"
                   value="<?= esc(old('sort_order') ?? ($row['sort_order'] ?? 0)) ?>" min="0">
          </div>

          <div class="col-md-4">
            <label class="form-label">Durum</label>
            <?php
              $act = old('is_active');
              if ($act === null || $act === '') $act = (string)((int)($row['is_active'] ?? 1));
            ?>
            <select name="is_active" class="form-select">
              <option value="1" <?= ($act === '1') ? 'selected' : '' ?>>Aktif</option>
              <option value="0" <?= ($act === '0') ? 'selected' : '' ?>>Pasif</option>
            </select>
          </div>

          <div class="col-12 d-flex justify-content-end gap-2">
            <a class="btn btn-outline-secondary" href="<?= site_url('admin/template-collections') ?>">İptal</a>
            <button class="btn btn-primary">
              <i class="bi bi-save2 me-1"></i> Güncelle
            </button>
          </div>
        </div>

      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
