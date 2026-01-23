<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\MetaPublishService;
use App\Services\QueueService;
use Config\Database;

class MetaMediaStatusHandler implements JobHandlerInterface
{
    public function handle(array $payload): bool
    {
        $publishId   = (int)($payload['publish_id'] ?? 0);
        $attempt     = (int)($payload['attempt'] ?? 0);
        $maxAttempts = 30;          // 30 deneme
        $intervalSec = 20;          // 20 saniye aralık

        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        $db = Database::connect();

        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.status,p.meta_json,p.error,p.remote_id')
            ->select('sa.external_id as ig_user_id')
            ->select('satm.access_token as meta_access_token')
            ->join('social_accounts sa', 'sa.id=p.account_id', 'left')
            ->join('social_account_tokens satm', "satm.social_account_id=sa.id AND satm.provider='meta'", 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);
        }

        // sadece instagram deferred için
        if (strtolower((string)$row['platform']) !== 'instagram') {
            return true;
        }

        // meta_json içinden creation_id al
        $meta = [];
        if (!empty($row['meta_json'])) {
            $tmp = json_decode((string)$row['meta_json'], true);
            if (is_array($tmp)) $meta = $tmp;
        }

        $creationId = (string)($payload['creation_id'] ?? ($meta['meta']['creation_id'] ?? ''));
        if ($creationId === '') {
            throw new \RuntimeException('creation_id bulunamadı (payload/meta_json).');
        }

        $igUserId = trim((string)($row['ig_user_id'] ?? ''));
        if ($igUserId === '') {
            throw new \RuntimeException('Instagram ig_user_id eksik. social_accounts.external_id kontrol et.');
        }

        $accessToken = trim((string)($row['meta_access_token'] ?? ''));
        if ($accessToken === '') {
            throw new \RuntimeException('Meta access_token eksik (provider=meta).');
        }

        $publishes = new PublishModel();
        $metaSvc   = new MetaPublishService();

        // 1) container status kontrol et
        $st = $metaSvc->getInstagramContainerStatus($creationId, $accessToken);
        $statusCode = strtoupper(trim((string)($st['status_code'] ?? '')));
        $errorMsg   = trim((string)($st['error'] ?? ''));

        // meta_json güncelle
        $meta['meta']['creation_id'] = $creationId;
        $meta['meta']['status'] = $statusCode !== '' ? $statusCode : ($meta['meta']['status'] ?? 'IN_PROGRESS');
        $meta['meta']['deferred'] = true;

        // meta_media_jobs tablosu varsa güncelle (opsiyonel)
        if ($db->tableExists('meta_media_jobs')) {
            $db->table('meta_media_jobs')->where('creation_id', $creationId)->update([
                'status'        => ($statusCode === 'FINISHED') ? 'ready' : 'processing',
                'status_code'   => ($statusCode !== '' ? $statusCode : null),
                'attempts'      => $attempt,
                'next_retry_at' => date('Y-m-d H:i:s', time() + $intervalSec),
                'last_error'    => ($statusCode === 'FINISHED') ? null : ('Video işleniyor (status=' . ($statusCode ?: 'IN_PROGRESS') . ')'),
                'updated_at'    => date('Y-m-d H:i:s'),
                'last_response_json' => json_encode($st['raw'] ?? $st, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
        }

        // 2) ERROR durumları → fail
        if ($errorMsg !== '' || in_array($statusCode, ['ERROR', 'FAILED', 'EXPIRED'], true)) {
            $publishes->update($publishId, [
                'status'    => PublishModel::STATUS_FAILED,
                'error'     => $errorMsg ?: ('Meta container failed: ' . ($statusCode ?: 'UNKNOWN')),
                'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            ]);
            return true;
        }

        // 3) FINISHED → media_publish yap → published_id al
        if ($statusCode === 'FINISHED' || $statusCode === 'READY') {
            $pub = $metaSvc->publishInstagramContainer($igUserId, $creationId, $accessToken);
            $publishedId = (string)($pub['published_id'] ?? '');

            if ($publishedId === '') {
                // publish aşaması da deferred olabilir ama şimdilik fail etmeyelim, tekrar dene
                $statusCode = 'PUBLISH_PENDING';
            } else {
                $permalink = $metaSvc->getInstagramPermalink($publishedId, $accessToken) ?: '';

                $meta['meta']['published_id'] = $publishedId;
                $meta['meta']['permalink']    = $permalink;

                $publishes->update($publishId, [
                    'status'       => PublishModel::STATUS_PUBLISHED,
                    'remote_id'    => $publishedId,
                    'published_at' => date('Y-m-d H:i:s'),
                    'error'        => null,
                    'meta_json'    => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                ]);

                return true;
            }
        }

        // 4) hâlâ processing → retry
        $publishes->update($publishId, [
            'status'    => PublishModel::STATUS_PUBLISHING,
            'error'     => null,
            'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        if ($attempt >= $maxAttempts) {
            $publishes->update($publishId, [
                'status' => PublishModel::STATUS_FAILED,
                'error'  => 'Meta video processing timeout (' . $maxAttempts . ' deneme)',
            ]);
            return true;
        }

        $queue = new QueueService();
        $queue->push('meta_media_status', [
            'publish_id'  => $publishId,
            'creation_id' => $creationId,
            'attempt'     => $attempt + 1,
        ], date('Y-m-d H:i:s', time() + $intervalSec), 90, 30);

        return true;
    }
}
