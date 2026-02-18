<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
function trPlatform($p) {
  $p = strtolower((string)$p);
  return match($p) {
    'instagram' => 'Instagram',
    'facebook'  => 'Facebook',
    'youtube'   => 'YouTube',
    'tiktok'    => 'TikTok',
    default     => strtoupper($p ?: '-')
  };
}

function trPublishStatus($s) {
  $s = strtolower((string)$s);
  return match($s) {
    'queued'     => ['label'=>'Kuyruğa alındı', 'class'=>'text-bg-secondary'],
    'scheduled'  => ['label'=>'Planlandı', 'class'=>'text-bg-primary'],
    'published'  => ['label'=>'Yayınlandı', 'class'=>'text-bg-success'],
    'success'    => ['label'=>'Yayınlandı', 'class'=>'text-bg-success'],
    'failed'     => ['label'=>'Hata', 'class'=>'text-bg-danger'],
    'error'      => ['label'=>'Hata', 'class'=>'text-bg-danger'],
    default      => ['label'=>($s ?: 'Bilinmiyor'), 'class'=>'text-bg-light border']
  };
}

// Türkçe ay başlığı
$monthTitle = (new IntlDateFormatter('tr_TR', IntlDateFormatter::LONG, IntlDateFormatter::NONE, null, null, 'MMMM yyyy'))
  ->format($monthStart);
?>

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
          <div class="display-6 mb-1"><?= esc($rootCount ?? 0) ?></div>
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
          <div class="display-6 mb-1"><?= esc($dealerCount ?? 0) ?></div>
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
          <div class="display-6 mb-1"><?= esc($activeUserCount ?? 0) ?></div>
          <div class="text-muted small">Aktif durumda olan kullanıcılar.</div>
        </div>
        <span class="badge rounded-pill text-bg-success" style="height:fit-content;">
          <i class="bi bi-check2-circle me-1"></i> Aktif
        </span>
      </div>
    </div>
  </div>
</div>

