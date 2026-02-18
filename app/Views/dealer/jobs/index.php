<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>

<?php helper('catalog'); ?>

<div class="container-fluid py-3">
  <div class="page-header">
    <div>
      <h1 class="page-title">Arka Plan İşleri</h1>
      <p class="text-muted mb-4">Sadece sizin oluşturduğunuz kullanıcıların paylaşımlarına ait işler.</p>
    </div>
  </div>

  <?php if (session('error')): ?>
    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
  <?php endif; ?>
  <?php if (session('success')): ?>
    <div class="alert alert-success"><?= esc(session('success')) ?></div>
  <?php endif; ?>

  <form method="get" class="card mb-3">
    <div class="card-body">
      <div class="row g-2">
        <div class="col-md-5">
          <label class="form-label mb-1">Arama</label>
          <input name="q" value="<?= esc($filters['q'] ?? '') ?>" class="form-control" placeholder="Tip / içerik / hata içinde ara...">
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">Durum</label>
          <select name="status" class="form-select">
            <option value="">Hepsi</option>
            <?php foreach (($statusOptions ?? []) as $st): ?>
              <option value="<?= esc($st) ?>" <?= (($filters['status'] ?? '') === $st) ? 'selected' : '' ?>>
                <?= esc(job_status_label($st)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">İş Tipi</label>
          <select name="type" class="form-select">
            <option value="">Hepsi</option>
            <?php foreach (($typeOptions ?? []) as $tp): ?>
              <option value="<?= esc($tp) ?>" <?= (($filters['type'] ?? '') === $tp) ? 'selected' : '' ?>>
                <?= esc(job_type_label($tp)) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">İşleniyor mu?</label>
          <select name="locked" class="form-select">
            <option value="">Hepsi</option>
            <option value="1" <?= (($filters['locked'] ?? '') === '1') ? 'selected' : '' ?>>Şu an işleniyor</option>
            <option value="0" <?= (($filters['locked'] ?? '') === '0') ? 'selected' : '' ?>>İşlenmiyor</option>
          </select>
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">Başlangıç</label>
          <input type="date" name="date_from" value="<?= esc($filters['date_from'] ?? '') ?>" class="form-control">
        </div>

        <div class="col-md-2">
          <label class="form-label mb-1">Bitiş</label>
          <input type="date" name="date_to" value="<?= esc($filters['date_to'] ?? '') ?>" class="form-control">
        </div>
      </div>

      <div class="d-flex gap-2 mt-3">
        <button class="btn btn-primary" type="submit">Filtrele</button>
        <a class="btn btn-outline-secondary" href="<?= site_url('dealer/jobs') ?>">Sıfırla</a>
      </div>

      <div class="mt-3 text-muted small">
        <strong>“İşleniyor”</strong> = Sistem bu işi şu anda yürütüyor (aynı işin iki kez çalışmaması için).
      </div>
    </div>
  </form>

  <div class="card">
    <div class="card-body p-0">
      <div class="table-responsive">
        <table class="table table-hover mb-0">
          <thead>
            <tr>
              <th style="width:90px;">ID</th>
              <th style="width:170px;">Durum</th>
              <th style="width:220px;">İş Tipi</th>
              <th>İçerik Özeti</th>
              <th style="width:120px;">Deneme</th>
              <th style="width:190px;">Planlanan Zaman</th>
              <th style="width:170px;">Kullanıcı</th>
              <th style="width:40px;"></th>
            </tr>
          </thead>

          <tbody>
          <?php if (empty($rows)): ?>
            <tr><td colspan="8" class="text-center py-4 text-muted">Kayıt bulunamadı.</td></tr>
          <?php else: ?>
            <?php foreach ($rows as $r): ?>
              <?php
                $status = (string)($r['status'] ?? '');
                $statusTr = job_status_label($status);

                $badge = match($status) {
                  'done'     => 'badge-soft-success',
                  'failed'   => 'badge-soft-danger',
                  'running'  => 'badge-soft-warning',
                  'queued'   => 'badge-soft-info',
                  'canceled' => 'badge-soft-muted',
                  default    => 'badge-soft-info',
                };

                $payload = (string)($r['payload_json'] ?? '');
                $summary = $payload ? mb_substr($payload, 0, 140) : '';

                $type = (string)($r['type'] ?? '');
                $typeTr = job_type_label($type);

                $lockedBy = (string)($r['locked_by'] ?? '');
                $isLocked = !empty($lockedBy);

                $userLine = '—';
                if (!empty($r['user_name']) || !empty($r['user_email'])) {
                  $nm = trim((string)($r['user_name'] ?? '')) ?: 'Kullanıcı';
                  $em = trim((string)($r['user_email'] ?? ''));
                  $userLine = $nm . ($em ? "<br><small class=\"text-muted\">" . esc($em) . "</small>" : "");
                }
              ?>

              <tr class="row-link"
                  role="button"
                  tabindex="0"
                  data-href="<?= site_url('dealer/jobs/' . (int)$r['id']) ?>">

                <td><?= esc($r['id']) ?></td>

                <td>
                  <span class="badge <?= $badge ?>"><?= esc($statusTr) ?></span>
                  <?php if ($isLocked || $status === 'running'): ?>
                    <span class="badge badge-soft-muted ms-2" title="Sistem bu işi şu anda yürütüyor">İşleniyor</span>
                  <?php endif; ?>
                </td>

                <td><code><?= esc($typeTr) ?></code></td>

                <td class="text-truncate" style="max-width:520px;">
                  <?= $summary ? esc($summary) : '<span class="text-muted">—</span>' ?>
                </td>

                <td><?= esc($r['attempts']) ?>/<?= esc($r['max_attempts']) ?></td>

                <td class="text-muted"><?= esc((string)($r['run_at'] ?? '')) ?></td>

                <td><?= $userLine ?></td>

                <td class="text-end pe-3"><span class="chev">›</span></td>
              </tr>
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
      $baseUrl = site_url('dealer/jobs') . ($baseQs ? ('?' . $baseQs . '&') : '?');
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
</div>

<style>
  .row-link { cursor: pointer; }
  .chev { color:#9ca3af; font-size:18px; transition:.15s; }
  tr.row-link:hover .chev { transform: translateX(4px); color:#6b7280; }

  .badge-soft-info{background:rgba(59,130,246,.12);color:#1d4ed8;border:1px solid rgba(59,130,246,.18);font-weight:600;}
  .badge-soft-warning{background:rgba(245,158,11,.14);color:#92400e;border:1px solid rgba(245,158,11,.22);font-weight:600;}
  .badge-soft-danger{background:rgba(239,68,68,.12);color:#b91c1c;border:1px solid rgba(239,68,68,.20);font-weight:600;}
  .badge-soft-success{background:rgba(34,197,94,.12);color:#166534;border:1px solid rgba(34,197,94,.20);font-weight:600;}
  .badge-soft-muted{background:rgba(107,114,128,.10);color:#374151;border:1px solid rgba(107,114,128,.18);font-weight:600;}
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
