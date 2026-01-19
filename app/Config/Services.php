<?php

namespace Config;

use CodeIgniter\Config\BaseService;


class Services extends BaseService
{
    public static function queue(bool $getShared = true)
    {
        if ($getShared) {
            return static::getSharedInstance('queue');
        }

        // Queue config'i al (Config\Queue.php)
        $config = config('Queue');

        return new \App\Services\QueueService($config);
    }
}
