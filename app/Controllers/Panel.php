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
        $templatesCount = $db->table('templates')
            ->where('is_active', 1)
            ->countAllResults();
    }

    $upcoming = $db->table('publishes p')
        ->select('p.id,p.platform,p.status,p.schedule_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
        ->join('contents c', 'c.id = p.content_id', 'left')
        ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
        ->where('p.user_id', $userId)
        ->whereIn('p.status', ['queued', 'scheduled'])
        ->orderBy('p.schedule_at', 'ASC')
        ->limit(5)
        ->get()->getResultArray();

    $recent = $db->table('publishes p')
        ->select('p.id,p.platform,p.status,p.updated_at,p.published_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
        ->join('contents c', 'c.id = p.content_id', 'left')
        ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
        ->where('p.user_id', $userId)
        ->whereIn('p.status', ['published', 'failed', 'canceled'])
        ->orderBy('p.updated_at', 'DESC')
        ->limit(5)
        ->get()->getResultArray();

    $accounts = $db->table('social_accounts')
        ->select('id, platform, name, username')
        ->where('user_id', $userId)
        ->orderBy('id', 'DESC')
        ->limit(5)
        ->get()->getResultArray();

    $templates = [];
    if ($db->tableExists('templates')) {
        $templates = $db->table('templates')
            ->select('id, name, platform_scope, format_key, base_media_id')
            ->where('is_active', 1)
            ->orderBy('id', 'DESC')
            ->limit(3)
            ->get()->getResultArray();
    }

    $monthStart = (new \DateTime('first day of this month'))->setTime(0, 0, 0)->format('Y-m-d H:i:s');
    $monthEnd   = (new \DateTime('last day of this month'))->setTime(23, 59, 59)->format('Y-m-d H:i:s');

    $dayCountsScheduled = [];
    $dayCountsPublished = [];

    $rowsSch = $db->table('publishes')
        ->select("DATE(schedule_at) as d, COUNT(*) as c")
        ->where('user_id', $userId)
        ->where('schedule_at >=', $monthStart)
        ->where('schedule_at <=', $monthEnd)
        ->whereIn('status', ['queued', 'scheduled'])
        ->groupBy("DATE(schedule_at)")
        ->get()->getResultArray();

    foreach ($rowsSch as $r) {
        $dayCountsScheduled[(string)$r['d']] = (int)$r['c'];
    }

    $rowsPub = $db->table('publishes')
        ->select("DATE(schedule_at) as d, COUNT(*) as c")
        ->where('user_id', $userId)
        ->where('schedule_at >=', $monthStart)
        ->where('schedule_at <=', $monthEnd)
        ->where('status', 'published')
        ->groupBy("DATE(schedule_at)")
        ->get()->getResultArray();

    foreach ($rowsPub as $r) {
        $dayCountsPublished[(string)$r['d']] = (int)$r['c'];
    }

    $step1Done = ($accountsCount > 0);

    $contentsCount = 0;
    if ($db->tableExists('contents')) {
        try {
            $contentsCount = $db->table('contents')->where('user_id', $userId)->countAllResults();
        } catch (\Throwable $e) {
            $contentsCount = $db->table('contents')->countAllResults();
        }
    }
    $step2Done = (($templatesCount > 0) || ($contentsCount > 0));

    $plannedTotal = $db->table('publishes')
        ->where('user_id', $userId)
        ->whereIn('status', ['queued', 'scheduled'])
        ->countAllResults();
    $step3Done = ($plannedTotal > 0);

    $wizardSteps = [
        [
            'key'   => 'accounts',
            'title' => 'Sosyal hesap bağla',
            'desc'  => 'Meta / TikTok / YouTube hesaplarını bağla.',
            'done'  => $step1Done,
            'url'   => site_url('panel/social-accounts'),
            'cta'   => $step1Done ? 'Yönet' : 'Bağla',
            'icon'  => 'bi-link-45deg',
        ],
        [
            'key'   => 'content',
            'title' => 'Şablon seç / içerik oluştur',
            'desc'  => 'Hazır şablonlardan içerik üret veya yeni içerik hazırla.',
            'done'  => $step2Done,
            'url'   => site_url('panel/templates'),
            'cta'   => $step2Done ? 'Göz at' : 'Başla',
            'icon'  => 'bi-easel2',
        ],
        [
            'key'   => 'plan',
            'title' => 'İlk paylaşımı planla',
            'desc'  => 'Planner üzerinden planla, Calendar’a düşsün.',
            'done'  => $step3Done,
            'url'   => site_url('panel/planner'),
            'cta'   => $step3Done ? 'Yeni planla' : 'Planla',
            'icon'  => 'bi-calendar2-plus',
        ],
    ];

    $doneCount = 0;
    foreach ($wizardSteps as $st) {
        if (!empty($st['done'])) $doneCount++;
    }
    $wizardPercent = (int) round(($doneCount / 3) * 100);

    return view('panel/dashboard', [
        'headerVariant'   => 'dashboard',
        'pageTitle'       => 'Gösterge Paneli',
        'pageSubtitle'    => 'Planlı gönderilerin, bağlı hesapların ve son işlemlerin özeti.',
        'plannedThisWeek' => $plannedThisWeek,
        'accountsCount'   => $accountsCount,
        'templatesCount'  => $templatesCount,
        'upcoming'        => $upcoming,
        'recent'          => $recent,
        'accounts'        => $accounts,
        'templates'       => $templates,
        'monthStart'      => $monthStart,
        'dayCountsScheduled' => $dayCountsScheduled,
        'dayCountsPublished' => $dayCountsPublished,
        'wizardSteps'     => $wizardSteps,
        'wizardPercent'   => $wizardPercent,
        'wizardDoneCount' => $doneCount,
    ]);
}


    public function calendar()
    {
        if ($redirect = $this->ensureLoggedIn()) return $redirect;

        return view('panel/calendar', [
            'pageTitle'     => 'Takvim & Planlama',
            'pageSubtitle'  => 'Tüm platformlardaki planlı gönderilerini tek bir takvim üzerinden yönet.',
            'headerVariant' => 'compact',
        ]);
    }
}
