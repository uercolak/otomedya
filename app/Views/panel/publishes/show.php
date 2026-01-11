<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
  $platform = strtoupper((string)($row['platform'] ?? ''));

  if (!empty($row['sa_username'])) $accLabel = '@' . $row['sa_username'];
  elseif (!empty($row['sa_name'])) $accLabel = (string)$row['sa_name'];
  else $accLabel = 'Hesap #' . (int)($row['account_id'] ?? 0);

  $contentLabel = !empty($row['content_title'])
    ? ('#' . (int)$row['content_id'] . ' — ' . $row['content_title'])
    : ('İçerik #' . (int)($row['content_id'] ?? 0));

  $status   = (string)($row['status'] ?? '');
  $remoteId = (string)($row['remote_id'] ?? '');
  $isUrl    = preg_match('~^https?://~i', $remoteId) === 1;

  $scheduleAt  = (string)($row['schedule_at'] ?? '');
  $publishedAt = (string)($row['published_at'] ?? '');
?>

<div class="page-header d-flex justify-content-between align-items-start">
  <div>
    <div class="fw-semibold" style="font-size:18px;">
      Paylaşım #<?= (int)$row['id'] ?>
    </div>
    <p class="text-muted mb-0">
      <?= esc($platform) ?> · <?= esc($accLabel) ?> · <?= esc($contentLabel) ?>
    </p>
  </div>

  <div class="d-flex gap-2">
    <?php if ($status === 'published' && $remoteId !== '' && $isUrl): ?>
      <a class="btn btn-outline-primary" href="<?= esc($remoteId) ?>" target="_blank" rel="noopener">
        Önizle
      </a>
    <?php endif; ?>

    <a class="btn btn-outline-secondary" href="<?= site_url('panel/publishes') ?>">Geri</a>
  </div>
</div>

<div class="card mb-3">
  <div class="card-body">
    <div class="row g-3">
      <div class="col-md-3">
        <div class="text-muted small">Durum</div>
        <?= view('partials/status_badge', ['status' => $status]) ?>
      </div>

      <div class="col-md-3">
        <div class="text-muted small">Planlanan Zaman</div>
        <div><?= esc($scheduleAt !== '' ? $scheduleAt : '—') ?></div>
      </div>

      <div class="col-md-3">
        <div class="text-muted small">Yayınlanan</div>
        <div><?= esc($publishedAt !== '' ? $publishedAt : '—') ?></div>
      </div>

      <div class="col-md-3">
        <div class="text-muted small">Paylaşım Bağlantısı / ID</div>

        <?php if ($remoteId === ''): ?>
          <div class="text-muted">—</div>
        <?php elseif ($isUrl): ?>
          <a href="<?= esc($remoteId) ?>" target="_blank" rel="noopener" class="fw-semibold text-decoration-none">
            Bağlantıyı aç
          </a>
          <div class="text-muted small mt-1"><?= esc($remoteId) ?></div>
        <?php else: ?>
          <div class="fw-semibold"><?= esc($remoteId) ?></div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($row['error'])): ?>
      <div class="alert alert-danger mt-3 mb-0">
        <strong>Hata:</strong> <?= esc((string)$row['error']) ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="mb-2">İçerik</h5>
    <div class="mb-2 text-muted"><?= esc($contentLabel) ?></div>

    <?php if (!empty($row['content_text'])): ?>
      <pre class="p-3 bg-light rounded" style="white-space: pre-wrap;"><?= esc((string)$row['content_text']) ?></pre>
    <?php else: ?>
      <div class="text-muted">Bu içerik için metin bulunamadı.</div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
