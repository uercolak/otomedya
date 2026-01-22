<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Services\QueueService;
use Config\Database;

class RefreshTikTokTokenHandler implements JobHandlerInterface
{
    public function handle(array $payload): bool
    {
        $db  = Database::connect();
        $now = date('Y-m-d H:i:s');

        $onlyAccountId = isset($payload['social_account_id']) ? (int)$payload['social_account_id'] : 0;
        $publishId     = isset($payload['publish_id']) ? (int)$payload['publish_id'] : 0; // opsiyonel
        $threshold     = date('Y-m-d H:i:s', time() + 10 * 60);

        // 1) Yenilenecek token satırlarını seç
        if ($onlyAccountId > 0) {
            $rows = $db->table('social_account_tokens sat')
                ->select('sat.id as token_row_id, sat.social_account_id, sat.refresh_token, sat.expires_at, sa.external_id')
                ->join('social_accounts sa', 'sa.id = sat.social_account_id', 'inner')
                ->where('sat.provider', 'tiktok')
                ->where('sat.social_account_id', $onlyAccountId)
                ->orderBy('sat.id', 'DESC')
                ->limit(1)
                ->get()->getResultArray();
        } else {
            $sql = "
                SELECT sat.id as token_row_id, sat.social_account_id, sat.refresh_token, sat.expires_at, sa.external_id
                FROM social_account_tokens sat
                INNER JOIN social_accounts sa ON sa.id = sat.social_account_id
                WHERE sat.provider = 'tiktok'
                  AND (sat.expires_at IS NULL OR sat.expires_at <= ?)
                  AND sat.id IN (
                      SELECT MAX(id)
                      FROM social_account_tokens
                      WHERE provider = 'tiktok'
                      GROUP BY social_account_id
                  )
                LIMIT 50
            ";
            $rows = $db->query($sql, [$threshold])->getResultArray();
        }

        if (!$rows) {
            log_message('info', '[TikTokRefresh] No tokens to refresh. threshold={threshold}', ['threshold' => $threshold]);
            return true;
        }

        $refreshedAny = false;
        $touchedAccountIds = [];

        foreach ($rows as $row) {
            $tokenRowId      = (int)($row['token_row_id'] ?? 0);
            $socialAccountId = (int)($row['social_account_id'] ?? 0);
            $refreshToken    = (string)($row['refresh_token'] ?? '');

            if ($tokenRowId <= 0 || $socialAccountId <= 0 || $refreshToken === '') {
                log_message('warning', '[TikTokRefresh] Skip row. token_row_id={tid} social_account_id={id} refreshTokenEmpty={empty}', [
                    'tid'   => $tokenRowId,
                    'id'    => $socialAccountId,
                    'empty' => $refreshToken === '' ? 'yes' : 'no',
                ]);
                continue;
            }

            try {
                $newToken = $this->refreshToken($refreshToken);
            } catch (\Throwable $e) {
                log_message('error', '[TikTokRefresh] Refresh failed account_id={id} err={err}', [
                    'id'  => $socialAccountId,
                    'err' => $e->getMessage(),
                ]);
                continue;
            }

            $accessToken = (string)($newToken['access_token'] ?? '');
            $expiresIn   = (int)($newToken['expires_in'] ?? 0);
            $scopeStr    = (string)($newToken['scope'] ?? '');
            $tokenType   = (string)($newToken['token_type'] ?? 'bearer');

            $newRefresh  = (string)($newToken['refresh_token'] ?? '');
            if ($newRefresh === '') $newRefresh = $refreshToken;

            if ($accessToken === '') {
                log_message('error', '[TikTokRefresh] Missing access_token after refresh. account_id={id} resp={resp}', [
                    'id'   => $socialAccountId,
                    'resp' => json_encode($newToken),
                ]);
                continue;
            }

            $expiresAt = ($expiresIn > 0) ? date('Y-m-d H:i:s', time() + $expiresIn - 60) : null;

            // 2) Sadece seçtiğimiz token satırını update et (en güvenlisi)
            $db->table('social_account_tokens')
                ->where('id', $tokenRowId)
                ->update([
                    'access_token'  => $accessToken,
                    'refresh_token' => $newRefresh,
                    'token_type'    => $tokenType,
                    'expires_at'    => $expiresAt,
                    'scope'         => $scopeStr !== '' ? $scopeStr : null,
                    'updated_at'    => $now,
                ]);

            // 3) social_accounts kopyasını da güncelle
            $db->table('social_accounts')
                ->where('id', $socialAccountId)
                ->update([
                    'access_token'     => $accessToken,
                    'token_expires_at' => $expiresAt,
                    'updated_at'       => $now,
                ]);

            $refreshedAny = true;
            $touchedAccountIds[] = $socialAccountId;

            log_message('info', '[TikTokRefresh] Refreshed token. social_account_id={id} expires_at={exp}', [
                'id'  => $socialAccountId,
                'exp' => $expiresAt ?? 'NULL',
            ]);
        }

        // 4) Refresh bir publish akışından geldiyse, status değil publish_post'u yeniden dene
        //    Çünkü 401 init/upload aşamasında geldiyse meta_json'da publish_id yoktur.
        if ($refreshedAny && $publishId > 0) {
            $queue = new QueueService();
            $queue->push('publish_post', [
                'publish_id' => $publishId,
                'post_type'  => 'post', // sende payload farklıysa burayı aynı bırakabiliriz, default zaten post.
            ], date('Y-m-d H:i:s', time() + 3), 90, 3);

            log_message('info', '[TikTokRefresh] Re-queued publish_post for publish_id={pid}', ['pid' => $publishId]);
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

        return (isset($json['data']) && is_array($json['data'])) ? $json['data'] : $json;
    }
}
