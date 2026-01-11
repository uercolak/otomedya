<?php namespace Config;

use CodeIgniter\Config\BaseConfig;

class LogCatalog extends BaseConfig
{
    public array $events = [
        'job.reserved'   => 'İş kuyruğa alındı',
        'job.succeeded'  => 'İş başarıyla tamamlandı',
        'job.failed'     => 'İş başarısız oldu',
    ];
}
