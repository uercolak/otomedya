<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<?php
// Takvim için yardımcılar
$year  = (int)date('Y');
$month = (int)date('m');
$firstDayTs = strtotime("$year-$month-01");
$daysInMonth = (int)date('t', $firstDayTs);
$firstWeekday = (int)date('N', $firstDayTs); // 1(Pzt) - 7(Paz)
$monthTitle = mb_strtoupper(strftime('%B %Y'));
?>

<div class="row g-3 mb-3">
  <!-- KPI -->
  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Toplam Kullanıcı</div>
        <div class="display-6"><?= esc($totalUsers ?? 0) ?></div>
        <div class="text-muted small">Toplam kayıtlı kullanıcı sayısı.</div>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div>
            <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Yönetici Sayısı</div>
            <div class="display-6"><?= esc($adminCount ?? 0) ?></div>
            <div class="text-muted small">Yönetim yetkisi olan hesaplar.</div>
          </div>
          <span class="badge rounded-pill text-bg-danger" style="height:fit-content;">
            <i class="bi bi-shield-lock me-1"></i> Yönetici
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div>
            <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Bayi Sayısı</div>
            <div class="display-6"><?= esc($bayiCount ?? 0) ?></div>
            <div class="text-muted small">Bayi paneline erişen hesaplar.</div>
          </div>
          <span class="badge rounded-pill text-bg-warning" style="height:fit-content;">
            <i class="bi bi-diagram-3 me-1"></i> Bayi
          </span>
        </div>
      </div>
    </div>
  </div>

  <div class="col-lg-3">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex justify-content-between">
          <div>
            <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Aktif Kullanıcı</div>
            <div class="display-6"><?= esc($activeUserCount ?? 0) ?></div>
            <div class="text-muted small">Aktif durumda olan kullanıcılar.</div>
          </div>
          <span class="badge rounded-pill text-bg-success" style="height:fit-content;">
            <i class="bi bi-person-check me-1"></i> Aktif
          </span>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ÜST ORTA 3 KUTU -->
