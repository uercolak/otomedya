<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use Config\Database;

class RefreshTikTokTokenHandler implements JobHandlerInterface
{
    public function handle(array $payload): bool
    {
        $db = Database::connect();
        $now = date('Y-m-d H:i:s');

        $onlyAccountId = isset($payload['social_account_id']) ? (int)$payload['social_account_id'] : 0;
        $threshold = date('Y-m-d H:i:s', time() + 10 * 60);

        $q = $db->table('social_account_tokens sat')
            ->select('sat.id as token_row_id, sat.social_account_id, sat.refresh_token, sat.expires_at, sa.external_id')
            ->join('social_accounts sa', 'sa.id = sat.social_account_id', 'inner')
            ->where('sat.provider', 'tiktok');

        if ($onlyAccountId > 0) {
            $q->where('sat.social_account_id', $onlyAccountId);
        } else {
            $q->groupStart()
                ->where('sat.expires_at IS NULL', null, false)
                ->orWhere('sat.expires_at <=', $threshold)
              ->groupEnd();
        }

        $rows = $q->limit(50)->get()->getResultArray();

        if (!$rows) {
            log_message('info', '[TikTokRefresh] No tokens to refresh. threshold={threshold}', ['threshold' => $threshold]);
            return true;
        }

        foreach ($rows as $row) {
            $socialAccountId = (int)($row['social_account_id'] ?? 0);
            $refreshToken = (string)($row['refresh_token'] ?? '');

            if ($socialAccountId <= 0 || $refreshToken === '') {
                log_message('warning', '[TikTokRefresh] Skip account. social_account_id={id} refreshTokenEmpty={empty}', [
                    'id' => $socialAccountId,
                    'empty' => $refreshToken === '' ? 'yes' : 'no',
                ]);
                continue;
            }

            try {
                $newToken = $this->refreshToken($refreshToken);
            } catch (\Throwable $e) {
                log_message('error', '[TikTokRefresh] Refresh failed account_id={id} err={err}', [
                    'id' => $socialAccountId,
                    'err' => $e->getMessage(),
                ]);
                continue;
            }

            $accessToken  = (string)($newToken['access_token'] ?? '');
            $expiresIn    = (int)($newToken['expires_in'] ?? 0);
            $scopeStr     = (string)($newToken['scope'] ?? '');
            $tokenType    = (string)($newToken['token_type'] ?? 'bearer');

            $newRefresh   = (string)($newToken['refresh_token'] ?? '');
            if ($newRefresh === '') $newRefresh = $refreshToken;

            if ($accessToken === '') {
                log_message('error', '[TikTokRefresh] Missing access_token after refresh. account_id={id} resp={resp}', [
                    'id' => $socialAccountId,
                    'resp' => json_encode($newToken),
                ]);
                continue;
            }

            $expiresAt = ($expiresIn > 0) ? date('Y-m-d H:i:s', time() + $expiresIn - 60) : null;

            $db->table('social_account_tokens')
                ->where('social_account_id', $socialAccountId)
                ->where('provider', 'tiktok')
                ->update([
                    'access_token'  => $accessToken,
                    'refresh_token' => $newRefresh,
                    'token_type'    => $tokenType,
                    'expires_at'    => $expiresAt,
                    'scope'         => $scopeStr !== '' ? $scopeStr : null,
                    'updated_at'    => $now,
                ]);

            $db->table('social_accounts')
                ->where('id', $socialAccountId)
                ->update([
                    'access_token'     => $accessToken,
                    'token_expires_at' => $expiresAt,
                    'updated_at'       => $now,
                ]);

            log_message('info', '[TikTokRefresh] Refreshed account_id={id} expires_at={exp}', [
                'id' => $socialAccountId,
                'exp' => $expiresAt,
            ]);
        }

        return true;
    }

    private function refreshToken(string $refreshToken): array
    {
        $clientKey    = trim((string) env('TIKTOK_CLIENT_KEY'));
        $clientSecret = trim((string) env('TIKTOK_CLIENT_SECRET'));

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
