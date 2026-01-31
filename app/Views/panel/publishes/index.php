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
      return esc(ui_dt($val));
    }
  }
}

$hasAnyFilter = false;
foreach (['q','status','date_from','date_to'] as $k) {
  if (!empty(trim((string)($filters[$k] ?? '')))) { $hasAnyFilter = true; break; }
}

// (opsiyonel) controller göndermediyse bile güvenli olsun
$hasTiktokAccount = (bool)($hasTiktokAccount ?? true);
?>

<style>
  .btn-brand{
    background: linear-gradient(90deg, #6a5cff, #ff4fd8);
    border: 0;
    color: #fff;
    font-weight: 700;
    border-radius: 999px;
  }
  .btn-brand:hover{ filter: brightness(0.97); color:#fff; }

  .btn-brand-outline{
    border: 1px solid rgba(106,92,255,.45);
    color: #6a5cff;
    background: #fff;
    font-weight: 800;
    border-radius: 999px;
  }
  .btn-brand-outline:hover{
    background: linear-gradient(90deg, rgba(106,92,255,.12), rgba(255,79,216,.12));
    color:#4a3cff;
  }

  .table-hover-soft tbody tr:hover{ background: rgba(0,0,0,.02); }
  .badge-soft-muted{
    background: rgba(108,117,125,.12);
    color: #6c757d;
    border: 1px solid rgba(108,117,125,.18);
    font-weight: 700;
  }

  .page-title{
    font-size: 26px;
    font-weight: 900;
    letter-spacing: -.4px;
    margin-bottom: 2px;
  }
  .page-sub{
    color: rgba(0,0,0,.55);
    font-size: 13.5px;
    max-width: 820px;
  }

  .notice-card{
    border:1px solid rgba(0,0,0,.06);
    border-radius:18px;
    background:#fff;
  }
</style>

<div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
  <div>
    <div class="page-title">TikTok Paylaşımları</div>
    <div class="page-sub">
      Planladığın, yayınlanan veya işlenmekte olan TikTok Direct Post paylaşımlarını buradan takip edebilirsin.
      “İşleniyor” durumunda TikTok videoyu işlemeye devam eder; durum otomatik güncellenir.
    </div>
  </div>

  <a href="<?= site_url('panel/planner') ?>" class="btn btn-brand btn-sm">
    <i class="bi bi-plus-circle me-1"></i> Post to TikTok
  </a>
</div>

<?php if (session('error')): ?>
  <div class="alert alert-danger"><?= esc(session('error')) ?></div>
<?php endif; ?>

<?php if (!$hasTiktokAccount): ?>
  <div class="notice-card p-3 mb-3">
    <div class="fw-bold mb-1">Önce TikTok hesabını bağlamalısın</div>
    <div class="text-muted small mb-2">
      TikTok Direct Post paylaşımı yapabilmek için hesabını yetkilendirmen gerekir.
    </div>
    <a href="<?= site_url('panel/social-accounts') ?>" class="btn btn-brand btn-sm">
      <i class="bi bi-music-note-beamed me-1"></i> TikTok’u Bağla
    </a>
  </div>
<?php endif; ?>

<form method="get" action="<?= site_url('panel/publishes') ?>" class="card mb-3">
  <div class="card-body">
    <div class="row g-2">

      <div class="col-md-5">
        <label class="form-label">Arama</label>
        <input type="text" name="q" class="form-control"
               placeholder="Başlık, hesap adı veya açıklama ara"
               value="<?= esc((string)($filters['q'] ?? '')) ?>">
      </div>

      <div class="col-md-3">
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
          <?= $hasAnyFilter ? 'Filtreler uygulanıyor.' : 'Tüm TikTok paylaşımları gösteriliyor.' ?>
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
            <th>Hesap</th>
            <th>İçerik</th>
            <th style="width:220px;">Durum</th>
            <th style="width:190px;">Planlanan</th>
            <th style="width:190px;">Yayınlanan</th>
            <th class="text-end" style="width:220px;">İşlemler</th>
          </tr>
        </thead>

        <tbody>
        <?php if (empty($rows)): ?>
          <tr>
            <td colspan="7" class="text-center text-muted py-5">
              <div class="fw-semibold mb-1">Henüz bir TikTok paylaşımı yok.</div>
              <div class="small mb-3">Yeni bir video hazırlayıp “Post to TikTok” ekranından yayınlayabilirsin.</div>
              <a href="<?= site_url('panel/planner') ?>" class="btn btn-brand btn-sm">
                Post to TikTok
              </a>
            </td>
          </tr>
        <?php else: ?>
          <?php foreach ($rows as $r): ?>
            <?php
              $status = (string)($r['status'] ?? '');

              // Hesap etiketi
              if (!empty($r['sa_username'])) $accLabel = '@' . $r['sa_username'];
              elseif (!empty($r['sa_name'])) $accLabel = (string)$r['sa_name'];
              else $accLabel = 'TikTok Hesabı';

              $contentTitle = trim((string)($r['content_title'] ?? ''));
              $contentId = (int)($r['content_id'] ?? 0);
              $contentLabelMain = $contentTitle !== '' ? $contentTitle : 'TikTok videosu';
              $contentLabelSub  = $contentId ? ('İçerik ID: #' . $contentId) : '';

              // yayınlanma tarihi fallback
              $publishedAt = $r['published_at'] ?? null;
              if (empty($publishedAt) && ($r['status'] ?? '') === 'published') {
                $publishedAt = $r['updated_at'] ?? null;
              }

              // meta_json içinden creation_id / permalink yakala (TikTok tarafı için de aynı yapıyı kullanıyoruz)
              $mj = [];
              if (!empty($r['meta_json'])) {
                $tmp = json_decode((string)$r['meta_json'], true);
                if (is_array($tmp)) $mj = $tmp;
              }

              // tiktok creation id / publish id (varsa)
              $creationId = (string)($mj['meta']['creation_id'] ?? '');
              $publishId  = (string)($mj['meta']['publish_id'] ?? '');

              $hasProcessingRef = ($creationId !== '' || $publishId !== '');

              // Görüntüleme linki (permalink / remote url)
              $previewUrl = (string)($mj['meta']['permalink'] ?? '');
              $remoteId = (string)($r['remote_id'] ?? '');
              if ($previewUrl === '' && preg_match('~^https?://~i', $remoteId) === 1) {
                $previewUrl = $remoteId;
              }
            ?>

            <tr style="cursor:pointer" onclick="window.location='<?= site_url('panel/publishes/' . (int)$r['id']) ?>'">
              <td><?= (int)$r['id'] ?></td>

              <td><?= esc($accLabel) ?></td>

              <td>
                <div class="fw-semibold"><?= esc($contentLabelMain) ?></div>
                <?php if ($contentLabelSub !== ''): ?>
                  <div class="text-muted small"><?= esc($contentLabelSub) ?></div>
                <?php endif; ?>
              </td>

              <td>
                <?= view('partials/status_badge', ['status' => $status]) ?>

                <?php if (in_array($status, ['publishing','processing'], true) && $hasProcessingRef): ?>
                  <div class="text-muted small mt-1">
                    Video TikTok tarafından işleniyor. Yayınlandığında durum otomatik güncellenir.
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
                          data-confirm="Bu TikTok paylaşımını iptal etmek istediğine emin misin? (Geri alınamaz)"
                          data-confirm-title="Paylaşımı iptal et"
                          data-confirm-ok="Evet, iptal et"
                          data-confirm-ok-class="btn-danger">
                      <?= csrf_field() ?>
                      <button type="submit" class="btn btn-outline-danger btn-sm" style="border-radius:999px; font-weight:800;">
                        İptal
                      </button>
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
