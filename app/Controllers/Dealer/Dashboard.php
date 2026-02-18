<?php

namespace App\Controllers\Dealer;

use App\Controllers\BaseController;
use App\Models\UserModel;

class Dashboard extends BaseController
{
    public function index()
    {
        if ($r = $this->ensureDealer()) return $r;

        $dealerId = (int) session('user_id');     // bayi user id
        $tenantId = (int) (session('tenant_id') ?? 0);

        $db = \Config\Database::connect();

        $usersQ = $db->table('users')
            ->where('created_by', $dealerId);

        if ($tenantId) {
            $usersQ->where('tenant_id', $tenantId);
        }

        $totalUsers   = (clone $usersQ)->countAllResults();
        $activeUsers  = (clone $usersQ)->where('status', 'active')->countAllResults();  // status alanın "active/passive" değilse aşağıyı değiştir
        $passiveUsers = (clone $usersQ)->where('status !=', 'active')->countAllResults();

        $userIds = (clone $usersQ)->select('id')->get()->getResultArray();
        $userIds = array_map(fn($r)=> (int)$r['id'], $userIds);

        $now = date('Y-m-d H:i:s');
        $in7 = date('Y-m-d H:i:s', strtotime('+7 days'));

        $plannedCount = 0;
        $failed7dCount = 0;
        $recentPublishes = [];
        $recentJobs = [];

        if (!empty($userIds)) {

            $plannedCount = $db->table('publishes')
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['queued','scheduled'])
                ->where('schedule_at >=', $now)
                ->where('schedule_at <=', $in7)
                ->countAllResults();

            $failed7dCount = $db->table('publishes')
                ->whereIn('user_id', $userIds)
                ->whereIn('status', ['failed','error'])
                ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults();

            $recentPublishes = $db->table('publishes p')
                ->select('p.id, p.platform, p.status, p.schedule_at, p.published_at, u.name as user_name, u.email as user_email')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->whereIn('p.user_id', $userIds)
                ->orderBy('p.id', 'DESC')
                ->limit(6)
                ->get()->getResultArray();

            $recentJobs = $db->table('jobs j')
                ->select('j.id, j.status, j.type, j.run_at, j.payload_json')
                ->orderBy('j.id', 'DESC')
                ->limit(6)
                ->get()->getResultArray();
        }


        return view('dealer/dashboard', [
            'pageTitle'    => 'Gösterge Paneli',
            'pageSubtitle' => 'Alt kullanıcıların ve işlemlerin özeti.',
            'totalUsers'   => $totalUsers,
            'activeUsers'  => $activeUsers,
            'passiveUsers' => $passiveUsers,
            'plannedCount'     => $plannedCount,
            'failed7dCount'    => $failed7dCount,
            'recentPublishes'  => $recentPublishes,
            'recentJobs'       => $recentJobs,
        ]);
    }
}
