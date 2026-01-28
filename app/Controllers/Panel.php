<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Panel extends BaseController
{
    protected function ensureLoggedIn()
    {
        if (! session('is_logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }
        return null;
    }

    public function index()
    {
        if ($redirect = $this->ensureLoggedIn()) return $redirect;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        // Bu haftanın başlangıç / bitişi (Pzt->Paz)
        $start = (new \DateTime('monday this week'))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $end   = (new \DateTime('sunday this week'))->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        $plannedThisWeek = $db->table('publishes')
            ->where('user_id', $userId)
            ->whereIn('status', ['queued', 'scheduled'])
            ->where('schedule_at >=', $start)
            ->where('schedule_at <=', $end)
            ->countAllResults();

        $accountsCount = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->countAllResults();

        $templatesCount = 0;
        if ($db->tableExists('templates')) {
            $templatesCount = $db->table('templates')->where('is_active', 1)->countAllResults();
        }

        // Yaklaşan 5 publish
        $upcoming = $db->table('publishes p')
            ->select('p.id,p.platform,p.status,p.schedule_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->whereIn('p.status', ['queued', 'scheduled'])
            ->orderBy('p.schedule_at', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        // Son 5 işlem (published/failed/canceled)
        $recent = $db->table('publishes p')
            ->select('p.id,p.platform,p.status,p.updated_at,p.published_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->whereIn('p.status', ['published', 'failed', 'canceled'])
            ->orderBy('p.updated_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Bağlı sosyal hesaplar (mini liste)
        $accounts = $db->table('social_accounts')
            ->select('id, platform, name, username')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Aktif şablonlar (mini liste)
        $templates = [];
        if ($db->tableExists('templates')) {
            $templates = $db->table('templates')
                ->select('id, name, platform_scope, format_key, base_media_id')
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get()->getResultArray();
        }

        // Mini takvim: bu ay planlı + yayınlanan gün bazında adet
        $monthStart = (new \DateTime('first day of this month'))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $monthEnd   = (new \DateTime('last day of this month'))->setTime(23, 59, 59)->format('Y-m-d H:i:s');

        // Planlı (schedule_at)
        $scheduledRows = $db->table('publishes')
            ->select("DATE(schedule_at) as d, COUNT(*) as c")
            ->where('user_id', $userId)
            ->whereIn('status', ['queued', 'scheduled'])
            ->where('schedule_at >=', $monthStart)
            ->where('schedule_at <=', $monthEnd)
            ->groupBy("DATE(schedule_at)")
            ->get()->getResultArray();

        $dayCountsScheduled = [];
        foreach ($scheduledRows as $r) {
            $dayCountsScheduled[(string)$r['d']] = (int)$r['c'];
        }

        // Yayınlanan (published_at)
        $publishedRows = $db->table('publishes')
            ->select("DATE(published_at) as d, COUNT(*) as c")
            ->where('user_id', $userId)
            ->where('status', 'published')
            ->where('published_at IS NOT NULL', null, false)
            ->where('published_at >=', $monthStart)
            ->where('published_at <=', $monthEnd)
            ->groupBy("DATE(published_at)")
            ->get()->getResultArray();

        $dayCountsPublished = [];
        foreach ($publishedRows as $r) {
            $dayCountsPublished[(string)$r['d']] = (int)$r['c'];
        }

        return view('panel/dashboard', [
            'headerVariant' => 'dashboard',
            'pageTitle'     => 'Gösterge Paneli',
            'pageSubtitle'  => 'Planlarınız, bağlı hesaplarınız ve son aktivitelerinizin özeti.',

            'plannedThisWeek' => $plannedThisWeek,
            'accountsCount'   => $accountsCount,
            'templatesCount'  => $templatesCount,

            'upcoming'        => $upcoming,
            'recent'          => $recent,
            'accounts'        => $accounts,
            'templates'       => $templates,

            'monthStart'        => $monthStart,
            'dayCountsScheduled'=> $dayCountsScheduled,
            'dayCountsPublished'=> $dayCountsPublished,
        ]);
    }

    public function calendar()
    {
        if ($redirect = $this->ensureLoggedIn()) return $redirect;

        return view('panel/calendar', [
            'pageTitle'      => 'Takvim ve Planlama',
            'pageSubtitle'   => 'Tüm platformlardaki paylaşımlarınızı tek bir takvim üzerinden planlayın ve yönetin.',
            'headerVariant'  => 'compact',
        ]);
    }
}
