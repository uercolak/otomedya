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

        // Alt kullanıcı ID listesi
        $subUserRows = $db->table('users')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('created_by', $dealerId)
            ->get()->getResultArray();

        $subUserIds = array_map('intval', array_column($subUserRows, 'id'));

        // Planlı Paylaşımlar (yaklaşan)
        $upcomingPosts = [];
        $upcomingCount = 0;

        if (!empty($subUserIds)) {
            $now = date('Y-m-d H:i:s');

            // scheduled_posts kolon adı: scheduled_at ✅
            $upcomingPosts = $db->table('scheduled_posts sp')
                ->select('sp.id, sp.scheduled_at, sp.status, sp.platform, u.name as user_name, u.email as user_email')
                ->join('users u', 'u.id = sp.publish_id', 'left') // publish_id kullanıcı id'si ise
                // publish_id user_id değilse, aşağıdaki satırı düzelt:
                // ->join('users u', 'u.id = sp.user_id', 'left')
                ->whereIn('sp.publish_id', $subUserIds) // publish_id kullanıcı id'si ise
                // publish_id user_id değilse, bunu değiştir:
                // ->whereIn('sp.user_id', $subUserIds)
                ->where('sp.scheduled_at >=', $now)
                ->orderBy('sp.scheduled_at', 'ASC')
                ->limit(10)
                ->get()->getResultArray();

            $upcomingCount = $db->table('scheduled_posts sp')
                ->whereIn('sp.publish_id', $subUserIds)
                ->where('sp.scheduled_at >=', $now)
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
