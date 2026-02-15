<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<?php
  $status = (string)($row['status'] ?? '');
  [$badgeClass, $badgeText] = ui_status_badge($status);

  $platformRaw = strtolower((string)($row['platform'] ?? ''));
  $platform = strtoupper((string)($row['platform'] ?? ''));

  if (!empty($row['sa_username'])) $accLabel = '@' . $row['sa_username'];
  elseif (!empty($row['sa_name'])) $accLabel = (string)$row['sa_name'];
  else $accLabel = 'Hesap #' . (int)($row['account_id'] ?? 0);

  $contentLabel = !empty($row['content_title'])
    ? ('#' . (int)$row['content_id'] . ' — ' . $row['content_title'])
    : ('İçerik #' . (int)($row['content_id'] ?? 0));

  $permalink = '';
  $metaJson = (string)($row['meta_json'] ?? '');
  if ($metaJson) {
    $metaArr = json_decode($metaJson, true);
    if (is_array($metaArr)) $permalink = (string)($metaArr['meta']['permalink'] ?? '');
  }

  $mmjHas = !empty($mmj) && !empty($mmj['creation_id']);
?>

<div class="page-header d-flex justify-content-between align-items-start">
  <div>
    <h1 class="page-title">Paylaşım #<?= (int)$row['id'] ?></h1>
    <p class="text-muted mb-4"><?= esc($platform) ?> · <?= esc($accLabel) ?> · <?= esc($contentLabel) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= site_url('dealer/publishes') ?>">Geri</a>
  </div>
</div>

<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>
<?php if (session('success')): ?>
  <div class="alert alert-success"><?= esc(session('success')) ?></div>
<?php endif; ?>

<div class="card mb-3">
  <div class="card-body">
    <div class="row g-3 align-items-start">
      <div class="col-md-3">
        <div class="text-muted small">Durum</div>
        <div><span class="<?= esc($badgeClass) ?>"><?= esc($badgeText) ?></span></div>
      </div>

      <div class="col-md-3">
        <div class="text-muted small">Planlanan Zaman</div>
        <div class="fw-semibold"><?= esc(ui_dt($row['schedule_at'] ?? null)) ?></div>
      </div>

      <div class="col-md-3">
        <div class="text-muted small">Yayınlanan</div>
        <div class="fw-semibold"><?= esc(ui_dt($row['published_at'] ?? null)) ?></div>
      </div>

      <div class="col-md-3">
        <div class="text-muted small">Bağlantı</div>
        <?php if ($permalink): ?>
          <a class="btn btn-sm btn-outline-primary"
             href="<?= esc($permalink) ?>" target="_blank" rel="noreferrer">
            Aç
          </a>
        <?php elseif (!empty($row['remote_id'])): ?>
          <div class="fw-semibold"><code><?= esc((string)$row['remote_id']) ?></code></div>
          <div class="text-muted small">Permalink yok, Post ID gösteriliyor.</div>
        <?php else: ?>
          <div class="text-muted">—</div>
        <?php endif; ?>
      </div>
    </div>

    <?php if (!empty($row['error'])): ?>
      <hr>
      <div class="alert alert-danger mb-0">
        <strong>Hata:</strong> <?= esc((string)$row['error']) ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div class="card mb-3">
  <div class="card-header d-flex justify-content-between align-items-center">
    <div class="fw-semibold">Bayi İşlemleri</div>
    <div class="text-muted small">Sadece kendi kullanıcıların için müdahale</div>
  </div>

  <div class="card-body d-flex flex-wrap gap-2">
    <?php if ($mmjHas): ?>
      <form method="post" action="<?= site_url('dealer/publishes/' . (int)$row['id'] . '/check') ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline-primary" type="submit">Şimdi Kontrol Et</button>
      </form>
    <?php endif; ?>

    <form method="post" action="<?= site_url('dealer/publishes/' . (int)$row['id'] . '/retry') ?>">
      <?= csrf_field() ?>
      <button class="btn btn-primary" type="submit">Tekrar Dene</button>
    </form>

    <form method="post" action="<?= site_url('dealer/publishes/' . (int)$row['id'] . '/reset-job') ?>"
          onsubmit="return confirm('Bu işlem kuyruğu sıfırlar ve paylaşımı tekrar çalıştırır. Devam?')">
      <?= csrf_field() ?>
      <button class="btn btn-warning" type="submit">İşi Sıfırla</button>
    </form>

    <form method="post" action="<?= site_url('dealer/publishes/' . (int)$row['id'] . '/cancel') ?>"
          onsubmit="return confirm('Bu paylaşımı iptal etmek istiyor musun?')">
      <?= csrf_field() ?>
      <button class="btn btn-outline-danger" type="submit">İptal Et</button>
    </form>
  </div>
</div>

<div class="card">
  <div class="card-body">
    <h5 class="mb-2">İçerik</h5>
    <div class="mb-2 text-muted"><?= esc($contentLabel) ?></div>

    <?php if (!empty($row['content_text'])): ?>
      <pre class="p-3 bg-light rounded" style="white-space: pre-wrap;"><?= esc((string)($row['content_text'])) ?></pre>
    <?php else: ?>
      <div class="text-muted">Bu içerik için metin bulunamadı.</div>
    <?php endif; ?>
  </div>
</div>

<?= $this->endSection() ?>
