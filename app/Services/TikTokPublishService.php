<?php

namespace App\Services;

class TikTokPublishService
{
    public function initVideoPublish(string $accessToken, string $caption, int $videoSize): array
    {
        $url = 'https://open.tiktokapis.com/v2/post/publish/video/init/';

        // TikTok caption içine hashtag direkt yazılır: "Merhaba #etiket"
        $body = json_encode([
            'post_info' => [
                'title' => mb_substr($caption, 0, 150),   // güvenli kısaltma
                'description' => $caption,               // caption/hashtag burada
                'privacy_level' => 'PUBLIC_TO_EVERYONE', // istersen UI’dan seçtiririz
                'disable_comment' => false,
                'disable_duet' => false,
                'disable_stitch' => false,
            ],
            'source_info' => [
                'source' => 'FILE_UPLOAD',
                'video_size' => $videoSize,
                'chunk_size' => $videoSize,
                'total_chunk_count' => 1,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $this->jsonPost($url, $accessToken, $body);
    }

    public function uploadToUrl(string $uploadUrl, string $filePath): void
    {
        $fp = fopen($filePath, 'rb');
        if (!$fp) throw new \RuntimeException('TikTok upload: dosya açılamadı: ' . $filePath);

        $data = stream_get_contents($fp);
        fclose($fp);

        if ($data === false) throw new \RuntimeException('TikTok upload: dosya okunamadı.');

        $ch = curl_init($uploadUrl);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_POSTFIELDS     => $data,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: video/mp4',
            ],
            CURLOPT_TIMEOUT        => 0,
        ]);

        $resp = curl_exec($ch);
        $http = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException('TikTok upload failed HTTP=' . $http . ' ERR=' . $err . ' RESP=' . substr((string)$resp, 0, 800));
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
