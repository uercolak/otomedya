<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class JobCatalog extends BaseConfig
{
    public array $typeLabels = [
        'publish_post' => 'Gönderiyi Yayınla',
        // 'sync_account' => 'Hesabı Senkronize Et',
    ];

    public array $statusLabels = [
        'pending'    => 'Bekliyor',
        'processing' => 'İşleniyor',
        'done'       => 'Tamamlandı',
        'failed'     => 'Başarısız',
    ];
}
