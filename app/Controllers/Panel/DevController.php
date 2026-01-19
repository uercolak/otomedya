<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class DevController extends BaseController
{
    public function testTikTokRefresh()
    {
        service('queue')->push('refresh_tiktok_token', [
            'social_account_id' => 50,
        ]);

        return $this->response->setBody('refresh job queued: social_account_id=50');
    }
}
