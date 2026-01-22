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

        // kaçıncı deneme? (default 0)
        $attempt = (int)($payload['attempt'] ?? 0);
        $maxAttempts = 30; // toplam deneme

        $db = Database::connect();
        $publishes = new PublishModel();
        $tt = new TikTokPublishService();

        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.status,p.meta_json,p.error,p.remote_id')
            ->select('sa.username as sa_username')
            ->select('sat.access_token as tiktok_access_token')
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

        // zaten published/failed ise işleme gerek yok
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

        $publishTokenId = (string)($meta['tiktok']['publish_id'] ?? '');
        if ($publishTokenId === '') {
            throw new \RuntimeException('TikTok publish_id meta_json içinde yok.');
        }

        // status fetch
        $st = $tt->fetchPublishStatus($accessToken, $publishTokenId);
        $data = $st['data'] ?? [];

        $status     = (string)($data['status'] ?? '');
        $failReason = (string)($data['fail_reason'] ?? '');

        // TikTok dokümanında post_id alanı dizi olarak gelebiliyor (typo ile)
        $postIds = $data['publicaly_available_post_id'] ?? $data['publicly_available_post_id'] ?? [];
        $postId  = '';

        if (is_array($postIds) && !empty($postIds)) {
            $postId = (string)($postIds[0] ?? '');
        }

        // bazı implementasyonlarda video_id diye de gelebiliyor (fallback)
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

        // 2) post_id geldi → published
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

        // 3) publish complete ama id yok → moderation bekliyor olabilir → retry
        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if ($attempt >= $maxAttempts) {
            // denemeler bitti: burada "failed" yapmıyoruz, publishing bırakıyoruz.
            // istersen "failed" + açıklama da verebiliriz.
            return true;
        }

        $queue = new QueueService();

        // sabit 10 sn (istersen artan backoff yaparız)
        $runAt = date('Y-m-d H:i:s', time() + 10);

        $queue->push('tiktok_publish_status', [
            'publish_id' => $publishId,
            'attempt'    => $attempt + 1,
        ], $runAt, 90, 3);

        return true;
    }
}
