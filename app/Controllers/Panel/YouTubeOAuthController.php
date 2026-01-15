<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\SocialAccountTokenModel;

class YouTubeOAuthController extends BaseController
{
    private function cfg(): array
    {
        $scopesEnv = trim((string)(getenv('YOUTUBE_SCOPES') ?: ''));
        $scopes = $scopesEnv !== ''
            ? array_values(array_filter(array_map('trim', explode(',', $scopesEnv))))
            : [
                'https://www.googleapis.com/auth/youtube.upload',
                'https://www.googleapis.com/auth/youtube.readonly',
              ];

        return [
            'client_id'     => (string)(getenv('GOOGLE_CLIENT_ID') ?: ''),
            'client_secret' => (string)(getenv('GOOGLE_CLIENT_SECRET') ?: ''),
            'redirect_uri'  => (string)(getenv('GOOGLE_REDIRECT_URI') ?: site_url('panel/social-accounts/youtube/callback')),
            'scopes'        => $scopes,
        ];
    }

    private function userId(): int { return (int)session('user_id'); }
    private function db() { return \Config\Database::connect(); }

    private function httpClient()
    {
        return \Config\Services::curlrequest([
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    public function connect()
    {
        if (!session('is_logged_in')) return redirect()->to(site_url('auth/login'));

        $cfg = $this->cfg();
        if ($cfg['client_id'] === '' || $cfg['client_secret'] === '') {
            return redirect()->to(site_url('panel/social-accounts'))
                ->with('error', 'YouTube bağlantısı için GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET eksik.');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('yt_oauth_state', $state);

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => implode(' ', $cfg['scopes']),
            'access_type'   => 'offline',   // refresh_token için şart
            'prompt'        => 'consent',   // ilk seferde refresh_token gelsin diye
            'include_granted_scopes' => 'true',
            'state'         => $state,
        ]);

        return redirect()->to($authUrl);
    }

    public function callback()
    {
        if (!session('is_logged_in')) return redirect()->to(site_url('auth/login'));

        $cfg = $this->cfg();

        $state = (string)$this->request->getGet('state');
        $expected = (string)session('yt_oauth_state');
        if (!$expected || !$state || !hash_equals($expected, $state)) {
            return redirect()->to(site_url('panel/social-accounts'))
                ->with('error', 'YouTube OAuth state doğrulanamadı. Tekrar dene.');
        }

        $code = (string)$this->request->getGet('code');
        if ($code === '') {
            $err = (string)$this->request->getGet('error');
            return redirect()->to(site_url('panel/social-accounts'))
                ->with('error', 'YouTube bağlantısı iptal edildi: ' . ($err ?: 'code yok'));
        }

        // 1) code -> token
        $client = $this->httpClient();
        $resp = $client->post('https://oauth2.googleapis.com/token', [
            'form_params' => [
                'code'          => $code,
                'client_id'     => $cfg['client_id'],
                'client_secret' => $cfg['client_secret'],
                'redirect_uri'  => $cfg['redirect_uri'],
                'grant_type'    => 'authorization_code',
            ],
        ]);

        $body = (string)$resp->getBody();
        $tok = json_decode($body, true);
        if (!is_array($tok)) $tok = ['raw' => $body];

        if (empty($tok['access_token'])) {
            return redirect()->to(site_url('panel/social-accounts'))
                ->with('error', 'YouTube token alınamadı: ' . ($tok['error_description'] ?? $tok['error'] ?? 'Bilinmeyen hata'));
        }

        $accessToken  = (string)$tok['access_token'];
        $refreshToken = (string)($tok['refresh_token'] ?? ''); // bazen boş gelebilir (prompt/consent yoksa)
        $expiresIn    = (int)($tok['expires_in'] ?? 0);
        $expiresAt    = $expiresIn ? date('Y-m-d H:i:s', time() + $expiresIn) : null;
        $scope        = (string)($tok['scope'] ?? '');

        // 2) Kanal bilgisi çek (mine=true)
        $yt = $client->get('https://www.googleapis.com/youtube/v3/channels', [
            'headers' => [
                'Authorization' => 'Bearer ' . $accessToken,
            ],
            'query' => [
                'part' => 'snippet',
                'mine' => 'true',
                'maxResults' => 1,
            ],
        ]);

        $ytBody = (string)$yt->getBody();
        $ytJson = json_decode($ytBody, true);
        if (!is_array($ytJson)) $ytJson = ['raw' => $ytBody];

        $items = $ytJson['items'] ?? [];
        if (empty($items) || empty($items[0]['id'])) {
            return redirect()->to(site_url('panel/social-accounts'))
                ->with('error', 'YouTube kanal bilgisi alınamadı. (Yetki/Scope sorunu olabilir)');
        }

        $channelId = (string)$items[0]['id'];
        $snip = $items[0]['snippet'] ?? [];

        $channelTitle = (string)($snip['title'] ?? ('YouTube Kanal ' . $channelId));
        $customUrl    = (string)($snip['customUrl'] ?? '');
        $avatar       = (string)($snip['thumbnails']['default']['url'] ?? ($snip['thumbnails']['high']['url'] ?? ''));

        // 3) social_accounts upsert (user + platform + external_id)
        $db = $this->db();
        $userId = $this->userId();
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->where('platform', 'youtube')
            ->where('external_id', $channelId)
            ->get()->getRowArray();

        $data = [
            'user_id'     => $userId,
            'platform'    => 'youtube',
            'external_id' => $channelId,
            'name'        => $channelTitle,
            'username'    => ($customUrl !== '' ? $customUrl : null),
            'avatar_url'  => ($avatar !== '' ? $avatar : null),
            'updated_at'  => $now,
        ];

        if ($existing) {
            $db->table('social_accounts')->where('id', (int)$existing['id'])->update($data);
            $socialAccountId = (int)$existing['id'];
        } else {
            $data['created_at'] = $now;
            $db->table('social_accounts')->insert($data);
            $socialAccountId = (int)$db->insertID();
        }

        // 4) tokenları social_account_tokens’a yaz
        $tokens = new SocialAccountTokenModel();

        $existingTok = $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'google')
            ->get()->getRowArray();

        $payload = [
            'social_account_id' => $socialAccountId,
            'provider'          => 'google',
            'access_token'      => $accessToken,
            'refresh_token'     => ($refreshToken !== '' ? $refreshToken : ($existingTok['refresh_token'] ?? null)), // refresh boş gelirse eskisini koru
            'token_type'        => (string)($tok['token_type'] ?? 'Bearer'),
            'expires_at'        => $expiresAt,
            'scope'             => ($scope !== '' ? $scope : null),
            'meta_json'         => json_encode([
                'channel_id' => $channelId,
                'raw_token'  => $tok,
            ], JSON_UNESCAPED_UNICODE),
            'updated_at'        => $now,
        ];

        if ($existingTok) {
            $tokens->update((int)$existingTok['id'], $payload);
        } else {
            $payload['created_at'] = $now;
            $tokens->insert($payload);
        }

        return redirect()->to(site_url('panel/social-accounts'))
            ->with('success', 'YouTube kanalı bağlandı ✅ (' . $channelTitle . ')');
    }
}
