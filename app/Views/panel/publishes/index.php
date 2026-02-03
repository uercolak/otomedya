<?= $this->extend('layouts/panel') ?>
<?= $this->section('content') ?>

<?php
// -------------------- Yardımcılar (bu sayfaya özel) --------------------
if (!function_exists('ui_dt_tr')) {
  function ui_dt_tr($val): string {
    if (empty($val)) return '—';

    try {
      $dt = new DateTime(is_string($val) ? $val : (string)$val);

      $months = [
        1=>'Ocak',2=>'Şubat',3=>'Mart',4=>'Nisan',5=>'Mayıs',6=>'Haziran',
        7=>'Temmuz',8=>'Ağustos',9=>'Eylül',10=>'Ekim',11=>'Kasım',12=>'Aralık'
      ];

      $m = (int)$dt->format('n');
      $monthName = $months[$m] ?? $dt->format('m');

      // 27 Ocak 2026 • 21:23
      return $dt->format('j ') . $monthName . $dt->format(' Y') . ' • ' . $dt->format('H:i');
    } catch (Throwable $e) {
      // fallback: mevcut helper ne veriyorsa
      return esc(ui_dt($val));
    }
  }
}

$hasAnyFilter = false;
foreach (['q','platform','status','date_from','date_to'] as $k) {
  if (!empty(trim((string)($filters[$k] ?? '')))) { $hasAnyFilter = true; break; }
}
?>

<style>
  /* Marka butonu (mavi yok) */
  .btn-brand{
    background: linear-gradient(90deg, #6a5cff, #ff4fd8);
    border: 0;
    color: #fff;
    font-weight: 600;
  }
  .btn-brand:hover{ filter: brightness(0.97); color:#fff; }

  .btn-brand-outline{
    border: 1px solid rgba(106,92,255,.45);
    color: #6a5cff;
    background: #fff;
    font-weight: 600;
  }
  .btn-brand-outline:hover{
    background: linear-gradient(90deg, rgba(106,92,255,.12), rgba(255,79,216,.12));
    color:#4a3cff;
  }

  /* tablo hover daha premium */
  .table-hover-soft tbody tr:hover{
    background: rgba(0,0,0,.02);
  }

  /* “İptal edildi” gibi rozetleri daha kurumsal göstermek istersen (partial etkiliyorsa) */
  .badge-soft-muted{
    background: rgba(108,117,125,.12);
    color: #6c757d;
    border: 1px solid rgba(108,117,125,.18);
    font-weight: 600;
  }
</style>

<div class="d-flex justify-content-between align-items-center mb-3">
  <div>
    <div class="fw-semibold" style="font-size:16px;">Paylaşımlar</div>
    <div class="text-muted small">
      Planladığınız, yayınlanan veya sorun yaşanan tüm paylaşımlarınızı buradan takip edebilirsiniz.
    </div>
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
               placeholder="Gönderi başlığı, hesap adı veya açıklama ara"
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
        <button class="btn btn-brand w-100" type="submit">Filtrele</button>
      </div>

      <div class="col-12 d-flex justify-content-between align-items-center mt-2">
        <div class="text-muted small">
          <?= $hasAnyFilter ? 'Filtreler uygulanıyor.' : 'Tüm paylaşımlar gösteriliyor.' ?>
        </div>

        <?php if ($hasAnyFilter): ?>
          <a class="small text-decoration-none" href="<?= site_url('panel/publishes') ?>">
            Filtreleri temizle
          </a>
        <?php endif; ?>
      </div>

    </div>
  </div>
</form>

<div class="card">
  <div class="card-body p-0">

    <div class="table-responsive">
      <table class="table mb-0 align-middle table-hover-soft">
        <thead>
          <tr>
            <th style="width:70px;">ID</th>
            <th style="width:130px;">Platform</th>
            <th>Hesap</th>
            <th>İçerik</th>
            <th style="width:170px;">Durum</th>
            <th style="width:190px;">Planlanan</th>
            <th style="width:190px;">Yayınlanan</th>
            <th class="text-end" style="width:210px;">İşlemler</th>
          </tr>
        </thead>

        <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="8" class="text-center text-muted py-5">
              <div class="fw-semibold mb-1">Bu kriterlere uygun bir paylaşım bulunmuyor.</div>
              <div class="small mb-3">Filtreleri değiştirebilir veya yeni bir gönderi planlayabilirsiniz.</div>
              <a href="<?= site_url('panel/planner') ?>" class="btn btn-brand btn-sm">
                Yeni Gönderi Planla
              </a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $status = (string)($r['status'] ?? '');

              if (!empty($r['sa_username'])) $accLabel = '@' . $r['sa_username'];
              elseif (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
              else $accLabel = 'Hesap #' . (int)($r['account_id'] ?? 0);

              $contentTitle = trim((string)($r['content_title'] ?? ''));
              $contentId = (int)($r['content_id'] ?? 0);
              $contentLabelMain = $contentTitle !== '' ? $contentTitle : 'Paylaşım içeriği';
              $contentLabelSub  = $contentId ? ('İçerik ID: #' . $contentId) : '';

              // yayınlanma tarihi fallback
              $publishedAt = $r['published_at'] ?? null;
              if (empty($publishedAt) && ($r['status'] ?? '') === 'published') {
                $publishedAt = $r['updated_at'] ?? null;
              }
            ?>

            <tr style="cursor:pointer" onclick="window.location='<?= site_url('panel/publishes/' . (int)$r['id']) ?>'">
              <td><?= (int)$r['id'] ?></td>
              <td><?= esc(strtoupper((string)$r['platform'])) ?></td>
              <td><?= esc($accLabel) ?></td>

              <td>
                <div class="fw-semibold"><?= esc($contentLabelMain) ?></div>
                <?php if ($contentLabelSub !== ''): ?>
                  <div class="text-muted small"><?= esc($contentLabelSub) ?></div>
                <?php endif; ?>
              </td>

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
                    Video işleniyor, otomatik olarak yayınlanacak.
                  </div>
                <?php endif; ?>
              </td>

              <td><?= esc(ui_dt_tr($r['schedule_at'] ?? null)) ?></td>
              <td><?= esc(ui_dt_tr($publishedAt)) ?></td>

              <td class="text-end" onclick="event.stopPropagation()">
                <div class="d-inline-flex gap-2 align-items-center">

                  <a class="btn btn-outline-secondary btn-sm"
                     href="<?= site_url('panel/publishes/' . (int)$r['id']) ?>">
                    Detay
                  </a>

                  <?php
                    $previewUrl = '';
                    $mj2 = $r['meta_json'] ?? '';
                    if ($mj2) {
                      $arr = json_decode((string)$mj2, true);
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
                    <a class="btn btn-brand-outline btn-sm"
                       href="<?= esc($previewUrl) ?>"
                       target="_blank" rel="noopener">
                      Görüntüle
                    </a>
                  <?php endif; ?>

                  <?php if (in_array($status, ['queued','scheduled'], true)): ?>
                    <form action="<?= site_url('panel/publishes/' . (int)$r['id'] . '/cancel') ?>"
                          method="post"
                          class="d-inline"
                          data-confirm="Bu paylaşımı iptal etmek istediğinize emin misiniz? (Bu işlem geri alınamaz)"
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
