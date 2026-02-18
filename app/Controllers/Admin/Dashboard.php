<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $totalUsers = (int) $db->table('users')->countAllResults();

        $rootCount = (int) $db->table('users')->where('role', 'root')->countAllResults();

        $dealerCount = (int) $db->table('users')->where('role', 'dealer')->countAllResults();

        $activeUserCount = (int) $db->table('users')
            ->where('role', 'user')
            ->where('status', 'active')
            ->countAllResults();

        $connectedAccountsCount = (int) $db->table('social_accounts')->countAllResults();

        $connectedAccounts = $db->table('social_accounts sa')
            ->select('sa.id, sa.platform, sa.username, sa.created_at')
            ->orderBy('sa.id', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        $startOfWeek = (new \DateTime('monday this week'))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $endOfWeek   = (new \DateTime('sunday this week'))->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $thisWeekPlannedCount = (int) $db->table('publishes p')
            ->where('p.schedule_at >=', $startOfWeek)
            ->where('p.schedule_at <=', $endOfWeek)
            ->countAllResults();

        $upcomingPublishes = $db->table('publishes p')
            ->select('p.id, p.platform, p.status, p.schedule_at, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->where('p.schedule_at IS NOT NULL', null, false)
            ->where('p.schedule_at >=', date('Y-m-d H:i:s'))
            ->orderBy('p.schedule_at', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        $activeTemplatesCount = (int) $db->table('templates')->where('is_active', 1)->countAllResults();

        $activeTemplates = $db->table('templates t')
            ->select('t.id, t.name, t.platform_type, t.type, t.base_media_id, t.thumb_path')
            ->where('t.is_active', 1)
            ->orderBy('t.id', 'DESC')
            ->limit(3)
            ->get()->getResultArray();

        $monthParam = $this->request->getGet('month');
        if (!$monthParam) $monthParam = date('Y-m');

        $monthStart = \DateTime::createFromFormat('Y-m-d H:i:s', $monthParam . '-01 00:00:00');
        if (!$monthStart) $monthStart = new \DateTime(date('Y-m-01 00:00:00'));

        $monthEnd = (clone $monthStart)->modify('last day of this month')->setTime(23, 59, 59);

        $rowsPlanned = $db->table('publishes p')
            ->select("DATE(p.schedule_at) as d, COUNT(*) as c", false)
            ->where('p.schedule_at >=', $monthStart->format('Y-m-d H:i:s'))
            ->where('p.schedule_at <=', $monthEnd->format('Y-m-d H:i:s'))
            ->groupBy('d')
            ->get()->getResultArray();

        $rowsPublished = $db->table('publishes p')
            ->select("DATE(p.published_at) as d, COUNT(*) as c", false)
            ->where('p.published_at IS NOT NULL', null, false)
            ->where('p.published_at >=', $monthStart->format('Y-m-d H:i:s'))
            ->where('p.published_at <=', $monthEnd->format('Y-m-d H:i:s'))
            ->groupBy('d')
            ->get()->getResultArray();

        $plannedByDay = [];
        foreach ($rowsPlanned as $r) $plannedByDay[$r['d']] = (int)$r['c'];

        $publishedByDay = [];
        foreach ($rowsPublished as $r) $publishedByDay[$r['d']] = (int)$r['c'];

        $recentActivities = $db->table('publishes p')
            ->select('p.id, p.platform, p.status, p.created_at, p.published_at, p.schedule_at, u.name as user_name, u.email as user_email')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->orderBy('p.id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('admin/dashboard', [
            'pageTitle'   => 'Yönetici Paneli',
            'pageSubtitle'=> 'Planlı gönderiler, bağlı hesaplar ve son işlemlerin özeti.',

            'totalUsers'          => $totalUsers,
            'rootCount'           => $rootCount,
            'dealerCount'         => $dealerCount,
            'activeUserCount'     => $activeUserCount,

            'connectedAccountsCount' => $connectedAccountsCount,
            'connectedAccounts'      => $connectedAccounts,

            'thisWeekPlannedCount' => $thisWeekPlannedCount,
            'upcomingPublishes'     => $upcomingPublishes,

            'activeTemplatesCount'  => $activeTemplatesCount,
            'activeTemplates'       => $activeTemplates,

            'monthParam'     => $monthStart->format('Y-m'),
            'monthStart'     => $monthStart,
            'plannedByDay'   => $plannedByDay,
            'publishedByDay' => $publishedByDay,

            'recentActivities' => $recentActivities,
        ]);
    }
}
