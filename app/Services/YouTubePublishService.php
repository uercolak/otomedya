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

    private function enc(): \CodeIgniter\Encryption\EncrypterInterface
    {
        return \Config\Services::encrypter();
    }

    private function decryptStr(?string $cipherB64): string
    {
        if (!$cipherB64) return '';
        $bin = base64_decode($cipherB64, true);
        if ($bin === false) return '';
        try {
            return (string)$this->enc()->decrypt($bin);
        } catch (\Throwable $e) {
            return '';
        }
    }

    private function encryptStr(string $plain): ?string
    {
        if ($plain === '') return null;
        return base64_encode($this->enc()->encrypt($plain));
    }

    public function getValidAccessToken($db, int $socialAccountId): string
    {
        $row = $db->table('social_account_tokens')
            ->select('id, access_token, refresh_token, expires_at')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'google')
            ->get()->getRowArray();

        $tokenId = (int)($row['id'] ?? 0);

        // ✅ decrypt
        $access  = $this->decryptStr($row['access_token'] ?? null);
        $refresh = $this->decryptStr($row['refresh_token'] ?? null);

        $expiresAtRaw = (string)($row['expires_at'] ?? '');

        $isExpired = false;
        if ($expiresAtRaw !== '') {
            $ts = strtotime($expiresAtRaw);
            if ($ts !== false) $isExpired = ($ts - 60) <= time();
        }

        if ($access !== '' && !$isExpired) return $access;
        if ($refresh === '') return $access;

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
            $msg = $json['error_description'] ?? $json['error'] ?? 'refresh failed';
            throw new \RuntimeException('YouTube token refresh başarısız: ' . $msg);
        }

        $newAccess = (string)$json['access_token'];
        $expiresIn = (int)($json['expires_in'] ?? 0);
        $newExpiresAt = $expiresIn ? date('Y-m-d H:i:s', time() + $expiresIn) : null;

        // ✅ encrypt + update
        $db->table('social_account_tokens')
            ->where('id', $tokenId)
            ->update([
                'access_token' => $this->encryptStr($newAccess),
                'expires_at'   => $newExpiresAt,
                'updated_at'   => date('Y-m-d H:i:s'),
            ]);

        return $newAccess;
    }
}
