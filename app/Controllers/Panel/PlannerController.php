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

        return view('panel/planner', [
            'pageTitle' => 'Yeni Gönderi Planla',
            'headerVariant' => 'compact',
            'accounts' => $accounts,
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

        $title = trim((string)$this->request->getPost('title'));
        $baseText = trim((string)$this->request->getPost('base_text'));
        $scheduleAtRaw = trim((string)$this->request->getPost('schedule_at'));

        // ✅ AUTO eklendi
        $postType = strtolower(trim((string)$this->request->getPost('post_type')));
        if (!in_array($postType, ['auto','post','reels','story'], true)) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Paylaşım tipi geçersiz.');
        }

        $accountIds = $this->request->getPost('account_ids');
        if (!is_array($accountIds)) $accountIds = [];
        $accountIds = array_values(array_unique(array_filter(array_map('intval', $accountIds))));

        if (empty($accountIds)) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'En az 1 hedef hesap seçmelisin.');
        }

        $scheduleAt = $this->normalizeDatetime($scheduleAtRaw);
        if ($scheduleAt === null) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Tarih/Saat formatı geçersiz.');
        }

        $rows = $db->table('social_accounts')
            ->select('id,platform')
            ->where('user_id', $userId)
            ->whereIn('id', $accountIds)
            ->get()->getResultArray();

        if (count($rows) !== count($accountIds)) {
            return redirect()->to(site_url('panel/planner'))
                ->with('error', 'Seçilen hesaplardan bazıları bulunamadı.');
        }

        // upload (opsiyonel)
        $mediaType = null; // image|video|null
        $mediaPath = null;

        $file = $this->request->getFile('media');
        if ($file && $file->isValid() && !$file->hasMoved()) {
            $mime = (string)$file->getMimeType();
            if (str_starts_with($mime, 'image/')) $mediaType = 'image';
            elseif (str_starts_with($mime, 'video/')) $mediaType = 'video';
            else {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Desteklenmeyen dosya tipi: ' . $mime);
            }

            $subdir = date('Y') . '/' . date('m');
            $targetDir = FCPATH . 'uploads/' . $subdir;
            if (!is_dir($targetDir)) {
                @mkdir($targetDir, 0775, true);
            }

            $newName = $file->getRandomName();
            $file->move($targetDir, $newName);
            $mediaPath = 'uploads/' . $subdir . '/' . $newName;
        }

        $selectedPlatforms = array_map(
            fn($r) => strtolower((string)($r['platform'] ?? '')),
            $rows
        );

        $hasInstagram = in_array('instagram', $selectedPlatforms, true);
        $hasFacebook  = in_array('facebook',  $selectedPlatforms, true);
        $hasYoutube   = in_array('youtube',   $selectedPlatforms, true);

        // =========================
        // ✅ Platform-aware VALIDATION
        // =========================

        // YouTube: video zorunlu
        if ($hasYoutube) {
            if ($mediaType !== 'video' || $mediaPath === null) {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'YouTube için video yüklemek zorunlu.');
            }
        }

        // Instagram: post/story için medya zorunlu, reels için video zorunlu
        if ($hasInstagram) {
            $igType = $this->resolvePostTypeForPlatform('instagram', $postType, $mediaType);

            if ($igType === 'reels' && $mediaType !== 'video') {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Instagram Reels için video yüklemelisin.');
            }

            if (in_array($igType, ['post','story'], true) && $mediaType === null) {
                return redirect()->to(site_url('panel/planner'))
                    ->with('error', 'Instagram Post/Story için en az 1 medya yüklemelisin.');
            }
        }

        // Facebook: medya yoksa text post OK, video varsa "video" akışı kullanacağız (handler tarafında ayrıştıracağız)
        // Burada ekstra zorunluluk koymuyoruz.

        $db->transStart();

        // =========================
        // 1) content
        // =========================
        // ✅ content.meta_json içine "base" post_type yazıyoruz (mevcut IG handler content_meta_json okuduğu için)
        // Çoklu platformda gerçek tipleri job payload'da ayrıca taşıyacağız.
        $contentMeta = [
            'post_type'  => ($postType === 'auto' ? 'auto' : $postType),
            'media_type' => $mediaType,
        ];

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

        $createdPublishes = 0;

        // =========================
        // 2) publish + job (platforma göre post_type normalize)
        // =========================
        foreach ($rows as $acc) {
            $accountId = (int)$acc['id'];
            $platform  = strtolower((string)$acc['platform']);

            $effectivePostType = $this->resolvePostTypeForPlatform($platform, $postType, $mediaType);

            $idempotencyKey = $this->makeIdempotencyKey(
                $userId, $platform, $accountId, $contentId, $scheduleAt, $effectivePostType
            );

            $existing = $db->table('publishes')
                ->select('id')
                ->where('user_id', $userId)
                ->where('idempotency_key', $idempotencyKey)
                ->get()->getRowArray();

            if ($existing) {
                continue;
            }

            // ✅ publish meta_json içine effective post type yazalım (ileride lazım olacak)
            $publishMeta = [
                'post_type' => $effectivePostType,
                'source_post_type' => $postType,
            ];

            $db->table('publishes')->insert([
                'user_id'         => $userId,
                'platform'        => $platform,
                'account_id'      => $accountId,
                'content_id'      => $contentId,
                'status'          => 'queued',
                'schedule_at'     => $scheduleAt,
                'remote_id'       => null,
                'meta_json'       => json_encode($publishMeta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'idempotency_key' => $idempotencyKey,
                'created_at'      => $now,
                'updated_at'      => $now,
            ]);
            $publishId = (int) $db->insertID();

            $db->table('jobs')->insert([
                'type'         => 'publish_post',
                'payload_json' => json_encode([
                    'publish_id' => $publishId,
                    'platform'   => $platform,
                    'account_id' => $accountId,
                    'content_id' => $contentId,

                    // ✅ platforma göre normalize edilmiş tip
                    'post_type'  => $effectivePostType,

                    // debug için
                    'source_post_type' => $postType,
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
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
                ->with('error', 'Planlama sırasında hata oluştu.');
        }

        return redirect()->to(site_url('panel/calendar'))
            ->with('success', "Planlandı. Oluşturulan gönderi sayısı: {$createdPublishes}");
    }

    /**
     * ✅ Platforma göre "doğru" post type seçer.
     * Dönüşler:
     * - instagram: post|reels|story
     * - facebook: post|video
     * - youtube: video
     * - (ileride) tiktok: video
     */
    private function resolvePostTypeForPlatform(string $platform, string $requested, ?string $mediaType): string
    {
        $platform = strtolower(trim($platform));
        $requested = strtolower(trim($requested));

        // AUTO modu: medya tipine göre karar ver
        if ($requested === 'auto') {
            if ($platform === 'youtube') return 'video';
            if ($platform === 'tiktok')  return 'video';

            if ($mediaType === 'video') {
                if ($platform === 'instagram') return 'reels';
                if ($platform === 'facebook')  return 'video';
            }

            // image ya da medya yoksa
            if ($platform === 'instagram') return 'post';
            if ($platform === 'facebook')  return 'post';

            return 'post';
        }

        // Manuel seçim: platforma uygun hale getir
        if ($platform === 'youtube') {
            // YouTube reels/story/post yok
            return 'video';
        }

        if ($platform === 'tiktok') {
            return 'video';
        }

        if ($platform === 'facebook') {
            // Facebook'ta reels/story yönetmiyoruz: video varsa video akışına, yoksa post
            if ($requested === 'reels') return ($mediaType === 'video' ? 'video' : 'post');
            if ($requested === 'story') return 'post';
            return 'post';
        }

        // instagram
        if (in_array($requested, ['post','reels','story'], true)) return $requested;

        return 'post';
    }

    private function normalizeDatetime(string $raw): ?string
    {
        $raw = trim($raw);
        if ($raw === '') return null;

        if (preg_match('~^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}$~', $raw)) {
            return $raw;
        }
        if (preg_match('~^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}$~', $raw)) {
            return str_replace('T', ' ', $raw) . ':00';
        }
        if (preg_match('~^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}$~', $raw)) {
            return str_replace('T', ' ', $raw);
        }

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
        string $postType
    ): string {
        $secret = (string) (getenv('IDEMPOTENCY_SECRET') ?: (config('App')->encryptionKey ?? 'otomedya-dev-secret'));

        $payload = implode('|', [
            $userId,
            strtolower($platform),
            $accountId,
            $contentId,
            $scheduleAt,
            $postType,
        ]);

        return hash_hmac('sha256', $payload, $secret);
    }
}
