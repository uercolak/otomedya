<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\PublishModel;
use App\Models\JobModel;
use App\Services\MetaPublishService;

class PublishesController extends BaseController
{
    private function db()
    {
        return \Config\Database::connect();
    }

    private function metaCfg(): array
    {
        return [
            'graph_ver'  => (string)(getenv('META_GRAPH_VER') ?: 'v24.0'),
            'verify_ssl' => (getenv('META_VERIFY_SSL') !== false && getenv('META_VERIFY_SSL') !== '0'),
        ];
    }

    private function httpClient(array $cfg)
    {
        return \Config\Services::curlrequest([
            'timeout' => 30,
            'verify'  => (bool)$cfg['verify_ssl'],
            'http_errors' => false,
        ]);
    }

    private function graphUrl(array $cfg, string $path, array $query = []): string
    {
        $base = "https://graph.facebook.com/{$cfg['graph_ver']}/" . ltrim($path, '/');
        if (!empty($query)) $base .= (str_contains($base, '?') ? '&' : '?') . http_build_query($query);
        return $base;
    }

    private function httpGetJson(string $url, array $cfg): array
    {
        $client = $this->httpClient($cfg);
        try {
            $resp = $client->get($url);
            $body = (string)$resp->getBody();
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
            $body = (string)$resp->getBody();
            $data = json_decode($body, true);
            if (!is_array($data)) $data = ['raw' => $body];
            $data['_http_code'] = $resp->getStatusCode();
            return $data;
        } catch (\Throwable $e) {
            return ['error' => ['message' => $e->getMessage(),'type' => 'Exception'], '_http_code' => 0];
        }
    }

    public function index()
    {
        $db = $this->db();

        $q        = trim((string)($this->request->getGet('q') ?? ''));
        $platform = trim((string)($this->request->getGet('platform') ?? ''));
        $status   = trim((string)($this->request->getGet('status') ?? ''));
        $user     = trim((string)($this->request->getGet('user') ?? ''));
        $dateFrom = trim((string)($this->request->getGet('date_from') ?? ''));
        $dateTo   = trim((string)($this->request->getGet('date_to') ?? ''));

        $builder = $db->table('publishes p')
            ->select('p.*')
            ->select('u.name as user_name, u.email as user_email')
            ->select('sa.platform as sa_platform, sa.name as sa_name, sa.username as sa_username')
            ->select('c.title as content_title')
            ->select('j.status as job_status, j.attempts as job_attempts, j.max_attempts as job_max_attempts, j.last_error as job_last_error, j.run_at as job_run_at, j.locked_at as job_locked_at')
            ->select('mmj.id as mmj_id, mmj.creation_id as mmj_creation_id, mmj.status as mmj_status, mmj.status_code as mmj_status_code, mmj.attempts as mmj_attempts, mmj.next_retry_at as mmj_next_retry_at, mmj.published_media_id as mmj_published_media_id')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->join('jobs j', 'j.id = p.job_id', 'left')
            ->join('meta_media_jobs mmj', 'mmj.publish_id = p.id', 'left');

        if ($q !== '') {
            $builder->groupStart()
                ->like('p.remote_id', $q)
                ->orLike('p.idempotency_key', $q)
                ->orLike('c.title', $q)
                ->orLike('p.error', $q)
                ->orLike('mmj.creation_id', $q)
            ->groupEnd();
        }

        if ($platform !== '') $builder->where('p.platform', $platform);
        if ($status !== '')   $builder->where('p.status', $status);

        if ($user !== '') {
            $builder->groupStart()
                ->like('u.name', $user)
                ->orLike('u.email', $user)
            ->groupEnd();
        }

        if ($dateFrom !== '') $builder->where('p.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo !== '')   $builder->where('p.created_at <=', $dateTo . ' 23:59:59');

        $builder->orderBy('p.id', 'DESC');

        $perPage = 15;
        $page = (int)($this->request->getGet('page') ?? 1);
        $offset = ($page - 1) * $perPage;

        $total = (clone $builder)->countAllResults(false);
        $rows = $builder->limit($perPage, $offset)->get()->getResultArray();

        $platforms = $db->table('publishes')->select('platform')->distinct()->orderBy('platform','ASC')->get()->getResultArray();
        $platformOptions = array_values(array_filter(array_map(fn($r)=> (string)$r['platform'], $platforms)));

        $statusOptions = ['queued','scheduled','publishing','published','failed','canceled'];

        return view('admin/publishes/index', [
            'rows' => $rows,
            'filters' => [
                'q' => $q,
                'platform' => $platform,
                'status' => $status,
                'user' => $user,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ],
            'platformOptions' => $platformOptions,
            'statusOptions' => $statusOptions,
            'pagination' => [
                'total' => $total,
                'perPage' => $perPage,
                'page' => $page,
                'pages' => (int)ceil($total / $perPage),
            ],
        ]);
    }

    public function show(int $id)
    {
        $db = $this->db();

        $row = $db->table('publishes p')
            ->select('p.*')
            ->select('u.name as user_name, u.email as user_email')
            ->select('sa.platform as sa_platform, sa.name as sa_name, sa.username as sa_username, sa.external_id as sa_external_id, sa.meta_page_id as sa_meta_page_id')
            ->select('c.title as content_title, c.base_text as content_text')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->where('p.id', $id)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('admin/publishes'))->with('error', 'Paylaşım kaydı bulunamadı.');
        }

