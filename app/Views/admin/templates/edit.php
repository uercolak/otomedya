<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3" style="max-width: 980px;">
  <h3 class="mb-1"><?= esc($pageTitle ?? 'Şablon Düzenle') ?></h3>
  <div class="text-muted mb-3">Dosya yüklemezsen mevcut medya korunur. Görsel şablonlarda boyut strict kontrol edilir.</div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="d-flex align-items-center gap-3">
        <?php if (!empty($row['base_media_id'])): ?>
          <?php if (($row['type'] ?? '') === 'video'): ?>
            <video
              src="<?= site_url('media/'.(int)$row['base_media_id']) ?>"
              style="width:140px;height:92px;border-radius:12px;border:1px solid #eee;object-fit:cover;"
              muted
              controls
              playsinline
            ></video>
          <?php else: ?>
            <img
              src="<?= site_url('media/'.(int)$row['base_media_id']) ?>"
              style="width:140px;height:auto;border-radius:12px;border:1px solid #eee;"
              alt="Önizleme"
            >
          <?php endif; ?>
        <?php endif; ?>

        <div class="small text-muted">
          <div><b>ID:</b> <?= (int)$row['id'] ?></div>
          <div><b>Mevcut:</b> <?= esc(($row['width'] && $row['height']) ? ($row['width'].'x'.$row['height']) : '-') ?></div>
          <div><b>Format:</b> <?= esc($row['format_key'] ?? '-') ?></div>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <form method="post" action="<?= site_url('admin/templates/'.(int)$row['id']) ?>" enctype="multipart/form-data">
        <?= csrf_field() ?>

        <div class="mb-3">
          <label class="form-label">Başlık</label>
          <input class="form-control" name="name" required value="<?= esc(old('name', $row['name'] ?? '')) ?>">
        </div>

        <div class="mb-3">
          <label class="form-label">Açıklama</label>
          <textarea class="form-control" name="description" rows="2"><?= esc(old('description', $row['description'] ?? '')) ?></textarea>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-6">
            <label class="form-label">Tema (Koleksiyon)</label>
            <select class="form-select" name="collection_id" required>
              <option value="">Tema seç</option>
              <?php foreach (($collections ?? []) as $c): ?>
                <option value="<?= (int)$c['id'] ?>"
                  <?= (old('collection_id', (string)($row['collection_id'] ?? '')) == (string)$c['id']) ? 'selected' : '' ?>>
                  <?= esc($c['name']) ?>
                </option>
              <?php endforeach; ?>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label">Durum</label>
            <select class="form-select" name="is_active">
              <option value="1" <?= (old('is_active', (string)($row['is_active'] ?? '1'))==='1')?'selected':'' ?>>Aktif</option>
              <option value="0" <?= (old('is_active', (string)($row['is_active'] ?? '1'))==='0')?'selected':'' ?>>Pasif</option>
            </select>
          </div>

          <div class="col-md-3">
            <label class="form-label d-block">Öne çıkan</label>
            <div class="form-check mt-2">
              <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="is_featured"
                <?= (old('is_featured', (string)($row['is_featured'] ?? '0'))==='1') ? 'checked' : '' ?>>
              <label class="form-check-label" for="is_featured">Öne çıkar</label>
            </div>
          </div>
        </div>

        <div class="row g-2 mb-3">
          <div class="col-md-4">
            <label class="form-label">Tür</label>
            <select class="form-select" name="type" required>
              <option value="image" <?= (old('type', $row['type'] ?? 'image')==='image')?'selected':'' ?>>Image</option>
              <option value="video" <?= (old('type', $row['type'] ?? 'image')==='video')?'selected':'' ?>>Video</option>
            </select>
            <div class="form-text">Tür değiştirirsen yeni dosya yüklemen gerekir.</div>
          </div>

          <div class="col-md-4">
            <label class="form-label">Platform scope</label>
            <select class="form-select" name="platform_scope" required>
              <option value="instagram" <?= (old('platform_scope', $row['platform_scope'] ?? '')==='instagram')?'selected':'' ?>>Instagram</option>
              <option value="facebook"  <?= (old('platform_scope', $row['platform_scope'] ?? '')==='facebook')?'selected':'' ?>>Facebook</option>
              <option value="tiktok"    <?= (old('platform_scope', $row['platform_scope'] ?? '')==='tiktok')?'selected':'' ?>>TikTok</option>
              <option value="youtube"   <?= (old('platform_scope', $row['platform_scope'] ?? '')==='youtube')?'selected':'' ?>>YouTube</option>
            </select>
          </div>

          <div class="col-md-4">
            <label class="form-label">Format (image için)</label>
            <select class="form-select" name="format_key">
              <option value="">(Video ise boş)</option>
              <?php foreach (($formats ?? []) as $k => $f): ?>
                <option value="<?= esc($k) ?>" <?= (old('format_key', (string)($row['format_key'] ?? ''))===$k)?'selected':'' ?>>
                  <?= esc($f['label']) ?> (<?= (int)$f['w'] ?>x<?= (int)$f['h'] ?>)
                </option>
              <?php endforeach; ?>
            </select>
          </div>
        </div>

        <div class="mb-3">
          <label class="form-label">Dosya (opsiyonel)</label>
          <input class="form-control" type="file" name="file">
          <div class="form-text">Yeni dosya seçmezsen mevcut dosya korunur. Image ise format ile tam aynı boyutta olmalı.</div>
        </div>

        <div class="d-flex gap-2">
          <a class="btn btn-light" href="<?= site_url('admin/templates') ?>">İptal</a>
          <button class="btn btn-primary">Güncelle</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?= $this->endSection() ?>