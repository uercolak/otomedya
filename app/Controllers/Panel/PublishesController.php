<?php

namespace App\Controllers\Panel;

use App\Controllers\BaseController;

class PublishesController extends BaseController
{
    private function ensureUser(): ?\CodeIgniter\HTTP\RedirectResponse
    {
        if (!session('is_logged_in')) {
            return redirect()->to(site_url('auth/login'));
        }
        // user role kontrolün varsa burada ekleyebilirsin
        return null;
    }

    public function index()
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $q        = trim((string)($this->request->getGet('q') ?? ''));
        $platform = trim((string)($this->request->getGet('platform') ?? ''));
        $status   = trim((string)($this->request->getGet('status') ?? ''));
        $dateFrom = trim((string)($this->request->getGet('date_from') ?? ''));
        $dateTo   = trim((string)($this->request->getGet('date_to') ?? ''));

        $builder = $db->table('publishes p')
            ->select('p.*')
            ->select('sa.name as sa_name, sa.username as sa_username')
            ->select('c.title as content_title')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->where('p.user_id', $userId);

        if ($q !== '') {
            $builder->groupStart()
                ->like('p.remote_id', $q)
                ->orLike('c.title', $q)
                ->orLike('p.error', $q)
            ->groupEnd();
        }

        if ($platform !== '') $builder->where('p.platform', $platform);
        if ($status !== '') $builder->where('p.status', $status);

        if ($dateFrom !== '') $builder->where('p.created_at >=', $dateFrom . ' 00:00:00');
        if ($dateTo !== '')   $builder->where('p.created_at <=', $dateTo . ' 23:59:59');

        $builder->orderBy('p.id', 'DESC');

        // basit pagination
        $perPage = 15;
        $page = max(1, (int)($this->request->getGet('page') ?? 1));
        $offset = ($page - 1) * $perPage;

        $total = (clone $builder)->countAllResults(false);
        $rows  = $builder->limit($perPage, $offset)->get()->getResultArray();

        $platforms = $db->table('publishes')
            ->select('platform')->distinct()
            ->where('user_id', $userId)
            ->orderBy('platform', 'ASC')
            ->get()->getResultArray();
        $platformOptions = array_values(array_filter(array_map(fn($r)=> (string)$r['platform'], $platforms)));

        $statusOptions = ['queued','publishing','published','failed','canceled'];

        return view('panel/publishes/index', [
            'rows' => $rows,
            'filters' => [
                'q' => $q,
                'platform' => $platform,
                'status' => $status,
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
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $row = $db->table('publishes p')
            ->select('p.*')
            ->select('sa.name as sa_name, sa.username as sa_username')
            ->select('c.title as content_title, c.base_text as content_text')
            ->join('social_accounts sa', 'sa.id = p.account_id', 'left')
            ->join('contents c', 'c.id = p.content_id', 'left')
            ->where('p.id', $id)
            ->where('p.user_id', $userId) // güvenlik: sadece kendi kaydı
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('panel/publishes'))->with('error', 'Paylaşım kaydı bulunamadı.');
        }

        $previewUrl = null;
        if (($row['status'] ?? '') === 'published') {
            $previewUrl = $this->buildPreviewUrl(
                (string)($row['platform'] ?? ''),
                (string)($row['remote_id'] ?? ''),
                (string)($row['sa_username'] ?? '')
            );
        }

        return view('panel/publishes/show', [
            'row' => $row,
            'previewUrl' => $previewUrl,
        ]);
    }

    private function buildPreviewUrl(?string $platform, ?string $remoteId, ?string $username = null): ?string
    {
        $platform = strtolower(trim((string)$platform));
        $remoteId = trim((string)$remoteId);
        $username = trim((string)$username);

        if ($remoteId === '') return null;

        // remote_id bazen zaten URL olabilir
        if (preg_match('~^https?://~i', $remoteId)) {
            return $remoteId;
        }

        // platform bazlı kaba mapping (gerekirse sonra genişletiriz)
        switch ($platform) {
            case 'x':
            case 'twitter':
                // tweet id
                return 'https://x.com/i/web/status/' . rawurlencode($remoteId);

            case 'instagram':
                // çoğu entegrasyonda remote_id shortcode olur (p/SHORTCODE)
                return 'https://www.instagram.com/p/' . rawurlencode($remoteId) . '/';

            case 'facebook':
                return 'https://www.facebook.com/' . rawurlencode($remoteId);

            case 'linkedin':
                // bazen URN/ID gelir; burada “en azından link üret” yaklaşımı
                return 'https://www.linkedin.com/feed/update/' . rawurlencode($remoteId);

            case 'tiktok':
                // tiktoK çoğu zaman video id; username varsa daha iyi
                if ($username !== '') {
                    return 'https://www.tiktok.com/@' . rawurlencode($username) . '/video/' . rawurlencode($remoteId);
                }
                return null;

            default:
                return null;
        }
    }

   public function cancel(int $id)
    {
        if ($r = $this->ensureUser()) return $r;

        $userId = (int) session('user_id');
        $db = \Config\Database::connect();

        $row = $db->table('publishes')
            ->select('id,status,user_id,job_id')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->get()->getRowArray();

        if (!$row) {
            return redirect()->to(site_url('panel/publishes'))->with('error', 'Paylaşım kaydı bulunamadı.');
        }

        $status = (string)($row['status'] ?? '');

        // ✅ queued + scheduled iptal edilebilir (istersen scheduled'ı çıkarabilirsin)
        $cancelable = in_array($status, ['queued', 'scheduled'], true);

        if (!$cancelable) {
            return redirect()->to(site_url('panel/publishes/' . $id))
                ->with('error', 'Bu paylaşım iptal edilemez. (Sadece queued/scheduled)');
        }

        $now = date('Y-m-d H:i:s');
        $db->transStart();

        // 1) Publish'i iptal et
        $db->table('publishes')
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'status'     => 'canceled',
                'updated_at' => $now,
            ]);

        // 2) Job queued ise cancel et (best-effort)
        $jobId = (int)($row['job_id'] ?? 0);
        if ($jobId > 0) {
            $db->table('jobs')
                ->where('id', $jobId)
                ->where('status', 'queued') // sadece bekleyen işi iptal et
                ->update([
                    'status'     => 'canceled',
                    'last_error' => 'Canceled by user',
                    'locked_at'  => null,
                    'locked_by'  => null,
                    'updated_at' => $now,
                ]);
        }

        $db->transComplete();

        if (!$db->transStatus()) {
            return redirect()->to(site_url('panel/publishes/' . $id))
                ->with('error', 'İptal işlemi sırasında hata oluştu.');
        }

        return redirect()->to(site_url('panel/publishes/' . $id))
            ->with('success', 'Paylaşım iptal edildi.');
    }
}
