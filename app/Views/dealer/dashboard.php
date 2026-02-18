<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">

  <div class="row g-3 mb-3">

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Toplam Kullanıcı</div>
          <div class="display-6 mb-1"><?= esc($totalUsers ?? 0) ?></div>
          <div class="text-muted small">Sadece senin alt kullanıcıların (created_by).</div>
          <div class="mt-3">
            <a href="<?= base_url('dealer/users') ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-people me-1"></i> Kullanıcıları Yönet
            </a>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Aktif Kullanıcı</div>
          <div class="display-6 mb-1"><?= esc($activeUsers ?? 0) ?></div>
          <div class="text-muted small">Giriş yapabilen alt kullanıcılar.</div>
          <div class="mt-3 small text-muted">
            Pasif: <b><?= esc($passiveUsers ?? 0) ?></b>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">7 Günlük Planlı Paylaşım</div>
          <div class="display-6 mb-1"><?= esc($plannedCount ?? 0) ?></div>
          <div class="text-muted small">Alt kullanıcıların yakın tarihteki planları.</div>

          <div class="mt-3 d-flex gap-2 flex-wrap">
            <a href="<?= base_url('dealer/publishes?status=queued') ?>" class="btn btn-sm btn-outline-secondary">
              <i class="bi bi-calendar2-check me-1"></i> Planlıları Gör
            </a>

            <?php if (!empty($failed7dCount)): ?>
              <a href="<?= base_url('dealer/publishes?status=failed') ?>" class="btn btn-sm btn-outline-danger">
                <i class="bi bi-exclamation-triangle me-1"></i> Hata: <?= (int)$failed7dCount ?>
              </a>
            <?php else: ?>
              <span class="badge rounded-pill text-bg-light border align-self-center">
                Son 7 gün hata yok
              </span>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

  </div>

  <div class="row g-3">

    <div class="col-lg-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">

          <div class="d-flex align-items-start justify-content-between">
            <div>
              <h5 class="mb-1">Son Paylaşımlar</h5>
              <div class="text-muted small">Alt kullanıcıların son hareketleri.</div>
            </div>
            <span class="badge rounded-pill text-bg-light border" style="height:fit-content;">
              Tenant: <?= esc(session('tenant_id') ?? '-') ?>
            </span>
          </div>

          <div class="table-responsive mt-3">
            <table class="table align-middle mb-0">
              <thead>
                <tr>
                  <th style="width:70px;">ID</th>
                  <th style="width:110px;">Platform</th>
                  <th style="width:140px;">Durum</th>
                  <th>Kim</th>
                  <th style="width:170px;">Planlanan</th>
                  <th style="width:170px;">Yayınlanan</th>
                </tr>
              </thead>
              <tbody>
                <?php if (empty($recentPublishes)): ?>
                  <tr>
                    <td colspan="6" class="text-center text-muted py-4">Henüz kayıt yok.</td>
                  </tr>
                <?php else: ?>
                  <?php foreach ($recentPublishes as $p): ?>
                    <?php
                      $st = strtolower((string)($p['status'] ?? ''));
                      $badge = 'badge-soft-info';
                      if (in_array($st, ['published','done','success'], true)) $badge = 'badge-soft-success';
                      elseif (in_array($st, ['failed','error'], true)) $badge = 'badge-soft-danger';
                      elseif (in_array($st, ['queued','scheduled','running'], true)) $badge = 'badge-soft-warning';
                    ?>
                    <tr>
                      <td><?= (int)$p['id'] ?></td>
                      <td><code><?= esc(strtoupper((string)$p['platform'])) ?></code></td>
                      <td><span class="badge <?= $badge ?>"><?= esc((string)$p['status']) ?></span></td>
                      <td>
                        <div class="fw-semibold"><?= esc((string)($p['user_name'] ?? 'Kullanıcı')) ?></div>
                        <div class="text-muted small"><?= esc((string)($p['user_email'] ?? '')) ?></div>
                      </td>
                      <td class="text-muted"><?= esc((string)($p['schedule_at'] ?? '—')) ?></td>
                      <td class="text-muted"><?= esc((string)($p['published_at'] ?? '—')) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <div class="mt-3 d-flex justify-content-end">
            <a href="<?= base_url('dealer/publishes') ?>" class="btn btn-sm btn-outline-primary">
              <i class="bi bi-arrow-right me-1"></i> Tüm Paylaşımlar
            </a>
          </div>

        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-1">Hızlı Aksiyonlar</h6>
          <div class="text-muted small mb-3">Sık kullanılan işlemler.</div>

          <div class="d-grid gap-2">
            <a href="<?= base_url('dealer/users') ?>" class="btn btn-outline-primary">
              <i class="bi bi-people me-1"></i> Kullanıcılarım
            </a>
            <a href="<?= base_url('dealer/publishes') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-send me-1"></i> Paylaşımlar
            </a>
            <a href="<?= base_url('dealer/jobs') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-gear me-1"></i> Arka Plan İşleri
            </a>
            <a href="<?= base_url('panel') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-box-arrow-in-right me-1"></i> Kendi Panelime Git
            </a>
          </div>

          <hr class="my-3">

          <div class="small text-muted">
            <div class="fw-semibold mb-1">Bayi kısıtları</div>
            <ul class="mb-0 ps-3">
              <li>Sistem ayarlarına erişemez</li>
              <li>Şablon ekleyip düzenleyemez</li>
              <li>Sadece kendi alt kullanıcılarını görür</li>
            </ul>
          </div>

        </div>
      </div>
    </div>

  </div>

</div>

<style>
  .badge-soft-info{background:rgba(59,130,246,.12);color:#1d4ed8;border:1px solid rgba(59,130,246,.18);font-weight:600;}
  .badge-soft-warning{background:rgba(245,158,11,.14);color:#92400e;border:1px solid rgba(245,158,11,.22);font-weight:600;}
  .badge-soft-danger{background:rgba(239,68,68,.12);color:#b91c1c;border:1px solid rgba(239,68,68,.20);font-weight:600;}
  .badge-soft-success{background:rgba(34,197,94,.12);color:#166534;border:1px solid rgba(34,197,94,.20);font-weight:600;}
</style>

<?= $this->endSection() ?>
