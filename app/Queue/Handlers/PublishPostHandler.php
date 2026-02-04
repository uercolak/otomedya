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
    private TikTokPublishService $tiktok;

    public function __construct()
    {
        $this->publishes = new PublishModel();
        $this->logger    = new LogService();
        $this->meta      = new MetaPublishService();
        $this->youtube   = new YouTubePublishService();
        $this->tiktok    = new TikTokPublishService();
    }

    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        $db  = Database::connect();
        $now = date('Y-m-d H:i:s');

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
                sa.username as sa_username,
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

        // ✅ settings: payload > publish.meta_json > content.meta_json
        $settings = $this->resolveSettings($payload, $row);

        // (Eski sistemle uyumluluk) job payload post_type varsa en sonda fallback
        $legacyPostType = strtolower(trim((string)($payload['post_type'] ?? '')));

        $caption   = trim((string)($row['content_text'] ?? ''));
        $mediaType = strtolower(trim((string)($row['content_media_type'] ?? ''))); // image|video|null
        $mediaPath = trim((string)($row['content_media_path'] ?? ''));

        // medya URL (Meta için)
        $mediaUrl = '';
        if ($mediaPath !== '') {
            $mediaUrl = base_url($mediaPath);
            if (!preg_match('~^https?://~i', $mediaUrl)) {
                $this->fail($publishId, 'Media URL http/https olmalı.', $now);
                return true;
            }
        }

        // mediaType fallback (dosya uzantısından)
        if ($mediaUrl !== '' && !in_array($mediaType, ['image', 'video'], true)) {
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($ext, ['mp4', 'mov', 'm4v', 'webm'], true) ? 'video' : 'image';
        }

        // publish = publishing
        $this->publishes->update($publishId, [
            'status'     => PublishModel::STATUS_PUBLISHING,
            'error'      => null,
            'updated_at' => $now,
        ]);

        try {
            // =========================
            // ✅ TIKTOK
            // =========================
            if ($platform === 'tiktok') {

                $tok = $db->table('social_account_tokens')
                    ->select('access_token, refresh_token, expires_at')
                    ->where('social_account_id', (int)($row['sa_id'] ?? 0))
                    ->where('provider', 'tiktok')
                    ->orderBy('id', 'DESC')
                    ->limit(1)
                    ->get()->getRowArray();

                $ttAccessToken = $tok ? trim((string)($tok['access_token'] ?? '')) : '';
                if ($ttAccessToken === '') {
                    throw new \RuntimeException('TikTok access_token yok.');
                }

                if ($mediaType !== 'video' || $mediaPath === '') {
                    throw new \RuntimeException('TikTok için video zorunlu.');
                }

                $absPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($mediaPath, '/\\');
                if (!is_file($absPath) || !is_readable($absPath)) {
                    throw new \RuntimeException('TikTok video dosyası okunamadı.');
                }

                $size = filesize($absPath);
                if ($size === false || (int)$size <= 0) {
                    throw new \RuntimeException('TikTok video boyutu okunamadı.');
                }

                // ✅ settings → tiktok options
                $tt = (array)($settings['tiktok'] ?? []);
                $ttPrivacy        = strtolower(trim((string)($tt['privacy'] ?? 'public')));
                $ttAllowComments  = (bool)($tt['allow_comments'] ?? true);
                $ttAllowDuet      = (bool)($tt['allow_duet'] ?? true);
                $ttAllowStitch    = (bool)($tt['allow_stitch'] ?? true);

                // init → upload_url + publish_id
                // Not: TikTokPublishService initVideoPublish imzası senin servisine göre değişebilir.
                // Şimdilik caption + size zorunlu; settings'i servisin destekliyorsa ekle.
                $init = $this->tiktok->initVideoPublish(
                    $ttAccessToken,
                    ($caption !== '' ? $caption : ' '),
                    (int)$size,
                    [
                        'privacy'         => $ttPrivacy,
                        'allow_comments'  => $ttAllowComments,
                        'allow_duet'      => $ttAllowDuet,
                        'allow_stitch'    => $ttAllowStitch,
                    ]
                );

                $data = $init['data'] ?? [];
                $uploadUrl       = (string)($data['upload_url'] ?? '');
                $tiktokPublishId = (string)($data['publish_id'] ?? '');

                if ($uploadUrl === '' || $tiktokPublishId === '') {
                    throw new \RuntimeException('TikTok init başarısız.');
                }

                // upload
                $this->tiktok->uploadToUrl($uploadUrl, $absPath);

                // meta_json set (settings dahil sakla)
                $meta = [
                    'settings' => $settings,
                    'tiktok' => [
                        'publish_id' => $tiktokPublishId,
                        'status'     => 'UPLOADED',
                    ],
                ];

                $this->publishes->update($publishId, [
                    'status'     => PublishModel::STATUS_PUBLISHING,
                    'error'      => null,
                    'meta_json'  => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at' => $now,
                ]);

                // status job
                $queue = new QueueService();
                $queue->push(
                    'tiktok_publish_status',
                    [
                        'publish_id'        => $publishId,
                        'tiktok_publish_id' => $tiktokPublishId,
                        'attempt'           => 0,
                    ],
                    date('Y-m-d H:i:s', time() + 10),
                    90,
                    30
                );

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
                throw new \RuntimeException('Meta page token eksik.');
            }

            // =========================
            // INSTAGRAM
            // =========================
            if ($platform === 'instagram') {

                $igUserId  = trim((string)($row['sa_external_id'] ?? ''));
                if ($igUserId === '') {
                    throw new \RuntimeException('Instagram ig_user_id eksik.');
                }

                if ($mediaUrl === '') {
                    throw new \RuntimeException('Instagram için medya zorunlu.');
                }

                $igSet = (array)($settings['instagram'] ?? []);
                $postType = strtolower(trim((string)($igSet['post_type'] ?? 'auto')));

                // legacy fallback
                if ($postType === '' && $legacyPostType !== '') $postType = $legacyPostType;

                if ($postType === 'auto') {
                    $postType = ($mediaType === 'video') ? 'reels' : 'post';
                }
                if ($postType === 'reels' && $mediaType !== 'video') $postType = 'post';
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

                    // ✅ settings’i de yolla (servis kullanmıyorsa görmezden gelir)
                    'settings'      => $settings,
                ]);

                $creationId  = (string)($res['creation_id'] ?? '');
                $publishedId = (string)($res['published_id'] ?? '');
                $statusCode  = (string)($res['status_code'] ?? '');
                $deferred    = !empty($res['deferred']);

                // deferred → polling
                if ($deferred && $creationId !== '' && $publishedId === '') {

                    $metaJson = [
                        'settings' => $settings,
                        'meta' => [
                            'creation_id' => $creationId,
                            'status'      => ($statusCode !== '' ? $statusCode : 'IN_PROGRESS'),
                            'deferred'    => true,
                        ],
                    ];

                    $this->publishes->update($publishId, [
                        'status'     => PublishModel::STATUS_PUBLISHING,
                        'error'      => null,
                        'meta_json'  => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at' => $now,
                    ]);

                    // meta_media_jobs upsert (varsa)
                    try {
                        $this->upsertMetaMediaJob($db, [
                            'user_id'            => (int)($row['user_id'] ?? 0),
                            'publish_id'         => $publishId,
                            'social_account_id'  => (int)($row['sa_id'] ?? 0),
                            'ig_user_id'         => $igUserId,
                            'page_id'            => (string)($row['sa_meta_page_id'] ?? null),
                            'creation_id'        => $creationId,
                            'type'               => $postType,
                            'media_kind'         => $mediaType,
                            'media_url'          => $mediaUrl,
                            'caption'            => ($caption !== '' ? $caption : null),
                            'status'             => 'processing',
                            'status_code'        => ($statusCode !== '' ? $statusCode : 'IN_PROGRESS'),
                            'attempts'           => 0,
                            'next_retry_at'      => date('Y-m-d H:i:s', time() + 20),
                            'last_error'         => 'Video işleniyor.',
                            'last_response_json' => json_encode(($res['raw'] ?? $res), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        ]);
                    } catch (\Throwable $e) {
                        // best-effort
                    }

                    $queue = new QueueService();
                    $queue->push(
                        'meta_media_status',
                        [
                            'publish_id'  => $publishId,
                            'creation_id' => $creationId,
                            'platform'    => 'instagram',
                            'attempt'     => 0,
                        ],
                        date('Y-m-d H:i:s', time() + 20),
                        90,
                        30
                    );

                    return true;
                }

                if ($publishedId === '') {
                    throw new \RuntimeException('Meta publish başarısız: published_id dönmedi.');
                }

                $permalink = $this->meta->getInstagramPermalink($publishedId, $pageToken) ?: '';

                $metaJson = [
                    'settings' => $settings,
                    'meta' => [
                        'creation_id'  => $creationId,
                        'published_id' => $publishedId,
                        'permalink'    => $permalink,
                    ],
                ];

                $this->publishes->update($publishId, [
                    'status'       => PublishModel::STATUS_PUBLISHED,
                    'remote_id'    => $publishedId,
                    'published_at' => $now,
                    'error'        => null,
                    'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'updated_at'   => $now,
                ]);

                return true;
            }

            // =========================
            // FACEBOOK
            // =========================
            $pageId = trim((string)($row['sa_external_id'] ?? ''));
            if ($pageId === '') $pageId = trim((string)($row['sa_meta_page_id'] ?? ''));
            if ($pageId === '') {
                throw new \RuntimeException('Facebook için page_id eksik.');
            }

            $fbSet = (array)($settings['facebook'] ?? []);
            $fbPrivacy = strtolower(trim((string)($fbSet['privacy'] ?? 'public')));
            if (!in_array($fbPrivacy, ['public','page'], true)) $fbPrivacy = 'public';
            $fbAllowComments = (bool)($fbSet['allow_comments'] ?? true);

            $fbPostType = 'post';
            if ($mediaType === 'video') $fbPostType = 'video';

            $fb = $this->meta->publishFacebookPage([
                'page_id'      => $pageId,
                'access_token' => $pageToken,
                'message'      => $caption,
                'media_url'    => $mediaUrl,
                'media_type'   => ($mediaUrl !== '' ? ($mediaType ?: 'image') : ''),
                'post_type'    => $fbPostType,

                // ✅ settings geç
                'privacy'      => $fbPrivacy,
                'allow_comments' => $fbAllowComments,
                'settings'     => $settings,
            ]);

            $postId = (string)($fb['published_id'] ?? '');
            if ($postId === '') {
                throw new \RuntimeException('Facebook publish başarısız: id dönmedi.');
            }

            $permalink = $this->meta->getFacebookPermalink($postId, $pageToken) ?: '';

            $metaJson = [
                'settings' => $settings,
                'meta' => [
                    'published_id' => $postId,
                    'permalink'    => $permalink,
                ],
            ];

            $this->publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $postId,
                'published_at' => $now,
                'error'        => null,
                'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at'   => $now,
            ]);

            return true;

        } catch (\Throwable $e) {
            $this->fail($publishId, $e->getMessage(), $now);
            return true; // job fail değil publish fail (UI net)
        }
    }

    /**
     * ✅ settings resolve: payload > publish.meta_json > content.meta_json
     */
    private function resolveSettings(array $payload, array $row): array
    {
        $out = [];

        // 1) payload
        if (!empty($payload['settings']) && is_array($payload['settings'])) {
            $out = $payload['settings'];
        }

        // 2) publish.meta_json
        if (empty($out)) {
            $pj = $this->jsonDecodeArray($row['meta_json'] ?? null);
            if (!empty($pj['settings']) && is_array($pj['settings'])) {
                $out = $pj['settings'];
            }
        }

        // 3) content.meta_json
        if (empty($out)) {
            $cj = $this->jsonDecodeArray($row['content_meta_json'] ?? null);
            if (!empty($cj['settings']) && is_array($cj['settings'])) {
                $out = $cj['settings'];
            }
        }

        return is_array($out) ? $out : [];
    }

    private function jsonDecodeArray($raw): array
    {
        if (!$raw) return [];
        if (is_array($raw)) return $raw;
        $raw = trim((string)$raw);
        if ($raw === '') return [];
        $arr = json_decode($raw, true);
        return is_array($arr) ? $arr : [];
    }

    private function fail(int $publishId, string $message, string $now): void
    {
        $this->publishes->update($publishId, [
            'status'     => PublishModel::STATUS_FAILED,
            'error'      => mb_substr($message, 0, 2000),
            'updated_at' => $now,
        ]);

        log_message('error', '[PublishPostHandler] publish_id={id} failed err={err}', [
            'id'  => $publishId,
            'err' => $message,
        ]);
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

            $ok = $db->table('meta_media_jobs')->insert($base);
            if ($ok === false) {
                $err = $db->error();
                throw new \RuntimeException('meta_media_jobs insert failed: ' . json_encode($err));
            }
        }
    }
}
