<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use Config\Database;

class PublishYouTubeHandler implements JobHandlerInterface
{
    private PublishModel $publishes;

    public function __construct()
    {
        $this->publishes = new PublishModel();
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
                sat.access_token as access_token,
                sat.refresh_token as refresh_token,
                sat.expires_at as expires_at
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            // provider adın sende farklıysa: 'google' yerine 'youtube' yazabilirsin.
            ->join('social_account_tokens sat', "sat.social_account_id = sa.id AND sat.provider='google'", 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);

        $status = (string)($row['status'] ?? '');
        if ($status === PublishModel::STATUS_PUBLISHED) return true;
        if ($status === PublishModel::STATUS_CANCELED)  return true;

        $platform = strtolower(trim((string)($row['platform'] ?? '')));
        if ($platform !== 'youtube') {
            throw new \RuntimeException('Desteklenmeyen platform (youtube handler): ' . $platform);
        }

        $mediaType = strtolower(trim((string)($row['content_media_type'] ?? '')));
        $mediaPath = trim((string)($row['content_media_path'] ?? ''));
        if ($mediaType !== 'video') {
            throw new \RuntimeException('YouTube için video zorunlu. content.media_type=' . ($mediaType ?: 'null'));
        }
        if ($mediaPath === '') {
            throw new \RuntimeException('YouTube için media_path boş.');
        }

        $absPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($mediaPath, '/\\');
        if (!is_file($absPath)) {
            throw new \RuntimeException('Video dosyası bulunamadı: ' . $absPath);
        }

        $contentMeta = [];
        if (!empty($row['content_meta_json'])) {
            $tmp = json_decode((string)$row['content_meta_json'], true);
            if (is_array($tmp)) $contentMeta = $tmp;
        }

        $ytTitle = trim((string)($contentMeta['youtube']['title'] ?? ''));
        $privacy = strtolower(trim((string)($contentMeta['youtube']['privacy'] ?? 'public')));
        if ($ytTitle === '') {
            // fallback: content title
            $ytTitle = trim((string)($row['content_title'] ?? ''));
        }
        if ($ytTitle === '') {
            throw new \RuntimeException('YouTube başlığı boş. (planner youtube_title gerekli)');
        }
        if (!in_array($privacy, ['public','unlisted','private'], true)) $privacy = 'public';

        $desc = trim((string)($row['content_text'] ?? ''));

        $accessToken  = trim((string)($row['access_token'] ?? ''));
        $refreshToken = trim((string)($row['refresh_token'] ?? ''));

        if ($accessToken === '' && $refreshToken === '') {
            throw new \RuntimeException('YouTube token yok. social_account_tokens(provider=google) kontrol et.');
        }

        // publishing
        $this->publishes->update($publishId, [
            'status' => PublishModel::STATUS_PUBLISHING,
            'error'  => null,
        ]);

        // Access token gerekiyorsa refresh et
        $accessToken = $this->ensureAccessToken(
            $db,
            (int)$row['sa_id'],
            $accessToken,
            $refreshToken,
            $row['expires_at'] ?? null
        );

        // 1) Resumable session başlat
        $initUrl = 'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status';

        $initBody = json_encode([
            'snippet' => [
                'title'       => $ytTitle,
                'description' => $desc,
            ],
            'status' => [
                'privacyStatus' => $privacy,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        [$location, $initResp] = $this->startResumableSession($initUrl, $accessToken, $initBody);
        if ($location === '') {
            throw new \RuntimeException('YouTube resumable session açılamadı: ' . $initResp);
        }

        // 2) Upload (PUT)
        $videoId = $this->uploadResumable($location, $absPath);

        if ($videoId === '') {
            throw new \RuntimeException('YouTube upload başarısız: video id dönmedi.');
        }

        $permalink = 'https://youtu.be/' . $videoId;

        $metaJson = [
            'meta' => [
                'published_id' => $videoId,
                'permalink'    => $permalink,
                'privacy'      => $privacy,
            ],
        ];

        $this->publishes->update($publishId, [
            'status'       => PublishModel::STATUS_PUBLISHED,
            'remote_id'    => $videoId,
            'published_at' => date('Y-m-d H:i:s'),
            'error'        => null,
            'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return true;
    }

    private function ensureAccessToken($db, int $socialAccountId, string $accessToken, string $refreshToken, ?string $expiresAt): string
    {
        $now = time();
        $exp = $expiresAt ? strtotime($expiresAt) : 0;

        if ($accessToken !== '' && $exp && $exp > ($now + 60)) {
            return $accessToken;
        }

        $clientId     = (string)(getenv('GOOGLE_CLIENT_ID') ?: '');
        $clientSecret = (string)(getenv('GOOGLE_CLIENT_SECRET') ?: '');

        if ($refreshToken === '') {
            throw new \RuntimeException('YouTube refresh_token yok. Hesabı yeniden bağlamak gerekir.');
        }
        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET env eksik.');
        }

        $tokenUrl = 'https://oauth2.googleapis.com/token';
        $postFields = http_build_query([
            'client_id'     => $clientId,
            'client_secret' => $clientSecret,
            'refresh_token' => $refreshToken,
            'grant_type'    => 'refresh_token',
        ]);

        $ch = curl_init($tokenUrl);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException('YT TOKEN refresh failed HTTP=' . $http . ' ERR=' . $err . ' BODY=' . (string)$body);
        }

        $json = json_decode((string)$body, true);
        $newAccess = trim((string)($json['access_token'] ?? ''));
        $expiresIn = (int)($json['expires_in'] ?? 0);

        if ($newAccess === '') {
            throw new \RuntimeException('YT TOKEN refresh: access_token boş. BODY=' . (string)$body);
        }

        $newExpiresAt = $expiresIn > 0 ? date('Y-m-d H:i:s', $now + $expiresIn - 60) : null;

        $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'google')
            ->update([
                'access_token' => $newAccess,
                'expires_at'   => $newExpiresAt,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return $newAccess;
    }


    private function startResumableSession(string $url, string $accessToken, string $jsonBody): array
    {
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json; charset=UTF-8',
            'X-Upload-Content-Type: video/*',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $jsonBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        ]);

        $resp = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException('YT INIT failed HTTP=' . $http . ' ERR=' . $err . ' RESP=' . (string)$resp);
        }

        $rawHeaders = substr((string)$resp, 0, $hdrSize);
        $body = substr((string)$resp, $hdrSize);

        $location = '';
        foreach (explode("\n", $rawHeaders) as $line) {
            if (stripos($line, 'Location:') === 0) {
                $location = trim(substr($line, strlen('Location:')));
                break;
            }
        }

        return [$location, $body];
    }

    private function uploadResumable(string $uploadUrl, string $filePath): string
    {
        $fp = fopen($filePath, 'rb');
        if (!$fp) throw new \RuntimeException('Video açılamadı: ' . $filePath);

        $size = filesize($filePath);
        if ($size === false) $size = 0;

        $headers = [
            'Content-Type: video/*',
            'Content-Length: ' . $size,
            'Expect:',
        ];

        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => 'PUT',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_INFILE => $fp,
            CURLOPT_INFILESIZE => $size,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TCP_KEEPALIVE => 1,
        ]);

        $resp = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);
        fclose($fp);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException('YT UPLOAD failed HTTP=' . $http . ' ERR=' . $err . ' RESP=' . (string)$resp);
        }

        $json = json_decode((string)$resp, true);
        return trim((string)($json['id'] ?? ''));
    }
}
