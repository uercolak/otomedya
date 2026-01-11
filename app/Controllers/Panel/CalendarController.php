<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\PublishModel;

class CalendarController extends BaseController
{
    protected function ensureUser()
    {
        if (! session('is_logged_in')) {
            return redirect()->to(base_url('auth/login'));
        }
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $rows = $db->table('publishes p')
            ->select('
                p.id,p.platform,p.status,p.schedule_at,p.published_at,p.remote_id,p.content_id,p.account_id,
                c.title as content_title,
                c.base_text as content_text,
                c.media_type as content_media_type,
                c.media_path as content_media_path,
                sa.username as sa_username, sa.name as sa_name
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.user_id', $userId)
            ->where('p.schedule_at IS NOT NULL', null, false)
            ->orderBy('p.schedule_at', 'ASC')
            ->get()->getResultArray();

        $events = array_map(function ($r) {
            $platform = strtoupper((string)($r['platform'] ?? ''));

            $contentTitle = trim((string)($r['content_title'] ?? ''));
            if ($contentTitle === '') $contentTitle = '#' . (int)($r['content_id'] ?? 0);

            $contentText = trim((string)($r['content_text'] ?? ''));

            $acc = '';
            if (!empty($r['sa_username'])) $acc = '@' . $r['sa_username'];
            elseif (!empty($r['sa_name'])) $acc = (string)$r['sa_name'];
            else $acc = 'Hesap #' . (int)($r['account_id'] ?? 0);

            $status = (string)($r['status'] ?? '');

            $remoteId = (string)($r['remote_id'] ?? '');
            $remoteUrl = (preg_match('~^https?://~i', $remoteId) === 1) ? $remoteId : '';

            $mediaType = (string)($r['content_media_type'] ?? '');
            $mediaPath = (string)($r['content_media_path'] ?? '');
            $mediaUrl  = $mediaPath ? base_url($mediaPath) : '';

            $canDrag = in_array($status, ['queued', 'scheduled'], true);

            return [
                'id'    => (string)$r['id'],
                'title' => $platform,
                'start' => (string)$r['schedule_at'],
                'url'   => site_url('panel/publishes/' . (int)$r['id']),
                'startEditable' => $canDrag,

                'extendedProps' => [
                    'status'       => $status,
                    'statusLabel'  => PublishModel::label($status),

                    'platform'     => $platform,
                    'account'      => $acc,

                    'contentTitle' => $contentTitle,
                    'contentText'  => $contentText,
                    'mediaType'    => $mediaType,
                    'mediaUrl'     => $mediaUrl,

                    'remoteUrl'    => $remoteUrl,
                ],
            ];
        }, $rows);

        return view('panel/calendar', [
            'pageTitle'     => 'Takvim & Planlama',
            'pageSubtitle'  => 'Tüm platformlardaki planlı gönderilerini tek bir takvim üzerinden yönet.',
            'headerVariant' => 'compact',
            'eventsJson'    => json_encode($events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    public function store()
    {
        if ($r = $this->ensureUser()) return $r;

        if ($this->request->getMethod(true) !== 'POST') {
            return $this->response->setStatusCode(405)->setJSON([
                'ok' => false,
                'message' => 'Method not allowed',
                'csrf' => csrf_hash(),
            ]);
        }

        $userId     = (int) session('user_id');
        $publishId  = (int)($this->request->getPost('publish_id') ?? 0);
        $scheduleAt = trim((string)($this->request->getPost('schedule_at') ?? ''));

        if ($publishId <= 0 || $scheduleAt === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'ok' => false,
                'message' => 'Eksik parametre',
                'csrf' => csrf_hash(),
            ]);
        }

        if (!preg_match('~^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$~', $scheduleAt)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Tarih/Saat formatı geçersiz.',
                'csrf' => csrf_hash(),
            ]);
        }

        $db  = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $row = $db->table('publishes')
            ->select('id,user_id,status,job_id,schedule_at')
            ->where('id', $publishId)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (!$row) {
            return $this->response->setStatusCode(404)->setJSON([
                'ok' => false,
                'message' => 'Kayıt bulunamadı',
                'csrf' => csrf_hash(),
            ]);
        }

        $status = (string)($row['status'] ?? '');
        if (!in_array($status, ['queued', 'scheduled'], true)) {
            return $this->response->setStatusCode(422)->setJSON([
                'ok' => false,
                'message' => 'Bu durumdaki kayıt taşınamaz',
                'csrf' => csrf_hash(),
            ]);
        }

        $isFuture = (strtotime($scheduleAt) > strtotime($now) + 30);

        $newStatus = $status;
        if ($status === 'queued' && $isFuture) {
            $newStatus = 'scheduled';
        } elseif ($status === 'scheduled' && !$isFuture) {
            $newStatus = 'queued';
        }

        $db->transStart();

        $db->table('publishes')
            ->where('id', $publishId)
            ->where('user_id', $userId)
            ->update([
                'schedule_at' => $scheduleAt,
                'status'      => $newStatus,
                'updated_at'  => $now,
            ]);

        $jobId = (int)($row['job_id'] ?? 0);
        if ($jobId > 0) {
            $db->table('jobs')
                ->where('id', $jobId)
                ->whereIn('status', ['queued', 'running'])
                ->update([
                    'run_at'     => $scheduleAt,
                    'status'     => 'queued',
                    'locked_at'  => null,
                    'locked_by'  => null,
                    'updated_at' => $now,
                ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return $this->response->setStatusCode(500)->setJSON([
                'ok' => false,
                'message' => 'DB hata',
                'csrf' => csrf_hash(),
            ]);
        }

        return $this->response->setJSON([
            'ok'          => true,
            'status'      => $newStatus,
            'statusLabel' => PublishModel::label($newStatus), 
            'csrf'        => csrf_hash(),
        ]);
    }
}
