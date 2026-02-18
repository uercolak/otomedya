<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
public function index()
    {
        if ($r = $this->ensureDealer()) return $r;

        $dealerId = (int) session('user_id');          // bayi user id
        $tenantId = (int) (session('tenant_id') ?? 0);

        $userModel = new UserModel();

        // sadece bayinin oluşturduğu alt kullanıcılar
        $base = $userModel->where('tenant_id', $tenantId)
                          ->where('role', 'user')
                          ->where('created_by', $dealerId);

        $totalUsers = (clone $base)->countAllResults();

        $activeUsers = (clone $base)->where('status', 'active')->countAllResults();
        $passiveUsers = (clone $base)->where('status', 'passive')->countAllResults();

        return view('dealer/dashboard', [
            'pageTitle'    => 'Gösterge Paneli',
            'pageSubtitle' => 'Alt kullanıcıların ve işlemlerin özeti.',
            'totalUsers'   => $totalUsers,
            'activeUsers'  => $activeUsers,
            'passiveUsers' => $passiveUsers,
        ]);
    }
}