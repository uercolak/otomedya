<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\LogService;
use App\Services\MetaPublishService;
use App\Services\YouTubePublishService;
use App\Services\TikTokPublishService;
use App\Services\QueueService;
use Config\Database;

class PublishPostHandler implements JobHandlerInterface
{
    private PublishModel $publishes;
    private LogService $logger;
    private MetaPublishService $meta;
    private YouTubePublishService $youtube;

    public function __construct()
    {
        $this->publishes = new PublishModel();
        $this->logger    = new LogService();
        $this->meta      = new MetaPublishService();
        $this->youtube   = new YouTubePublishService();
    }

    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        $db = Database::connect();

        $jobPostType = strtolower(trim((string)($payload['post_type'] ?? 'post')));

        $row = $db->table('publishes p')
            ->select('
                p.id, p.user_id, p.platform, p.account_id, p.content_id, p.status, p.schedule_at, p.remote_id, p.meta_json, p.error,
                c.title as content_title,
                c.base_text as content_text,
                c.media_type as content_media_type,
                c.media_path as content_media_path,
                c.meta_json as content_meta_json,
                sa.id as sa_id,
                sa.platform as sa_platform,
                sa.external_id as sa_external_id,
                sa.meta_page_id as sa_meta_page_id,

                satm.access_token as meta_access_token,

                satg.id as google_token_id,
                satg.access_token as google_access_token,
                satg.refresh_token as google_refresh_token,
                satg.expires_at as google_expires_at
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('social_account_tokens satm', "satm.social_account_id = sa.id AND satm.provider='meta'", 'left')
            ->join('social_account_tokens satg', "satg.social_account_id = sa.id AND satg.provider='google'", 'left')
            ->where('p.id', $publishId)
            ->get()
            ->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);
        }

        $status = (string)($row['status'] ?? '');
        if ($status === PublishModel::STATUS_PUBLISHED) return true;
        if ($status === PublishModel::STATUS_CANCELED)  return true;

        $platform = strtolower(trim((string)($row['platform'] ?? '')));

        $caption   = trim((string)($row['content_text'] ?? ''));
        $mediaType = strtolower(trim((string)($row['content_media_type'] ?? ''))); // image|video|null
        $mediaPath = trim((string)($row['content_media_path'] ?? ''));

        // Meta için URL (IG/FB)
        $mediaUrl = '';
        if ($mediaPath !== '') {
            $mediaUrl = base_url($mediaPath);
            if (!preg_match('~^https?://~i', $mediaUrl)) {
                throw new \RuntimeException('Media URL http/https olmalı: ' . $mediaUrl);
            }
        }

        if ($mediaUrl !== '' && !in_array($mediaType, ['image', 'video'], true)) {
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($ext, ['mp4', 'mov', 'm4v', 'webm'], true) ? 'video' : 'image';
        }

        // publish -> publishing
        $this->publishes->update($publishId, [
            'status' => PublishModel::STATUS_PUBLISHING,
            'error'  => null,
        ]);

        // =========================
        // ✅ TIKTOK (MVP: video + caption)
        // =========================
        if ($platform === 'tiktok') {

            $saId = (int)($row['sa_id'] ?? 0);
            if ($saId <= 0) {
                throw new \RuntimeException('TikTok için social_account (sa_id) bulunamadı.');
            }

            // token'ı ayrı çekiyoruz (meta/google join'larından bağımsız)
            $tok = $db->table('social_account_tokens')
                ->select('access_token, refresh_token, expires_at')
                ->where('social_account_id', $saId)
                ->where('provider', 'tiktok')
                ->orderBy('id', 'DESC')
                ->get()->getRowArray();

            $ttAccessToken = $tok ? trim((string)($tok['access_token'] ?? '')) : '';
            if ($ttAccessToken === '') {
                throw new \RuntimeException('TikTok access_token yok. social_account_tokens(provider=tiktok) kontrol et.');
            }

            if ($mediaType !== 'video' || $mediaPath === '') {
                throw new \RuntimeException('TikTok için şimdilik sadece video destekli. media_type=video ve media_path zorunlu.');
            }

            $absPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($mediaPath, '/\\');
            if (!is_file($absPath) || !is_readable($absPath)) {
                throw new \RuntimeException('TikTok video dosyası okunamadı: ' . $absPath);
            }

            $size = filesize($absPath);
            if ($size === false || (int)$size <= 0) {
                throw new \RuntimeException('TikTok video boyutu okunamadı.');
            }

            // mevcut meta_json varsa merge edelim
            $meta = [];
            if (!empty($row['meta_json'])) {
                $tmp = json_decode((string)$row['meta_json'], true);
                if (is_array($tmp)) $meta = $tmp;
            }

            $tt = new TikTokPublishService();

            // init -> upload_url + publish_id
            try {
                $init = $tt->initVideoPublish($ttAccessToken, ($caption !== '' ? $caption : ' '), (int)$size);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();

                // 401 / invalid token ise refresh'e düş
                if (stripos($msg, 'HTTP=401') !== false || stripos($msg, 'access_token_invalid') !== false) {

                    $queue = new QueueService();
                    $queue->push('refresh_tiktok_token', [
                        'social_account_id' => $saId,
                        'publish_id'        => $publishId,
                    ], date('Y-m-d H:i:s', time() + 2), 90, 3);

                    $this->publishes->update($publishId, [
                        'status' => PublishModel::STATUS_PUBLISHING,
                        'error'  => null,
                    ]);

                    return true;
                }

                throw $e;
            }

            $data = $init['data'] ?? [];

            $uploadUrl      = (string)($data['upload_url'] ?? '');
            $publishTokenId = (string)($data['publish_id'] ?? '');

            if ($uploadUrl === '' || $publishTokenId === '') {
                throw new \RuntimeException('TikTok init başarısız. RESP=' . json_encode($init, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            }

            // upload (burada da token invalid çıkabilir)
            try {
                $tt->uploadToUrl($uploadUrl, $absPath);
            } catch (\Throwable $e) {
                $msg = $e->getMessage();
                if (stripos($msg, 'HTTP=401') !== false || stripos($msg, 'access_token_invalid') !== false) {

                    $queue = new QueueService();
                    $queue->push('refresh_tiktok_token', [
                        'social_account_id' => $saId,
                        'publish_id'        => $publishId,
                    ], date('Y-m-d H:i:s', time() + 2), 90, 3);

                    $this->publishes->update($publishId, [
                        'status' => PublishModel::STATUS_PUBLISHING,
                        'error'  => null,
                    ]);

                    return true;
                }

                throw $e;
            }

            // meta güncelle (merge)
            $meta['tiktok'] = array_merge(($meta['tiktok'] ?? []), [
                'publish_id' => $publishTokenId,
                'status'     => 'UPLOADED',
            ]);

            // status job bas (attempt=0)
            $queue = new QueueService();
            $queue->push('tiktok_publish_status', [
                'publish_id' => $publishId,
                'attempt'    => 0,
            ], date('Y-m-d H:i:s', time() + 10), 90, 30);

            $this->publishes->update($publishId, [
                'status'    => PublishModel::STATUS_PUBLISHING,
                'error'     => null,
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // =========================
        // ✅ YOUTUBE
        // =========================
        if ($platform === 'youtube') {
            throw new \RuntimeException('youtube publish_post ile değil publish_youtube job ile çalışmalı.');
        }

        // =========================
        // ✅ META (INSTAGRAM / FACEBOOK)
        // =========================
        if (!in_array($platform, ['instagram', 'facebook'], true)) {
            throw new \RuntimeException('Desteklenmeyen platform: ' . $platform);
        }

        $pageToken = trim((string)($row['meta_access_token'] ?? ''));
        if ($pageToken === '') {
            throw new \RuntimeException('Meta page token eksik. social_account_tokens(provider=meta).access_token kontrol et.');
        }

        // =========================
        // INSTAGRAM
        // =========================
        if ($platform === 'instagram') {

            $igUserId  = trim((string)($row['sa_external_id'] ?? ''));
            if ($igUserId === '') {
                throw new \RuntimeException('Instagram ig_user_id eksik. social_accounts.external_id kontrol et.');
            }

            if ($mediaUrl === '') {
                throw new \RuntimeException('Instagram için medya zorunlu. content.media_path boş.');
            }

            $postType = $jobPostType;
            if ($postType === 'auto') {
                $postType = ($mediaType === 'video') ? 'reels' : 'post';
            }

            if ($postType === 'reels' && $mediaType !== 'video') $postType = 'post';
            if ($postType === 'story' && $mediaUrl === '') {
                throw new \RuntimeException('Instagram Story için medya zorunlu.');
            }
            if (!in_array($postType, ['post','reels','story'], true)) $postType = 'post';

            $this->logger->event('info', 'queue', 'publish.started', [
                'publish_id' => $publishId,
                'platform'   => 'instagram',
                'ig_user_id' => $igUserId,
                'post_type'  => $postType,
                'media_type' => $mediaType,
            ], $row['user_id'] ?? null);

            $res = $this->meta->publishInstagram([
                'ig_user_id'    => $igUserId,
                'access_token'  => $pageToken,
                'post_type'     => $postType,
                'media_type'    => ($mediaType ?: 'image'),
                'media_url'     => $mediaUrl,
                'caption'       => $caption,
            ]);

            $creationId  = (string)($res['creation_id'] ?? '');
            $publishedId = (string)($res['published_id'] ?? '');
            $statusCode  = (string)($res['status_code'] ?? '');
            $deferred    = !empty($res['deferred']);

            if ($deferred && $creationId !== '' && $publishedId === '') {

                $this->upsertMetaMediaJob($db, [
                    'user_id'           => (int)($row['user_id'] ?? 0),
                    'publish_id'        => $publishId,
                    'social_account_id' => (int)($row['sa_id'] ?? 0),
                    'ig_user_id'        => $igUserId,
                    'page_id'           => (string)($row['sa_meta_page_id'] ?? null),
                    'creation_id'       => $creationId,
                    'type'              => $postType,
                    'media_kind'        => $mediaType,
                    'media_url'         => $mediaUrl,
                    'caption'           => ($caption !== '' ? $caption : null),
                    'status'            => 'processing',
                    'status_code'       => ($statusCode !== '' ? $statusCode : 'IN_PROGRESS'),
                    'attempts'          => 0,
                    'next_retry_at'     => date('Y-m-d H:i:s', time() + 20),
                    'last_error'        => 'Video işleniyor (status=' . ($statusCode ?: 'IN_PROGRESS') . ').',
                    'last_response_json'=> json_encode(($res['raw'] ?? $res), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                $metaJson = [
                    'meta' => [
                        'creation_id' => $creationId,
                        'status'      => ($statusCode !== '' ? $statusCode : 'IN_PROGRESS'),
                        'deferred'    => true,
                    ],
                ];

                $this->publishes->update($publishId, [
                    'status'    => PublishModel::STATUS_PUBLISHING,
                    'error'     => null,
                    'meta_json' => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                return true;
            }

            if ($publishedId === '') {
                throw new \RuntimeException('Meta publish başarısız: published_id dönmedi.');
            }

            $permalink = $this->meta->getInstagramPermalink($publishedId, $pageToken) ?: '';

            $metaJson = [
                'meta' => [
                    'creation_id'  => $creationId,
                    'published_id' => $publishedId,
                    'permalink'    => $permalink,
                ],
            ];

            $this->publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $publishedId,
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // =========================
        // FACEBOOK
        // =========================
        $pageId = trim((string)($row['sa_external_id'] ?? ''));
        if ($pageId === '') $pageId = trim((string)($row['sa_meta_page_id'] ?? ''));
        if ($pageId === '') {
            throw new \RuntimeException('Facebook için page_id eksik. social_accounts.external_id/meta_page_id kontrol et.');
        }

        $fbPostType = $jobPostType; // post|video
        if (!in_array($fbPostType, ['post','video'], true)) $fbPostType = 'post';

        $fb = $this->meta->publishFacebookPage([
            'page_id'      => $pageId,
            'access_token' => $pageToken,
            'message'      => $caption,
            'media_url'    => $mediaUrl,
            'media_type'   => ($mediaUrl !== '' ? ($mediaType ?: 'image') : ''),
            'post_type'    => $fbPostType,
        ]);

        $postId = (string)($fb['published_id'] ?? '');
        if ($postId === '') {
            throw new \RuntimeException('Facebook publish başarısız: id dönmedi.');
        }

        $permalink = $this->meta->getFacebookPermalink($postId, $pageToken) ?: '';

        $metaJson = [
            'meta' => [
                'published_id' => $postId,
                'permalink'    => $permalink,
            ],
        ];

        $this->publishes->update($publishId, [
            'status'       => PublishModel::STATUS_PUBLISHED,
            'remote_id'    => $postId,
            'published_at' => date('Y-m-d H:i:s'),
            'error'        => null,
            'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return true;
    }

    private function upsertMetaMediaJob($db, array $data): void
    {
        if (!$db->tableExists('meta_media_jobs')) return;

        $now = date('Y-m-d H:i:s');
        $creationId = (string)($data['creation_id'] ?? '');
        if ($creationId === '') return;

        $existing = $db->table('meta_media_jobs')->where('creation_id', $creationId)->get()->getRowArray();
        $base = array_merge(['updated_at' => $now], $data);

        if ($existing) {
            unset($base['created_at']);
            $db->table('meta_media_jobs')->where('id', (int)$existing['id'])->update($base);
        } else {
            $base['created_at'] = $now;
            if (!isset($base['attempts'])) $base['attempts'] = 0;
            $db->table('meta_media_jobs')->insert($base);
        }
    }
}
