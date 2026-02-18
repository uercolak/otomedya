<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $tenantId = (int) session('tenant_id');
        $userModel = new UserModel();

        $totalUsers = $userModel->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->countAllResults();

        $activeUsers = $userModel->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('status', 'active')
            ->countAllResults();

        $passiveUsers = $userModel->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('status', 'passive')
            ->countAllResults();

        return view('dealer/dashboard', [
            'pageTitle'    => 'Gösterge Paneli',
            'pageSubtitle' => 'Alt kullanıcıların ve işlemlerin özeti.',
            'totalUsers'   => $totalUsers,
            'activeUsers'  => $activeUsers,
            'passiveUsers' => $passiveUsers,
        ]);
    }
}
