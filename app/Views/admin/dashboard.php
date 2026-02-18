<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
// Türkçe ay adı için (Intl şart)
$dt = new \DateTime(sprintf('%04d-%02d-01', $calendarYear, $calendarMonth));
$fmt = new \IntlDateFormatter('tr_TR', \IntlDateFormatter::LONG, \IntlDateFormatter::NONE);
$fmt->setPattern('MMMM yyyy');
$monthTitle = mb_strtoupper($fmt->format($dt), 'UTF-8');

// Takvim için hesaplamalar
$firstDay = new \DateTime(sprintf('%04d-%02d-01', $calendarYear, $calendarMonth));
$daysInMonth = (int)$firstDay->format('t');

// Pazartesi bazlı takvim: 1=Pzt ... 7=Paz
$startWeekday = (int)$firstDay->format('N');
$cellCount = (int)ceil(($startWeekday - 1 + $daysInMonth) / 7) * 7;

function trLogMessage($msg) {
  $map = [
    'job.reserved'  => 'İş kuyruğa alındı',
    'job.succeeded' => 'İş başarıyla tamamlandı',
    'job.failed'    => 'İş hata verdi',
  ];
  return $map[$msg] ?? $msg;
}
function trLogLevel($lvl) {
  $lvl = strtolower((string)$lvl);
  $map = ['info'=>'Bilgi','warning'=>'Uyarı','error'=>'Hata','debug'=>'Debug'];
  return $map[$lvl] ?? ucfirst($lvl);
}
?>

<!-- ÜST KARTLAR -->
<div class="row g-3 mb-3">
  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Toplam Kullanıcı</div>
        <div class="display-6 mb-1"><?= esc($totalUsers ?? 0) ?></div>
        <div class="text-muted small">Toplam kayıtlı kullanıcı sayısı.</div>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-start justify-content-between">
        <div>
          <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Yönetici Sayısı</div>
          <div class="display-6 mb-1"><?= esc($admins ?? 0) ?></div>
          <div class="text-muted small">Yönetim yetkisi olan hesaplar.</div>
        </div>
        <span class="badge rounded-pill text-bg-danger" style="height:fit-content;">
          <i class="bi bi-shield-lock me-1"></i> Yönetici
        </span>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-start justify-content-between">
        <div>
          <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Bayi Sayısı</div>
          <div class="display-6 mb-1"><?= esc($dealers ?? 0) ?></div>
          <div class="text-muted small">Bayi paneline erişebilen hesaplar.</div>
        </div>
        <span class="badge rounded-pill text-bg-warning" style="height:fit-content;">
          <i class="bi bi-diagram-3 me-1"></i> Bayi
        </span>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body d-flex align-items-start justify-content-between">
        <div>
          <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Aktif Kullanıcı</div>
          <div class="display-6 mb-1"><?= esc($activeUsers ?? 0) ?></div>
          <div class="text-muted small">Aktif durumda olan kullanıcılar.</div>
        </div>
        <span class="badge rounded-pill text-bg-success" style="height:fit-content;">
          <i class="bi bi-check2-circle me-1"></i> Aktif
        </span>
      </div>
    </div>
  </div>
</div>

<!-- ORTA KISIM: 3 KART -->
<div class="row g-3 mb-3">
  <!-- Bu hafta planlı + Yaklaşan (Son 5) -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Bu Hafta Planlı</div>
            <div class="display-6 mb-1"><?= esc($weekPlanned ?? 0) ?></div>
            <div class="text-muted small">Bu hafta için planlanan paylaşım adedi.</div>
          </div>
          <a href="<?= base_url('panel/calendar') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar3 me-1"></i> Takvim
          </a>
        </div>

        <hr class="my-3">

        <div class="fw-semibold mb-2">Yaklaşan Paylaşımlar (Son 5)</div>

        <?php if (empty($upcomingPlans)): ?>
          <div class="p-3 rounded border bg-white text-muted small">
            Henüz planlı paylaşım yok.<br>
            Takvimden yeni bir paylaşım planlayabilirsin.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($upcomingPlans as $p): ?>
              <div class="list-group-item px-0">
                <div class="d-flex justify-content-between">
                  <div class="fw-semibold"><?= esc(strtoupper($p['platform'] ?? '-')) ?></div>
                  <div class="text-muted small"><?= esc(date('d.m.Y H:i', strtotime($p['schedule_at']))) ?></div>
                </div>
                <div class="text-muted small">
                  <?= esc($p['user_name'] ?? '-') ?> (<?= esc($p['user_email'] ?? '-') ?>)
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-3">
          <a href="<?= base_url('admin/publishes') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list-ul me-1"></i> Tüm paylaşımlar
          </a>
          <a href="<?= base_url('panel/publishes/create') ?>" class="btn btn-sm btn-primary">
            <i class="bi bi-plus-lg me-1"></i> Yeni paylaşım planla
          </a>
        </div>
      </div>
    </div>
  </div>

  <!-- Bağlı sosyal hesap -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Bağlı Sosyal Hesap</div>
            <div class="display-6 mb-1"><?= esc($accountsCount ?? 0) ?></div>
            <div class="text-muted small">Bağlı hesaplarınızın kısa özeti.</div>
          </div>
          <a href="<?= base_url('panel/accounts') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-share me-1"></i> Hesaplar
          </a>
        </div>

        <hr class="my-3">

        <?php if (empty($accountsList)): ?>
          <div class="p-3 rounded border bg-white text-muted small">
            Henüz hesap bağlı değil.<br>
            Sosyal hesaplarını bağlayarak paylaşım planlamaya başlayın.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($accountsList as $a): ?>
              <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                <div>
                  <div class="fw-semibold">
                    <?= esc($a['username'] ?: ($a['name'] ?? '-')) ?>
                  </div>
                  <div class="text-muted small"><?= esc($a['platform'] ?? '-') ?></div>
                </div>
                <span class="badge rounded-pill text-bg-light border">Bağlı</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="<?= base_url('panel/accounts') ?>" class="btn btn-sm btn-outline-secondary w-100 mt-3">
          <i class="bi bi-gear me-1"></i> Hesapları yönet
        </a>
      </div>
    </div>
  </div>

  <!-- Aktif Şablon -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Aktif Şablon</div>
            <div class="display-6 mb-1"><?= esc($activeTemplateCount ?? 0) ?></div>
            <div class="text-muted small">Kullanıma açık şablon sayısı.</div>
          </div>
          <a href="<?= base_url('panel/templates') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-images me-1"></i> Şablonlar
          </a>
        </div>

        <hr class="my-3">

        <?php if (empty($activeTemplates)): ?>
          <div class="p-3 rounded border bg-white text-muted small">
            Aktif şablon bulunamadı.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($activeTemplates as $t): ?>
              <div class="list-group-item px-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <?php if (!empty($t['thumb_path'])): ?>
                    <img src="<?= base_url($t['thumb_path']) ?>"
                         alt=""
                         style="width:36px;height:36px;border-radius:10px;object-fit:cover;border:1px solid #eee;">
                  <?php else: ?>
                    <div style="width:36px;height:36px;border-radius:10px;border:1px solid #eee;"
                         class="d-flex align-items-center justify-content-center text-muted">
                      <i class="bi bi-image"></i>
                    </div>
                  <?php endif; ?>

                  <div>
                    <div class="fw-semibold"><?= esc($t['name'] ?? '-') ?></div>
                    <div class="text-muted small">
                      <?= esc($t['platform_scope'] ?? '-') ?> • <?= esc($t['type'] ?? '-') ?>
                    </div>
                  </div>
                </div>

                <a href="<?= base_url('panel/templates') ?>" class="btn btn-sm btn-outline-primary">Aç</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="<?= base_url('panel/templates') ?>" class="btn btn-sm btn-outline-secondary w-100 mt-3">
          Şablonlara git
        </a>
      </div>
    </div>
  </div>