<div class="row g-3 mb-3">
  <!-- Bu hafta planlı -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Bu Hafta Planlı</div>
            <div class="display-6 mb-1"><?= esc($weeklyPlansCount ?? 0) ?></div>
            <div class="text-muted small">Bu hafta için planlanan paylaşım adedi.</div>
          </div>
          <a href="<?= base_url('admin/scheduled') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-calendar3 me-1"></i> Takvim
          </a>
        </div>

        <hr class="my-3">

        <div class="text-muted small mb-2">Yaklaşan Paylaşımlar (Son 5)</div>

        <?php if (!empty($weeklyPlans)): ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <tbody>
              <?php foreach ($weeklyPlans as $p): ?>
                <tr>
                  <td class="small">
                    <div class="fw-semibold"><?= esc($p['user_name'] ?? '-') ?></div>
                    <div class="text-muted"><?= esc($p['platform'] ?? '-') ?> • <?= esc($p['scheduled_at'] ?? '-') ?></div>
                  </td>
                  <td class="text-end">
                    <?php
                      $st = strtolower((string)($p['status'] ?? 'scheduled'));
                      $badge = 'text-bg-secondary';
                      if (in_array($st, ['scheduled','queued'])) $badge = 'text-bg-primary';
                      if (in_array($st, ['posted','published'])) $badge = 'text-bg-success';
                      if ($st === 'failed') $badge = 'text-bg-danger';
                    ?>
                    <span class="badge <?= $badge ?>"><?= esc($p['status'] ?? '-') ?></span>
                  </td>
                </tr>
              <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php else: ?>
          <div class="p-3 rounded border bg-light small">
            Henüz planlı paylaşım yok.
          </div>
        <?php endif; ?>

        <div class="mt-3 d-flex gap-2">
          <a href="<?= base_url('admin/publishes') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-list me-1"></i> Tüm paylaşımlar
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
            <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Bağlı Sosyal Hesap</div>
            <div class="display-6 mb-1"><?= esc($connectedAccountsCount ?? 0) ?></div>
            <div class="text-muted small">Bağlı hesaplarınızın kısa özeti.</div>
          </div>
          <a href="<?= base_url('admin/social-accounts') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-people me-1"></i> Hesaplar
          </a>
        </div>

        <hr class="my-3">
        <?php if (!empty($latestAccounts)): ?>
          <?php foreach ($latestAccounts as $a): ?>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
              <div class="small">
                <div class="fw-semibold"><?= esc($a['username'] ?? '@Kullanıcı') ?></div>
                <div class="text-muted"><?= esc($a['platform'] ?? '#Platform') ?></div>
              </div>
              <span class="badge text-bg-light border">Bağlı</span>
            </div>
          <?php endforeach; ?>
          <div class="mt-3">
            <a href="<?= base_url('admin/social-accounts') ?>" class="btn btn-sm btn-outline-secondary w-100">
              <i class="bi bi-gear me-1"></i> Hesapları yönet
            </a>
          </div>
        <?php else: ?>
          <div class="p-3 rounded border bg-light small">
            Henüz hesap bağlı değil. Sosyal hesaplarınızı bağlayarak paylaşım planlamaya başlayın.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- Aktif şablonlar -->
  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Aktif Şablon</div>
            <div class="display-6 mb-1"><?= esc($activeTemplatesCount ?? 0) ?></div>
            <div class="text-muted small">Kullanıma açık şablon sayısı.</div>
          </div>
          <a href="<?= base_url('admin/templates') ?>" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-images me-1"></i> Şablonlar
          </a>
        </div>

        <hr class="my-3">

        <?php if (!empty($activeTemplates)): ?>
          <?php foreach ($activeTemplates as $t): ?>
            <div class="d-flex align-items-center justify-content-between py-2 border-bottom">
              <div class="small">
                <div class="fw-semibold"><?= esc($t['name'] ?? '-') ?></div>
                <div class="text-muted">
                  <?= esc($t['platform_scope'] ?? '-') ?> • <?= esc($t['type'] ?? '-') ?>
                </div>
              </div>
              <a href="<?= base_url('admin/templates') ?>" class="btn btn-sm btn-outline-primary">Aç</a>
            </div>
          <?php endforeach; ?>

          <div class="mt-3">
            <a href="<?= base_url('admin/templates') ?>" class="btn btn-sm btn-outline-secondary w-100">
              Şablonlara git
            </a>
          </div>
        <?php else: ?>
          <div class="p-3 rounded border bg-light small">
            Henüz aktif şablon yok.
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<!-- ALT: Takvim + Son aktiviteler -->
<div class="row g-3">
  <div class="col-lg-8">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <h5 class="mb-1">Takvim Özeti</h5>
            <div class="text-muted small">Bu ayın planlı ve yayınlanan paylaşımlarını hızlıca görün.</div>
          </div>
          <div class="d-flex gap-2 align-items-center">
            <span class="badge text-bg-primary">Planlı</span>
            <span class="badge text-bg-success">Yayınlanan</span>
          </div>
        </div>

        <div class="mt-3 p-3 rounded border bg-white">
          <div class="fw-semibold mb-2"><?= esc($monthTitle) ?></div>

          <div class="table-responsive">
            <table class="table table-bordered align-middle text-center mb-0">
              <thead class="small text-muted">
                <tr>
                  <th>Pzt</th><th>Sal</th><th>Çar</th><th>Per</th><th>Cum</th><th>Cmt</th><th>Paz</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <?php
                  // boş hücreler
                  for ($i=1; $i<$firstWeekday; $i++) {
                      echo '<td class="bg-light"></td>';
                  }

                  $weekday = $firstWeekday;
                  for ($d=1; $d<=$daysInMonth; $d++) {
                      $dateKey = sprintf('%04d-%02d-%02d', $year, $month, $d);
                      $planned = $dayStats[$dateKey]['planned'] ?? 0;
                      $posted  = $dayStats[$dateKey]['posted'] ?? 0;

                      echo '<td style="min-width:92px; vertical-align:top;">';
                      echo '<div class="small fw-semibold">'.$d.'</div>';
                      echo '<div class="mt-1 d-flex justify-content-center gap-1 flex-wrap">';
                      if ($planned > 0) echo '<span class="badge text-bg-primary">'.$planned.'</span>';
                      if ($posted  > 0) echo '<span class="badge text-bg-success">'.$posted.'</span>';
                      if ($planned==0 && $posted==0) echo '<span class="text-muted small">-</span>';
                      echo '</div>';
                      echo '</td>';

                      if ($weekday == 7 && $d != $daysInMonth) {
                          echo '</tr><tr>';
                          $weekday = 1;
                      } else {
                          $weekday++;
                      }
                  }

                  // ay bitince kalan boşlar
                  if ($weekday !== 1) {
                      for ($i=$weekday; $i<=7; $i++) echo '<td class="bg-light"></td>';
                  }
                  ?>
                </tr>
              </tbody>
            </table>
          </div>

          <div class="mt-3 d-flex gap-2">
            <a href="<?= base_url('admin/templates') ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-images me-1"></i> Şablonlardan oluştur
            </a>
          </div>
        </div>

      </div>
    </div>
  </div>

  <div class="col-lg-4">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body">
        <div class="d-flex align-items-start justify-content-between">
          <div>
            <h6 class="mb-1">Son Aktiviteler</h6>
            <div class="text-muted small">Son yayınlar ve işlem geçmişi.</div>
          </div>
          <span class="badge rounded-pill text-bg-light border">
            <i class="bi bi-activity me-1"></i> Kayıtlar
          </span>
        </div>

        <hr class="my-3">

        <?php if (!empty($recentLogs)): ?>
          <?php foreach ($recentLogs as $l): ?>
            <div class="p-3 rounded border bg-white mb-2">
              <div class="small text-muted mb-1"><?= esc($l['created_at'] ?? '-') ?></div>
              <div class="fw-semibold small"><?= esc($l['level'] ?? 'info') ?></div>
              <div class="text-muted small"><?= esc($l['message'] ?? '-') ?></div>
            </div>
          <?php endforeach; ?>
          <a href="<?= base_url('admin/logs') ?>" class="btn btn-sm btn-outline-secondary w-100">
            Tüm kayıtları görüntüle
          </a>
        <?php else: ?>
          <div class="p-3 rounded border bg-light">
            <div class="fw-semibold">Henüz aktivite yok</div>
            <div class="text-muted small">Yeni işlem oluştuğunda burada durumlar görüntülenir.</div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<?= $this->endSection() ?>
