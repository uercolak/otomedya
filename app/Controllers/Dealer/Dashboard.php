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

        // Sadece bayinin oluşturduğu alt kullanıcılar
        $base = $userModel->where('tenant_id', $tenantId)
                          ->where('role', 'user')
                          ->where('created_by', $dealerId);

        $totalUsers   = (clone $base)->countAllResults();
        $activeUsers  = (clone $base)->where('status', 'active')->countAllResults();
        $passiveUsers = (clone $base)->where('status', 'passive')->countAllResults();

        // Alt kullanıcı id’leri (publishes/jobs filtrelemek için)
        $childIds = $userModel->select('id')
            ->where('tenant_id', $tenantId)
            ->where('role', 'user')
            ->where('created_by', $dealerId)
            ->findColumn('id') ?? [];

        $plannedCount = 0;
        $failed7dCount = 0;
        $recentPublishes = [];

        // Publishes tablon varsa (senin projede var diye ilerliyorum)
        if (!empty($childIds)) {
            $db = \Config\Database::connect();

            // 7 gün içinde planlı (queued/scheduled) sayısı
            $plannedCount = (int) $db->table('publishes')
                ->whereIn('user_id', $childIds)
                ->whereIn('status', ['queued','scheduled'])
                ->where('schedule_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults();

            // 7 gün içinde failed sayısı
            $failed7dCount = (int) $db->table('publishes')
                ->whereIn('user_id', $childIds)
                ->whereIn('status', ['failed','error'])
                ->where('updated_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
                ->countAllResults();

            // Son 10 paylaşım (kullanıcı adı/email ile)
            $recentPublishes = $db->table('publishes p')
                ->select('p.id, p.platform, p.status, p.schedule_at, p.published_at, u.name as user_name, u.email as user_email')
                ->join('users u', 'u.id = p.user_id', 'left')
                ->whereIn('p.user_id', $childIds)
                ->orderBy('p.id', 'DESC')
                ->limit(10)
                ->get()
                ->getResultArray();
        }

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
