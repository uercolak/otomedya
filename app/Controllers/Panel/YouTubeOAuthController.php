<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;
use App\Models\SocialAccountTokenModel;

class YouTubeOAuthController extends BaseController
{
    private function ensureUser(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (!session('is_logged_in')) return redirect()->to(site_url('auth/login'));
        return null;
    }

    private function cfg(): array
    {
        $scopesEnv = trim((string)(getenv('YOUTUBE_SCOPES') ?: ''));
        $default = [
            'https://www.googleapis.com/auth/youtube.upload',
            'https://www.googleapis.com/auth/youtube.readonly',
        ];

        $scopes = $default;
        if ($scopesEnv !== '') {
            $parts = array_filter(array_map('trim', explode(',', $scopesEnv)));
            if ($parts) $scopes = array_values(array_unique(array_merge($default, $parts)));
        }

        return [
            'client_id'     => (string)(getenv('GOOGLE_CLIENT_ID') ?: ''),
            'client_secret' => (string)(getenv('GOOGLE_CLIENT_SECRET') ?: ''),
            'redirect_uri'  => (string)(getenv('GOOGLE_REDIRECT_URI') ?: site_url('panel/social-accounts/youtube/callback')),
            'scopes'        => $scopes,
        ];
    }

    private function db() { return \Config\Database::connect(); }
    private function userId(): int { return (int)session('user_id'); }

    private function httpClient()
    {
        return \Config\Services::curlrequest([
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    public function wizard()
    {
        if ($r = $this->ensureUser()) return $r;

        $db = $this->db();
        $userId = $this->userId();

        $row = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->where('platform', 'youtube')
            ->orderBy('id','DESC')
            ->get()->getRowArray();

        $hasConnected = (bool)$row;
        $channel = null;

        if ($row) {
            $channel = [
                'id' => (string)($row['external_id'] ?? ''),
                'title' => (string)($row['name'] ?? ''),
                'customUrl' => (string)($row['username'] ?? ''),
                'avatar' => (string)($row['avatar_url'] ?? ''),
            ];
        }

        return view('panel/social_accounts/youtube_wizard', [
            'hasConnected' => $hasConnected,
            'channel' => $channel,
            'debug' => [],
        ]);
    }

    public function connect()
    {
        if ($r = $this->ensureUser()) return $r;

        $cfg = $this->cfg();
        if ($cfg['client_id'] === '' || $cfg['client_secret'] === '') {
            return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
                ->with('error', 'YouTube bağlantısı için GOOGLE_CLIENT_ID / GOOGLE_CLIENT_SECRET eksik.');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('yt_oauth_state', $state);

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id'     => $cfg['client_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'response_type' => 'code',
            'scope'         => implode(' ', $cfg['scopes']),
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'include_granted_scopes' => 'true',
            'state'         => $state,
        ]);

        return redirect()->to($authUrl);
    }

    public function callback()
    {
        if ($r = $this->ensureUser()) return $r;

        $cfg = $this->cfg();

        $state = (string)$this->request->getGet('state');
        $expected = (string)session('yt_oauth_state');
        if (!$expected || !$state || !hash_equals($expected, $state)) {
            return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
                ->with('error', 'YouTube OAuth doğrulanamadı. Tekrar dene.');
        }

        $code = (string)$this->request->getGet('code');
        if ($code === '') {
            $err = (string)$this->request->getGet('error');
            return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
                ->with('error', 'YouTube bağlantısı iptal edildi: ' . ($err ?: 'code yok'));
        }

        $client = $this->httpClient();

        // code -> token
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
        log_message('error', 'YT TOKEN HTTP='.$resp->getStatusCode().' BODY='.$body);

        $tok  = json_decode($body, true);
        if (!is_array($tok)) $tok = ['raw' => $body];
        
        if (empty($tok['access_token'])) {
            return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
                ->with('error', 'YouTube token alınamadı: ' . ($tok['error_description'] ?? $tok['error'] ?? 'Bilinmeyen hata'));
        }

        $accessToken  = (string)$tok['access_token'];
        $refreshToken = (string)($tok['refresh_token'] ?? '');
        $expiresIn    = (int)($tok['expires_in'] ?? 0);
        $expiresAt    = $expiresIn ? date('Y-m-d H:i:s', time() + $expiresIn) : null;
        $scope        = (string)($tok['scope'] ?? '');

        // channel info
        $yt = $client->get('https://www.googleapis.com/youtube/v3/channels', [
            'headers' => ['Authorization' => 'Bearer ' . $accessToken],
            'query' => [
                'part' => 'snippet',
                'mine' => 'true',
                'maxResults' => 1,
            ],
        ]);

        $ytBody = (string)$yt->getBody();
        log_message('error', 'YT CHANNELS HTTP='.$yt->getStatusCode().' BODY='.$ytBody);
        $ytJson = json_decode($ytBody, true);
        if (!is_array($ytJson)) $ytJson = ['raw' => $ytBody];

        $items = $ytJson['items'] ?? [];
        if (empty($items) || empty($items[0]['id'])) {
            return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
                ->with('error', 'YouTube kanal bilgisi alınamadı. (İzin/Scope ayarlarını kontrol et)');
        }

        $channelId = (string)$items[0]['id'];
        $snip = $items[0]['snippet'] ?? [];

        $title     = (string)($snip['title'] ?? ('YouTube Kanal ' . $channelId));
        $customUrl = (string)($snip['customUrl'] ?? '');
        $avatar    = (string)($snip['thumbnails']['default']['url'] ?? ($snip['thumbnails']['high']['url'] ?? ''));

        $db = $this->db();
        $userId = $this->userId();
        $now = date('Y-m-d H:i:s');

        // upsert social_accounts
        $existing = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->where('platform', 'youtube')
            ->where('external_id', $channelId)
            ->get()->getRowArray();

        $data = [
            'user_id'     => $userId,
            'platform'    => 'youtube',
            'external_id' => $channelId,
            'name'        => $title,
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

        // upsert token
        $tokens = new SocialAccountTokenModel();
        $existingTok = $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'google')
            ->get()->getRowArray();

        $payload = [
            'social_account_id' => $socialAccountId,
            'provider'          => 'google',
            'access_token'      => $accessToken,
            'refresh_token'     => ($refreshToken !== '' ? $refreshToken : ($existingTok['refresh_token'] ?? null)),
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

        return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
            ->with('success', 'YouTube kanalı bağlandı ✅ (' . $title . ')');
    }

    public function disconnect()
    {
        if ($r = $this->ensureUser()) return $r;

        $db = $this->db();
        $userId = $this->userId();

        $rows = $db->table('social_accounts')
            ->select('id')
            ->where('user_id', $userId)
            ->where('platform', 'youtube')
            ->get()->getResultArray();

        $ids = array_map(fn($r) => (int)$r['id'], $rows);

        if ($ids) {
            $db->table('social_account_tokens')
                ->whereIn('social_account_id', $ids)
                ->where('provider', 'google')
                ->delete();

            $db->table('social_accounts')
                ->whereIn('id', $ids)
                ->delete();
        }

        return redirect()->to(site_url('panel/social-accounts/youtube/wizard'))
            ->with('success', 'YouTube bağlantısı sıfırlandı.');
    }
}
