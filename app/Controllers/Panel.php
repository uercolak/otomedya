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
        $start = (new \DateTime('monday this week'))->setTime(0,0,0)->format('Y-m-d H:i:s');
        $end   = (new \DateTime('sunday this week'))->setTime(23,59,59)->format('Y-m-d H:i:s');

        $plannedThisWeek = $db->table('publishes')
            ->where('user_id', $userId)
            ->whereIn('status', ['queued','scheduled'])
            ->where('schedule_at >=', $start)
            ->where('schedule_at <=', $end)
            ->countAllResults();

        $accountsCount = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->countAllResults();

        // templates tablon yoksa 0 kalsın (opsiyonel)
        $templatesCount = 0;
        if ($db->tableExists('templates')) {
            $templatesCount = $db->table('templates')->countAllResults();
        }

        // Yaklaşan 5 publish
        $upcoming = $db->table('publishes p')
            ->select('p.id,p.platform,p.status,p.schedule_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->whereIn('p.status', ['queued','scheduled'])
            ->orderBy('p.schedule_at', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        // Son 5 işlem (published/failed/canceled)
        $recent = $db->table('publishes p')
            ->select('p.id,p.platform,p.status,p.updated_at,p.published_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->whereIn('p.status', ['published','failed','canceled'])
            ->orderBy('p.updated_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        return view('panel/dashboard', [
            'pageTitle'    => 'Dashboard',
            'pageSubtitle' => 'Planlanmış içeriklerin, hesapların ve akışın genel görünümü.',
            'plannedThisWeek' => $plannedThisWeek,
            'accountsCount'   => $accountsCount,
            'templatesCount'  => $templatesCount,
            'upcoming'        => $upcoming,
            'recent'          => $recent,
        ]);
    }

    public function calendar()
    {
        if ($redirect = $this->ensureLoggedIn()) return $redirect;

        return view('panel/calendar', [
            'pageTitle'      => 'Takvim & Planlama',
            'pageSubtitle'   => 'Tüm platformlardaki planlı gönderilerini tek bir takvim üzerinden yönet.',
            'headerVariant'  => 'compact', // 👈
        ]);
    }
}
