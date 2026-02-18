<?= $this->extend('layouts/admin') ?>
<?= $this->section('content') ?>

<div class="row g-3 mb-3">
    <!-- Toplam Kullanıcı -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">
                            Bu Hafta Toplam Kullanıcı
                        </div>
                        <div class="display-6 mb-1"><?= esc($totalUsers) ?></div>
                        <div class="text-muted small">
                            Toplam kayıtlı kullanıcı sayısı.
                        </div>
                    </div>
                    <a href="<?= base_url('admin/users') ?>" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-people me-1"></i> Kullanıcılar
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Admin Sayısı -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">
                            Admin Sayısı
                        </div>
                        <div class="display-6 mb-1"><?= esc($admins) ?></div>
                        <div class="text-muted small">
                            Yönetim yetkisi olan kullanıcılar.
                        </div>
                    </div>
                    <span class="badge rounded-pill text-bg-danger" style="height: fit-content;">
                        <i class="bi bi-shield-lock me-1"></i> Admin
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Normal Kullanıcı -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between">
                    <div>
                        <div class="text-muted small mb-1 text-uppercase" style="letter-spacing:.08em;">
                            Normal Kullanıcı
                        </div>
                        <div class="display-6 mb-1"><?= esc($normalUsers) ?></div>
                        <div class="text-muted small">
                            Paneli kullanan standart hesaplar.
                        </div>
                    </div>
                    <span class="badge rounded-pill text-bg-secondary" style="height: fit-content;">
                        <i class="bi bi-person me-1"></i> User
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Genel Durum -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h5 class="mb-1">Genel Durum</h5>
                        <div class="text-muted small">
                            Sistem yönetimi için özet ekran. Burayı zamanla “gerçek veriler” ile dolduracağız.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="<?= base_url('admin/users/new') ?>" class="btn btn-sm btn-primary">
                            <i class="bi bi-plus-lg me-1"></i> Yeni Kullanıcı
                        </a>
                        <a href="<?= base_url('admin/users') ?>" class="btn btn-sm btn-outline-secondary">
                            Yönet
                        </a>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-md-4">
                        <div class="p-3 rounded border bg-white">
                            <div class="text-muted small mb-1">Plan / Limit</div>
                            <div class="fw-semibold">Yakında</div>
                            <div class="text-muted small">Paket ve limit yönetimi eklenecek.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border bg-white">
                            <div class="text-muted small mb-1">Log Kayıtları</div>
                            <div class="fw-semibold">Yakında</div>
                            <div class="text-muted small">Başarılı/başarısız işlem takibi.</div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="p-3 rounded border bg-white">
                            <div class="text-muted small mb-1">Entegrasyonlar</div>
                            <div class="fw-semibold">Yakında</div>
                            <div class="text-muted small">Sosyal hesap bağlantıları.</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Son İşlemler / Kısayollar -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <div>
                        <h6 class="mb-1">Son İşlemler</h6>
                        <div class="text-muted small">Şimdilik boş. Log sistemi bağlanınca dolacak.</div>
                    </div>
                    <span class="badge rounded-pill text-bg-light border">
                        <i class="bi bi-activity me-1"></i> Log
                    </span>
                </div>

                <div class="p-3 rounded border bg-white mb-3">
                    <div class="text-muted small mb-1">Durum</div>
                    <div class="fw-semibold">Henüz işlem yok</div>
                    <div class="text-muted small">Yeni kullanıcı oluşturduğunda burada gösterilebilir.</div>
                </div>

                <div class="d-grid gap-2">
                    <a href="<?= base_url('admin/users') ?>" class="btn btn-outline-primary">
                        <i class="bi bi-people me-1"></i> Kullanıcıları Yönet
                    </a>
                    <a href="<?= base_url('panel') ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Kullanıcı Paneline Git
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
