<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use Config\Database;

class TikTokController extends BaseController
{
    public function start()
    {
        // panel group zaten auth filter ile korunuyor, ama yine de garanti:
        if (!session('is_logged_in')) {
            return redirect()->to(site_url('auth/login'));
        }

        $clientKey = trim((string) env('TIKTOK_CLIENT_KEY'));
        $redirectUri = trim((string) env('TIKTOK_REDIRECT_URI')) ?: site_url('panel/auth/tiktok/callback');
        if ($clientKey === '') {
            return $this->response->setStatusCode(500)->setBody('TIKTOK_CLIENT_KEY eksik.');
        }

        // CSRF benzeri state
        $state = bin2hex(random_bytes(16));
        session()->set('tiktok_oauth_state', $state);

        // Portalda seçtiğin scope’larla aynı olsun
        $scope = implode(',', [
            'user.info.basic',
            'video.upload',
            'video.publish',
        ]);
        

        $authUrl = 'https://www.tiktok.com/v2/auth/authorize/?' . http_build_query([
            'client_key'    => $clientKey,
            'response_type' => 'code',
            'scope'         => $scope,
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
        ]);

        return redirect()->to($authUrl);
    }

    public function callback()
    {
        if (!session('is_logged_in')) {
            return redirect()->to(site_url('auth/login'));
        }

        $code  = trim((string) $this->request->getGet('code'));
        $state = trim((string) $this->request->getGet('state'));
        $err   = trim((string) $this->request->getGet('error'));
        $desc  = trim((string) $this->request->getGet('error_description'));

        if ($err !== '') {
            return $this->response->setStatusCode(400)->setBody('TikTok error: ' . esc($err . ' ' . $desc));
        }

        // TikTok portal bazen doğrulama/preview için code göndermeden de hit atabiliyor.
        if ($code === '') {
            return $this->response->setStatusCode(200)->setBody('OK');
        }

        $expectedState = (string) session('tiktok_oauth_state');
        session()->remove('tiktok_oauth_state');

        if ($expectedState === '' || !hash_equals($expectedState, $state)) {
            return $this->response->setStatusCode(400)->setBody('Geçersiz state.');
        }

        $clientKey    = trim((string) env('TIKTOK_CLIENT_KEY'));
        $clientSecret = trim((string) env('TIKTOK_CLIENT_SECRET'));
        $redirectUri  = trim((string) env('TIKTOK_REDIRECT_URI')) ?: site_url('panel/auth/tiktok/callback');

        if ($clientKey === '' || $clientSecret === '') {
            return $this->response->setStatusCode(500)->setBody('TIKTOK_CLIENT_KEY / TIKTOK_CLIENT_SECRET eksik.');
        }

        $token = $this->exchangeCodeForToken($clientKey, $clientSecret, $redirectUri, $code);

        $accessToken  = (string) ($token['access_token'] ?? '');
        $refreshToken = (string) ($token['refresh_token'] ?? '');
        $openId       = (string) ($token['open_id'] ?? '');
        $scopeStr     = (string) ($token['scope'] ?? '');
        $expiresIn    = (int) ($token['expires_in'] ?? 0);

        if ($accessToken === '' || $openId === '') {
            return $this->response->setStatusCode(500)->setBody('TikTok token alınamadı: ' . json_encode($token));
        }

        $db     = Database::connect();
        $now    = date('Y-m-d H:i:s');
        $userId = (int) session('user_id');

        // expires_at hesapla (refresh için 60sn buffer)
        $expiresAt = ($expiresIn > 0) ? date('Y-m-d H:i:s', time() + $expiresIn - 60) : null;

        // social_accounts tablonun yapısına göre upsert
        $existing = $db->table('social_accounts')
            ->select('id')
            ->where('user_id', $userId)
            ->where('platform', 'tiktok')
            ->where('external_id', $openId)
            ->get()->getRowArray();

        if ($existing) {
            $accountId = (int) $existing['id'];

            $db->table('social_accounts')
                ->where('id', $accountId)
                ->update([
                    'access_token'     => $accessToken,
                    'token_expires_at' => $expiresAt,
                    'updated_at'       => $now,
                ]);
        } else {
            $db->table('social_accounts')->insert([
                'user_id'          => $userId,
                'platform'         => 'tiktok',
                'external_id'      => $openId,
                'meta_page_id'     => null,
                'access_token'     => $accessToken,
                'token_expires_at' => $expiresAt,
                'name'             => null,
                'username'         => null,
                'avatar_url'       => null,
                'created_at'       => $now,
                'updated_at'       => $now,
            ]);

            $accountId = (int) $db->insertID();
        }

        // refresh_token'ı social_account_tokens tablosuna yaz (upsert)
        $db->table('social_account_tokens')->replace([
            'social_account_id' => $accountId,
            'provider'          => 'tiktok',
            'access_token'      => $accessToken,
            'refresh_token'     => $refreshToken !== '' ? $refreshToken : null,
            'token_type'        => 'bearer',
            'expires_at'        => $expiresAt,
            'scope'             => $scopeStr !== '' ? $scopeStr : null,
            'meta_json'         => null,
            'created_at'        => $now,
            'updated_at'        => $now,
        ]);

        return redirect()->to(site_url('panel/social-accounts'))
            ->with('success', 'TikTok hesabı başarıyla bağlandı.');
    }

    private function exchangeCodeForToken(string $clientKey, string $clientSecret, string $redirectUri, string $code): array
    {
        $url = 'https://open.tiktokapis.com/v2/oauth/token/';

        $postFields = http_build_query([
            'client_key'    => $clientKey,
            'client_secret' => $clientSecret,
            'code'          => $code,
            'grant_type'    => 'authorization_code',
            'redirect_uri'  => $redirectUri,
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
            throw new \RuntimeException("TikTok token HTTP={$http} ERR={$err} RESP=" . substr((string) $resp, 0, 400));
        }

        $json = json_decode((string) $resp, true);
        if (!is_array($json)) {
            throw new \RuntimeException('TikTok token JSON parse edilemedi: ' . substr((string) $resp, 0, 200));
        }

        // bazen data wrapper ile dönebiliyor
        if (isset($json['data']) && is_array($json['data'])) {
            return $json['data'];
        }

        return $json;
    }


    public function testTikTokRefresh()
{
    service('queue')->push('refresh_tiktok_token', [
        'social_account_id' => 50,
    ]);

    return 'refresh job queued';
}


}
