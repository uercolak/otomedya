<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    public function index()
    {
        // Basit istatistikler
        $totalUsers   = $this->userModel->countAllResults();
        $admins       = $this->userModel->where('role', 'admin')->countAllResults();
        $normalUsers  = $this->userModel->where('role', 'user')->countAllResults();

        $data = [
            'pageTitle'       => 'Admin Panel',
            'pageSubtitle' => 'Kullanıcılar, loglar ve sistem ayarları.',
            'totalUsers'  => $totalUsers,
            'admins'      => $admins,
            'normalUsers' => $normalUsers,
        ];

        return view('admin/dashboard', $data);
    }
}
