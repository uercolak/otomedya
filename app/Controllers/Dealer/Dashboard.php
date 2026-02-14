<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        return view('dealer/dashboard');
    }
}
