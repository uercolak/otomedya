<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Queue extends BaseConfig
{
    public array $handlers = [
        'publish_post' => \App\Queue\Handlers\PublishPostHandler::class,
        'publish_youtube' => \App\Queue\Handlers\PublishYouTubeHandler::class,
        'refresh_tiktok_token' => \App\Queue\Handlers\RefreshTikTokTokenHandler::class,
    ];
}
