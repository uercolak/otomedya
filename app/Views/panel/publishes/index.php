<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold" style="font-size:16px;">Paylaşımlar</div>
    <div class="text-muted small">Kuyruğa alınan, yayınlanan veya hata alan paylaşımların.</div>
  </div>
</div>

<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<form method="get" action="<?= site_url('panel/publishes') ?>" class="card mb-3">
  <div class="card-body">
    <div class="row g-2">

      <div class="col-md-4">
        <label class="form-label">Arama</label>
        <input type="text" name="q" class="form-control"
               placeholder="remote_id / başlık / hata"
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

      <div class="col-md-2 d-flex align-items-end">
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
            <th>ID</th>
            <th>Platform</th>
            <th>Hesap</th>
            <th>İçerik</th>
            <th>Durum</th>
            <th>Planlanan</th>
            <th>Yayınlanan</th>
            <th class="text-end">İşlemler</th>
          </tr>
        </thead>

        <tbody>
        <?php if (empty($rows)): ?>
          <tr><td colspan="8" class="text-center text-muted py-4">Kayıt bulunamadı.</td></tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $status = (string)($r['status'] ?? '');

              if (!empty($r['sa_username'])) $accLabel = '@' . $r['sa_username'];
              elseif (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
              else $accLabel = 'Hesap #' . (int)($r['account_id'] ?? 0);

              $contentLabel = !empty($r['content_title'])
                ? ('#' . (int)$r['content_id'] . ' — ' . $r['content_title'])
                : ('İçerik #' . (int)($r['content_id'] ?? 0));
            ?>

            <tr style="cursor:pointer" onclick="window.location='<?= site_url('panel/publishes/' . (int)$r['id']) ?>'">
              <td><?= (int)$r['id'] ?></td>
              <td><?= esc(strtoupper((string)$r['platform'])) ?></td>
              <td><?= esc($accLabel) ?></td>
              <td><?= esc($contentLabel) ?></td>

                <td>
                    <?= view('partials/status_badge', ['status' => $status]) ?>

                    <?php
                        $mj = [];
                        if (!empty($r['meta_json'])) {
                        $tmp = json_decode((string)$r['meta_json'], true);
                        if (is_array($tmp)) $mj = $tmp;
                        }
                        $hasCreation = !empty($mj['meta']['creation_id']);
                    ?>

                    <?php if ($status === 'publishing' && $hasCreation): ?>
                        <div class="text-muted small mt-1">
                        Video işleniyor, otomatik yayınlanacak.
                        </div>
                    <?php endif; ?>
                </td>

              <td><?= esc(ui_dt($r['schedule_at'] ?? null)) ?></td>
              <?php
                $publishedAt = $r['published_at'] ?? null;
                if (empty($publishedAt) && ($r['status'] ?? '') === 'published') {
                    $publishedAt = $r['updated_at'] ?? null; // fallback
                }
                ?>
              <td><?= esc(ui_dt($publishedAt)) ?></td>

              <td class="text-end" onclick="event.stopPropagation()">
                <div class="d-inline-flex gap-2 align-items-center">
                  <a class="btn btn-outline-secondary btn-sm"
                     href="<?= site_url('panel/publishes/' . (int)$r['id']) ?>">
                    Detay
                  </a>

                    <?php
                        $previewUrl = '';
                        $mj = $r['meta_json'] ?? '';
                        if ($mj) {
                            $arr = json_decode((string)$mj, true);
                            if (is_array($arr)) {
                                $previewUrl = (string)($arr['meta']['permalink'] ?? '');
                            }
                        }

                        // fallback: remote_id URL ise onu kullan
                        $remoteId = (string)($r['remote_id'] ?? '');
                        if ($previewUrl === '' && preg_match('~^https?://~i', $remoteId) === 1) {
                            $previewUrl = $remoteId;
                        }
                        ?>

                        <?php if ($status === 'published' && $previewUrl !== ''): ?>
                        <a class="btn btn-outline-primary btn-sm"
                            href="<?= esc($previewUrl) ?>"
                            target="_blank" rel="noopener">
                            Önizle
                        </a>
                        <?php endif; ?>

                  <?php if (in_array($status, ['queued','scheduled'], true)): ?>
                    <form action="<?= site_url('panel/publishes/' . (int)$r['id'] . '/cancel') ?>"
                          method="post"
                          class="d-inline"
                          data-confirm="Bu paylaşımı iptal etmek istediğine emin misin? (Bu işlem geri alınamaz)"
                          data-confirm-title="Paylaşımı iptal et"
                          data-confirm-ok="Evet, iptal et"
                          data-confirm-ok-class="btn-danger">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-outline-danger btn-sm">İptal</button>
                    </form>
                  <?php endif; ?>

                </div>
                
              </td>
            </tr>

          <?php endforeach; ?>
        <?php endif; ?>
        </tbody>

      </table>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
