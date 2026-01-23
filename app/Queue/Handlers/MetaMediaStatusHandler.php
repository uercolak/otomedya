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
        $publishId  = (int)($payload['publish_id'] ?? 0);
        $creationId = (string)($payload['creation_id'] ?? '');
        $attempt    = (int)($payload['attempt'] ?? 0);

        if ($publishId <= 0 || $creationId === '') {
            throw new \RuntimeException('publish_id ve creation_id zorunlu.');
        }

        $maxAttempts = 30; // 30 * 20sn = ~10 dk
        $now = date('Y-m-d H:i:s');

        $db = Database::connect();

        // publish + token + ig_user_id al
        $row = $db->table('publishes p')
            ->select('p.id,p.user_id,p.platform,p.account_id,p.status,p.meta_json,p.error,p.remote_id')
            ->select('sa.external_id as ig_user_id')
            ->select('satm.access_token as meta_access_token')
            ->join('social_accounts sa', 'sa.id=p.account_id', 'left')
            ->join('social_account_tokens satm', "satm.social_account_id = sa.id AND satm.provider='meta'", 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Publish bulunamadı: #' . $publishId);
        }

        $platform = strtolower((string)($row['platform'] ?? ''));
        if (!in_array($platform, ['instagram'], true)) {
            return true; // şimdilik sadece IG polling
        }

        // Zaten bitti mi?
        $curStatus = (string)($row['status'] ?? '');
        if ($curStatus === PublishModel::STATUS_PUBLISHED || $curStatus === PublishModel::STATUS_FAILED) {
            return true;
        }

        $accessToken = trim((string)($row['meta_access_token'] ?? ''));
        $igUserId    = trim((string)($row['ig_user_id'] ?? ''));

        if ($accessToken === '' || $igUserId === '') {
            // Token/ig_user_id yoksa fail
            (new PublishModel())->update($publishId, [
                'status'     => PublishModel::STATUS_FAILED,
                'error'      => 'Meta access_token veya ig_user_id eksik.',
                'updated_at' => $now,
            ]);
            return true;
        }

        $meta = new MetaPublishService();
        $publishes = new PublishModel();

        // 1) container status
        $st = $meta->getInstagramContainerStatus($creationId, $accessToken);
        $code = strtoupper((string)($st['status_code'] ?? ''));

        // meta_json güncelle
        $metaJson = [];
        if (!empty($row['meta_json'])) {
            $tmp = json_decode((string)$row['meta_json'], true);
            if (is_array($tmp)) $metaJson = $tmp;
        }
        $metaJson['meta']['creation_id'] = $creationId;
        $metaJson['meta']['status_code'] = $code ?: 'IN_PROGRESS';

        // 2) FINISHED → publish et
        if ($code === 'FINISHED') {
            $pub = $meta->publishInstagramContainer($igUserId, $creationId, $accessToken);
            $publishedId = (string)($pub['published_id'] ?? '');

            if ($publishedId === '') {
                // publish çağrısı id dönmediyse retry
                return $this->requeue($publishes, $publishId, $attempt, $maxAttempts, $metaJson, 'media_publish id dönmedi');
            }

            $permalink = $meta->getInstagramPermalink($publishedId, $accessToken) ?: '';

            $metaJson['meta']['published_id'] = $publishedId;
            if ($permalink !== '') $metaJson['meta']['permalink'] = $permalink;

            $publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'remote_id'    => $publishedId,
                'published_at' => $now,
                'error'        => null,
                'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at'   => $now,
            ]);

            return true;
        }

        // 3) ERROR / EXPIRED gibi durumlarda fail
        if (in_array($code, ['ERROR', 'EXPIRED'], true)) {
            $publishes->update($publishId, [
                'status'     => PublishModel::STATUS_FAILED,
                'error'      => 'Instagram container status: ' . ($code ?: 'ERROR'),
                'meta_json'  => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'updated_at' => $now,
            ]);
            return true;
        }

        // 4) hala processing → requeue
        return $this->requeue($publishes, $publishId, $attempt, $maxAttempts, $metaJson, 'Video işleniyor ('.$code.')');
    }

    private function requeue(PublishModel $publishes, int $publishId, int $attempt, int $maxAttempts, array $metaJson, string $note): bool
    {
        $now = date('Y-m-d H:i:s');

        $publishes->update($publishId, [
            'status'     => PublishModel::STATUS_PUBLISHING,
            'error'      => $note,
            'meta_json'  => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at' => $now,
        ]);

        if ($attempt >= $maxAttempts) {
            // Süre doldu → fail yapmak istersen burada değiştir
            return true;
        }

        $queue = new QueueService();
        $runAt = date('Y-m-d H:i:s', time() + 20);

        $queue->push('meta_media_status', [
            'publish_id'  => $publishId,
            'creation_id' => (string)($metaJson['meta']['creation_id'] ?? ''),
            'attempt'     => $attempt + 1,
        ], $runAt, 90, 30);

        return true;
    }
}
