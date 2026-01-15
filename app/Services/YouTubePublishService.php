<?php

namespace App\Services;

class YouTubePublishService
{
    private function httpClient()
    {
        return \Config\Services::curlrequest([
            'timeout'     => 120,
            'http_errors' => false,
        ]);
    }

    public function getValidAccessToken($db, int $socialAccountId, array $hint = []): string
    {
        // DB’den en sağlamı oku
        $row = $db->table('social_account_tokens')
            ->select('id, access_token, refresh_token, expires_at')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'google')
            ->get()->getRowArray();

        $tokenId = (int)($row['id'] ?? ($hint['google_token_id'] ?? 0));
        $access  = (string)($row['access_token'] ?? ($hint['google_access_token'] ?? ''));
        $refresh = (string)($row['refresh_token'] ?? ($hint['google_refresh_token'] ?? ''));
        $expiresAtRaw = (string)($row['expires_at'] ?? ($hint['google_expires_at'] ?? ''));

        // expires_at boşsa erişimi deneriz (bazı projelerde doldurulmayabiliyor)
        $isExpired = false;
        if ($expiresAtRaw !== '') {
            $ts = strtotime($expiresAtRaw);
            if ($ts !== false) {
                // 60sn buffer
                $isExpired = ($ts - 60) <= time();
            }
        }

        if ($access !== '' && !$isExpired) {
            return $access;
        }

        if ($refresh === '') {
            // refresh yoksa eldekini döndürmeye çalış (bazı durumlarda works)
            return $access;
        }

        $clientId     = (string)(getenv('GOOGLE_CLIENT_ID') ?: '');
        $clientSecret = (string)(getenv('GOOGLE_CLIENT_SECRET') ?: '');

        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET eksik (YouTube refresh için gerekli).');
        }

        $client = $this->httpClient();
        $resp = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'client_id'     => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refresh,
                'grant_type'    => 'refresh_token',
            ],
        ]);

        $body = (string)$resp->getBody();
        $json = json_decode($body, true);
        if (!is_array($json)) $json = [];

        if (empty($json['access_token'])) {
            $msg = $json['error_description'] ?? $json['error'] ?? $body ?? 'refresh failed';
            throw new \RuntimeException('YouTube token refresh başarısız: ' . $msg);
        }

        $newAccess = (string)$json['access_token'];
        $expiresIn = (int)($json['expires_in'] ?? 0);
        $newExpiresAt = $expiresIn ? date('Y-m-d H:i:s', time() + $expiresIn) : null;

        // DB update
        if ($tokenId > 0) {
            $db->table('social_account_tokens')
                ->where('id', $tokenId)
                ->update([
                    'access_token' => $newAccess,
                    'expires_at'   => $newExpiresAt,
                    'updated_at'   => date('Y-m-d H:i:s'),
                ]);
        } else {
            // çok uç bir durum: token satırı yoksa insertle
            $db->table('social_account_tokens')->insert([
                'social_account_id' => $socialAccountId,
                'provider'          => 'google',
                'access_token'      => $newAccess,
                'refresh_token'     => $refresh,
                'token_type'        => 'Bearer',
                'expires_at'        => $newExpiresAt,
                'scope'             => null,
                'meta_json'         => null,
                'created_at'        => date('Y-m-d H:i:s'),
                'updated_at'        => date('Y-m-d H:i:s'),
            ]);
        }

        return $newAccess;
    }

    /**
     * ✅ YouTube videos.insert (multipart upload)
     * Dönen JSON içinde "id" (videoId) bekliyoruz.
     */
    public function uploadVideoMultipart(array $p): array
    {
        $accessToken = (string)($p['access_token'] ?? '');
        $filePath    = (string)($p['file_path'] ?? '');
        $title       = (string)($p['title'] ?? 'Video');
        $description = (string)($p['description'] ?? '');
        $privacy     = (string)($p['privacyStatus'] ?? 'unlisted');

        if ($accessToken === '') throw new \RuntimeException('YouTube access_token boş.');
        if ($filePath === '' || !is_file($filePath)) throw new \RuntimeException('Video dosyası yok: ' . $filePath);

        $mime = mime_content_type($filePath) ?: 'video/mp4';
        $videoBytes = file_get_contents($filePath);
        if ($videoBytes === false) {
            throw new \RuntimeException('Video dosyası okunamadı: ' . $filePath);
        }

        $meta = [
            'snippet' => [
                'title'       => $title,
                'description' => $description,
                'categoryId'  => '22', // People & Blogs (default)
            ],
            'status' => [
                'privacyStatus' => $privacy, // public|private|unlisted
                'selfDeclaredMadeForKids' => false,
            ],
        ];

        $boundary = '===============yt_' . bin2hex(random_bytes(8));
        $eol = "\r\n";

        $body =
            "--{$boundary}{$eol}" .
            "Content-Type: application/json; charset=UTF-8{$eol}{$eol}" .
            json_encode($meta, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . "{$eol}" .
            "--{$boundary}{$eol}" .
            "Content-Type: {$mime}{$eol}" .
            "Content-Transfer-Encoding: binary{$eol}{$eol}" .
            $videoBytes . "{$eol}" .
            "--{$boundary}--{$eol}";

        $client = $this->httpClient();
        $resp = $client->request('POST', 'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=multipart&part=snippet,status', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'multipart/related; boundary=' . $boundary,
            ],
            'body' => $body,
        ]);

        $respBody = (string)$resp->getBody();
        $json = json_decode($respBody, true);

        if (!is_array($json)) {
            throw new \RuntimeException('YouTube upload response parse edilemedi. HTTP=' . $resp->getStatusCode() . ' BODY=' . $respBody);
        }

        if (!empty($json['error'])) {
            $msg = $json['error']['message'] ?? 'YouTube API error';
            throw new \RuntimeException('YouTube API hata: ' . $msg);
        }

        return $json;
    }
}
