<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>
<?php helper('ui'); ?>

<div class="page-header">
  <div>
    <h1 class="page-title">Paylaşımlar</h1>
    <p class="text-muted mb-4">Kullanıcılarınıza ait paylaşımları görüntüleyebilir, askıda kalan içerikleri yeniden kuyruğa alarak gönderim sürecini başlatabilirsiniz. </p>
  </div>
</div>

<form method="get" action="<?= site_url('dealer/publishes') ?>" class="card mb-3">
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

      <div class="col-md-3">
        <label class="form-label">Kullanıcı</label>
        <input type="text" name="user" class="form-control"
               placeholder="ad veya email"
               value="<?= esc((string)($filters['user'] ?? '')) ?>">
      </div>

      <div class="col-md-1">
        <label class="form-label">Başlangıç</label>
        <input type="date" name="date_from" class="form-control"
               value="<?= esc((string)($filters['date_from'] ?? '')) ?>">
      </div>

      <div class="col-md-1">
        <label class="form-label">Bitiş</label>
        <input type="date" name="date_to" class="form-control"
               value="<?= esc((string)($filters['date_to'] ?? '')) ?>">
      </div>

      <div class="col-md-12 d-flex justify-content-end gap-2 mt-2">
        <button class="btn btn-primary" type="submit">Filtrele</button>
        <a class="btn btn-outline-secondary" href="<?= site_url('dealer/publishes') ?>">Sıfırla</a>
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
            <th style="width:180px;">Kullanıcı</th>
          </tr>
        </thead>

        <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-4">Kayıt bulunamadı.</td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $status = (string)($r['status'] ?? '');
              [$badgeClass, $badgeText] = ui_status_badge($status);

              $platform = (string)($r['platform'] ?? '');
              $platformRaw = strtolower($platform);

              if ($platformRaw === 'instagram') {
                if (!empty($r['sa_username'])) $accLabel = '@' . $r['sa_username'];
                elseif (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
                else $accLabel = 'Hesap #' . (int)($r['account_id'] ?? 0);
              } else {
                if (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
                elseif (!empty($r['sa_username'])) $accLabel = (string)$r['sa_username'];
                else $accLabel = 'Hesap #' . (int)($r['account_id'] ?? 0);
              }

              $contentLabel = !empty($r['content_title'])
                ? ('#' . (int)$r['content_id'] . ' — ' . $r['content_title'])
                : ('İçerik #' . (int)($r['content_id'] ?? 0));

              $userLabel = trim((string)($r['user_name'] ?? '')) ?: 'Kullanıcı';
              if (!empty($r['user_email'])) {
                $userLabel .= "<br><small class=\"text-muted\">" . esc($r['user_email']) . "</small>";
              }
            ?>

            <tr class="row-link"
                role="button"
                tabindex="0"
                data-href="<?= site_url('dealer/publishes/' . (int)$r['id']) ?>">
              <td><?= (int)$r['id'] ?></td>
              <td><?= esc(strtoupper($platform)) ?></td>
              <td><?= esc($accLabel) ?></td>
              <td><?= esc($contentLabel) ?></td>
              <td><span class="<?= esc($badgeClass) ?>"><?= esc($badgeText) ?></span></td>
              <td><?= esc(ui_dt($r['schedule_at'] ?? null)) ?></td>
              <td><?= esc(ui_dt($r['published_at'] ?? null)) ?></td>
              <td><?= $userLabel ?></td>
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
    $baseUrl = site_url('dealer/publishes') . ($baseQs ? ('?' . $baseQs . '&') : '?');
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

<style>.row-link{cursor:pointer;}</style>
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
