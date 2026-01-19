<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class DevController extends BaseController
{
    public function testTikTokRefresh()
    {
        try {
            service('queue')->push('refresh_tiktok_token', [
                'social_account_id' => 50,
            ]);

            return $this->response->setBody('refresh job queued: social_account_id=50');
        } catch (\Throwable $e) {
            log_message('error', 'testTikTokRefresh error: {msg}', ['msg' => $e->getMessage()]);
            return $this->response
                ->setStatusCode(500)
                ->setBody("ERROR: " . $e->getMessage());
        }
    }
}
