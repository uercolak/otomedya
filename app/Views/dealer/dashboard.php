<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">

  <!-- ÜST: KPI kartları -->
  <div class="row g-3 mb-3">
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Toplam Kullanıcı</div>
          <div class="display-6 mb-1"><?= esc($totalUsers ?? 0) ?></div>
          <div class="text-muted small">Sadece senin alt kullanıcıların.</div>
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
          <div class="text-muted small">Giriş yapabilen kullanıcılar.</div>
          <div class="mt-3 small text-muted">
            Pasif: <b><?= esc($passiveUsers ?? 0) ?></b>
          </div>
        </div>
      </div>
    </div>

    <div class="col-lg-4">
      <div class="card border-0 shadow-sm h-100" style="border-radius:16px;">
        <div class="card-body">
          <div class="text-muted small text-uppercase" style="letter-spacing:.08em;">Planlı Paylaşımlar</div>
          <div class="d-flex align-items-end justify-content-between">
            <div class="display-6 mb-1"><?= esc($upcomingCount ?? 0) ?></div>
            <div class="text-muted small mb-2">Yaklaşan</div>
          </div>
          <div class="text-muted small">
            Alt kullanıcılarının yaklaşan planlı paylaşımları.
          </div>
        </div>
      </div>
    </div>
  </div>

  <!-- ALT: Dinamik dashboard -->
  <div class="row g-3">
    <!-- SOL: Yaklaşan paylaşımlar listesi -->
    <div class="col-lg-8">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <div class="d-flex align-items-start justify-content-between">
            <div>
              <h5 class="mb-1">Yaklaşan Paylaşımlar</h5>
              <div class="text-muted small">Alt kullanıcıların en yakın planlı paylaşımları.</div>
            </div>
            <span class="badge rounded-pill text-bg-light border" style="height:fit-content;">
              Tenant: <?= esc(session('tenant_id') ?? '-') ?>
            </span>
          </div>

          <div class="mt-3">
            <?php if (!empty($upcomingPosts)): ?>
              <div class="table-responsive">
                <table class="table table-sm align-middle mb-0">
                  <thead class="small text-muted">
                    <tr>
                      <th>Kullanıcı</th>
                      <th>Platform</th>
                      <th>Planlanan</th>
                      <th class="text-end">Durum</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($upcomingPosts as $p): ?>
                      <?php
                        $status = strtolower((string)($p['status'] ?? 'scheduled'));
                        $badge  = 'text-bg-secondary';
                        if ($status === 'scheduled') $badge = 'text-bg-primary';
                        if ($status === 'queued')    $badge = 'text-bg-info';
                        if ($status === 'failed')    $badge = 'text-bg-danger';
                        if ($status === 'posted')    $badge = 'text-bg-success';
                      ?>
                      <tr>
                        <td>
                          <div class="fw-semibold"><?= esc($p['user_name'] ?? '-') ?></div>
                          <div class="text-muted small"><?= esc($p['user_email'] ?? '') ?></div>
                        </td>
                        <td class="small">
                          <span class="badge text-bg-light border"><?= esc($p['platform'] ?? '-') ?></span>
                        </td>
                        <td class="small"><?= esc($p['scheduled_at'] ?? '-') ?></td>
                        <td class="text-end">
                          <span class="badge <?= esc($badge) ?>">
                            <?= esc($p['status'] ?? 'scheduled') ?>
                          </span>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>

              <div class="mt-3 d-flex justify-content-end">
                <a href="<?= base_url('dealer/publishes') ?>" class="btn btn-sm btn-outline-secondary">
                  <i class="bi bi-list me-1"></i> Tüm Paylaşımları Gör
                </a>
              </div>
            <?php else: ?>
              <div class="p-4 rounded border bg-light">
                <div class="fw-semibold mb-1">Şu an yaklaşan planlı paylaşım yok.</div>
                <div class="text-muted small">
                  Alt kullanıcıların planlı paylaşım oluşturdukça burada listelenecek.
                </div>
              </div>
            <?php endif; ?>
          </div>

        </div>
      </div>
    </div>

    <!-- SAĞ: Dinamik özet + aksiyon -->
    <div class="col-lg-4">
      <div class="card border-0 shadow-sm" style="border-radius:16px;">
        <div class="card-body">
          <h6 class="mb-1">Özet</h6>
          <div class="text-muted small mb-3">Bayi hesabının takip edebileceği veriler.</div>

          <div class="row g-2">
            <div class="col-6">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small">Alt Kullanıcı</div>
                <div class="fw-bold fs-5"><?= esc($totalUsers ?? 0) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small">Yaklaşan</div>
                <div class="fw-bold fs-5"><?= esc($upcomingCount ?? 0) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small">Aktif</div>
                <div class="fw-bold fs-5"><?= esc($activeUsers ?? 0) ?></div>
              </div>
            </div>
            <div class="col-6">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small">Pasif</div>
                <div class="fw-bold fs-5"><?= esc($passiveUsers ?? 0) ?></div>
              </div>
            </div>
          </div>

          <hr class="my-3">

          <div class="d-grid gap-2">
            <a href="<?= base_url('dealer/users') ?>" class="btn btn-primary">
              <i class="bi bi-person-plus me-1"></i> Alt Kullanıcı Oluştur
            </a>
            <a href="<?= base_url('dealer/publishes') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-eye me-1"></i> Paylaşımları Takip Et
            </a>
          </div>

          <div class="mt-3 small text-muted">
            Not: Bayi yalnızca kendi alt kullanıcılarını ve onların paylaşımlarını görebilir.
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
