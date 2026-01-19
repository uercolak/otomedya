<?php

namespace App\Controllers;

class Legal extends BaseController
{
    public function privacy()
    {
        return view('legal/privacy', [
            'pageTitle' => 'Gizlilik Politikası',
        ]);
    }

    public function terms()
    {
        return view('legal/terms', [
            'pageTitle' => 'Kullanım Şartları',
        ]);
    }

    public function dataDeletion()
    {
        return view('legal/data_deletion', [
            'pageTitle' => 'Veri Silme Politikası',
        ]);
    }
}
