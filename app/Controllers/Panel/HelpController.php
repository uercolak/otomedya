<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\SocialAccountModel;

class HelpController extends BaseController
{
    public function accountLinking()
    {
        $userId = (int) session('user_id');

        $saModel = new SocialAccountModel();
        $accounts = $saModel->where('user_id', $userId)->findAll();

        $fb = null; $ig = null;
        foreach ($accounts as $a) {
            if (($a['platform'] ?? '') === 'facebook')  $fb = $a;
            if (($a['platform'] ?? '') === 'instagram') $ig = $a;
        }

        $hasFb = !empty($fb);
        $hasIg = !empty($ig);

        $igLinkedToPage = $hasIg && !empty($ig['meta_page_id']) && $hasFb && !empty($fb['external_id'])
            && (string)$ig['meta_page_id'] === (string)$fb['external_id'];

        $canVerifyIgType = false;

        $stats = [
            'fb_count' => $hasFb ? 1 : 0,
            'ig_count' => $hasIg ? 1 : 0,
        ];

        $checks = [
            'hasFbPage'       => $hasFb,
            'hasIgConnected'  => $hasIg,
            'igLinkedToPage'  => $igLinkedToPage,

            'isFbAdmin'       => null,   
            'isIgBusiness'    => null,  
        ];

        return view('panel/help/account_linking', [
            'title'   => 'Yardım / Hesap Bağlama Rehberi',
            'stats'   => $stats,
            'checks'  => $checks,
        ]);
    }
}
