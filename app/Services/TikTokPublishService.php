<?php

namespace App\Services;

class TikTokPublishService
{
    /**
     * @param array $options
     *  - privacy: public|private|followers|friends  (opsiyonel)
     *  - allow_comments: bool (default true)
     *  - allow_duet: bool (default true)
     *  - allow_stitch: bool (default true)
     */
    public function initVideoPublish(string $accessToken, string $caption, int $videoSize, array $options = []): array
    {
        $url = 'https://open.tiktokapis.com/v2/post/publish/video/init/';

        // ✅ privacy: options > env
        $privacy = $this->resolvePrivacyLevel($options);

        // ✅ toggles: options (default true)
        $allowComments = array_key_exists('allow_comments', $options) ? (bool)$options['allow_comments'] : true;
        $allowDuet     = array_key_exists('allow_duet', $options) ? (bool)$options['allow_duet'] : true;
        $allowStitch   = array_key_exists('allow_stitch', $options) ? (bool)$options['allow_stitch'] : true;

        // TikTok API: disable_* bekliyor
        $disableComment = !$allowComments;
        $disableDuet    = !$allowDuet;
        $disableStitch  = !$allowStitch;

        $safeTitle = trim($caption) !== '' ? trim($caption) : ' ';
        $safeTitle = mb_substr($safeTitle, 0, 150);

        $videoSize = max(1, (int)$videoSize);

        $body = json_encode([
            'post_info' => [
                'title'            => $safeTitle,
                'description'      => $caption,
                'privacy_level'    => $privacy,
                'disable_comment'  => $disableComment,
                'disable_duet'     => $disableDuet,
                'disable_stitch'   => $disableStitch,
            ],
            'source_info' => [
                'source'            => 'FILE_UPLOAD',
                'video_size'        => $videoSize,
                'chunk_size'        => $videoSize,
                'total_chunk_count' => 1,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->jsonPost($url, $accessToken, $body);
    }

    public function uploadToUrl(string $uploadUrl, string $filePath): void
    {
        if (!is_file($filePath) || !is_readable($filePath)) {
            throw new \RuntimeException('TikTok upload: dosya okunamadı.');
        }

        $size = filesize($filePath);
        if ($size === false || (int)$size <= 0) {
            throw new \RuntimeException('TikTok upload: dosya boyutu okunamadı.');
        }

        $fp = fopen($filePath, 'rb');
        if (!$fp) {
            throw new \RuntimeException('TikTok upload: dosya açılamadı.');
        }

        $start = 0;
        $end   = $size - 1;

        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_UPLOAD         => true,
            CURLOPT_INFILE         => $fp,
            CURLOPT_INFILESIZE     => $size,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: video/mp4',
                'Content-Length: ' . $size,
                'Content-Range: bytes ' . $start . '-' . $end . '/' . $size,
                'Expect:',
            ],
            CURLOPT_TIMEOUT        => 0,
        ]);

        $resp = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);

        curl_close($ch);
        fclose($fp);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException(
                'TikTok upload failed HTTP=' . $http .
                ' ERR=' . $err .
                ' RESP=' . substr((string)$resp, 0, 1200)
            );
        }
    }

    public function fetchPublishStatus(string $accessToken, string $publishId): array
    {
        $url = 'https://open.tiktokapis.com/v2/post/publish/status/fetch/';

        $body = json_encode([
            'publish_id' => $publishId,
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->jsonPost($url, $accessToken, $body);
    }

    private function resolvePrivacyLevel(array $options): string
    {
        // TikTok API allowed values:
        // SELF_ONLY, PUBLIC_TO_EVERYONE, MUTUAL_FOLLOW_FRIENDS, FOLLOWER_OF_CREATOR
        $fromOptions = strtolower(trim((string)($options['privacy'] ?? '')));

        if ($fromOptions !== '') {
            return match ($fromOptions) {
                'public', 'everyone'     => 'PUBLIC_TO_EVERYONE',
                'private', 'self'        => 'SELF_ONLY',
                'followers', 'follower'  => 'FOLLOWER_OF_CREATOR',
                'friends', 'mutual'      => 'MUTUAL_FOLLOW_FRIENDS',
                default                  => 'SELF_ONLY',
            };
        }

        // ENV fallback (senin mevcut yapın)
        $privacy = strtoupper(trim((string) env('TIKTOK_PRIVACY_LEVEL', 'SELF_ONLY')));
        $allowed = ['SELF_ONLY', 'PUBLIC_TO_EVERYONE', 'MUTUAL_FOLLOW_FRIENDS', 'FOLLOWER_OF_CREATOR'];
        if (!in_array($privacy, $allowed, true)) $privacy = 'SELF_ONLY';
        return $privacy;
    }

    private function jsonPost(string $url, string $accessToken, string $jsonBody): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json; charset=UTF-8',
            ],
            CURLOPT_TIMEOUT        => 60,
        ]);

        $resp = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException("TikTok API failed HTTP={$http} ERR={$err} RESP=" . substr((string)$resp, 0, 800));
        }

        $json = json_decode((string)$resp, true);
        if (!is_array($json)) {
            throw new \RuntimeException('TikTok API JSON parse edilemedi: ' . substr((string)$resp, 0, 400));
        }

        return $json;
    }
}
