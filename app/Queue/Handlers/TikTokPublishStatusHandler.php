<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\TikTokPublishService;
use App\Services\QueueService;
use Config\Database;

class TikTokPublishStatusHandler implements JobHandlerInterface
{
    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        $attempt     = (int)($payload['attempt'] ?? 0);
        $maxAttempts = 60; // ~10 dk

        $db        = Database::connect();
        $publishes = new PublishModel();
        $tt        = new TikTokPublishService();

        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.status,p.meta_json,p.error,p.remote_id')
            ->select('sa.username as sa_username')
            ->select('sat.access_token as tiktok_access_token')
            ->select('sat.refresh_token as tiktok_refresh_token')
            ->join('social_accounts sa', 'sa.id=p.account_id', 'left')
            ->join('social_account_tokens sat', "sat.social_account_id=sa.id AND sat.provider='tiktok'", 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);
        }

        if (strtolower((string)$row['platform']) !== 'tiktok') {
            return true;
        }

        $curStatus = (string)($row['status'] ?? '');
        if ($curStatus === PublishModel::STATUS_PUBLISHED || $curStatus === PublishModel::STATUS_FAILED) {
            return true;
        }

        $accessToken = trim((string)($row['tiktok_access_token'] ?? ''));
        if ($accessToken === '') {
            throw new \RuntimeException('TikTok access_token yok (social_account_tokens provider=tiktok).');
        }

        // meta_json oku
        $meta = [];
        if (!empty($row['meta_json'])) {
            $tmp = json_decode((string)$row['meta_json'], true);
            if (is_array($tmp)) $meta = $tmp;
        }

        // ✅ publish_id önce payload'dan (sen zaten yolluyorsun)
        $publishTokenId = (string)($payload['tiktok_publish_id'] ?? '');
        if ($publishTokenId === '') {
            $publishTokenId = (string)($meta['tiktok']['publish_id'] ?? '');
        }
        if ($publishTokenId === '') {
            throw new \RuntimeException('TikTok publish_id bulunamadı (payload/meta_json).');
        }

        // status fetch (401 -> refresh kuyruğu)
        try {
            $st = $tt->fetchPublishStatus($accessToken, $publishTokenId);
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (stripos($msg, 'HTTP=401') !== false || stripos($msg, 'access_token_invalid') !== false) {
                $queue = new QueueService();

                // refresh’i spamlamayalım
                $queue->push('refresh_tiktok_token', [
                    'social_account_id' => (int)($row['account_id'] ?? 0),
                    'publish_id'        => $publishId,
                ], date('Y-m-d H:i:s', time() + 2), 90, 3);

                // sonra tekrar dene (küçük backoff)
                $delay = ($attempt <= 3) ? 6 : 10;

                $queue->push('tiktok_publish_status', [
                    'publish_id'        => $publishId,
                    'tiktok_publish_id' => $publishTokenId,
                    'attempt'           => $attempt + 1,
                ], date('Y-m-d H:i:s', time() + $delay), 90, 3);

                $publishes->update($publishId, [
                    'status' => PublishModel::STATUS_PUBLISHING,
                    'error'  => null,
                    'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                return true;
            }

            $publishes->update($publishId, [
                'status' => PublishModel::STATUS_FAILED,
                'error'  => 'TikTok status fetch error: ' . mb_substr($msg, 0, 500),
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // ✅ TikTok response normalize
        $data = $st['data'] ?? $st ?? [];
        if (!is_array($data)) $data = [];

        $status     = strtoupper(trim((string)($data['status'] ?? '')));
        $failReason = trim((string)($data['fail_reason'] ?? ''));

        // post id / video id parse (farklı isimler dönebiliyor)
        $postIds = $data['publicaly_available_post_id'] ?? $data['publicly_available_post_id'] ?? null;

        $postId = '';
        if (is_array($postIds) && !empty($postIds)) {
            $postId = (string)($postIds[0] ?? '');
        }
        if ($postId === '') {
            $postId = (string)($data['video_id'] ?? '');
        }

        // meta güncelle
        $meta['tiktok']['publish_id'] = $publishTokenId;
        $meta['tiktok']['status'] = $status !== '' ? $status : ($meta['tiktok']['status'] ?? 'PROCESSING');
        if ($failReason !== '') $meta['tiktok']['fail_reason'] = $failReason;

        // 1) FAILED
        if ($status === 'FAILED' || $failReason !== '') {
            $publishes->update($publishId, [
                'status'    => PublishModel::STATUS_FAILED,
                'error'     => $failReason !== '' ? $failReason : 'TikTok publish failed',
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            return true;
        }

        // 2) post_id geldi => published
        if ($postId !== '') {
            $username  = (string)($row['sa_username'] ?? '');
            $permalink = ($username !== '')
                ? 'https://www.tiktok.com/@' . rawurlencode($username) . '/video/' . rawurlencode($postId)
                : null;

            $meta['tiktok']['video_id'] = $postId;
            if ($permalink) $meta['tiktok']['permalink'] = $permalink;

            $publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $postId,
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // 3) Done ama post_id yok => published say (mevcut mantığın)
        $doneStatuses = ['SUCCESS', 'COMPLETED', 'PUBLISH_COMPLETE', 'PUBLISHED'];
        if (in_array($status, $doneStatuses, true)) {
            $meta['tiktok']['published_without_post_id'] = true;

            $publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $publishTokenId,
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // 4) hâlâ processing => retry
        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if ($attempt >= $maxAttempts) {
            return true; // istersen burada failed
        }

        $queue = new QueueService();
        $delay = ($attempt <= 10) ? 10 : 15;

        $queue->push('tiktok_publish_status', [
            'publish_id'        => $publishId,
            'tiktok_publish_id' => $publishTokenId,
            'attempt'           => $attempt + 1,
        ], date('Y-m-d H:i:s', time() + $delay), 90, 3);

        return true;
    }
}
