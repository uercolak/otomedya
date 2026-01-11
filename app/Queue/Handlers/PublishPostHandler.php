<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\LogService;
use App\Services\MetaPublishService;
use Config\Database;

class PublishPostHandler implements JobHandlerInterface
{
    private PublishModel $publishes;
    private LogService $logger;
    private MetaPublishService $meta;

    public function __construct()
    {
        $this->publishes = new PublishModel();
        $this->logger    = new LogService();
        $this->meta      = new MetaPublishService();
    }

    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        $db = Database::connect();

        $row = $db->table('publishes p')
            ->select('
                p.id, p.user_id, p.platform, p.account_id, p.content_id, p.status, p.schedule_at, p.remote_id, p.meta_json,
                c.title as content_title,
                c.base_text as content_text,
                c.media_type as content_media_type,
                c.media_path as content_media_path,
                c.meta_json as content_meta_json,
                sa.id as sa_id,
                sa.platform as sa_platform,
                sa.external_id as sa_external_id,
                sa.meta_page_id as sa_meta_page_id,
                sat.access_token as page_access_token
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('social_account_tokens sat', "sat.social_account_id = sa.id AND sat.provider='meta'", 'left')
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
        if (!in_array($platform, ['instagram', 'facebook'], true)) {
            throw new \RuntimeException('Desteklenmeyen platform: ' . $platform);
        }

        // Ortak alanlar
        $caption   = trim((string)($row['content_text'] ?? ''));
        $mediaType = strtolower(trim((string)($row['content_media_type'] ?? '')));
        $mediaPath = trim((string)($row['content_media_path'] ?? ''));
        $pageToken = trim((string)($row['page_access_token'] ?? ''));

        if ($pageToken === '') {
            throw new \RuntimeException(
                'Meta page token eksik. social_account_tokens(access_token) kontrol et.'
            );
        }

        // Media URL (facebook için opsiyonel, instagram için zorunlu)
        $mediaUrl = '';
        if ($mediaPath !== '') {
            $mediaUrl = base_url($mediaPath);
            if (!preg_match('~^https?://~i', $mediaUrl)) {
                throw new \RuntimeException('Media URL http/https olmalı: ' . $mediaUrl);
            }
        }

        // media_type fallback (uzantıdan)
        if ($mediaUrl !== '' && !in_array($mediaType, ['image', 'video'], true)) {
            $ext = strtolower(pathinfo(parse_url($mediaUrl, PHP_URL_PATH) ?? '', PATHINFO_EXTENSION));
            $mediaType = in_array($ext, ['mp4', 'mov', 'm4v'], true) ? 'video' : 'image';
        }

        // publish "publishing" (işe başladık)
        $this->publishes->update($publishId, [
            'status' => PublishModel::STATUS_PUBLISHING,
            'error'  => null,
        ]);

        // =========================
        // INSTAGRAM
        // =========================
        if ($platform === 'instagram') {

            $igUserId  = trim((string)($row['sa_external_id'] ?? ''));

            if ($igUserId === '') {
                throw new \RuntimeException(
                    'Instagram hesabında ig_user_id eksik. social_accounts.external_id kontrol et.'
                );
            }

            if ($mediaUrl === '') {
                throw new \RuntimeException('Instagram için medya zorunlu. content.media_path boş.');
            }

            $postType = 'post'; // post|reels|story
            $contentMeta = [];
            if (!empty($row['content_meta_json'])) {
                $tmp = json_decode((string)$row['content_meta_json'], true);
                if (is_array($tmp)) $contentMeta = $tmp;
            }
            if (!empty($contentMeta['post_type'])) {
                $pt = strtolower(trim((string)$contentMeta['post_type']));
                if (in_array($pt, ['post','reels','story'], true)) $postType = $pt;
            }

            $this->logger->event('info', 'queue', 'publish.started', [
                'publish_id' => $publishId,
                'platform'   => $platform,
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

            // ✅ video/reels işleniyor → meta_media_jobs'e yaz, publish "publishing" kalsın, job başarılı bitsin.
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

                $this->logger->event('info', 'queue', 'publish.deferred', [
                    'publish_id'  => $publishId,
                    'creation_id' => $creationId,
                    'status_code' => $statusCode,
                ], $row['user_id'] ?? null);

                return true;
            }

            // normal flow: published
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

            $this->logger->event('info', 'queue', 'publish.succeeded', [
                'publish_id'  => $publishId,
                'remote_id'   => $publishedId,
                'creation_id' => $creationId,
                'permalink'   => $permalink,
            ], $row['user_id'] ?? null);

            return true;
        }

        // =========================
        // FACEBOOK
        // =========================
        // Facebook için medya opsiyonel: media yoksa text-only post atar.
        $pageId = trim((string)($row['sa_external_id'] ?? ''));
        if ($pageId === '') $pageId = trim((string)($row['sa_meta_page_id'] ?? ''));
        if ($pageId === '') {
            throw new \RuntimeException('Facebook için page_id eksik. social_accounts.external_id/meta_page_id kontrol et.');
        }

        $this->logger->event('info', 'queue', 'publish.started', [
            'publish_id' => $publishId,
            'platform'   => $platform,
            'page_id'    => $pageId,
            'media_type' => ($mediaUrl !== '' ? $mediaType : 'none'),
        ], $row['user_id'] ?? null);

        $fb = $this->meta->publishFacebookPage([
            'page_id'      => $pageId,
            'access_token' => $pageToken,
            'message'      => $caption,
            'media_url'    => $mediaUrl, // '' olabilir
            'media_type'   => ($mediaUrl !== '' ? ($mediaType ?: 'image') : ''),
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

        $this->logger->event('info', 'queue', 'publish.succeeded', [
            'publish_id' => $publishId,
            'remote_id'  => $postId,
            'permalink'  => $permalink,
        ], $row['user_id'] ?? null);

        return true;
    }

    private function upsertMetaMediaJob($db, array $data): void
    {
        // tablo yoksa sessiz geç
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
