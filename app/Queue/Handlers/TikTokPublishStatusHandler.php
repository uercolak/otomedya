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
        $maxAttempts = 30;

        $db       = Database::connect();
        $publishes = new PublishModel();
        $tt       = new TikTokPublishService();

        // Publish + account username çek (token JOIN YOK!)
        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.status,p.meta_json,p.error,p.remote_id')
            ->select('sa.username as sa_username')
            ->join('social_accounts sa', 'sa.id=p.account_id', 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);
        }

        if (strtolower((string)$row['platform']) !== 'tiktok') {
            return true;
        }

        // zaten published/failed ise dokunma
        $curStatus = (string)($row['status'] ?? '');
        if ($curStatus === PublishModel::STATUS_PUBLISHED || $curStatus === PublishModel::STATUS_FAILED) {
            return true;
        }

        $accountId = (int)($row['account_id'] ?? 0);
        if ($accountId <= 0) {
            throw new \RuntimeException('account_id eksik.');
        }

        // ✅ TikTok token'ı EN GÜNCEL satırdan çek (ORDER BY id DESC)
        $tok = $db->table('social_account_tokens')
            ->select('access_token, refresh_token, expires_at, updated_at')
            ->where('social_account_id', $accountId)
            ->where('provider', 'tiktok')
            ->orderBy('id', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        $accessToken = $tok ? trim((string)($tok['access_token'] ?? '')) : '';
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
        try {
            $st   = $tt->fetchPublishStatus($accessToken, $publishTokenId);
        } catch (\Throwable $e) {
            // ✅ 401 access_token_invalid ise token refresh job'u tetikle ve retry et
            $msg = $e->getMessage();

            if (stripos($msg, 'HTTP=401') !== false || stripos($msg, 'access_token_invalid') !== false) {

                // publish kaydına bilgilendirici meta yaz
                $meta['tiktok']['status'] = 'TOKEN_INVALID';
                $meta['tiktok']['last_error'] = 'access_token_invalid → refresh tetiklendi';

                $publishes->update($publishId, [
                    'status'    => PublishModel::STATUS_PUBLISHING,
                    'error'     => null,
                    'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                // refresh job (senin handler'ın var: refresh_tiktok_token)
                $queue = new QueueService();
                $queue->push('refresh_tiktok_token', [
                    'social_account_id' => $accountId,
                    'publish_id'        => $publishId, // opsiyonel: refresh sonrası status job tekrar atsın diye
                ], date('Y-m-d H:i:s', time() + 2), 80, 3);

                // status check job'u biraz gecikmeli tekrar koy
                if ($attempt < $maxAttempts) {
                    $queue->push('tiktok_publish_status', [
                        'publish_id' => $publishId,
                        'attempt'    => $attempt + 1,
                    ], date('Y-m-d H:i:s', time() + 12), 90, 3);
                }

                return true;
            }

            // farklı hata: olduğu gibi fırlat (worker loglasın)
            throw $e;
        }

        $data = $st['data'] ?? [];

        $status     = (string)($data['status'] ?? '');
        $failReason = (string)($data['fail_reason'] ?? '');

        // TikTok bazı yerlerde id dizisi döndürüyor
        $postIds = $data['publicaly_available_post_id'] ?? $data['publicly_available_post_id'] ?? [];
        $postId  = '';

        if (is_array($postIds) && !empty($postIds)) {
            $postId = (string)($postIds[0] ?? '');
        }

        // bazı implementasyonlarda video_id diye de gelebiliyor
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

        // 3) Complete ama id yok → moderasyon / private / gecikme olabilir → retry
        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if ($attempt >= $maxAttempts) {
            return true;
        }

        $queue = new QueueService();
        $queue->push('tiktok_publish_status', [
            'publish_id' => $publishId,
            'attempt'    => $attempt + 1,
        ], date('Y-m-d H:i:s', time() + 10), 90, 3);

        return true;
    }
}
