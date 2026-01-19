<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\TikTokPublishService;
use Config\Database;

class TikTokPublishStatusHandler implements JobHandlerInterface
{
    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) throw new \RuntimeException('publish_id zorunlu.');

        $db = Database::connect();
        $publishes = new PublishModel();
        $tt = new TikTokPublishService();

        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.content_id,p.status,p.meta_json,p.error')
            ->select('sa.username as sa_username')
            ->select('sat.access_token as tiktok_access_token')
            ->join('social_accounts sa', 'sa.id=p.account_id', 'left')
            ->join('social_account_tokens sat', "sat.social_account_id=sa.id AND sat.provider='tiktok'", 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);
        if (strtolower((string)$row['platform']) !== 'tiktok') return true;

        $accessToken = trim((string)($row['tiktok_access_token'] ?? ''));
        if ($accessToken === '') throw new \RuntimeException('TikTok access_token yok (social_account_tokens provider=tiktok).');

        $meta = [];
        if (!empty($row['meta_json'])) {
            $tmp = json_decode((string)$row['meta_json'], true);
            if (is_array($tmp)) $meta = $tmp;
        }

        $publishTokenId = (string)($meta['tiktok']['publish_id'] ?? '');
        if ($publishTokenId === '') throw new \RuntimeException('TikTok publish_id meta_json içinde yok.');

        $st = $tt->fetchPublishStatus($accessToken, $publishTokenId);
        $data = $st['data'] ?? [];

        $status = (string)($data['status'] ?? '');
        $videoId = (string)($data['video_id'] ?? '');
        $failReason = (string)($data['fail_reason'] ?? '');

        // başarılıysa video_id gelir
        if ($videoId !== '') {
            $username = (string)($row['sa_username'] ?? '');
            $permalink = ($username !== '')
                ? 'https://www.tiktok.com/@' . rawurlencode($username) . '/video/' . rawurlencode($videoId)
                : null;

            $meta['tiktok']['status'] = $status ?: 'SUCCESS';
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

        // 실패
        if ($status === 'FAILED' || $failReason !== '') {
            $meta['tiktok']['status'] = $status ?: 'FAILED';
            $meta['tiktok']['fail_reason'] = $failReason;

            $publishes->update($publishId, [
                'status'    => PublishModel::STATUS_FAILED,
                'error'     => $failReason !== '' ? $failReason : 'TikTok publish failed',
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);

            return true;
        }

        // hâlâ processing → publish kaydı publishing kalsın, worker bu job’u bitirsin.
        $meta['tiktok']['status'] = $status ?: 'PROCESSING';

        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        // Job success: retry’ı biz zamanlayacağız (PublishPostHandler’dan)
        return true;
    }
}
