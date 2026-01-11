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
                // IG feed video çoğu zaman reels’e düşüyor
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
                // ✅ artık exception yok: deferred
                return [
                    'creation_id'  => $creationId,
                    'published_id' => '',
                    'status_code'  => ($code !== '' ? $code : 'IN_PROGRESS'),
                    'deferred'     => true,
                    'raw'          => [
                        'create' => $create,
                        'status' => $status,
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

    private function get(string $path, array $query): array
    {
        $url = $this->graphBase . $path . '?' . http_build_query($query);
        $res = @file_get_contents($url);
        if ($res === false) {
            throw new \RuntimeException('Meta GET failed: ' . $url);
        }
        $json = json_decode($res, true);
        if (isset($json['error'])) {
            $msg = $json['error']['message'] ?? 'Meta error';
            throw new \RuntimeException($msg);
        }
        return $json ?: [];
    }

    private function post(string $path, array $data): array
    {
        $url = $this->graphBase . $path;

        $opts = [
            'http' => [
                'method'        => 'POST',
                'header'        => "Content-type: application/x-www-form-urlencoded\r\n",
                'content'       => http_build_query($data),
                'timeout'       => 60,
                'ignore_errors' => true, 
            ],
        ];

        $ctx = stream_context_create($opts);
        $res = @file_get_contents($url, false, $ctx);

        $statusLine = $http_response_header[0] ?? '';
        preg_match('~HTTP/\S+\s+(\d+)~', $statusLine, $m);
        $httpCode = isset($m[1]) ? (int)$m[1] : 0;

        if ($res === false) {
            $err = error_get_last();
            throw new \RuntimeException('Meta POST failed: ' . $path . ' | ' . ($err['message'] ?? 'unknown'));
        }

        $json = json_decode($res, true);

        // Graph error varsa hepsini yaz
        if (is_array($json) && isset($json['error'])) {
            $e = $json['error'];
            $msg = $e['message'] ?? 'Meta error';
            $code = $e['code'] ?? '';
            $sub = $e['error_subcode'] ?? '';
            $trace = $e['fbtrace_id'] ?? '';
            throw new \RuntimeException("Meta error ({$httpCode}) {$msg} | code={$code} subcode={$sub} trace={$trace}");
        }

        // Bazı durumlarda 200 ama json boş olabilir
        if ($httpCode >= 400) {
            throw new \RuntimeException("Meta HTTP {$httpCode}: " . substr($res, 0, 300));
        }

        return is_array($json) ? $json : [];
    }

    public function publishFacebookPage(array $ctx): array
    {
        $pageId    = (string)($ctx['page_id'] ?? '');
        $accessTok = (string)($ctx['access_token'] ?? '');
        $message   = (string)($ctx['message'] ?? '');
        $mediaUrl  = (string)($ctx['media_url'] ?? '');
        $mediaType = strtolower((string)($ctx['media_type'] ?? ''));

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
}
