<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class TikTok extends BaseConfig
{
    public string $clientKey;
    public string $clientSecret;
    public string $redirectUri;

    public function __construct()
    {
        $this->clientKey    = env('TIKTOK_CLIENT_KEY');
        $this->clientSecret = env('TIKTOK_CLIENT_SECRET');
        $this->redirectUri  = env('TIKTOK_REDIRECT_URI');
    }

    public array $scopes = [
        'user.info.basic',
        'user.info.profile',
        'user.info.stats',
        'video.upload',
        'video.publish',
    ];
}
