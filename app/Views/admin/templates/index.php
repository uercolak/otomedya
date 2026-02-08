<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
// --- UI label helpers (root panel) ---
$scopeLabel = static function (?string $scope): string {
  $scope = strtolower(trim((string)$scope));
  return match ($scope) {
    'instagram' => 'Instagram',
    'facebook'  => 'Facebook',
    'tiktok'    => 'TikTok',
    'youtube'   => 'YouTube',
    default     => ($scope !== '' ? $scope : '-'),
  };
};

$typeLabel = static function (?string $type): string {
  $type = strtolower(trim((string)$type));
  return match ($type) {
    'image' => 'Görsel',
    'video' => 'Video',
    default => ($type !== '' ? $type : '-'),
  };
};

$formatKeyToLabel = static function (?string $key, array $formats = []): string {
  $key = trim((string)$key);
  if ($key === '' || $key === '0') return '-';

  // 1) Controller'dan gelen $formats içinde label varsa onu kullan
  if (!empty($formats[$key]['label'])) {
    return (string)$formats[$key]['label'];
  }

  // 2) Fallback map (olmazsa bile en azından kötü görünmesin)
  $map = [
    'ig_post_1_1'    => 'Instagram Post (1:1)',
    'ig_post_4_5'    => 'Instagram Post (4:5)',
    'ig_story_9_16'  => 'Instagram Story (9:16)',
    'ig_reels_9_16'  => 'Instagram Reels (9:16)',

    'fb_post_1_1'    => 'Facebook Post (1:1)',
    'fb_story_9_16'  => 'Facebook Story (9:16)',

    'tt_video_9_16'  => 'TikTok (9:16)',

    // Controller'daki key’lerle uyumlu:
    'yt_thumb_16_9'  => 'YouTube Thumbnail (16:9)',
    'yt_short_9_16'  => 'YouTube Shorts (9:16)',
  ];

  return $map[$key] ?? $key;
};
?>

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
        <div class="col-md-3">
          <input class="form-control" name="q" placeholder="Ara (başlık/açıklama)" value="<?= esc($filters['q'] ?? '') ?>">
        </div>

        <!-- ✅ Tema filtresi -->
        <div class="col-md-2">
          <select class="form-select" name="collection">
            <option value="">Tema (hepsi)</option>
            <?php foreach (($collections ?? []) as $c): ?>
              <option value="<?= (int)$c['id'] ?>" <?= (($filters['collection'] ?? '')==(string)$c['id'])?'selected':'' ?>>
                <?= esc($c['name']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-1">
          <select class="form-select" name="type">
            <option value="">Tür</option>
            <option value="image" <?= (($filters['type'] ?? '')==='image')?'selected':'' ?>>Görsel</option>
            <option value="video" <?= (($filters['type'] ?? '')==='video')?'selected':'' ?>>Video</option>
          </select>
        </div>

        <div class="col-md-2">
          <select class="form-select" name="scope">
            <option value="">Kapsam</option>
            <option value="instagram" <?= (($filters['scope'] ?? '')==='instagram')?'selected':'' ?>>Instagram</option>
            <option value="facebook"  <?= (($filters['scope'] ?? '')==='facebook')?'selected':'' ?>>Facebook</option>
            <option value="tiktok"    <?= (($filters['scope'] ?? '')==='tiktok')?'selected':'' ?>>TikTok</option>
            <option value="youtube"   <?= (($filters['scope'] ?? '')==='youtube')?'selected':'' ?>>YouTube</option>
          </select>
        </div>

        <div class="col-md-2">
          <select class="form-select" name="format">
            <option value="">Boyut Türü</option>
            <?php foreach (($formats ?? []) as $k => $f): ?>
              <option value="<?= esc($k) ?>" <?= (($filters['format'] ?? '')===$k)?'selected':'' ?>>
                <?= esc($f['label']) ?> (<?= (int)$f['w'] ?>x<?= (int)$f['h'] ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-1">
          <select class="form-select" name="active">
            <option value="">Durum</option>
            <option value="1" <?= (($filters['active'] ?? '')==='1')?'selected':'' ?>>Aktif</option>
            <option value="0" <?= (($filters['active'] ?? '')==='0')?'selected':'' ?>>Pasif</option>
          </select>
        </div>

        <!-- ✅ Öne çıkan filtresi -->
        <div class="col-md-1">
          <select class="form-select" name="featured">
            <option value="">Öne</option>
            <option value="1" <?= (($filters['featured'] ?? '')==='1')?'selected':'' ?>>Evet</option>
            <option value="0" <?= (($filters['featured'] ?? '')==='0')?'selected':'' ?>>Hayır</option>
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
            <th>Tema</th>
            <th>Tür</th>
            <th>Kapsam</th>
            <th>Boyut Türü</th>
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
                <?php if (($r['type'] ?? '') === 'video'): ?>
                  <video
                    src="<?= site_url('media/'.(int)$r['base_media_id']) ?>"
                    style="width:96px;height:64px;border-radius:10px;border:1px solid #eee;object-fit:cover;"
                    muted
                    preload="metadata"
                    playsinline
                  ></video>
                <?php else: ?>
                  <img
                    src="<?= site_url('media/'.(int)$r['base_media_id']) ?>"
                    style="width:96px;height:auto;border-radius:10px;border:1px solid #eee;"
                    alt="Önizleme"
                  >
                <?php endif; ?>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>

            <td>
              <div class="fw-semibold d-flex align-items-center gap-2">
                <span><?= esc($r['name'] ?? '') ?></span>
                <?php if ((int)($r['is_featured'] ?? 0) === 1): ?>
                  <span class="badge text-bg-warning">Öne Çıkan</span>
                <?php endif; ?>
              </div>
              <div class="text-muted small"><?= esc($r['description'] ?? '') ?></div>
            </td>

            <!-- ✅ Tema -->
            <td>
              <?php if (!empty($r['collection_name'])): ?>
                <span class="badge text-bg-light border"><?= esc($r['collection_name']) ?></span>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>

            <td><?= esc($typeLabel($r['type'] ?? '')) ?></td>
            <td><?= esc($scopeLabel($r['platform_scope'] ?? '')) ?></td>
            <td><?= esc($formatKeyToLabel($r['format_key'] ?? '', $formats ?? [])) ?></td>

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
          <tr><td colspan="10" class="text-center text-muted py-4">Kayıt yok</td></tr>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
