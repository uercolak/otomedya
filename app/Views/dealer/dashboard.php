<?= $this->extend('layouts/dealer') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-3">

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
          <div class="display-6 mb-1">Yakında</div>
          <div class="text-muted small">Alt kullanıcıların planladığı paylaşımlar burada özetlenecek.</div>
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
              <h5 class="mb-1">Genel Durum</h5>
              <div class="text-muted small">Bayi paneli sadece alt kullanıcı yönetimi ve takip içindir.</div>
            </div>
            <span class="badge rounded-pill text-bg-light border" style="height:fit-content;">
              Tenant: <?= esc(session('tenant_id') ?? '-') ?>
            </span>
          </div>

          <div class="row g-3 mt-1">
            <div class="col-md-4">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small mb-1">Kullanıcı Yönetimi</div>
                <div class="fw-semibold">Hazır</div>
                <div class="text-muted small">Kullanıcı ekle / pasif yap / detay gör.</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small mb-1">Paylaşım Takibi</div>
                <div class="fw-semibold">Yakında</div>
                <div class="text-muted small">Alt kullanıcı paylaşımları ve tekrar deneme.</div>
              </div>
            </div>
            <div class="col-md-4">
              <div class="p-3 rounded border bg-white">
                <div class="text-muted small mb-1">Şablonlar</div>
                <div class="fw-semibold">Root Kontrolünde</div>
                <div class="text-muted small">Bayi şablon ekleyemez/düzenleyemez.</div>
              </div>
            </div>
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
            <a href="<?= base_url('panel') ?>" class="btn btn-outline-secondary">
              <i class="bi bi-box-arrow-in-right me-1"></i> Kendi Panelime Git
            </a>
          </div>

          <div class="mt-3 small text-muted">
            Not: Bayi paneli sistem ayarlarına ve şablonlara erişemez.
          </div>
        </div>
      </div>
    </div>
  </div>

</div>

<?= $this->endSection() ?>
