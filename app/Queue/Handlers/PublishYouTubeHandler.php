<?php

namespace App\Queue\Handlers;

use App\Queue\JobHandlerInterface;
use App\Models\PublishModel;
use App\Services\YouTubePublishService;
use Config\Database;

class PublishYouTubeHandler implements JobHandlerInterface
{
    private PublishModel $publishes;
    private YouTubePublishService $yt;

    public function __construct()
    {
        $this->publishes = new PublishModel();
        $this->yt = new YouTubePublishService();
    }

    public function handle(array $payload): bool
    {
        $publishId = (int)($payload['publish_id'] ?? 0);
        if ($publishId <= 0) {
            throw new \RuntimeException('publish_id zorunlu.');
        }

        try {
            return $this->doHandle($payload, $publishId);
        } catch (\Throwable $e) {
            // UI "yayınlanıyor"da kalmasın
            try {
                $this->publishes->update($publishId, [
                    'status' => PublishModel::STATUS_FAILED,
                    'error'  => mb_substr($e->getMessage(), 0, 2000),
                ]);
            } catch (\Throwable $ignored) {
            }

            // Queue retry/failed mekanizması çalışsın
            throw $e;
        }
    }

    private function doHandle(array $payload, int $publishId): bool
    {
        $db = Database::connect();

        $row = $db->table('publishes p')
            ->select('
                p.id, p.user_id, p.platform, p.account_id, p.content_id, p.status, p.schedule_at, p.remote_id, p.meta_json,
                c.title as content_title,
                c.base_text as content_text,
                c.media_type as content_media_type,
                c.media_path as content_media_path,
                c.meta_json as content_meta_json,
                sa.id as sa_id
            ')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->where('p.id', $publishId)
            ->get()->getRowArray();

        if (!$row) {
            throw new \RuntimeException('Publish kaydı bulunamadı: #' . $publishId);
        }

        $status = (string)($row['status'] ?? '');
        if ($status === PublishModel::STATUS_PUBLISHED) return true;
        if ($status === PublishModel::STATUS_CANCELED)  return true;

        $remoteId = trim((string)($row['remote_id'] ?? ''));
        if ($remoteId !== '') {
            $this->publishes->update($publishId, [
                'status'       => PublishModel::STATUS_PUBLISHED,
                'published_at' => date('Y-m-d H:i:s'),
                'error'        => null,
            ]);
            return true;
        }

        $platform = strtolower(trim((string)($row['platform'] ?? '')));
        if ($platform !== 'youtube') {
            throw new \RuntimeException('Desteklenmeyen platform (youtube handler): ' . $platform);
        }

        $mediaType = strtolower(trim((string)($row['content_media_type'] ?? '')));
        $mediaPath = trim((string)($row['content_media_path'] ?? ''));

        if ($mediaType !== 'video') {
            throw new \RuntimeException('YouTube için video zorunlu. content.media_type=' . ($mediaType ?: 'null'));
        }
        if ($mediaPath === '') {
            throw new \RuntimeException('YouTube için media_path boş.');
        }

        $absPath = rtrim(FCPATH, '/\\') . DIRECTORY_SEPARATOR . ltrim($mediaPath, '/\\');
        if (!is_file($absPath)) {
            throw new \RuntimeException('Video dosyası bulunamadı: ' . $absPath);
        }

        $fileSize = filesize($absPath);
        if ($fileSize === false || (int)$fileSize <= 0) {
            throw new \RuntimeException('Video dosyası boyutu okunamadı: ' . $absPath);
        }
        $fileSize = (int)$fileSize;

        $contentMeta = [];
        if (!empty($row['content_meta_json'])) {
            $tmp = json_decode((string)$row['content_meta_json'], true);
            if (is_array($tmp)) $contentMeta = $tmp;
        }

        $ytTitle = trim((string)($contentMeta['youtube']['title'] ?? ''));
        $privacy = strtolower(trim((string)($contentMeta['youtube']['privacy'] ?? 'public')));

        if ($ytTitle === '') $ytTitle = trim((string)($row['content_title'] ?? ''));
        if ($ytTitle === '') throw new \RuntimeException('YouTube başlığı boş.');

        if (!in_array($privacy, ['public', 'unlisted', 'private'], true)) {
            $privacy = 'public';
        }

        $desc = trim((string)($row['content_text'] ?? ''));
        if ($desc === '') $desc = $ytTitle; // UI boş olmasın, açıklama boşsa title yaz

        $saId = (int)($row['sa_id'] ?? 0);
        if ($saId <= 0) {
            throw new \RuntimeException('YouTube social account bulunamadı (sa_id boş). Hesap bağlı mı?');
        }

        // publishing
        $this->publishes->update($publishId, [
            'status' => PublishModel::STATUS_PUBLISHING,
            'error'  => null,
        ]);

        // Tokeni servis ile al (decrypt + refresh + db update)
        $accessToken = $this->yt->getValidAccessToken($db, $saId);
        if (trim($accessToken) === '') {
            throw new \RuntimeException('YouTube access token alınamadı (decrypt/refresh başarısız). Hesabı yeniden bağla.');
        }

        // 1) Resumable session init
        $initUrl = 'https://www.googleapis.com/upload/youtube/v3/videos?uploadType=resumable&part=snippet,status';

        $initBody = json_encode([
            'snippet' => [
                'title'       => $ytTitle,
                'description' => $desc,
            ],
            'status' => [
                'privacyStatus' => $privacy,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        try {
            [$location, $initRespBody] = $this->startResumableSession($initUrl, $accessToken, $initBody, $fileSize);
        } catch (\Throwable $e) {
            // 401 olursa bir kere refresh dene
            if (str_contains($e->getMessage(), 'HTTP=401')) {
                $accessToken = $this->yt->getValidAccessToken($db, $saId);
                [$location, $initRespBody] = $this->startResumableSession($initUrl, $accessToken, $initBody, $fileSize);
            } else {
                throw $e;
            }
        }

        if ($location === '') {
            throw new \RuntimeException('YouTube resumable session açılamadı. RESP=' . $initRespBody);
        }

        // 2) Upload (chunk + 308)
        $videoId = $this->uploadResumable($location, $absPath, $accessToken);
        if ($videoId === '') {
            throw new \RuntimeException('YouTube upload başarısız: video id dönmedi.');
        }

        $permalink = 'https://youtu.be/' . $videoId;

        $metaJson = [
            'meta' => [
                'published_id' => $videoId,
                'permalink'    => $permalink,
                'privacy'      => $privacy,
            ],
        ];

        $this->publishes->update($publishId, [
            'status'       => PublishModel::STATUS_PUBLISHED,
            'remote_id'    => $videoId,
            'published_at' => date('Y-m-d H:i:s'),
            'error'        => null,
            'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);

        return true;
    }

    private function startResumableSession(string $url, string $accessToken, string $jsonBody, int $fileSize): array
    {
        $headers = [
            'Authorization: Bearer ' . $accessToken,
            'Content-Type: application/json; charset=UTF-8',
            'X-Upload-Content-Type: video/*',
            'X-Upload-Content-Length: ' . $fileSize,
            'Expect:',
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $jsonBody,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER         => true,
            CURLOPT_HTTPHEADER     => $headers,
            CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
            CURLOPT_CONNECTTIMEOUT => 30,
            CURLOPT_TIMEOUT        => 60,
        ]);

        $resp    = curl_exec($ch);
        $http    = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $hdrSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $err     = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http < 200 || $http >= 300) {
            $snippet = '';
            if (is_string($resp)) $snippet = substr($resp, 0, 1200);
            throw new \RuntimeException('YT INIT failed HTTP=' . $http . ' ERR=' . $err . ' RESP=' . $snippet);
        }

        $rawHeaders = substr((string)$resp, 0, $hdrSize);
        $body       = substr((string)$resp, $hdrSize);

        $location = '';
        foreach (explode("\n", $rawHeaders) as $line) {
            if (stripos($line, 'Location:') === 0) {
                $location = trim(substr($line, strlen('Location:')));
                break;
            }
        }

        return [$location, $body];
    }

    private function uploadResumable(string $uploadUrl, string $filePath, string $accessToken): string
    {
        $fp = fopen($filePath, 'rb');
        if (!$fp) throw new \RuntimeException('Video açılamadı: ' . $filePath);

        $fileSize = filesize($filePath);
        if ($fileSize === false || $fileSize <= 0) {
            fclose($fp);
            throw new \RuntimeException('Video boyutu okunamadı: ' . $filePath);
        }
        $fileSize = (int)$fileSize;

        $chunkSize = 8 * 1024 * 1024; // 8MB
        $offset = 0;

        while ($offset < $fileSize) {
            $bytesToSend = min($chunkSize, $fileSize - $offset);

            if (fseek($fp, $offset) !== 0) {
                fclose($fp);
                throw new \RuntimeException('fseek başarısız. offset=' . $offset);
            }

            $data = fread($fp, $bytesToSend);
            if ($data === false || strlen($data) !== $bytesToSend) {
                fclose($fp);
                throw new \RuntimeException('Dosya okunamadı. offset=' . $offset . ' len=' . $bytesToSend);
            }

            $start = $offset;
            $end   = $offset + $bytesToSend - 1;

            $headers = [
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: video/*',
                'Content-Length: ' . $bytesToSend,
                'Content-Range: bytes ' . $start . '-' . $end . '/' . $fileSize,
                'Expect:',
            ];

            $ch = curl_init($uploadUrl);
            curl_setopt_array($ch, [
                CURLOPT_CUSTOMREQUEST  => 'PUT',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER     => $headers,
                CURLOPT_POSTFIELDS     => $data,
                CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
                CURLOPT_CONNECTTIMEOUT => 30,
                CURLOPT_TIMEOUT        => 0,
                CURLOPT_TCP_KEEPALIVE  => 1,
                CURLOPT_HEADER         => true, // 308 için header okuyacağız
            ]);

            $resp = curl_exec($ch);
            $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $hdrSize = (int)curl_getinfo($ch, CURLINFO_HEADER_SIZE);
            $err  = curl_error($ch);
            curl_close($ch);

            if ($resp === false) {
                fclose($fp);
                throw new \RuntimeException('YT UPLOAD failed HTTP=' . $http . ' ERR=' . $err);
            }

            $rawHeaders = substr((string)$resp, 0, $hdrSize);
            $body       = substr((string)$resp, $hdrSize);

            // 200/201 => bitti
            if ($http === 200 || $http === 201) {
                $json = json_decode((string)$body, true);
                fclose($fp);
                return trim((string)($json['id'] ?? ''));
            }

            // 308 => devam
            if ($http === 308) {
                $rangeEnd = null;
                foreach (explode("\n", $rawHeaders) as $line) {
                    $line = trim($line);
                    if (stripos($line, 'Range:') === 0) {
                        if (preg_match('~bytes=\d+-(\d+)~i', $line, $m)) {
                            $rangeEnd = (int)$m[1];
                        }
                        break;
                    }
                }

                if ($rangeEnd !== null) {
                    $offset = $rangeEnd + 1;
                } else {
                    $offset = $end + 1;
                }
                continue;
            }

            // diğer hatalar
            $snippet = substr((string)$resp, 0, 1200);
            fclose($fp);
            throw new \RuntimeException('YT UPLOAD failed HTTP=' . $http . ' ERR=' . $err . ' RESP=' . $snippet);
        }

        fclose($fp);
        return '';
    }
}
