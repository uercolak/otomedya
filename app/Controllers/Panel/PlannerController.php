<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class PlannerController extends BaseController
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

        $accounts = $db->table('social_accounts')
            ->select('id,platform,username,name')
            ->where('user_id', $userId)
            ->orderBy('platform', 'ASC')
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        $contentId = (int)($this->request->getGet('content_id') ?? 0);
        $prefill = null;

        if ($contentId > 0) {
            $prefill = $db->table('contents')
                ->select('id,title,base_text,media_type,media_path,meta_json')
                ->where('id', $contentId)
                ->where('user_id', $userId)
                ->get()->getRowArray();
        }

        // ✅ yeni view yolu
        return view('panel/planner/index', [
            'pageTitle' => 'Yeni Gönderi Planla',
            'headerVariant' => 'compact',
            'accounts' => $accounts,
            'prefill' => $prefill,
        ]);
    }

    public function store()
    {
        if ($r = $this->ensureUser()) return $r;

        if ($this->request->getMethod(true) !== 'POST') {
            return $this->response->setStatusCode(405)->setBody('Method not allowed');
        }

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        $title         = trim((string)$this->request->getPost('title'));
        $baseText      = trim((string)$this->request->getPost('base_text'));
        $scheduleAtRaw = trim((string)$this->request->getPost('schedule_at'));

        $incomingContentId = (int)($this->request->getPost('content_id') ?? 0);
        $existingContent = null;

        if ($incomingContentId > 0) {
            $existingContent = $db->table('contents')
                ->select('id,title,base_text,media_type,media_path,meta_json,template_id')
                ->where('id', $incomingContentId)
                ->where('user_id', $userId)
                ->get()->getRowArray();

            if (!$existingContent) {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'İçerik bulunamadı. Lütfen tekrar deneyin.');
            }

            if ($title === '' && !empty($existingContent['title'])) {
                $title = (string)$existingContent['title'];
            }
            if ($baseText === '' && !empty($existingContent['base_text'])) {
                $baseText = (string)$existingContent['base_text'];
            }
        }

        $accountIds = $this->request->getPost('account_ids');
        if (!is_array($accountIds)) $accountIds = [];
        $accountIds = array_values(array_unique(array_filter(array_map('intval', $accountIds))));

        if (empty($accountIds)) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Lütfen en az 1 hedef hesap seçin.');
        }

        $scheduleAt = $this->normalizeDatetime($scheduleAtRaw);
        if ($scheduleAt === null) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Lütfen geçerli bir tarih ve saat seçin.');
        }

        // ✅ --- ZAMAN KONTROLÜ (2 dk toleranslı) ---
        // normalizeDatetime() "Y-m-d H:i" döndürüyor olabilir; saniye yoksa :00 ekleyelim
        $scheduleAtFixed = str_replace('T', ' ', (string)$scheduleAt);
        if (preg_match('~^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}$~', $scheduleAtFixed)) {
            $scheduleAtFixed .= ':00';
        }

        // timezone'ı uygulamayla aynı tutalım
        $tzName = config('App')->appTimezone ?? 'Europe/Istanbul';
        try {
            $tz = new \DateTimeZone($tzName);
        } catch (\Throwable $e) {
            $tz = new \DateTimeZone('Europe/Istanbul');
        }

        try {
            $dtSchedule = new \DateTime($scheduleAtFixed, $tz);
            $dtNow      = new \DateTime('now', $tz);

            // ✅ 2 dakika tolerans: bundan daha eskiyse geçmiş say
            $minAllowed = (clone $dtNow)->modify('-2 minutes');

            if ($dtSchedule < $minAllowed) {
                return redirect()->to(site_url('panel/planner'))
                    ->withInput()
                    ->with('error', 'Geçmiş tarihli paylaşım yapılamamaktadır. Lütfen ileri bir tarih/saat seçin.');
            }

            // Veritabanına giden schedule_at formatını da sabitleyelim
            $scheduleAt = $dtSchedule->format('Y-m-d H:i:s');

        } catch (\Throwable $e) {
            return redirect()->to(site_url('panel/planner'))
                ->withInput()
                ->with('error', 'Tarih/Saat formatı okunamadı. Lütfen tekrar seçin.');
        }
        // ✅ --- /ZAMAN KONTROLÜ ---

        $rows = $db->table('social_accounts')
            ->select('id,platform')
            ->where('user_id', $userId)
            ->whereIn('id', $accountIds)
            ->get()->getResultArray();

        if (count($rows) !== count($accountIds)) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Seçilen hesaplardan bazıları bulunamadı.');
        }

        $selectedPlatforms = array_map(fn($r) => strtolower((string)($r['platform'] ?? '')), $rows);

        $hasInstagram = in_array('instagram', $selectedPlatforms, true);
        $hasFacebook  = in_array('facebook',  $selectedPlatforms, true);
        $hasYouTube   = in_array('youtube',   $selectedPlatforms, true);
        $hasTikTok    = in_array('tiktok',    $selectedPlatforms, true);

        // ---------- Media ----------
        $mediaType = null;
        $mediaPath = null;

        if ($existingContent) {
            $mediaType = !empty($existingContent['media_type']) ? (string)$existingContent['media_type'] : null;
            $mediaPath = !empty($existingContent['media_path']) ? (string)$existingContent['media_path'] : null;
        }

        $file = $this->request->getFile('media');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mime = (string)$file->getMimeType();
            if (str_starts_with($mime, 'image/')) $mediaType = 'image';
            elseif (str_starts_with($mime, 'video/')) $mediaType = 'video';
            else {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Bu dosya türü desteklenmiyor.');
            }

            $subdir = date('Y') . '/' . date('m');
            $targetDir = FCPATH . 'uploads/' . $subdir;
            if (!is_dir($targetDir)) @mkdir($targetDir, 0775, true);

            $newName = $file->getRandomName();
            $file->move($targetDir, $newName);
            $mediaPath = 'uploads/' . $subdir . '/' . $newName;
        }

        // ---------- Settings (UI -> backend) ----------
        $settings = [];

        // Instagram
        $igPostType = strtolower(trim((string)$this->request->getPost('ig_post_type')));
        if ($igPostType === '') $igPostType = 'auto';
        if (!in_array($igPostType, ['auto','post','reels','story'], true)) $igPostType = 'auto';

        if ($hasInstagram) {
            if ($igPostType === 'story' && $mediaType === null) {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Instagram Hikaye için görsel veya video seçmelisiniz.');
            }
            if ($igPostType === 'reels' && $mediaType !== 'video') {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Instagram Reels için video seçmelisiniz.');
            }
            if ($igPostType === 'post' && $mediaType === null) {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Instagram Gönderi için görsel seçmelisiniz.');
            }
            if ($igPostType === 'auto' && $mediaType === null) {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Instagram için paylaşım yapabilmek adına medya seçmelisiniz.');
            }

            $settings['instagram'] = [
                'post_type' => $igPostType,
            ];
        }

        // Facebook
        if ($hasFacebook) {
            $fbPrivacy = strtolower(trim((string)$this->request->getPost('fb_privacy')));
            if (!in_array($fbPrivacy, ['public','page'], true)) $fbPrivacy = 'public';

            $fbAllowComments = $this->request->getPost('fb_allow_comments') ? true : false;

            $settings['facebook'] = [
                'privacy' => $fbPrivacy,
                'allow_comments' => $fbAllowComments,
            ];
        }

        // TikTok
        if ($hasTikTok) {
            if ($mediaType !== 'video') {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'TikTok için video seçmelisiniz.');
            }
            if (trim($baseText) === '') {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'TikTok için açıklama boş olamaz.');
            }

            $ttPrivacy = strtolower(trim((string)$this->request->getPost('tt_privacy')));
            if (!in_array($ttPrivacy, ['public','private'], true)) $ttPrivacy = 'public';

            $settings['tiktok'] = [
                'privacy' => $ttPrivacy,
                'allow_comments' => $this->request->getPost('tt_allow_comments') ? true : false,
                'allow_duet'     => $this->request->getPost('tt_allow_duet') ? true : false,
                'allow_stitch'   => $this->request->getPost('tt_allow_stitch') ? true : false,
            ];
        }

        // YouTube
        $postSettings = $this->request->getPost('settings') ?? [];

        $ytTitle = trim((string)($postSettings['youtube']['title'] ?? ''));
        $ytPrivacy = strtolower(trim((string)(
            $postSettings['youtube']['privacy'] ?? $this->request->getPost('youtube_privacy') ?? ''
        )));

        if ($ytPrivacy === '') $ytPrivacy = 'public';
        if (!in_array($ytPrivacy, ['public','unlisted','private'], true)) $ytPrivacy = 'public';

        if ($hasYouTube) {
            if ($ytTitle === '') {
                return redirect()->to(site_url('panel/planner'))
                    ->withInput()
                    ->with('error', 'YouTube için video başlığı girmelisiniz.');
            }
            if ($mediaType !== 'video') {
                return redirect()->to(site_url('panel/planner'))
                    ->withInput()
                    ->with('error', 'YouTube için video seçmelisiniz.');
            }

            $settings['youtube'] = [
                'title'   => $ytTitle,
                'privacy' => $ytPrivacy,
            ];
        }

        // ---------- Save content meta ----------
        $contentMeta = [
            'settings' => $settings,
        ];

        $db->transStart();

        if ($existingContent) {
            $contentId = (int)$existingContent['id'];

            $db->table('contents')
                ->where('id', $contentId)
                ->where('user_id', $userId)
                ->update([
                    'title'      => ($title !== '' ? $title : null),
                    'base_text'  => ($baseText !== '' ? $baseText : null),
                    'meta_json'  => json_encode($contentMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => $now,
                ]);
        } else {
            $db->table('contents')->insert([
                'user_id'     => $userId,
                'title'       => ($title !== '' ? $title : null),
                'base_text'   => ($baseText !== '' ? $baseText : null),
                'media_type'  => $mediaType,
                'media_path'  => $mediaPath,
                'template_id' => null,
                'meta_json'   => json_encode($contentMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
            $contentId = (int) $db->insertID();
        }

        $createdPublishes = 0;

        foreach ($rows as $acc) {
            $accountId = (int)$acc['id'];
            $platform  = strtolower((string)$acc['platform']);

            $jobType = ($platform === 'youtube') ? 'publish_youtube' : 'publish_post';

            $idempotencyKey = $this->makeIdempotencyKey(
                $userId, $platform, $accountId, $contentId, $scheduleAt,
                md5(json_encode($settings, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES))
            );

            $existing = $db->table('publishes')
                ->select('id')
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->get()->getRowArray();

            if ($existing) continue;

            $db->table('publishes')->insert([
                'user_id'         => $userId,
                'platform'        => $platform,
                'account_id'      => $accountId,
                'content_id'      => $contentId,
                'status'          => 'queued',
                'schedule_at'     => $scheduleAt,
                'idempotency_key' => $idempotencyKey,
                'created_at'      => $now,
                'updated_at'      => $now,
                'meta_json'       => json_encode(['settings' => $settings], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            $publishId = (int) $db->insertID();

            $payload = [
                'publish_id' => $publishId,
                'platform'   => $platform,
                'account_id' => $accountId,
                'content_id' => $contentId,
                'settings'   => $settings,
            ];

            $db->table('jobs')->insert([
                'type'         => $jobType,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'status'       => 'queued',
                'priority'     => 100,
                'run_at'       => $scheduleAt,
                'locked_at'    => null,
                'locked_by'    => null,
                'attempts'     => 0,
                'max_attempts' => 3,
                'last_error'   => null,
                'created_at'   => $now,
                'updated_at'   => $now,
            ]);
            $jobId = (int) $db->insertID();

            $db->table('publishes')
                ->where('id', $publishId)
                ->where('user_id', $userId)
                ->update([
                    'job_id'     => $jobId,
                    'updated_at' => $now,
                ]);

            $createdPublishes++;
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Planlama sırasında bir sorun oluştu. Lütfen tekrar deneyin.');
        }

        return redirect()->to(site_url('panel/calendar'))
            ->with('success', "Planlandı. Oluşturulan gönderi sayısı: {$createdPublishes}");
    }

    private function normalizeDatetime(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        if (preg_match('~^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$~', $raw)) return $raw;
        if (preg_match('~^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$~', $raw)) return str_replace('T', ' ', $raw) . ':00';
        if (preg_match('~^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$~', $raw)) return str_replace('T', ' ', $raw);

        $ts = strtotime($raw);
        if ($ts === false) return null;
        return date('Y-m-d H:i:s', $ts);
    }

    private function makeIdempotencyKey(
        int $userId,
        string $platform,
        int $accountId,
        int $contentId,
        string $scheduleAt,
        string $settingsHash
    ): string {
        $secret = (string) (getenv('IDEMPOTENCY_SECRET') ?: (config('App')->encryptionKey ?? 'otomedya-dev-secret'));

        $payload = implode('|', [
            $userId,
            strtolower($platform),
            $accountId,
            $contentId,
            $scheduleAt,
            $settingsHash,
        ]);

        return hash_hmac('sha256', $payload, $secret);
    }
}
