<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if ($r = $this->ensureDealer()) return $r;

        $dealerId = (int) session('user_id');
        $tenantId = (int) (session('tenant_id') ?? 0);

        $userModel = new UserModel();
        $db = \Config\Database::connect();

        // Alt kullanıcılar: created_by = bayi
        $base = $userModel->where('tenant_id', $tenantId)
                          ->where('role', 'user')
                          ->where('created_by', $dealerId);

        $totalUsers   = (clone $base)->countAllResults();
        $activeUsers  = (clone $base)->where('status', 'active')->countAllResults();
        $passiveUsers = (clone $base)->where('status', 'passive')->countAllResults();

        // Alt kullanıcı id listesi (subquery)
        $subUserIds = $db->table('users')
            ->select('id')
            ->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('created_by', $dealerId);

        $now = date('Y-m-d H:i:s');
        $plus7 = date('Y-m-d H:i:s', time() + 7 * 86400);
        $minus7 = date('Y-m-d H:i:s', time() - 7 * 86400);

        // 7 günlük planlı paylaşımlar (status isimlerin sende nasıl: queued/scheduled vs)
        $plannedCount = $db->table('publishes')
            ->whereIn('user_id', $subUserIds)
            ->where('schedule_at >=', $now)
            ->where('schedule_at <=', $plus7)
            ->whereIn('status', ['queued', 'scheduled', 'running'])
            ->countAllResults();

        // Son 7 günde hata
        $failed7dCount = $db->table('publishes')
            ->whereIn('user_id', $subUserIds)
            ->whereIn('status', ['failed', 'error'])
            ->where('updated_at >=', $minus7) // yoksa published_at veya created_at kullan
            ->countAllResults();

        // Son paylaşımlar (dealer panel tablosu)
        $recentPublishes = $db->table('publishes p')
            ->select('p.id, p.platform, p.status, p.schedule_at, p.published_at, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->whereIn('p.user_id', $subUserIds)
            ->orderBy('p.id', 'DESC')
            ->limit(8)
            ->get()->getResultArray();

        return view('dealer/dashboard', [
            'pageTitle'       => 'Gösterge Paneli',
            'pageSubtitle'    => 'Alt kullanıcıların ve işlemlerin özeti.',
            'totalUsers'      => $totalUsers,
            'activeUsers'     => $activeUsers,
            'passiveUsers'    => $passiveUsers,
            'plannedCount'    => $plannedCount,
            'failed7dCount'   => $failed7dCount,
            'recentPublishes' => $recentPublishes,
        ]);
    }
}
