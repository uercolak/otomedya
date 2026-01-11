<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Catalog extends BaseConfig
{
    public array $jobTypeLabels = [
        'publish_post' => 'Gönderiyi Yayınla',
    ];

    public array $jobStatusLabels = [
        'pending'    => 'Bekliyor',
        'processing' => 'İşleniyor',
        'done'       => 'Tamamlandı',
        'failed'     => 'Başarısız',
    ];

    public array $jobStatusExplain = [
        'pending'    => 'Bu iş sırasını bekliyor. Planlanan zamanda otomatik çalışacak.',
        'processing' => 'Bu iş şu anda arka planda çalışıyor.',
        'done'       => 'Bu iş başarıyla tamamlandı.',
        'failed'     => 'Bu işte hata oluştu. Detaylar aşağıda yer alır.',
    ];

    public array $logLevelLabels = [
        'info'    => 'Bilgi',
        'warning' => 'Uyarı',
        'error'   => 'Hata',
    ];

    public array $logLevelHints = [
        'info'    => 'Bilgi amaçlı kayıt',
        'warning' => 'Dikkat edilmesi gereken durum',
        'error'   => 'Hata oluştu',
    ];

    public array $logChannelLabels = [
        'queue'       => 'Kuyruk',
        'auth'        => 'Giriş/Yetki',
        'admin'       => 'Yönetim',
        'integration' => 'Entegrasyon',
        'content'     => 'İçerik',
        'schedule'    => 'Planlama',
        'user'        => 'Kullanıcı',
    ];

    public array $logChannelHints = [
        'queue'       => 'Planlanan işler ve arka plan işlemleri',
        'auth'        => 'Giriş/izin işlemleri',
        'admin'       => 'Yönetim paneli işlemleri',
        'integration' => 'Sosyal medya / entegrasyon bağlantıları',
        'content'     => 'İçerik oluşturma ve düzenleme',
        'schedule'    => 'Planlama / takvim işlemleri',
        'user'        => 'Kullanıcı işlemleri',
    ];

    public array $logEventLabels = [
    'job.reserved'  => 'İş kuyruğa alındı',
    'job.succeeded' => 'İş başarıyla tamamlandı',
    'job.failed'    => 'İş başarısız oldu',

    'publish.started'   => 'Paylaşım başlatıldı',
    'publish.succeeded' => 'Paylaşım yapıldı',
    'publish.failed'    => 'Paylaşım başarısız',
];
}
