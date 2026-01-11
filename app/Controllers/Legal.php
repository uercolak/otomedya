<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Legal extends BaseController
{
    public function privacy()
    {
        return view('legal/privacy');
    }

    public function terms()
    {
        return view('legal/terms');
    }

    public function dataDeletion()
    {
        return view('legal/data_deletion');
    }
}
