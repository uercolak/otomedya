<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\LogService;
use Config\Database;

class PublishYouTubeHandler implements JobHandlerInterface
{
    private PublishModel $publishes;
    private LogService $logger;

    public function __construct()
    {
        $this->publishes = new PublishModel();
        $this->logger    = new LogService();
    }

    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        $db = Database::connect();

        // Not: provider adı sende farklıysa (google/youtube) aşağıdan değiştir.
        $tokenProvider = 'google';

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
                sat.access_token as access_token,
                sat.refresh_token as refresh_token,
                sat.expires_at as expires_at
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('social_account_tokens sat', "sat.social_account_id = sa.id AND sat.provider='{$tokenProvider}'", 'left')
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
        if ($platform !== 'youtube') {
            throw new \RuntimeException('Yanlış handler: platform=' . $platform);
        }

        // Zorunlular
        $accessToken  = trim((string)($row['access_token'] ?? ''));
        $refreshToken = trim((string)($row['refresh_token'] ?? ''));
        $expiresAt    = trim((string)($row['expires_at'] ?? ''));

        if ($accessToken === '' && $refreshToken === '') {
            throw new \RuntimeException('YouTube token eksik. social_account_tokens kontrol et.');
        }

        // içerik
        $desc     = trim((string)($row['content_text'] ?? ''));
        $mediaType = strtolower(trim((string)($row['content_media_type'] ?? '')));
        $mediaPath = trim((string)($row['content_media_path'] ?? ''));

        if ($mediaType !== 'video' || $mediaPath === '') {
            throw new \RuntimeException('YouTube için video zorunlu. content.media_path boş ya da video değil.');
        }

        $absPath = FCPATH . ltrim($mediaPath, '/');
        if (!is_file($absPath)) {
            throw new \RuntimeException('Video dosyası bulunamadı: ' . $absPath);
        }

        // content_meta_json içinden youtube ayarları
        $contentMeta = [];
        if (!empty($row['content_meta_json'])) {
            $tmp = json_decode((string)$row['content_meta_json'], true);
            if (is_array($tmp)) $contentMeta = $tmp;
        }

        $ytTitle = trim((string)($contentMeta['youtube']['title'] ?? ''));
        $privacy = strtolower(trim((string)($contentMeta['youtube']['privacy'] ?? 'public')));
        if (!in_array($privacy, ['public','unlisted','private'], true)) $privacy = 'public';

        if ($ytTitle === '') {
            // fallback: content title
            $ytTitle = trim((string)($row['content_title'] ?? ''));
        }
        if ($ytTitle === '') {
            throw new \RuntimeException('YouTube başlık boş. Planner youtube_title zorunlu olmalı.');
        }

        // publish "publishing"
        $this->publishes->update($publishId, [
            'status' => PublishModel::STATUS_PUBLISHING,
            'error'  => null,
        ]);

        // Token yenile (expires_at varsa ve geçmişse)
        $accessToken = $this->ensureAccessToken($db, (int)$row['sa_id'], $accessToken, $refreshToken, $expiresAt, $tokenProvider);

        $this->logger->event('info', 'queue', 'youtube.publish.started', [
            'publish_id' => $publishId,
            'title'      => $ytTitle,
            'privacy'    => $privacy,
        ], $row['user_id'] ?? null);

        // Upload
        $videoId = $this->uploadMultipartVideo($accessToken, $absPath, [
            'title'       => $ytTitle,
            'description' => $desc,
            'privacy'     => $privacy,
        ]);

        if ($videoId === '') {
            throw new \RuntimeException('YouTube upload başarısız: videoId dönmedi.');
        }

        $permalink = 'https://www.youtube.com/watch?v=' . $videoId;

        $metaJson = [
            'meta' => [
                'published_id' => $videoId,
                'permalink'    => $permalink,
            ],
        ];

        $this->publishes->update($publishId, [
            'status'       => PublishModel::STATUS_PUBLISHED,
            'remote_id'    => $videoId,
            'published_at' => date('Y-m-d H:i:s'),
            'error'        => null,
            'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        $this->logger->event('info', 'queue', 'youtube.publish.succeeded', [
            'publish_id' => $publishId,
            'video_id'   => $videoId,
        ], $row['user_id'] ?? null);

        return true;
    }

    private function ensureAccessToken($db, int $socialAccountId, string $accessToken, string $refreshToken, string $expiresAt, string $provider): string
    {
        // expires_at boşsa dokunma (bazı projelerde kullanılmıyor)
        if ($refreshToken === '' || $expiresAt === '') {
            return $accessToken;
        }

        $expTs = strtotime($expiresAt);
        if ($expTs !== false && $expTs > time() + 60) {
            return $accessToken; // hala geçerli
        }

        // refresh
        $clientId     = (string)(getenv('GOOGLE_CLIENT_ID') ?: '');
        $clientSecret = (string)(getenv('GOOGLE_CLIENT_SECRET') ?: '');

        if ($clientId === '' || $clientSecret === '') {
            // .env'de var diye varsayıyoruz, yoksa hata verelim
            throw new \RuntimeException('GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET .env eksik.');
        }

        $post = http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        $ch = curl_init('https://oauth2.googleapis.com/token');
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $post,
            CURLOPT_HTTPHEADER     => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $body = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException('Token refresh başarısız. HTTP=' . $http . ' ERR=' . $err . ' BODY=' . (string)$body);
        }

        $json = json_decode((string)$body, true);
        if (!is_array($json) || empty($json['access_token'])) {
            throw new \RuntimeException('Token refresh parse başarısız: ' . (string)$body);
        }

        $newToken = (string)$json['access_token'];
        $expiresIn = (int)($json['expires_in'] ?? 3600);
        $newExp = date('Y-m-d H:i:s', time() + max(60, $expiresIn - 30));

        // DB güncelle
        $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', $provider)
            ->update([
                'access_token' => $newToken,
                'expires_at'   => $newExp,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return $newToken;
    }

    private function uploadMultipartVideo(string $accessToken, string $absPath, array $meta): string
    {
        $boundary = '----otomedya_' . bin2hex(random_bytes(12));
        $snippet = [
            'title'       => (string)($meta['title'] ?? ''),
            'description' => (string)($meta['description'] ?? ''),
        ];
        $status = [
            'privacyStatus' => (string)($meta['privacy'] ?? 'public'),
        ];

        $metaJson = json_encode([
            'snippet' => $snippet,
            'status'  => $status,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $videoBin = file_get_contents($absPath);
        if ($videoBin === false) {
            throw new \RuntimeException('Video okunamadı: ' . $absPath);
        }

        $eol = "\r\n";
        $body =
            '--' . $boundary . $eol .
            'Content-Type: application/json; charset=UTF-8' . $eol . $eol .
            $metaJson . $eol .
            '--' . $boundary . $eol .
            'Content-Type: video/*' . $eol .
            'Content-Transfer-Encoding: binary' . $eol . $eol .
            $videoBin . $eol .
            '--' . $boundary . '--' . $eol;

        $url = 'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=multipart&part=snippet,status';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $body,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: multipart/related; boundary=' . $boundary,
                'Content-Length: ' . strlen($body),
            ],
            CURLOPT_TIMEOUT        => 300,
        ]);

        $resp = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException('YouTube upload failed. HTTP=' . $http . ' ERR=' . $err . ' BODY=' . (string)$resp);
        }

        $json = json_decode((string)$resp, true);
        if (!is_array($json)) {
            throw new \RuntimeException('YouTube upload response parse failed: ' . (string)$resp);
        }

        return (string)($json['id'] ?? '');
    }
}
