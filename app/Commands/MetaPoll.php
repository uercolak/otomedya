<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Database;

class MetaPoll extends BaseCommand
{
    protected $group       = 'Meta';
    protected $name        = 'meta:poll';
    protected $description = 'Process meta_media_jobs rows and finalize Instagram video publishing when ready.';

    public function run(array $params)
    {
        $db = Database::connect();
        $now = date('Y-m-d H:i:s');

        $limit = 20; // başlangıç
        $maxAttempts = 12; // meta bazen uzun sürer; 12 deneme ~ birkaç dakika/backoff ile

        $rows = $db->table('meta_media_jobs mmj')
            ->select('mmj.*, sat.access_token as meta_access_token')
            ->join('social_account_tokens sat', "sat.social_account_id = mmj.social_account_id AND sat.provider='meta'", 'left')
            ->whereIn('mmj.status', ['processing','queued'])
            ->where('mmj.next_retry_at <=', $now)
            ->where('mmj.attempts <', $maxAttempts)
            ->orderBy('mmj.next_retry_at', 'ASC')
            ->limit($limit)
            ->get()->getResultArray();

        if (!$rows) {
            CLI::write('[meta:poll] no due jobs', 'yellow');
            return;
        }

        $graphVersion = getenv('META_GRAPH_VERSION') ?: 'v24.0';

        foreach ($rows as $j) {
            $id         = (int)($j['id'] ?? 0);
            $creationId = (string)($j['creation_id'] ?? '');
            $igUserId   = (string)($j['ig_user_id'] ?? '');
            $token      = (string)($j['meta_access_token'] ?? '');

            if ($id <= 0 || $creationId === '' || $igUserId === '' || $token === '') {
                $this->failJob($db, $id, 'meta:poll invalid row/token/ids');
                continue;
            }

            // attempts++ + basit lock (kolonların sende var olduğunu varsayıyorum; yoksa bu update kısmını kaldır)
            $attempts = (int)($j['attempts'] ?? 0) + 1;

            $db->table('meta_media_jobs')->where('id', $id)->update([
                'attempts'    => $attempts,
                'updated_at'  => $now,
            ]);

            try {
                // 1) status_code oku
                $statusResp = $this->getJson("https://graph.facebook.com/{$graphVersion}/{$creationId}?fields=status_code&access_token=" . urlencode($token));
                $statusCode = (string)($statusResp['status_code'] ?? '');

                // status_code yoksa: tekrar dene
                if ($statusCode === '') {
                    $this->reschedule($db, $id, $attempts, 'status_code missing', $statusResp);
                    continue;
                }

                // IN_PROGRESS vb -> reschedule
                if (!in_array($statusCode, ['FINISHED','PUBLISHED'], true)) {
                    $this->reschedule($db, $id, $attempts, "status={$statusCode}", $statusResp);
                    continue;
                }

                // 2) FINISHED ise publish et
                // Not: bazı akışlarda FINISHED sonrası media_publish gerekir
                $publishResp = $this->postForm("https://graph.facebook.com/{$graphVersion}/{$igUserId}/media_publish", [
                    'creation_id'  => $creationId,
                    'access_token' => $token,
                ]);

                $mediaId = (string)($publishResp['id'] ?? '');
                if ($mediaId === '') {
                    $this->reschedule($db, $id, $attempts, 'media_publish did not return id', $publishResp);
                    continue;
                }

                // 3) permalink al
                $permaResp = $this->getJson("https://graph.facebook.com/{$graphVersion}/{$mediaId}?fields=permalink&access_token=" . urlencode($token));
                $permalink = (string)($permaResp['permalink'] ?? '');

                // 4) publishes kaydını Published yap
                $publishId = (int)($j['publish_id'] ?? 0);
                if ($publishId > 0) {
                    $metaJson = [
                        'meta' => [
                            'creation_id'  => $creationId,
                            'published_id' => $mediaId,
                            'permalink'    => $permalink,
                            'via'          => 'meta:poll',
                        ],
                    ];

                    $db->table('publishes')->where('id', $publishId)->update([
                        'status'       => 'published',
                        'remote_id'    => $mediaId,
                        'published_at' => date('Y-m-d H:i:s'),
                        'error'        => null,
                        'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                        'updated_at'   => date('Y-m-d H:i:s'),
                    ]);
                }

                // 5) meta_media_jobs done
                $db->table('meta_media_jobs')->where('id', $id)->update([
                    'status'             => 'done',
                    'status_code'        => 'PUBLISHED',
                    'published_media_id' => $mediaId,
                    'last_error'         => null,
                    'last_response_json' => json_encode([
                        'status'  => $statusResp,
                        'publish' => $publishResp,
                        'perma'   => $permaResp,
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                    'next_retry_at'      => null,
                    'updated_at'         => date('Y-m-d H:i:s'),
                ]);

                CLI::write("[meta:poll] OK publish_id={$publishId} media_id={$mediaId}", 'green');

            } catch (\Throwable $e) {
                $this->reschedule($db, $id, $attempts, $e->getMessage(), null);
            }
        }
    }

    private function backoffSeconds(int $attempts): int
    {
        // 1:20s, 2:60s, 3:120s, 4:180s, 5+:300s
        if ($attempts <= 1) return 20;
        if ($attempts === 2) return 60;
        if ($attempts === 3) return 120;
        if ($attempts === 4) return 180;
        return 300;
    }

    private function reschedule($db, int $id, int $attempts, string $note, $raw): void
    {
        $next = date('Y-m-d H:i:s', time() + $this->backoffSeconds($attempts));

        $db->table('meta_media_jobs')->where('id', $id)->update([
            'status'             => 'processing',
            'status_code'        => 'IN_PROGRESS',
            'next_retry_at'      => $next,
            'last_error'         => $note,
            'last_response_json' => $raw ? json_encode($raw, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null,
            'updated_at'         => date('Y-m-d H:i:s'),
        ]);
    }

    private function failJob($db, int $id, string $note): void
    {
        if ($id <= 0) return;
        $db->table('meta_media_jobs')->where('id', $id)->update([
            'status'        => 'failed',
            'last_error'    => $note,
            'updated_at'    => date('Y-m-d H:i:s'),
        ]);
    }

    private function getJson(string $url): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
        ]);
        $body = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException("GET failed HTTP={$http} ERR={$err} BODY=" . (string)$body);
        }

        $json = json_decode((string)$body, true);
        return is_array($json) ? $json : [];
    }

    private function postForm(string $url, array $data): array
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query($data),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $body = curl_exec($ch);
        $http = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err  = curl_error($ch);
        curl_close($ch);

        if ($body === false || $http < 200 || $http >= 300) {
            throw new \RuntimeException("POST failed HTTP={$http} ERR={$err} BODY=" . (string)$body);
        }

        $json = json_decode((string)$body, true);
        return is_array($json) ? $json : [];
    }
}
