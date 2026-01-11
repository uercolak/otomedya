<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Queue extends BaseConfig
{
    public array $handlers = [
        'publish_post' => \App\Queue\Handlers\PublishPostHandler::class,
    ];
}
