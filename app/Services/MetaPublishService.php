<?php

namespace App\Services;

class MetaPublishService
{
    private string $graphBase;

    public function __construct()
    {
        $ver = getenv('META_GRAPH_VER') ?: 'v24.0';
        $this->graphBase = 'https://graph.facebook.com/' . $ver;
    }

    public function publishInstagram(array $ctx): array
    {
        $igUserId  = (string)($ctx['ig_user_id'] ?? '');
        $accessTok = (string)($ctx['access_token'] ?? '');
        $postType  = (string)($ctx['post_type'] ?? 'post');   // post|reels|story
        $mediaType = (string)($ctx['media_type'] ?? 'image'); // image|video
        $mediaUrl  = (string)($ctx['media_url'] ?? '');
        $caption   = (string)($ctx['caption'] ?? '');

        // settings (şimdilik opsiyonel, kırmasın)
        $settings  = (array)($ctx['settings'] ?? []);

        if ($igUserId === '' || $accessTok === '' || $mediaUrl === '') {
            throw new \RuntimeException('MetaPublishService: ig_user_id/access_token/media_url zorunlu.');
        }

        if (!preg_match('~^https?://~i', $mediaUrl)) {
            throw new \RuntimeException('MetaPublishService: media_url http/https olmalı.');
        }

        $payload = [
            'access_token' => $accessTok,
            'caption'      => $caption,
        ];

        if ($mediaType === 'video') $payload['video_url'] = $mediaUrl;
        else $payload['image_url'] = $mediaUrl;

        if ($postType === 'reels') {
            if ($mediaType !== 'video') {
                throw new \RuntimeException('Reels için media_type video olmalı.');
            }
            $payload['media_type'] = 'REELS';
        } elseif ($postType === 'story') {
            $payload['media_type'] = 'STORIES';
        } else {
            // post
            if ($mediaType === 'video') {
                $payload['media_type'] = 'REELS';
            }
        }

        $create = $this->post("/{$igUserId}/media", $payload);

        $creationId = (string)($create['id'] ?? '');
        if ($creationId === '') {
            throw new \RuntimeException('Media container oluşturulamadı: id dönmedi.');
        }

        $needsPoll = ($mediaType === 'video') || ($postType === 'story' && $mediaType === 'video');

        if ($needsPoll) {
            $status = $this->get("/{$creationId}", [
                'fields' => 'status_code',
                'access_token' => $accessTok,
            ]);

            $code = strtoupper((string)($status['status_code'] ?? ''));
            if ($code !== 'FINISHED') {
                return [
                    'creation_id'  => $creationId,
                    'published_id' => '',
                    'status_code'  => ($code !== '' ? $code : 'IN_PROGRESS'),
                    'deferred'     => true,
                    'raw'          => [
                        'create'   => $create,
                        'status'   => $status,
                        'settings' => $settings,
                    ],
                ];
            }
        }

        $pub = $this->post("/{$igUserId}/media_publish", [
            'creation_id'  => $creationId,
            'access_token' => $accessTok,
        ]);

        $publishedId = (string)($pub['id'] ?? '');
        if ($publishedId === '') {
            throw new \RuntimeException('Publish başarısız: id dönmedi.');
        }

        return [
            'creation_id'  => $creationId,
            'published_id' => $publishedId,
            'status_code'  => 'FINISHED',
            'deferred'     => false,
            'raw'          => $pub,
        ];
    }

    public function getInstagramPermalink(string $mediaId, string $accessToken): ?string
    {
        $mediaId = trim($mediaId);
        $accessToken = trim($accessToken);
        if ($mediaId === '' || $accessToken === '') return null;

        $res = $this->get("/{$mediaId}", [
            'fields' => 'permalink',
            'access_token' => $accessToken,
        ]);

        $link = (string)($res['permalink'] ?? '');
        return $link !== '' ? $link : null;
    }

    public function publishFacebookPage(array $ctx): array
    {
        $pageId    = (string)($ctx['page_id'] ?? '');
        $accessTok = (string)($ctx['access_token'] ?? '');
        $message   = (string)($ctx['message'] ?? '');
        $mediaUrl  = (string)($ctx['media_url'] ?? '');
        $mediaType = strtolower((string)($ctx['media_type'] ?? ''));

        // opsiyonel alanlar (kırmasın)
        $privacy = strtolower(trim((string)($ctx['privacy'] ?? '')));
        $allowComments = (bool)($ctx['allow_comments'] ?? true);
        $settings = (array)($ctx['settings'] ?? []);

        if ($pageId === '' || $accessTok === '') {
            throw new \RuntimeException('MetaPublishService: page_id/access_token zorunlu.');
        }

        // 1) Text-only post
        if ($mediaUrl === '') {
            $res = $this->post("/{$pageId}/feed", [
                'message'      => $message,
                'access_token' => $accessTok,
            ]);

            $id = (string)($res['id'] ?? '');
            if ($id === '') throw new \RuntimeException('Facebook feed post başarısız: id dönmedi.');

            return [
                'published_id' => $id,
                'raw'          => $res,
            ];
        }

        if (!preg_match('~^https?://~i', $mediaUrl)) {
            throw new \RuntimeException('MetaPublishService: media_url http/https olmalı.');
        }

        // 2) Image post
        if ($mediaType !== 'video') {
            $res = $this->post("/{$pageId}/photos", [
                'url'          => $mediaUrl,
                'message'      => $message,
                'published'    => 'true',
                'access_token' => $accessTok,
            ]);

            $id = (string)($res['post_id'] ?? $res['id'] ?? '');
            if ($id === '') throw new \RuntimeException('Facebook photo post başarısız: id dönmedi.');

            return [
                'published_id' => $id,
                'raw'          => $res,
            ];
        }

        // 3) Video post
        $res = $this->post("/{$pageId}/videos", [
            'file_url'      => $mediaUrl,
            'description'   => $message,
            'access_token'  => $accessTok,
        ]);

        $id = (string)($res['id'] ?? '');
        if ($id === '') throw new \RuntimeException('Facebook video post başarısız: id dönmedi.');

        return [
            'published_id' => $id,
            'raw'          => $res,
        ];
    }

