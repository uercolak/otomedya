<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php helper('ui'); ?>

<?php
  $status = (string)($row['status'] ?? '');
  [$badgeClass, $badgeText] = ui_status_badge($status);

  $platformRaw = strtolower((string)($row['platform'] ?? ''));
  $platform = strtoupper((string)($row['platform'] ?? ''));
  $isInstagram = ($platformRaw === 'instagram');
  $isFacebook  = ($platformRaw === 'facebook');

  if (!empty($row['sa_username'])) $accLabel = '@' . $row['sa_username'];
  elseif (!empty($row['sa_name'])) $accLabel = (string)$row['sa_name'];
  else $accLabel = 'Hesap #' . (int)($row['account_id'] ?? 0);

  $contentLabel = !empty($row['content_title'])
    ? ('#' . (int)$row['content_id'] . ' — ' . $row['content_title'])
    : ('İçerik #' . (int)($row['content_id'] ?? 0));

  // permalink (published olduktan sonra meta_json içine yazıyorsun)
  $permalink = '';
  $metaArr = null;
  $metaJson = (string)($row['meta_json'] ?? '');
  if ($metaJson) {
    $metaArr = json_decode($metaJson, true);
    if (is_array($metaArr)) {
      $permalink = (string)($metaArr['meta']['permalink'] ?? '');
    }
  }

  $mmjHas = !empty($mmj) && !empty($mmj['creation_id']);
?>

<div class="page-header d-flex justify-content-between align-items-start">
  <div>
    <h1 class="page-title">Paylaşım #<?= (int)$row['id'] ?></h1>
    <p class="text-muted mb-4"><?= esc($platform) ?> · <?= esc($accLabel) ?> · <?= esc($contentLabel) ?></p>
  </div>
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href="<?= site_url('admin/publishes') ?>">Geri</a>
    <?php if (!empty($row['job_id'])): ?>
      <a class="btn btn-outline-primary" href="<?= site_url('admin/jobs/' . (int)$row['job_id']) ?>">İş #<?= (int)$row['job_id'] ?></a>
    <?php endif; ?>
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
        <div class="text-muted small">
          <?= $isFacebook ? 'Facebook Bağlantısı' : 'Instagram Bağlantısı' ?>
        </div>

        <?php if ($permalink): ?>
          <a class="btn btn-sm btn-outline-primary"
             href="<?= esc($permalink) ?>" target="_blank" rel="noreferrer">
            <?= $isFacebook ? 'Facebook’ta Aç' : 'Instagram’da Aç' ?>
          </a>
        <?php elseif (!empty($row['remote_id'])): ?>
          <div class="fw-semibold">
            <code><?= esc((string)$row['remote_id']) ?></code>
            <?= function_exists('ui_clip_btn') ? ui_clip_btn((string)$row['remote_id']) : '' ?>
          </div>
          <div class="text-muted small">Permalink kaydedilmemiş. Post ID (remote_id) gösteriliyor.</div>
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
    <div class="fw-semibold">Yönetici İşlemleri</div>
    <div class="text-muted small">Müdahale işlemleri (dikkatli kullan)</div>
  </div>

  <div class="card-body d-flex flex-wrap gap-2">
    <?php if ($mmjHas): ?>
      <form method="post" action="<?= site_url('admin/publishes/' . (int)$row['id'] . '/check') ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline-primary" type="submit">Şimdi Kontrol Et</button>
      </form>
    <?php endif; ?>

    <form method="post" action="<?= site_url('admin/publishes/' . (int)$row['id'] . '/retry') ?>">
        <?= csrf_field() ?>
      <button class="btn btn-primary" type="submit">Tekrar Dene</button>
    </form>

    <form method="post" action="<?= site_url('admin/publishes/' . (int)$row['id'] . '/reset-job') ?>"
          onsubmit="return confirm('Bu işlem, ilgili kuyruğu sıfırlayıp paylaşımı tekrar çalıştırır. Devam edilsin mi?')">
          <?= csrf_field() ?>
      <button class="btn btn-warning" type="submit">İşi Sıfırla</button>
    </form>

    <form method="post" action="<?= site_url('admin/publishes/' . (int)$row['id'] . '/cancel') ?>"
          onsubmit="return confirm('Bu paylaşımı iptal etmek istiyor musun?')">
          <?= csrf_field() ?>
      <button class="btn btn-outline-danger" type="submit">İptal Et</button>
    </form>
  </div>
</div>

<?php if ($mmjHas): ?>
  <div class="card mb-3">
    <div class="card-header fw-semibold">Meta Video İşleme Akışı (meta_media_jobs)</div>
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-6">
          <div class="text-muted small">Medya Konteyner ID</div>
          <div class="fw-semibold">
            <code><?= esc((string)$mmj['creation_id']) ?></code>
            <?= ui_clip_btn((string)$mmj['creation_id']) ?>
          </div>
        </div>

        <div class="col-md-6">
          <div class="text-muted small">Durum</div>
          <div class="fw-semibold">
            <?= esc(ui_meta_media_status_tr((string)($mmj['status'] ?? ''))) ?>
            · <?= esc(ui_meta_status_code_tr((string)($mmj['status_code'] ?? ''))) ?>
          </div>
        </div>

        <div class="col-md-3">
          <div class="text-muted small">Deneme</div>
          <div class="fw-semibold"><?= (int)($mmj['attempts'] ?? 0) ?></div>
        </div>

        <div class="col-md-3">
          <div class="text-muted small">Sonraki Deneme</div>
          <div class="fw-semibold"><?= esc(ui_dt($mmj['next_retry_at'] ?? null)) ?></div>
        </div>

        <div class="col-md-6">
          <div class="text-muted small">Yayın ID</div>
          <div class="fw-semibold">
            <?php if (!empty($mmj['published_media_id'])): ?>
              <code><?= esc((string)$mmj['published_media_id']) ?></code>
              <?= ui_clip_btn((string)$mmj['published_media_id']) ?>
            <?php else: ?>
              <span class="text-muted">—</span>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <?php if (!empty($mmj['last_error'])): ?>
        <hr>
        <div class="text-muted small mb-1">Son Teknik Not</div>
        <pre class="p-3 bg-light rounded mb-0" style="white-space: pre-wrap;"><?= esc(ui_humanize_technical_note((string)$mmj['last_error'])) ?></pre>
      <?php endif; ?>

      <?php if (!empty($mmj['last_response_json'])): ?>
        <details class="mt-3">
          <summary class="text-muted">Teknik Yanıt (JSON)</summary>
          <pre class="p-3 bg-light rounded mt-2" style="white-space: pre-wrap;"><?= esc((string)$mmj['last_response_json']) ?></pre>
        </details>
      <?php endif; ?>
    </div>
  </div>
<?php endif; ?>

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

<script>
(function(){
  document.addEventListener('click', async function(e){
    const btn = e.target.closest('button[data-clip]');
    if (!btn) return;

    e.preventDefault();
    e.stopPropagation();

    const txt = btn.getAttribute('data-clip') || '';
    if (!txt) return;

    try {
      await navigator.clipboard.writeText(txt);
      const old = btn.textContent;
      btn.textContent = 'Kopyalandı';
      setTimeout(()=>btn.textContent = old, 900);
    } catch(err) {}
  });
})();
</script>

<?= $this->endSection() ?>
