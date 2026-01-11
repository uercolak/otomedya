<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class MetaOAuthController extends BaseController
{
    private function metaConfig(): array
    {
        $scopesEnv = trim((string) getenv('META_SCOPES'));

        $defaultScopes = [
            'public_profile',
            'pages_show_list',
            'pages_read_engagement',
            'instagram_basic',
            'instagram_content_publish',
            'business_management',
            'pages_manage_posts',
        ];

        $scopes = $defaultScopes;

        // ENV varsa override ETME, merge ET
        if ($scopesEnv !== '') {
            $parts = array_filter(array_map('trim', explode(',', $scopesEnv)));
            if (!empty($parts)) {
                $scopes = array_values(array_unique(array_merge($defaultScopes, $parts)));
            }
        } else {
            $scopes = array_values(array_unique($defaultScopes));
        }

        // safety: her durumda business_management garantile
        if (!in_array('business_management', $scopes, true)) {
            $scopes[] = 'business_management';
        }

        return [
            'app_id'       => (string) (getenv('META_APP_ID') ?: ''),
            'app_secret'   => (string) (getenv('META_APP_SECRET') ?: ''),
            'redirect_uri' => (string) (getenv('META_REDIRECT_URI') ?: site_url('panel/social-accounts/meta/callback')),

            'graph_ver'    => (string) (getenv('META_GRAPH_VER') ?: 'v24.0'),
            'verify_ssl'   => (getenv('META_VERIFY_SSL') !== false && getenv('META_VERIFY_SSL') !== '0'),

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

    private function isExpiringSoon(?string $expiresAt, int $thresholdDays): bool
    {
        if (!$expiresAt) return false;
        $ts = strtotime($expiresAt);
        if (!$ts) return false;
        return ($ts - time()) <= ($thresholdDays * 86400);
    }

    /* ===================== DB: meta_tokens ===================== */

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
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);
    }

    private function saveUserAccessToken(int $userId, string $accessToken, ?string $expiresAt, array $extraMeta = []): void
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

            if ($db->fieldExists('meta_json', 'meta_tokens')) {
                $meta = [];
                if (!empty($existing['meta_json'])) {
                    $tmp = json_decode((string) $existing['meta_json'], true);
                    if (is_array($tmp)) $meta = $tmp;
                }
                $meta = array_merge($meta, $extraMeta);

                $db->table('meta_tokens')->where('user_id', $userId)->update([
                    'meta_json' => json_encode($meta, JSON_UNESCAPED_UNICODE),
                ]);
            }
            return;
        }

        $insert = [
            'user_id'      => $userId,
            'access_token' => $accessToken,
            'expires_at'   => $expiresAt,
            'created_at'   => $now,
            'updated_at'   => $now,
        ];

        if ($db->fieldExists('meta_json', 'meta_tokens')) {
            $insert['meta_json'] = json_encode($extraMeta, JSON_UNESCAPED_UNICODE);
        }

        $db->table('meta_tokens')->insert($insert);
    }

    private function getUserAccessTokenOrNull(int $userId): ?string
    {
        $row = $this->getMetaTokenRow($userId);
        $tok = $row['access_token'] ?? null;
        if (!$tok) return null;
        if (trim((string)$tok) === '') return null;
        return (string) $tok;
    }

    /* ===================== DB helpers: page id discovery ===================== */

    private function getKnownPageIdsForUser(int $userId): array
    {
        $db = $this->db();
        $pageIds = [];

        if ($db->fieldExists('meta_page_id', 'social_accounts')) {
            $rows = $db->table('social_accounts')
                ->select('meta_page_id')
                ->where('user_id', $userId)
                ->where('meta_page_id IS NOT NULL', null, false)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $pid = trim((string) ($r['meta_page_id'] ?? ''));
                if ($pid !== '') $pageIds[] = $pid;
            }
        }

        $rows2 = $db->table('social_account_tokens')
            ->select('meta_json')
            ->join('social_accounts', 'social_accounts.id = social_account_tokens.social_account_id', 'left')
            ->where('social_accounts.user_id', $userId)
            ->where('social_account_tokens.provider', 'meta')
            ->get()->getResultArray();

        foreach ($rows2 as $r) {
            $mj = $r['meta_json'] ?? null;
            if (!$mj) continue;
            $arr = json_decode((string)$mj, true);
            if (!is_array($arr)) continue;
            $pid = trim((string)($arr['page_id'] ?? ''));
            if ($pid !== '') $pageIds[] = $pid;
        }

        return array_values(array_unique(array_filter($pageIds)));
    }

    /* ===================== DB: social_accounts + social_account_tokens ===================== */

    private function upsertSocialAccountInstagram(int $userId, array $ig, string $pageId, string $pageName): int
    {
        $db  = $this->db();
        $now = date('Y-m-d H:i:s');

        $externalId = (string) ($ig['id'] ?? '');
        $username   = (string) ($ig['username'] ?? '');
        $name       = (string) ($ig['name'] ?? ($username ?: 'Instagram'));
        $avatar     = (string) ($ig['profile_picture_url'] ?? '');

        $existing = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->where('platform', 'instagram')
            ->where('external_id', $externalId)
            ->get()->getRowArray();

        if ($existing) {
            $db->table('social_accounts')->where('id', (int)$existing['id'])->update([
                'name'        => $name,
                'username'    => $username ?: null,
                'avatar_url'  => $avatar ?: null,
                'meta_page_id'=> $pageId,
                'updated_at'  => $now,
            ]);
            return (int) $existing['id'];
        }

        $db->table('social_accounts')->insert([
            'user_id'      => $userId,
            'platform'     => 'instagram',
            'external_id'  => $externalId,
            'name'         => $name,
            'username'     => $username ?: null,
            'avatar_url'   => $avatar ?: null,
            'meta_page_id' => $pageId,
            'access_token' => null,
            'token_expires_at' => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ]);

        return (int) $db->insertID();
    }


    private function upsertSocialAccountFacebookPage(int $userId, string $pageId, string $pageName): int
    {
        $db = \Config\Database::connect();
        $now = date('Y-m-d H:i:s');

        // Page zaten kayıtlı mı? (user + platform + external_id)
        $existing = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->where('platform', 'facebook')
            ->where('external_id', $pageId)
            ->get()->getRowArray();

        $data = [
            'user_id'     => $userId,
            'platform'    => 'facebook',
            'external_id' => $pageId,   // page_id
            'meta_page_id'=> $pageId,   // tutarlılık için aynı
            'name'        => $pageName ?: ('Facebook Page ' . $pageId),
            'username'    => null,
            'avatar_url'  => null,
            'updated_at'  => $now,
        ];

        if ($existing) {
            $db->table('social_accounts')->where('id', (int)$existing['id'])->update($data);
            return (int)$existing['id'];
        }

        $data['created_at'] = $now;
        $db->table('social_accounts')->insert($data);

        return (int)$db->insertID();
    }

    private function upsertMetaPageToken(int $socialAccountId, string $pageToken, ?string $expiresAt, array $metaJson): void
    {
        $db  = $this->db();
        $now = date('Y-m-d H:i:s');

        $existing = $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'meta')
            ->get()->getRowArray();

        $payload = [
            'social_account_id' => $socialAccountId,
            'provider'          => 'meta',
            'access_token'      => $pageToken,
            'token_type'        => 'page',
            'expires_at'        => $expiresAt,
            'scope'             => null,
            'meta_json'         => json_encode($metaJson, JSON_UNESCAPED_UNICODE),
            'updated_at'        => $now,
        ];

        if ($existing) {
            $db->table('social_account_tokens')->where('id', (int)$existing['id'])->update($payload);
        } else {
            $payload['created_at'] = $now;
            $db->table('social_account_tokens')->insert($payload);
        }
    }

    /* ===================== NEW: meta_media_jobs helpers ===================== */

    private function jobsEnabled(): bool
    {
        return $this->db()->tableExists('meta_media_jobs');
    }

    private function jobInsertOrUpdate(array $data): void
    {
        if (!$this->jobsEnabled()) return;

        $db = $this->db();
        $now = date('Y-m-d H:i:s');

        $creationId = (string)($data['creation_id'] ?? '');
        if ($creationId === '') return;

        $existing = $db->table('meta_media_jobs')->where('creation_id', $creationId)->get()->getRowArray();

        $base = array_merge([
            'updated_at' => $now,
        ], $data);

        if ($existing) {
            unset($base['created_at']);
            $db->table('meta_media_jobs')->where('id', (int)$existing['id'])->update($base);
        } else {
            $base['created_at'] = $now;
            if (!isset($base['attempts'])) $base['attempts'] = 0;
            $db->table('meta_media_jobs')->insert($base);
        }
    }

    private function jobScheduleRetry(string $creationId, int $attempts, ?string $lastError = null, ?array $lastResp = null, ?string $statusCode = null): void
    {
        if (!$this->jobsEnabled()) return;

        $retryBase = (int)(getenv('META_MEDIA_JOB_RETRY_SECONDS') ?: 20); // default 20s
        $maxBackoff = (int)(getenv('META_MEDIA_JOB_MAX_BACKOFF_SECONDS') ?: 300); // 5dk max
        $delay = min($maxBackoff, $retryBase * max(1, $attempts));

        $next = date('Y-m-d H:i:s', time() + $delay);

        $this->jobInsertOrUpdate([
            'creation_id' => $creationId,
            'status' => 'processing',
            'status_code' => $statusCode,
            'attempts' => $attempts,
            'next_retry_at' => $next,
            'last_error' => $lastError,
            'last_response_json' => $lastResp ? json_encode($lastResp, JSON_UNESCAPED_UNICODE) : null,
        ]);
    }

    /* ===================== OAuth Flow ===================== */

    public function consent()
    {
        $userId = $this->userId();
        $this->markConsentAccepted($userId);
        return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
            ->with('success', 'Onay kaydedildi. Şimdi Meta ile bağlanabilirsin.');
    }

    public function connect()
    {
        $cfg = $this->metaConfig();
        $userId = $this->userId();

        if (!$this->hasConsent($userId)) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Devam etmek için önce onayı kabul etmelisin.');
        }

        $state = bin2hex(random_bytes(16));
        session()->set('meta_oauth_state', $state);

        $loginUrl = 'https://www.facebook.com/' . $cfg['graph_ver'] . '/dialog/oauth?' . http_build_query([
            'client_id'     => $cfg['app_id'],
            'redirect_uri'  => $cfg['redirect_uri'],
            'state'         => $state,
            'response_type' => 'code',
            'scope'         => implode(',', $cfg['scopes']),
        ]);

        return redirect()->to($loginUrl);
    }

    public function callback()
    {
        $cfg = $this->metaConfig();
        $userId = $this->userId();

        $state = (string) $this->request->getGet('state');
        $expected = (string) session('meta_oauth_state');
        if (!$expected || !$state || !hash_equals($expected, $state)) {
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'OAuth state doğrulanamadı. Tekrar dene.');
        }

        $code = (string) $this->request->getGet('code');
        if (!$code) {
            $err = (string) $this->request->getGet('error_message');
            return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
                ->with('error', 'Meta callback hatası: ' . ($err ?: 'code yok'));
        }

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
        $expiresAt  = null;

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

        $this->saveUserAccessToken($userId, $finalToken, $expiresAt, [
            'debug_token' => $debugData,
        ]);

        return redirect()->to(site_url('panel/social-accounts/meta/wizard'))
            ->with('success', 'Meta bağlantısı tamamlandı.');
    }

    private function httpGetAllData(array $cfg, string $path, array $query): array
    {
        $out = [];
        $pages = 0;

        $url = $this->graphUrl($cfg, $path, $query);

        while ($url && $pages < 20) { // safety
            $pages++;

            $resp = $this->httpGetJson($url, $cfg);

            if (!empty($resp['error'])) {
                // hata olursa kır
                break;
            }

            if (!empty($resp['data']) && is_array($resp['data'])) {
                foreach ($resp['data'] as $row) $out[] = $row;
            }

            // paging next
            $next = $resp['paging']['next'] ?? null;
            $url = is_string($next) && $next !== '' ? $next : null;

            if (!$url) break;
        }

        return [
            'data'  => $out,
            '_meta' => [
                'pages' => $pages,
            ],
        ];
    }

    private function extractPageIdsFromGraphList(array $list): array
    {
        $ids = [];
        if (!empty($list['data']) && is_array($list['data'])) {
            foreach ($list['data'] as $p) {
                if (!empty($p['id'])) $ids[] = (string)$p['id'];
            }
        }
        return $ids;
    }

    private function discoverPageIdsViaBusinesses(array $cfg, string $userToken): array
    {
        $debug = [];

        // 1) businesses
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
                // bazı hesaplarda owned_pages çalışır, bazılarında client_pages
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

        return [
            'page_ids' => $pageIds,
            'debug'    => $debug,
        ];
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

            // B) Alternatif field-based (bazı hesaplarda farklı davranabiliyor)
            $pagesB = $this->httpGetJson(
                $this->graphUrl($cfg, 'me', [
                    'fields' => 'accounts.limit(200){id,name,instagram_business_account,connected_instagram_account}',
                    'access_token' => $userToken,
                ]),
                $cfg
            );
            $debug['pages_me_fields_accounts'] = $pagesB;

            $pageIdsToTry = [];

            // A kaynak
            if (!empty($pagesA['data']) && is_array($pagesA['data'])) {
                foreach ($pagesA['data'] as $p) {
                    if (!empty($p['id'])) $pageIdsToTry[] = (string)$p['id'];
                }
            }

            // B kaynak
            if (!empty($pagesB['accounts']['data']) && is_array($pagesB['accounts']['data'])) {
                foreach ($pagesB['accounts']['data'] as $p) {
                    if (!empty($p['id'])) $pageIdsToTry[] = (string)$p['id'];
                }
            }

            // ✅ C) Business fallback (A + B boşsa devreye gir)
            if (empty($pageIdsToTry)) {
                $fallback = $this->discoverPageIdsViaBusinesses($cfg, $userToken);
                $debug['business_fallback'] = $fallback['debug'] ?? [];
                foreach (($fallback['page_ids'] ?? []) as $pid) $pageIdsToTry[] = (string)$pid;
            }

            // manual page id
            $manualPageId = trim((string) $this->request->getGet('page_id'));
            if ($manualPageId !== '') $pageIdsToTry[] = $manualPageId;

            // page_ref (link/username) -> resolve
            $pageRef = trim((string) $this->request->getGet('page_ref'));
            if ($pageRef !== '') {
                $resolved = $this->resolvePageIdFromRef($cfg, $userToken, $pageRef);
                $debug['page_ref'] = $pageRef;
                $debug['page_ref_resolved'] = $resolved;

                if (!empty($resolved['id'])) {
                    $pageIdsToTry[] = (string)$resolved['id'];
                }
            }

            // last_page_id
            $metaRow = $this->getMetaTokenRow($userId);
            if ($metaRow && !empty($metaRow['meta_json'])) {
                $mj = json_decode((string)$metaRow['meta_json'], true);
                if (is_array($mj)) {
                    $last = trim((string)($mj['last_page_id'] ?? ''));
                    if ($last !== '') $pageIdsToTry[] = $last;
                }
            }

            $debug['scopes_requested'] = $cfg['scopes'];

            // DB fallback: known page ids
            $known = $this->getKnownPageIdsForUser($userId);
            foreach ($known as $pid) $pageIdsToTry[] = $pid;

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
                if (empty($igNode['id'])) {
                    $igNode = $bundle['connected_instagram_account'] ?? null;
                }
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
        }

        // remove duplicates
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
        $fbAccountId = $this->upsertSocialAccountFacebookPage($userId, $pageId, $pageName);

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

    /* ===================== Health / Cron ===================== */

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

    /* ===================== Publish Test UI ===================== */

    public function publishTestForm()
    {
        $userId = $this->userId();
        $db = $this->db();

        $rows = $db->table('social_accounts')
            ->where('user_id', $userId)
            ->where('platform', 'instagram')
            ->orderBy('id', 'DESC')
            ->get()->getResultArray();

        return view('panel/social_accounts/meta_publish_test', [
            'accounts' => $rows,
            'default_media_url' => (string) (getenv('META_TEST_MEDIA_URL') ?: ''),
        ]);
    }

    /* ===================== Publish Test Action ===================== */

    public function testPublish()
    {
        $cfg = $this->metaConfig();
        $userId = $this->userId();
        $db = $this->db();

        $socialAccountId = (int) $this->request->getPost('social_account_id');
        $type = strtolower(trim((string) $this->request->getPost('type')));
        $mediaKind = strtolower(trim((string) $this->request->getPost('media_kind')));
        $mediaUrl = trim((string) $this->request->getPost('media_url'));
        $caption = trim((string) $this->request->getPost('caption'));

        $mapType = [
            'post' => 'post',
            'reels' => 'reels',
            'story' => 'story',
            'stories' => 'story',
            'reel' => 'reels',
            'feed' => 'post',
            'gönderi' => 'post',
            'hikaye' => 'story',
        ];
        if (isset($mapType[$type])) $type = $mapType[$type];
        if ($type === '') {
            $t2 = strtolower(trim((string)$this->request->getPost('tip')));
            if (isset($mapType[$t2])) $type = $mapType[$t2];
        }

        if (!$socialAccountId || $mediaUrl === '') {
            return redirect()->back()->with('error', 'Eksik bilgi: hesap veya media URL');
        }

        if (!in_array($type, ['post','reels','story'], true)) {
            return redirect()->back()->with('error', 'Tip geçersiz');
        }

        if (!in_array($mediaKind, ['image','video'], true)) {
            $mediaKind = 'image';
        }

        $acc = $db->table('social_accounts')
            ->where('id', $socialAccountId)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (!$acc) return redirect()->back()->with('error', 'Hesap bulunamadı');

        $igUserId = (string) ($acc['external_id'] ?? '');
        if ($igUserId === '') return redirect()->back()->with('error', 'IG external_id yok');

        $tokRow = $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'meta')
            ->get()->getRowArray();

        if (!$tokRow || empty($tokRow['access_token'])) {
            return redirect()->back()->with('error', 'Page token bulunamadı. Wizard’da tekrar bağla.');
        }

        $pageToken = (string) $tokRow['access_token'];
        $now = date('Y-m-d H:i:s');

        // Container create
        $createParams = ['access_token' => $pageToken];

        if ($type === 'post') {
            if ($mediaKind === 'image') {
                $createParams['image_url'] = $mediaUrl;
                if ($caption !== '') $createParams['caption'] = $caption;
            } else {
                return redirect()->back()->with('error', 'Video post artık desteklenmiyor. Tip=Reels seç.');
            }
        } elseif ($type === 'reels') {
            $createParams['video_url'] = $mediaUrl;
            $createParams['media_type'] = 'REELS';
            if ($caption !== '') $createParams['caption'] = $caption;
        } elseif ($type === 'story') {
            $createParams['media_type'] = 'STORIES';
            if ($mediaKind === 'image') $createParams['image_url'] = $mediaUrl;
            else $createParams['video_url'] = $mediaUrl;
        }

        $createRes = $this->httpPostJson(
            $this->graphUrl($cfg, $igUserId . '/media'),
            $cfg,
            $createParams
        );

        if (empty($createRes['id'])) {
            $msg = $createRes['error']['message'] ?? json_encode($createRes);
            return redirect()->back()->with('error', 'Media oluşturma hatası: ' . $msg);
        }

        $creationId = (string) $createRes['id'];

        // Job insert
        $this->jobInsertOrUpdate([
            'user_id' => $userId,
            'social_account_id' => $socialAccountId,
            'ig_user_id' => $igUserId,
            'page_id' => (string)($acc['meta_page_id'] ?? null),
            'creation_id' => $creationId,
            'type' => $type,
            'media_kind' => $this->jobsEnabled() ? $mediaKind : null,
            'media_url' => $mediaUrl,
            'caption' => ($caption !== '' ? $caption : null),
            'status' => 'created',
            'attempts' => 0,
            'next_retry_at' => $now,
            'last_response_json' => json_encode($createRes, JSON_UNESCAPED_UNICODE),
        ]);

        // Quick poll
        $maxWaitSeconds = (int)(getenv('META_VIDEO_PROCESS_MAX_WAIT') ?: 30);
        $intervalSeconds = (int)(getenv('META_VIDEO_PROCESS_POLL_INTERVAL') ?: 3);
        $maxTry = max(1, (int) floor($maxWaitSeconds / $intervalSeconds));

        $ready = false;
        $lastStatus = null;
        $lastStatusResp = null;

        for ($i = 0; $i < $maxTry; $i++) {
            $st = $this->httpGetJson(
                $this->graphUrl($cfg, $creationId, [
                    'fields' => 'status_code',
                    'access_token' => $pageToken,
                ]),
                $cfg
            );
            $lastStatusResp = $st;
            $lastStatus = $st['status_code'] ?? null;

            if ($lastStatus === 'FINISHED') { $ready = true; break; }
            if ($lastStatus === 'ERROR') break;

            sleep($intervalSeconds);
        }

        if (!$ready) {
            $attempts = 1;
            $this->jobScheduleRetry(
                $creationId,
                $attempts,
                'IN_PROGRESS',
                $lastStatusResp ?: null,
                $lastStatus ? (string)$lastStatus : null
            );

            return redirect()->back()->with('error',
                'Video işleniyor. Kuyruğa alındı ✅ Cron otomatik yayınlayacak. Creation: ' . $creationId
                . ($lastStatus ? (' (status=' . $lastStatus . ')') : '')
            );
        }

        // Publish now
        $publishRes = $this->httpPostJson(
            $this->graphUrl($cfg, $igUserId . '/media_publish'),
            $cfg,
            [
                'creation_id' => $creationId,
                'access_token' => $pageToken,
            ]
        );

        if (empty($publishRes['id'])) {
            $msg = $publishRes['error']['message'] ?? json_encode($publishRes);

            $this->jobScheduleRetry($creationId, 1, $msg, $publishRes, 'FINISHED');
            return redirect()->back()->with('error', 'Publish hatası: ' . $msg);
        }

        // Job published
        $this->jobInsertOrUpdate([
            'creation_id' => $creationId,
            'status' => 'published',
            'status_code' => 'FINISHED',
            'published_media_id' => (string)$publishRes['id'],
            'published_at' => date('Y-m-d H:i:s'),
            'attempts' => 1,
            'last_response_json' => json_encode($publishRes, JSON_UNESCAPED_UNICODE),
        ]);

        return redirect()->back()->with('success', 'Paylaşım tetiklendi ✅ Media ID: ' . (string)$publishRes['id']);
    }
}