    public function getFacebookPermalink(string $postId, string $accessToken): ?string
    {
        $postId = trim($postId);
        $accessToken = trim($accessToken);
        if ($postId === '' || $accessToken === '') return null;

        $res = $this->get("/{$postId}", [
            'fields' => 'permalink_url',
            'access_token' => $accessToken,
        ]);

        $link = (string)($res['permalink_url'] ?? '');
        return $link !== '' ? $link : null;
    }

    public function getInstagramContainerStatus(string $creationId, string $accessToken): array
    {
        $creationId = trim($creationId);
        $accessToken = trim($accessToken);

        if ($creationId === '' || $accessToken === '') {
            throw new \RuntimeException('creation_id/access_token zorunlu.');
        }

        $raw = $this->get("/{$creationId}", [
            'fields' => 'status_code',
            'access_token' => $accessToken,
        ]);

        $code = strtoupper((string)($raw['status_code'] ?? ''));

        return [
            'status_code' => $code,
            'raw' => $raw,
        ];
    }

    public function publishInstagramContainer(string $igUserId, string $creationId, string $accessToken): array
    {
        $igUserId = trim($igUserId);
        $creationId = trim($creationId);
        $accessToken = trim($accessToken);

        if ($igUserId === '' || $creationId === '' || $accessToken === '') {
            throw new \RuntimeException('ig_user_id/creation_id/access_token zorunlu.');
        }

        $raw = $this->post("/{$igUserId}/media_publish", [
            'creation_id'  => $creationId,
            'access_token' => $accessToken,
        ]);

        return [
            'published_id' => (string)($raw['id'] ?? ''),
            'raw' => $raw,
        ];
    }

    private function get(string $path, array $query): array
    {
        $url = $this->graphBase . $path . '?' . http_build_query($query);

        $resp = $this->curl('GET', $url, null);

        $json = json_decode((string)$resp['body'], true);
        if (!is_array($json)) $json = [];

        if (isset($json['error'])) {
            $this->throwGraphError($json['error'], $resp['http_code']);
        }

        if ($resp['http_code'] >= 400) {
            throw new \RuntimeException("Meta HTTP {$resp['http_code']}: " . substr((string)$resp['body'], 0, 300));
        }

        return $json;
    }

    private function post(string $path, array $data): array
    {
        $url = $this->graphBase . $path;

        $resp = $this->curl('POST', $url, http_build_query($data), [
            'Content-Type: application/x-www-form-urlencoded',
        ]);

        $json = json_decode((string)$resp['body'], true);
        if (!is_array($json)) $json = [];

        if (isset($json['error'])) {
            $this->throwGraphError($json['error'], $resp['http_code']);
        }

        if ($resp['http_code'] >= 400) {
            throw new \RuntimeException("Meta HTTP {$resp['http_code']}: " . substr((string)$resp['body'], 0, 300));
        }

        return $json;
    }

    private function curl(string $method, string $url, ?string $body = null, array $headers = []): array
    {
        $ch = curl_init($url);

        $h = [];
        foreach ($headers as $line) $h[] = $line;

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_HTTPHEADER     => $h,
            CURLOPT_CUSTOMREQUEST  => strtoupper($method),
            CURLOPT_SSL_VERIFYPEER => (getenv('META_VERIFY_SSL') === false || getenv('META_VERIFY_SSL') === '0') ? false : true,
            CURLOPT_SSL_VERIFYHOST => (getenv('META_VERIFY_SSL') === false || getenv('META_VERIFY_SSL') === '0') ? 0 : 2,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body ?? '');
        }

        $respBody = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err      = curl_error($ch);
        curl_close($ch);

        if ($respBody === false) {
            throw new \RuntimeException("Meta {$method} failed: {$err}");
        }

        return [
            'http_code' => $httpCode,
            'body'      => $respBody,
        ];
    }

    private function throwGraphError(array $e, int $httpCode): void
    {
        $msg   = (string)($e['message'] ?? 'Meta error');
        $code  = (string)($e['code'] ?? '');
        $sub   = (string)($e['error_subcode'] ?? '');
        $trace = (string)($e['fbtrace_id'] ?? '');
        throw new \RuntimeException("Meta error ({$httpCode}) {$msg} | code={$code} subcode={$sub} trace={$trace}");
    }
}
