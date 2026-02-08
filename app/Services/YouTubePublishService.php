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
}