<div class="row g-3 mb-3">
  <!-- Bu hafta planlı -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div>
            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Bu Hafta Planlı</div>
            <div class="display-6 mb-1"><?= esc($thisWeekPlannedCount ?? 0) ?></div>
            <div class="text-muted small">Bu hafta için planlanan paylaşım adedi.</div>
          </div>
          <a href="<?= base_url('admin/calendar') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar3 me-1"></i> Takvim
          </a>
        </div>

        <div class="text-muted small mb-2">Yaklaşan Paylaşımlar (Son 5)</div>

        <?php if (empty($upcomingPublishes)): ?>
          <div class="p-3 rounded border bg-white text-muted small">Henüz planlı paylaşım yok.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($upcomingPublishes as $p): ?>
              <?php $st = trPublishStatus($p['status'] ?? ''); ?>
              <a class="list-group-item list-group-item-action d-flex justify-content-between align-items-start"
                 href="<?= base_url('admin/publishes') ?>">
                <div>
                  <div class="fw-semibold"><?= esc(trPlatform($p['platform'] ?? '-')) ?></div>
                  <div class="text-muted small">
                    <?= esc($p['user_name'] ?? '-') ?> (<?= esc($p['user_email'] ?? '-') ?>)
                  </div>
                  <div class="text-muted small">Planlanan: <?= esc(date('d.m.Y H:i', strtotime($p['schedule_at']))) ?></div>
                </div>
                <span class="badge <?= esc($st['class']) ?>"><?= esc($st['label']) ?></span>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <div class="d-flex gap-2 mt-3">
          <a href="<?= base_url('admin/publishes') ?>" class="btn btn-outline-secondary w-50">
            <i class="bi bi-list-ul me-1"></i> Tüm paylaşımlar
          </a>
          <a href="<?= base_url('admin/publishes/new') ?>" class="btn btn-primary w-50">
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
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div>
            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Bağlı Sosyal Hesap</div>
            <div class="display-6 mb-1"><?= esc($connectedAccountsCount ?? 0) ?></div>
            <div class="text-muted small">Bağlı hesaplarınızın kısa özeti.</div>
          </div>
          <a href="<?= base_url('admin/social-accounts') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people me-1"></i> Hesaplar
          </a>
        </div>

        <?php if (empty($connectedAccounts)): ?>
          <div class="p-3 rounded border bg-white text-muted small">
            Henüz hesap bağlı değil.<br> Sosyal hesaplarınızı bağlayarak paylaşım planlamaya başlayın.
          </div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($connectedAccounts as $a): ?>
              <div class="list-group-item d-flex align-items-center justify-content-between">
                <div>
                  <div class="fw-semibold"><?= esc($a['username'] ?? '-') ?></div>
                  <div class="text-muted small"><?= esc(trPlatform($a['platform'] ?? '-')) ?></div>
                </div>
                <span class="badge rounded-pill text-bg-success">Bağlı</span>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="<?= base_url('admin/social-accounts') ?>" class="btn btn-outline-secondary w-100 mt-3">
          <i class="bi bi-gear me-1"></i> Hesapları yönet
        </a>
      </div>
    </div>
  </div>

  <!-- Aktif şablon -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div>
            <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">Aktif Şablon</div>
            <div class="display-6 mb-1"><?= esc($activeTemplatesCount ?? 0) ?></div>
            <div class="text-muted small">Kullanıma açık şablon sayısı.</div>
          </div>
          <a href="<?= base_url('admin/templates') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-images me-1"></i> Şablonlar
          </a>
        </div>

        <?php if (empty($activeTemplates)): ?>
          <div class="p-3 rounded border bg-white text-muted small">Aktif şablon yok.</div>
        <?php else: ?>
          <div class="list-group list-group-flush">
            <?php foreach ($activeTemplates as $t): ?>
              <?php
                $thumb = null;
                if (!empty($t['base_media_id'])) $thumb = base_url('media/' . $t['base_media_id']);
                elseif (!empty($t['thumb_path'])) $thumb = base_url($t['thumb_path']);
              ?>
              <div class="list-group-item d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                  <div style="width:40px;height:40px;border-radius:10px;border:1px solid #eee;overflow:hidden;background:#fafafa;display:flex;align-items:center;justify-content:center;">
                    <?php if ($thumb): ?>
                      <img src="<?= esc($thumb) ?>" alt="Önizleme" style="width:100%;height:100%;object-fit:cover;">
                    <?php else: ?>
                      <i class="bi bi-image text-muted"></i>
                    <?php endif; ?>
                  </div>
                  <div>
                    <div class="fw-semibold"><?= esc($t['name'] ?? '-') ?></div>
                    <div class="text-muted small">
                      <?= esc(trPlatform($t['platform_type'] ?? '-')) ?> • <?= esc($t['type'] ?? '-') ?>
                    </div>
                  </div>
                </div>
                <a class="btn btn-sm btn-outline-primary" href="<?= base_url('admin/templates') ?>">Aç</a>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="<?= base_url('admin/templates') ?>" class="btn btn-outline-secondary w-100 mt-3">Şablonlara git</a>
      </div>
    </div>
  </div>
</div>

