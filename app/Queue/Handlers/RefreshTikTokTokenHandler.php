<?php

namespace App\Queue\Handlers;

use CodeIgniter\Database\Config as DBConfig;

class RefreshTikTokTokenHandler
{
    public function handle(array $payload = []): void
    {
        $db = DBConfig::connect();

        // 10 dk içinde bitecek tokenları yenile
        $threshold = date('Y-m-d H:i:s', time() + 10 * 60);

        $rows = $db->table('social_account_tokens sat')
            ->select('sat.id as token_row_id, sat.social_account_id, sat.refresh_token, sat.expires_at, sa.external_id')
            ->join('social_accounts sa', 'sa.id = sat.social_account_id', 'inner')
            ->where('sat.provider', 'tiktok')
            ->groupStart()
                ->where('sat.expires_at IS NULL', null, false)
                ->orWhere('sat.expires_at <=', $threshold)
            ->groupEnd()
            ->limit(50)
            ->get()
            ->getResultArray();

        foreach ($rows as $row) {
            $refreshToken = (string) ($row['refresh_token'] ?? '');
            if ($refreshToken === '') {
                // refresh token yoksa yenileyemeyiz
                continue;
            }

            $newToken = $this->refreshToken($refreshToken);

            $accessToken  = (string) ($newToken['access_token'] ?? '');
            $expiresIn    = (int)    ($newToken['expires_in'] ?? 0);
            $scopeStr     = (string) ($newToken['scope'] ?? null);
            $tokenType    = (string) ($newToken['token_type'] ?? 'bearer');

            // TikTok bazen refresh sonucu yeni refresh_token da döndürebilir
            $newRefresh   = (string) ($newToken['refresh_token'] ?? '');
            if ($newRefresh === '') {
                $newRefresh = $refreshToken; // eskisini koru
            }

            if ($accessToken === '') {
                // refresh başarısız; loglamak istersen buraya log_message ekleyebilirsin
                continue;
            }

            $now = date('Y-m-d H:i:s');
            $expiresAt = ($expiresIn > 0) ? date('Y-m-d H:i:s', time() + $expiresIn - 60) : null;

            // social_account_tokens güncelle
            $db->table('social_account_tokens')
                ->where('social_account_id', (int)$row['social_account_id'])
                ->where('provider', 'tiktok')
                ->update([
                    'access_token'  => $accessToken,
                    'refresh_token' => $newRefresh,
                    'token_type'    => $tokenType,
                    'expires_at'    => $expiresAt,
                    'scope'         => $scopeStr ?: null,
                    'updated_at'    => $now,
                ]);

            // social_accounts güncelle (panel liste vs buradan okuyor)
            $db->table('social_accounts')
                ->where('id', (int)$row['social_account_id'])
                ->update([
                    'access_token'     => $accessToken,
                    'token_expires_at' => $expiresAt,
                    'updated_at'       => $now,
                ]);
        }
    }

    private function refreshToken(string $refreshToken): array
    {
        $clientKey    = (string) getenv('TIKTOK_CLIENT_KEY');
        $clientSecret = (string) getenv('TIKTOK_CLIENT_SECRET');

        if ($clientKey === '' || $clientSecret === '') {
            throw new \RuntimeException('TIKTOK_CLIENT_KEY / TIKTOK_CLIENT_SECRET eksik.');
        }

        $url = 'https://open.tiktokapis.com/v2/oauth/token/';

        $postFields = http_build_query([
            'client_key'    => $clientKey,
            'client_secret' => $clientSecret,
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken,
        ]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $postFields,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
                'Cache-Control: no-cache',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $resp = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException("TikTok refresh HTTP={$http} ERR={$err} RESP=" . substr((string)$resp, 0, 400));
        }

        $json = json_decode((string)$resp, true);
        if (!is_array($json)) {
            throw new \RuntimeException('TikTok refresh JSON parse edilemedi: ' . substr((string)$resp, 0, 200));
        }

        if (isset($json['data']) && is_array($json['data'])) {
            return $json['data'];
        }

        return $json;
    }
}
