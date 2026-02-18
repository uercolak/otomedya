<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
 public function index()
    {
        if ($r = $this->ensureDealer()) return $r;

        $dealerId = (int) session('user_id');      // bayi user id
        $tenantId = (int) (session('tenant_id') ?? 0);

        $db = \Config\Database::connect();

        // Alt kullanıcılar: created_by = bayi
        $usersBase = $db->table('users')
            ->where('created_by', $dealerId);

        if ($tenantId) {
            $usersBase->where('tenant_id', $tenantId);
        }

        // Not: alt kullanıcıların rolü "user" ise filtre ekleyelim
        // (dealer hesabı users tablosundaysa karışmasın diye önerilir)
        $usersBase->where('role', 'user');

        $totalUsers  = (clone $usersBase)->countAllResults();
        $activeUsers = (clone $usersBase)->where('status', 'active')->countAllResults();
        $passiveUsers= (clone $usersBase)->where('status', 'passive')->countAllResults();

        // Alt kullanıcı ID listesi
        $userIds = (clone $usersBase)->select('id')->get()->getResultArray();
        $userIds = array_map(fn($r)=> (int)$r['id'], $userIds);

        // Publish özetleri
        $now = date('Y-m-d H:i:s');
        $in7 = date('Y-m-d H:i:s', strtotime('+7 days'));
        $last7 = date('Y-m-d H:i:s', strtotime('-7 days'));

        $plannedCount = 0;
        $todayPlanned = 0;
        $failed7dCount = 0;
        $recentPublishes = [];

        if (!empty($userIds)) {

            // 7 gün içinde planlı (queued/scheduled) paylaşım
            $plannedCount = $db->table('publishes')
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['queued','scheduled'])
                ->where('schedule_at >=', $now)
                ->where('schedule_at <=', $in7)
                ->countAllResults();

            // Bugün planlı
            $startToday = date('Y-m-d 00:00:00');
            $endToday   = date('Y-m-d 23:59:59');

            $todayPlanned = $db->table('publishes')
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['queued','scheduled'])
                ->where('schedule_at >=', $startToday)
                ->where('schedule_at <=', $endToday)
                ->countAllResults();

            // Son 7 gün hata (failed/error)
            $failed7dCount = $db->table('publishes')
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['failed','error'])
                ->where('created_at >=', $last7)
                ->countAllResults();

            // Son 8 publish (bayinin alt kullanıcıları)
            $recentPublishes = $db->table('publishes p')
                ->select('p.id, p.platform, p.status, p.schedule_at, p.published_at, u.name as user_name, u.email as user_email')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->whereIn('p.user_id', $userIds)
                ->orderBy('p.id', 'DESC')
                ->limit(8)
                ->get()->getResultArray();
        }

        return view('dealer/dashboard', [
            'pageTitle'        => 'Gösterge Paneli',

            'totalUsers'       => $totalUsers,
            'activeUsers'      => $activeUsers,
            'passiveUsers'     => $passiveUsers,

            'plannedCount'     => $plannedCount,
            'todayPlanned'     => $todayPlanned,
            'failed7dCount'    => $failed7dCount,
            'recentPublishes'  => $recentPublishes,
        ]);
    }
}