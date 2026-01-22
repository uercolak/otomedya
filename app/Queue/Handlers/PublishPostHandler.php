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

        $attempt     = (int)($payload['attempt'] ?? 0);
        $maxAttempts = 3;

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
            ->get()->getRowArray();

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

        // media url (Meta için)
        $mediaUrl = '';
        if ($mediaPath !== '') {
            $mediaUrl = base_url($mediaPath);
            if (!preg_match('~^https?://~i', $mediaUrl)) {
                throw new \RuntimeException('Media URL http/https olmalı: ' . $mediaUrl);
            }
        }

        // mediaType auto-detect
        if ($mediaUrl !== '' && !in_array($mediaType, ['image', 'video'], true)) {
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($ext, ['mp4', 'mov', 'm4v', 'webm'], true) ? 'video' : 'image';
        }

        // publishing
        $this->publishes->update($publishId, [
            'status' => PublishModel::STATUS_PUBLISHING,
            'error'  => null,
        ]);

        // =========================
        // ✅ TIKTOK
        // =========================
        if ($platform === 'tiktok') {

            $saId = (int)($row['sa_id'] ?? 0);
            if ($saId <= 0) {
                throw new \RuntimeException('TikTok social_account_id bulunamadı (sa_id boş).');
            }

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

            $tt = new TikTokPublishService();

            try {
                // init → upload_url + publish_id
                $init = $tt->initVideoPublish($ttAccessToken, ($caption !== '' ? $caption : ' '), (int)$size);
                $data = $init['data'] ?? [];

                $uploadUrl      = (string)($data['upload_url'] ?? '');
                $publishTokenId = (string)($data['publish_id'] ?? '');

                if ($uploadUrl === '' || $publishTokenId === '') {
                    throw new \RuntimeException('TikTok init başarısız. RESP=' . json_encode($init));
                }

                // upload
                $tt->uploadToUrl($uploadUrl, $absPath);

                $meta = [
                    'tiktok' => [
                        'publish_id' => $publishTokenId,
                        'status'     => 'UPLOADED',
                    ],
                ];

                $this->publishes->update($publishId, [
                    'status'    => PublishModel::STATUS_PUBLISHING,
                    'error'     => null,
                    'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                // status job
                $queue = new QueueService();
                $queue->push('tiktok_publish_status', [
                    'publish_id' => $publishId,
                    'attempt'    => 0,
                ], date('Y-m-d H:i:s', time() + 10), 90, 30);

                return true;

            } catch (\Throwable $e) {

                $msg = $e->getMessage();

                // 401 / access_token_invalid → refresh + retry publish (max 3)
                if ((stripos($msg, 'HTTP=401') !== false || stripos($msg, 'access_token_invalid') !== false) && $attempt < $maxAttempts) {
                    $queue = new QueueService();

                    $queue->push('refresh_tiktok_token', [
                        'social_account_id' => $saId,
                        'publish_id'        => $publishId,
                    ], date('Y-m-d H:i:s', time() + 2), 90, 3);

                    // publish_post tekrar
                    $queue->push('publish_post', [
                        'publish_id' => $publishId,
                        'post_type'  => $jobPostType,
                        'attempt'    => $attempt + 1,
                    ], date('Y-m-d H:i:s', time() + 8), 90, 3);

                    // publishing bırak
                    $this->publishes->update($publishId, [
                        'status' => PublishModel::STATUS_PUBLISHING,
                        'error'  => null,
                    ]);

                    return true;
                }

                // diğer hatalar: fail
                $this->publishes->update($publishId, [
                    'status' => PublishModel::STATUS_FAILED,
                    'error'  => 'TikTok publish error: ' . mb_substr($msg, 0, 700),
                ]);

                return true;
            }
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

        // INSTAGRAM
        if ($platform === 'instagram') {
            $igUserId = trim((string)($row['sa_external_id'] ?? ''));
            if ($igUserId === '') {
                throw new \RuntimeException('Instagram ig_user_id eksik. social_accounts.external_id kontrol et.');
            }

            if ($mediaUrl === '') {
                throw new \RuntimeException('Instagram için medya zorunlu. content.media_path boş.');
            }

            $postType = $jobPostType;
            if ($postType === 'auto') $postType = ($mediaType === 'video') ? 'reels' : 'post';
            if ($postType === 'reels' && $mediaType !== 'video') $postType = 'post';
            if (!in_array($postType, ['post','reels','story'], true)) $postType = 'post';

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
                // (senin mevcut upsertMetaMediaJob logic’in burada kalsın)
                // ...
                $this->publishes->update($publishId, [
                    'status'    => PublishModel::STATUS_PUBLISHING,
                    'error'     => null,
                    'meta_json' => json_encode([
                        'meta' => [
                            'creation_id' => $creationId,
                            'status'      => ($statusCode !== '' ? $statusCode : 'IN_PROGRESS'),
                            'deferred'    => true,
                        ],
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);
                return true;
            }

            if ($publishedId === '') {
                throw new \RuntimeException('Meta publish başarısız: published_id dönmedi.');
            }

            $permalink = $this->meta->getInstagramPermalink($publishedId, $pageToken) ?: '';

            $this->publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $publishedId,
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode([
                    'meta' => [
                        'creation_id'  => $creationId,
                        'published_id' => $publishedId,
                        'permalink'    => $permalink,
                    ],
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // FACEBOOK
        $pageId = trim((string)($row['sa_external_id'] ?? ''));
        if ($pageId === '') $pageId = trim((string)($row['sa_meta_page_id'] ?? ''));
        if ($pageId === '') {
            throw new \RuntimeException('Facebook için page_id eksik. social_accounts.external_id/meta_page_id kontrol et.');
        }

        $fbPostType = $jobPostType;
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

        $this->publishes->update($publishId, [
            'status'       => PublishModel::STATUS_PUBLISHED,
            'remote_id'    => $postId,
            'published_at' => date('Y-m-d H:i:s'),
            'error'        => null,
            'meta_json'    => json_encode([
                'meta' => [
                    'published_id' => $postId,
                    'permalink'    => $permalink,
                ],
            ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return true;
    }
}
