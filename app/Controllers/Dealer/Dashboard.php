<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        $tenantId = (int) session('tenant_id');
        $dealerId = (int) session('user_id'); // bayi hesabının id'si

        $userModel = new UserModel();
        $db = \Config\Database::connect();

        // Bayinin alt kullanıcıları: created_by = bayi_id
        $baseUsers = $userModel->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('created_by', $dealerId);

        $totalUsers = (clone $baseUsers)->countAllResults();

        $activeUsers = (clone $baseUsers)
            ->where('status', 'active')
            ->countAllResults();

        $passiveUsers = (clone $baseUsers)
            ->where('status', 'passive')
            ->countAllResults();

        // Alt kullanıcı ID listesi (planlı paylaşımları çekmek için)
        $subUserRows = $db->table('users')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('created_by', $dealerId)
            ->get()->getResultArray();

        $subUserIds = array_map('intval', array_column($subUserRows, 'id'));

        // Planlı Paylaşımlar (yaklaşan ilk 10)
        $upcomingPosts = [];
        $upcomingCount = 0;

        if (!empty($subUserIds)) {
            // scheduled_posts: kolon adların farklıysa burayı güncelle
            // Örn: schedule_at yerine publish_at / scheduled_for vs.
            $upcomingPosts = $db->table('scheduled_posts sp')
                ->select('sp.id, sp.schedule_at, sp.status, u.name as user_name, u.email as user_email')
                ->join('users u', 'u.id = sp.user_id', 'left')
                ->where('sp.tenant_id', $tenantId)
                ->whereIn('sp.user_id', $subUserIds)
                ->where('sp.schedule_at >=', date('Y-m-d H:i:s'))
                ->orderBy('sp.schedule_at', 'ASC')
                ->limit(10)
                ->get()->getResultArray();

            $upcomingCount = $db->table('scheduled_posts sp')
                ->where('sp.tenant_id', $tenantId)
                ->whereIn('sp.user_id', $subUserIds)
                ->where('sp.schedule_at >=', date('Y-m-d H:i:s'))
                ->countAllResults();
        }

        return view('dealer/dashboard', [
            'pageTitle'     => 'Gösterge Paneli',
            'pageSubtitle'  => 'Alt kullanıcıların ve işlemlerin özeti.',
            'totalUsers'    => $totalUsers,
            'activeUsers'   => $activeUsers,
            'passiveUsers'  => $passiveUsers,
            'upcomingPosts' => $upcomingPosts,
            'upcomingCount' => $upcomingCount,
        ]);
    }
}
