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

        return new \App\Services\QueueService();
    }
}
