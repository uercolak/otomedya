<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>

<?php helper('catalog'); ?>

<?php
  $status = (string)($job['status'] ?? '');
  $statusTr = job_status_label($status);
  $humanExplain = job_status_explain($status);

  $badge = match($status) {
    'done'     => 'badge-soft-success',
    'failed'   => 'badge-soft-danger',
    'running'  => 'badge-soft-warning',
    'queued'   => 'badge-soft-info',
    'canceled' => 'badge-soft-muted',
    default    => 'badge-soft-info',
  };

  $payloadRaw = (string)($job['payload_json'] ?? '');
  $payloadPretty = $payloadRaw;
  try {
    if ($payloadRaw) {
      $payloadPretty = json_encode(json_decode($payloadRaw, true), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    }
  } catch (\Throwable $e) {}

  $pubId = (int)($job['publish_id'] ?? 0);
?>

<div class="container-fluid py-3">

  <div class="d-flex justify-content-between align-items-start mb-3">
    <div>
      <h1 class="page-title mb-1">İş #<?= esc((string)$job['id']) ?></h1>
      <div class="text-muted">İş Tipi: <code><?= esc(job_type_label((string)$job['type'])) ?></code></div>
      <?php if ($pubId): ?>
        <div class="text-muted mt-1">
          Bağlı Paylaşım:
          <a href="<?= site_url('dealer/publishes') ?>" class="text-decoration-none">Paylaşımlar</a>
          <span class="text-muted">· Publish ID: <code><?= $pubId ?></code></span>
        </div>
      <?php endif; ?>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="<?= site_url('dealer/jobs') ?>">Geri</a>

      <form method="post" action="<?= site_url('dealer/jobs/' . (int)$job['id'] . '/retry') ?>">
        <?= csrf_field() ?>
        <button class="btn btn-outline-primary" type="submit">Tekrar Kuyruğa Al</button>
      </form>

      <form method="post" action="<?= site_url('dealer/jobs/' . (int)$job['id'] . '/reset') ?>"
            onsubmit="return confirm('Bu işin attempts ve last_error alanları sıfırlanacak. Devam edilsin mi?')">
        <?= csrf_field() ?>
        <button class="btn btn-warning" type="submit">Reset</button>
      </form>

      <form method="post" action="<?= site_url('dealer/jobs/' . (int)$job['id'] . '/cancel') ?>"
            onsubmit="return confirm('Bu işi iptal etmek istiyor musun?')">
        <?= csrf_field() ?>
        <button class="btn btn-outline-danger" type="submit">İptal</button>
      </form>
    </div>
  </div>

  <?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
  <?php endif; ?>
  <?php if (session('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
  <?php endif; ?>

  <div class="alert alert-light border">
    <div class="fw-semibold mb-1">Bu sayfa ne işe yarar?</div>
    <div class="text-muted small">
      Burada arka planda çalışan bir işlemin durumunu görebilir, hata varsa nedenini inceleyebilirsiniz.
      Teknik detaylar “Teknik Detaylar” bölümünde.
    </div>
  </div>

  <div class="card mb-3">
    <div class="card-body">
      <div class="row g-3">
        <div class="col-md-2">
          <div class="text-muted small">Durum</div>
          <span class="badge <?= $badge ?>"><?= esc($statusTr) ?></span>
          <div class="text-muted small mt-1"><?= esc($humanExplain) ?></div>
        </div>

        <div class="col-md-2">
          <div class="text-muted small">Öncelik</div>
          <div class="fw-semibold"><?= esc((string)($job['priority'] ?? '—')) ?></div>
        </div>

        <div class="col-md-2">
          <div class="text-muted small">Deneme</div>
          <div class="fw-semibold"><?= esc((string)($job['attempts'] ?? 0)) ?>/<?= esc((string)($job['max_attempts'] ?? 0)) ?></div>
        </div>

        <div class="col-md-3">
          <div class="text-muted small">Planlanan Zaman</div>
          <div class="fw-semibold"><?= esc((string)($job['run_at'] ?? '—')) ?></div>
        </div>

        <div class="col-md-3">
          <div class="text-muted small">Kilit</div>
          <div class="fw-semibold"><?= esc((string)($job['locked_by'] ?? '—')) ?></div>
          <div class="text-muted small">Kilit zamanı: <?= esc((string)($job['locked_at'] ?? '—')) ?></div>
        </div>

        <div class="col-md-6">
          <div class="text-muted small">Oluşturma</div>
          <div class="fw-semibold"><?= esc((string)($job['created_at'] ?? '—')) ?></div>
        </div>

        <div class="col-md-6">
          <div class="text-muted small">Güncelleme</div>
          <div class="fw-semibold"><?= esc((string)($job['updated_at'] ?? '—')) ?></div>
        </div>
      </div>

      <?php if (!empty($job['last_error'])): ?>
        <hr>
        <div class="text-muted small mb-1">Son Hata (özet)</div>
        <div class="text-danger small">Bu iş hata vermiş. Daha detaylı çıktı için aşağıdaki “Deneme Geçmişi”ni açabilirsiniz.</div>
      <?php endif; ?>
    </div>
  </div>

  <div class="accordion mb-3" id="jobTechAcc">
    <div class="accordion-item">
      <h2 class="accordion-header" id="payloadHead">
        <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#payloadBody" aria-expanded="true" aria-controls="payloadBody">
          İş İçeriği (Gerekli Bilgiler)
        </button>
      </h2>
      <div id="payloadBody" class="accordion-collapse collapse show" aria-labelledby="payloadHead" data-bs-parent="#jobTechAcc">
        <div class="accordion-body">
          <div class="d-flex justify-content-between align-items-center">
            <div class="fw-semibold">Gönderilecek/işlenecek veri</div>
            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnCopyPayload">Kopyala</button>
          </div>
          <pre class="border rounded p-2 bg-light mb-0 mt-2" id="payloadBox" style="white-space:pre-wrap;"><?= esc($payloadPretty ?: '—') ?></pre>
        </div>
      </div>
    </div>

    <div class="accordion-item">
      <h2 class="accordion-header" id="techHead2">
        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#techBody2" aria-expanded="false" aria-controls="techBody2">
          Teknik Detaylar (gerekince aç)
        </button>
      </h2>
      <div id="techBody2" class="accordion-collapse collapse" aria-labelledby="techHead2" data-bs-parent="#jobTechAcc">
        <div class="accordion-body">
          <?php if (!empty($job['last_error'])): ?>
            <div class="text-muted small mb-1">Son Hata (tam)</div>
            <pre class="border rounded p-2 bg-light mb-0" style="white-space:pre-wrap;"><?= esc((string)$job['last_error']) ?></pre>
          <?php else: ?>
            <div class="text-muted">Teknik hata kaydı yok.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header fw-semibold">Deneme Geçmişi</div>
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table mb-0 align-middle">
          <thead>
            <tr>
              <th style="width:90px;">#</th>
              <th style="width:150px;">Durum</th>
              <th style="width:180px;">Başlangıç</th>
              <th style="width:180px;">Bitiş</th>
              <th>Detay</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($attempts)): ?>
              <tr><td colspan="5" class="text-center py-4 text-muted">Deneme kaydı yok.</td></tr>
            <?php else: ?>
              <?php foreach ($attempts as $a): ?>
                <?php
                  $st = (string)($a['status'] ?? '');
                  $stTr = ($st === 'success') ? 'Başarılı' : (($st === 'failed') ? 'Hatalı' : 'Bilinmiyor');

                  $ab = match($st) {
                    'success' => 'badge-soft-success',
                    'failed'  => 'badge-soft-danger',
                    default   => 'badge-soft-warning',
                  };

                  $err  = (string)($a['error'] ?? '');
                  $resp = (string)($a['response_json'] ?? '');
                ?>
                <tr>
                  <td><?= esc((string)($a['attempt_no'] ?? '')) ?></td>
                  <td><span class="badge <?= $ab ?>"><?= esc($stTr) ?></span></td>
                  <td class="text-muted"><?= esc((string)($a['started_at'] ?? '—')) ?></td>
                  <td class="text-muted"><?= esc((string)($a['finished_at'] ?? '—')) ?></td>
                  <td>
                    <?php if ($err): ?>
                      <details>
                        <summary class="text-danger">Hata detayı (göster)</summary>
                        <pre class="border rounded p-2 bg-light mb-0 mt-2" style="white-space:pre-wrap;"><?= esc($err) ?></pre>
                      </details>
                    <?php elseif ($resp): ?>
                      <details>
                        <summary>Yanıt detayı (göster)</summary>
                        <pre class="border rounded p-2 bg-light mb-0 mt-2" style="white-space:pre-wrap;"><?= esc($resp) ?></pre>
                      </details>
                    <?php else: ?>
                      <span class="text-muted">—</span>
                    <?php endif; ?>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

</div>

<style>
  .badge-soft-info{background:rgba(59,130,246,.12);color:#1d4ed8;border:1px solid rgba(59,130,246,.18);font-weight:600;}
  .badge-soft-warning{background:rgba(245,158,11,.14);color:#92400e;border:1px solid rgba(245,158,11,.22);font-weight:600;}
  .badge-soft-danger{background:rgba(239,68,68,.12);color:#b91c1c;border:1px solid rgba(239,68,68,.20);font-weight:600;}
  .badge-soft-success{background:rgba(34,197,94,.12);color:#166534;border:1px solid rgba(34,197,94,.20);font-weight:600;}
  .badge-soft-muted{background:rgba(107,114,128,.10);color:#374151;border:1px solid rgba(107,114,128,.18);font-weight:600;}
</style>

<script>
(function(){
  const btn = document.getElementById('btnCopyPayload');
  const box = document.getElementById('payloadBox');
  if (!btn || !box) return;

  btn.addEventListener('click', async function(){
    const text = box.textContent || '';
    if (!text || text === '—') return;

    try {
      await navigator.clipboard.writeText(text);
      btn.textContent = 'Kopyalandı';
      setTimeout(()=>btn.textContent='Kopyala', 1200);
    } catch(e) {}
  });
})();
</script>

<?= $this->endSection() ?>
