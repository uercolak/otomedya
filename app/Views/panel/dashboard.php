<?= $this->extend('layouts/panel') ?>

<?= $this->section('content') ?>

<div class="row g-3 mb-3">
    <div class="col-md-4">
        <div class="card-soft p-3 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-label mb-1">Bu Hafta Planlanan Gönderi</div>
                    <div class="metric-value"><?= (int)($plannedThisWeek ?? 0) ?></div>
                </div>
                <span class="metric-tag">
                    <i class="bi bi-calendar2-week me-1"></i>Takvim
                </span>
            </div>
            <div class="text-muted small">
                Takvim modülü gerçek veriye bağlandığında, Facebook / Instagram / YouTube / TikTok için bu haftaki toplam planlı gönderi sayısı burada görünecek.
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-soft p-3 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-label mb-1">Bağlı Sosyal Hesap</div>
                    <div class="metric-value"><?= (int)($accountsCount ?? 0) ?></div>
                </div>
                <span class="metric-tag">
                    <i class="bi bi-share me-1"></i>Hesaplar
                </span>
            </div>
            <div class="text-muted small">
                Meta, YouTube ve TikTok entegrasyonları tamamlandığında tüm bağlı sayfa/kanal adedi burada listelenecek.
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card-soft p-3 h-100">
            <div class="d-flex justify-content-between align-items-start mb-2">
                <div>
                    <div class="metric-label mb-1">Aktif Şablon</div>
                    <div class="metric-value"><?= (int)($templatesCount ?? 0) ?></div>
                </div>
                <span class="metric-tag">
                    <i class="bi bi-images me-1"></i>Şablon Merkezi
                </span>
            </div>
            <div class="text-muted small">
                Yönetici panelinden ekleyeceğiniz hazır şablonların toplam sayısı burada gösterilecek.
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card-soft p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="metric-label">Takvim Önizleme</div>
                <span class="metric-tag">
                    <i class="bi bi-stars me-1"></i> Yakında
                </span>
            </div>
            <div class="pseudo-chart mb-2"></div>
            <div class="text-muted small">
                Buraya FullCalendar veya benzeri bir takvim bileşeni gelecek. Kullanıcılar planladıkları tüm gönderileri
                ay / hafta görünümünde görebilecek, sürükle-bırak ile tarih & saat değiştirebilecek.
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card-soft p-3 h-100">
            <div class="d-flex justify-content-between align-items-center mb-2">
                <div class="metric-label">Son İşlemler</div>
                <span class="metric-tag">
                    <i class="bi bi-activity me-1"></i> Log
                </span>
            </div>
            <table class="table table-sm table-borderless table-activity mb-1">
                <tbody>
                    <tr>
                        <td width="70%">
                            <div class="text-muted small">Henüz yayınlanmış gönderi yok.</div>
                        </td>
                        <td class="text-end"><span class="pill-status pill-draft">Geliştirme</span></td>
                    </tr>
                    <tr>
                        <td>
                            <div class="text-muted small">Log tablosu bağlandığında başarısız / başarılı paylaşım kayıtları burada listelenecek.</div>
                        </td>
                        <td class="text-end"><span class="pill-status pill-scheduled">Yakında</span></td>
                    </tr>
                </tbody>
            </table>
            <div class="text-muted small">
                Hata alan gönderiler için buradan hızlı aksiyon alabileceksiniz.
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