</div>

<!-- ALT KISIM: Takvim + Son aktiviteler -->
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <h5 class="mb-1">Takvim Özeti</h5>
            <div class="text-muted small">Bu ayın planlı ve yayınlanan paylaşımlarını hızlıca görün.</div>
          </div>
          <div class="d-flex gap-2">
            <span class="badge rounded-pill text-bg-primary">Planlı</span>
            <span class="badge rounded-pill text-bg-success">Yayınlanan</span>
          </div>
        </div>

        <div class="mt-3 p-3 rounded border bg-white">
          <div class="fw-semibold mb-2"><?= esc($monthTitle) ?></div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0" style="min-width:700px;">
              <thead>
                <tr class="text-center">
                  <th>Pzt</th><th>Sal</th><th>Çar</th><th>Per</th><th>Cum</th><th>Cmt</th><th>Paz</th>
                </tr>
              </thead>
              <tbody>
              <?php
                $day = 1;
                for ($i = 0; $i < $cellCount; $i++):
                  if ($i % 7 === 0) echo '<tr class="text-center" style="height:72px;">';

                  $cellIndex = $i + 1;
                  $isEmpty = ($cellIndex < $startWeekday) || ($day > $daysInMonth);

                  if ($isEmpty) {
                    echo '<td class="text-muted"> </td>';
                  } else {
                    $dateKey = sprintf('%04d-%02d-%02d', $calendarYear, $calendarMonth, $day);
                    $pCount = $calendarPlanned[$dateKey] ?? 0;
                    $yCount = $calendarPublished[$dateKey] ?? 0;

                    echo '<td>';
                    echo '<div class="fw-semibold">'.$day.'</div>';
                    if ($pCount > 0) echo '<div class="small text-primary">Planlı: '.$pCount.'</div>';
                    if ($yCount > 0) echo '<div class="small text-success">Yayınlanan: '.$yCount.'</div>';
                    if ($pCount === 0 && $yCount === 0) echo '<div class="small text-muted">-</div>';
                    echo '</td>';

                    $day++;
                  }

                  if ($i % 7 === 6) echo '</tr>';
                endfor;
              ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex gap-2 mt-3">
            <a href="<?= base_url('panel/calendar') ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-calendar3 me-1"></i> Takvime git
            </a>
            <a href="<?= base_url('panel/templates') ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-images me-1"></i> Şablonlardan oluştur
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <h6 class="mb-1">Son Aktiviteler</h6>
            <div class="text-muted small">Son yayınlar ve işlem geçmişi.</div>
          </div>
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-clock-history me-1"></i> Kayıtlar
          </span>
        </div>

        <hr class="my-3">

        <?php if (empty($logs)): ?>
          <div class="p-3 rounded border bg-white text-muted small">
            Henüz aktivite yok.
          </div>
        <?php else: ?>
          <?php foreach ($logs as $l): ?>
            <div class="p-3 rounded border bg-white mb-2">
              <div class="text-muted small"><?= esc(date('d.m.Y H:i:s', strtotime($l['created_at']))) ?></div>
              <div class="fw-semibold"><?= esc(trLogLevel($l['level'] ?? 'info')) ?></div>
              <div class="text-muted"><?= esc(trLogMessage($l['message'] ?? '-')) ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>

        <a href="<?= base_url('admin/logs') ?>" class="btn btn-outline-secondary w-100 mt-2">
          Tüm kayıtları görüntüle
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