        $mmj = null;
        if ($db->tableExists('meta_media_jobs')) {
            $mmj = $db->table('meta_media_jobs')->where('publish_id', $id)->orderBy('id','DESC')->get()->getRowArray();
        }

        return view('admin/publishes/show', [
            'row' => $row,
            'mmj' => $mmj,
        ]);
    }

    public function create()
    {
        $db = $this->db();

        $contents = $db->table('contents')
            ->select('id, title, user_id, created_at')
            ->orderBy('id', 'DESC')
            ->limit(200)
            ->get()->getResultArray();

        $accounts = $db->table('social_accounts')
            ->select('id, user_id, platform, name, username')
            ->orderBy('id', 'DESC')
            ->limit(200)
            ->get()->getResultArray();

        $debug = [
            'db' => $db->getDatabase(),
            'contents_count' => count($contents),
            'accounts_count' => count($accounts),
        ];

        return view('admin/publishes/create', [
            'contents' => $contents,
            'accounts' => $accounts,
            'debug'    => $debug,
        ]);
    }

    public function store()
    {
        $publishModel = new PublishModel();
        $jobModel     = new JobModel();

        $contentId  = (int)($this->request->getPost('content_id') ?? 0);
        $accountId  = (int)($this->request->getPost('account_id') ?? 0);
        $scheduleAt = trim((string)($this->request->getPost('schedule_at') ?? ''));
        $userId     = (int)($this->request->getPost('user_id') ?? 0);

        if ($contentId <= 0 || $accountId <= 0) {
            return redirect()->back()->withInput()->with('error', 'Lütfen içerik ve sosyal hesap seçin.');
        }

        $db = $this->db();
        $acc = $db->table('social_accounts')->select('id, user_id, platform')->where('id', $accountId)->get()->getRowArray();
        if (!$acc) return redirect()->back()->withInput()->with('error', 'Sosyal hesap bulunamadı.');

        $cnt = $db->table('contents')->select('id, user_id')->where('id', $contentId)->get()->getRowArray();
        if (!$cnt) return redirect()->back()->withInput()->with('error', 'İçerik bulunamadı.');

        if ($userId <= 0) $userId = (int)($cnt['user_id'] ?? $acc['user_id'] ?? 0);

        $scheduleAtDb = null;
        if ($scheduleAt !== '') {
            $ts = strtotime($scheduleAt);
            if ($ts === false) return redirect()->back()->withInput()->with('error', 'Planlanan zaman formatı geçersiz. Örn: 2025-01-01 12:00:00');
            $scheduleAtDb = date('Y-m-d H:i:s', $ts);
        }

        $idempotencyKey = hash('sha256', $acc['platform'] . '|' . $accountId . '|' . $contentId . '|' . ($scheduleAtDb ?? 'now'));
        $now = date('Y-m-d H:i:s');

        $publishId = $publishModel->insert([
            'job_id' => null,
            'user_id' => $userId ?: null,
            'platform' => (string)$acc['platform'],
            'account_id' => $accountId,
            'content_id' => $contentId,
            'status' => PublishModel::STATUS_QUEUED,
            'schedule_at' => $scheduleAtDb,
            'idempotency_key' => $idempotencyKey,
            'meta_json' => null,
        ], true);

        if (!$publishId) return redirect()->back()->withInput()->with('error', 'Publish kaydı oluşturulamadı.');

        $payload = [
            'publish_id'  => (int)$publishId,
            'platform'    => (string)$acc['platform'],
            'account_id'  => (int)$accountId,
            'content_id'  => (int)$contentId,
            'schedule_at' => $scheduleAtDb,
        ];

        $runAt = $scheduleAtDb ?: $now;

        // ✅ PENDING YOK -> QUEUED
        $jobId = $jobModel->insert([
            'type'         => 'publish_post',
            'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
            'status'       => 'queued',
            'priority'     => 100,
            'run_at'       => $runAt,
            'locked_at'    => null,
            'locked_by'    => null,
            'attempts'     => 0,
            'max_attempts' => 3,
            'last_error'   => null,
            'created_at'   => $now,
            'updated_at'   => $now,
        ], true);

        if (!$jobId) {
            $publishModel->update($publishId, [
                'status'     => PublishModel::STATUS_FAILED,
                'error'      => 'Job oluşturulamadı',
                'updated_at' => $now,
            ]);
            return redirect()->back()->withInput()->with('error', 'Job oluşturulamadı.');
        }

        $publishModel->update($publishId, [
            'job_id'      => (int)$jobId,
            'updated_at'  => $now,
        ]);

        return redirect()->to(site_url('admin/jobs/' . (int)$jobId))
            ->with('success', 'Planlı paylaşım oluşturuldu. İş kuyruğa eklendi.');
    }

    public function cancel(int $id)
    {
        $db = $this->db();
        $now = date('Y-m-d H:i:s');

        $pub = $db->table('publishes')->where('id', $id)->get()->getRowArray();
        if (!$pub) return redirect()->to(site_url('admin/publishes'))->with('error', 'Paylaşım bulunamadı.');

        $db->table('publishes')->where('id', $id)->update([
            'status' => 'canceled',
            'updated_at' => $now,
        ]);

        $jobId = (int)($pub['job_id'] ?? 0);
        if ($jobId > 0 && $db->tableExists('jobs')) {
            $db->table('jobs')->where('id', $jobId)->update([
                'status' => 'canceled',
                'locked_at' => null,
                'locked_by' => null,
                'updated_at' => $now,
            ]);
        }

        if ($db->tableExists('meta_media_jobs')) {
            $db->table('meta_media_jobs')->where('publish_id', $id)->update([
                'status' => 'failed', // enum'da canceled yok
                'last_error' => 'Canceled by admin',
                'updated_at' => $now,
            ]);
        }

        return redirect()->to(site_url('admin/publishes/' . $id))->with('success', 'Paylaşım iptal edildi.');
    }

    public function resetJob(int $id)
    {
        $db = $this->db();
        $now = date('Y-m-d H:i:s');

        $pub = $db->table('publishes')->where('id', $id)->get()->getRowArray();
        if (!$pub) return redirect()->to(site_url('admin/publishes'))->with('error', 'Paylaşım bulunamadı.');

        $jobId = (int)($pub['job_id'] ?? 0);
        if ($jobId <= 0) {
            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'Bu paylaşımın job_id değeri yok.');
        }

        $db->table('jobs')->where('id', $jobId)->update([
            'status' => 'queued',
            'attempts' => 0,
            'last_error' => null,
            'run_at' => $now,
            'locked_at' => null,
            'locked_by' => null,
            'updated_at' => $now,
        ]);

        $db->table('publishes')->where('id', $id)->update([
            'status' => 'queued',
            'error' => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('admin/publishes/' . $id))->with('success', 'Job resetlendi ve publish tekrar kuyruğa alındı.');
    }

    public function retry(int $id)
    {
        $db = $this->db();
        $now = date('Y-m-d H:i:s');

        $pub = $db->table('publishes')->where('id', $id)->get()->getRowArray();
        if (!$pub) return redirect()->to(site_url('admin/publishes'))->with('error', 'Paylaşım bulunamadı.');

        // Öncelik: meta_media_jobs varsa onu canlandır
        if ($db->tableExists('meta_media_jobs')) {
            $mmj = $db->table('meta_media_jobs')->where('publish_id', $id)->orderBy('id','DESC')->get()->getRowArray();
            if ($mmj) {
                $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                    'status' => 'processing',
                    'next_retry_at' => $now,
                    'last_error' => null,
                    'updated_at' => $now,
                ]);

                $db->table('publishes')->where('id', $id)->update([
                    'status' => 'publishing',
                    'error' => null,
                    'updated_at' => $now,
                ]);

                return redirect()->to(site_url('admin/publishes/' . $id))->with('success', 'Video pipeline tekrar denenecek (meta_media_jobs).');
            }
        }

        // meta job yoksa: normal job reset/requeue
        $jobId = (int)($pub['job_id'] ?? 0);
        if ($jobId > 0 && $db->tableExists('jobs')) {
            $db->table('jobs')->where('id', $jobId)->update([
                'status' => 'queued',
                'run_at' => $now,
                'locked_at' => null,
                'locked_by' => null,
                'updated_at' => $now,
            ]);
        }

        $db->table('publishes')->where('id', $id)->update([
            'status' => 'queued',
            'error' => null,
            'updated_at' => $now,
        ]);

        return redirect()->to(site_url('admin/publishes/' . $id))->with('success', 'Paylaşım tekrar kuyruğa alındı.');
    }

    public function check(int $id)
    {
        $db = $this->db();
        $now = date('Y-m-d H:i:s');

        if (!$db->tableExists('meta_media_jobs')) {
            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'meta_media_jobs tablosu yok.');
        }

        $mmj = $db->table('meta_media_jobs')->where('publish_id', $id)->orderBy('id','DESC')->get()->getRowArray();
        if (!$mmj) {
            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'Bu paylaşıma bağlı meta_media_job bulunamadı.');
        }

        $cfg = $this->metaCfg();

        $creationId = (string)$mmj['creation_id'];
        $igUserId   = (string)$mmj['ig_user_id'];
        $socialAccountId = (int)$mmj['social_account_id'];
        $attempts   = (int)$mmj['attempts'];

        $maxAttempts = (int)(getenv('META_MEDIA_JOB_MAX_ATTEMPTS') ?: 60);

        if ($attempts >= $maxAttempts) {
            $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                'status' => 'failed',
                'last_error' => 'Max attempts reached',
                'updated_at' => $now,
            ]);

            $db->table('publishes')->where('id', $id)->update([
                'status' => 'failed',
                'error'  => 'Video işleme zaman aşımı (max attempts).',
                'updated_at' => $now,
            ]);

            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'Max attempts aşıldı. Job failed.');
        }

        // page token
        $tokRow = $db->table('social_account_tokens')
            ->where('social_account_id', $socialAccountId)
            ->where('provider', 'meta')
            ->get()->getRowArray();

        if (!$tokRow || empty($tokRow['access_token'])) {
            $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                'status' => 'failed',
                'last_error' => 'Page token not found',
                'updated_at' => $now,
            ]);

            $db->table('publishes')->where('id', $id)->update([
                'status' => 'failed',
                'error'  => 'Meta page token bulunamadı.',
                'updated_at' => $now,
            ]);

            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'Page token bulunamadı.');
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
            $next = date('Y-m-d H:i:s', time() + (int)(getenv('META_MEDIA_JOB_RETRY_SECONDS') ?: 20));

            $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                'status' => 'processing',
                'status_code' => null,
                'attempts' => $attempts,
                'next_retry_at' => $next,
                'last_error' => 'status_code alınamadı',
                'last_response_json' => json_encode($st, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'status_code alınamadı. Retry scheduled.');
        }

        if ($statusCode === 'ERROR') {
            $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                'status' => 'failed',
                'status_code' => $statusCode,
                'attempts' => $attempts + 1,
                'last_error' => 'Container status ERROR',
                'last_response_json' => json_encode($st, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            $db->table('publishes')->where('id', $id)->update([
                'status' => 'failed',
                'error'  => 'Video işleme hatası (ERROR).',
                'updated_at' => $now,
            ]);

            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'Container ERROR -> failed.');
        }

        if ($statusCode !== 'FINISHED') {
            $attempts++;
            $next = date('Y-m-d H:i:s', time() + (int)(getenv('META_MEDIA_JOB_RETRY_SECONDS') ?: 20));

            $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                'status' => 'processing',
                'status_code' => $statusCode,
                'attempts' => $attempts,
                'next_retry_at' => $next,
                'last_error' => 'IN_PROGRESS',
                'last_response_json' => json_encode($st, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            $db->table('publishes')->where('id', $id)->update([
                'status' => 'publishing',
                'error' => null,
                'updated_at' => $now,
            ]);

            return redirect()->to(site_url('admin/publishes/' . $id))->with('success', 'Container henüz bitmedi (IN_PROGRESS).');
        }

        // FINISHED -> media_publish
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
            $next = date('Y-m-d H:i:s', time() + (int)(getenv('META_MEDIA_JOB_RETRY_SECONDS') ?: 20));

            $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
                'status' => 'processing',
                'status_code' => 'FINISHED',
                'attempts' => $attempts,
                'next_retry_at' => $next,
                'last_error' => $msg,
                'last_response_json' => json_encode($pub, JSON_UNESCAPED_UNICODE),
                'updated_at' => $now,
            ]);

            return redirect()->to(site_url('admin/publishes/' . $id))->with('error', 'Publish başarısız: ' . $msg);
        }

        $publishedMediaId = (string)$pub['id'];

        $meta = new MetaPublishService();
        $permalink = $meta->getInstagramPermalink($publishedMediaId, $pageToken) ?: '';

        $db->table('meta_media_jobs')->where('id', (int)$mmj['id'])->update([
            'status' => 'published',
            'status_code' => 'FINISHED',
            'published_media_id' => $publishedMediaId,
            'published_at' => $now,
            'attempts' => $attempts + 1,
            'last_error' => null,
            'last_response_json' => json_encode($pub, JSON_UNESCAPED_UNICODE),
            'updated_at' => $now,
        ]);

        $metaJson = [
            'meta' => [
                'creation_id'  => $creationId,
                'published_id' => $publishedMediaId,
                'permalink'    => $permalink,
            ],
        ];

        $db->table('publishes')->where('id', $id)->update([
            'status'       => 'published',
            'remote_id'    => $publishedMediaId,
            'published_at' => $now,
            'error'        => null,
            'meta_json'    => json_encode($metaJson, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'updated_at'   => $now,
        ]);

        return redirect()->to(site_url('admin/publishes/' . $id))->with('success', 'Yayın tamamlandı ✅');
    }
}
