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

    private function dec(?string $cipher): string
    {
        $cipher = ($cipher ?? '');
        if ($cipher === '') return '';
        $enc = \Config\Services::encrypter();
        try {
            return (string)$enc->decrypt(base64_decode($cipher, true) ?: '');
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function enc(?string $plain): ?string
    {
        $plain = ($plain ?? '');
        if ($plain === '') return null;
        $enc = \Config\Services::encrypter();
        return base64_encode($enc->encrypt($plain));
    }

    public function getValidAccessToken($db, int $socialAccountId): string
    {
        $row = $db->table('social_account_tokens')
            ->select('id, access_token, refresh_token, expires_at')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'google')
            ->get()->getRowArray();

        if (!$row) throw new \RuntimeException('YouTube token kaydı bulunamadı.');

        $tokenId = (int)$row['id'];
        $access  = $this->dec($row['access_token'] ?? null);
        $refresh = $this->dec($row['refresh_token'] ?? null);
        $expiresAtRaw = (string)($row['expires_at'] ?? '');

        $isExpired = true;

        if ($expiresAtRaw !== '') {
            $ts = strtotime($expiresAtRaw);
            if ($ts !== false) {
                $isExpired = ($ts - 60) <= time();
            }
        }

        if ($access !== '' && !$isExpired) return $access;

        if ($refresh === '') return $access;

        $clientId     = (string)(getenv('GOOGLE_CLIENT_ID') ?: '');
        $clientSecret = (string)(getenv('GOOGLE_CLIENT_SECRET') ?: '');
        if ($clientId === '' || $clientSecret === '') {
            throw new \RuntimeException('GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET eksik (refresh için gerekli).');
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

        $json = json_decode((string)$resp->getBody(), true);
        if (!is_array($json)) $json = [];

        if (empty($json['access_token'])) {
            $msg = $json['error_description'] ?? $json['error'] ?? 'refresh failed';
            throw new \RuntimeException('YouTube token refresh başarısız: ' . $msg);
        }

        $newAccess = (string)$json['access_token'];
        $expiresIn = (int)($json['expires_in'] ?? 0);
        $newExpiresAt = $expiresIn ? date('Y-m-d H:i:s', time() + $expiresIn) : null;

        $db->table('social_account_tokens')
            ->where('id', $tokenId)
            ->update([
                'access_token' => $this->enc($newAccess),
                'expires_at'   => $newExpiresAt,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return $newAccess;
    }

    public function setThumbnailWithRetry(string $accessToken, string $videoId, string $thumbAbsPath): void
    {
        $delays = [1, 2, 4]; // saniye
        $lastErr = null;

        foreach ($delays as $d) {
            try {
                $this->setThumbnail($accessToken, $videoId, $thumbAbsPath);
                return;
            } catch (\Throwable $e) {
                $lastErr = $e;
                sleep($d);
            }
        }

        // son deneme (delay yok)
        if ($lastErr) {
            $this->setThumbnail($accessToken, $videoId, $thumbAbsPath);
        }
    }

    public function setThumbnail(string $accessToken, string $videoId, string $thumbAbsPath): void
    {
        if (!is_file($thumbAbsPath)) {
            throw new \RuntimeException('Thumbnail file not found: ' . $thumbAbsPath);
        }

        $url = 'https://www.googleapis.com/upload/youtube/v3/thumbnails/set?videoId=' . rawurlencode($videoId);

        $mime = mime_content_type($thumbAbsPath) ?: 'image/jpeg';

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Expect:',
            ],
            CURLOPT_POSTFIELDS     => [
                'media' => new \CURLFile($thumbAbsPath, $mime, basename($thumbAbsPath)),
            ],
            CURLOPT_TIMEOUT        => 120,
            CURLOPT_CONNECTTIMEOUT => 30,
        ]);

        $resp = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            $snippet = is_string($resp) ? substr($resp, 0, 1200) : '';
            throw new \RuntimeException('YT THUMB failed HTTP=' . $http . ' ERR=' . $err . ' RESP=' . $snippet);
        }
    }
}
