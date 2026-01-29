<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0"><?= esc($pageTitle ?? 'Temalar') ?></h3>
      <div class="text-muted">Şablonları kategorize etmek için temaları buradan yönet.</div>
    </div>
    <a class="btn btn-primary" href="<?= site_url('admin/template-collections/new') ?>">
      <i class="bi bi-plus-circle me-1"></i> Yeni Tema
    </a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-2">
        <div class="col-md-6">
          <input class="form-control" name="q" placeholder="Ara (tema adı/slug)" value="<?= esc($filters['q'] ?? '') ?>">
        </div>
        <div class="col-md-3">
          <select class="form-select" name="active">
            <option value="">Durum (hepsi)</option>
            <option value="1" <?= (($filters['active'] ?? '')==='1')?'selected':'' ?>>Aktif</option>
            <option value="0" <?= (($filters['active'] ?? '')==='0')?'selected':'' ?>>Pasif</option>
          </select>
        </div>
        <div class="col-md-3 d-flex justify-content-end">
          <button class="btn btn-outline-secondary">Filtrele</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="table-responsive">
      <table class="table align-middle mb-0">
        <thead>
          <tr>
            <th>#</th>
            <th>Tema</th>
            <th>Slug</th>
            <th>Sıra</th>
            <th>Durum</th>
            <th class="text-end">İşlem</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach (($rows ?? []) as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td>
              <div class="fw-semibold"><?= esc($r['name']) ?></div>
              <?php if (!empty($r['description'])): ?>
                <div class="text-muted small"><?= esc($r['description']) ?></div>
              <?php endif; ?>
            </td>
            <td><code><?= esc($r['slug']) ?></code></td>
            <td><?= (int)($r['sort_order'] ?? 0) ?></td>
            <td>
              <?php if ((int)($r['is_active'] ?? 0) === 1): ?>
                <span class="badge text-bg-success">Aktif</span>
              <?php else: ?>
                <span class="badge text-bg-secondary">Pasif</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <a class="btn btn-sm btn-outline-secondary" href="<?= site_url('admin/template-collections/'.(int)$r['id'].'/edit') ?>">Düzenle</a>

              <form method="post" action="<?= site_url('admin/template-collections/'.(int)$r['id'].'/toggle') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-primary">Aktif/Pasif</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="6" class="text-center text-muted py-4">Kayıt yok</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
