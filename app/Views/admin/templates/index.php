<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">
  <div class="d-flex align-items-center justify-content-between mb-3">
    <div>
      <h3 class="mb-0"><?= esc($pageTitle ?? 'Hazır Şablonlar') ?></h3>
      <div class="text-muted">Root panelden şablon yükleyip aktif/pasif yönet.</div>
    </div>
    <a class="btn btn-primary" href="<?= site_url('admin/templates/new') ?>">
      <i class="bi bi-plus-circle me-1"></i> Yeni Şablon
    </a>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <form class="row g-2">
        <div class="col-md-4">
          <input class="form-control" name="q" placeholder="Ara (başlık/açıklama)" value="<?= esc($filters['q'] ?? '') ?>">
        </div>
        <div class="col-md-2">
          <select class="form-select" name="type">
            <option value="">Tür (hepsi)</option>
            <option value="image" <?= (($filters['type'] ?? '')==='image')?'selected':'' ?>>Image</option>
            <option value="video" <?= (($filters['type'] ?? '')==='video')?'selected':'' ?>>Video</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="scope">
            <option value="">Scope (hepsi)</option>
            <option value="universal" <?= (($filters['scope'] ?? '')==='universal')?'selected':'' ?>>Universal</option>
            <option value="instagram" <?= (($filters['scope'] ?? '')==='instagram')?'selected':'' ?>>Instagram</option>
            <option value="facebook" <?= (($filters['scope'] ?? '')==='facebook')?'selected':'' ?>>Facebook</option>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="format">
            <option value="">Format (hepsi)</option>
            <?php foreach (($formats ?? []) as $k => $f): ?>
              <option value="<?= esc($k) ?>" <?= (($filters['format'] ?? '')===$k)?'selected':'' ?>>
                <?= esc($f['label']) ?> (<?= (int)$f['w'] ?>x<?= (int)$f['h'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <select class="form-select" name="active">
            <option value="">Durum (hepsi)</option>
            <option value="1" <?= (($filters['active'] ?? '')==='1')?'selected':'' ?>>Aktif</option>
            <option value="0" <?= (($filters['active'] ?? '')==='0')?'selected':'' ?>>Pasif</option>
          </select>
        </div>
        <div class="col-12 d-flex justify-content-end">
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
            <th>Önizleme</th>
            <th>Başlık</th>
            <th>Tür</th>
            <th>Scope</th>
            <th>Format</th>
            <th>Boyut</th>
            <th>Durum</th>
            <th class="text-end">İşlem</th>
          </tr>
        </thead>
        <tbody>
        <?php foreach (($rows ?? []) as $r): ?>
          <tr>
            <td><?= (int)$r['id'] ?></td>
            <td style="width:110px;">
              <?php if (!empty($r['base_media_id'])): ?>
                <img src="<?= site_url('media/'.(int)$r['base_media_id']) ?>" style="width:96px;height:auto;border-radius:10px;border:1px solid #eee;">
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <div class="fw-semibold"><?= esc($r['name'] ?? '') ?></div>
              <div class="text-muted small"><?= esc($r['description'] ?? '') ?></div>
            </td>
            <td><?= esc($r['type'] ?? '') ?></td>
            <td><?= esc($r['platform_scope'] ?? '') ?></td>
            <td><?= esc($r['format_key'] ?? '') ?></td>
            <td>
              <?php if (!empty($r['width']) && !empty($r['height'])): ?>
                <?= (int)$r['width'] ?>x<?= (int)$r['height'] ?>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
            <td>
              <?php if ((int)($r['is_active'] ?? 0) === 1): ?>
                <span class="badge text-bg-success">Aktif</span>
              <?php else: ?>
                <span class="badge text-bg-secondary">Pasif</span>
              <?php endif; ?>
            </td>
            <td class="text-end">
              <form method="post" action="<?= site_url('admin/templates/'.(int)$r['id'].'/toggle') ?>" class="d-inline">
                <?= csrf_field() ?>
                <button class="btn btn-sm btn-outline-primary">Aktif/Pasif</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (empty($rows)): ?>
          <tr><td colspan="9" class="text-center text-muted py-4">Kayıt yok</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
