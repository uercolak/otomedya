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
        $maxAttempts = 60; // 10 sn aralıkla ~10 dk (istersen artırırız)

        $db = Database::connect();
        $publishes = new PublishModel();
        $tt = new TikTokPublishService();

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

        // meta_json
        $meta = [];
        if (!empty($row['meta_json'])) {
            $tmp = json_decode((string)$row['meta_json'], true);
            if (is_array($tmp)) $meta = $tmp;
        }

        $publishTokenId = (string)($meta['tiktok']['publish_id'] ?? '');
        if ($publishTokenId === '') {
            throw new \RuntimeException('TikTok publish_id meta_json içinde yok.');
        }

        // ✅ STATUS FETCH (401 yakala → refresh kuyruğu)
        try {
            $st = $tt->fetchPublishStatus($accessToken, $publishTokenId);
        } catch (\Throwable $e) {

            $msg = $e->getMessage();

            // 401 / access_token_invalid yakala
            if (stripos($msg, 'HTTP=401') !== false || stripos($msg, 'access_token_invalid') !== false) {
                $queue = new QueueService();

                // Refresh job (senin handler'ın adı: refresh_tiktok_token varsayıyorum)
                $queue->push('refresh_tiktok_token', [
                    'social_account_id' => (int)($row['account_id'] ?? 0),
                    'publish_id'        => $publishId,
                ], date('Y-m-d H:i:s', time() + 2), 90, 3);

                // Publish status tekrar dene
                $queue->push('tiktok_publish_status', [
                    'publish_id' => $publishId,
                    'attempt'    => $attempt + 1,
                ], date('Y-m-d H:i:s', time() + 6), 90, 3);

                // publish kaydını “publishing” bırak, hata yazma
                $publishes->update($publishId, [
                    'status' => PublishModel::STATUS_PUBLISHING,
                    'error'  => null,
                ]);

                return true;
            }

            // başka hata: fail
            $publishes->update($publishId, [
                'status' => PublishModel::STATUS_FAILED,
                'error'  => 'TikTok status fetch error: ' . mb_substr($msg, 0, 500),
            ]);

            return true;
        }

        $data = $st['data'] ?? [];
        $status     = strtoupper(trim((string)($data['status'] ?? '')));
        $failReason = trim((string)($data['fail_reason'] ?? ''));

        // TikTok dokümanda typo var: publicaly_available_post_id
        $postIds = $data['publicaly_available_post_id'] ?? $data['publicly_available_post_id'] ?? [];
        $postId  = '';

        if (is_array($postIds) && !empty($postIds)) {
            $postId = (string)($postIds[0] ?? '');
        }

        if ($postId === '') {
            $postId = (string)($data['video_id'] ?? '');
        }

        // meta güncelle
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

        // ✅ 2) post_id geldi → published
        if ($postId !== '') {
            $username = (string)($row['sa_username'] ?? '');
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

        // ✅ 3) Status SUCCESS ama post_id yok → YİNE DE “published” yap (senin yaşadığın durum)
        if ($status === 'SUCCESS' || $status === 'COMPLETED') {
            $meta['tiktok']['published_without_post_id'] = true;

            $publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $publishTokenId, // en azından referans
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // 4) Hâlâ processing → retry
        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if ($attempt >= $maxAttempts) {
            // Deneme bitti: publishing bırakalım (istersen failed yapabiliriz)
            return true;
        }

        $queue = new QueueService();
        $runAt = date('Y-m-d H:i:s', time() + 10);

        $queue->push('tiktok_publish_status', [
            'publish_id' => $publishId,
            'attempt'    => $attempt + 1,
        ], $runAt, 90, 3);

        return true;
    }
}
