<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class MetaOAuthController extends BaseController
{
    /* =========================================================
     * CONFIG
     * ========================================================= */
    private function metaConfig(): array
    {
        $scopesEnv = trim((string) getenv('META_SCOPES'));

        // Minimum set (wizard'ın sayfa/IG bulması için)
        $defaultScopes = [
            'public_profile',
            'pages_show_list',
            'pages_read_engagement',
            'instagram_basic',
            'instagram_content_publish',
        ];

        // ENV varsa merge
        $scopes = $defaultScopes;
        if ($scopesEnv !== '') {
            $parts = array_filter(array_map('trim', explode(',', $scopesEnv)));
            if (!empty($parts)) {
                $scopes = array_values(array_unique(array_merge($defaultScopes, $parts)));
            }
        }

        return [
            'app_id'       => (string) (getenv('META_APP_ID') ?: ''),
            'app_secret'   => (string) (getenv('META_APP_SECRET') ?: ''),
            'redirect_uri' => (string) (getenv('META_REDIRECT_URI') ?: site_url('panel/social-accounts/meta/callback')),
            'graph_ver'    => (string) (getenv('META_GRAPH_VER') ?: 'v24.0'),
            'verify_ssl'   => (getenv('META_VERIFY_SSL') !== false && getenv('META_VERIFY_SSL') !== '0'),

            // Facebook Login for Business config_id (opsiyonel)
            'login_config_id' => trim((string) getenv('META_LOGIN_CONFIG_ID')),

            'scopes' => $scopes,

            'refresh_threshold_days' => (int) (getenv('META_REFRESH_THRESHOLD_DAYS') ?: 10),
            'cron_secret'  => (string) (getenv('META_CRON_SECRET') ?: (getenv('IDEMPOTENCY_SECRET') ?: '')),
            'health_key'   => (string) (getenv('META_HEALTH_KEY') ?: (getenv('IDEMPOTENCY_SECRET') ?: '')),
        ];
    }

    private function userId(): int { return (int) session('user_id'); }
    private function db() { return \Config\Database::connect(); }

    private function httpClient(array $cfg)
    {
        return \Config\Services::curlrequest([
            'timeout' => 30,
            'verify'  => (bool) $cfg['verify_ssl'],
            'http_errors' => false,
        ]);
    }

    private function httpGetJson(string $url, array $cfg): array
    {
        $client = $this->httpClient($cfg);
        try {
            $resp = $client->get($url);
            $body = (string) $resp->getBody();
            $data = json_decode($body, true);
            if (!is_array($data)) $data = ['raw' => $body];
            $data['_http_code'] = $resp->getStatusCode();
            return $data;
        } catch (\Throwable $e) {
            return ['error' => ['message' => $e->getMessage(),'type' => 'Exception'], '_http_code' => 0];
        }
    }

    private function httpPostJson(string $url, array $cfg, array $formParams = []): array
    {
        $client = $this->httpClient($cfg);
        try {
            $resp = $client->post($url, ['form_params' => $formParams]);
            $body = (string) $resp->getBody();
            $data = json_decode($body, true);
            if (!is_array($data)) $data = ['raw' => $body];
            $data['_http_code'] = $resp->getStatusCode();
            return $data;
        } catch (\Throwable $e) {
            return ['error' => ['message' => $e->getMessage(),'type' => 'Exception'], '_http_code' => 0];
        }
    }

    private function graphUrl(array $cfg, string $path, array $query = []): string
    {
        $base = "https://graph.facebook.com/{$cfg['graph_ver']}/" . ltrim($path, '/');
        if (!empty($query)) $base .= (str_contains($base, '?') ? '&' : '?') . http_build_query($query);
        return $base;
    }

    private function appAccessToken(array $cfg): string { return $cfg['app_id'] . '|' . $cfg['app_secret']; }

    private function parseUnixToDateTime(?int $unix): ?string
    {
        if (!$unix || $unix <= 0) return null;
        return date('Y-m-d H:i:s', $unix);
    }

    /* =========================================================
     * DB: meta_tokens
     * ========================================================= */
    private function getMetaTokenRow(int $userId): ?array
    {
        $row = $this->db()->table('meta_tokens')->where('user_id', $userId)->get()->getRowArray();
        return $row ?: null;
    }

    private function hasConsent(int $userId): bool
    {
        $row = $this->getMetaTokenRow($userId);
        return !empty($row['consent_accepted_at']);
    }

    private function markConsentAccepted(int $userId): void
    {
        $db  = $this->db();
        $now = date('Y-m-d H:i:s');

        $existing = $this->getMetaTokenRow($userId);

        if ($existing) {
            $db->table('meta_tokens')->where('user_id', $userId)->update([
                'consent_accepted_at' => $now,
                'updated_at'          => $now,
            ]);
            return;
        }

        $db->table('meta_tokens')->insert([
            'user_id'             => $userId,
            'access_token'        => '',
            'expires_at'          => null,
            'consent_accepted_at' => $now,
            'oauth_nonce'         => null,
            'oauth_ts'            => null,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
    }

    private function saveUserAccessToken(int $userId, string $accessToken, ?string $expiresAt): void
    {
        $db  = $this->db();
        $now = date('Y-m-d H:i:s');

        $existing = $this->getMetaTokenRow($userId);

        if ($existing) {
            $db->table('meta_tokens')->where('user_id', $userId)->update([
                'access_token' => $accessToken,
                'expires_at'   => $expiresAt,
                'updated_at'   => $now,
            ]);
            return;
        }

        $db->table('meta_tokens')->insert([
            'user_id'      => $userId,
            'access_token' => $accessToken,
            'expires_at'   => $expiresAt,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }

    private function saveOAuthNonce(int $userId, string $nonce, int $ts): void
    {
        $db  = $this->db();
        $now = date('Y-m-d H:i:s');

        $existing = $this->getMetaTokenRow($userId);

        if ($existing) {
            $db->table('meta_tokens')->where('user_id', $userId)->update([
                'oauth_nonce' => $nonce,
                'oauth_ts'    => $ts,
                'updated_at'  => $now,
            ]);
            return;
        }

        $db->table('meta_tokens')->insert([
            'user_id'      => $userId,
            'access_token' => '',
            'expires_at'   => null,
            'oauth_nonce'  => $nonce,
            'oauth_ts'     => $ts,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);
    }

    private function getUserAccessTokenOrNull(int $userId): ?string
    {
        $row = $this->getMetaTokenRow($userId);
        $tok = $row['access_token'] ?? null;
        if (!$tok) return null;
        if (trim((string)$tok) === '') return null;
        return (string) $tok;
    }

    /* =========================================================
     * OAuth helpers
     * ========================================================= */
    private function b64urlEncode(string $raw): string
    {
        return rtrim(strtr(base64_encode($raw), '+/', '-_'), '=');
    }
    private function b64urlDecode(string $b64): string
    {
        $b64 = strtr($b64, '-_', '+/');
        return base64_decode($b64 . str_repeat('=', (4 - strlen($b64) % 4) % 4)) ?: '';
    }

    /* =========================================================
     * ROUTES
     * ========================================================= */
    public function consent()
    {
        $userId = $this->userId();
        $this->markConsentAccepted($userId);

        return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
            ->with('success', 'Onay kaydedildi. Şimdi Meta ile bağlanabilirsin.');
    }

    public function connect()
    {
        $cfg    = $this->metaConfig();
        $userId = $this->userId();

        if (!$this->hasConsent($userId)) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Devam etmek için önce onayı kabul etmelisin.');
        }

        if (empty($cfg['app_id']) || empty($cfg['app_secret']) || empty($cfg['redirect_uri'])) {
            log_message('error', 'META CONFIG ERROR: app_id / app_secret / redirect_uri boş');
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Meta ayarları eksik: META_APP_ID / META_APP_SECRET / META_REDIRECT_URI kontrol et.');
        }

        // STATE üret
        $nonce  = bin2hex(random_bytes(16));
        $ts     = time();

        $payloadArr = ['u' => $userId, 'n' => $nonce, 't' => $ts];
        $payload    = $this->b64urlEncode(json_encode($payloadArr, JSON_UNESCAPED_UNICODE));
        $sig        = hash_hmac('sha256', $payload, $cfg['app_secret']);
        $state      = $payload . '.' . $sig;

        // nonce DB'ye yaz (asıl doğrulama buradan)
        $this->saveOAuthNonce($userId, $nonce, $ts);

        // ÖNEMLİ: config_id kullansan bile scope'u TAM gönder.
        // Yoksa çoğu kullanıcıda me/accounts boş döner -> IG bulamaz.
        $scopes = $cfg['scopes'] ?? [];
        if (!in_array('public_profile', $scopes, true)) $scopes[] = 'public_profile';
        $scopeStr = implode(',', array_values(array_unique($scopes)));

        $params = [
            'client_id'     => $cfg['app_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'state'         => $state,
            'response_type' => 'code',
            'scope'         => $scopeStr,
        ];

        $configId = trim((string) ($cfg['login_config_id'] ?? ''));
        if ($configId !== '') {
            $params['config_id'] = $configId;
        }

        $loginUrl = 'https://www.facebook.com/' . $cfg['graph_ver'] . '/dialog/oauth?' . http_build_query($params);

        log_message('error', 'META CONFIG_ID: ' . ($configId !== '' ? $configId : 'EMPTY'));
        log_message('error', 'META SCOPE SENT: ' . $scopeStr);
        log_message('error', 'META REDIRECT_URI: ' . $cfg['redirect_uri']);
        log_message('error', 'META LOGIN URL LEN: ' . strlen($loginUrl));
        log_message('error', 'META LOGIN URL: ' . $loginUrl);

        return redirect()->to($loginUrl);
    }

    public function callback()
    {
        $cfg = $this->metaConfig();

        log_message('error', 'META CALLBACK RAW: ' . ($this->request->getServer('REQUEST_URI') ?? 'NO_URI'));
        log_message('error', 'META CALLBACK QUERY_STRING: ' . ($this->request->getServer('QUERY_STRING') ?? 'NO_QS'));

        $stateRaw = (string) $this->request->getGet('state');
        if (!$stateRaw || !str_contains($stateRaw, '.')) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state eksik.');
        }

        [$payload, $sig] = explode('.', $stateRaw, 2);
        $calc = hash_hmac('sha256', $payload, $cfg['app_secret']);

        if (!hash_equals($calc, $sig)) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state imzası geçersiz.');
        }

        $decoded = $this->b64urlDecode($payload);
        $arr = json_decode($decoded, true);

        $userId = (int)($arr['u'] ?? 0);
        $nonce  = (string)($arr['n'] ?? '');
        $ts     = (int)($arr['t'] ?? 0);

        if ($userId <= 0 || $nonce === '' || $ts <= 0) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state içeriği geçersiz.');
        }

        // 15 dk timeout
        if (abs(time() - $ts) > 900) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state zaman aşımı. Tekrar dene.');
        }

        // nonce DB ile eşleşiyor mu?
        $row = $this->getMetaTokenRow($userId);
        $dbNonce = (string)($row['oauth_nonce'] ?? '');
        $dbTs    = (int)($row['oauth_ts'] ?? 0);

        if ($dbNonce === '' || !hash_equals($dbNonce, $nonce)) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state doğrulanamadı (nonce).');
        }

        // Ek güvenlik: ts de tutarlı olsun (çok şart değil ama iyi)
        if ($dbTs > 0 && abs($dbTs - $ts) > 900) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state doğrulanamadı (ts).');
        }

        $code = (string) $this->request->getGet('code');
        if (!$code) {
            $err = [
                'error' => (string) $this->request->getGet('error'),
                'reason' => (string) $this->request->getGet('error_reason'),
                'desc' => (string) $this->request->getGet('error_description'),
                'msg' => (string) $this->request->getGet('error_message'),
                'qs' => $_GET,
            ];
            log_message('error', 'META CALLBACK NO CODE: ' . json_encode($err, JSON_UNESCAPED_UNICODE));

            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Meta callback hatası: code yok');
        }

        // code -> short token
        $tokenRes = $this->httpGetJson(
            $this->graphUrl($cfg, 'oauth/access_token', [
                'client_id'     => $cfg['app_id'],
                'client_secret' => $cfg['app_secret'],
                'redirect_uri'  => $cfg['redirect_uri'],
                'code'          => $code,
            ]),
            $cfg
        );

        if (empty($tokenRes['access_token'])) {
            $msg = $tokenRes['error']['message'] ?? 'Token alınamadı';
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Meta token alınamadı: ' . $msg);
        }

        $shortToken = (string) $tokenRes['access_token'];

        // long token
        $longRes = $this->httpGetJson(
            $this->graphUrl($cfg, 'oauth/access_token', [
                'grant_type'        => 'fb_exchange_token',
                'client_id'         => $cfg['app_id'],
                'client_secret'     => $cfg['app_secret'],
                'fb_exchange_token' => $shortToken,
            ]),
            $cfg
        );

        $finalToken = $shortToken;
        $expiresAt  = null;

        if (!empty($longRes['access_token'])) {
            $finalToken = (string) $longRes['access_token'];
            if (!empty($longRes['expires_in'])) {
                $expiresAt = date('Y-m-d H:i:s', time() + (int)$longRes['expires_in']);
            }
        } else {
            if (!empty($tokenRes['expires_in'])) {
                $expiresAt = date('Y-m-d H:i:s', time() + (int)$tokenRes['expires_in']);
            }
        }

        // debug_token ile kesin expires
        $debug = $this->httpGetJson(
            $this->graphUrl($cfg, 'debug_token', [
                'input_token'  => $finalToken,
                'access_token' => $this->appAccessToken($cfg),
            ]),
            $cfg
        );

        $debugData = $debug['data'] ?? [];
        if (is_array($debugData) && !empty($debugData['expires_at'])) {
            $expiresAt = $this->parseUnixToDateTime((int)$debugData['expires_at']);
        }

        // İzinleri logla (IG bulunmuyor problemi için altın değerinde)
        $perm = $this->httpGetJson(
            $this->graphUrl($cfg, 'me/permissions', ['access_token' => $finalToken]),
            $cfg
        );
        log_message('error', 'META PERMISSIONS: ' . json_encode($perm, JSON_UNESCAPED_UNICODE));

        // token kaydet
        $this->saveUserAccessToken($userId, $finalToken, $expiresAt);

        return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
            ->with('success', 'Meta bağlantısı tamamlandı.');
    }

    /* =========================================================
     * Wizard (IG bulma)
     * ========================================================= */

    // ✅ Eksik method hatasını çözer
    private function getKnownPageIdsForUser(int $userId): array
    {
        $db = $this->db();

        // social_accounts tablonuzda meta_page_id tutuyorsanız:
        // Kolon adı farklıysa burada düzeltiriz.
        $rows = $db->table('social_accounts')
            ->select('meta_page_id')
            ->where('user_id', $userId)
            ->where('meta_page_id IS NOT NULL', null, false)
            ->get()->getResultArray();

        $out = [];
        foreach ($rows as $r) {
            $pid = trim((string)($r['meta_page_id'] ?? ''));
            if ($pid !== '') $out[] = $pid;
        }
        return array_values(array_unique($out));
    }

    private function httpGetAllData(array $cfg, string $path, array $query): array
    {
        $out = [];
        $pages = 0;

        $url = $this->graphUrl($cfg, $path, $query);

        while ($url && $pages < 20) {
            $pages++;

            $resp = $this->httpGetJson($url, $cfg);

            if (!empty($resp['error'])) break;

            if (!empty($resp['data']) && is_array($resp['data'])) {
                foreach ($resp['data'] as $row) $out[] = $row;
            }

            $next = $resp['paging']['next'] ?? null;
            $url = is_string($next) && $next !== '' ? $next : null;
        }

        return ['data' => $out, '_meta' => ['pages' => $pages]];
    }

    private function discoverPageIdsViaBusinesses(array $cfg, string $userToken): array
    {
        // Bu fallback bazı hesaplarda "business_management" ister.
        // Yoksa error döner; wizard debug'da görürsün.
        $debug = [];

        $bizList = $this->httpGetAllData($cfg, 'me/businesses', [
            'fields' => 'id,name',
            'limit'  => 200,
            'access_token' => $userToken,
        ]);
        $debug['businesses'] = $bizList;

        $bizIds = [];
        foreach (($bizList['data'] ?? []) as $b) {
            if (!empty($b['id'])) $bizIds[] = (string)$b['id'];
        }

        $edges = ['owned_pages', 'client_pages', 'pages'];
        $pageIds = [];

        foreach ($bizIds as $bizId) {
            foreach ($edges as $edge) {
                $pages = $this->httpGetAllData($cfg, $bizId . '/' . $edge, [
                    'fields' => 'id,name,instagram_business_account,connected_instagram_account',
                    'limit'  => 200,
                    'access_token' => $userToken,
                ]);

                $debug["business_{$bizId}_{$edge}"] = $pages;

                foreach (($pages['data'] ?? []) as $p) {
                    if (!empty($p['id'])) $pageIds[] = (string)$p['id'];
                }
            }
        }

        $pageIds = array_values(array_unique(array_filter($pageIds)));

        return ['page_ids' => $pageIds, 'debug' => $debug];
    }

    public function wizard()
    {
        $cfg = $this->metaConfig();
        $userId = $this->userId();

        $hasConsent = $this->hasConsent($userId);
        $userToken  = $this->getUserAccessTokenOrNull($userId);
        $hasToken   = (bool) $userToken;

        $igOptions = [];
        $debug = [];

        if ($hasToken) {
            $debug['me'] = $this->httpGetJson(
                $this->graphUrl($cfg, 'me', ['fields' => 'id,name', 'access_token' => $userToken]),
                $cfg
            );

            $debug['permissions'] = $this->httpGetJson(
                $this->graphUrl($cfg, 'me/permissions', ['access_token' => $userToken]),
                $cfg
            );

            // A) Klasik liste
            $pagesA = $this->httpGetJson(
                $this->graphUrl($cfg, 'me/accounts', [
                    'fields' => 'id,name,instagram_business_account,connected_instagram_account',
                    'limit' => 200,
                    'access_token' => $userToken,
                ]),
                $cfg
            );
            $debug['pages_me_accounts'] = $pagesA;

            // B) Alternatif
            $pagesB = $this->httpGetJson(
                $this->graphUrl($cfg, 'me', [
                    'fields' => 'accounts.limit(200){id,name,instagram_business_account,connected_instagram_account}',
                    'access_token' => $userToken,
                ]),
                $cfg
            );
            $debug['pages_me_fields_accounts'] = $pagesB;

            $pageIdsToTry = [];

            if (!empty($pagesA['data']) && is_array($pagesA['data'])) {
                foreach ($pagesA['data'] as $p) if (!empty($p['id'])) $pageIdsToTry[] = (string)$p['id'];
            }

            if (!empty($pagesB['accounts']['data']) && is_array($pagesB['accounts']['data'])) {
                foreach ($pagesB['accounts']['data'] as $p) if (!empty($p['id'])) $pageIdsToTry[] = (string)$p['id'];
            }

            // C) Business fallback (A+B boşsa)
            if (empty($pageIdsToTry)) {
                $fallback = $this->discoverPageIdsViaBusinesses($cfg, $userToken);
                $debug['business_fallback'] = $fallback['debug'] ?? [];
                foreach (($fallback['page_ids'] ?? []) as $pid) $pageIdsToTry[] = (string)$pid;
            }

            // DB fallback: daha önce bağlananlardan
            foreach ($this->getKnownPageIdsForUser($userId) as $pid) $pageIdsToTry[] = $pid;

            $pageIdsToTry = array_values(array_unique(array_filter($pageIdsToTry)));
            $debug['page_ids_to_try'] = $pageIdsToTry;

            foreach ($pageIdsToTry as $pid) {
                $bundle = $this->httpGetJson(
                    $this->graphUrl($cfg, $pid, [
                        'fields' => 'id,name,instagram_business_account,connected_instagram_account',
                        'access_token' => $userToken,
                    ]),
                    $cfg
                );

                $debug["page_bundle_$pid"] = $bundle;
                if (!empty($bundle['error'])) continue;

                $pageId   = (string)($bundle['id'] ?? '');
                $pageName = (string)($bundle['name'] ?? '');

                $igNode = $bundle['instagram_business_account'] ?? null;
                if (empty($igNode['id'])) $igNode = $bundle['connected_instagram_account'] ?? null;
                if (empty($igNode['id'])) continue;

                $igId = (string)$igNode['id'];

                $igDetails = $this->httpGetJson(
                    $this->graphUrl($cfg, $igId, [
                        'fields' => 'id,username,name,profile_picture_url',
                        'access_token' => $userToken,
                    ]),
                    $cfg
                );

                $debug["ig_details_$igId"] = $igDetails;
                if (!empty($igDetails['error'])) continue;

                $igOptions[] = [
                    'page_id'     => $pageId,
                    'page_name'   => $pageName,
                    'ig_id'       => (string)($igDetails['id'] ?? $igId),
                    'ig_username' => (string)($igDetails['username'] ?? ''),
                    'ig_name'     => (string)($igDetails['name'] ?? ''),
                    'ig_avatar'   => (string)($igDetails['profile_picture_url'] ?? ''),
                ];
            }

            // duplicate temizle
            if (!empty($igOptions)) {
                $seen = [];
                $uniq = [];
                foreach ($igOptions as $opt) {
                    $k = (string)($opt['ig_id'] ?? '');
                    if ($k === '' || isset($seen[$k])) continue;
                    $seen[$k] = true;
                    $uniq[] = $opt;
                }
                $igOptions = $uniq;
            }
        }

        return view('panel/social_accounts/meta_wizard', [
            'hasConsent' => $hasConsent,
            'hasToken'   => $hasToken,
            'igOptions'  => $igOptions,
            'debug'      => $debug,
        ]);
    }

    private function resolvePageIdFromRef(array $cfg, string $userToken, string $ref): array
    {
        $ref = trim($ref);
        if ($ref === '') return [];

        // profile.php?id=123
        if (preg_match('~[?&]id=(\d+)~', $ref, $m)) {
            return ['id' => $m[1], 'method' => 'profile.php?id'];
        }

        // numeric id
        if (preg_match('~^\d{6,}$~', $ref)) {
            return ['id' => $ref, 'method' => 'numeric'];
        }

        // URL -> extract slug
        $slug = $ref;

        // remove protocol
        $slug = preg_replace('~^https?://~i', '', $slug);
        // remove domain if exists
        $slug = preg_replace('~^(www\.)?facebook\.com/~i', '', $slug);
        $slug = preg_replace('~^(www\.)?fb\.com/~i', '', $slug);

        // strip query + fragments + trailing slashes
        $slug = preg_replace('~[?#].*$~', '', $slug);
        $slug = trim($slug, "/ \t\n\r\0\x0B");

        if ($slug === '') return [];

        // common patterns: pages/NAME/ID
        if (preg_match('~^pages/[^/]+/(\d+)$~i', $slug, $m)) {
            return ['id' => $m[1], 'method' => 'pages/..../id'];
        }

        // If slug is still numeric after cleanup
        if (preg_match('~^\d{6,}$~', $slug)) {
            return ['id' => $slug, 'method' => 'slug_numeric'];
        }

        // Try resolve username -> id
        $res = $this->httpGetJson(
            $this->graphUrl($cfg, $slug, [
                'fields' => 'id,name',
                'access_token' => $userToken,
            ]),
            $cfg
        );

        if (!empty($res['id'])) {
            return ['id' => (string)$res['id'], 'name' => (string)($res['name'] ?? ''), 'method' => 'username_lookup'];
        }

        return ['error' => $res['error']['message'] ?? 'resolve failed', 'raw' => $res];
    }

    public function attach()
    {
        $cfg = $this->metaConfig();
        $userId = $this->userId();

        $userToken = $this->getUserAccessTokenOrNull($userId);
        if (!$userToken) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Meta token bulunamadı. Yeniden bağlan.');
        }

        $pageId   = trim((string) $this->request->getPost('page_id'));
        $pageName = trim((string) $this->request->getPost('page_name'));
        $igId     = trim((string) $this->request->getPost('ig_id'));

        if ($pageId === '' || $igId === '') {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Eksik bilgi: page_id / ig_id');
        }

        $bundle = $this->httpGetJson(
            $this->graphUrl($cfg, $pageId, [
                'fields' => 'id,name,access_token,instagram_business_account',
                'access_token' => $userToken,
            ]),
            $cfg
        );

        if (empty($bundle['access_token'])) {
            $msg = $bundle['error']['message'] ?? 'Page token alınamadı';
            return redirect()->to(site_url('panel/social-accounts/meta/wizard') . '?page_id=' . urlencode($pageId))
                ->with('error', 'Page token alınamadı: ' . $msg);
        }

        $pageToken = (string) $bundle['access_token'];
        $pageName  = (string) ($bundle['name'] ?? $pageName);
        $pageDetails = $this->httpGetJson(
            $this->graphUrl($cfg, $pageId, [
                'fields' => 'id,name,username,picture.type(large){url}',
                'access_token' => $pageToken,
            ]),
            $cfg
        );

        $pageUsername = trim((string)($pageDetails['username'] ?? ''));
        $pageAvatar   = trim((string)($pageDetails['picture']['data']['url'] ?? ''));

        $igDetails = $this->httpGetJson(
            $this->graphUrl($cfg, $igId, [
                'fields' => 'id,username,name,profile_picture_url',
                'access_token' => $userToken,
            ]),
            $cfg
        );
        if (!empty($igDetails['error'])) {
            $msg = $igDetails['error']['message'] ?? 'IG detay alınamadı';
            return redirect()->to(site_url('panel/social-accounts/meta/wizard') . '?page_id=' . urlencode($pageId))
                ->with('error', 'IG detay alınamadı: ' . $msg);
        }

        $dbg = $this->httpGetJson(
            $this->graphUrl($cfg, 'debug_token', [
                'input_token'  => $pageToken,
                'access_token' => $this->appAccessToken($cfg),
            ]),
            $cfg
        );
        $pageExpiresAt = null;
        $dbgData = $dbg['data'] ?? null;
        if (is_array($dbgData) && !empty($dbgData['expires_at'])) {
            $pageExpiresAt = $this->parseUnixToDateTime((int)$dbgData['expires_at']);
        }

        // İnstagram
        $igAccountId = $this->upsertSocialAccountInstagram($userId, $igDetails, $pageId, $pageName);

        // Facebook 
        $fbAccountId = $this->upsertSocialAccountFacebookPage($userId, $pageId, $pageName, $pageUsername ?: null, $pageAvatar ?: null);

        // 3) Aynı page token’ı her iki hesap için de tokens tablosuna yaz
        $this->upsertMetaPageToken($igAccountId, $pageToken, $pageExpiresAt, [
            'page_id'   => $pageId,
            'page_name' => $pageName,
        ]);

        $this->upsertMetaPageToken($fbAccountId, $pageToken, $pageExpiresAt, [
            'page_id'   => $pageId,
            'page_name' => $pageName,
        ]);

        $metaRow = $this->getMetaTokenRow($userId);
        $this->saveUserAccessToken($userId, $userToken, $metaRow['expires_at'] ?? null, [
            'last_page_id' => $pageId,
        ]);

        return redirect()->to(site_url('panel/social-accounts'))
            ->with('success', 'Instagram hesabı başarıyla bağlandı.');
    }

    public function disconnect()
    {
        $db = $this->db();
        $userId = $this->userId();

        $db->table('meta_tokens')->where('user_id', $userId)->delete();

        $rows = $db->table('social_accounts')->select('id')->where('user_id', $userId)->get()->getResultArray();
        $ids = array_map(fn($r) => (int)$r['id'], $rows);

        if (!empty($ids)) {
            $db->table('social_account_tokens')->whereIn('social_account_id', $ids)->where('provider', 'meta')->delete();
        }

        return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
            ->with('success', 'Meta bağlantısı sıfırlandı. Yeniden bağlanabilirsin.');
    }


    public function health()
    {
        $cfg = $this->metaConfig();

        $key = (string) $this->request->getGet('key');
        if (!$cfg['health_key'] || !hash_equals($cfg['health_key'], $key)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $db = $this->db();

        $metaRows = $db->table('meta_tokens')->select('user_id,access_token,expires_at')->get()->getResultArray();
        $metaOut = [];

        foreach ($metaRows as $r) {
            $tok = (string) ($r['access_token'] ?? '');
            $valid = false;

            if ($tok !== '') {
                $dbg = $this->httpGetJson(
                    $this->graphUrl($cfg, 'debug_token', [
                        'input_token'  => $tok,
                        'access_token' => $this->appAccessToken($cfg),
                    ]),
                    $cfg
                );
                $data = $dbg['data'] ?? null;
                $valid = (is_array($data) && !empty($data['is_valid']));
            }

            $metaOut[] = [
                'user_id' => (int)$r['user_id'],
                'is_valid' => $valid,
                'expires_at' => $r['expires_at'] ?? null,
                'expiring_soon' => $this->isExpiringSoon($r['expires_at'] ?? null, (int)$cfg['refresh_threshold_days']),
            ];
        }

        $pageRows = $db->table('social_account_tokens')
            ->select('social_account_id,access_token,expires_at')
            ->where('provider', 'meta')
            ->get()->getResultArray();

        $pageOut = [];
        foreach ($pageRows as $r) {
            $tok = (string) ($r['access_token'] ?? '');
            $valid = false;

            if ($tok !== '') {
                $dbg = $this->httpGetJson(
                    $this->graphUrl($cfg, 'debug_token', [
                        'input_token'  => $tok,
                        'access_token' => $this->appAccessToken($cfg),
                    ]),
                    $cfg
                );
                $data = $dbg['data'] ?? null;
                $valid = (is_array($data) && !empty($data['is_valid']));
            }

            $pageOut[] = [
                'social_account_id' => (int)$r['social_account_id'],
                'is_valid' => $valid,
                'expires_at' => $r['expires_at'] ?? null,
            ];
        }

        return $this->response->setJSON([
            'ok' => true,
            'checked_at' => date('c'),
            'meta_tokens' => $metaOut,
            'page_tokens' => $pageOut,
        ]);
    }

    public function cron()
    {
        $cfg = $this->metaConfig();

        $key = (string) $this->request->getGet('key');
        if (!$cfg['cron_secret'] || !hash_equals($cfg['cron_secret'], $key)) {
            return $this->response->setStatusCode(403)->setJSON(['ok' => false, 'error' => 'forbidden']);
        }

        $db = $this->db();
        $now = date('Y-m-d H:i:s');

        // 1) Token refresh (mevcut davranışı bozmuyoruz)
        $refreshed = false;

        // 2) meta_media_jobs processing
        $processed = 0;
        $published = 0;
        $failed = 0;

        if ($this->jobsEnabled()) {
            $maxAttempts = (int)(getenv('META_MEDIA_JOB_MAX_ATTEMPTS') ?: 40);

            $jobs = $db->table('meta_media_jobs')
                ->whereIn('status', ['created','processing'])
                ->where('next_retry_at IS NULL OR next_retry_at <= ' . $db->escape($now), null, false)
                ->orderBy('next_retry_at','ASC')
                ->limit(10)
                ->get()->getResultArray();

            foreach ($jobs as $job) {
                $processed++;

                $creationId = (string)$job['creation_id'];
                $igUserId   = (string)$job['ig_user_id'];
                $socialAccountId = (int)$job['social_account_id'];
                $publishId  = (int)($job['publish_id'] ?? 0);
                $attempts   = (int)($job['attempts'] ?? 0);

                if ($attempts >= $maxAttempts) {
                    $db->table('meta_media_jobs')->where('id', (int)$job['id'])->update([
                        'status' => 'failed',
                        'last_error' => 'Max attempts reached',
                        'updated_at' => $now,
                    ]);

                    if ($publishId > 0 && $db->tableExists('publishes')) {
                        $db->table('publishes')->where('id', $publishId)->update([
                            'status' => 'failed',
                            'error'  => 'Video işleme zaman aşımı (max attempts).',
                            'updated_at' => $now,
                        ]);
                    }

                    $failed++;
                    continue;
                }

                // page token
                $tokRow = $db->table('social_account_tokens')
                    ->where('social_account_id', $socialAccountId)
                    ->where('provider', 'meta')
                    ->get()->getRowArray();

                if (!$tokRow || empty($tokRow['access_token'])) {
                    $db->table('meta_media_jobs')->where('id', (int)$job['id'])->update([
                        'status' => 'failed',
                        'last_error' => 'Page token not found',
                        'updated_at' => $now,
                    ]);

                    if ($publishId > 0 && $db->tableExists('publishes')) {
                        $db->table('publishes')->where('id', $publishId)->update([
                            'status' => 'failed',
                            'error'  => 'Meta page token bulunamadı.',
                            'updated_at' => $now,
                        ]);
                    }

                    $failed++;
                    continue;
                }

                $pageToken = (string)$tokRow['access_token'];

                // status_code check
                $st = $this->httpGetJson(
                    $this->graphUrl($cfg, $creationId, [
                        'fields' => 'status_code',
                        'access_token' => $pageToken,
                    ]),
                    $cfg
                );

                $statusCode = strtoupper((string)($st['status_code'] ?? ''));

                if ($statusCode === '' || !empty($st['error'])) {
                    $attempts++;
                    $this->jobScheduleRetry($creationId, $attempts, 'status_code alınamadı', $st, null);
                    continue;
                }

                if ($statusCode === 'ERROR') {
                    $db->table('meta_media_jobs')->where('id', (int)$job['id'])->update([
                        'status' => 'failed',
                        'status_code' => $statusCode,
                        'attempts' => $attempts + 1,
                        'last_error' => 'Container status ERROR',
                        'last_response_json' => json_encode($st, JSON_UNESCAPED_UNICODE),
                        'updated_at' => $now,
                    ]);

                    if ($publishId > 0 && $db->tableExists('publishes')) {
                        $db->table('publishes')->where('id', $publishId)->update([
                            'status' => 'failed',
                            'error'  => 'Video işleme hatası (ERROR).',
                            'updated_at' => $now,
                        ]);
                    }

                    $failed++;
                    continue;
                }

                if ($statusCode !== 'FINISHED') {
                    $attempts++;
                    $this->jobScheduleRetry($creationId, $attempts, 'IN_PROGRESS', $st, $statusCode);

                    // publish varsa kullanıcıya "yayınlanıyor" kalsın
                    if ($publishId > 0 && $db->tableExists('publishes')) {
                        $db->table('publishes')->where('id', $publishId)->update([
                            'status' => 'publishing',
                            'error'  => null,
                            'updated_at' => $now,
                        ]);
                    }

                    continue;
                }

                // FINISHED -> publish
                $pub = $this->httpPostJson(
                    $this->graphUrl($cfg, $igUserId . '/media_publish'),
                    $cfg,
                    [
                        'creation_id' => $creationId,
                        'access_token' => $pageToken,
                    ]
                );

                if (empty($pub['id'])) {
                    $attempts++;
                    $msg = $pub['error']['message'] ?? 'publish failed';
                    $this->jobScheduleRetry($creationId, $attempts, $msg, $pub, 'FINISHED');
                    continue;
                }

                $publishedMediaId = (string)$pub['id'];

                // permalink çek
                $pl = $this->httpGetJson(
                    $this->graphUrl($cfg, $publishedMediaId, [
                        'fields' => 'permalink',
                        'access_token' => $pageToken,
                    ]),
                    $cfg
                );
                $permalink = (string)($pl['permalink'] ?? '');

                // meta_media_jobs update
                $db->table('meta_media_jobs')->where('id', (int)$job['id'])->update([
                    'status' => 'published',
                    'status_code' => 'FINISHED',
                    'published_media_id' => $publishedMediaId,
                    'published_at' => $now,
                    'attempts' => $attempts + 1,
                    'last_error' => null,
                    'last_response_json' => json_encode($pub, JSON_UNESCAPED_UNICODE),
                    'updated_at' => $now,
                ]);

                // publishes update
                if ($publishId > 0 && $db->tableExists('publishes')) {
                    $metaJson = [
                        'meta' => [
                            'creation_id'  => $creationId,
                            'published_id' => $publishedMediaId,
                            'permalink'    => $permalink,
                        ],
                    ];

                    $db->table('publishes')->where('id', $publishId)->update([
                        'status'       => 'published',
                        'remote_id'    => $publishedMediaId,
                        'published_at' => $now,
                        'error'        => null,
                        'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at'   => $now,
                    ]);
                }

                $published++;
            }
        }

        return $this->response->setJSON([
            'ok' => true,
            'refreshed' => $refreshed,
            'jobs' => [
                'processed' => $processed,
                'published' => $published,
                'failed' => $failed,
            ],
        ]);
    }
    
}