<div class="row g-3">
  <!-- Takvim özeti -->
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div>
            <h5 class="mb-1">Takvim Özeti</h5>
            <div class="text-muted small">Bu ayın planlı ve yayınlanan paylaşımlarını hızlıca görün.</div>
          </div>
          <div class="d-flex gap-2">
            <span class="badge text-bg-primary">Planlı</span>
            <span class="badge text-bg-success">Yayınlanan</span>
          </div>
        </div>

        <div class="p-3 rounded border bg-white">
          <div class="fw-semibold mb-2"><?= esc(mb_convert_case($monthTitle, MB_CASE_UPPER, "UTF-8")) ?></div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle mb-0">
              <thead>
                <tr class="text-center">
                  <th>Pzt</th><th>Sal</th><th>Çar</th><th>Per</th><th>Cum</th><th>Cmt</th><th>Paz</th>
                </tr>
              </thead>
              <tbody>
                <?php
                $firstDay = (clone $monthStart);
                $firstDow = (int)$firstDay->format('N'); // 1=Mon
                $daysInMonth = (int)(clone $monthStart)->modify('last day of this month')->format('j');

                $cell = 1;
                $day = 1;

                // 6 satır güvenli
                for ($row=0; $row<6; $row++):
                  echo "<tr class='text-center'>";
                  for ($col=1; $col<=7; $col++):
                    if ($cell < $firstDow || $day > $daysInMonth) {
                      echo "<td style='height:72px;background:#fcfcfc;'></td>";
                    } else {
                      $dStr = $monthStart->format('Y-m-') . str_pad((string)$day, 2, '0', STR_PAD_LEFT);
                      $pCount = $plannedByDay[$dStr] ?? 0;
                      $yCount = $publishedByDay[$dStr] ?? 0;

                      echo "<td style='height:72px;vertical-align:top;'>";
                      echo "<div class='fw-semibold'>$day</div>";
                      if ($pCount) echo "<div class='small badge text-bg-primary mt-1'>Planlı: $pCount</div><br>";
                      if ($yCount) echo "<div class='small badge text-bg-success mt-1'>Yayın: $yCount</div>";
                      echo "</td>";
                      $day++;
                    }
                    $cell++;
                  endfor;
                  echo "</tr>";
                  if ($day > $daysInMonth) break;
                endfor;
                ?>
              </tbody>
            </table>
          </div>

          <div class="d-flex gap-2 mt-3">
            <a href="<?= base_url('admin/calendar') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-calendar3 me-1"></i> Takvime git
            </a>
            <a href="<?= base_url('admin/templates') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-images me-1"></i> Şablonlardan oluştur
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <!-- Son aktiviteler -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between mb-2">
          <div>
            <h6 class="mb-1">Son Aktiviteler</h6>
            <div class="text-muted small">Son yayınlar ve işlem geçmişi.</div>
          </div>
          <a href="<?= base_url('admin/publishes') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-journal-text me-1"></i> Kayıtlar
          </a>
        </div>

        <?php if (empty($recentActivities)): ?>
          <div class="p-3 rounded border bg-white text-muted small">Henüz aktivite yok.</div>
        <?php else: ?>
          <div class="d-grid gap-2">
            <?php foreach ($recentActivities as $a): ?>
              <?php $st = trPublishStatus($a['status'] ?? ''); ?>
              <a class="p-3 rounded border bg-white text-decoration-none" href="<?= base_url('admin/publishes') ?>">
                <div class="text-muted small mb-1">
                  <?= esc(date('d.m.Y H:i', strtotime($a['created_at'] ?? 'now'))) ?>
                </div>
                <div class="d-flex align-items-center justify-content-between">
                  <div class="fw-semibold"><?= esc($a['user_name'] ?? '-') ?></div>
                  <span class="badge <?= esc($st['class']) ?>"><?= esc($st['label']) ?></span>
                </div>
                <div class="text-muted small">
                  <?= esc(trPlatform($a['platform'] ?? '-')) ?>
                  <?php if (!empty($a['schedule_at'])): ?>
                    • Planlanan: <?= esc(date('d.m.Y H:i', strtotime($a['schedule_at']))) ?>
                  <?php endif; ?>
                  <?php if (!empty($a['published_at'])): ?>
                    • Yayın: <?= esc(date('d.m.Y H:i', strtotime($a['published_at']))) ?>
                  <?php endif; ?>
                </div>
              </a>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="<?= base_url('admin/publishes') ?>" class="btn btn-outline-secondary w-100 mt-3">
          Tüm kayıtları görüntüle
        </a>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
