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

        // Bu hafta "planlı" = queued + scheduled
        $plannedThisWeek = $db->table('publishes')
            ->where('user_id', $userId)
            ->whereIn('status', ['queued','scheduled'])
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

        // Yaklaşan 5 (queued/scheduled)
        $upcoming = $db->table('publishes p')
            ->select('p.id,p.platform,p.status,p.schedule_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->whereIn('p.status', ['queued','scheduled'])
            ->orderBy('p.schedule_at', 'ASC')
            ->limit(5)
            ->get()->getResultArray();

        // Son 5 aktivite (published/failed/canceled)
        $recent = $db->table('publishes p')
            ->select('p.id,p.platform,p.status,p.updated_at,p.published_at, c.title as content_title, sa.username as sa_username, sa.name as sa_name')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->whereIn('p.status', ['published','failed','canceled'])
            ->orderBy('p.updated_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Bağlı sosyal hesaplar (mini)
        $accounts = $db->table('social_accounts')
            ->select('id, platform, name, username')
            ->where('user_id', $userId)
            ->orderBy('id', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        // Aktif şablonlar (mini)
        $templates = [];
        if ($db->tableExists('templates')) {
            $templates = $db->table('templates')
                ->select('id, name, platform_scope, format_key, base_media_id')
                ->where('is_active', 1)
                ->orderBy('id', 'DESC')
                ->limit(3)
                ->get()->getResultArray();
        }

        /**
         * ✅ Dashboard mini takvim rozetleri:
         * Bu ay içindeki gönderileri gün bazında say.
         * Müşteri için mantıklı olan: planlı + yayınlanan + hata alan (canceled hariç)
         */
        $monthStart = (new \DateTime('first day of this month'))->setTime(0,0,0)->format('Y-m-d H:i:s');
        $monthEnd   = (new \DateTime('last day of this month'))->setTime(23,59,59)->format('Y-m-d H:i:s');

        $dayCountsRows = $db->table('publishes')
            ->select("DATE(COALESCE(schedule_at, published_at, created_at)) as d, COUNT(*) as c")
            ->where('user_id', $userId)
            ->whereIn('status', ['queued','scheduled','published','failed']) // canceled hariç
            ->where("COALESCE(schedule_at, published_at, created_at) >=", $monthStart)
            ->where("COALESCE(schedule_at, published_at, created_at) <=", $monthEnd)
            ->groupBy("DATE(COALESCE(schedule_at, published_at, created_at))")
            ->get()->getResultArray();

        $dayCounts = [];
        foreach ($dayCountsRows as $r) {
            $dayCounts[(string)$r['d']] = (int)$r['c'];
        }

        // Bu ay "dikkat gerektiren" (failed) sayısı - küçük bir uyarı gösterebiliriz
        $needsAttentionCount = $db->table('publishes')
            ->where('user_id', $userId)
            ->where('status', 'failed')
            ->where('updated_at >=', $monthStart)
            ->where('updated_at <=', $monthEnd)
            ->countAllResults();

        return view('panel/dashboard', [
            'headerVariant' => 'dashboard',
            'pageTitle'     => 'Gösterge Paneli',
            'pageSubtitle'  => 'Planlı gönderiler, bağlı hesaplar ve son aktivitelerin özeti.',
            'plannedThisWeek'     => $plannedThisWeek,
            'accountsCount'       => $accountsCount,
            'templatesCount'      => $templatesCount,
            'needsAttentionCount' => $needsAttentionCount,
            'upcoming'            => $upcoming,
            'recent'              => $recent,
            'accounts'            => $accounts,
            'templates'           => $templates,
            'monthStart'          => $monthStart,
            'dayCounts'           => $dayCounts,
        ]);
    }

    public function calendar()
    {
        if ($redirect = $this->ensureLoggedIn()) return $redirect;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        // ✅ Drag&Drop update (AJAX POST)
        if ($this->request->getMethod() === 'post') {
            $publishId  = (int) $this->request->getPost('publish_id');
            $scheduleAt = trim((string) $this->request->getPost('schedule_at'));

            if ($publishId <= 0 || $scheduleAt === '') {
                return $this->response->setJSON(['ok' => false, 'message' => 'Geçersiz istek.'])
                    ->setStatusCode(400);
            }

            // sadece kullanıcının kaydı + sadece queued/scheduled güncellenebilir
            $row = $db->table('publishes')
                ->select('id,status')
                ->where('id', $publishId)
                ->where('user_id', $userId)
                ->get()->getRowArray();

            if (! $row) {
                return $this->response->setJSON(['ok' => false, 'message' => 'Kayıt bulunamadı.'])
                    ->setStatusCode(404);
            }

            if (! in_array($row['status'], ['queued','scheduled'], true)) {
                return $this->response->setJSON(['ok' => false, 'message' => 'Bu gönderinin tarihi değiştirilemez.'])
                    ->setStatusCode(409);
            }

            // update
            $db->table('publishes')
                ->where('id', $publishId)
                ->where('user_id', $userId)
                ->update([
                    'schedule_at' => $scheduleAt,
                    'updated_at'  => date('Y-m-d H:i:s'),
                ]);

            return $this->response->setJSON([
                'ok' => true,
                'status' => $row['status'],
                'statusLabel' => ($row['status'] === 'queued' ? 'Sırada' : 'Planlı'),
            ]);
        }

        /**
         * ✅ Calendar events:
         * Çok geniş çekmeyelim; performans için 60 gün yeterli.
         */
        $from = (new \DateTime('first day of this month'))->modify('-15 days')->setTime(0,0,0)->format('Y-m-d H:i:s');
        $to   = (new \DateTime('last day of this month'))->modify('+15 days')->setTime(23,59,59)->format('Y-m-d H:i:s');

        $rows = $db->table('publishes p')
            ->select('
                p.id, p.platform, p.status, p.schedule_at, p.published_at, p.updated_at,
                p.remote_id,
                c.title as content_title, c.text as content_text,
                c.media_id as media_id, c.media_type as media_type,
                sa.username as sa_username, sa.name as sa_name
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->where("COALESCE(p.schedule_at, p.published_at, p.created_at) >=", $from)
            ->where("COALESCE(p.schedule_at, p.published_at, p.created_at) <=", $to)
            ->orderBy('COALESCE(p.schedule_at, p.published_at, p.created_at)', 'ASC')
            ->get()->getResultArray();

        $statusLabel = function($s){
            return match((string)$s){
                'queued'     => 'Sırada',
                'scheduled'  => 'Planlı',
                'publishing' => 'Yayınlanıyor',
                'published'  => 'Yayınlandı',
                'failed'     => 'Hata',
                'canceled'   => 'İptal',
                default      => ucfirst((string)$s ?: '—'),
            };
        };

        $events = [];
        foreach ($rows as $r) {
            $start = $r['schedule_at'] ?: ($r['published_at'] ?: ($r['updated_at'] ?: null));
            if (! $start) continue;

            $platform = strtoupper((string)($r['platform'] ?? ''));
            $title = $platform ?: 'Gönderi';

            $mediaUrl = '';
            $mediaId = (int)($r['media_id'] ?? 0);
            if ($mediaId > 0) {
                $mediaUrl = site_url('media/' . $mediaId);
            }

            // yayınlananlarda remoteUrl varsa tıklayınca açılacak
            $remoteUrl = '';
            if (!empty($r['remote_id'])) {
                // sende remoteUrl üretimi başka yerdeyse burayı oraya göre uyarlarsın
                // şimdilik boş bırakıyorum; doluysa eventClick direkt açar
                $remoteUrl = '';
            }

            $events[] = [
                'id'    => (string)$r['id'],
                'title' => $title,
                'start' => date('c', strtotime((string)$start)),
                'url'   => site_url('panel/publishes/' . (int)$r['id']),
                'extendedProps' => [
                    'platform'     => $platform,
                    'status'       => (string)($r['status'] ?? ''),
                    'statusLabel'  => $statusLabel($r['status'] ?? ''),
                    'account'      => (string)($r['sa_username'] ?: ($r['sa_name'] ?: '')),
                    'contentTitle' => (string)($r['content_title'] ?? ''),
                    'contentText'  => (string)($r['content_text'] ?? ''),
                    'mediaType'    => (string)($r['media_type'] ?? ''),
                    'mediaUrl'     => $mediaUrl,
                    'remoteUrl'    => $remoteUrl,
                ],
            ];
        }

        // sağ kart için hızlı sayılar
        $accountsCount = $db->table('social_accounts')->where('user_id', $userId)->countAllResults();

        $monthStart = (new \DateTime('first day of this month'))->setTime(0,0,0)->format('Y-m-d H:i:s');
        $monthEnd   = (new \DateTime('last day of this month'))->setTime(23,59,59)->format('Y-m-d H:i:s');

        $plannedThisMonth = $db->table('publishes')
            ->where('user_id', $userId)
            ->whereIn('status', ['queued','scheduled'])
            ->where('schedule_at >=', $monthStart)
            ->where('schedule_at <=', $monthEnd)
            ->countAllResults();

        $needsAttentionCount = $db->table('publishes')
            ->where('user_id', $userId)
            ->where('status', 'failed')
            ->where('updated_at >=', $monthStart)
            ->where('updated_at <=', $monthEnd)
            ->countAllResults();

        return view('panel/calendar', [
            'pageTitle'      => 'Takvim & Planlama',
            'pageSubtitle'   => 'Tüm platformlardaki gönderilerini tek bir takvim üzerinden kolayca yönet.',
            'headerVariant'  => 'compact',
            'eventsJson'     => json_encode($events, JSON_UNESCAPED_UNICODE),
            'accountsCount'  => $accountsCount,
            'plannedThisMonth' => $plannedThisMonth,
            'needsAttentionCount' => $needsAttentionCount,
        ]);
    }
}
