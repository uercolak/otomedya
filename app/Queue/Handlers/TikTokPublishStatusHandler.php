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

        $db       = Database::connect();
        $publishes = new PublishModel();
        $tt       = new TikTokPublishService();

        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.content_id,p.status,p.meta_json,p.error')
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

        // Zaten published/failed/canceled ise boşuna dönme
        $currentStatus = strtolower((string)($row['status'] ?? ''));
        if (in_array($currentStatus, ['published', 'failed', 'canceled'], true)) {
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

        // ✅ raw response sakla (debug için)
        $meta['tiktok']['last_response'] = $st;

        $data = $st['data'] ?? [];
        if (!is_array($data)) $data = [];

        // bazı cevaplarda farklı key olabilir diye fallback
        $status = (string)($data['status'] ?? $data['publish_status'] ?? '');
        $videoId = (string)($data['video_id'] ?? $data['videoId'] ?? $data['item_id'] ?? '');
        $failReason = (string)($data['fail_reason'] ?? $data['reason'] ?? $data['error_message'] ?? '');

        $statusUpper = strtoupper(trim($status));

        // tamamlandı kabul edeceğimiz statüler
        $doneStatuses = ['PUBLISH_COMPLETE', 'SUCCESS', 'PUBLISHED', 'COMPLETED'];

        // ✅ video_id varsa kesin published
        if ($videoId !== '') {
            $username = (string)($row['sa_username'] ?? '');
            $permalink = ($username !== '')
                ? 'https://www.tiktok.com/@' . rawurlencode($username) . '/video/' . rawurlencode($videoId)
                : null;

            $meta['tiktok']['status'] = $statusUpper !== '' ? $statusUpper : 'SUCCESS';
            $meta['tiktok']['video_id'] = $videoId;
            if ($permalink) $meta['tiktok']['permalink'] = $permalink;

            $publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $videoId,
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // ❌ fail
        if ($failReason !== '' || $statusUpper === 'FAILED') {
            $meta['tiktok']['status'] = $statusUpper !== '' ? $statusUpper : 'FAILED';
            $meta['tiktok']['fail_reason'] = $failReason !== '' ? $failReason : 'TikTok publish failed';

            $publishes->update($publishId, [
                'status'    => PublishModel::STATUS_FAILED,
                'error'     => $meta['tiktok']['fail_reason'],
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // ✅ video_id gelmese bile status "PUBLISH_COMPLETE" ise published'a çevir
        if ($statusUpper !== '' && in_array($statusUpper, $doneStatuses, true)) {
            $meta['tiktok']['status'] = $statusUpper;

            $publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $publishTokenId, // video_id yoksa publish_id yaz
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
                'meta_json'    => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // Hâlâ processing
        $meta['tiktok']['status'] = $statusUpper !== '' ? $statusUpper : 'PROCESSING';

        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        // 10sn sonra tekrar kontrol job’u bas
        $queue = new QueueService();
        $queue->push('tiktok_publish_status', [
            'publish_id' => $publishId,
        ], date('Y-m-d H:i:s', time() + 10), 90, 30);

        return true;
    }
}
