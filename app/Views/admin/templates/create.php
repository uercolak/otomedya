<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3" style="max-width: 980px;">
  <h3 class="mb-1"><?= esc($pageTitle ?? 'Yeni Şablon') ?></h3>
  <div class="text-muted mb-3">Görsel şablonlarda boyut strict kontrol edilir.</div>

  <div class="card">
    <div class="card-body">
      <form method="post" action="<?= site_url('admin/templates') ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label">Başlık</label>
          <input class="form-control" name="name" required value="<?= esc(old('name')) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Açıklama</label>
          <textarea class="form-control" name="description" rows="2"><?= esc(old('description')) ?></textarea>
        </div>

        <!-- ✅ Tema + Durum + Öne çıkan -->
        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label">Tema (Koleksiyon)</label>
            <select class="form-select" name="collection_id" required>
              <option value="">Tema seç</option>
              <?php foreach (($collections ?? []) as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (old('collection_id') == (string)$c['id']) ? 'selected' : '' ?>>
                  <?= esc($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Durum</label>
            <select class="form-select" name="is_active">
              <option value="1" <?= (old('is_active','1')==='1')?'selected':'' ?>>Aktif</option>
              <option value="0" <?= (old('is_active')==='0')?'selected':'' ?>>Pasif</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label d-block">Öne çıkan</label>
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                <?= (old('is_featured')==='1') ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_featured">
                Öne çıkar
              </label>
            </div>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <label class="form-label">Tür</label>
            <select class="form-select" name="type" required>
              <option value="image" <?= (old('type','image')==='image')?'selected':'' ?>>Image</option>
              <option value="video" <?= (old('type')==='video')?'selected':'' ?>>Video</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Platform scope</label>
            <select class="form-select" name="platform_scope" required>
              <option value="universal" <?= (old('platform_scope','universal')==='universal')?'selected':'' ?>>Universal</option>
              <option value="instagram" <?= (old('platform_scope')==='instagram')?'selected':'' ?>>Instagram</option>
              <option value="facebook" <?= (old('platform_scope')==='facebook')?'selected':'' ?>>Facebook</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Format (image için)</label>
            <select class="form-select" name="format_key">
              <option value="">(Video ise boş)</option>
              <?php foreach (($formats ?? []) as $k => $f): ?>
                <option value="<?= esc($k) ?>" <?= (old('format_key')===$k)?'selected':'' ?>>
                  <?= esc($f['label']) ?> (<?= (int)$f['w'] ?>x<?= (int)$f['h'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Dosya</label>
          <input class="form-control" type="file" name="file" required>
          <div class="form-text">Image: seçilen format ile tam aynı boyutta olmalı.</div>
        </div>

        <div class="d-flex gap-2">
          <a class="btn btn-light" href="<?= site_url('admin/templates') ?>">İptal</a>
          <button class="btn btn-primary">Kaydet</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
