<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<div class="page-header">
  <div>
    <h1 class="page-title">Paylaşımlar</h1>
    <p class="text-muted mb-4">Kuyruğa alınan ve yayınlanan paylaşımların kayıtları.</p>
  </div>
</div>

<div class="d-flex justify-content-end mb-3">
  <a class="btn btn-primary" href="<?= site_url('admin/publishes/create') ?>">+ Planlı Paylaşım Oluştur</a>
</div>

<form method="get" action="<?= site_url('admin/publishes') ?>" class="card mb-3">
  <div class="card-body">
    <div class="row g-2">

      <div class="col-md-3">
        <label class="form-label">Arama</label>
        <input type="text" name="q" class="form-control"
               placeholder="ID / başlık / hata içinde ara..."
               value="<?= esc((string)($filters['q'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Platform</label>
        <select name="platform" class="form-select">
          <option value="">Tümü</option>
          <?php foreach (($platformOptions ?? []) as $p): ?>
            <option value="<?= esc($p) ?>" <?= ((string)($filters['platform'] ?? '') === (string)$p) ? 'selected' : '' ?>>
              <?= esc(strtoupper($p)) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Durum</label>
        <select name="status" class="form-select">
          <option value="">Tümü</option>
          <?php foreach (($statusOptions ?? []) as $s): ?>
            <?php [, $label] = ui_status_badge((string)$s); ?>
            <option value="<?= esc($s) ?>" <?= ((string)($filters['status'] ?? '') === (string)$s) ? 'selected' : '' ?>>
              <?= esc($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Kullanıcı</label>
        <input type="text" name="user" class="form-control"
               placeholder="ad veya email"
               value="<?= esc((string)($filters['user'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Başlangıç</label>
        <input type="date" name="date_from" class="form-control"
               value="<?= esc((string)($filters['date_from'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Bitiş</label>
        <input type="date" name="date_to" class="form-control"
               value="<?= esc((string)($filters['date_to'] ?? '')) ?>">
      </div>

      <div class="col-md-1 d-flex align-items-end gap-2">
        <button class="btn btn-primary w-100" type="submit">Filtrele</button>
      </div>

    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">
    <div class="table-responsive">
      <table class="table mb-0 align-middle">
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th style="width:110px;">Platform</th>
            <th>Hesap</th>
            <th>İçerik</th>
            <th style="width:140px;">Durum</th>
            <th style="width:150px;">Planlanan</th>
            <th style="width:150px;">Yayınlanan</th>
            <th style="width:110px;">İş</th>
            <th style="width:180px;">Kimin adına?</th>
          </tr>
        </thead>

        <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="9" class="text-center text-muted py-4">Kayıt bulunamadı.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $status = (string)($r['status'] ?? '');
              [$badgeClass, $badgeText] = ui_status_badge($status);

              $platform = (string)($r['platform'] ?? '');
              $platformRaw = strtolower($platform);

              // Hesap label: platforma göre daha doğru
              if ($platformRaw === 'instagram') {
                if (!empty($r['sa_username'])) $accLabel = '@' . $r['sa_username'];
                elseif (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
                else $accLabel = 'Hesap #' . (int)($r['account_id'] ?? 0);
              } else { // facebook ve diğerleri
                if (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
                elseif (!empty($r['sa_username'])) $accLabel = (string)$r['sa_username'];
                else $accLabel = 'Hesap #' . (int)($r['account_id'] ?? 0);
              }

              $contentLabel = !empty($r['content_title'])
                ? ('#' . (int)$r['content_id'] . ' — ' . $r['content_title'])
                : ('İçerik #' . (int)($r['content_id'] ?? 0));

              $userLabel = '—';
              if (!empty($r['user_name']) || !empty($r['user_email'])) {
                $userLabel = trim((string)($r['user_name'] ?? '')) ?: 'Kullanıcı';
                if (!empty($r['user_email'])) {
                  $userLabel .= "<br><small class=\"text-muted\">" . esc($r['user_email']) . "</small>";
                }
              }

              $jobId = (int)($r['job_id'] ?? 0);

              // permalink / remote_id mini bilgi
              $permalink = '';
              if (!empty($r['meta_json'])) {
                $tmp = json_decode((string)$r['meta_json'], true);
                if (is_array($tmp)) {
                  $permalink = (string)($tmp['meta']['permalink'] ?? '');
                }
              }
              $remoteId = (string)($r['remote_id'] ?? '');
            ?>

            <tr class="row-link"
                role="button"
                tabindex="0"
                data-href="<?= site_url('admin/publishes/' . (int)$r['id']) ?>">

              <td><?= (int)$r['id'] ?></td>
              <td><?= esc(strtoupper($platform)) ?></td>

              <td>
                <?= esc($accLabel) ?>

                <?php if ($permalink): ?>
                  <div class="small mt-1">
                    <a onclick="event.stopPropagation()"
                       href="<?= esc($permalink) ?>" target="_blank" rel="noreferrer"
                       class="text-decoration-none">
                      <?= ($platformRaw === 'facebook') ? 'Facebook’ta Aç' : 'Instagram’da Aç' ?>
                    </a>
                  </div>
                <?php elseif ($remoteId !== ''): ?>
                  <div class="small text-muted mt-1">
                    Post ID: <code><?= esc($remoteId) ?></code>
                  </div>
                <?php endif; ?>
              </td>

              <td><?= esc($contentLabel) ?></td>
              <td><span class="<?= esc($badgeClass) ?>"><?= esc($badgeText) ?></span></td>
              <td><?= esc(ui_dt($r['schedule_at'] ?? null)) ?></td>
              <td><?= esc(ui_dt($r['published_at'] ?? null)) ?></td>

              <td>
                <?php if ($jobId): ?>
                  <a onclick="event.stopPropagation()" href="<?= site_url('admin/jobs/' . $jobId) ?>">İş #<?= $jobId ?></a>
                <?php else: ?>
                  —
                <?php endif; ?>
              </td>

              <td><?= $userLabel ?></td>
            </tr>

            <?php
                $jobLastError = (string)($r['job_last_error'] ?? '');
                $jobAttempts  = (int)($r['job_attempts'] ?? 0);
                $jobMax       = (int)($r['job_max_attempts'] ?? 0);

                // Türkçeleştirilmiş metinler
                $pubErrTr = !empty($r['error']) ? ui_humanize_error_tr((string)$r['error']) : '';
                $jobErrTr = $jobLastError !== '' ? ui_humanize_error_tr($jobLastError) : '';
                ?>

                <?php if (!empty($r['error']) || $jobLastError !== ''): ?>
                <tr class="bg-light">
                    <td colspan="9">

                    <?php if (!empty($r['error'])): ?>
                        <div class="text-danger mb-2">
                        <strong><?= ui_publish_label_tr() ?> Hatası:</strong> <?= esc($pubErrTr) ?>
                        </div>

                        <!-- İstersen teknik detayı collapsible yapalım -->
                        <details>
                        <summary class="text-muted">Teknik detay</summary>
                        <div class="mt-2"><code><?= esc((string)$r['error']) ?></code></div>
                        </details>
                    <?php endif; ?>

                    <?php if ($jobLastError !== ''): ?>
                        <div class="text-muted mt-2">
                        <strong><?= ui_job_label_tr() ?>:</strong>
                        <?= esc((string)($r['job_status'] ?? '')) ?>
                        · Deneme: <?= $jobAttempts ?><?= $jobMax ? ('/' . $jobMax) : '' ?>
                        </div>

                        <div class="text-danger mt-1">
                        <strong>İş Hatası:</strong> <?= esc($jobErrTr) ?>
                        </div>

                        <details class="mt-2">
                        <summary class="text-muted">Teknik detay</summary>
                        <div class="mt-2"><code><?= esc($jobLastError) ?></code></div>
                        </details>
                    <?php endif; ?>

                    </td>
                </tr>
                <?php endif; ?>

          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<?php if (!empty($pagination) && ($pagination['pages'] ?? 1) > 1): ?>
  <?php
    $page  = (int)($pagination['page'] ?? 1);
    $pages = (int)($pagination['pages'] ?? 1);

    $qs = $_GET ?? [];
    unset($qs['page']);
    $baseQs = http_build_query($qs);
    $baseUrl = site_url('admin/publishes') . ($baseQs ? ('?' . $baseQs . '&') : '?');
  ?>
  <nav class="mt-3">
    <ul class="pagination">
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= esc($baseUrl . 'page=' . max(1, $page - 1)) ?>">‹</a>
      </li>

      <?php for ($i = 1; $i <= $pages; $i++): ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="<?= esc($baseUrl . 'page=' . $i) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <li class="page-item <?= $page >= $pages ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= esc($baseUrl . 'page=' . min($pages, $page + 1)) ?>">›</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>

<style>
  .row-link { cursor:pointer; }
</style>

<script>
(function(){
  document.addEventListener('click', function(e){
    const tr = e.target.closest('tr.row-link');
    if (!tr) return;
    const href = tr.getAttribute('data-href');
    if (href) window.location.href = href;
  });

  document.addEventListener('keydown', function(e){
    const tr = e.target.closest('tr.row-link');
    if (!tr) return;
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      tr.click();
    }
  });
})();
</script>

<?= $this->endSection() ?>
